<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập quản trị - MienTayShop</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --brand: #2563eb;
            --brand-dark: #1e3a8a;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at 18% 18%, rgba(255, 255, 255, 0.18), transparent 28%),
                radial-gradient(circle at 82% 20%, rgba(6, 182, 212, 0.26), transparent 26%),
                linear-gradient(135deg, #020617 0%, #172554 58%, #0891b2 100%);
            color: var(--ink);
            font-family: "Segoe UI", "Be Vietnam Pro", Arial, sans-serif;
            padding: 24px;
        }

        .login-shell {
            width: min(100%, 1020px);
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) 420px;
            gap: 24px;
            align-items: stretch;
        }

        .brand-panel,
        .login-card {
            border-radius: 30px;
            box-shadow: 0 30px 70px rgba(15, 23, 42, 0.25);
        }

        .brand-panel {
            position: relative;
            overflow: hidden;
            padding: 44px;
            color: white;
            background:
                radial-gradient(circle at 78% 28%, rgba(255, 255, 255, 0.18), transparent 22%),
                rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(18px);
        }

        .brand-panel::after {
            content: "";
            position: absolute;
            right: -110px;
            bottom: -130px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 46px;
            font-weight: 950;
            font-size: 24px;
            letter-spacing: -0.05em;
        }

        .brand-logo span:first-child {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--brand), #06b6d4);
        }

        .brand-panel h1 {
            position: relative;
            z-index: 1;
            max-width: 620px;
            margin: 0 0 16px;
            font-size: clamp(38px, 5vw, 66px);
            line-height: 0.96;
            letter-spacing: -0.07em;
            font-weight: 950;
        }

        .brand-panel p {
            position: relative;
            z-index: 1;
            max-width: 560px;
            margin: 0;
            color: rgba(255, 255, 255, 0.82);
            font-size: 17px;
            line-height: 1.75;
        }

        .brand-metrics {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 42px;
        }

        .brand-metrics div {
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.12);
            padding: 18px;
        }

        .brand-metrics strong {
            display: block;
            margin-bottom: 6px;
            font-size: 24px;
            font-weight: 950;
        }

        .brand-metrics span {
            color: rgba(255, 255, 255, 0.72);
            font-size: 12px;
            font-weight: 800;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(20px);
            padding: 32px;
        }

        .login-card h2 {
            margin: 0 0 8px;
            color: var(--ink);
            font-size: 28px;
            font-weight: 950;
            letter-spacing: -0.05em;
        }

        .login-card > p {
            margin: 0 0 24px;
            color: var(--muted);
            line-height: 1.6;
            font-weight: 600;
        }

        .alert {
            margin-bottom: 16px;
            border-radius: 16px;
            padding: 12px 14px;
            font-size: 14px;
            font-weight: 700;
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

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-size: 13px;
            font-weight: 900;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: white;
            color: var(--ink);
            padding: 14px 14px 14px 44px;
            font-size: 15px;
            outline: none;
            transition: all 0.2s ease;
        }

        input:focus {
            border-color: rgba(37, 99, 235, 0.65);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 20px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 700;
        }

        .login-btn {
            width: 100%;
            border: none;
            border-radius: 999px;
            background: linear-gradient(135deg, #111827, var(--brand));
            color: white;
            padding: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            font-size: 15px;
            font-weight: 900;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 34px rgba(37, 99, 235, 0.24);
        }

        .account-box {
            margin-top: 20px;
            border: 1px solid #bfdbfe;
            border-radius: 20px;
            background: #eef2ff;
            padding: 16px;
        }

        .account-box h3 {
            margin: 0 0 10px;
            color: var(--brand-dark);
            font-size: 14px;
            font-weight: 900;
        }

        .account-box p {
            margin: 5px 0;
            color: #475569;
            font-size: 13px;
            font-weight: 700;
        }

        .home-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 18px;
            color: white;
            text-decoration: none;
            font-weight: 800;
        }

        @media (max-width: 900px) {
            .login-shell {
                grid-template-columns: 1fr;
            }

            .brand-panel {
                padding: 30px;
            }

            .brand-metrics {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <section class="brand-panel">
            <div class="brand-logo">
                <span><i class="fas fa-bag-shopping"></i></span>
                <span>MienTayShop</span>
            </div>
            <h1>Quản trị shop đa ngành của bạn</h1>
            <p>
                Khu vực admin dùng để quản lý khách hàng, danh mục, người bán và đơn hàng toàn hệ thống.
                Giao diện được đồng bộ với trang bán hàng để bạn thao tác nhanh và dễ theo dõi hơn.
            </p>
            <div class="brand-metrics">
                <div>
                    <strong>353+</strong>
                    <span>Sản phẩm đang bán</span>
                </div>
                <div>
                    <strong>20</strong>
                    <span>Nhóm danh mục</span>
                </div>
                <div>
                    <strong>100+</strong>
                    <span>Đơn hàng mẫu</span>
                </div>
            </div>
        </section>

        <section>
            <div class="login-card">
                <h2>Đăng nhập admin</h2>
                <p>Vào bảng điều khiển MienTayShop để cập nhật dữ liệu cửa hàng.</p>

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

                <form method="POST" action="{{ route('admin.login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email">Email admin</label>
                        <div class="input-wrap">
                            <i class="fas fa-envelope"></i>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus>
                        </div>
                        @error('email')
                            <p style="margin-top: 8px; color: #dc2626; font-size: 13px; font-weight: 700;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <div class="input-wrap">
                            <i class="fas fa-lock"></i>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required>
                        </div>
                        @error('password')
                            <p style="margin-top: 8px; color: #dc2626; font-size: 13px; font-weight: 700;">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="remember">
                        <input type="checkbox" name="remember" id="remember">
                        Ghi nhớ đăng nhập
                    </label>

                    <button type="submit" class="login-btn" id="loginBtn">
                        <i class="fas fa-right-to-bracket"></i>
                        <span id="loginText">Đăng nhập</span>
                    </button>
                </form>

                <div class="account-box">
                    <h3><i class="fas fa-circle-info"></i> Tài khoản quản trị</h3>
                    <p><strong>Email:</strong> admin@mientayshop.com</p>
                    <p><strong>Mật khẩu:</strong> admin123</p>
                </div>
            </div>

            <a href="{{ route('home') }}" class="home-link">
                <i class="fas fa-arrow-left"></i>
                Quay lại trang bán hàng
            </a>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            const loginBtn = document.getElementById('loginBtn');
            const loginText = document.getElementById('loginText');

            form.addEventListener('submit', function () {
                loginBtn.disabled = true;
                loginBtn.style.opacity = '0.75';
                loginText.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang đăng nhập...';
            });

            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-8px)';
                    setTimeout(() => alert.remove(), 260);
                });
            }, 5000);
        });
    </script>
</body>
</html>
