@extends('layouts.app')

@section('title', 'Giỏ hàng - MienTayShop')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Giỏ hàng của bạn</h1>
            <p class="text-gray-600">Xem lại sản phẩm trước khi thanh toán</p>
        </div>

        @if($cartItems->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm p-8 text-center">
                <div class="w-32 h-32 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5-6m0 0h15.5m0 0L19 13"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Giỏ hàng đang trống</h2>
                <p class="text-gray-600 mb-6">Bạn chưa thêm sản phẩm nào vào giỏ hàng.</p>
                <a href="{{ route('products.shop') }}" class="inline-block bg-black text-white px-8 py-3 rounded-full font-medium hover:bg-gray-800 transition-colors">
                    Tiếp tục mua sắm
                </a>
            </div>
        @else
            <div class="lg:grid lg:grid-cols-12 lg:gap-8">
                <div class="lg:col-span-8">
                    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Sản phẩm trong giỏ ({{ $cartCount }} sản phẩm)</h2>
                        </div>

                        <div class="divide-y divide-gray-200" id="cart-items">
                            @foreach($cartItems as $item)
                                <div class="p-6 cart-item" data-cart-id="{{ $item->id }}">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <img src="{{ $item->cameraLens->image_url }}"
                                                 alt="{{ $item->cameraLens->name }}"
                                                 loading="lazy"
                                                 decoding="async"
                                                 class="w-20 h-20 object-cover rounded-lg bg-gray-100">
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-lg font-medium text-gray-900 truncate">{{ $item->cameraLens->name }}</h3>
                                            <p class="text-sm text-gray-500">{{ $item->cameraLens->brand }} • {{ $item->cameraLens->display_product_type }}</p>
                                            <p class="text-lg font-semibold text-gray-900 mt-1">{{ $item->formatted_unit_price }}</p>
                                        </div>

                                        <div class="flex items-center space-x-3">
                                            <div class="flex items-center border border-gray-300 rounded-lg">
                                                <button type="button"
                                                        class="quantity-btn px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-50 transition-colors"
                                                        data-action="decrease"
                                                        data-cart-id="{{ $item->id }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                    </svg>
                                                </button>

                                                <input type="number"
                                                       value="{{ $item->quantity }}"
                                                       min="1"
                                                       max="10"
                                                       class="quantity-input w-12 px-2 py-2 text-center border-0 focus:ring-0 focus:outline-none"
                                                       data-cart-id="{{ $item->id }}">

                                                <button type="button"
                                                        class="quantity-btn px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-50 transition-colors"
                                                        data-action="increase"
                                                        data-cart-id="{{ $item->id }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <p class="text-lg font-semibold text-gray-900 item-total">{{ $item->formatted_total_price }}</p>
                                            <button type="button"
                                                    class="remove-item text-sm text-red-600 hover:text-red-800 mt-2 transition-colors"
                                                    data-cart-id="{{ $item->id }}">
                                                Xóa
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="p-6 bg-gray-50 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <a href="{{ route('products.shop') }}" class="text-black font-medium hover:text-gray-700 transition-colors">
                                    ← Tiếp tục mua sắm
                                </a>
                                <button type="button" id="clear-cart" class="text-red-600 font-medium hover:text-red-800 transition-colors">
                                    Xóa tất cả
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 mt-8 lg:mt-0">
                    <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Tóm tắt đơn hàng</h3>

                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tạm tính</span>
                                <span class="cart-subtotal">{{ number_format($total, 0, ',', '.') }} VNĐ</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Phí vận chuyển</span>
                                <span class="text-green-600">Miễn phí</span>
                            </div>
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between">
                                    <span class="text-lg font-semibold text-gray-900">Tổng cộng</span>
                                    <span class="text-lg font-semibold text-gray-900 cart-total">{{ number_format($total, 0, ',', '.') }} VNĐ</span>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('cart.checkout') }}" class="w-full bg-black text-white py-3 px-6 rounded-full font-medium hover:bg-gray-800 transition-colors text-center block">
                            Tiến hành thanh toán
                        </a>

                        <div class="mt-6 space-y-3">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Miễn phí vận chuyển toàn quốc
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Hỗ trợ xác nhận đơn thật
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Có thể mua ngay hoặc thêm giỏ
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.quantity-btn')) {
            const btn = e.target.closest('.quantity-btn');
            const action = btn.dataset.action;
            const cartId = btn.dataset.cartId;
            const input = document.querySelector(`input[data-cart-id="${cartId}"]`);
            let quantity = parseInt(input.value, 10);

            if (action === 'increase' && quantity < 10) {
                quantity++;
            } else if (action === 'decrease' && quantity > 1) {
                quantity--;
            }

            input.value = quantity;
            updateCartItem(cartId, quantity);
        }
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('quantity-input')) {
            const cartId = e.target.dataset.cartId;
            const quantity = parseInt(e.target.value, 10) || 1;

            if (quantity < 1) {
                e.target.value = 1;
                return;
            }

            if (quantity > 10) {
                e.target.value = 10;
                return;
            }

            updateCartItem(cartId, quantity);
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            removeCartItem(e.target.dataset.cartId);
        }
    });

    document.getElementById('clear-cart')?.addEventListener('click', function() {
        if (confirm('Bạn có chắc muốn xóa tất cả sản phẩm khỏi giỏ hàng không?')) {
            clearCart();
        }
    });

    function updateCartItem(cartId, quantity) {
        fetch(`/cart/update/${cartId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ quantity: quantity })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                showMessage(data.message, 'error');
                return;
            }

            const itemElement = document.querySelector(`[data-cart-id="${cartId}"]`);
            itemElement.querySelector('.item-total').textContent = data.item_total + ' VNĐ';
            document.querySelector('.cart-subtotal').textContent = data.cart_total + ' VNĐ';
            document.querySelector('.cart-total').textContent = data.cart_total + ' VNĐ';
            updateCartCount(data.cart_count);
            showMessage(data.message, 'success');
        })
        .catch(() => {
            showMessage('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
        });
    }

    function removeCartItem(cartId) {
        fetch(`/cart/remove/${cartId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                showMessage(data.message, 'error');
                return;
            }

            document.querySelector(`[data-cart-id="${cartId}"]`)?.remove();
            document.querySelector('.cart-subtotal').textContent = data.cart_total + ' VNĐ';
            document.querySelector('.cart-total').textContent = data.cart_total + ' VNĐ';
            updateCartCount(data.cart_count);

            if (data.cart_count === 0) {
                location.reload();
            }

            showMessage(data.message, 'success');
        })
        .catch(() => {
            showMessage('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
        });
    }

    function clearCart() {
        fetch('/cart/clear', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(() => {
            showMessage('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
        });
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

    function showMessage(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white`;
        alertDiv.textContent = message;

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }
});
</script>
@endsection
