<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\IntegrationLog;

class IntegrationService
{
    protected $externalApiUrl;
    protected $apiKey;
    
    public function __construct()
    {
        // Konfigurasi URL dan API Key diambil dari .env
        $this->externalApiUrl = env('KELOMPOK1_API_URL', 'https://kelompok1-api.local/api/external/sync');
        $this->apiKey = env('KELOMPOK1_API_KEY', 'default-key-for-local');
    }
    
    public function syncData($data, $type)
    {
        try {
            $payload = [
                'sync_timestamp' => now()->toIso8601String(),
                'data_type' => $type,
                'source_system' => 'kst-ngijo',
                'data' => [$data->toArray()],
            ];
            
            // Pengiriman POST request ke Kelompok 1
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->externalApiUrl, $payload);
            
            // Pencatatan aktivitas ke tabel integration_logs
            IntegrationLog::create([
                'endpoint' => 'kelompok1/sync-data',
                'method' => 'POST',
                'payload' => json_encode($payload),
                'response_status' => $response->status(),
                'response_body' => $response->body(),
                'external_system' => 'kelompok_1',
                'success' => $response->successful(),
                'error_message' => !$response->successful() ? $response->body() : null,
            ]);
            
            if ($response->successful()) {
                // Penandaan data berhasil disinkronisasi
                $data->update(['synced_at' => now()]);
            }
            
            return $response;
        } catch (\Exception $e) {
            IntegrationLog::create([
                'endpoint' => 'kelompok1/sync-data',
                'method' => 'POST',
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
}