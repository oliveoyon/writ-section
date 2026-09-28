<?php

namespace Tests\Feature;

use App\Models\CasePetitioner;
use App\Models\Court;
use App\Models\CourtCase;
use App\Models\Department;
use App\Models\FileMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ProductionScaleSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_movement_register_is_paginated_at_one_hundred_rows(): void
    {
        [$user, $case] = $this->reportParticipant();
        $this->insertMovements($case, 101, 'Typing Section');

        $this->actingAs($user)
            ->get(route('admin.tracking.register-report'))
            ->assertOk()
            ->assertViewHas('movements', function ($movements): bool {
                return $movements instanceof LengthAwarePaginator
                    && $movements->perPage() === 100
                    && $movements->total() === 101
                    && $movements->count() === 100;
            });
    }

    public function test_pdf_register_rejects_more_than_five_thousand_rows(): void
    {
        [$user, $case] = $this->reportParticipant();
        $this->insertMovements($case, 5001, 'Typing Section');

        $this->actingAs($user)
            ->from(route('admin.tracking.register-report'))
            ->get(route('admin.tracking.register-report.pdf'))
            ->assertRedirect(route('admin.tracking.register-report'))
            ->assertSessionHasErrors('date_from');
    }

    public function test_court_dispatch_limits_batch_size_and_does_not_keep_an_empty_batch(): void
    {
        $department = Department::create([
            'name' => 'Office Assistant',
            'display_name' => 'Office Assistant',
        ]);
        $user = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
        ]);
        $court = Court::create([
            'name_en' => 'Test Court',
            'code' => 'TC',
            'is_active' => true,
        ]);

        $tooMany = collect(range(1, 201))
            ->map(fn (int $serial) => '132026'.str_pad((string) $serial, 6, '0', STR_PAD_LEFT))
            ->implode("\n");

        $this->actingAs($user)
            ->post(route('admin.tracking.court.dispatch.store'), [
                'court_id' => $court->id,
                'barcodes' => $tooMany,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('court_dispatch_batches', 0);

        $this->actingAs($user)
            ->post(route('admin.tracking.court.dispatch.store'), [
                'court_id' => $court->id,
                'barcodes' => 'INVALID-PRODUCT-BARCODE',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('court_dispatch_batches', 0);
    }

    public function test_case_reference_uses_the_exact_lookup_path_for_lookup_and_print_suggestions(): void
    {
        $department = Department::create([
            'name' => 'Assistant Registrar Office',
            'display_name' => 'Assistant Registrar Office',
        ]);
        $user = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
        ]);
        CourtCase::create([
            'status' => 'in_progress',
            'permanent_barcode' => '132026004582',
            'final_case_number' => 'WRPET 4582/2026',
            'final_case_year' => '2026',
            'registration_serial' => 4582,
            'current_section' => $department->name,
            'current_holder_user_id' => $user->id,
            'current_holder_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.tracking.lookup', ['q' => '4582/2026']))
            ->assertOk()
            ->assertSee('WRPET 4582/2026');

        $this->actingAs($user)
            ->getJson(route('admin.tracking.filing.print-suggest', ['q' => '4582/2026']))
            ->assertOk()
            ->assertJsonPath('items.0.title', 'WRPET 4582/2026');
    }

    public function test_party_name_remains_available_in_lookup_and_print_suggestions(): void
    {
        $department = Department::create([
            'name' => 'Assistant Registrar Office',
            'display_name' => 'Assistant Registrar Office',
        ]);
        $user = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
        ]);
        $case = CourtCase::create([
            'status' => 'in_progress',
            'permanent_barcode' => '132026004583',
            'final_case_number' => 'WRPET 4583/2026',
            'final_case_year' => '2026',
            'registration_serial' => 4583,
            'current_section' => $department->name,
        ]);
        CasePetitioner::create([
            'case_id' => $case->id,
            'name_or_organization' => 'Unique Constitutional Foundation',
        ]);

        $this->actingAs($user)
            ->get(route('admin.tracking.lookup', ['q' => 'Constitutional Foundation']))
            ->assertOk()
            ->assertSee('WRPET 4583/2026');

        $this->actingAs($user)
            ->getJson(route('admin.tracking.filing.print-suggest', ['q' => 'Constitutional Foundation']))
            ->assertOk()
            ->assertJsonPath('items.0.title', 'WRPET 4583/2026');
    }

    private function reportParticipant(): array
    {
        $department = Department::create([
            'name' => 'Typing Section',
            'display_name' => 'Typing Section',
        ]);
        $user = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
        ]);
        $case = CourtCase::create([
            'status' => 'in_progress',
            'permanent_barcode' => '132026000001',
            'final_case_number' => 'WRPET 1/2026',
            'final_case_year' => '2026',
            'registration_serial' => 1,
            'current_section' => $department->name,
            'current_holder_user_id' => $user->id,
            'current_holder_at' => now(),
        ]);

        return [$user, $case];
    }

    private function insertMovements(CourtCase $case, int $count, string $section): void
    {
        $now = now();

        collect(range(1, $count))->chunk(500)->each(function ($rows) use ($case, $section, $now) {
            FileMovement::insert($rows->map(fn (int $row): array => [
                'case_id' => $case->id,
                'barcode_scanned' => $case->permanent_barcode,
                'from_section' => 'Filing Section',
                'to_section' => $section,
                'movement_type' => 'receive',
                'received_at' => $now,
                'is_override' => false,
                'notes' => 'Scale row '.$row,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all());
        });
    }
}
