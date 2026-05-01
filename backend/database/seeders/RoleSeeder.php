<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin'], ['description' => 'Administrator with full access']);
        Role::firstOrCreate(['name' => 'operator'], ['description' => 'Operator who can input data']);
    }
}
