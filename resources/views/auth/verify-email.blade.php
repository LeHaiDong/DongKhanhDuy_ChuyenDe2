@extends('layouts.app')

@section('title', 'Xác thực email - MienTayShop')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 52%, #0891b2 100%);">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0 bg-gradient-to-br from-sky-400/20 to-transparent"></div>
        <div class="absolute top-0 left-0 w-full h-full"
             style="background-image: radial-gradient(circle at 25% 25%, #60a5fa 0%, transparent 35%), radial-gradient(circle at 75% 70%, #22d3ee 0%, transparent 30%);"></div>
    </div>

    <div class="max-w-md w-full space-y-8 relative z-10">
        <div class="text-center animate-fade-in-up" data-animation="fade-in-up">
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-sky-500 rounded-2xl flex items-center justify-center animate-glow">
                    <i class="fas fa-envelope-open-text text-white text-4xl"></i>
                </div>
            </div>
            <h2 class="text-3xl font-bold text-white mb-2">Xác thực email</h2>
            <p class="text-gray-300 text-lg">Nhập mã 6 số vừa được gửi để kích hoạt tài khoản mua sắm.</p>
            @auth
                <p class="text-sky-300 text-sm mt-2">{{ Auth::user()->email }}</p>
            @endauth
        </div>

        <div class="glass-card p-8 animate-fade-in-scale" data-animation="fade-in-scale">
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-500/20 border border-green-500/30 rounded-lg text-green-400 text-sm">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-6 p-4 bg-yellow-500/20 border border-yellow-500/30 rounded-lg text-yellow-400 text-sm">
                    <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('warning') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-500/20 border border-red-500/30 rounded-lg text-red-400 text-sm">
                    <i class="fas fa-times-circle mr-2"></i>{{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('verification.verify') }}" class="space-y-6">
                @csrf

                <div class="form-group">
                    <label for="verification_code" class="block text-sm font-medium text-white mb-2">
                        <i class="fas fa-key mr-2 text-sky-300"></i>Mã xác thực (6 số)
                    </label>
                    <input id="verification_code"
                           name="verification_code"
                           type="text"
                           maxlength="6"
                           pattern="[0-9]{6}"
                           required
                           value="{{ old('verification_code') }}"
                           class="w-full px-4 py-3 border-2 border-white/20 rounded-xl bg-white/10 text-white placeholder-white/60 backdrop-blur-md focus:border-sky-400 focus:ring-4 focus:ring-sky-400/20 transition-all duration-300 text-center text-2xl font-mono letter-spacing-wide @error('verification_code') border-red-500 @enderror"
                           autocomplete="off">
                    @error('verification_code')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-white/5 border border-white/10 rounded-lg p-4 text-sm text-white/80">
                    <h4 class="font-semibold text-white mb-2">
                        <i class="fas fa-circle-info mr-2 text-blue-400"></i>Hướng dẫn:
                    </h4>
                    <ul class="space-y-1 text-xs">
                        <li>• Kiểm tra hộp thư đến và cả mục spam.</li>
                        <li>• Mã xác thực có hiệu lực trong 15 phút.</li>
                        <li>• Nhập đúng 6 chữ số rồi bấm xác thực.</li>
                    </ul>
                </div>

                <button type="submit"
                        class="btn-modern w-full bg-gradient-to-r from-blue-500 to-sky-500 text-white py-3 px-6 rounded-xl font-semibold hover:from-blue-600 hover:to-sky-600 focus:outline-none focus:ring-4 focus:ring-sky-400/50 transform hover:scale-105 transition-all duration-300">
                    <i class="fas fa-check mr-2"></i>
                    Xác thực email
                </button>
            </form>

            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-white/20"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-transparent text-white/60">hoặc</span>
                </div>
            </div>

            <form method="POST" action="{{ route('verification.resend') }}" class="text-center">
                @csrf
                <p class="text-white/80 text-sm mb-4">
                    Chưa nhận được email?
                </p>
                <button type="submit"
                        class="text-sky-300 hover:text-sky-200 font-medium transition-colors hover:underline text-sm">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Gửi lại mã xác thực
                </button>
            </form>
        </div>

        <div class="text-center animate-slide-in-left" data-animation="slide-in-left">
            <a href="{{ route('auth.customer.login') }}"
               class="inline-flex items-center text-white/60 hover:text-white transition-colors group">
                <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                Quay lại đăng nhập
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('verification_code');

    codeInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 6) {
            value = value.slice(0, 6);
        }
        e.target.value = value;
    });

    codeInput.focus();
    codeInput.addEventListener('focus', function() {
        this.select();
    });
});
</script>

<style>
.letter-spacing-wide {
    letter-spacing: 0.5em;
}

.glass-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.animate-glow {
    animation: glow 2s ease-in-out infinite alternate;
}

@keyframes glow {
    from {
        box-shadow: 0 0 20px rgba(56, 189, 248, 0.45);
    }
    to {
        box-shadow: 0 0 30px rgba(37, 99, 235, 0.7);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}

.animate-fade-in-scale {
    animation: fadeInScale 0.8s ease-out 0.2s both;
}

.animate-slide-in-left {
    animation: slideInLeft 0.6s ease-out 0.4s both;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>
@endsection
