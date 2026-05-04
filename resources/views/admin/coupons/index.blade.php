@extends('layouts.admin')

@section('title', 'Quản lý voucher - MienTayShop Admin')
@section('page-title', 'Quản lý voucher')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 28px; font-weight: 900; color: #0f172a; margin-bottom: 8px;">Chương trình voucher</h1>
        <p style="color: #64748b;">Tạo và kiểm soát mã giảm giá để khách hàng áp dụng khi thanh toán.</p>
    </div>
    <a href="{{ route('admin.coupons.create') }}" class="nike-btn nike-btn-primary">
        <i class="fas fa-plus"></i>
        Tạo voucher
    </a>
</div>

<div class="stats-grid">
    <div class="stat-card-sm">
        <div class="stat-icon-sm"><i class="fas fa-ticket"></i></div>
        <div>
            <strong>{{ number_format($stats['total']) }}</strong>
            <span>Tổng voucher</span>
        </div>
    </div>
    <div class="stat-card-sm">
        <div class="stat-icon-sm"><i class="fas fa-bolt"></i></div>
        <div>
            <strong>{{ number_format($stats['active']) }}</strong>
            <span>Đang có hiệu lực</span>
        </div>
    </div>
    <div class="stat-card-sm">
        <div class="stat-icon-sm"><i class="fas fa-receipt"></i></div>
        <div>
            <strong>{{ number_format($stats['used']) }}</strong>
            <span>Lượt đã dùng</span>
        </div>
    </div>
    <div class="stat-card-sm">
        <div class="stat-icon-sm"><i class="fas fa-calendar-xmark"></i></div>
        <div>
            <strong>{{ number_format($stats['expired']) }}</strong>
            <span>Đã hết hạn</span>
        </div>
    </div>
</div>

<div class="admin-card" style="margin-bottom: 24px;">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.coupons.index') }}">
            <div style="display: grid; grid-template-columns: minmax(0, 1fr) 220px auto auto; gap: 16px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="search">Tìm voucher</label>
                    <input id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nhập mã hoặc tên voucher">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="status">Trạng thái</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">Tất cả</option>
                        <option value="active" @selected(request('status') === 'active')>Đang hoạt động</option>
                        <option value="scheduled" @selected(request('status') === 'scheduled')>Sắp bắt đầu</option>
                        <option value="expired" @selected(request('status') === 'expired')>Hết hạn</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Tạm dừng</option>
                    </select>
                </div>
                <button class="nike-btn nike-btn-primary" type="submit">
                    <i class="fas fa-search"></i>
                    Tìm kiếm
                </button>
                <a href="{{ route('admin.coupons.index') }}" class="nike-btn nike-btn-outline">
                    <i class="fas fa-rotate-left"></i>
                    Đặt lại
                </a>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table class="admin-table" style="min-width: 1040px;">
            <thead>
                <tr>
                    <th>Voucher</th>
                    <th>Ưu đãi</th>
                    <th>Điều kiện</th>
                    <th>Thời gian</th>
                    <th>Lượt dùng</th>
                    <th>Trạng thái</th>
                    <th style="width: 150px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $coupon)
                    <tr>
                        <td>
                            <div style="font-weight: 950; color: #0f172a;">{{ $coupon->code }}</div>
                            <div style="font-weight: 800; color: #334155;">{{ $coupon->name }}</div>
                            @if($coupon->description)
                                <div style="font-size: 12px; color: #64748b; max-width: 320px;">{{ $coupon->description }}</div>
                            @endif
                        </td>
                        <td>
                            <strong style="color: #2563eb;">{{ $coupon->formatted_value }}</strong>
                            @if($coupon->formatted_maximum_discount)
                                <div style="font-size: 12px; color: #64748b;">Tối đa {{ $coupon->formatted_maximum_discount }}</div>
                            @endif
                        </td>
                        <td>
                            <div>Đơn từ {{ $coupon->formatted_minimum_amount ?: '0 VNĐ' }}</div>
                            @if($coupon->first_order_only)
                                <span class="status-badge bg-info" style="margin-top: 6px;">Khách mới</span>
                            @endif
                        </td>
                        <td style="font-size: 13px; color: #475569;">
                            <div>{{ $coupon->starts_at?->format('d/m/Y H:i') }}</div>
                            <div>đến {{ $coupon->expires_at?->format('d/m/Y H:i') }}</div>
                        </td>
                        <td>
                            <strong>{{ number_format($coupon->used_count) }}</strong>
                            <span style="color: #64748b;">/ {{ $coupon->usage_limit ? number_format($coupon->usage_limit) : 'Không giới hạn' }}</span>
                        </td>
                        <td>{!! $coupon->status_badge !!}</td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.coupons.edit', $coupon) }}" class="action-btn action-btn-edit" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.coupons.toggle-status', $coupon) }}">
                                    @csrf
                                    <button type="submit" class="action-btn action-btn-view" title="{{ $coupon->is_active ? 'Tạm dừng' : 'Bật lại' }}">
                                        <i class="fas {{ $coupon->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Bạn có chắc muốn xóa voucher này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn action-btn-delete" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 42px; color: #64748b;">
                            Chưa có voucher nào. Hãy tạo chương trình ưu đãi đầu tiên cho khách hàng.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 18px;">
    {{ $coupons->links() }}
</div>
@endsection
