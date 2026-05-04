@extends('layouts.admin')

@section('title', 'Lịch sử hệ thống')

@section('content')

@php
    $modelNames = [
        'App\Models\Building' => 'Tòa nhà',
        'App\Models\Elevator' => 'Thang máy',
        'App\Models\Incident' => 'Sự cố',
        'App\Models\MaintenanceCheck' => 'Phiếu bảo trì',
        'App\Models\Order' => 'Đơn hàng',
        'App\Models\User' => 'Người dùng',
        'App\Models\Installation' => 'Lắp đặt',
    ];

    $fieldNames = [
        'name' => 'Tên',
        'code' => 'Mã',
        'status' => 'Trạng thái',
        'address' => 'Địa chỉ',
        'contact_name' => 'Người liên hệ',
        'contact_phone' => 'Số điện thoại liên hệ',
        'building_id' => 'ID Tòa nhà',
        'branch_id' => 'ID Chi nhánh',
        'customer_name' => 'Tên khách hàng',
        'customer_phone' => 'SĐT khách hàng',
        'province' => 'Tỉnh/Thành phố',
        'district' => 'Quận/Huyện',
        'manufacturer' => 'Hãng sản xuất',
        'model' => 'Model',
        'type' => 'Loại',
        'capacity' => 'Tải trọng',
        'floors' => 'Số điểm dừng',
        'cycle_days' => 'Chu kỳ bảo trì (ngày)',
        'note' => 'Ghi chú',
        'notes' => 'Ghi chú',
        'maintenance_deadline' => 'Hạn bảo trì tiếp theo',
        'maintenance_end_date' => 'Ngày kết thúc bảo trì',
        'elevator_count' => 'Số lượng thang máy',
        'is_active' => 'Trạng thái hoạt động',
        'total_amount' => 'Tổng tiền',
        'elevator_id' => 'ID Thang máy',
        'task_type' => 'Loại công việc',
        'fault_category' => 'Hạng mục lỗi',
        'check_date' => 'Ngày kiểm tra',
        'evaluation' => 'Đánh giá',
        'staff_ids' => 'ID Nhân viên',
        'staff_names' => 'Nhân viên phụ trách',
        'start_time' => 'Thời gian bắt đầu',
        'end_time' => 'Thời gian kết thúc',
        'user_id' => 'ID Người dùng',
        'start_date' => 'Ngày bắt đầu',
        'due_date' => 'Ngày dự kiến HT',
        'role_id' => 'ID Phân quyền',
        'email' => 'Email',
        'phone' => 'Số điện thoại',
        'created_at' => 'Ngày tạo',
        'updated_at' => 'Ngày cập nhật',
        'deleted_at' => 'Ngày xóa',
    ];
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Lịch sử hệ thống</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách thao tác</h6>
    </div>
    <div class="card-body">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="mb-4 row g-3">
            <div class="col-md-3">
                <select name="subject_type" class="form-select">
                    <option value="">-- Tất cả Đối tượng --</option>
                    @foreach($subjectTypes as $type)
                        @if($type)
                        <option value="{{ $type }}" {{ request('subject_type') == $type ? 'selected' : '' }}>
                            {{ $modelNames[$type] ?? class_basename($type) }}
                        </option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="event" class="form-select">
                    <option value="">-- Tất cả Hành động --</option>
                    @foreach($events as $event)
                        @if($event)
                        <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                            @if($event == 'created') Thêm mới
                            @elseif($event == 'updated') Cập nhật
                            @elseif($event == 'deleted') Xóa
                            @else {{ ucfirst($event) }} @endif
                        </option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="causer_id" class="form-select">
                    <option value="">-- Tất cả Người dùng --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('causer_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-secondary">Khôi phục</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th>Thời gian</th>
                        <th>Người thực hiện</th>
                        <th>Hành động</th>
                        <th>Đối tượng</th>
                        <th>Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        <tr>
                            <td>{{ $activity->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>
                                @if($activity->causer)
                                    <span class="badge bg-info text-dark">{{ $activity->causer->name }}</span>
                                @else
                                    <span class="text-muted">Hệ thống</span>
                                @endif
                            </td>
                            <td>
                                @if($activity->event == 'created')
                                    <span class="badge bg-success">Thêm mới</span>
                                @elseif($activity->event == 'updated')
                                    <span class="badge bg-warning text-dark">Cập nhật</span>
                                @elseif($activity->event == 'deleted')
                                    <span class="badge bg-danger">Xóa</span>
                                @else
                                    <span class="badge bg-secondary">{{ $activity->event }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    if ($activity->subject) {
                                        $subjectIdentifier = $activity->subject->name ?: ($activity->subject->code ?: ($activity->subject->customer_name ?: ('ID: ' . $activity->subject->id)));
                                    } else {
                                        $oldData = $activity->properties['old'] ?? [];
                                        $subjectIdentifier = ($oldData['name'] ?? '') ?: (($oldData['code'] ?? '') ?: (($oldData['customer_name'] ?? '') ?: ('ID: ' . $activity->subject_id . ' - Đã bị xóa')));
                                    }
                                @endphp
                                <strong>{{ $modelNames[$activity->subject_type] ?? class_basename($activity->subject_type) }}</strong>
                                <div class="text-muted mt-1 small">
                                    <i class="fas fa-tag me-1"></i> {{ $subjectIdentifier }}
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#logModal{{ $activity->id }}">
                                    Xem chi tiết
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="logModal{{ $activity->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    Chi tiết thay đổi - {{ $modelNames[$activity->subject_type] ?? class_basename($activity->subject_type) }}
                                                    ({{ $subjectIdentifier }})
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-0">
                                                @php
                                                    $old = $activity->properties['old'] ?? [];
                                                    $new = $activity->properties['attributes'] ?? [];
                                                    $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));
                                                @endphp

                                                @if(count($allKeys) > 0)
                                                    <div class="table-responsive m-0">
                                                        <table class="table table-bordered table-striped m-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th width="30%">Trường dữ liệu</th>
                                                                    <th width="35%">Dữ liệu cũ</th>
                                                                    <th width="35%">Dữ liệu mới</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($allKeys as $key)
                                                                    @php
                                                                        $oldVal = $old[$key] ?? '';
                                                                        $newVal = $new[$key] ?? '';
                                                                        
                                                                        // Format arrays/objects
                                                                        if (is_array($oldVal) || is_object($oldVal)) {
                                                                            $oldVal = json_encode($oldVal, JSON_UNESCAPED_UNICODE);
                                                                        }
                                                                        if (is_array($newVal) || is_object($newVal)) {
                                                                            $newVal = json_encode($newVal, JSON_UNESCAPED_UNICODE);
                                                                        }

                                                                        // Check if changed
                                                                        $isChanged = ($activity->event == 'updated' && $oldVal != $newVal);
                                                                    @endphp
                                                                    <tr class="{{ $isChanged ? 'table-warning' : '' }}">
                                                                        <td>
                                                                            <strong>{{ $fieldNames[$key] ?? $key }}</strong>
                                                                            @if(isset($fieldNames[$key]))
                                                                                <br><small class="text-muted fst-italic">({{ $key }})</small>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-danger" style="word-break: break-all;">
                                                                            @if($activity->event == 'created')
                                                                                <span class="text-muted fst-italic">-</span>
                                                                            @else
                                                                                {{ $oldVal === '' || $oldVal === null ? '(Trống)' : (strlen($oldVal) > 100 ? substr($oldVal, 0, 100) . '...' : $oldVal) }}
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-success" style="word-break: break-all;">
                                                                            @if($activity->event == 'deleted')
                                                                                <span class="text-muted fst-italic">-</span>
                                                                            @else
                                                                                {{ $newVal === '' || $newVal === null ? '(Trống)' : (strlen($newVal) > 100 ? substr($newVal, 0, 100) . '...' : $newVal) }}
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <div class="p-4 text-center text-muted">
                                                        <i class="fas fa-info-circle mb-2 fa-2x opacity-50"></i>
                                                        <p class="mb-0">Không có chi tiết thay đổi nào được ghi nhận cho thao tác này.</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Không có dữ liệu lịch sử nào khớp với bộ lọc.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $activities->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
