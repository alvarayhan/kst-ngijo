<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionData;
use App\Models\ResearchProject;
use App\Models\Tenant;
use Illuminate\Http\Request;

/**
 * PublicController - Handles public endpoints accessible without authentication
 * 
 * FLOW PENJELASAN:
 * Endpoints ini untuk landing page, info page, dan public statistics
 * Tidak memerlukan JWT token, semua user (termasuk guest) bisa akses
 * 
 * ENDPOINTS:
 * 1. getInfo() - Basic info about KST Ngijo
 * 2. getStatistics() - Year-to-date statistics (public data only)
 * 3. getResearchHighlights() - Featured research projects
 * 4. getTenantSpotlight() - Featured tenants
 * 5. getLatestNews() - Latest activities (non-sensitive)
 */
class PublicController extends Controller
{
    /**
     * ENDPOINT: GET /api/external/landing-page/info
     * 
     * ALUR KERJA:
     * 1. Return static/semi-static info tentang KST Ngijo
     * 2. Info ini bisa di-cache karena jarang berubah
     * 3. Gunakan untuk hero section di landing page
     * 
     * RESPONSE:
     * {
     *   "success": true,
     *   "data": {
     *     "name": "KST Ngijo",
     *     "description": "Science and Technology Park Universitas Brawijaya",
     *     "established_year": 2020,
     *     "location": "Ngijo, Malang, East Java"
     *   }
     * }
     */
    public function getInfo()
    {
        // Return basic info tentang KST Ngijo (bisa dari config atau hardcoded)
        return response()->json([
            'success' => true,
            'data' => [
                'name' => 'KST Ngijo',
                'full_name' => 'Science and Technology Park Universitas Brawijaya',
                'description' => 'A science and technology park dedicated to fostering innovation and entrepreneurship within the Universitas Brawijaya ecosystem',
                'established_year' => 2020,
                'location' => 'Ngijo, Malang, East Java, Indonesia',
                'institution' => 'Universitas Brawijaya',
                'contact_email' => 'info@kstngijo.id',
                'website' => 'https://kstngijo.id',
                'mission' => 'To support the development of technology-based businesses and research initiatives',
                'vision' => 'Become a leading science and technology park in Indonesia'
            ]
        ]);
    }

    /**
     * ENDPOINT: GET /api/external/landing-page/stats
     * 
     * ALUR KERJA:
     * 1. Query approved production data untuk year-to-date visitor count
     * 2. Query active research projects
     * 3. Query active tenants
     * 4. Return aggregated statistics tanpa data sensitif
     * 
     * CATATAN:
     * - Hanya tampilkan 'approved' data
     * - Aggregate by category untuk insights
     * - Cache hasil untuk performa (tidak query DB setiap request)
     * 
     * RESPONSE:
     * {
     *   "success": true,
     *   "data": {
     *     "total_visitors_ytd": 12450,
     *     "active_research_projects": 8,
     *     "active_tenants": 15,
     *     "statistics": { ... }
     *   }
     * }
     */
    public function getStatistics()
    {
        // Hitung total visitors year-to-date (hanya approved data)
        $totalVisitorsYtd = ProductionData::where('status', 'approved')
            ->whereYear('date', now()->year)
            ->sum('visitor_count');

        // Hitung active research projects
        $activeResearchProjects = ResearchProject::where('status', 'active')->count();

        // Hitung active tenants
        $activeTenants = Tenant::where('status', 'active')->count();

        // Group production data by visitor category untuk breakdown
        $visitorsByCategory = ProductionData::where('status', 'approved')
            ->whereYear('date', now()->year)
            ->selectRaw('visitor_category, SUM(visitor_count) as count')
            ->groupBy('visitor_category')
            ->pluck('count', 'visitor_category');

        // Group research by category
        $researchByCategory = ResearchProject::where('status', '!=', 'completed')
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');

        // Return comprehensive statistics
        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_visitors_ytd' => $totalVisitorsYtd,
                    'active_research_projects' => $activeResearchProjects,
                    'active_tenants' => $activeTenants,
                ],
                'statistics' => [
                    'visitors_by_category' => $visitorsByCategory,
                    'research_by_category' => $researchByCategory,
                ]
            ]
        ]);
    }

    /**
     * ENDPOINT: GET /api/external/landing-page/research-highlights
     * 
     * ALUR KERJA:
     * 1. Query active research projects dengan published outputs
     * 2. Filter untuk hanya menampilkan "public-friendly" projects
     * 3. Include collaborators dan outputs untuk showcase
     * 4. Limit hasil untuk performance (top 6-8 projects)
     * 
     * USE CASE: Showcase successful research untuk marketing/branding
     */
    public function getResearchHighlights()
    {
        // Query active research dengan minimal 1 output (completed research)
        $highlights = ResearchProject::where('status', 'active')
            ->withCount('outputs')
            ->with(['principalInvestigator', 'outputs' => function ($query) {
                $query->latest('date_produced')->limit(3); // Top 3 outputs per project
            }])
            ->having('outputs_count', '>', 0)
            ->latest('start_date')
            ->limit(6) // Batasi hanya 6 project terbaru untuk homepage
            ->get();

        // Return dengan format yang friendly untuk display
        return response()->json([
            'success' => true,
            'data' => $highlights->map(function ($project) {
                return [
                    'id' => $project->id,
                    'title' => $project->title,
                    'description' => substr($project->description, 0, 200) . '...',
                    'category' => $project->category,
                    'status' => $project->status,
                    'pi_name' => $project->principalInvestigator?->name,
                    'output_count' => $project->outputs_count,
                    'recent_outputs' => $project->outputs->pluck('title'),
                ];
            })
        ]);
    }

    /**
     * ENDPOINT: GET /api/external/landing-page/tenants
     * 
     * ALUR KERJA:
     * 1. Query active tenants saja (no inactive/graduated)
     * 2. Load latest metrics untuk setiap tenant
     * 3. Limit untuk performance (top 10 tenants)
     * 4. Format untuk display dengan key metrics
     * 
     * USE CASE: Showcase active tenants/businesses in KST
     */
    public function getTenantSpotlight()
    {
        // Query active tenants dengan latest metrics
        $tenants = Tenant::where('status', 'active')
            ->with(['metrics' => function ($query) {
                $query->latest('metric_date')->limit(1); // Latest metric only
            }])
            ->latest('registration_date')
            ->limit(10)
            ->get();

        // Return formatted untuk homepage display
        return response()->json([
            'success' => true,
            'data' => $tenants->map(function ($tenant) {
                $latestMetric = $tenant->metrics->first();
                return [
                    'id' => $tenant->id,
                    'company_name' => $tenant->company_name,
                    'industry_category' => $tenant->industry_category,
                    'contact_person' => $tenant->contact_person,
                    'website' => $tenant->website,
                    'latest_metrics' => [
                        'employees' => $latestMetric?->employees_count,
                        'market_reach' => $latestMetric?->market_reach,
                        'sustainability_score' => $latestMetric?->sustainability_score,
                    ]
                ];
            })
        ]);
    }

    /**
     * ENDPOINT: GET /api/external/landing-page/latest-activities
     * 
     * ALUR KERJA:
     * 1. Aggregate "latest activities" dari approved data saja
     * 2. Combine dari: production data, research projects, tenant registrations
     * 3. Sort by date descending
     * 4. Limit untuk recent activities (last 5-10)
     * 5. Format sebagai feed/timeline untuk landing page
     * 
     * CATATAN: Hanya public/approved data, tidak ada draft/pending
     */
    public function getLatestActivities()
    {
        // Get latest approved production data (visitors)
        $latestVisitors = ProductionData::where('status', 'approved')
            ->latest('date')
            ->limit(3)
            ->get()
            ->map(function ($data) {
                return [
                    'type' => 'visitors',
                    'title' => 'KST Ngijo Visited',
                    'description' => "{$data->visitor_count} {$data->visitor_category} visited",
                    'date' => date('Y-m-d', strtotime($data->date)),
                    'timestamp' => $data->updated_at,
                ];
            });

        // Get latest research projects
        $latestResearch = ResearchProject::where('status', 'active')
            ->latest('start_date')
            ->limit(3)
            ->get()
            ->map(function ($project) {
                return [
                    'type' => 'research',
                    'title' => 'New Research Project',
                    'description' => $project->title,
                    'date' => date('Y-m-d', strtotime($project->start_date)),
                    'timestamp' => $project->created_at,
                ];
            });

        // Get latest tenant registrations
        $latestTenants = Tenant::where('status', 'active')
            ->latest('registration_date')
            ->limit(3)
            ->get()
            ->map(function ($tenant) {
                return [
                    'type' => 'tenant',
                    'title' => 'New Tenant Registration',
                    'description' => "{$tenant->company_name} ({$tenant->industry_category})",
                    'date' => date('Y-m-d', strtotime($tenant->registration_date)),
                    'timestamp' => $tenant->created_at,
                ];
            });

        // Combine semua activities dan sort by timestamp descending
        $allActivities = $latestVisitors->concat($latestResearch)
            ->concat($latestTenants)
            ->sortByDesc('timestamp')
            ->take(10)
            ->values();

        // Return activities feed
        return response()->json([
            'success' => true,
            'data' => $allActivities
        ]);
    }
}