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

            // Installation Management (NEW)
            'view_installation' => 'Xem danh sách lắp đặt',
            'create_installation' => 'Thêm mới lắp đặt',
            'update_installation' => 'Chỉnh sửa lắp đặt',
            'delete_installation' => 'Xóa lắp đặt',

            // Maintenance Order & Quote Management (NEW)
            'view_maintenance_order' => 'Xem danh sách đơn bảo trì & báo giá',
            'create_maintenance_order' => 'Thêm mới đơn bảo trì & báo giá',
            'update_maintenance_order' => 'Chỉnh sửa đơn bảo trì & báo giá',
            'delete_maintenance_order' => 'Xóa đơn bảo trì & báo giá',

            // Maintenance Schedule (NEW)
            'view_maintenance_schedule' => 'Xem lịch bảo trì',
            'create_maintenance_schedule' => 'Thêm lịch bảo trì',
            'update_maintenance_schedule' => 'Chỉnh sửa lịch bảo trì',
            'delete_maintenance_schedule' => 'Xóa lịch bảo trì',

            // Incident Management (NEW)
            'view_incident' => 'Xem danh sách sự cố',
            'create_incident' => 'Báo cáo sự cố',
            'update_incident' => 'Xử lý sự cố',
            'delete_incident' => 'Xóa sự cố',

            // Reports & Analytics (NEW)
            'view_report' => 'Xem báo cáo & phân tích',
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
