<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $indexes = [
        ['cases', 'cases_current_holder_user_id_foreign', 'current_holder_user_id'],
        ['court_dispatch_batches', 'court_dispatch_batches_created_by_user_id_foreign', 'created_by_user_id'],
        ['court_dispatch_batch_items', 'court_dispatch_batch_items_case_id_foreign', 'case_id'],
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->indexes as [$table, $index]) {
            if ($this->indexExists($table, $index)) {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->indexes as [$table, $index, $column]) {
            if (! $this->indexExists($table, $index)) {
                DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$index}` (`{$column}`)");
            }
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $index)
            ->exists();
    }
};
