<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'manage_kst_data', 'description' => 'Manage KST Data (CRUD)'],
            ['name' => 'validate_data', 'description' => 'Validate Data (approve/reject)'],
            ['name' => 'input_production_data', 'description' => 'Input Production Data'],
            ['name' => 'input_research_data', 'description' => 'Input Research Data'],
            ['name' => 'input_sustainability_data', 'description' => 'Input Sustainability Data'],
            ['name' => 'view_production_dashboard', 'description' => 'View Production Dashboard'],
            ['name' => 'view_research_dashboard', 'description' => 'View Research Dashboard'],
            ['name' => 'view_sustainability_dashboard', 'description' => 'View Sustainability Dashboard'],
            ['name' => 'view_executive_dashboard', 'description' => 'View Executive Dashboard'],
            ['name' => 'manage_users', 'description' => 'Manage Users'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }
    }
}