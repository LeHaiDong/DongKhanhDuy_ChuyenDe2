<?php

namespace App\Http\Controllers;

use App\Models\CameraLens;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    private const SESSION_COUPON_KEY = 'checkout_coupon_code';

    public function index()
    {
        $cartItems = $this->getCartItems();
        $total = (float) $cartItems->sum('total_price');
        $cartCount = (int) $cartItems->sum('quantity');

        return view('cart.index', compact('cartItems', 'total', 'cartCount'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'camera_lens_id' => 'required|exists:camera_lenses,id',
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $wantsJson = $request->expectsJson() || $request->ajax() || $request->isJson();
        $cameraLens = CameraLens::findOrFail($request->camera_lens_id);

        if ($this->currentUserOwnsProductShop($cameraLens)) {
            $message = 'Bạn đang đăng nhập bằng tài khoản chủ shop nên không thể tự mua sản phẩm của chính shop. Hãy đăng xuất và đăng nhập tài khoản khách hàng để đặt mua.';

            if (!$wantsJson) {
                return back()->with('error', $message);
            }

            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        if ($cameraLens->stock_quantity < $request->quantity) {
            if (!$wantsJson) {
                return back()->with('error', 'Sản phẩm trong kho không đủ số lượng bạn chọn.');
            }

            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm trong kho không đủ số lượng bạn chọn.',
            ]);
        }

        $existingItem = Cart::where('camera_lens_id', $request->camera_lens_id)
            ->where(function ($query) {
                if (Auth::check()) {
                    $query->where('user_id', Auth::id());
                    return;
                }

                $query->where('session_id', session()->getId());
            })
            ->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $request->quantity;

            if ($newQuantity > $cameraLens->stock_quantity) {
                if (!$wantsJson) {
                    return back()->with('error', 'Không thể thêm vượt quá số lượng còn trong kho.');
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Không thể thêm vượt quá số lượng còn trong kho.',
                ]);
            }

            $existingItem->update(['quantity' => $newQuantity]);
        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'session_id' => Auth::check() ? null : session()->getId(),
                'camera_lens_id' => $request->camera_lens_id,
                'quantity' => $request->quantity,
                'unit_price' => $cameraLens->price,
            ]);
        }

        if (!$wantsJson) {
            if ($request->boolean('buy_now')) {
                return redirect()
                    ->route('cart.checkout')
                    ->with('success', 'Đã thêm sản phẩm, bạn có thể kiểm tra thông tin thanh toán.');
            }

            return back()->with('success', 'Đã thêm sản phẩm vào giỏ hàng.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm sản phẩm vào giỏ hàng.',
            'cart_count' => Cart::getCartCount(Auth::id(), session()->getId()),
        ]);
    }

    public function update(Request $request, Cart $cart)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        if (!$this->isOwner($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thao tác với sản phẩm này.',
            ], 403);
        }

        if ($cart->cameraLens->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Số lượng tồn kho hiện tại không đủ.',
            ]);
        }

        $cart->update(['quantity' => $request->quantity]);

        $cartItems = $this->getCartItems();
        $total = (float) $cartItems->sum('total_price');
        $cartCount = (int) $cartItems->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật giỏ hàng.',
            'item_total' => number_format((float) $cart->total_price, 0, ',', '.'),
            'cart_total' => number_format($total, 0, ',', '.'),
            'cart_count' => $cartCount,
        ]);
    }

    public function remove(Cart $cart)
    {
        if (!$this->isOwner($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thao tác với sản phẩm này.',
            ], 403);
        }

        $cart->delete();

        $cartItems = $this->getCartItems();
        $total = (float) $cartItems->sum('total_price');
        $cartCount = (int) $cartItems->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.',
            'cart_total' => number_format($total, 0, ',', '.'),
            'cart_count' => $cartCount,
        ]);
    }

    public function clear()
    {
        $query = Cart::query();

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('session_id', session()->getId());
        }

        $query->delete();
        $this->forgetCoupon();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa toàn bộ giỏ hàng.',
            'cart_count' => 0,
        ]);
    }

    public function count()
    {
        return response()->json([
            'cart_count' => Cart::getCartCount(Auth::id(), session()->getId()),
        ]);
    }

    public function checkout()
    {
        if (!Auth::check()) {
            return redirect()
                ->guest(route('auth.customer.login'))
                ->with('error', 'Vui lòng đăng nhập để tiếp tục thanh toán.');
        }

        $cartItems = $this->getCartItems();

        if ($cartItems->isEmpty()) {
            $this->forgetCoupon();

            return redirect()
                ->route('cart.index')
                ->with('error', 'Giỏ hàng đang trống. Hãy thêm sản phẩm trước khi thanh toán.');
        }

        $total = (float) $cartItems->sum('total_price');
        $cartCount = (int) $cartItems->sum('quantity');
        $couponState = $this->resolveCoupon(session(self::SESSION_COUPON_KEY), $cartItems, $total);

        if (($couponState['requested'] ?? false) && !($couponState['valid'] ?? false)) {
            $this->forgetCoupon();
            session()->flash('error', $couponState['message']);
            $couponState = ['requested' => false, 'valid' => false];
        }

        $availableCoupons = $this->getAvailableCoupons($cartItems, $total);
        $appliedCoupon = $couponState['coupon'] ?? null;
        $discountAmount = (float) ($couponState['discount_amount'] ?? 0);
        $finalTotal = max($total - $discountAmount, 0);

        return view('cart.checkout', compact(
            'cartItems',
            'total',
            'cartCount',
            'availableCoupons',
            'appliedCoupon',
            'discountAmount',
            'finalTotal'
        ));
    }

    public function applyCoupon(Request $request)
    {
        if (!Auth::check()) {
            return redirect()
                ->guest(route('auth.customer.login'))
                ->with('error', 'Vui lòng đăng nhập để dùng voucher.');
        }

        $request->validate([
            'coupon_code' => 'required|string|max:50',
        ]);

        $cartItems = $this->getCartItems();

        if ($cartItems->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Giỏ hàng đang trống nên chưa thể áp dụng voucher.');
        }

        $total = (float) $cartItems->sum('total_price');
        $couponState = $this->resolveCoupon($request->string('coupon_code')->trim()->toString(), $cartItems, $total);

        if (!($couponState['valid'] ?? false)) {
            return back()->with('error', $couponState['message'] ?? 'Voucher không hợp lệ.');
        }

        session([self::SESSION_COUPON_KEY => $couponState['coupon']->code]);

        return back()->with(
            'success',
            "Đã áp dụng voucher {$couponState['coupon']->code}, giảm " .
            number_format((float) $couponState['discount_amount'], 0, ',', '.') . ' VNĐ.'
        );
    }

    public function removeCoupon()
    {
        $this->forgetCoupon();

        return back()->with('success', 'Đã gỡ voucher khỏi đơn hàng.');
    }

    public function processOrder(Request $request)
    {
        if (!Auth::check()) {
            return redirect()
                ->guest(route('auth.customer.login'))
                ->with('error', 'Vui lòng đăng nhập để đặt hàng.');
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:500',
            'payment_method' => 'required|in:cod,bank_transfer',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $cartItems = $this->getCartItems();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng đang trống.');
        }

        $subtotal = (float) $cartItems->sum('total_price');
        $couponState = $this->resolveCoupon(session(self::SESSION_COUPON_KEY), $cartItems, $subtotal);

        if (($couponState['requested'] ?? false) && !($couponState['valid'] ?? false)) {
            $this->forgetCoupon();

            return redirect()
                ->route('cart.checkout')
                ->with('error', $couponState['message'] ?? 'Voucher không còn hợp lệ. Vui lòng kiểm tra lại.');
        }

        $coupon = $couponState['coupon'] ?? null;
        $discountAmount = (float) ($couponState['discount_amount'] ?? 0);

        $order = DB::transaction(function () use ($request, $cartItems, $subtotal, $coupon, $discountAmount) {
            $products = CameraLens::whereIn('id', $cartItems->pluck('camera_lens_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($cartItems as $item) {
                $product = $products->get($item->camera_lens_id);

                if (!$product || !$product->is_active) {
                    throw ValidationException::withMessages([
                        'stock' => "Sản phẩm {$item->cameraLens->name} hiện không còn khả dụng.",
                    ]);
                }

                if ($this->currentUserOwnsProductShop($product)) {
                    throw ValidationException::withMessages([
                        'cart' => "Bạn không thể đặt mua sản phẩm {$product->name} vì đây là sản phẩm thuộc shop của chính tài khoản đang đăng nhập.",
                    ]);
                }

                if ($product->stock_quantity < $item->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => "Sản phẩm {$product->name} chỉ còn {$product->stock_quantity} trong kho.",
                    ]);
                }
            }

            $addressParts = $this->extractAddressParts($request->string('customer_address')->trim()->toString());

            $order = Order::create([
                'user_id' => Auth::id(),
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_method === 'bank_transfer'
                    ? $request->string('payment_reference')->trim()->toString()
                    : null,
                'shipping_name' => $request->customer_name,
                'shipping_phone' => $request->customer_phone,
                'shipping_email' => $request->customer_email,
                'shipping_address' => $request->customer_address,
                'shipping_province' => $addressParts['province'],
                'shipping_district' => $addressParts['district'],
                'shipping_ward' => $addressParts['ward'],
                'shipping_method' => 'standard',
                'shipping_fee' => 0,
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'discount_amount' => $discountAmount,
                'coupon_code' => $coupon?->code,
                'total_amount' => max($subtotal - $discountAmount, 0),
                'notes' => $request->notes,
            ]);

            foreach ($cartItems as $item) {
                $product = $products->get($item->camera_lens_id);

                OrderItem::createFromCameraLens(
                    $order,
                    $product,
                    $item->quantity,
                    $item->unit_price
                );
            }

            $order->load('items.cameraLens');
            $order->reduceStock();
            $order->calculateTotals();

            if ($coupon && $discountAmount > 0) {
                $coupon->use(Auth::user(), $order->fresh(), $discountAmount);
            }

            return $order->fresh();
        });

        $this->clearCart();
        $this->forgetCoupon();

        return redirect()
            ->route('home')
            ->with('success', "Đặt hàng thành công! Mã đơn của bạn là {$order->order_number}.");
    }

    private function getCartItems()
    {
        return Cart::getCartItems(Auth::id(), session()->getId());
    }

    private function getAvailableCoupons($cartItems, float $orderTotal)
    {
        if (!Auth::check() || $cartItems->isEmpty()) {
            return collect();
        }

        $couponCartItems = $this->buildCouponCartItems($cartItems);

        return Coupon::available()
            ->orderBy('type')
            ->orderByDesc('value')
            ->get()
            ->filter(function (Coupon $coupon) use ($orderTotal, $couponCartItems) {
                return $coupon->canBeUsedBy(Auth::user(), $orderTotal, $couponCartItems)['valid'] ?? false;
            })
            ->take(6)
            ->values();
    }

    private function resolveCoupon(?string $couponCode, $cartItems, float $orderTotal): array
    {
        $couponCode = strtoupper(trim((string) $couponCode));

        if ($couponCode === '') {
            return [
                'requested' => false,
                'valid' => false,
            ];
        }

        if (!Auth::check()) {
            return [
                'requested' => true,
                'valid' => false,
                'message' => 'Vui lòng đăng nhập để dùng voucher.',
            ];
        }

        if ($cartItems->isEmpty()) {
            return [
                'requested' => true,
                'valid' => false,
                'message' => 'Giỏ hàng đang trống nên chưa thể áp dụng voucher.',
            ];
        }

        $couponCartItems = $this->buildCouponCartItems($cartItems);
        $couponResult = Coupon::findValidCoupon($couponCode, Auth::user(), $orderTotal, $couponCartItems);

        if (!($couponResult['valid'] ?? false)) {
            return [
                'requested' => true,
                'valid' => false,
                'message' => $couponResult['message'] ?? 'Voucher không hợp lệ.',
            ];
        }

        $coupon = $couponResult['coupon'];
        $discountAmount = (float) $coupon->calculateDiscount($orderTotal, $couponCartItems);

        if ($discountAmount <= 0) {
            return [
                'requested' => true,
                'valid' => false,
                'message' => 'Voucher này chưa áp dụng được cho giỏ hàng hiện tại.',
            ];
        }

        return [
            'requested' => true,
            'valid' => true,
            'coupon' => $coupon,
            'discount_amount' => $discountAmount,
        ];
    }

    private function buildCouponCartItems($cartItems): array
    {
        $cartItems->loadMissing('cameraLens.categories');

        return $cartItems
            ->map(function (Cart $item) {
                $product = $item->cameraLens;

                return [
                    'product_id' => $item->camera_lens_id,
                    'camera_lens_id' => $item->camera_lens_id,
                    'total' => (float) $item->total_price,
                    'category_ids' => $product
                        ? $product->categories->pluck('id')->map(fn ($id) => (int) $id)->all()
                        : [],
                ];
            })
            ->filter(fn ($item) => !empty($item['camera_lens_id']))
            ->values()
            ->all();
    }

    private function isOwner(Cart $cart): bool
    {
        if (Auth::check()) {
            return (int) $cart->user_id === (int) Auth::id();
        }

        return $cart->session_id === session()->getId();
    }

    private function clearCart(): void
    {
        $query = Cart::query();

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('session_id', session()->getId());
        }

        $query->delete();
    }

    private function forgetCoupon(): void
    {
        session()->forget(self::SESSION_COUPON_KEY);
    }

    private function currentUserOwnsProductShop(CameraLens $product): bool
    {
        if (!Auth::check() || !$product->seller_shop_id) {
            return false;
        }

        $sellerShop = Auth::user()->sellerShop;

        return $sellerShop && (int) $sellerShop->id === (int) $product->seller_shop_id;
    }

    private function extractAddressParts(string $address): array
    {
        $parts = collect(explode(',', $address))
            ->map(fn ($part) => trim($part))
            ->filter()
            ->values();

        return [
            'ward' => $parts->count() >= 3 ? $parts->get($parts->count() - 3) : null,
            'district' => $parts->count() >= 2 ? $parts->get($parts->count() - 2) : 'Chưa cập nhật',
            'province' => $parts->count() >= 1 ? $parts->last() : 'Chưa cập nhật',
        ];
    }
}
