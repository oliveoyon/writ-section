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
            'final_case_number' => 'WR-2026-000001',
            'final_case_year' => '2026',
            'permanent_barcode' => 'WRIT-2026-00000001',
            'permanent_barcode_generated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.tracking.filing.print-index', [
                'permanent_barcode' => $case->permanent_barcode,
            ]))
            ->assertOk()
            ->assertSee('Print Barcode')
            ->assertSee('width_mm=25&amp;height_mm=50&amp;orientation=counter', false)
            ->assertDontSee('Print via PDF')
            ->assertDontSee('GS 2406T File');

        $this->actingAs($user)
            ->get(route('admin.tracking.filing.print-label-pdf', [
                'case' => $case,
                'width_mm' => 25,
                'height_mm' => 50,
                'orientation' => 'counter',
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertSee('%PDF-1.4', false);
    }
}
