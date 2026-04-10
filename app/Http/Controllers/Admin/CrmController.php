<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ship;
use Illuminate\Support\Facades\DB;

class CrmController extends Controller
{
    /**
     * Display the CRM Ship list
     */
    public function index(Request $request)
    {
        $this->authorize('view_crm');
        
        $query = Ship::query();
        $search = $request->input('search');

        // Basic Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('owner_name', 'like', "%{$search}%")
                  ->orWhere('hull_number', 'like', "%{$search}%");
            });
        }

        // Basic Filters (Quick tabs)
        if ($request->filled('filter')) {
            $filter = $request->filter;
            $today = \Carbon\Carbon::now()->startOfDay();

            if ($filter === 'expired') {
                $query->whereNotNull('expiration_date')->where('expiration_date', '<', $today);
            } elseif ($filter === 'expiring') {
                $query->whereNotNull('expiration_date')->whereBetween('expiration_date', [$today, $today->copy()->addDays(30)]);
            } elseif ($filter === 'valid') {
                $query->whereNotNull('expiration_date')->where('expiration_date', '>', $today->copy()->addDays(30));
            }
        }

        // Advanced Filters (adv_*)
        if ($request->filled('adv_registration')) {
            $query->where('registration_number', 'like', '%' . $request->adv_registration . '%');
        }
        if ($request->filled('adv_owner')) {
            $query->where(function($q) use ($request) {
                $q->where('owner_name', 'like', '%' . $request->adv_owner . '%')
                  ->orWhere('owner_phone', 'like', '%' . $request->adv_owner . '%')
                  ->orWhereHas('user', function($u) use ($request) {
                      $u->where('email', 'like', '%' . $request->adv_owner . '%');
                  });
            });
        }
        if ($request->filled('adv_status')) {
            if ($request->adv_status === 'processing') {
                $query->whereHas('inspections', fn($q) => $q->where('status', 'in_progress'));
            } else {
                $query->where('status', $request->adv_status);
            }
        }
        if ($request->filled('adv_expiration')) {
            $now = now();
            if ($request->adv_expiration === 'expired') {
                $query->whereNotNull('expiration_date')->where('expiration_date', '<', $now->startOfDay());
            } elseif ($request->adv_expiration === 'expiring_soon') {
                $query->whereNotNull('expiration_date')->whereBetween('expiration_date', [$now->startOfDay(), now()->addDays(30)]);
            } elseif ($request->adv_expiration === 'valid') {
                $query->whereNotNull('expiration_date')->where('expiration_date', '>', now()->addDays(30));
            }
        }
        if ($request->filled('adv_usage')) {
            $query->where('usage', 'like', '%' . $request->adv_usage . '%');
        }

        $perPage = $request->input('per_page', 20);
        $ships = $query->paginate($perPage)->withQueryString();

        return view('admin.crm.index', compact('ships', 'search'));
    }

    /**
     * Get details of ships belonging to a specific customer phone (API - kept for backwards compat)
     */
    public function getCustomerShips($phone = null)
    {
        $this->authorize('view_crm');
        if (!$phone) {
            return response()->json(['success' => false, 'message' => 'Lỗi số điện thoại'], 400);
        }

        $ships = Ship::where('owner_phone', $phone)
            ->with(['inspections' => function($q) {
                $q->latest('inspection_date')->limit(1);
            }])
            ->get();

        $formattedShips = $ships->map(function ($ship) {
            $latestInspection = $ship->inspections->first();
            
            return [
                'id' => $ship->id,
                'registration_number' => $ship->registration_number,
                'name' => $ship->name,
                'usage' => $ship->usage,
                'expiration_date' => $ship->expiration_date ? $ship->expiration_date->format('Y-m-d') : null,
                'fee_amount' => $latestInspection ? (int)$latestInspection->fee_amount : 0,
                'inspection_status' => $latestInspection ? $latestInspection->status : 'none',
                'inspection_result' => $latestInspection ? $latestInspection->result : null,
                'latest_inspection_date' => $latestInspection ? $latestInspection->inspection_date->format('Y-m-d') : null,
            ];
        });

        return response()->json([
            'success' => true,
            'ships' => $formattedShips
        ]);
    }
}
