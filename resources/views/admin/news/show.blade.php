@extends('layouts.admin')

@section('title', 'Chi tiết thông báo')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h1 class="h3 mb-0 text-gray-800">Chi tiết thông báo</h1>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="{{ route('admin.news.index') }}" class="btn btn-tech-outline">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>
</div>

<div class="tech-card mb-4" style="max-width: 800px; margin: 0 auto;">
    <div class="tech-header d-flex justify-content-between align-items-center">
        <div><i class="fas fa-newspaper text-white-50 me-2"></i> {{ $news->title }}</div>
    </div>
    <div class="card-body p-4 p-md-5">
        <h3 class="fw-bold mb-3 text-dark">{{ $news->title }}</h3>
        
        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
            <div class="avatar-md bg-light text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width: 48px; height: 48px; font-size: 1.2rem;">
                {{ strtoupper(substr($news->creator->name ?? 'S', 0, 1)) }}
            </div>
            <div>
                <h6 class="mb-0 fw-bold">{{ $news->creator->name ?? 'Hệ thống' }}</h6>
                <small class="text-muted"><i class="far fa-clock me-1"></i> {{ $news->created_at->format('d/m/Y H:i') }}</small>
            </div>
            
            @if(auth()->user()->can('update_news') || auth()->user()->can('delete_news'))
            <div class="ms-auto">
                <span class="badge bg-light text-dark border">
                    <i class="fas fa-users text-muted me-1"></i>
                    Gửi đến: 
                    @if($news->recipient_type == 'all')
                        Tất cả
                    @elseif($news->recipient_type == 'role')
                        Theo chức vụ
                    @else
                        Cá nhân cụ thể
                    @endif
                </span>
            </div>
            @endif
        </div>

        <div class="news-content mb-5" style="line-height: 1.6; font-size: 1.05rem; white-space: pre-wrap;">{{ $news->content }}</div>

        @if($news->attachment)
        @php
            // Backward compat: nếu là JSON array thì decode, ngược lại wrap string vào array
            $raw = $news->attachment;
            $attachments = (is_string($raw) && str_starts_with(trim($raw), '['))
                ? json_decode($raw, true)
                : [$raw];
            $attachments = array_filter($attachments ?? []);
        @endphp
        <div class="attachment-box mt-4 p-4 bg-light rounded-4 border">
            <h6 class="fw-bold mb-3"><i class="fas fa-paperclip text-primary me-2"></i> File đính kèm ({{ count($attachments) }})</h6>
            <div class="d-flex flex-wrap gap-3">
                @foreach($attachments as $att)
                @php
                    $ext = strtolower(pathinfo($att, PATHINFO_EXTENSION));
                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                @endphp
                @if($isImage)
                    <a href="{{ Storage::url($att) }}" target="_blank" class="d-block" title="{{ basename($att) }}">
                        <img src="{{ Storage::url($att) }}" class="rounded-3 border shadow-sm" style="height: 140px; width: auto; object-fit: cover;" alt="{{ basename($att) }}">
                    </a>
                @else
                    <div class="d-flex align-items-center justify-content-between p-3 bg-white rounded-3 border-start border-4 border-primary shadow-sm" style="max-width: 360px;">
                        <div class="d-flex align-items-center overflow-hidden w-100">
                            <div class="me-3 text-danger">
                                @php
                                    $icon = 'fa-file';
                                    if($ext === 'pdf') $icon = 'fa-file-pdf';
                                    elseif(in_array($ext, ['doc','docx'])) $icon = 'fa-file-word text-primary';
                                    elseif(in_array($ext, ['xls','xlsx'])) $icon = 'fa-file-excel text-success';
                                @endphp
                                <i class="fas {{ $icon }} fa-2x"></i>
                            </div>
                            <div class="text-truncate flex-grow-1">
                                <div class="fw-medium text-truncate mb-0" style="font-size: 0.95rem;">{{ basename($att) }}</div>
                                <small class="text-muted">Tài liệu đính kèm</small>
                            </div>
                        </div>
                        <div class="ms-3 flex-shrink-0">
                            <a href="{{ Storage::url($att) }}" download class="btn btn-sm btn-primary rounded-circle" style="width: 32px; height: 32px;" title="Tải xuống">
                                <i class="fas fa-download mx-auto mt-1"></i>
                            </a>
                        </div>
                    </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
