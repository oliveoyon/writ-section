<?php

namespace Tests\Feature;

use App\Services\RtftsCaseReference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RtftsCaseReferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_barcode_is_formatted_as_human_reference(): void
    {
        $this->assertSame(
            'WRPET 13120/2025',
            RtftsCaseReference::humanReferenceFromBarcode('132025013120')
        );

        $this->assertSame('132025013120', RtftsCaseReference::barcodeFromSearch('WRPET 13120/2025'));
        $this->assertSame('132025013120', RtftsCaseReference::barcodeFromSearch('WRITPET 13120/2025'));
        $this->assertSame('132025013120', RtftsCaseReference::barcodeFromSearch('13120/2025'));
        $this->assertSame('132025013120', RtftsCaseReference::barcodeFromSearch('132025013120'));
    }

    public function test_yearly_serials_are_zero_padded_and_incremented(): void
    {
        $service = app(RtftsCaseReference::class);

        $first = $service->issue(2025);
        $second = $service->issue(2025);
        $nextYear = $service->issue(2026);

        $this->assertSame('132025000001', $first['barcode']);
        $this->assertSame('WRPET 1/2025', $first['reference']);
        $this->assertSame('132025000002', $second['barcode']);
        $this->assertSame('132026000001', $nextYear['barcode']);
    }
}
