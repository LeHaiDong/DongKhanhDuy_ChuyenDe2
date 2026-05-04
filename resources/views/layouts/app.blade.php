<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MienTayShop - Shop đa ngành')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            overflow-x: hidden;
        }

        body {
            font-family: "Segoe UI", Inter, system-ui, sans-serif;
            line-height: 1.6;
            color: #0f172a;
            background:
                radial-gradient(circle at 12% -6%, rgba(14, 165, 233, 0.16), transparent 30%),
                radial-gradient(circle at 88% 8%, rgba(79, 70, 229, 0.10), transparent 28%),
                linear-gradient(180deg, #edf5ff 0%, #f7f9ff 46%, #ffffff 100%);
        }

        a {
            color: inherit;
        }

        .hidden {
            display: none !important;
        }

        .site-nav {
            position: fixed;
            inset: 0 0 auto;
            z-index: 1000;
            color: white;
            background: linear-gradient(180deg, #172554 0%, #1d4ed8 100%);
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.12);
        }

        .nav-top-bar {
            border-bottom: 1px solid rgba(255, 255, 255, 0.18);
        }

        .nav-top-shell,
        .nav-main-shell {
            max-width: 1320px;
            margin: 0 auto;
            padding-left: 24px;
            padding-right: 24px;
        }

        .nav-top-shell {
            min-height: 34px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            font-size: 13px;
        }

        .top-link-group {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .top-link-group a {
            text-decoration: none;
            color: rgba(255, 255, 255, 0.94);
        }

        .top-link-group a:hover {
            color: white;
        }

        .top-divider {
            color: rgba(255, 255, 255, 0.45);
        }

        .top-user-chip {
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            font-weight: 700;
        }

        .nav-main-shell {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: 26px;
            align-items: center;
            padding-top: 18px;
            padding-bottom: 14px;
        }

        .nav-brand {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            white-space: nowrap;
        }

        .nav-logo-mark {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
        }

        .nav-logo-text {
            font-size: 38px;
            line-height: 1;
            font-weight: 300;
            letter-spacing: -0.04em;
        }

        .header-search-stack {
            min-width: 0;
        }

        .header-search-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 0;
            background: white;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 14px 24px rgba(15, 23, 42, 0.1);
        }

        .header-search-form input {
            width: 100%;
            border: 0;
            outline: 0;
            padding: 13px 16px;
            font-size: 15px;
            color: #0f172a;
            background: white;
        }

        .header-search-form button {
            width: 60px;
            border: 0;
            cursor: pointer;
            background: linear-gradient(180deg, #06b6d4 0%, #2563eb 100%);
            color: white;
            font-size: 18px;
        }

        .header-keywords {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 8px;
        }

        .header-keywords a {
            text-decoration: none;
            color: rgba(255, 255, 255, 0.88);
            font-size: 13px;
        }

        .header-keywords a:hover {
            color: white;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .header-auth-pill {
            padding: 10px 14px;
            border-radius: 999px;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.12);
            color: white;
            font-size: 14px;
            font-weight: 800;
        }

        .header-icon,
        .user-trigger {
            position: relative;
            width: 46px;
            height: 46px;
            border: 0;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            cursor: pointer;
            font-size: 20px;
        }

        .count-pill {
            position: absolute;
            top: -6px;
            right: -2px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: #fff;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 900;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .count-pill[hidden] {
            display: none !important;
        }

        .user-menu-wrap {
            position: relative;
        }

        .notification-wrap {
            position: relative;
        }

        .user-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            min-width: 250px;
            border-radius: 18px;
            background: white;
            border: 1px solid #e2e8f0;
            box-shadow: 0 22px 44px rgba(15, 23, 42, 0.12);
            overflow: hidden;
            display: none;
            color: #334155;
        }

        .user-dropdown.open {
            display: block;
        }

        .notification-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            width: min(360px, calc(100vw - 32px));
            border-radius: 20px;
            background: white;
            border: 1px solid #dbeafe;
            box-shadow: 0 24px 52px rgba(15, 23, 42, 0.14);
            overflow: hidden;
            display: none;
            color: #334155;
        }

        .notification-dropdown.open {
            display: block;
        }

        .notification-dropdown [hidden] {
            display: none !important;
        }

        .notification-header {
            padding: 16px 18px;
            border-bottom: 1px solid #e2e8f0;
            background: linear-gradient(135deg, #eff6ff 0%, #ecfeff 100%);
        }

        .notification-header strong {
            display: block;
            color: #0f172a;
            font-size: 15px;
            font-weight: 900;
        }

        .notification-header span {
            display: block;
            margin-top: 3px;
            color: #64748b;
            font-size: 12px;
            font-weight: 650;
        }

        .notification-list {
            max-height: 340px;
            overflow-y: auto;
        }

        .notification-item,
        .notification-empty {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr);
            gap: 12px;
            padding: 14px 18px;
            text-decoration: none;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        .notification-item:last-child,
        .notification-empty:last-child {
            border-bottom: 0;
        }

        .notification-item:hover {
            background: #f8fbff;
        }

        .notification-icon {
            width: 38px;
            height: 38px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            color: white;
            background: linear-gradient(135deg, #2563eb, #06b6d4);
        }

        .notification-item strong,
        .notification-empty strong {
            display: block;
            color: #0f172a;
            font-size: 14px;
            font-weight: 900;
        }

        .notification-item span,
        .notification-empty span {
            display: block;
            margin-top: 3px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.45;
        }

        .user-dropdown a,
        .user-dropdown button {
            width: 100%;
            border: 0;
            background: transparent;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            text-align: left;
            color: #334155;
            cursor: pointer;
            font-size: 14px;
            font-weight: 650;
        }

        .user-dropdown a:hover,
        .user-dropdown button:hover {
            background: #f8fafc;
        }

        .main-content {
            min-height: 100vh;
            padding-top: 130px;
        }

        .alert-stack {
            max-width: 1320px;
            margin: 0 auto;
            padding: 16px 24px 0;
            display: grid;
            gap: 12px;
        }

        .alert {
            border-radius: 18px;
            padding: 14px 18px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .site-footer {
            background: #0f172a;
            color: white;
            margin-top: 40px;
        }

        .footer-shell {
            max-width: 1320px;
            margin: 0 auto;
            padding: 42px 24px 28px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr;
            gap: 28px;
        }

        .footer-title {
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 14px;
        }

        .footer-list {
            display: grid;
            gap: 10px;
        }

        .footer-list a {
            text-decoration: none;
            color: rgba(255, 255, 255, 0.72);
        }

        .footer-list a:hover {
            color: white;
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            margin-top: 26px;
            padding-top: 20px;
            color: rgba(255, 255, 255, 0.68);
            font-size: 14px;
        }

        @media (max-width: 1040px) {
            .nav-main-shell {
                grid-template-columns: 1fr;
                gap: 14px;
            }

            .nav-brand,
            .header-actions {
                justify-self: center;
            }

            .header-search-stack {
                width: 100%;
            }

            .main-content {
                padding-top: 178px;
            }

            .footer-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 760px) {
            .nav-top-shell,
            .nav-main-shell,
            .alert-stack,
            .footer-shell {
                padding-left: 16px;
                padding-right: 16px;
            }

            .top-link-group {
                gap: 8px;
            }

            .top-link-group a,
            .top-user-chip {
                font-size: 12px;
            }

            .nav-logo-text {
                font-size: 28px;
            }

            .header-keywords {
                display: none;
            }

            .header-auth-pill {
                display: none;
            }

            .main-content {
                padding-top: 164px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    @php
        $customerNotifications = collect();

        if (auth()->check()) {
            $currentUser = auth()->user();
            $sellerShop = $currentUser->sellerShop()->first();

            if ($sellerShop) {
                $sellerNotice = match ($sellerShop->status) {
                    \App\Models\SellerShop::STATUS_APPROVED => [
                        'key' => 'seller-shop-' . $sellerShop->id . '-' . $sellerShop->status . '-' . optional($sellerShop->updated_at)->timestamp,
                        'title' => 'Kênh bán đã được duyệt',
                        'body' => 'Bạn có thể vào kênh người bán để quản lý sản phẩm và đơn hàng.',
                        'url' => route('seller.dashboard'),
                        'icon' => 'fa-store',
                    ],
                    \App\Models\SellerShop::STATUS_REJECTED => [
                        'key' => 'seller-shop-' . $sellerShop->id . '-' . $sellerShop->status . '-' . optional($sellerShop->updated_at)->timestamp,
                        'title' => 'Hồ sơ bán hàng cần bổ sung',
                        'body' => $sellerShop->admin_note ?: 'Admin cần thêm thông tin trước khi duyệt shop.',
                        'url' => route('seller.apply'),
                        'icon' => 'fa-circle-exclamation',
                    ],
                    default => [
                        'key' => 'seller-shop-' . $sellerShop->id . '-' . $sellerShop->status . '-' . optional($sellerShop->updated_at)->timestamp,
                        'title' => 'Hồ sơ bán hàng đang chờ duyệt',
                        'body' => 'Admin sẽ kiểm tra thông tin shop trước khi mở quyền bán.',
                        'url' => route('seller.apply'),
                        'icon' => 'fa-clock',
                    ],
                };

                $customerNotifications->push($sellerNotice);
            }

            $latestOrder = $currentUser->orders()->latest()->first();

            if ($latestOrder) {
                $orderStatus = [
                    'pending' => 'đang chờ người bán xác nhận',
                    'confirmed' => 'đã được người bán xác nhận',
                    'processing' => 'đang được chuẩn bị',
                    'shipped' => 'đang giao đến bạn',
                    'delivered' => 'đã giao thành công',
                    'cancelled' => 'đã hủy',
                ][$latestOrder->status] ?? 'đã được cập nhật';

                $customerNotifications->push([
                    'key' => 'order-' . $latestOrder->id . '-' . $latestOrder->status . '-' . $latestOrder->payment_status . '-' . optional($latestOrder->updated_at)->timestamp,
                    'title' => 'Cập nhật đơn ' . $latestOrder->order_number,
                    'body' => 'Đơn hàng ' . $orderStatus . '. Tổng tiền: ' . $latestOrder->formatted_total . '.',
                    'url' => route('auth.customer.orders'),
                    'icon' => 'fa-receipt',
                ]);
            }

        }

        $notificationCount = $customerNotifications->count();
        $headerKeywords = [
            ['label' => 'Điện thoại', 'url' => route('products.shop', ['q' => 'điện thoại'])],
            ['label' => 'Tai nghe', 'url' => route('products.shop', ['q' => 'tai nghe'])],
            ['label' => 'Sữa cho bé', 'url' => route('products.shop', ['q' => 'sữa cho bé'])],
            ['label' => 'Bách hóa', 'url' => route('products.shop', ['q' => 'bách hóa'])],
            ['label' => 'Áo polo', 'url' => route('products.shop', ['q' => 'áo polo'])],
            ['label' => 'Khẩu trang', 'url' => route('products.shop', ['q' => 'khẩu trang'])],
            ['label' => 'Gia dụng', 'url' => route('products.shop', ['q' => 'gia dụng'])],
        ];
    @endphp

    <nav class="site-nav">
        <div class="nav-top-bar">
            <div class="nav-top-shell">
                <div class="top-link-group">
                    <a href="{{ route('home') }}">Trang chủ</a>
                    <span class="top-divider">|</span>
                    <a href="{{ route('seller.dashboard') }}">Kênh người bán</a>
                </div>

                <div class="top-link-group">
                    <a href="javascript:void(0)" onclick="document.getElementById('chatToggle')?.click(); return false;"><i class="fas fa-circle-question"></i> Hỗ trợ</a>

                    @auth
                        <span class="top-user-chip">{{ Auth::user()->email }}</span>
                    @else
                        <a href="{{ route('auth.customer.login') }}">Đăng nhập</a>
                        <span class="top-divider">|</span>
                        <a href="{{ route('auth.customer.register') }}">Đăng ký</a>
                    @endauth
                </div>
            </div>
        </div>

        <div class="nav-main-shell">
            <a href="{{ route('home') }}" class="nav-brand">
                <span class="nav-logo-mark">
                    <i class="fas fa-bag-shopping"></i>
                </span>
                <span class="nav-logo-text">MienTayShop</span>
            </a>

            <div class="header-search-stack">
                <form action="{{ route('products.search') }}" method="GET" class="header-search-form">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm sản phẩm, thương hiệu, mặt hàng đang sale..." aria-label="Tìm sản phẩm">
                    <button type="submit" aria-label="Tìm kiếm">
                        <i class="fas fa-search"></i>
                    </button>
                </form>

                <div class="header-keywords">
                    @foreach($headerKeywords as $keyword)
                        <a href="{{ $keyword['url'] }}">{{ $keyword['label'] }}</a>
                    @endforeach
                </div>
            </div>

            <div class="header-actions">
                @auth
                    <div class="notification-wrap">
                        <button type="button" class="header-icon notification-trigger" onclick="toggleNotificationMenu()" aria-label="Mở thông báo">
                            <i class="fas fa-bell"></i>
                            @if($notificationCount > 0)
                                <span class="count-pill notification-count">{{ $notificationCount }}</span>
                            @endif
                        </button>

                        <div id="notification-menu" class="notification-dropdown">
                            <div class="notification-header">
                                <strong>Thông báo của bạn</strong>
                                <span>Cập nhật đơn hàng và kênh bán.</span>
                            </div>
                            <div class="notification-list">
                                @forelse($customerNotifications as $notice)
                                    <a href="{{ $notice['url'] }}" class="notification-item" data-notification-key="{{ $notice['key'] }}">
                                        <span class="notification-icon">
                                            <i class="fas {{ $notice['icon'] }}"></i>
                                        </span>
                                        <span>
                                            <strong>{{ $notice['title'] }}</strong>
                                            <span>{{ $notice['body'] }}</span>
                                        </span>
                                    </a>
                                @empty
                                    <div class="notification-empty">
                                        <span class="notification-icon">
                                            <i class="fas fa-bell-slash"></i>
                                        </span>
                                        <span>
                                            <strong>Chưa có thông báo mới</strong>
                                            <span>Khi đơn hàng hoặc hồ sơ bán hàng thay đổi, thông tin sẽ hiển thị tại đây.</span>
                                        </span>
                                    </div>
                                @endforelse
                                @if($notificationCount > 0)
                                    <div class="notification-empty notification-empty-client" hidden>
                                        <span class="notification-icon">
                                            <i class="fas fa-bell-slash"></i>
                                        </span>
                                        <span>
                                            <strong>Chưa có thông báo mới</strong>
                                            <span>Các thông báo bạn đã xem sẽ tạm ẩn tại đây.</span>
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="user-menu-wrap">
                        <button type="button" class="user-trigger" onclick="toggleUserMenu()" aria-label="Mở menu tài khoản">
                            <i class="fas fa-user"></i>
                        </button>

                        <div id="user-menu" class="user-dropdown">
                            <div style="padding: 14px 16px; border-bottom: 1px solid #e2e8f0;">
                                <div style="font-weight: 900; color: #0f172a;">{{ Auth::user()->name }}</div>
                                <div style="font-size: 13px; color: #64748b;">{{ Auth::user()->email }}</div>
                            </div>
                            <a href="{{ route('auth.customer.profile') }}">
                                <i class="fas fa-id-card"></i>
                                Thông tin cá nhân
                            </a>
                            <a href="{{ route('auth.customer.orders') }}">
                                <i class="fas fa-receipt"></i>
                                Lịch sử mua hàng
                            </a>
                            <a href="{{ route('cart.index') }}">
                                <i class="fas fa-basket-shopping"></i>
                                Giỏ hàng
                            </a>
                            <a href="{{ route('seller.dashboard') }}">
                                <i class="fas fa-store"></i>
                                Kênh người bán
                            </a>
                            <form method="POST" action="{{ route('auth.customer.logout') }}" style="margin: 0;">
                                @csrf
                                <button type="submit">
                                    <i class="fas fa-right-from-bracket"></i>
                                    Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('auth.customer.login') }}" class="header-auth-pill">Đăng nhập</a>
                @endauth

                <a href="{{ route('cart.index') }}" class="header-icon" title="Giỏ hàng">
                    <i class="fas fa-cart-shopping"></i>
                </a>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="alert-stack">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-circle-check"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-circle-exclamation"></i>
                    {{ session('error') }}
                </div>
            @endif
        </div>

        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="footer-shell">
            <div class="footer-grid">
                <div>
                    <div class="footer-title">MienTayShop</div>
                    <p style="margin: 0 0 16px; color: rgba(255,255,255,0.72); max-width: 520px;">
                        MienTayShop là cửa hàng đa ngành với danh mục rõ ràng, giỏ hàng, đặt hàng, lịch sử mua và chatbot tư vấn ở góc phải mọi trang.
                    </p>
                </div>

                <div>
                    <div class="footer-title">Danh mục</div>
                    <div class="footer-list">
                        <a href="{{ route('products.shop', ['q' => 'điện thoại']) }}">Điện thoại & phụ kiện</a>
                        <a href="{{ route('products.shop', ['q' => 'tai nghe']) }}">Tai nghe & loa</a>
                        <a href="{{ route('products.shop', ['q' => 'sữa']) }}">Sữa & dinh dưỡng</a>
                        <a href="{{ route('products.shop', ['q' => 'khẩu trang']) }}">Sức khỏe & chăm sóc</a>
                        <a href="{{ route('products.shop', ['q' => 'văn phòng phẩm']) }}">Nhà sách online</a>
                    </div>
                </div>

                <div>
                    <div class="footer-title">Tiện ích</div>
                    <div class="footer-list">
                        <a href="{{ route('products.search') }}">Tìm kiếm sản phẩm</a>
                        <a href="{{ route('cart.index') }}">Giỏ hàng</a>
                        @auth
                            <a href="{{ route('auth.customer.orders') }}">Lịch sử mua hàng</a>
                        @else
                            <a href="{{ route('auth.customer.login') }}">Đăng nhập để xem lịch sử mua</a>
                        @endauth
                        <a href="{{ route('auth.customer.register') }}">Tạo tài khoản</a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <span>&copy; 2026 MienTayShop. Cửa hàng đa ngành.</span>
                <span>Laravel + giỏ hàng + lịch sử mua + chatbot góc phải</span>
            </div>
        </div>
    </footer>

    @include('chat.widget')

    <script>
        function toggleUserMenu() {
            const menu = document.getElementById('user-menu');
            if (menu) {
                menu.classList.toggle('open');
            }
        }

        function toggleNotificationMenu() {
            const menu = document.getElementById('notification-menu');
            if (menu) {
                menu.classList.toggle('open');
            }
        }

        document.addEventListener('click', function (event) {
            const menu = document.getElementById('user-menu');
            const trigger = event.target.closest('.user-trigger');
            const notificationMenu = document.getElementById('notification-menu');
            const notificationTrigger = event.target.closest('.notification-trigger');

            if (menu && !menu.contains(event.target) && !trigger) {
                menu.classList.remove('open');
            }

            if (notificationMenu && !notificationMenu.contains(event.target) && !notificationTrigger) {
                notificationMenu.classList.remove('open');
            }
        });

        (function setupCustomerNotifications() {
            const storageKey = 'mientayshop_seen_customer_notifications_{{ auth()->id() ?: 'guest' }}';
            const menu = document.getElementById('notification-menu');
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
                const visibleItems = Array.from(menu.querySelectorAll('.notification-item'))
                    .filter((item) => !item.hidden);
                const countPill = document.querySelector('.notification-count');
                const emptyState = menu.querySelector('.notification-empty-client');

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
            menu.querySelectorAll('.notification-item[data-notification-key]').forEach((item) => {
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

        setTimeout(function () {
            document.querySelectorAll('.alert').forEach(function (alert) {
                alert.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-6px)';
                setTimeout(function () {
                    alert.remove();
                }, 250);
            });
        }, 5000);
    </script>
    @stack('scripts')
</body>
</html>
