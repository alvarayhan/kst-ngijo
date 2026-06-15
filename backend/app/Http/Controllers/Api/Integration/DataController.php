<?php

namespace App\Http\Controllers\Api\Integration;

use App\Http\Controllers\Controller;
use App\Http\Traits\IntegrationResponse;
use App\Models\ResearchProject;
use App\Models\ResearchOutput;
use App\Models\ResearchCollaborator;
use App\Models\SustainabilityData;
use Illuminate\Http\Request;

class DataController extends Controller
{
    use IntegrationResponse;

    /**
     * Map category string to category variant index
     */
    private function mapCategoryToIndex($category)
    {
        $map = [
            'technology' => 0,
            'agriculture' => 1,
            'energy' => 2,
            'sustainability' => 3,
            'other' => 4
        ];
        return $map[$category] ?? 4;
    }

    /**
     * Helper to process table queries with offset, limit, search, sort
     */
    private function processTableQuery($query, $request, $columnsConfig)
    {
        $offset = max(0, (int)$request->query('offset', 0));
        $limit = min(50, max(1, (int)$request->query('limit', 15)));

        // Handle sort
        $sortColIdx = (int)$request->query('sort_col', -1);
        $sortOrder = strtolower($request->query('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortColIdx === -1) {
            $query->orderBy('created_at', $sortOrder);
        } else {
            if (isset($columnsConfig[$sortColIdx])) {
                $query->orderBy($columnsConfig[$sortColIdx], $sortOrder);
            }
        }

        // Handle search
        $search = $request->query('search', '');
        $searchCols = $request->query('search_col', []);
        
        if (!empty($search)) {
            $query->where(function($q) use ($search, $searchCols, $columnsConfig) {
                if (empty($searchCols)) {
                    foreach ($columnsConfig as $colName) {
                        $q->orWhere($colName, 'like', '%' . $search . '%');
                    }
                } else {
                    $searchColsArray = is_array($searchCols) ? $searchCols : [$searchCols];
                    foreach ($searchColsArray as $colIdx) {
                        if (isset($columnsConfig[$colIdx])) {
                            $q->orWhere($columnsConfig[$colIdx], 'like', '%' . $search . '%');
                        }
                    }
                }
            });
        }

        // Apply offset and fetch one extra to check hasNext
        $results = $query->skip($offset)->take($limit + 1)->get();
        
        $hasNext = $results->count() > $limit;
        $items = $results->take($limit);

        return [
            'hasNext' => $hasNext,
            'items' => $items,
            'offset' => $offset,
            'limit' => $limit
        ];
    }

    /**
     * Get data by path
     */
    public function show(Request $request, $path)
    {
        switch ($path) {
            case 'tracker-inovasi/projek-aktif':
            case 'ringkasan/projek-aktif': // Kept for backward compatibility
                return $this->integrationSuccess([
                    'code' => '1f0ca001-0001-6000-8000-00000000bb01',
                    'createdAt' => now()->toIso8601String(),
                    'updatedAt' => null,
                    'data' => [
                        'typeName' => 'number',
                        'value' => ResearchProject::where('status', 'active')->count(),
                        'unit' => null
                    ]
                ]);

            case 'tracker-inovasi/avg-trl':
            case 'ringkasan/avg-trl':
                $avgTrl = ResearchProject::where('status', 'active')->avg('trl_level') ?? 0;
                return $this->integrationSuccess([
                    'code' => '1f0ca001-0001-6000-8000-00000000bb02',
                    'createdAt' => now()->toIso8601String(),
                    'updatedAt' => null,
                    'data' => [
                        'typeName' => 'number',
                        'value' => round((float)$avgTrl, 2),
                        'unit' => null
                    ]
                ]);

            case 'tracker-inovasi/paten-tertunda':
                return $this->integrationSuccess([
                    'code' => '1f0ca001-0001-6000-8000-00000000bb05',
                    'createdAt' => now()->toIso8601String(),
                    'updatedAt' => null,
                    'data' => [
                        'typeName' => 'number',
                        'value' => ResearchOutput::where('output_type', 'patent')->count(),
                        'unit' => null
                    ]
                ]);

            case 'tracker-inovasi/kolaborasi':
                return $this->integrationSuccess([
                    'code' => '1f0ca001-0001-6000-8000-00000000bb06',
                    'createdAt' => now()->toIso8601String(),
                    'updatedAt' => null,
                    'data' => [
                        'typeName' => 'number',
                        'value' => ResearchCollaborator::count(),
                        'unit' => null
                    ]
                ]);

            case 'keberlanjutan/energi-terbarukan':
            case 'ringkasan/energi-terbarukan':
                $totalEnergy = SustainabilityData::where('category', 'energy')
                                    ->where('status', 'approved')
                                    ->sum('value') ?? 0;
                return $this->integrationSuccess([
                    'code' => '1f0ca001-0001-6000-8000-00000000bb03',
                    'createdAt' => now()->toIso8601String(),
                    'updatedAt' => null,
                    'data' => [
                        'typeName' => 'number',
                        'value' => round((float)$totalEnergy, 2),
                        'unit' => 'MWh'
                    ]
                ]);

            case 'keberlanjutan/green-performance':
                $sustainabilityReadings = SustainabilityData::where('status', 'approved')
                    ->whereNotNull('target_value')
                    ->where('target_value', '>', 0)
                    ->get();
                    
                $greenScore = $sustainabilityReadings->count() > 0
                    ? round($sustainabilityReadings->avg(fn($r) => min(100, $r->value / $r->target_value * 100)), 2)
                    : 0;

                return $this->integrationSuccess([
                    'code' => '1f0ca001-0001-6000-8000-00000000bb07',
                    'createdAt' => now()->toIso8601String(),
                    'updatedAt' => null,
                    'data' => [
                        'typeName' => 'number',
                        'value' => $greenScore,
                        'unit' => '%'
                    ]
                ]);

            case 'keberlanjutan/air-daur-ulang':
                $recycledWater = SustainabilityData::where('category', 'water')
                    ->where('notes', 'like', '%Recycled%')
                    ->sum('value') ?? 0;

                return $this->integrationSuccess([
                    'code' => '1f0ca001-0001-6000-8000-00000000bb08',
                    'createdAt' => now()->toIso8601String(),
                    'updatedAt' => null,
                    'data' => [
                        'typeName' => 'number',
                        'value' => round((float)$recycledWater, 2),
                        'unit' => 'L'
                    ]
                ]);

            case 'keberlanjutan/metrik-limbah':
                $solidWaste = SustainabilityData::where('category', 'waste')
                    ->where('notes', 'like', '%Solid Waste%')
                    ->sum('value') ?? 0;

                return $this->integrationSuccess([
                    'code' => '1f0ca001-0001-6000-8000-00000000bb09',
                    'createdAt' => now()->toIso8601String(),
                    'updatedAt' => null,
                    'data' => [
                        'typeName' => 'number',
                        'value' => round((float)$solidWaste, 2),
                        'unit' => 'ton'
                    ]
                ]);

            case 'keberlanjutan/dinamika-energi':
                $startTime = $request->query('start_time') ? \Carbon\Carbon::createFromTimestamp($request->query('start_time')) : now()->subMinutes(900);
                $endTime = $request->query('end_time') ? \Carbon\Carbon::createFromTimestamp($request->query('end_time')) : now();
                $limit = min(1000, max(1, (int)$request->query('limit', 1000)));

                $energyData = SustainabilityData::where('category', 'energy')
                    ->whereBetween('synced_at', [$startTime, $endTime])
                    ->orderBy('synced_at', 'asc')
                    ->take($limit + 1)
                    ->get();

                $hasMore = $energyData->count() > $limit;
                $energyData = $energyData->take($limit);

                $values = $energyData->map(function($data) {
                    return [
                        'timestamp' => $data->synced_at->toIso8601String(),
                        'value' => (float)$data->value
                    ];
                });

                return $this->integrationSuccess([
                    'code' => '1f0ca001-0001-6000-8000-00000000bc01',
                    'createdAt' => now()->toIso8601String(),
                    'updatedAt' => null,
                    'data' => [
                        'typeName' => 'timeSeries',
                        'rangeStart' => $startTime->toIso8601String(),
                        'rangeEnd' => $endTime->toIso8601String(),
                        'unit' => 'MWh',
                        'hasMore' => $hasMore,
                        'value' => $values
                    ]
                ]);

            case 'keberlanjutan/sensor-feed':
                $query = SustainabilityData::orderBy('synced_at', 'desc');
                
                $columnsConfig = [
                    0 => 'metric_name',
                    1 => 'category',
                    2 => 'value',
                    3 => 'status',
                    4 => 'synced_at'
                ];

                $tableResult = $this->processTableQuery($query, $request, $columnsConfig);

                $formattedItems = $tableResult['items']->map(function($data) {
                    return [
                        'rowId' => (string) $data->id,
                        'createdAt' => $data->created_at->toIso8601String(),
                        'updatedAt' => $data->updated_at ? $data->updated_at->toIso8601String() : null,
                        'colValues' => [
                            ['colIdx' => 0, 'value' => $data->metric_name],
                            ['colIdx' => 1, 'value' => $data->category],
                            ['colIdx' => 2, 'value' => (float)$data->value],
                            ['colIdx' => 3, 'value' => $data->status],
                            ['colIdx' => 4, 'value' => $data->synced_at ? $data->synced_at->toIso8601String() : null]
                        ]
                    ];
                });

                return $this->integrationSuccess([
                    'code' => '1f0ca001-0001-6000-8000-00000000bc02',
                    'createdAt' => now()->toIso8601String(),
                    'updatedAt' => null,
                    'data' => [
                        'typeName' => 'table',
                        'offset' => $tableResult['offset'],
                        'limit' => $tableResult['limit'],
                        'hasNext' => $tableResult['hasNext'],
                        'items' => $formattedItems
                    ]
                ]);

            case 'penelitian/aktif':
                $query = ResearchProject::with('principalInvestigator')
                            ->where('status', 'active');
                
                $columnsConfig = [
                    0 => 'title',
                    1 => 'principal_investigator_id',
                    2 => 'category',
                    3 => 'trl_level',
                    4 => 'start_date'
                ];

                $tableResult = $this->processTableQuery($query, $request, $columnsConfig);

                $formattedItems = $tableResult['items']->map(function($project) {
                    return [
                        'rowId' => (string) $project->id,
                        'createdAt' => $project->created_at->toIso8601String(),
                        'updatedAt' => $project->updated_at ? $project->updated_at->toIso8601String() : null,
                        'colValues' => [
                            ['colIdx' => 0, 'value' => $project->title],
                            ['colIdx' => 1, 'value' => $project->principalInvestigator ? $project->principalInvestigator->name : '-'],
                            ['colIdx' => 2, 'value' => $this->mapCategoryToIndex($project->category)],
                            ['colIdx' => 3, 'value' => $project->trl_level],
                            ['colIdx' => 4, 'value' => $project->start_date ? $project->start_date->toIso8601String() : null]
                        ]
                    ];
                });

                return $this->integrationSuccess([
                    'code' => '1f0ca001-0001-6000-8000-00000000bb04',
                    'createdAt' => now()->toIso8601String(),
                    'updatedAt' => null,
                    'data' => [
                        'typeName' => 'table',
                        'offset' => $tableResult['offset'],
                        'limit' => $tableResult['limit'],
                        'hasNext' => $tableResult['hasNext'],
                        'items' => $formattedItems
                    ]
                ]);

            default:
                return $this->integrationError(404, 'Path data tidak ditemukan');
        }
    }
}
