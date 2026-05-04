<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Support\ProductCategoryClassifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NormalizeProductCategoriesCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_moves_products_to_a_single_correct_visible_category()
    {
        foreach (ProductCategoryClassifier::visibleCategorySlugs() as $index => $slug) {
            Category::create([
                'name' => $slug,
                'slug' => $slug,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }

        $electronics = Category::where('slug', 'thiet-bi-dien-tu')->firstOrFail();
        $health = Category::where('slug', 'suc-khoe')->firstOrFail();
        $phone = Category::where('slug', 'dien-thoai-phu-kien')->firstOrFail();

        $mask = Product::create([
            'name' => '3M KF94 10 Cai',
            'brand' => '3M',
            'product_type' => 'Khau trang',
            'price' => 59000,
            'stock_quantity' => 12,
            'focal_length' => 'Khau trang 3M',
            'max_aperture' => '3M',
            'mount_type' => 'Khau trang',
            'search_keywords' => 'khau trang, 3m, y te',
            'is_active' => true,
        ]);
        $mask->categories()->sync([$electronics->id, $health->id]);

        $earbuds = Product::create([
            'name' => 'AirPods 4',
            'brand' => 'Apple',
            'product_type' => 'Tai nghe TWS',
            'price' => 4290000,
            'stock_quantity' => 10,
            'focal_length' => 'AirPods 4',
            'max_aperture' => 'Apple',
            'mount_type' => 'Tai nghe',
            'search_keywords' => 'airpods, tai nghe, apple',
            'is_active' => true,
        ]);
        $earbuds->categories()->sync([$electronics->id, $phone->id]);

        $this->artisan('products:normalize-categories')
            ->assertExitCode(0);

        $this->assertSame([(int) $health->id], $mask->fresh()->categories()->pluck('categories.id')->map(fn ($id) => (int) $id)->sort()->values()->all());
        $this->assertSame([(int) $electronics->id], $earbuds->fresh()->categories()->pluck('categories.id')->map(fn ($id) => (int) $id)->sort()->values()->all());
    }
}
