@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng - MienTayShop')
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
                <h1>Đơn hàng {{ $order->order_number }}</h1>
                <p>Chỉ hiển thị phần sản phẩm và doanh thu thuộc shop {{ $shop->shop_name }}.</p>
            </div>
            <a href="{{ route('seller.orders.index') }}" class="seller-btn outline"><i class="fas fa-arrow-left"></i> Danh sách đơn</a>
        </div>

        <div class="seller-stats">
            <div class="seller-stat highlight">
                <strong>{{ number_format($sellerSubtotal, 0, ',', '.') }} VNĐ</strong>
                <span>Doanh thu của shop trong đơn</span>
            </div>
            <div class="seller-stat">
                <strong>{{ number_format($sellerItems->sum('quantity')) }}</strong>
                <span>Sản phẩm của shop</span>
            </div>
            <div class="seller-stat">
                <strong>{{ $orderStatusLabels[$order->status] ?? 'Đang cập nhật' }}</strong>
                <span>Trạng thái đơn hàng</span>
            </div>
        </div>

        <div class="seller-grid" style="margin-bottom: 24px;">
            <div class="seller-stat">
                <strong>{{ $order->shipping_name }}</strong>
                <span>Khách hàng · {{ $order->shipping_phone }}</span>
            </div>
            <div class="seller-stat">
                <strong>{{ $order->payment_method_name }}</strong>
                <span>{{ $order->payment_status === 'paid' ? 'Đã thanh toán' : 'Chờ thanh toán' }}</span>
            </div>
            @if($order->payment_reference)
                <div class="seller-stat">
                    <strong>{{ $order->payment_reference }}</strong>
                    <span>Nội dung chuyển khoản khách được hướng dẫn nhập</span>
                </div>
            @endif
        </div>

        <div style="border: 1px solid #e2e8f0; border-radius: 18px; padding: 16px; margin-bottom: 22px; color: #475569;">
            <strong style="display: block; color: #0f172a; margin-bottom: 6px;">Địa chỉ giao hàng</strong>
            {{ $order->full_shipping_address ?: $order->shipping_address }}
        </div>

        <div style="border: 1px solid #dbeafe; border-radius: 22px; padding: 18px; margin-bottom: 22px; background: #f8fbff;">
            <div class="seller-card-head" style="margin-bottom: 14px;">
                <div>
                    <h2 style="font-size: 22px;">Người bán xử lý đơn hàng</h2>
                    <p>Admin chỉ duyệt người bán và quản lý hệ thống. Shop của bạn là bên xác nhận, giao hàng và cập nhật thanh toán cho đơn này.</p>
                </div>
            </div>

            <div class="seller-actions" style="margin-top: 0;">
                @if($order->canBeConfirmed())
                    <form method="POST" action="{{ route('seller.orders.confirm', $order) }}">
                        @csrf
                        <button type="submit" class="seller-btn primary">
                            <i class="fas fa-check"></i>
                            Xác nhận đơn
                        </button>
                    </form>
                @endif

                @if($order->canBeShipped())
                    <form method="POST" action="{{ route('seller.orders.ship', $order) }}" class="seller-actions" style="margin-top: 0; align-items: center;">
                        @csrf
                        <input type="text" name="carrier" aria-label="Đơn vị giao hàng" style="min-height: 44px; border: 1px solid #dbe3ef; border-radius: 14px; padding: 0 12px;">
                        <input type="text" name="tracking_number" aria-label="Mã vận đơn" style="min-height: 44px; border: 1px solid #dbe3ef; border-radius: 14px; padding: 0 12px;">
                        <button type="submit" class="seller-btn primary">
                            <i class="fas fa-truck-fast"></i>
                            Chuyển sang đang giao
                        </button>
                    </form>
                @endif

                @if($order->canBeDelivered())
                    <form method="POST" action="{{ route('seller.orders.deliver', $order) }}">
                        @csrf
                        <button type="submit" class="seller-btn primary">
                            <i class="fas fa-circle-check"></i>
                            Đã giao thành công
                        </button>
                    </form>
                @endif

                @if(!$order->isPaid() && !$order->isCancelled())
                    <form method="POST" action="{{ route('seller.orders.mark-as-paid', $order) }}" class="seller-actions" style="margin-top: 0; align-items: center;">
                        @csrf
                        <input type="text" name="payment_reference" aria-label="Ghi chú thanh toán" style="min-height: 44px; border: 1px solid #dbe3ef; border-radius: 14px; padding: 0 12px;">
                        <button type="submit" class="seller-btn outline">
                            <i class="fas fa-money-check-dollar"></i>
                            Xác nhận thanh toán
                        </button>
                    </form>
                @endif

                @if($order->canBeCancelled())
                    <form method="POST" action="{{ route('seller.orders.cancel', $order) }}" class="seller-actions" style="margin-top: 0; align-items: center;">
                        @csrf
                        <input type="text" name="cancel_reason" aria-label="Lý do hủy đơn" required style="min-height: 44px; border: 1px solid #fecaca; border-radius: 14px; padding: 0 12px;">
                        <button type="submit" class="seller-btn outline" style="color: #b91c1c;">
                            <i class="fas fa-ban"></i>
                            Hủy đơn
                        </button>
                    </form>
                @endif

                @if(!$order->canBeConfirmed() && !$order->canBeShipped() && !$order->canBeDelivered() && !$order->canBeCancelled() && ($order->isPaid() || $order->isCancelled()))
                    <div class="seller-empty" style="margin: 0; padding: 14px 18px;">
                        Đơn hàng hiện không còn thao tác cần xử lý.
                    </div>
                @endif
            </div>
        </div>

        <div class="seller-table-wrap">
            <table class="seller-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Thương hiệu</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sellerItems as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->product_name }}</strong>
                                <div style="color: #64748b; font-size: 13px;">{{ $item->cameraLens?->category_names ?: $item->cameraLens?->display_product_type }}</div>
                            </td>
                            <td>{{ $item->product_brand }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->formatted_unit_price }}</td>
                            <td><strong style="color: #2563eb;">{{ $item->formatted_final_price }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
