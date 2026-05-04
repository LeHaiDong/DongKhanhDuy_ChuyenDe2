@extends('layouts.app')

@section('title', 'Tìm kiếm: ' . $keyword . ' - MienTayShop')

@section('content')
<section style="background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%); padding: 56px 0 40px;">
    <div style="max-width: 1240px; margin: 0 auto; padding: 0 24px;">
        <div style="max-width: 760px; margin: 0 auto; text-align: center;">
            <h1 style="font-size: clamp(32px, 5vw, 48px); font-weight: 900; color: #0f172a; margin-bottom: 14px;">Kết quả tìm kiếm</h1>
            <p style="font-size: 17px; color: #64748b; line-height: 1.7; margin-bottom: 26px;">
                @if($totalResults > 0)
                    Tìm thấy <strong>{{ $totalResults }}</strong> sản phẩm phù hợp với "<strong style="color: #1d4ed8;">{{ $keyword }}</strong>".
                @else
                    Chưa có kết quả phù hợp với "<strong style="color: #dc2626;">{{ $keyword }}</strong>".
                @endif
            </p>

            <form action="{{ route('products.search') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; justify-content: center;">
                <input type="text" name="q" value="{{ $keyword }}" aria-label="Từ khóa tìm kiếm" style="flex: 1; min-width: 260px; max-width: 560px; padding: 16px 18px; border-radius: 18px; border: 1px solid #cbd5e1; background: white; color: #0f172a; outline: none;">
                <button type="submit" style="padding: 16px 20px; border: none; border-radius: 18px; background: #111827; color: white; font-weight: 800; cursor: pointer;">
                    <i class="fas fa-search" style="margin-right: 8px;"></i>
                    Tìm lại
                </button>
            </form>
        </div>
    </div>
</section>

@if($lenses->isNotEmpty())
    <section style="background: #fff; padding: 32px 0 52px;">
        <div style="max-width: 1240px; margin: 0 auto; padding: 0 24px;">
            <div id="search-results">
                @include('products.partials.product-grid', ['lenses' => $lenses])
            </div>

            <div style="margin-top: 42px;">
                {{ $lenses->appends(['q' => $keyword])->links() }}
            </div>
        </div>
    </section>
@else
    <section style="background: #fff; padding: 52px 0 72px;">
        <div style="max-width: 900px; margin: 0 auto; padding: 0 24px;">
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 28px; padding: 42px 28px; box-shadow: 0 24px 48px rgba(15, 23, 42, 0.06);">
                <div style="text-align: center; margin-bottom: 28px;">
                    <div style="width: 88px; height: 88px; margin: 0 auto 16px; border-radius: 999px; background: #e0f2fe; color: #0369a1; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-search" style="font-size: 32px;"></i>
                    </div>
                    <h2 style="font-size: 30px; font-weight: 900; color: #0f172a; margin-bottom: 10px;">Không tìm thấy sản phẩm phù hợp</h2>
                    <p style="font-size: 16px; color: #64748b; line-height: 1.8; max-width: 620px; margin: 0 auto;">
                        Bạn có thể tìm theo tên sản phẩm, thương hiệu, danh mục hoặc đặc điểm chính của món hàng.
                    </p>
                </div>

                <div style="display: grid; gap: 18px; margin-bottom: 24px;">
                    <div style="padding: 18px; border-radius: 20px; background: #f8fafc;">
                        <div style="font-size: 13px; font-weight: 800; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px;">Gợi ý tìm kiếm</div>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                            @foreach(['iPhone 15', 'Tai nghe Sony', 'Sữa cho bé', 'Khẩu trang 3M', 'Áo polo', 'Bánh kẹo', 'Gia dụng', 'Sách'] as $hint)
                                <a href="{{ route('products.search', ['q' => $hint]) }}" style="padding: 8px 12px; border-radius: 999px; background: white; border: 1px solid #e2e8f0; color: #0f172a; text-decoration: none; font-weight: 700;">
                                    {{ $hint }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    @if($suggestions->isNotEmpty())
                        <div style="padding: 18px; border-radius: 20px; background: #eff6ff; border: 1px solid #bfdbfe;">
                            <div style="font-size: 13px; font-weight: 800; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px;">Có thể bạn đang tìm</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                                @foreach($suggestions as $suggestion)
                                    <a href="{{ route('products.search', ['q' => $suggestion->name]) }}" style="padding: 8px 12px; border-radius: 999px; background: white; color: #1e40af; text-decoration: none; font-weight: 700;">
                                        {{ $suggestion->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
                    <a href="{{ route('products.shop') }}" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 18px; border-radius: 999px; background: #111827; color: white; text-decoration: none; font-weight: 800;">
                        <i class="fas fa-store"></i>
                        Xem tất cả sản phẩm
                    </a>
                    <button type="button" onclick="document.getElementById('chatToggle')?.click()" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 18px; border-radius: 999px; border: 0; background: #e0f2fe; color: #0369a1; text-decoration: none; font-weight: 800; cursor: pointer;">
                        <i class="fas fa-comments"></i>
                        Hỏi chatbot
                    </button>
                </div>
            </div>
        </div>
    </section>
@endif

<style>
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.pagination li {
    margin: 0;
}

.pagination a, .pagination span {
    display: block;
    padding: 12px 16px;
    color: #334155;
    text-decoration: none;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    font-weight: 700;
    background: white;
}

.pagination .active span {
    background: #111827;
    color: white;
    border-color: #111827;
}
</style>
@endsection
