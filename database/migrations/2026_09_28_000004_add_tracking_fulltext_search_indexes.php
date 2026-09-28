<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $indexes = [
        ['cases', 'cases_tracking_fulltext', ['case_type', 'description']],
        ['case_petitioners', 'case_petitioners_tracking_fulltext', ['name_or_organization', 'represented_by', 'designation', 'address']],
        ['case_respondents', 'case_respondents_tracking_fulltext', ['name_or_organization', 'represented_by', 'designation', 'address']],
        ['lawyers', 'lawyers_tracking_fulltext', ['full_name', 'bar_council_id', 'phone']],
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->indexes as [$table, $index, $columns]) {
            $columnSql = collect($columns)->map(fn ($column) => "`{$column}`")->implode(', ');
            DB::statement("ALTER TABLE `{$table}` ADD FULLTEXT INDEX `{$index}` ({$columnSql})");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach (array_reverse($this->indexes) as [$table, $index]) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
        }
    }
};
