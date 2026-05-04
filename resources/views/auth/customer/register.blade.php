@extends('layouts.app')

@section('title', 'Đăng ký - MienTayShop')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 52%, #0891b2 100%);">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0 bg-gradient-to-br from-sky-400/25 to-transparent"></div>
        <div class="absolute top-0 left-0 w-full h-full"
             style="background-image: radial-gradient(circle at 25% 25%, #60a5fa 0%, transparent 35%), radial-gradient(circle at 75% 70%, #22d3ee 0%, transparent 30%);"></div>
    </div>

    <div class="max-w-md w-full space-y-8 relative z-10">
        <div class="text-center animate-fade-in-up" data-animation="fade-in-up">
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-sky-500 rounded-2xl flex items-center justify-center animate-glow">
                    <i class="fas fa-user-plus text-white text-4xl"></i>
                </div>
            </div>
            <h2 class="text-3xl font-bold text-white mb-2">Tạo tài khoản mới</h2>
            <p class="text-gray-300 text-lg">Đăng ký để mua sắm tại cửa hàng đa ngành và nhận hỗ trợ từ chatbot tư vấn.</p>
        </div>

        <div class="glass-card p-8 animate-fade-in-scale" data-animation="fade-in-scale">
            <form method="POST" action="{{ route('auth.customer.register') }}" class="auth-form space-y-6">
                @csrf

                <div class="form-group">
                    <label for="name" class="block text-sm font-medium text-white mb-2">
                        <i class="fas fa-user mr-2 text-sky-300"></i>Họ và tên
                    </label>
                    <input id="name"
                           name="name"
                           type="text"
                           autocomplete="name"
                           required
                           value="{{ old('name') }}"
                           class="w-full px-4 py-3 border-2 border-white/20 rounded-xl bg-white/10 text-white placeholder-white/60 backdrop-blur-md focus:border-sky-400 focus:ring-4 focus:ring-sky-400/20 transition-all duration-300 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="block text-sm font-medium text-white mb-2">
                        <i class="fas fa-envelope mr-2 text-sky-300"></i>Email
                    </label>
                    <input id="email"
                           name="email"
                           type="email"
                           autocomplete="email"
                           required
                           value="{{ old('email') }}"
                           class="w-full px-4 py-3 border-2 border-white/20 rounded-xl bg-white/10 text-white placeholder-white/60 backdrop-blur-md focus:border-sky-400 focus:ring-4 focus:ring-sky-400/20 transition-all duration-300 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="block text-sm font-medium text-white mb-2">
                        <i class="fas fa-phone mr-2 text-sky-300"></i>Số điện thoại <span class="text-gray-400">(tùy chọn)</span>
                    </label>
                    <input id="phone"
                           name="phone"
                           type="tel"
                           autocomplete="tel"
                           value="{{ old('phone') }}"
                           class="w-full px-4 py-3 border-2 border-white/20 rounded-xl bg-white/10 text-white placeholder-white/60 backdrop-blur-md focus:border-sky-400 focus:ring-4 focus:ring-sky-400/20 transition-all duration-300 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="block text-sm font-medium text-white mb-2">
                        <i class="fas fa-lock mr-2 text-sky-300"></i>Mật khẩu
                    </label>
                    <div class="relative">
                        <input id="password"
                               name="password"
                               type="password"
                               autocomplete="new-password"
                               required
                               class="w-full px-4 py-3 border-2 border-white/20 rounded-xl bg-white/10 text-white placeholder-white/60 backdrop-blur-md focus:border-sky-400 focus:ring-4 focus:ring-sky-400/20 transition-all duration-300 @error('password') border-red-500 @enderror"
                               style="letter-spacing: 0.1em;">
                        <button type="button"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-white/60 hover:text-white transition-colors"
                                onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="password-toggle-icon"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="block text-sm font-medium text-white mb-2">
                        <i class="fas fa-lock mr-2 text-sky-300"></i>Xác nhận mật khẩu
                    </label>
                    <div class="relative">
                        <input id="password_confirmation"
                               name="password_confirmation"
                               type="password"
                               autocomplete="new-password"
                               required
                               class="w-full px-4 py-3 border-2 border-white/20 rounded-xl bg-white/10 text-white placeholder-white/60 backdrop-blur-md focus:border-sky-400 focus:ring-4 focus:ring-sky-400/20 transition-all duration-300"
                               style="letter-spacing: 0.1em;">
                        <button type="button"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-white/60 hover:text-white transition-colors"
                                onclick="togglePassword('password_confirmation')">
                            <i class="fas fa-eye" id="password_confirmation-toggle-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-start">
                    <input id="terms"
                           name="terms"
                           type="checkbox"
                           required
                           class="w-4 h-4 text-blue-500 bg-white/10 border-white/20 rounded focus:ring-sky-400 focus:ring-2 mt-1">
                    <label for="terms" class="ml-3 text-sm text-white leading-5">
                        Tôi đồng ý với
                        <a href="#" class="text-sky-300 hover:text-sky-200 transition-colors">điều khoản sử dụng</a>
                        và
                        <a href="#" class="text-sky-300 hover:text-sky-200 transition-colors">chính sách bảo mật</a>
                        của MienTayShop.
                    </label>
                </div>

                <button type="submit"
                        class="btn-modern w-full bg-gradient-to-r from-blue-500 to-sky-500 text-white py-3 px-6 rounded-xl font-semibold hover:from-blue-600 hover:to-sky-600 focus:outline-none focus:ring-4 focus:ring-sky-400/50 transform hover:scale-105 transition-all duration-300 hover-ripple">
                    <i class="fas fa-user-plus mr-2"></i>
                    Tạo tài khoản
                </button>

                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-white/20"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-transparent text-white/60">hoặc</span>
                    </div>
                </div>

                <div class="text-center">
                    <p class="text-white/80">
                        Đã có tài khoản?
                        <a href="{{ route('auth.customer.login') }}"
                           class="text-sky-300 hover:text-sky-200 font-medium transition-colors hover:underline">
                            Đăng nhập ngay
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <div class="text-center animate-slide-in-left" data-animation="slide-in-left">
            <a href="{{ route('home') }}"
               class="inline-flex items-center text-white/60 hover:text-white transition-colors group">
                <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                Quay về trang chủ
            </a>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const toggleIcon = document.getElementById(fieldId + '-toggle-icon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>
@endsection
