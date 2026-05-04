@extends('layouts.admin')

@section('title', 'Quản lý người bán hàng - MienTayShop Admin')
@section('page-title', 'Người bán hàng')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 28px; font-weight: 900; color: #0f172a; margin-bottom: 8px;">Quản lý người bán hàng</h1>
        <p style="color: #64748b;">Admin vận hành hệ thống, phê duyệt shop người bán và theo dõi hoạt động của từng kênh bán.</p>
    </div>
    <a href="{{ route('home') }}" class="nike-btn nike-btn-outline" target="_blank">
        <i class="fas fa-store"></i>
        Xem trang khách hàng
    </a>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-value">{{ number_format($statusCounts['pending']) }}</div>
        <div class="stat-label">Hồ sơ chờ duyệt</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
        <div class="stat-value">{{ number_format($statusCounts['approved']) }}</div>
        <div class="stat-label">Người bán đang hoạt động</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-circle-xmark"></i></div>
        <div class="stat-value">{{ number_format($statusCounts['rejected']) }}</div>
        <div class="stat-label">Hồ sơ bị từ chối</div>
    </div>
</div>

<div class="admin-card">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.seller-shops.index') }}" style="display: flex; gap: 12px; align-items: end; flex-wrap: wrap;">
            <div class="form-group" style="min-width: 240px; margin-bottom: 0;">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-control">
                    <option value="all" @selected(request('status', 'all') === 'all')>Tất cả</option>
                    <option value="pending" @selected(request('status') === 'pending')>Chờ duyệt</option>
                    <option value="approved" @selected(request('status') === 'approved')>Đã duyệt</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Từ chối</option>
                </select>
            </div>
            <button class="nike-btn" type="submit"><i class="fas fa-filter"></i> Lọc</button>
            <a href="{{ route('admin.seller-shops.index') }}" class="nike-btn nike-btn-outline"><i class="fas fa-rotate-left"></i> Đặt lại</a>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table class="admin-table" style="min-width: 980px;">
            <thead>
                <tr>
                    <th>Kênh bán</th>
                    <th>Ngành hàng</th>
                    <th>Người bán</th>
                    <th>Liên hệ</th>
                    <th>Sản phẩm</th>
                    <th>Trạng thái</th>
                    <th>Ngày gửi</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sellerShops as $shop)
                    <tr>
                        <td>
                            <div style="font-weight: 900; color: #0f172a;">{{ $shop->shop_name }}</div>
                            <div style="font-size: 12px; color: #64748b;">{{ $shop->brand_name ?: 'Chưa có thương hiệu' }}</div>
                        </td>
                        <td>
                            <span class="status-badge bg-info">{{ $shop->category?->display_name ?: 'Chưa chọn' }}</span>
                        </td>
                        <td>
                            <div style="font-weight: 800;">{{ $shop->user->name }}</div>
                            <div style="font-size: 12px; color: #64748b;">{{ $shop->user->email }}</div>
                        </td>
                        <td>
                            <div>{{ $shop->phone }}</div>
                            <div style="font-size: 12px; color: #64748b;">{{ $shop->address ?: 'Chưa nhập địa chỉ' }}</div>
                        </td>
                        <td>
                            <span class="status-badge bg-info">{{ number_format($shop->products_count) }} sản phẩm</span>
                        </td>
                        <td>
                            <span class="status-badge {{ $shop->status_badge_class }}">{{ $shop->status_label }}</span>
                        </td>
                        <td style="font-size: 13px; color: #64748b;">
                            {{ $shop->created_at->format('d/m/Y') }}
                            <div>{{ $shop->created_at->format('H:i') }}</div>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.seller-shops.show', $shop) }}" class="action-btn action-btn-view" title="Xem hồ sơ">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($shop->isPending() || $shop->isRejected())
                                    <form method="POST" action="{{ route('admin.seller-shops.approve', $shop) }}" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="action-btn action-btn-edit" title="Duyệt nhanh">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 50px 16px; color: #64748b;">
                            Chưa có hồ sơ kênh bán nào.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($sellerShops->hasPages())
    <div style="margin-top: 24px;">
        {{ $sellerShops->links('pagination::bootstrap-4') }}
    </div>
@endif
@endsection
