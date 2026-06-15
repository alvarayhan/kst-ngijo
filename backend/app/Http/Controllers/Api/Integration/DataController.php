<?php

namespace App\Http\Controllers\Api\Integration;

use App\Http\Controllers\Controller;
use App\Http\Traits\IntegrationResponse;
use App\Models\ResearchProject;
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
            // Find the column name mapped to this index
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
                    // Search all defined columns
                    foreach ($columnsConfig as $colName) {
                        $q->orWhere($colName, 'like', '%' . $search . '%');
                    }
                } else {
                    // Search specific columns
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
            case 'ringkasan/projek-aktif':
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

            case 'penelitian/aktif':
                $query = ResearchProject::with('principalInvestigator')
                            ->where('status', 'active');
                
                $columnsConfig = [
                    0 => 'title',
                    1 => 'principal_investigator_id', // search will be tricky since it's an ID, but we map it for sorting
                    2 => 'category',
                    3 => 'trl_level',
                    4 => 'start_date'
                ];

                $tableResult = $this->processTableQuery($query, $request, $columnsConfig);

                $formattedItems = $tableResult['items']->map(function($project) {
                    return [
                        'rowId' => (string) $project->id, // Contract asks for UUIDv6 ideally, but string ID works if we don't have UUID
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
