<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function home()
    {
        $featuredProducts = Product::active()->inStock()->with('categories')->latest()->take(12)->get();
        $flashSaleProducts = Product::active()
            ->inStock()
            ->with('categories')
            ->orderBy('price')
            ->take(15)
            ->get();
        $recommendedProducts = Product::active()
            ->inStock()
            ->with('categories')
            ->inRandomOrder()
            ->take(18)
            ->get();
        $featuredLenses = $featuredProducts;
        $totalProducts = Product::active()->count();
        $heroCategories = Category::active()->root()->ordered()->with('allChildren')->get();
        $categoryTiles = Category::active()
            ->root()
            ->ordered()
            ->with('allChildren')
            ->take(20)
            ->get();
        $topBrands = Product::active()
            ->select('brand')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('brand')
            ->orderByDesc('total')
            ->take(8)
            ->pluck('brand');
        $availableCoupons = Coupon::available()
            ->orderBy('type')
            ->orderByDesc('value')
            ->get();

        return view('home', compact(
            'featuredLenses',
            'featuredProducts',
            'flashSaleProducts',
            'recommendedProducts',
            'totalProducts',
            'heroCategories',
            'categoryTiles',
            'topBrands',
            'availableCoupons'
        ));
    }

    public function index(Request $request)
    {
        $query = Product::query()->with('categories');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($productQuery) use ($search) {
                $productQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('product_type', 'like', "%{$search}%")
                    ->orWhere('search_keywords', 'like', "%{$search}%");
            });
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->input('brand'));
        }

        if ($request->filled('status')) {
            match ($request->input('status')) {
                'active' => $query->where('is_active', true),
                'inactive' => $query->where('is_active', false),
                'low_stock' => $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5),
                'out_of_stock' => $query->where('stock_quantity', '<=', 0),
                default => null,
            };
        }

        match ($request->input('sort', 'newest')) {
            'oldest' => $query->orderBy('created_at'),
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->orderByDesc('created_at'),
        };

        $perPage = max(10, min(50, (int) $request->input('per_page', 10)));
        $products = $query->paginate($perPage)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::active()->ordered()->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate($this->adminRules());

        $categoryIds = $this->resolveAdminCategoryIds($request);
        $data = $this->buildAdminProductData($request, $categoryIds);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeProductImage($request->file('image'));
        }

        $product = Product::create($data);
        $product->categories()->sync($categoryIds);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Sản phẩm đã được thêm thành công.');
    }

    public function show(Product $product)
    {
        $lens = $product->load(['categories', 'sellerShop', 'approvedReviews.user']);
        $reviewStats = [
            'average' => round((float) $lens->approvedReviews()->avg('rating'), 1),
            'count' => $lens->approvedReviews()->count(),
        ];
        $userReview = auth()->check()
            ? $lens->reviews()->where('user_id', auth()->id())->first()
            : null;

        return view('products.show', compact('lens', 'reviewStats', 'userReview'));
    }

    public function edit(Product $product)
    {
        $categories = Category::active()->ordered()->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate($this->adminRules());

        $categoryIds = $this->resolveAdminCategoryIds($request);
        $data = $this->buildAdminProductData($request, $categoryIds, $product);

        if ($request->hasFile('image')) {
            $this->deleteProductImage($product->image);
            $data['image'] = $this->storeProductImage($request->file('image'));
        }

        $product->update($data);
        $product->categories()->sync($categoryIds);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Sản phẩm đã được cập nhật thành công.');
    }

    public function destroy(Product $product)
    {
        $this->deleteProductImage($product->image);

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Sản phẩm đã được xóa thành công.');
    }

    private function storeProductImage(UploadedFile $image): string
    {
        $extension = strtolower($image->getClientOriginalExtension() ?: $image->extension() ?: 'jpg');
        $filename = Str::uuid() . '.' . $extension;
        $directory = public_path('uploads/products');

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $image->move($directory, $filename);

        return 'uploads/products/' . $filename;
    }

    private function deleteProductImage(?string $imagePath): void
    {
        if (!$imagePath || Str::startsWith($imagePath, ['http://', 'https://'])) {
            return;
        }

        $path = ltrim(str_replace('\\', '/', $imagePath), '/');

        if (Str::startsWith($path, 'uploads/products/')) {
            $publicPath = public_path($path);

            if (is_file($publicPath)) {
                @unlink($publicPath);
            }

            return;
        }

        Storage::disk('public')->delete($path);
    }

    public function shop(Request $request)
    {
        $query = Product::active()->with('categories');
        $selectedCategory = $this->resolveRequestedCategory($request);
        $keyword = $request->filled('q')
            ? $request->string('q')->trim()->toString()
            : '';

        if (!$selectedCategory && $keyword !== '') {
            $selectedCategory = $this->findCategoryByKeyword($keyword);
        }

        if ($selectedCategory) {
            $this->applyCategoryFilter($query, $selectedCategory);
        } elseif ($keyword !== '') {
            $this->applyKeywordSearch($query, $keyword);
        }

        if ($request->filled('product_type') && $request->product_type !== 'all') {
            $query->where('product_type', $request->product_type);
        }

        if ($request->filled('brand') && $request->brand !== 'all') {
            $query->where('brand', $request->brand);
        }

        if ($request->filled('mount_type') && $request->mount_type !== 'all') {
            $query->where('mount_type', $request->mount_type);
        }

        if ($request->filled('condition') && $request->condition !== 'all') {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (int) $request->min_price * 1000000);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (int) $request->max_price * 1000000);
        }

        if ($request->filled('in_stock') && $request->in_stock === '1') {
            $query->where('stock_quantity', '>', 0);
        }

        match ($request->get('sort', 'name')) {
            'price_low' => $query->orderBy('price'),
            'price_high' => $query->orderByDesc('price'),
            'newest' => $query->orderByDesc('created_at'),
            'popular' => $query->orderByDesc('stock_quantity'),
            default => $query->orderBy('name'),
        };

        $perPage = (int) $request->get('per_page', 12);
        $lenses = $query->paginate($perPage)->withQueryString();

        $brands = Product::active()->select('brand')->distinct()->orderBy('brand')->pluck('brand');
        $productTypes = Product::active()->select('product_type')->distinct()->orderBy('product_type')->pluck('product_type');
        $mountTypes = Product::active()->select('mount_type')->distinct()->orderBy('mount_type')->pluck('mount_type');
        $conditions = Product::active()->select('condition')->distinct()->pluck('condition');

        $priceRange = Product::active()->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();
        $minPrice = $priceRange?->min_price ? (int) floor($priceRange->min_price / 1000000) : 0;
        $maxPrice = $priceRange?->max_price ? (int) ceil($priceRange->max_price / 1000000) : 0;

        if ($request->ajax()) {
            return response()->json([
                'html' => view('products.partials.product-grid', compact('lenses'))->render(),
                'pagination' => $lenses->links()->toHtml(),
                'total' => $lenses->total(),
                'scope' => $selectedCategory?->display_name,
            ]);
        }

        return view('products.shop', compact(
            'lenses',
            'brands',
            'productTypes',
            'mountTypes',
            'conditions',
            'minPrice',
            'maxPrice',
            'selectedCategory'
        ));
    }

    public function search(Request $request)
    {
        $keyword = trim((string) $request->get('q'));

        if ($keyword === '') {
            return redirect()->route('products.shop');
        }

        if ($category = $this->findCategoryByKeyword($keyword)) {
            return redirect()->route('products.shop', ['category' => $category->id]);
        }

        $query = Product::active()->with('categories');
        $this->applyKeywordSearch($query, $keyword);

        $variants = $this->keywordVariants($keyword);
        $primaryMatch = '%' . ($variants[0] ?? $keyword) . '%';

        $lenses = $query->orderByRaw(
            "CASE
                WHEN name LIKE ? THEN 1
                WHEN product_type LIKE ? THEN 2
                WHEN brand LIKE ? THEN 3
                WHEN search_keywords LIKE ? THEN 4
                ELSE 5
            END, name ASC",
            [$primaryMatch, $primaryMatch, $primaryMatch, $primaryMatch]
        )->paginate(12)->withQueryString();

        $suggestions = collect();

        if ($lenses->isEmpty()) {
            $words = collect($variants)
                ->filter(fn ($word) => mb_strlen($word) > 2)
                ->values();

            $suggestions = Product::active()
                ->where(function ($productQuery) use ($words) {
                    foreach ($words as $word) {
                        $productQuery->orWhere('name', 'like', "%{$word}%")
                            ->orWhere('brand', 'like', "%{$word}%")
                            ->orWhere('product_type', 'like', "%{$word}%")
                            ->orWhere('search_keywords', 'like', "%{$word}%");
                    }
                })
                ->take(5)
                ->get(['name', 'brand', 'product_type']);
        }

        $totalResults = $lenses->total();

        return view('products.search', compact('lenses', 'keyword', 'suggestions', 'totalResults'));
    }

    public function searchSuggestions(Request $request)
    {
        $keyword = trim((string) $request->get('q'));

        if (mb_strlen($keyword) < 2) {
            return response()->json([]);
        }

        $query = Product::active();
        $this->applyKeywordSearch($query, $keyword);
        $variants = $this->keywordVariants($keyword);
        $primaryMatch = '%' . ($variants[0] ?? $keyword) . '%';

        $suggestions = $query
            ->select('id', 'name', 'brand', 'product_type')
            ->orderByRaw(
                "CASE
                    WHEN name LIKE ? THEN 1
                    WHEN brand LIKE ? THEN 2
                    WHEN product_type LIKE ? THEN 3
                    WHEN search_keywords LIKE ? THEN 4
                    ELSE 5
                END, stock_quantity DESC, name ASC",
                [$primaryMatch, $primaryMatch, $primaryMatch, $primaryMatch]
            )
            ->take(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'text' => $product->name,
                    'brand' => $product->brand,
                    'product_type' => $product->display_product_type,
                    'url' => route('products.show', $product->id),
                ];
            });

        return response()->json($suggestions);
    }

    private function adminRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'product_type' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'search_keywords' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'stock_quantity' => 'required|integer|min:0',
            'condition' => 'nullable|in:new,used,refurbished',
            'category_id' => 'nullable|exists:categories,id',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'is_active' => 'nullable|boolean',
            'spec_label_1' => 'nullable|string|max:100',
            'spec_label_2' => 'nullable|string|max:100',
            'spec_label_3' => 'nullable|string|max:100',
            'focal_length' => 'nullable|string|max:255',
            'max_aperture' => 'nullable|string|max:255',
            'mount_type' => 'nullable|string|max:255',
        ];
    }

    private function resolveAdminCategoryIds(Request $request): array
    {
        if ($request->filled('category_id')) {
            return [(int) $request->input('category_id')];
        }

        return collect($request->input('categories', []))
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function buildAdminProductData(Request $request, array $categoryIds, ?Product $product = null): array
    {
        $name = trim((string) $request->input('name', $product?->name));
        $brand = trim((string) $request->input('brand', $product?->brand));
        $productType = trim((string) $request->input('product_type', $product?->product_type));

        $specLabel1 = trim((string) $request->input('spec_label_1', $product?->spec_label_1 ?: 'Tên sản phẩm'));
        $specLabel2 = trim((string) $request->input('spec_label_2', $product?->spec_label_2 ?: 'Thương hiệu'));
        $specLabel3 = trim((string) $request->input('spec_label_3', $product?->spec_label_3 ?: 'Phân loại'));

        $specValue1 = trim((string) $request->input('focal_length', $product?->focal_length ?: $name));
        $specValue2 = trim((string) $request->input('max_aperture', $product?->max_aperture ?: $brand));
        $specValue3 = trim((string) $request->input('mount_type', $product?->mount_type ?: $productType));

        return [
            'name' => $name,
            'brand' => $brand,
            'product_type' => $productType,
            'spec_label_1' => $specLabel1 !== '' ? $specLabel1 : 'Tên sản phẩm',
            'spec_label_2' => $specLabel2 !== '' ? $specLabel2 : 'Thương hiệu',
            'spec_label_3' => $specLabel3 !== '' ? $specLabel3 : 'Phân loại',
            'focal_length' => $specValue1 !== '' ? $specValue1 : $name,
            'max_aperture' => $specValue2 !== '' ? $specValue2 : $brand,
            'mount_type' => $specValue3 !== '' ? $specValue3 : $productType,
            'price' => (float) $request->input('price'),
            'description' => $this->nullableString($request->input('description')),
            'search_keywords' => $this->buildAdminSearchKeywords(
                $request->input('search_keywords'),
                $name,
                $brand,
                $productType,
                $categoryIds
            ),
            'stock_quantity' => (int) $request->input('stock_quantity'),
            'is_active' => $request->boolean('is_active', $product?->is_active ?? true),
            'condition' => $request->input('condition', $product?->condition ?: 'new'),
        ];
    }

    private function buildAdminSearchKeywords($searchKeywords, string $name, string $brand, string $productType, array $categoryIds): string
    {
        $provided = trim((string) $searchKeywords);

        if ($provided !== '') {
            return $provided;
        }

        $categoryNames = Category::query()
            ->whereIn('id', $categoryIds)
            ->pluck('name')
            ->all();

        return collect([$name, $brand, $productType, ...$categoryNames])
            ->filter()
            ->map(fn ($value) => trim((string) $value))
            ->unique()
            ->implode(', ');
    }

    private function nullableString($value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function applyKeywordSearch($query, string $keyword): void
    {
        $terms = $this->keywordVariants($keyword);

        $query->where(function ($productQuery) use ($terms) {
            foreach ($terms as $term) {
                $productQuery->orWhere('name', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%")
                    ->orWhere('product_type', 'like', "%{$term}%")
                    ->orWhere('focal_length', 'like', "%{$term}%")
                    ->orWhere('max_aperture', 'like', "%{$term}%")
                    ->orWhere('mount_type', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('search_keywords', 'like', "%{$term}%")
                    ->orWhereHas('categories', function ($categoryQuery) use ($term) {
                        $categoryQuery->where('name', 'like', "%{$term}%");
                    });
            }
        });
    }

    private function keywordVariants(string $keyword): array
    {
        $stopWords = ['trang', 'thoi', 'san', 'pham', 'hang', 'danh', 'muc', 'va', 'cho', 'cua', 'online', 'toi', 'minh', 'can', 'muon', 'tim', 'kiem', 'mua', 'co', 'khong', 'duoi', 'tren'];
        $normalized = Str::of($keyword)->ascii()->lower()->squish()->value();
        $normalized = str_replace(
            ['ip15 plus', 'ip 15 plus', 'iphone15 plus', 'ip15', 'ip 15', 'iphone15', 'ip14 pro', 'ip 14 pro', 'ip14', 'ip 14', 'iphone14', 'dt'],
            ['iphone 15 plus', 'iphone 15 plus', 'iphone 15 plus', 'iphone 15', 'iphone 15', 'iphone 15', 'iphone 14 pro', 'iphone 14 pro', 'iphone 14', 'iphone 14', 'iphone 14', 'dien thoai'],
            $normalized
        );

        return collect([$keyword, $normalized])
            ->filter()
            ->flatMap(function ($term) {
                $parts = preg_split('/[^\pL\pN]+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [];

                return array_merge([$term], $parts);
            })
            ->map(fn ($term) => trim((string) $term))
            ->filter(function ($term) use ($stopWords) {
                $normalizedTerm = $this->normalizeSearchText($term);

                return mb_strlen($term) >= 2
                    && (str_contains($normalizedTerm, ' ') || !in_array($normalizedTerm, $stopWords, true));
            })
            ->unique()
            ->values()
            ->all();
    }

    private function resolveRequestedCategory(Request $request): ?Category
    {
        $category = trim((string) $request->get('category'));

        if ($category === '' || $category === 'all') {
            return null;
        }

        return Category::active()
            ->where(function ($query) use ($category) {
                $query->where('slug', $category);

                if (ctype_digit($category)) {
                    $query->orWhere('id', (int) $category);
                }
            })
            ->with('children')
            ->first();
    }

    private function findCategoryByKeyword(string $keyword): ?Category
    {
        $normalizedKeyword = $this->normalizeSearchText($keyword);
        $slugKeyword = Str::slug($keyword);

        if ($normalizedKeyword === '') {
            return null;
        }

        return Category::active()
            ->with('children')
            ->get()
            ->first(function (Category $category) use ($normalizedKeyword, $slugKeyword) {
                return $normalizedKeyword === $this->normalizeSearchText($category->name)
                    || $normalizedKeyword === $this->normalizeSearchText($category->display_name)
                    || $slugKeyword === $category->slug;
            });
    }

    private function applyCategoryFilter($query, Category $category): void
    {
        $category->loadMissing('children.allChildren');
        $categoryIds = $category->getAllChildrenIds()->unique()->values()->all();

        $query->whereHas('categories', function ($categoryQuery) use ($categoryIds) {
            $categoryQuery->whereIn('categories.id', $categoryIds);
        });
    }

    private function normalizeSearchText(string $value): string
    {
        $value = Str::of($value)->ascii()->lower()->toString();
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }
}
