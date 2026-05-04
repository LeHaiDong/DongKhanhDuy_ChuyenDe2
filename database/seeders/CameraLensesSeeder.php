<?php

namespace Database\Seeders;

use App\Models\CameraLens;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CameraLensesSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('camera_lens_categories')->delete();
        DB::table('camera_lenses')->delete();
        Schema::enableForeignKeyConstraints();

        $sequence = 1;

        foreach ($this->catalogGroups() as $group) {
            foreach ($group['items'] as $item) {
                $product = CameraLens::create([
                    'name' => $item['name'],
                    'brand' => $item['brand'],
                    'product_type' => $group['product_type'],
                    'spec_label_1' => $group['spec_labels'][0],
                    'spec_label_2' => $group['spec_labels'][1],
                    'spec_label_3' => $group['spec_labels'][2],
                    'focal_length' => $item['specs'][0],
                    'max_aperture' => $item['specs'][1],
                    'mount_type' => $item['specs'][2],
                    'price' => $item['price'],
                    'cost_price' => $item['cost_price'] ?? (int) round($item['price'] * 0.72),
                    'stock_quantity' => $item['stock'] ?? 10,
                    'condition' => $item['condition'] ?? 'new',
                    'is_active' => true,
                    'sku' => $item['sku'] ?? $this->makeSku($item['brand'], $group['product_type'], $sequence),
                    'supplier' => $item['supplier'] ?? ($item['brand'] . ' Marketplace Partner'),
                    'description' => $item['description'] ?? $this->buildDescription($group, $item),
                    'search_keywords' => $this->buildKeywords($group, $item),
                    'image' => null,
                ]);

                $this->attachCategories(
                    $product,
                    array_values(array_unique(array_merge($group['categories'], $item['categories'] ?? [])))
                );

                $sequence++;
            }
        }
    }

    private function attachCategories(CameraLens $product, array $categoryNames): void
    {
        if (empty($categoryNames)) {
            return;
        }

        $categoryNames = collect($categoryNames)
            ->map(fn (string $name) => $this->visibleCategoryName($name))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $categoryIds = Category::whereIn('name', $categoryNames)->pluck('id')->all();

        if (!empty($categoryIds)) {
            $product->categories()->sync($categoryIds);
        }
    }

    private function visibleCategoryName(string $categoryName): ?string
    {
        return [
            'Dien tu va cong nghe' => null,
            'Dien thoai smartphone' => 'Điện thoại & phụ kiện',
            'May tinh bang' => 'Máy tính & Laptop',
            'Tai nghe va headset' => 'Thiết bị điện tử',
            'Sac va pin du phong' => 'Điện thoại & phụ kiện',
            'Am thanh va phu kien' => null,
            'Tai nghe TWS' => 'Thiết bị điện tử',
            'Tai nghe choi game' => 'Thiết bị điện tử',
            'Loa bluetooth' => 'Thiết bị điện tử',
            'Cap sac va cu sac' => 'Điện thoại & phụ kiện',
            'Sua va dinh duong' => null,
            'Sua cong thuc' => 'Mẹ & Bé',
            'Sua tuoi va sua hat' => 'Bách Hóa Online',
            'Ngu coc dinh duong' => 'Bách Hóa Online',
            'Bot an dam' => 'Mẹ & Bé',
            'Banh keo va do an vat' => null,
            'Snack khoai tay' => 'Bách Hóa Online',
            'Banh quy va banh bong lan' => 'Bách Hóa Online',
            'Keo va socola' => 'Bách Hóa Online',
            'Mi va chao an lien' => 'Bách Hóa Online',
            'Suc khoe va cham soc' => null,
            'Khau trang' => 'Sức Khỏe',
            'Rua tay va sat khuan' => 'Sức Khỏe',
            'Cham soc ca nhan' => 'Sức Khỏe',
            'Vat dung y te nho' => 'Sức Khỏe',
            'Me va be' => null,
            'Ta bim' => 'Mẹ & Bé',
            'Do dung cho be' => 'Mẹ & Bé',
            'Cham soc me va be' => 'Mẹ & Bé',
            'Phu kien an dam' => 'Mẹ & Bé',
            'Gia dung nha bep' => null,
            'Noi chao va bep mini' => 'Thiết bị điện gia dụng',
            'Hop dung va binh nuoc' => 'Nhà Cửa & Đời Sống',
            'Dung cu ve sinh' => 'Nhà Cửa & Đời Sống',
            'Do gia dung thong minh' => 'Thiết bị điện gia dụng',
            'Van phong pham va lifestyle' => null,
            'But va dung cu viet' => 'Nhà Sách Online',
            'Vo tap va giay in' => 'Nhà Sách Online',
            'Hop but va balo nho' => 'Nhà Sách Online',
            'Phu kien ban hoc' => 'Nhà Sách Online',
        ][$categoryName] ?? $categoryName;
    }

    private function makeSku(string $brand, string $productType, int $sequence): string
    {
        $brandCode = Str::upper(Str::substr(preg_replace('/[^A-Za-z]/', '', $brand), 0, 3));
        $typeCode = Str::upper(Str::substr(Str::slug($productType, ''), 0, 4));

        return sprintf('%s-%s-%04d', $brandCode ?: 'GEN', $typeCode ?: 'ITEM', $sequence);
    }

    private function buildDescription(array $group, array $item): string
    {
        $highlights = implode(', ', [
            $group['spec_labels'][0] . ': ' . $item['specs'][0],
            $group['spec_labels'][1] . ': ' . $item['specs'][1],
            $group['spec_labels'][2] . ': ' . $item['specs'][2],
        ]);

        $useCases = implode(', ', $item['tags'] ?? $group['focus_tags']);

        return "{$item['name']} thuộc nhóm {$group['product_type']}. {$highlights}. Phù hợp cho {$useCases}. Sản phẩm được bán trên MienTayShop với thông tin giá, tồn kho và tư vấn chatbot đi kèm.";
    }

    private function buildKeywords(array $group, array $item): string
    {
        $keywords = array_merge(
            [$item['name'], $item['brand'], $group['product_type']],
            $group['categories'],
            $item['categories'] ?? [],
            $item['tags'] ?? [],
            $item['specs']
        );

        return implode(', ', array_values(array_unique(array_filter($keywords))));
    }

    private function catalogGroups(): array
    {
        return [
            [
                'product_type' => 'Dien thoai smartphone',
                'spec_labels' => ['Man hinh', 'Bo nho', 'Ket noi'],
                'categories' => ['Dien tu va cong nghe', 'Dien thoai smartphone'],
                'focus_tags' => ['hoc tap', 'di lam', 'giai tri', 'mua online'],
                'items' => [
                    ['name' => 'iPhone 14 128GB', 'brand' => 'Apple', 'specs' => ['6.1 inch OLED', '128GB', '5G / Lightning'], 'price' => 15490000, 'stock' => 16, 'tags' => ['iPhone', 'ip14', 'camera dep', 'de dung']],
                    ['name' => 'iPhone 14 Pro 128GB', 'brand' => 'Apple', 'specs' => ['6.1 inch OLED 120Hz', '128GB', '5G / Lightning'], 'price' => 19990000, 'stock' => 7, 'tags' => ['iPhone', 'ip14 pro', 'cao cap', 'camera dep']],
                    ['name' => 'iPhone 15 128GB', 'brand' => 'Apple', 'specs' => ['6.1 inch OLED', '128GB', '5G / USB-C'], 'price' => 18990000, 'stock' => 14, 'tags' => ['iPhone', 'camera dep', 'cao cap']],
                    ['name' => 'iPhone 15 Plus 128GB', 'brand' => 'Apple', 'specs' => ['6.7 inch OLED', '128GB', '5G / USB-C'], 'price' => 21990000, 'stock' => 9, 'tags' => ['pin trau', 'man lon', 'iOS']],
                    ['name' => 'Galaxy A55 5G 8GB/128GB', 'brand' => 'Samsung', 'specs' => ['6.6 inch AMOLED', '128GB', '5G / NFC'], 'price' => 8990000, 'stock' => 18, 'tags' => ['tam trung', 'samsung', 'hoc online']],
                    ['name' => 'Galaxy S24 FE 8GB/256GB', 'brand' => 'Samsung', 'specs' => ['6.7 inch AMOLED', '256GB', '5G / NFC'], 'price' => 14990000, 'stock' => 11, 'tags' => ['AI', 'gaming', 'samsung']],
                    ['name' => 'Redmi Note 13 Pro 8GB/256GB', 'brand' => 'Xiaomi', 'specs' => ['6.67 inch AMOLED', '256GB', '5G / NFC'], 'price' => 7790000, 'stock' => 21, 'tags' => ['gia tot', 'xiaomi', 'camera 200MP']],
                    ['name' => 'Xiaomi 14T 12GB/256GB', 'brand' => 'Xiaomi', 'specs' => ['6.67 inch AMOLED', '256GB', '5G / eSIM'], 'price' => 12990000, 'stock' => 10, 'tags' => ['cau hinh manh', 'gaming', 'Leica']],
                    ['name' => 'OPPO Reno12 F 5G 12GB/256GB', 'brand' => 'OPPO', 'specs' => ['6.67 inch AMOLED', '256GB', '5G / NFC'], 'price' => 8490000, 'stock' => 16, 'tags' => ['thiet ke dep', 'selfie', 'oppo']],
                    ['name' => 'realme 12+ 5G 8GB/256GB', 'brand' => 'realme', 'specs' => ['6.67 inch AMOLED', '256GB', '5G / NFC'], 'price' => 7390000, 'stock' => 15, 'tags' => ['realme', 'mua online', 'gia mem']],
                    ['name' => 'iPhone 13 128GB', 'brand' => 'Apple', 'specs' => ['6.1 inch OLED', '128GB', '5G / Lightning'], 'price' => 12990000, 'stock' => 12, 'tags' => ['iPhone', 'de dung', 'ban chay']],
                    ['name' => 'Galaxy S24 Ultra 12GB/256GB', 'brand' => 'Samsung', 'specs' => ['6.8 inch AMOLED', '256GB', '5G / S Pen'], 'price' => 25990000, 'stock' => 8, 'tags' => ['cao cap', 'camera', 'flagship']],
                ],
            ],
            [
                'product_type' => 'May tinh bang',
                'spec_labels' => ['Man hinh', 'Bo nho', 'Ket noi'],
                'categories' => ['Dien tu va cong nghe', 'May tinh bang'],
                'focus_tags' => ['hoc tap', 'doc sach', 'giai tri', 'lam viec di dong'],
                'items' => [
                    ['name' => 'iPad 10 Wi-Fi 64GB', 'brand' => 'Apple', 'specs' => ['10.9 inch Liquid Retina', '64GB', 'Wi-Fi / USB-C'], 'price' => 9490000, 'stock' => 12, 'tags' => ['ipad', 'hoc sinh', 'gia dinh']],
                    ['name' => 'iPad Air M2 Wi-Fi 128GB', 'brand' => 'Apple', 'specs' => ['11 inch Liquid Retina', '128GB', 'Wi-Fi 6E / USB-C'], 'price' => 16990000, 'stock' => 7, 'tags' => ['M2', 'designer', 'ghi chu']],
                    ['name' => 'Galaxy Tab A9 64GB', 'brand' => 'Samsung', 'specs' => ['8.7 inch LCD', '64GB', '4G / USB-C'], 'price' => 3590000, 'stock' => 20, 'tags' => ['gia re', 'giai tri', 'tre em']],
                    ['name' => 'Galaxy Tab S9 FE 128GB', 'brand' => 'Samsung', 'specs' => ['10.9 inch LCD 90Hz', '128GB', 'Wi-Fi / S Pen'], 'price' => 9990000, 'stock' => 8, 'tags' => ['ghi chu', 'hoc online', 'samsung']],
                    ['name' => 'Xiaomi Pad 6 128GB', 'brand' => 'Xiaomi', 'specs' => ['11 inch 144Hz', '128GB', 'Wi-Fi / USB-C'], 'price' => 7990000, 'stock' => 11, 'tags' => ['gia tot', 'xem phim', 'lam viec']],
                    ['name' => 'Lenovo Tab M11 128GB', 'brand' => 'Lenovo', 'specs' => ['11 inch IPS', '128GB', 'Wi-Fi / USB-C'], 'price' => 5290000, 'stock' => 13, 'tags' => ['hoc tap', 'lenovo', 'gia mem']],
                    ['name' => 'iPad mini 6 Wi-Fi 64GB', 'brand' => 'Apple', 'specs' => ['8.3 inch Liquid Retina', '64GB', 'Wi-Fi / USB-C'], 'price' => 11990000, 'stock' => 6, 'tags' => ['nho gon', 'cao cap', 'ghi chu']],
                    ['name' => 'Redmi Pad SE 128GB', 'brand' => 'Xiaomi', 'specs' => ['11 inch 90Hz', '128GB', 'Wi-Fi / USB-C'], 'price' => 4890000, 'stock' => 14, 'tags' => ['gia re', 'giai tri', 'hoc tap']],
                ],
            ],
            [
                'product_type' => 'Tai nghe TWS',
                'spec_labels' => ['Cong nghe', 'Pin', 'Ket noi'],
                'categories' => ['Dien tu va cong nghe', 'Tai nghe va headset', 'Am thanh va phu kien', 'Tai nghe TWS'],
                'focus_tags' => ['nghe nhac', 'di chuyen', 'tap gym', 'goi dien'],
                'items' => [
                    ['name' => 'AirPods 4', 'brand' => 'Apple', 'specs' => ['Adaptive Audio', '30 gio voi case', 'Bluetooth 5.3'], 'price' => 4290000, 'stock' => 22, 'tags' => ['airpods', 'iphone', 'goi dien']],
                    ['name' => 'Galaxy Buds FE', 'brand' => 'Samsung', 'specs' => ['ANC', '30 gio voi case', 'Bluetooth 5.2'], 'price' => 1790000, 'stock' => 25, 'tags' => ['samsung', 'chong on', 'gia tot']],
                    ['name' => 'Sony WF-C700N', 'brand' => 'Sony', 'specs' => ['ANC', '15 gio', 'Bluetooth 5.2'], 'price' => 2390000, 'stock' => 16, 'tags' => ['sony', 'music', 'travel']],
                    ['name' => 'JBL Wave Beam 2', 'brand' => 'JBL', 'specs' => ['JBL Deep Bass', '32 gio', 'Bluetooth 5.3'], 'price' => 1490000, 'stock' => 24, 'tags' => ['bass', 'JBL', 'TWS']],
                    ['name' => 'SoundPEATS Capsule3 Pro+', 'brand' => 'SoundPEATS', 'specs' => ['Hi-Res LDAC', '43 gio', 'Bluetooth 5.3'], 'price' => 1590000, 'stock' => 18, 'tags' => ['LDAC', 'gia tot', 'android']],
                    ['name' => 'Redmi Buds 5 Pro', 'brand' => 'Xiaomi', 'specs' => ['ANC 52dB', '38 gio', 'Bluetooth 5.3'], 'price' => 1490000, 'stock' => 20, 'tags' => ['xiaomi', 'pin lau', 'nhac']],
                    ['name' => 'OPPO Enco Air4', 'brand' => 'OPPO', 'specs' => ['ANC', '43 gio', 'Bluetooth 5.4'], 'price' => 1690000, 'stock' => 15, 'tags' => ['OPPO', 'mic ro', 'TWS']],
                    ['name' => 'Soundcore Liberty 4 NC', 'brand' => 'Anker', 'specs' => ['ANC', '50 gio', 'Bluetooth 5.3'], 'price' => 2290000, 'stock' => 17, 'tags' => ['Anker', 'chong on', 'pin trau']],
                    ['name' => 'AirPods Pro 2 USB-C', 'brand' => 'Apple', 'specs' => ['ANC + Spatial Audio', '30 gio voi case', 'Bluetooth 5.3'], 'price' => 5790000, 'stock' => 9, 'tags' => ['cao cap', 'iphone', 'airpods']],
                    ['name' => 'JBL Tune Flex', 'brand' => 'JBL', 'specs' => ['ANC', '32 gio', 'Bluetooth 5.2'], 'price' => 1990000, 'stock' => 13, 'tags' => ['JBL', 'TWS', 'bass']],
                ],
            ],
            [
                'product_type' => 'Tai nghe headset',
                'spec_labels' => ['Cong nghe', 'Trong luong', 'Ket noi'],
                'categories' => ['Dien tu va cong nghe', 'Tai nghe va headset', 'Am thanh va phu kien', 'Tai nghe choi game'],
                'focus_tags' => ['gaming', 'meeting', 'hoc online', 'nghe nhac'],
                'items' => [
                    ['name' => 'Sony WH-CH720N', 'brand' => 'Sony', 'specs' => ['ANC', '192g', 'Bluetooth / 3.5mm'], 'price' => 2490000, 'stock' => 13, 'tags' => ['sony', 'khong day', 'travel']],
                    ['name' => 'JBL Tune 770NC', 'brand' => 'JBL', 'specs' => ['ANC', '232g', 'Bluetooth / 3.5mm'], 'price' => 2290000, 'stock' => 14, 'tags' => ['JBL', 'bass', 'office']],
                    ['name' => 'HyperX Cloud III', 'brand' => 'HyperX', 'specs' => ['53mm driver', '320g', 'USB-C / 3.5mm'], 'price' => 2190000, 'stock' => 11, 'tags' => ['gaming', 'PC', 'mic tot']],
                    ['name' => 'Logitech G435', 'brand' => 'Logitech', 'specs' => ['Lightspeed', '165g', 'Bluetooth / USB'], 'price' => 1390000, 'stock' => 19, 'tags' => ['nhe', 'wireless', 'gaming']],
                    ['name' => 'Razer BlackShark V2 X', 'brand' => 'Razer', 'specs' => ['50mm driver', '240g', '3.5mm'], 'price' => 1290000, 'stock' => 16, 'tags' => ['Razer', 'esports', 'headset']],
                    ['name' => 'Soundcore Q20i', 'brand' => 'Anker', 'specs' => ['ANC', '246g', 'Bluetooth / AUX'], 'price' => 1190000, 'stock' => 18, 'tags' => ['Anker', 'gia mem', 'nghe nhac']],
                ],
            ],
            [
                'product_type' => 'Loa bluetooth',
                'spec_labels' => ['Cong suat', 'Pin', 'Ket noi'],
                'categories' => ['Am thanh va phu kien', 'Loa bluetooth'],
                'focus_tags' => ['du lich', 'ca nhan', 'da ngoai', 'giai tri gia dinh'],
                'items' => [
                    ['name' => 'JBL Go 4', 'brand' => 'JBL', 'specs' => ['4.2W', '7 gio', 'Bluetooth 5.3'], 'price' => 990000, 'stock' => 24, 'tags' => ['mini', 'JBL', 'du lich']],
                    ['name' => 'JBL Flip 6', 'brand' => 'JBL', 'specs' => ['30W', '12 gio', 'Bluetooth 5.1'], 'price' => 2590000, 'stock' => 12, 'tags' => ['loa mang di', 'bass', 'party']],
                    ['name' => 'Marshall Emberton II', 'brand' => 'Marshall', 'specs' => ['20W', '30 gio', 'Bluetooth 5.1'], 'price' => 3490000, 'stock' => 8, 'tags' => ['cao cap', 'Marshall', 'phong cach']],
                    ['name' => 'Sony SRS-XB100', 'brand' => 'Sony', 'specs' => ['Extra Bass', '16 gio', 'Bluetooth 5.3'], 'price' => 1190000, 'stock' => 18, 'tags' => ['sony', 'nho gon', 'ngoai troi']],
                    ['name' => 'Harman Kardon Neo', 'brand' => 'Harman Kardon', 'specs' => ['10W', '10 gio', 'Bluetooth 4.2'], 'price' => 1790000, 'stock' => 9, 'tags' => ['thiet ke dep', 'premium', 'quatang']],
                    ['name' => 'Soundcore Motion 300', 'brand' => 'Anker', 'specs' => ['30W', '13 gio', 'Bluetooth 5.3'], 'price' => 1990000, 'stock' => 14, 'tags' => ['Anker', 'stereo', 'ngoai troi']],
                ],
            ],
            [
                'product_type' => 'Sac va pin du phong',
                'spec_labels' => ['Cong suat', 'Dung luong', 'Ket noi'],
                'categories' => ['Dien tu va cong nghe', 'Sac va pin du phong', 'Am thanh va phu kien', 'Cap sac va cu sac'],
                'focus_tags' => ['sac nhanh', 'mang theo', 'cong tac', 'hoc tap'],
                'items' => [
                    ['name' => 'Anker Nano 30W', 'brand' => 'Anker', 'specs' => ['30W', 'Sac nhanh', 'USB-C'], 'price' => 390000, 'stock' => 28, 'tags' => ['Anker', 'GaN', 'cu sac']],
                    ['name' => 'Apple USB-C Power Adapter 20W', 'brand' => 'Apple', 'specs' => ['20W', 'Sac nhanh', 'USB-C'], 'price' => 490000, 'stock' => 19, 'tags' => ['Apple', 'iPhone', 'USB-C']],
                    ['name' => 'Samsung Super Fast Charger 25W', 'brand' => 'Samsung', 'specs' => ['25W', 'Sac nhanh', 'USB-C'], 'price' => 390000, 'stock' => 22, 'tags' => ['Samsung', 'PD', 'sac nhanh']],
                    ['name' => 'Baseus GaN5 Pro 65W', 'brand' => 'Baseus', 'specs' => ['65W', 'GaN', '2C1A'], 'price' => 690000, 'stock' => 17, 'tags' => ['laptop', 'da cong', 'Baseus']],
                    ['name' => 'Anker PowerCore 10000', 'brand' => 'Anker', 'specs' => ['12W', '10000mAh', 'USB-A / USB-C'], 'price' => 690000, 'stock' => 21, 'tags' => ['power bank', 'mang theo', 'Anker']],
                    ['name' => 'Baseus Magnetic Mini 10000mAh', 'brand' => 'Baseus', 'specs' => ['20W', '10000mAh', 'USB-C / MagSafe'], 'price' => 890000, 'stock' => 14, 'tags' => ['pin du phong', 'nam cham', 'iPhone']],
                    ['name' => 'Xiaomi Power Bank 10000mAh 22.5W', 'brand' => 'Xiaomi', 'specs' => ['22.5W', '10000mAh', 'USB-C / USB-A'], 'price' => 490000, 'stock' => 24, 'tags' => ['gia tot', 'Xiaomi', 'sac nhanh']],
                    ['name' => 'Energizer UE20058 20000mAh', 'brand' => 'Energizer', 'specs' => ['18W', '20000mAh', 'USB-C / USB-A'], 'price' => 790000, 'stock' => 16, 'tags' => ['pin lon', 'du lich', 'Energizer']],
                ],
            ],
            [
                'product_type' => 'Sua cong thuc',
                'spec_labels' => ['Do tuoi', 'Khoi luong', 'Cong dung'],
                'categories' => ['Sua va dinh duong', 'Sua cong thuc'],
                'focus_tags' => ['tre nho', 'phat trien', 'bo sung dinh duong', 'gia dinh'],
                'items' => [
                    ['name' => 'Similac Eye-Q So 3 900g', 'brand' => 'Abbott', 'specs' => ['1-2 tuoi', '900g', 'Ho tro tri nao'], 'price' => 579000, 'stock' => 25, 'tags' => ['Abbott', 'sua bot', 'tre nho']],
                    ['name' => 'Similac Eye-Q So 4 900g', 'brand' => 'Abbott', 'specs' => ['2-6 tuoi', '900g', 'Tang de khang'], 'price' => 559000, 'stock' => 22, 'tags' => ['Abbott', 'tri nao', 'tre em']],
                    ['name' => 'Enfa A+ NeuroPro So 2 900g', 'brand' => 'Enfa', 'specs' => ['6-12 thang', '900g', 'DHA / MFGM'], 'price' => 639000, 'stock' => 18, 'tags' => ['Enfa', 'DHA', 'be nho']],
                    ['name' => 'NAN Optipro Plus 3 800g', 'brand' => 'Nestle', 'specs' => ['1-2 tuoi', '800g', 'Ho tro tieu hoa'], 'price' => 489000, 'stock' => 26, 'tags' => ['NAN', 'Nestle', 'Optipro']],
                    ['name' => 'NAN Optipro Plus 4 800g', 'brand' => 'Nestle', 'specs' => ['2-6 tuoi', '800g', 'Bo sung dam whey'], 'price' => 475000, 'stock' => 23, 'tags' => ['NAN', 'tre em', 'dinh duong']],
                    ['name' => 'Meiji Infant Formula 0-1 800g', 'brand' => 'Meiji', 'specs' => ['0-12 thang', '800g', 'Vi thanh nhat'], 'price' => 529000, 'stock' => 19, 'tags' => ['Meiji', 'nhat ban', 'be nho']],
                    ['name' => 'Friso Gold Pro 3 850g', 'brand' => 'Friso', 'specs' => ['1-2 tuoi', '850g', 'Khoi phuc he tieu hoa'], 'price' => 545000, 'stock' => 16, 'tags' => ['Friso', 'gold', 'be khoe']],
                    ['name' => 'PediaSure BA 850g', 'brand' => 'Abbott', 'specs' => ['1-10 tuoi', '850g', 'Tang can khoe manh'], 'price' => 629000, 'stock' => 21, 'tags' => ['PediaSure', 'cho be bieng an', 'dinh duong']],
                    ['name' => 'Aptamil Profutura So 3 900g', 'brand' => 'Aptamil', 'specs' => ['1-2 tuoi', '900g', 'Ho tro tieu hoa'], 'price' => 699000, 'stock' => 11, 'tags' => ['Aptamil', 'cao cap', 'sua bot']],
                    ['name' => 'GrowPLUS+ Do 900g', 'brand' => 'Nutifood', 'specs' => ['1-10 tuoi', '900g', 'Tang can cho be'], 'price' => 515000, 'stock' => 17, 'tags' => ['GrowPLUS', 'dinh duong', 'tre em']],
                ],
            ],
            [
                'product_type' => 'Sua tuoi va sua hat',
                'spec_labels' => ['Dung tich', 'Vi', 'Dong goi'],
                'categories' => ['Sua va dinh duong', 'Sua tuoi va sua hat'],
                'focus_tags' => ['uong hang ngay', 'gia dinh', 'an sang', 'mang di'],
                'items' => [
                    ['name' => 'Vinamilk Green Farm 1L', 'brand' => 'Vinamilk', 'specs' => ['1L', 'Co duong', 'Hop giay'], 'price' => 39000, 'stock' => 35, 'tags' => ['sua tuoi', 'gia dinh', 'Vinamilk']],
                    ['name' => 'TH true Milk It Duong 1L', 'brand' => 'TH true Milk', 'specs' => ['1L', 'It duong', 'Hop giay'], 'price' => 42000, 'stock' => 31, 'tags' => ['TH', 'sua tuoi', 'it duong']],
                    ['name' => 'Oatside Chocolate 1L', 'brand' => 'Oatside', 'specs' => ['1L', 'Socola', 'Hop giay'], 'price' => 59000, 'stock' => 26, 'tags' => ['sua hat', 'yen mach', 'socola']],
                    ['name' => '137 Degrees Almond Milk 1L', 'brand' => '137 Degrees', 'specs' => ['1L', 'Hanh nhan', 'Hop giay'], 'price' => 72000, 'stock' => 18, 'tags' => ['hanh nhan', 'healthy', 'sua hat']],
                    ['name' => 'NuVi Grow 180ml Lo 4 Hop', 'brand' => 'Nutifood', 'specs' => ['720ml', 'Vanilla', '4 hop'], 'price' => 36000, 'stock' => 40, 'tags' => ['tre em', 'mang di hoc', 'NuVi']],
                    ['name' => 'Dutch Lady Cao Khoe 180ml Lo 4 Hop', 'brand' => 'Dutch Lady', 'specs' => ['720ml', 'Co duong', '4 hop'], 'price' => 34000, 'stock' => 37, 'tags' => ['Dutch Lady', 'gia dinh', 'hop nho']],
                    ['name' => 'TH true Nut Hat Oc Cho 180ml Lo 4 Hop', 'brand' => 'TH true Nut', 'specs' => ['720ml', 'Hat oc cho', '4 hop'], 'price' => 46000, 'stock' => 24, 'tags' => ['sua hat', 'healthy', 'mang theo']],
                    ['name' => 'Vinamilk ADM Gold 180ml Lo 4 Hop', 'brand' => 'Vinamilk', 'specs' => ['720ml', 'Socola', '4 hop'], 'price' => 39000, 'stock' => 28, 'tags' => ['hoc sinh', 'nang luong', 'sua tuoi']],
                ],
            ],
            [
                'product_type' => 'Ngu coc dinh duong',
                'spec_labels' => ['Khoi luong', 'Huong vi', 'Cong dung'],
                'categories' => ['Sua va dinh duong', 'Ngu coc dinh duong'],
                'focus_tags' => ['an sang', 'bo sung nang luong', 'hoc tap', 'van dong'],
                'items' => [
                    ['name' => 'Milo 3in1 17 Goi', 'brand' => 'Nestle', 'specs' => ['289g', 'Socola lua mach', 'Nang luong buoi sang'], 'price' => 62000, 'stock' => 29, 'tags' => ['Milo', 'hoc sinh', 'an sang']],
                    ['name' => 'Ovaltine 18 Goi', 'brand' => 'Ovaltine', 'specs' => ['324g', 'Socola mach nha', 'Bo sung vitamin'], 'price' => 68000, 'stock' => 24, 'tags' => ['Ovaltine', 'socola', 'gia dinh']],
                    ['name' => 'Calbee Frugra 600g', 'brand' => 'Calbee', 'specs' => ['600g', 'Ngu coc trai cay', 'An sang nhanh'], 'price' => 145000, 'stock' => 17, 'tags' => ['Calbee', 'granola', 'healthy']],
                    ['name' => 'Quaker Oats 1kg', 'brand' => 'Quaker', 'specs' => ['1kg', 'Yen mach nguyen chat', 'Tot cho suc khoe'], 'price' => 119000, 'stock' => 20, 'tags' => ['yen mach', 'eat clean', 'healthy']],
                    ['name' => 'Ngu Coc Nutifood 500g', 'brand' => 'Nutifood', 'specs' => ['500g', 'Ngu coc tong hop', 'Bo sung chat xo'], 'price' => 79000, 'stock' => 22, 'tags' => ['Nutifood', 'dinh duong', 'gia dinh']],
                    ['name' => 'Ensure Gold Vigor 400g', 'brand' => 'Abbott', 'specs' => ['400g', 'Vanilla', 'Nguoi truong thanh'], 'price' => 245000, 'stock' => 14, 'tags' => ['Ensure', 'nguoi lon', 'dinh duong']],
                ],
            ],
            [
                'product_type' => 'Snack va banh keo',
                'spec_labels' => ['Khoi luong', 'Huong vi', 'Dong goi'],
                'categories' => ['Banh keo va do an vat', 'Snack khoai tay'],
                'focus_tags' => ['an vat', 'xem phim', 'mang di lam', 'gia dinh'],
                'items' => [
                    ['name' => "Lay's Original 95g", 'brand' => "Lay's", 'specs' => ['95g', 'Vi tu nhien', 'Goi'], 'price' => 22000, 'stock' => 48, 'tags' => ['snack', 'khoai tay', 'an vat']],
                    ['name' => "Lay's Stax Sour Cream 90g", 'brand' => "Lay's", 'specs' => ['90g', 'Kem chua hanh', 'Lon'], 'price' => 28000, 'stock' => 36, 'tags' => ['Stax', 'khoai tay', 'snack']],
                    ['name' => 'Oishi Tom Yum 52g', 'brand' => 'Oishi', 'specs' => ['52g', 'Tom yum', 'Goi'], 'price' => 12000, 'stock' => 60, 'tags' => ['Oishi', 'an vat', 'gia re']],
                    ['name' => 'Poca Seaweed 56g', 'brand' => 'Poca', 'specs' => ['56g', 'Rong bien', 'Goi'], 'price' => 18000, 'stock' => 42, 'tags' => ['Poca', 'snack', 'rong bien']],
                    ['name' => 'Poca BBQ 56g', 'brand' => 'Poca', 'specs' => ['56g', 'BBQ', 'Goi'], 'price' => 18000, 'stock' => 38, 'tags' => ['BBQ', 'snack', 'Poca']],
                    ['name' => 'Swing Potato BBQ 60g', 'brand' => 'Swing', 'specs' => ['60g', 'BBQ', 'Goi'], 'price' => 17000, 'stock' => 40, 'tags' => ['khoai tay', 'BBQ', 'Swing']],
                    ['name' => 'Tao Kae Noi Big Roll 9g', 'brand' => 'Tao Kae Noi', 'specs' => ['9g', 'Rong bien cay', 'Hop'], 'price' => 18000, 'stock' => 28, 'tags' => ['rong bien', 'Thai Lan', 'an vat']],
                    ['name' => 'Pringles Original 102g', 'brand' => 'Pringles', 'specs' => ['102g', 'Original', 'Lon'], 'price' => 55000, 'stock' => 19, 'tags' => ['Pringles', 'cao cap', 'snack']],
                    ['name' => 'Doritos Nacho Cheese 92g', 'brand' => 'Doritos', 'specs' => ['92g', 'Pho mai', 'Goi'], 'price' => 35000, 'stock' => 22, 'tags' => ['Nacho', 'snack', 'pho mai']],
                    ['name' => 'KitKat Mini 120g', 'brand' => 'Nestle', 'specs' => ['120g', 'Socola', 'Tui'], 'price' => 43000, 'stock' => 26, 'tags' => ['socola', 'an vat', 'KitKat']],
                ],
            ],
            [
                'product_type' => 'Keo va socola',
                'spec_labels' => ['Khoi luong', 'Huong vi', 'Dong goi'],
                'categories' => ['Banh keo va do an vat', 'Keo va socola'],
                'focus_tags' => ['qua tang', 'an vat', 'van phong', 'gia dinh'],
                'items' => [
                    ['name' => 'KitKat 9 Fingers 153g', 'brand' => 'Nestle', 'specs' => ['153g', 'Socola wafers', 'Goi'], 'price' => 49000, 'stock' => 26, 'tags' => ['KitKat', 'socola', 'an vat']],
                    ['name' => "M&M's Peanut 150g", 'brand' => "M&M's", 'specs' => ['150g', 'Socola dau phong', 'Goi'], 'price' => 65000, 'stock' => 20, 'tags' => ['M&M', 'dau phong', 'socola']],
                    ['name' => 'Ferrero Rocher T16', 'brand' => 'Ferrero', 'specs' => ['200g', 'Hazelnut', 'Hop'], 'price' => 189000, 'stock' => 12, 'tags' => ['quatang', 'cao cap', 'socola']],
                    ['name' => 'Alpenliebe Butter 122g', 'brand' => 'Alpenliebe', 'specs' => ['122g', 'Bo sua', 'Goi'], 'price' => 22000, 'stock' => 34, 'tags' => ['keo mem', 'gia re', 'tre em']],
                    ['name' => 'Mentos Fruit 120g', 'brand' => 'Mentos', 'specs' => ['120g', 'Trai cay', 'Cuon'], 'price' => 26000, 'stock' => 31, 'tags' => ['Mentos', 'keo vien', 'trai cay']],
                    ['name' => 'Halls XS Watermelon', 'brand' => 'Halls', 'specs' => ['15g', 'Dua hau', 'Hop'], 'price' => 14000, 'stock' => 44, 'tags' => ['keo the', 'mang theo', 'Halls']],
                ],
            ],
            [
                'product_type' => 'Mi va chao an lien',
                'spec_labels' => ['Khoi luong', 'Huong vi', 'Dong goi'],
                'categories' => ['Banh keo va do an vat', 'Mi va chao an lien'],
                'focus_tags' => ['an nhanh', 'tich tru', 'sinh vien', 'mang di'],
                'items' => [
                    ['name' => 'Hao Hao Tom Chua Cay 75g', 'brand' => 'Acecook', 'specs' => ['75g', 'Tom chua cay', 'Goi'], 'price' => 4500, 'stock' => 100, 'tags' => ['mi goi', 'quoc dan', 'Acecook']],
                    ['name' => 'Omachi Suon Ham 80g', 'brand' => 'Omachi', 'specs' => ['80g', 'Suon ham', 'Ly'], 'price' => 8500, 'stock' => 66, 'tags' => ['mi ly', 'Omachi', 'an dem']],
                    ['name' => 'Cung Dinh Lau Thai 80g', 'brand' => 'Cung Dinh', 'specs' => ['80g', 'Lau thai', 'Goi'], 'price' => 7800, 'stock' => 58, 'tags' => ['mi goi', 'thai', 'gia mem']],
                    ['name' => 'Kokomi 90 Vi Dai 90g', 'brand' => 'Kokomi', 'specs' => ['90g', 'Tom chua cay', 'Goi'], 'price' => 5500, 'stock' => 72, 'tags' => ['Kokomi', 'mi goi', 'sinh vien']],
                    ['name' => 'Acecook Pho Bo 65g', 'brand' => 'Acecook', 'specs' => ['65g', 'Pho bo', 'Ly'], 'price' => 12000, 'stock' => 37, 'tags' => ['pho ly', 'an nhanh', 'Acecook']],
                    ['name' => 'SG Food Chao Tuoi Thit Bam 240g', 'brand' => 'SG Food', 'specs' => ['240g', 'Thit bam', 'Goi'], 'price' => 26000, 'stock' => 29, 'tags' => ['chao tuoi', 'an lien', 'tien dung']],
                ],
            ],
            [
                'product_type' => 'Khau trang',
                'spec_labels' => ['Loai', 'So lop', 'Quy cach'],
                'categories' => ['Suc khoe va cham soc', 'Khau trang'],
                'focus_tags' => ['di duong', 'di hoc', 'bao ve suc khoe', 'mua hang ngay'],
                'items' => [
                    ['name' => '3M KF94 10 Cai', 'brand' => '3M', 'specs' => ['KF94', '4 lop', '10 cai'], 'price' => 59000, 'stock' => 45, 'tags' => ['3M', 'KF94', 'khang bui']],
                    ['name' => 'Unicharm 3D Mask 20 Cai', 'brand' => 'Unicharm', 'specs' => ['3D Mask', '4 lop', '20 cai'], 'price' => 79000, 'stock' => 33, 'tags' => ['Unicharm', 'thoang', 'hang ngay']],
                    ['name' => 'Dr.Mask 4 Lop 50 Cai', 'brand' => 'Dr.Mask', 'specs' => ['Y te', '4 lop', '50 cai'], 'price' => 42000, 'stock' => 62, 'tags' => ['y te', 'hop lon', 'gia dinh']],
                    ['name' => 'Famapro Y Te 4 Lop 50 Cai', 'brand' => 'Famapro', 'specs' => ['Y te', '4 lop', '50 cai'], 'price' => 39000, 'stock' => 58, 'tags' => ['Famapro', 'mua si', 'khau trang']],
                    ['name' => 'Khau Trang Than Hoat Tinh 10 Cai', 'brand' => 'Sunstar', 'specs' => ['Than hoat tinh', '4 lop', '10 cai'], 'price' => 25000, 'stock' => 36, 'tags' => ['loc bui', 'than hoat tinh', 'hang ngay']],
                    ['name' => 'Khau Trang Tre Em KF94 10 Cai', 'brand' => 'Anyguard', 'specs' => ['KF94 tre em', '4 lop', '10 cai'], 'price' => 48000, 'stock' => 27, 'tags' => ['tre em', 'KF94', 'mau sac']],
                    ['name' => '3M Aura 1860 N95 5 Cai', 'brand' => '3M', 'specs' => ['N95', '5 lop', '5 cai'], 'price' => 149000, 'stock' => 15, 'tags' => ['3M', 'N95', 'cao cap']],
                    ['name' => 'Unicharm Mask Siam 30 Cai', 'brand' => 'Unicharm', 'specs' => ['Khau trang mem', '3 lop', '30 cai'], 'price' => 69000, 'stock' => 21, 'tags' => ['Unicharm', 'hang ngay', 'de tho']],
                ],
            ],
            [
                'product_type' => 'Rua tay sat khuan',
                'spec_labels' => ['Dung tich', 'Mui huong', 'Cong dung'],
                'categories' => ['Suc khoe va cham soc', 'Rua tay va sat khuan'],
                'focus_tags' => ['ve sinh', 'gia dinh', 'van phong', 'mang theo'],
                'items' => [
                    ['name' => 'Lifebuoy Matcha 500g', 'brand' => 'Lifebuoy', 'specs' => ['500g', 'Matcha', 'Rua tay sach khuan'], 'price' => 48000, 'stock' => 29, 'tags' => ['Lifebuoy', 'gia dinh', 'rua tay']],
                    ['name' => 'Dettol Original 500ml', 'brand' => 'Dettol', 'specs' => ['500ml', 'Original', 'Khang khuan'], 'price' => 69000, 'stock' => 24, 'tags' => ['Dettol', 'sat khuan', 'gia dinh']],
                    ['name' => 'Green Cross 250ml', 'brand' => 'Green Cross', 'specs' => ['250ml', 'Khong mui', 'Gel rua tay kho'], 'price' => 38000, 'stock' => 31, 'tags' => ['Green Cross', 'gel rua tay', 'mang theo']],
                    ['name' => 'Purell Advanced 236ml', 'brand' => 'Purell', 'specs' => ['236ml', 'Khong mui', 'Sat khuan nhanh'], 'price' => 79000, 'stock' => 17, 'tags' => ['Purell', 'cao cap', 'sat khuan']],
                    ['name' => 'On1 Nano Bac 100ml', 'brand' => 'On1', 'specs' => ['100ml', 'Khong mui', 'Xit khu khuan'], 'price' => 32000, 'stock' => 26, 'tags' => ['xit khu khuan', 'mang theo', 'On1']],
                    ['name' => 'Safeguard Aloe 225ml', 'brand' => 'Safeguard', 'specs' => ['225ml', 'Aloe vera', 'Rua tay hang ngay'], 'price' => 41000, 'stock' => 23, 'tags' => ['Safeguard', 've sinh', 'gia dinh']],
                ],
            ],
            [
                'product_type' => 'Cham soc ca nhan',
                'spec_labels' => ['Dung tich', 'Loai', 'Cong dung'],
                'categories' => ['Suc khoe va cham soc', 'Cham soc ca nhan'],
                'focus_tags' => ['ve sinh co the', 'hang ngay', 'gia dinh', 'cham soc'],
                'items' => [
                    ['name' => 'Pantene Smooth & Silky 650g', 'brand' => 'Pantene', 'specs' => ['650g', 'Dau goi', 'Mem toc'], 'price' => 169000, 'stock' => 20, 'tags' => ['Pantene', 'toc suon', 'gia dinh']],
                    ['name' => 'Dove Deeply Nourishing 530g', 'brand' => 'Dove', 'specs' => ['530g', 'Sua tam', 'Duong am'], 'price' => 129000, 'stock' => 22, 'tags' => ['Dove', 'duong da', 'hang ngay']],
                    ['name' => 'Colgate Optic White 100g', 'brand' => 'Colgate', 'specs' => ['100g', 'Kem danh rang', 'Lam trang rang'], 'price' => 54000, 'stock' => 33, 'tags' => ['Colgate', 'rang mieng', 'hang ngay']],
                    ['name' => 'Oral-B Pro-Health 2 Cay', 'brand' => 'Oral-B', 'specs' => ['2 cay', 'Ban chai', 'Lam sach sau'], 'price' => 65000, 'stock' => 28, 'tags' => ['Oral-B', 'ban chai', 'rang mieng']],
                    ['name' => 'Nivea Men Cool Kick 150ml', 'brand' => 'Nivea', 'specs' => ['150ml', 'Lan khu mui', 'Kho thoang'], 'price' => 92000, 'stock' => 18, 'tags' => ['Nivea', 'nam gioi', 'hang ngay']],
                    ['name' => 'Cetaphil Gentle Cleanser 125ml', 'brand' => 'Cetaphil', 'specs' => ['125ml', 'Sua rua mat', 'Da nhay cam'], 'price' => 165000, 'stock' => 15, 'tags' => ['Cetaphil', 'skincare', 'da nhay cam']],
                ],
            ],
            [
                'product_type' => 'Ta bim em be',
                'spec_labels' => ['Size', 'So luong', 'Tinh nang'],
                'categories' => ['Me va be', 'Ta bim'],
                'focus_tags' => ['tre nho', 'hang ngay', 'mua dinh ky', 'gia dinh'],
                'items' => [
                    ['name' => 'Bobby Tape M 60 Mieng', 'brand' => 'Bobby', 'specs' => ['M', '60 mieng', 'Tham hut nhanh'], 'price' => 189000, 'stock' => 24, 'tags' => ['Bobby', 'ta dan', 'tre nho']],
                    ['name' => 'Huggies Platinum M58', 'brand' => 'Huggies', 'specs' => ['M', '58 mieng', 'Mem min'], 'price' => 235000, 'stock' => 18, 'tags' => ['Huggies', 'cao cap', 'ta dan']],
                    ['name' => 'Pampers Premium M62', 'brand' => 'Pampers', 'specs' => ['M', '62 mieng', 'Thoang khi'], 'price' => 259000, 'stock' => 16, 'tags' => ['Pampers', 'premium', 'be yeu']],
                    ['name' => 'Molfix M60', 'brand' => 'Molfix', 'specs' => ['M', '60 mieng', 'Mat trong cotton'], 'price' => 199000, 'stock' => 19, 'tags' => ['Molfix', 'ta dan', 'gia tot']],
                    ['name' => 'Merries M64', 'brand' => 'Merries', 'specs' => ['M', '64 mieng', 'Sieu mem'], 'price' => 285000, 'stock' => 14, 'tags' => ['Merries', 'nhat ban', 'cao cap']],
                    ['name' => 'Goon Premium M58', 'brand' => 'Goon', 'specs' => ['M', '58 mieng', 'Thiet ke mong'], 'price' => 249000, 'stock' => 13, 'tags' => ['Goon', 'premium', 'tre nho']],
                ],
            ],
            [
                'product_type' => 'Do dung cho be',
                'spec_labels' => ['Chat lieu', 'Dung tich', 'Cong dung'],
                'categories' => ['Me va be', 'Do dung cho be', 'Cham soc me va be'],
                'focus_tags' => ['tre nho', 'an dam', 'mang theo', 'gia dinh'],
                'items' => [
                    ['name' => 'Pigeon Baby Wipes 80 To', 'brand' => 'Pigeon', 'specs' => ['Vai mem', '80 to', 'Lau da nhay cam'], 'price' => 32000, 'stock' => 34, 'tags' => ['khan giay', 'Pigeon', 'tre em']],
                    ['name' => 'Farlin Feeding Bottle 120ml', 'brand' => 'Farlin', 'specs' => ['Nhua PPSU', '120ml', 'Binh sua'], 'price' => 119000, 'stock' => 18, 'tags' => ['binh sua', 'Farlin', 'so sinh']],
                    ['name' => 'Richell Straw Cup 200ml', 'brand' => 'Richell', 'specs' => ['Nhua Tritan', '200ml', 'Coc tap uong'], 'price' => 149000, 'stock' => 15, 'tags' => ['coc tap uong', 'Richell', 'be nho']],
                    ['name' => 'Kidsme Fruit Feeder', 'brand' => 'Kidsme', 'specs' => ['Silicone', '1 bo', 'Tap an dam'], 'price' => 89000, 'stock' => 20, 'tags' => ['an dam', 'Kidsme', 'hoa qua']],
                    ['name' => 'Babyganics Foaming Soap 250ml', 'brand' => 'Babyganics', 'specs' => ['Bot rua tay', '250ml', 'Cho be'], 'price' => 129000, 'stock' => 12, 'tags' => ['ve sinh', 'cho be', 'nhap khau']],
                    ['name' => 'Chicco Body Lotion 200ml', 'brand' => 'Chicco', 'specs' => ['Lotion', '200ml', 'Duong am da be'], 'price' => 159000, 'stock' => 11, 'tags' => ['Chicco', 'duong am', 'tre nho']],
                ],
            ],
            [
                'product_type' => 'Gia dung nha bep',
                'spec_labels' => ['Cong suat', 'Dung tich', 'Phu hop cho'],
                'categories' => ['Gia dung nha bep', 'Noi chao va bep mini', 'Do gia dung thong minh'],
                'focus_tags' => ['nha bep', 'gia dinh', 'tan dung khong gian', 'su dung hang ngay'],
                'items' => [
                    ['name' => 'Sunhouse Electric Kettle 1.8L', 'brand' => 'Sunhouse', 'specs' => ['1800W', '1.8L', 'Dun nuoc nhanh'], 'price' => 329000, 'stock' => 17, 'tags' => ['am sieu toc', 'Sunhouse', 'gia dinh']],
                    ['name' => 'LocknLock Air Fryer 5.2L', 'brand' => 'LocknLock', 'specs' => ['1800W', '5.2L', 'Chien khong dau'], 'price' => 1890000, 'stock' => 9, 'tags' => ['noi chien', 'LocknLock', 'nha bep']],
                    ['name' => 'Bear Mini Rice Cooker 1.2L', 'brand' => 'Bear', 'specs' => ['400W', '1.2L', 'Nau com cho 1-2 nguoi'], 'price' => 690000, 'stock' => 12, 'tags' => ['mini', 'Bear', 'sinh vien']],
                    ['name' => 'Elmich Fry Pan 26cm', 'brand' => 'Elmich', 'specs' => ['Chong dinh', '26cm', 'Bep tu / gas'], 'price' => 459000, 'stock' => 16, 'tags' => ['chao', 'Elmich', 'nau an']],
                    ['name' => 'Philips Hand Blender HR2531', 'brand' => 'Philips', 'specs' => ['650W', '1 toc do', 'Xay sinh to'], 'price' => 799000, 'stock' => 10, 'tags' => ['Philips', 'xay cam tay', 'nhanh gon']],
                    ['name' => 'Sunhouse Induction Cooker SHD6872', 'brand' => 'Sunhouse', 'specs' => ['2000W', 'Mat kinh', 'Bep don'], 'price' => 899000, 'stock' => 8, 'tags' => ['bep tu', 'Sunhouse', 'gia dinh']],
                    ['name' => 'Kangaroo Electric Lunch Box', 'brand' => 'Kangaroo', 'specs' => ['270W', '1.5L', 'Ham nong do an'], 'price' => 590000, 'stock' => 13, 'tags' => ['hop com dien', 'mang di lam', 'Kangaroo']],
                    ['name' => 'Bluestone Toaster 2 Slice', 'brand' => 'Bluestone', 'specs' => ['700W', '2 khe', 'Nuong banh mi'], 'price' => 649000, 'stock' => 11, 'tags' => ['toaster', 'banh mi', 'an sang']],
                ],
            ],
            [
                'product_type' => 'Hop dung va binh nuoc',
                'spec_labels' => ['Chat lieu', 'Dung tich', 'Phu hop cho'],
                'categories' => ['Gia dung nha bep', 'Hop dung va binh nuoc'],
                'focus_tags' => ['mang theo', 'van phong', 'gia dinh', 'meal prep'],
                'items' => [
                    ['name' => 'LocknLock Bottle 700ml', 'brand' => 'LocknLock', 'specs' => ['Tritan', '700ml', 'Mang di hoc va lam'], 'price' => 169000, 'stock' => 20, 'tags' => ['binh nuoc', 'LocknLock', 'hang ngay']],
                    ['name' => 'LocknLock Glass Container 650ml', 'brand' => 'LocknLock', 'specs' => ['Thuy tinh', '650ml', 'Dung do an'], 'price' => 129000, 'stock' => 24, 'tags' => ['hop dung', 'meal prep', 'thuy tinh']],
                    ['name' => 'Duy Tan 3 Tier Lunch Box', 'brand' => 'Duy Tan', 'specs' => ['Nhua PP', '3 tang', 'Mang com'], 'price' => 99000, 'stock' => 26, 'tags' => ['hop com', 'mang di lam', 'gia re']],
                    ['name' => 'Thermos JNL 500ml', 'brand' => 'Thermos', 'specs' => ['Thep khong gi', '500ml', 'Giu nhiet 6 gio'], 'price' => 790000, 'stock' => 9, 'tags' => ['giu nhiet', 'cao cap', 'Thermos']],
                    ['name' => 'Elmich Vacuum Flask 520ml', 'brand' => 'Elmich', 'specs' => ['Inox 304', '520ml', 'Giu nong lanh'], 'price' => 359000, 'stock' => 14, 'tags' => ['binh giu nhiet', 'Elmich', 'hang ngay']],
                    ['name' => 'Inochi Storage Box 10L', 'brand' => 'Inochi', 'specs' => ['Nhua PP', '10L', 'Luu tru do dung'], 'price' => 119000, 'stock' => 19, 'tags' => ['Inochi', 'hop dung', 'sap xep nha']],
                ],
            ],
            [
                'product_type' => 'Van phong pham',
                'spec_labels' => ['Loai', 'Quy cach', 'Phu hop cho'],
                'categories' => ['Van phong pham va lifestyle', 'But va dung cu viet', 'Phu kien ban hoc'],
                'focus_tags' => ['hoc tap', 'van phong', 'ghi chu', 'thi cu'],
                'items' => [
                    ['name' => 'Thien Long Gel Pen TL-027', 'brand' => 'Thien Long', 'specs' => ['But gel', '0.5mm', 'Ghi chu hang ngay'], 'price' => 5000, 'stock' => 120, 'tags' => ['but gel', 'thi cu', 'gia re']],
                    ['name' => 'Pilot G2 0.5', 'brand' => 'Pilot', 'specs' => ['But gel', '0.5mm', 'Ghi mem'], 'price' => 32000, 'stock' => 48, 'tags' => ['Pilot', 'cao cap', 'ghi chu']],
                    ['name' => 'Pentel EnerGel 0.5', 'brand' => 'Pentel', 'specs' => ['But nuoc', '0.5mm', 'Muc kho nhanh'], 'price' => 35000, 'stock' => 42, 'tags' => ['Pentel', 'hoc tap', 'muc gel']],
                    ['name' => 'Sharpie Permanent Marker Black', 'brand' => 'Sharpie', 'specs' => ['But marker', '1 cay', 'Danh dau vat dung'], 'price' => 29000, 'stock' => 38, 'tags' => ['Sharpie', 'marker', 'van phong']],
                    ['name' => 'Thien Long Highlighter HL-03 Set 5', 'brand' => 'Thien Long', 'specs' => ['Highlighter', 'Bo 5', 'Danh dau bai hoc'], 'price' => 39000, 'stock' => 44, 'tags' => ['highlight', 'on thi', 'Thien Long']],
                    ['name' => 'Casio fx-580VN X', 'brand' => 'Casio', 'specs' => ['May tinh', '1 may', 'Hoc sinh sinh vien'], 'price' => 689000, 'stock' => 18, 'tags' => ['Casio', 'may tinh', 'thi cu']],
                    ['name' => 'Maped Geometry Box 8 Mon', 'brand' => 'Maped', 'specs' => ['Bo eke', '8 mon', 'Toan hoc'], 'price' => 79000, 'stock' => 24, 'tags' => ['Maped', 'hoc tap', 'hinh hoc']],
                    ['name' => 'Deli Stapler 0314', 'brand' => 'Deli', 'specs' => ['Bam kim', '1 cai', 'Van phong'], 'price' => 45000, 'stock' => 33, 'tags' => ['Deli', 'bam kim', 'ban hoc']],
                ],
            ],
            [
                'product_type' => 'Vo tap va giay in',
                'spec_labels' => ['Loai', 'Quy cach', 'Phu hop cho'],
                'categories' => ['Van phong pham va lifestyle', 'Vo tap va giay in'],
                'focus_tags' => ['hoc tap', 'ghi chu', 'in an', 'van phong'],
                'items' => [
                    ['name' => 'Campus Notebook B5 200 Pages', 'brand' => 'Campus', 'specs' => ['Vo B5', '200 trang', 'Ghi chu mon hoc'], 'price' => 39000, 'stock' => 35, 'tags' => ['Campus', 'vo dep', 'hoc sinh']],
                    ['name' => 'Hong Ha Notebook 200 Pages', 'brand' => 'Hong Ha', 'specs' => ['Vo hoc sinh', '200 trang', 'Ghi chu hang ngay'], 'price' => 22000, 'stock' => 52, 'tags' => ['Hong Ha', 'gia tot', 'vo hoc sinh']],
                    ['name' => 'Double A A4 70gsm 500 Sheets', 'brand' => 'Double A', 'specs' => ['Giay A4', '500 to', 'May in va photocopy'], 'price' => 89000, 'stock' => 27, 'tags' => ['Double A', 'giay in', 'van phong']],
                    ['name' => 'IK Plus A4 80gsm 500 Sheets', 'brand' => 'IK Plus', 'specs' => ['Giay A4', '500 to', 'In mau va van ban'], 'price' => 99000, 'stock' => 23, 'tags' => ['IK Plus', 'giay in', '80gsm']],
                    ['name' => 'Flashcard 100 The', 'brand' => 'Deli', 'specs' => ['Flashcard', '100 the', 'Hoc tu vung'], 'price' => 25000, 'stock' => 41, 'tags' => ['flashcard', 'hoc tap', 'ghi nho']],
                    ['name' => 'Sticky Notes 5 Mau', 'brand' => 'Deli', 'specs' => ['Giay note', '5 tap', 'Danh dau cong viec'], 'price' => 28000, 'stock' => 39, 'tags' => ['sticky notes', 'note', 'van phong']],
                ],
            ],
            [
                'product_type' => 'Phu kien hoc tap',
                'spec_labels' => ['Loai', 'Chat lieu', 'Phu hop cho'],
                'categories' => ['Van phong pham va lifestyle', 'Hop but va balo nho', 'Phu kien ban hoc'],
                'focus_tags' => ['mang theo', 'hoc tap', 'ban hoc', 'di hoc'],
                'items' => [
                    ['name' => 'Deli Pencil Case Large', 'brand' => 'Deli', 'specs' => ['Hop but', 'Vai canvas', 'Hoc sinh sinh vien'], 'price' => 49000, 'stock' => 28, 'tags' => ['hop but', 'Deli', 'ban hoc']],
                    ['name' => 'Artline Mesh Pouch', 'brand' => 'Artline', 'specs' => ['Tui but', 'Luoi PVC', 'Mang theo hang ngay'], 'price' => 39000, 'stock' => 24, 'tags' => ['tui but', 'Artline', 'gai trai deu dung']],
                    ['name' => 'Sun Eight Pencil Box', 'brand' => 'Sun Eight', 'specs' => ['Hop but', 'Nhua ABS', 'Tre em'], 'price' => 59000, 'stock' => 19, 'tags' => ['hop but tre em', 'Sun Eight', 'mau sac']],
                    ['name' => 'Sakos Mini Backpack 10L', 'brand' => 'Sakos', 'specs' => ['Balo mini', '10L', 'Mang sach vo'], 'price' => 329000, 'stock' => 12, 'tags' => ['balo nho', 'Sakos', 'di hoc']],
                    ['name' => 'Mikkor Pencil Pouch', 'brand' => 'Mikkor', 'specs' => ['Tui but', 'Vai polyester', 'Ban hoc gon gang'], 'price' => 79000, 'stock' => 17, 'tags' => ['Mikkor', 'tui but', 'hoc tap']],
                    ['name' => 'Canvas Study Tote Bag', 'brand' => 'Generic', 'specs' => ['Tui tote', 'Canvas', 'Mang tai lieu'], 'price' => 119000, 'stock' => 14, 'tags' => ['tote bag', 'hoc sinh', 'phu kien']],
                ],
            ],
        ];
    }
}
