@extends('layouts.admin')

@section('title', 'Chỉnh sửa Phiếu #' . $finance->code)

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 fw-bold">
            Chỉnh sửa Phiếu
            <span class="badge-tech ms-2
                {{ $finance->type == 'income' ? 'text-success bg-success' : 'text-danger bg-danger' }}
                bg-opacity-10" style="font-size: 0.65rem; vertical-align: middle;">
                {{ $finance->code }}
            </span>
        </h1>
        <p class="text-muted small mb-0">
            Tạo bởi <strong>{{ $finance->user->name ?? 'Admin' }}</strong>
            lúc {{ $finance->created_at->format('H:i, d/m/Y') }}
        </p>
    </div>
    <a href="{{ route('admin.finance.index') }}" class="btn btn-tech-outline">
        <i class="fas fa-arrow-left me-2"></i>Quay lại
    </a>
</div>

<form action="{{ route('admin.finance.update', $finance) }}" method="POST">
    @csrf
    @method('PUT')

    {{-- === CARD 1: LOẠI PHIẾU + NGÀY + TRẠNG THÁI === --}}
    <div class="tech-card mb-4">
        <div class="tech-header">
            <i class="fas fa-file-invoice me-2"></i>
            Thông tin chung
        </div>
        <div class="card-body p-4">
            <div class="row g-4 align-items-end">
                {{-- Loại phiếu --}}
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-uppercase text-muted">
                        Loại phiếu <span class="text-danger">*</span>
                    </label>
                    <div class="d-flex gap-2 mt-1">
                        <label for="typeIncome" class="flex-fill">
                            <input type="radio" name="type" id="typeIncome" value="income"
                                   class="d-none finance-type-radio"
                                   {{ old('type', $finance->type) == 'income' ? 'checked' : '' }}>
                            <div class="finance-type-card text-center py-3 rounded-3 border fw-bold small" style="cursor:pointer;">
                                <i class="fas fa-arrow-circle-down fa-lg d-block mb-2"></i>
                                Phiếu Thu
                            </div>
                        </label>
                        <label for="typeExpense" class="flex-fill">
                            <input type="radio" name="type" id="typeExpense" value="expense"
                                   class="d-none finance-type-radio"
                                   {{ old('type', $finance->type) == 'expense' ? 'checked' : '' }}>
                            <div class="finance-type-card text-center py-3 rounded-3 border fw-bold small" style="cursor:pointer;">
                                <i class="fas fa-arrow-circle-up fa-lg d-block mb-2"></i>
                                Phiếu Chi
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Ngày ghi nhận --}}
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="date" name="transaction_date" id="transDate"
                               class="form-control @error('transaction_date') is-invalid @enderror"
                               value="{{ old('transaction_date', $finance->transaction_date->format('Y-m-d')) }}"
                               required>
                        <label for="transDate">Ngày ghi nhận <span class="text-danger">*</span></label>
                        @error('transaction_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Trạng thái --}}
                <div class="col-md-4">
                    <div class="form-floating">
                        <select name="status" id="status"
                                class="form-select @error('status') is-invalid @enderror" required>
                            <option value="pending"  {{ old('status', $finance->status) == 'pending'  ? 'selected' : '' }}>Chờ duyệt</option>
                            <option value="approved" {{ old('status', $finance->status) == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                            <option value="rejected" {{ old('status', $finance->status) == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                        </select>
                        <label for="status">Trạng thái <span class="text-danger">*</span></label>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- === CARD 2: THÔNG TIN CHI TIẾT (2 CỘT) === --}}
    <div class="tech-card mb-4">
        <div class="tech-header" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
            <i class="fas fa-user-edit me-2"></i>
            Thông tin người nộp / nhận
        </div>
        <div class="card-body p-4">
            <div class="row g-0">

                {{-- CỘT TRÁI --}}
                <div class="col-md-7 pe-md-4" style="border-right: 1px solid #eaecf4;">

                    <div class="mb-3">
                        <div class="form-floating">
                            <input type="text" name="customer_name" id="customerName"
                                   class="form-control @error('customer_name') is-invalid @enderror"
                                   value="{{ old('customer_name', $finance->customer_name) }}"
                                   placeholder="Họ tên...">
                            <label for="customerName">Họ tên người nộp/nhận <span class="text-danger">*</span></label>
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-floating">
                            <input type="text" name="address" id="address"
                                   class="form-control" value="{{ old('address') }}" placeholder="Địa chỉ...">
                            <label for="address">Địa chỉ</label>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-7">
                            <div class="form-floating">
                                <input type="text" name="company_name" id="companyName"
                                       class="form-control" value="{{ old('company_name') }}" placeholder="Công ty...">
                                <label for="companyName">Đơn vị (Company)</label>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="form-floating">
                                <input type="text" name="tax_code" id="taxCode"
                                       class="form-control" value="{{ old('tax_code') }}" placeholder="MST...">
                                <label for="taxCode">Mã số thuế</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">
                            Nội dung / Lý do <span class="text-danger">*</span>
                        </label>
                        <textarea name="description" id="description"
                                  class="form-control @error('description') is-invalid @enderror"
                                  rows="4" placeholder="Nhập nội dung phiếu..."
                                  style="border: 2px solid #eaecf4; border-radius: 12px; font-weight: 500; color: #5a5c69; resize: vertical;">{{ old('description', $finance->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Hình thức thanh toán --}}
                    <div>
                        <label class="form-label fw-bold small text-uppercase text-muted">Hình thức thanh toán</label>
                        <div class="d-flex gap-4 mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method"
                                       id="payCash" value="cash"
                                       {{ old('payment_method', $finance->payment_method) == 'cash' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="payCash">
                                    <i class="fas fa-money-bill-wave me-1 text-success"></i> Tiền mặt
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method"
                                       id="payTransfer" value="transfer"
                                       {{ old('payment_method', $finance->payment_method) == 'transfer' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="payTransfer">
                                    <i class="fas fa-university me-1 text-primary"></i> Chuyển khoản
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CỘT PHẢI --}}
                <div class="col-md-5 ps-md-4 pt-3 pt-md-0">

                    {{-- Số tiền --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted d-block text-end">
                            Số tiền (VNĐ) <span class="text-danger">*</span>
                        </label>
                        <div class="rounded-3 p-3 text-end" style="background: #f8f9fc; border: 2px solid #eaecf4;">
                            <div class="text-muted mb-1" style="font-size: 0.7rem; letter-spacing: 1px; text-transform: uppercase;">Tổng số tiền</div>
                            <input type="number" name="amount" id="amountInput"
                                   class="border-0 bg-transparent text-end fw-bold w-100 @error('amount') is-invalid @enderror"
                                   style="font-size: 2.2rem; color: #4e73df; outline: none; line-height: 1.2;"
                                   value="{{ old('amount', $finance->amount) }}" min="1" required>
                            <div class="text-muted mt-1" id="amountWords" style="font-size: 0.72rem; min-height: 16px;"></div>
                        </div>
                    </div>

                    {{-- Mã phiếu (readonly) --}}
                    <div class="mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control bg-light text-muted fw-bold"
                                   id="codeDisplay" value="{{ $finance->code }}" disabled placeholder="-">
                            <label for="codeDisplay">Mã phiếu (tự động)</label>
                        </div>
                    </div>

                    {{-- Mã tham chiếu --}}
                    <div class="mb-4">
                        <div class="form-floating">
                            <input type="text" name="reference_id" id="referenceId"
                                   class="form-control" value="{{ old('reference_id', $finance->reference_id) }}"
                                   placeholder="Số biên lai...">
                            <label for="referenceId">Mã tham chiếu / Số biên lai</label>
                        </div>
                    </div>

                    {{-- Thông tin ngân hàng --}}
                    <div class="rounded-3 p-3" style="background: #f8f9fc; border: 2px solid #eaecf4;">
                        <div class="fw-bold small text-uppercase text-muted text-end mb-3">
                            <i class="fas fa-university me-1"></i> Thông tin ngân hàng
                        </div>
                        <div class="form-floating mb-2">
                            <input type="text" name="bank_name" id="bankName"
                                   class="form-control" value="{{ old('bank_name') }}"
                                   placeholder="Ngân hàng...">
                            <label for="bankName">Tên ngân hàng (VCB, TCB...)</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" name="bank_account" id="bankAccount"
                                   class="form-control" value="{{ old('bank_account') }}"
                                   placeholder="Số tài khoản...">
                            <label for="bankAccount">Số tài khoản</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="d-flex justify-content-between">
        <button type="button" class="btn btn-outline-danger px-4" onclick="confirmDeleteFinance()">
            <i class="fas fa-trash me-2"></i>Xóa phiếu này
        </button>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.finance.index') }}" class="btn btn-tech-outline px-4">
                <i class="fas fa-times me-2"></i>Hủy bỏ
            </a>
            <button type="submit" class="btn btn-tech-primary px-4">
                <i class="fas fa-save me-2"></i>Lưu thay đổi
            </button>
        </div>
    </div>
</form>

{{-- Delete form riêng --}}
<form action="{{ route('admin.finance.destroy', $finance) }}" method="POST" id="delete-finance-form" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('styles')
<style>
    .finance-type-card {
        color: #858796;
        border-color: #eaecf4 !important;
        background: #f8f9fc;
        transition: all 0.2s ease;
    }
    label:has(#typeIncome:checked) .finance-type-card {
        color: #1cc88a;
        border-color: #1cc88a !important;
        background: #f0fdf9;
        box-shadow: 0 4px 15px rgba(28, 200, 138, 0.15);
    }
    label:has(#typeExpense:checked) .finance-type-card {
        color: #e74a3b;
        border-color: #e74a3b !important;
        background: #fff5f5;
        box-shadow: 0 4px 15px rgba(231, 74, 59, 0.15);
    }
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; }
    input[type=number] { -moz-appearance: textfield; }
</style>
@endsection

@section('scripts')
<script>
    function refreshTypeCards() {
        const radios = document.querySelectorAll('.finance-type-radio');
        document.querySelectorAll('.finance-type-card').forEach(c => {
            c.style.color = '#858796'; c.style.borderColor = '#eaecf4';
            c.style.background = '#f8f9fc'; c.style.boxShadow = 'none';
        });
        radios.forEach(r => {
            if (r.checked) {
                const card = r.nextElementSibling;
                if (r.value === 'income') {
                    Object.assign(card.style, { color:'#1cc88a', borderColor:'#1cc88a', background:'#f0fdf9', boxShadow:'0 4px 15px rgba(28,200,138,0.15)' });
                } else {
                    Object.assign(card.style, { color:'#e74a3b', borderColor:'#e74a3b', background:'#fff5f5', boxShadow:'0 4px 15px rgba(231,74,59,0.15)' });
                }
            }
        });
    }
    document.querySelectorAll('.finance-type-radio').forEach(r => r.addEventListener('change', refreshTypeCards));
    refreshTypeCards();

    const amountInput = document.getElementById('amountInput');
    const amountWords = document.getElementById('amountWords');
    function updateAmount() {
        const v = parseFloat(amountInput.value) || 0;
        amountWords.textContent = v > 0 ? v.toLocaleString('vi-VN') + ' đồng' : '';
    }
    amountInput.addEventListener('input', updateAmount);
    updateAmount();

    function confirmDeleteFinance() {
        Swal.fire({
            title: 'Xóa phiếu {{ $finance->code }}?',
            text: 'Hành động này không thể hoàn tác!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Vâng, xóa!',
            cancelButtonText: 'Hủy'
        }).then(result => {
            if (result.isConfirmed) document.getElementById('delete-finance-form').submit();
        });
    }
</script>
@endsection
