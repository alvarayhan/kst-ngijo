<?php

namespace App\Services;

use App\Models\ProductionData;
use App\Models\ResearchProject;
use App\Models\SustainabilityData;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * DashboardService - Handles dashboard data aggregation dan caching
 * 
 * ALUR KERJA:
 * 1. Aggregate data dari multiple tables (production, research, sustainability, tenant)
 * 2. Cache hasil untuk performa (avoid expensive query setiap request)
 * 3. Provide methods untuk different dashboard views (overview, production, research, etc)
 * 4. Invalidate cache ketika data berubah (via events/listeners)
 * 
 * KEUNTUNGAN:
 * - Separation of concerns: complex logic terpisah dari controller
 * - Performance: query results di-cache, tidak DB query setiap request
 * - Reusable: bisa pakai method ini dari multiple endpoints
 * - Testable: service bisa di-unit test tanpa HTTP context
 * 
 * CACHE STRATEGY:
 * - Cache key format: 'dashboard.{type}.{daterange}'
 * - Cache TTL: 1 hour (3600 seconds) - balance antara freshness dan performance
 * - Manual invalidation: call clearCache() ketika data approved/rejected
 */
class DashboardService
{
    /**
     * CACHE PREFIX
     * Gunakan prefix konsisten agar mudah invalidate sekaligus
     */
    private const CACHE_PREFIX = 'dashboard.';

    /**
     * CACHE TTL (Time To Live)
     * 1 jam = 3600 detik
     * Cukup fresh untuk realtime dashboard, tapi tidak query database terlalu sering
     */
    private const CACHE_TTL = 3600;

    /**
     * METHOD: getOverviewDashboard()
     * 
     * ALUR KERJA:
     * 1. Generate cache key berdasarkan date range
     * 2. Check apakah cached data ada, jika ya return dari cache
     * 3. Jika tidak ada, query database untuk aggregate data
     * 4. Store hasil di cache dengan TTL 1 jam
     * 5. Return aggregated data
     * 
     * CACHED QUERIES:
     * - Total visitors (YTD dan current month)
     * - Active research projects count
     * - Active tenants count
     * 
     * RESPONSE FORMAT:
     * [
     *   'summary' => [
     *     'total_visitors_month' => 5000,
     *     'total_visitors_year' => 60000,
     *     'active_research_projects' => 12,
     *     'active_tenants' => 8,
     *   ]
     * ]
     */
    public function getOverviewDashboard($dateFrom = null, $dateTo = null)
    {
        // Set default dates jika tidak diberikan
        $dateFrom = $dateFrom ?? now()->startOfMonth();
        $dateTo = $dateTo ?? now()->endOfMonth();

        // Generate cache key unik berdasarkan date range
        $cacheKey = self::CACHE_PREFIX . "overview.{$dateFrom}.{$dateTo}";

        // Return cached data jika ada, otherwise compute dan cache
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($dateFrom, $dateTo) {
            // Query total visitors bulan ini (hanya approved data)
            $totalVisitorsMonth = ProductionData::where('status', 'approved')
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->sum('visitor_count');

            // Query total visitors year-to-date
            $totalVisitorsYear = ProductionData::where('status', 'approved')
                ->whereYear('date', now()->year)
                ->sum('visitor_count');

            // Count active research projects
            $activeResearchProjects = ResearchProject::where('status', 'active')->count();

            // Count active tenants
            $activeTenants = Tenant::where('status', 'active')->count();

            // Return aggregated data (akan di-cache)
            return [
                'summary' => [
                    'total_visitors_month' => $totalVisitorsMonth,
                    'total_visitors_year' => $totalVisitorsYear,
                    'active_research_projects' => $activeResearchProjects,
                    'active_tenants' => $activeTenants,
                ]
            ];
        });
    }

    /**
     * METHOD: getProductionDashboard()
     * 
     * ALUR KERJA:
     * 1. Cache production data breakdown
     * 2. Include aggregate by category (individuals, groups, researchers, students)
     * 3. Include aggregate by time_slot (morning, afternoon, evening)
     * 4. Calculate trend data (daily aggregates)
     * 5. Return formatted untuk chart/visualization
     * 
     * CACHED QUERIES:
     * - Total visitors
     * - Breakdown by category
     * - Breakdown by time slot
     * - Daily trend (for line chart)
     * 
     * RESPONSE FORMAT:
     * [
     *   'total_visitors' => 5000,
     *   'average_daily' => 166.67,
     *   'by_category' => ['individuals' => 2000, 'groups' => 1500, ...],
     *   'by_time_slot' => ['morning' => 2000, 'afternoon' => 2000, ...],
     *   'trend' => [['date' => '2026-04-01', 'count' => 150], ...],
     * ]
     */
    public function getProductionDashboard($dateFrom = null, $dateTo = null)
    {
        $dateFrom = $dateFrom ?? now()->startOfMonth();
        $dateTo = $dateTo ?? now()->endOfMonth();

        $cacheKey = self::CACHE_PREFIX . "production.{$dateFrom}.{$dateTo}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($dateFrom, $dateTo) {
            // Base query untuk approved data dalam date range
            $query = ProductionData::where('status', 'approved')
                ->whereBetween('date', [$dateFrom, $dateTo]);

            // Total visitors
            $totalVisitors = (clone $query)->sum('visitor_count');

            // Average daily visitors
            $dayDifference = $dateFrom->diffInDays($dateTo) ?: 1;
            $averageDaily = $totalVisitors / $dayDifference;

            // Breakdown by visitor category
            $byCategory = (clone $query)
                ->groupBy('visitor_category')
                ->select('visitor_category', DB::raw('sum(visitor_count) as count'))
                ->get()
                ->pluck('count', 'visitor_category')
                ->toArray();

            // Breakdown by time slot
            $byTimeSlot = (clone $query)
                ->groupBy('time_slot')
                ->select('time_slot', DB::raw('sum(visitor_count) as count'))
                ->get()
                ->pluck('count', 'time_slot')
                ->toArray();

            // Daily trend (untuk line chart)
            $trend = (clone $query)
                ->orderBy('date')
                ->select('date', DB::raw('sum(visitor_count) as count'))
                ->groupBy('date')
                ->get()
                ->map(fn($item) => [
                    'date' => $item->date->toDateString(),
                    'count' => $item->count,
                ])
                ->toArray();

            // Return cached production dashboard
            return [
                'total_visitors' => $totalVisitors,
                'average_daily' => round($averageDaily, 2),
                'by_category' => $byCategory,
                'by_time_slot' => $byTimeSlot,
                'trend' => $trend,
            ];
        });
    }

    /**
     * METHOD: getResearchDashboard()
     * 
     * ALUR KERJA:
     * 1. Cache research project stats
     * 2. Count by status (planning, active, completed, paused)
     * 3. Count by category (technology, agriculture, etc)
     * 4. Get top projects dengan outputs
     * 5. Calculate productivity metrics
     */
    public function getResearchDashboard()
    {
        $cacheKey = self::CACHE_PREFIX . 'research';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            // Count by status
            $byStatus = ResearchProject::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();

            // Count by category
            $byCategory = ResearchProject::selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->get()
                ->pluck('count', 'category')
                ->toArray();

            // Total research projects
            $totalProjects = ResearchProject::count();

            // Active projects dengan outputs
            $activeProjects = ResearchProject::where('status', 'active')
                ->withCount('outputs')
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'title' => $p->title,
                    'category' => $p->category,
                    'outputs_count' => $p->outputs_count,
                ])
                ->toArray();

            return [
                'total_projects' => $totalProjects,
                'by_status' => $byStatus,
                'by_category' => $byCategory,
                'active_projects' => $activeProjects,
            ];
        });
    }

    /**
     * METHOD: getSustainabilityDashboard()
     * 
     * ALUR KERJA:
     * 1. Cache sustainability metrics aggregation
     * 2. Calculate overall score (average dari semua kategori)
     * 3. Breakdown per category dengan target achievement
     * 4. Calculate achievement percentage (actual vs target)
     */
    public function getSustainabilityDashboard($dateFrom = null, $dateTo = null)
    {
        $dateFrom = $dateFrom ?? now()->startOfMonth();
        $dateTo = $dateTo ?? now()->endOfMonth();

        $cacheKey = self::CACHE_PREFIX . "sustainability.{$dateFrom}.{$dateTo}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($dateFrom, $dateTo) {
            // Get approved sustainability data
            $data = SustainabilityData::where('status', 'approved')
                ->whereBetween('record_date', [$dateFrom, $dateTo])
                ->get();

            // Group by category dan calculate achievement
            $byCategory = $data->groupBy('category')
                ->map(function ($items) {
                    // Calculate average achievement percentage
                    $scores = $items->map(function ($item) {
                        return $item->target_value 
                            ? ($item->value / $item->target_value) * 100 
                            : 0;
                    });

                    return [
                        'score' => round($scores->average(), 2),
                        'count' => $items->count(),
                        'items' => $items->toArray(),
                    ];
                })
                ->toArray();

            // Calculate overall score
            $overallScore = collect($byCategory)
                ->avg('score') ?? 0;

            return [
                'overall_score' => round($overallScore, 2),
                'by_category' => $byCategory,
            ];
        });
    }

    /**
     * METHOD: clearCache()
     * 
     * ALUR KERJA:
     * 1. Dipanggil ketika ada data approved/rejected
     * 2. Invalidate semua cache yang related dengan data yang berubah
     * 3. Next request akan re-compute dan re-cache
     * 
     * CALL POINT:
     * - Dari ValidationController::approve() / reject()
     * - Dari Event Listener saat DataApproved dipush
     * 
     * CATATAN:
     * - Cache::forget() menghapus specific key
     * - Cache::flush() menghapus SEMUA cache (dangerous!)
     * - Lebih safe pake forget dengan prefix matching
     */
    public function clearCache($type = null)
    {
        // Jika type specific, hapus hanya cache untuk type itu
        if ($type) {
            // Clear semua cache dengan prefix tertentu
            // Cache::forget() hanya bisa hapus 1 key, jadi gunakan tags jika possible
            // Untuk sekarang, clear common date ranges
            Cache::forget(self::CACHE_PREFIX . $type);
            
            // Clear dengan berbagai date ranges yang mungkin digunakan
            for ($i = 0; $i < 3; $i++) {
                $date = now()->subDays($i)->format('Y-m-d');
                Cache::forget(self::CACHE_PREFIX . "{$type}.{$date}");
            }
        } else {
            // Clear semua dashboard cache jika tidak ada type specific
            // Ini adalah "nuclear option" - gunakan hanya ketika banyak data berubah
            // Ideally, trigger dari event/listener dan clear hanya yang relevant
            $this->clearAllDashboardCache();
        }
    }

    /**
     * METHOD: clearAllDashboardCache()
     * 
     * ALUR KERJA:
     * 1. Hapus SEMUA dashboard cache
     * 2. Next request akan re-compute
     * 3. Gunakan hanya ketika bulk update/import
     */
    private function clearAllDashboardCache()
    {
        // Flush cache dengan prefix matching
        // Laravel Cache::forget() tidak support prefix glob, jadi ini workaround
        // Better solution: use cache tags
        // Cache::tags(['dashboard'])->flush();
        
        // Untuk sekarang, manually clear common keys
        $types = ['overview', 'production', 'research', 'sustainability'];
        foreach ($types as $type) {
            Cache::forget(self::CACHE_PREFIX . $type);
        }
    }
}