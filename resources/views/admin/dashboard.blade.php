@extends('layouts.admin')

@section('title', 'Tổng quan quản trị - MienTayShop')
@section('page-title', 'Tổng quan MienTayShop')

@section('content')
@php
    $recentProducts = \App\Models\Product::with('categories')->latest()->take(6)->get();
    $pendingSellerShops = \App\Models\SellerShop::with('user')
        ->where('status', \App\Models\SellerShop::STATUS_PENDING)
        ->latest()
        ->take(6)
        ->get();
    $recentCustomers = \App\Models\User::where('is_admin', false)->latest()->take(6)->get();
    $categoryTotal = \App\Models\Category::count();
    $sellerTotal = \App\Models\SellerShop::count();
    $approvedSellerTotal = \App\Models\SellerShop::where('status', \App\Models\SellerShop::STATUS_APPROVED)->count();
    $pendingSellerTotal = \App\Models\SellerShop::where('status', \App\Models\SellerShop::STATUS_PENDING)->count();
@endphp

<section class="admin-hero">
    <div class="hero-panel">
        <span class="hero-kicker">Trung tâm vận hành sàn</span>
        <h2>Quản trị MienTayShop gọn, rõ vai trò và dễ kiểm soát</h2>
        <p>
            Admin chịu trách nhiệm duyệt hồ sơ người bán, chuẩn hóa danh mục và quản lý tài khoản khách hàng.
            Người bán sẽ tự xử lý đơn hàng, sản phẩm và doanh thu trong kênh bán riêng.
        </p>
        <div class="hero-actions">
            <a href="{{ route('admin.seller-shops.index') }}" class="nike-btn nike-btn-primary">
                <i class="fas fa-shop"></i>
                Duyệt người bán
            </a>
            <a href="{{ route('home') }}" class="nike-btn nike-btn-outline" target="_blank">
                <i class="fas fa-store"></i>
                Xem trang người dùng
            </a>
        </div>
    </div>

    <div class="hero-summary">
        <div class="summary-card">
            <span>Shop chờ duyệt</span>
            <strong>{{ number_format($pendingSellerTotal) }}</strong>
            <small>Hồ sơ cần kiểm tra giấy tờ và ngành hàng</small>
        </div>
        <div class="summary-card">
            <span>Người bán hoạt động</span>
            <strong>{{ number_format($approvedSellerTotal) }}</strong>
            <small>Shop đã được phép đăng bán sản phẩm</small>
        </div>
        <div class="summary-card">
            <span>Danh mục</span>
            <strong>{{ number_format($categoryTotal) }}</strong>
            <small>Khung phân loại hiển thị cho khách hàng</small>
        </div>
    </div>
</section>

<div class="ops-flow">
    <div class="flow-card">
        <span>01</span>
        <strong>Duyệt người bán</strong>
        <p>Kiểm tra tên shop, thương hiệu, danh mục kinh doanh và giấy tờ xác minh.</p>
    </div>
    <div class="flow-card">
        <span>02</span>
        <strong>Quản lý nền tảng</strong>
        <p>Chuẩn hóa danh mục, kiểm tra tài khoản khách hàng và giữ cấu trúc sàn rõ ràng.</p>
    </div>
    <div class="flow-card">
        <span>03</span>
        <strong>Người bán vận hành đơn</strong>
        <p>Shop đã được duyệt tự đăng sản phẩm, xác nhận đơn, giao hàng và theo dõi doanh thu.</p>
    </div>
</div>

<div class="stats-grid">
    <a href="{{ route('admin.seller-shops.index') }}" class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-shop"></i>
        </div>
        <div class="stat-value">{{ number_format($sellerTotal) }}</div>
        <div class="stat-label">Người bán hàng</div>
        <div class="stat-change">{{ number_format($pendingSellerTotal) }} hồ sơ đang chờ duyệt</div>
    </a>

    <a href="{{ route('admin.categories.index') }}" class="stat-card">
        <div class="stat-icon stat-icon-teal">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="stat-value">{{ number_format($categoryTotal) }}</div>
        <div class="stat-label">Danh mục sản phẩm</div>
        <div class="stat-change">Phân loại hàng hóa rõ ràng hơn</div>
    </a>

    <a href="{{ route('admin.seller-shops.index') }}" class="stat-card">
        <div class="stat-icon stat-icon-blue">
            <i class="fas fa-boxes-stacked"></i>
        </div>
        <div class="stat-value">{{ number_format($stats['total_products'] ?? 0) }}</div>
        <div class="stat-label">Sản phẩm từ các shop</div>
        <div class="stat-change">Admin chỉ giám sát, người bán tự quản lý sản phẩm</div>
    </a>

    <a href="{{ route('admin.users.index') }}" class="stat-card">
        <div class="stat-icon stat-icon-violet">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-value">{{ number_format($stats['total_users'] ?? 0) }}</div>
        <div class="stat-label">Tài khoản người dùng</div>
        <div class="stat-change">+{{ number_format($stats['new_users_this_month'] ?? 0) }} tài khoản mới trong tháng</div>
    </a>
</div>

<div class="secondary-stats">
    <div class="stat-item">
        <div class="stat-info">
            <span class="stat-number">{{ number_format($stats['total_customers'] ?? 0) }}</span>
            <span class="stat-text">Khách hàng</span>
        </div>
        <div class="stat-icon-sm"><i class="fas fa-user"></i></div>
    </div>
    <div class="stat-item">
        <div class="stat-info">
            <span class="stat-number">{{ number_format($pendingSellerTotal) }}</span>
            <span class="stat-text">Shop cần duyệt</span>
        </div>
        <div class="stat-icon-sm"><i class="fas fa-user-check"></i></div>
    </div>
    <div class="stat-item">
        <div class="stat-info">
            <span class="stat-number">{{ number_format($stats['active_products'] ?? 0) }}</span>
            <span class="stat-text">Sản phẩm đang bán</span>
        </div>
        <div class="stat-icon-sm"><i class="fas fa-check-circle"></i></div>
    </div>
    <div class="stat-item">
        <div class="stat-info">
            <span class="stat-number">{{ number_format($categoryTotal) }}</span>
            <span class="stat-text">Danh mục</span>
        </div>
        <div class="stat-icon-sm"><i class="fas fa-layer-group"></i></div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-box-open"></i>
                Sản phẩm mới từ các shop
            </h3>
            <a href="{{ route('admin.seller-shops.index') }}" class="nike-btn nike-btn-outline nike-btn-sm">Xem người bán</a>
        </div>
        <div class="card-body">
            @forelse($recentProducts as $product)
                <div class="compact-item">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="compact-thumb">
                    <div class="compact-info">
                        <strong>{{ $product->name }}</strong>
                        <span>{{ $product->brand }} · {{ $product->display_product_type }}</span>
                    </div>
                    <div class="compact-price">{{ $product->formatted_price }}</div>
                </div>
            @empty
                <div class="empty-state">Chưa có sản phẩm nào để hiển thị.</div>
            @endforelse
        </div>
    </div>

    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-check"></i>
                Người bán chờ duyệt
            </h3>
            <a href="{{ route('admin.seller-shops.index', ['status' => 'pending']) }}" class="nike-btn nike-btn-outline nike-btn-sm">Xem hồ sơ</a>
        </div>
        <div class="card-body">
            @forelse($pendingSellerShops as $shop)
                <div class="compact-item">
                    <div class="order-icon">
                        <i class="fas fa-shop"></i>
                    </div>
                    <div class="compact-info">
                        <strong>{{ $shop->shop_name }}</strong>
                        <span>{{ optional($shop->user)->name }} · {{ $shop->phone }}</span>
                    </div>
                    <div class="order-right">
                        <a href="{{ route('admin.seller-shops.show', $shop) }}" class="nike-btn nike-btn-outline nike-btn-sm">Duyệt</a>
                    </div>
                </div>
            @empty
                <div class="empty-state positive">
                    <i class="fas fa-circle-check"></i>
                    Không còn hồ sơ người bán đang chờ duyệt.
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users"></i>
                Khách hàng mới
            </h3>
            <a href="{{ route('admin.users.index') }}" class="nike-btn nike-btn-outline nike-btn-sm">Xem khách hàng</a>
        </div>
        <div class="card-body">
            @forelse($recentCustomers as $customer)
                <div class="stock-row">
                    <div>
                        <strong>{{ $customer->name }}</strong>
                        <span>{{ $customer->email }}</span>
                    </div>
                    <span class="status-badge status-confirmed">{{ $customer->created_at->format('d/m/Y') }}</span>
                </div>
            @empty
                <div class="empty-state positive">
                    Chưa có khách hàng mới để hiển thị.
                </div>
            @endforelse
        </div>
    </div>

    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-bolt"></i>
                Thao tác nhanh
            </h3>
        </div>
        <div class="card-body">
            <div class="quick-grid">
                <a href="{{ route('admin.seller-shops.index') }}" class="quick-action">
                    <i class="fas fa-shop"></i>
                    <strong>Duyệt người bán</strong>
                    <span>Kiểm tra hồ sơ shop trước khi cho phép đăng sản phẩm.</span>
                </a>
                <a href="{{ route('admin.categories.index') }}" class="quick-action">
                    <i class="fas fa-folder-open"></i>
                    <strong>Quản lý danh mục</strong>
                    <span>Sắp xếp và chỉnh lại nhóm sản phẩm.</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="quick-action">
                    <i class="fas fa-users"></i>
                    <strong>Quản lý khách hàng</strong>
                    <span>Xem và kiểm tra trạng thái tài khoản khách mua.</span>
                </a>
                <a href="{{ route('home') }}" class="quick-action" target="_blank">
                    <i class="fas fa-store"></i>
                    <strong>Xem trang khách hàng</strong>
                    <span>Kiểm tra trải nghiệm mua sắm ở giao diện người dùng.</span>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .admin-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(300px, 0.65fr);
        gap: 24px;
        margin-bottom: 24px;
    }

    .hero-panel,
    .hero-summary {
        border-radius: 28px;
        box-shadow: var(--shadow);
    }

    .hero-panel {
        position: relative;
        overflow: hidden;
        padding: 34px;
        background:
            radial-gradient(circle at 84% 24%, rgba(255, 255, 255, 0.22), transparent 24%),
            radial-gradient(circle at 18% 0%, rgba(6, 182, 212, 0.34), transparent 28%),
            linear-gradient(135deg, #020617 0%, #1e3a8a 58%, #0891b2 100%);
        color: white;
    }

    .hero-panel::after {
        content: "";
        position: absolute;
        right: -90px;
        bottom: -120px;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.14);
    }

    .hero-kicker {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        padding: 9px 14px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .hero-panel h2 {
        position: relative;
        z-index: 1;
        max-width: 720px;
        margin: 18px 0 12px;
        font-size: clamp(32px, 4vw, 46px);
        line-height: 1.04;
        letter-spacing: -0.06em;
        font-weight: 950;
    }

    .hero-panel p {
        position: relative;
        z-index: 1;
        max-width: 620px;
        margin: 0;
        color: rgba(255, 255, 255, 0.8);
        font-size: 16px;
        line-height: 1.75;
    }

    .hero-actions {
        position: relative;
        z-index: 1;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 24px;
    }

    .hero-summary {
        display: grid;
        gap: 14px;
        padding: 16px;
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(226, 232, 240, 0.9);
    }

    .summary-card {
        border: 1px solid var(--line);
        border-radius: 22px;
        background: white;
        padding: 18px 20px;
    }

    .summary-card span,
    .summary-card small {
        display: block;
    }

    .summary-card span {
        color: var(--muted);
        font-size: 13px;
        font-weight: 800;
    }

    .summary-card strong {
        display: block;
        margin: 8px 0 6px;
        color: var(--ink);
        font-size: 28px;
        font-weight: 950;
        letter-spacing: -0.05em;
    }

    .summary-card small {
        color: #94a3b8;
        font-size: 12px;
        font-weight: 700;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 18px;
    }

    .ops-flow {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .flow-card {
        border: 1px solid rgba(191, 219, 254, 0.9);
        border-radius: 24px;
        background:
            radial-gradient(circle at top right, rgba(6, 182, 212, 0.12), transparent 32%),
            rgba(255, 255, 255, 0.96);
        padding: 20px;
        box-shadow: var(--shadow);
    }

    .flow-card span {
        display: inline-flex;
        width: 42px;
        height: 42px;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: var(--brand-soft);
        color: var(--brand-dark);
        font-weight: 950;
        margin-bottom: 14px;
    }

    .flow-card strong {
        display: block;
        color: var(--ink);
        font-size: 16px;
        font-weight: 950;
        margin-bottom: 8px;
    }

    .flow-card p {
        margin: 0;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.7;
        font-weight: 650;
    }

    .stat-card {
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.94);
        box-shadow: var(--shadow);
        padding: 22px;
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        border-color: rgba(37, 99, 235, 0.3);
        box-shadow: 0 26px 46px rgba(15, 23, 42, 0.12);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #2563eb, #06b6d4);
        color: white;
        font-size: 22px;
    }

    .stat-icon-teal {
        background: linear-gradient(135deg, #0f766e, #2dd4bf);
    }

    .stat-icon-blue {
        background: linear-gradient(135deg, #2563eb, #38bdf8);
    }

    .stat-icon-violet {
        background: linear-gradient(135deg, #7c3aed, #a855f7);
    }

    .stat-value {
        margin-top: 16px;
        color: var(--ink);
        font-size: 36px;
        font-weight: 950;
        letter-spacing: -0.06em;
    }

    .stat-label {
        margin-top: 8px;
        color: #334155;
        font-size: 14px;
        font-weight: 800;
    }

    .stat-change {
        margin-top: 8px;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .secondary-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.94);
        box-shadow: var(--shadow);
    }

    .stat-number {
        display: block;
        color: var(--ink);
        font-size: 28px;
        font-weight: 950;
        letter-spacing: -0.05em;
    }

    .stat-text {
        display: block;
        margin-top: 4px;
        color: var(--muted);
        font-size: 13px;
        font-weight: 800;
    }

    .stat-icon-sm {
        width: 46px;
        height: 46px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        background: var(--brand-soft);
        color: var(--brand);
        font-size: 18px;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
        margin-bottom: 24px;
    }

    .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 22px 24px 0;
    }

    .card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--ink);
        font-size: 22px;
        font-weight: 900;
        letter-spacing: -0.04em;
    }

    .card-title i {
        color: var(--brand);
    }

    .card-body {
        padding: 20px 24px 24px;
    }

    .compact-item,
    .stock-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 0;
        border-bottom: 1px solid #eef2f7;
    }

    .compact-item:last-child,
    .stock-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .compact-thumb {
        width: 68px;
        height: 68px;
        border-radius: 18px;
        object-fit: cover;
        background: #f8fafc;
        flex-shrink: 0;
    }

    .compact-info {
        min-width: 0;
        flex: 1;
    }

    .compact-info strong,
    .stock-row strong {
        display: block;
        color: var(--ink);
        font-size: 15px;
        font-weight: 900;
        line-height: 1.5;
    }

    .compact-info span,
    .stock-row span {
        display: block;
        color: var(--muted);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.6;
    }

    .compact-price,
    .order-right {
        text-align: right;
        flex-shrink: 0;
    }

    .compact-price,
    .order-right strong {
        color: var(--ink);
        font-size: 15px;
        font-weight: 900;
    }

    .order-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        background: var(--brand-soft);
        color: var(--brand);
        font-size: 18px;
        flex-shrink: 0;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        margin-top: 8px;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-confirmed {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .status-processing {
        background: #e0f2fe;
        color: #0369a1;
    }

    .status-shipped {
        background: #ede9fe;
        color: #6d28d9;
    }

    .status-delivered {
        background: #dcfce7;
        color: #166534;
    }

    .status-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .empty-state {
        color: var(--muted);
        font-size: 14px;
        font-weight: 700;
        padding: 8px 0;
    }

    .empty-state.positive {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #166534;
    }

    .quick-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .quick-action {
        border: 1px solid var(--line);
        border-radius: 22px;
        background: #fff;
        padding: 20px;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .quick-action:hover {
        transform: translateY(-2px);
        border-color: rgba(37, 99, 235, 0.35);
        box-shadow: 0 18px 32px rgba(37, 99, 235, 0.12);
    }

    .quick-action i {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        margin-bottom: 14px;
        background: var(--brand-soft);
        color: var(--brand);
        font-size: 18px;
    }

    .quick-action strong,
    .quick-action span {
        display: block;
    }

    .quick-action strong {
        color: var(--ink);
        font-size: 15px;
        font-weight: 900;
    }

    .quick-action span {
        margin-top: 7px;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.7;
    }

    @media (max-width: 1200px) {
        .admin-hero,
        .dashboard-grid,
        .stats-grid,
        .ops-flow,
        .secondary-stats {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 900px) {
        .admin-hero,
        .dashboard-grid,
        .stats-grid,
        .secondary-stats,
        .ops-flow,
        .quick-grid {
            grid-template-columns: 1fr;
        }

        .card-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .compact-item,
        .stock-row {
            align-items: flex-start;
            flex-direction: column;
        }

        .compact-price,
        .order-right {
            text-align: left;
        }
    }
</style>
@endsection
