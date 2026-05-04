<?php

namespace Tests\Unit;

use App\Support\ProductCategoryClassifier;
use PHPUnit\Framework\TestCase;

class ProductCategoryClassifierTest extends TestCase
{
    /** @test */
    public function it_classifies_products_by_product_type_before_keyword_overlap()
    {
        $classifier = new ProductCategoryClassifier();

        $this->assertSame('suc-khoe', $classifier->classify([
            'name' => '3M KF94 10 Cai',
            'brand' => '3M',
            'product_type' => 'Khau trang',
            'search_keywords' => 'khau trang, 3m, y te',
        ]));

        $this->assertSame('thiet-bi-dien-tu', $classifier->classify([
            'name' => 'AirPods 4',
            'brand' => 'Apple',
            'product_type' => 'Tai nghe TWS',
            'search_keywords' => 'airpods, tai nghe, apple',
        ]));

        $this->assertSame('me-be', $classifier->classify([
            'name' => 'Similac Eye-Q So 3 900g',
            'brand' => 'Abbott',
            'product_type' => 'Sua cong thuc',
            'search_keywords' => 'similac, sua cong thuc, tre nho',
        ]));

        $this->assertSame('nha-sach-online', $classifier->classify([
            'name' => 'But bi Thien Long TL-027',
            'brand' => 'Thien Long',
            'product_type' => 'Van phong pham',
            'search_keywords' => 'but, van phong pham, hoc tap',
        ]));
    }

    /** @test */
    public function it_uses_product_keywords_when_the_product_type_is_too_generic()
    {
        $classifier = new ProductCategoryClassifier();

        $this->assertSame('bach-hoa-online', $classifier->classify([
            'name' => 'Mi De Nhat',
            'brand' => 'ACoooK',
            'product_type' => 'Gia dung',
            'search_keywords' => 'Mi De Nhat, ACoooK, Gia dung, Bach Hoa Online',
        ]));
    }
}
