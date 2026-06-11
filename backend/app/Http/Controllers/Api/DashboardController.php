<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionData;
use App\Models\ResearchProject;
use App\Models\SustainabilityData;
use App\Models\Tenant;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function getExecutiveData()
    {
        $currentYear = now()->year;

        // 1. Hitung Green Score (Average Value / Target) tetep jalan asli
        $sustainabilityReadings = SustainabilityData::where('status', 'approved')
            ->whereNotNull('target_value')
            ->where('target_value', '>', 0)
            ->get();
            
        $greenScore = $sustainabilityReadings->count() > 0
            ? (int) round($sustainabilityReadings->avg(fn($r) => min(100, $r->value / $r->target_value * 100)))
            : 0;

        // 2. Trend Tahunan - MOCK DATA (Biar presentasi aman, ntar tinggal colok query asli)
        $trendTahunan = [];
        $dummyOutputTahunan = [18, 22, 28, 35, 52, 68];
        $dummyDampakTahunan = [12, 15, 20, 24, 38, 55];
        
        // FIX: Langsung kurangin aja dari angkanya, gausah di-clone wkwk
        $startYear = $currentYear - 5; 
        
        for ($i = 0; $i < 6; $i++) {
            $trendTahunan[] = [
                'year' => (string) ($startYear + $i),
                'output' => $dummyOutputTahunan[$i] ?? 0,
                'dampak' => $dummyDampakTahunan[$i] ?? 0
            ];
        }
        // 3. Trend Bulanan - MOCK DATA (Biar presentasi aman)
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $dummyOutputBulanan = [5, 7, 6, 9, 8, 11, 10, 12, 14, 13, 15, 16];
        $dummyDampakBulanan = [3, 4, 5, 6, 7, 9, 8, 10, 11, 12, 13, 14];
        $trendBulanan = [];
        foreach ($months as $idx => $m) {
            $trendBulanan[] = [
                'year' => $m,
                'output' => $dummyOutputBulanan[$idx] ?? 0,
                'dampak' => $dummyDampakBulanan[$idx] ?? 0
            ];
        }
        // 4. Fasilitas Utilization - MOCK DINAMIS (Ntar ganti pake tabel Log/Presensi)
        $facilities = Facility::where('is_active', true)->take(3)->get();
        $colors = ['#1e3a5f', '#b45309', '#1e40af'];
        $fasilitasData = [];
        foreach ($facilities as $idx => $f) {
            $mockValue = $facilities->count() > 0 ? ( (strlen($f->name) * 7) % 50 ) + 40 : 0; 
            $fasilitasData[] = [
                'name' => $f->name,
                'value' => $mockValue,
                'color' => $colors[$idx % 3] ?? '#1e3a5f'
            ];
        }

        return [
            'kpis' => [
                'total_visitors_ytd' => ProductionData::where('status', 'approved')->whereYear('date', $currentYear)->sum('visitor_count'),
                'active_projects' => ResearchProject::where('status', 'active')->count(),
                'active_tenants' => Tenant::where('status', 'active')->count(),
                'green_score' => $greenScore,
            ],
            'trend_tahunan' => $trendTahunan,
            'trend_bulanan' => $trendBulanan,
            'fasilitas' => $fasilitasData,
            'alerts' => []
        ];
    }

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
            'data' => $this->getExecutiveData()
        ]);
    }

    public function overview(Request $request)
    {
        return $this->publicOverview($request);
    }

    public function production(Request $request)
    {
        return $this->publicProduction($request);
    }

    public function research(Request $request)
    {
        return $this->publicResearch($request);
    }

    public function sustainability(Request $request)
    {
        return $this->publicSustainability($request);
    }

    public function executive(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->getExecutiveData()
        ]);
    }
}