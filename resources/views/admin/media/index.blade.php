@extends('layouts.admin')

@section('title', 'Quản lý Tài liệu An toàn')

@section('content')
    {{-- Breadcrumb Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">Quản lý Tài liệu An toàn</h1>
            <p class="mb-0 text-muted small">Thư mục hiện tại:
                <strong>{{ $currentFolder === '/' || $currentFolder === '' ? 'Gốc' : $currentFolder }}</strong>
            </p>
        </div>
    </div>

    <div class="row">
        {{-- Upload Card --}}
        <div class="col-lg-4 mb-4">
            <div class="tech-card mb-4">
                <div class="tech-header"
                    style="background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%); padding: 20px 25px;">
                    <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
                        <i class="fas fa-cloud-upload-alt me-2 bg-white bg-opacity-25 rounded-circle p-2"
                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"></i>
                        Tải lên Tệp tin
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data"
                        id="upload-form">
                        @csrf
                        <input type="hidden" name="folder" value="{{ $currentFolder }}">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Tiêu đề tệp (tuỳ chọn)</label>
                            <input type="text" name="title" class="form-control"
                                placeholder="Tên gợi nhớ cho tài liệu...">
                            <div class="form-text small">Nếu để trống sẽ sử dụng tên gốc của tệp.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Loại tài liệu <span
                                    class="text-danger">*</span></label>
                            <select name="document_type" class="form-select" required>
                                @foreach ($documentTypes ?? [] as $key => $label)
                                    <option value="{{ $key }}" {{ $key === 'other' ? 'selected' : '' }}>
                                        {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Ghi chú (tuỳ chọn)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Thêm ghi chú..."></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="files" class="form-label fw-bold small text-uppercase text-muted">Chọn tệp tin
                                <span class="text-danger">*</span></label>
                            <div class="upload-area p-5 text-center border rounded-3 bg-light position-relative"
                                id="drop-zone"
                                style="border: 2px dashed #cbd5e1 !important; cursor: pointer; transition: all 0.2s;">
                                <i class="fas fa-cloud-arrow-up fa-3x text-secondary mb-3 opacity-50" id="upload-icon"></i>
                                <p class="mb-1 text-dark fw-bold" id="upload-text">Nhấn để chọn hoặc kéo thả tệp vào đây</p>
                                <p class="small text-muted mb-0" id="upload-subtext">Hỗ trợ tải nhiều tệp cùng lúc</p>
                                <input class="form-control d-none" type="file" id="files" name="files[]" multiple
                                    required onchange="handleFiles(this.files)">
                            </div>
                            <div id="file-list-display" class="mt-3 small"></div>
                            <div class="form-text small text-center mt-2">Hỗ trợ tất cả định dạng. Tối đa 10MB/tệp.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm" id="submit-btn"
                            disabled>
                            <i class="fas fa-upload me-1"></i> Tải lên ngay
                        </button>
                    </form>
                </div>
            </div>

            @can('create_media')
                <div class="tech-card">
                    <div class="tech-header"
                        style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); padding: 20px 25px;">
                        <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
                            <i class="fas fa-folder-plus me-2 bg-white bg-opacity-25 rounded-circle p-2"
                                style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"></i>
                            Tạo Thư Mục Mới
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.media.create-folder') }}" method="POST">
                            @csrf
                            <input type="hidden" name="current_folder" value="{{ $currentFolder }}">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Tên thư mục</label>
                                <input type="text" name="folder_name" class="form-control"
                                    placeholder="Nhập tên thư mục mới..." required>
                            </div>
                            <button type="submit" class="btn btn-warning text-white w-100 fw-bold shadow-sm">
                                <i class="fas fa-plus me-1"></i> Tạo thư mục
                            </button>
                        </form>
                    </div>
                </div>
            @endcan
        </div>

        {{-- File List --}}
        <div class="col-lg-8 mb-4">
            <div class="tech-card h-100">
                <div class="tech-header"
                    style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); padding: 20px 25px;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
                                <i class="fas fa-folder-open me-2 bg-white bg-opacity-25 rounded-circle p-2"
                                    style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"></i>
                            </h6>
                            {{-- Paste Button (Hidden by default, shown via JS) --}}
                            <button type="button" id="btn-paste-items"
                                class="btn btn-sm btn-light text-primary fw-bold ms-3 d-none rounded-pill shadow-sm"
                                onclick="pasteItems()">
                                <i class="fas fa-paste me-1"></i> Dán vào thư mục này
                            </button>
                        </div>

                        <form method="GET" action="{{ route('admin.media.index') }}"
                            class="d-flex align-items-center flex-wrap gap-2">
                            {{-- Per Page --}}
                            <div class="d-flex align-items-center bg-white rounded-pill px-3 py-2 shadow-sm">
                                <small class="text-muted fw-bold me-2 text-uppercase" style="font-size: 0.65rem;">Hiển
                                    thị</small>
                                <select name="per_page"
                                    class="form-select form-select-sm border-0 bg-transparent fw-bold text-dark py-0 pe-4"
                                    style="width: auto; box-shadow: none; cursor: pointer;" onchange="this.form.submit()">
                                    <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20
                                    </option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>

                            {{-- Type Filter --}}
                            <div class="d-flex align-items-center bg-white rounded-pill px-3 py-2 shadow-sm">
                                <small class="text-muted fw-bold me-2 text-uppercase" style="font-size: 0.65rem;">Loại
                                    tệp</small>
                                <select name="type"
                                    class="form-select form-select-sm border-0 bg-transparent fw-bold text-dark py-0 pe-4"
                                    style="width: auto; box-shadow: none; cursor: pointer;" onchange="this.form.submit()">
                                    <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>Tất cả
                                    </option>
                                    <option value="word" {{ request('type') == 'word' ? 'selected' : '' }}>Word
                                        (doc/docx)</option>
                                    <option value="pdf" {{ request('type') == 'pdf' ? 'selected' : '' }}>PDF</option>
                                    <option value="excel" {{ request('type') == 'excel' ? 'selected' : '' }}>Excel
                                        (xls/csv)</option>
                                    <option value="image" {{ request('type') == 'image' ? 'selected' : '' }}>Hình ảnh
                                    </option>
                                    <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Video
                                    </option>
                                    <option value="archive" {{ request('type') == 'archive' ? 'selected' : '' }}>Nén
                                        (Zip/Rar)</option>
                                </select>
                            </div>

                            {{-- Search --}}
                            <div class="bg-white rounded-pill shadow-sm" style="min-width: 200px;">
                                <div class="position-relative">
                                    <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y text-muted ms-3"
                                        style="z-index: 5;"></i>
                                    <input type="text" name="search"
                                        class="form-control form-select-sm border-0 bg-transparent rounded-pill ps-5 pe-3 py-2"
                                        placeholder="Tìm tệp tin..." value="{{ request('search') }}">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0" id="filesTable">
                            <thead>
                                <tr>
                                    <th class="ps-4" style="width: 40px;">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                    </th>
                                    <th class="ps-2" style="width: 45%;">Tên tệp</th>
                                    <th class="text-center">Kích thước</th>
                                    <th class="text-center">Ngày tải lên</th>
                                    <th class="text-end pe-4">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($files as $file)
                                    <tr>
                                        <td class="ps-4 align-middle">
                                            @if ($file['name'] !== '.. (Quay lại)')
                                                <input class="form-check-input file-checkbox" type="checkbox"
                                                    value="{{ $file['path'] }}">
                                            @endif
                                        </td>
                                        <td class="ps-2">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3 position-relative">
                                                    @php
                                                        if (isset($file['is_dir']) && $file['is_dir']) {
                                                            $ext = 'folder';
                                                            $icon =
                                                                $file['name'] === '.. (Quay lại)'
                                                                    ? 'fa-level-up-alt'
                                                                    : 'fa-folder';
                                                            $color = 'text-warning';
                                                            $bg = 'bg-warning bg-opacity-10';
                                                        } else {
                                                            $ext = strtolower(
                                                                pathinfo($file['name'], PATHINFO_EXTENSION),
                                                            );
                                                            $icon = 'fa-file';
                                                            $color = 'text-secondary';
                                                            $bg = 'bg-light';

                                                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                                                $icon = 'fa-file-image';
                                                                $color = 'text-warning';
                                                                $bg = 'bg-warning bg-opacity-10';
                                                            } elseif (in_array($ext, ['pdf'])) {
                                                                $icon = 'fa-file-pdf';
                                                                $color = 'text-danger';
                                                                $bg = 'bg-danger bg-opacity-10';
                                                            } elseif (in_array($ext, ['doc', 'docx'])) {
                                                                $icon = 'fa-file-word';
                                                                $color = 'text-primary';
                                                                $bg = 'bg-primary bg-opacity-10';
                                                            } elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) {
                                                                $icon = 'fa-file-excel';
                                                                $color = 'text-success';
                                                                $bg = 'bg-success bg-opacity-10';
                                                            } elseif (in_array($ext, ['zip', 'rar', '7z'])) {
                                                                $icon = 'fa-file-archive';
                                                                $color = 'text-info';
                                                                $bg = 'bg-info bg-opacity-10';
                                                            } elseif (in_array($ext, ['txt', 'md'])) {
                                                                $icon = 'fa-file-alt';
                                                                $color = 'text-secondary';
                                                            } elseif (in_array($ext, ['mp4', 'mov', 'avi'])) {
                                                                $icon = 'fa-file-video';
                                                                $color = 'text-danger';
                                                            }
                                                        }
                                                    @endphp
                                                    <span
                                                        class="rounded-3 d-inline-flex align-items-center justify-content-center {{ $bg }} {{ $color }} shadow-sm"
                                                        style="width: 40px; height: 40px;">
                                                        <i class="fas {{ $icon }} fa-lg"></i>
                                                    </span>
                                                </div>
                                                <div style="min-width: 0; max-width: 250px;">
                                                    <a href="{{ $file['url'] }}"
                                                        @if (!isset($file['is_dir']) || !$file['is_dir']) target="_blank" @endif
                                                        class="fw-bold text-dark text-decoration-none text-truncate d-block"
                                                        title="{{ isset($file['document']) ? $file['document']->title : $file['name'] }}">
                                                        {{ isset($file['document']) ? $file['document']->title : $file['name'] }}
                                                    </a>
                                                    <div class="small text-muted text-uppercase"
                                                        style="font-size: 0.7rem;">
                                                        @if (isset($file['is_dir']) && $file['is_dir'])
                                                            THƯ MỤC
                                                        @else
                                                            @if (isset($file['document']))
                                                                <span
                                                                    class="badge bg-primary bg-opacity-10 text-primary fw-bold me-1 border border-primary border-opacity-25">{{ $file['document']->type_label }}</span>
                                                            @endif
                                                            {{ $ext }} FILE
                                                        @endif
                                                    </div>
                                                    @if (isset($file['document']) && $file['document']->notes)
                                                        <div class="small text-muted mt-1 text-truncate"
                                                            style="max-width: 250px;"
                                                            title="{{ $file['document']->notes }}">
                                                            <i class="fas fa-sticky-note me-1 opacity-50"></i>
                                                            {{ $file['document']->notes }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if (isset($file['is_dir']) && $file['is_dir'])
                                                <span class="text-muted">—</span>
                                            @else
                                                <span class="badge bg-light text-dark border fw-normal">
                                                    {{ number_format($file['size'] / 1024, 2) }} KB
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center text-muted small">
                                            {{ $file['last_modified'] ? date('d/m/Y H:i', $file['last_modified']) : '—' }}
                                        </td>
                                        <td class="text-end pe-4">
                                            @if ($file['name'] !== '.. (Quay lại)')
                                                @can('view_media')
                                                    @canany(['create_media', 'update_media'])
                                                        @if (!isset($file['is_dir']) || !$file['is_dir'])
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-warning rounded-circle d-inline-flex align-items-center justify-content-center ms-1"
                                                                style="width: 32px; height: 32px;"
                                                                title="{{ isset($file['document']) ? 'Chỉnh sửa định danh' : 'Định danh tệp này' }}"
                                                                onclick="openMapModal('{{ addslashes($file['path']) }}', '{{ isset($file['document']) ? addslashes($file['document']->title) : addslashes($file['name']) }}', '{{ isset($file['document']) ? $file['document']->document_type : '' }}', '{{ isset($file['document']) ? addslashes($file['document']->notes) : '' }}')">
                                                                <i
                                                                    class="fas {{ isset($file['document']) ? 'fa-edit' : 'fa-tag' }}"></i>
                                                            </button>
                                                        @endif
                                                    @endcanany
                                                    @if (!isset($file['is_dir']) || !$file['is_dir'])
                                                        @if (in_array($ext, ['doc', 'docx']))
                                                            <a href="{{ route('admin.media.generate.form', ['filename' => $file['path']]) }}"
                                                                class="btn btn-sm btn-outline-info rounded-circle d-inline-flex align-items-center justify-content-center"
                                                                style="width: 32px; height: 32px;" title="Sử dụng mẫu">
                                                                <i class="fas fa-magic"></i>
                                                            </a>
                                                        @endif
                                                        <a href="{{ $file['url'] }}" target="_blank"
                                                            class="btn btn-sm btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center ms-1"
                                                            style="width: 32px; height: 32px;" title="Xem / Tải xuống">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    @else
                                                        <a href="{{ $file['url'] }}"
                                                            class="btn btn-sm btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center ms-1"
                                                            style="width: 32px; height: 32px;" title="Mở thư mục">
                                                            <i class="fas fa-folder-open"></i>
                                                        </a>
                                                    @endif
                                                @endcan

                                                @can('delete_media')
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger rounded-circle d-inline-flex align-items-center justify-content-center ms-1"
                                                        style="width: 32px; height: 32px;" title="Xóa"
                                                        onclick="confirmDelete('delete-{{ \Str::slug(\Str::replace('/', '_', $file['path'])) }}', '{{ addslashes($file['name']) }}', {{ isset($file['is_dir']) && $file['is_dir'] ? 'true' : 'false' }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>

                                                    <form id="delete-{{ \Str::slug(\Str::replace('/', '_', $file['path'])) }}"
                                                        action="{{ route('admin.media.destroy', ['filename' => $file['path']]) }}"
                                                        method="POST" class="d-none">
                                                        @csrf
                                                        @method('DELETE')
                                                        @if (isset($file['is_dir']) && $file['is_dir'])
                                                            <input type="hidden" name="is_folder" value="1">
                                                        @endif
                                                    </form>
                                                @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-light rounded-circle p-4 mb-3">
                                                    <i class="fas fa-folder-open fa-3x text-secondary opacity-50"></i>
                                                </div>
                                                <h6 class="text-muted fw-bold">Chưa có tệp tin nào</h6>
                                                <p class="text-muted small mb-0">Hãy tải lên tệp tin đầu tiên của bạn.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 border-top">
                        {{ $files->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Floating Action Bar for Bulk Actions --}}
    <div id="bulk-action-bar"
        class="position-fixed top-0 start-50 translate-middle-x mt-4 shadow-lg rounded-pill bg-white px-4 py-2 d-none "
        style="z-index: 1050; background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); padding: 20px 25px; border: 2px solid #ffffffff; shadow-lg">
        <div class="d-flex align-items-center gap-3">
            <span class="fw-bold text-white"><span id="selected-count">0</span><span class="d-none d-md-inline"> mục đã
                    chọn</span></span>
            <div class="vr mx-1"></div>
            @can('update_media')
                <button class="btn btn-sm btn-success rounded-pill fw-bold px-3" onclick="storeActionItems('copy')"
                    title="Sao chép">
                    <i class="fas fa-copy"></i><span class="d-none d-md-inline ms-1">Sao chép</span>
                </button>
                <button class="btn btn-sm btn-warning rounded-pill fw-bold px-3" onclick="storeActionItems('cut')"
                    title="Cắt">
                    <i class="fas fa-cut"></i><span class="d-none d-md-inline ms-1">Cắt</span>
                </button>
            @endcan
            @can('delete_media')
                <button class="btn btn-sm btn-danger rounded-pill fw-bold px-3 shadow-sm" onclick="deleteSelectedItems()"
                    title="Xoá mục đã chọn">
                    <i class="fas fa-trash"></i><span class="d-none d-md-inline ms-1">Xoá mục đã chọn</span>
                </button>
            @endcan
            <button type="button" class="btn-close ms-2" aria-label="Close" onclick="clearSelection()"
                style="font-size: 0.75rem;"></button>
        </div>
    </div>

    {{-- Hidden Form for Bulk Actions --}}
    <form id="bulk-action-form" method="POST" class="d-none">
        @csrf
        <div id="bulk-action-inputs"></div>
        <input type="hidden" name="destination_folder" value="{{ $currentFolder }}">
    </form>

    {{-- Map File Modal --}}
    <div class="modal fade" id="mapFileModal" tabindex="-1" aria-labelledby="mapFileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4 position-relative">
                    <h5 class="modal-title fw-bold text-dark" id="mapFileModalLabel">
                        <i
                            class="fas fa-tag text-warning me-2 box-icon bg-warning bg-opacity-10 rounded text-center p-2"></i>
                        Định danh Tệp tin
                    </h5>
                    <button type="button" class="btn-close position-absolute top-0 end-0 mt-4 me-4"
                        data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.8rem;"></button>
                </div>

                <form action="{{ route('admin.media.map') }}" method="POST">
                    @csrf
                    <div class="modal-body px-4 py-4">
                        <div
                            class="alert alert-info border-info bg-info bg-opacity-10 d-flex align-items-center p-3 mb-4 rounded-3">
                            <i class="fas fa-info-circle text-info fs-4 me-3"></i>
                            <div class="small">
                                Bạn đang chỉnh sửa thuộc tính cho tệp: <strong><span id="map-filename-display"
                                        class="text-dark"></span></strong>.
                                Việc định danh giúp quản lý, tìm kiếm và sử dụng tệp tin dễ dàng hơn.
                            </div>
                        </div>

                        <input type="hidden" name="file_path" id="map-file-path">

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-muted mb-2">
                                Tên hiển thị (Tiêu đề) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" id="map-title-input" class="form-control"
                                placeholder="Nhập tên dễ nhớ cho tệp..." required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-muted mb-2">
                                Loại tài liệu <span class="text-danger">*</span>
                            </label>
                            <select name="document_type" id="map-type-input" class="form-select" required>
                                <option value="" disabled selected>-- Chọn loại tài liệu --</option>
                                @foreach ($documentTypes ?? [] as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold small text-uppercase text-muted mb-2">
                                Ghi chú (tuỳ chọn)
                            </label>
                            <textarea name="notes" id="map-notes-input" class="form-control" rows="2" placeholder="Thêm ghi chú..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer border-top-0 px-4 pb-4 bg-white" style="border-radius: 0 0 16px 16px;">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy
                            bỏ</button>
                        <button type="submit" class="btn btn-warning text-white rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fas fa-save me-1"></i> Lưu lại
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        // --- File Upload Logic ---
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('files');
        const fileListDisplay = document.getElementById('file-list-display');
        const submitBtn = document.getElementById('submit-btn');

        // Click to upload
        dropZone.addEventListener('click', () => fileInput.click());

        // Drag and Drop Events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('border-primary', 'bg-white', 'shadow-sm');
            dropZone.style.transform = 'scale(1.02)';
        }

        function unhighlight(e) {
            dropZone.classList.remove('border-primary', 'bg-white', 'shadow-sm');
            dropZone.style.transform = 'scale(1)';
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            // Update input files
            if (files.length > 0) {
                fileInput.files = files;
                handleFiles(files);
            }
        }

        function handleFiles(files) {
            fileListDisplay.innerHTML = '';
            if (files.length > 0) {

                // Check total size
                let totalSize = 0;
                let invalidFiles = [];
                const MAX_SIZE_MB = 20;
                const MAX_SIZE_BYTES = MAX_SIZE_MB * 1024 * 1024;

                Array.from(files).forEach(file => {
                    totalSize += file.size;
                    if (file.size > 10 * 1024 * 1024) {
                        invalidFiles.push(`${file.name} (> 10MB)`);
                    }
                });

                if (totalSize > MAX_SIZE_BYTES) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Dung lượng quá lớn!',
                        text: `Tổng dung lượng các tệp tin (${formatBytes(totalSize)}) vượt quá giới hạn cho phép (${MAX_SIZE_MB}MB). Vui lòng chia nhỏ lần tải lên set.`,
                    });
                    fileInput.value = '';
                    resetUploadUI();
                    return;
                }

                if (invalidFiles.length > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tệp tin quá lớn!',
                        text: `Các tệp sau vượt quá 10MB: ${invalidFiles.join(', ')}`,
                    });
                    fileInput.value = '';
                    resetUploadUI();
                    return;
                }

                submitBtn.disabled = false;

                const list = document.createElement('ul');
                list.className = 'list-group list-group-flush text-start';

                Array.from(files).forEach(file => {
                    const li = document.createElement('li');
                    li.className =
                        'list-group-item bg-transparent py-2 px-0 border-light d-flex justify-content-between align-items-center';

                    let iconClass = 'fa-file text-secondary';
                    if (file.type.includes('image')) iconClass = 'fa-file-image text-warning';
                    else if (file.type.includes('pdf')) iconClass = 'fa-file-pdf text-danger';

                    li.innerHTML = `
                    <div class="d-flex align-items-center text-truncate pe-2">
                        <i class="fas ${iconClass} me-2"></i>
                        <span class="text-truncate fw-bold text-dark">${file.name}</span>
                    </div>
                    <span class="badge bg-light text-secondary border">${formatBytes(file.size)}</span>
                `;
                    list.appendChild(li);
                });
                fileListDisplay.appendChild(list);

                document.getElementById('upload-text').textContent = files.length + ' tệp đã được chọn';
                document.getElementById('upload-text').classList.add('text-primary');
                document.getElementById('upload-icon').className = 'fas fa-check-circle fa-3x text-success mb-3';

            } else {
                resetUploadUI();
            }
        }

        function resetUploadUI() {
            submitBtn.disabled = true;
            document.getElementById('upload-text').textContent = 'Nhấn để chọn hoặc kéo thả tệp vào đây';
            document.getElementById('upload-text').classList.remove('text-primary');
            document.getElementById('upload-icon').className = 'fas fa-cloud-arrow-up fa-3x text-secondary mb-3 opacity-50';
            fileListDisplay.innerHTML = '';
        }

        function formatBytes(bytes, decimals = 2) {
            if (!+bytes) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
        }

        function confirmDelete(formId, filename, isFolder) {
            const typeText = isFolder ? 'Thư mục' : 'Tệp tin';
            Swal.fire({
                title: 'Bạn có chắc chắn?',
                text: typeText + " '" + filename + "' sẽ bị xóa vĩnh viễn" + (isFolder ?
                    " (bao gồm TẤT CẢ các tệp/thư mục bên trong)!" : "!"),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Vâng, xóa nó!',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById(formId);
                    if (form) {
                        form.submit();
                    } else {
                        Swal.fire('Lỗi', 'Không tìm thấy form xóa. Vui lòng tải lại trang.', 'error');
                    }
                }
            })
        }

        function openMapModal(filePath, currentTitle, docType, notes) {
            document.getElementById('map-file-path').value = filePath;
            document.getElementById('map-filename-display').textContent = filePath.split('/').pop();

            // Auto-fill title with currentTitle (or fallback original name without extension)
            const nameWithoutExt = currentTitle.includes('.') ? (currentTitle.substring(0, currentTitle.lastIndexOf('.')) ||
                currentTitle) : currentTitle;
            document.getElementById('map-title-input').value = nameWithoutExt;

            document.getElementById('map-type-input').value = docType || '';
            document.getElementById('map-notes-input').value = notes || '';

            const mapModal = new bootstrap.Modal(document.getElementById('mapFileModal'));
            mapModal.show();
        }

        // --- Bulk Actions Logic ---
        const selectAllCheckbox = document.getElementById('selectAll');
        const fileCheckboxes = document.querySelectorAll('.file-checkbox');
        const bulkActionBar = document.getElementById('bulk-action-bar');
        const selectedCountSpan = document.getElementById('selected-count');

        function updateActionBar() {
            const selectedCount = document.querySelectorAll('.file-checkbox:checked').length;
            selectedCountSpan.textContent = selectedCount;

            if (selectedCount > 0) {
                bulkActionBar.classList.remove('d-none');
                bulkActionBar.classList.add('d-flex');
            } else {
                bulkActionBar.classList.add('d-none');
                bulkActionBar.classList.remove('d-flex');
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.checked = selectedCount > 0 && selectedCount === fileCheckboxes.length;
            }
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                fileCheckboxes.forEach(cb => cb.checked = isChecked);
                updateActionBar();
            });
        }

        fileCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateActionBar);
        });

        function clearSelection() {
            if (selectAllCheckbox) selectAllCheckbox.checked = false;
            fileCheckboxes.forEach(cb => cb.checked = false);
            updateActionBar();
        }

        function getSelectedPaths() {
            return Array.from(document.querySelectorAll('.file-checkbox:checked')).map(cb => cb.value);
        }

        function deleteSelectedItems() {
            const paths = getSelectedPaths();
            if (paths.length === 0) return;

            Swal.fire({
                title: 'Xóa hàng loạt?',
                text: `Bạn có chắc chắn muốn xóa vĩnh viễn ${paths.length} mục đã chọn?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Xóa ngay',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitBulkAction('{{ route('admin.media.bulk-delete') }}', paths);
                }
            });
        }

        function storeActionItems(action) {
            const paths = getSelectedPaths();
            if (paths.length === 0) return;

            // Lưu vào sessionStorage với cấu trúc { action: '...', paths: [...] }
            const clipboardData = {
                action: action,
                paths: paths
            };
            sessionStorage.setItem('mediaClipboard', JSON.stringify(clipboardData));
            clearSelection();

            const actionText = action === 'copy' ? 'Sao chép' : 'Cắt';

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: `Đã đưa ${paths.length} mục vào Clipboard (${actionText}). Hãy di chuyển đến thư mục đích và nhấn "Dán".`,
                showConfirmButton: false,
                timer: 3000
            });

            checkClipboard();
        }

        function checkClipboard() {
            const pasteBtn = document.getElementById('btn-paste-items');
            if (!pasteBtn) return;

            const clipboardStr = sessionStorage.getItem('mediaClipboard');
            if (clipboardStr) {
                const clipboardData = JSON.parse(clipboardStr);
                const paths = clipboardData.paths || [];
                const action = clipboardData.action || 'cut';

                if (paths && paths.length > 0) {
                    pasteBtn.classList.remove('d-none');
                    const actionLabel = action === 'copy' ? 'Dán' : 'Dán';
                    pasteBtn.innerHTML = `<i class="fas fa-paste me-1"></i> ${actionLabel} ${paths.length} mục`;
                } else {
                    pasteBtn.classList.add('d-none');
                }
            } else {
                pasteBtn.classList.add('d-none');
            }
        }

        function pasteItems() {
            const clipboardStr = sessionStorage.getItem('mediaClipboard');
            if (!clipboardStr) return;

            const clipboardData = JSON.parse(clipboardStr);
            const paths = clipboardData.paths || [];
            const action = clipboardData.action || 'cut';

            if (!paths || paths.length === 0) return;

            const actionTitle = action === 'copy' ? 'Sao chép mục?' : 'Di chuyển mục?';
            const actionText = action === 'copy' ? `Bạn sẽ sao chép ${paths.length} mục vào thư mục hiện tại.` :
                `Bạn sẽ di chuyển ${paths.length} mục vào thư mục hiện tại.`;
            const routeUrl = action === 'copy' ? '{{ route('admin.media.copy') }}' : '{{ route('admin.media.move') }}';

            Swal.fire({
                title: actionTitle,
                text: actionText,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Dán ngay',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitBulkAction(routeUrl, paths);
                    sessionStorage.removeItem('mediaClipboard'); // Clear clipboard after paste
                }
            });
        }

        function submitBulkAction(url, paths) {
            const form = document.getElementById('bulk-action-form');
            const inputsDiv = document.getElementById('bulk-action-inputs');

            form.action = url;
            inputsDiv.innerHTML = ''; // Clear old inputs

            paths.forEach(path => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'paths[]';
                input.value = path;
                inputsDiv.appendChild(input);
            });

            form.submit();
        }

        // Chạy khi load trang
        document.addEventListener('DOMContentLoaded', checkClipboard);
    </script>
@endsection
