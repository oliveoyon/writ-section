<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('departments', 'display_name')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->string('display_name')->nullable()->after('name');
            });

            DB::table('departments')->update(['display_name' => DB::raw('name')]);
        }

        $officeAssistantExists = DB::table('departments')->where('name', 'Office Assistant')->exists();
        if (!$officeAssistantExists) {
            DB::table('departments')
                ->where('name', 'Dealing Assistant')
                ->update(['name' => 'Office Assistant']);
        }

        if (Schema::hasTable('roles') && !Schema::hasColumn('roles', 'display_name')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('display_name')->nullable()->after('name');
            });

            DB::table('roles')->update(['display_name' => DB::raw('name')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('departments', 'display_name')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropColumn('display_name');
            });
        }

        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'display_name')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('display_name');
            });
        }
    }
};
