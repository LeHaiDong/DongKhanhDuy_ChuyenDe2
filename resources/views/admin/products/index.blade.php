@extends('layouts.admin')

@section('title', 'Quản lý sản phẩm - MienTayShop Admin')
@section('page-title', 'Quản lý sản phẩm')

@php
    $brands = \App\Models\Product::query()
        ->select('brand')
        ->whereNotNull('brand')
        ->distinct()
        ->orderBy('brand')
        ->pluck('brand');
@endphp

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 28px; font-weight: 900; color: #0f172a; margin-bottom: 8px;">Quản lý sản phẩm</h1>
        <p style="color: #64748b;">Theo dõi toàn bộ catalog đa ngành và cập nhật thông tin theo đúng cấu trúc mới.</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="nike-btn nike-btn-primary">
        <i class="fas fa-plus"></i>
        Thêm sản phẩm mới
    </a>
</div>

<div class="admin-card">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.products.index') }}">
            <div style="display: grid; grid-template-columns: minmax(0, 1.4fr) 220px 180px 180px auto; gap: 16px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Tìm kiếm</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Thương hiệu</label>
                    <select name="brand" class="form-control">
                        <option value="">Tất cả</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand }}" @selected(request('brand') === $brand)>{{ $brand }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-control">
                        <option value="">Tất cả</option>
                        <option value="active" @selected(request('status') === 'active')>Đang bán</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Tạm ẩn</option>
                        <option value="low_stock" @selected(request('status') === 'low_stock')>Sắp hết hàng</option>
                        <option value="out_of_stock" @selected(request('status') === 'out_of_stock')>Hết hàng</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Sắp xếp</label>
                    <select name="sort" class="form-control">
                        <option value="newest" @selected(request('sort', 'newest') === 'newest')>Mới nhất</option>
                        <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                        <option value="name_asc" @selected(request('sort') === 'name_asc')>Tên A-Z</option>
                        <option value="name_desc" @selected(request('sort') === 'name_desc')>Tên Z-A</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Giá tăng dần</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Giá giảm dần</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="nike-btn">
                        <i class="fas fa-filter"></i>
                        Lọc
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="nike-btn nike-btn-outline">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 16px;">
    <div style="color: #64748b; font-weight: 700;">
        Hiển thị <strong>{{ $products->count() }}</strong> trên tổng <strong>{{ $products->total() }}</strong> sản phẩm
    </div>
    <div style="display: flex; align-items: center; gap: 8px;">
        <span style="color: #64748b; font-size: 14px;">Hiển thị:</span>
        @foreach([10, 25, 50] as $perPage)
            <a href="{{ request()->fullUrlWithQuery(['per_page' => $perPage]) }}"
               class="nike-btn nike-btn-outline {{ (int) request('per_page', 10) === $perPage ? 'active' : '' }}"
               style="min-height: 34px; padding: 8px 12px;">
                {{ $perPage }}
            </a>
        @endforeach
    </div>
</div>

@if($products->count() > 0)
    <div class="admin-card">
        <div class="card-body" style="padding: 0; overflow-x: auto;">
            <table class="admin-table" style="min-width: 1080px;">
                <thead>
                    <tr>
                        <th style="width: 88px;">Hình ảnh</th>
                        <th>Sản phẩm</th>
                        <th>Thương hiệu</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th style="width: 128px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>
                                <img src="{{ $product->image_url }}"
                                     alt="{{ $product->name }}"
                                     class="product-image"
                                     onerror="this.onerror=null;this.src='{{ $product->fallback_image_url }}';">
                            </td>
                            <td>
                                <div style="font-weight: 900; color: #0f172a; margin-bottom: 4px;">{{ $product->name }}</div>
                                <div style="font-size: 12px; color: #64748b;">{{ $product->display_product_type }}</div>
                            </td>
                            <td>
                                <span class="status-badge bg-light">{{ $product->brand }}</span>
                            </td>
                            <td>
                                <div style="font-size: 13px; color: #475569; line-height: 1.5;">
                                    {{ $product->category_names ?: 'Chưa gán danh mục' }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 900; color: #2563eb;">{{ $product->formatted_price }}</div>
                            </td>
                            <td>
                                @if($product->stock_quantity > 5)
                                    <span class="status-badge status-active">
                                        <i class="fas fa-check"></i>
                                        {{ $product->stock_quantity }}
                                    </span>
                                @elseif($product->stock_quantity > 0)
                                    <span class="status-badge status-low-stock">
                                        <i class="fas fa-triangle-exclamation"></i>
                                        {{ $product->stock_quantity }}
                                    </span>
                                @else
                                    <span class="status-badge status-inactive">
                                        <i class="fas fa-xmark"></i>
                                        Hết hàng
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="status-badge status-active">
                                        <i class="fas fa-circle-check"></i>
                                        Đang bán
                                    </span>
                                @else
                                    <span class="status-badge status-inactive">
                                        <i class="fas fa-circle-pause"></i>
                                        Tạm ẩn
                                    </span>
                                @endif
                            </td>
                            <td style="font-size: 13px; color: #64748b;">
                                {{ $product->created_at->format('d/m/Y') }}
                                <div>{{ $product->created_at->format('H:i') }}</div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('products.show', $product) }}" class="action-btn action-btn-view" title="Xem sản phẩm" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="action-btn action-btn-edit" title="Chỉnh sửa">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <button type="button" class="action-btn action-btn-delete" title="Xóa" onclick="confirmDelete({{ $product->id }}, '{{ addslashes($product->name) }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($products->hasPages())
        <div style="margin-top: 28px; display: flex; justify-content: center;">
            {{ $products->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    @endif
@else
    <div class="admin-card">
        <div class="card-body" style="text-align: center; padding: 72px 24px;">
            <i class="fas fa-box-open" style="font-size: 60px; color: #cbd5e1; margin-bottom: 20px;"></i>
            <h3 style="font-size: 24px; font-weight: 900; color: #0f172a; margin-bottom: 10px;">Chưa có sản phẩm phù hợp</h3>
            <p style="color: #64748b; margin-bottom: 24px;">Hãy thử đổi bộ lọc hoặc thêm sản phẩm mới vào catalog.</p>
            <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
                <a href="{{ route('admin.products.index') }}" class="nike-btn nike-btn-outline">
                    <i class="fas fa-rotate-left"></i>
                    Xóa bộ lọc
                </a>
                <a href="{{ route('admin.products.create') }}" class="nike-btn nike-btn-primary">
                    <i class="fas fa-plus"></i>
                    Thêm sản phẩm
                </a>
            </div>
        </div>
    </div>
@endif

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(id, name) {
    if (!confirm(`Bạn có chắc muốn xóa sản phẩm "${name}"?`)) {
        return;
    }

    const form = document.getElementById('deleteForm');
    form.action = `/admin/products/${id}`;
    form.submit();
}
</script>
@endsection
