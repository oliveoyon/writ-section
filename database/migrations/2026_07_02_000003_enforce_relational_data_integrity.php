<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $references = [
        ['lawyers', 'user_id', 'users', 'restrict'],
        ['cases', 'lawyer_id', 'lawyers', 'set null'],
        ['cases', 'initiated_by_user_id', 'users', 'set null'],
        ['cases', 'section_verified_by', 'users', 'set null'],
        ['cases', 'current_holder_user_id', 'users', 'set null'],
        ['cases', 'returned_by_user_id', 'users', 'set null'],
        ['case_petitioners', 'case_id', 'cases', 'cascade'],
        ['case_respondents', 'case_id', 'cases', 'cascade'],
        ['case_files', 'case_id', 'cases', 'cascade'],
        ['file_movements', 'case_id', 'cases', 'restrict'],
        ['file_movements', 'received_by_user_id', 'users', 'set null'],
        ['file_movements', 'court_id', 'courts', 'restrict'],
        ['file_movements', 'court_dispatch_batch_id', 'court_dispatch_batches', 'restrict'],
        ['court_dispatch_batches', 'court_id', 'courts', 'restrict'],
        ['court_dispatch_batches', 'created_by_user_id', 'users', 'set null'],
        ['court_dispatch_batch_items', 'batch_id', 'court_dispatch_batches', 'restrict'],
        ['court_dispatch_batch_items', 'case_id', 'cases', 'restrict'],
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->references as [$table, $column, $referencedTable]) {
            $this->assertNoOrphans($table, $column, $referencedTable);
        }

        $tables = DB::table('information_schema.TABLES')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_TYPE', 'BASE TABLE')
            ->pluck('TABLE_NAME');

        foreach ($tables as $table) {
            DB::statement(sprintf('ALTER TABLE `%s` ENGINE=InnoDB', str_replace('`', '``', $table)));
        }

        foreach ($this->references as [$table, $column, $referencedTable, $deleteRule]) {
            $this->addForeignKeyIfMissing($table, $column, $referencedTable, $deleteRule);
        }
    }

    public function down(): void
    {
        // Intentionally irreversible: reverting would weaken historical data protection.
    }

    private function assertNoOrphans(string $table, string $column, string $referencedTable): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column) || !Schema::hasTable($referencedTable)) {
            return;
        }

        $count = DB::table($table . ' as child')
            ->leftJoin($referencedTable . ' as parent', 'child.' . $column, '=', 'parent.id')
            ->whereNotNull('child.' . $column)
            ->whereNull('parent.id')
            ->count();

        if ($count > 0) {
            throw new \RuntimeException("Cannot enforce {$table}.{$column}: {$count} orphaned record(s) found.");
        }
    }

    private function addForeignKeyIfMissing(
        string $table,
        string $column,
        string $referencedTable,
        string $deleteRule
    ): void {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column) || !Schema::hasTable($referencedTable)) {
            return;
        }

        $onDelete = match ($deleteRule) {
            'cascade' => 'CASCADE',
            'set null' => 'SET NULL',
            default => 'RESTRICT',
        };

        $existingConstraint = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->value('CONSTRAINT_NAME');

        if ($existingConstraint) {
            $existingRule = DB::table('information_schema.REFERENTIAL_CONSTRAINTS')
                ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', $table)
                ->where('CONSTRAINT_NAME', $existingConstraint)
                ->value('DELETE_RULE');

            if (strtoupper((string) $existingRule) === $onDelete) {
                return;
            }

            DB::statement(sprintf(
                'ALTER TABLE `%s` DROP FOREIGN KEY `%s`',
                str_replace('`', '``', $table),
                str_replace('`', '``', $existingConstraint)
            ));
        }

        $constraint = substr($table . '_' . $column . '_foreign', 0, 64);

        DB::statement(sprintf(
            'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`id`) ON DELETE %s',
            str_replace('`', '``', $table),
            str_replace('`', '``', $constraint),
            str_replace('`', '``', $column),
            str_replace('`', '``', $referencedTable),
            $onDelete
        ));
    }
};
