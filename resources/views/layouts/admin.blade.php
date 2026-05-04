<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị MienTayShop')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --brand: #2563eb;
            --brand-dark: #1e3a8a;
            --brand-soft: #eef2ff;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --soft: #f8fafc;
            --card: #ffffff;
            --success: #16a34a;
            --warning: #f59e0b;
            --danger: #dc2626;
            --info: #0891b2;
            --shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
            --radius: 22px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at 86% 4%, rgba(14, 165, 233, 0.16), transparent 30%),
                radial-gradient(circle at 18% 12%, rgba(79, 70, 229, 0.12), transparent 28%),
                linear-gradient(180deg, #edf5ff 0, #f7f9ff 260px, #f8fafc 100%);
            color: var(--ink);
            font-family: "Segoe UI", "Be Vietnam Pro", Arial, sans-serif;
        }

        a {
            color: inherit;
        }

        .admin-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: 288px;
            background: linear-gradient(180deg, #020617 0%, #172554 58%, #0e7490 100%);
            color: white;
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease;
            box-shadow: 24px 0 60px rgba(15, 23, 42, 0.24);
        }

        .admin-sidebar.closed {
            transform: translateX(-288px);
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
        }

        .sidebar-logo-icon {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--brand), #06b6d4);
            display: grid;
            place-items: center;
            box-shadow: 0 14px 34px rgba(37, 99, 235, 0.34);
        }

        .sidebar-logo-text {
            display: block;
            font-size: 21px;
            font-weight: 900;
            letter-spacing: -0.04em;
            line-height: 1;
        }

        .sidebar-logo-subtitle {
            display: block;
            margin-top: 5px;
            color: rgba(255, 255, 255, 0.68);
            font-size: 12px;
            font-weight: 600;
        }

        .sidebar-nav {
            padding: 22px 0 32px;
        }

        .nav-group {
            margin-bottom: 26px;
        }

        .nav-group-title {
            padding: 0 24px 10px;
            color: rgba(255, 255, 255, 0.45);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 4px 14px;
            padding: 12px 14px;
            border-radius: 16px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .nav-item i {
            width: 22px;
            color: rgba(255, 255, 255, 0.72);
            text-align: center;
        }

        .nav-item:hover,
        .nav-item.active {
            color: white;
            background: rgba(255, 255, 255, 0.13);
            transform: translateX(4px);
        }

        .nav-item:hover i,
        .nav-item.active i {
            color: #bae6fd;
        }

        .admin-main {
            min-height: 100vh;
            margin-left: 288px;
            transition: margin-left 0.3s ease;
        }

        .admin-main.expanded {
            margin-left: 0;
        }

        .admin-topbar {
            position: sticky;
            top: 0;
            z-index: 100;
            min-height: 76px;
            padding: 14px 30px;
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(18px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .topbar-left,
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .sidebar-toggle {
            width: 42px;
            height: 42px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: white;
            color: var(--ink);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .sidebar-toggle:hover {
            border-color: rgba(37, 99, 235, 0.42);
            color: var(--brand);
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.12);
        }

        .page-title {
            font-size: 23px;
            font-weight: 900;
            letter-spacing: -0.04em;
            color: var(--ink);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 12px 7px 7px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: white;
            color: #334155;
            font-size: 14px;
            font-weight: 700;
        }

        .admin-notification-wrap {
            position: relative;
        }

        .admin-icon-btn {
            position: relative;
            width: 44px;
            height: 44px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: white;
            color: var(--brand-dark);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .admin-icon-btn:hover {
            border-color: rgba(37, 99, 235, 0.42);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.12);
            transform: translateY(-1px);
        }

        .admin-count-pill {
            position: absolute;
            top: -5px;
            right: -3px;
            min-width: 19px;
            height: 19px;
            padding: 0 5px;
            border-radius: 999px;
            background: #0ea5e9;
            color: white;
            font-size: 11px;
            font-weight: 900;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .admin-count-pill[hidden] {
            display: none !important;
        }

        .admin-notification-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            width: min(380px, calc(100vw - 32px));
            border: 1px solid #dbeafe;
            border-radius: 20px;
            background: white;
            box-shadow: 0 24px 52px rgba(15, 23, 42, 0.15);
            overflow: hidden;
            display: none;
            z-index: 120;
        }

        .admin-notification-dropdown.open {
            display: block;
        }

        .admin-notification-dropdown [hidden] {
            display: none !important;
        }

        .admin-notification-head {
            padding: 16px 18px;
            background: linear-gradient(135deg, #eff6ff 0%, #ecfeff 100%);
            border-bottom: 1px solid var(--line);
        }

        .admin-notification-head strong {
            display: block;
            color: var(--ink);
            font-size: 15px;
            font-weight: 900;
        }

        .admin-notification-head span {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 650;
        }

        .admin-notification-item,
        .admin-notification-empty {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr);
            gap: 12px;
            padding: 14px 18px;
            color: #334155;
            text-decoration: none;
            border-bottom: 1px solid #f1f5f9;
        }

        .admin-notification-item:last-child,
        .admin-notification-empty:last-child {
            border-bottom: 0;
        }

        .admin-notification-item:hover {
            background: #f8fbff;
        }

        .admin-notification-icon {
            width: 38px;
            height: 38px;
            border-radius: 14px;
            color: white;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--brand), #06b6d4);
        }

        .admin-notification-item strong,
        .admin-notification-empty strong {
            display: block;
            color: var(--ink);
            font-size: 14px;
            font-weight: 900;
        }

        .admin-notification-item span,
        .admin-notification-empty span {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            color: white;
            background: linear-gradient(135deg, var(--brand), #06b6d4);
            font-weight: 900;
        }

        .logout-btn {
            border: none;
            border-radius: 999px;
            background: #fee2e2;
            color: #991b1b;
            padding: 11px 16px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .logout-btn:hover {
            background: #fecaca;
            transform: translateY(-1px);
        }

        .admin-content {
            width: min(100%, 1440px);
            margin: 0 auto;
            padding: 30px;
        }

        .admin-card {
            margin-bottom: 24px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: var(--radius);
            background: rgba(255, 255, 255, 0.94);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .card-header {
            padding: 22px 24px;
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .card-title {
            color: var(--ink);
            font-size: 19px;
            font-weight: 900;
            letter-spacing: -0.03em;
        }

        .card-body {
            padding: 24px;
        }

        .card-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .nike-btn,
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            border: 1px solid transparent;
            border-radius: 999px;
            background: var(--ink);
            color: white;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 800;
            line-height: 1;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .nike-btn:hover,
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.18);
        }

        .nike-btn-primary,
        .btn-primary {
            background: linear-gradient(135deg, var(--brand), #06b6d4);
            color: white;
        }

        .nike-btn-success,
        .btn-success {
            background: var(--success);
            color: white;
        }

        .nike-btn-danger,
        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .nike-btn-warning,
        .btn-warning {
            background: var(--warning);
            color: #111827;
        }

        .nike-btn-outline,
        .btn-outline {
            background: white;
            color: var(--ink);
            border-color: var(--line);
        }

        .nike-btn-outline:hover,
        .btn-outline:hover,
        .nike-btn-outline.active {
            border-color: rgba(37, 99, 235, 0.45);
            color: var(--brand-dark);
            background: var(--brand-soft);
        }

        .nike-btn-sm,
        .btn-sm {
            min-height: 34px;
            padding: 8px 12px;
            font-size: 12px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-label,
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .form-control,
        .form-input,
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="file"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: white;
            color: var(--ink);
            padding: 12px 14px;
            font-size: 14px;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-input:focus,
        input:focus,
        select:focus,
        textarea:focus {
            border-color: rgba(37, 99, 235, 0.65);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .error-message {
            margin-top: 6px;
            color: var(--danger);
            font-size: 12px;
            font-weight: 700;
        }

        .filter-form > div {
            align-items: end;
        }

        .admin-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
        }

        .admin-table th {
            padding: 15px 16px;
            border-bottom: 1px solid var(--line);
            background: #eef2ff;
            color: #475569;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-align: left;
            text-transform: uppercase;
        }

        .admin-table td {
            padding: 15px 16px;
            border-bottom: 1px solid #eef2f7;
            color: #1e293b;
            vertical-align: middle;
        }

        .admin-table tr:last-child td {
            border-bottom: none;
        }

        .admin-table tbody tr:hover {
            background: #f8fbff;
        }

        .product-image {
            width: 62px;
            height: 62px;
            object-fit: contain;
            padding: 4px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: white;
        }

        .status-badge,
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .status-active,
        .bg-success {
            background: #dcfce7;
            color: #166534;
        }

        .status-inactive,
        .bg-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-low-stock,
        .bg-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .status-out-stock,
        .bg-secondary,
        .bg-light {
            background: #e0f2fe;
            color: #075985;
        }

        .bg-info,
        .bg-primary {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .bg-dark {
            background: #e5e7eb;
            color: #111827;
        }

        .action-buttons {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 12px;
            display: inline-grid;
            place-items: center;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            transform: translateY(-1px);
        }

        .action-btn-view {
            background: #ecfeff;
            color: #0e7490;
        }

        .action-btn-edit {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .action-btn-delete {
            background: #fef2f2;
            color: #b91c1c;
        }

        .alert {
            margin-bottom: 22px;
            border-radius: 18px;
            padding: 15px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            transition: all 0.25s ease;
        }

        .alert-success {
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #166534;
        }

        .alert-error {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .stat-card,
        .stat-card-modern {
            position: relative;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 22px;
            background: white;
            padding: 22px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .stat-card::after,
        .stat-card-modern::after {
            content: "";
            position: absolute;
            inset: auto 0 0 0;
            height: 4px;
            background: linear-gradient(90deg, var(--brand), #06b6d4);
        }

        .stat-card-sm {
            display: flex;
            align-items: center;
            gap: 14px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 20px;
            background: white;
            padding: 18px;
            box-shadow: var(--shadow);
        }

        .stat-card-sm .stat-icon-sm {
            color: white;
            flex: 0 0 auto;
        }

        .stat-content {
            min-width: 0;
        }

        .stat-card[onclick],
        .stat-card-modern[onclick] {
            cursor: pointer;
        }

        .stat-card[onclick]:hover,
        .stat-card-modern[onclick]:hover {
            transform: translateY(-3px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            margin-bottom: 16px;
            color: white;
            font-size: 19px;
            background: linear-gradient(135deg, var(--brand), #06b6d4);
        }

        .stat-value,
        .stat-number {
            color: var(--ink);
            font-size: 30px;
            font-weight: 900;
            letter-spacing: -0.04em;
            line-height: 1.05;
        }

        .stat-label,
        .stat-text {
            margin-top: 6px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 700;
        }

        .stat-change {
            margin-top: 10px;
            color: var(--brand-dark);
            font-size: 12px;
            font-weight: 800;
        }

        .secondary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: white;
            padding: 18px;
        }

        .stat-info {
            display: flex;
            flex-direction: column;
        }

        .stat-icon-sm {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: var(--brand-soft);
            color: var(--brand-dark);
            display: grid;
            place-items: center;
        }

        .sort-link {
            color: inherit;
            text-decoration: none;
        }

        .sort-link.active,
        .sort-link:hover {
            color: var(--brand-dark);
        }

        .pagination {
            display: flex;
            gap: 6px;
            justify-content: center;
            margin-top: 20px;
            list-style: none;
        }

        .pagination a,
        .pagination span {
            display: inline-flex;
            min-width: 36px;
            height: 36px;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: white;
            color: var(--ink);
            text-decoration: none;
            font-weight: 800;
        }

        .pagination .active span,
        .pagination a:hover {
            border-color: var(--brand);
            background: var(--brand);
            color: white;
        }

        @media (max-width: 1100px) {
            .topbar-right {
                gap: 8px;
            }

            .user-info span,
            .logout-btn span,
            .topbar-right .nike-btn span {
                display: none;
            }
        }

        @media (max-width: 900px) {
            .admin-sidebar {
                transform: translateX(-288px);
            }

            .admin-sidebar.open {
                transform: translateX(0);
            }

            .admin-main,
            .admin-main.expanded {
                margin-left: 0;
            }

            .admin-topbar {
                padding: 12px 16px;
            }

            .admin-content {
                padding: 18px;
            }

            .page-title {
                font-size: 18px;
            }

            .card-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .admin-table {
                min-width: 760px;
            }

            .card-body {
                overflow-x: auto;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    @php
        $adminPendingSellerShops = \App\Models\SellerShop::pending()->with('user')->latest()->take(4)->get();
        $adminNewCustomersToday = \App\Models\User::where('is_admin', false)->whereDate('created_at', today())->count();
        $adminNotificationCount = $adminPendingSellerShops->count() + ($adminNewCustomersToday > 0 ? 1 : 0);
    @endphp

    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-logo">
                <div class="sidebar-logo-icon">
                    <i class="fas fa-bag-shopping"></i>
                </div>
                <div>
                    <span class="sidebar-logo-text">MienTayShop</span>
                    <span class="sidebar-logo-subtitle">Quản trị hệ thống sàn bán hàng</span>
                </div>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-group">
                <div class="nav-group-title">Vận hành</div>
                <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i>
                    Tổng quan
                </a>
                <a href="{{ route('admin.categories.index') }}" class="nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="fas fa-layer-group"></i>
                    Danh mục sản phẩm
                </a>
                <a href="{{ route('admin.seller-shops.index') }}" class="nav-item {{ request()->routeIs('admin.seller-shops.*') ? 'active' : '' }}">
                    <i class="fas fa-shop"></i>
                    Người bán hàng
                </a>
                <a href="{{ route('admin.coupons.index') }}" class="nav-item {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                    <i class="fas fa-ticket"></i>
                    Voucher
                </a>
                <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    Khách hàng
                </a>
            </div>

            <div class="nav-group">
                <div class="nav-group-title">Liên kết</div>
                <a href="{{ route('home') }}" class="nav-item" target="_blank">
                    <i class="fas fa-store"></i>
                    Xem trang người dùng
                </a>
            </div>
        </nav>
    </aside>

    <main class="admin-main" id="adminMain">
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" type="button" onclick="toggleSidebar()" aria-label="Mở menu quản trị">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title">@yield('page-title', 'Tổng quan')</h1>
            </div>

            <div class="topbar-right">
                <div class="admin-notification-wrap">
                    <button type="button" class="admin-icon-btn admin-notification-trigger" onclick="toggleAdminNotifications()" aria-label="Mở thông báo quản trị">
                        <i class="fas fa-bell"></i>
                        @if($adminNotificationCount > 0)
                            <span class="admin-count-pill admin-notification-count">{{ $adminNotificationCount }}</span>
                        @endif
                    </button>

                    <div id="admin-notification-menu" class="admin-notification-dropdown">
                        <div class="admin-notification-head">
                            <strong>Thông báo quản trị</strong>
                            <span>Theo dõi hồ sơ người bán và thay đổi quan trọng của hệ thống.</span>
                        </div>

                        @forelse($adminPendingSellerShops as $shop)
                            <a href="{{ route('admin.seller-shops.show', $shop) }}" class="admin-notification-item" data-notification-key="seller-shop-{{ $shop->id }}-{{ $shop->status }}-{{ optional($shop->updated_at)->timestamp }}">
                                <span class="admin-notification-icon">
                                    <i class="fas fa-store"></i>
                                </span>
                                <span>
                                    <strong>{{ $shop->shop_name }} chờ duyệt</strong>
                                    <span>{{ $shop->user?->email ?? 'Người bán mới' }} đã gửi hồ sơ mở shop.</span>
                                </span>
                            </a>
                        @empty
                            @if($adminNewCustomersToday === 0)
                                <div class="admin-notification-empty">
                                    <span class="admin-notification-icon">
                                        <i class="fas fa-circle-check"></i>
                                    </span>
                                    <span>
                                        <strong>Hệ thống đang ổn định</strong>
                                        <span>Hiện chưa có hồ sơ người bán nào cần xử lý.</span>
                                    </span>
                                </div>
                            @endif
                        @endforelse

                        @if($adminNewCustomersToday > 0)
                            <a href="{{ route('admin.users.index') }}" class="admin-notification-item" data-notification-key="new-customers-{{ now()->format('Ymd') }}-{{ $adminNewCustomersToday }}">
                                <span class="admin-notification-icon">
                                    <i class="fas fa-user-plus"></i>
                                </span>
                                <span>
                                    <strong>{{ $adminNewCustomersToday }} khách hàng mới hôm nay</strong>
                                    <span>Kiểm tra danh sách khách hàng nếu cần hỗ trợ tài khoản.</span>
                                </span>
                            </a>
                        @endif
                        @if($adminNotificationCount > 0)
                            <div class="admin-notification-empty admin-notification-empty-client" hidden>
                                <span class="admin-notification-icon">
                                    <i class="fas fa-circle-check"></i>
                                </span>
                                <span>
                                    <strong>Không còn thông báo mới</strong>
                                    <span>Các thông báo đã xem sẽ tạm ẩn khỏi danh sách này.</span>
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <a href="{{ route('home') }}" class="nike-btn nike-btn-outline" target="_blank">
                    <i class="fas fa-store"></i>
                    <span>Xem cửa hàng</span>
                </a>

                <div class="user-info">
                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <span>{{ Auth::guard('admin')->user()->name ?? 'Administrator' }}</span>
                </div>

                <form method="POST" action="{{ route('admin.logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn" onclick="return confirm('Bạn có chắc muốn đăng xuất khỏi trang quản trị?')">
                        <i class="fas fa-right-from-bracket"></i>
                        <span style="margin-left: 8px;">Đăng xuất</span>
                    </button>
                </form>
            </div>
        </header>

        <div class="admin-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const main = document.getElementById('adminMain');

            if (window.innerWidth <= 900) {
                sidebar.classList.toggle('open');
                return;
            }

            sidebar.classList.toggle('closed');
            main.classList.toggle('expanded');
        }

        function syncSidebarForViewport() {
            const sidebar = document.getElementById('adminSidebar');
            const main = document.getElementById('adminMain');

            if (window.innerWidth <= 900) {
                sidebar.classList.remove('closed');
                sidebar.classList.remove('open');
                main.classList.add('expanded');
            } else {
                sidebar.classList.remove('open');
                if (!sidebar.classList.contains('closed')) {
                    main.classList.remove('expanded');
                }
            }
        }

        function toggleAdminNotifications() {
            const menu = document.getElementById('admin-notification-menu');
            if (menu) {
                menu.classList.toggle('open');
            }
        }

        document.addEventListener('click', function (event) {
            const menu = document.getElementById('admin-notification-menu');
            const trigger = event.target.closest('.admin-notification-trigger');

            if (menu && !menu.contains(event.target) && !trigger) {
                menu.classList.remove('open');
            }
        });

        (function setupAdminNotifications() {
            const storageKey = 'mientayshop_seen_admin_notifications';
            const menu = document.getElementById('admin-notification-menu');
            if (!menu) {
                return;
            }

            const readSeenMap = () => {
                try {
                    return JSON.parse(localStorage.getItem(storageKey) || '{}');
                } catch (error) {
                    return {};
                }
            };

            const writeSeenMap = (seenMap) => {
                localStorage.setItem(storageKey, JSON.stringify(seenMap));
            };

            const refreshNotificationState = () => {
                const visibleItems = Array.from(menu.querySelectorAll('.admin-notification-item'))
                    .filter((item) => !item.hidden);
                const countPill = document.querySelector('.admin-notification-count');
                const emptyState = menu.querySelector('.admin-notification-empty-client');

                if (countPill) {
                    if (visibleItems.length > 0) {
                        countPill.textContent = visibleItems.length;
                        countPill.hidden = false;
                    } else {
                        countPill.hidden = true;
                    }
                }

                if (emptyState) {
                    emptyState.hidden = visibleItems.length > 0;
                }
            };

            const seenMap = readSeenMap();
            menu.querySelectorAll('.admin-notification-item[data-notification-key]').forEach((item) => {
                const key = item.dataset.notificationKey;

                if (seenMap[key]) {
                    item.hidden = true;
                    return;
                }

                item.addEventListener('click', (event) => {
                    event.preventDefault();
                    const nextSeenMap = readSeenMap();
                    nextSeenMap[key] = Date.now();
                    writeSeenMap(nextSeenMap);
                    item.hidden = true;
                    refreshNotificationState();
                    window.location.href = item.href;
                });
            });

            refreshNotificationState();
        })();

        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-8px)';
                setTimeout(() => alert.remove(), 260);
            });
        }, 5000);

        syncSidebarForViewport();
        window.addEventListener('resize', syncSidebarForViewport);
    </script>
    @stack('scripts')
</body>
</html>
