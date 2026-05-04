@extends('layouts.admin')

@section('title', 'Chỉnh sửa khách hàng - MienTayShop Admin')
@section('page-title', 'Chỉnh sửa khách hàng')

@section('content')
<div class="admin-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-user-edit" style="margin-right: 12px; color: #f59e0b;"></i>
            Cập nhật khách hàng: {{ $user->name }}
        </h2>
        <div class="card-actions">
            <a href="{{ route('admin.users.show', $user) }}" class="nike-btn nike-btn-outline">
                <i class="fas fa-eye"></i>
                Xem chi tiết
            </a>
            <a href="{{ route('admin.users.index') }}" class="nike-btn nike-btn-outline">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="customer-form">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="form-group">
                    <label for="name" class="form-label">Họ và tên *</label>
                    <input type="text" id="name" name="name" class="form-control @error('name') error @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" id="email" name="email" class="form-control @error('email') error @enderror" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" class="form-control @error('phone') error @enderror" value="{{ old('phone', $user->phone) }}">
                    @error('phone')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Mật khẩu mới</label>
                    <input type="password" id="password" name="password" class="form-control @error('password') error @enderror">
                    <div class="form-hint">Để trống nếu không muốn đổi mật khẩu.</div>
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label for="address" class="form-label">Địa chỉ</label>
                <textarea id="address" name="address" class="form-control @error('address') error @enderror" rows="3">{{ old('address', $user->address) }}</textarea>
                @error('address')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="account-summary">
                <h4>Thông tin tài khoản</h4>
                <div class="summary-grid">
                    <div>
                        <span>Ngày tham gia</span>
                        <strong>{{ $user->created_at->format('d/m/Y H:i') }}</strong>
                    </div>
                    <div>
                        <span>Cập nhật cuối</span>
                        <strong>{{ $user->updated_at->format('d/m/Y H:i') }}</strong>
                    </div>
                    <div>
                        <span>Số ngày thành viên</span>
                        <strong>{{ $user->created_at->diffInDays(now()) }} ngày</strong>
                    </div>
                    <div>
                        <span>Vai trò</span>
                        <strong>Khách hàng</strong>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="nike-btn nike-btn-primary">
                    <i class="fas fa-save"></i>
                    Cập nhật thông tin
                </button>

                <button type="reset" class="nike-btn nike-btn-outline">
                    <i class="fas fa-undo"></i>
                    Khôi phục
                </button>

                <button type="button" class="nike-btn nike-btn-danger" onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')">
                    <i class="fas fa-trash"></i>
                    Xóa khách hàng
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
.card-actions,
.form-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.customer-form {
    max-width: 1050px;
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

.form-hint,
.error-message {
    font-size: 12px;
    margin-top: 6px;
}

.form-hint {
    color: #64748b;
}

.error-message {
    color: #ef4444;
    font-weight: 700;
}

.account-summary {
    background: #f5fbff;
    border: 1px solid #d8ecff;
    padding: 20px;
    border-radius: 18px;
    margin: 24px 0;
}

.account-summary h4 {
    margin: 0 0 16px;
    color: #10213f;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
}

.summary-grid div {
    display: grid;
    gap: 4px;
    padding: 12px;
    background: #fff;
    border-radius: 14px;
}

.summary-grid span {
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

.summary-grid strong {
    color: #0f172a;
}

.form-actions {
    margin-top: 28px;
    padding-top: 24px;
    border-top: 1px solid #e6edf5;
}

@media (max-width: 768px) {
    .card-header {
        align-items: flex-start;
        flex-direction: column;
        gap: 16px;
    }

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
        const isInvalid = password.value && password.value !== confirmPassword.value;
        confirmPassword.setCustomValidity(isInvalid ? 'Mật khẩu xác nhận không khớp' : '');
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

function deleteUser(userId, userName) {
    if (!confirm(`Bạn có chắc muốn xóa khách hàng "${userName}"? Hành động này không thể hoàn tác.`)) {
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/users/${userId}`;
    form.innerHTML = `
        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
        <input type="hidden" name="_method" value="DELETE">
    `;
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection
