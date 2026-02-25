<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('cases', 'lawyer_id')) {
            return;
        }

        DB::statement('ALTER TABLE cases MODIFY lawyer_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (!Schema::hasColumn('cases', 'lawyer_id')) {
            return;
        }

        DB::statement('ALTER TABLE cases MODIFY lawyer_id BIGINT UNSIGNED NOT NULL');
    }
};
