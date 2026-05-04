@extends('layouts.admin')

@section('title', 'Tương tác sản phẩm - MienTayShop Admin')
@section('page-title', 'Tương tác sản phẩm')

@section('content')
<div class="admin-card" style="margin-bottom: 24px;">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.favorites.index') }}" style="display: grid; grid-template-columns: 220px auto auto; gap: 16px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="time_range">Khoảng thời gian</label>
                <select id="time_range" name="time_range" class="form-control">
                    <option value="7_days" @selected($timeRange === '7_days')>7 ngày qua</option>
                    <option value="30_days" @selected($timeRange === '30_days')>30 ngày qua</option>
                    <option value="3_months" @selected($timeRange === '3_months')>3 tháng qua</option>
                    <option value="6_months" @selected($timeRange === '6_months')>6 tháng qua</option>
                    <option value="1_year" @selected($timeRange === '1_year')>1 năm qua</option>
                    <option value="all_time" @selected($timeRange === 'all_time')>Tất cả</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="nike-btn">
                    <i class="fas fa-filter"></i>
                    Lọc dữ liệu
                </button>
                <a href="{{ route('admin.favorites.index') }}" class="nike-btn nike-btn-outline">
                    <i class="fas fa-rotate-left"></i>
                    Đặt lại
                </a>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <a href="{{ route('admin.favorites.export', ['time_range' => $timeRange]) }}" class="nike-btn nike-btn-success">
                    <i class="fas fa-download"></i>
                    Xuất CSV
                </a>
            </div>
        </form>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="admin-card">
        <div class="card-body stat-box">
            <strong>{{ number_format($stats['total_favorites']) }}</strong>
            <span>Tổng lượt tương tác</span>
        </div>
    </div>
    <div class="admin-card">
        <div class="card-body stat-box">
            <strong>{{ number_format($stats['unique_products']) }}</strong>
            <span>Sản phẩm được quan tâm</span>
        </div>
    </div>
    <div class="admin-card">
        <div class="card-body stat-box">
            <strong>{{ number_format($stats['unique_users']) }}</strong>
            <span>Người dùng tham gia</span>
        </div>
    </div>
    <div class="admin-card">
        <div class="card-body stat-box">
            <strong>{{ $stats['average_per_user'] }}</strong>
            <span>Trung bình mỗi người</span>
        </div>
    </div>
    <div class="admin-card">
        <div class="card-body stat-box">
            <strong>{{ number_format($stats['today_favorites']) }}</strong>
            <span>Phát sinh hôm nay</span>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: minmax(0, 1.8fr) minmax(320px, 0.9fr); gap: 24px; margin-bottom: 24px;">
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">Sản phẩm được quan tâm nhiều</h3>
        </div>
        <div class="card-body">
            @if($favoriteProducts->count() > 0)
                <div class="interaction-grid">
                    @foreach($favoriteProducts as $product)
                        <article class="interaction-card">
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="interaction-thumb">
                            <div class="interaction-info">
                                <strong>{{ $product->name }}</strong>
                                <span>{{ $product->brand }}</span>
                                <span>{{ $product->formatted_price }}</span>
                                <div class="interaction-badge">
                                    <i class="fas fa-heart"></i>
                                    {{ $product->favorites_count }} lượt
                                </div>
                            </div>
                            <a href="{{ route('products.show', $product) }}" class="action-btn action-btn-view" target="_blank" title="Xem sản phẩm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </article>
                    @endforeach
                </div>

                @if($favoriteProducts->hasPages())
                    <div style="margin-top: 20px; display: flex; justify-content: center;">
                        {{ $favoriteProducts->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            @else
                <div class="empty-state">Chưa có dữ liệu tương tác trong khoảng thời gian này.</div>
            @endif
        </div>
    </div>

    <div style="display: grid; gap: 24px;">
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title">Top người dùng</h3>
            </div>
            <div class="card-body">
                @if($topUsers->count() > 0)
                    <div class="side-list">
                        @foreach($topUsers as $index => $user)
                            <div class="side-item">
                                <div class="side-rank">{{ $index + 1 }}</div>
                                <div>
                                    <strong>{{ $user->name }}</strong>
                                    <span>{{ $user->favorites_count }} lượt tương tác</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">Chưa có người dùng nào tương tác.</div>
                @endif
            </div>
        </div>

        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title">Thương hiệu được quan tâm</h3>
            </div>
            <div class="card-body">
                @if($brandStats->count() > 0)
                    <div class="side-list">
                        @foreach($brandStats as $brand)
                            <div class="side-item side-item-between">
                                <div>
                                    <strong>{{ $brand['brand'] }}</strong>
                                    <span>{{ $brand['unique_products'] }} sản phẩm</span>
                                </div>
                                <div class="side-number">{{ $brand['count'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">Chưa có thương hiệu nào được quan tâm.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="card-header">
        <h3 class="card-title">Lịch sử tương tác gần đây</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        @if($recentFavorites->count() > 0)
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Người dùng</th>
                        <th>Sản phẩm</th>
                        <th>Thương hiệu</th>
                        <th>Giá</th>
                        <th>Thời gian</th>
                        <th width="110">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentFavorites as $favorite)
                        <tr>
                            <td>
                                <div style="font-weight: 800; color: #0f172a;">{{ $favorite->user?->name }}</div>
                                <div style="font-size: 12px; color: #64748b;">{{ $favorite->user?->email }}</div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <img src="{{ $favorite->cameraLens?->image_url }}" alt="{{ $favorite->cameraLens?->name }}" style="width: 46px; height: 46px; border-radius: 12px; object-fit: cover;">
                                    <div>
                                        <div style="font-weight: 800; color: #0f172a;">{{ \Illuminate\Support\Str::limit($favorite->cameraLens?->name, 42) }}</div>
                                        <div style="font-size: 12px; color: #64748b;">{{ $favorite->cameraLens?->display_product_type }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $favorite->cameraLens?->brand }}</td>
                                <td style="font-weight: 800; color: #2563eb;">{{ $favorite->cameraLens?->formatted_price }}</td>
                            <td>
                                {{ optional($favorite->created_at)->format('d/m/Y') }}
                                <div style="font-size: 12px; color: #64748b;">{{ optional($favorite->created_at)->format('H:i') }}</div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('products.show', $favorite->cameraLens) }}" class="action-btn action-btn-view" target="_blank" title="Xem sản phẩm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="action-btn action-btn-delete" title="Xóa tương tác" onclick="removeFavorite({{ $favorite->id }}, '{{ addslashes($favorite->user?->name ?? 'Người dùng') }}', '{{ addslashes($favorite->cameraLens?->name ?? 'Sản phẩm') }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($recentFavorites->hasPages())
                <div style="padding: 20px 24px; display: flex; justify-content: center;">
                    {{ $recentFavorites->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            @endif
        @else
            <div class="empty-state" style="margin: 24px;">Chưa có lịch sử tương tác nào để hiển thị.</div>
        @endif
    </div>
</div>

<form id="removeFavoriteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<style>
    .stat-box {
        display: grid;
        gap: 8px;
        text-align: center;
    }

    .stat-box strong {
        font-size: 28px;
        font-weight: 900;
        color: #0f172a;
    }

    .stat-box span {
        color: #64748b;
        font-weight: 700;
    }

    .interaction-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 16px;
    }

    .interaction-card {
        display: grid;
        grid-template-columns: 80px minmax(0, 1fr) auto;
        gap: 14px;
        align-items: center;
        padding: 16px;
        border: 1px solid var(--line);
        border-radius: 18px;
        background: #f8fafc;
    }

    .interaction-thumb {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 16px;
        background: white;
    }

    .interaction-info strong,
    .side-item strong {
        display: block;
        color: #0f172a;
        font-weight: 900;
        margin-bottom: 4px;
    }

    .interaction-info span,
    .side-item span {
        display: block;
        color: #64748b;
        font-size: 13px;
        margin-bottom: 2px;
    }

    .interaction-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #ffe4e6;
        color: #be123c;
        font-size: 12px;
        font-weight: 800;
    }

    .side-list {
        display: grid;
        gap: 12px;
    }

    .side-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px;
        border: 1px solid var(--line);
        border-radius: 16px;
        background: #f8fafc;
    }

    .side-item-between {
        justify-content: space-between;
    }

    .side-rank,
    .side-number {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        background: var(--brand-soft);
        color: var(--brand-dark);
        font-weight: 900;
        flex: 0 0 auto;
    }

    @media (max-width: 1100px) {
        .interaction-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
function removeFavorite(id, userName, productName) {
    if (!confirm(`Bạn có chắc muốn xóa tương tác của "${userName}" với "${productName}" không?`)) {
        return;
    }

    const form = document.getElementById('removeFavoriteForm');
    form.action = `/admin/favorites/${id}`;
    form.submit();
}
</script>
@endsection
