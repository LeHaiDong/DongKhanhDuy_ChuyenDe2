@extends('layouts.app')

@section('title', $lens->name . ' - Chi tiết sản phẩm')

@section('content')
<div style="min-height: 100vh; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%); padding: 36px 0 64px;">
    <div style="max-width: 1220px; margin: 0 auto; padding: 0 24px;">
        <nav style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 24px; font-size: 14px; color: #64748b;">
            <a href="{{ route('home') }}" style="color: #2563eb; text-decoration: none; font-weight: 600;">Trang chủ</a>
            <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
            <a href="{{ route('products.shop') }}" style="color: #2563eb; text-decoration: none; font-weight: 600;">Cửa hàng</a>
            <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
            <span>{{ $lens->name }}</span>
        </nav>

        <div style="display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr); gap: 30px; background: white; border: 1px solid #e2e8f0; border-radius: 28px; overflow: hidden; box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);">
            <div style="padding: 28px;">
                <div style="position: relative; aspect-ratio: 1 / 1; border-radius: 24px; overflow: hidden; background: radial-gradient(circle at top, rgba(14,165,233,0.14), transparent 35%), linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);">
                    <img src="{{ $lens->image_url }}"
                         alt="{{ $lens->name }}"
                         fetchpriority="high"
                         decoding="async"
                         onerror="this.onerror=null;this.src='{{ $lens->fallback_image_url }}';"
                         style="width: 100%; height: 100%; object-fit: cover;">

                    <div style="position: absolute; top: 18px; left: 18px; display: flex; gap: 10px; flex-wrap: wrap;">
                        <span style="padding: 8px 14px; border-radius: 999px; background: rgba(15, 23, 42, 0.85); color: white; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                            {{ $lens->display_product_type }}
                        </span>

                        <span style="padding: 8px 14px; border-radius: 999px; background: {{ $lens->stock_quantity > 0 ? '#16a34a' : '#64748b' }}; color: white; font-size: 12px; font-weight: 700;">
                            {{ $lens->stock_quantity > 0 ? 'Còn hàng' : 'Hết hàng' }}
                        </span>
                    </div>
                </div>
            </div>

            <div style="padding: 34px 30px 32px; display: flex; flex-direction: column; gap: 24px;">
                <div>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px;">
                        <span style="padding: 6px 12px; border-radius: 999px; background: #e0f2fe; color: #0369a1; font-size: 12px; font-weight: 700;">
                            {{ $lens->brand }}
                        </span>
                        <span style="padding: 6px 12px; border-radius: 999px; background: #eff6ff; color: #1d4ed8; font-size: 12px; font-weight: 700;">
                            {{ $lens->display_condition }}
                        </span>
                    </div>

                    <h1 style="font-size: clamp(30px, 4vw, 42px); line-height: 1.15; font-weight: 900; color: #0f172a; margin-bottom: 14px;">
                        {{ $lens->name }}
                    </h1>

                    <div style="font-size: 34px; font-weight: 900; color: #dc2626; margin-bottom: 18px;">
                        {{ $lens->formatted_price }}
                    </div>

                    @if($lens->description)
                        <p style="font-size: 15px; line-height: 1.8; color: #475569; margin-bottom: 0;">
                            {{ $lens->description }}
                        </p>
                    @endif
                </div>

                <div style="display: grid; gap: 12px;">
                    @foreach($lens->display_specifications as $spec)
                        <div style="display: flex; justify-content: space-between; gap: 16px; padding: 14px 16px; border-radius: 16px; background: #f8fafc; border: 1px solid #e2e8f0;">
                            <span style="font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em;">
                                {{ $spec['label'] }}
                            </span>
                            <span style="font-size: 14px; font-weight: 800; color: #0f172a; text-align: right;">
                                {{ $spec['value'] }}
                            </span>
                        </div>
                    @endforeach
                    <div style="display: flex; justify-content: space-between; gap: 16px; padding: 14px 16px; border-radius: 16px; background: #f8fafc; border: 1px solid #e2e8f0;">
                        <span style="font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em;">
                            Tồn kho
                        </span>
                        <span style="font-size: 14px; font-weight: 800; color: {{ $lens->stock_quantity > 0 ? '#16a34a' : '#dc2626' }};">
                            {{ $lens->stock_quantity }} sản phẩm
                        </span>
                    </div>
                </div>

                @if($lens->categories->isNotEmpty())
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 12px;">
                            Danh mục liên quan
                        </div>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                            @foreach($lens->categories as $category)
                                <span style="padding: 8px 12px; border-radius: 999px; background: #e0f2fe; color: #0369a1; font-size: 13px; font-weight: 600;">
                                    {{ $category->display_name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div style="display: flex; flex-wrap: wrap; gap: 14px; align-items: stretch;">
                    @if($lens->stock_quantity > 0 && $lens->is_active)
                        <div style="display: inline-flex; align-items: center; border: 1px solid #cbd5e1; border-radius: 16px; overflow: hidden; background: white;">
                            <button type="button" id="decrease-qty" style="width: 44px; border: none; background: transparent; cursor: pointer; color: #475569;">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" id="quantity" value="1" min="1" max="{{ $lens->stock_quantity }}" style="width: 64px; border: none; text-align: center; font-weight: 700; color: #0f172a; outline: none;">
                            <button type="button" id="increase-qty" style="width: 44px; border: none; background: transparent; cursor: pointer; color: #475569;">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>

                        <button id="add-to-cart-btn"
                                data-lens-id="{{ $lens->id }}"
                                style="flex: 1; min-width: 220px; border: none; border-radius: 18px; background: linear-gradient(135deg, #111827 0%, #1f2937 100%); color: white; padding: 16px 20px; font-size: 16px; font-weight: 800; cursor: pointer;">
                            <i class="fas fa-shopping-cart" style="margin-right: 10px;"></i>
                            Thêm vào giỏ hàng
                        </button>

                        <button id="buy-now-btn"
                                data-lens-id="{{ $lens->id }}"
                                style="flex: 1; min-width: 200px; border: none; border-radius: 18px; background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%); color: white; padding: 16px 20px; font-size: 16px; font-weight: 800; cursor: pointer;">
                            <i class="fas fa-bolt" style="margin-right: 10px;"></i>
                            Mua ngay
                        </button>
                    @else
                        <button disabled
                                style="flex: 1; border: none; border-radius: 18px; background: #cbd5e1; color: #475569; padding: 16px 20px; font-size: 16px; font-weight: 800;">
                            <i class="fas fa-times-circle" style="margin-right: 10px;"></i>
                            Tạm hết hàng
                        </button>
                    @endif

                    <a href="{{ route('products.shop') }}"
                       style="display: inline-flex; align-items: center; justify-content: center; padding: 16px 20px; border-radius: 18px; border: 1px solid #cbd5e1; color: #0f172a; text-decoration: none; font-weight: 800; white-space: nowrap;">
                        <i class="fas fa-arrow-left" style="margin-right: 10px;"></i>
                        Quay lại cửa hàng
                    </a>
                </div>
            </div>
        </div>

        <div style="margin-top: 30px; background: white; border: 1px solid #e2e8f0; border-radius: 24px; padding: 28px;">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;">
                <div>
                    <h2 style="font-size: 24px; font-weight: 900; color: #0f172a; margin-bottom: 10px;">Cần tư vấn thêm?</h2>
                    <p style="font-size: 15px; line-height: 1.7; color: #64748b; margin: 0;">
                        Bạn có thể xem thêm sản phẩm cùng nhóm hàng, so sánh thương hiệu khác hoặc hỏi chatbot để được gợi ý nhanh.
                    </p>
                </div>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="{{ route('products.shop', ['product_type' => $lens->product_type]) }}"
                       style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 16px; border-radius: 999px; background: #e0f2fe; color: #0369a1; text-decoration: none; font-weight: 800;">
                        <i class="fas fa-layer-group"></i>
                        Xem cùng loại
                    </a>
                    <button type="button" onclick="document.getElementById('chatToggle')?.click()"
                       style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 16px; border-radius: 999px; background: #111827; color: white; text-decoration: none; font-weight: 800;">
                        <i class="fas fa-comments"></i>
                        Hỏi chatbot
                    </button>
                </div>
            </div>
        </div>

        <section class="reviews-box">
            <div class="reviews-head">
                <div>
                    <span>Đánh giá sản phẩm</span>
                    <h2>{{ $reviewStats['average'] ?: 0 }}/5 sao</h2>
                    <p>{{ $reviewStats['count'] }} đánh giá đã được duyệt</p>
                </div>
                <div class="stars-large">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star {{ $i <= round($reviewStats['average']) ? 'active' : '' }}"></i>
                    @endfor
                </div>
            </div>

            @auth
                <form method="POST" action="{{ route('products.reviews.store', $lens) }}" class="review-form">
                    @csrf
                    <div class="review-grid">
                        <label>
                            Số sao
                            <select name="rating" required>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}" {{ (int) old('rating', $userReview->rating ?? 5) === $i ? 'selected' : '' }}>{{ $i }} sao</option>
                                @endfor
                            </select>
                        </label>
                        <label>
                            Tiêu đề
                            <input name="title" value="{{ old('title', $userReview->title ?? '') }}">
                        </label>
                    </div>
                    <label>
                        Nội dung đánh giá
                        <textarea name="content" rows="4" required>{{ old('content', $userReview->content ?? '') }}</textarea>
                    </label>
                    <button type="submit">
                        <i class="fas fa-paper-plane"></i>
                        {{ $userReview ? 'Cập nhật đánh giá' : 'Gửi đánh giá' }}
                    </button>
                </form>
            @else
                <div class="review-login">
                    <i class="fas fa-user-lock"></i>
                    <span>Đăng nhập để viết đánh giá sản phẩm.</span>
                    <a href="{{ route('auth.customer.login') }}">Đăng nhập</a>
                </div>
            @endauth

            <div class="review-list">
                @forelse($lens->approvedReviews as $review)
                    <article class="review-item">
                        <div class="review-avatar">{{ mb_substr($review->user->name ?? 'K', 0, 1) }}</div>
                        <div>
                            <div class="review-top">
                                <strong>{{ $review->user->name ?? 'Khách hàng' }}</strong>
                                <span>
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $review->rating ? 'active' : '' }}"></i>
                                    @endfor
                                </span>
                            </div>
                            @if($review->title)
                                <h3>{{ $review->title }}</h3>
                            @endif
                            <p>{{ $review->content }}</p>
                            <small>{{ $review->formatted_created_at }} {{ $review->is_verified_purchase ? '• Đã mua hàng' : '' }}</small>
                        </div>
                    </article>
                @empty
                    <div class="review-empty">Chưa có đánh giá nào. Bạn có thể là người đánh giá đầu tiên.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>

<style>
.reviews-box {
    margin-top: 30px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 24px;
    padding: 28px;
}

.reviews-head {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.reviews-head span {
    color: #0284c7;
    font-size: 12px;
    font-weight: 900;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.reviews-head h2 {
    margin: 6px 0;
    font-size: 34px;
    font-weight: 950;
    color: #0f172a;
}

.stars-large,
.review-top span {
    color: #cbd5e1;
}

.stars-large .active,
.review-top .active {
    color: #f59e0b;
}

.review-form {
    display: grid;
    gap: 14px;
    padding: 18px;
    border-radius: 20px;
    background: #f8fafc;
    margin-bottom: 22px;
}

.review-grid {
    display: grid;
    grid-template-columns: 180px minmax(0, 1fr);
    gap: 14px;
}

.review-form label {
    display: grid;
    gap: 8px;
    color: #334155;
    font-weight: 800;
}

.review-form input,
.review-form select,
.review-form textarea {
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    padding: 12px 14px;
    outline: none;
}

.review-form button,
.review-login a {
    width: fit-content;
    border: 0;
    border-radius: 999px;
    background: #2563eb;
    color: white;
    padding: 12px 18px;
    font-weight: 900;
    text-decoration: none;
    cursor: pointer;
}

.review-login {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    padding: 16px;
    border-radius: 18px;
    background: #eff6ff;
    color: #1e40af;
    margin-bottom: 22px;
}

.review-list {
    display: grid;
    gap: 14px;
}

.review-item {
    display: grid;
    grid-template-columns: 48px minmax(0, 1fr);
    gap: 14px;
    padding: 16px;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
}

.review-avatar {
    width: 48px;
    height: 48px;
    border-radius: 999px;
    background: #111827;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 950;
}

.review-top {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.review-item h3 {
    margin: 8px 0 4px;
    color: #0f172a;
}

.review-item p {
    margin: 0 0 8px;
    color: #475569;
}

.review-item small,
.review-empty {
    color: #64748b;
}

@media (max-width: 900px) {
    div[style*="grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr)"] {
        grid-template-columns: 1fr !important;
    }

    .review-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('quantity');
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    const buyNowBtn = document.getElementById('buy-now-btn');

    document.getElementById('decrease-qty')?.addEventListener('click', function() {
        quantityInput.value = Math.max(1, (parseInt(quantityInput.value, 10) || 1) - 1);
    });

    document.getElementById('increase-qty')?.addEventListener('click', function() {
        const max = parseInt(quantityInput.max, 10) || 1;
        quantityInput.value = Math.min(max, (parseInt(quantityInput.value, 10) || 1) + 1);
    });

    addToCartBtn?.addEventListener('click', function() {
        handleCartAction(this, false);
    });

    buyNowBtn?.addEventListener('click', function() {
        handleCartAction(this, true);
    });
});

function handleCartAction(button, redirectToCheckout) {
    const quantityInput = document.getElementById('quantity');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 10px;"></i>Đang xử lý...';
    button.disabled = true;

    fetch(redirectToCheckout ? '/cart/buy-now' : '/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            camera_lens_id: button.dataset.lensId,
            quantity: parseInt(quantityInput.value, 10) || 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            createNotification(data.message || 'Không thể thêm vào giỏ hàng.', 'error');
            return;
        }

        if (redirectToCheckout) {
            window.location.href = data.redirect_url || '/cart/checkout';
            return;
        }

        updateCartCount(data.cart_count);
        button.innerHTML = '<i class="fas fa-check" style="margin-right: 10px;"></i>Đã thêm';
        button.style.background = '#16a34a';
        createNotification('Đã thêm sản phẩm vào giỏ hàng.', 'success');
    })
    .catch(() => {
        createNotification('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
    })
    .finally(() => {
        setTimeout(() => {
            button.disabled = false;
            button.innerHTML = originalText;
            button.style.background = button.id === 'buy-now-btn'
                ? 'linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%)'
                : 'linear-gradient(135deg, #111827 0%, #1f2937 100%)';
        }, redirectToCheckout ? 100 : 1200);
    });
}

function updateCartCount(count) {
    document.querySelectorAll('.cart-count').forEach(element => {
        element.textContent = count;
        if (count > 0) {
            element.classList.remove('hidden');
        } else {
            element.classList.add('hidden');
        }
    });
}

function createNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 z-50 px-6 py-3 rounded-xl text-white font-medium transition-all duration-300 transform translate-x-full';

    notification.style.background = type === 'success'
        ? '#16a34a'
        : (type === 'error' ? '#dc2626' : '#2563eb');

    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
            ${message}
        </div>
    `;

    document.body.appendChild(notification);

    requestAnimationFrame(() => {
        notification.style.transform = 'translateX(0)';
    });

    setTimeout(() => {
        notification.style.transform = 'translateX(110%)';
        setTimeout(() => notification.remove(), 300);
    }, 2500);
}
</script>
@endsection
