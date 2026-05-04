@extends('layouts.admin')

@section('title', 'Tạo voucher - MienTayShop Admin')
@section('page-title', 'Tạo voucher')

@section('content')
<div style="margin-bottom: 24px;">
    <h1 style="font-size: 28px; font-weight: 900; color: #0f172a; margin-bottom: 8px;">Tạo chương trình voucher</h1>
    <p style="color: #64748b;">Thiết lập mã giảm giá để khách dùng trực tiếp ở trang thanh toán.</p>
</div>

<form method="POST" action="{{ route('admin.coupons.store') }}">
    @csrf
    @include('admin.coupons._form')
</form>
@endsection
