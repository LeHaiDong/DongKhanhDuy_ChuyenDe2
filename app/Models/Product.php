<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $table = 'camera_lenses';

    protected $fillable = [
        'seller_shop_id',
        'name',
        'brand',
        'product_type',
        'spec_label_1',
        'spec_label_2',
        'spec_label_3',
        'focal_length',
        'max_aperture',
        'mount_type',
        'price',
        'description',
        'search_keywords',
        'image',
        'stock_quantity',
        'is_active',
        'condition',
        'sku',
        'low_stock_threshold',
        'reserved_quantity',
        'cost_price',
        'supplier',
        'supplier_sku',
        'last_restocked_at',
        'reorder_point',
        'reorder_quantity',
        'stock_status',
        'stock_notes',
    ];

    protected $casts = [
        'seller_shop_id' => 'integer',
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
        'last_restocked_at' => 'date',
    ];

    public function getSpec1LabelAttribute()
    {
        return $this->beautifyCatalogText($this->spec_label_1 ?: 'Thong so 1');
    }

    public function getSpec2LabelAttribute()
    {
        return $this->beautifyCatalogText($this->spec_label_2 ?: 'Thong so 2');
    }

    public function getSpec3LabelAttribute()
    {
        return $this->beautifyCatalogText($this->spec_label_3 ?: 'Thong so 3');
    }

    public function getDisplayProductTypeAttribute()
    {
        return $this->beautifyCatalogText($this->product_type ?: 'San pham');
    }

    public function getDisplayConditionAttribute()
    {
        return match ($this->condition) {
            'used' => 'Đã sử dụng',
            'refurbished' => 'Tân trang',
            default => 'Mới',
        };
    }

    public function getDisplaySpecificationsAttribute()
    {
        return collect([
            ['label' => $this->spec1_label, 'value' => $this->focal_length],
            ['label' => $this->spec2_label, 'value' => $this->max_aperture],
            ['label' => $this->spec3_label, 'value' => $this->mount_type],
        ])->filter(function ($item) {
            return filled($item['value']);
        })->values();
    }

    public function getImageUrlAttribute()
    {
        return $this->resolveStoredImageUrl()
            ?: $this->productSpecificImageUrl()
            ?: $this->fallbackStockPhotoUrl()
            ?: $this->buildPlaceholderImageDataUri();
    }

    public function getFallbackImageUrlAttribute(): string
    {
        return $this->resolveStoredImageUrl()
            ?: $this->fallbackStockPhotoUrl()
            ?: $this->buildPlaceholderImageDataUri();
    }

    protected function resolveStoredImageUrl(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        if (Str::startsWith($this->image, '/')) {
            return asset(ltrim($this->image, '/'));
        }

        if (Str::startsWith($this->image, ['catalog/', 'images/', 'uploads/', 'storage/'])) {
            $publicPath = public_path(ltrim($this->image, '/'));

            if (file_exists($publicPath)) {
                return asset(ltrim($this->image, '/'));
            }

            return null;
        }

        $storagePath = storage_path('app/public/' . ltrim($this->image, '/'));

        if (file_exists($storagePath)) {
            $publicStoragePath = public_path('storage/' . ltrim($this->image, '/'));

            if (file_exists($publicStoragePath)) {
                return asset('storage/' . ltrim($this->image, '/'));
            }

            return url('product-media/' . ltrim($this->image, '/'));
        }

        return null;
    }

    public function getPlaceholderIconAttribute()
    {
        $type = Str::lower($this->product_type ?? '');

        return match (true) {
            Str::contains($type, ['dien thoai', 'smartphone', 'iphone', 'android']) => 'fas fa-mobile-alt',
            Str::contains($type, ['may tinh bang', 'tablet', 'ipad']) => 'fas fa-tablet-alt',
            Str::contains($type, ['tai nghe', 'headphone', 'headset', 'tws']) => 'fas fa-headphones',
            Str::contains($type, ['loa', 'speaker']) => 'fas fa-volume-up',
            Str::contains($type, ['sac', 'charger', 'pin du phong', 'power bank', 'adapter']) => 'fas fa-bolt',
            Str::contains($type, ['sua', 'dinh duong', 'ngu coc']) => 'fas fa-glass-whiskey',
            Str::contains($type, ['banh', 'snack', 'keo', 'socola', 'do an vat']) => 'fas fa-cookie-bite',
            Str::contains($type, ['khau trang', 'mask']) => 'fas fa-head-side-mask',
            Str::contains($type, ['sat khuan', 'rua tay', 'y te', 'cham soc']) => 'fas fa-pump-soap',
            Str::contains($type, ['ta', 'bim', 'me va be', 'em be']) => 'fas fa-baby',
            Str::contains($type, ['noi', 'chao', 'gia dung', 'nha bep']) => 'fas fa-blender',
            Str::contains($type, ['hop dung', 'binh nuoc']) => 'fas fa-box',
            Str::contains($type, ['but', 'vo', 'van phong', 'giay', 'balo']) => 'fas fa-pen-ruler',
            Str::contains($type, ['camera', 'may anh']) => 'fas fa-camera',
            default => 'fas fa-box-open',
        };
    }

    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, ',', '.') . ' VND';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getInStockAttribute()
    {
        return $this->stock_quantity > 0;
    }

    public function getStockAttribute()
    {
        return $this->stock_quantity;
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'camera_lens_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'camera_lens_id');
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class, 'camera_lens_id')->approved();
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites', 'camera_lens_id', 'user_id')->withTimestamps();
    }

    public function getFavoritesCountAttribute()
    {
        return $this->favorites()->count();
    }

    public function getAverageRatingAttribute()
    {
        return $this->approvedReviews()->avg('rating') ?: 0;
    }

    public function getReviewCountAttribute()
    {
        return $this->approvedReviews()->count();
    }

    public function getFormattedRatingAttribute()
    {
        return number_format($this->average_rating, 1);
    }

    public function getRatingStarsAttribute()
    {
        $rating = round($this->average_rating);

        return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
    }

    public function isFavoritedBy($userId): bool
    {
        return $this->favorites()->where('user_id', $userId)->exists();
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'camera_lens_categories', 'camera_lens_id', 'category_id');
    }

    public function sellerShop()
    {
        return $this->belongsTo(SellerShop::class);
    }

    public function primaryCategory()
    {
        return $this->categories()->orderBy('camera_lens_categories.created_at')->first();
    }

    public function getCategoryNamesAttribute()
    {
        return $this->categories->pluck('display_name')->implode(', ');
    }

    public function inCategory($categoryId)
    {
        return $this->categories()->where('categories.id', $categoryId)->exists();
    }

    public function scopeInCategory($query, $categoryId)
    {
        return $query->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        });
    }

    public function scopeInCategorySlug($query, $slug)
    {
        return $query->whereHas('categories', function ($q) use ($slug) {
            $q->where('categories.slug', $slug);
        });
    }

    public function scopeOfProductType($query, $productType)
    {
        return $query->where('product_type', $productType);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'camera_lens_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'camera_lens_id');
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock_quantity <= low_stock_threshold');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    public function scopeNeedsReorder($query)
    {
        return $query->whereRaw('stock_quantity <= reorder_point');
    }

    public function getAvailableStockAttribute()
    {
        return $this->stock_quantity - $this->reserved_quantity;
    }

    public function getIsInStockAttribute()
    {
        return $this->stock_quantity > 0;
    }

    public function getIsLowStockAttribute()
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function getIsOutOfStockAttribute()
    {
        return $this->stock_quantity <= 0;
    }

    public function getNeedsReorderAttribute()
    {
        return $this->stock_quantity <= $this->reorder_point;
    }

    public function getFormattedCostPriceAttribute()
    {
        if (!$this->cost_price) {
            return null;
        }

        return number_format($this->cost_price, 0, ',', '.') . ' VND';
    }

    public function getProfitMarginAttribute()
    {
        if (!$this->cost_price || $this->cost_price <= 0) {
            return null;
        }

        return round((($this->price - $this->cost_price) / $this->cost_price) * 100, 2);
    }

    public function updateStockStatus()
    {
        if ($this->stock_quantity <= 0) {
            $this->stock_status = 'out_of_stock';
        } elseif ($this->stock_quantity <= $this->low_stock_threshold) {
            $this->stock_status = 'low_stock';
        } else {
            $this->stock_status = 'in_stock';
        }

        $this->save();
    }

    public function reserveStock($quantity)
    {
        if ($this->available_stock < $quantity) {
            return false;
        }

        $this->increment('reserved_quantity', $quantity);

        return true;
    }

    public function releaseReservedStock($quantity)
    {
        $this->decrement('reserved_quantity', $quantity);

        if ($this->reserved_quantity < 0) {
            $this->update(['reserved_quantity' => 0]);
        }
    }

    public function addStock($quantity, $unitCost = null, $options = [])
    {
        return StockMovement::purchase($this, $quantity, $unitCost, $options);
    }

    public function removeStock($quantity, $orderId = null, $options = [])
    {
        if ($this->stock_quantity < $quantity) {
            return false;
        }

        return StockMovement::sale($this, $quantity, $orderId, $options);
    }

    public function adjustStock($quantity, $reason, $options = [])
    {
        return StockMovement::adjustment($this, $quantity, $reason, $options);
    }

    public function generateSku()
    {
        if ($this->sku) {
            return $this->sku;
        }

        $prefix = strtoupper(substr($this->brand, 0, 3));
        $suffix = str_pad($this->id, 4, '0', STR_PAD_LEFT);
        $sku = $prefix . '-' . $suffix;

        $this->update(['sku' => $sku]);

        return $sku;
    }

    protected function fallbackStockPhotoUrl(): ?string
    {
        $type = Str::of($this->product_type ?? '')->ascii()->lower()->value();

        $filename = match (true) {
            Str::contains($type, ['dien thoai', 'smartphone', 'iphone', 'android']) => 'smartphone.jpg',
            Str::contains($type, ['may tinh bang', 'tablet', 'ipad']) => 'tablet.jpg',
            Str::contains($type, ['tai nghe', 'headphone', 'headset', 'tws']) => 'audio.jpg',
            Str::contains($type, ['loa', 'speaker']) => 'speaker.jpg',
            Str::contains($type, ['sac', 'charger', 'pin du phong', 'power bank', 'adapter']) => 'charging.jpg',
            Str::contains($type, ['sua', 'dinh duong']) => 'milk.jpg',
            Str::contains($type, ['ngu coc', 'oat', 'cereal']) => 'cereal.jpg',
            Str::contains($type, ['banh', 'snack', 'keo', 'socola', 'mi', 'chao']) => 'snack.jpg',
            Str::contains($type, ['khau trang', 'sat khuan', 'rua tay', 'cham soc', 'y te']) => 'health.jpg',
            Str::contains($type, ['ta', 'bim', 'me va be', 'em be']) => 'baby.jpg',
            Str::contains($type, ['noi', 'chao', 'gia dung', 'nha bep', 'hop dung', 'binh nuoc']) => 'home.jpg',
            Str::contains($type, ['but', 'vo', 'van phong', 'giay', 'balo']) => 'stationery.jpg',
            default => null,
        };

        if (!$filename) {
            return null;
        }

        $relativePath = 'catalog/fallback/' . $filename;

        if (file_exists(public_path($relativePath))) {
            return asset($relativePath);
        }

        return null;
    }

    protected function productSpecificImageUrl(): ?string
    {
        $name = Str::of($this->name ?? '')->ascii()->lower()->squish()->value();

        $imageMap = [
            'iphone 14 pro' => 'https://fdn2.gsmarena.com/vv/pics/apple/apple-iphone-14-pro-1.jpg',
            'iphone 14' => 'https://fdn2.gsmarena.com/vv/pics/apple/apple-iphone-14-1.jpg',
            'iphone 15 plus' => 'https://fdn2.gsmarena.com/vv/pics/apple/apple-iphone-15-plus-1.jpg',
            'iphone 15' => 'https://fdn2.gsmarena.com/vv/pics/apple/apple-iphone-15-1.jpg',
            'iphone 13' => 'https://fdn2.gsmarena.com/vv/pics/apple/apple-iphone-13-01.jpg',
            'galaxy s24 ultra' => 'https://fdn2.gsmarena.com/vv/pics/samsung/samsung-galaxy-s24-ultra-5g-0.jpg',
            'galaxy s24 fe' => 'https://fdn2.gsmarena.com/vv/pics/samsung/samsung-galaxy-s24-fe-1.jpg',
            'galaxy a55' => 'https://fdn2.gsmarena.com/vv/pics/samsung/samsung-galaxy-a55-5g-1.jpg',
            'redmi note 13 pro' => 'https://fdn2.gsmarena.com/vv/pics/xiaomi/xiaomi-redmi-note-13-pro-5g-1.jpg',
            'xiaomi 14t' => 'https://fdn2.gsmarena.com/vv/pics/xiaomi/xiaomi-14t-1.jpg',
            'oppo reno12 f' => 'https://fdn2.gsmarena.com/vv/pics/oppo/oppo-reno12-f-5g-1.jpg',
            'realme 12+' => 'https://fdn2.gsmarena.com/vv/pics/realme/realme-12-plus-5g-1.jpg',
            'ipad 10' => 'https://fdn2.gsmarena.com/vv/pics/apple/apple-ipad-10-2.jpg',
            'ipad air m2' => 'https://fdn2.gsmarena.com/vv/pics/apple/apple-ipad-air-13-2024-1.jpg',
            'ipad mini 6' => 'https://fdn2.gsmarena.com/vv/pics/apple/apple-ipad-mini-2021-1.jpg',
            'galaxy tab a9' => 'https://fdn2.gsmarena.com/vv/pics/samsung/samsung-galaxy-tab-a9-1.jpg',
            'galaxy tab s9 fe' => 'https://fdn2.gsmarena.com/vv/pics/samsung/samsung-galaxy-tab-s9-fe-1.jpg',
            'xiaomi pad 6' => 'https://fdn2.gsmarena.com/vv/pics/xiaomi/xiaomi-pad-6-1.jpg',
            'airpods pro 2' => 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MQD83.jpg',
            'airpods 4' => 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/airpods-4-anc-select-202409.jpg',
            'galaxy buds fe' => 'https://images.samsung.com/is/image/samsung/p6pim/vn/sm-r400nzawxxv/gallery/vn-galaxy-buds-fe-r400-sm-r400nzawxxv-538477248',
            'jbl flip 6' => 'https://www.jbl.com/on/demandware.static/-/Sites-masterCatalog_Harman/default/dw4230e4e5/JBL_FLIP6_HERO_BLACK_29391_x1.png',
            'jbl go 4' => 'https://www.jbl.com/on/demandware.static/-/Sites-masterCatalog_Harman/default/dw99f47439/JBL_GO_4_HERO_BLACK_48144_x1.png',
        ];

        foreach ($imageMap as $needle => $url) {
            if (str_contains($name, $needle)) {
                return $url;
            }
        }

        return null;
    }

    protected function buildPlaceholderImageDataUri(): string
    {
        $palette = $this->placeholderPalette();
        $brand = $this->sanitizePlaceholderText($this->brand ?: 'Shop', 18);
        $type = $this->sanitizePlaceholderText($this->display_product_type, 18);
        $name = $this->sanitizePlaceholderText($this->name ?: 'San pham', 28);
        $price = $this->price ? number_format((float) $this->price, 0, ',', '.') . ' VND' : 'Gia cap nhat';
        $icon = $this->productIllustrationSvg();

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$palette['start']}"/>
      <stop offset="100%" stop-color="{$palette['end']}"/>
    </linearGradient>
  </defs>
  <rect width="800" height="800" rx="48" fill="url(#bg)"/>
  <circle cx="640" cy="150" r="115" fill="rgba(255,255,255,0.14)"/>
  <circle cx="170" cy="660" r="150" fill="rgba(255,255,255,0.08)"/>
  <rect x="72" y="72" width="184" height="46" rx="23" fill="rgba(255,255,255,0.16)"/>
  <text x="164" y="102" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="22" font-weight="700" fill="#FFFFFF">{$brand}</text>
  <rect x="72" y="164" width="656" height="348" rx="40" fill="rgba(255,255,255,0.12)"/>
  {$icon}
  <text x="400" y="420" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="42" font-weight="800" fill="#FFFFFF">{$type}</text>
  <text x="400" y="466" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="28" font-weight="600" fill="rgba(255,255,255,0.92)">{$name}</text>
  <rect x="72" y="566" width="294" height="96" rx="28" fill="rgba(255,255,255,0.18)"/>
  <text x="102" y="624" font-family="Arial, Helvetica, sans-serif" font-size="26" font-weight="700" fill="#FFFFFF">{$price}</text>
  <text x="72" y="726" font-family="Arial, Helvetica, sans-serif" font-size="24" fill="rgba(255,255,255,0.84)">MienTayShop catalog</text>
</svg>
SVG;

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
    }

    protected function productIllustrationSvg(): string
    {
        $text = Str::of(($this->product_type ?? '') . ' ' . ($this->name ?? ''))->ascii()->lower()->value();

        return match (true) {
            Str::contains($text, ['ao polo', 'ao thun', 'so mi', 'thoi trang nam']) => '<g transform="translate(280 210)" fill="none" stroke="#fff" stroke-width="18" stroke-linejoin="round"><path d="M70 20h100l44 44-44 54v138H70V118L26 64z" fill="rgba(255,255,255,.2)"/><path d="M94 22c15 35 52 35 68 0"/><path d="M70 118h100"/></g>',
            Str::contains($text, ['dam', 'vay', 'thoi trang nu']) => '<g transform="translate(280 205)" fill="none" stroke="#fff" stroke-width="18" stroke-linejoin="round"><path d="M120 22c-32 52-58 122-82 230h164C178 144 152 74 120 22z" fill="rgba(255,255,255,.2)"/><path d="M82 84h76"/><path d="M102 24h36"/></g>',
            Str::contains($text, ['giay', 'sneaker', 'dep']) => '<g transform="translate(245 280)" fill="none" stroke="#fff" stroke-width="18" stroke-linejoin="round"><path d="M42 92c66 32 166 28 260 12 22-4 30 8 34 26l6 30H28c-20 0-28-14-18-32z" fill="rgba(255,255,255,.22)"/><path d="M112 82l36 42M172 82l36 36"/></g>',
            Str::contains($text, ['dien thoai', 'iphone', 'smartphone']) => '<g transform="translate(315 188)" fill="none" stroke="#fff" stroke-width="18"><rect x="0" y="0" width="170" height="260" rx="30" fill="rgba(255,255,255,.18)"/><circle cx="85" cy="226" r="8" fill="#fff"/><path d="M62 32h46"/></g>',
            Str::contains($text, ['laptop', 'may tinh']) => '<g transform="translate(235 235)" fill="none" stroke="#fff" stroke-width="18" stroke-linejoin="round"><rect x="46" y="0" width="238" height="150" rx="16" fill="rgba(255,255,255,.16)"/><path d="M0 190h330l-34-40H34z" fill="rgba(255,255,255,.22)"/></g>',
            Str::contains($text, ['tivi', 'tv', 'thiet bi dien tu']) => '<g transform="translate(235 230)" fill="none" stroke="#fff" stroke-width="18"><rect x="0" y="0" width="330" height="190" rx="20" fill="rgba(255,255,255,.17)"/><path d="M120 226h90M165 190v36"/></g>',
            Str::contains($text, ['may anh', 'camera', 'quay phim']) => '<g transform="translate(240 245)" fill="none" stroke="#fff" stroke-width="18"><rect x="0" y="44" width="320" height="180" rx="28" fill="rgba(255,255,255,.18)"/><circle cx="160" cy="134" r="62"/><path d="M70 44l26-38h88l26 38M250 88h32"/></g>',
            Str::contains($text, ['dong ho', 'watch']) => '<g transform="translate(315 190)" fill="none" stroke="#fff" stroke-width="18"><path d="M60 0h70l14 76H46zM46 196h98l-14 76H60z" fill="rgba(255,255,255,.16)"/><circle cx="95" cy="136" r="70" fill="rgba(255,255,255,.16)"/><path d="M95 96v44l34 22"/></g>',
            Str::contains($text, ['tui', 'balo', 'vali']) => '<g transform="translate(265 230)" fill="none" stroke="#fff" stroke-width="18" stroke-linejoin="round"><rect x="20" y="68" width="250" height="180" rx="28" fill="rgba(255,255,255,.18)"/><path d="M88 68c0-44 114-44 114 0"/><path d="M72 248v24M218 248v24"/></g>',
            Str::contains($text, ['son', 'kem', 'sac dep', 'skincare']) => '<g transform="translate(290 220)" fill="none" stroke="#fff" stroke-width="18" stroke-linejoin="round"><rect x="20" y="100" width="88" height="168" rx="18" fill="rgba(255,255,255,.22)"/><path d="M42 100V44l44-30 22 86M164 56h102v212H164z" fill="rgba(255,255,255,.15)"/><path d="M164 112h102"/></g>',
            Str::contains($text, ['bong', 'the thao']) => '<g transform="translate(300 220)" fill="none" stroke="#fff" stroke-width="18"><circle cx="100" cy="100" r="92" fill="rgba(255,255,255,.18)"/><path d="M100 8v184M8 100h184M36 36c42 36 86 36 128 0M36 164c42-36 86-36 128 0"/></g>',
            Str::contains($text, ['sach', 'book']) => '<g transform="translate(250 215)" fill="none" stroke="#fff" stroke-width="18" stroke-linejoin="round"><path d="M40 0h126c28 0 48 20 48 48v206H88c-28 0-48-20-48-48z" fill="rgba(255,255,255,.18)"/><path d="M214 48c0-28 20-48 48-48h68v254h-116zM96 72h72M96 122h72"/></g>',
            Str::contains($text, ['sua', 'baby', 'me va be', 'binh sua']) => '<g transform="translate(310 205)" fill="none" stroke="#fff" stroke-width="18" stroke-linejoin="round"><path d="M74 36h82v44c38 22 54 62 54 112v84H20v-84c0-50 16-90 54-112z" fill="rgba(255,255,255,.18)"/><path d="M84 0h62v36H84zM62 150h106M62 206h106"/></g>',
            Str::contains($text, ['nha cua', 'gia dung', 'noi', 'chao', 'bep']) => '<g transform="translate(245 240)" fill="none" stroke="#fff" stroke-width="18" stroke-linejoin="round"><path d="M54 86h220v98c0 56-44 92-110 92S54 240 54 184z" fill="rgba(255,255,255,.18)"/><path d="M54 104H12M274 104h44M94 46h140M116 0c-20 24-20 42 0 66M190 0c-20 24-20 42 0 66"/></g>',
            Str::contains($text, ['khau trang', 'y te', 'suc khoe']) => '<g transform="translate(240 260)" fill="none" stroke="#fff" stroke-width="18"><path d="M54 42c74-36 168-36 242 0v126c-74 44-168 44-242 0z" fill="rgba(255,255,255,.18)"/><path d="M54 76H12v58h42M296 76h42v58h-42M100 92h150M100 138h150"/></g>',
            default => '<g transform="translate(285 220)" fill="none" stroke="#fff" stroke-width="18" stroke-linejoin="round"><rect x="20" y="70" width="230" height="190" rx="30" fill="rgba(255,255,255,.18)"/><path d="M20 112h230M82 70c0-60 106-60 106 0"/></g>',
        };
    }

    protected function placeholderPalette(): array
    {
        $type = Str::lower($this->product_type ?? '');

        return match (true) {
            Str::contains($type, ['thoi trang nam', 'ao polo', 'ao thun', 'so mi']) => ['start' => '#0f172a', 'end' => '#0ea5e9'],
            Str::contains($type, ['thoi trang nu', 'giay dep nu', 'tui vi']) => ['start' => '#9f1239', 'end' => '#fb7185'],
            Str::contains($type, ['sac dep', 'trang suc']) => ['start' => '#be185d', 'end' => '#f59e0b'],
            Str::contains($type, ['the thao', 'du lich']) => ['start' => '#065f46', 'end' => '#22c55e'],
            Str::contains($type, ['sach']) => ['start' => '#1e3a8a', 'end' => '#a16207'],
            Str::contains($type, ['may anh', 'quay phim']) => ['start' => '#111827', 'end' => '#64748b'],
            Str::contains($type, ['dong ho']) => ['start' => '#292524', 'end' => '#d97706'],
            Str::contains($type, ['dien thoai', 'smartphone', 'tablet']) => ['start' => '#0f172a', 'end' => '#2563eb'],
            Str::contains($type, ['tai nghe', 'headset', 'loa']) => ['start' => '#111827', 'end' => '#7c3aed'],
            Str::contains($type, ['sua', 'dinh duong']) => ['start' => '#1d4ed8', 'end' => '#38bdf8'],
            Str::contains($type, ['banh', 'snack', 'keo', 'socola']) => ['start' => '#f97316', 'end' => '#facc15'],
            Str::contains($type, ['khau trang', 'sat khuan', 'cham soc', 'y te']) => ['start' => '#0f766e', 'end' => '#2dd4bf'],
            Str::contains($type, ['ta', 'bim', 'me va be']) => ['start' => '#db2777', 'end' => '#fb7185'],
            Str::contains($type, ['gia dung', 'nha bep', 'hop dung', 'binh nuoc']) => ['start' => '#334155', 'end' => '#f97316'],
            Str::contains($type, ['but', 'vo', 'van phong', 'giay', 'balo']) => ['start' => '#7c2d12', 'end' => '#f59e0b'],
            default => ['start' => '#0f172a', 'end' => '#2563eb'],
        };
    }

    protected function sanitizePlaceholderText(string $value, int $limit): string
    {
        return htmlspecialchars(
            Str::upper(Str::limit(Str::of($value)->ascii()->replace('-', ' ')->squish()->value(), $limit, '')),
            ENT_QUOTES
        );
    }

    protected function beautifyCatalogText(?string $value): string
    {
        $value = (string) $value;

        $map = [
            'San pham' => 'Sản phẩm',
            'Thong so 1' => 'Thông số 1',
            'Thong so 2' => 'Thông số 2',
            'Thong so 3' => 'Thông số 3',
            'Man hinh' => 'Màn hình',
            'Bo nho' => 'Bộ nhớ',
            'Ket noi' => 'Kết nối',
            'Cong nghe' => 'Công nghệ',
            'Trong luong' => 'Trọng lượng',
            'Cong suat' => 'Công suất',
            'Dung luong' => 'Dung lượng',
            'Do tuoi' => 'Độ tuổi',
            'Khoi luong' => 'Khối lượng',
            'Cong dung' => 'Công dụng',
            'Dung tich' => 'Dung tích',
            'Vi' => 'Vị',
            'Dong goi' => 'Đóng gói',
            'Chat lieu' => 'Chất liệu',
            'Quy cach' => 'Quy cách',
            'Dien thoai smartphone' => 'Điện thoại smartphone',
            'May tinh bang' => 'Máy tính bảng',
            'Sac va pin du phong' => 'Sạc và pin dự phòng',
            'Sua cong thuc' => 'Sữa công thức',
            'Sua tuoi va sua hat' => 'Sữa tươi và sữa hạt',
            'Ngu coc dinh duong' => 'Ngũ cốc dinh dưỡng',
            'Bot an dam' => 'Bột ăn dặm',
            'Khau trang' => 'Khẩu trang',
            'Rua tay va sat khuan' => 'Rửa tay và sát khuẩn',
            'Cham soc ca nhan' => 'Chăm sóc cá nhân',
            'Vat dung y te nho' => 'Vật dụng y tế nhỏ',
            'Me va be' => 'Mẹ và bé',
            'Ta bim' => 'Tã bỉm',
            'Do dung cho be' => 'Đồ dùng cho bé',
            'Cham soc me va be' => 'Chăm sóc mẹ và bé',
            'Phu kien an dam' => 'Phụ kiện ăn dặm',
            'Gia dung nha bep' => 'Gia dụng nhà bếp',
            'Noi chao va bep mini' => 'Nồi chảo và bếp mini',
            'Hop dung va binh nuoc' => 'Hộp đựng và bình nước',
            'Dung cu ve sinh' => 'Dụng cụ vệ sinh',
            'Do gia dung thong minh' => 'Đồ gia dụng thông minh',
            'Van phong pham' => 'Văn phòng phẩm',
            'Van phong pham va lifestyle' => 'Văn phòng phẩm và lifestyle',
            'But va dung cu viet' => 'Bút và dụng cụ viết',
            'Vo tap va giay in' => 'Vở tập và giấy in',
            'Hop but va balo nho' => 'Hộp bút và balo nhỏ',
            'Phu kien hoc tap' => 'Phụ kiện học tập',
            'Phu kien ban hoc' => 'Phụ kiện bàn học',
        ];

        return $map[$value] ?? $value;
    }
}
