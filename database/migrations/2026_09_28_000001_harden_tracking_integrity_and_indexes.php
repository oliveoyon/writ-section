<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->enforceUserDepartmentReference();

        Schema::table('cases', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'cases_status_created_at_index');
        });

        Schema::table('court_dispatch_batches', function (Blueprint $table) {
            $table->index(['type', 'dispatched_at'], 'court_batches_type_dispatched_index');
            $table->index(['type', 'returned_at'], 'court_batches_type_returned_index');
            $table->index(['created_by_user_id', 'type', 'id'], 'court_batches_creator_type_id_index');
        });

        Schema::table('court_dispatch_batch_items', function (Blueprint $table) {
            $table->index(['case_id', 'batch_id'], 'court_batch_items_case_batch_index');
        });
    }

    public function down(): void
    {
        Schema::table('court_dispatch_batch_items', function (Blueprint $table) {
            $table->dropIndex('court_batch_items_case_batch_index');
        });

        Schema::table('court_dispatch_batches', function (Blueprint $table) {
            $table->dropIndex('court_batches_creator_type_id_index');
            $table->dropIndex('court_batches_type_returned_index');
            $table->dropIndex('court_batches_type_dispatched_index');
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->dropIndex('cases_status_created_at_index');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `users` DROP FOREIGN KEY `users_department_foreign`');
            DB::statement('ALTER TABLE `users` MODIFY `department` VARCHAR(255) NULL');
        }
    }

    private function enforceUserDepartmentReference(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $invalidCount = DB::table('users as users')
            ->leftJoin('departments', DB::raw('CAST(users.department AS UNSIGNED)'), '=', 'departments.id')
            ->whereNotNull('users.department')
            ->where(function ($query) {
                $query->whereRaw("users.department NOT REGEXP '^[0-9]+$'")
                    ->orWhereNull('departments.id');
            })
            ->count();

        if ($invalidCount > 0) {
            throw new RuntimeException(
                "Cannot enforce users.department: {$invalidCount} user record(s) do not reference an existing department."
            );
        }

        DB::statement('ALTER TABLE `users` MODIFY `department` BIGINT UNSIGNED NULL');

        $foreignExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'users')
            ->where('COLUMN_NAME', 'department')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();

        if (! $foreignExists) {
            DB::statement(
                'ALTER TABLE `users` ADD CONSTRAINT `users_department_foreign` '
                .'FOREIGN KEY (`department`) REFERENCES `departments` (`id`) ON DELETE RESTRICT'
            );
        }
    }
};
