@extends('layouts.app')

@section('title', 'MienTayShop - Shop đa ngành')

@section('content')
<div class="shop-home">
    @if($availableCoupons->isNotEmpty())
        <section class="content-section">
            <div class="section-head">
                <div>
                    <span>VOUCHER</span>
                    <h2>Mã giảm giá cho khách hàng</h2>
                </div>
                <div class="section-head-actions">
                    <div class="rail-controls voucher-controls">
                        <button type="button" class="rail-button" onclick="scrollProductRail('voucher-track', -1)" aria-label="Xem voucher trước">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" class="rail-button" onclick="scrollProductRail('voucher-track', 1)" aria-label="Xem voucher tiếp">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <a href="{{ route('cart.checkout') }}">Dùng khi thanh toán <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <div class="voucher-rail-shell">
                <div class="rail-fade rail-fade-left"></div>
                <div class="rail-fade rail-fade-right"></div>

                <div class="voucher-grid" id="voucher-track">
                    @foreach($availableCoupons as $coupon)
                        <article class="voucher-card">
                            <div class="voucher-top">
                                <span class="voucher-code">{{ $coupon->code }}</span>
                                <strong>{{ $coupon->formatted_value }}</strong>
                            </div>
                            <h3>{{ $coupon->name }}</h3>
                            <p>{{ $coupon->description }}</p>
                            @if($coupon->minimum_amount)
                                <small>Áp dụng cho đơn từ {{ number_format($coupon->minimum_amount, 0, ',', '.') }} VNĐ</small>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="content-section">
        <div class="section-head">
            <div>
                <span>DANH MỤC</span>
                <h2>Mua sắm theo danh mục</h2>
            </div>
            <a href="{{ route('products.shop') }}">Xem toàn bộ <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="category-grid">
            @foreach($categoryTiles as $category)
                <a href="{{ $category->url }}" class="category-tile">
                    <span class="category-image">
                        @if($category->display_image_url)
                            <img src="{{ $category->display_image_url }}" alt="{{ $category->display_name }}" loading="lazy" decoding="async">
                        @else
                            <i class="{{ $category->icon_with_fallback }}"></i>
                        @endif
                    </span>
                    <strong>{{ $category->display_name }}</strong>
                    <small>{{ number_format($category->total_products_count) }} sản phẩm</small>
                </a>
            @endforeach
        </div>
    </section>

    <section class="content-section flash-sale-section">
        <div class="section-head">
            <div>
                <span>FLASH SALE</span>
                <h2>Ưu đãi tốt trong ngày</h2>
            </div>
            <div class="section-head-actions">
                <div class="rail-controls">
                    <button type="button" class="rail-button" onclick="scrollProductRail('flash-sale-track', -1)" aria-label="Xem sản phẩm trước">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" class="rail-button" onclick="scrollProductRail('flash-sale-track', 1)" aria-label="Xem sản phẩm tiếp">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <a href="{{ route('products.shop', ['sort' => 'price_low']) }}">Xem thêm <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        <div class="product-rail-shell">
            <div class="rail-fade rail-fade-left"></div>
            <div class="rail-fade rail-fade-right"></div>

            <div
                class="horizontal-products"
                id="flash-sale-track"
                data-product-rail
                data-auto-scroll="true"
            >
                @foreach($flashSaleProducts as $product)
                    @include('products.partials.compact-product-card', ['product' => $product, 'badge' => 'Sale'])
                @endforeach
            </div>
        </div>
    </section>

    <section class="content-section">
        <div class="section-head">
            <div>
                <span>GỢI Ý HÔM NAY</span>
                <h2>Sản phẩm nổi bật</h2>
            </div>
            <a href="{{ route('products.shop') }}">Vào cửa hàng <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="recommend-grid">
            @foreach($recommendedProducts as $product)
                @include('products.partials.compact-product-card', ['product' => $product, 'badge' => null])
            @endforeach
        </div>
    </section>
</div>

<style>
.shop-home {
    background:
        radial-gradient(circle at 12% 0%, rgba(14, 165, 233, 0.14), transparent 30%),
        radial-gradient(circle at 88% 8%, rgba(79, 70, 229, 0.10), transparent 28%),
        linear-gradient(180deg, #edf5ff 0%, #f7f9ff 38%, #ffffff 100%);
    padding: 34px 24px 76px;
}

.content-section {
    max-width: 1320px;
    margin: 0 auto;
}

.market-stage {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(280px, 0.9fr);
    gap: 14px;
}

.market-stage-main,
.content-section {
    border-radius: 26px;
    box-shadow: 0 18px 42px rgba(15, 23, 42, 0.07);
}

.market-stage-main {
    position: relative;
    display: grid;
    grid-template-columns: minmax(0, 1.05fr) minmax(280px, 0.95fr);
    gap: 12px;
    min-height: 280px;
    overflow: hidden;
    padding: 28px;
    text-decoration: none;
    color: white;
    background:
        radial-gradient(circle at 88% 12%, rgba(255, 214, 10, 0.22), transparent 20%),
        radial-gradient(circle at 16% 90%, rgba(255, 255, 255, 0.15), transparent 24%),
        linear-gradient(135deg, #ff2d2d 0%, #ff5a2f 34%, #ff7a00 100%);
}

.stage-main-copy {
    position: relative;
    z-index: 2;
}

.stage-chip {
    display: inline-flex;
    align-items: center;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(15, 23, 42, 0.18);
    color: white;
    font-size: 12px;
    font-weight: 950;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.stage-kicker {
    display: block;
    margin-top: 18px;
    font-size: 13px;
    font-weight: 900;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.92);
}

.stage-main-copy h1 {
    margin: 10px 0 12px;
    font-size: clamp(34px, 5vw, 58px);
    line-height: 0.98;
    font-weight: 950;
    letter-spacing: -0.05em;
}

.stage-main-copy p {
    max-width: 520px;
    margin: 0;
    color: rgba(255, 255, 255, 0.92);
    font-size: 15px;
    line-height: 1.75;
}

.stage-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 20px;
}

.stage-pills span {
    padding: 9px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.14);
    color: white;
    font-size: 12px;
    font-weight: 800;
}

.stage-main-visual {
    position: relative;
    min-height: 100%;
}

.stage-product {
    position: absolute;
    overflow: hidden;
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.14);
    box-shadow: 0 18px 34px rgba(15, 23, 42, 0.18);
}

.stage-product img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.stage-product-main {
    top: 10px;
    right: 16px;
    width: 210px;
    height: 210px;
    transform: rotate(-8deg);
}

.stage-product-support {
    bottom: 6px;
    left: 8px;
    width: 150px;
    height: 150px;
    transform: rotate(8deg);
}

.stage-floating-badge {
    position: absolute;
    top: 34px;
    left: 22px;
    width: 88px;
    height: 88px;
    border-radius: 999px;
    display: grid;
    place-items: center;
    align-content: center;
    background: #ffe500;
    color: #0284c7;
    box-shadow: 0 18px 30px rgba(15, 23, 42, 0.16);
}

.stage-floating-badge strong,
.stage-floating-badge span {
    display: block;
    line-height: 1;
}

.stage-floating-badge strong {
    font-size: 30px;
    font-weight: 950;
}

.stage-floating-badge span {
    margin-top: 4px;
    font-size: 12px;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.market-stage-side {
    display: grid;
    gap: 14px;
}

.stage-side-card {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 118px;
    gap: 12px;
    align-items: center;
    min-height: 133px;
    padding: 16px 18px;
    border-radius: 22px;
    text-decoration: none;
    color: #0f172a;
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 18px 38px rgba(15, 23, 42, 0.08);
}

.stage-side-card-top {
    background: linear-gradient(135deg, #fff1f2 0%, #ffffff 100%);
}

.stage-side-card-bottom {
    background: linear-gradient(135deg, #ecfeff 0%, #ffffff 100%);
}

.stage-side-copy span {
    color: #0284c7;
    font-size: 11px;
    font-weight: 950;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.stage-side-copy strong {
    display: block;
    margin-top: 8px;
    font-size: 28px;
    line-height: 1.02;
    font-weight: 950;
    letter-spacing: -0.04em;
}

.stage-side-copy small {
    display: block;
    margin-top: 8px;
    color: #64748b;
    font-size: 13px;
    line-height: 1.55;
}

.stage-side-image {
    width: 118px;
    height: 100px;
    border-radius: 18px;
    overflow: hidden;
    background: white;
    box-shadow: inset 0 0 0 1px rgba(226, 232, 240, 0.8);
}

.stage-side-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.service-strip {
    margin-top: 18px;
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 12px;
}

.service-item {
    min-height: 84px;
    padding: 14px 16px;
    border-radius: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    text-decoration: none;
    background: white;
    border: 1px solid #e2e8f0;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
}

.service-icon {
    width: 46px;
    height: 46px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
    color: #0284c7;
    font-size: 18px;
}

.service-item strong {
    color: #0f172a;
    font-size: 15px;
    font-weight: 900;
}

.content-section {
    margin-top: 24px;
    padding: 26px;
    background: white;
    border: 1px solid #e2e8f0;
}

.section-head {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 22px;
}

.section-head span {
    color: #1d4ed8;
    font-size: 12px;
    font-weight: 950;
    letter-spacing: 0.12em;
}

.section-head h2 {
    margin: 8px 0 0;
    color: #0f172a;
    font-size: clamp(24px, 4vw, 34px);
    line-height: 1.1;
    font-weight: 950;
    letter-spacing: -0.035em;
}

.section-head > a,
.section-head-actions > a {
    color: #1d4ed8;
    text-decoration: none;
    font-weight: 900;
}

.section-head-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.voucher-rail-shell {
    position: relative;
}

.voucher-grid {
    display: grid;
    grid-auto-flow: column;
    grid-auto-columns: minmax(270px, calc((100% - 48px) / 4));
    gap: 16px;
    overflow-x: auto;
    padding: 4px 4px 10px;
    scroll-snap-type: x mandatory;
    scrollbar-width: none;
}

.voucher-grid::-webkit-scrollbar {
    display: none;
}

.voucher-card {
    scroll-snap-align: start;
    min-height: 214px;
    padding: 20px;
    border-radius: 24px;
    border: 1px solid #bae6fd;
    background:
        radial-gradient(circle at top right, rgba(255, 255, 255, 0.3), transparent 30%),
        linear-gradient(135deg, #eff6ff 0%, #ecfeff 100%);
    box-shadow: 0 14px 34px rgba(37, 99, 235, 0.08);
}

.voucher-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
}

.voucher-code {
    display: inline-flex;
    align-items: center;
    padding: 8px 12px;
    border-radius: 999px;
    background: #1d4ed8;
    color: white;
    font-size: 12px;
    font-weight: 900;
    letter-spacing: 0.08em;
}

.voucher-card h3 {
    margin: 0 0 8px;
    color: #0f172a;
    font-size: 18px;
    font-weight: 900;
}

.voucher-card p {
    margin: 0 0 12px;
    color: #475569;
    line-height: 1.6;
}

.voucher-card small {
    color: #1d4ed8;
    font-weight: 800;
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(8, minmax(0, 1fr));
    border: 1px solid #edf2f7;
    border-radius: 22px;
    overflow: hidden;
}

.category-tile {
    min-height: 158px;
    padding: 16px 10px;
    display: grid;
    place-items: center;
    align-content: center;
    gap: 8px;
    text-align: center;
    text-decoration: none;
    color: #0f172a;
    border-right: 1px solid #edf2f7;
    border-bottom: 1px solid #edf2f7;
    transition: 0.2s ease;
}

.category-tile:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    z-index: 1;
}

.category-image {
    width: 74px;
    height: 74px;
    border-radius: 999px;
    background: #eff6ff;
    color: #1d4ed8;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    font-size: 24px;
}

.category-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.category-tile strong {
    font-size: 14px;
    line-height: 1.35;
}

.category-tile small {
    color: #64748b;
}

.flash-sale-section {
    position: relative;
}

.rail-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}

.rail-button {
    width: 40px;
    height: 40px;
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    background: white;
    color: #0f172a;
    cursor: pointer;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
}

.product-rail-shell {
    position: relative;
}

.rail-fade {
    position: absolute;
    top: 0;
    bottom: 10px;
    width: 46px;
    z-index: 2;
    pointer-events: none;
}

.rail-fade-left {
    left: 0;
    background: linear-gradient(90deg, white 0%, rgba(255, 255, 255, 0) 100%);
}

.rail-fade-right {
    right: 0;
    background: linear-gradient(270deg, white 0%, rgba(255, 255, 255, 0) 100%);
}

.horizontal-products {
    display: grid;
    grid-auto-flow: column;
    grid-auto-columns: minmax(220px, 220px);
    gap: 16px;
    overflow-x: auto;
    padding: 4px 4px 8px;
    scroll-snap-type: x mandatory;
    scrollbar-width: none;
}

.horizontal-products::-webkit-scrollbar {
    display: none;
}

.horizontal-products > * {
    scroll-snap-align: start;
}

.recommend-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 16px;
}

@media (max-width: 1180px) {
    .voucher-grid {
        grid-auto-columns: minmax(270px, calc((100% - 16px) / 2));
    }

    .category-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .recommend-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

}

@media (max-width: 760px) {
    .shop-home {
        padding-left: 16px;
        padding-right: 16px;
    }

    .content-section {
        padding: 20px;
    }

    .category-grid,
    .recommend-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .voucher-grid {
        grid-auto-columns: minmax(250px, 86vw);
    }

    .section-head {
        flex-direction: column;
        align-items: start;
    }

    .section-head-actions {
        width: 100%;
        justify-content: space-between;
    }

}
</style>
@endsection

@push('scripts')
<script>
function scrollProductRail(target, direction) {
    const rail = typeof target === 'string' ? document.getElementById(target) : target;

    if (!rail) {
        return;
    }

    const step = Math.max(rail.clientWidth * 0.78, 240);
    const maxScroll = Math.max(rail.scrollWidth - rail.clientWidth, 0);
    let nextPosition = rail.scrollLeft + (step * direction);

    if (direction > 0 && rail.scrollLeft >= maxScroll - 24) {
        nextPosition = 0;
    }

    if (direction < 0 && rail.scrollLeft <= 24) {
        nextPosition = maxScroll;
    }

    rail.scrollTo({
        left: Math.max(0, nextPosition),
        behavior: 'smooth',
    });
}

document.addEventListener('DOMContentLoaded', function () {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    document.querySelectorAll('[data-product-rail][data-auto-scroll="true"]').forEach(function (rail) {
        let autoTimer = setInterval(function () {
            scrollProductRail(rail, 1);
        }, 3400);

        rail.addEventListener('mouseenter', function () {
            clearInterval(autoTimer);
        });

        rail.addEventListener('mouseleave', function () {
            clearInterval(autoTimer);
            autoTimer = setInterval(function () {
                scrollProductRail(rail, 1);
            }, 3400);
        });
    });
});
</script>
@endpush
