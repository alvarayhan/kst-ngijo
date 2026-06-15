<?php

namespace App\Http\Controllers\Api\Integration;

use App\Http\Controllers\Controller;
use App\Http\Traits\IntegrationResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    use IntegrationResponse;

    /**
     * Health check endpoint for API Gateway
     */
    public function check(Request $request)
    {
        try {
            // Check database connection
            DB::connection()->getPdo();
            
            return $this->integrationSuccess([
                'status' => 'ok'
            ]);
        } catch (\Exception $e) {
            return $this->integrationError(503, 'Service unhealthy');
        }
    }
}
