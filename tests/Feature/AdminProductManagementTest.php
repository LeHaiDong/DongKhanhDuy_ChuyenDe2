<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_create_product_with_only_essential_fields()
    {
        $admin = User::create([
            'name' => 'Admin Demo',
            'email' => 'admin-product@example.com',
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        $category = Category::create([
            'name' => 'Điện thoại & phụ kiện',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.products.store'), [
            'name' => 'iPhone 14 128GB',
            'brand' => 'Apple',
            'product_type' => 'Điện thoại',
            'price' => 18990000,
            'stock_quantity' => 8,
            'condition' => 'new',
            'category_id' => $category->id,
            'description' => 'Máy mới, bảo hành chính hãng.',
            'is_active' => 1,
            'image' => UploadedFile::fake()->createWithContent(
                'iphone-14.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
            ),
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::first();

        $this->assertNotNull($product);
        $this->assertSame('iPhone 14 128GB', $product->name);
        $this->assertSame('Apple', $product->brand);
        $this->assertSame('Điện thoại', $product->product_type);
        $this->assertSame('Tên sản phẩm', $product->spec_label_1);
        $this->assertSame('Thương hiệu', $product->spec_label_2);
        $this->assertSame('Phân loại', $product->spec_label_3);
        $this->assertStringStartsWith('uploads/products/', $product->image);
        $this->assertFileExists(public_path($product->image));
        $this->assertDatabaseHas('camera_lens_categories', [
            'camera_lens_id' => $product->id,
            'category_id' => $category->id,
        ]);

        @unlink(public_path($product->image));
    }

    /** @test */
    public function admin_can_open_product_interaction_page()
    {
        $admin = User::create([
            'name' => 'Admin Favorite',
            'email' => 'admin-favorite@example.com',
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        $customer = User::create([
            'name' => 'Khach Mua Hang',
            'email' => 'customer-favorite@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);

        $product = Product::create([
            'name' => 'Tai nghe Sony WH-CH520',
            'brand' => 'Sony',
            'product_type' => 'Tai nghe',
            'price' => 990000,
            'stock_quantity' => 12,
            'focal_length' => 'Tai nghe Sony WH-CH520',
            'max_aperture' => 'Sony',
            'mount_type' => 'Tai nghe',
            'is_active' => true,
        ]);

        Favorite::create([
            'user_id' => $customer->id,
            'camera_lens_id' => $product->id,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.favorites.index'));

        $response->assertOk();
        $response->assertSee('Tương tác sản phẩm');
        $response->assertSee('Tai nghe Sony WH-CH520');
    }

    /** @test */
    public function admin_can_update_category_image()
    {
        $admin = User::create([
            'name' => 'Admin Category',
            'email' => 'admin-category@example.com',
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        $category = Category::create([
            'name' => 'Bánh kẹo',
            'slug' => 'banh-keo',
            'description' => 'Snack va do an vat.',
            'icon' => 'fas fa-cookie-bite',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.categories.update', $category), [
            'name' => 'Bánh kẹo',
            'slug' => 'banh-keo',
            'description' => 'Snack va do an vat.',
            'icon' => 'fas fa-cookie-bite',
            'sort_order' => 0,
            'is_active' => 1,
            'image' => UploadedFile::fake()->createWithContent(
                'banh-keo.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
            ),
        ]);

        $response->assertRedirect(route('admin.categories.index'));

        $category->refresh();

        $this->assertStringStartsWith('uploads/categories/', $category->image);
        $this->assertFileExists(public_path($category->image));
        $this->assertStringContainsString('/uploads/categories/', $category->display_image_url);

        @unlink(public_path($category->image));
    }

    /** @test */
    public function admin_category_index_defaults_to_customer_root_categories()
    {
        $admin = User::create([
            'name' => 'Admin Root Category',
            'email' => 'admin-root-category@example.com',
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        $root = Category::create([
            'name' => 'Root Demo',
            'slug' => 'root-demo',
            'is_active' => true,
        ]);

        $child = Category::create([
            'name' => 'Child Demo',
            'slug' => 'child-demo',
            'parent_id' => $root->id,
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Demo Child Product',
            'brand' => 'Demo',
            'product_type' => 'Demo',
            'price' => 100000,
            'stock_quantity' => 5,
            'focal_length' => 'Demo Child Product',
            'max_aperture' => 'Demo',
            'mount_type' => 'Demo',
            'is_active' => true,
        ]);
        $product->categories()->attach($child->id);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.categories.index'));

        $response->assertOk();
        $response->assertSee('Root Demo');
        $response->assertSee('1 sản phẩm');
        $response->assertDontSee('Child Demo');

        $allCategoriesResponse = $this->actingAs($admin, 'admin')->get(route('admin.categories.index', [
            'parent' => 'all',
        ]));

        $allCategoriesResponse->assertOk();
        $allCategoriesResponse->assertSee('Child Demo');
    }

    /** @test */
    public function customer_home_category_count_includes_child_category_products()
    {
        $root = Category::create([
            'name' => 'Root Customer Demo',
            'slug' => 'root-customer-demo',
            'is_active' => true,
        ]);

        $child = Category::create([
            'name' => 'Child Customer Demo',
            'slug' => 'child-customer-demo',
            'parent_id' => $root->id,
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Customer Child Product',
            'brand' => 'Demo',
            'product_type' => 'Demo',
            'price' => 100000,
            'stock_quantity' => 5,
            'focal_length' => 'Customer Child Product',
            'max_aperture' => 'Demo',
            'mount_type' => 'Demo',
            'is_active' => true,
        ]);
        $product->categories()->attach($child->id);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Root Customer Demo');
        $response->assertSee('1 sản phẩm');
    }
}
