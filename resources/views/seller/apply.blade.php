@extends('layouts.app')

@section('title', 'Mở kênh người bán - MienTayShop')
@push('styles')
    @include('seller.partials.styles')
@endpush

@section('content')
<div class="seller-shell">
    <section class="seller-hero">
        <div>
            <span class="seller-kicker"><i class="fas fa-store"></i> Kênh người bán</span>
            <h1>Tạo shop riêng trên MienTayShop</h1>
            <p>Người bán gửi hồ sơ shop, danh mục kinh doanh, giấy tờ xác minh và hình ảnh nhận diện để admin duyệt trước khi mở bán.</p>
        </div>

        <div class="seller-steps">
            <div class="seller-step">
                <i class="fas fa-file-signature"></i>
                <div>
                    <strong>1. Gửi hồ sơ shop</strong>
                    <span>Cung cấp thông tin shop, ngành hàng chính và giấy tờ xác minh.</span>
                </div>
            </div>
            <div class="seller-step">
                <i class="fas fa-user-shield"></i>
                <div>
                    <strong>2. Admin duyệt</strong>
                    <span>Admin kiểm tra thông tin trước khi cho phép bán hàng.</span>
                </div>
            </div>
            <div class="seller-step">
                <i class="fas fa-box-open"></i>
                <div>
                    <strong>3. Đăng sản phẩm</strong>
                    <span>Sản phẩm của shop sẽ hiển thị chung trong cửa hàng khách hàng.</span>
                </div>
            </div>
        </div>
    </section>

    <form method="POST" action="{{ route('seller.apply.store') }}" class="seller-card" enctype="multipart/form-data">
        @csrf
        <div class="seller-card-head">
            <div>
                <h2>{{ $shop?->isRejected() ? 'Cập nhật lại hồ sơ shop' : 'Thông tin đăng ký bán hàng' }}</h2>
                <p>Các thông tin bên dưới giúp admin xác minh người bán trước khi cho phép mở shop.</p>
            </div>
            <a href="{{ route('home') }}" class="seller-btn outline"><i class="fas fa-arrow-left"></i> Về trang chủ</a>
        </div>

        @if($shop?->isRejected())
            <div style="margin-bottom: 18px; border: 1px solid #fecaca; border-radius: 18px; background: #fef2f2; color: #991b1b; padding: 14px 16px; font-weight: 800;">
                Hồ sơ trước đó bị từ chối. Ghi chú admin: {{ $shop->admin_note ?: 'Cần bổ sung thông tin.' }}
            </div>
        @endif

        <div class="seller-grid">
            <div class="seller-field">
                <label for="shop_name">Tên shop</label>
                <input id="shop_name" name="shop_name" value="{{ old('shop_name', $shop->shop_name ?? '') }}" required>
                @error('shop_name')<div class="seller-error">{{ $message }}</div>@enderror
            </div>

            <div class="seller-field">
                <label for="brand_name">Tên thương hiệu</label>
                <input id="brand_name" name="brand_name" value="{{ old('brand_name', $shop->brand_name ?? '') }}" required>
                @error('brand_name')<div class="seller-error">{{ $message }}</div>@enderror
            </div>

            <div class="seller-field">
                <label for="primary_category_id">Danh mục kinh doanh chính</label>
                <select id="primary_category_id" name="primary_category_id" required>
                    <option value="">Chọn danh mục</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('primary_category_id', $shop->primary_category_id ?? 0) === (int) $category->id)>
                            {{ $category->display_name }}
                        </option>
                    @endforeach
                </select>
                @error('primary_category_id')<div class="seller-error">{{ $message }}</div>@enderror
            </div>

            <div class="seller-field">
                <label for="phone">Số điện thoại</label>
                <input id="phone" name="phone" value="{{ old('phone', $shop->phone ?? auth()->user()->phone ?? '') }}" required>
                @error('phone')<div class="seller-error">{{ $message }}</div>@enderror
            </div>

            <div class="seller-field">
                <label for="address">Địa chỉ lấy hàng</label>
                <input id="address" name="address" value="{{ old('address', $shop->address ?? auth()->user()->address ?? '') }}" required>
                @error('address')<div class="seller-error">{{ $message }}</div>@enderror
            </div>

            <div class="seller-field">
                <label for="document_type">Loại giấy tờ xác minh</label>
                <select id="document_type" name="document_type" required>
                    <option value="">Chọn loại giấy tờ</option>
                    <option value="business_registration" @selected(old('document_type', $shop->document_type ?? '') === 'business_registration')>Giấy đăng ký kinh doanh</option>
                    <option value="citizen_id" @selected(old('document_type', $shop->document_type ?? '') === 'citizen_id')>Căn cước công dân</option>
                    <option value="household_business" @selected(old('document_type', $shop->document_type ?? '') === 'household_business')>Giấy hộ kinh doanh</option>
                    <option value="brand_authorization" @selected(old('document_type', $shop->document_type ?? '') === 'brand_authorization')>Giấy ủy quyền thương hiệu</option>
                </select>
                @error('document_type')<div class="seller-error">{{ $message }}</div>@enderror
            </div>

            <div class="seller-field">
                <label for="document_number">Mã số giấy tờ</label>
                <input id="document_number" name="document_number" value="{{ old('document_number', $shop->document_number ?? '') }}" required>
                @error('document_number')<div class="seller-error">{{ $message }}</div>@enderror
            </div>

            <div class="seller-field full">
                <label for="description">Mô tả thông tin shop</label>
                <textarea id="description" name="description" rows="5" required>{{ old('description', $shop->description ?? '') }}</textarea>
                @error('description')<div class="seller-error">{{ $message }}</div>@enderror
            </div>

            <div class="seller-field full">
                <label for="document_note">Thông tin giấy tờ cần bổ sung</label>
                <textarea id="document_note" name="document_note" rows="3">{{ old('document_note', $shop->document_note ?? '') }}</textarea>
                <div class="seller-help">Có thể ghi người đại diện, nơi cấp, phạm vi thương hiệu hoặc ghi chú xác minh khác.</div>
                @error('document_note')<div class="seller-error">{{ $message }}</div>@enderror
            </div>

            <div class="seller-field">
                <label for="shop_image">Hình ảnh shop hoặc thương hiệu</label>
                <input id="shop_image" name="shop_image" type="file" accept="image/*" @required(!$shop?->shop_image)>
                <div class="seller-help">Ảnh logo, bảng hiệu, quầy hàng hoặc ảnh nhận diện shop.</div>
                @if($shop?->shop_image_url)
                    <img src="{{ $shop->shop_image_url }}" alt="{{ $shop->shop_name }}" class="seller-preview">
                @endif
                @error('shop_image')<div class="seller-error">{{ $message }}</div>@enderror
            </div>

            <div class="seller-field">
                <label for="document_image">Ảnh giấy tờ xác minh</label>
                <input id="document_image" name="document_image" type="file" accept="image/*" @required(!$shop?->document_image)>
                <div class="seller-help">Ảnh giấy phép, căn cước hoặc giấy ủy quyền liên quan đến shop.</div>
                @if($shop?->document_image_url)
                    <img src="{{ $shop->document_image_url }}" alt="Giấy tờ xác minh" class="seller-preview">
                @endif
                @error('document_image')<div class="seller-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="seller-actions">
            <button type="submit" class="seller-btn primary">
                <i class="fas fa-paper-plane"></i>
                Gửi hồ sơ cho admin duyệt
            </button>
            <a href="{{ route('products.shop') }}" class="seller-btn outline">Xem cửa hàng</a>
        </div>
    </form>
</div>
@endsection
