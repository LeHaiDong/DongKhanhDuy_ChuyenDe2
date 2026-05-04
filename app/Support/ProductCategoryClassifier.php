<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProductCategoryClassifier
{
    public const DEFAULT_SLUG = 'bach-hoa-online';

    /**
     * Ordered slugs used for visible customer categories.
     *
     * @return string[]
     */
    public static function visibleCategorySlugs(): array
    {
        return [
            'thoi-trang-nam',
            'dien-thoai-phu-kien',
            'thiet-bi-dien-tu',
            'may-tinh-laptop',
            'may-anh-may-quay-phim',
            'dong-ho',
            'giay-dep-nam',
            'thiet-bi-dien-gia-dung',
            'the-thao-du-lich',
            'o-to-xe-may-xe-dap',
            'thoi-trang-nu',
            'me-be',
            'nha-cua-doi-song',
            'sac-dep',
            'suc-khoe',
            'giay-dep-nu',
            'tui-vi-nu',
            'phu-kien-trang-suc-nu',
            'bach-hoa-online',
            'nha-sach-online',
        ];
    }

    /**
     * @param  Product|array<string, mixed>  $product
     */
    public function classify(Product|array $product): string
    {
        $data = $this->extractData($product);
        $productType = $this->normalize((string) Arr::get($data, 'product_type', ''));

        if ($productType !== '') {
            $mappedSlug = $this->slugFromProductType($productType);
            if ($mappedSlug) {
                return $mappedSlug;
            }
        }

        $text = $this->normalize(implode(' ', array_filter([
            (string) Arr::get($data, 'name', ''),
            (string) Arr::get($data, 'brand', ''),
            (string) Arr::get($data, 'product_type', ''),
            (string) Arr::get($data, 'mount_type', ''),
            (string) Arr::get($data, 'focal_length', ''),
            (string) Arr::get($data, 'max_aperture', ''),
            (string) Arr::get($data, 'description', ''),
            (string) Arr::get($data, 'search_keywords', ''),
            (string) Arr::get($data, 'supplier', ''),
        ])));

        foreach ($this->keywordRules() as $slug => $needles) {
            if ($this->containsAny($text, $needles)) {
                return $slug;
            }
        }

        return self::DEFAULT_SLUG;
    }

    /**
     * @param  Product|array<string, mixed>  $product
     * @return array<string, mixed>
     */
    private function extractData(Product|array $product): array
    {
        if ($product instanceof Product) {
            return [
                'name' => $product->name,
                'brand' => $product->brand,
                'product_type' => $product->product_type,
                'mount_type' => $product->mount_type,
                'focal_length' => $product->focal_length,
                'max_aperture' => $product->max_aperture,
                'description' => $product->description,
                'search_keywords' => $product->search_keywords,
                'supplier' => $product->supplier,
            ];
        }

        return $product;
    }

    private function slugFromProductType(string $productType): ?string
    {
        return [
            'thoi trang nam' => 'thoi-trang-nam',
            'dien thoai smartphone' => 'dien-thoai-phu-kien',
            'dien thoai phu kien' => 'dien-thoai-phu-kien',
            'thiet bi dien tu' => 'thiet-bi-dien-tu',
            'tai nghe tws' => 'thiet-bi-dien-tu',
            'tai nghe headset' => 'thiet-bi-dien-tu',
            'loa bluetooth' => 'thiet-bi-dien-tu',
            'may tinh bang' => 'may-tinh-laptop',
            'may tinh laptop' => 'may-tinh-laptop',
            'may anh may quay phim' => 'may-anh-may-quay-phim',
            'dong ho' => 'dong-ho',
            'giay dep nam' => 'giay-dep-nam',
            'sac va pin du phong' => 'dien-thoai-phu-kien',
            'gia dung nha bep' => 'thiet-bi-dien-gia-dung',
            'thiet bi dien gia dung' => 'thiet-bi-dien-gia-dung',
            'the thao du lich' => 'the-thao-du-lich',
            'o to xe may xe dap' => 'o-to-xe-may-xe-dap',
            'thoi trang nu' => 'thoi-trang-nu',
            'me be' => 'me-be',
            'ta bim em be' => 'me-be',
            'sua cong thuc' => 'me-be',
            'do dung cho be' => 'me-be',
            'nha cua doi song' => 'nha-cua-doi-song',
            'hop dung va binh nuoc' => 'nha-cua-doi-song',
            'sac dep' => 'sac-dep',
            'suc khoe' => 'suc-khoe',
            'khau trang' => 'suc-khoe',
            'rua tay sat khuan' => 'suc-khoe',
            'cham soc ca nhan' => 'suc-khoe',
            'giay dep nu' => 'giay-dep-nu',
            'tui vi nu' => 'tui-vi-nu',
            'phu kien trang suc nu' => 'phu-kien-trang-suc-nu',
            'bach hoa online' => 'bach-hoa-online',
            'sua tuoi va sua hat' => 'bach-hoa-online',
            'ngu coc dinh duong' => 'bach-hoa-online',
            'snack va banh keo' => 'bach-hoa-online',
            'keo va socola' => 'bach-hoa-online',
            'mi va chao an lien' => 'bach-hoa-online',
            'nha sach online' => 'nha-sach-online',
            'van phong pham' => 'nha-sach-online',
            'vo tap va giay in' => 'nha-sach-online',
            'phu kien hoc tap' => 'nha-sach-online',
        ][$productType] ?? null;
    }

    /**
     * @return array<string, string[]>
     */
    private function keywordRules(): array
    {
        return [
            'suc-khoe' => ['khau trang', 'n95', 'kf94', 'suc khoe', 'sat khuan', 'rua tay', 'dettol', 'lifebuoy', 'omron', 'urgo', 'vitamin', 'y te'],
            'me-be' => ['sua cong thuc', 'similac', 'enfa', 'nan ', 'aptamil', 'pediasure', 'growplus', 'friso', 'meiji', 'ta bim', 'baby', 'binh sua', 'mamamy', 'mastela', 'zaracos'],
            'bach-hoa-online' => ['bach hoa', 'snack', 'banh', 'keo', 'socola', 'hao hao', 'acecook', 'oreo', 'sua tuoi', 'sua hat', 'ngu coc', 'ovaltine', 'quaker', 'ca phe', 'dau an', 'bot giat'],
            'nha-sach-online' => ['sach', 'book', 'van phong pham', 'but', 'vo tap', 'giay in', 'double a', 'campus', 'flashcard', 'casio', 'pilot', 'pentel', 'thien long', 'deli', 'notebook', 'hoc tap'],
            'sac-dep' => ['sac dep', 'son', 'serum', 'skincare', 'kem chong nang', 'sua rua mat', 'mat na', 'mascara', 'bioderma', 'cetaphil', 'hada labo', 'mediheal', 'maybelline'],
            'tui-vi-nu' => ['tui xach', 'tui vi', 'vi nu', 'tui deo cheo', 'tui tote', 'handbag', 'bucket'],
            'phu-kien-trang-suc-nu' => ['trang suc', 'bong tai', 'day chuyen', 'nhan nu', 'kep toc', 'bracelet', 'vong tay', 'lac chan'],
            'giay-dep-nu' => ['giay dep nu', 'sandal nu', 'cao got', 'bup be', 'mary jane', 'sneaker nu'],
            'giay-dep-nam' => ['giay dep nam', 'giay the thao nam', 'giay tay nam', 'dep nam', 'crocs classic nam'],
            'dong-ho' => ['dong ho', 'watch', 'g shock', 'orient', 'garmin', 'smart band', 'apple watch', 'galaxy watch', 'redmi watch'],
            'may-anh-may-quay-phim' => ['may anh', 'may quay', 'camera', 'gopro', 'instax', 'tripod', 'canon eos', 'sony zv', 'handycam', 'dji osmo'],
            'may-tinh-laptop' => ['laptop', 'macbook', 'ideapad', 'aspire', 'tuf gaming', 'pavilion', 'dell 24 inch', 'ssd', 'ram', 'tablet', 'ipad', 'galaxy tab', 'xiaomi pad', 'tab m11', 'redmi pad'],
            'dien-thoai-phu-kien' => ['dien thoai', 'smartphone', 'iphone', 'galaxy', 'redmi', 'oppo', 'realme', 'cap sac', 'cu sac', 'pin du phong', 'power bank', 'op lung', 'adapter'],
            'thiet-bi-dien-tu' => ['tai nghe', 'headphone', 'airpods', 'buds', 'loa', 'speaker', 'bluetooth', 'webcam', 'micro thu am', 'microphone', 'kindle', 'tivi', 'tv', 'camera an ninh', 'chuot', 'ban phim'],
            'thiet-bi-dien-gia-dung' => ['noi chien', 'noi com', 'am sieu toc', 'may xay', 'may hut bui', 'ban ui', 'bep tu', 'may say toc', 'loc khong khi', 'thiet bi dien gia dung'],
            'nha-cua-doi-song' => ['nha cua', 'doi song', 'hop dung', 'hop com', 'binh nuoc', 'binh giu nhiet', 'ke sach', 'chan ga', 'cay lau nha', 'ga goi', 'cho chong dinh'],
            'the-thao-du-lich' => ['the thao', 'du lich', 'vali', 'balo du lich', 'yoga', 'cau long', 'leu cam trai', 'travel', 'kinh boi', 'tui ngu'],
            'o-to-xe-may-xe-dap' => ['xe may', 'xe dap', 'o to', 'mu bao hiem', 'helmet', 'camera hanh trinh', 'bom lop', 'ao mua', 'gia do dien thoai xe may', 'gang tay xe may'],
            'thoi-trang-nu' => ['thoi trang nu', 'dam nu', 'vay nu', 'chan vay', 'ao kieu nu', 'blazer nu', 'croptop', 'cardigan nu', 'culottes'],
            'thoi-trang-nam' => ['thoi trang nam', 'ao polo', 'ao thun nam', 'quan jean nam', 'so mi nam', 'kaki nam', 'hoodie nam', 'jogger nam', 'ao khoac nam'],
        ];
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
        $value = Str::of($value)->ascii()->lower()->value();
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }
}
