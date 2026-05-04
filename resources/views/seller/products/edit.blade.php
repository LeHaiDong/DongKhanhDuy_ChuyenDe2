@extends('layouts.app')

@section('title', 'Sửa sản phẩm - MienTayShop')
@push('styles')
    @include('seller.partials.styles')
@endpush

@section('content')
<div class="seller-shell">
    <div class="seller-card-head">
        <div>
            <h1>Sửa sản phẩm</h1>
            <p>Cập nhật thông tin của <strong>{{ $product->name }}</strong>.</p>
        </div>
        <a href="{{ route('seller.products.index') }}" class="seller-btn outline"><i class="fas fa-arrow-left"></i> Danh sách sản phẩm</a>
    </div>

    @include('seller.products._form', [
        'action' => route('seller.products.update', $product),
        'method' => 'PUT',
        'isEdit' => true,
    ])
</div>
@endsection
