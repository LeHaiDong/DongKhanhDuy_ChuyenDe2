@extends('layouts.app')

@section('title', 'Cửa hàng đa mặt hàng - MienTayShop')

@section('content')
<section style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 48%, #0f172a 100%); color: white; padding: 86px 0 56px; position: relative; overflow: hidden;">
    <div style="position: absolute; inset: 0; background: radial-gradient(circle at top left, rgba(14,165,233,0.28), transparent 30%), radial-gradient(circle at bottom right, rgba(96,165,250,0.22), transparent 28%);"></div>
    <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px; position: relative; z-index: 1;">
        <div style="max-width: 760px;">
            <span style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 999px; background: rgba(255,255,255,0.08); font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 18px;">
                <i class="fas fa-store"></i>
                Catalog tổng hợp
            </span>
            <h1 style="font-size: clamp(38px, 7vw, 64px); line-height: 1.05; font-weight: 900; margin-bottom: 16px;">
                Cửa hàng
                <span style="color: #7dd3fc;">đa mặt hàng</span>
            </h1>
            <p style="max-width: 640px; font-size: 18px; line-height: 1.7; color: rgba(255,255,255,0.82); margin-bottom: 26px;">
                Từ điện thoại, tai nghe, sữa, snack, khẩu trang đến gia dụng và văn phòng phẩm. Bạn có thể lọc theo nhóm hàng, thương hiệu và tình trạng trong một màn hình duy nhất.
            </p>

            <form action="{{ route('products.search') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 12px; max-width: 760px;">
                <div style="flex: 1; min-width: 260px; position: relative;">
                    <input id="search-input"
                           type="text"
                           name="q"
                           value="{{ request('q') }}"
                           aria-label="Từ khóa tìm kiếm"
                           style="width: 100%; padding: 16px 18px 16px 50px; border-radius: 18px; border: 1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.1); color: white; outline: none; backdrop-filter: blur(14px);">
                    <i class="fas fa-search" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.6);"></i>
                    <div id="search-suggestions" style="display: none; position: absolute; top: calc(100% + 8px); left: 0; right: 0; background: white; color: #111827; border-radius: 18px; box-shadow: 0 24px 48px rgba(15,23,42,0.18); z-index: 20; overflow: hidden;"></div>
                </div>
                <button type="submit" style="padding: 16px 22px; border: none; border-radius: 18px; background: linear-gradient(135deg, #2563eb 0%, #0891b2 100%); color: white; font-weight: 800; cursor: pointer;">
                    Tìm sản phẩm
                </button>
            </form>
        </div>
    </div>
</section>

<section style="background: #f8fafc; padding: 28px 0 18px; border-bottom: 1px solid #e2e8f0;">
    <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">
        <form id="filter-form" method="GET" action="{{ route('products.shop') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px;">
            @if($selectedCategory ?? null)
                <input type="hidden" name="category" value="{{ $selectedCategory->id }}">
            @else
                <input type="hidden" name="q" value="{{ request('q') }}">
            @endif

            <div style="background: white; border-radius: 18px; padding: 16px; border: 1px solid #e2e8f0;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px;">Loại sản phẩm</label>
                <select name="product_type" style="width: 100%; border: none; background: transparent; color: #0f172a; font-weight: 600; outline: none;">
                    <option value="all">Tất cả</option>
                    @foreach($productTypes as $productType)
                        @php($displayProductType = \App\Models\CameraLens::make(['product_type' => $productType])->display_product_type)
                        <option value="{{ $productType }}" {{ request('product_type') == $productType ? 'selected' : '' }}>{{ $displayProductType }}</option>
                    @endforeach
                </select>
            </div>

            <div style="background: white; border-radius: 18px; padding: 16px; border: 1px solid #e2e8f0;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px;">Thương hiệu</label>
                <select name="brand" style="width: 100%; border: none; background: transparent; color: #0f172a; font-weight: 600; outline: none;">
                    <option value="all">Tất cả</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>{{ $brand }}</option>
                    @endforeach
                </select>
            </div>

            <div style="background: white; border-radius: 18px; padding: 16px; border: 1px solid #e2e8f0;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px;">Hệ / kết nối</label>
                <select name="mount_type" style="width: 100%; border: none; background: transparent; color: #0f172a; font-weight: 600; outline: none;">
                    <option value="all">Tất cả</option>
                    @foreach($mountTypes as $mountType)
                        <option value="{{ $mountType }}" {{ request('mount_type') == $mountType ? 'selected' : '' }}>{{ $mountType }}</option>
                    @endforeach
                </select>
            </div>

            <div style="background: white; border-radius: 18px; padding: 16px; border: 1px solid #e2e8f0;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px;">Tình trạng</label>
                <select name="condition" style="width: 100%; border: none; background: transparent; color: #0f172a; font-weight: 600; outline: none;">
                    <option value="all">Tất cả</option>
                    @foreach($conditions as $condition)
                        <option value="{{ $condition }}" {{ request('condition') == $condition ? 'selected' : '' }}>
                            {{ $condition === 'used' ? 'Đã sử dụng' : ($condition === 'refurbished' ? 'Tân trang' : 'Mới') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="background: white; border-radius: 18px; padding: 16px; border: 1px solid #e2e8f0;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px;">Sắp xếp</label>
                <select name="sort" style="width: 100%; border: none; background: transparent; color: #0f172a; font-weight: 600; outline: none;">
                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Tên A-Z</option>
                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Giá thấp</option>
                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Giá cao</option>
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Mới nhất</option>
                </select>
            </div>

            <div style="display: flex; align-items: center; gap: 12px; background: white; border-radius: 18px; padding: 16px; border: 1px solid #e2e8f0;">
                <label style="display: inline-flex; align-items: center; gap: 10px; font-weight: 600; color: #0f172a;">
                    <input type="checkbox" name="in_stock" value="1" {{ request('in_stock') ? 'checked' : '' }}>
                    Còn hàng
                </label>
                <div style="margin-left: auto; display: flex; gap: 10px;">
                    <button type="submit" style="padding: 10px 14px; border: none; border-radius: 12px; background: #111827; color: white; font-weight: 700; cursor: pointer;">
                        Áp dụng
                    </button>
                    <a href="{{ route('products.shop') }}" style="display: inline-flex; align-items: center; justify-content: center; padding: 10px 14px; border-radius: 12px; background: #fff7ed; color: #c2410c; text-decoration: none; font-weight: 700;">
                        Xóa lọc
                    </a>
                </div>
            </div>
        </form>
    </div>
</section>

<section style="background: #ffffff; padding: 22px 0 8px;">
    <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px; display: flex; justify-content: space-between; align-items: center; gap: 18px; flex-wrap: wrap;">
        <div>
            <div style="font-size: 14px; color: #64748b; margin-bottom: 6px;">
                Khoảng giá tham khảo: {{ number_format($minPrice, 0, ',', '.') }} - {{ number_format($maxPrice, 0, ',', '.') }} triệu
            </div>
            <div style="font-size: 18px; font-weight: 800; color: #0f172a;">
                <span id="results-count">{{ $lenses->total() }}</span> sản phẩm
                <span id="results-scope">
                    @if($selectedCategory ?? null)
                        trong "{{ $selectedCategory->display_name }}"
                    @elseif(request('q'))
                        cho "{{ request('q') }}"
                    @endif
                </span>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 14px; color: #64748b;">Hiển thị:</span>
            <select name="per_page" onchange="changePerPage(this.value)" style="padding: 10px 12px; border-radius: 12px; border: 1px solid #cbd5e1; background: white; font-weight: 600;">
                <option value="12" {{ request('per_page', 12) == 12 ? 'selected' : '' }}>12</option>
                <option value="24" {{ request('per_page') == 24 ? 'selected' : '' }}>24</option>
                <option value="48" {{ request('per_page') == 48 ? 'selected' : '' }}>48</option>
            </select>
        </div>
    </div>
</section>

<section style="background: #ffffff; padding: 28px 0 48px; min-height: 640px;">
    <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">
        <div id="products-container">
            @include('products.partials.product-grid', ['lenses' => $lenses])
        </div>

        <div id="pagination-container" style="margin-top: 42px;">
            {{ $lenses->links() }}
        </div>
    </div>
</section>

<div id="loading-overlay" style="display: none; position: fixed; inset: 0; background: rgba(255,255,255,0.86); z-index: 9999; align-items: center; justify-content: center;">
    <div style="text-align: center;">
        <i class="fas fa-spinner fa-spin" style="font-size: 40px; color: #1d4ed8; margin-bottom: 14px;"></i>
        <p style="font-size: 16px; font-weight: 700; color: #0f172a;">Đang tải sản phẩm...</p>
    </div>
</div>

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

.pagination .disabled span {
    color: #94a3b8;
}

.search-suggestion {
    padding: 14px 18px;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
}

.search-suggestion:hover {
    background: #f8fafc;
}

.search-suggestion:last-child {
    border-bottom: none;
}

@media (max-width: 768px) {
    .pagination {
        flex-wrap: wrap;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filter-form');
    filterForm.querySelectorAll('select, input[type="checkbox"]').forEach(input => {
        input.addEventListener('change', submitFilters);
    });

    const searchInput = document.getElementById('search-input');
    const suggestionsContainer = document.getElementById('search-suggestions');
    let searchTimeout;

    if (searchInput && suggestionsContainer) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(searchTimeout);

            if (query.length < 2) {
                suggestionsContainer.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('products.search-suggestions') }}?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(suggestions => {
                    if (!Array.isArray(suggestions) || suggestions.length === 0) {
                        suggestionsContainer.style.display = 'none';
                        return;
                    }

                    suggestionsContainer.innerHTML = suggestions.map(suggestion => `
                        <div class="search-suggestion" onclick="selectSuggestion('${suggestion.url}')">
                            <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px;">${suggestion.text}</div>
                            <div style="font-size: 12px; color: #64748b;">${suggestion.brand} • ${suggestion.product_type}</div>
                        </div>
                    `).join('');

                    suggestionsContainer.style.display = 'block';
                })
                .catch(() => {
                    suggestionsContainer.style.display = 'none';
                });
            }, 250);
        });

        document.addEventListener('click', function(event) {
            if (!event.target.closest('#search-input') && !event.target.closest('#search-suggestions')) {
                suggestionsContainer.style.display = 'none';
            }
        });
    }

    function submitFilters() {
        const params = new URLSearchParams(new FormData(filterForm));
        showLoading();

        fetch(`{{ route('products.shop') }}?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.html) {
                document.getElementById('products-container').innerHTML = data.html;
            }

            if (data.pagination) {
                document.getElementById('pagination-container').innerHTML = data.pagination;
            }

            if (typeof data.total !== 'undefined') {
                document.getElementById('results-count').textContent = data.total;
            }

            if (data.scope) {
                const scopeElement = document.getElementById('results-scope');

                if (scopeElement) {
                    scopeElement.textContent = ` trong "${data.scope}"`;
                }
            }

            window.history.pushState(null, '', `${window.location.pathname}?${params.toString()}`);
        })
        .catch(() => {
            window.location.href = `{{ route('products.shop') }}?${params.toString()}`;
        })
        .finally(hideLoading);
    }

    window.changePerPage = function(value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'per_page';
        input.value = value;
        filterForm.appendChild(input);
        filterForm.submit();
    };
});

function selectSuggestion(url) {
    window.location.href = url;
}

function showLoading() {
    document.getElementById('loading-overlay').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loading-overlay').style.display = 'none';
}

function updateCartCount(count) {
    document.querySelectorAll('.cart-count').forEach(element => {
        element.textContent = count;
        if (count > 0) {
            element.classList.remove('hidden');
        } else {
            element.classList.add('hidden');
        }
    });
}

document.addEventListener('click', function(event) {
    const button = event.target.closest('.add-to-cart-btn');
    if (!button) return;

    event.stopPropagation();

    const lensId = button.dataset.lensId;
    const originalHtml = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Đang thêm...';
    button.disabled = true;

    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            camera_lens_id: lensId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cart_count);
            button.innerHTML = '<i class="fas fa-check" style="margin-right: 8px;"></i>Đã thêm';
            button.style.background = '#16a34a';
        } else {
            button.innerHTML = originalHtml;
        }
    })
    .catch(() => {
        button.innerHTML = originalHtml;
    })
    .finally(() => {
        setTimeout(() => {
            button.disabled = false;
            button.innerHTML = originalHtml;
            button.style.background = 'linear-gradient(135deg, #111827 0%, #1f2937 100%)';
        }, 1200);
    });
});
</script>
@endsection
