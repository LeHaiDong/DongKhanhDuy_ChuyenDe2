<?php

namespace Database\Seeders;

use App\Models\CameraLens;
use App\Models\Category;
use App\Support\ProductCategoryClassifier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ShopeeCategoryExpansionSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::whereIn('slug', ProductCategoryClassifier::visibleCategorySlugs())
            ->get()
            ->keyBy('slug');

        $this->attachExistingProducts($categories, app(ProductCategoryClassifier::class));
        $this->createExtraProducts($categories);
    }

    private function attachExistingProducts(Collection $categories, ProductCategoryClassifier $classifier): void
    {
        CameraLens::with('categories')->chunkById(100, function ($products) use ($categories, $classifier) {
            foreach ($products as $product) {
                $category = $categories->get($classifier->classify($product));

                if ($category) {
                    $product->categories()->syncWithoutDetaching([$category->id]);
                }
            }
        });
    }

    private function createExtraProducts(Collection $categories): void
    {
        foreach ($this->extraProducts() as $categoryName => $products) {
            $category = $categories->get(Str::slug($categoryName));

            if (!$category) {
                continue;
            }

            foreach ($products as $index => $item) {
                [$name, $brand, $price] = $item;

                $product = CameraLens::updateOrCreate(
                    ['name' => $name],
                    [
                        'brand' => $brand,
                        'product_type' => $categoryName,
                        'spec_label_1' => 'Tên',
                        'spec_label_2' => 'Thương hiệu',
                        'spec_label_3' => 'Phân loại',
                        'focal_length' => $name,
                        'max_aperture' => $brand,
                        'mount_type' => $categoryName,
                        'price' => $price,
                        'cost_price' => (int) round($price * 0.68),
                        'stock_quantity' => 18 + (($index % 8) * 4),
                        'condition' => 'new',
                        'is_active' => true,
                        'sku' => 'MH-' . strtoupper(substr(md5($name), 0, 8)),
                        'supplier' => $brand . ' Official Store',
                        'description' => $name . ' thuộc danh mục ' . $categoryName . ', dùng để demo cửa hàng đa ngành giống Shopee.',
                        'search_keywords' => implode(', ', [$name, $brand, $categoryName, Str::ascii($name)]),
                    ]
                );

                $product->categories()->syncWithoutDetaching([$category->id]);
            }
        }
    }

    private function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $value): string
    {
        return Str::of($value)->ascii()->lower()->squish()->value();
    }

    private function categoryRules(): array
    {
        return [
            'Thời trang nam' => ['ao polo', 'ao thun nam', 'quan jean nam', 'so mi nam', 'thoi trang nam', 'coolmate', 'telab', 'routine'],
            'Điện thoại & phụ kiện' => ['dien thoai', 'smartphone', 'iphone', 'galaxy', 'redmi', 'oppo', 'realme', 'op lung', 'cap sac', 'cu sac', 'pin du phong'],
            'Thiết bị điện tử' => ['tai nghe', 'headphone', 'airpods', 'buds', 'loa', 'speaker', 'tivi', 'tv', 'bluetooth', 'webcam', 'micro thu am'],
            'Máy tính & Laptop' => ['laptop', 'may tinh', 'macbook', 'asus', 'dell', 'lenovo', 'hp pavilion', 'acer', 'ban phim', 'chuot', 'ssd', 'ram'],
            'Máy ảnh & Máy quay phim' => ['may anh', 'may quay', 'camera', 'canon eos', 'sony handycam', 'gopro', 'dji osmo', 'tripod'],
            'Đồng hồ' => ['dong ho', 'watch', 'casio', 'smart band', 'xiaomi band', 'garmin', 'orient'],
            'Giày dép nam' => ['giay the thao nam', 'giay dep nam', 'giay nam', 'dep nam', 'biti', 'men shoes'],
            'Thiết bị điện gia dụng' => ['gia dung', 'noi chien', 'noi com', 'am sieu toc', 'may xay', 'may hut bui', 'ban ui', 'bep tu', 'may say toc'],
            'Thể thao & Du lịch' => ['the thao', 'bong da', 'du lich', 'vali', 'balo du lich', 'yoga', 'cau long', 'leu cam trai', 'travel'],
            'Ô tô & Xe máy & Xe đạp' => ['xe may', 'xe dap', 'o to', 'mu bao hiem', 'helmet', 'camera hanh trinh', 'bom lop', 'ao mua'],
            'Thời trang nữ' => ['thoi trang nu', 'dam nu', 'vay nu', 'chan vay', 'ao kieu nu', 'blazer nu', 'croptop'],
            'Mẹ & bé' => ['me va be', 'ta bim', 'bim', 'em be', 'baby', 'pigeon', 'merries', 'goon', 'huggies', 'pampers', 'aptamil', 'binh sua'],
            'Nhà cửa & Đời sống' => ['nha cua', 'doi song', 'binh giu nhiet', 'hop dung', 'hop com', 'den ban', 'ke sach', 'chan ga', 'cay lau nha'],
            'Sắc đẹp' => ['sac dep', 'son', 'kem chong nang', 'skincare', 'serum', 'sua rua mat', 'mascara', 'mat na', 'bioderma', 'cetaphil'],
            'Sức khỏe' => ['suc khoe', 'khau trang', '3m', 'n95', 'kf94', 'sat khuan', 'rua tay', 'dettol', 'lifebuoy', 'y te', 'vitamin', 'omron'],
            'Giày dép nữ' => ['giay sneaker nu', 'giay dep nu', 'giay nu', 'sandal nu', 'cao got', 'bup be', 'mary jane'],
            'Túi ví nữ' => ['tui xach', 'tui vi', 'vi nu', 'tui deo cheo', 'tui tote', 'handbag', 'bag'],
            'Phụ kiện & Trang sức nữ' => ['trang suc', 'vong tay', 'bong tai', 'day chuyen', 'nhan nu', 'kep toc', 'pnj', 'bracelet'],
            'Bách hóa online' => ['bach hoa', 'banh', 'snack', 'keo', 'socola', 'mi ', 'hao hao', 'acecook', 'oreo', 'sua tuoi', 'sua hat', 'ngu coc', 'ca phe', 'dau an', 'bot giat'],
            'Nhà sách online' => ['sach', 'book', 'van phong pham', 'but', 'vo tap', 'giay in', 'casio fx', 'pilot', 'pentel', 'thien long', 'deli', 'notebook', 'flashcard', 'hoc tap'],
        ];
    }

    private function extraProducts(): array
    {
        return [
            'Thời trang nam' => [
                ['Áo sơ mi nam Việt Tiến', 'Việt Tiến', 329000],
                ['Quần kaki nam Owen', 'Owen', 459000],
                ['Áo khoác nam Yody', 'Yody', 399000],
                ['Quần short nam Coolmate', 'Coolmate', 199000],
                ['Áo polo nam Routine', 'Routine', 249000],
                ['Bộ đồ thể thao nam Nike', 'Nike', 799000],
                ['Áo hoodie nam 5S Fashion', '5S Fashion', 369000],
                ['Quần jogger nam Adidas', 'Adidas', 699000],
            ],
            'Điện thoại & phụ kiện' => [
                ['iPhone 15 128GB', 'Apple', 19990000],
                ['Samsung Galaxy A55 5G', 'Samsung', 8990000],
                ['Xiaomi Redmi Note 13 Pro', 'Xiaomi', 7290000],
                ['OPPO Reno12 F', 'OPPO', 6490000],
                ['Cáp sạc Anker USB-C', 'Anker', 190000],
                ['Pin dự phòng Baseus 10000mAh', 'Baseus', 490000],
                ['Ốp lưng Samsung S24 UAG', 'UAG', 590000],
                ['Củ sạc Apple 20W', 'Apple', 490000],
            ],
            'Thiết bị điện tử' => [
                ['Tai nghe AirPods Pro 2 USB-C', 'Apple', 5790000],
                ['Loa JBL Flip 6', 'JBL', 2390000],
                ['Máy đọc sách Kindle Paperwhite', 'Amazon', 3490000],
                ['Camera an ninh Xiaomi C400', 'Xiaomi', 890000],
                ['Chuột Logitech MX Master 3S', 'Logitech', 2290000],
                ['Bàn phím Logitech K380', 'Logitech', 690000],
                ['Webcam Logitech C920', 'Logitech', 1690000],
                ['Micro thu âm Fifine K669', 'Fifine', 790000],
            ],
            'Máy tính & Laptop' => [
                ['MacBook Air M2 13 inch', 'Apple', 22990000],
                ['Lenovo IdeaPad Slim 3', 'Lenovo', 10990000],
                ['HP Pavilion 15', 'HP', 12990000],
                ['Acer Aspire 7', 'Acer', 14990000],
                ['ASUS TUF Gaming F15', 'ASUS', 18990000],
                ['Màn hình Dell 24 inch', 'Dell', 3190000],
                ['Ổ cứng SSD Samsung 1TB', 'Samsung', 1790000],
                ['RAM Kingston Fury 16GB', 'Kingston', 990000],
            ],
            'Máy ảnh & Máy quay phim' => [
                ['Sony ZV-E10', 'Sony', 16990000],
                ['Fujifilm Instax Mini 12', 'Fujifilm', 2190000],
                ['Canon EOS R50', 'Canon', 18490000],
                ['GoPro Hero 12', 'GoPro', 9490000],
                ['DJI Osmo Action 4', 'DJI', 8290000],
                ['Thẻ nhớ Sandisk 128GB', 'Sandisk', 390000],
                ['Tripod Ulanzi MT-44', 'Ulanzi', 590000],
                ['Túi máy ảnh Lowepro', 'Lowepro', 890000],
            ],
            'Đồng hồ' => [
                ['Apple Watch SE 2', 'Apple', 6290000],
                ['Samsung Galaxy Watch6', 'Samsung', 4990000],
                ['Casio G-Shock GA-2100', 'Casio', 2690000],
                ['Orient Bambino', 'Orient', 4590000],
                ['Đồng hồ nữ Daniel Wellington', 'Daniel Wellington', 3790000],
                ['Garmin Forerunner 55', 'Garmin', 4490000],
                ['Xiaomi Redmi Watch 4', 'Xiaomi', 2290000],
                ['Dây đồng hồ Apple Watch', 'Apple', 290000],
            ],
            'Giày dép nam' => [
                ['Giày Nike Revolution 7 nam', 'Nike', 1690000],
                ['Giày Adidas Runfalcon nam', 'Adidas', 1490000],
                ['Dép quai ngang Nike nam', 'Nike', 690000],
                ['Giày lười nam Pedro', 'Pedro', 1890000],
                ['Giày tây nam An Phước', 'An Phước', 2190000],
                ['Giày sandal nam Vento', 'Vento', 490000],
                ['Giày đá bóng Kamito', 'Kamito', 790000],
                ['Dép Crocs Classic nam', 'Crocs', 1090000],
            ],
            'Thiết bị điện gia dụng' => [
                ['Máy xay sinh tố Philips', 'Philips', 990000],
                ['Nồi cơm điện Toshiba 1.8L', 'Toshiba', 1590000],
                ['Máy hút bụi Deerma DX700', 'Deerma', 890000],
                ['Bàn ủi hơi nước Philips', 'Philips', 790000],
                ['Máy lọc không khí Xiaomi', 'Xiaomi', 2990000],
                ['Ấm siêu tốc Sunhouse', 'Sunhouse', 290000],
                ['Bếp từ Kangaroo', 'Kangaroo', 1190000],
                ['Máy sấy tóc Panasonic', 'Panasonic', 690000],
            ],
            'Thể thao & Du lịch' => [
                ['Balo du lịch Naturehike', 'Naturehike', 890000],
                ['Vợt cầu lông Yonex', 'Yonex', 1290000],
                ['Thảm yoga Adidas', 'Adidas', 590000],
                ['Bình nước thể thao LocknLock', 'LocknLock', 190000],
                ['Kính bơi View', 'View', 390000],
                ['Lều cắm trại Naturehike', 'Naturehike', 2490000],
                ['Túi ngủ du lịch Trackman', 'Trackman', 690000],
                ['Giày chạy bộ Puma', 'Puma', 1590000],
            ],
            'Ô tô & Xe máy & Xe đạp' => [
                ['Nón bảo hiểm Royal M139', 'Royal', 590000],
                ['Găng tay xe máy Scoyco', 'Scoyco', 390000],
                ['Camera hành trình Vietmap', 'Vietmap', 2490000],
                ['Bơm lốp mini Xiaomi', 'Xiaomi', 890000],
                ['Khóa chống trộm xe máy Việt Tiệp', 'Việt Tiệp', 190000],
                ['Áo mưa bộ Givi', 'Givi', 690000],
                ['Đèn xe đạp Rockbros', 'Rockbros', 350000],
                ['Giá đỡ điện thoại xe máy Baseus', 'Baseus', 250000],
            ],
            'Thời trang nữ' => [
                ['Áo kiểu nữ Elise', 'Elise', 399000],
                ['Váy hoa nữ Hnoss', 'Hnoss', 359000],
                ['Quần jean nữ Gumac', 'Gumac', 429000],
                ['Áo blazer nữ IVY Moda', 'IVY Moda', 899000],
                ['Áo croptop nữ Totoshop', 'Totoshop', 189000],
                ['Đầm công sở Nem', 'Nem', 790000],
                ['Quần culottes nữ Maybi', 'Maybi', 329000],
                ['Áo cardigan nữ Len Clothing', 'Len Clothing', 359000],
            ],
            'Mẹ & bé' => [
                ['Tã Bobby M60', 'Bobby', 245000],
                ['Tã Huggies Platinum M54', 'Huggies', 329000],
                ['Sữa Aptamil số 3 800g', 'Aptamil', 735000],
                ['Sữa NAN Optipro 3 800g', 'Nestle', 499000],
                ['Bình sữa Avent 260ml', 'Avent', 269000],
                ['Khăn ướt Mamamy 100 tờ', 'Mamamy', 39000],
                ['Xe đẩy em bé Zaracos', 'Zaracos', 2390000],
                ['Ghế ăn dặm Mastela', 'Mastela', 890000],
            ],
            'Nhà cửa & Đời sống' => [
                ['Hộp đựng thực phẩm Inochi', 'Inochi', 89000],
                ['Bộ ga gối Everon', 'Everon', 1290000],
                ['Kệ sách lắp ghép Tashuan', 'Tashuan', 390000],
                ['Đèn bàn Xiaomi', 'Xiaomi', 690000],
                ['Bộ chăn ga Cotton', 'Cotton House', 890000],
                ['Chảo chống dính Elmich', 'Elmich', 450000],
                ['Cây lau nhà LocknLock', 'LocknLock', 390000],
                ['Hộp cơm giữ nhiệt Zojirushi', 'Zojirushi', 790000],
            ],
            'Sắc đẹp' => [
                ['Son Maybelline SuperStay', 'Maybelline', 219000],
                ['Nước tẩy trang Bioderma', 'Bioderma', 399000],
                ['Sữa rửa mặt Cetaphil', 'Cetaphil', 259000],
                ['Serum The Ordinary Niacinamide', 'The Ordinary', 329000],
                ['Kem dưỡng Hada Labo', 'Hada Labo', 249000],
                ['Phấn nước Aprilskin', 'Aprilskin', 390000],
                ['Mascara Maybelline', 'Maybelline', 189000],
                ['Mặt nạ Mediheal', 'Mediheal', 25000],
            ],
            'Sức khỏe' => [
                ['Nước rửa tay Lifebuoy', 'Lifebuoy', 85000],
                ['Dung dịch sát khuẩn Dettol', 'Dettol', 99000],
                ['Máy đo huyết áp Omron', 'Omron', 890000],
                ['Nhiệt kế điện tử Microlife', 'Microlife', 390000],
                ['Vitamin C DHC', 'DHC', 180000],
                ['Viên uống Blackmores', 'Blackmores', 390000],
                ['Khẩu trang Unicharm 3D', 'Unicharm', 69000],
                ['Băng cá nhân Urgo', 'Urgo', 25000],
            ],
            'Giày dép nữ' => [
                ['Giày cao gót Juno', 'Juno', 599000],
                ['Sandal nữ Biti\'s', 'Biti\'s', 459000],
                ['Giày búp bê Vascara', 'Vascara', 699000],
                ['Sneaker nữ Adidas Court', 'Adidas', 1490000],
                ['Dép nữ Crocs Classic', 'Crocs', 1090000],
                ['Giày Mary Jane nữ Sablanca', 'Sablanca', 590000],
                ['Giày thể thao nữ Nike Downshifter', 'Nike', 1590000],
                ['Dép quai ngang nữ Erosska', 'Erosska', 299000],
            ],
            'Túi ví nữ' => [
                ['Ví nữ Vascara', 'Vascara', 499000],
                ['Túi tote nữ Canvas', 'Canvas', 159000],
                ['Túi đeo chéo Charles & Keith', 'Charles & Keith', 1590000],
                ['Balo nữ Sakos', 'Sakos', 890000],
                ['Túi xách công sở Juno', 'Juno', 749000],
                ['Ví cầm tay nữ Pedro', 'Pedro', 1290000],
                ['Túi mini nữ Lyn', 'Lyn', 1190000],
                ['Túi bucket nữ Nucelle', 'Nucelle', 990000],
            ],
            'Phụ kiện & Trang sức nữ' => [
                ['Bông tai bạc PNJ', 'PNJ', 390000],
                ['Dây chuyền nữ PNJ', 'PNJ', 890000],
                ['Nhẫn nữ Bảo Tín Minh Châu', 'Bảo Tín Minh Châu', 1290000],
                ['Kẹp tóc Hàn Quốc', 'Korea Style', 39000],
                ['Đồng hồ lắc tay nữ', 'Julius', 690000],
                ['Vòng cổ ngọc trai', 'Pearl', 590000],
                ['Lắc chân bạc', 'Silver', 290000],
                ['Set phụ kiện tóc nữ', 'Miniso', 79000],
            ],
            'Bách hóa online' => [
                ['Sữa tươi TH True Milk', 'TH True Milk', 36000],
                ['Nước khoáng Lavie 500ml', 'Lavie', 5000],
                ['Cà phê G7 3in1', 'G7', 56000],
                ['Dầu ăn Tường An', 'Tường An', 69000],
                ['Nước mắm Nam Ngư', 'Nam Ngư', 45000],
                ['Bột giặt Omo 3kg', 'Omo', 189000],
                ['Snack Lay\'s 90g', 'Lay\'s', 32000],
                ['Kẹo Mentos Rainbow', 'Mentos', 18000],
            ],
            'Nhà sách online' => [
                ['Sách Tuổi Trẻ Đáng Giá Bao Nhiêu', 'Nhã Nam', 85000],
                ['Sách Atomic Habits', 'First News', 189000],
                ['Bút bi Thiên Long TL-027', 'Thiên Long', 5000],
                ['Vở Campus 200 trang', 'Campus', 22000],
                ['Giấy Double A A4', 'Double A', 79000],
                ['Máy tính Casio fx-580VN X', 'Casio', 690000],
                ['Flashcard Deli 100 tờ', 'Deli', 35000],
                ['Balo học sinh Miti', 'Miti', 420000],
            ],
        ];
    }
}
