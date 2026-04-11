<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Simplified Permissions for Hải An Elevator
        $permissions = [
            // Dashboard
            'view_dashboard' => 'Xem bảng điều khiển',

            // User Management
            'view_user' => 'Xem danh sách tài khoản',
            'create_user' => 'Thêm mới tài khoản',
            'update_user' => 'Chỉnh sửa tài khoản',
            'delete_user' => 'Xóa tài khoản',
            'assign_role' => 'Phân quyền tài khoản',

            // Role Management
            'view_role' => 'Xem danh sách chức vụ',
            'create_role' => 'Thêm mới chức vụ',
            'update_role' => 'Chỉnh sửa chức vụ',
            'delete_role' => 'Xóa chức vụ',

            // Branch Management (NEW)
            'view_branch' => 'Xem danh sách chi nhánh',
            'create_branch' => 'Thêm mới chi nhánh',
            'update_branch' => 'Chỉnh sửa chi nhánh',
            'delete_branch' => 'Xóa chi nhánh',

            // Building & Customer Management (NEW)
            'view_building' => 'Xem danh sách tòa nhà',
            'create_building' => 'Thêm mới tòa nhà',
            'update_building' => 'Chỉnh sửa tòa nhà',
            'delete_building' => 'Xóa tòa nhà',

            // Elevator Management (NEW)
            'view_elevator' => 'Xem danh sách thang máy',
            'create_elevator' => 'Thêm mới thang máy',
            'update_elevator' => 'Chỉnh sửa thang máy',
            'delete_elevator' => 'Xóa thang máy',

            // News / Notifications
            'view_news' => 'Xem danh sách tin tức',
            'create_news' => 'Tạo tin tức',
            'update_news' => 'Cập nhật tin tức',
            'delete_news' => 'Xóa tin tức',
        ];

        // Clean up OLD permissions that are NOT in the new list
        Permission::whereNotIn('name', array_keys($permissions))->delete();

        // Create or update current permissions
        foreach ($permissions as $name => $displayName) {
            Permission::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['display_name' => $displayName]
            );
        }

        // Assign all to Admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            // Re-sync all permissions to admin to ensure they have the new ones
            $adminRole->syncPermissions(array_keys($permissions));
        }

        $this->command->info('Permissions refactored and cleaned up successfully.');
    }
}
