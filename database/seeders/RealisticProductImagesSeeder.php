<?php

namespace Database\Seeders;

use App\Models\CameraLens;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RealisticProductImagesSeeder extends Seeder
{
    public function run(): void
    {
        $imageDir = public_path('catalog/product-images');

        if (!is_dir($imageDir)) {
            mkdir($imageDir, 0755, true);
        }

        $imageMap = $this->imageMap();

        foreach (Category::all() as $category) {
            $key = $this->matchImageKey($category->name, $category->name, $imageMap);
            $path = $this->ensureImage($key, $imageMap[$key]['query'], $imageDir);

            if ($path) {
                $category->update(['image' => $path]);
            }
        }

        CameraLens::chunkById(50, function ($products) use ($imageMap, $imageDir) {
            foreach ($products as $product) {
                [$key, $query] = $this->productImageTarget($product, $imageMap);
                $path = $this->ensureImage($key, $query, $imageDir);

                if ($path) {
                    $product->update(['image' => $path]);
                }
            }
        });
    }

    private function ensureImage(string $key, string $query, string $imageDir): ?string
    {
        foreach (glob($imageDir . DIRECTORY_SEPARATOR . $key . '.*') ?: [] as $existingPath) {
            if (filesize($existingPath) > 10000) {
                return 'catalog/product-images/' . basename($existingPath);
            }
        }

        if ($path = $this->downloadBingImage($key, $query, $imageDir)) {
            return $path;
        }

        $url = 'https://loremflickr.com/800/800/' . Str::slug($query, ',') . '?lock=' . crc32($key);
        $relativePath = 'catalog/product-images/' . $key . '.jpg';
        $absolutePath = public_path($relativePath);

        try {
            $response = Http::timeout(25)->withOptions(['allow_redirects' => true])->get($url);

            if (!$response->successful() || strlen($response->body()) < 10000) {
                return null;
            }

            file_put_contents($absolutePath, $response->body());

            return $relativePath;
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }
    }

    private function downloadBingImage(string $key, string $query, string $imageDir): ?string
    {
        foreach ($this->bingImageUrls($query) as $imageUrl) {
            try {
                $response = Http::timeout(25)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                    ])
                    ->withOptions(['allow_redirects' => true])
                    ->get($imageUrl);

                if (!$response->successful() || strlen($response->body()) < 10000) {
                    continue;
                }

                $contentType = strtolower((string) $response->header('Content-Type'));
                $extension = match (true) {
                    str_contains($contentType, 'png') => 'png',
                    str_contains($contentType, 'webp') => 'webp',
                    str_contains($contentType, 'jpeg'), str_contains($contentType, 'jpg') => 'jpg',
                    default => $this->extensionFromUrl($imageUrl),
                };

                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    continue;
                }

                $extension = $extension === 'jpeg' ? 'jpg' : $extension;
                $relativePath = 'catalog/product-images/' . $key . '.' . $extension;
                file_put_contents(public_path($relativePath), $response->body());

                return $relativePath;
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return null;
    }

    private function bingImageUrls(string $query): array
    {
        try {
            $response = Http::timeout(20)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
                ->get('https://www.bing.com/images/search', [
                    'q' => $query,
                    'form' => 'HDRSC2',
                    'first' => 1,
                ]);

            if (!$response->successful()) {
                return [];
            }

            preg_match_all('/murl&quot;:&quot;([^&]+)&quot;/', $response->body(), $matches);

            return collect($matches[1] ?? [])
                ->map(fn ($url) => html_entity_decode($url, ENT_QUOTES | ENT_HTML5))
                ->map(fn ($url) => stripslashes($url))
                ->filter(fn ($url) => Str::startsWith($url, ['http://', 'https://']))
                ->filter(fn ($url) => !str_contains(strtolower($url), '.svg'))
                ->unique()
                ->take(12)
                ->values()
                ->all();
        } catch (\Throwable $exception) {
            report($exception);

            return [];
        }
    }

    private function extensionFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? $extension : '';
    }

    private function matchImageKey(string $name, ?string $type, array $imageMap): string
    {
        $text = Str::of($name . ' ' . $type)->ascii()->lower()->value();

        foreach ($imageMap as $key => $item) {
            foreach ($item['needles'] as $needle) {
                if (str_contains($text, $needle)) {
                    return $key;
                }
            }
        }

        return 'marketplace-product';
    }

    private function productImageTarget(CameraLens $product, array $imageMap): array
    {
        $key = 'real-' . Str::slug($product->name . '-' . $product->brand);
        $query = $this->defaultProductQuery($product);
        $name = Str::of($product->name)->ascii()->lower()->squish()->value();

        foreach ($this->specificProductQueries() as $needle => $specificQuery) {
            if (str_contains($name, $needle)) {
                return [$key, $specificQuery];
            }
        }

        return [$key, $query];
    }

    private function defaultProductQuery(CameraLens $product): string
    {
        $name = trim($product->name . ' ' . $product->brand);
        $asciiName = Str::of($name)->ascii()->toString();
        $hasVietnameseCharacters = $asciiName !== $name;

        return $hasVietnameseCharacters
            ? $name . ' ảnh sản phẩm'
            : $name . ' product image white background';
    }

    private function specificProductQueries(): array
    {
        return [
            'ao polo nam telab' => 'Áo polo nam Telab ảnh sản phẩm',
            'ao thun nam coolmate' => 'Áo thun nam Coolmate ảnh sản phẩm',
            'quan jean nam routine' => 'Quần jean nam Routine ảnh sản phẩm',
            'dam nu maybi' => 'Đầm nữ Maybi ảnh sản phẩm',
            'chan vay nu hnoss' => 'Chân váy nữ Hnoss ảnh sản phẩm',
            'giay sneaker nu juno' => 'Giày sneaker nữ Juno ảnh sản phẩm',
            'giay the thao nam biti' => 'Giày thể thao nam Biti Hunter ảnh sản phẩm',
            'tui xach nu juno' => 'Túi xách nữ Juno ảnh sản phẩm',
            'vong tay nu pnj' => 'Vòng tay nữ PNJ ảnh sản phẩm',
            'iphone 14 128gb' => 'iPhone 14 128GB product image white background',
            'op lung iphone 14 uag' => 'Ốp lưng iPhone 14 UAG ảnh sản phẩm',
            'tai nghe sony wh ch720n' => 'Sony WH-CH720N product image white background',
            'tivi samsung 43 inch' => 'Samsung 43 inch smart TV product image white background',
            'laptop asus vivobook 14' => 'Asus Vivobook 14 X1404ZA product image',
            'laptop dell inspiron 15' => 'Dell Inspiron 15 laptop product image white background',
            'may anh canon eos m50' => 'Canon EOS M50 product image white background',
            'may quay sony handycam' => 'Sony Handycam camcorder product image white background',
            'dong ho casio mtp' => 'Casio MTP watch product image white background',
            'dong ho thong minh xiaomi band' => 'Xiaomi Smart Band product image white background',
            'noi chien khong dau locknlock' => 'Nồi chiên không dầu LocknLock ảnh sản phẩm',
            'binh sua pigeon 160ml' => 'Bình sữa Pigeon 160ml ảnh sản phẩm',
            'sua bot abbott grow' => 'Sữa bột Abbott Grow ảnh sản phẩm',
            'binh giu nhiet locknlock' => 'Bình giữ nhiệt LocknLock ảnh sản phẩm',
            'son kem black rouge' => 'Son kem Black Rouge ảnh sản phẩm',
            'kem chong nang anessa' => 'Kem chống nắng Anessa ảnh sản phẩm',
            'khau trang 3m kf94' => 'Khẩu trang 3M KF94 9013 ảnh sản phẩm',
            '3m aura 1860' => '3M Aura 1860 N95 respirator product image',
            '3m kf94' => 'Khẩu trang 3M KF94 9013 ảnh sản phẩm',
            'bong da dong luc' => 'Bóng đá Động Lực ảnh sản phẩm',
            'vali du lich sakos' => 'Vali du lịch Sakos ảnh sản phẩm',
            'mu bao hiem andes' => 'Mũ bảo hiểm Andes ảnh sản phẩm',
            'banh oreo hop' => 'Bánh Oreo hộp ảnh sản phẩm',
            'mi hao hao tom chua cay' => 'Mì Hảo Hảo tôm chua cay ảnh sản phẩm',
            'sach dac nhan tam' => 'Sách Đắc Nhân Tâm ảnh sản phẩm',
            'sach nha gia kim' => 'Sách Nhà Giả Kim ảnh sản phẩm',
        ];
    }

    private function imageMap(): array
    {
        return [
            'mens-polo-shirt' => ['query' => 'polo shirt men', 'needles' => ['ao polo', 'polo']],
            'mens-tshirt' => ['query' => 'men t shirt fashion', 'needles' => ['ao thun']],
            'mens-jeans' => ['query' => 'men jeans fashion', 'needles' => ['quan jean']],
            'mens-fashion' => ['query' => 'men fashion clothing', 'needles' => ['thoi trang nam']],
            'womens-dress' => ['query' => 'women dress fashion', 'needles' => ['dam nu', 'vay']],
            'womens-fashion' => ['query' => 'women fashion clothing', 'needles' => ['thoi trang nu']],
            'womens-shoes' => ['query' => 'women shoes sneaker', 'needles' => ['giay sneaker nu', 'giay dep nu']],
            'mens-shoes' => ['query' => 'men sport shoes', 'needles' => ['giay the thao nam', 'giay dep nam']],
            'womens-bag' => ['query' => 'women handbag', 'needles' => ['tui xach', 'tui vi']],
            'jewelry' => ['query' => 'women jewelry bracelet', 'needles' => ['trang suc', 'vong tay']],
            'smartphone' => ['query' => 'smartphone iphone', 'needles' => ['dien thoai', 'smartphone', 'iphone', 'op lung']],
            'tablet' => ['query' => 'tablet ipad', 'needles' => ['may tinh bang', 'tablet', 'ipad']],
            'headphones' => ['query' => 'headphones product', 'needles' => ['tai nghe', 'headset', 'airpods', 'buds']],
            'speaker' => ['query' => 'bluetooth speaker', 'needles' => ['loa', 'speaker']],
            'charger' => ['query' => 'phone charger power bank', 'needles' => ['sac', 'pin du phong', 'power bank', 'adapter']],
            'television' => ['query' => 'television tv product', 'needles' => ['tivi', 'tv']],
            'electronics' => ['query' => 'consumer electronics product', 'needles' => ['thiet bi dien tu']],
            'laptop' => ['query' => 'laptop computer', 'needles' => ['laptop', 'may tinh']],
            'camera' => ['query' => 'camera photography', 'needles' => ['may anh', 'may quay', 'camera']],
            'watch' => ['query' => 'wrist watch product', 'needles' => ['dong ho', 'watch']],
            'milk-powder' => ['query' => 'baby milk powder can', 'needles' => ['sua bot', 'sua cong thuc', 'abbott grow']],
            'milk-carton' => ['query' => 'milk carton', 'needles' => ['sua tuoi', 'sua hat', 'green farm', 'true milk']],
            'cereal' => ['query' => 'breakfast cereal', 'needles' => ['ngu coc', 'cereal', 'bot an dam']],
            'cookies' => ['query' => 'cookies biscuits snack', 'needles' => ['oreo', 'banh quy', 'banh bong lan']],
            'snack' => ['query' => 'potato chips snack', 'needles' => ['snack', 'pringles', 'poca', 'oishi']],
            'chocolate-candy' => ['query' => 'chocolate candy', 'needles' => ['keo', 'socola', 'mentos', 'halls']],
            'instant-noodles' => ['query' => 'instant noodles package', 'needles' => ['mi ', 'hao hao', 'omachi', 'kokomi', 'chao']],
            'face-mask' => ['query' => 'medical face mask', 'needles' => ['khau trang', 'mask']],
            'hand-sanitizer' => ['query' => 'hand sanitizer bottle', 'needles' => ['sat khuan', 'rua tay', 'lifebuoy', 'dettol']],
            'beauty' => ['query' => 'beauty cosmetics lipstick sunscreen', 'needles' => ['sac dep', 'son', 'kem chong nang', 'skincare', 'anessa']],
            'personal-care' => ['query' => 'shampoo personal care', 'needles' => ['cham soc ca nhan', 'dau goi', 'sua tam', 'kem danh rang']],
            'baby-diaper' => ['query' => 'baby diapers', 'needles' => ['ta bim', 'bobby', 'huggies', 'pampers']],
            'baby-bottle' => ['query' => 'baby bottle', 'needles' => ['binh sua', 'do dung cho be', 'pigeon']],
            'mother-baby' => ['query' => 'mother baby products', 'needles' => ['me va be']],
            'kitchen-appliance' => ['query' => 'kitchen appliance air fryer', 'needles' => ['gia dung', 'nha bep', 'noi chien', 'bep', 'am sieu toc']],
            'water-bottle' => ['query' => 'water bottle thermos', 'needles' => ['binh nuoc', 'binh giu nhiet']],
            'lunch-box' => ['query' => 'lunch box food container', 'needles' => ['hop dung', 'hop com']],
            'home-living' => ['query' => 'home living decor products', 'needles' => ['nha cua', 'doi song']],
            'stationery' => ['query' => 'stationery pen notebook', 'needles' => ['van phong pham', 'but', 'dung cu viet', 'casio']],
            'notebook' => ['query' => 'notebook paper stationery', 'needles' => ['vo tap', 'giay in', 'flashcard']],
            'pencil-case' => ['query' => 'pencil case backpack', 'needles' => ['hop but', 'tui but', 'balo', 'phu kien hoc tap']],
            'football' => ['query' => 'football soccer ball', 'needles' => ['bong da', 'the thao']],
            'suitcase' => ['query' => 'travel suitcase luggage', 'needles' => ['vali', 'du lich']],
            'helmet' => ['query' => 'motorcycle helmet', 'needles' => ['mu bao hiem', 'xe may']],
            'book' => ['query' => 'book cover reading', 'needles' => ['sach', 'nha sach']],
            'marketplace-product' => ['query' => 'online shopping product', 'needles' => []],
        ];
    }
}
