@php
    $startsAt = old('starts_at', optional($coupon->starts_at)->format('Y-m-d\TH:i'));
    $expiresAt = old('expires_at', optional($coupon->expires_at)->format('Y-m-d\TH:i'));
@endphp

@if($errors->any())
    <div class="alert alert-error">
        <i class="fas fa-circle-exclamation"></i>
        <span>{{ $errors->first() }}</span>
    </div>
@endif

<div class="admin-card">
    <div class="card-body">
        <div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 18px;">
            <div class="form-group">
                <label class="form-label" for="code">Mã voucher *</label>
                <input id="code" name="code" value="{{ old('code', $coupon->code) }}" class="form-control" required style="text-transform: uppercase;">
            </div>

            <div class="form-group">
                <label class="form-label" for="name">Tên chương trình *</label>
                <input id="name" name="name" value="{{ old('name', $coupon->name) }}" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="type">Kiểu giảm *</label>
                <select id="type" name="type" class="form-control" required>
                    <option value="fixed" @selected(old('type', $coupon->type) === 'fixed')>Giảm tiền trực tiếp</option>
                    <option value="percentage" @selected(old('type', $coupon->type) === 'percentage')>Giảm theo phần trăm</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="value">Giá trị giảm *</label>
                <input id="value" name="value" type="number" min="1" step="1000" value="{{ old('value', $coupon->value) }}" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="minimum_amount">Đơn tối thiểu</label>
                <input id="minimum_amount" name="minimum_amount" type="number" min="0" step="1000" value="{{ old('minimum_amount', $coupon->minimum_amount) }}" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label" for="maximum_discount">Giảm tối đa</label>
                <input id="maximum_discount" name="maximum_discount" type="number" min="0" step="1000" value="{{ old('maximum_discount', $coupon->maximum_discount) }}" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label" for="usage_limit">Tổng lượt dùng</label>
                <input id="usage_limit" name="usage_limit" type="number" min="1" value="{{ old('usage_limit', $coupon->usage_limit) }}" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label" for="usage_limit_per_user">Lượt dùng mỗi khách</label>
                <input id="usage_limit_per_user" name="usage_limit_per_user" type="number" min="1" value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user) }}" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label" for="starts_at">Bắt đầu *</label>
                <input id="starts_at" name="starts_at" type="datetime-local" value="{{ $startsAt }}" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="expires_at">Kết thúc *</label>
                <input id="expires_at" name="expires_at" type="datetime-local" value="{{ $expiresAt }}" class="form-control" required>
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label" for="description">Mô tả hiển thị cho khách</label>
                <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $coupon->description) }}</textarea>
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label" for="admin_notes">Ghi chú nội bộ</label>
                <textarea id="admin_notes" name="admin_notes" rows="3" class="form-control">{{ old('admin_notes', $coupon->admin_notes) }}</textarea>
            </div>

            <label style="display: inline-flex; align-items: center; gap: 10px; font-weight: 800; color: #334155;">
                <input type="checkbox" name="first_order_only" value="1" @checked(old('first_order_only', $coupon->first_order_only))>
                Chỉ áp dụng cho đơn đầu tiên
            </label>

            <label style="display: inline-flex; align-items: center; gap: 10px; font-weight: 800; color: #334155;">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon->is_active ?? true))>
                Đang bật voucher
            </label>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 24px;">
            <button type="submit" class="nike-btn nike-btn-primary">
                <i class="fas fa-save"></i>
                Lưu voucher
            </button>
            <a href="{{ route('admin.coupons.index') }}" class="nike-btn nike-btn-outline">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('type');
    const maxDiscount = document.getElementById('maximum_discount');

    function syncMaxDiscount() {
        const isFixed = typeSelect.value === 'fixed';
        maxDiscount.disabled = isFixed;
        maxDiscount.closest('.form-group').style.opacity = isFixed ? '0.55' : '1';
        if (isFixed) {
            maxDiscount.value = '';
        }
    }

    typeSelect.addEventListener('change', syncMaxDiscount);
    syncMaxDiscount();
});
</script>
