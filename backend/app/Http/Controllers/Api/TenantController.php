<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantMetric;
use Illuminate\Http\Request;

/**
 * TenantController - Manages tenant/company data in KST Ngijo
 * 
 * FLOW PENJELASAN:
 * 1. index() - Retrieve list of tenants (paginated, with filters)
 * 2. store() - Create new tenant company registration
 * 3. show() - Get detailed tenant info including metrics history
 * 4. update() - Update tenant company information
 * 5. destroy() - Delete/deactivate tenant
 * 6. addMetric() - Add performance metrics for a tenant
 * 7. getMetrics() - Retrieve historical metrics for tenant
 */
class TenantController extends Controller
{
    /**
     * ENDPOINT: GET /api/internal/tenants
     * 
     * ALUR KERJA:
     * 1. Terima query parameters: status, search, per_page
     * 2. Build query dengan filter dan relasi creator
     * 3. Paginate hasil (default 20 per halaman)
     * 4. Return JSON response dengan pagination metadata
     * 
     * RESPONSE CONTOH:
     * {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "company_name": "PT Inovasi Tech",
     *       "industry_category": "tech",
     *       "status": "active",
     *       "creator": { "id": 1, "name": "Admin User" }
     *     }
     *   ],
     *   "pagination": { "total": 5, "per_page": 20, "current_page": 1 }
     * }
     */
    public function index(Request $request)
    {
        // Build base query dengan eager load creator user
        $query = Tenant::with('creator');

        // Filter by status jika parameter diberikan (active, inactive, graduated)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by search jika parameter diberikan (cari di company_name dan email)
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where('company_name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm);
        }

        // Paginate result dengan per_page dari request atau default 20
        $tenants = $query->latest('registration_date')->paginate($request->per_page ?? 20);

        // Return success response dengan data dan pagination metadata
        return response()->json([
            'success' => true,
            'data' => $tenants->items(),
            'pagination' => [
                'total' => $tenants->total(),
                'per_page' => $tenants->perPage(),
                'current_page' => $tenants->currentPage(),
                'last_page' => $tenants->lastPage(),
            ]
        ]);
    }

    /**
     * ENDPOINT: POST /api/internal/tenants
     * 
     * ALUR KERJA:
     * 1. Validate input request sesuai business rules
     * 2. Create tenant record dengan created_by_user_id dari auth user
     * 3. Set status default = 'active'
     * 4. Return created tenant dengan status 201
     * 
     * INPUT VALIDATION:
     * - company_name: required, string, max 255
     * - industry_category: required, enum (manufacturing, tech, agriculture, energy, other)
     * - email: required, email format
     * - registration_date: required, date format
     * - contact_person: optional, string
     * - phone: optional, string
     * - address: optional, string
     * - website: optional, URL format
     * - notes: optional, string
     */
    public function store(Request $request)
    {
        // Validasi input sesuai requirement dan constraint database
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'industry_category' => 'required|in:manufacturing,tech,agriculture,energy,other',
            'email' => 'required|email|unique:tenants,email',
            'registration_date' => 'required|date|before_or_equal:today',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Create tenant dengan menambahkan created_by_user_id dari authenticated user
        $tenant = Tenant::create(array_merge($validated, [
            'created_by_user_id' => auth('api')->id(),
            'status' => 'active', // Default status untuk tenant baru
        ]));

        // Return created response dengan status code 201 dan tenant data
        return response()->json([
            'success' => true,
            'message' => 'Tenant registered successfully',
            'data' => $tenant->load('creator')
        ], 201);
    }

    /**
     * ENDPOINT: GET /api/internal/tenants/{id}
     * 
     * ALUR KERJA:
     * 1. Find tenant by ID dengan relasi creator dan metrics
     * 2. Jika tidak ditemukan, return 404 error
     * 3. Return tenant dengan full detail dan relasi
     * 
     * RESPONSE CONTOH:
     * {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "company_name": "PT Inovasi Tech",
     *     "metrics": [
     *       {
     *         "metric_date": "2026-04-26",
     *         "employees_count": 50,
     *         "revenue": 10000000000,
     *         "sustainability_score": 85
     *       }
     *     ]
     *   }
     * }
     */
    public function show($id)
    {
        // Find tenant dengan eager load creator dan metrics relationships
        $tenant = Tenant::with(['creator', 'metrics'])->find($id);

        // Handle not found case dengan 404 response
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }

        // Return success response dengan tenant dan relasi lengkap
        return response()->json([
            'success' => true,
            'data' => $tenant
        ]);
    }

    /**
     * ENDPOINT: PUT /api/internal/tenants/{id}
     * 
     * ALUR KERJA:
     * 1. Find tenant by ID
     * 2. Validate updated fields (partial update allowed)
     * 3. Update tenant record
     * 4. Return updated tenant data
     */
    public function update(Request $request, $id)
    {
        // Find tenant or fail dengan 404
        $tenant = Tenant::findOrFail($id);

        // Validate request dengan rules yang sama seperti store tapi optional
        $validated = $request->validate([
            'company_name' => 'string|max:255',
            'industry_category' => 'in:manufacturing,tech,agriculture,energy,other',
            'email' => 'email|unique:tenants,email,' . $id,
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url',
            'status' => 'in:active,inactive,graduated',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Update tenant dengan validated data
        $tenant->update($validated);

        // Return updated tenant data
        return response()->json([
            'success' => true,
            'message' => 'Tenant updated successfully',
            'data' => $tenant->load('creator')
        ]);
    }

    /**
     * ENDPOINT: DELETE /api/internal/tenants/{id}
     * 
     * ALUR KERJA:
     * 1. Find tenant by ID
     * 2. Delete tenant dari database
     * 3. Return success message
     * 
     * CATATAN: Hard delete akan cascade delete metrics (see migration)
     */
    public function destroy($id)
    {
        // Find tenant or fail
        $tenant = Tenant::findOrFail($id);

        // Delete tenant (cascade akan delete related metrics)
        $tenant->delete();

        // Return success message
        return response()->json([
            'success' => true,
            'message' => 'Tenant deleted successfully'
        ]);
    }

    /**
     * ENDPOINT: POST /api/internal/tenants/{id}/metrics
     * 
     * ALUR KERJA:
     * 1. Find tenant by ID
     * 2. Validate metric input
     * 3. Create TenantMetric record linked to tenant
     * 4. Return created metric
     * 
     * CATATAN: Digunakan untuk track performa tenant dari waktu ke waktu
     */
    public function addMetric(Request $request, $id)
    {
        // Find tenant or fail dengan 404
        $tenant = Tenant::findOrFail($id);

        // Validate metric input data
        $validated = $request->validate([
            'metric_date' => 'required|date|before_or_equal:today',
            'employees_count' => 'nullable|integer|min:0',
            'revenue' => 'nullable|integer|min:0',
            'products_produced' => 'nullable|integer|min:0',
            'market_reach' => 'nullable|in:local,regional,national,international',
            'sustainability_score' => 'nullable|integer|min:0|max:100',
        ]);

        // Create metric record linked ke tenant_id
        $metric = TenantMetric::create(array_merge($validated, [
            'tenant_id' => $tenant->id,
        ]));

        // Return created metric dengan status 201
        return response()->json([
            'success' => true,
            'message' => 'Metric added successfully',
            'data' => $metric
        ], 201);
    }

    /**
     * ENDPOINT: GET /api/internal/tenants/{id}/metrics
     * 
     * ALUR KERJA:
     * 1. Find tenant by ID
     * 2. Retrieve all metrics untuk tenant tersebut
     * 3. Sort by metric_date descending (terbaru dulu)
     * 4. Return metrics data
     */
    public function getMetrics($id)
    {
        // Find tenant or fail
        $tenant = Tenant::findOrFail($id);

        // Retrieve metrics dari tenant, sorted by date descending
        $metrics = $tenant->metrics()->latest('metric_date')->get();

        // Return success response dengan metrics array
        return response()->json([
            'success' => true,
            'data' => $metrics
        ]);
    }
}