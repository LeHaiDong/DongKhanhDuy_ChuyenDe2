@extends('layouts.admin')

@section('title', 'Chi tiết danh mục - Admin Panel')
@section('page-title', 'Chi tiết danh mục')

@section('content')
<!-- Category Header -->
<div class="admin-card" style="margin-bottom: 24px;">
    <div class="card-header">
        <div class="category-header-info">
            <div class="category-avatar">
                @if($category->display_image_url)
                    <img src="{{ $category->display_image_url }}" alt="{{ $category->display_name }}">
                @else
                    <div class="category-icon">
                        <i class="{{ $category->icon_with_fallback }}"></i>
                    </div>
                @endif
            </div>
            <div class="category-details">
                <h1 class="category-name">{{ $category->display_name }}</h1>
                <div class="category-path">{{ $category->full_path }}</div>
                @if($category->description)
                    <p class="category-description">{{ $category->description }}</p>
                @endif
                <div class="category-meta">
                    <span class="status-badge {{ $category->is_active ? 'active' : 'inactive' }}">
                        {{ $category->is_active ? 'Hoạt động' : 'Tạm dừng' }}
                    </span>
                    <span class="slug-badge">{{ $category->slug }}</span>
                </div>
            </div>
        </div>
        <div class="card-actions">
            <a href="{{ route('admin.categories.edit', $category) }}" class="nike-btn nike-btn-primary">
                <i class="fas fa-edit"></i>
                Chỉnh sửa
            </a>
            <button type="button" 
                    class="nike-btn nike-btn-warning"
                    onclick="toggleStatus({{ $category->id }}, {{ $category->is_active ? 'false' : 'true' }})">
                <i class="fas fa-toggle-{{ $category->is_active ? 'off' : 'on' }}"></i>
                {{ $category->is_active ? 'Tạm dừng' : 'Kích hoạt' }}
            </button>
            <a href="{{ route('admin.categories.index') }}" class="nike-btn nike-btn-outline">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>
</div>

<!-- Statistics Overview -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card-modern">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
            <i class="fas fa-camera"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['total_products'] }}</div>
            <div class="stat-label">Tổng sản phẩm</div>
        </div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon" style="background: linear-gradient(135deg, #28a745, #34ce57);">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['active_products'] }}</div>
            <div class="stat-label">Sản phẩm hoạt động</div>
        </div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon" style="background: linear-gradient(135deg, #ffc107, #ffca2c);">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['in_stock_products'] }}</div>
            <div class="stat-label">Còn hàng</div>
        </div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon" style="background: linear-gradient(135deg, #17a2b8, #20c997);">
            <i class="fas fa-sitemap"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['total_children'] }}</div>
            <div class="stat-label">Danh mục con</div>
        </div>
    </div>
    
    <div class="stat-card-modern">
        <div class="stat-icon" style="background: linear-gradient(135deg, #6f42c1, #e83e8c);">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $stats['total_descendants'] }}</div>
            <div class="stat-label">Tổng cấp con</div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Category Information -->
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle" style="margin-right: 8px; color: #17a2b8;"></i>
                Thông tin chi tiết
            </h3>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-section">
                    <h4 class="info-section-title">Thông tin cơ bản</h4>
                    <div class="info-item">
                        <label>Tên danh mục:</label>
                        <span>{{ $category->display_name }}</span>
                    </div>
                    <div class="info-item">
                        <label>Slug:</label>
                        <span class="code">{{ $category->slug }}</span>
                    </div>
                    <div class="info-item">
                        <label>Mô tả:</label>
                        <span>{{ $category->description ?: 'Chưa có mô tả' }}</span>
                    </div>
                    <div class="info-item">
                        <label>URL danh mục:</label>
                        <a href="{{ $category->url }}" target="_blank" class="external-link">
                            {{ $category->url }}
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>
                
                <div class="info-section">
                    <h4 class="info-section-title">Phân cấp</h4>
                    <div class="info-item">
                        <label>Danh mục cha:</label>
                        <span>
                            @if($category->parent)
                                <a href="{{ route('admin.categories.show', $category->parent) }}" class="parent-link">
                                    {{ $category->parent->display_name }}
                                </a>
                            @else
                                <em>Danh mục gốc</em>
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <label>Thứ tự hiển thị:</label>
                        <span>{{ $category->sort_order }}</span>
                    </div>
                    <div class="info-item">
                        <label>Đường dẫn đầy đủ:</label>
                        <span class="breadcrumb-path">{{ $category->full_path }}</span>
                    </div>
                </div>
                
                <div class="info-section">
                    <h4 class="info-section-title">SEO & Meta</h4>
                    <div class="info-item">
                        <label>Meta Title:</label>
                        <span>{{ $category->meta_title ?: 'Chưa thiết lập' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Meta Description:</label>
                        <span>{{ $category->meta_description ?: 'Chưa thiết lập' }}</span>
                    </div>
                </div>
                
                <div class="info-section">
                    <h4 class="info-section-title">Timestamps</h4>
                    <div class="info-item">
                        <label>Ngày tạo:</label>
                        <span>{{ $category->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div class="info-item">
                        <label>Cập nhật cuối:</label>
                        <span>{{ $category->updated_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Category Tree -->
    <div>
        <!-- Children Categories -->
        @if($category->children->count() > 0)
            <div class="admin-card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-sitemap" style="margin-right: 8px; color: #28a745;"></i>
                        Danh mục con ({{ $category->children->count() }})
                    </h3>
                    <div class="card-actions">
                        <a href="{{ route('admin.categories.create') }}?parent_id={{ $category->id }}" class="nike-btn nike-btn-success nike-btn-sm">
                            <i class="fas fa-plus"></i>
                            Thêm con
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="children-list">
                        @foreach($category->children as $child)
                            <div class="child-item">
                                <div class="child-info">
                                    <div class="child-icon">
                                        @if($child->display_image_url)
                                            <img src="{{ $child->display_image_url }}" alt="{{ $child->display_name }}">
                                        @else
                                            <i class="{{ $child->icon_with_fallback }}"></i>
                                        @endif
                                    </div>
                                    <div class="child-details">
                                        <div class="child-name">{{ $child->display_name }}</div>
                                        <div class="child-stats">
                                            {{ $child->total_products_count }} sản phẩm
                                        </div>
                                    </div>
                                </div>
                                <div class="child-actions">
                                    <a href="{{ route('admin.categories.show', $child) }}" class="action-btn action-view">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.categories.edit', $child) }}" class="action-btn action-edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Breadcrumb -->
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-route" style="margin-right: 8px; color: #6f42c1;"></i>
                    Đường dẫn danh mục
                </h3>
            </div>
            <div class="card-body">
                <div class="breadcrumb-tree">
                    @foreach($category->breadcrumb as $crumb)
                        <div class="breadcrumb-item {{ $loop->last ? 'current' : '' }}">
                            @if(!$loop->last)
                                <a href="{{ route('admin.categories.show', $crumb['id']) }}">
                                    {{ $crumb['name'] }}
                                </a>
                                <i class="fas fa-chevron-right"></i>
                            @else
                                <span class="current-category">{{ $crumb['name'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Products -->
@if($recentProducts->count() > 0)
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-camera" style="margin-right: 8px; color: #ff6b6b;"></i>
                Sản phẩm gần đây ({{ $recentProducts->count() }})
            </h3>
            <div class="card-actions">
                <a href="{{ route('admin.products.index') }}?category={{ $category->id }}" class="nike-btn nike-btn-outline nike-btn-sm">
                    <i class="fas fa-list"></i>
                    Xem tất cả
                </a>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="80">Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Thương hiệu</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Trạng thái</th>
                        <th>Ngày thêm</th>
                        <th width="100">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentProducts as $product)
                        <tr>
                            <td>
                                <div class="product-image-mini">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                </div>
                            </td>
                            <td>
                                <div class="product-name-mini">{{ Str::limit($product->name, 40) }}</div>
                                <div class="product-categories-mini">
                                    {{ $product->category_names }}
                                </div>
                            </td>
                            <td>
                                <span class="brand-badge">{{ $product->brand }}</span>
                            </td>
                            <td>
                                <span class="price-text">{{ $product->formatted_price }}</span>
                            </td>
                            <td>
                                <span class="stock-count {{ $product->in_stock ? 'in-stock' : 'out-of-stock' }}">
                                    {{ $product->stock_quantity }}
                                </span>
                            </td>
                            <td>
                                <span class="status-badge {{ $product->is_active ? 'active' : 'inactive' }}">
                                    {{ $product->is_active ? 'Hoạt động' : 'Tạm dừng' }}
                                </span>
                            </td>
                            <td>
                                <div class="date-mini">{{ $product->created_at->format('d/m/Y') }}</div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('products.show', $product) }}" 
                                       class="action-btn action-view" 
                                       title="Xem chi tiết"
                                       target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" 
                                       class="action-btn action-edit" 
                                       title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="admin-card">
        <div class="card-body">
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-camera"></i>
                </div>
                <h3>Chưa có sản phẩm nào</h3>
                <p>Danh mục này chưa có sản phẩm nào được gán.</p>
                <a href="{{ route('admin.products.create') }}" class="nike-btn nike-btn-primary">
                    <i class="fas fa-plus"></i>
                    Thêm sản phẩm đầu tiên
                </a>
            </div>
        </div>
    </div>
@endif

<style>
/* Category Header */
.category-header-info {
    display: flex;
    align-items: flex-start;
    gap: 20px;
}

.category-avatar {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.category-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.category-icon {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    font-size: 32px;
}

.category-details {
    flex: 1;
    min-width: 0;
}

.category-name {
    margin: 0 0 8px 0;
    color: #333;
    font-size: 28px;
    font-weight: 700;
}

.category-path {
    color: #666;
    font-size: 14px;
    margin-bottom: 8px;
    font-weight: 500;
}

.category-description {
    color: #555;
    margin: 0 0 12px 0;
    line-height: 1.5;
}

.category-meta {
    display: flex;
    gap: 8px;
    align-items: center;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.active {
    background: #d4edda;
    color: #155724;
}

.status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}

.slug-badge {
    background: #e9ecef;
    color: #495057;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-family: monospace;
}

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

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.info-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.info-section-title {
    margin: 0 0 16px 0;
    color: #333;
    font-size: 16px;
    font-weight: 600;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 8px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
    gap: 12px;
}

.info-item:last-child {
    margin-bottom: 0;
}

.info-item label {
    font-weight: 600;
    color: #555;
    font-size: 13px;
    min-width: 120px;
    flex-shrink: 0;
}

.info-item span {
    color: #333;
    font-size: 13px;
    word-break: break-word;
    text-align: right;
}

.code {
    font-family: monospace;
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px !important;
}

.external-link {
    color: #667eea;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 4px;
}

.external-link:hover {
    text-decoration: underline;
}

.parent-link {
    color: #28a745;
    text-decoration: none;
    font-weight: 500;
}

.parent-link:hover {
    text-decoration: underline;
}

.breadcrumb-path {
    font-style: italic;
    color: #666 !important;
}

/* Children List */
.children-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.child-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.child-item:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.child-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.child-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    font-size: 16px;
    flex-shrink: 0;
}

.child-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.child-details {
    flex: 1;
    min-width: 0;
}

.child-name {
    font-weight: 600;
    color: #333;
    font-size: 14px;
    margin-bottom: 2px;
}

.child-stats {
    color: #666;
    font-size: 12px;
}

.child-actions {
    display: flex;
    gap: 4px;
}

/* Breadcrumb Tree */
.breadcrumb-tree {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.breadcrumb-item a {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    padding: 4px 8px;
    border-radius: 4px;
    transition: background 0.2s ease;
}

.breadcrumb-item a:hover {
    background: #f0f4ff;
    text-decoration: none;
}

.breadcrumb-item.current .current-category {
    color: #333;
    font-weight: 600;
    padding: 4px 8px;
    background: #667eea;
    color: white;
    border-radius: 4px;
}

.breadcrumb-item i {
    color: #ccc;
    font-size: 10px;
}

/* Product Mini Components */
.product-image-mini {
    width: 50px;
    height: 50px;
    border-radius: 6px;
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
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ccc;
    font-size: 18px;
}

.product-name-mini {
    font-weight: 600;
    color: #333;
    font-size: 13px;
    margin-bottom: 2px;
}

.product-categories-mini {
    color: #666;
    font-size: 11px;
}

.brand-badge {
    background: #667eea;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.price-text {
    font-weight: 600;
    color: #667eea;
    font-size: 13px;
}

.stock-count {
    font-weight: 600;
    font-size: 13px;
}

.stock-count.in-stock {
    color: #28a745;
}

.stock-count.out-of-stock {
    color: #dc3545;
}

.date-mini {
    font-size: 12px;
    color: #666;
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

.action-edit {
    background: #ffc107;
    color: #212529;
}

.action-edit:hover {
    background: #e0a800;
    color: #212529;
}

.action-buttons {
    display: flex;
    gap: 4px;
}

/* Empty State */
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

/* Responsive */
@media (max-width: 768px) {
    .category-header-info {
        flex-direction: column;
        text-align: center;
    }
    
    .category-name {
        font-size: 24px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    
    .info-item span {
        text-align: left;
    }
    
    .breadcrumb-tree {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .stat-card-modern {
        padding: 16px;
    }
    
    .stat-card-modern .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .stat-card-modern .stat-number {
        font-size: 24px;
    }
}
</style>

<script>
// Toggle status
function toggleStatus(categoryId, isActive) {
    fetch(`/admin/categories/${categoryId}/toggle-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ is_active: isActive })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to update UI
            location.reload();
        } else {
            alert('Có lỗi xảy ra');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra');
    });
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


