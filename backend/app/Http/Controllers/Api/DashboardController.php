<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionData;
use App\Models\ResearchProject;
use App\Models\SustainabilityData;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function publicOverview(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth();
        $dateTo = $request->date_to ?? now()->endOfMonth();

        $totalVisitors = ProductionData::where('status', 'approved')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->sum('visitor_count');

        $activeProjects = ResearchProject::where('status', 'active')->count();
        $activeTenants = Tenant::where('status', 'active')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_visitors_month' => $totalVisitors,
                    'total_visitors_year' => ProductionData::where('status', 'approved')
                        ->whereYear('date', now()->year)
                        ->sum('visitor_count'),
                    'active_research_projects' => $activeProjects,
                    'active_tenants' => $activeTenants,
                ],
                'recent_activities' => []
            ]
        ]);
    }

    public function publicProduction(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth();
        $dateTo = $request->date_to ?? now()->endOfMonth();

        $query = ProductionData::where('status', 'approved')
            ->whereBetween('date', [$dateFrom, $dateTo]);

        $totalVisitors = (clone $query)->sum('visitor_count');
        $byCategory = (clone $query)->groupBy('visitor_category')
            ->select('visitor_category', DB::raw('sum(visitor_count) as count'))
            ->get();

        $byTimeSlot = (clone $query)->groupBy('time_slot')
            ->select('time_slot', DB::raw('sum(visitor_count) as count'))
            ->get();

        $trend = (clone $query)->orderBy('date')
            ->select('date', DB::raw('sum(visitor_count) as count'))
            ->groupBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_visitors' => $totalVisitors,
                'average_daily' => $totalVisitors / now()->diffInDays($dateFrom),
                'by_category' => $byCategory->pluck('count', 'visitor_category'),
                'by_time_slot' => $byTimeSlot->pluck('count', 'time_slot'),
                'trend' => $trend
            ]
        ]);
    }

    public function publicResearch(Request $request)
    {
        $projects = ResearchProject::withCount('outputs')
            ->where('status', '!=', 'completed')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_projects' => ResearchProject::count(),
                'active_projects' => ResearchProject::where('status', 'active')->count(),
                'completed_projects' => ResearchProject::where('status', 'completed')->count(),
                'by_category' => ResearchProject::select('category', DB::raw('count(*) as count'))
                    ->groupBy('category')
                    ->pluck('count', 'category'),
                'projects' => $projects
            ]
        ]);
    }

    public function publicSustainability(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth();
        $dateTo = $request->date_to ?? now()->endOfMonth();

        $data = SustainabilityData::where('status', 'approved')
            ->whereBetween('record_date', [$dateFrom, $dateTo])
            ->get();

        $byCategory = $data->groupBy('category')
            ->map(function ($items) {
                return [
                    'score' => $items->average(function ($item) {
                        return $item->target_value ? ($item->value / $item->target_value) * 100 : 0;
                    }),
                    'items' => $items->toArray()
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'overall_score' => $byCategory->average('score'),
                'by_category' => $byCategory,
            ]
        ]);
    }

    public function publicExecutive(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'kpis' => [
                    'total_visitors_ytd' => ProductionData::where('status', 'approved')
                        ->whereYear('date', now()->year)
                        ->sum('visitor_count'),
                    'active_projects' => ResearchProject::where('status', 'active')->count(),
                    'active_tenants' => Tenant::where('status', 'active')->count(),
                ],
                'alerts' => []
            ]
        ]);
    }

    public function overview(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth();
        $dateTo = $request->date_to ?? now()->endOfMonth();

        $totalVisitors = ProductionData::where('status', 'approved')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->sum('visitor_count');

        $activeProjects = ResearchProject::where('status', 'active')->count();
        $activeTenants = Tenant::where('status', 'active')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_visitors_month' => $totalVisitors,
                    'total_visitors_year' => ProductionData::where('status', 'approved')
                        ->whereYear('date', now()->year)
                        ->sum('visitor_count'),
                    'active_research_projects' => $activeProjects,
                    'active_tenants' => $activeTenants,
                ],
                'recent_activities' => []
            ]
        ]);
    }

    public function production(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth();
        $dateTo = $request->date_to ?? now()->endOfMonth();

        $query = ProductionData::where('status', 'approved')
            ->whereBetween('date', [$dateFrom, $dateTo]);

        $totalVisitors = (clone $query)->sum('visitor_count');
        $byCategory = (clone $query)->groupBy('visitor_category')
            ->select('visitor_category', DB::raw('sum(visitor_count) as count'))
            ->get();

        $byTimeSlot = (clone $query)->groupBy('time_slot')
            ->select('time_slot', DB::raw('sum(visitor_count) as count'))
            ->get();

        $trend = (clone $query)->orderBy('date')
            ->select('date', DB::raw('sum(visitor_count) as count'))
            ->groupBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_visitors' => $totalVisitors,
                'average_daily' => $totalVisitors / now()->diffInDays($dateFrom),
                'by_category' => $byCategory->pluck('count', 'visitor_category'),
                'by_time_slot' => $byTimeSlot->pluck('count', 'time_slot'),
                'trend' => $trend
            ]
        ]);
    }

    public function research(Request $request)
    {
        $projects = ResearchProject::withCount('outputs')
            ->where('status', '!=', 'completed')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_projects' => ResearchProject::count(),
                'active_projects' => ResearchProject::where('status', 'active')->count(),
                'completed_projects' => ResearchProject::where('status', 'completed')->count(),
                'by_category' => ResearchProject::select('category', DB::raw('count(*) as count'))
                    ->groupBy('category')
                    ->pluck('count', 'category'),
                'projects' => $projects
            ]
        ]);
    }

    public function sustainability(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth();
        $dateTo = $request->date_to ?? now()->endOfMonth();

        $data = SustainabilityData::where('status', 'approved')
            ->whereBetween('record_date', [$dateFrom, $dateTo])
            ->get();

        $byCategory = $data->groupBy('category')
            ->map(function ($items) {
                return [
                    'score' => $items->average(function ($item) {
                        return $item->target_value ? ($item->value / $item->target_value) * 100 : 0;
                    }),
                    'items' => $items->toArray()
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'overall_score' => $byCategory->average('score'),
                'by_category' => $byCategory,
            ]
        ]);
    }

    public function executive(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'kpis' => [
                    'total_visitors_ytd' => ProductionData::where('status', 'approved')
                        ->whereYear('date', now()->year)
                        ->sum('visitor_count'),
                    'active_projects' => ResearchProject::where('status', 'active')->count(),
                    'active_tenants' => Tenant::where('status', 'active')->count(),
                ],
                'alerts' => []
            ]
        ]);
    }
}
