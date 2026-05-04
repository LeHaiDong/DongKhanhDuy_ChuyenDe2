@extends('layouts.app')

@section('title', 'Sản phẩm của shop - MienTayShop')
@push('styles')
    @include('seller.partials.styles')
@endpush

@section('content')
<div class="seller-shell">
    <section class="seller-card">
        <div class="seller-card-head">
            <div>
                <h1>Sản phẩm của {{ $shop->shop_name }}</h1>
                <p>Quản lý những mặt hàng do shop của bạn đăng bán.</p>
            </div>
            <div class="seller-actions" style="margin-top: 0;">
                <a href="{{ route('seller.products.create') }}" class="seller-btn primary"><i class="fas fa-plus"></i> Đăng sản phẩm</a>
                <a href="{{ route('seller.dashboard') }}" class="seller-btn outline"><i class="fas fa-store"></i> Kênh bán</a>
            </div>
        </div>

        @if($products->isNotEmpty())
            <div class="seller-table-wrap">
                <table class="seller-table">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Tồn kho</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td><img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="seller-product-thumb" onerror="this.onerror=null;this.src='{{ $product->fallback_image_url }}';"></td>
                                <td>
                                    <strong>{{ $product->name }}</strong>
                                    <div style="color: #64748b; font-size: 13px;">{{ $product->brand }}</div>
                                </td>
                                <td>{{ $product->category_names ?: $product->display_product_type }}</td>
                                <td><strong style="color: #2563eb;">{{ $product->formatted_price }}</strong></td>
                                <td>{{ $product->stock_quantity }}</td>
                                <td><span class="seller-status {{ $product->is_active ? '' : 'muted' }}">{{ $product->is_active ? 'Đang bán' : 'Tạm ẩn' }}</span></td>
                                <td>
                                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                        <a href="{{ route('products.show', $product) }}" class="seller-btn outline" style="min-height: 36px; padding: 8px 12px;" target="_blank">Xem</a>
                                        <a href="{{ route('seller.products.edit', $product) }}" class="seller-btn outline" style="min-height: 36px; padding: 8px 12px;">Sửa</a>
                                        <form method="POST" action="{{ route('seller.products.destroy', $product) }}" onsubmit="return confirm('Xóa sản phẩm {{ addslashes($product->name) }}?')" style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="seller-btn" style="min-height: 36px; padding: 8px 12px; background: #dc2626;">Xóa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div style="margin-top: 22px;">
                    {{ $products->links('pagination::bootstrap-4') }}
                </div>
            @endif
        @else
            <div style="border: 1px dashed #cbd5e1; border-radius: 20px; padding: 42px; text-align: center;">
                <i class="fas fa-box-open" style="font-size: 44px; color: #cbd5e1;"></i>
                <h2 style="margin: 14px 0 8px;">Shop chưa có sản phẩm</h2>
                <p style="color: #64748b;">Hãy đăng sản phẩm đầu tiên để khách hàng có thể mua từ shop của bạn.</p>
                <a href="{{ route('seller.products.create') }}" class="seller-btn primary" style="margin-top: 14px;"><i class="fas fa-plus"></i> Đăng sản phẩm</a>
            </div>
        @endif
    </section>
</div>
@endsection
