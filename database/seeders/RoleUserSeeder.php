<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Models\Permission;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define Roles
        $roles = [
            'staff' => 'Nhân viên',
            'manager' => 'Quản lý',
        ];

        foreach ($roles as $name => $displayName) {
            $role = Role::firstOrCreate(
                ['name' => $name],
                ['display_name' => $displayName, 'guard_name' => 'web']
            );
            
            // Assign basic permissions if needed (optional for now)
            // $role->givePermissionTo('view_dashboard');
        }

        // Define Users
        $users = [
            [
                'name' => 'Trần Thị Nhân',
                'email' => 'nhan@dangkiemtauca.com',
                'role' => 'staff',
                'password' => 'password',
                'phone' => '0987654321',
            ],
            [
                'name' => 'Lê Văn Quản',
                'email' => 'quan@dangkiemtauca.com',
                'role' => 'manager',
                'password' => 'password',
                'phone' => '0909090909',
            ],
            
        ];

        foreach ($users as $userData) {
            $role = Role::where('name', $userData['role'])->first();
            
            if ($role) {
                User::firstOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'],
                        'password' => Hash::make($userData['password']),
                        'role_id' => $role->id,
                        'phone' => $userData['phone'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
