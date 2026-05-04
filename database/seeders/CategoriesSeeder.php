<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('camera_lens_categories')->delete();
        Category::query()->delete();
        Schema::enableForeignKeyConstraints();

        foreach ($this->customerCategories() as $index => $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'sort_order' => $index + 1,
                'is_active' => true,
                'meta_title' => $category['name'] . ' - MienTayShop',
                'meta_description' => $category['description'],
            ]);
        }
    }

    private function customerCategories(): array
    {
        return [
            ['name' => 'Thời trang nam', 'icon' => 'fas fa-shirt', 'description' => 'Áo polo, áo thun, quần jean và phụ kiện thời trang nam.'],
            ['name' => 'Điện thoại & phụ kiện', 'icon' => 'fas fa-mobile-screen-button', 'description' => 'Điện thoại, tai nghe, sạc, ốp lưng và phụ kiện công nghệ cá nhân.'],
            ['name' => 'Thiết bị điện tử', 'icon' => 'fas fa-tv', 'description' => 'Tivi, loa bluetooth, thiết bị giải trí và đồ điện tử thông dụng.'],
            ['name' => 'Máy tính & Laptop', 'icon' => 'fas fa-laptop', 'description' => 'Laptop, máy tính bảng và phụ kiện làm việc học tập.'],
            ['name' => 'Máy ảnh & Máy quay phim', 'icon' => 'fas fa-camera', 'description' => 'Máy ảnh, máy quay, webcam và phụ kiện ghi hình.'],
            ['name' => 'Đồng hồ', 'icon' => 'fas fa-clock', 'description' => 'Đồng hồ thời trang, đồng hồ thông minh và phụ kiện đi kèm.'],
            ['name' => 'Giày dép nam', 'icon' => 'fas fa-shoe-prints', 'description' => 'Giày thể thao, giày công sở và dép nam.'],
            ['name' => 'Thiết bị điện gia dụng', 'icon' => 'fas fa-blender', 'description' => 'Nồi chiên, bếp mini, ấm siêu tốc và đồ điện gia dụng.'],
            ['name' => 'Thể thao & Du lịch', 'icon' => 'fas fa-futbol', 'description' => 'Đồ thể thao, balo, vali và phụ kiện du lịch.'],
            ['name' => 'Ô tô & Xe máy & Xe đạp', 'icon' => 'fas fa-motorcycle', 'description' => 'Phụ kiện xe máy, ô tô, xe đạp và đồ bảo dưỡng xe.'],
            ['name' => 'Thời trang nữ', 'icon' => 'fas fa-vest', 'description' => 'Đầm, váy, áo kiểu và phụ kiện thời trang nữ.'],
            ['name' => 'Mẹ & Bé', 'icon' => 'fas fa-baby', 'description' => 'Tã bỉm, sữa công thức, bình sữa và đồ dùng cho bé.'],
            ['name' => 'Nhà Cửa & Đời Sống', 'icon' => 'fas fa-house', 'description' => 'Đồ dùng nhà cửa, hộp đựng, bình nước và vật dụng đời sống.'],
            ['name' => 'Sắc Đẹp', 'icon' => 'fas fa-spa', 'description' => 'Mỹ phẩm, chăm sóc da, chăm sóc tóc và dụng cụ làm đẹp.'],
            ['name' => 'Sức Khỏe', 'icon' => 'fas fa-heart-pulse', 'description' => 'Khẩu trang, sát khuẩn, chăm sóc cá nhân và vật dụng y tế nhỏ.'],
            ['name' => 'Giày dép nữ', 'icon' => 'fas fa-shoe-prints', 'description' => 'Giày cao gót, giày búp bê, sneaker và dép nữ.'],
            ['name' => 'Túi Ví Nữ', 'icon' => 'fas fa-bag-shopping', 'description' => 'Túi xách, ví nữ và phụ kiện mang theo hằng ngày.'],
            ['name' => 'Phụ Kiện & Trang Sức Nữ', 'icon' => 'fas fa-gem', 'description' => 'Dây chuyền, vòng tay, bông tai và phụ kiện trang sức.'],
            ['name' => 'Bách Hóa Online', 'icon' => 'fas fa-basket-shopping', 'description' => 'Bánh kẹo, mì gói, sữa, ngũ cốc và hàng tiêu dùng nhanh.'],
            ['name' => 'Nhà Sách Online', 'icon' => 'fas fa-book', 'description' => 'Sách, vở tập, bút viết, giấy in và văn phòng phẩm.'],
        ];
    }
}
