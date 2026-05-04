@extends('layouts.admin')

@section('title', 'Chỉnh sửa sản phẩm - MienTayShop Admin')
@section('page-title', 'Chỉnh sửa sản phẩm')

@php
    $brands = ['Apple', 'Samsung', 'Xiaomi', 'OPPO', 'realme', 'vivo', 'Sony', 'JBL', 'Anker', 'Baseus', 'Vinamilk', 'Abbott', 'Meiji', 'Nestle', 'Oreo', "Lay's", 'Cosy', 'Unicharm', '3M', 'Sunhouse', 'LocknLock', 'Thiên Long', 'Khác'];
    $productTypes = ['Điện thoại', 'Tablet', 'Tai nghe', 'Loa', 'Phụ kiện sạc', 'Sữa', 'Ngũ cốc', 'Bánh kẹo', 'Khẩu trang', 'Chăm sóc cá nhân', 'Mẹ và bé', 'Gia dụng', 'Văn phòng phẩm', 'Thời trang nam', 'Thời trang nữ', 'Giày dép', 'Đồng hồ', 'Sách'];
    $groupedCategories = isset($categories) ? $categories->groupBy('parent_id') : collect();
    $rootCategories = isset($categories) ? $categories->whereNull('parent_id')->values() : collect();
    $selectedCategoryId = old('category_id', $product->categories->pluck('id')->first());
@endphp

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 28px; font-weight: 900; color: #0f172a; margin-bottom: 8px;">Chỉnh sửa sản phẩm</h1>
        <p style="color: #64748b;">Cập nhật nhanh thông tin cho <strong>{{ $product->name }}</strong> với các trường cần thiết.</p>
    </div>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="{{ route('products.show', $product) }}" class="nike-btn nike-btn-outline" target="_blank">
            <i class="fas fa-eye"></i>
            Xem sản phẩm
        </a>
        <a href="{{ route('admin.products.index') }}" class="nike-btn nike-btn-outline">
            <i class="fas fa-arrow-left"></i>
            Quay lại danh sách
        </a>
    </div>
</div>

<form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div style="display: grid; grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr); gap: 24px;">
        <div style="display: grid; gap: 24px;">
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="card-title">Thông tin chính</h2>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px;">
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label" for="name">Tên sản phẩm</label>
                            <input id="name" name="name" value="{{ old('name', $product->name) }}" class="form-control @error('name') error @enderror" required>
                            @error('name')<div class="error-message">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="brand">Thương hiệu</label>
                            <input list="brand-options" id="brand" name="brand" value="{{ old('brand', $product->brand) }}" class="form-control @error('brand') error @enderror" required>
                            <datalist id="brand-options">
                                @foreach($brands as $brand)
                                    <option value="{{ $brand }}"></option>
                                @endforeach
                            </datalist>
                            @error('brand')<div class="error-message">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="product_type">Loại sản phẩm</label>
                            <select id="product_type" name="product_type" class="form-control @error('product_type') error @enderror" required>
                                <option value="">Chọn loại sản phẩm</option>
                                @foreach($productTypes as $productType)
                                    <option value="{{ $productType }}" @selected(old('product_type', $product->product_type) === $productType)>{{ $productType }}</option>
                                @endforeach
                            </select>
                            @error('product_type')<div class="error-message">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="price">Giá bán</label>
                            <input type="number" id="price" name="price" value="{{ old('price', (int) $product->price) }}" class="form-control @error('price') error @enderror" min="0" step="1000" required>
                            @error('price')<div class="error-message">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="stock_quantity">Tồn kho</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" class="form-control @error('stock_quantity') error @enderror" min="0" required>
                            @error('stock_quantity')<div class="error-message">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="condition">Tình trạng</label>
                            <select id="condition" name="condition" class="form-control @error('condition') error @enderror">
                                <option value="new" @selected(old('condition', $product->condition) === 'new')>Mới</option>
                                <option value="used" @selected(old('condition', $product->condition) === 'used')>Đã sử dụng</option>
                                <option value="refurbished" @selected(old('condition', $product->condition) === 'refurbished')>Tân trang</option>
                            </select>
                            @error('condition')<div class="error-message">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="category_id">Danh mục</label>
                            <select id="category_id" name="category_id" class="form-control @error('category_id') error @enderror">
                                <option value="">Chọn danh mục</option>
                                @foreach($rootCategories as $parent)
                                    <option value="{{ $parent->id }}" @selected((int) $selectedCategoryId === $parent->id)>{{ $parent->display_name }}</option>
                                    @foreach($groupedCategories->get($parent->id, collect()) as $child)
                                        <option value="{{ $child->id }}" @selected((int) $selectedCategoryId === $child->id)>&mdash; {{ $child->display_name }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                            @error('category_id')<div class="error-message">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label" for="description">Mô tả ngắn</label>
                            <textarea id="description" name="description" rows="5" class="form-control @error('description') error @enderror">{{ old('description', $product->description) }}</textarea>
                            @error('description')<div class="error-message">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: grid; gap: 24px;">
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="card-title">Hình ảnh</h2>
                </div>
                <div class="card-body">
                    @if($product->image_url)
                        <div style="margin-bottom: 16px;">
                            <div style="margin-bottom: 10px; color: #64748b; font-size: 12px; font-weight: 900; letter-spacing: 0.08em; text-transform: uppercase;">Hình hiện tại</div>
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width: 100%; max-height: 280px; object-fit: cover; border-radius: 18px;">
                        </div>
                    @endif

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="image">Đổi ảnh sản phẩm</label>
                        <input type="file" id="image" name="image" accept="image/*" class="form-control @error('image') error @enderror" onchange="previewImage(this)">
                        @error('image')<div class="error-message">{{ $message }}</div>@enderror
                    </div>
                    <div id="imagePreview" style="display: none; margin-top: 16px;">
                        <img id="previewImg" style="width: 100%; max-height: 280px; object-fit: cover; border-radius: 18px;">
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <div class="card-header">
                    <h2 class="card-title">Hiển thị</h2>
                </div>
                <div class="card-body">
                    <label style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border: 1px solid #e2e8f0; border-radius: 18px; background: #f8fafc;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))>
                        <span style="font-weight: 800; color: #0f172a;">Hiển thị sản phẩm trên website</span>
                    </label>
                </div>
            </div>

            <div class="admin-card">
                <div class="card-body" style="display: grid; gap: 12px;">
                    <button type="submit" class="nike-btn nike-btn-primary" style="justify-content: center;">
                        <i class="fas fa-save"></i>
                        Cập nhật sản phẩm
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="nike-btn nike-btn-outline" style="justify-content: center;">
                        <i class="fas fa-xmark"></i>
                        Hủy bỏ
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');

    if (!input.files || !input.files[0]) {
        preview.style.display = 'none';
        return;
    }

    const reader = new FileReader();
    reader.onload = function (event) {
        previewImg.src = event.target.result;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
@endsection
