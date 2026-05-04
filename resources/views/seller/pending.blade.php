@extends('layouts.app')

@section('title', 'Trạng thái kênh bán - MienTayShop')
@push('styles')
    @include('seller.partials.styles')
@endpush

@section('content')
<div class="seller-shell">
    <section class="seller-card">
        <div class="seller-card-head">
            <div>
                <h1>{{ $shop->isRejected() ? 'Hồ sơ cần chỉnh lại' : 'Hồ sơ đang chờ duyệt' }}</h1>
                <p>Shop: <strong>{{ $shop->shop_name }}</strong> · Trạng thái: <strong>{{ $shop->status_label }}</strong></p>
            </div>
            <a href="{{ route('seller.apply') }}" class="seller-btn primary">
                <i class="fas fa-pen"></i>
                {{ $shop->isRejected() ? 'Cập nhật hồ sơ' : 'Xem hồ sơ đã gửi' }}
            </a>
        </div>

        <div style="display: grid; gap: 16px; color: #475569;">
            @if($shop->isRejected())
                <div style="border: 1px solid #fecaca; border-radius: 18px; background: #fef2f2; color: #991b1b; padding: 18px; font-weight: 800;">
                    Ghi chú admin: {{ $shop->admin_note ?: 'Hồ sơ cần bổ sung thông tin trước khi duyệt.' }}
                </div>
            @else
                <div style="border: 1px solid #bfdbfe; border-radius: 18px; background: #eff6ff; color: #1e3a8a; padding: 18px; font-weight: 800;">
                    Admin sẽ kiểm tra hồ sơ. Khi được duyệt, bạn sẽ thấy nút đăng sản phẩm và quản lý kênh bán.
                </div>
            @endif

            <div class="seller-grid">
                <div class="seller-stat">
                    <strong>{{ $shop->shop_name }}</strong>
                    <span>Tên shop</span>
                </div>
                <div class="seller-stat">
                    <strong>{{ $shop->brand_name ?: 'Chưa nhập' }}</strong>
                    <span>Thương hiệu</span>
                </div>
                <div class="seller-stat">
                    <strong>{{ $shop->phone }}</strong>
                    <span>Số điện thoại</span>
                </div>
            </div>

            <div class="seller-actions">
                <a href="{{ route('home') }}" class="seller-btn outline"><i class="fas fa-house"></i> Về trang chủ</a>
                <a href="{{ route('products.shop') }}" class="seller-btn outline"><i class="fas fa-store"></i> Xem cửa hàng</a>
            </div>
        </div>
    </section>
</div>
@endsection
