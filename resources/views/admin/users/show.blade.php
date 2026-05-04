@extends('layouts.admin')

@section('title', 'Chi tiết người dùng - Admin Panel')
@section('page-title', 'Chi tiết người dùng')

@section('content')
<!-- User Header -->
<div class="admin-card" style="margin-bottom: 24px;">
    <div class="card-body">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <div class="user-avatar-lg">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-header-info">
                    <h2 class="user-name">{{ $user->name }}</h2>
                    <p class="user-email">{{ $user->email }}</p>
                    <div class="user-badges">
                        @if($user->is_admin)
                            <span class="status-badge status-admin">
                                <i class="fas fa-shield-alt"></i>
                                Quản trị viên
                            </span>
                        @else
                            <span class="status-badge status-customer">
                                <i class="fas fa-user"></i>
                                Khách hàng
                            </span>
                        @endif
                        <span class="member-since">
                            <i class="fas fa-calendar"></i>
                            Thành viên từ {{ $stats['join_date'] }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="header-actions">
                <a href="{{ route('admin.users.edit', $user) }}" class="nike-btn nike-btn-primary">
                    <i class="fas fa-edit"></i>
                    Chỉnh sửa
                </a>
                <a href="{{ route('admin.users.index') }}" class="nike-btn nike-btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
            </div>
        </div>
    </div>
</div>

<!-- User Stats -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card-sm">
        <div class="stat-icon-sm" style="background: linear-gradient(135deg, #28a745, #34ce57);">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['days_member'] }}</div>
            <div class="stat-label">Ngày thành viên</div>
        </div>
    </div>
    
    <div class="stat-card-sm">
        <div class="stat-icon-sm" style="background: linear-gradient(135deg, #dc3545, #e63946);">
            <i class="fas fa-heart"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['favorites_count'] }}</div>
            <div class="stat-label">Sản phẩm yêu thích</div>
        </div>
    </div>
    
    <div class="stat-card-sm">
        <div class="stat-icon-sm" style="background: linear-gradient(135deg, #17a2b8, #20c997);">
            <i class="fas fa-comments"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['chat_messages_count'] }}</div>
            <div class="stat-label">Tin nhắn chat</div>
        </div>
    </div>
    
    <div class="stat-card-sm">
        <div class="stat-icon-sm" style="background: linear-gradient(135deg, #ffc107, #ffca2c);">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $user->updated_at->diffForHumans() }}</div>
            <div class="stat-label">Hoạt động cuối</div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- User Information -->
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-circle" style="margin-right: 8px; color: #667eea;"></i>
                Thông tin cá nhân
            </h3>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Họ và tên</div>
                    <div class="info-value">{{ $user->name }}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $user->email }}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Số điện thoại</div>
                    <div class="info-value">{{ $user->phone ?: 'Chưa cập nhật' }}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Địa chỉ</div>
                    <div class="info-value">{{ $user->address ?: 'Chưa cập nhật' }}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Ngày tham gia</div>
                    <div class="info-value">{{ $stats['join_date'] }}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Hoạt động gần đây</div>
                    <div class="info-value">{{ $stats['last_activity'] }}</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Favorites -->
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-heart" style="margin-right: 8px; color: #dc3545;"></i>
                Sản phẩm yêu thích gần đây
            </h3>
        </div>
        <div class="card-body">
            @if($recentFavorites->count() > 0)
                <div class="favorites-list">
                    @foreach($recentFavorites as $lens)
                        <div class="favorite-item">
                            <div class="favorite-image">
                                <img src="{{ $lens->image_url }}" alt="{{ $lens->name }}">
                            </div>
                            <div class="favorite-info">
                                <div class="favorite-name">{{ $lens->name }}</div>
                                <div class="favorite-brand">{{ $lens->brand }} • {{ $lens->display_product_type }}</div>
                                <div class="favorite-price">{{ $lens->formatted_price }}</div>
                            </div>
                            <div class="favorite-actions">
                                <a href="{{ route('products.show', $lens) }}" 
                                   class="action-btn action-view" 
                                   title="Xem chi tiết"
                                   target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state-sm">
                    <i class="fas fa-heart"></i>
                    <span>Chưa có sản phẩm yêu thích</span>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Recent Chat Messages -->
<div class="admin-card" style="margin-top: 24px;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-comment-dots" style="margin-right: 8px; color: #17a2b8;"></i>
            Tin nhắn chat gần đây
        </h3>
    </div>
    <div class="card-body">
        @if($recentMessages->count() > 0)
            <div class="messages-list">
                @foreach($recentMessages as $message)
                    <div class="message-item {{ $message->is_user ? 'user-message' : 'ai-message' }}">
                        <div class="message-avatar">
                            <i class="fas fa-{{ $message->is_user ? 'user' : 'robot' }}"></i>
                        </div>
                        <div class="message-content">
                            <div class="message-text">{{ $message->message }}</div>
                            <div class="message-time">{{ $message->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state-sm">
                <i class="fas fa-comments"></i>
                <span>Chưa có tin nhắn chat</span>
            </div>
        @endif
    </div>
</div>

<style>
/* User Avatar Large */
.user-avatar-lg {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 32px;
    flex-shrink: 0;
}

.user-header-info {
    flex: 1;
}

.user-name {
    margin: 0 0 8px 0;
    font-size: 28px;
    font-weight: 700;
    color: #333;
}

.user-email {
    margin: 0 0 12px 0;
    font-size: 16px;
    color: #666;
}

.user-badges {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.member-since {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #999;
    font-size: 14px;
}

.header-actions {
    display: flex;
    gap: 12px;
}

/* Stats Cards Small */
.stat-card-sm {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-icon-sm {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    flex-shrink: 0;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 24px;
    font-weight: 700;
    color: #333;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 14px;
    color: #666;
}

/* Info Grid */
.info-grid {
    display: grid;
    gap: 20px;
}

.info-item {
    padding-bottom: 16px;
    border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.info-label {
    font-weight: 600;
    color: #666;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 6px;
}

.info-value {
    color: #333;
    font-size: 14px;
    font-weight: 500;
}

/* Favorites List */
.favorites-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.favorite-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: background 0.2s ease;
}

.favorite-item:hover {
    background: #e9ecef;
}

.favorite-image {
    width: 50px;
    height: 50px;
    border-radius: 6px;
    overflow: hidden;
    flex-shrink: 0;
}

.favorite-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    width: 100%;
    height: 100%;
    background: #ddd;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
}

.favorite-info {
    flex: 1;
    min-width: 0;
}

.favorite-name {
    font-weight: 600;
    color: #333;
    font-size: 14px;
    margin-bottom: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.favorite-brand {
    color: #666;
    font-size: 12px;
    margin-bottom: 2px;
}

.favorite-price {
    color: #667eea;
    font-weight: 600;
    font-size: 13px;
}

.favorite-actions {
    flex-shrink: 0;
}

/* Messages List */
.messages-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: 400px;
    overflow-y: auto;
}

.message-item {
    display: flex;
    gap: 12px;
    padding: 12px;
    border-radius: 8px;
}

.user-message {
    background: #e3f2fd;
    flex-direction: row-reverse;
}

.ai-message {
    background: #f8f9fa;
}

.message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
    flex-shrink: 0;
}

.user-message .message-avatar {
    background: #2196f3;
}

.ai-message .message-avatar {
    background: #667eea;
}

.message-content {
    flex: 1;
    min-width: 0;
}

.user-message .message-content {
    text-align: right;
}

.message-text {
    color: #333;
    font-size: 14px;
    line-height: 1.4;
    margin-bottom: 4px;
}

.message-time {
    color: #999;
    font-size: 11px;
}

/* Empty State Small */
.empty-state-sm {
    text-align: center;
    padding: 40px 20px;
    color: #999;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.empty-state-sm i {
    font-size: 32px;
    color: #ddd;
}

/* Action Buttons */
.action-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.2s ease;
}

.action-view {
    background: #17a2b8;
    color: white;
}

.action-view:hover {
    background: #138496;
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .card-body > div:first-child {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 16px !important;
    }
    
    .header-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .main-content-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endsection
