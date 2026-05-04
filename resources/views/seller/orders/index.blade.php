@extends('layouts.app')

@section('title', 'Đơn hàng của shop - MienTayShop')
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
                <h1>Đơn hàng của {{ $shop->shop_name }}</h1>
                <p>Danh sách chỉ hiển thị các đơn có sản phẩm thuộc shop của bạn.</p>
            </div>
            <a href="{{ route('seller.dashboard') }}" class="seller-btn outline"><i class="fas fa-arrow-left"></i> Kênh bán</a>
        </div>

        @if($orders->isNotEmpty())
            <div class="seller-table-wrap">
                <table class="seller-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Sản phẩm của shop</th>
                            <th>Doanh thu shop</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
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
                                <td><span class="seller-status {{ $order->payment_status === 'paid' ? '' : 'pending' }}">{{ $order->payment_status === 'paid' ? 'Đã thanh toán' : 'Chờ thanh toán' }}</span></td>
                                <td><span class="seller-status">{{ $orderStatusLabels[$order->status] ?? 'Đang cập nhật' }}</span></td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td><a href="{{ route('seller.orders.show', $order) }}" class="seller-btn outline" style="min-height: 36px; padding: 8px 12px;">Xử lý</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div style="margin-top: 22px;">
                    {{ $orders->links('pagination::bootstrap-4') }}
                </div>
            @endif
        @else
            <div class="seller-empty">Shop chưa có đơn hàng nào.</div>
        @endif
    </section>
</div>
@endsection
