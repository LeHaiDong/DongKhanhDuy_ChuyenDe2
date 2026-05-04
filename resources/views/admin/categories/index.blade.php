@extends('layouts.admin')

@section('title', 'Quản lý danh mục - MienTayShop Admin')
@section('page-title', 'Quản lý danh mục')

@section('content')
<!-- Filter and Search Bar -->
<div class="admin-card" style="margin-bottom: 24px;">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="filter-form">
            <div style="display: grid; grid-template-columns: 1fr auto auto auto auto auto; gap: 16px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="search">Tìm kiếm</label>
                    <input type="text" id="search" name="search" 
                           value="{{ request('search') }}" 
                           class="form-input">
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="status">Trạng thái</label>
                    <select id="status" name="status" class="form-input">
                        <option value="">Tất cả</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="parent">Danh mục cha</label>
                    <select id="parent" name="parent" class="form-input">
                        <option value="">Danh mục đang hiện ở trang khách hàng</option>
                        <option value="all" {{ request('parent') === 'all' ? 'selected' : '' }}>Tất cả danh mục</option>
                        <option value="root" {{ request('parent') === 'root' ? 'selected' : '' }}>Danh mục gốc</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}" {{ request('parent') == $parent->id ? 'selected' : '' }}>
                                {{ $parent->display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <button type="submit" class="nike-btn nike-btn-primary">
                    <i class="fas fa-search"></i>
                    Tìm kiếm
                </button>
                
                <a href="{{ route('admin.categories.index') }}" class="nike-btn nike-btn-outline">
                    <i class="fas fa-undo"></i>
                    Đặt lại
                </a>
                
                <a href="{{ route('admin.categories.create') }}" class="nike-btn nike-btn-success">
                    <i class="fas fa-plus"></i>
                    Thêm danh mục
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions -->
<div class="admin-card" style="margin-bottom: 24px; display: none;" id="bulkActions">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.categories.bulk-action') }}" id="bulkActionForm">
            @csrf
            <div style="display: flex; align-items: center; gap: 16px;">
                <span class="selected-count">Đã chọn <strong id="selectedCount">0</strong> danh mục</span>
                
                <select name="action" class="form-input" style="width: auto;">
                    <option value="">Chọn thao tác</option>
                    <option value="activate">Kích hoạt</option>
                    <option value="deactivate">Tạm dừng</option>
                    <option value="delete">Xóa</option>
                </select>
                
                <button type="submit" class="nike-btn nike-btn-warning">
                    <i class="fas fa-play"></i>
                    Thực hiện
                </button>
                
                <button type="button" class="nike-btn nike-btn-outline" onclick="clearSelection()">
                    <i class="fas fa-times"></i>
                    Bỏ chọn
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Categories Table -->
<div class="admin-card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-folder-tree" style="margin-right: 8px; color: #667eea;"></i>
            Danh sách danh mục ({{ $categories->total() }} danh mục)
        </h3>
        <div class="card-actions">
            <button type="button" class="nike-btn nike-btn-outline nike-btn-sm" onclick="toggleSelectAll()">
                <i class="fas fa-check-square"></i>
                Chọn tất cả
            </button>
        </div>
    </div>
    
    <div class="card-body" style="padding: 0;">
        @if($categories->count() > 0)
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                        </th>
                        <th width="60">Hình</th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}"
                               class="sort-link {{ request('sort') === 'name' ? 'active' : '' }}">
                                Tên danh mục
                                <i class="fas fa-sort{{ request('sort') === 'name' ? (request('order') === 'asc' ? '-up' : '-down') : '' }}"></i>
                            </a>
                        </th>
                        <th>Danh mục cha</th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'products_count', 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}"
                               class="sort-link {{ request('sort') === 'products_count' ? 'active' : '' }}">
                                Sản phẩm
                                <i class="fas fa-sort{{ request('sort') === 'products_count' ? (request('order') === 'asc' ? '-up' : '-down') : '' }}"></i>
                            </a>
                        </th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'sort_order', 'order' => request('order') === 'asc' ? 'desc' : 'asc']) }}"
                               class="sort-link {{ request('sort') === 'sort_order' ? 'active' : '' }}">
                                Thứ tự
                                <i class="fas fa-sort{{ request('sort') === 'sort_order' ? (request('order') === 'asc' ? '-up' : '-down') : '' }}"></i>
                            </a>
                        </th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th width="150">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                        <tr data-category-id="{{ $category->id }}">
                            <td>
                                <input type="checkbox" class="category-checkbox" 
                                       name="categories[]" 
                                       value="{{ $category->id }}"
                                       onchange="updateBulkActions()">
                            </td>
                            <td>
                                <div class="category-image">
                                    @if($category->display_image_url)
                                        <img src="{{ $category->display_image_url }}" 
                                             alt="{{ $category->display_name }}"
                                             class="category-thumb">
                                    @else
                                        <div class="category-icon-placeholder">
                                            <i class="{{ $category->icon_with_fallback }}"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="category-info">
                                    <div class="category-name">
                                        {{ $category->display_name }}
                                        @if($category->children_count > 0)
                                            <span class="children-badge">{{ $category->children_count }} con</span>
                                        @endif
                                    </div>
                                    <div class="category-slug">{{ $category->slug }}</div>
                                    @if($category->description)
                                        <div class="category-description">{{ Str::limit($category->description, 100) }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($category->parent)
                                    <span class="parent-badge">{{ $category->parent->display_name }}</span>
                                @else
                                    <span class="text-muted">Gốc</span>
                                @endif
                            </td>
                            <td>
                                <span class="products-count {{ $category->total_products_count > 0 ? 'has-products' : '' }}">
                                    {{ $category->total_products_count }} sản phẩm
                                </span>
                            </td>
                            <td>
                                <input type="number" 
                                       class="sort-order-input" 
                                       value="{{ $category->sort_order }}"
                                       min="0"
                                       onchange="updateSortOrder({{ $category->id }}, this.value)">
                            </td>
                            <td>
                                <div class="status-toggle-container">
                                    <label class="status-toggle">
                                        <input type="checkbox" 
                                               {{ $category->is_active ? 'checked' : '' }}
                                               onchange="toggleStatus({{ $category->id }}, this.checked)">
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span class="status-text">{{ $category->is_active ? 'Hoạt động' : 'Tạm dừng' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="date-info">
                                    <div class="date-main">{{ $category->created_at->format('d/m/Y') }}</div>
                                    <div class="date-sub">{{ $category->created_at->format('H:i') }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('admin.categories.show', $category) }}" 
                                       class="action-btn action-view" 
                                       title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.categories.edit', $category) }}" 
                                       class="action-btn action-edit" 
                                       title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="action-btn action-delete" 
                                            title="Xóa"
                                            onclick="deleteCategory({{ $category->id }}, @js($category->display_name), {{ $category->total_products_count }}, {{ $category->children_count }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="pagination-container">
                {{ $categories->appends(request()->query())->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h3>Chưa có danh mục nào</h3>
                <p>Chưa có danh mục nào được tạo hoặc không có danh mục nào phù hợp với điều kiện tìm kiếm.</p>
                <a href="{{ route('admin.categories.create') }}" class="nike-btn nike-btn-primary">
                    <i class="fas fa-plus"></i>
                    Tạo danh mục đầu tiên
                </a>
            </div>
        @endif
    </div>
</div>

<style>
/* Category specific styles */
.category-image {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
}

.category-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.category-icon-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    font-size: 18px;
}

.category-info {
    min-width: 0; /* Allow text truncation */
}

.category-name {
    font-weight: 600;
    color: #333;
    font-size: 14px;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.children-badge {
    background: #667eea;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 500;
}

.category-slug {
    color: #666;
    font-size: 12px;
    font-family: monospace;
    margin-bottom: 2px;
}

.category-description {
    color: #888;
    font-size: 11px;
    line-height: 1.3;
}

.parent-badge {
    background: #e9ecef;
    color: #495057;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.products-count {
    font-weight: 600;
    color: #666;
    font-size: 14px;
}

.products-count.has-products {
    color: #28a745;
}

.sort-order-input {
    width: 60px;
    padding: 4px 6px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-align: center;
    font-size: 12px;
}

.sort-order-input:focus {
    outline: none;
    border-color: #667eea;
}

/* Status Toggle */
.status-toggle-container {
    display: flex;
    align-items: center;
    gap: 8px;
}

.status-toggle {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
}

.status-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: #28a745;
}

input:checked + .toggle-slider:before {
    transform: translateX(20px);
}

.status-text {
    font-size: 12px;
    color: #666;
    min-width: 60px;
}

/* Date info */
.date-info {
    text-align: center;
}

.date-main {
    font-weight: 500;
    color: #333;
    font-size: 13px;
}

.date-sub {
    color: #999;
    font-size: 11px;
}

/* Bulk actions */
.selected-count {
    color: #667eea;
    font-weight: 500;
}

/* Sort links */
.sort-link {
    color: #333;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 4px;
}

.sort-link:hover {
    color: #667eea;
}

.sort-link.active {
    color: #667eea;
    font-weight: 600;
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
    margin-bottom: 24px;
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

.action-delete {
    background: #dc3545;
    color: white;
}

.action-delete:hover {
    background: #c82333;
}

.action-buttons {
    display: flex;
    gap: 4px;
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
}
</style>

<script>
// Bulk actions
function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.category-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    if (checkboxes.length > 0) {
        bulkActions.style.display = 'block';
        selectedCount.textContent = checkboxes.length;
        
        // Add selected values to bulk action form
        const form = document.getElementById('bulkActionForm');
        // Remove existing hidden inputs
        form.querySelectorAll('input[name="categories[]"]').forEach(input => input.remove());
        
        // Add new hidden inputs
        checkboxes.forEach(checkbox => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'categories[]';
            hiddenInput.value = checkbox.value;
            form.appendChild(hiddenInput);
        });
    } else {
        bulkActions.style.display = 'none';
    }
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
    
    categoryCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateBulkActions();
}

function clearSelection() {
    document.getElementById('selectAll').checked = false;
    document.querySelectorAll('.category-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    updateBulkActions();
}

// Status toggle
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
            // Update status text
            const row = document.querySelector(`tr[data-category-id="${categoryId}"]`);
            const statusText = row.querySelector('.status-text');
            statusText.textContent = isActive ? 'Hoạt động' : 'Tạm dừng';
            
            // Show success message
            showNotification(data.message, 'success');
        } else {
            showNotification('Có lỗi xảy ra', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra', 'error');
    });
}

// Update sort order
function updateSortOrder(categoryId, sortOrder) {
    const data = {
        categories: [{
            id: categoryId,
            sort_order: parseInt(sortOrder)
        }]
    };
    
    fetch('/admin/categories/reorder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra khi cập nhật thứ tự', 'error');
    });
}

// Delete category
function deleteCategory(categoryId, categoryName, productsCount, childrenCount) {
    let message = `Bạn có chắc muốn xóa danh mục "${categoryName}"?`;
    
    if (productsCount > 0) {
        message += `\n\nDanh mục này có ${productsCount} sản phẩm. Bạn cần di chuyển hoặc xóa sản phẩm trước.`;
        alert(message);
        return;
    }
    
    if (childrenCount > 0) {
        message += `\n\nDanh mục này có ${childrenCount} danh mục con. Bạn cần xử lý danh mục con trước.`;
        alert(message);
        return;
    }
    
    if (confirm(message)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/categories/${categoryId}`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Bulk action form submission
document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
    const action = this.querySelector('select[name="action"]').value;
    if (!action) {
        e.preventDefault();
        alert('Vui lòng chọn thao tác');
        return;
    }
    
    const selectedCount = document.querySelectorAll('.category-checkbox:checked').length;
    const actionNames = {
        'activate': 'kích hoạt',
        'deactivate': 'tạm dừng',
        'delete': 'xóa'
    };
    
    if (!confirm(`Bạn có chắc muốn ${actionNames[action]} ${selectedCount} danh mục đã chọn?`)) {
        e.preventDefault();
    }
});

// Notification function
function showNotification(message, type = 'info') {
    // Simple notification - you can replace with a more sophisticated notification system
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        z-index: 1000;
        max-width: 300px;
    `;
    
    if (type === 'success') {
        notification.style.backgroundColor = '#28a745';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#dc3545';
    } else {
        notification.style.backgroundColor = '#17a2b8';
    }
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Show success/error messages
@if(session('success'))
    showNotification('{{ session("success") }}', 'success');
@endif

@if(session('error'))
    showNotification('{{ session("error") }}', 'error');
@endif

// Initialize bulk actions on page load
document.addEventListener('DOMContentLoaded', function() {
    updateBulkActions();
});
</script>
@endsection


