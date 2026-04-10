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

        // Permissions for User Management
        $permissions = [
            'view_dashboard' => 'Xem bảng điều khiển',
            'view_user' => 'Xem danh sách tài khoản',
            'create_user' => 'Thêm mới tài khoản',
            'update_user' => 'Chỉnh sửa tài khoản',
            'delete_user' => 'Xóa tài khoản',
            'assign_role' => 'Phân quyền tài khoản',

            // Permissions for Role Management
            'view_role' => 'Xem danh sách chức vụ',
            'create_role' => 'Thêm mới chức vụ',
            'update_role' => 'Chỉnh sửa chức vụ',
            'delete_role' => 'Xóa chức vụ',

            // Permissions for Media Manager
            // Permissions for Media Manager
            'view_media' => 'Xem tài liệu an toàn',
            'create_media' => 'Tải lên tài liệu',
            'update_media' => 'Cập nhật tài liệu',
            'delete_media' => 'Xóa tài liệu',

            // Permissions for Inspection Process
            'view_inspection_process' => 'Xem quy trình đăng kiểm',
            'create_inspection_process' => 'Tạo quy trình đăng kiểm',
            'update_inspection_process' => 'Chỉnh sửa quy trình',
            'delete_inspection_process' => 'Xóa quy trình',

            // Permissions for Ship Management
            'view_ship' => 'Xem danh sách tàu',
            'create_ship' => 'Thêm mới tàu',
            'update_ship' => 'Chỉnh sửa tàu',
            'delete_ship' => 'Xóa tàu',

            // Permissions for Shipyards
            'view_shipyard' => 'Xem cơ sở đóng mới',
            'create_shipyard' => 'Thêm cơ sở đóng mới',
            'update_shipyard' => 'Cập nhật cơ sở đóng mới',
            'delete_shipyard' => 'Xóa cơ sở đóng mới',

            // Permissions for Proposal Management
            'view_proposal' => 'Xem danh sách đề xuất',
            'create_proposal' => 'Tạo đề xuất mới',
            'approve_proposal' => 'Ký duyệt đề xuất',
            'delete_proposal' => 'Xóa đề xuất',

            // Permissions for Inspections
            'view_inspections' => 'Xem đợt đăng kiểm',
            'create_inspections' => 'Thêm đợt đăng kiểm',
            'update_inspections' => 'Cập nhật đợt đăng kiểm',
            'delete_inspections' => 'Xóa đợt đăng kiểm',
            'approve_inspections' => 'Duyệt đợt đăng kiểm',

            // Permissions for CRM
            'view_crm' => 'Xem khách hàng',
            'create_crm' => 'Thêm khách hàng',
            'update_crm' => 'Cập nhật khách hàng',
            'delete_crm' => 'Xóa khách hàng',

            // Permissions for Finance
            'view_finance' => 'Xem tài chính & phí',
            'create_finance' => 'Thêm giao dịch',
            'update_finance' => 'Cập nhật giao dịch',
            'delete_finance' => 'Xóa giao dịch',

            // Permissions for KPI
            'view_kpi'   => 'Xem KPI nhân viên',
            'create_kpi' => 'Thiết lập KPI',
            'update_kpi' => 'Cập nhật KPI',
            'delete_kpi' => 'Xóa KPI',
            'reset_kpi'  => 'Reset KPI nhân viên',

            // Permissions for News
            'view_news' => 'Xem danh sách tin tức',
            'create_news' => 'Tạo tin tức',
            'update_news' => 'Cập nhật tin tức',
            'delete_news' => 'Xóa tin tức',
        ];

        foreach ($permissions as $name => $displayName) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['display_name' => $displayName]
            );
        }

        // Assign to Admin Role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(array_keys($permissions));
        }

        $this->command->info('User permissions seeded successfully.');
    }
}
