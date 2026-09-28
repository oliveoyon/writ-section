<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourtCase;
use App\Models\FileMovement;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $periodDays = (int) request()->integer('period', 30);
        if (! in_array($periodDays, [7, 30, 90], true)) {
            $periodDays = 30;
        }

        $caseTotals = CourtCase::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status IN ('draft', 'resubmitted', 'returned_to_lawyer') THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN current_section = 'Record Room' OR status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->first();

        $totalCases = (int) ($caseTotals->total ?? 0);
        $pendingCount = (int) ($caseTotals->pending ?? 0);
        $completedCount = (int) ($caseTotals->completed ?? 0);

        $inProgressCount = max($totalCases - $pendingCount - $completedCount, 0);

        $start = now()->startOfMonth()->subMonths(11);
        $monthExpression = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";
        $monthlyRows = CourtCase::query()
            ->selectRaw("{$monthExpression} as month_key, COUNT(*) as total")
            ->where('created_at', '>=', $start)
            ->groupByRaw($monthExpression)
            ->orderByRaw($monthExpression)
            ->get()
            ->keyBy('month_key');

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
        $todayMovementTotals = FileMovement::query()
            ->whereBetween('received_at', [$todayStart, $todayEnd])
            ->selectRaw("SUM(CASE WHEN movement_type = 'receive' THEN 1 ELSE 0 END) as received")
            ->selectRaw("SUM(CASE WHEN movement_type = 'reject' THEN 1 ELSE 0 END) as rejected")
            ->selectRaw("SUM(CASE WHEN movement_type = 'dispatch_to_court' THEN 1 ELSE 0 END) as court_dispatch")
            ->selectRaw("SUM(CASE WHEN movement_type = 'returned_from_court_handover' THEN 1 ELSE 0 END) as court_return")
            ->selectRaw('SUM(CASE WHEN is_override = 1 THEN 1 ELSE 0 END) as overridden')
            ->first();

        $todayReceived = (int) ($todayMovementTotals->received ?? 0);
        $todayRejected = (int) ($todayMovementTotals->rejected ?? 0);
        $todayCourtDispatch = (int) ($todayMovementTotals->court_dispatch ?? 0);
        $todayCourtReturn = (int) ($todayMovementTotals->court_return ?? 0);
        $todayOverride = (int) ($todayMovementTotals->overridden ?? 0);
        $todayCasesCount = CourtCase::query()
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->count();

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
        $periodMovementTotals = FileMovement::query()
            ->whereBetween('received_at', [$rangeStart, $rangeEnd])
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN movement_type = 'receive' THEN 1 ELSE 0 END) as received")
            ->selectRaw("SUM(CASE WHEN movement_type = 'reject' THEN 1 ELSE 0 END) as rejected")
            ->selectRaw("SUM(CASE WHEN movement_type = 'dispatch_to_court' THEN 1 ELSE 0 END) as court_dispatch")
            ->selectRaw("SUM(CASE WHEN movement_type = 'returned_from_court_handover' THEN 1 ELSE 0 END) as court_return")
            ->selectRaw("SUM(CASE WHEN movement_type = 'override_receive' THEN 1 ELSE 0 END) as overridden")
            ->first();

        $totalPeriodMovements = (int) ($periodMovementTotals->total ?? 0);
        $periodReceive = (int) ($periodMovementTotals->received ?? 0);
        $periodReject = (int) ($periodMovementTotals->rejected ?? 0);
        $periodCourtDispatch = (int) ($periodMovementTotals->court_dispatch ?? 0);
        $periodCourtReturn = (int) ($periodMovementTotals->court_return ?? 0);
        $periodOverride = (int) ($periodMovementTotals->overridden ?? 0);

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
