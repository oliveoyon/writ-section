<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Services\RtftsCaseReference;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GenerateTrackingBenchmarkData extends Command
{
    protected $signature = 'tracking:benchmark-generate
        {--cases=100000 : Number of benchmark cases (1-3000000)}
        {--movements=5 : Movement rows per case (1-20)}
        {--chunk=500 : Cases inserted per transaction (100-1000)}
        {--with-parties : Add one petitioner and respondent per case}';

    protected $description = 'Generate isolated, removable tracking data for local performance testing';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing', 'staging'])) {
            $this->error('Benchmark data generation is allowed only in local, testing, or staging environments.');

            return self::FAILURE;
        }

        $caseCount = (int) $this->option('cases');
        $movementCount = (int) $this->option('movements');
        $chunkSize = (int) $this->option('chunk');

        if ($caseCount < 1 || $caseCount > 3_000_000) {
            $this->error('--cases must be between 1 and 3000000.');

            return self::INVALID;
        }
        if ($movementCount < 1 || $movementCount > 20) {
            $this->error('--movements must be between 1 and 20.');

            return self::INVALID;
        }
        if ($chunkSize < 100 || $chunkSize > 1000) {
            $this->error('--chunk must be between 100 and 1000.');

            return self::INVALID;
        }
        if (DB::table('cases')->where('entry_source', 'benchmark')->exists()) {
            $this->error('Benchmark cases already exist. Run tracking:benchmark-clean before generating another set.');

            return self::FAILURE;
        }

        $sections = Department::query()->orderBy('id')->pluck('name')->filter()->values()->all();
        if ($sections === []) {
            $this->error('Create departments before generating benchmark data.');

            return self::FAILURE;
        }

        $holderBySection = DB::table('users')
            ->join('departments', 'departments.id', '=', 'users.department')
            ->where('users.is_active', true)
            ->whereIn('users.user_type', ['admin', 'staff'])
            ->orderBy('users.id')
            ->pluck('users.id', 'departments.name')
            ->map(fn ($id) => (int) $id)
            ->all();

        $years = $this->availableYears($caseCount);
        $nextCaseId = ((int) DB::table('cases')->max('id')) + 1;
        DB::connection()->disableQueryLog();
        $generated = 0;
        $movementRows = 0;
        $progress = $this->output->createProgressBar($caseCount);
        $progress->start();

        while ($generated < $caseCount) {
            $take = min($chunkSize, $caseCount - $generated);
            $caseRows = [];
            $historyRows = [];
            $petitionerRows = [];
            $respondentRows = [];

            for ($offset = 0; $offset < $take; $offset++) {
                $ordinal = $generated + $offset;
                [$year, $serial] = $this->nextRegistration($years);
                $caseId = $nextCaseId++;
                $barcode = RtftsCaseReference::barcode($year, $serial);
                $createdAtValue = now()->subDays($ordinal % 1825)->subSeconds($ordinal % 86400);
                $createdAt = $createdAtValue->format('Y-m-d H:i:s');
                $journey = [];

                for ($step = 0; $step < $movementCount; $step++) {
                    $journey[] = $sections[($ordinal + $step) % count($sections)];
                }

                $currentSection = end($journey);
                $currentHolder = $holderBySection[$currentSection] ?? null;
                $currentHolderAt = $createdAtValue->copy()->addMinutes(($movementCount - 1) * 30)->format('Y-m-d H:i:s');

                $caseRows[] = [
                    'id' => $caseId,
                    'lawyer_id' => null,
                    'initiated_by_user_id' => null,
                    'entry_source' => 'benchmark',
                    'case_type' => 'Service Matter',
                    'description' => 'RTFTS benchmark record. Remove before production.',
                    'status' => 'in_progress',
                    'temporary_barcode' => null,
                    'permanent_barcode' => $barcode,
                    'permanent_barcode_generated_at' => $createdAt,
                    'section_verified_at' => $createdAt,
                    'section_verified_by' => null,
                    'final_case_number' => RtftsCaseReference::humanReference($year, $serial),
                    'final_case_year' => $year,
                    'registration_serial' => $serial,
                    'current_section' => $currentSection,
                    'current_holder_user_id' => $currentHolder,
                    'current_holder_at' => $currentHolderAt,
                    'created_at' => $createdAt,
                    'updated_at' => $currentHolderAt,
                ];

                $fromSection = null;
                foreach ($journey as $step => $toSection) {
                    $receivedAt = $createdAtValue->copy()->addMinutes($step * 30)->format('Y-m-d H:i:s');
                    $historyRows[] = [
                        'case_id' => $caseId,
                        'barcode_scanned' => $barcode,
                        'from_section' => $fromSection,
                        'to_section' => $toSection,
                        'movement_type' => 'receive',
                        'received_by_user_id' => $holderBySection[$toSection] ?? null,
                        'received_at' => $receivedAt,
                        'notes' => 'Benchmark movement.',
                        'is_override' => false,
                        'created_at' => $receivedAt,
                        'updated_at' => $receivedAt,
                    ];
                    $fromSection = $toSection;
                }

                if ($this->option('with-parties')) {
                    $petitionerRows[] = [
                        'case_id' => $caseId,
                        'name_or_organization' => 'Benchmark Petitioner '.str_pad((string) $ordinal, 8, '0', STR_PAD_LEFT),
                        'represented_by' => null,
                        'designation' => null,
                        'address' => 'Benchmark address, Dhaka.',
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ];
                    $respondentRows[] = [
                        'case_id' => $caseId,
                        'name_or_organization' => 'Benchmark Respondent '.str_pad((string) $ordinal, 8, '0', STR_PAD_LEFT),
                        'represented_by' => null,
                        'designation' => null,
                        'address' => 'Benchmark office, Dhaka.',
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ];
                }
            }

            DB::transaction(function () use ($caseRows, $historyRows, $petitionerRows, $respondentRows) {
                DB::table('cases')->insert($caseRows);
                foreach (array_chunk($historyRows, 500) as $rows) {
                    DB::table('file_movements')->insert($rows);
                }
                if ($petitionerRows !== []) {
                    DB::table('case_petitioners')->insert($petitionerRows);
                    DB::table('case_respondents')->insert($respondentRows);
                }
            }, 3);

            $generated += $take;
            $movementRows += count($historyRows);
            $progress->advance($take);
            unset($caseRows, $historyRows, $petitionerRows, $respondentRows);
            gc_collect_cycles();
        }

        foreach ($years as $year => $state) {
            if ($state['last_used'] === null) {
                continue;
            }

            DB::table('case_registration_sequences')->updateOrInsert(
                ['year' => $year],
                [
                    'last_serial' => $state['last_used'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $progress->finish();
        $this->newLine(2);
        $this->info(number_format($generated).' benchmark cases and '.number_format($movementRows).' movements created.');
        $this->warn('Run tracking:benchmark-clean before production or before generating another benchmark set.');

        return self::SUCCESS;
    }

    private function availableYears(int $required): array
    {
        $years = [];
        $capacity = 0;

        for ($year = (int) now()->year; $year >= 1971 && $capacity < $required; $year--) {
            $lastSerial = (int) DB::table('cases')
                ->where('final_case_year', (string) $year)
                ->max('registration_serial');
            $years[(string) $year] = [
                'next' => $lastSerial + 1,
                'last_used' => null,
            ];
            $capacity += max(RtftsCaseReference::MAX_SERIAL - $lastSerial, 0);
        }

        if ($capacity < $required) {
            throw new RuntimeException('Not enough RTFTS serial capacity is available between 1971 and the current year.');
        }

        return $years;
    }

    private function nextRegistration(array &$years): array
    {
        foreach ($years as $year => &$state) {
            if ($state['next'] > RtftsCaseReference::MAX_SERIAL) {
                continue;
            }

            $serial = $state['next']++;
            $state['last_used'] = $serial;

            return [$year, $serial];
        }

        throw new RuntimeException('RTFTS benchmark serial capacity was exhausted.');
    }
}
