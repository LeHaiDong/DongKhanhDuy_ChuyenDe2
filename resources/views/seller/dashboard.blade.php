@extends('layouts.app')

@section('title', 'Quản lý kênh bán - MienTayShop')
@push('styles')
    @include('seller.partials.styles')
@endpush

@section('content')
@php
    $orderStatusLabels = [
        'pending' => 'Chờ người bán xác nhận',
        'confirmed' => 'Đã xác nhận',
        'processing' => 'Đang chuẩn bị',
        'shipped' => 'Đang giao',
        'delivered' => 'Đã giao',
        'cancelled' => 'Đã hủy',
    ];
@endphp
<div class="seller-shell">
    <section class="seller-card">
        <div class="seller-card-head">
            <div>
                <h1>Kênh bán {{ $shop->shop_name }}</h1>
                <p>Shop đã được admin phê duyệt. Khu vực này dành riêng cho người bán quản lý sản phẩm, đơn hàng và doanh thu.</p>
            </div>
            <div class="seller-actions" style="margin-top: 0;">
                <a href="{{ route('seller.products.create') }}" class="seller-btn primary"><i class="fas fa-plus"></i> Đăng sản phẩm</a>
                <a href="{{ route('seller.orders.index') }}" class="seller-btn outline"><i class="fas fa-receipt"></i> Đơn hàng</a>
                <a href="{{ route('seller.products.index') }}" class="seller-btn outline"><i class="fas fa-boxes-stacked"></i> Sản phẩm</a>
            </div>
        </div>

        <div class="seller-stats seller-stats-wide">
            <div class="seller-stat highlight">
                <strong>{{ number_format($stats['gross_revenue'], 0, ',', '.') }} VNĐ</strong>
                <span>Doanh thu ghi nhận</span>
            </div>
            <div class="seller-stat">
                <strong>{{ number_format($stats['paid_revenue'], 0, ',', '.') }} VNĐ</strong>
                <span>Doanh thu đã thanh toán</span>
            </div>
            <div class="seller-stat">
                <strong>{{ number_format($stats['orders']) }}</strong>
                <span>Đơn hàng có sản phẩm của shop</span>
            </div>
            <div class="seller-stat">
                <strong>{{ number_format($stats['pending_orders']) }}</strong>
                <span>Đơn cần theo dõi</span>
            </div>
            <div class="seller-stat">
                <strong>{{ number_format($stats['active_products']) }}/{{ number_format($stats['products']) }}</strong>
                <span>Sản phẩm đang bán / tổng sản phẩm</span>
            </div>
            <div class="seller-stat">
                <strong>{{ number_format($stats['customers']) }}</strong>
                <span>Khách hàng đã mua</span>
            </div>
        </div>
    </section>

    <section class="seller-card">
        <div class="seller-card-head">
            <div>
                <h2>Đơn hàng gần đây</h2>
                <p>Mỗi người bán chỉ thấy phần đơn hàng chứa sản phẩm thuộc shop của mình.</p>
            </div>
            <a href="{{ route('seller.orders.index') }}" class="seller-btn outline">Xem tất cả đơn hàng</a>
        </div>

        @if($latestOrders->isNotEmpty())
            <div class="seller-table-wrap">
                <table class="seller-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Sản phẩm của shop</th>
                            <th>Doanh thu shop</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($latestOrders as $order)
                            @php
                                $sellerItems = $order->items->filter(fn ($item) => (int) $item->cameraLens?->seller_shop_id === (int) $shop->id);
                                $sellerRevenue = (float) $sellerItems->sum('final_price');
                            @endphp
                            <tr>
                                <td><strong>{{ $order->order_number }}</strong></td>
                                <td>
                                    <strong>{{ $order->shipping_name ?: $order->user?->name }}</strong>
                                    <div style="color: #64748b; font-size: 13px;">{{ $order->shipping_phone }}</div>
                                </td>
                                <td>
                                    <strong>{{ number_format($sellerItems->sum('quantity')) }} sản phẩm</strong>
                                    <div style="color: #64748b; font-size: 13px;">
                                        {{ $sellerItems->pluck('product_name')->take(2)->implode(', ') }}
                                        @if($sellerItems->count() > 2)
                                            ...
                                        @endif
                                    </div>
                                </td>
                                <td><strong style="color: #2563eb;">{{ number_format($sellerRevenue, 0, ',', '.') }} VNĐ</strong></td>
                                <td><span class="seller-status">{{ $orderStatusLabels[$order->status] ?? 'Đang cập nhật' }}</span></td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td><a href="{{ route('seller.orders.show', $order) }}" class="seller-btn outline" style="min-height: 36px; padding: 8px 12px;">Xử lý</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="seller-empty">Chưa có đơn hàng nào chứa sản phẩm của shop.</div>
        @endif
    </section>

    <section class="seller-card">
        <div class="seller-card-head">
            <div>
                <h2>Sản phẩm mới nhất</h2>
                <p>Người bán chịu trách nhiệm đăng, sửa, ẩn hoặc xóa sản phẩm của shop.</p>
            </div>
            <a href="{{ route('seller.products.index') }}" class="seller-btn outline">Quản lý sản phẩm</a>
        </div>

        @if($latestProducts->isNotEmpty())
            <div class="seller-table-wrap">
                <table class="seller-table">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Tồn kho</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($latestProducts as $product)
                            <tr>
                                <td><img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="seller-product-thumb" onerror="this.onerror=null;this.src='{{ $product->fallback_image_url }}';"></td>
                                <td>
                                    <strong>{{ $product->name }}</strong>
                                    <div style="color: #64748b; font-size: 13px;">{{ $product->brand }}</div>
                                </td>
                                <td>{{ $product->category_names ?: $product->display_product_type }}</td>
                                <td><strong style="color: #2563eb;">{{ $product->formatted_price }}</strong></td>
                                <td>{{ $product->stock_quantity }}</td>
                                <td><span class="seller-status {{ $product->is_active ? '' : 'muted' }}">{{ $product->is_active ? 'Đang bán' : 'Tạm ẩn' }}</span></td>
                                <td><a href="{{ route('seller.products.edit', $product) }}" class="seller-btn outline" style="min-height: 36px; padding: 8px 12px;">Sửa</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="seller-empty">Chưa có sản phẩm nào. Hãy đăng sản phẩm đầu tiên cho shop.</div>
        @endif
    </section>
</div>
@endsection
