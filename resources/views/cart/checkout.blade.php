@extends('layouts.app')

@section('title', 'Thanh toán - MienTayShop')

@section('content')
@php
    $bankAccounts = [
        [
            'code' => 'VCB',
            'bank' => 'Vietcombank',
            'account_number' => '1234567890',
            'account_name' => 'MIENTAYSHOP',
            'branch' => 'Chi nhánh Cần Thơ',
            'status' => 'Đã liên kết',
            'last4' => '7890',
        ],
        [
            'code' => 'MB',
            'bank' => 'MB Bank',
            'account_number' => '9090123456',
            'account_name' => 'MIENTAYSHOP',
            'branch' => 'Chi nhánh TP. Hồ Chí Minh',
            'status' => 'Khả dụng',
            'last4' => '3456',
        ],
        [
            'code' => 'ACB',
            'bank' => 'ACB',
            'account_number' => '1688990011',
            'account_name' => 'MIENTAYSHOP',
            'branch' => 'Chi nhánh Bình Dương',
            'status' => 'Khả dụng',
            'last4' => '0011',
        ],
    ];

    $bankAccount = [
        'bank' => 'Vietcombank',
        'account_number' => '1234567890',
        'account_name' => 'MIENTAYSHOP',
        'branch' => 'Chi nhánh Cần Thơ',
    ];
    $bankTransferContent = 'MTS-' . auth()->id() . '-' . now()->format('dmHi');
    $bankQrUrl = 'https://img.vietqr.io/image/VCB-' . $bankAccount['account_number'] . '-compact2.png?amount=' .
        (int) $finalTotal . '&addInfo=' . urlencode($bankTransferContent) . '&accountName=' . urlencode($bankAccount['account_name']);
@endphp

<div class="min-h-screen py-8" style="background: linear-gradient(180deg, #edf5ff 0%, #f8fbff 42%, #ffffff 100%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Thanh toán</h1>
            <p class="text-gray-600">Kiểm tra thông tin nhận hàng, voucher và phương thức thanh toán trước khi đặt hàng.</p>
        </div>

        <div class="flex items-center justify-center mb-8">
            <div class="flex items-center">
                <div class="flex items-center text-blue-600">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-medium">
                        <i class="fas fa-check"></i>
                    </div>
                    <span class="ml-2 font-medium">Giỏ hàng</span>
                </div>
                <div class="w-12 h-0.5 bg-blue-600 mx-4"></div>
                <div class="flex items-center text-blue-600">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-medium">
                        2
                    </div>
                    <span class="ml-2 font-medium">Thanh toán</span>
                </div>
                <div class="w-12 h-0.5 bg-gray-300 mx-4"></div>
                <div class="flex items-center text-gray-500">
                    <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">
                        3
                    </div>
                    <span class="ml-2">Hoàn tất</span>
                </div>
            </div>
        </div>

        <form action="{{ route('cart.order') }}" method="POST" id="checkout-form">
            @csrf
            <input type="hidden" name="payment_reference" value="{{ $bankTransferContent }}">
            <div class="lg:grid lg:grid-cols-12 lg:gap-8">
                <div class="lg:col-span-7">
                    <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Thông tin giao hàng</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Họ và tên <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="customer_name"
                                    name="customer_name"
                                    value="{{ old('customer_name', auth()->user()->name ?? '') }}"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                >
                                @error('customer_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="email"
                                    id="customer_email"
                                    name="customer_email"
                                    value="{{ old('customer_email', auth()->user()->email ?? '') }}"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                >
                                @error('customer_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Số điện thoại <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="tel"
                                    id="customer_phone"
                                    name="customer_phone"
                                    value="{{ old('customer_phone', auth()->user()->phone ?? '') }}"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                >
                                @error('customer_phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="customer_address" class="block text-sm font-medium text-gray-700 mb-2">
                                    Địa chỉ giao hàng <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    id="customer_address"
                                    name="customer_address"
                                    required
                                    rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                >{{ old('customer_address', auth()->user()->address ?? '') }}</textarea>
                                @error('customer_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Phương thức thanh toán</h2>

                        <input type="hidden" name="payment_channel" id="payment_channel" value="linked_vcb">
                        <input type="hidden" name="payment_bank_code" id="payment_bank_code" value="VCB">

                        <div class="space-y-4">
                            <label class="payment-card flex items-start p-4 border border-gray-200 rounded-2xl cursor-pointer hover:border-blue-500 transition-colors">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="cod"
                                    checked
                                    class="mt-1 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                >
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center">
                                        <span class="font-medium text-gray-900">Thanh toán khi nhận hàng (COD)</span>
                                        <i class="fas fa-truck ml-2 text-green-600"></i>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Thanh toán bằng tiền mặt khi nhận hàng. Phù hợp nếu bạn muốn kiểm tra hàng trước.</p>
                                </div>
                            </label>

                            <label class="payment-card flex items-start p-4 border border-gray-200 rounded-2xl cursor-pointer hover:border-blue-500 transition-colors">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="bank_transfer"
                                    class="mt-1 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                >
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center">
                                        <span class="font-medium text-gray-900">Thanh toán qua ngân hàng liên kết</span>
                                        <i class="fas fa-university ml-2 text-blue-600"></i>
                                        <span class="ml-2 inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 border border-blue-100">
                                            NAPAS 247
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Chọn ngân hàng/ví liên kết, quét QR hoặc sao chép thông tin chuyển khoản để người bán đối chiếu nhanh.</p>

                                    <div class="mt-4 rounded-3xl border border-blue-200 bg-gradient-to-br from-blue-50 via-white to-cyan-50 p-4 text-sm text-slate-700 bank-info hidden">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-4">
                                            <div>
                                                <strong class="block text-slate-900 text-base">Liên kết ngân hàng & thanh toán nhanh</strong>
                                                <span class="text-slate-600">MienTayShop mô phỏng cổng thanh toán như sàn thương mại: chọn nguồn tiền, tạo mã giao dịch rồi người bán xác nhận trong kênh bán.</span>
                                            </div>
                                            <span class="inline-flex w-max items-center rounded-full bg-white px-3 py-1 text-xs font-bold text-blue-700 border border-blue-200">
                                                <i class="fas fa-shield-alt mr-1"></i> Bảo mật mô phỏng
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 mb-4">
                                            <button type="button" class="payment-channel-option rounded-2xl border-2 border-blue-600 bg-white p-3 text-left shadow-sm" data-channel="linked_vcb" data-bank="VCB">
                                                <span class="block text-xs font-bold uppercase tracking-wide text-blue-700">Đã liên kết</span>
                                                <strong class="block text-slate-900">Vietcombank •••• {{ $bankAccounts[0]['last4'] }}</strong>
                                                <span class="text-xs text-slate-500">Trừ tiền sau khi xác nhận</span>
                                            </button>
                                            <button type="button" class="payment-channel-option rounded-2xl border border-slate-200 bg-white p-3 text-left hover:border-blue-400" data-channel="napas_atm" data-bank="NAPAS">
                                                <span class="block text-xs font-bold uppercase tracking-wide text-orange-600">Thẻ ATM</span>
                                                <strong class="block text-slate-900">ATM nội địa / NAPAS</strong>
                                                <span class="text-xs text-slate-500">Liên kết ngân hàng mới</span>
                                            </button>
                                            <button type="button" class="payment-channel-option rounded-2xl border border-slate-200 bg-white p-3 text-left hover:border-blue-400" data-channel="qr_transfer" data-bank="VCB">
                                                <span class="block text-xs font-bold uppercase tracking-wide text-emerald-600">Quét mã</span>
                                                <strong class="block text-slate-900">QR chuyển khoản</strong>
                                                <span class="text-xs text-slate-500">Mở app ngân hàng để quét</span>
                                            </button>
                                            <button type="button" class="payment-channel-option rounded-2xl border border-slate-200 bg-white p-3 text-left hover:border-blue-400" data-channel="momo_wallet" data-bank="MOMO">
                                                <span class="block text-xs font-bold uppercase tracking-wide text-pink-600">Ví điện tử</span>
                                                <strong class="block text-slate-900">MoMo / Ví liên kết</strong>
                                                <span class="text-xs text-slate-500">Thanh toán qua ví mô phỏng</span>
                                            </button>
                                        </div>

                                        <div class="grid gap-4 lg:grid-cols-5">
                                            <div class="lg:col-span-2 rounded-2xl bg-white p-4 border border-blue-100 text-center">
                                                <div class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 mb-3">
                                                    QR ngân hàng
                                                </div>
                                                <img
                                                    src="{{ $bankQrUrl }}"
                                                    alt="QR chuyển khoản MienTayShop"
                                                    class="mx-auto h-44 w-44 rounded-2xl border border-slate-100 bg-white object-contain p-2"
                                                    loading="lazy"
                                                >
                                                <p class="mt-3 text-xs text-slate-500">Quét bằng app ngân hàng, nội dung đã gắn mã đơn để shop đối chiếu.</p>
                                            </div>

                                            <div class="lg:col-span-3 grid gap-3">
                                                @foreach($bankAccounts as $account)
                                                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-white p-3 border border-blue-100">
                                                        <div class="flex items-center gap-3">
                                                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-xs font-black text-white">{{ $account['code'] }}</span>
                                                            <div>
                                                                <strong class="block text-slate-900">{{ $account['bank'] }}</strong>
                                                                <span class="text-xs text-slate-500">{{ $account['status'] }} • {{ $account['branch'] }}</span>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="copy-bank-value rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700" data-copy="{{ $account['account_number'] }}">
                                                            Sao chép STK
                                                        </button>
                                                    </div>
                                                @endforeach

                                                <div class="rounded-2xl bg-white p-3 border border-blue-100">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <span class="text-slate-500">Chủ tài khoản</span>
                                                        <strong class="text-slate-900">{{ $bankAccount['account_name'] }}</strong>
                                                    </div>
                                                </div>
                                                <div class="rounded-2xl bg-white p-3 border border-blue-100">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <span class="text-slate-500">Số tiền cần thanh toán</span>
                                                        <strong class="text-blue-700">{{ number_format($finalTotal, 0, ',', '.') }} VNĐ</strong>
                                                    </div>
                                                </div>
                                                <div class="rounded-2xl bg-white p-3 border border-blue-100">
                                                    <span class="block text-slate-500 mb-1">Nội dung chuyển khoản</span>
                                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                        <strong class="text-slate-900">{{ $bankTransferContent }}</strong>
                                                        <button type="button" class="copy-bank-value rounded-full bg-blue-600 px-4 py-2 text-xs font-bold text-white" data-copy="{{ $bankTransferContent }}">Sao chép nội dung</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4 grid gap-2 rounded-2xl bg-white/80 p-3 text-xs leading-6 text-slate-600 border border-blue-100">
                                            <p><strong class="text-slate-900">Quy trình:</strong> bấm “Đặt hàng ngay” để tạo đơn, chuyển khoản đúng số tiền/nội dung, sau đó người bán xác nhận thanh toán trong kênh bán.</p>
                                            <p><strong class="text-slate-900">Lưu ý:</strong> đây là mô phỏng liên kết ngân hàng cho bài nộp, chưa trừ tiền thật như cổng thanh toán thương mại.</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div class="mt-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Ghi chú đơn hàng
                            </label>
                            <textarea
                                id="notes"
                                name="notes"
                                rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            >{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 mt-8 lg:mt-0">
                    <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">Đơn hàng của bạn</h3>

                        <div class="space-y-4 mb-6">
                            @foreach($cartItems as $item)
                                <div class="flex items-center space-x-4 pb-4 border-b border-gray-100 last:border-b-0">
                                    <img
                                        src="{{ $item->cameraLens->image_url }}"
                                        alt="{{ $item->cameraLens->name }}"
                                        loading="lazy"
                                        decoding="async"
                                        class="w-16 h-16 object-cover rounded-lg bg-gray-100"
                                    >

                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-medium text-gray-900 truncate">{{ $item->cameraLens->name }}</h4>
                                        <p class="text-sm text-gray-500">{{ $item->cameraLens->brand }}</p>
                                        <div class="flex items-center justify-between mt-1">
                                            <span class="text-sm text-gray-600">SL: {{ $item->quantity }}</span>
                                            <span class="text-sm font-semibold text-gray-900">{{ $item->formatted_total_price }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 mb-6">
                            <div class="flex items-center justify-between gap-4 mb-3">
                                <div>
                                    <h4 class="font-semibold text-gray-900">Voucher của khách hàng</h4>
                                    <p class="text-sm text-gray-600">Nhập mã hoặc chọn nhanh voucher phù hợp với đơn hàng.</p>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-sky-700 border border-sky-200">
                                    Có sẵn
                                </span>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3 mb-4">
                                <input
                                    type="text"
                                    id="coupon_code"
                                    name="coupon_code"
                                    value="{{ old('coupon_code', $appliedCoupon?->code) }}"
                                    aria-label="Mã voucher"
                                    class="flex-1 rounded-xl border border-sky-200 bg-white px-4 py-3 text-sm focus:border-sky-400 focus:ring-2 focus:ring-sky-100"
                                >
                                <button
                                    type="submit"
                                    formaction="{{ route('cart.coupon.apply') }}"
                                    formmethod="POST"
                                    formnovalidate
                                    class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 transition-colors"
                                >
                                    Áp dụng
                                </button>
                            </div>

                            @if($appliedCoupon)
                                <div class="rounded-xl border border-green-200 bg-green-50 p-4 mb-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="inline-flex rounded-full bg-green-600 px-3 py-1 text-xs font-bold text-white">
                                                    {{ $appliedCoupon->code }}
                                                </span>
                                                <span class="text-sm font-semibold text-green-700">Đang áp dụng</span>
                                            </div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $appliedCoupon->name }}</p>
                                            <p class="text-sm text-gray-600">{{ $appliedCoupon->description }}</p>
                                            <p class="text-sm text-green-700 mt-2">
                                                Giảm ngay {{ number_format($discountAmount, 0, ',', '.') }} VNĐ cho đơn hàng này.
                                            </p>
                                        </div>
                                        <button
                                            type="submit"
                                            formaction="{{ route('cart.coupon.remove') }}"
                                            formmethod="POST"
                                            formnovalidate
                                            class="rounded-lg border border-green-200 bg-white px-3 py-2 text-xs font-semibold text-green-700 hover:bg-green-100"
                                        >
                                            Gỡ mã
                                        </button>
                                    </div>
                                </div>
                            @endif

                            @if($availableCoupons->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach($availableCoupons as $coupon)
                                        <div class="rounded-xl border border-sky-100 bg-white p-4">
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-700">
                                                            {{ $coupon->code }}
                                                        </span>
                                                        <span class="text-xs font-semibold text-gray-500">
                                                            {{ $coupon->formatted_value }}
                                                        </span>
                                                    </div>
                                                    <p class="text-sm font-semibold text-gray-900">{{ $coupon->name }}</p>
                                                    <p class="text-sm text-gray-600">{{ $coupon->description }}</p>
                                                    @if($coupon->minimum_amount)
                                                        <p class="text-xs text-gray-500 mt-2">
                                                            Đơn tối thiểu {{ number_format($coupon->minimum_amount, 0, ',', '.') }} VNĐ
                                                        </p>
                                                    @endif
                                                </div>
                                                <button
                                                    type="submit"
                                                    formaction="{{ route('cart.coupon.apply') }}"
                                                    formmethod="POST"
                                                    formnovalidate
                                                    onclick="document.getElementById('coupon_code').value='{{ $coupon->code }}'"
                                                    class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700 hover:bg-sky-100"
                                                >
                                                    Dùng mã
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">Hiện chưa có voucher phù hợp với giỏ hàng này.</p>
                            @endif
                        </div>

                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-gray-600">
                                <span>Tạm tính ({{ $cartCount }} sản phẩm)</span>
                                <span>{{ number_format($total, 0, ',', '.') }} VNĐ</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Phí vận chuyển</span>
                                <span class="text-green-600">Miễn phí</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Voucher</span>
                                <span class="{{ $discountAmount > 0 ? 'text-green-600 font-semibold' : '' }}">
                                    {{ $discountAmount > 0 ? '-' . number_format($discountAmount, 0, ',', '.') . ' VNĐ' : 'Chưa áp dụng' }}
                                </span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Thuế VAT</span>
                                <span>Đã bao gồm</span>
                            </div>
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between text-lg font-semibold text-gray-900">
                                    <span>Tổng cộng</span>
                                    <span>{{ number_format($finalTotal, 0, ',', '.') }} VNĐ</span>
                                </div>
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="w-full bg-blue-700 text-white py-4 px-6 rounded-full font-semibold text-lg hover:bg-blue-800 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
                            id="place-order-btn"
                        >
                            <i class="fas fa-lock mr-2"></i>
                            Đặt hàng ngay
                        </button>

                        <div class="mt-6 space-y-3 text-sm text-gray-600">
                            <div class="flex items-center">
                                <i class="fas fa-shield-alt text-green-500 mr-2"></i>
                                Thông tin được bảo mật tuyệt đối
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-ticket text-blue-500 mr-2"></i>
                                Voucher sẽ được lưu đúng trong đơn hàng của bạn
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-headset text-sky-500 mr-2"></i>
                                Hỗ trợ xác nhận đơn qua hotline hoặc chatbot
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const bankTransferRadio = document.querySelector('input[value="bank_transfer"]');
    const codRadio = document.querySelector('input[value="cod"]');
    const bankInfo = document.querySelector('.bank-info');
    const paymentCards = document.querySelectorAll('.payment-card');
    const paymentChannelInput = document.getElementById('payment_channel');
    const paymentBankCodeInput = document.getElementById('payment_bank_code');

    function toggleBankInfo() {
        if (bankInfo && bankTransferRadio) {
            bankInfo.classList.toggle('hidden', !bankTransferRadio.checked);
        }

        paymentCards.forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            const isChecked = radio?.checked;
            card.classList.toggle('border-blue-500', isChecked);
            card.classList.toggle('bg-blue-50', isChecked);
            card.classList.toggle('shadow-sm', isChecked);
        });
    }

    bankTransferRadio?.addEventListener('change', toggleBankInfo);
    codRadio?.addEventListener('change', toggleBankInfo);

    document.querySelectorAll('.payment-channel-option').forEach(button => {
        button.addEventListener('click', function () {
            bankTransferRadio.checked = true;

            document.querySelectorAll('.payment-channel-option').forEach(item => {
                item.classList.remove('border-blue-600', 'shadow-sm');
                item.classList.add('border-slate-200');
            });

            this.classList.remove('border-slate-200');
            this.classList.add('border-blue-600', 'shadow-sm');

            if (paymentChannelInput) {
                paymentChannelInput.value = this.dataset.channel || '';
            }

            if (paymentBankCodeInput) {
                paymentBankCodeInput.value = this.dataset.bank || '';
            }

            toggleBankInfo();
        });
    });

    document.querySelectorAll('.copy-bank-value').forEach(button => {
        button.addEventListener('click', async function () {
            const value = this.dataset.copy || '';

            try {
                await navigator.clipboard.writeText(value);
                const original = this.textContent;
                this.textContent = 'Đã sao chép';
                setTimeout(() => {
                    this.textContent = original;
                }, 1400);
            } catch (error) {
                alert('Bạn có thể sao chép thủ công: ' + value);
            }
        });
    });

    const form = document.getElementById('checkout-form');
    const submitBtn = document.getElementById('place-order-btn');
    const requiredFields = form.querySelectorAll('[required]');

    form.addEventListener('submit', function () {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang xử lý...';
        submitBtn.disabled = true;
    });

    function validateForm() {
        let isValid = true;

        requiredFields.forEach(field => {
            if (!String(field.value).trim()) {
                isValid = false;
            }
        });

        submitBtn.disabled = !isValid;
        return isValid;
    }

    requiredFields.forEach(field => {
        field.addEventListener('input', validateForm);
        field.addEventListener('blur', validateForm);
    });

    validateForm();
    toggleBankInfo();

    const phoneInput = document.getElementById('customer_phone');
    phoneInput.addEventListener('input', function (event) {
        let value = event.target.value.replace(/\D/g, '');

        if (value.length > 0 && !value.startsWith('0')) {
            value = '0' + value;
        }

        if (value.length > 11) {
            value = value.slice(0, 11);
        }

        event.target.value = value;
    });
});
</script>
@endsection
