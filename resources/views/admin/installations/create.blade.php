@extends('layouts.admin')

@section('title', 'Tạo đơn lắp đặt')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 45px;
            border-radius: 12px;
            border: 1px solid #e3e6f0;
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 43px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 45px;
            padding-left: 15px;
            color: #4e73df;
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <div class="tech-header-container mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.installations.index') }}" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-arrow-left text-primary"></i>
            </a>
            <div>
                <h1 class="h3 mb-1 text-gray-800 fw-bold">Tạo đơn lắp đặt mới</h1>
                <p class="mb-0 text-muted small">Thiết lập thông tin lắp đặt và phân công nhân sự</p>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white p-4 border-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-file-invoice me-2"></i> Thông tin đơn hàng</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.installations.store') }}" method="POST">
                        @csrf
                        
                        <div class="row g-4">
                            {{-- Mã đơn hàng --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Mã đơn lắp đặt</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-hashtag text-primary"></i></span>
                                    <input type="text" name="code" class="form-control bg-light border-0 p-3 rounded-end-4 fw-bold" 
                                           placeholder="Ví dụ: INST-001" value="{{ old('code', 'INST-' . time()) }}" required>
                                </div>
                                @error('code') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            {{-- Chi nhánh --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Chi nhánh</label>
                                <select name="branch_id" class="form-select modern-form-control p-3 bg-light border-0 fw-bold" required>
                                    <option value="">-- Chọn chi nhánh --</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            {{-- Tòa nhà / Khách hàng --}}
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Tòa nhà / Khách hàng</label>
                                <select name="building_id" id="building_select" class="form-select select2-tag-enable" required style="width: 100%;">
                                    <option value="">-- Chọn tòa nhà hoặc nhập tên để tạo mới --</option>
                                    @foreach($buildings as $building)
                                        <option value="{{ $building->id }}" {{ old('building_id') == $building->id ? 'selected' : '' }}>{{ $building->name }} - {{ $building->address }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text small text-muted">Mẹo: Nếu không tìm thấy, bạn có thể gõ trực tiếp tên tòa nhà mới vào ô trên và ấn Enter.</div>
                                @error('building_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            {{-- Nhân viên phụ trách --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Nhân viên phụ trách</label>
                                <select name="user_id" class="form-select modern-form-control p-3 bg-light border-0 fw-bold" required>
                                    <option value="">-- Chọn nhân viên --</option>
                                    @foreach($staffs as $staff)
                                        <option value="{{ $staff->id }}" {{ old('user_id') == $staff->id ? 'selected' : '' }}>{{ $staff->name }} ({{ $staff->role->display_name ?? 'NV' }})</option>
                                    @endforeach
                                </select>
                                @error('user_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            {{-- Trạng thái --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Trạng thái ban đầu</label>
                                <select name="status" class="form-select modern-form-control p-3 bg-light border-0 fw-bold" required>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Chờ giao (Pending)</option>
                                    <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>Đang lắp (In Progress)</option>
                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Đã xong (Completed)</option>
                                </select>
                                @error('status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            {{-- Ngày bắt đầu & Thời hạn --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Ngày bắt đầu</label>
                                <input type="date" name="start_date" class="form-control bg-light border-0 p-3 rounded-4 fw-bold" value="{{ old('start_date', date('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Ngày dự kiến hoàn thành</label>
                                <input type="date" name="due_date" class="form-control bg-light border-0 p-3 rounded-4 fw-bold" value="{{ old('due_date') }}">
                            </div>

                            {{-- Ghi chú --}}
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Ghi chú thêm</label>
                                <textarea name="notes" class="form-control bg-light border-0 p-3 rounded-4" rows="3" placeholder="Yêu cầu đặc biệt hoặc thông tin lưu ý...">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-5 d-flex gap-2">
                            <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold flex-grow-1 shadow-sm">
                                <i class="fas fa-save me-2"></i> Lưu đơn lắp đặt
                            </button>
                            <a href="{{ route('admin.installations.index') }}" class="btn btn-light rounded-pill px-4 py-3 fw-bold text-muted">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#building_select').select2({
                tags: true, // This enables creating new options (Auto-creation logic support)
                placeholder: "-- Chọn tòa nhà hoặc nhập tên để tạo mới --",
                allowClear: true,
                language: {
                    noResults: function() {
                        return "Không tìm thấy. Gõ phím bất kỳ và ấn Enter để tạo mới";
                    }
                }
            });
        });
    </script>
@endsection
