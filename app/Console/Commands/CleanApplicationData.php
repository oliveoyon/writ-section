<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanApplicationData extends Command
{
    protected $signature = 'app:clean-data {--force : Execute deletion; otherwise preview only}';

    protected $description = 'Clear cases and lawyers while preserving non-lawyer users and their access configuration';

    private const TABLES = [
        'file_transfer_items', 'file_transfer_batches', 'court_dispatch_batch_items',
        'file_movements', 'court_dispatch_batches', 'case_files', 'case_petitioners',
        'case_respondents', 'cases', 'case_registration_sequences', 'lawyers', 'courts',
        'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens', 'sessions',
        'cache', 'cache_locks',
    ];

    public function handle(): int
    {
        $this->info('Database: '.DB::connection()->getDatabaseName());
        $this->table(['Delete from', 'Rows'], collect(self::TABLES)->map(
            fn ($table) => [$table, DB::table($table)->count()]
        )->push(['users (lawyers only)', DB::table('users')->where('user_type', 'lawyer')->count()])->all());
        $this->info('Preserve: all non-lawyer users, their photos, departments, roles, permissions, and migrations.');

        if (! $this->option('force')) {
            $this->info('Preview only. Use --force to execute.');

            return self::SUCCESS;
        }
        if (app()->environment('production') && ! app()->isDownForMaintenance()) {
            $this->error('Run php artisan down before production cleanup. Stop queue workers and scheduled writers too.');

            return self::FAILURE;
        }

        // Limit file deletion to application upload folders, including orphaned uploads.
        $paths = ['local' => [], 'public' => []];
        foreach (['local' => ['case_files'], 'public' => ['case_files', 'lawyers']] as $disk => $folders) {
            foreach ($folders as $folder) {
                $paths[$disk] = array_merge($paths[$disk], Storage::disk($disk)->allFiles($folder));
            }
        }
        $lawyers = DB::table('users')->where('user_type', 'lawyer')->get();
        $protectedPhotos = DB::table('users')->where(function ($query) {
            $query->where('user_type', '!=', 'lawyer')->orWhereNull('user_type');
        })->pluck('photo')->filter()->all();
        foreach ($lawyers as $lawyer) {
            if ($lawyer->photo && ! in_array($lawyer->photo, $protectedPhotos, true)
                && preg_match('~^storage/user-photos/[a-zA-Z0-9_.-]+$~D', $lawyer->photo)) {
                $paths['public'][] = substr($lawyer->photo, 8);
            }
        }
        foreach ($protectedPhotos as $photo) {
            $paths['public'] = array_values(array_diff($paths['public'], [preg_replace('~^storage/~', '', $photo)]));
        }

        DB::transaction(function () use ($lawyers) {
            foreach (self::TABLES as $table) {
                DB::table($table)->delete();
            }
            foreach (['model_has_roles', 'model_has_permissions'] as $table) {
                DB::table($table)->where('model_type', (new User)->getMorphClass())
                    ->whereIn('model_id', $lawyers->pluck('id'))->delete();
            }
            DB::table('users')->where('user_type', 'lawyer')->delete();
        });

        $failed = false;
        foreach ($paths as $disk => $files) {
            foreach (array_unique($files) as $file) {
                if (! Storage::disk($disk)->delete($file)) {
                    $this->error("Could not delete {$disk}:{$file}");
                    $failed = true;
                }
            }
        }
        foreach (['labels', 'mpdf'] as $folder) {
            $root = storage_path('app/'.$folder);
            if (is_link($root)) {
                $this->error("Refusing linked generated-file directory: {$root}");
                $failed = true;

                continue;
            }
            $disk = Storage::build(['driver' => 'local', 'root' => $root]);
            foreach ($disk->allFiles() as $file) {
                if ($file !== '.gitignore' && ! $disk->delete($file)) {
                    $this->error("Could not delete {$folder}/{$file}");
                    $failed = true;
                }
            }
        }
        foreach (self::TABLES as $table) {
            if (DB::table($table)->exists()) {
                $this->error("Records remain in {$table}; check for active writers.");
                $failed = true;
            }
        }
        if (DB::table('users')->where('user_type', 'lawyer')->exists()) {
            $failed = true;
        }
        $this->info('Remaining non-lawyer users: '.DB::table('users')->count());
        $this->line('SQL backups and application logs are not deleted.');
        if ($failed) {
            $this->error('Database cleanup finished, but verification/file cleanup needs attention.');

            return self::FAILURE;
        }
        $this->info('Cleanup verified: cases, lawyers, and related data are empty.');

        return self::SUCCESS;
    }
}
