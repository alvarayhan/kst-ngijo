<?php

namespace App\Http\Controllers\Api\Integration;

use App\Http\Controllers\Controller;
use App\Http\Traits\IntegrationResponse;
use Illuminate\Http\Request;

class QueryController extends Controller
{
    use IntegrationResponse;

    /**
     * Handle aggregate query
     */
    public function query(Request $request)
    {
        $payload = $request->json()->all();
        
        if (!isset($payload['queries']) || !is_array($payload['queries'])) {
            return $this->integrationError(400, 'Payload harus memiliki array "queries"');
        }

        $queries = $payload['queries'];

        if (count($queries) > 80) {
            return $this->integrationError(413, 'Maksimal 80 query per request');
        }

        $results = [];

        $codeToPathMap = [
            '1f0ca001-0001-6000-8000-00000000bb01' => 'tracker-inovasi/projek-aktif',
            '1f0ca001-0001-6000-8000-00000000bb02' => 'tracker-inovasi/avg-trl',
            '1f0ca001-0001-6000-8000-00000000bb05' => 'tracker-inovasi/paten-tertunda',
            '1f0ca001-0001-6000-8000-00000000bb06' => 'tracker-inovasi/kolaborasi',
            '1f0ca001-0001-6000-8000-00000000bb03' => 'keberlanjutan/energi-terbarukan',
            '1f0ca001-0001-6000-8000-00000000bb07' => 'keberlanjutan/green-performance',
            '1f0ca001-0001-6000-8000-00000000bb08' => 'keberlanjutan/air-daur-ulang',
            '1f0ca001-0001-6000-8000-00000000bb09' => 'keberlanjutan/metrik-limbah',
            '1f0ca001-0001-6000-8000-00000000bb04' => 'penelitian/aktif',
            '1f0ca001-0001-6000-8000-00000000bc01' => 'keberlanjutan/dinamika-energi',
            '1f0ca001-0001-6000-8000-00000000bc02' => 'keberlanjutan/sensor-feed',
        ];

        $dataController = new DataController();

        foreach ($queries as $q) {
            $code = $q['code'] ?? '';
            $params = $q['params'] ?? [];

            if (array_key_exists($code, $codeToPathMap)) {
                $path = $codeToPathMap[$code];
                
                $syntheticRequest = new Request($params);
                
                try {
                    $response = $dataController->show($syntheticRequest, $path);
                    if ($response->getStatusCode() === 200) {
                        $dataObj = json_decode($response->getContent(), true)['response'];
                        $results[] = $dataObj;
                    } else {
                        $errObj = json_decode($response->getContent(), true)['error'];
                        $results[] = [
                            'code' => $code,
                            'createdAt' => now()->toIso8601String(),
                            'updatedAt' => null,
                            'data' => null,
                            'error' => $errObj
                        ];
                    }
                } catch (\Exception $e) {
                    $results[] = [
                        'code' => $code,
                        'createdAt' => now()->toIso8601String(),
                        'updatedAt' => null,
                        'data' => null,
                        'error' => [
                            'code' => 500,
                            'message' => 'Internal server error processing this query item'
                        ]
                    ];
                }
            } else {
                $results[] = [
                    'code' => $code,
                    'createdAt' => now()->toIso8601String(),
                    'updatedAt' => null,
                    'data' => null,
                    'error' => [
                        'code' => 404,
                        'message' => 'Code data tidak dikenali'
                    ]
                ];
            }
        }

        return $this->integrationSuccess($results);
    }
}
