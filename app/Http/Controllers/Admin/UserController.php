<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Ship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_user');

        $query = User::with(['role']);

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by Role
        if ($request->has('role_id') && $request->role_id != '') {
            $query->where('role_id', $request->role_id);
        }

        // Filter by Status
        if ($request->has('is_active') && $request->is_active != '') {
            $query->where('is_active', $request->is_active);
        }

        // Sort
        $sortColumn = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['id', 'name', 'email', 'created_at', 'is_active'];
        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, $sortOrder);
        } else {
            $query->latest();
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $users = $query->paginate($perPage)->withQueryString();

        $roles = Role::all();

        return view('admin.users.index', compact('users', 'sortColumn', 'sortOrder', 'roles'));
    }

    public function create()
    {
        $this->authorize('create_user');
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->authorize('create_user');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'street_address' => 'nullable|string|max:255',
            'province_id' => 'nullable|string|max:100',
            'ward_id' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'code' => 'nullable|string|unique:users',
            'start_date' => 'nullable|date',
            // Business Info
            'company_name' => 'nullable|string|max:255',
            'tax_code' => 'nullable|string|max:50',
            'bank_account' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            // Viettel MySign CA Info
            'mysign_client_id'       => 'nullable|string|max:255',
            'mysign_client_secret'   => 'nullable|string|max:255',
            'mysign_profile_id'      => 'nullable|string|max:255',
            'mysign_user_id'         => 'nullable|string|max:255',
            'mysign_credential_id'   => 'nullable|string|max:255',
            'mysign_signature_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active');
        $validated['role_id'] = Role::where('name', 'customer')->value('id') ?? null; // Default to customer if not specified, or null

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'Tài khoản đã được tạo thành công.');
    }

    public function edit(User $user)
    {
        $this->authorize('update_user');
        $roles = Role::all();
        $ships = Ship::orderBy('registration_number')->get();
        $assignedShipIds = $user->managedShips->pluck('id')->toArray();
        return view('admin.users.edit', compact('user', 'roles', 'ships', 'assignedShipIds'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update_user');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'street_address' => 'nullable|string|max:255',
            'province_id' => 'nullable|string|max:100',
            'ward_id' => 'nullable|string|max:100',
            'avatar' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'code' => ['nullable', 'string', Rule::unique('users')->ignore($user->id)],
            'start_date' => 'nullable|date',
            // Business Info
            'company_name' => 'nullable|string|max:255',
            'tax_code' => 'nullable|string|max:50',
            'bank_account' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            // Viettel MySign CA Info
            'mysign_client_id'       => 'nullable|string|max:255',
            'mysign_client_secret'   => 'nullable|string|max:255',
            'mysign_profile_id'      => 'nullable|string|max:255',
            'mysign_user_id'         => 'nullable|string|max:255',
            'mysign_credential_id'   => 'nullable|string|max:255',
            'mysign_signature_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Không update client_secret nếu để trống
        if (!$request->filled('mysign_client_secret')) {
            unset($validated['mysign_client_secret']);
        }

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                $storagePath = str_replace('storage/', '', $user->avatar);
                if (Storage::disk('public')->exists($storagePath)) {
                    Storage::disk('public')->delete($storagePath);
                }
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        if ($request->hasFile('mysign_signature_image')) {
            if ($user->mysign_signature_image) {
                $storagePath = str_replace('storage/', '', $user->mysign_signature_image);
                if (Storage::disk('public')->exists($storagePath)) {
                    Storage::disk('public')->delete($storagePath);
                }
            }
            $path = $request->file('mysign_signature_image')->store('mysign_signatures', 'public');
            $validated['mysign_signature_image'] = $path;
        }

        $user->update($validated);

        // Sync managed ships
        $shipIds = $request->input('ship_ids', []);
        $user->managedShips()->sync($shipIds);

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật tài khoản thành công.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete_user');
        
        if ($user->id === auth()->id()) {
            return back()->withErrors('Bạn không thể xóa chính mình.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Xóa tài khoản thành công.');
    }

    public function updateRole(Request $request, User $user)
    {
        $this->authorize('assign_role');
        $request->validate(['role_id' => 'required|exists:roles,id']);
        $user->update(['role_id' => $request->role_id]);
        
        return back()->with('success', 'Cập nhật chức vụ thành công.');
    }

    /**
     * Universal fallback to serve any public storage file (avatars, uploads, etc) 
     * on shared hosting where storage:link fails to create a symlink.
     */
    public function servePublicStorageFile($path)
    {
        // 1. Basic security against directory traversal
        $path = str_replace(['..', '\\'], ['', '/'], $path);
        
        $fullPath = storage_path('app/public/' . $path);
        
        if (!file_exists($fullPath) || is_dir($fullPath)) {
            abort(404);
        }

        // 2. Fetch MIME type
        $mimeType = mime_content_type($fullPath);
        
        // Use a generic octet-stream if PHP cant guess it
        if (!$mimeType) {
            $mimeType = 'application/octet-stream';
        }

        $headers = [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=86400', // Cache for 1 day
        ];

        return response()->file($fullPath, $headers);
    }
}
