<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IntegrationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * IntegrationController - Handles external system integration endpoints
 * 
 * ALUR KERJA:
 * 1. Receive incoming webhooks dari Kelompok 1
 * 2. Validate API key untuk security
 * 3. Process webhook data
 * 4. Log integration untuk audit trail
 * 5. Return response ke external system
 * 
 * ENDPOINTS:
 * 1. POST /api/external/webhook/sync-complete - Notification bahwa sync berhasil
 * 2. POST /api/external/webhook/sync-error - Notification tentang sync error
 * 3. GET /api/external/status - Health check endpoint
 * 
 * SECURITY:
 * - API key authentication via header (X-API-Key)
 * - Request signing (optional, untuk prevent tampering)
 * - Rate limiting (implement di middleware)
 */
class IntegrationController extends Controller
{
    /**
     * ENDPOINT: GET /api/external/status
     * 
     * ALUR KERJA:
     * 1. Public endpoint untuk health check
     * 2. Tidak memerlukan authentication
     * 3. Return status OK jika service up
     * 4. External system bisa pakai ini untuk monitoring
     * 
     * RESPONSE:
     * {
     *   "success": true,
     *   "status": "operational",
     *   "timestamp": "2026-04-26T10:00:00Z"
     * }
     */
    public function healthCheck()
    {
        // Return operational status
        return response()->json([
            'success' => true,
            'status' => 'operational',
            'system' => 'kst-ngijo-api',
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0',
        ]);
    }

    /**
     * ENDPOINT: POST /api/external/webhook/sync-complete
     * 
     * ALUR KERJA:
     * 1. Receive notification dari Kelompok 1 bahwa sync complete
     * 2. Validate API key di header
     * 3. Log sync completion untuk audit trail
     * 4. Update synced_at timestamp di data (optional)
     * 5. Return acknowledgment
     * 
     * REQUEST BODY:
     * {
     *   "event": "sync_complete",
     *   "data_type": "production",
     *   "sync_count": 5,
     *   "timestamp": "2026-04-26T10:00:00Z"
     * }
     * 
     * SECURITY:
     * - Validate X-API-Key header (mutual authentication)
     * - Log semua incoming requests
     */
    public function receiveSyncComplete(Request $request)
    {
        // Validate API key untuk security
        if (!$this->validateApiKey($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key'
            ], 401);
        }

        // Validate request structure
        $validated = $request->validate([
            'event' => 'required|string',
            'data_type' => 'required|in:production,research,sustainability',
            'sync_count' => 'required|integer|min:0',
            'timestamp' => 'required|date',
        ]);

        try {
            // Log webhook ke integration_logs untuk audit
            IntegrationLog::create([
                'endpoint' => '/api/external/webhook/sync-complete',
                'method' => 'POST',
                'payload' => json_encode($request->all()),
                'response_status' => 200,
                'external_system' => 'kelompok_1',
                'success' => true,
            ]);

            // Return success acknowledgment
            return response()->json([
                'success' => true,
                'message' => 'Sync complete notification received',
                'data' => [
                    'sync_count' => $validated['sync_count'],
                    'data_type' => $validated['data_type'],
                ]
            ]);

        } catch (\Exception $e) {
            // Log error
            Log::error('IntegrationController::receiveSyncComplete error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            // Log failure ke integration_logs
            IntegrationLog::create([
                'endpoint' => '/api/external/webhook/sync-complete',
                'method' => 'POST',
                'payload' => json_encode($request->all()),
                'response_status' => 500,
                'external_system' => 'kelompok_1',
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);

            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'Error processing webhook'
            ], 500);
        }
    }

    /**
     * ENDPOINT: POST /api/external/webhook/sync-error
     * 
     * ALUR KERJA:
     * 1. Receive error notification dari Kelompok 1
     * 2. Validate API key
     * 3. Log error untuk debugging
     * 4. Optional: notify admin via notification service
     * 5. Return acknowledgment
     * 
     * REQUEST BODY:
     * {
     *   "event": "sync_error",
     *   "data_type": "production",
     *   "error_message": "Invalid data format",
     *   "failed_count": 2,
     *   "timestamp": "2026-04-26T10:00:00Z"
     * }
     * 
     * CATATAN:
     * - Error dari Kelompok 1 harus di-log dan di-monitor
     * - Admin harus di-notify untuk troubleshoot
     * - Jangan retry otomatis, biarkan operator yang decide
     */
    public function receiveSyncError(Request $request)
    {
        // Validate API key
        if (!$this->validateApiKey($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key'
            ], 401);
        }

        // Validate request structure
        $validated = $request->validate([
            'event' => 'required|string|in:sync_error',
            'data_type' => 'required|in:production,research,sustainability',
            'error_message' => 'required|string|max:1000',
            'failed_count' => 'required|integer|min:1',
            'timestamp' => 'required|date',
        ]);

        try {
            // Log error webhook ke integration_logs
            IntegrationLog::create([
                'endpoint' => '/api/external/webhook/sync-error',
                'method' => 'POST',
                'payload' => json_encode($request->all()),
                'response_status' => 400,
                'external_system' => 'kelompok_1',
                'success' => false,
                'error_message' => $validated['error_message'],
            ]);

            // Log ke application logs juga untuk monitoring
            Log::warning('Sync error from Kelompok 1', [
                'data_type' => $validated['data_type'],
                'error_message' => $validated['error_message'],
                'failed_count' => $validated['failed_count'],
            ]);

            // TODO: Notify admin via NotificationService
            // $notificationService->sendSystemAlert(
            //     'Sync Error from Kelompok 1',
            //     "Data type: {$validated['data_type']}, Error: {$validated['error_message']}",
            //     'admin'
            // );

            // Return acknowledgment
            return response()->json([
                'success' => true,
                'message' => 'Error notification received and logged',
                'data' => [
                    'data_type' => $validated['data_type'],
                    'failed_count' => $validated['failed_count'],
                ]
            ]);

        } catch (\Exception $e) {
            // Log error
            Log::error('IntegrationController::receiveSyncError error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'Error processing error notification'
            ], 500);
        }
    }

    /**
     * ENDPOINT: GET /api/external/sync-status
     * 
     * ALUR KERJA:
     * 1. Return sync status summary (recent syncs, failures)
     * 2. Digunakan untuk monitoring dashboard
     * 3. Show last N syncs dengan status
     * 
     * RESPONSE:
     * {
     *   "success": true,
     *   "data": {
     *     "last_sync": "2026-04-26T10:00:00Z",
     *     "recent_syncs": [...],
     *     "total_synced": 150
     *   }
     * }
     */
    public function getSyncStatus(Request $request)
    {
        // Get last 10 integration logs
        $recentLogs = IntegrationLog::where('external_system', 'kelompok_1')
            ->latest('created_at')
            ->limit(10)
            ->get();

        // Get last successful sync
        $lastSuccessSync = IntegrationLog::where('external_system', 'kelompok_1')
            ->where('success', true)
            ->latest('created_at')
            ->first();

        // Count total successful syncs
        $totalSynced = IntegrationLog::where('external_system', 'kelompok_1')
            ->where('success', true)
            ->count();

        // Count recent failures (last 24 hours)
        $recentFailures = IntegrationLog::where('external_system', 'kelompok_1')
            ->where('success', false)
            ->where('created_at', '>', now()->subHours(24))
            ->count();

        // Return status
        return response()->json([
            'success' => true,
            'data' => [
                'last_sync' => $lastSuccessSync?->created_at->toIso8601String(),
                'recent_logs' => $recentLogs->map(fn($log) => [
                    'endpoint' => $log->endpoint,
                    'status' => $log->success ? 'success' : 'failed',
                    'timestamp' => $log->created_at->toIso8601String(),
                ]),
                'stats' => [
                    'total_synced' => $totalSynced,
                    'recent_failures' => $recentFailures,
                ]
            ]
        ]);
    }

    /**
     * PRIVATE METHOD: validateApiKey()
     * 
     * ALUR KERJA:
     * 1. Get API key dari X-API-Key header
     * 2. Compare dengan expected API key dari config
     * 3. Return true jika valid, false jika invalid
     * 
     * SECURITY:
     * - API key disimpan di .env, tidak di-hardcode
     * - Use timing-safe comparison untuk prevent timing attacks
     * 
     * CATATAN:
     * - TODO: Implement API key versioning/rotation
     * - TODO: Implement rate limiting per API key
     */
    private function validateApiKey($request)
    {
        // Get API key dari request header
        $requestApiKey = $request->header('X-API-Key');
        
        // Get expected API key dari config
        $expectedApiKey = env('KELOMPOK1_API_KEY');

        // Use hash_equals untuk timing-safe comparison (prevent timing attacks)
        return hash_equals($expectedApiKey, $requestApiKey ?? '');
    }
}