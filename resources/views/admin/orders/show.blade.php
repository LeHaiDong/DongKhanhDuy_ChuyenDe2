@extends('layouts.admin')

@section('title', 'Chi tiết đơn hàng - Admin Panel')
@section('page-title', 'Chi tiết đơn hàng')

@section('content')
<!-- Order Header -->
<div class="admin-card" style="margin-bottom: 24px;">
    <div class="card-header">
        <div class="order-header-info">
            <div class="order-main-info">
                <h1 class="order-number">{{ $order->order_number }}</h1>
                <div class="order-meta">
                    <span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                    <span class="payment-badge payment-{{ $order->payment_status }}">{{ ucfirst($order->payment_status) }}</span>
                    <span class="order-date">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>
        <div class="card-actions">
            <!-- Quick Actions -->
            @if($order->canBeConfirmed())
                <button type="button" class="nike-btn nike-btn-success" onclick="confirmOrder({{ $order->id }})">
                    <i class="fas fa-check"></i>
                    Xác nhận
                </button>
            @endif
            
            @if($order->canBeShipped())
                <button type="button" class="nike-btn nike-btn-primary" onclick="shipOrder({{ $order->id }})">
                    <i class="fas fa-shipping-fast"></i>
                    Gửi hàng
                </button>
            @endif
            
            @if($order->canBeDelivered())
                <button type="button" class="nike-btn nike-btn-success" onclick="deliverOrder({{ $order->id }})">
                    <i class="fas fa-check-double"></i>
                    Đã giao
                </button>
            @endif
            
            @if($order->payment_status === 'pending')
                <button type="button" class="nike-btn nike-btn-warning" onclick="markAsPaid({{ $order->id }})">
                    <i class="fas fa-money-bill"></i>
                    Đánh dấu đã thanh toán
                </button>
            @endif
            
            @if($order->canBeCancelled())
                <button type="button" class="nike-btn nike-btn-danger" onclick="cancelOrder({{ $order->id }})">
                    <i class="fas fa-times"></i>
                    Hủy đơn
                </button>
            @endif
            
            <a href="{{ route('admin.orders.index') }}" class="nike-btn nike-btn-outline">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Order Items -->
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-shopping-cart" style="margin-right: 8px; color: #667eea;"></i>
                Sản phẩm đặt hàng ({{ $order->items->count() }} sản phẩm)
            </h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="80">Hình ảnh</th>
                        <th>Sản phẩm</th>
                        <th width="80">SL</th>
                        <th width="120">Đơn giá</th>
                        <th width="120">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>
                                <div class="product-image-mini">
                                    @if($item->cameraLens)
                                        <img src="{{ $item->cameraLens->image_url }}" alt="{{ $item->product_name }}">
                                    @elseif($item->product_image)
                                        <img src="{{ asset('storage/' . $item->product_image) }}" alt="{{ $item->product_name }}">
                                    @else
                                        <div class="no-image-mini">
                                            <i class="fas fa-box-open"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="product-details">
                                    <div class="product-name">{{ $item->product_name }}</div>
                                    <div class="product-brand">{{ $item->product_brand }}</div>
                                    @if($item->cameraLens)
                                        <div class="product-link">
                                            <a href="{{ route('products.show', $item->cameraLens) }}" target="_blank">
                                                <i class="fas fa-external-link-alt"></i>
                                                Xem sản phẩm
                                            </a>
                                        </div>
                                    @else
                                        <div class="product-deleted">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Sản phẩm đã bị xóa
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="quantity-display">
                                    <span class="qty-number">{{ $item->quantity }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="price-display">
                                    <span class="unit-price">{{ $item->formatted_unit_price }}</span>
                                    @if($item->has_discount)
                                        <div class="discount-info">
                                            -{{ $item->formatted_discount_amount }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="total-price">
                                    <strong>{{ $item->formatted_final_price }}</strong>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="order-summary">
                        <td colspan="4" class="text-right"><strong>Tạm tính:</strong></td>
                        <td><strong>{{ $order->formatted_subtotal }}</strong></td>
                    </tr>
                    @if($order->shipping_fee > 0)
                        <tr class="order-summary">
                            <td colspan="4" class="text-right">Phí vận chuyển:</td>
                            <td>{{ $order->formatted_shipping_fee }}</td>
                        </tr>
                    @endif
                    @if($order->discount_amount > 0)
                        <tr class="order-summary">
                            <td colspan="4" class="text-right">Giảm giá:</td>
                            <td class="discount">-{{ $order->formatted_discount_amount }}</td>
                        </tr>
                    @endif
                    @if($order->tax_amount > 0)
                        <tr class="order-summary">
                            <td colspan="4" class="text-right">Thuế:</td>
                            <td>{{ number_format($order->tax_amount, 0, ',', '.') }} VNĐ</td>
                        </tr>
                    @endif
                    <tr class="order-total">
                        <td colspan="4" class="text-right"><strong>Tổng cộng:</strong></td>
                        <td><strong class="total-amount">{{ $order->formatted_total }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <!-- Sidebar Info -->
    <div>
        <!-- Customer Information -->
        <div class="admin-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user" style="margin-right: 8px; color: #28a745;"></i>
                    Thông tin khách hàng
                </h3>
            </div>
            <div class="card-body">
                <div class="customer-info-detail">
                    @if($order->user)
                        <div class="info-row">
                            <label>Tài khoản:</label>
                            <span>{{ $order->user->name }}</span>
                        </div>
                        <div class="info-row">
                            <label>Email tài khoản:</label>
                            <span>{{ $order->user->email }}</span>
                        </div>
                    @endif
                    
                    <div class="info-row">
                        <label>Tên người nhận:</label>
                        <span>{{ $order->shipping_name }}</span>
                    </div>
                    
                    <div class="info-row">
                        <label>Số điện thoại:</label>
                        <span>{{ $order->shipping_phone }}</span>
                    </div>
                    
                    @if($order->shipping_email)
                        <div class="info-row">
                            <label>Email:</label>
                            <span>{{ $order->shipping_email }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Shipping Information -->
        <div class="admin-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shipping-fast" style="margin-right: 8px; color: #17a2b8;"></i>
                    Thông tin giao hàng
                </h3>
            </div>
            <div class="card-body">
                <div class="shipping-info-detail">
                    <div class="info-row">
                        <label>Địa chỉ:</label>
                        <span>{{ $order->full_shipping_address }}</span>
                    </div>
                    
                    <div class="info-row">
                        <label>Phương thức:</label>
                        <span>{{ ucfirst($order->shipping_method) }}</span>
                    </div>
                    
                    <div class="info-row">
                        <label>Phí vận chuyển:</label>
                        <span>{{ $order->formatted_shipping_fee }}</span>
                    </div>
                    
                    @if($order->tracking_info && isset($order->tracking_info['tracking_number']))
                        <div class="info-row">
                            <label>Mã vận đơn:</label>
                            <span class="tracking-number">{{ $order->tracking_info['tracking_number'] }}</span>
                        </div>
                    @endif
                    
                    @if($order->tracking_info && isset($order->tracking_info['carrier']))
                        <div class="info-row">
                            <label>Đơn vị vận chuyển:</label>
                            <span>{{ $order->tracking_info['carrier'] }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Payment Information -->
        <div class="admin-card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-credit-card" style="margin-right: 8px; color: #ffc107;"></i>
                    Thông tin thanh toán
                </h3>
            </div>
            <div class="card-body">
                <div class="payment-info-detail">
                    <div class="info-row">
                        <label>Phương thức:</label>
                        <span>{{ $order->payment_method_name }}</span>
                    </div>
                    
                    <div class="info-row">
                        <label>Trạng thái:</label>
                        <span class="payment-badge payment-{{ $order->payment_status }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                    
                    @if($order->payment_reference)
                        <div class="info-row">
                            <label>Mã giao dịch:</label>
                            <span class="payment-ref">{{ $order->payment_reference }}</span>
                        </div>
                    @endif
                    
                    @if($order->coupon_code)
                        <div class="info-row">
                            <label>Mã giảm giá:</label>
                            <span class="coupon-code">{{ $order->coupon_code }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Timeline -->
<div class="admin-card" style="margin-bottom: 24px;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-history" style="margin-right: 8px; color: #6f42c1;"></i>
            Lịch sử đơn hàng
        </h3>
    </div>
    <div class="card-body">
        <div class="timeline">
            @foreach($timeline as $event)
                <div class="timeline-item">
                    <div class="timeline-marker {{ $event['color'] }}">
                        <i class="{{ $event['icon'] }}"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <h4 class="timeline-title">{{ $event['title'] }}</h4>
                            <span class="timeline-time">{{ $event['timestamp']->format('d/m/Y H:i') }}</span>
                        </div>
                        <p class="timeline-description">{{ $event['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Notes Section -->
@if($order->notes)
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-sticky-note" style="margin-right: 8px; color: #e83e8c;"></i>
                Ghi chú
            </h3>
        </div>
        <div class="card-body">
            <div class="order-notes">
                {{ $order->notes }}
            </div>
        </div>
    </div>
@endif

<style>
/* Order Header */
.order-header-info {
    display: flex;
    align-items: flex-start;
    gap: 20px;
}

.order-number {
    margin: 0 0 8px 0;
    color: #333;
    font-size: 28px;
    font-weight: 800;
    font-family: monospace;
}

.order-meta {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.order-date {
    color: #666;
    font-size: 14px;
    font-weight: 500;
}

/* Status badges */
.status-badge, .payment-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-confirmed { background: #d1ecf1; color: #0c5460; }
.status-processing { background: #d4edda; color: #155724; }
.status-shipped { background: #cce5ff; color: #004085; }
.status-delivered { background: #d4edda; color: #155724; }
.status-cancelled { background: #f8d7da; color: #721c24; }

.payment-pending { background: #fff3cd; color: #856404; }
.payment-paid { background: #d4edda; color: #155724; }
.payment-failed { background: #f8d7da; color: #721c24; }

/* Product table */
.product-image-mini {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image-mini img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image-mini {
    color: #ccc;
    font-size: 20px;
}

.product-details {
    min-width: 0;
}

.product-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
    font-size: 14px;
}

.product-brand {
    color: #666;
    font-size: 12px;
    margin-bottom: 4px;
}

.product-link a {
    color: #667eea;
    text-decoration: none;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.product-link a:hover {
    text-decoration: underline;
}

.product-deleted {
    color: #dc3545;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.quantity-display {
    text-align: center;
}

.qty-number {
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 14px;
}

.price-display {
    text-align: right;
}

.unit-price {
    font-weight: 500;
    color: #333;
}

.discount-info {
    color: #dc3545;
    font-size: 12px;
    margin-top: 2px;
}

.total-price {
    text-align: right;
    font-size: 15px;
}

/* Order summary */
.order-summary td {
    padding: 8px 16px !important;
    border-top: 1px solid #e9ecef;
}

.order-total td {
    padding: 12px 16px !important;
    border-top: 2px solid #dee2e6;
    background: #f8f9fa;
}

.total-amount {
    color: #28a745;
    font-size: 18px;
}

.discount {
    color: #dc3545;
}

/* Info sections */
.info-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 8px 0;
    border-bottom: 1px solid #f8f9fa;
    gap: 12px;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row label {
    font-weight: 600;
    color: #555;
    font-size: 13px;
    min-width: 100px;
    flex-shrink: 0;
}

.info-row span {
    color: #333;
    font-size: 13px;
    text-align: right;
    word-break: break-word;
}

.tracking-number, .payment-ref, .coupon-code {
    font-family: monospace;
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px !important;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    z-index: 1;
}

.timeline-marker.primary { background: #007bff; }
.timeline-marker.success { background: #28a745; }
.timeline-marker.info { background: #17a2b8; }
.timeline-marker.warning { background: #ffc107; color: #333; }
.timeline-marker.danger { background: #dc3545; }

.timeline-content {
    background: #f8f9fa;
    padding: 16px;
    border-radius: 8px;
    border-left: 4px solid #e9ecef;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.timeline-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.timeline-time {
    color: #666;
    font-size: 12px;
    font-weight: 500;
}

.timeline-description {
    margin: 0;
    color: #555;
    font-size: 14px;
    line-height: 1.5;
}

/* Notes */
.order-notes {
    background: #f8f9fa;
    padding: 16px;
    border-radius: 8px;
    border-left: 4px solid #ffc107;
    font-size: 14px;
    line-height: 1.6;
    color: #333;
}

/* Responsive */
@media (max-width: 768px) {
    .order-header-info {
        flex-direction: column;
    }
    
    .order-meta {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    
    .info-row span {
        text-align: left;
    }
    
    .timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        left: -18px;
        width: 24px;
        height: 24px;
        font-size: 10px;
    }
}
</style>

<script>
// Order actions
function confirmOrder(orderId) {
    if (confirm('Bạn có chắc muốn xác nhận đơn hàng này?')) {
        submitAction(`/admin/orders/${orderId}/confirm`, {});
    }
}

function shipOrder(orderId) {
    const trackingNumber = prompt('Nhập mã vận đơn (tùy chọn):');
    const carrier = prompt('Nhập đơn vị vận chuyển (tùy chọn):');
    
    submitAction(`/admin/orders/${orderId}/ship`, {
        tracking_number: trackingNumber || '',
        carrier: carrier || ''
    });
}

function deliverOrder(orderId) {
    if (confirm('Xác nhận đơn hàng đã được giao thành công?')) {
        submitAction(`/admin/orders/${orderId}/deliver`, {});
    }
}

function cancelOrder(orderId) {
    const reason = prompt('Nhập lý do hủy đơn hàng:');
    
    if (reason && reason.trim()) {
        submitAction(`/admin/orders/${orderId}/cancel`, {
            cancel_reason: reason
        });
    }
}

function markAsPaid(orderId) {
    const paymentRef = prompt('Nhập mã giao dịch thanh toán (tùy chọn):');
    
    if (confirm('Đánh dấu đơn hàng này là đã thanh toán?')) {
        submitAction(`/admin/orders/${orderId}/mark-as-paid`, {
            payment_reference: paymentRef || ''
        });
    }
}

function submitAction(url, data) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    
    let formHtml = `<input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">`;
    
    for (let key in data) {
        formHtml += `<input type="hidden" name="${key}" value="${data[key]}">`;
    }
    
    form.innerHTML = formHtml;
    document.body.appendChild(form);
    form.submit();
}

// Show success/error messages
@if(session('success'))
    setTimeout(() => {
        alert('{{ session("success") }}');
    }, 100);
@endif

@if(session('error'))
    setTimeout(() => {
        alert('{{ session("error") }}');
    }, 100);
@endif
</script>
@endsection


