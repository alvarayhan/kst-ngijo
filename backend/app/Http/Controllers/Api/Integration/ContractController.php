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
                    'name' => 'Tracker Inovasi KST Ngijo',
                    'path' => '/tracker-inovasi/',
                    'iconUri' => null,
                    'description' => 'Metrik utama Tracker Inovasi KST Ngijo',
                    'items' => [
                        [
                            'name' => 'Total Projek Aktif',
                            'path' => '/tracker-inovasi/projek-aktif',
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
                            'path' => '/tracker-inovasi/avg-trl',
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
                            'name' => 'Paten Tertunda',
                            'path' => '/tracker-inovasi/paten-tertunda',
                            'code' => '1f0ca001-0001-6000-8000-00000000bb05',
                            'iconUri' => null,
                            'description' => 'Jumlah paten yang diajukan / tertunda',
                            'operations' => ['read'],
                            'dataType' => [
                                'typeName' => 'number',
                                'unit' => null
                            ],
                            'params' => []
                        ],
                        [
                            'name' => 'Kolaborasi',
                            'path' => '/tracker-inovasi/kolaborasi',
                            'code' => '1f0ca001-0001-6000-8000-00000000bb06',
                            'iconUri' => null,
                            'description' => 'Jumlah mitra kolaborasi dalam riset',
                            'operations' => ['read'],
                            'dataType' => [
                                'typeName' => 'number',
                                'unit' => null
                            ],
                            'params' => []
                        ]
                    ]
                ],
                [
                    'name' => 'Keberlanjutan KST Ngijo',
                    'path' => '/keberlanjutan/',
                    'iconUri' => null,
                    'description' => 'Metrik utama keberlanjutan KST Ngijo',
                    'items' => [
                        [
                            'name' => 'Total Energi Terbarukan',
                            'path' => '/keberlanjutan/energi-terbarukan',
                            'code' => '1f0ca001-0001-6000-8000-00000000bb03',
                            'iconUri' => null,
                            'description' => 'Total produksi energi terbarukan yang tercatat di KST Ngijo',
                            'operations' => ['read'],
                            'dataType' => [
                                'typeName' => 'number',
                                'unit' => 'MWh'
                            ],
                            'params' => []
                        ],
                        [
                            'name' => 'Green Performance',
                            'path' => '/keberlanjutan/green-performance',
                            'code' => '1f0ca001-0001-6000-8000-00000000bb07',
                            'iconUri' => null,
                            'description' => 'Skor rata-rata target ketercapaian green performance',
                            'operations' => ['read'],
                            'dataType' => [
                                'typeName' => 'number',
                                'unit' => '%'
                            ],
                            'params' => []
                        ],
                        [
                            'name' => 'Siklus Air (Daur Ulang)',
                            'path' => '/keberlanjutan/air-daur-ulang',
                            'code' => '1f0ca001-0001-6000-8000-00000000bb08',
                            'iconUri' => null,
                            'description' => 'Total volume air daur ulang',
                            'operations' => ['read'],
                            'dataType' => [
                                'typeName' => 'number',
                                'unit' => 'L'
                            ],
                            'params' => []
                        ],
                        [
                            'name' => 'Metrik Limbah',
                            'path' => '/keberlanjutan/metrik-limbah',
                            'code' => '1f0ca001-0001-6000-8000-00000000bb09',
                            'iconUri' => null,
                            'description' => 'Metrik pemrosesan limbah padat',
                            'operations' => ['read'],
                            'dataType' => [
                                'typeName' => 'number',
                                'unit' => 'ton'
                            ],
                            'params' => []
                        ],
                        [
                            'name' => 'Dinamika Energi',
                            'path' => '/keberlanjutan/dinamika-energi',
                            'code' => '1f0ca001-0001-6000-8000-00000000bc01',
                            'iconUri' => null,
                            'description' => 'Dinamika energi secara runtun waktu',
                            'operations' => ['read'],
                            'dataType' => [
                                'typeName' => 'timeSeries',
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
                ],
                [
                    'name' => 'Real Time Sensor Feed',
                    'path' => '/keberlanjutan/sensor-feed',
                    'code' => '1f0ca001-0001-6000-8000-00000000bc02',
                    'iconUri' => null,
                    'description' => 'Daftar log data sustainability (sensor)',
                    'operations' => ['read'],
                    'dataType' => [
                        'typeName' => 'table',
                        'columns' => [
                            [
                                'colIdx' => 0,
                                'name' => 'Lokasi Sensor',
                                'dataType' => ['typeName' => 'text']
                            ],
                            [
                                'colIdx' => 1,
                                'name' => 'Tipe',
                                'dataType' => ['typeName' => 'text']
                            ],
                            [
                                'colIdx' => 2,
                                'name' => 'Baca',
                                'dataType' => ['typeName' => 'number', 'unit' => null]
                            ],
                            [
                                'colIdx' => 3,
                                'name' => 'Status',
                                'dataType' => ['typeName' => 'text']
                            ],
                            [
                                'colIdx' => 4,
                                'name' => 'Timestamp',
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
