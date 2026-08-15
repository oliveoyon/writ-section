<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_petitioners', function (Blueprint $table) {
            if (!Schema::hasColumn('case_petitioners', 'designation')) {
                $table->string('designation')->nullable()->after('represented_by');
            }

            if (!Schema::hasColumn('case_petitioners', 'address')) {
                $table->string('address')->nullable()->after('designation');
            }
        });

        Schema::table('case_respondents', function (Blueprint $table) {
            if (!Schema::hasColumn('case_respondents', 'name_or_organization')) {
                $table->string('name_or_organization')->nullable()->after('case_id');
            }

            if (!Schema::hasColumn('case_respondents', 'represented_by')) {
                $table->string('represented_by')->nullable()->after('name_or_organization');
            }

        });

        if (Schema::hasColumn('case_respondents', 'name')) {
            DB::table('case_respondents')
                ->whereNull('name_or_organization')
                ->update(['name_or_organization' => DB::raw('`name`')]);
        }

        Schema::table('case_respondents', function (Blueprint $table) {
            if (Schema::hasColumn('case_respondents', 'name')) {
                $table->dropColumn('name');
            }

            if (Schema::hasColumn('case_respondents', 'organization')) {
                $table->dropColumn('organization');
            }
        });
    }

    public function down(): void
    {
        Schema::table('case_respondents', function (Blueprint $table) {
            if (!Schema::hasColumn('case_respondents', 'name')) {
                $table->string('name')->nullable()->after('case_id');
            }

            if (!Schema::hasColumn('case_respondents', 'organization')) {
                $table->string('organization')->nullable()->after('designation');
            }
        });

        if (Schema::hasColumn('case_respondents', 'name_or_organization')) {
            DB::table('case_respondents')
                ->whereNull('name')
                ->update(['name' => DB::raw('`name_or_organization`')]);
        }

        Schema::table('case_respondents', function (Blueprint $table) {
            if (Schema::hasColumn('case_respondents', 'name_or_organization')) {
                $table->dropColumn('name_or_organization');
            }

            if (Schema::hasColumn('case_respondents', 'represented_by')) {
                $table->dropColumn('represented_by');
            }

        });

        Schema::table('case_petitioners', function (Blueprint $table) {
            if (Schema::hasColumn('case_petitioners', 'designation')) {
                $table->dropColumn('designation');
            }

            if (Schema::hasColumn('case_petitioners', 'address')) {
                $table->dropColumn('address');
            }
        });
    }
};
