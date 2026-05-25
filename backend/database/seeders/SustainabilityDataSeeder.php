<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SustainabilityDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sustainability_data')->insert([
            [
                'record_date' => now()->format('Y-m-d'),
                'category' => 'energy',
                'metric_name' => 'North Solar Grid A-12 (Solar Panel Array) - Zona Energi, Sektor Utara',
                'value' => 425.80,
                'unit' => 'kWh',
                'target_value' => 500.00,
                'notes' => 'Trend: up. Status Sensor: OPTIMAL',
                'created_by_user_id' => null, // Nanti bisa diisi ID User Admin lo
                'approved_by_user_id' => null,
                'status' => 'approved', // Langsung set approved biar langsung muncul di API
                'synced_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'record_date' => now()->format('Y-m-d'),
                'category' => 'water',
                'metric_name' => 'Western Water Rec. Station (Water Recycling) - Zona Utilitas, Sektor Barat',
                'value' => 124.50,
                'unit' => 'm³',
                'target_value' => 150.00,
                'notes' => 'Trend: stable. Status Sensor: OPTIMAL',
                'created_by_user_id' => null,
                'approved_by_user_id' => null,
                'status' => 'approved',
                'synced_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'record_date' => now()->format('Y-m-d'),
                'category' => 'energy',
                'metric_name' => 'Biomass Plant Chamber 1 (Biomass Generator) - Zona Utilitas, Sektor Selatan',
                'value' => 89.20,
                'unit' => 'kWh',
                'target_value' => 120.00,
                'notes' => 'Trend: down. Status Sensor: ATTENTION (Butuh maintenance kecil)',
                'created_by_user_id' => null,
                'approved_by_user_id' => null,
                'status' => 'approved',
                'synced_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'record_date' => now()->format('Y-m-d'),
                'category' => 'waste',
                'metric_name' => 'Main Waste Sorting Conveyor (Solid Waste Metrics) - Zona Logistik, Sektor Timur',
                'value' => 12.40,
                'unit' => 'ton',
                'target_value' => 15.00,
                'notes' => 'Trend: up. Status Sensor: OPTIMAL',
                'created_by_user_id' => null,
                'approved_by_user_id' => null,
                'status' => 'approved',
                'synced_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}