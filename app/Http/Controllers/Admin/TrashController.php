<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Elevator;
use App\Models\Installation;
use App\Models\Incident;
use App\Models\User;
use App\Models\Branch;
use App\Models\MaintenanceCheck;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    protected $models = [
        'buildings' => Building::class,
        'elevators' => Elevator::class,
        'installations' => Installation::class,
        'incidents' => Incident::class,
        'users' => User::class,
        'branches' => Branch::class,
        'maintenance_checks' => MaintenanceCheck::class,
    ];

    public function index(Request $request)
    {
        $this->authorize('view_user'); // Assuming admin/manager level

        $trashedData = [];
        foreach ($this->models as $key => $modelClass) {
            $trashedData[$key] = $modelClass::onlyTrashed()->latest('deleted_at')->get();
        }

        return view('admin.trash.index', compact('trashedData'));
    }

    public function restore($type, $id)
    {
        $this->authorize('update_user');

        if (!isset($this->models[$type])) {
            return back()->with('error', 'Loại dữ liệu không hợp lệ.');
        }

        $modelClass = $this->models[$type];
        $item = $modelClass::onlyTrashed()->findOrFail($id);
        $item->restore();

        return back()->with('success', 'Đã khôi phục dữ liệu thành công.');
    }

    public function forceDelete($type, $id)
    {
        $this->authorize('delete_user');

        if (!isset($this->models[$type])) {
            return back()->with('error', 'Loại dữ liệu không hợp lệ.');
        }

        $modelClass = $this->models[$type];
        $item = $modelClass::onlyTrashed()->findOrFail($id);
        $item->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn dữ liệu.');
    }
}
