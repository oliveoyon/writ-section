<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_registration_sequences', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->char('year', 4)->primary();
            $table->unsignedInteger('last_serial')->default(0);
            $table->timestamps();
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->unsignedInteger('registration_serial')->nullable()->after('final_case_year');
            $table->unique(
                ['final_case_year', 'registration_serial'],
                'cases_registration_year_serial_unique'
            );
        });

        $highestSerialByYear = [];

        DB::table('cases')
            ->select(['id', 'permanent_barcode'])
            ->whereNotNull('permanent_barcode')
            ->orderBy('id')
            ->each(function ($case) use (&$highestSerialByYear) {
                if (!preg_match('/^13(\d{4})(\d{6})$/', (string) $case->permanent_barcode, $matches)) {
                    return;
                }

                $year = $matches[1];
                $serial = (int) $matches[2];
                if ($serial < 1) {
                    return;
                }

                DB::table('cases')->where('id', $case->id)->update([
                    'final_case_year' => $year,
                    'registration_serial' => $serial,
                    'final_case_number' => 'WRPET ' . $serial . '/' . $year,
                ]);

                $highestSerialByYear[$year] = max($highestSerialByYear[$year] ?? 0, $serial);
            });

        foreach ($highestSerialByYear as $year => $serial) {
            DB::table('case_registration_sequences')->updateOrInsert(
                ['year' => $year],
                [
                    'last_serial' => $serial,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropUnique('cases_registration_year_serial_unique');
            $table->dropColumn('registration_serial');
        });

        Schema::dropIfExists('case_registration_sequences');
    }
};
