@extends('layouts.admin')

@section('title', 'Quản lý khách hàng - MienTayShop Admin')
@section('page-title', 'Quản lý khách hàng')

@section('content')
<!-- Search and Filter Bar -->
<div class="admin-card" style="margin-bottom: 24px;">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users.index') }}" class="search-form">
            <div style="display: grid; grid-template-columns: 1fr auto auto; gap: 16px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="search">Tìm kiếm khách hàng</label>
                    <input type="text" id="search" name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Nhập tên, email hoặc số điện thoại"
                           class="form-input">
                </div>
                
                <button type="submit" class="nike-btn nike-btn-primary">
                    <i class="fas fa-search"></i>
                    Tìm kiếm
                </button>
                
                <a href="{{ route('admin.users.create') }}" class="nike-btn nike-btn-success">
                    <i class="fas fa-plus"></i>
                    Thêm khách hàng
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Stats Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card-sm">
        <div class="stat-icon-sm" style="background: linear-gradient(135deg, #667eea, #764ba2);">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ number_format($stats['total_customers'] ?? $users->total()) }}</div>
            <div class="stat-label">Tổng khách hàng</div>
        </div>
    </div>
    
    <div class="stat-card-sm">
        <div class="stat-icon-sm" style="background: linear-gradient(135deg, #28a745, #34ce57);">
            <i class="fas fa-user"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $users->count() }}</div>
            <div class="stat-label">Đang hiển thị</div>
        </div>
    </div>
    
    <div class="stat-card-sm">
        <div class="stat-icon-sm" style="background: linear-gradient(135deg, #0ea5e9, #14b8a6);">
            <i class="fas fa-filter"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">100%</div>
            <div class="stat-label">Chỉ hiển thị khách hàng</div>
        </div>
    </div>
    
    <div class="stat-card-sm">
        <div class="stat-icon-sm" style="background: linear-gradient(135deg, #17a2b8, #20c997);">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ number_format($stats['new_this_week'] ?? 0) }}</div>
            <div class="stat-label">Mới tuần này</div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="admin-card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-users" style="margin-right: 12px; color: #667eea;"></i>
            Danh sách khách hàng ({{ $users->total() }})
        </h2>
        
        <div class="card-actions">
            <button class="nike-btn nike-btn-outline" onclick="toggleBulkActions()">
                <i class="fas fa-tasks"></i>
                Thao tác hàng loạt
            </button>
        </div>
    </div>
    
    <div class="card-body" style="padding: 0;">
        <!-- Bulk Actions Bar -->
        <div id="bulkActionsBar" class="bulk-actions-bar" style="display: none;">
            <form id="bulkActionsForm" method="POST" action="{{ route('admin.users.bulk-action') }}">
                @csrf
                <div class="bulk-actions-content">
                    <div class="bulk-selection">
                        <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes()">
                        <label for="selectAll">Chọn tất cả</label>
                        <span id="selectedCount" class="selected-count">0 đã chọn</span>
                    </div>
                    
                    <div class="bulk-actions-buttons">
                        <select name="action" class="form-input" style="margin-right: 8px;">
                            <option value="">Chọn hành động</option>
                            <option value="delete">Xóa khách hàng</option>
                        </select>
                        
                        <button type="submit" class="nike-btn nike-btn-primary">
                            <i class="fas fa-check"></i>
                            Thực hiện
                        </button>
                        
                        <button type="button" class="nike-btn nike-btn-outline" onclick="toggleBulkActions()">
                            <i class="fas fa-times"></i>
                            Hủy
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if($users->count() > 0)
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="tableSelectAll" onchange="toggleAllTableCheckboxes()" style="display: none;">
                        </th>
                        <th>Thông tin khách hàng</th>
                        <th>Loại tài khoản</th>
                        <th>Ngày tham gia</th>
                        <th>Hoạt động gần đây</th>
                        <th width="180">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" 
                                       class="user-checkbox" style="display: none;"
                                       onchange="updateSelectedCount()">
                            </td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="user-details">
                                        <div class="user-name">{{ $user->name }}</div>
                                        <div class="user-email">{{ $user->email }}</div>
                                        @if($user->phone)
                                            <div class="user-phone">{{ $user->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="user-type">
                                    <span class="status-badge status-customer">
                                        <i class="fas fa-user"></i>
                                        Khách hàng
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="date-info">
                                    <div class="date-main">{{ $user->created_at->format('d/m/Y') }}</div>
                                    <div class="date-sub">{{ $user->created_at->diffForHumans() }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="date-info">
                                    <div class="date-main">{{ $user->updated_at->format('d/m/Y H:i') }}</div>
                                    <div class="date-sub">{{ $user->updated_at->diffForHumans() }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('admin.users.show', $user) }}" 
                                       class="action-btn action-view" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       class="action-btn action-edit" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    @if($user->id !== auth()->id())
                                        <button type="button" 
                                                class="action-btn action-delete" 
                                                title="Xóa khách hàng"
                                                onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <span class="action-btn action-disabled" title="Tài khoản hiện tại">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="table-pagination">
                {{ $users->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Không tìm thấy khách hàng</h3>
                <p>Không có khách hàng nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
                <div class="empty-actions">
                    <a href="{{ route('admin.users.create') }}" class="nike-btn nike-btn-primary">
                        <i class="fas fa-plus"></i>
                        Thêm khách hàng mới
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="nike-btn nike-btn-outline">
                        <i class="fas fa-refresh"></i>
                        Xem tất cả
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
/* User Management Specific Styles */
.search-form .form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #333;
    font-size: 14px;
}

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

/* Bulk Actions */
.bulk-actions-bar {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 16px 24px;
}

.bulk-actions-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.bulk-selection {
    display: flex;
    align-items: center;
    gap: 12px;
}

.selected-count {
    background: #667eea;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.bulk-actions-buttons {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* User Info Display */
.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
    flex-shrink: 0;
}

.user-details {
    flex: 1;
    min-width: 0;
}

.user-name {
    font-weight: 600;
    color: #333;
    font-size: 14px;
    margin-bottom: 2px;
}

.user-email {
    color: #666;
    font-size: 13px;
    margin-bottom: 2px;
}

.user-phone {
    color: #999;
    font-size: 12px;
}

.date-info {
    text-align: left;
}

.date-main {
    font-weight: 500;
    color: #333;
    font-size: 14px;
    margin-bottom: 2px;
}

.date-sub {
    color: #999;
    font-size: 12px;
}

/* Status Badges */
.status-admin {
    background: #dc3545 !important;
    color: white !important;
}

.status-customer {
    background: #28a745 !important;
    color: white !important;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 4px;
}

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

.action-toggle {
    background: #6f42c1;
    color: white;
}

.action-toggle:hover {
    background: #5a32a3;
}

.action-delete {
    background: #dc3545;
    color: white;
}

.action-delete:hover {
    background: #c82333;
}

.action-disabled {
    background: #e9ecef;
    color: #6c757d;
    cursor: not-allowed;
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

.empty-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Responsive */
@media (max-width: 768px) {
    .search-form > div {
        grid-template-columns: 1fr !important;
        gap: 12px !important;
    }
    
    .bulk-actions-content {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }
    
    .bulk-actions-buttons {
        justify-content: center;
    }
    
    .user-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .action-buttons {
        flex-wrap: wrap;
    }
}
</style>

<script>
// Bulk Actions
function toggleBulkActions() {
    const bulkBar = document.getElementById('bulkActionsBar');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    const tableSelectAll = document.getElementById('tableSelectAll');
    
    if (bulkBar.style.display === 'none') {
        bulkBar.style.display = 'block';
        checkboxes.forEach(cb => cb.style.display = 'block');
        tableSelectAll.style.display = 'block';
    } else {
        bulkBar.style.display = 'none';
        checkboxes.forEach(cb => {
            cb.style.display = 'none';
            cb.checked = false;
        });
        tableSelectAll.style.display = 'none';
        tableSelectAll.checked = false;
        updateSelectedCount();
    }
}

function toggleAllTableCheckboxes() {
    const selectAll = document.getElementById('tableSelectAll');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    const countElement = document.getElementById('selectedCount');
    countElement.textContent = checkboxes.length + ' đã chọn';
}

function deleteUser(userId, userName) {
    if (confirm(`Bạn có chắc muốn xóa khách hàng "${userName}"? Hành động này không thể hoàn tác.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${userId}`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Bulk Actions Form Submission
document.getElementById('bulkActionsForm').addEventListener('submit', function(e) {
    const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
    const action = this.querySelector('select[name="action"]').value;
    
    if (selectedCheckboxes.length === 0) {
        e.preventDefault();
        alert('Vui lòng chọn ít nhất một khách hàng');
        return;
    }
    
    if (!action) {
        e.preventDefault();
        alert('Vui lòng chọn hành động');
        return;
    }
    
    // Add selected user IDs to form
    selectedCheckboxes.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'user_ids[]';
        input.value = cb.value;
        this.appendChild(input);
    });
    
    if (action === 'delete') {
        if (!confirm(`Bạn có chắc muốn xóa ${selectedCheckboxes.length} khách hàng đã chọn? Hành động này không thể hoàn tác.`)) {
            e.preventDefault();
            return;
        }
    } else {
        if (!confirm(`Bạn có chắc muốn thực hiện hành động này cho ${selectedCheckboxes.length} khách hàng đã chọn?`)) {
            e.preventDefault();
            return;
        }
    }
});

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


