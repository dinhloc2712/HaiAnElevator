<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_branch');
        $query = Branch::withCount('users');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $branches = $query->latest()->paginate(20)->withQueryString();

        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        $this->authorize('create_branch');
        return view('admin.branches.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create_branch');
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'address'   => 'nullable|string|max:500',
            'phone'     => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Branch::create($validated);

        return redirect()->route('admin.branches.index')->with('success', 'Chi nhánh đã được thêm thành công.');
    }

    public function show(Branch $branch)
    {
        return redirect()->route('admin.branches.edit', $branch);
    }

    public function edit(Branch $branch)
    {
        $this->authorize('update_branch');
        $branch->loadCount('users');
        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $this->authorize('update_branch');
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'address'   => 'nullable|string|max:500',
            'phone'     => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $branch->update($validated);

        return redirect()->route('admin.branches.index')->with('success', 'Cập nhật chi nhánh thành công.');
    }

    public function destroy(Branch $branch)
    {
        $this->authorize('delete_branch');
        if ($branch->users()->count() > 0) {
            return back()->withErrors('Không thể xóa chi nhánh đang có nhân viên.');
        }

        $branch->delete();
        return redirect()->route('admin.branches.index')->with('success', 'Đã xóa chi nhánh.');
    }
}
