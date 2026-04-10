<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Create Admin Role
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator', 'guard_name' => 'web']
        );

        // 2. Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@dangkiemtauca.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'), // Default password
                'role_id' => $adminRole->id,
                'phone' => '0901234567',
                'is_active' => true,
            ]
        );
        
        $this->command->info('Admin user created successfully.');
        $this->command->info('Email: admin@dangkiemtauca.com');
        $this->command->info('Password: password');
    }
}
