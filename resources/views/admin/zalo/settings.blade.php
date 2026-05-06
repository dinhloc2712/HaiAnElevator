@extends('layouts.admin')

@section('title', 'Cấu hình Zalo Business Service')

@section('content')
    <div class="container-fluid py-4">
        <div class="tech-header-container mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 fw-bold">Cấu hình Zalo Business (ZBS)</h1>
                    <p class="mb-0 text-muted small">Quản lý API Keys và Tokens cho dịch vụ thông báo Zalo</p>
                </div>
                <div class="header-actions">
                    <a href="https://developers.zalo.me/" target="_blank"
                        class="btn btn-outline-primary rounded-pill px-4 me-2">
                        <i class="fas fa-external-link-alt me-2"></i> Zalo Developers
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="tech-card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-3 p-2 me-3">
                                <i class="fas fa-cog"></i>
                            </div>
                            <h5 class="mb-0 fw-bold text-dark">Thông số API</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.zalo.settings.update') }}" method="POST">
                            @csrf
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Zalo App ID</label>
                                    <input type="text" name="app_id"
                                        class="form-control bg-light border-0 p-3 rounded-4 shadow-sm"
                                        value="{{ old('app_id', $settings['app_id']) }}" placeholder="Nhập App ID..."
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Zalo App
                                        Secret</label>
                                    <input type="password" name="app_secret"
                                        class="form-control bg-light border-0 p-3 rounded-4 shadow-sm"
                                        value="{{ old('app_secret', $settings['app_secret']) }}"
                                        placeholder="Nhập App Secret..." required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Official Account (OA)
                                        ID</label>
                                    <input type="text" name="oa_id"
                                        class="form-control bg-light border-0 p-3 rounded-4 shadow-sm"
                                        value="{{ old('oa_id', $settings['oa_id']) }}" placeholder="Nhập OA ID..." required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">ZNS Template ID (Mặc
                                        định)</label>
                                    <input type="text" name="template_id"
                                        class="form-control bg-light border-0 p-3 rounded-4 shadow-sm"
                                        value="{{ old('template_id', $settings['template_id']) }}"
                                        placeholder="Ví dụ: 574418">
                                </div>

                                <div class="col-md-12">
                                    <hr class="my-3 opacity-10">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-0">Tokens (Tự
                                            động cập nhật)</label>
                                        <span class="badge bg-soft-info text-info rounded-pill px-3 py-2 small">
                                            <i class="fas fa-sync-alt me-1"></i> Tự động gia hạn
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Access Token</label>
                                    <textarea name="access_token" class="form-control bg-light border-0 p-3 rounded-4 shadow-sm font-monospace"
                                        rows="3" placeholder="Access Token sẽ hiện tại đây...">{{ old('access_token', $settings['access_token']) }}</textarea>
                                    <small class="text-muted mt-2 d-block">Lưu ý: Access Token thường có thời hạn 25
                                        giờ.</small>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Refresh Token</label>
                                    <input type="text" name="refresh_token"
                                        class="form-control bg-light border-0 p-3 rounded-4 shadow-sm font-monospace"
                                        value="{{ old('refresh_token', $settings['refresh_token']) }}"
                                        placeholder="Nhập Refresh Token ban đầu...">
                                </div>

                                <div class="col-md-12 mt-5">
                                    <button type="submit"
                                        class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm py-3">
                                        <i class="fas fa-save me-2"></i> LƯU CẤU HÌNH
                                    </button>
                                    <a href="{{ route('admin.zalo.settings.redirect') }}"
                                        class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm py-3">
                                        <i class="fas fa-link me-2"></i> Lấy token mới
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="tech-card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <h5 class="mb-0 fw-bold text-dark">Hướng dẫn</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="instruction-step mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <span
                                    class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2"
                                    style="width: 24px; height: 24px; font-size: 0.7rem;">1</span>
                                <h6 class="fw-bold mb-0">Zalo Cloud Account</h6>
                            </div>
                            <p class="small text-muted mb-0">Truy cập Zalo Developers, tạo ứng dụng và liên kết với OA của
                                bạn.</p>
                        </div>
                        <div class="instruction-step mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <span
                                    class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2"
                                    style="width: 24px; height: 24px; font-size: 0.7rem;">2</span>
                                <h6 class="fw-bold mb-0">Cấp quyền (Permissions)</h6>
                            </div>
                            <p class="small text-muted mb-0">Đảm bảo ứng dụng có quyền <code>oa.query.show</code>,
                                <code>oa.message.transaction</code> và <code>oa.message.send</code>.
                            </p>
                        </div>
                        <div class="instruction-step">
                            <div class="d-flex align-items-center mb-2">
                                <span
                                    class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2"
                                    style="width: 24px; height: 24px; font-size: 0.7rem;">3</span>
                                <h6 class="fw-bold mb-0">Lấy Refresh Token</h6>
                            </div>
                            <p class="small text-muted mb-0">Sử dụng công cụ API Explorer của Zalo để lấy Refresh Token ban
                                đầu và dán vào đây.</p>
                        </div>

                        <div class="alert alert-warning border-0 rounded-4 mt-4 mb-0 small">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Hệ thống sẽ tự động gia hạn Access Token mỗi khi gửi tin nhắn nếu token cũ hết hạn.
                        </div>
                    </div>
                </div>

                <div class="tech-card shadow-sm border-0 rounded-4 overflow-hidden mt-4">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <h5 class="mb-0 fw-bold text-dark">Kiểm tra gửi thử</h5>
                    </div>
                    <div class="card-body p-4 text-center">
                        <div class="zalo-status-icon mb-3">
                            <i class="fas fa-paper-plane fa-3x text-info"></i>
                        </div>
                        <p class="small text-muted mb-3">Nhập số điện thoại để gửi tin nhắn ZNS thử nghiệm (dùng các tham
                            số mẫu).</p>

                        <form action="{{ route('admin.zalo.settings.test') }}" method="POST">
                            @csrf
                            <div class="input-group mb-3">
                                <input type="text" name="phone" class="form-control rounded-start-pill ps-3"
                                    placeholder="Ví dụ: 0912345678" required>
                                <button class="btn btn-info text-white rounded-end-pill px-3" type="submit">
                                    <i class="fas fa-play me-1"></i> GỬI THỬ
                                </button>
                            </div>
                        </form>

                        <div class="mt-3 text-start">
                            <small class="text-muted" style="font-size: 0.75rem;">
                                <i class="fas fa-info-circle me-1"></i> Lưu ý: Số điện thoại phải có Zalo và OA phải còn đủ
                                hạn mức ZNS.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-soft-info {
            background-color: rgba(54, 185, 204, 0.1);
        }

        .step-number {
            font-weight: 800;
        }

        .tech-card {
            background: #fff;
        }
    </style>
@endsection
