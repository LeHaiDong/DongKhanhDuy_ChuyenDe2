@extends('layouts.admin')

@section('title', 'Chi tiết người bán - MienTayShop Admin')
@section('page-title', 'Chi tiết người bán')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 28px; font-weight: 900; color: #0f172a; margin-bottom: 8px;">{{ $sellerShop->shop_name }}</h1>
        <p style="color: #64748b;">Admin chỉ phê duyệt và giám sát; người bán tự quản lý sản phẩm, đơn hàng và doanh thu của shop.</p>
    </div>
    <a href="{{ route('admin.seller-shops.index') }}" class="nike-btn nike-btn-outline">
        <i class="fas fa-arrow-left"></i>
        Quay lại
    </a>
</div>

<div style="display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(320px, 0.75fr); gap: 24px;">
    <div class="admin-card">
        <div class="card-header">
            <h2 class="card-title">Thông tin kênh bán</h2>
            <span class="status-badge {{ $sellerShop->status_badge_class }}">{{ $sellerShop->status_label }}</span>
        </div>
        <div class="card-body">
            <div style="display: grid; gap: 16px;">
                <div>
                    <div class="form-label">Tên shop</div>
                    <div style="font-size: 20px; font-weight: 900;">{{ $sellerShop->shop_name }}</div>
                </div>
                <div>
                    <div class="form-label">Thương hiệu</div>
                    <div>{{ $sellerShop->brand_name ?: 'Chưa nhập' }}</div>
                </div>
                <div>
                    <div class="form-label">Danh mục kinh doanh chính</div>
                    <div>{{ $sellerShop->category?->display_name ?: 'Chưa chọn danh mục' }}</div>
                </div>
                <div>
                    <div class="form-label">Slug</div>
                    <div>{{ $sellerShop->slug }}</div>
                </div>
                <div>
                    <div class="form-label">Mô tả</div>
                    <div style="white-space: pre-line; color: #475569;">{{ $sellerShop->description ?: 'Chưa có mô tả.' }}</div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px;">
                    <div>
                        <div class="form-label">Loại giấy tờ</div>
                        <div>{{ $sellerShop->document_type_label }}</div>
                    </div>
                    <div>
                        <div class="form-label">Mã số giấy tờ</div>
                        <div>{{ $sellerShop->document_number ?: 'Chưa nhập' }}</div>
                    </div>
                </div>
                <div>
                    <div class="form-label">Thông tin giấy tờ bổ sung</div>
                    <div style="white-space: pre-line; color: #475569;">{{ $sellerShop->document_note ?: 'Không có ghi chú bổ sung.' }}</div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px;">
                    <div>
                        <div class="form-label">Hình ảnh shop</div>
                        @if($sellerShop->shop_image_url)
                            <img src="{{ $sellerShop->shop_image_url }}" alt="{{ $sellerShop->shop_name }}" style="width: 100%; max-height: 220px; object-fit: cover; border-radius: 18px; border: 1px solid #e2e8f0;">
                        @else
                            <div style="color: #64748b;">Chưa có hình ảnh shop.</div>
                        @endif
                    </div>
                    <div>
                        <div class="form-label">Ảnh giấy tờ</div>
                        @if($sellerShop->document_image_url)
                            <img src="{{ $sellerShop->document_image_url }}" alt="Giấy tờ xác minh" style="width: 100%; max-height: 220px; object-fit: cover; border-radius: 18px; border: 1px solid #e2e8f0;">
                        @else
                            <div style="color: #64748b;">Chưa có ảnh giấy tờ.</div>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="form-label">Ghi chú admin</div>
                    <div style="white-space: pre-line; color: #475569;">{{ $sellerShop->admin_note ?: 'Chưa có ghi chú.' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div style="display: grid; gap: 24px;">
        <div class="admin-card">
            <div class="card-header">
                <h2 class="card-title">Người bán</h2>
            </div>
            <div class="card-body" style="display: grid; gap: 12px;">
                <div>
                    <div class="form-label">Họ tên</div>
                    <div style="font-weight: 900;">{{ $sellerShop->user->name }}</div>
                </div>
                <div>
                    <div class="form-label">Email</div>
                    <div>{{ $sellerShop->user->email }}</div>
                </div>
                <div>
                    <div class="form-label">Số điện thoại shop</div>
                    <div>{{ $sellerShop->phone }}</div>
                </div>
                <div>
                    <div class="form-label">Địa chỉ</div>
                    <div>{{ $sellerShop->address ?: 'Chưa nhập' }}</div>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="card-header">
                <h2 class="card-title">Duyệt hồ sơ</h2>
            </div>
            <div class="card-body" style="display: grid; gap: 16px;">
                <form method="POST" action="{{ route('admin.seller-shops.approve', $sellerShop) }}" style="display: grid; gap: 12px;">
                    @csrf
                    <textarea name="admin_note" rows="3" class="form-control">{{ old('admin_note') }}</textarea>
                    <button type="submit" class="nike-btn nike-btn-success" style="justify-content: center;">
                        <i class="fas fa-check"></i>
                        Duyệt cho phép bán hàng
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.seller-shops.reject', $sellerShop) }}" style="display: grid; gap: 12px;">
                    @csrf
                    <textarea name="admin_note" rows="3" class="form-control">{{ old('admin_note') }}</textarea>
                    <button type="submit" class="nike-btn nike-btn-danger" style="justify-content: center;" onclick="return confirm('Từ chối hồ sơ kênh bán này?')">
                        <i class="fas fa-xmark"></i>
                        Từ chối hồ sơ
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-boxes-stacked"></i></div>
        <div class="stat-value">{{ number_format($stats['active_products']) }}/{{ number_format($stats['products']) }}</div>
        <div class="stat-label">Sản phẩm đang bán / tổng sản phẩm</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-receipt"></i></div>
        <div class="stat-value">{{ number_format($stats['orders']) }}</div>
        <div class="stat-label">Đơn hàng có sản phẩm shop</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-value">{{ number_format($stats['customers']) }}</div>
        <div class="stat-label">Khách hàng đã mua</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-coins"></i></div>
        <div class="stat-value">{{ number_format($stats['gross_revenue'], 0, ',', '.') }} VNĐ</div>
        <div class="stat-label">Doanh thu ghi nhận</div>
    </div>
</div>

<div class="admin-card">
    <div class="card-header">
        <h2 class="card-title">Sản phẩm của shop</h2>
        <span class="status-badge bg-info">{{ number_format($sellerShop->products->count()) }} sản phẩm</span>
    </div>
    <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table class="admin-table" style="min-width: 860px;">
            <thead>
                <tr>
                    <th>Ảnh</th>
                    <th>Sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Giá</th>
                    <th>Tồn kho</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sellerShop->products as $product)
                    <tr>
                        <td><img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-image" onerror="this.onerror=null;this.src='{{ $product->fallback_image_url }}';"></td>
                        <td>
                            <div style="font-weight: 900;">{{ $product->name }}</div>
                            <div style="font-size: 12px; color: #64748b;">{{ $product->brand }}</div>
                        </td>
                        <td>{{ $product->category_names ?: $product->display_product_type }}</td>
                        <td><strong style="color: #2563eb;">{{ $product->formatted_price }}</strong></td>
                        <td>{{ $product->stock_quantity }}</td>
                        <td>
                            <span class="status-badge {{ $product->is_active ? 'status-active' : 'status-inactive' }}">
                                {{ $product->is_active ? 'Đang bán' : 'Tạm ẩn' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 42px 16px; color: #64748b;">
                            Shop chưa đăng sản phẩm nào.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
