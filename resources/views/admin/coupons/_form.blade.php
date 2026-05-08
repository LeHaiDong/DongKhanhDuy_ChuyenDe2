@php
    $startsAt = old('starts_at', optional($coupon->starts_at)->format('Y-m-d\TH:i'));
    $expiresAt = old('expires_at', optional($coupon->expires_at)->format('Y-m-d\TH:i'));

    $formatDecimal = function (string $field, $value) {
        $raw = old($field, $value);

        if ($raw === null || $raw === '') {
            return '';
        }

        if (is_string($raw)) {
            $raw = trim($raw);
            $normalized = str_replace([' ', ','], ['', '.'], $raw);

            if (! is_numeric($normalized)) {
                return $raw;
            }

            $raw = $normalized;
        }

        return rtrim(rtrim(number_format((float) $raw, 2, '.', ''), '0'), '.');
    };
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
                <input
                    id="value"
                    name="value"
                    type="text"
                    inputmode="decimal"
                    value="{{ $formatDecimal('value', $coupon->value) }}"
                    class="form-control"
                    required
                    data-decimal-input
                    data-discount-value
                >
                <small id="valueHelp" style="display:block; margin-top:8px; color:#64748b; font-weight:700;"></small>
            </div>

            <div class="form-group">
                <label class="form-label" for="minimum_amount">Đơn tối thiểu</label>
                <input
                    id="minimum_amount"
                    name="minimum_amount"
                    type="text"
                    inputmode="numeric"
                    value="{{ $formatDecimal('minimum_amount', $coupon->minimum_amount) }}"
                    class="form-control"
                    data-decimal-input
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="maximum_discount">Giảm tối đa</label>
                <input
                    id="maximum_discount"
                    name="maximum_discount"
                    type="text"
                    inputmode="numeric"
                    value="{{ $formatDecimal('maximum_discount', $coupon->maximum_discount) }}"
                    class="form-control"
                    data-decimal-input
                >
                <small style="display:block; margin-top:8px; color:#64748b; font-weight:700;">Chỉ dùng khi voucher giảm theo phần trăm.</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="usage_limit">Tổng lượt dùng</label>
                <input id="usage_limit" name="usage_limit" type="number" min="1" step="1" value="{{ old('usage_limit', $coupon->usage_limit) }}" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label" for="usage_limit_per_user">Lượt dùng mỗi khách</label>
                <input id="usage_limit_per_user" name="usage_limit_per_user" type="number" min="1" step="1" value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user) }}" class="form-control">
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
    const form = document.currentScript.closest('form');
    const typeSelect = document.getElementById('type');
    const valueInput = document.getElementById('value');
    const valueHelp = document.getElementById('valueHelp');
    const maxDiscount = document.getElementById('maximum_discount');
    const decimalInputs = document.querySelectorAll('[data-decimal-input]');

    function normalizeNumber(raw) {
        let value = String(raw || '').trim().replace(/\s/g, '');

        if (!value) {
            return '';
        }

        const hasComma = value.includes(',');
        const hasDot = value.includes('.');

        if (hasComma && hasDot) {
            value = value.replace(/\./g, '').replace(',', '.');
        } else if (hasComma) {
            value = value.replace(',', '.');
        } else if (hasDot) {
            const dotParts = value.split('.');

            if (dotParts.length > 2 || (dotParts.length === 2 && dotParts[1].length === 3 && dotParts[0].length <= 3)) {
                value = value.replace(/\./g, '');
            }
        }

        return value;
    }

    function syncDiscountFields() {
        const isPercentage = typeSelect.value === 'percentage';

        maxDiscount.disabled = !isPercentage;
        maxDiscount.closest('.form-group').style.opacity = isPercentage ? '1' : '0.55';

        if (!isPercentage) {
            maxDiscount.value = '';
            valueHelp.textContent = 'Nhập số tiền giảm theo VND, ví dụ 30000 là giảm 30.000 VNĐ.';
            return;
        }

        valueHelp.textContent = 'Nhập số phần trăm từ 1 đến 100. Ví dụ 10 nghĩa là giảm 10%, không nhập 0.1.';
    }

    decimalInputs.forEach((input) => {
        input.addEventListener('input', function () {
            this.value = this.value.replace(/[^\d.,]/g, '');
        });
    });

    form?.addEventListener('submit', function (event) {
        decimalInputs.forEach((input) => {
            input.value = normalizeNumber(input.value);
        });

        if (typeSelect.value === 'percentage') {
            const discount = Number(valueInput.value);

            if (!Number.isFinite(discount) || discount < 1 || discount > 100) {
                event.preventDefault();
                alert('Voucher giảm theo phần trăm chỉ được nhập từ 1 đến 100. Ví dụ: nhập 10 để giảm 10%.');
                valueInput.focus();
            }
        }
    });

    typeSelect.addEventListener('change', syncDiscountFields);
    syncDiscountFields();
});
</script>
