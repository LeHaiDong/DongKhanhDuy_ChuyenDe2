<?php

use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\Seller\SellerController;
use App\Models\CameraLens;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'home'])->name('home');

Route::get('/shop', [ProductController::class, 'shop'])->name('products.shop');
Route::get('/search', [ProductController::class, 'search'])->name('products.search');
Route::get('/api/search-suggestions', [ProductController::class, 'searchSuggestions'])->name('products.search-suggestions');
Route::get('/product-media/{path}', function (string $path) {
    $path = ltrim(str_replace('\\', '/', $path), '/');

    abort_if(
        \Illuminate\Support\Str::contains($path, ['..']) ||
        !\Illuminate\Support\Str::startsWith($path, 'products/'),
        404
    );

    $file = storage_path('app/public/' . $path);

    abort_unless(is_file($file), 404);

    return response()->file($file);
})->where('path', '.*')->name('products.media');
Route::get('/category-media/{path}', function (string $path) {
    $path = ltrim(str_replace('\\', '/', $path), '/');

    abort_if(
        \Illuminate\Support\Str::contains($path, ['..']) ||
        !\Illuminate\Support\Str::startsWith($path, 'categories/'),
        404
    );

    $file = storage_path('app/public/' . $path);

    abort_unless(is_file($file), 404);

    return response()->file($file);
})->where('path', '.*')->name('categories.media');
Route::get('/product/{product}', [ProductController::class, 'show'])->name('products.show');
Route::post('/product/{product}/reviews', [ProductReviewController::class, 'store'])->middleware('auth')->name('products.reviews.store');

Route::get('/lens/{cameraLens}', function (CameraLens $cameraLens) {
    return redirect()->route('products.show', ['product' => $cameraLens->id]);
});

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/count', [CartController::class, 'count'])->name('count');
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::patch('/update/{cart}', [CartController::class, 'update'])->name('update');
    Route::delete('/remove/{cart}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/coupon/apply', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::post('/coupon/remove', [CartController::class, 'removeCoupon'])->name('coupon.remove');
    Route::post('/order', [CartController::class, 'processOrder'])->name('order');
});

Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('login');

Route::prefix('auth')->name('auth.customer.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [CustomerAuthController::class, 'login']);
        Route::get('/register', [CustomerAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [CustomerAuthController::class, 'register']);
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
        Route::get('/profile', [CustomerAuthController::class, 'profile'])->name('profile');
        Route::patch('/profile', [CustomerAuthController::class, 'updateProfile'])->name('profile.update');
        Route::get('/change-password', [CustomerAuthController::class, 'showChangePasswordForm'])->name('change-password');
        Route::patch('/change-password', [CustomerAuthController::class, 'updatePassword'])->name('change-password.update');
        Route::get('/orders', [CustomerAuthController::class, 'orders'])->name('orders');
    });
});

Route::prefix('favorites')->name('favorites.')->group(function () {
    Route::get('/count', [FavoriteController::class, 'count'])->name('count');

    Route::middleware('auth')->group(function () {
        Route::get('/', [FavoriteController::class, 'index'])->name('index');
        Route::post('/toggle/{cameraLens}', [FavoriteController::class, 'toggle'])->name('toggle');
        Route::delete('/remove/{cameraLens}', [FavoriteController::class, 'destroy'])->name('destroy');
        Route::delete('/clear', [FavoriteController::class, 'clear'])->name('clear');
    });
});

Route::prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::post('/send', [ChatController::class, 'sendMessage'])->name('send');
    Route::get('/history', [ChatController::class, 'getChatHistory'])->name('history');
    Route::delete('/clear', [ChatController::class, 'clearChat'])->name('clear');

    Route::post('/webhook/nlp-event', function (\Illuminate\Http\Request $request) {
        \App\Models\ChatEvent::create([
            'chat_message_id' => $request->input('chat_message_id'),
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'type' => $request->input('type', 'nlp'),
            'payload' => $request->input('payload', []),
        ]);

        return response()->json(['ok' => true]);
    })->name('webhook.nlp-event');
});

Route::prefix('seller')->middleware('auth')->name('seller.')->group(function () {
    Route::get('/', [SellerController::class, 'dashboard'])->name('dashboard');
    Route::get('/apply', [SellerController::class, 'apply'])->name('apply');
    Route::post('/apply', [SellerController::class, 'submitApplication'])->name('apply.store');
    Route::get('/products', [SellerController::class, 'products'])->name('products.index');
    Route::get('/products/create', [SellerController::class, 'createProduct'])->name('products.create');
    Route::post('/products', [SellerController::class, 'storeProduct'])->name('products.store');
    Route::get('/products/{product}/edit', [SellerController::class, 'editProduct'])->name('products.edit');
    Route::put('/products/{product}', [SellerController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{product}', [SellerController::class, 'destroyProduct'])->name('products.destroy');
    Route::get('/orders', [SellerController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order}', [SellerController::class, 'showOrder'])->name('orders.show');
    Route::post('/orders/{order}/confirm', [SellerController::class, 'confirmOrder'])->name('orders.confirm');
    Route::post('/orders/{order}/ship', [SellerController::class, 'shipOrder'])->name('orders.ship');
    Route::post('/orders/{order}/deliver', [SellerController::class, 'deliverOrder'])->name('orders.deliver');
    Route::post('/orders/{order}/cancel', [SellerController::class, 'cancelOrder'])->name('orders.cancel');
    Route::post('/orders/{order}/mark-as-paid', [SellerController::class, 'markOrderAsPaid'])->name('orders.mark-as-paid');
});

Route::prefix('admin')->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [App\Http\Controllers\Auth\AdminAuthController::class, 'login']);
    Route::post('/logout', [App\Http\Controllers\Auth\AdminAuthController::class, 'logout'])->name('admin.logout');
    Route::get('/check-auth', [App\Http\Controllers\Auth\AdminAuthController::class, 'checkAuth'])->name('admin.check-auth');
});

Route::prefix('admin')->middleware(['admin'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');

    Route::resource('users', App\Http\Controllers\Admin\UserController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ]);
    Route::post('/users/{user}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    Route::post('/users/bulk-action', [App\Http\Controllers\Admin\UserController::class, 'bulkAction'])->name('admin.users.bulk-action');

    Route::get('/favorites', [App\Http\Controllers\Admin\FavoriteController::class, 'index'])->name('admin.favorites.index');
    Route::delete('/favorites/{favorite}', [App\Http\Controllers\Admin\FavoriteController::class, 'destroy'])->name('admin.favorites.destroy');
    Route::get('/favorites/analytics', [App\Http\Controllers\Admin\FavoriteController::class, 'analytics'])->name('admin.favorites.analytics');
    Route::get('/favorites/export', [App\Http\Controllers\Admin\FavoriteController::class, 'export'])->name('admin.favorites.export');

    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class)->names([
        'index' => 'admin.categories.index',
        'create' => 'admin.categories.create',
        'store' => 'admin.categories.store',
        'show' => 'admin.categories.show',
        'edit' => 'admin.categories.edit',
        'update' => 'admin.categories.update',
        'destroy' => 'admin.categories.destroy',
    ]);
    Route::post('/categories/{category}/toggle-status', [App\Http\Controllers\Admin\CategoryController::class, 'toggleStatus'])->name('admin.categories.toggle-status');
    Route::post('/categories/bulk-action', [App\Http\Controllers\Admin\CategoryController::class, 'bulkAction'])->name('admin.categories.bulk-action');
    Route::post('/categories/reorder', [App\Http\Controllers\Admin\CategoryController::class, 'reorder'])->name('admin.categories.reorder');
    Route::get('/categories/tree', [App\Http\Controllers\Admin\CategoryController::class, 'getTree'])->name('admin.categories.tree');

    Route::resource('coupons', App\Http\Controllers\Admin\CouponController::class)->names([
        'index' => 'admin.coupons.index',
        'create' => 'admin.coupons.create',
        'store' => 'admin.coupons.store',
        'edit' => 'admin.coupons.edit',
        'update' => 'admin.coupons.update',
        'destroy' => 'admin.coupons.destroy',
    ])->except(['show']);
    Route::post('/coupons/{coupon}/toggle-status', [App\Http\Controllers\Admin\CouponController::class, 'toggleStatus'])->name('admin.coupons.toggle-status');

    Route::redirect('/orders', '/admin/dashboard')->name('admin.orders.index');
    Route::redirect('/orders/{order}', '/admin/dashboard')->name('admin.orders.show');

    Route::get('/seller-shops', [App\Http\Controllers\Admin\SellerShopController::class, 'index'])->name('admin.seller-shops.index');
    Route::get('/seller-shops/{sellerShop}', [App\Http\Controllers\Admin\SellerShopController::class, 'show'])->name('admin.seller-shops.show');
    Route::post('/seller-shops/{sellerShop}/approve', [App\Http\Controllers\Admin\SellerShopController::class, 'approve'])->name('admin.seller-shops.approve');
    Route::post('/seller-shops/{sellerShop}/reject', [App\Http\Controllers\Admin\SellerShopController::class, 'reject'])->name('admin.seller-shops.reject');

    Route::redirect('/camera-lenses', '/admin/products');
    Route::redirect('/camera-lenses/create', '/admin/products/create');
    Route::get('/camera-lenses/{camera_lense}/edit', function ($camera_lense) {
        return redirect()->route('admin.products.edit', ['product' => $camera_lense]);
    });

    Route::resource('products', ProductController::class)
        ->except(['show'])
        ->names([
            'index' => 'admin.products.index',
            'create' => 'admin.products.create',
            'store' => 'admin.products.store',
            'edit' => 'admin.products.edit',
            'update' => 'admin.products.update',
            'destroy' => 'admin.products.destroy',
        ]);
});
