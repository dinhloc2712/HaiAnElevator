<div class="row g-4">
    <!-- Chọn Thang máy -->
    <div class="col-lg-12">
        <label class="form-label fw-bold text-dark"><i class="fas fa-elevator me-1"></i> Thang máy & Tòa nhà <span class="text-danger">*</span></label>
        <select name="elevator_id" class="form-select form-control p-3 rounded-4 shadow-sm @error('elevator_id') is-invalid @enderror" required>
            <option value="">-- Chọn thang máy --</option>
            @foreach($elevators as $elv)
                <option value="{{ $elv->id }}" {{ (isset($incident) && $incident->elevator_id == $elv->id) || old('elevator_id') == $elv->id ? 'selected' : '' }}>
                    {{ $elv->code }} - {{ $elv->building->name ?? 'N/A' }}
                </option>
            @endforeach
        </select>
        @error('elevator_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Thông tin người báo -->
    <div class="col-md-6">
        <label class="form-label fw-bold text-dark"><i class="far fa-user me-1"></i> Tên người báo cáo</label>
        <input type="text" name="reporter_name" class="form-control p-3 rounded-4 shadow-sm @error('reporter_name') is-invalid @enderror" 
            placeholder="Nhập tên khách hàng/người báo..." 
            value="{{ isset($incident) ? $incident->reporter_name : old('reporter_name') }}">
        @error('reporter_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold text-dark"><i class="fas fa-phone-alt me-1"></i> Số điện thoại</label>
        <input type="text" name="reporter_phone" class="form-control p-3 rounded-4 shadow-sm @error('reporter_phone') is-invalid @enderror" 
            placeholder="Nhập số điện thoại..." 
            value="{{ isset($incident) ? $incident->reporter_phone : old('reporter_phone') }}">
        @error('reporter_phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Phân công nhân viên -->
    <div class="col-lg-12">
        <label class="form-label fw-bold text-dark"><i class="fas fa-tools me-1"></i> Nhân viên xử lý</label>
        <select name="staff_ids[]" class="form-select form-control p-3 rounded-4 shadow-sm @error('staff_ids') is-invalid @enderror">
            <option value="">-- Chọn nhân viên xử lý --</option>
            @php 
                // Since it's a single select now but sends an array, we get the first selected item
                $selectedStaff = isset($incident) && !empty($incident->staff_ids) ? $incident->staff_ids[0] : (old('staff_ids') ? old('staff_ids')[0] : ''); 
            @endphp
            @foreach($staffs as $staff)
                <option value="{{ $staff->id }}" {{ $selectedStaff == $staff->id ? 'selected' : '' }}>
                    {{ $staff->code ? $staff->code . ' - ' : '' }}{{ $staff->name }}
                </option>
            @endforeach
        </select>
        <div class="form-text text-muted small"><i class="fas fa-info-circle me-1"></i>Hệ thống sẽ tự động gửi thông báo đến các nhân viên được chọn.</div>
        @error('staff_ids')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Mức độ & Trạng thái -->
    <div class="col-md-6">
        <label class="form-label fw-bold text-dark"><i class="fas fa-exclamation-triangle me-1"></i> Mức độ ưu tiên <span class="text-danger">*</span></label>
        <select name="priority" class="form-select form-control p-3 rounded-4 shadow-sm @error('priority') is-invalid @enderror" required>
            @php
                $priorities = [
                    'emergency' => 'Khẩn cấp',
                    'high' => 'Cao',
                    'medium' => 'Trung bình',
                    'low' => 'Thấp'
                ];
            @endphp
            @foreach($priorities as $val => $label)
                <option value="{{ $val }}" {{ (isset($incident) && $incident->priority == $val) || old('priority') == $val ? 'selected' : ($val == 'medium' ? 'selected' : '') }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold text-dark"><i class="far fa-clock me-1"></i> Trạng thái xử lý <span class="text-danger">*</span></label>
        <select name="status" class="form-select form-control p-3 rounded-4 shadow-sm @error('status') is-invalid @enderror" required>
            @php
                $statuses = [
                    'new' => 'Mới báo',
                    'processing' => 'Đang xử lý',
                    'resolved' => 'Hoàn thành',
                    'canceled' => 'Đã hủy'
                ];
            @endphp
            @foreach($statuses as $val => $label)
                <option value="{{ $val }}" {{ (isset($incident) && $incident->status == $val) || old('status') == $val ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Thời gian báo cáo -->
    <div class="col-md-6">
        <label class="form-label fw-bold text-dark"><i class="far fa-calendar-alt me-1"></i> Ngày báo cáo <span class="text-danger">*</span></label>
        <input type="date" name="reported_date" class="form-control p-3 rounded-4 shadow-sm @error('reported_date') is-invalid @enderror" 
            value="{{ isset($incident) ? $incident->reported_at->format('Y-m-d') : (old('reported_date') ?? date('Y-m-d')) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold text-dark"><i class="fas fa-history me-1"></i> Giờ báo cáo <span class="text-danger">*</span></label>
        <input type="time" name="reported_time" class="form-control p-3 rounded-4 shadow-sm @error('reported_time') is-invalid @enderror" 
            value="{{ isset($incident) ? $incident->reported_at->format('H:i') : (old('reported_time') ?? date('H:i')) }}" required>
    </div>

    <!-- Nội dung sự cố -->
    <div class="col-lg-12">
        <label class="form-label fw-bold text-dark"><i class="far fa-comment-dots me-1"></i> Nội dung sự cố / Mô tả chi tiết <span class="text-danger">*</span></label>
        <textarea name="description" class="form-control p-3 rounded-4 shadow-sm @error('description') is-invalid @enderror" 
            rows="5" placeholder="Mô tả chi tiết tình trạng thang máy, vị trí kẹt, số người..." required>{{ isset($incident) ? $incident->description : old('description') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
