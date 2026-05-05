<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_product_management_routes_redirect_to_system_dashboard()
    {
        $admin = $this->makeAdmin('admin-product@example.com');

        $category = Category::create([
            'name' => 'Dien thoai va phu kien',
            'slug' => 'dien-thoai-va-phu-kien',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.index'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin, 'admin')
            ->post(route('admin.products.store'), [
                'name' => 'iPhone 14 128GB',
                'brand' => 'Apple',
                'product_type' => 'Dien thoai',
                'price' => 18990000,
                'stock_quantity' => 8,
                'condition' => 'new',
                'category_id' => $category->id,
                'description' => 'May moi, bao hanh chinh hang.',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseMissing('camera_lenses', [
            'name' => 'iPhone 14 128GB',
        ]);
    }

    /** @test */
    public function admin_product_interaction_pages_redirect_to_system_dashboard()
    {
        $admin = $this->makeAdmin('admin-favorite@example.com');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.favorites.index'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.favorites.analytics'))
            ->assertRedirect(route('admin.dashboard'));
    }

    /** @test */
    public function admin_can_update_category_image()
    {
        $admin = $this->makeAdmin('admin-category@example.com');

        $category = Category::create([
            'name' => 'Banh keo',
            'slug' => 'banh-keo',
            'description' => 'Snack va do an vat.',
            'icon' => 'fas fa-cookie-bite',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.categories.update', $category), [
            'name' => 'Banh keo',
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
        $admin = $this->makeAdmin('admin-root-category@example.com');

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

    private function makeAdmin(string $email): User
    {
        return User::create([
            'name' => 'Administrator',
            'email' => $email,
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);
    }
}
