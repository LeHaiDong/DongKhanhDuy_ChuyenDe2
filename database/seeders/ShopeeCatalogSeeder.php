<?php

namespace Database\Seeders;

use App\Models\CameraLens;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopeeCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Thời trang nam', 'fas fa-shirt'],
            ['Điện thoại & phụ kiện', 'fas fa-mobile-screen-button'],
            ['Thiết bị điện tử', 'fas fa-tv'],
            ['Máy tính & Laptop', 'fas fa-laptop'],
            ['Máy ảnh & Máy quay phim', 'fas fa-camera'],
            ['Đồng hồ', 'fas fa-clock'],
            ['Giày dép nam', 'fas fa-shoe-prints'],
            ['Thiết bị điện gia dụng', 'fas fa-blender'],
            ['Thể thao & Du lịch', 'fas fa-volleyball'],
            ['Ô tô & Xe máy & Xe đạp', 'fas fa-motorcycle'],
            ['Thời trang nữ', 'fas fa-person-dress'],
            ['Mẹ & bé', 'fas fa-baby'],
            ['Nhà cửa & Đời sống', 'fas fa-house'],
            ['Sắc đẹp', 'fas fa-wand-magic-sparkles'],
            ['Sức khỏe', 'fas fa-heart-pulse'],
            ['Giày dép nữ', 'fas fa-shoe-prints'],
            ['Túi ví nữ', 'fas fa-bag-shopping'],
            ['Phụ kiện & Trang sức nữ', 'fas fa-gem'],
            ['Bách hóa online', 'fas fa-basket-shopping'],
            ['Nhà sách online', 'fas fa-book-open'],
        ];

        Category::whereNull('parent_id')
            ->whereNotIn('name', array_column($categories, 0))
            ->update(['sort_order' => 100]);

        foreach ($categories as $index => [$name, $icon]) {
            Category::updateOrCreate(
                ['name' => $name],
                [
                    'slug' => Str::slug($name),
                    'description' => $name . ' trong danh mục Shopee demo.',
                    'icon' => $icon,
                    'parent_id' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }

        foreach ($this->products() as $index => $item) {
            $product = CameraLens::updateOrCreate(
                ['name' => $item['name']],
                [
                    'brand' => $item['brand'],
                    'product_type' => $item['category'],
                    'spec_label_1' => 'Tên',
                    'spec_label_2' => 'Thương hiệu',
                    'spec_label_3' => 'Phân loại',
                    'focal_length' => $item['name'],
                    'max_aperture' => $item['brand'],
                    'mount_type' => $item['category'],
                    'price' => $item['price'],
                    'cost_price' => (int) round($item['price'] * 0.68),
                    'stock_quantity' => $item['stock'] ?? 20,
                    'condition' => 'new',
                    'is_active' => true,
                    'sku' => $item['sku'] ?? sprintf('SHP-%04d', $index + 1),
                    'supplier' => $item['brand'] . ' Official Store',
                    'description' => $item['name'] . ' - sản phẩm thuộc danh mục ' . $item['category'] . ', phù hợp để demo bán hàng đa ngành.',
                    'search_keywords' => implode(', ', [$item['name'], $item['brand'], $item['category'], $item['keywords'] ?? '']),
                    'image' => null,
                ]
            );

            $category = Category::where('name', $item['category'])->first();
            if ($category) {
                $product->categories()->syncWithoutDetaching([$category->id]);
            }
        }
    }

    private function products(): array
    {
        return [
            ['name' => 'Áo polo nam Telab', 'brand' => 'Telab', 'category' => 'Thời trang nam', 'price' => 189000, 'keywords' => 'ao polo nam'],
            ['name' => 'Áo thun nam Coolmate', 'brand' => 'Coolmate', 'category' => 'Thời trang nam', 'price' => 159000, 'keywords' => 'ao thun nam'],
            ['name' => 'Quần jean nam Routine', 'brand' => 'Routine', 'category' => 'Thời trang nam', 'price' => 429000, 'keywords' => 'quan jean nam'],
            ['name' => 'Đầm nữ Maybi', 'brand' => 'Maybi', 'category' => 'Thời trang nữ', 'price' => 349000, 'keywords' => 'dam nu vay nu'],
            ['name' => 'Chân váy nữ Hnoss', 'brand' => 'Hnoss', 'category' => 'Thời trang nữ', 'price' => 299000, 'keywords' => 'chan vay nu'],
            ['name' => 'Giày sneaker nữ Juno', 'brand' => 'Juno', 'category' => 'Giày dép nữ', 'price' => 599000, 'keywords' => 'giay nu sneaker'],
            ['name' => 'Giày thể thao nam Biti’s Hunter', 'brand' => 'Biti’s', 'category' => 'Giày dép nam', 'price' => 799000, 'keywords' => 'giay nam the thao'],
            ['name' => 'Túi xách nữ Juno', 'brand' => 'Juno', 'category' => 'Túi ví nữ', 'price' => 549000, 'keywords' => 'tui xach nu'],
            ['name' => 'Vòng tay nữ PNJ', 'brand' => 'PNJ', 'category' => 'Phụ kiện & Trang sức nữ', 'price' => 690000, 'keywords' => 'trang suc vong tay'],
            ['name' => 'iPhone 14 128GB', 'brand' => 'Apple', 'category' => 'Điện thoại & phụ kiện', 'price' => 15490000, 'keywords' => 'iphone 14 ip14 dien thoai'],
            ['name' => 'Ốp lưng iPhone 14 UAG', 'brand' => 'UAG', 'category' => 'Điện thoại & phụ kiện', 'price' => 450000, 'keywords' => 'op lung iphone'],
            ['name' => 'Tai nghe Sony WH-CH720N', 'brand' => 'Sony', 'category' => 'Thiết bị điện tử', 'price' => 2490000, 'keywords' => 'tai nghe sony'],
            ['name' => 'Tivi Samsung 43 inch', 'brand' => 'Samsung', 'category' => 'Thiết bị điện tử', 'price' => 6990000, 'keywords' => 'tivi tv samsung'],
            ['name' => 'Laptop Asus Vivobook 14', 'brand' => 'Asus', 'category' => 'Máy tính & Laptop', 'price' => 11990000, 'keywords' => 'laptop asus'],
            ['name' => 'Laptop Dell Inspiron 15', 'brand' => 'Dell', 'category' => 'Máy tính & Laptop', 'price' => 13990000, 'keywords' => 'laptop dell'],
            ['name' => 'Máy ảnh Canon EOS M50', 'brand' => 'Canon', 'category' => 'Máy ảnh & Máy quay phim', 'price' => 12990000, 'keywords' => 'may anh canon'],
            ['name' => 'Máy quay Sony Handycam', 'brand' => 'Sony', 'category' => 'Máy ảnh & Máy quay phim', 'price' => 8990000, 'keywords' => 'may quay sony'],
            ['name' => 'Đồng hồ Casio MTP', 'brand' => 'Casio', 'category' => 'Đồng hồ', 'price' => 1290000, 'keywords' => 'dong ho casio'],
            ['name' => 'Đồng hồ thông minh Xiaomi Band', 'brand' => 'Xiaomi', 'category' => 'Đồng hồ', 'price' => 990000, 'keywords' => 'smartwatch dong ho'],
            ['name' => 'Nồi chiên không dầu LocknLock', 'brand' => 'LocknLock', 'category' => 'Thiết bị điện gia dụng', 'price' => 1890000, 'keywords' => 'noi chien khong dau'],
            ['name' => 'Bình sữa Pigeon 160ml', 'brand' => 'Pigeon', 'category' => 'Mẹ & bé', 'price' => 149000, 'keywords' => 'binh sua me va be'],
            ['name' => 'Sữa bột Abbott Grow', 'brand' => 'Abbott', 'category' => 'Mẹ & bé', 'price' => 545000, 'keywords' => 'sua bot cho be'],
            ['name' => 'Bình giữ nhiệt LocknLock', 'brand' => 'LocknLock', 'category' => 'Nhà cửa & Đời sống', 'price' => 359000, 'keywords' => 'binh giu nhiet'],
            ['name' => 'Son kem Black Rouge', 'brand' => 'Black Rouge', 'category' => 'Sắc đẹp', 'price' => 179000, 'keywords' => 'son kem sac dep'],
            ['name' => 'Kem chống nắng Anessa', 'brand' => 'Anessa', 'category' => 'Sắc đẹp', 'price' => 459000, 'keywords' => 'kem chong nang'],
            ['name' => 'Khẩu trang 3M KF94', 'brand' => '3M', 'category' => 'Sức khỏe', 'price' => 59000, 'keywords' => 'khau trang 3m'],
            ['name' => 'Bóng đá Động Lực', 'brand' => 'Động Lực', 'category' => 'Thể thao & Du lịch', 'price' => 259000, 'keywords' => 'bong da the thao'],
            ['name' => 'Vali du lịch Sakos', 'brand' => 'Sakos', 'category' => 'Thể thao & Du lịch', 'price' => 1290000, 'keywords' => 'vali du lich'],
            ['name' => 'Mũ bảo hiểm Andes', 'brand' => 'Andes', 'category' => 'Ô tô & Xe máy & Xe đạp', 'price' => 390000, 'keywords' => 'mu bao hiem xe may'],
            ['name' => 'Bánh Oreo hộp', 'brand' => 'Oreo', 'category' => 'Bách hóa online', 'price' => 42000, 'keywords' => 'banh oreo bach hoa'],
            ['name' => 'Mì Hảo Hảo tôm chua cay', 'brand' => 'Acecook', 'category' => 'Bách hóa online', 'price' => 4500, 'keywords' => 'mi hao hao'],
            ['name' => 'Sách Đắc Nhân Tâm', 'brand' => 'First News', 'category' => 'Nhà sách online', 'price' => 86000, 'keywords' => 'sach dac nhan tam'],
            ['name' => 'Sách Nhà Giả Kim', 'brand' => 'Nhã Nam', 'category' => 'Nhà sách online', 'price' => 79000, 'keywords' => 'sach nha gia kim'],
        ];
    }
}
