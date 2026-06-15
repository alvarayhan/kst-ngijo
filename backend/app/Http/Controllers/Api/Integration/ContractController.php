<?php

namespace App\Http\Controllers\Api\Integration;

use App\Http\Controllers\Controller;
use App\Http\Traits\IntegrationResponse;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    use IntegrationResponse;

    /**
     * Get data contract for KST Ngijo
     */
    public function index(Request $request)
    {
        // Currently we expose everything as read-only, so permission=r or rw returns the same contract.
        
        $contract = [
            'version' => '0.0.1',
            'contract' => [
                [
                    'name' => 'Ringkasan KST Ngijo',
                    'path' => '/ringkasan/',
                    'iconUri' => null,
                    'description' => 'Data ringkasan KST Ngijo',
                    'items' => [
                        [
                            'name' => 'Total Projek Aktif',
                            'path' => '/ringkasan/projek-aktif',
                            'code' => '1f0ca001-0001-6000-8000-00000000bb01',
                            'iconUri' => null,
                            'description' => 'Jumlah penelitian yang sedang aktif di KST Ngijo',
                            'operations' => ['read'],
                            'dataType' => [
                                'typeName' => 'number',
                                'unit' => null
                            ],
                            'params' => []
                        ],
                        [
                            'name' => 'Rata-rata Skor TRL',
                            'path' => '/ringkasan/avg-trl',
                            'code' => '1f0ca001-0001-6000-8000-00000000bb02',
                            'iconUri' => null,
                            'description' => 'Rata-rata Technology Readiness Level dari seluruh penelitian aktif',
                            'operations' => ['read'],
                            'dataType' => [
                                'typeName' => 'number',
                                'unit' => null
                            ],
                            'params' => []
                        ],
                        [
                            'name' => 'Total Energi Terbarukan',
                            'path' => '/ringkasan/energi-terbarukan',
                            'code' => '1f0ca001-0001-6000-8000-00000000bb03',
                            'iconUri' => null,
                            'description' => 'Total produksi energi terbarukan yang tercatat di KST Ngijo',
                            'operations' => ['read'],
                            'dataType' => [
                                'typeName' => 'number',
                                'unit' => 'MWh'
                            ],
                            'params' => []
                        ]
                    ]
                ],
                [
                    'name' => 'Daftar Penelitian Aktif',
                    'path' => '/penelitian/aktif',
                    'code' => '1f0ca001-0001-6000-8000-00000000bb04',
                    'iconUri' => null,
                    'description' => 'Daftar penelitian yang sedang berjalan di KST Ngijo',
                    'operations' => ['read'],
                    'dataType' => [
                        'typeName' => 'table',
                        'columns' => [
                            [
                                'colIdx' => 0,
                                'name' => 'Judul',
                                'dataType' => ['typeName' => 'text']
                            ],
                            [
                                'colIdx' => 1,
                                'name' => 'Peneliti Utama',
                                'dataType' => ['typeName' => 'text']
                            ],
                            [
                                'colIdx' => 2,
                                'name' => 'Kategori',
                                'dataType' => [
                                    'typeName' => 'variant',
                                    'variants' => [
                                        ['index' => 0, 'variant' => 'technology', 'semantic' => '#2563eb'],
                                        ['index' => 1, 'variant' => 'agriculture', 'semantic' => '#16a34a'],
                                        ['index' => 2, 'variant' => 'energy', 'semantic' => '#ea580c'],
                                        ['index' => 3, 'variant' => 'sustainability', 'semantic' => '#0d9488'],
                                        ['index' => 4, 'variant' => 'other', 'semantic' => 'neutral']
                                    ]
                                ]
                            ],
                            [
                                'colIdx' => 3,
                                'name' => 'TRL Level',
                                'dataType' => ['typeName' => 'number', 'unit' => null]
                            ],
                            [
                                'colIdx' => 4,
                                'name' => 'Mulai',
                                'dataType' => ['typeName' => 'datetime']
                            ]
                        ]
                    ],
                    'params' => []
                ]
            ]
        ];

        return $this->integrationSuccess($contract);
    }
}
