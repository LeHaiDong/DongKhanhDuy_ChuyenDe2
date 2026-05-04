@extends('layouts.app')

@section('title', 'Lịch sử mua hàng - MienTayShop')

@section('content')
<div class="orders-page">
    <div class="orders-shell">
        <div class="orders-hero">
            <div>
                <span class="orders-kicker">Tài khoản của tôi</span>
                <h1>Lịch sử mua hàng</h1>
                <p>Các sản phẩm đã đặt hàng thành công sẽ hiển thị tại đây để bạn dễ kiểm tra lại mã đơn, trạng thái và tổng tiền.</p>
            </div>
            <a href="{{ route('products.shop') }}" class="orders-shop-link">
                <i class="fas fa-store"></i>
                Tiếp tục mua sắm
            </a>
        </div>

        @if($orders->isEmpty())
            <div class="orders-empty">
                <div class="orders-empty-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <h2>Bạn chưa có đơn hàng nào</h2>
                <p>Hãy thêm sản phẩm vào giỏ hàng và đặt hàng thử để lịch sử mua hàng được ghi lại tại đây.</p>
                <a href="{{ route('products.shop') }}">
                    <i class="fas fa-bag-shopping"></i>
                    Mua sắm ngay
                </a>
            </div>
        @else
            <div class="orders-list">
                @foreach($orders as $order)
                    @php
                        $statusLabels = [
                            'pending' => ['Chờ shop xác nhận', 'warning'],
                            'confirmed' => ['Đã xác nhận', 'info'],
                            'processing' => ['Đang xử lý', 'info'],
                            'shipped' => ['Đang giao', 'shipping'],
                            'delivered' => ['Đã giao thành công', 'success'],
                            'cancelled' => ['Đã hủy', 'danger'],
                            'refunded' => ['Đã hoàn tiền', 'muted'],
                        ];
                        $status = $statusLabels[$order->status] ?? ['Đã đặt hàng', 'muted'];
                    @endphp

                    <article class="order-card">
                        <div class="order-card-head">
                            <div>
                                <div class="order-number">{{ $order->order_number }}</div>
                                <div class="order-meta">
                                    <span><i class="fas fa-calendar-day"></i> {{ $order->created_at->format('d/m/Y H:i') }}</span>
                                    <span><i class="fas fa-box"></i> {{ $order->items->sum('quantity') }} sản phẩm</span>
                                    <span><i class="fas fa-wallet"></i> {{ $order->payment_method_name }}</span>
                                </div>
                            </div>
                            <div class="order-summary">
                                <span class="order-status {{ $status[1] }}">{{ $status[0] }}</span>
                                <strong>{{ $order->formatted_total }}</strong>
                            </div>
                        </div>

                        <div class="order-items">
                            @foreach($order->items as $item)
                                @php
                                    $imageUrl = $item->cameraLens?->image_url ?: $item->product_image;
                                    $fallbackUrl = $item->cameraLens?->fallback_image_url ?: asset('catalog/fallback/home.jpg');
                                @endphp

                                <div class="order-item">
                                    <div class="order-item-image">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='{{ $fallbackUrl }}';">
                                        @else
                                            <i class="fas fa-box-open"></i>
                                        @endif
                                    </div>
                                    <div class="order-item-info">
                                        <div class="order-item-name">{{ $item->product_name }}</div>
                                        <div class="order-item-brand">{{ $item->product_brand }} • Số lượng: {{ $item->quantity }}</div>
                                    </div>
                                    <div class="order-item-price">{{ $item->formatted_final_price }}</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="order-address">
                            <i class="fas fa-location-dot"></i>
                            <span>{{ $order->shipping_name }} - {{ $order->shipping_phone }} - {{ $order->full_shipping_address }}</span>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="orders-pagination">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

<style>
.orders-page {
    min-height: 100vh;
    background: linear-gradient(180deg, #edf5ff 0%, #f8fbff 28%, #ffffff 100%);
    padding: 42px 0 76px;
}

.orders-shell {
    max-width: 1180px;
    margin: 0 auto;
    padding: 0 24px;
}

.orders-hero {
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 26px;
}

.orders-kicker {
    color: #0284c7;
    font-size: 12px;
    font-weight: 900;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.orders-hero h1 {
    margin: 8px 0 10px;
    font-size: clamp(32px, 5vw, 48px);
    line-height: 1.05;
    font-weight: 950;
    letter-spacing: -0.04em;
    color: #0f172a;
}

.orders-hero p {
    max-width: 720px;
    margin: 0;
    color: #64748b;
    line-height: 1.75;
}

.orders-shop-link,
.orders-empty a {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 13px 18px;
    border-radius: 999px;
    background: #111827;
    color: white;
    text-decoration: none;
    font-weight: 850;
}

.orders-list {
    display: grid;
    gap: 18px;
}

.order-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 28px;
    box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    overflow: hidden;
}

.order-card-head {
    display: flex;
    justify-content: space-between;
    align-items: start;
    gap: 18px;
    padding: 22px;
    border-bottom: 1px solid #e2e8f0;
}

.order-number {
    font-size: 20px;
    font-weight: 950;
    color: #0f172a;
}

.order-meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 8px;
    color: #64748b;
    font-size: 13px;
}

.order-summary {
    display: grid;
    justify-items: end;
    gap: 9px;
}

.order-summary strong {
    color: #dc2626;
    font-size: 22px;
    font-weight: 950;
}

.order-status {
    padding: 7px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 850;
}

.order-status.warning { background: #fef3c7; color: #92400e; }
.order-status.info { background: #dbeafe; color: #1d4ed8; }
.order-status.shipping { background: #ede9fe; color: #6d28d9; }
.order-status.success { background: #dcfce7; color: #166534; }
.order-status.danger { background: #fee2e2; color: #991b1b; }
.order-status.muted { background: #f1f5f9; color: #475569; }

.order-items {
    display: grid;
    gap: 1px;
    background: #e2e8f0;
}

.order-item {
    display: grid;
    grid-template-columns: 82px minmax(0, 1fr) auto;
    align-items: center;
    gap: 16px;
    padding: 16px 22px;
    background: white;
}

.order-item-image {
    width: 82px;
    height: 82px;
    border-radius: 18px;
    overflow: hidden;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0284c7;
}

.order-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.order-item-name {
    color: #0f172a;
    font-weight: 900;
}

.order-item-brand {
    margin-top: 5px;
    color: #64748b;
    font-size: 13px;
}

.order-item-price {
    font-weight: 950;
    color: #0f172a;
}

.order-address {
    display: flex;
    gap: 10px;
    padding: 16px 22px 20px;
    color: #475569;
    font-size: 14px;
}

.orders-empty {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 30px;
    padding: 52px 24px;
    text-align: center;
    box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
}

.orders-empty-icon {
    width: 86px;
    height: 86px;
    margin: 0 auto 18px;
    border-radius: 28px;
    background: #e0f2fe;
    color: #0369a1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
}

.orders-empty h2 {
    margin: 0 0 10px;
    font-size: 26px;
    color: #0f172a;
}

.orders-empty p {
    max-width: 520px;
    margin: 0 auto 20px;
    color: #64748b;
}

.orders-pagination {
    margin-top: 22px;
}

@media (max-width: 720px) {
    .order-card-head,
    .order-item {
        grid-template-columns: 1fr;
    }

    .order-card-head {
        display: grid;
    }

    .order-summary {
        justify-items: start;
    }

    .order-item {
        grid-template-columns: 72px minmax(0, 1fr);
    }

    .order-item-price {
        grid-column: 1 / -1;
    }
}
</style>
@endsection
