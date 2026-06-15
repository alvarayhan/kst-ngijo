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

        // In a real scenario, this would likely map 'code' to specific model methods.
        // For our simplified implementation, we'll map codes back to paths and reuse DataController logic.
        
        $codeToPathMap = [
            '1f0ca001-0001-6000-8000-00000000bb01' => 'ringkasan/projek-aktif',
            '1f0ca001-0001-6000-8000-00000000bb02' => 'ringkasan/avg-trl',
            '1f0ca001-0001-6000-8000-00000000bb03' => 'ringkasan/energi-terbarukan',
            '1f0ca001-0001-6000-8000-00000000bb04' => 'penelitian/aktif',
        ];

        // Instantiate DataController to reuse its show method logic
        $dataController = new DataController();

        foreach ($queries as $q) {
            $code = $q['code'] ?? '';
            $params = $q['params'] ?? [];

            if (array_key_exists($code, $codeToPathMap)) {
                $path = $codeToPathMap[$code];
                
                // Create a synthetic request with the params
                $syntheticRequest = new Request($params);
                
                try {
                    $response = $dataController->show($syntheticRequest, $path);
                    // The show method returns a JsonResponse. We extract its data.
                    if ($response->getStatusCode() === 200) {
                        $dataObj = json_decode($response->getContent(), true)['response'];
                        $results[] = $dataObj;
                    } else {
                        // Forward the error structure
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
