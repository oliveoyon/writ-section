<?php

namespace Tests\Feature;

use App\Models\CourtCase;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilingPrintAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_filing_staff_can_prepare_barcode_pdf_print(): void
    {
        $department = Department::create(['name' => 'Typing Section']);
        $user = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
            'is_active' => true,
        ]);

        $case = CourtCase::create([
            'entry_source' => 'filing',
            'case_type' => 'Writ',
            'subject' => 'Test label',
            'status' => 'filed',
            'final_case_number' => 'WRPET 1/2026',
            'final_case_year' => '2026',
            'registration_serial' => 1,
            'permanent_barcode' => '132026000001',
            'permanent_barcode_generated_at' => now(),
        ]);

        foreach ([$case->permanent_barcode, 'WRPET 1/2026', 'WRITPET 1/2026', '1/2026'] as $search) {
            $this->actingAs($user)
                ->get(route('admin.tracking.filing.print-index', [
                    'permanent_barcode' => $search,
                ]))
                ->assertOk()
                ->assertSee('Print Barcode')
                ->assertSee('WRPET 1/2026')
                ->assertSee('132026000001');
        }

        $this->actingAs($user)
            ->get(route('admin.tracking.filing.print-label', [
                'case' => $case,
                'width_mm' => 50,
                'height_mm' => 25,
            ]))
            ->assertOk()
            ->assertSee('WRPET 1/2026')
            ->assertSee('132026000001');

        $this->actingAs($user)
            ->get(route('admin.tracking.filing.print-label-pdf', [
                'case' => $case,
                'width_mm' => 25,
                'height_mm' => 50,
                'orientation' => 'counter',
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertSee('%PDF-1.4', false)
            ->assertSee('WRPET 1/2026', false)
            ->assertSee('132026000001', false);
    }
}
