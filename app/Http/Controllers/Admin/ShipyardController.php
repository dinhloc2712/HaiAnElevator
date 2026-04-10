<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipyard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShipyardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view_shipyard');

        $query = Shipyard::query();

        if ($request->has('search') && $request->input('search') != '') {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('owner_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->input('status') != '') {
            $query->where('status', $request->input('status'));
        }

        $perPage = $request->input('per_page', 20);
        $shipyards = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        return view('admin.shipyards.index', compact('shipyards'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create_shipyard');
        return view('admin.shipyards.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create_shipyard');

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'owner_name'     => 'required|string|max:255',
            'owner_id_card'  => 'nullable|string|max:50',
            'phone'          => 'nullable|string|max:50',
            'address'        => 'nullable|string|max:255',
            'province_id'    => 'nullable|string|max:50',
            'ward_id'        => 'nullable|string|max:50',
            'status'         => 'required|in:active,inactive',
            'license_number' => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
            'files.*'        => 'nullable|file', // For array of uploaded files
        ]);

        $shipyard = Shipyard::create($validated);

        if ($request->hasFile('files')) {
            $this->handleFileUploads($request, $shipyard);
        }

        return redirect()->route('admin.shipyards.index')->with('success', 'Đã thêm cơ sở đóng mới thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Shipyard $shipyard)
    {
        $this->authorize('view_shipyard');
        return view('admin.shipyards.show', compact('shipyard'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shipyard $shipyard)
    {
        $this->authorize('update_shipyard');
        return view('admin.shipyards.edit', compact('shipyard'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shipyard $shipyard)
    {
        $this->authorize('update_shipyard');

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'owner_name'     => 'required|string|max:255',
            'owner_id_card'  => 'nullable|string|max:50',
            'phone'          => 'nullable|string|max:50',
            'address'        => 'nullable|string|max:255',
            'province_id'    => 'nullable|string|max:50',
            'ward_id'        => 'nullable|string|max:50',
            'status'         => 'required|in:active,inactive',
            'license_number' => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
        ]);

        $shipyard->update($validated);

        return redirect()->route('admin.shipyards.index')->with('success', 'Đã cập nhật thông tin cơ sở đóng mới.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shipyard $shipyard)
    {
        $this->authorize('delete_shipyard');

        // Delete attached files if any
        $files = $shipyard->files ?? [];
        foreach ($files as $file) {
            Storage::disk('private')->delete($file['path']);
        }

        $shipyard->delete();

        return redirect()->route('admin.shipyards.index')->with('success', 'Đã xóa cơ sở đóng mới thành công.');
    }

    /**
     * Internal method to handle multiple file uploads
     */
    private function handleFileUploads(Request $request, Shipyard $shipyard)
    {
        $existingFiles = is_array($shipyard->files) ? $shipyard->files : [];
        
        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $filename     = time() . '_' . Str::random(5) . '_' . $originalName;
            
            // store securely in private disk
            $path = $file->storeAs('shipyards/' . $shipyard->id, $filename, 'private');

            $existingFiles[] = [
                'filename' => $originalName,
                'path'     => $path,
                'url'      => route('admin.media.serve', ['filename' => $path]),
            ];
        }

        $shipyard->files = $existingFiles;
        $shipyard->save();
    }

    /**
     * Upload additional files to an existing shipyard via AJAX
     */
    public function uploadFile(Request $request, Shipyard $shipyard)
    {
        $this->authorize('update_shipyard');

        $request->validate([
            'files.*' => 'required|file',
        ]);

        if ($request->hasFile('files')) {
            $this->handleFileUploads($request, $shipyard);
            return response()->json(['success' => true, 'message' => 'Tải lên thành công!', 'files' => $shipyard->files]);
        }

        return response()->json(['success' => false, 'message' => 'Không có file nào được tải lên.'], 400);
    }

    /**
     * Delete a specific file from a shipyard via AJAX
     */
    public function deleteFile(Request $request, Shipyard $shipyard)
    {
        $this->authorize('update_shipyard');

        $pathToDelete = $request->input('path');
        
        if (!$pathToDelete) {
            return response()->json(['success' => false, 'message' => 'Thiếu đường dẫn file.'], 400);
        }

        $currentFiles = is_array($shipyard->files) ? $shipyard->files : [];
        
        // Filter out the file to delete
        $updatedFiles = array_filter($currentFiles, function ($file) use ($pathToDelete) {
            return $file['path'] !== $pathToDelete;
        });

        if (count($currentFiles) === count($updatedFiles)) {
             return response()->json(['success' => false, 'message' => 'Không tìm thấy file.'], 404);
        }

        // Delete from storage
        Storage::disk('private')->delete($pathToDelete);

        // Update database
        $shipyard->files = array_values($updatedFiles); // reindex array
        $shipyard->save();

        return response()->json(['success' => true, 'message' => 'Đã xóa file.']);
    }
}
