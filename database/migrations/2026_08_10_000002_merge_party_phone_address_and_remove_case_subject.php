<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('case_petitioners', 'phone') && Schema::hasColumn('case_petitioners', 'address')) {
            DB::table('case_petitioners')->orderBy('id')->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $merged = collect([$row->phone ?? null, $row->address ?? null])
                        ->map(fn ($value) => trim((string) $value))
                        ->filter()
                        ->unique()
                        ->join("\n");

                    DB::table('case_petitioners')
                        ->where('id', $row->id)
                        ->update(['address' => $merged !== '' ? $merged : null]);
                }
            });
        }

        if (Schema::hasColumn('case_respondents', 'phone') && Schema::hasColumn('case_respondents', 'address')) {
            DB::table('case_respondents')->orderBy('id')->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $merged = collect([$row->phone ?? null, $row->address ?? null])
                        ->map(fn ($value) => trim((string) $value))
                        ->filter()
                        ->unique()
                        ->join("\n");

                    DB::table('case_respondents')
                        ->where('id', $row->id)
                        ->update(['address' => $merged !== '' ? $merged : null]);
                }
            });
        }

        Schema::table('case_petitioners', function (Blueprint $table) {
            if (Schema::hasColumn('case_petitioners', 'phone')) {
                $table->dropColumn('phone');
            }
        });

        Schema::table('case_respondents', function (Blueprint $table) {
            if (Schema::hasColumn('case_respondents', 'phone')) {
                $table->dropColumn('phone');
            }
        });

        Schema::table('cases', function (Blueprint $table) {
            if (Schema::hasColumn('cases', 'subject')) {
                $table->dropColumn('subject');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            if (!Schema::hasColumn('cases', 'subject')) {
                $table->string('subject')->nullable()->after('case_type');
            }
        });

        Schema::table('case_petitioners', function (Blueprint $table) {
            if (!Schema::hasColumn('case_petitioners', 'phone')) {
                $table->string('phone')->nullable()->after('designation');
            }
        });

        Schema::table('case_respondents', function (Blueprint $table) {
            if (!Schema::hasColumn('case_respondents', 'phone')) {
                $table->string('phone')->nullable()->after('designation');
            }
        });
    }
};
