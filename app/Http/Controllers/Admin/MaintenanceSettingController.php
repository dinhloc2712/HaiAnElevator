<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCategory;
use App\Models\MaintenanceItem;
use App\Models\MaintenanceStatus;
use Illuminate\Http\Request;

class MaintenanceSettingController extends Controller
{
    public function index()
    {
        $categories = MaintenanceCategory::with('items')->orderBy('sort_order')->get();
        $statuses = MaintenanceStatus::orderBy('sort_order')->get();
        
        return view('admin.maintenance.settings', compact('categories', 'statuses'));
    }

    // Category Methods
    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        
        MaintenanceCategory::create([
            'name' => $request->name,
            'sort_order' => MaintenanceCategory::max('sort_order') + 1
        ]);

        return back()->with('success', 'Đã thêm nhóm hạng mục mới.');
    }

    public function updateCategory(Request $request, MaintenanceCategory $category)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $category->update(['name' => $request->name]);
        return back()->with('success', 'Đã cập nhật tên nhóm.');
    }

    public function destroyCategory(MaintenanceCategory $category)
    {
        $category->delete();
        return back()->with('success', 'Đã xóa nhóm hạng mục.');
    }

    // Item Methods
    public function storeItem(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:maintenance_categories,id',
            'name' => 'required|string|max:255'
        ]);

        MaintenanceItem::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'sort_order' => MaintenanceItem::where('category_id', $request->category_id)->max('sort_order') + 1
        ]);

        return back()->with('success', 'Đã thêm hạng mục kiểm tra mới.');
    }

    public function updateItem(Request $request, MaintenanceItem $item)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $item->update(['name' => $request->name]);
        return back()->with('success', 'Đã cập nhật hạng mục.');
    }

    public function destroyItem(MaintenanceItem $item)
    {
        $item->delete();
        return back()->with('success', 'Đã xóa hạng mục.');
    }

    // Status Methods
    public function storeStatus(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        
        MaintenanceStatus::create([
            'name' => $request->name,
            'sort_order' => MaintenanceStatus::max('sort_order') + 1
        ]);

        return back()->with('success', 'Đã thêm trạng thái mới.');
    }

    public function updateStatus(Request $request, MaintenanceStatus $status)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $status->update(['name' => $request->name]);
        return back()->with('success', 'Đã cập nhật trạng thái.');
    }

    public function destroyStatus(MaintenanceStatus $status)
    {
        $status->delete();
        return back()->with('success', 'Đã xóa trạng thái.');
    }
}
