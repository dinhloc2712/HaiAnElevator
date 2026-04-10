@extends('layouts.admin')

@section('title', 'Chi tiết Cơ sở: ' . $shipyard->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 fw-bold">Chi Tiết Cơ Sở Đóng Mới</h1>
        <p class="text-muted small mb-0">Hồ sơ xưởng: <strong>{{ $shipyard->name }}</strong></p>
    </div>
    <div>
        @can('update_shipyard')
        <a href="{{ route('admin.shipyards.edit', $shipyard->id) }}" class="btn btn-primary me-2">
            <i class="fas fa-edit me-1"></i> Chỉnh sửa
        </a>
        @endcan
        <a href="{{ route('admin.shipyards.index') }}" class="btn btn-tech-outline">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>
</div>

<div class="row">
    <!-- Main Details Column -->
    <div class="col-md-8">
        {{-- Thông tin xưởng --}}
        <div class="tech-card mb-4">
            <div class="tech-header">
                <h5 class="m-0 fw-bold"><i class="fas fa-industry me-2"></i> Thông tin chung</h5>
            </div>
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="small text-muted text-uppercase fw-bold">Tên cơ sở</label>
                        <div class="fs-5 fw-bold text-primary">{{ $shipyard->name }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted text-uppercase fw-bold">Trạng thái</label>
                        <div>
                            @if($shipyard->status === 'active')
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Đang Hoạt Động</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">Tạm Ngưng</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="small text-muted text-uppercase fw-bold">Số Giấy phép kinh doanh</label>
                        <div class="fw-bold">{{ $shipyard->license_number ?: 'Chưa cập nhật' }}</div>
                    </div>
                </div>
                
                @if($shipyard->notes)
                <div class="row mt-3">
                    <div class="col-12">
                        <label class="small text-muted text-uppercase fw-bold"><i class="fas fa-sticky-note me-1"></i> Ghi chú</label>
                        <div class="bg-light-50 p-3 rounded-3 border mt-1">
                            {!! nl2br(e($shipyard->notes)) !!}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Thông tin chủ cơ sở --}}
        <div class="tech-card mb-4">
            <div class="tech-header" style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%); color: #fff;">
                <h5 class="m-0 fw-bold"><i class="fas fa-user-tie me-2"></i> Thông tin Chủ cơ sở</h5>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="small text-muted">Họ Tên Chủ Cơ Sở</label>
                        <div class="fw-bold">{{ $shipyard->owner_name }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="small text-muted">Số CCCD / CMND</label>
                        <div>{{ $shipyard->owner_id_card ?: 'Chưa cung cấp' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="small text-muted">Liên hệ SĐT</label>
                        <div>
                            @if($shipyard->phone)
                                <a href="tel:{{ $shipyard->phone }}" class="text-decoration-none fw-bold">
                                    <i class="fas fa-phone-alt text-muted small me-1"></i> {{ $shipyard->phone }}
                                </a>
                            @else
                                Chưa cung cấp
                            @endif
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="small text-muted">Địa chỉ cơ sở</label>
                        <div class="fw-bold">{{ $shipyard->address ?: 'Chưa cập nhật địa chỉ chi tiết' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Column -->
    <div class="col-md-4">
        {{-- Tài liệu --}}
        <div class="tech-card mb-4">
            <div class="tech-header" style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); color: #fff;">
                <h5 class="m-0 fw-bold"><i class="fas fa-file-archive me-2"></i> Hồ Sơ & Tài Liệu (<span class="text-white">{{ is_array($shipyard->files) ? count($shipyard->files) : 0 }}</span>)</h5>
            </div>
            <div class="card-body p-4">
                @php $files = is_array($shipyard->files) ? $shipyard->files : []; @endphp

                @if(count($files) === 0)
                    <div class="text-center py-4 bg-light rounded-3 border border-dashed">
                        <i class="fas fa-folder-open text-muted mb-2 fs-2 opacity-50"></i>
                        <h6 class="text-muted fw-bold mb-1">Không có tài liệu</h6>
                        <p class="text-muted small mb-3">Sang chế độ Chỉnh sửa để thêm.</p>
                        @can('update_shipyard')
                        <a href="{{ route('admin.shipyards.edit', $shipyard->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">Tải lên ngay</a>
                        @endcan
                    </div>
                @else
                    <div class="d-flex flex-column gap-2" style="max-height: 400px; overflow-y: auto;">
                        @foreach($files as $file)
                            <div class="bg-white p-2 rounded-3 border d-flex align-items-center shadow-sm">
                                <div class="bg-light text-primary rounded-2 p-2 me-2 d-flex align-items-center justify-content-center">
                                    @php
                                        $ext = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));
                                        $icon = 'fa-file-alt';
                                        if (in_array($ext, ['pdf'])) $icon = 'fa-file-pdf text-danger';
                                        elseif (in_array($ext, ['doc', 'docx'])) $icon = 'fa-file-word text-primary';
                                        elseif (in_array($ext, ['xls', 'xlsx'])) $icon = 'fa-file-excel text-success';
                                        elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) $icon = 'fa-file-image text-warning';
                                    @endphp
                                    <i class="fas {{ $icon }} fs-5"></i>
                                </div>
                                <div class="flex-grow-1 text-truncate pe-2">
                                    <h6 class="fw-bold mb-0 text-truncate small w-100" title="{{ $file['filename'] }}">{{ $file['filename'] }}</h6>
                                </div>
                                <a href="{{ $file['url'] }}" target="_blank" class="btn btn-sm btn-light text-primary rounded-circle shadow-sm" title="Tải xuống / Xem">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Lịch sử dữ liệu --}}
        <div class="tech-card mb-4">
            <div class="tech-header" style="background: linear-gradient(135deg, #858796 0%, #60616f 100%); color: #fff;">
                <h5 class="m-0 fw-bold"><i class="fas fa-history me-2"></i> Lịch sử dữ liệu</h5>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="small text-muted">Ngày tạo</label>
                    <div class="fw-bold">{{ $shipyard->created_at->format('H:i d/m/Y') }}</div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted">Cập nhật lần cuối</label>
                    <div class="fw-bold">{{ $shipyard->updated_at->format('H:i d/m/Y') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
