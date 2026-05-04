@extends('layouts.app')

@section('title', 'Tai khoan cua toi - MienTayShop')

@section('content')
<div class="profile-container">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 40px 20px;">
        
        <!-- Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <div class="avatar-circle">
                    <i class="fas fa-user"></i>
                </div>
            </div>
            <div class="profile-info">
                <h1>{{ Auth::user()->name }}</h1>
                <p>{{ Auth::user()->email }}</p>
                <span class="member-since">Thành viên từ {{ Auth::user()->created_at->format('m/Y') }}</span>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="profile-nav">
            <button class="nav-btn active" data-tab="info">
                <i class="fas fa-user"></i>
                Thông tin cá nhân
            </button>
            <button class="nav-btn" data-tab="orders">
                <i class="fas fa-shopping-bag"></i>
                Đơn hàng của tôi
            </button>
            <button class="nav-btn hidden" data-tab="favorites" style="display: none;">
                <i class="fas fa-heart"></i>
                Sản phẩm yêu thích
            </button>
            <button class="nav-btn" data-tab="security">
                <i class="fas fa-shield-alt"></i>
                Bảo mật
            </button>
        </div>

        <!-- Tab Contents -->
        <div class="tab-content">
            
            <!-- Personal Info Tab -->
            <div class="tab-pane active" id="info">
                <div class="info-grid">
                    <div class="info-card">
                        <div class="card-header">
                            <h3>Thông tin cá nhân</h3>
                            <button class="edit-btn" onclick="toggleEdit('personal')">
                                <i class="fas fa-edit"></i>
                                Chỉnh sửa
                            </button>
                        </div>
                        
                        <form id="personal-form" method="POST" action="{{ route('auth.customer.profile.update') }}">
                            @csrf
                            @method('PATCH')
                            
                            <div class="form-group">
                                <label for="name">Họ và tên</label>
                                <input type="text" id="name" name="name" value="{{ Auth::user()->name }}" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="{{ Auth::user()->email }}" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Số điện thoại</label>
                                <input type="tel" id="phone" name="phone" value="{{ Auth::user()->phone }}" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Địa chỉ</label>
                                <textarea id="address" name="address" rows="3" readonly>{{ Auth::user()->address }}</textarea>
                            </div>
                            
                            <div class="form-actions" style="display: none;">
                                <button type="submit" class="save-btn">
                                    <i class="fas fa-save"></i>
                                    Lưu thay đổi
                                </button>
                                <button type="button" class="cancel-btn" onclick="cancelEdit('personal')">
                                    <i class="fas fa-times"></i>
                                    Hủy
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="stats-card">
                        <h3>Thống kê tài khoản</h3>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-number">{{ Auth::user()->orders()->count() }}</div>
                                <div class="stat-label">Đơn hàng đã đặt</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">{{ number_format(Auth::user()->orders()->sum('total_amount'), 0, ',', '.') }}</div>
                                <div class="stat-label">Tổng tiền mua</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">{{ Auth::user()->created_at->diffInDays(now()) }}</div>
                                <div class="stat-label">Ngày thành viên</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Tab -->
            <div class="tab-pane" id="orders">
                <div class="orders-container">
                    @if(($orders ?? collect())->isNotEmpty())
                        <div class="profile-orders-head">
                            <div>
                                <span>Lịch sử mua hàng</span>
                                <h3>Đơn hàng gần đây</h3>
                            </div>
                            <a href="{{ route('auth.customer.orders') }}" class="view-all-orders">
                                Xem đầy đủ
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>

                        <div class="profile-order-list">
                            @foreach($orders as $order)
                                @php
                                    $statusLabels = [
                                        'pending' => ['Chờ shop xác nhận', 'warning'],
                                        'confirmed' => ['Đã xác nhận', 'info'],
                                        'processing' => ['Đang chuẩn bị hàng', 'info'],
                                        'shipped' => ['Đang giao hàng', 'shipping'],
                                        'delivered' => ['Đã giao thành công', 'success'],
                                        'cancelled' => ['Đã hủy', 'danger'],
                                        'refunded' => ['Đã hoàn tiền', 'muted'],
                                    ];
                                    $status = $statusLabels[$order->status] ?? ['Đã đặt hàng', 'muted'];
                                @endphp

                                <article class="profile-order-card">
                                    <div class="profile-order-top">
                                        <div>
                                            <strong>{{ $order->order_number }}</strong>
                                            <small>{{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->items->sum('quantity') }} sản phẩm</small>
                                        </div>
                                        <div class="profile-order-total">
                                            <span class="mini-order-status {{ $status[1] }}">{{ $status[0] }}</span>
                                            <b>{{ $order->formatted_total }}</b>
                                        </div>
                                    </div>

                                    <div class="profile-order-items">
                                        @foreach($order->items->take(3) as $item)
                                            @php
                                                $imageUrl = $item->cameraLens?->image_url ?: $item->product_image;
                                                $fallbackUrl = $item->cameraLens?->fallback_image_url ?: null;
                                            @endphp

                                            <div class="profile-order-item">
                                                <div class="profile-order-image">
                                                    @if($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" loading="lazy" decoding="async" @if($fallbackUrl) onerror="this.onerror=null;this.src='{{ $fallbackUrl }}';" @endif>
                                                    @else
                                                        <i class="fas fa-box-open"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h4>{{ $item->product_name }}</h4>
                                                    <p>{{ $item->product_brand }} · SL: {{ $item->quantity }}</p>
                                                </div>
                                                <strong>{{ $item->formatted_final_price }}</strong>
                                            </div>
                                        @endforeach

                                        @if($order->items->count() > 3)
                                            <div class="profile-order-more">
                                                Còn {{ $order->items->count() - 3 }} sản phẩm khác trong đơn hàng này.
                                            </div>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <h3>Chưa có đơn hàng nào</h3>
                            <p>Đơn hàng sau khi đặt thành công sẽ hiển thị ở đây để bạn theo dõi trạng thái và tổng tiền.</p>
                            <a href="{{ route('products.shop') }}" class="shop-btn">
                                <i class="fas fa-shopping-cart"></i>
                                Mua sắm ngay
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Favorites Tab -->
            <div class="tab-pane" id="favorites">
                <div class="favorites-container">
                    @if(Auth::user()->favoriteCameraLenses->count() > 0)
                        <div class="favorites-grid">
                            @foreach(Auth::user()->favoriteCameraLenses as $lens)
                                <div class="favorite-item">
                                    <div class="product-image">
                                        <img src="{{ $lens->image_url }}" alt="{{ $lens->name }}">
                                    </div>
                                    <div class="product-info">
                                        <h4>{{ $lens->name }}</h4>
                                        <p class="brand">{{ $lens->brand }}</p>
                                        <p class="specs">{{ $lens->display_product_type }} • {{ $lens->focal_length }}</p>
                                        <p class="price">{{ $lens->formatted_price }}</p>
                                    </div>
                                    <div class="product-actions">
                                        <a href="{{ route('products.show', $lens) }}" class="view-btn">
                                            <i class="fas fa-eye"></i>
                                            Xem chi tiết
                                        </a>
                                        <button class="remove-btn" onclick="removeFavorite({{ $lens->id }})">
                                            <i class="fas fa-heart-broken"></i>
                                            Bỏ yêu thích
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h3>Chưa có sản phẩm yêu thích</h3>
                            <p>Bạn chưa có sản phẩm yêu thích nào. Hãy khám phá và thêm sản phẩm yêu thích!</p>
                            <a href="{{ route('products.shop') }}" class="shop-btn">
                                <i class="fas fa-search"></i>
                                Khám phá sản phẩm
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Security Tab -->
            <div class="tab-pane" id="security">
                <div class="security-container">
                    <div class="security-card">
                        <div class="card-header">
                            <h3>Thay đổi mật khẩu</h3>
                        </div>
                        
                        <form method="POST" action="{{ route('auth.customer.change-password.update') }}">
                            @csrf
                            @method('PATCH')
                            
                            <div class="form-group">
                                <label for="current_password">Mật khẩu hiện tại</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Mật khẩu mới</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="password_confirmation">Xác nhận mật khẩu mới</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" required>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="save-btn">
                                    <i class="fas fa-shield-alt"></i>
                                    Đổi mật khẩu
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="security-tips">
                        <h4>Mẹo bảo mật</h4>
                        <ul>
                            <li><i class="fas fa-check"></i> Sử dụng mật khẩu mạnh với ít nhất 8 ký tự</li>
                            <li><i class="fas fa-check"></i> Kết hợp chữ hoa, chữ thường, số và ký tự đặc biệt</li>
                            <li><i class="fas fa-check"></i> Không chia sẻ mật khẩu với ai khác</li>
                            <li><i class="fas fa-check"></i> Thay đổi mật khẩu định kỳ</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 40px 0;
}

.profile-header {
    display: flex;
    align-items: center;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.profile-avatar {
    margin-right: 30px;
}

.avatar-circle {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 32px;
}

.profile-info h1 {
    margin: 0 0 8px 0;
    font-size: 28px;
    font-weight: 700;
    color: #333;
}

.profile-info p {
    margin: 0 0 8px 0;
    color: #666;
    font-size: 16px;
}

.member-since {
    color: #999;
    font-size: 14px;
}

.profile-nav {
    display: flex;
    background: white;
    border-radius: 12px;
    padding: 8px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow-x: auto;
}

.nav-btn {
    flex: 1;
    background: none;
    border: none;
    padding: 16px 20px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #666;
    font-weight: 500;
}

.nav-btn:hover {
    background: #f8f9fa;
    color: #333;
}

.nav-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.tab-content {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
}

.info-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

.info-card, .stats-card, .security-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 24px;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.card-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #333;
}

.edit-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s ease;
}

.edit-btn:hover {
    background: #0056b3;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #333;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #667eea;
}

.form-group input[readonly],
.form-group textarea[readonly] {
    background: #f8f9fa;
    cursor: not-allowed;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
}

.save-btn, .cancel-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.save-btn {
    background: #28a745;
    color: white;
}

.save-btn:hover {
    background: #218838;
}

.cancel-btn {
    background: #6c757d;
    color: white;
}

.cancel-btn:hover {
    background: #5a6268;
}

.stats-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
}

.stat-item {
    text-align: center;
    padding: 20px;
    background: white;
    border-radius: 8px;
    border: 2px solid #e9ecef;
}

.stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #667eea;
    margin-bottom: 8px;
}

.stat-label {
    color: #666;
    font-size: 14px;
}

.profile-orders-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 22px;
}

.profile-orders-head span {
    display: block;
    color: #0284c7;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 6px;
}

.profile-orders-head h3 {
    margin: 0;
    color: #0f172a;
    font-size: 24px;
}

.view-all-orders {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 16px;
    border-radius: 999px;
    background: #0f172a;
    color: #fff;
    text-decoration: none;
    font-weight: 800;
}

.view-all-orders:hover {
    color: #fff;
    text-decoration: none;
    transform: translateY(-1px);
}

.profile-order-list {
    display: grid;
    gap: 16px;
}

.profile-order-card {
    border: 1px solid #e2e8f0;
    border-radius: 22px;
    background: #fff;
    overflow: hidden;
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.06);
}

.profile-order-top {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 20px;
    border-bottom: 1px solid #e2e8f0;
}

.profile-order-top strong {
    color: #0f172a;
    font-size: 18px;
}

.profile-order-top small {
    display: block;
    color: #64748b;
    margin-top: 5px;
}

.profile-order-total {
    display: grid;
    justify-items: end;
    gap: 8px;
}

.profile-order-total b {
    color: #dc2626;
    font-size: 18px;
}

.mini-order-status {
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 850;
}

.mini-order-status.warning { background: #fef3c7; color: #92400e; }
.mini-order-status.info { background: #dbeafe; color: #1d4ed8; }
.mini-order-status.shipping { background: #ede9fe; color: #6d28d9; }
.mini-order-status.success { background: #dcfce7; color: #166534; }
.mini-order-status.danger { background: #fee2e2; color: #991b1b; }
.mini-order-status.muted { background: #f1f5f9; color: #475569; }

.profile-order-items {
    display: grid;
    gap: 1px;
    background: #e2e8f0;
}

.profile-order-item {
    display: grid;
    grid-template-columns: 70px minmax(0, 1fr) auto;
    align-items: center;
    gap: 14px;
    padding: 14px 20px;
    background: #fff;
}

.profile-order-image {
    width: 70px;
    height: 70px;
    border-radius: 16px;
    overflow: hidden;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0284c7;
}

.profile-order-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-order-item h4 {
    margin: 0 0 5px;
    color: #0f172a;
    font-size: 15px;
}

.profile-order-item p {
    margin: 0;
    color: #64748b;
    font-size: 13px;
}

.profile-order-item > strong {
    color: #0f172a;
    white-space: nowrap;
}

.profile-order-more {
    background: #f8fafc;
    color: #64748b;
    padding: 12px 20px;
    font-weight: 700;
}

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
    margin-bottom: 24px;
}

.shop-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    padding: 14px 28px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    transition: transform 0.2s ease;
}

.shop-btn:hover {
    transform: translateY(-2px);
    text-decoration: none;
    color: white;
}

.favorites-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
}

.favorite-item {
    background: #f8f9fa;
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.2s ease;
}

.favorite-item:hover {
    transform: translateY(-4px);
}

.product-image {
    height: 200px;
    overflow: hidden;
    background: #fff;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    color: #ccc;
    font-size: 48px;
}

.product-info {
    padding: 20px;
}

.product-info h4 {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.brand {
    color: #666;
    font-size: 14px;
    margin: 0 0 4px 0;
}

.specs {
    color: #999;
    font-size: 13px;
    margin: 0 0 8px 0;
}

.price {
    color: #007bff;
    font-weight: 600;
    font-size: 16px;
    margin: 0;
}

.product-actions {
    padding: 0 20px 20px;
    display: flex;
    gap: 8px;
}

.view-btn, .remove-btn {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.view-btn {
    background: #007bff;
    color: white;
}

.view-btn:hover {
    background: #0056b3;
    color: white;
    text-decoration: none;
}

.remove-btn {
    background: #dc3545;
    color: white;
}

.remove-btn:hover {
    background: #c82333;
}

.security-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

.security-tips {
    background: #f8f9fa;
    padding: 24px;
    border-radius: 12px;
}

.security-tips h4 {
    margin: 0 0 16px 0;
    color: #333;
}

.security-tips ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.security-tips li {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    color: #666;
    font-size: 14px;
}

.security-tips i {
    color: #28a745;
}

/* Responsive */
@media (max-width: 768px) {
    .info-grid,
    .security-container {
        grid-template-columns: 1fr;
    }
    
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-avatar {
        margin-right: 0;
        margin-bottom: 20px;
    }
    
    .profile-nav {
        flex-direction: column;
    }
    
    .nav-btn {
        justify-content: flex-start;
    }
    
    .favorites-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }

    .profile-orders-head,
    .profile-order-top {
        align-items: flex-start;
        flex-direction: column;
    }

    .profile-order-total {
        justify-items: start;
    }

    .profile-order-item {
        grid-template-columns: 60px minmax(0, 1fr);
    }

    .profile-order-item > strong {
        grid-column: 1 / -1;
    }
}
</style>

<script>
// Tab switching
document.querySelectorAll('.nav-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tab = this.dataset.tab;
        
        // Remove active class from all buttons and panes
        document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        
        // Add active class to clicked button and corresponding pane
        this.classList.add('active');
        document.getElementById(tab).classList.add('active');
    });
});

// Edit form toggle
function toggleEdit(form) {
    const formElement = document.getElementById(form + '-form');
    const inputs = formElement.querySelectorAll('input, textarea');
    const actions = formElement.querySelector('.form-actions');
    const editBtn = formElement.closest('.info-card').querySelector('.edit-btn');
    
    inputs.forEach(input => {
        if (input.name !== 'email') { // Don't allow email editing
            input.readOnly = false;
            input.style.background = 'white';
            input.style.cursor = 'text';
        }
    });
    
    actions.style.display = 'flex';
    editBtn.style.display = 'none';
}

function cancelEdit(form) {
    const formElement = document.getElementById(form + '-form');
    const inputs = formElement.querySelectorAll('input, textarea');
    const actions = formElement.querySelector('.form-actions');
    const editBtn = formElement.closest('.info-card').querySelector('.edit-btn');
    
    inputs.forEach(input => {
        input.readOnly = true;
        input.style.background = '#f8f9fa';
        input.style.cursor = 'not-allowed';
    });
    
    actions.style.display = 'none';
    editBtn.style.display = 'flex';
    
    // Reset form
    formElement.reset();
    location.reload(); // Simple way to reset values
}

// Remove favorite
function removeFavorite(lensId) {
    if (confirm('Bạn có chắc muốn bỏ yêu thích sản phẩm này?')) {
        fetch(`/favorites/remove/${lensId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra. Vui lòng thử lại.');
        });
    }
}

// Show success/error messages
@if(session('success'))
    setTimeout(() => {
        alert('{{ session("success") }}');
    }, 100);
@endif

@if($errors->any())
    setTimeout(() => {
        alert('{{ $errors->first() }}');
    }, 100);
@endif
</script>
@endsection


