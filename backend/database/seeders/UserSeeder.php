<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $operatorRole = Role::where('name', 'operator')->first();

        // Create Admin User
        User::firstOrCreate(
            ['email' => 'admin@kst.local'],
            [
                'name' => 'Admin KST Ngijo',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRole?->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Create Operator User
        User::firstOrCreate(
            ['email' => 'operator@kst.local'],
            [
                'name' => 'Operator KST',
                'password' => Hash::make('operator123'),
                'role_id' => $operatorRole?->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }
}