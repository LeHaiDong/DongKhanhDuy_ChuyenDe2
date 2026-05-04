<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'icon',
        'parent_id',
        'sort_order',
        'is_active',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function cameraLenses()
    {
        return $this->belongsToMany(CameraLens::class, 'camera_lens_categories');
    }

    public function activeCameraLenses()
    {
        return $this->cameraLenses()->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getFullPathAttribute()
    {
        $path = collect([$this->display_name]);
        $parent = $this->parent;

        while ($parent) {
            $path->prepend($parent->display_name);
            $parent = $parent->parent;
        }

        return $path->implode(' > ');
    }

    public function getTotalProductsCountAttribute()
    {
        $count = $this->cameraLenses()->count();

        foreach ($this->allChildren as $child) {
            $count += $child->total_products_count;
        }

        return $count;
    }

    public function getDisplayNameAttribute()
    {
        return $this->beautifyCatalogText($this->name);
    }

    public function getUrlAttribute()
    {
        return route('products.shop', ['category' => $this->id]);
    }

    public function getBreadcrumbAttribute()
    {
        $breadcrumb = collect();
        $current = $this;

        while ($current) {
            $breadcrumb->prepend([
                'id' => $current->id,
                'name' => $current->display_name,
                'url' => $current->url,
                'slug' => $current->slug,
            ]);
            $current = $current->parent;
        }

        return $breadcrumb;
    }

    public function isParent()
    {
        return $this->children()->count() > 0;
    }

    public function isRoot()
    {
        return is_null($this->parent_id);
    }

    public function getAllChildrenIds()
    {
        $ids = collect([$this->id]);

        foreach ($this->children as $child) {
            $ids = $ids->merge($child->getAllChildrenIds());
        }

        return $ids;
    }

    public function canBeDeleted()
    {
        if ($this->cameraLenses()->count() > 0) {
            return false;
        }

        if ($this->children()->count() > 0) {
            return false;
        }

        return true;
    }

    public function getIconWithFallbackAttribute()
    {
        return $this->icon ?: 'fas fa-folder';
    }

    public function getDisplayImageUrlAttribute(): ?string
    {
        if ($this->image) {
            if (Str::startsWith($this->image, ['http://', 'https://'])) {
                return $this->image;
            }

            $path = ltrim($this->image, '/');

            if (file_exists(public_path($path))) {
                return asset($path);
            }

            $storagePath = storage_path('app/public/' . $path);

            if (file_exists($storagePath)) {
                $publicStoragePath = public_path('storage/' . $path);

                if (file_exists($publicStoragePath)) {
                    return asset('storage/' . $path);
                }

                return url('category-media/' . $path);
            }
        }

        return $this->fallbackCategoryImageUrl()
            ?: $this->buildCategoryIllustrationDataUri();
    }

    protected function fallbackCategoryImageUrl(): ?string
    {
        $name = Str::of($this->name)->ascii()->lower()->value();

        $filename = match (true) {
            Str::contains($name, ['dien thoai', 'smartphone']) => 'smartphone.jpg',
            Str::contains($name, ['may tinh bang', 'tablet']) => 'tablet.jpg',
            Str::contains($name, ['tai nghe', 'headset', 'am thanh']) => 'audio.jpg',
            Str::contains($name, ['loa']) => 'speaker.jpg',
            Str::contains($name, ['sac', 'pin du phong', 'cap sac']) => 'charging.jpg',
            Str::contains($name, ['sua']) => 'milk.jpg',
            Str::contains($name, ['ngu coc', 'bot an dam']) => 'cereal.jpg',
            Str::contains($name, ['banh', 'keo', 'snack', 'mi', 'chao']) => 'snack.jpg',
            Str::contains($name, ['suc khoe', 'khau trang', 'sat khuan', 'cham soc', 'y te']) => 'health.jpg',
            Str::contains($name, ['me va be', 'ta bim', 'do dung cho be']) => 'baby.jpg',
            Str::contains($name, ['gia dung', 'nha bep', 'noi chao', 'hop dung', 'binh nuoc']) => 'home.jpg',
            Str::contains($name, ['van phong', 'but', 'vo tap', 'hoc tap', 'balo']) => 'stationery.jpg',
            default => null,
        };

        if (!$filename) {
            return null;
        }

        $relativePath = 'catalog/fallback/' . $filename;

        return file_exists(public_path($relativePath)) ? asset($relativePath) : null;
    }

    protected function buildCategoryIllustrationDataUri(): string
    {
        $name = Str::of($this->display_name ?: $this->name)->limit(28, '')->value();
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $normalized = Str::of($this->name)->ascii()->lower()->value();
        $palette = match (true) {
            Str::contains($normalized, ['thoi trang nam', 'giay dep nam']) => ['#0f172a', '#0ea5e9'],
            Str::contains($normalized, ['thoi trang nu', 'giay dep nu', 'tui vi', 'sac dep']) => ['#be185d', '#fb7185'],
            Str::contains($normalized, ['dien thoai', 'dien tu', 'laptop', 'may anh']) => ['#1e293b', '#2563eb'],
            Str::contains($normalized, ['me va be']) => ['#db2777', '#f9a8d4'],
            Str::contains($normalized, ['nha cua', 'doi song', 'gia dung']) => ['#92400e', '#f97316'],
            Str::contains($normalized, ['the thao', 'du lich']) => ['#065f46', '#22c55e'],
            Str::contains($normalized, ['sach']) => ['#1e3a8a', '#a16207'],
            default => ['#f97316', '#ef4444'],
        };

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240">
  <defs>
    <linearGradient id="g" x1="0" x2="1" y1="0" y2="1">
      <stop offset="0%" stop-color="{$palette[0]}"/>
      <stop offset="100%" stop-color="{$palette[1]}"/>
    </linearGradient>
  </defs>
  <rect width="240" height="240" rx="48" fill="url(#g)"/>
  <circle cx="178" cy="58" r="42" fill="rgba(255,255,255,.18)"/>
  <circle cx="60" cy="188" r="54" fill="rgba(255,255,255,.12)"/>
  <rect x="50" y="54" width="140" height="92" rx="28" fill="rgba(255,255,255,.2)"/>
  <path d="M82 116h76M92 88h56" stroke="#fff" stroke-width="13" stroke-linecap="round"/>
  <text x="120" y="190" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="19" font-weight="800" fill="#fff">{$safeName}</text>
</svg>
SVG;

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
    }

    public static function getTreeSelectOptions($selectedId = null, $level = 0)
    {
        $categories = static::root()->active()->ordered()->get();
        $options = collect();

        foreach ($categories as $category) {
            if ($category->id !== $selectedId) {
                $prefix = str_repeat('— ', $level);
                $options->push([
                    'id' => $category->id,
                    'name' => $prefix . $category->display_name,
                    'level' => $level,
                ]);

                $options = $options->merge(static::getChildSelectOptions($category, $selectedId, $level + 1));
            }
        }

        return $options;
    }

    protected static function getChildSelectOptions($parent, $selectedId = null, $level = 0)
    {
        $options = collect();

        foreach ($parent->children as $child) {
            if ($child->id !== $selectedId) {
                $prefix = str_repeat('— ', $level);
                $options->push([
                    'id' => $child->id,
                    'name' => $prefix . $child->display_name,
                    'level' => $level,
                ]);

                $options = $options->merge(static::getChildSelectOptions($child, $selectedId, $level + 1));
            }
        }

        return $options;
    }

    protected function beautifyCatalogText(?string $value): string
    {
        $value = (string) $value;

        $map = [
            'Dien tu va cong nghe' => 'Điện tử và công nghệ',
            'Dien thoai smartphone' => 'Điện thoại smartphone',
            'May tinh bang' => 'Máy tính bảng',
            'Tai nghe va headset' => 'Tai nghe và headset',
            'Sac va pin du phong' => 'Sạc và pin dự phòng',
            'Am thanh va phu kien' => 'Âm thanh và phụ kiện',
            'Tai nghe choi game' => 'Tai nghe chơi game',
            'Loa bluetooth' => 'Loa bluetooth',
            'Cap sac va cu sac' => 'Cáp sạc và củ sạc',
            'Sua va dinh duong' => 'Sữa và dinh dưỡng',
            'Sua cong thuc' => 'Sữa công thức',
            'Sua tuoi va sua hat' => 'Sữa tươi và sữa hạt',
            'Ngu coc dinh duong' => 'Ngũ cốc dinh dưỡng',
            'Bot an dam' => 'Bột ăn dặm',
            'Banh keo va do an vat' => 'Bánh kẹo và đồ ăn vặt',
            'Snack khoai tay' => 'Snack khoai tây',
            'Banh quy va banh bong lan' => 'Bánh quy và bánh bông lan',
            'Keo va socola' => 'Kẹo và socola',
            'Mi va chao an lien' => 'Mì và cháo ăn liền',
            'Suc khoe va cham soc' => 'Sức khỏe và chăm sóc',
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
            'Van phong pham va lifestyle' => 'Văn phòng phẩm và lifestyle',
            'But va dung cu viet' => 'Bút và dụng cụ viết',
            'Vo tap va giay in' => 'Vở tập và giấy in',
            'Hop but va balo nho' => 'Hộp bút và balo nhỏ',
            'Phu kien ban hoc' => 'Phụ kiện bàn học',
        ];

        return $map[$value] ?? $value;
    }
}
