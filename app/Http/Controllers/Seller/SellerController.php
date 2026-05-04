<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SellerShop;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SellerController extends Controller
{
    public function dashboard(Request $request)
    {
        $shop = $request->user()->sellerShop()->first();

        if (!$shop) {
            return redirect()->route('seller.apply');
        }

        if (!$shop->isApproved()) {
            return view('seller.pending', compact('shop'));
        }

        $productsQuery = $shop->products();
        $orderItemsQuery = $this->sellerOrderItemsQuery($shop);
        $ordersQuery = $this->sellerOrdersQuery($shop);

        $stats = [
            'products' => (clone $productsQuery)->count(),
            'active_products' => (clone $productsQuery)->where('is_active', true)->count(),
            'stock' => (clone $productsQuery)->sum('stock_quantity'),
            'gross_revenue' => (float) (clone $orderItemsQuery)
                ->whereHas('order', fn ($query) => $query->where('status', '!=', 'cancelled'))
                ->sum('final_price'),
            'paid_revenue' => (float) (clone $orderItemsQuery)
                ->whereHas('order', fn ($query) => $query->where('payment_status', 'paid'))
                ->sum('final_price'),
            'orders' => (clone $ordersQuery)->count(),
            'pending_orders' => (clone $ordersQuery)->whereIn('status', ['pending', 'confirmed', 'processing'])->count(),
            'sold_quantity' => (int) (clone $orderItemsQuery)
                ->whereHas('order', fn ($query) => $query->where('status', '!=', 'cancelled'))
                ->sum('quantity'),
            'customers' => (clone $ordersQuery)->distinct('user_id')->count('user_id'),
        ];
        $latestProducts = $shop->products()
            ->with('categories')
            ->latest()
            ->take(8)
            ->get();
        $latestOrders = (clone $ordersQuery)
            ->with(['user', 'items.cameraLens'])
            ->latest()
            ->take(6)
            ->get();

        return view('seller.dashboard', compact('shop', 'stats', 'latestProducts', 'latestOrders'));
    }

    public function apply(Request $request)
    {
        $shop = $request->user()->sellerShop()->first();
        $categories = $this->sellerCategories();

        if ($shop?->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        return view('seller.apply', compact('shop', 'categories'));
    }

    public function submitApplication(Request $request)
    {
        $shop = $request->user()->sellerShop()->first();
        $data = $request->validate([
            'shop_name' => 'required|string|max:255',
            'brand_name' => 'required|string|max:255',
            'primary_category_id' => 'required|exists:categories,id',
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:255',
            'description' => 'required|string|max:1200',
            'document_type' => 'required|in:business_registration,citizen_id,household_business,brand_authorization',
            'document_number' => 'required|string|max:120',
            'document_note' => 'nullable|string|max:1200',
            'document_image' => [
                $shop?->document_image ? 'nullable' : 'required',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:4096',
            ],
            'shop_image' => [
                $shop?->shop_image ? 'nullable' : 'required',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:4096',
            ],
        ]);

        if ($shop?->isApproved()) {
            return redirect()
                ->route('seller.dashboard')
                ->with('success', 'Kênh bán của bạn đã được duyệt và đang hoạt động.');
        }

        if ($request->hasFile('document_image')) {
            $this->deleteSellerFile($shop?->document_image);
            $data['document_image'] = $this->storeSellerFile($request->file('document_image'));
        }

        if ($request->hasFile('shop_image')) {
            $this->deleteSellerFile($shop?->shop_image);
            $data['shop_image'] = $this->storeSellerFile($request->file('shop_image'));
        }

        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueShopSlug($data['shop_name'], $shop);
        $data['status'] = SellerShop::STATUS_PENDING;
        $data['admin_note'] = null;
        $data['approved_at'] = null;
        $data['approved_by'] = null;

        SellerShop::updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        return redirect()
            ->route('seller.dashboard')
            ->with('success', 'Đã gửi hồ sơ kênh bán. Admin sẽ duyệt trước khi bạn đăng sản phẩm.');
    }

    public function products(Request $request)
    {
        $shop = $this->approvedShopOrRedirect($request);

        if (!$shop instanceof SellerShop) {
            return $shop;
        }

        $products = $shop->products()
            ->with('categories')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('seller.products.index', compact('shop', 'products'));
    }

    public function createProduct(Request $request)
    {
        $shop = $this->approvedShopOrRedirect($request);

        if (!$shop instanceof SellerShop) {
            return $shop;
        }

        $categories = $this->sellerCategories();
        $product = new Product([
            'stock_quantity' => 1,
            'condition' => 'new',
        ]);

        return view('seller.products.create', compact('shop', 'categories', 'product'));
    }

    public function storeProduct(Request $request)
    {
        $shop = $this->approvedShopOrRedirect($request);

        if (!$shop instanceof SellerShop) {
            return $shop;
        }

        $data = $request->validate($this->productRules(true));
        $category = Category::active()->root()->findOrFail($data['category_id']);
        $productData = $this->buildProductData($data, $shop, $category);
        $productData['image'] = $this->storeProductImage($request->file('image'));

        $product = Product::create($productData);
        $product->categories()->sync([$category->id]);

        return redirect()
            ->route('seller.products.index')
            ->with('success', 'Sản phẩm đã được đăng lên kênh bán của bạn.');
    }

    public function editProduct(Request $request, Product $product)
    {
        $shop = $this->approvedShopOrRedirect($request);

        if (!$shop instanceof SellerShop) {
            return $shop;
        }

        $this->ensureOwnProduct($shop, $product);

        $categories = $this->sellerCategories();

        return view('seller.products.edit', compact('shop', 'categories', 'product'));
    }

    public function updateProduct(Request $request, Product $product)
    {
        $shop = $this->approvedShopOrRedirect($request);

        if (!$shop instanceof SellerShop) {
            return $shop;
        }

        $this->ensureOwnProduct($shop, $product);

        $data = $request->validate($this->productRules(false));
        $category = Category::active()->root()->findOrFail($data['category_id']);
        $productData = $this->buildProductData($data, $shop, $category, $product);

        if ($request->hasFile('image')) {
            $this->deleteProductImage($product->image);
            $productData['image'] = $this->storeProductImage($request->file('image'));
        }

        $product->update($productData);
        $product->categories()->sync([$category->id]);

        return redirect()
            ->route('seller.products.index')
            ->with('success', 'Sản phẩm đã được cập nhật.');
    }

    public function destroyProduct(Request $request, Product $product)
    {
        $shop = $this->approvedShopOrRedirect($request);

        if (!$shop instanceof SellerShop) {
            return $shop;
        }

        $this->ensureOwnProduct($shop, $product);
        $this->deleteProductImage($product->image);
        $product->delete();

        return redirect()
            ->route('seller.products.index')
            ->with('success', 'Sản phẩm đã được xóa khỏi kênh bán.');
    }

    public function orders(Request $request)
    {
        $shop = $this->approvedShopOrRedirect($request);

        if (!$shop instanceof SellerShop) {
            return $shop;
        }

        $orders = $this->sellerOrdersQuery($shop)
            ->with(['user', 'items.cameraLens'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('seller.orders.index', compact('shop', 'orders'));
    }

    public function showOrder(Request $request, Order $order)
    {
        $shop = $this->approvedShopOrRedirect($request);

        if (!$shop instanceof SellerShop) {
            return $shop;
        }

        $this->ensureOwnOrder($shop, $order);

        $order->load(['user', 'items.cameraLens']);
        $sellerItems = $this->sellerItemsFromOrder($order, $shop);
        $sellerSubtotal = (float) $sellerItems->sum('final_price');

        return view('seller.orders.show', compact('shop', 'order', 'sellerItems', 'sellerSubtotal'));
    }

    public function confirmOrder(Request $request, Order $order)
    {
        $shop = $this->approvedShopOrRedirect($request);

        if (!$shop instanceof SellerShop) {
            return $shop;
        }

        $this->ensureOwnOrder($shop, $order);

        if (!$order->confirm()) {
            return back()->with('error', 'Đơn hàng này không còn ở trạng thái chờ xác nhận.');
        }

        return back()->with('success', 'Người bán đã xác nhận đơn hàng.');
    }

    public function shipOrder(Request $request, Order $order)
    {
        $shop = $this->approvedShopOrRedirect($request);

        if (!$shop instanceof SellerShop) {
            return $shop;
        }

        $this->ensureOwnOrder($shop, $order);

        $data = $request->validate([
            'carrier' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
            'tracking_note' => 'nullable|string|max:500',
        ]);

        $trackingInfo = array_filter([
            'carrier' => $data['carrier'] ?? null,
            'tracking_number' => $data['tracking_number'] ?? null,
            'note' => $data['tracking_note'] ?? null,
            'updated_by_shop' => $shop->shop_name,
        ]);

        if (!$order->ship($trackingInfo)) {
            return back()->with('error', 'Đơn hàng cần được xác nhận trước khi chuyển sang đang giao.');
        }

        return back()->with('success', 'Đã cập nhật đơn sang trạng thái đang giao.');
    }

    public function deliverOrder(Request $request, Order $order)
    {
        $shop = $this->approvedShopOrRedirect($request);

        if (!$shop instanceof SellerShop) {
            return $shop;
        }

        $this->ensureOwnOrder($shop, $order);

        if (!$order->deliver()) {
            return back()->with('error', 'Chỉ có thể hoàn tất đơn đang giao.');
        }

        return back()->with('success', 'Đã xác nhận đơn hàng giao thành công.');
    }

    public function cancelOrder(Request $request, Order $order)
    {
        $shop = $this->approvedShopOrRedirect($request);

        if (!$shop instanceof SellerShop) {
            return $shop;
        }

        $this->ensureOwnOrder($shop, $order);

        $data = $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        if (!$order->cancel($data['cancel_reason'])) {
            return back()->with('error', 'Đơn hàng này không thể hủy ở trạng thái hiện tại.');
        }

        return back()->with('success', 'Đã hủy đơn hàng và hoàn lại tồn kho.');
    }

    public function markOrderAsPaid(Request $request, Order $order)
    {
        $shop = $this->approvedShopOrRedirect($request);

        if (!$shop instanceof SellerShop) {
            return $shop;
        }

        $this->ensureOwnOrder($shop, $order);

        $data = $request->validate([
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $order->markAsPaid($data['payment_reference'] ?? null);

        return back()->with('success', 'Đã xác nhận thanh toán cho đơn hàng.');
    }

    private function approvedShopOrRedirect(Request $request)
    {
        $shop = $request->user()->sellerShop()->first();

        if (!$shop) {
            return redirect()
                ->route('seller.apply')
                ->with('error', 'Bạn cần tạo kênh bán trước.');
        }

        if (!$shop->isApproved()) {
            return redirect()
                ->route('seller.dashboard')
                ->with('error', 'Kênh bán cần được admin duyệt trước khi đăng sản phẩm.');
        }

        return $shop;
    }

    private function ensureOwnProduct(SellerShop $shop, Product $product): void
    {
        abort_if((int) $product->seller_shop_id !== (int) $shop->id, 403);
    }

    private function ensureOwnOrder(SellerShop $shop, Order $order): void
    {
        abort_unless($this->sellerOrdersQuery($shop)->whereKey($order->id)->exists(), 403);
    }

    private function productRules(bool $creating): array
    {
        return [
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string|max:2000',
            'image' => [
                $creating ? 'required' : 'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:4096',
            ],
            'is_active' => 'nullable|boolean',
        ];
    }

    private function buildProductData(array $data, SellerShop $shop, Category $category, ?Product $product = null): array
    {
        $brand = trim((string) ($data['brand'] ?? ''));

        if ($brand === '') {
            $brand = $shop->brand_name ?: $shop->shop_name;
        }

        $name = trim((string) $data['name']);
        $productType = $category->display_name;

        return [
            'seller_shop_id' => $shop->id,
            'name' => $name,
            'brand' => $brand,
            'product_type' => $productType,
            'spec_label_1' => 'Tên sản phẩm',
            'spec_label_2' => 'Thương hiệu',
            'spec_label_3' => 'Danh mục',
            'focal_length' => $name,
            'max_aperture' => $brand,
            'mount_type' => $productType,
            'price' => (float) $data['price'],
            'description' => $this->nullableString($data['description'] ?? null),
            'search_keywords' => collect([$name, $brand, $productType, $shop->shop_name, $shop->brand_name])
                ->filter()
                ->unique()
                ->implode(', '),
            'stock_quantity' => (int) $data['stock_quantity'],
            'is_active' => (bool) ($data['is_active'] ?? $product?->is_active ?? true),
            'condition' => 'new',
        ];
    }

    private function sellerCategories()
    {
        return Category::active()
            ->root()
            ->ordered()
            ->get();
    }

    private function sellerOrderItemsQuery(SellerShop $shop)
    {
        return OrderItem::query()
            ->whereHas('cameraLens', function ($query) use ($shop) {
                $query->where('seller_shop_id', $shop->id);
            });
    }

    private function sellerOrdersQuery(SellerShop $shop)
    {
        return Order::query()
            ->whereHas('items.cameraLens', function ($query) use ($shop) {
                $query->where('seller_shop_id', $shop->id);
            });
    }

    private function sellerItemsFromOrder(Order $order, SellerShop $shop)
    {
        return $order->items->filter(function (OrderItem $item) use ($shop) {
            return (int) $item->cameraLens?->seller_shop_id === (int) $shop->id;
        })->values();
    }

    private function uniqueShopSlug(string $shopName, ?SellerShop $existingShop = null): string
    {
        $base = Str::slug($shopName) ?: 'shop';
        $slug = $base;
        $counter = 2;

        while (SellerShop::query()
            ->where('slug', $slug)
            ->when($existingShop, fn ($query) => $query->whereKeyNot($existingShop->id))
            ->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function storeSellerFile(UploadedFile $image): string
    {
        $extension = strtolower($image->getClientOriginalExtension() ?: $image->extension() ?: 'jpg');
        $filename = Str::uuid() . '.' . $extension;
        $directory = public_path('uploads/seller-shops');

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $image->move($directory, $filename);

        return 'uploads/seller-shops/' . $filename;
    }

    private function deleteSellerFile(?string $imagePath): void
    {
        if (!$imagePath || Str::startsWith($imagePath, ['http://', 'https://'])) {
            return;
        }

        $path = ltrim(str_replace('\\', '/', $imagePath), '/');

        if (Str::startsWith($path, 'uploads/seller-shops/')) {
            $publicPath = public_path($path);

            if (is_file($publicPath)) {
                @unlink($publicPath);
            }
        }
    }

    private function storeProductImage(UploadedFile $image): string
    {
        $extension = strtolower($image->getClientOriginalExtension() ?: $image->extension() ?: 'jpg');
        $filename = Str::uuid() . '.' . $extension;
        $directory = public_path('uploads/products');

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $image->move($directory, $filename);

        return 'uploads/products/' . $filename;
    }

    private function deleteProductImage(?string $imagePath): void
    {
        if (!$imagePath || Str::startsWith($imagePath, ['http://', 'https://'])) {
            return;
        }

        $path = ltrim(str_replace('\\', '/', $imagePath), '/');

        if (Str::startsWith($path, 'uploads/products/')) {
            $publicPath = public_path($path);

            if (is_file($publicPath)) {
                @unlink($publicPath);
            }

            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function nullableString($value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
