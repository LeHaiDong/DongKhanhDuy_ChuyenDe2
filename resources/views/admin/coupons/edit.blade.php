@extends('layouts.admin')

@section('title', 'Chỉnh sửa voucher - MienTayShop Admin')
@section('page-title', 'Chỉnh sửa voucher')

@section('content')
<div style="margin-bottom: 24px;">
    <h1 style="font-size: 28px; font-weight: 900; color: #0f172a; margin-bottom: 8px;">Chỉnh sửa voucher {{ $coupon->code }}</h1>
    <p style="color: #64748b;">Cập nhật điều kiện, thời gian và trạng thái sử dụng của voucher.</p>
</div>

<form method="POST" action="{{ route('admin.coupons.update', $coupon) }}">
    @csrf
    @method('PUT')
    @include('admin.coupons._form')
</form>
@endsection
