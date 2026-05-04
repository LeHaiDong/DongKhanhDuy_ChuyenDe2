<article class="compact-product-card">
    <a href="{{ route('products.show', $product) }}" class="compact-product-link">
        <div class="compact-product-image">
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='{{ $product->fallback_image_url }}';">
            @if($badge)
                <span class="compact-badge">{{ $badge }}</span>
            @endif
        </div>
        <div class="compact-product-info">
            <span class="compact-brand">{{ $product->brand }}</span>
            <h3>{{ $product->name }}</h3>
            <div class="compact-meta">
                <span>{{ $product->display_product_type }}</span>
                <span>Còn {{ $product->stock_quantity }}</span>
            </div>
            <strong>{{ $product->formatted_price }}</strong>
        </div>
    </a>
    <div class="compact-actions">
        <button type="button" onclick="homeAddToCart({{ $product->id }}, this)">
            <i class="fas fa-cart-plus"></i>
            Thêm
        </button>
        <button type="button" class="buy" onclick="homeBuyNow({{ $product->id }}, this)">
            Mua ngay
        </button>
    </div>
</article>

@once
<style>
.compact-product-card {
    position: relative;
    min-width: 0;
    border: 1px solid #edf2f7;
    border-radius: 22px;
    background: white;
    overflow: hidden;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
    transition: 0.2s ease;
}

.compact-product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 18px 38px rgba(15, 23, 42, 0.11);
}

.compact-product-link {
    display: block;
    color: inherit;
    text-decoration: none;
}

.compact-product-image {
    position: relative;
    aspect-ratio: 1 / 1;
    background: #ecfeff;
    overflow: hidden;
}

.compact-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.compact-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    padding: 6px 10px;
    border-radius: 999px;
    background: #0f766e;
    color: white;
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
}

.compact-product-info {
    padding: 14px;
}

.compact-brand {
    color: #0f766e;
    font-size: 11px;
    font-weight: 950;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.compact-product-info h3 {
    min-height: 43px;
    margin: 6px 0 10px;
    color: #0f172a;
    font-size: 15px;
    line-height: 1.4;
    font-weight: 900;
}

.compact-meta {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    color: #64748b;
    font-size: 12px;
    margin-bottom: 10px;
}

.compact-product-info strong {
    display: block;
    color: #dc2626;
    font-size: 17px;
    font-weight: 950;
}

.compact-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    padding: 0 14px 14px;
}

.compact-actions button {
    border: 0;
    border-radius: 13px;
    padding: 10px 8px;
    background: #111827;
    color: white;
    font-size: 13px;
    font-weight: 900;
    cursor: pointer;
}

.compact-actions button.buy {
    background: #0f766e;
}
</style>

<script>
function homeAddToCart(productId, button) {
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            camera_lens_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateHomeCartCount(data.cart_count);
            homeNotify(data.message || 'Đã thêm sản phẩm vào giỏ hàng.', 'success');
            button.innerHTML = '<i class="fas fa-check"></i> Đã thêm';
        } else {
            homeNotify(data.message || 'Không thể thêm sản phẩm.', 'error');
            button.innerHTML = originalText;
        }
    })
    .catch(() => {
        homeNotify('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
        button.innerHTML = originalText;
    })
    .finally(() => {
        setTimeout(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        }, 1300);
    });
}

function homeBuyNow(productId, button) {
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            camera_lens_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateHomeCartCount(data.cart_count);
            window.location.href = '/cart/checkout';
            return;
        }

        homeNotify(data.message || 'Không thể mua ngay sản phẩm này.', 'error');
        button.disabled = false;
        button.innerHTML = 'Mua ngay';
    })
    .catch(() => {
        homeNotify('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
        button.disabled = false;
        button.innerHTML = 'Mua ngay';
    });
}

function updateHomeCartCount(count) {
    document.querySelectorAll('.cart-count').forEach(element => {
        element.textContent = count;
        element.classList.toggle('hidden', Number(count) <= 0);
    });
}

function homeNotify(message, type = 'info') {
    const notification = document.createElement('div');
    notification.style.position = 'fixed';
    notification.style.top = '18px';
    notification.style.right = '18px';
    notification.style.zIndex = '12000';
    notification.style.padding = '12px 16px';
    notification.style.borderRadius = '16px';
    notification.style.color = 'white';
    notification.style.fontWeight = '800';
    notification.style.boxShadow = '0 16px 36px rgba(15,23,42,0.18)';
    notification.style.background = type === 'success' ? '#16a34a' : '#dc2626';
    notification.textContent = message;

    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 2400);
}
</script>
@endonce
