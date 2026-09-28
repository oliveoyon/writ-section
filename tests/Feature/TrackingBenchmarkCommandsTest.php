<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TrackingBenchmarkCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_benchmark_data_can_be_generated_probed_and_completely_removed(): void
    {
        $department = Department::create([
            'name' => 'Typing Section',
            'display_name' => 'Typing Section',
        ]);
        User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
        ]);

        $this->artisan('tracking:benchmark-generate', [
            '--cases' => 100,
            '--movements' => 2,
            '--chunk' => 100,
            '--with-parties' => true,
        ])->assertSuccessful();

        $this->assertDatabaseCount('cases', 100);
        $this->assertDatabaseCount('file_movements', 200);
        $this->assertDatabaseCount('case_petitioners', 100);
        $this->assertDatabaseCount('case_respondents', 100);
        $this->assertSame(100, (int) DB::table('cases')->distinct()->count('permanent_barcode'));
        $this->assertDatabaseHas('cases', ['entry_source' => 'benchmark']);

        $exitCode = Artisan::call('tracking:benchmark-probe', [
            '--iterations' => 1,
            '--days' => 3650,
            '--json' => true,
        ]);
        $this->assertSame(0, $exitCode);
        $output = Artisan::output();
        $this->assertStringContainsString('Exact barcode lookup', $output);
        $this->assertStringContainsString('Section movement report', $output);

        $this->artisan('tracking:benchmark-clean', ['--chunk' => 100])
            ->assertSuccessful();

        $this->assertDatabaseCount('cases', 0);
        $this->assertDatabaseCount('file_movements', 0);
        $this->assertDatabaseCount('case_petitioners', 0);
        $this->assertDatabaseCount('case_respondents', 0);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('departments', 1);
    }
}
