<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 32px;" id="products-grid">
    @forelse($lenses as $lens)
        <div class="product-card"
             style="background: white; border-radius: 18px; overflow: hidden; border: 1px solid #edf2f7; transition: all 0.35s ease; box-shadow: 0 14px 40px rgba(15, 23, 42, 0.08); cursor: pointer;"
             onclick="window.location.href='{{ route('products.show', $lens) }}'"
             onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 20px 48px rgba(15, 23, 42, 0.14)'"
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 14px 40px rgba(15, 23, 42, 0.08)'">

            <div style="position: relative; aspect-ratio: 1 / 1; overflow: hidden; background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);">
                @if($lens->image_url)
                    <img src="{{ $lens->image_url }}"
                         alt="{{ $lens->name }}"
                         loading="lazy"
                         decoding="async"
                         style="width: 100%; height: 100%; object-fit: contain; padding: 12px; box-sizing: border-box; background: #ffffff; transition: transform 0.4s ease;"
                         onerror="this.onerror=null;this.src='{{ $lens->fallback_image_url }}';"
                         onmouseover="this.style.transform='scale(1.08)'"
                         onmouseout="this.style.transform='scale(1)'">
                @else
                    <div style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px; background: radial-gradient(circle at top, rgba(37, 99, 235, 0.16), transparent 55%), linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%); color: #1d4ed8;">
                        <i class="{{ $lens->placeholder_icon }}" style="font-size: 54px;"></i>
                        <span style="font-size: 13px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;">
                            {{ $lens->display_product_type }}
                        </span>
                    </div>
                @endif

                <div style="position: absolute; top: 16px; left: 16px; display: flex; flex-wrap: wrap; gap: 8px; max-width: calc(100% - 32px);">
                    <span style="background: rgba(15, 23, 42, 0.86); color: white; padding: 6px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;">
                        {{ $lens->display_product_type }}
                    </span>

                    @if($lens->stock_quantity <= 5 && $lens->stock_quantity > 0)
                        <span style="background: #0284c7; color: white; padding: 6px 12px; border-radius: 999px; font-size: 11px; font-weight: 700;">
                            Sắp hết
                        </span>
                    @elseif($lens->stock_quantity <= 0)
                        <span style="background: #475569; color: white; padding: 6px 12px; border-radius: 999px; font-size: 11px; font-weight: 700;">
                            Hết hàng
                        </span>
                    @endif
                </div>

                <div class="quick-action"
                     style="position: absolute; top: 16px; right: 16px; opacity: 0; transition: opacity 0.25s ease; display: flex; flex-direction: column; gap: 10px;">
                    @if($lens->stock_quantity > 0)
                        <button class="quick-add-btn"
                                data-lens-id="{{ $lens->id }}"
                                onclick="event.stopPropagation(); quickAddToCart({{ $lens->id }})"
                                style="background: rgba(15, 23, 42, 0.88); color: white; border: none; width: 42px; height: 42px; border-radius: 999px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    @endif

                </div>
            </div>

            <div style="padding: 22px 18px 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 10px;">
                    <span style="font-size: 12px; font-weight: 700; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.08em;">
                        {{ $lens->brand }}
                    </span>
                    <span style="font-size: 12px; color: #64748b;">
                        {{ $lens->display_condition }}
                    </span>
                </div>

                <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 16px; line-height: 1.35; min-height: 48px;">
                    {{ $lens->name }}
                </h3>

                <div style="display: grid; gap: 10px; margin-bottom: 18px;">
                    @foreach($lens->display_specifications->take(3) as $spec)
                        <div style="display: flex; justify-content: space-between; gap: 12px; padding: 10px 12px; border-radius: 12px; background: #f8fafc;">
                            <span style="font-size: 12px; color: #64748b; font-weight: 600;">
                                {{ $spec['label'] }}
                            </span>
                            <span style="font-size: 12px; color: #0f172a; font-weight: 700; text-align: right;">
                                {{ $spec['value'] }}
                            </span>
                        </div>
                    @endforeach
                </div>

                @if($lens->categories->isNotEmpty())
                    <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px;">
                        @foreach($lens->categories->take(2) as $category)
                            <span style="padding: 5px 10px; border-radius: 999px; background: #eff6ff; color: #1d4ed8; font-size: 11px; font-weight: 600;">
                                {{ $category->display_name }}
                            </span>
                        @endforeach
                    </div>
                @endif

                <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 18px;">
                    <div style="font-size: 22px; font-weight: 800; color: #111827;">
                        {{ $lens->formatted_price }}
                    </div>
                    <div style="font-size: 12px; color: #64748b;">
                        Tồn kho: {{ $lens->stock_quantity }}
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    @if($lens->stock_quantity > 0)
                        <form action="{{ route('cart.add') }}" method="POST" style="flex: 1;" onclick="event.stopPropagation();">
                            @csrf
                            <input type="hidden" name="camera_lens_id" value="{{ $lens->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit"
                                    style="width: 100%; background: linear-gradient(135deg, #111827 0%, #1f2937 100%); color: white; border: none; padding: 12px 16px; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: pointer;">
                                <i class="fas fa-shopping-cart" style="margin-right: 8px;"></i>
                                Thêm vào giỏ
                            </button>
                        </form>
                        <form action="{{ route('cart.add') }}" method="POST" style="flex: 1;" onclick="event.stopPropagation();">
                            @csrf
                            <input type="hidden" name="camera_lens_id" value="{{ $lens->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="buy_now" value="1">
                            <button type="submit"
                                    style="width: 100%; background: #1d4ed8; color: white; border: none; padding: 12px 16px; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: pointer;">
                                <i class="fas fa-bolt" style="margin-right: 8px;"></i>
                                Mua ngay
                            </button>
                        </form>
                    @else
                        <button disabled
                                style="flex: 1; background: #cbd5e1; color: #475569; border: none; padding: 12px 16px; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: not-allowed;">
                            <i class="fas fa-times" style="margin-right: 8px;"></i>
                            Hết hàng
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; display: flex; justify-content: center; padding: 36px 0;">
            <div style="max-width: 460px; width: 100%; background: white; border: 1px solid #e2e8f0; border-radius: 24px; padding: 42px 28px; text-align: center; box-shadow: 0 18px 42px rgba(15, 23, 42, 0.06);">
                <div style="width: 84px; height: 84px; margin: 0 auto 18px; border-radius: 999px; background: #eff6ff; color: #1d4ed8; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-search" style="font-size: 30px;"></i>
                </div>
                <h3 style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">
                    Không tìm thấy sản phẩm
                </h3>
                <p style="font-size: 15px; color: #64748b; line-height: 1.6; margin-bottom: 22px;">
                    Thử thu gọn bộ lọc hoặc tìm với nhóm hàng, thương hiệu và từ khóa khác.
                </p>
                <a href="{{ route('products.shop') }}"
                   style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 18px; border-radius: 999px; background: #111827; color: white; text-decoration: none; font-weight: 700;">
                    <i class="fas fa-store"></i>
                    Xem tất cả sản phẩm
                </a>
            </div>
        </div>
    @endforelse
</div>

<script>
function quickAddToCart(lensId) {
    const button = document.querySelector(`[data-lens-id="${lensId}"].quick-add-btn`);
    if (!button) return;

    const originalHtml = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            camera_lens_id: lensId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cart_count);
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.style.background = '#16a34a';
            createNotification(data.message, 'success');
        } else {
            createNotification(data.message, 'error');
        }
    })
    .catch(() => {
        createNotification('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
    })
    .finally(() => {
        setTimeout(() => {
            button.innerHTML = originalHtml;
            button.style.background = 'rgba(15, 23, 42, 0.88)';
            button.disabled = false;
        }, 1500);
    });
}

function buyNowFromGrid(lensId) {
    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            camera_lens_id: lensId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cart_count);
            window.location.href = '/cart/checkout';
        } else {
            createNotification(data.message, 'error');
        }
    })
    .catch(() => {
        createNotification('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            const quickAction = this.querySelector('.quick-action');
            if (quickAction) quickAction.style.opacity = '1';
        });

        card.addEventListener('mouseleave', function() {
            const quickAction = this.querySelector('.quick-action');
            if (quickAction) quickAction.style.opacity = '0';
        });
    });
});

function toggleFavorite(lensId) {
    const button = document.querySelector(`[data-lens-id="${lensId}"].favorite-btn`);
    if (!button) return;

    fetch(`/favorites/toggle/${lensId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.style.color = data.is_favorited ? '#dc2626' : '#475569';
            createNotification(data.message, data.is_favorited ? 'success' : 'info');
        } else if (data.redirect) {
            window.location.href = data.redirect;
        } else {
            createNotification(data.message, 'error');
        }
    })
    .catch(() => {
        createNotification('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
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
