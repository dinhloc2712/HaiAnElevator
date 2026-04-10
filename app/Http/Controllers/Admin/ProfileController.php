<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        return view('admin.profile.show', compact('user'));
    }

    public function edit()
    {
        $user = auth()->user();
        return view('admin.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'street_address' => 'nullable|string|max:255',
            'province_id' => 'nullable|string|max:10',
            'ward_id' => 'nullable|string|max:10',
            'bank_account' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:2048',
            'current_password' => 'nullable|required_with:new_password|current_password',
            'new_password' => 'nullable|string|min:6|confirmed',
        ]);

        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                $storagePath = str_replace('storage/', '', $user->avatar);
                if (Storage::disk('public')->exists($storagePath)) {
                    Storage::disk('public')->delete($storagePath);
                }
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        // Update Basic Info
        $user->name = $validated['name'];
        $user->phone = $validated['phone'];
        $user->street_address = $validated['street_address'] ?? null;
        $user->province_id = $validated['province_id'] ?? null;
        $user->ward_id = $validated['ward_id'] ?? null;
        $user->bank_account = $validated['bank_account'] ?? null;
        $user->bank_name = $validated['bank_name'] ?? null;

        // Handle Password Change
        if ($request->filled('new_password')) {
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();

        return redirect()->route('admin.profile.show')->with('success', 'Hồ sơ cá nhân đã được cập nhật.');
    }
}
