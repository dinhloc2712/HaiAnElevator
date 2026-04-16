@extends('layouts.admin')

@section('title', 'Trung tâm thông báo')

@section('content')
{{-- Breadcrumb Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 fw-bold">Thông báo hệ thống</h1>
        <p class="mb-0 text-muted small">Xem lịch sử các thông báo và cảnh báo từ hệ thống</p>
    </div>
    
    <div class="d-flex gap-2">
        @if(auth()->user()->unreadNotifications->count() > 0)
        <form action="{{ route('admin.notifications.mark-all-as-read') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="fas fa-check-double me-1"></i> Đánh dấu tất cả là đã đọc
            </button>
        </form>
        @endif
    </div>
</div>

<div class="tech-card h-100 mb-4">
    <div class="tech-header" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
        <h6 class="mb-0 fw-bold text-white d-flex align-items-center">
            <i class="fas fa-bell me-2 bg-white bg-opacity-25 rounded-circle p-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"></i>
            Lịch sử thông báo
        </h6>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 50%;">Nội dung</th>
                        <th>Loại</th>
                        <th>Thời gian</th>
                        <th>Trạng thái</th>
                        <th class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                        @php
                            $data = $notification->data;
                            $title = $data['title'] ?? 'Thông báo hệ thống';
                            $body = $data['body'] ?? '';
                            $icon = $data['icon'] ?? 'fas fa-info-circle';
                            $color = $data['color'] ?? 'info';
                            $url = $data['url'] ?? '#';
                            // If it's unread, use the mark-as-read route which redirects to the actual URL
                            if (!$notification->read_at) {
                                $targetUrl = route('admin.notifications.mark-as-read', $notification->id);
                            } else {
                                $targetUrl = $url;
                            }
                        @endphp
                        <tr class="{{ $notification->read_at ? 'opacity-75' : 'bg-light bg-opacity-10 fw-bold' }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle bg-{{ $color }} bg-opacity-10 text-{{ $color }} rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; flex-shrink: 0;">
                                        <i class="{{ $icon }}"></i>
                                    </div>
                                    <div>
                                        <div class="text-dark">{{ $title }}</div>
                                        <small class="text-muted text-truncate d-block" style="max-width: 400px;">{{ $body }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $typeMap = [
                                        'incident' => 'Sự cố',
                                        'maintenance' => 'Bảo trì',
                                        'order' => 'Đơn hàng',
                                        'system' => 'Hệ thống',
                                        'MaintenanceNotification' => 'Cảnh báo',
                                    ];
                                    $typeKey = $data['type'] ?? Str::afterLast($notification->type, '\\');
                                    $translatedType = $typeMap[$typeKey] ?? ucfirst($typeKey);
                                @endphp
                                <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} px-2 py-1 rounded">
                                    {{ $translatedType }}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted small" title="{{ $notification->created_at->format('d/m/Y H:i:s') }}">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </td>
                            <td>
                                @if($notification->read_at)
                                    <span class="badge bg-light text-secondary border px-2 py-1 rounded">Đã đọc</span>
                                @else
                                    <span class="badge bg-primary px-2 py-1 rounded">Mới</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($targetUrl != '#')
                                <a href="{{ $targetUrl }}" class="btn btn-sm btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-bell-slash fa-3x mb-3 text-light"></i>
                                <p class="mb-0">Bạn chưa có thông báo nào.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($notifications->hasPages())
    <div class="card-footer bg-white border-0 mt-2">
        {{ $notifications->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
