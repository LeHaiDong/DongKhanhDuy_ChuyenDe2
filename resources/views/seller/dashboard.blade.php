@extends('layouts.app')

@section('title', 'Quản lý kênh bán - MienTayShop')
@push('styles')
    @include('seller.partials.styles')
@endpush

@section('content')
@php
    $orderStatusLabels = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'processing' => 'Đang chuẩn bị',
        'shipped' => 'Đang giao',
        'delivered' => 'Đã giao',
        'cancelled' => 'Đã hủy',
    ];
    $operatingRate = $stats['products'] > 0 ? round(($stats['active_products'] / $stats['products']) * 100) : 0;
    $averageOrderValue = $stats['orders'] > 0 ? $stats['gross_revenue'] / $stats['orders'] : 0;
    $latestRevenue = $chartData->sum('revenue');
@endphp

<div class="seller-shell seller-dashboard">
    <section class="seller-dashboard-hero">
        <div class="seller-hero-copy">
            <span class="seller-kicker"><i class="fas fa-store"></i> Trung tâm người bán</span>
            <h1>{{ $shop->shop_name }}</h1>
            <p>
                Theo dõi doanh thu, xử lý đơn hàng và quản lý sản phẩm của shop trên cùng một bảng điều khiển.
                Các đơn hàng bên dưới chỉ tính phần sản phẩm thuộc kênh bán của bạn.
            </p>

            <div class="seller-actions seller-hero-actions">
                <a href="{{ route('seller.products.create') }}" class="seller-btn primary"><i class="fas fa-plus"></i> Đăng sản phẩm</a>
                <a href="{{ route('seller.orders.index') }}" class="seller-btn outline light"><i class="fas fa-receipt"></i> Xử lý đơn hàng</a>
                <a href="{{ route('seller.products.index') }}" class="seller-btn outline light"><i class="fas fa-boxes-stacked"></i> Kho sản phẩm</a>
            </div>

            <div class="seller-hero-mini">
                <div>
                    <strong>{{ number_format($stats['gross_revenue'], 0, ',', '.') }} VNĐ</strong>
                    <span>Doanh thu ghi nhận</span>
                </div>
                <div>
                    <strong>{{ number_format($stats['orders']) }}</strong>
                    <span>Đơn có sản phẩm của shop</span>
                </div>
                <div>
                    <strong>{{ $operatingRate }}%</strong>
                    <span>Sản phẩm đang hiển thị</span>
                </div>
            </div>
        </div>

        <div class="seller-chart-card hero-chart">
            <div class="seller-chart-head">
                <div>
                    <span class="seller-card-label">14 ngày gần đây</span>
                    <h2>Biểu đồ doanh thu</h2>
                </div>
                <strong>{{ number_format($latestRevenue, 0, ',', '.') }} VNĐ</strong>
            </div>

            <div class="seller-chart-bars" aria-label="Biểu đồ doanh thu 14 ngày">
                @foreach($chartData as $day)
                    @php
                        $barHeight = max(10, round(($day['revenue'] / $chartMaxRevenue) * 150));
                    @endphp
                    <div class="seller-chart-day" title="{{ $day['label'] }}: {{ number_format($day['revenue'], 0, ',', '.') }} VNĐ">
                        <div class="seller-chart-track">
                            <span style="height: {{ $barHeight }}px"></span>
                        </div>
                        <small>{{ $day['label'] }}</small>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="seller-dashboard-grid">
        <article class="seller-stat-card accent-blue">
            <i class="fas fa-sack-dollar"></i>
            <span>Doanh thu đã thanh toán</span>
            <strong>{{ number_format($stats['paid_revenue'], 0, ',', '.') }} VNĐ</strong>
            <small>Đơn đã được xác nhận thanh toán hoặc giao thành công.</small>
        </article>

        <article class="seller-stat-card accent-cyan">
            <i class="fas fa-box-open"></i>
            <span>Sản phẩm đang bán</span>
            <strong>{{ number_format($stats['active_products']) }}/{{ number_format($stats['products']) }}</strong>
            <small>Tổng tồn kho hiện tại: {{ number_format($stats['stock']) }} sản phẩm.</small>
        </article>

        <article class="seller-stat-card accent-amber">
            <i class="fas fa-truck-fast"></i>
            <span>Đơn cần theo dõi</span>
            <strong>{{ number_format($stats['pending_orders']) }}</strong>
            <small>Gồm đơn chờ xác nhận, đã xác nhận và đang chuẩn bị.</small>
        </article>

        <article class="seller-stat-card accent-violet">
            <i class="fas fa-users"></i>
            <span>Khách đã mua</span>
            <strong>{{ number_format($stats['customers']) }}</strong>
            <small>Giá trị trung bình mỗi đơn: {{ number_format($averageOrderValue, 0, ',', '.') }} VNĐ.</small>
        </article>
    </section>

    <section class="seller-insight-grid">
        <div class="seller-card seller-card-compact">
            <div class="seller-card-head compact">
                <div>
                    <span class="seller-card-label">Sản phẩm nổi bật</span>
                    <h2>Bán chạy theo doanh thu</h2>
                </div>
                <a href="{{ route('seller.products.index') }}" class="seller-link">Quản lý sản phẩm <i class="fas fa-arrow-right"></i></a>
            </div>

            @if($bestProducts->isNotEmpty())
                <div class="seller-product-rank">
                    @foreach($bestProducts as $index => $item)
                        @php
                            $maxBestRevenue = max(1, (float) $bestProducts->max('revenue'));
                            $progress = max(8, round(((float) $item->revenue / $maxBestRevenue) * 100));
                        @endphp
                        <div class="seller-rank-item">
                            <span class="seller-rank-number">{{ $index + 1 }}</span>
                            <div>
                                <strong>{{ $item->product_name }}</strong>
                                <small>{{ $item->product_brand ?: $shop->brand_name }} · Đã bán {{ number_format($item->sold_quantity) }}</small>
                                <div class="seller-progress"><span style="width: {{ $progress }}%"></span></div>
                            </div>
                            <b>{{ number_format($item->revenue, 0, ',', '.') }} VNĐ</b>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="seller-empty small">Chưa có doanh thu để xếp hạng sản phẩm.</div>
            @endif
        </div>

        <div class="seller-card seller-card-compact">
            <div class="seller-card-head compact">
                <div>
                    <span class="seller-card-label">Vận hành đơn hàng</span>
                    <h2>Tình trạng hiện tại</h2>
                </div>
                <a href="{{ route('seller.orders.index') }}" class="seller-link">Xem đơn <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="seller-status-grid">
                @foreach($orderStatusLabels as $status => $label)
                    <div class="seller-status-tile {{ $status }}">
                        <strong>{{ number_format((int) ($orderStatusStats[$status] ?? 0)) }}</strong>
                        <span>{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="seller-card">
        <div class="seller-card-head">
            <div>
                <span class="seller-card-label">Đơn cần xử lý</span>
                <h2>Đơn hàng gần đây</h2>
                <p>Mỗi người bán chỉ thấy phần đơn hàng có sản phẩm thuộc shop của mình.</p>
            </div>
            <a href="{{ route('seller.orders.index') }}" class="seller-btn outline">Xem tất cả đơn hàng</a>
        </div>

        @if($latestOrders->isNotEmpty())
            <div class="seller-table-wrap">
                <table class="seller-table seller-table-modern">
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
                                    <div class="seller-muted">{{ $order->shipping_phone }}</div>
                                </td>
                                <td>
                                    <strong>{{ number_format($sellerItems->sum('quantity')) }} sản phẩm</strong>
                                    <div class="seller-muted">
                                        {{ $sellerItems->pluck('product_name')->take(2)->implode(', ') }}
                                        @if($sellerItems->count() > 2)
                                            ...
                                        @endif
                                    </div>
                                </td>
                                <td><strong class="seller-money">{{ number_format($sellerRevenue, 0, ',', '.') }} VNĐ</strong></td>
                                <td><span class="seller-status {{ $order->status }}">{{ $orderStatusLabels[$order->status] ?? 'Đang cập nhật' }}</span></td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td><a href="{{ route('seller.orders.show', $order) }}" class="seller-btn outline mini">Xử lý</a></td>
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
                <span class="seller-card-label">Kho hàng</span>
                <h2>Sản phẩm mới nhất</h2>
                <p>Người bán chịu trách nhiệm đăng, sửa, ẩn hoặc xóa sản phẩm của shop.</p>
            </div>
            <a href="{{ route('seller.products.index') }}" class="seller-btn outline">Quản lý sản phẩm</a>
        </div>

        @if($latestProducts->isNotEmpty())
            <div class="seller-table-wrap">
                <table class="seller-table seller-table-modern">
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
                                    <div class="seller-muted">{{ $product->brand }}</div>
                                </td>
                                <td>{{ $product->category_names ?: $product->display_product_type }}</td>
                                <td><strong class="seller-money">{{ $product->formatted_price }}</strong></td>
                                <td>{{ $product->stock_quantity }}</td>
                                <td><span class="seller-status {{ $product->is_active ? 'delivered' : 'muted' }}">{{ $product->is_active ? 'Đang bán' : 'Tạm ẩn' }}</span></td>
                                <td><a href="{{ route('seller.products.edit', $product) }}" class="seller-btn outline mini">Sửa</a></td>
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
