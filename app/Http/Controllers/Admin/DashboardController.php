<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourtCase;
use App\Models\FileMovement;

class DashboardController extends Controller
{
    public function index()
    {
        $periodDays = (int) request()->integer('period', 30);
        if (!in_array($periodDays, [7, 30, 90], true)) {
            $periodDays = 30;
        }

        $totalCases = CourtCase::count();

        $pendingCount = CourtCase::query()
            ->whereIn('status', ['draft', 'resubmitted', 'returned_to_lawyer'])
            ->count();

        $completedCount = CourtCase::query()
            ->where(function ($q) {
                $q->where('current_section', 'Record Room')
                    ->orWhere('status', 'completed');
            })
            ->count();

        $inProgressCount = max($totalCases - $pendingCount - $completedCount, 0);

        $start = now()->startOfMonth()->subMonths(11);
        $monthlyRows = CourtCase::query()
            ->selectRaw('YEAR(created_at) as y, MONTH(created_at) as m, COUNT(*) as total')
            ->where('created_at', '>=', $start)
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get()
            ->keyBy(fn ($r) => sprintf('%04d-%02d', (int) $r->y, (int) $r->m));

        $monthlyLabels = [];
        $monthlyCounts = [];
        for ($i = 0; $i < 12; $i++) {
            $month = (clone $start)->addMonths($i);
            $key = $month->format('Y-m');
            $monthlyLabels[] = $month->format('M Y');
            $monthlyCounts[] = (int) ($monthlyRows[$key]->total ?? 0);
        }

        $sectionCounts = CourtCase::query()
            ->selectRaw('COALESCE(NULLIF(current_section, \'\'), \'Unassigned\') as section_name, COUNT(*) as total')
            ->groupBy('section_name')
            ->orderByDesc('total')
            ->get();

        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $todayMovements = FileMovement::query()->whereBetween('received_at', [$todayStart, $todayEnd]);

        $todayReceived = (clone $todayMovements)->where('movement_type', 'receive')->count();
        $todayRejected = (clone $todayMovements)->where('movement_type', 'reject')->count();
        $todayCourtDispatch = (clone $todayMovements)->where('movement_type', 'dispatch_to_court')->count();
        $todayCourtReturn = (clone $todayMovements)->where('movement_type', 'returned_from_court_handover')->count();
        $todayOverride = (clone $todayMovements)->where('is_override', true)->count();
        $todayCasesCount = CourtCase::query()->whereDate('created_at', now()->toDateString())->count();

        $pendingTempCount = CourtCase::query()
            ->whereNotNull('temporary_barcode')
            ->whereNull('permanent_barcode')
            ->count();

        $inCourtCount = CourtCase::query()
            ->where('current_section', 'Court')
            ->count();

        $returnedToLawyerCount = CourtCase::query()
            ->where('status', 'returned_to_lawyer')
            ->count();

        $activeCasesQuery = CourtCase::query()
            ->where(function ($q) {
                $q->whereNull('current_section')
                    ->orWhere('current_section', '<>', 'Record Room');
            })
            ->where('status', '<>', 'completed');

        $overdueCount = (clone $activeCasesQuery)
            ->whereDate('created_at', '<=', now()->subDays(15)->toDateString())
            ->count();

        $recentCases = CourtCase::query()
            ->with(['lawyer', 'petitioners'])
            ->latest('id')
            ->limit(10)
            ->get();

        $recentMovements = FileMovement::query()
            ->with([
                'courtCase:id,final_case_number,permanent_barcode,temporary_barcode',
                'receivedBy:id,name',
            ])
            ->latest('received_at')
            ->latest('id')
            ->limit(12)
            ->get();

        $rangeStart = now()->subDays($periodDays - 1)->startOfDay();
        $rangeEnd = now()->endOfDay();
        $periodMovements = FileMovement::query()
            ->whereBetween('received_at', [$rangeStart, $rangeEnd]);

        $totalPeriodMovements = (clone $periodMovements)->count();
        $periodReceive = (clone $periodMovements)->where('movement_type', 'receive')->count();
        $periodReject = (clone $periodMovements)->where('movement_type', 'reject')->count();
        $periodCourtDispatch = (clone $periodMovements)->where('movement_type', 'dispatch_to_court')->count();
        $periodCourtReturn = (clone $periodMovements)->where('movement_type', 'returned_from_court_handover')->count();
        $periodOverride = (clone $periodMovements)->where('movement_type', 'override_receive')->count();

        $last7Start = now()->subDays(6)->startOfDay();
        $rowsByDate = FileMovement::query()
            ->selectRaw('DATE(received_at) as d')
            ->selectRaw("SUM(CASE WHEN movement_type = 'receive' THEN 1 ELSE 0 END) as receive_total")
            ->selectRaw("SUM(CASE WHEN movement_type = 'reject' THEN 1 ELSE 0 END) as reject_total")
            ->selectRaw("SUM(CASE WHEN movement_type = 'dispatch_to_court' THEN 1 ELSE 0 END) as court_dispatch_total")
            ->selectRaw("SUM(CASE WHEN movement_type = 'returned_from_court_handover' THEN 1 ELSE 0 END) as court_return_total")
            ->whereBetween('received_at', [$last7Start, now()->endOfDay()])
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $last7Labels = [];
        $last7Receive = [];
        $last7Reject = [];
        $last7CourtDispatch = [];
        $last7CourtReturn = [];
        for ($i = 0; $i < 7; $i++) {
            $day = now()->subDays(6 - $i)->toDateString();
            $last7Labels[] = now()->subDays(6 - $i)->format('d M');
            $last7Receive[] = (int) ($rowsByDate[$day]->receive_total ?? 0);
            $last7Reject[] = (int) ($rowsByDate[$day]->reject_total ?? 0);
            $last7CourtDispatch[] = (int) ($rowsByDate[$day]->court_dispatch_total ?? 0);
            $last7CourtReturn[] = (int) ($rowsByDate[$day]->court_return_total ?? 0);
        }

        $topSectionBacklog = CourtCase::query()
            ->selectRaw('COALESCE(NULLIF(current_section, \'\'), \'Unassigned\') as section_name, COUNT(*) as total')
            ->where('status', '<>', 'completed')
            ->groupBy('section_name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $topHolders = CourtCase::query()
            ->leftJoin('users', 'cases.current_holder_user_id', '=', 'users.id')
            ->selectRaw('COALESCE(users.name, \'Unassigned\') as holder_name, COUNT(cases.id) as total')
            ->groupBy('holder_name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $oldestActiveCases = CourtCase::query()
            ->with('petitioners')
            ->where('status', '<>', 'completed')
            ->where(function ($q) {
                $q->whereNull('current_section')
                    ->orWhere('current_section', '<>', 'Record Room');
            })
            ->oldest('created_at')
            ->limit(10)
            ->get();

        return view('admin.home', [
            'periodDays' => $periodDays,
            'totalCases' => $totalCases,
            'pendingCount' => $pendingCount,
            'inProgressCount' => $inProgressCount,
            'completedCount' => $completedCount,
            'overdueCount' => $overdueCount,
            'monthlyLabels' => $monthlyLabels,
            'monthlyCounts' => $monthlyCounts,
            'sectionLabels' => $sectionCounts->pluck('section_name')->values(),
            'sectionValues' => $sectionCounts->pluck('total')->map(fn ($v) => (int) $v)->values(),
            'todayReceived' => $todayReceived,
            'todayRejected' => $todayRejected,
            'todayCourtDispatch' => $todayCourtDispatch,
            'todayCourtReturn' => $todayCourtReturn,
            'todayOverride' => $todayOverride,
            'todayCasesCount' => $todayCasesCount,
            'pendingTempCount' => $pendingTempCount,
            'inCourtCount' => $inCourtCount,
            'returnedToLawyerCount' => $returnedToLawyerCount,
            'totalPeriodMovements' => $totalPeriodMovements,
            'periodReceive' => $periodReceive,
            'periodReject' => $periodReject,
            'periodCourtDispatch' => $periodCourtDispatch,
            'periodCourtReturn' => $periodCourtReturn,
            'periodOverride' => $periodOverride,
            'last7Labels' => $last7Labels,
            'last7Receive' => $last7Receive,
            'last7Reject' => $last7Reject,
            'last7CourtDispatch' => $last7CourtDispatch,
            'last7CourtReturn' => $last7CourtReturn,
            'topSectionBacklog' => $topSectionBacklog,
            'topHolders' => $topHolders,
            'recentCases' => $recentCases,
            'recentMovements' => $recentMovements,
            'oldestActiveCases' => $oldestActiveCases,
        ]);
    }
}
