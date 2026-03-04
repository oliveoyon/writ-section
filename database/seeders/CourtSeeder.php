<?php

namespace Database\Seeders;

use App\Models\Court;
use Illuminate\Database\Seeder;

class CourtSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name_en' => 'Court 1', 'name_bn' => '????? ?', 'code' => 'CRT-01'],
            ['name_en' => 'Court 2', 'name_bn' => '????? ?', 'code' => 'CRT-02'],
            ['name_en' => 'Court 3', 'name_bn' => '????? ?', 'code' => 'CRT-03'],
        ];

        foreach ($rows as $row) {
            Court::updateOrCreate(
                ['code' => $row['code']],
                [
                    'name_en' => $row['name_en'],
                    'name_bn' => $row['name_bn'],
                    'is_active' => true,
                ]
            );
        }
    }
}
