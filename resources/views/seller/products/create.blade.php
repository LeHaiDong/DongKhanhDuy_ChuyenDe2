@extends('layouts.app')

@section('title', 'Đăng sản phẩm - MienTayShop')
@push('styles')
    @include('seller.partials.styles')
@endpush

@section('content')
<div class="seller-shell">
    <div class="seller-card-head">
        <div>
            <h1>Đăng sản phẩm mới</h1>
            <p>Shop: <strong>{{ $shop->shop_name }}</strong>. Chọn đúng danh mục để sản phẩm hiện đúng nơi trên trang khách hàng.</p>
        </div>
        <a href="{{ route('seller.dashboard') }}" class="seller-btn outline"><i class="fas fa-arrow-left"></i> Kênh bán</a>
    </div>

    @include('seller.products._form', [
        'action' => route('seller.products.store'),
        'method' => 'POST',
        'isEdit' => false,
    ])
</div>
@endsection
