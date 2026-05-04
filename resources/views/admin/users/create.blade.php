@extends('layouts.admin')

@section('title', 'Thêm khách hàng - MienTayShop Admin')
@section('page-title', 'Thêm khách hàng')

@section('content')
<div class="admin-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-user-plus" style="margin-right: 12px; color: #10b981;"></i>
            Tạo tài khoản khách hàng
        </h2>
        <a href="{{ route('admin.users.index') }}" class="nike-btn nike-btn-outline">
            <i class="fas fa-arrow-left"></i>
            Quay lại
        </a>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.store') }}" class="customer-form">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label for="name" class="form-label">Họ và tên *</label>
                    <input type="text" id="name" name="name" class="form-control @error('name') error @enderror" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" id="email" name="email" class="form-control @error('email') error @enderror" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" class="form-control @error('phone') error @enderror" value="{{ old('phone') }}">
                    @error('phone')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Mật khẩu *</label>
                    <input type="password" id="password" name="password" class="form-control @error('password') error @enderror" required>
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Xác nhận mật khẩu *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label for="address" class="form-label">Địa chỉ</label>
                <textarea id="address" name="address" class="form-control @error('address') error @enderror" rows="3">{{ old('address') }}</textarea>
                @error('address')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-note">
                <i class="fas fa-circle-info"></i>
                Tài khoản được tạo tại đây luôn là khách hàng. Tài khoản quản trị được quản lý riêng để tránh nhầm quyền hệ thống.
            </div>

            <div class="form-actions">
                <button type="submit" class="nike-btn nike-btn-primary">
                    <i class="fas fa-save"></i>
                    Tạo khách hàng
                </button>

                <button type="reset" class="nike-btn nike-btn-outline">
                    <i class="fas fa-undo"></i>
                    Làm mới
                </button>

                <a href="{{ route('admin.users.index') }}" class="nike-btn nike-btn-outline">
                    <i class="fas fa-times"></i>
                    Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.customer-form {
    max-width: 1000px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-control {
    width: 100%;
    padding: 13px 16px;
    border: 1px solid #dbe5f2;
    border-radius: 14px;
    font-size: 14px;
    transition: all 0.2s ease;
    background: #fff;
}

.form-control:focus {
    outline: none;
    border-color: #0ea5e9;
    box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.12);
}

.form-control.error {
    border-color: #ef4444;
}

.form-label {
    display: block;
    font-weight: 800;
    color: #16233a;
    margin-bottom: 8px;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.error-message {
    color: #ef4444;
    font-size: 12px;
    margin-top: 6px;
    font-weight: 700;
}

.form-note {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    padding: 14px 16px;
    background: #eef8ff;
    border: 1px solid #bfe7ff;
    border-radius: 14px;
    color: #315170;
    font-weight: 700;
    margin-top: 8px;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 28px;
    padding-top: 24px;
    border-top: 1px solid #e6edf5;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.customer-form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('password_confirmation');

    function validatePasswordMatch() {
        confirmPassword.setCustomValidity(password.value !== confirmPassword.value ? 'Mật khẩu xác nhận không khớp' : '');
    }

    password.addEventListener('input', validatePasswordMatch);
    confirmPassword.addEventListener('input', validatePasswordMatch);

    form.addEventListener('submit', function (event) {
        validatePasswordMatch();
        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
        }
    });
});
</script>
@endsection
