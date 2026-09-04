<?php

namespace Database\Seeders;

use App\Models\CasePetitioner;
use App\Models\CaseRespondent;
use App\Models\CourtCase;
use App\Models\FileMovement;
use App\Models\User;
use App\Services\RtftsCaseReference;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoCaseSeeder extends Seeder
{
    private array $caseRows = [
        ['year' => '2026', 'type' => 'Service Matter', 'petitioner' => 'Md. Abdul Karim', 'respondent' => 'Government of Bangladesh', 'current' => 'Dealing Assistant', 'days' => 4],
        ['year' => '2026', 'type' => 'Constitutional Matter', 'petitioner' => 'Rahima Begum', 'respondent' => 'Ministry of Law, Justice and Parliamentary Affairs', 'current' => 'Affidavit Section', 'days' => 6],
        ['year' => '2026', 'type' => 'Detention Matter', 'petitioner' => 'Md. Hasan Ali', 'respondent' => 'Inspector General of Police', 'current' => 'Compare Section', 'days' => 8],
        ['year' => '2026', 'type' => 'VAT and Custom Matter', 'petitioner' => 'M/S Delta Trading', 'respondent' => 'National Board of Revenue', 'current' => 'Ready Table', 'days' => 10],
        ['year' => '2026', 'type' => 'Income Tax Matter', 'petitioner' => 'M/S Eastern Holdings Ltd.', 'respondent' => 'Commissioner of Taxes', 'current' => 'Record Room', 'days' => 12],
        ['year' => '2026', 'type' => 'PIL Matter', 'petitioner' => 'Human Rights Forum', 'respondent' => 'Secretary, Ministry of Health', 'current' => 'Dealing Assistant', 'days' => 15],
        ['year' => '2026', 'type' => 'RAJUK Matter', 'petitioner' => 'Md. Rezaul Islam', 'respondent' => 'RAJUK', 'current' => 'Put-Up Section', 'days' => 18],
        ['year' => '2026', 'type' => 'DUDUK Matter', 'petitioner' => 'Ayesha Siddika', 'respondent' => 'Anti-Corruption Commission', 'current' => 'Requisite Section', 'days' => 21],
        ['year' => '2026', 'type' => 'Lease Matter', 'petitioner' => 'M/S City Properties', 'respondent' => 'Deputy Commissioner, Dhaka', 'current' => 'Case Docket', 'days' => 24],
        ['year' => '2026', 'type' => 'Artha Rin Matter', 'petitioner' => 'Md. Borhan Uddin', 'respondent' => 'Sonali Bank PLC', 'current' => 'Scanning', 'days' => 27],
        ['year' => '2025', 'type' => 'Service Matter', 'petitioner' => 'Nasrin Akter', 'respondent' => 'Director General, Health Services', 'current' => 'Record Room', 'days' => 35],
        ['year' => '2025', 'type' => 'Constitutional Matter', 'petitioner' => 'Md. Shafiqur Rahman', 'respondent' => 'Election Commission Bangladesh', 'current' => 'Ready Table', 'days' => 42],
        ['year' => '2025', 'type' => 'Court of Settlement Matter', 'petitioner' => 'M/S Metro Builders', 'respondent' => 'Court of Settlement', 'current' => 'Dealing Assistant', 'days' => 49],
        ['year' => '2025', 'type' => 'Labour Court Matter', 'petitioner' => 'Sultana Parvin', 'respondent' => 'Labour Appellate Tribunal', 'current' => 'Affidavit Section', 'days' => 56],
        ['year' => '2025', 'type' => 'Others Matter', 'petitioner' => 'Md. Alamgir Hossain', 'respondent' => 'Bangladesh Road Transport Authority', 'current' => 'Process Service', 'days' => 63],
        ['year' => '2025', 'type' => 'Service Matter', 'petitioner' => 'Jannatul Ferdous', 'respondent' => 'Ministry of Education', 'current' => 'Typing Section', 'days' => 70],
        ['year' => '2025', 'type' => 'Income Tax Matter', 'petitioner' => 'M/S Northern Agro Ltd.', 'respondent' => 'Deputy Commissioner of Taxes', 'current' => 'Compare Section', 'days' => 77],
        ['year' => '2025', 'type' => 'PIL Matter', 'petitioner' => 'Public Interest Legal Aid Cell', 'respondent' => 'Secretary, Ministry of Environment', 'current' => 'Requisite Section', 'days' => 84],
        ['year' => '2025', 'type' => 'VAT and Custom Matter', 'petitioner' => 'M/S Ocean Freight Services', 'respondent' => 'Customs Bond Commissionerate', 'current' => 'Case Docket', 'days' => 91],
        ['year' => '2025', 'type' => 'Detention Matter', 'petitioner' => 'Md. Ibrahim Khalil', 'respondent' => 'Home Secretary', 'current' => 'Others', 'days' => 98],
    ];

    public function run(): void
    {
        $this->removeExistingDemoCases();

        DB::transaction(function () {
            foreach ($this->caseRows as $index => $row) {
                $filingAt = now()->subDays($row['days'])->setTime(10 + ($index % 4), 15);
                $registration = app(RtftsCaseReference::class)->issue($row['year']);
                $journey = $this->journeyFor($row['current']);
                $holder = $this->userForSection($row['current']) ?? $this->userForSection('Filing Section');

                $case = CourtCase::create([
                    'lawyer_id' => null,
                    'initiated_by_user_id' => $this->userForSection('Filing Section')?->id,
                    'entry_source' => 'demo',
                    'case_type' => $row['type'],
                    'description' => 'Demo case for RTFTS workflow testing.',
                    'status' => 'filed',
                    'permanent_barcode' => $registration['barcode'],
                    'permanent_barcode_generated_at' => $filingAt,
                    'section_verified_at' => $filingAt,
                    'section_verified_by' => $this->userForSection('Filing Section')?->id,
                    'final_case_number' => $registration['reference'],
                    'final_case_year' => $registration['year'],
                    'registration_serial' => $registration['serial'],
                    'current_section' => $row['current'],
                    'current_holder_user_id' => $holder?->id,
                    'current_holder_at' => $filingAt->copy()->addHours((count($journey) - 1) * 8),
                    'created_at' => $filingAt,
                    'updated_at' => $filingAt,
                ]);

                CasePetitioner::create([
                    'case_id' => $case->id,
                    'name_or_organization' => $row['petitioner'],
                    'represented_by' => null,
                    'designation' => null,
                    'address' => 'Demo address, Dhaka. Phone: 01XXXXXXXXX',
                ]);

                CaseRespondent::create([
                    'case_id' => $case->id,
                    'name_or_organization' => $row['respondent'],
                    'represented_by' => null,
                    'designation' => null,
                    'address' => 'Official address, Dhaka.',
                ]);

                $previousSection = null;
                foreach ($journey as $step => $section) {
                    $receivedAt = $filingAt->copy()->addHours($step * 8);
                    $receiver = $this->userForSection($section) ?? $holder;

                    FileMovement::create([
                        'case_id' => $case->id,
                        'barcode_scanned' => $registration['barcode'],
                        'from_section' => $previousSection,
                        'to_section' => $section,
                        'movement_type' => 'receive',
                        'received_by_user_id' => $receiver?->id,
                        'received_at' => $receivedAt,
                        'notes' => $previousSection
                            ? 'Demo movement from ' . $previousSection . ' to ' . $section . '.'
                            : 'Demo filing received at Filing Section.',
                        'created_at' => $receivedAt,
                        'updated_at' => $receivedAt,
                    ]);

                    $previousSection = $section;
                }
            }
        });
    }

    private function journeyFor(string $currentSection): array
    {
        $journey = ['Filing Section', 'Affidavit Section'];

        if (!in_array($currentSection, $journey, true)) {
            $journey[] = 'Dealing Assistant';
        }

        if (!in_array($currentSection, $journey, true)) {
            $journey[] = $currentSection;
        }

        return $journey;
    }

    private function userForSection(string $section): ?User
    {
        return User::query()
            ->where('user_type', 'staff')
            ->where('is_active', true)
            ->whereHas('departmentRelation', fn ($query) => $query->where('name', $section))
            ->orderBy('name')
            ->first();
    }

    private function removeExistingDemoCases(): void
    {
        $caseIds = CourtCase::where('entry_source', 'demo')->pluck('id');

        if ($caseIds->isEmpty()) {
            return;
        }

        FileMovement::whereIn('case_id', $caseIds)->delete();
        CasePetitioner::whereIn('case_id', $caseIds)->delete();
        CaseRespondent::whereIn('case_id', $caseIds)->delete();
        DB::table('court_dispatch_batch_items')->whereIn('case_id', $caseIds)->delete();
        CourtCase::whereIn('id', $caseIds)->delete();
    }
}
