<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanTrackingBenchmarkData extends Command
{
    protected $signature = 'tracking:benchmark-clean {--chunk=5000 : Cases removed per transaction}';

    protected $description = 'Remove only data created by tracking:benchmark-generate';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing', 'staging'])) {
            $this->error('Benchmark cleanup is allowed only in local, testing, or staging environments.');

            return self::FAILURE;
        }

        $chunkSize = (int) $this->option('chunk');
        if ($chunkSize < 100 || $chunkSize > 10_000) {
            $this->error('--chunk must be between 100 and 10000.');

            return self::INVALID;
        }

        $total = DB::table('cases')->where('entry_source', 'benchmark')->count();
        if ($total === 0) {
            $this->info('No benchmark cases were found.');

            return self::SUCCESS;
        }

        $years = DB::table('cases')
            ->where('entry_source', 'benchmark')
            ->whereNotNull('final_case_year')
            ->distinct()
            ->pluck('final_case_year')
            ->all();
        $removed = 0;
        $progress = $this->output->createProgressBar($total);
        $progress->start();

        while (true) {
            $caseIds = DB::table('cases')
                ->where('entry_source', 'benchmark')
                ->orderBy('id')
                ->limit($chunkSize)
                ->pluck('id');
            if ($caseIds->isEmpty()) {
                break;
            }

            DB::transaction(function () use ($caseIds) {
                DB::table('file_movements')->whereIn('case_id', $caseIds)->delete();
                DB::table('case_petitioners')->whereIn('case_id', $caseIds)->delete();
                DB::table('case_respondents')->whereIn('case_id', $caseIds)->delete();
                DB::table('case_files')->whereIn('case_id', $caseIds)->delete();
                DB::table('cases')->whereIn('id', $caseIds)->delete();
            }, 3);

            $removed += $caseIds->count();
            $progress->advance($caseIds->count());
        }

        foreach ($years as $year) {
            $lastSerial = (int) DB::table('cases')
                ->where('final_case_year', $year)
                ->max('registration_serial');

            DB::table('case_registration_sequences')->updateOrInsert(
                ['year' => $year],
                [
                    'last_serial' => $lastSerial,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $progress->finish();
        $this->newLine(2);
        $this->info(number_format($removed).' benchmark cases removed. Users and master data were not changed.');

        return self::SUCCESS;
    }
}
