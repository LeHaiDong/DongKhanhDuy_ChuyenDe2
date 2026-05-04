@php
    $selectedCategory = old('category_id', $product->categories->first()->id ?? '');
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="seller-card">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="seller-grid">
        <div class="seller-field full">
            <label for="name">Tên sản phẩm</label>
            <input id="name" name="name" value="{{ old('name', $product->name) }}" required>
            @error('name')<div class="seller-error">{{ $message }}</div>@enderror
        </div>

        <div class="seller-field">
            <label for="brand">Thương hiệu</label>
            <input id="brand" name="brand" value="{{ old('brand', $product->brand ?: $shop->brand_name) }}">
            @error('brand')<div class="seller-error">{{ $message }}</div>@enderror
        </div>

        <div class="seller-field">
            <label for="category_id">Danh mục hiển thị</label>
            <select id="category_id" name="category_id" required>
                <option value="">Chọn danh mục</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) $selectedCategory === $category->id)>{{ $category->display_name }}</option>
                @endforeach
            </select>
            @error('category_id')<div class="seller-error">{{ $message }}</div>@enderror
        </div>

        <div class="seller-field">
            <label for="price">Giá bán</label>
            <input type="number" id="price" name="price" value="{{ old('price', $product->price ? (float) $product->price : '') }}" min="0" step="1000" required>
            @error('price')<div class="seller-error">{{ $message }}</div>@enderror
        </div>

        <div class="seller-field">
            <label for="stock_quantity">Tồn kho</label>
            <input type="number" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 1) }}" min="0" required>
            @error('stock_quantity')<div class="seller-error">{{ $message }}</div>@enderror
        </div>

        <div class="seller-field full">
            <label for="image">Ảnh sản phẩm {{ $isEdit ? '(có thể để trống nếu không đổi)' : '' }}</label>
            <input type="file" id="image" name="image" accept="image/*" {{ $isEdit ? '' : 'required' }} onchange="previewSellerImage(this)">
            @error('image')<div class="seller-error">{{ $message }}</div>@enderror
            <small style="color: #64748b;">Nên dùng ảnh thật đúng sản phẩm để trang khách hàng nhìn rõ và hợp lý hơn.</small>
        </div>

        <div class="seller-field full">
            <div id="sellerImagePreview" style="{{ $isEdit && $product->image ? '' : 'display: none;' }} border: 1px solid #e2e8f0; border-radius: 20px; padding: 14px; background: #f8fafc;">
                <img id="sellerPreviewImg" src="{{ $isEdit && $product->image ? $product->image_url : '' }}" alt="Ảnh xem trước" style="max-width: 260px; width: 100%; max-height: 260px; object-fit: contain; border-radius: 16px; background: white;">
            </div>
        </div>

        <div class="seller-field full">
            <label for="description">Mô tả sản phẩm</label>
            <textarea id="description" name="description" rows="5">{{ old('description', $product->description) }}</textarea>
            @error('description')<div class="seller-error">{{ $message }}</div>@enderror
        </div>

        <div class="seller-field full">
            <label style="display: flex; align-items: center; gap: 10px; text-transform: none; letter-spacing: 0; font-size: 15px;">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true)) style="width: auto;">
                Hiển thị sản phẩm trong cửa hàng
            </label>
        </div>
    </div>

    <div class="seller-actions">
        <button type="submit" class="seller-btn primary">
            <i class="fas fa-floppy-disk"></i>
            {{ $isEdit ? 'Cập nhật sản phẩm' : 'Đăng sản phẩm' }}
        </button>
        <a href="{{ route('seller.products.index') }}" class="seller-btn outline">Quay lại danh sách</a>
    </div>
</form>

@push('scripts')
<script>
function previewSellerImage(input) {
    const wrap = document.getElementById('sellerImagePreview');
    const img = document.getElementById('sellerPreviewImg');

    if (!input.files || !input.files[0]) {
        return;
    }

    const reader = new FileReader();
    reader.onload = function (event) {
        img.src = event.target.result;
        wrap.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
@endpush
