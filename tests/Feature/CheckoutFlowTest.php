<?php

namespace Tests\Feature;

use App\Models\CameraLens;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_create_order_and_stock_is_reduced()
    {
        $user = User::create([
            'name' => 'Nguyen Van A',
            'email' => 'checkout@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $product = CameraLens::create([
            'name' => 'iPhone Test 128GB',
            'description' => 'San pham test dat hang',
            'price' => 10000000,
            'stock_quantity' => 10,
            'brand' => 'Apple',
            'product_type' => 'Dien thoai smartphone',
            'focal_length' => '6.1 inch',
            'max_aperture' => '128GB',
            'mount_type' => '5G',
            'is_active' => true,
        ]);

        Cart::create([
            'user_id' => $user->id,
            'camera_lens_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price,
        ]);

        $response = $this->actingAs($user)->post('/cart/order', [
            'customer_name' => 'Nguyen Van A',
            'customer_email' => 'checkout@example.com',
            'customer_phone' => '0901234567',
            'customer_address' => '123 Duong ABC, Phuong 1, Quan 1, TP HCM',
            'payment_method' => 'cod',
            'notes' => 'Test dat hang',
        ]);

        $response->assertRedirect(route('home'));

        $order = Order::first();

        $this->assertNotNull($order);
        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals('Nguyen Van A', $order->shipping_name);
        $this->assertEquals('cod', $order->payment_method);
        $this->assertDatabaseCount('order_items', 1);

        $product->refresh();
        $this->assertEquals(8, $product->stock_quantity);
        $this->assertDatabaseCount('carts', 0);
    }

    /** @test */
    public function authenticated_user_can_apply_coupon_when_creating_order()
    {
        $user = User::create([
            'name' => 'Nguyen Van B',
            'email' => 'coupon@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $product = CameraLens::create([
            'name' => 'Tai nghe Sony WH-1000XM5',
            'description' => 'San pham test voucher',
            'price' => 2000000,
            'stock_quantity' => 5,
            'brand' => 'Sony',
            'product_type' => 'Tai nghe',
            'focal_length' => 'Bluetooth',
            'max_aperture' => 'Chong on',
            'mount_type' => 'Khong day',
            'is_active' => true,
        ]);

        $coupon = Coupon::create([
            'code' => 'GIAM50K',
            'name' => 'Giam 50.000',
            'description' => 'Voucher test',
            'type' => Coupon::TYPE_FIXED,
            'value' => 50000,
            'minimum_amount' => 100000,
            'maximum_discount' => null,
            'usage_limit' => 10,
            'usage_limit_per_user' => 1,
            'used_count' => 0,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'is_active' => true,
            'first_order_only' => false,
        ]);

        Cart::create([
            'user_id' => $user->id,
            'camera_lens_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->price,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['checkout_coupon_code' => $coupon->code])
            ->post('/cart/order', [
                'customer_name' => 'Nguyen Van B',
                'customer_email' => 'coupon@example.com',
                'customer_phone' => '0907654321',
                'customer_address' => '45 Duong DEF, Phuong 2, Quan 3, TP HCM',
                'payment_method' => 'cod',
            ]);

        $response->assertRedirect(route('home'));

        $order = Order::first();

        $this->assertNotNull($order);
        $this->assertSame('GIAM50K', $order->coupon_code);
        $this->assertEquals(50000.0, (float) $order->discount_amount);
        $this->assertEquals(1950000.0, (float) $order->total_amount);
        $this->assertDatabaseHas('coupon_usages', [
            'coupon_id' => $coupon->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
        ]);
    }

    /** @test */
    public function admin_login_does_not_replace_customer_when_customer_places_order()
    {
        $customer = User::create([
            'name' => 'Khach Hang',
            'email' => 'khach@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);

        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin-test@example.com',
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        $product = CameraLens::create([
            'name' => 'Ao polo Telab',
            'description' => 'San pham test tach guard',
            'price' => 350000,
            'stock_quantity' => 10,
            'brand' => 'Telab',
            'product_type' => 'Thoi trang nam',
            'focal_length' => 'Size L',
            'max_aperture' => 'Cotton',
            'mount_type' => 'Mau trang',
            'is_active' => true,
        ]);

        Cart::create([
            'user_id' => $customer->id,
            'camera_lens_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->price,
        ]);

        $this->actingAs($customer, 'web');

        $adminLogin = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'secret123',
        ]);

        $adminLogin->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($customer, 'web');
        $this->assertSame($admin->id, auth()->guard('admin')->id());

        $response = $this->post('/cart/order', [
            'customer_name' => 'Khach Hang',
            'customer_email' => $customer->email,
            'customer_phone' => '0908888888',
            'customer_address' => '10 Duong Test, Phuong Test, Quan Test, Can Tho',
            'payment_method' => 'cod',
        ]);

        $response->assertRedirect(route('home'));

        $order = Order::first();

        $this->assertNotNull($order);
        $this->assertEquals($customer->id, $order->user_id);
        $this->assertNotEquals($admin->id, $order->user_id);
    }
}
