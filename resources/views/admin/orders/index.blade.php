@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng - MienTayShop Admin')
@section('page-title', 'Quản lý đơn hàng')

@section('content')
<!-- Statistics Overview -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card-modern">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ number_format($stats['total_orders']) }}</div>
            <div class="stat-label">Tổng đơn hàng</div>
        </div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon" style="background: linear-gradient(135deg, #ffc107, #ffca2c);">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ number_format($stats['pending_orders']) }}</div>
            <div class="stat-label">Chờ xử lý</div>
        </div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon" style="background: linear-gradient(135deg, #17a2b8, #20c997);">
            <i class="fas fa-cogs"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ number_format($stats['processing_orders']) }}</div>
            <div class="stat-label">Đang xử lý</div>
        </div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon" style="background: linear-gradient(135deg, #28a745, #34ce57);">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ number_format($stats['delivered_orders']) }}</div>
            <div class="stat-label">Đã giao</div>
        </div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon" style="background: linear-gradient(135deg, #fd7e14, #e55353);">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ number_format($stats['today_orders']) }}</div>
            <div class="stat-label">Hôm nay</div>
        </div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon" style="background: linear-gradient(135deg, #6f42c1, #e83e8c);">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ number_format($stats['today_revenue'] / 1000000, 1) }}M</div>
            <div class="stat-label">Doanh thu hôm nay</div>
        </div>
    </div>
</div>

<!-- Filter and Search Bar -->
<div class="admin-card" style="margin-bottom: 24px;">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="filter-form">
            <div style="display: grid; grid-template-columns: 1fr auto auto auto auto auto; gap: 16px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="search">Tìm kiếm</label>
                    <input type="text" id="search" name="search" 
                           value="{{ request('search') }}" 
                           aria-label="Tìm kiếm đơn hàng"
                           class="form-input">
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="status">Trạng thái</label>
                    <select id="status" name="status" class="form-input">
                        <option value="">Tất cả</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                        <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Đã gửi</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Đã giao</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="payment_status">Thanh toán</label>
                    <select id="payment_status" name="payment_status" class="form-input">
                        <option value="">Tất cả</option>
                        <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Chờ thanh toán</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                        <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Thất bại</option>
                    </select>
                </div>
                
                <button type="submit" class="nike-btn nike-btn-primary">
                    <i class="fas fa-search"></i>
                    Tìm kiếm
                </button>
                
                <a href="{{ route('admin.orders.index') }}" class="nike-btn nike-btn-outline">
                    <i class="fas fa-undo"></i>
                    Đặt lại
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="admin-card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list" style="margin-right: 8px; color: #667eea;"></i>
            Danh sách đơn hàng ({{ $orders->total() }} đơn)
        </h3>
        <div class="card-actions">
            <div class="dropdown">
                <button class="nike-btn nike-btn-outline dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-download"></i>
                    Xuất dữ liệu
                </button>
                <div class="dropdown-menu">
                    <a href="{{ route('admin.orders.export') }}?{{ http_build_query(request()->query()) }}" class="dropdown-item">
                        <i class="fas fa-file-csv"></i>
                        Xuất CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card-body" style="padding: 0;">
        @if($orders->count() > 0)
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Sản phẩm</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thanh toán</th>
                        <th>Ngày tạo</th>
                        <th width="150">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>
                                <div class="order-number">
                                    <strong>{{ $order->order_number }}</strong>
                                </div>
                            </td>
                            <td>
                                <div class="customer-info">
                                    <div class="customer-name">{{ $order->shipping_name }}</div>
                                    <div class="customer-contact">
                                        <span class="phone">{{ $order->shipping_phone }}</span>
                                        @if($order->user)
                                            <span class="user-badge">{{ $order->user->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="order-items">
                                    <span class="items-count">{{ $order->items_count }} sản phẩm</span>
                                    <div class="items-preview">
                                        @foreach($order->items->take(2) as $item)
                                            <div class="item-preview">{{ Str::limit($item->product_name, 30) }}</div>
                                        @endforeach
                                        @if($order->items_count > 2)
                                            <div class="more-items">+{{ $order->items_count - 2 }} khác</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="order-total">
                                    <strong class="total-amount">{{ $order->formatted_total }}</strong>
                                    @if($order->shipping_fee > 0)
                                        <div class="shipping-fee">+ {{ $order->formatted_shipping_fee }} ship</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $order->status }}">
                                    @switch($order->status)
                                        @case('pending')
                                            <i class="fas fa-clock"></i> Chờ xử lý
                                            @break
                                        @case('confirmed')
                                            <i class="fas fa-check"></i> Đã xác nhận
                                            @break
                                        @case('processing')
                                            <i class="fas fa-cogs"></i> Đang xử lý
                                            @break
                                        @case('shipped')
                                            <i class="fas fa-shipping-fast"></i> Đã gửi
                                            @break
                                        @case('delivered')
                                            <i class="fas fa-check-circle"></i> Đã giao
                                            @break
                                        @case('cancelled')
                                            <i class="fas fa-times-circle"></i> Đã hủy
                                            @break
                                    @endswitch
                                </span>
                            </td>
                            <td>
                                <span class="payment-badge payment-{{ $order->payment_status }}">
                                    @switch($order->payment_status)
                                        @case('pending')
                                            <i class="fas fa-clock"></i> Chờ thanh toán
                                            @break
                                        @case('paid')
                                            <i class="fas fa-check-circle"></i> Đã thanh toán
                                            @break
                                        @case('failed')
                                            <i class="fas fa-times-circle"></i> Thất bại
                                            @break
                                        @case('refunded')
                                            <i class="fas fa-undo"></i> Đã hoàn tiền
                                            @break
                                    @endswitch
                                </span>
                            </td>
                            <td>
                                <div class="order-date">
                                    <div class="date-main">{{ $order->created_at->format('d/m/Y') }}</div>
                                    <div class="date-time">{{ $order->created_at->format('H:i') }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('admin.orders.show', $order) }}" 
                                       class="action-btn action-view" 
                                       title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($order->canBeConfirmed())
                                        <button type="button" 
                                                class="action-btn action-confirm" 
                                                title="Xác nhận đơn hàng"
                                                onclick="confirmOrder({{ $order->id }})">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                    
                                    @if($order->canBeShipped())
                                        <button type="button" 
                                                class="action-btn action-ship" 
                                                title="Gửi đơn hàng"
                                                onclick="shipOrder({{ $order->id }})">
                                            <i class="fas fa-shipping-fast"></i>
                                        </button>
                                    @endif
                                    
                                    @if($order->canBeCancelled())
                                        <button type="button" 
                                                class="action-btn action-cancel" 
                                                title="Hủy đơn hàng"
                                                onclick="cancelOrder({{ $order->id }}, '{{ $order->order_number }}')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="pagination-container">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>Chưa có đơn hàng nào</h3>
                <p>Chưa có đơn hàng nào được tạo hoặc không có đơn hàng nào phù hợp với điều kiện tìm kiếm.</p>
            </div>
        @endif
    </div>
</div>

<style>
/* Statistics Cards */
.stat-card-modern {
    background: white;
    padding: 24px;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.3s ease;
}

.stat-card-modern:hover {
    transform: translateY(-4px);
}

.stat-card-modern .stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    flex-shrink: 0;
}

.stat-card-modern .stat-content {
    flex: 1;
}

.stat-card-modern .stat-number {
    font-size: 28px;
    font-weight: 800;
    color: #333;
    margin-bottom: 4px;
}

.stat-card-modern .stat-label {
    font-size: 14px;
    color: #666;
    font-weight: 500;
}

/* Order table styles */
.order-number strong {
    color: #667eea;
    font-family: monospace;
    font-size: 14px;
}

.customer-info {
    min-width: 160px;
}

.customer-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.customer-contact {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.phone {
    color: #666;
    font-size: 12px;
}

.user-badge {
    background: #e9ecef;
    color: #495057;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 500;
}

.order-items {
    min-width: 200px;
}

.items-count {
    font-weight: 600;
    color: #667eea;
    display: block;
    margin-bottom: 4px;
}

.items-preview {
    font-size: 12px;
    color: #666;
}

.item-preview {
    margin-bottom: 2px;
}

.more-items {
    color: #999;
    font-style: italic;
}

.order-total {
    text-align: right;
}

.total-amount {
    color: #28a745;
    font-size: 16px;
    display: block;
    margin-bottom: 2px;
}

.shipping-fee {
    color: #666;
    font-size: 11px;
}

/* Status badges */
.status-badge, .payment-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
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
.payment-refunded { background: #e2e3e5; color: #383d41; }

/* Date display */
.order-date {
    text-align: center;
}

.date-main {
    font-weight: 500;
    color: #333;
    font-size: 13px;
}

.date-time {
    color: #666;
    font-size: 11px;
}

/* Action buttons */
.action-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.2s ease;
}

.action-view {
    background: #17a2b8;
    color: white;
}

.action-view:hover {
    background: #138496;
}

.action-confirm {
    background: #28a745;
    color: white;
}

.action-confirm:hover {
    background: #218838;
}

.action-ship {
    background: #007bff;
    color: white;
}

.action-ship:hover {
    background: #0056b3;
}

.action-cancel {
    background: #dc3545;
    color: white;
}

.action-cancel:hover {
    background: #c82333;
}

.action-buttons {
    display: flex;
    gap: 4px;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state h3 {
    margin: 0 0 12px 0;
    color: #666;
    font-size: 24px;
}

.empty-state p {
    color: #999;
    margin-bottom: 0;
}

/* Dropdown */
.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-toggle::after {
    content: '';
    margin-left: 8px;
    border: 4px solid transparent;
    border-top: 4px solid currentColor;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    z-index: 1000;
    display: none;
    min-width: 150px;
    padding: 8px 0;
    margin: 4px 0 0;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.dropdown:hover .dropdown-menu {
    display: block;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    color: #333;
    text-decoration: none;
    font-size: 14px;
    transition: background 0.2s ease;
}

.dropdown-item:hover {
    background: #f8f9fa;
    color: #333;
    text-decoration: none;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-table th,
    .admin-table td {
        padding: 8px 4px;
        font-size: 12px;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 2px;
    }
    
    .filter-form > div {
        grid-template-columns: 1fr !important;
        gap: 12px !important;
    }
    
    .stat-card-modern {
        padding: 16px;
    }
}
</style>

<script>
// Order actions
function confirmOrder(orderId) {
    if (confirm('Bạn có chắc muốn xác nhận đơn hàng này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/orders/${orderId}/confirm`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function shipOrder(orderId) {
    const trackingNumber = prompt('Nhập mã vận đơn (tùy chọn):');
    const carrier = prompt('Nhập đơn vị vận chuyển (tùy chọn):');
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/orders/${orderId}/ship`;
    form.innerHTML = `
        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
        <input type="hidden" name="tracking_number" value="${trackingNumber || ''}">
        <input type="hidden" name="carrier" value="${carrier || ''}">
    `;
    document.body.appendChild(form);
    form.submit();
}

function cancelOrder(orderId, orderNumber) {
    const reason = prompt(`Nhập lý do hủy đơn hàng ${orderNumber}:`);
    
    if (reason && reason.trim()) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/orders/${orderId}/cancel`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
            <input type="hidden" name="cancel_reason" value="${reason}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
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


