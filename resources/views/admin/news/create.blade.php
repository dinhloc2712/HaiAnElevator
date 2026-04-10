@extends('layouts.admin')

@section('title', 'Tạo thông báo mới')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h1 class="h3 mb-0 text-gray-800">Tạo thông báo mới</h1>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="{{ route('admin.news.index') }}" class="btn-add" style="background: #6c757d;">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>
</div>

<div class="tech-card mb-4">
    <div class="tech-header">
        <div><i class="fas fa-plus-circle text-white-50 me-2"></i> Soạn thông báo</div>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label for="title" class="form-label fw-bold small text-uppercase text-muted">Tiêu đề thông báo <span class="text-danger">*</span></label>
                <input type="text" class="form-control modern-form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required placeholder="Nhập tiêu đề...">
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="content" class="form-label fw-bold small text-uppercase text-muted">Nội dung <span class="text-danger">*</span></label>
                <textarea class="form-control modern-form-control @error('content') is-invalid @enderror" id="content" name="content" rows="6" required placeholder="Nhập nội dung chi tiết...">{{ old('content') }}</textarea>
                @error('content')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="attachment" class="form-label fw-bold small text-uppercase text-muted">File đính kèm (Hình ảnh, PDF, Word, v.v...)</label>
                <input class="form-control modern-form-control @error('attachment') is-invalid @enderror" type="file" id="attachment" name="attachment">
                <div class="form-text text-muted"><i class="fas fa-info-circle me-1"></i> Dung lượng tối đa 10MB.</div>
                @error('attachment')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="card bg-light border-0 mb-4 p-4 rounded-4 shadow-sm">
                <h5 class="fw-bold fs-6 mb-4 small text-uppercase text-primary"><i class="fas fa-users me-2"></i> Đối tượng nhận thông báo</h5>
                
                <div class="mb-3">
                    <div class="d-flex flex-wrap gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="recipient_type" id="recipientAll" value="all" {{ old('recipient_type', 'all') == 'all' ? 'checked' : '' }} onchange="toggleRecipientOptions()">
                            <label class="form-check-label fw-medium cursor-pointer" for="recipientAll">
                                <i class="fas fa-globe text-success me-1"></i> Tất cả mọi người
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="recipient_type" id="recipientRole" value="role" {{ old('recipient_type') == 'role' ? 'checked' : '' }} onchange="toggleRecipientOptions()">
                            <label class="form-check-label fw-medium cursor-pointer" for="recipientRole">
                                <i class="fas fa-user-tie text-info me-1"></i> Theo chức vụ
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="recipient_type" id="recipientUser" value="user" {{ old('recipient_type') == 'user' ? 'checked' : '' }} onchange="toggleRecipientOptions()">
                            <label class="form-check-label fw-medium cursor-pointer" for="recipientUser">
                                <i class="fas fa-user text-secondary me-1"></i> Cá nhân cụ thể
                            </label>
                        </div>
                    </div>
                </div>

                <div id="roleSelectWrapper" class="mt-4" style="display: {{ old('recipient_type') == 'role' ? 'block' : 'none' }};">
                    <label class="form-label fw-bold">Chọn các chức vụ</label>
                    <select class="form-select select2" name="recipient_ids[]" id="rolesSelect" multiple>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ is_array(old('recipient_ids')) && in_array($role->id, old('recipient_ids')) ? 'selected' : '' }}>
                                {{ $role->display_name }} ({{ $role->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="userSelectWrapper" class="mt-4" style="display: {{ old('recipient_type') == 'user' ? 'block' : 'none' }};">
                    <label class="form-label fw-bold">Chọn các cá nhân</label>
                    <select class="form-select select2" name="recipient_ids[]" id="usersSelect" multiple>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ is_array(old('recipient_ids')) && in_array($user->id, old('recipient_ids')) ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-tech-primary px-4 py-2">
                    <i class="fas fa-paper-plane me-1"></i> Gửi thông báo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

@endsection

<script>
    function toggleRecipientOptions() {
        const type = document.querySelector('input[name="recipient_type"]:checked').value;
        const roleWrapper = document.getElementById('roleSelectWrapper');
        const userWrapper = document.getElementById('userSelectWrapper');
        const rolesSelect = $('#rolesSelect');
        const usersSelect = $('#usersSelect');

        if (type === 'all') {
            roleWrapper.style.display = 'none';
            userWrapper.style.display = 'none';
            rolesSelect.prop('disabled', true);
            usersSelect.prop('disabled', true);
        } else if (type === 'role') {
            roleWrapper.style.display = 'block';
            userWrapper.style.display = 'none';
            rolesSelect.prop('disabled', false);
            usersSelect.prop('disabled', true);
        } else if (type === 'user') {
            roleWrapper.style.display = 'none';
            userWrapper.style.display = 'block';
            rolesSelect.prop('disabled', true);
            usersSelect.prop('disabled', false);
        }
    }

    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Vui lòng click để chọn (có thể chọn nhiều)...',
            allowClear: true
        });
        
        // Initialize state
        toggleRecipientOptions();
    });
</script>
@endsection
