<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_role');
        
        $query = Role::withCount('users');

         // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortColumn = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'asc');
        
        $allowedSorts = ['id', 'name', 'display_name', 'users_count']; 
        
        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, $sortOrder);
        } else {
             $query->orderBy('id', 'asc');
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $roles = $query->paginate($perPage)->withQueryString();

        return view('admin.roles.index', compact('roles', 'sortColumn', 'sortOrder'));
    }

    public function create()
    {
        $this->authorize('create_role');
        return view('admin.roles.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create_role');

        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:roles',
            'display_name' => 'required|string|max:100',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'guard_name' => 'web'
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Thêm chức vụ thành công.');
    }

    public function edit($id)
    {
        $this->authorize('update_role');
        $role = Role::with('permissions')->findOrFail($id);
        // We don't need to pass all permissions here because the view fetches them dynamically based on modules
        // But for standard implementation usually we pass them. 
        // In GiaBao's view, it queries Permission model directly in the view loop.
        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update_role');
        $role = Role::findOrFail($id);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('roles')->ignore($role->id)],
            'display_name' => 'required|string|max:100',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Prevent changing name of system roles
        if (in_array($role->name, ['admin', 'employee', 'customer'])) {
             if ($role->name !== $validated['name']) {
                 return back()->withErrors(['name' => 'Không thể thay đổi mã của chức vụ mặc định.']);
             }
        }

        $role->update([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
        ]);
        
        // Sync permissions
        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        } else {
            $role->permissions()->detach();
        }

        return redirect()->route('admin.roles.index')->with('success', 'Cập nhật chức vụ và quyền hạn thành công.');
    }

    public function destroy($id)
    {
        $this->authorize('delete_role');
        $role = Role::findOrFail($id);
        
        // Prevent deleting critical roles
        if (in_array($role->name, ['admin', 'employee', 'customer'])) {
            return redirect()->route('admin.roles.index')->with('error', 'Không thể xóa các chức vụ mặc định của hệ thống.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')->with('error', 'Chức vụ này đang có người dùng, không thể xóa.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Xóa chức vụ thành công.');
    }
}
