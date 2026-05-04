<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SellerShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerShopController extends Controller
{
    public function index(Request $request)
    {
        $query = SellerShop::query()
            ->with(['user', 'category'])
            ->withCount('products')
            ->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $sellerShops = $query->paginate(12)->withQueryString();
        $statusCounts = [
            'pending' => SellerShop::pending()->count(),
            'approved' => SellerShop::approved()->count(),
            'rejected' => SellerShop::where('status', SellerShop::STATUS_REJECTED)->count(),
        ];

        return view('admin.seller-shops.index', compact('sellerShops', 'statusCounts'));
    }

    public function show(SellerShop $sellerShop)
    {
        $sellerShop->load(['user', 'approvedBy', 'category', 'products.categories']);
        $orderItemsQuery = OrderItem::query()
            ->whereHas('cameraLens', fn ($query) => $query->where('seller_shop_id', $sellerShop->id));
        $ordersQuery = Order::query()
            ->whereHas('items.cameraLens', fn ($query) => $query->where('seller_shop_id', $sellerShop->id));
        $stats = [
            'products' => $sellerShop->products->count(),
            'active_products' => $sellerShop->products->where('is_active', true)->count(),
            'orders' => (clone $ordersQuery)->count(),
            'customers' => (clone $ordersQuery)->distinct('user_id')->count('user_id'),
            'gross_revenue' => (float) (clone $orderItemsQuery)
                ->whereHas('order', fn ($query) => $query->where('status', '!=', 'cancelled'))
                ->sum('final_price'),
            'paid_revenue' => (float) (clone $orderItemsQuery)
                ->whereHas('order', fn ($query) => $query->where('payment_status', 'paid'))
                ->sum('final_price'),
        ];

        return view('admin.seller-shops.show', compact('sellerShop', 'stats'));
    }

    public function approve(Request $request, SellerShop $sellerShop)
    {
        $data = $request->validate([
            'admin_note' => 'nullable|string|max:1200',
        ]);

        $sellerShop->update([
            'status' => SellerShop::STATUS_APPROVED,
            'admin_note' => $data['admin_note'] ?? null,
            'approved_at' => now(),
            'approved_by' => Auth::guard('admin')->id(),
        ]);

        return redirect()
            ->route('admin.seller-shops.show', $sellerShop)
            ->with('success', 'Đã duyệt kênh bán. Người bán có thể đăng sản phẩm.');
    }

    public function reject(Request $request, SellerShop $sellerShop)
    {
        $data = $request->validate([
            'admin_note' => 'nullable|string|max:1200',
        ]);

        $sellerShop->update([
            'status' => SellerShop::STATUS_REJECTED,
            'admin_note' => $data['admin_note'] ?? 'Hồ sơ cần bổ sung thông tin trước khi duyệt.',
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return redirect()
            ->route('admin.seller-shops.show', $sellerShop)
            ->with('success', 'Đã từ chối hồ sơ kênh bán và gửi ghi chú cho người bán.');
    }
}
