<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SellerShop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SellerChannelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customer_can_submit_seller_shop_application()
    {
        $user = User::create([
            'name' => 'Nguoi Ban Demo',
            'email' => 'seller-demo@example.com',
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);
        $category = Category::create([
            'name' => 'Bach hoa online',
            'slug' => 'bach-hoa-demo',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('seller.apply.store'), [
            'shop_name' => 'Shop Mien Tay Demo',
            'brand_name' => 'Mien Tay Brand',
            'primary_category_id' => $category->id,
            'phone' => '0909000111',
            'address' => 'Can Tho',
            'description' => 'Shop ban hang da nganh.',
            'document_type' => 'business_registration',
            'document_number' => 'GPKD-001',
            'document_note' => 'Ho so hop le.',
            'shop_image' => UploadedFile::fake()->createWithContent(
                'shop.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
            ),
            'document_image' => UploadedFile::fake()->createWithContent(
                'document.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
            ),
        ]);

        $response->assertRedirect(route('seller.dashboard'));

        $this->assertDatabaseHas('seller_shops', [
            'user_id' => $user->id,
            'shop_name' => 'Shop Mien Tay Demo',
            'primary_category_id' => $category->id,
            'status' => SellerShop::STATUS_PENDING,
        ]);

        $shop = SellerShop::where('user_id', $user->id)->first();
        $this->assertFileExists(public_path($shop->shop_image));
        $this->assertFileExists(public_path($shop->document_image));

        $this->actingAs($user)
            ->get(route('seller.dashboard'))
            ->assertOk()
            ->assertSee('Hồ sơ đang chờ duyệt');

        @unlink(public_path($shop->shop_image));
        @unlink(public_path($shop->document_image));
    }

    /** @test */
    public function admin_can_approve_shop_and_seller_can_create_product()
    {
        $seller = User::create([
            'name' => 'Seller Owner',
            'email' => 'seller-owner@example.com',
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);

        $admin = User::create([
            'name' => 'Admin Seller',
            'email' => 'admin-seller@example.com',
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        $category = Category::create([
            'name' => 'Thời Trang Nam',
            'slug' => 'thoi-trang-nam',
            'is_active' => true,
        ]);

        $shop = SellerShop::create([
            'user_id' => $seller->id,
            'shop_name' => 'Coolmate Demo',
            'slug' => 'coolmate-demo',
            'brand_name' => 'Coolmate',
            'phone' => '0909000222',
            'status' => SellerShop::STATUS_PENDING,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.seller-shops.approve', $shop))
            ->assertRedirect(route('admin.seller-shops.show', $shop));

        $shop->refresh();
        $this->assertTrue($shop->isApproved());

        $response = $this->actingAs($seller)->post(route('seller.products.store'), [
            'name' => 'Áo polo nam Coolmate',
            'brand' => 'Coolmate',
            'category_id' => $category->id,
            'price' => 199000,
            'stock_quantity' => 15,
            'description' => 'Áo polo nam chất liệu thoáng mát.',
            'is_active' => 1,
            'image' => UploadedFile::fake()->createWithContent(
                'ao-polo.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
            ),
        ]);

        $response->assertRedirect(route('seller.products.index'));

        $product = Product::where('name', 'Áo polo nam Coolmate')->first();

        $this->assertNotNull($product);
        $this->assertSame($shop->id, $product->seller_shop_id);
        $this->assertSame('Thời Trang Nam', $product->product_type);
        $this->assertDatabaseHas('camera_lens_categories', [
            'camera_lens_id' => $product->id,
            'category_id' => $category->id,
        ]);
        $this->assertFileExists(public_path($product->image));

        @unlink(public_path($product->image));
    }

    /** @test */
    public function approved_seller_can_view_revenue_and_orders_for_own_products()
    {
        $seller = User::create([
            'name' => 'Seller Revenue',
            'email' => 'seller-revenue@example.com',
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);

        $customer = User::create([
            'name' => 'Customer Buyer',
            'email' => 'customer-buyer@example.com',
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);

        $shop = SellerShop::create([
            'user_id' => $seller->id,
            'shop_name' => 'Revenue Shop',
            'slug' => 'revenue-shop',
            'brand_name' => 'Revenue Brand',
            'phone' => '0909000333',
            'status' => SellerShop::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        $category = Category::create([
            'name' => 'Bach hoa online',
            'slug' => 'bach-hoa-online',
            'is_active' => true,
        ]);

        $product = Product::create([
            'seller_shop_id' => $shop->id,
            'name' => 'Nuoc mam Phu Quoc',
            'brand' => 'Phu Quoc',
            'product_type' => 'Bach hoa online',
            'price' => 175000,
            'stock_quantity' => 20,
            'focal_length' => 'Nuoc mam Phu Quoc',
            'max_aperture' => 'Phu Quoc',
            'mount_type' => 'Bach hoa online',
            'is_active' => true,
        ]);
        $product->categories()->sync([$category->id]);

        $order = Order::create([
            'user_id' => $customer->id,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'payment_method' => 'cod',
            'shipping_name' => 'Customer Buyer',
            'shipping_phone' => '0909123456',
            'shipping_email' => 'customer-buyer@example.com',
            'shipping_address' => 'Can Tho',
            'shipping_province' => 'Can Tho',
            'shipping_district' => 'Ninh Kieu',
            'shipping_method' => 'standard',
            'shipping_fee' => 0,
            'subtotal' => 350000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 350000,
        ]);
        OrderItem::createFromCameraLens($order, $product, 2, 175000);

        $this->actingAs($seller)
            ->get(route('seller.dashboard'))
            ->assertOk()
            ->assertSee('Doanh thu ghi nhận')
            ->assertSee('350.000 VN');

        $this->actingAs($seller)
            ->get(route('seller.orders.index'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Nuoc mam Phu Quoc');
    }
}
