<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Document;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_media');
        
        $folder = current(explode('?', $request->input('folder', '/')));
        if (str_contains($folder, '..')) abort(403);
        $diskPath = $folder === '/' ? '' : $folder;

        $files = [];
        
        // Add "Up" directory
        if ($folder !== '/' && $folder !== '') {
            $parent = dirname($folder);
            if ($parent === '\\' || $parent === '.') $parent = '/';
            $files[] = [
                'name'          => '.. (Quay lại)',
                'path'          => $parent,
                'is_dir'        => true,
                'size'          => 0,
                'last_modified' => 0,
                'mime_type'     => 'folder',
                'url'           => route('admin.media.index', ['folder' => $parent]),
            ];
        }

        $directories = Storage::disk('private')->directories($diskPath);
        foreach ($directories as $dir) {
            $files[] = [
                'name'          => basename($dir),
                'path'          => $dir,
                'is_dir'        => true,
                'size'          => 0,
                'last_modified' => Storage::disk('private')->lastModified($dir),
                'mime_type'     => 'folder',
                'url'           => route('admin.media.index', ['folder' => $dir]),
            ];
        }

        $allFiles = Storage::disk('private')->files($diskPath);
        
        // Truy vấn DB lấy thông tin bổ sung cho các file
        $filePathsForDb = array_map(function($f) { return $f; }, $allFiles);
        $documentsDb = Document::whereIn('file_path', $filePathsForDb)->get()->keyBy('file_path');

        foreach ($allFiles as $file) {
            if (basename($file) === '.gitignore') continue;

            $doc = $documentsDb->get($file);

            $files[] = [
                'name'          => basename($file),
                'path'          => $file,
                'is_dir'        => false,
                'size'          => Storage::disk('private')->size($file),
                'last_modified' => Storage::disk('private')->lastModified($file),
                'mime_type'     => Storage::mimeType('private/' . $file),
                'url'           => route('admin.media.serve', ['filename' => $file]),
                'document'      => $doc,
            ];
        }

        // Sort: Up dir first -> then directories -> then files by last modified desc
        usort($files, function($a, $b) {
            if ($a['name'] === '.. (Quay lại)') return -1;
            if ($b['name'] === '.. (Quay lại)') return 1;
            if ($a['is_dir'] !== $b['is_dir']) return $a['is_dir'] ? -1 : 1;
            return $b['last_modified'] <=> $a['last_modified'];
        });

        $search = $request->input('search');
        if ($search) {
            $files = array_filter($files, function($file) use ($search) {
                if ($file['name'] === '.. (Quay lại)') return true;
                return stripos($file['name'], $search) !== false;
            });
        }

        $type = $request->input('type');
        if ($type && $type !== 'all') {
            $files = array_filter($files, function($file) use ($type) {
                if ($file['is_dir']) return true; // keep folders when filtering types
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                switch ($type) {
                    case 'word': return in_array($ext, ['doc', 'docx']);
                    case 'pdf': return in_array($ext, ['pdf']);
                    case 'excel': return in_array($ext, ['xls', 'xlsx', 'csv']);
                    case 'image': return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    case 'video': return in_array($ext, ['mp4', 'mov', 'avi']);
                    case 'archive': return in_array($ext, ['zip', 'rar', '7z']);
                    default: return true;
                }
            });
        }

        $perPage = $request->input('per_page', 20);
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($files, ($currentPage - 1) * $perPage, $perPage);
        $paginatedFiles = new LengthAwarePaginator($currentItems, count($files), $perPage, $currentPage, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);

        return view('admin.media.index', [
            'files' => $paginatedFiles, 
            'search' => $search, 
            'type' => $type,
            'currentFolder' => $folder,
            'documentTypes' => Document::$types
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create_media');
        
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240', // Max 10MB per file
            'folder' => 'nullable|string',
            'document_type' => 'required|string',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $folder = $request->input('folder', '/');
        if (str_contains($folder, '..')) abort(403);
        $diskPath = $folder === '/' ? '' : $folder;

        if ($request->hasFile('files')) {
            $count = 0;
            foreach ($request->file('files') as $file) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $cleanName = \Str::slug($originalName) . '.' . $extension;
                
                $filename = time() . '_' . $count . '_' . $cleanName;
                
                $path = $file->storeAs($diskPath, $filename, 'private');
                
                Document::create([
                    'uploaded_by' => auth()->id(),
                    'document_type' => $request->input('document_type'),
                    'title' => $request->input('title') ?: $originalName,
                    'file_path' => $path,
                    'file_name' => $originalName . '.' . $extension,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'notes' => $request->input('notes'),
                ]);

                $count++;
            }
            
            return redirect()->route('admin.media.index', ['folder' => $folder])->with('success', 'Tải lên ' . $count . ' tập tin thành công.');
        }

        return back()->withErrors('Vui lòng chọn ít nhất một tập tin.');
    }

    public function mapFile(Request $request)
    {
        if (!auth()->user()->can('create_media') && !auth()->user()->can('update_media')) {
            abort(403, 'Bạn không có quyền này.');
        }

        $request->validate([
            'file_path' => 'required|string',
            'document_type' => 'required|string',
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $filePath = $request->input('file_path');

        if (str_contains($filePath, '..')) abort(403);

        if (!Storage::disk('private')->exists($filePath)) {
            return back()->withErrors('Không tìm thấy tệp tin trên hệ thống.');
        }

        $size = Storage::disk('private')->size($filePath);
        $mimeType = Storage::mimeType('private/' . $filePath);
        $fileName = basename($filePath);

        Document::updateOrCreate(
            ['file_path' => $filePath],
            [
                'uploaded_by' => auth()->id(),
                'document_type' => $request->input('document_type'),
                'title' => $request->input('title'),
                'file_name' => $fileName,
                'file_size' => $size,
                'mime_type' => $mimeType,
                'notes' => $request->input('notes'),
            ]
        );

        return back()->with('success', 'Định danh/Cập nhật thông tin tệp tin thành công.');
    }

    public function createFolder(Request $request)
    {
        $this->authorize('create_media');

        $request->validate([
            'folder_name' => 'required|string|max:255',
            'current_folder' => 'nullable|string',
        ]);

        $currentFolder = $request->input('current_folder', '/');
        if (str_contains($currentFolder, '..')) abort(403);
        
        $folderName = \Str::slug($request->input('folder_name'));
        $diskPath = ($currentFolder === '/' ? '' : $currentFolder . '/') . $folderName;

        if (!Storage::disk('private')->exists($diskPath)) {
            Storage::disk('private')->makeDirectory($diskPath);
            return redirect()->route('admin.media.index', ['folder' => $currentFolder])->with('success', 'Tạo thư mục thành công.');
        }

        return back()->withErrors('Thư mục đã tồn tại.');
    }

    public function destroy(Request $request, $filename)
    {
        $this->authorize('delete_media');
        
        if (str_contains($filename, '..')) abort(403);

        $isFolder = $request->input('is_folder', false);

        if ($isFolder) {
            if (Storage::disk('private')->exists($filename)) {
                Storage::disk('private')->deleteDirectory($filename);
                // Xoá luôn các records trong DB có đường dẫn bắt đầu bằng thư mục này
                Document::where('file_path', 'like', $filename . '/%')->delete();
                return back()->with('success', 'Xóa thư mục thành công.');
            }
        } else {
            if (Storage::disk('private')->exists($filename)) {
                Storage::disk('private')->delete($filename);
                Document::where('file_path', $filename)->delete();
                return back()->with('success', 'Xóa tập tin thành công.');
            }
        }

        return back()->withErrors('Không tìm thấy mục cần xóa.');
    }

    public function bulkDestroy(Request $request)
    {
        $this->authorize('delete_media');
        
        $paths = $request->input('paths', []);
        
        if (empty($paths)) {
            return back()->withErrors('Không có mục nào được chọn để xóa.');
        }

        $deletedCount = 0;

        foreach ($paths as $path) {
            if (str_contains($path, '..')) continue; // Skip invalid paths

            // Kiểm tra xem nó là thư mục hay tệp tin
            $isFolder = false;
            if (Storage::disk('private')->exists($path)) {
                // Determine if it's a directory by checking if it exists in the directories list
                $parentDir = dirname($path);
                if ($parentDir === '\\' || $parentDir === '.') $parentDir = '';
                $directories = Storage::disk('private')->directories($parentDir);
                $isFolder = in_array($path, $directories);
            }

            if ($isFolder) {
                 if (Storage::disk('private')->exists($path)) {
                    Storage::disk('private')->deleteDirectory($path);
                    Document::where('file_path', 'like', $path . '/%')->orWhere('file_path', $path)->delete();
                    $deletedCount++;
                 }
            } else {
                 if (Storage::disk('private')->exists($path)) {
                    Storage::disk('private')->delete($path);
                    Document::where('file_path', $path)->delete();
                    $deletedCount++;
                 }
            }
        }

        return back()->with('success', "Đã xóa thành công $deletedCount mục.");
    }

    public function moveItems(Request $request)
    {
        $this->authorize('update_media');

        $paths = $request->input('paths', []);
        $destination = $request->input('destination_folder', '');

        if (str_contains($destination, '..')) abort(403);
        if (empty($paths)) {
            return back()->withErrors('Không có tệp/thư mục nào được chọn để di chuyển.');
        }

        // Định dạng destination
        $diskDestination = $destination === '/' ? '' : $destination;
        
        // Nếu destination không tồn tại và không phải thư mục gốc,
        // (tuy nhiên thường ta chỉ dán vào thư mục đang đứng đã tồn tại)
        if ($diskDestination !== '' && !Storage::disk('private')->exists($diskDestination)) {
            return back()->withErrors('Thư mục đích không tồn tại.');
        }

        $movedCount = 0;
        
        foreach ($paths as $sourcePath) {
            if (str_contains($sourcePath, '..')) continue;

            if (!Storage::disk('private')->exists($sourcePath)) continue;

            $itemName = basename($sourcePath);
            $newPath = $diskDestination !== '' ? $diskDestination . '/' . $itemName : $itemName;

            // Xử lý trùng tên
            if (Storage::disk('private')->exists($newPath)) {
                $ext = pathinfo($itemName, PATHINFO_EXTENSION);
                $nameWithoutExt = pathinfo($itemName, PATHINFO_FILENAME);
                $newPath = $diskDestination !== '' 
                    ? $diskDestination . '/' . $nameWithoutExt . '_' . time() . '.' . $ext 
                    : $nameWithoutExt . '_' . time() . '.' . $ext;
            }

            // Di chuyển vật lý
            Storage::disk('private')->move($sourcePath, $newPath);

            // Cập nhật DB (Document table)
            // Nếu là tệp tin:
            $doc = Document::where('file_path', $sourcePath)->first();
            if ($doc) {
                $doc->update(['file_path' => $newPath]);
            }

            // Nếu là thư mục: phải cập nhật tất cả file bên trong (rất hiếm khi xảy ra vì Document map thẳng file, không map folder)
            // Tuy nhiên phòng lúc query, ta update các file path con
            $subDocs = Document::where('file_path', 'like', $sourcePath . '/%')->get();
            foreach ($subDocs as $subDoc) {
                $newSubPath = str_replace($sourcePath . '/', $newPath . '/', $subDoc->file_path);
                $subDoc->update(['file_path' => $newSubPath]);
            }

            $movedCount++;
        }

        return back()->with('success', "Đã di chuyển thành công $movedCount mục.");
    }

    public function copyItems(Request $request)
    {
        $this->authorize('create_media');

        $paths = $request->input('paths', []);
        $destination = $request->input('destination_folder', '');

        if (str_contains($destination, '..')) abort(403);
        if (empty($paths)) {
            return back()->withErrors('Không có tệp/thư mục nào được chọn để sao chép.');
        }

        $diskDestination = $destination === '/' ? '' : $destination;
        
        if ($diskDestination !== '' && !Storage::disk('private')->exists($diskDestination)) {
            return back()->withErrors('Thư mục đích không tồn tại.');
        }

        $copiedCount = 0;
        
        foreach ($paths as $sourcePath) {
            if (str_contains($sourcePath, '..')) continue;

            if (!Storage::disk('private')->exists($sourcePath)) continue;

            $itemName = basename($sourcePath);
            $newPath = $diskDestination !== '' ? $diskDestination . '/' . $itemName : $itemName;

            // Xử lý trùng tên
            $counter = 1;
            $ext = pathinfo($itemName, PATHINFO_EXTENSION);
            $nameWithoutExt = pathinfo($itemName, PATHINFO_FILENAME);
            $baseNewPath = $newPath;
            
            while (Storage::disk('private')->exists($newPath)) {
                $newName = $nameWithoutExt . ' - Copy (' . $counter . ').' . $ext;
                $newPath = $diskDestination !== '' ? $diskDestination . '/' . $newName : $newName;
                $counter++;
            }

            // Sao chép vật lý
            Storage::disk('private')->copy($sourcePath, $newPath);

            // Cập nhật DB (Document table)
            // Nếu là tệp tin:
            $doc = Document::where('file_path', $sourcePath)->first();
            if ($doc) {
                Document::create([
                    'uploaded_by' => auth()->id(),
                    'document_type' => $doc->document_type,
                    'title' => $doc->title . ' (Bản sao)',
                    'file_path' => $newPath,
                    'file_name' => basename($newPath),
                    'file_size' => $doc->file_size,
                    'mime_type' => $doc->mime_type,
                    'notes' => $doc->notes,
                ]);
            }

            // Copy folder logic is more complex to maintain Document mappings cleanly across deep trees.
            // Assuming flat files mostly for now as per usual mapFile usage.
            // Can expand recursively if needed, but standard file copy is handled here.
            $copiedCount++;
        }

        return back()->with('success', "Đã sao chép thành công $copiedCount mục.");
    }

    public function serve($filename)
    {
        // Check permission strictly here
        $this->authorize('view_media');
        
        if (!Storage::disk('private')->exists($filename)) {
            abort(404);
        }

        $path = storage_path('app/private/' . $filename);
        $mimeType = Storage::mimeType('private/' . $filename);

        return Response::file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($filename) . '"',
        ]);
    }

    /**
     * Serve a file publicly (no auth required — used for QR code links).
     */
    public function publicServe($filename)
    {
        if (str_contains($filename, '..')) abort(403);

        if (!Storage::disk('private')->exists($filename)) {
            abort(404);
        }

        $path = storage_path('app/private/' . $filename);
        $mimeType = Storage::mimeType('private/' . $filename);

        return Response::file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($filename) . '"',
        ]);
    }

    public function showGenerateForm($filename)
    {
        $this->authorize('view_media');

        if (!Storage::disk('private')->exists($filename)) {
            abort(404);
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($extension, ['doc', 'docx'])) {
            return back()->with('error', 'Chỉ hỗ trợ tạo biểu mẫu cho file Word (.doc, .docx)');
        }

        $path = storage_path('app/private/' . $filename);
        
        try {
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($path);
            $variables = $templateProcessor->getVariables();
            // deduplicate variables
            $variables = array_values(array_unique($variables));
        } catch (\Exception $e) {
            return back()->with('error', 'Không thể đọc file mẫu: ' . $e->getMessage());
        }

        $templateConfig = \App\Models\TemplateConfig::where('filename', $filename)->first();
        $savedConfig = $templateConfig ? $templateConfig->config : null;

        return view('admin.media.show', compact('filename', 'variables', 'savedConfig'));
    }

    public function saveConfig(Request $request, $filename)
    {
        $this->authorize('view_media');

        $config = $request->input('config', []);
        
        \App\Models\TemplateConfig::updateOrCreate(
            ['filename' => $filename],
            ['config' => $config]
        );

        return response()->json(['success' => true]);
    }

    public function generateDocument(Request $request, $filename)
    {
        $this->authorize('view_media');

        if (!Storage::disk('private')->exists($filename)) {
            abort(404);
        }

        $path = storage_path('app/private/' . $filename);
        
        try {
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($path);
            
            // Lấy danh sách biến gốc từ template
            $originalVars = $templateProcessor->getVariables();
            $originalVars = array_values(array_unique($originalVars));

            // Map request data back to original variable names
            // PHP/trình duyệt tự đổi khoảng trắng/dấu chấm trong thuộc tính name thành '_'
            $mappedData = [];
            foreach ($originalVars as $varName) {
                $formFieldName = str_replace([' ', '.'], '_', $varName);
                if ($request->has($formFieldName)) {
                    $mappedData[$varName] = $request->input($formFieldName);
                }
            }
            
            $tableMode = $request->input('table_mode', false);
            
            if ($tableMode) {
                $tableAnchor = $request->input('table_anchor');
                $tableCols = $request->input('table_cols', []);
                
                $rowCount = 0;
                // Find how many rows we have based on array inputs
                foreach ($tableCols as $col) {
                    $formColName = str_replace([' ', '.'], '_', $col);
                    if (is_array($request->input($formColName))) {
                        $rowCount = max($rowCount, count($request->input($formColName)));
                    }
                }

                $tableData = [];
                for ($i = 0; $i < $rowCount; $i++) {
                    $row = [];
                    foreach ($tableCols as $col) {
                        $formColName = str_replace([' ', '.'], '_', $col);
                        $colData = $request->input($formColName);
                        $row[$col] = is_array($colData) ? ($colData[$i] ?? '') : '';
                        
                        // Unset from mappedData so we don't process it below
                        if (isset($mappedData[$col])) unset($mappedData[$col]);
                    }
                    $tableData[] = $row;
                }

                if ($tableAnchor && !empty($tableData)) {
                    // Try to clone row, but if anchor is not found in table, it might throw exception.
                    $templateProcessor->cloneRowAndSetValues($tableAnchor, $tableData);
                }
            } 
            
            // Handle normal mapping with ORIGINAL variable names (preserving spaces)
            foreach ($mappedData as $key => $value) {
                if (!is_array($value)) {
                    $templateProcessor->setValue($key, $value ?? '');
                }
            }
            
            $cleanName = pathinfo($filename, PATHINFO_FILENAME);
            $outputFilename = 'Generated_' . time() . '_' . $cleanName . '.docx';
            $tempDir = storage_path('app/temp');
            $tempPath = $tempDir . '/' . $outputFilename;
            
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            $templateProcessor->saveAs($tempPath);
            
            return Response::download($tempPath, $outputFilename)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi tạo tài liệu: ' . $e->getMessage());
        }
    }
}
