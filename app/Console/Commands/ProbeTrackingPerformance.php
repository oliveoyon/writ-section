<?php

namespace App\Console\Commands;

use App\Models\CourtCase;
use App\Models\FileMovement;
use App\Models\FileTransferBatch;
use App\Models\FileTransferItem;
use App\Services\CourtCaseSearch;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProbeTrackingPerformance extends Command
{
    protected $signature = 'tracking:benchmark-probe
        {--iterations=5 : Timed executions per query (1-20)}
        {--days=30 : Movement report date range (1-3650)}
        {--case-id= : Case to use; defaults to a benchmark or recent permanent case}
        {--section= : Section to use; defaults to the selected case section}
        {--json : Print machine-readable JSON}';

    protected $description = 'Measure critical tracking queries and display their database execution plans';

    public function __construct(private readonly CourtCaseSearch $caseSearch)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $iterations = (int) $this->option('iterations');
        $days = (int) $this->option('days');
        if ($iterations < 1 || $iterations > 20 || $days < 1 || $days > 3650) {
            $this->error('--iterations must be 1-20 and --days must be 1-3650.');

            return self::INVALID;
        }

        $case = CourtCase::query()
            ->when($this->option('case-id'), fn ($query, $id) => $query->whereKey((int) $id))
            ->when(! $this->option('case-id'), function ($query) {
                $query->whereNotNull('permanent_barcode')
                    ->orderByRaw("CASE WHEN entry_source = 'benchmark' THEN 0 ELSE 1 END")
                    ->latest('id');
            })
            ->first();

        if (! $case || ! $case->permanent_barcode) {
            $this->error('No permanent case is available. Generate benchmark data or provide --case-id.');

            return self::FAILURE;
        }

        $section = trim((string) ($this->option('section') ?: $case->current_section));
        if ($section === '') {
            $this->error('The selected case has no section. Provide --section.');

            return self::FAILURE;
        }

        $rangeStart = now()->subDays($days - 1)->startOfDay();
        $rangeEnd = now()->endOfDay();
        $holderId = $case->current_holder_user_id;
        $partySearch = (string) (DB::table('case_petitioners')
            ->where('case_id', $case->id)
            ->value('name_or_organization') ?: $case->final_case_number);

        $queries = [
            'Exact barcode lookup' => fn () => DB::table('cases')
                ->where('permanent_barcode', $case->permanent_barcode)
                ->limit(1),
            'Case timeline page' => fn () => DB::table('file_movements')
                ->where('case_id', $case->id)
                ->orderByDesc('received_at')
                ->orderByDesc('id')
                ->limit(100),
            'Section movement report' => fn () => DB::table('file_movements')
                ->whereBetween('received_at', [$rangeStart, $rangeEnd])
                ->where(function ($query) use ($section) {
                    $query->where('to_section', $section)
                        ->orWhere('from_section', $section);
                })
                ->orderBy('received_at')
                ->orderBy('id')
                ->limit(100),
            'Current holder queue' => fn () => DB::table('cases')
                ->where('current_holder_user_id', $holderId ?: -1)
                ->where('status', 'in_progress')
                ->orderByDesc('current_holder_at')
                ->limit(100),
            'Incoming handover queue' => fn () => DB::table('file_transfer_items')
                ->join('file_transfer_batches', 'file_transfer_batches.id', '=', 'file_transfer_items.batch_id')
                ->where('file_transfer_items.status', FileTransferItem::STATUS_PENDING)
                ->where('file_transfer_batches.recipient_user_id', $holderId ?: -1)
                ->whereIn('file_transfer_batches.status', [
                    FileTransferBatch::STATUS_PENDING,
                    FileTransferBatch::STATUS_PARTIALLY_RECEIVED,
                ])
                ->orderByDesc('file_transfer_items.sent_at')
                ->limit(100),
            'Court history by case' => fn () => DB::table('court_dispatch_batch_items')
                ->where('case_id', $case->id)
                ->orderByDesc('batch_id')
                ->limit(100),
            'Broad party text search' => fn () => $this->caseSearch
                ->query($partySearch)
                ->limit(30)
                ->toBase(),
            'Dashboard movement aggregate' => fn () => DB::table('file_movements')
                ->whereBetween('received_at', [$rangeStart, $rangeEnd])
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN movement_type = 'receive' THEN 1 ELSE 0 END) as received")
                ->selectRaw("SUM(CASE WHEN movement_type = 'dispatch_to_court' THEN 1 ELSE 0 END) as court_dispatch"),
        ];

        DB::connection()->disableQueryLog();
        $results = [];

        foreach ($queries as $name => $factory) {
            $factory()->get();
            $durations = [];

            for ($iteration = 0; $iteration < $iterations; $iteration++) {
                $started = hrtime(true);
                $rowCount = $factory()->get()->count();
                $durations[] = (hrtime(true) - $started) / 1_000_000;
            }

            $plan = $this->explain($factory());
            $results[] = [
                'query' => $name,
                'minimum_ms' => round(min($durations), 3),
                'average_ms' => round(array_sum($durations) / count($durations), 3),
                'maximum_ms' => round(max($durations), 3),
                'returned_rows' => $rowCount,
                'plan' => $plan,
            ];
        }

        $payload = [
            'driver' => DB::getDriverName(),
            'database' => DB::getDatabaseName(),
            'case_id' => $case->id,
            'section' => $section,
            'iterations' => $iterations,
            'case_count' => CourtCase::query()->count(),
            'movement_count' => FileMovement::query()->count(),
            'results' => $results,
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->components->twoColumnDetail('Driver / database', $payload['driver'].' / '.$payload['database']);
        $this->components->twoColumnDetail('Rows', number_format($payload['case_count']).' cases / '.number_format($payload['movement_count']).' movements');
        $this->components->twoColumnDetail('Probe case / section', $case->id.' / '.$section);
        $this->newLine();
        $this->table(
            ['Query', 'Min ms', 'Avg ms', 'Max ms', 'Rows', 'Execution plan'],
            collect($results)->map(fn (array $result) => [
                $result['query'],
                number_format($result['minimum_ms'], 3),
                number_format($result['average_ms'], 3),
                number_format($result['maximum_ms'], 3),
                $result['returned_rows'],
                $result['plan'],
            ])->all()
        );

        $slow = collect($results)->filter(fn (array $result) => $result['average_ms'] > 500);
        if ($slow->isNotEmpty()) {
            $this->warn($slow->count().' query probe(s) averaged above 500 ms. Review their plans before launch.');
        } else {
            $this->info('All sampled queries averaged below 500 ms on this dataset.');
        }

        return self::SUCCESS;
    }

    private function explain(Builder $query): string
    {
        try {
            if (DB::getDriverName() === 'sqlite') {
                $rows = DB::select('EXPLAIN QUERY PLAN '.$query->toSql(), $query->getBindings());

                return collect($rows)
                    ->pluck('detail')
                    ->filter()
                    ->implode(' | ');
            }

            $rows = DB::select('EXPLAIN '.$query->toSql(), $query->getBindings());

            return collect($rows)->map(function ($row) {
                $row = (array) $row;

                return implode('; ', array_filter([
                    isset($row['table']) ? 'table='.$row['table'] : null,
                    isset($row['type']) ? 'type='.$row['type'] : null,
                    isset($row['key']) ? 'key='.$row['key'] : null,
                    isset($row['rows']) ? 'rows='.$row['rows'] : null,
                    isset($row['Extra']) ? $row['Extra'] : null,
                ]));
            })->implode(' | ');
        } catch (Throwable $exception) {
            return 'EXPLAIN unavailable: '.$exception->getMessage();
        }
    }
}
