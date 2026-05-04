@extends('layouts.app')

@section('title', 'Chatbot MienTayShop')

@section('content')
<div style="min-height: 60vh; display: grid; place-items: center; padding: 48px 24px; background: linear-gradient(180deg, #edf5ff 0%, #ffffff 100%);">
    <div style="max-width: 620px; text-align: center; background: white; border: 1px solid #bfdbfe; border-radius: 28px; padding: 38px; box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);">
        <div style="width: 76px; height: 76px; border-radius: 26px; background: #e0f2fe; color: #0369a1; display: inline-flex; align-items: center; justify-content: center; font-size: 30px; margin-bottom: 18px;">
            <i class="fas fa-robot"></i>
        </div>
        <h1 style="font-size: 34px; font-weight: 950; color: #0f172a; margin: 0 0 12px;">Chatbot đã nằm ở góc phải</h1>
        <p style="color: #64748b; line-height: 1.75; margin: 0 0 22px;">
            Trang chat riêng không còn cần dùng nữa. Bạn có thể bấm nút chat nổi ở góc phải trên bất kỳ trang nào để hỏi sản phẩm, giá, giao hàng hoặc đặt hàng.
        </p>
        <a href="{{ route('home') }}" style="display: inline-flex; align-items: center; gap: 10px; padding: 13px 18px; border-radius: 999px; background: #111827; color: white; text-decoration: none; font-weight: 850;">
            <i class="fas fa-house"></i>
            Về trang chủ
        </a>
    </div>
</div>
@endsection
