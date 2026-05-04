<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Category::with(['parent', 'allChildren'])
                        ->withCount(['cameraLenses', 'children']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Keep the default admin list aligned with the customer category grid.
        $parentId = $request->get('parent');
        if ($parentId === 'root' || (!$request->filled('parent') && !$request->filled('search'))) {
            $query->whereNull('parent_id');
        } elseif ($request->filled('parent') && $parentId !== 'all') {
            $query->where('parent_id', $parentId);
        }

        // Sort
        $sortBy = $request->get('sort', 'sort_order');
        $sortOrder = $request->get('order', 'asc');
        
        if ($sortBy === 'products_count') {
            $query->orderBy('camera_lenses_count', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $categories = $query->paginate(20);

        // Get parent categories for filter dropdown
        $parentCategories = Category::root()->active()->ordered()->get();

        return view('admin.categories.index', compact(
            'categories', 
            'parentCategories'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentCategories = Category::getTreeSelectOptions();
        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'image' => $this->categoryImageRules(),
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);
        $this->validateCategoryImageExtension($request);

        $data = $request->all();

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Ensure slug is unique
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Category::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $this->storeCategoryImage($request->file('image'));
        }

        // Set default sort_order if not provided
        if (!isset($data['sort_order'])) {
            $maxOrder = Category::where('parent_id', $data['parent_id'])->max('sort_order') ?? 0;
            $data['sort_order'] = $maxOrder + 1;
        }

        $category = Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', "Danh mục '{$category->name}' đã được tạo thành công!");
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $category->load(['parent', 'children.allChildren', 'cameraLenses']);
        
        // Get category statistics
        $stats = [
            'total_products' => $category->total_products_count,
            'active_products' => $category->cameraLenses()->where('is_active', true)->count(),
            'in_stock_products' => $category->cameraLenses()->where('stock_quantity', '>', 0)->count(),
            'total_children' => $category->children()->count(),
            'total_descendants' => $this->countDescendants($category),
        ];

        // Get recent products in this category
        $recentProducts = $category->cameraLenses()
                                 ->with('categories')
                                 ->latest()
                                 ->take(10)
                                 ->get();

        return view('admin.categories.show', compact('category', 'stats', 'recentProducts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::getTreeSelectOptions($category->id);
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'image' => $this->categoryImageRules(),
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);
        $this->validateCategoryImageExtension($request);

        // Prevent circular reference
        if ($request->parent_id) {
            $parentCategory = Category::find($request->parent_id);
            $ancestorIds = $this->getAncestorIds($parentCategory);
            
            if (in_array($category->id, $ancestorIds)) {
                return back()->withErrors([
                    'parent_id' => 'Không thể chọn danh mục con làm danh mục cha.'
                ]);
            }
        }

        $data = $request->all();

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Ensure slug is unique (excluding current category)
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Category::where('slug', $data['slug'])->where('id', '!=', $category->id)->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $this->deleteCategoryImage($category->image);
            $data['image'] = $this->storeCategoryImage($request->file('image'));
        } elseif ($request->boolean('remove_image')) {
            $this->deleteCategoryImage($category->image);
            $data['image'] = null;
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', "Danh mục '{$category->name}' đã được cập nhật thành công!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Check if category can be deleted
        if (!$category->canBeDeleted()) {
            $reasons = [];
            
            if ($category->cameraLenses()->count() > 0) {
                $reasons[] = 'có ' . $category->cameraLenses()->count() . ' sản phẩm';
            }
            
            if ($category->children()->count() > 0) {
                $reasons[] = 'có ' . $category->children()->count() . ' danh mục con';
            }
            
            $message = "Không thể xóa danh mục '{$category->name}' vì " . implode(' và ', $reasons) . '.';
            
            return back()->withErrors(['delete' => $message]);
        }

        $categoryName = $category->name;

        // Delete image if exists
        if ($category->image) {
            $this->deleteCategoryImage($category->image);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', "Danh mục '{$categoryName}' đã được xóa thành công!");
    }

    /**
     * Toggle category status
     */
    public function toggleStatus(Request $request, Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);

        if ($request->expectsJson() || $request->ajax() || $request->isJson()) {
            $statusText = $category->is_active ? 'kích hoạt' : 'vô hiệu hóa';

            return response()->json([
                'success' => true,
                'is_active' => $category->is_active,
                'message' => "Đã {$statusText} danh mục '{$category->display_name}'.",
            ]);
        }
        
        $status = $category->is_active ? 'kích hoạt' : 'vô hiệu hóa';
        
        return back()->with('success', "Đã {$status} danh mục '{$category->name}'.");
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id'
        ]);

        $categories = Category::whereIn('id', $request->categories)->get();
        $count = $categories->count();
        $action = $request->action;

        switch ($action) {
            case 'activate':
                Category::whereIn('id', $request->categories)->update(['is_active' => true]);
                $message = "Đã kích hoạt {$count} danh mục.";
                break;

            case 'deactivate':
                Category::whereIn('id', $request->categories)->update(['is_active' => false]);
                $message = "Đã vô hiệu hóa {$count} danh mục.";
                break;

            case 'delete':
                $deletedCount = 0;
                $errors = [];

                foreach ($categories as $category) {
                    if ($category->canBeDeleted()) {
                        if ($category->image) {
                            $this->deleteCategoryImage($category->image);
                        }
                        $category->delete();
                        $deletedCount++;
                    } else {
                        $errors[] = "'{$category->name}' không thể xóa";
                    }
                }

                $message = "Đã xóa {$deletedCount} danh mục.";
                if (!empty($errors)) {
                    $message .= ' ' . implode(', ', $errors) . '.';
                }
                break;
        }

        return back()->with('success', $message);
    }

    /**
     * Reorder categories
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.sort_order' => 'required|integer|min:0'
        ]);

        foreach ($request->categories as $categoryData) {
            Category::where('id', $categoryData['id'])
                   ->update(['sort_order' => $categoryData['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'Đã cập nhật thứ tự danh mục.']);
    }

    /**
     * Get category tree for AJAX
     */
    public function getTree()
    {
        $categories = Category::with('allChildren')
                             ->root()
                             ->active()
                             ->ordered()
                             ->get();

        return response()->json($this->buildTree($categories));
    }

    private function storeCategoryImage(UploadedFile $image): string
    {
        $extension = strtolower($image->getClientOriginalExtension() ?: $image->extension() ?: 'jpg');
        $filename = Str::uuid() . '.' . $extension;
        $directory = public_path('uploads/categories');

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $image->move($directory, $filename);

        return 'uploads/categories/' . $filename;
    }

    private function categoryImageRules(): string
    {
        return 'nullable|file|max:10240';
    }

    private function validateCategoryImageExtension(Request $request): void
    {
        if (!$request->hasFile('image')) {
            return;
        }

        $extension = strtolower($request->file('image')->getClientOriginalExtension());
        $allowedExtensions = ['jpg', 'jpeg', 'jfif', 'png', 'gif', 'webp', 'avif', 'bmp', 'svg'];

        if (!in_array($extension, $allowedExtensions, true)) {
            throw ValidationException::withMessages([
                'image' => 'Ảnh danh mục chỉ hỗ trợ JPG, PNG, GIF, WEBP, AVIF, BMP hoặc SVG.',
            ]);
        }
    }

    private function deleteCategoryImage(?string $imagePath): void
    {
        if (!$imagePath || Str::startsWith($imagePath, ['http://', 'https://', 'catalog/'])) {
            return;
        }

        $path = ltrim(str_replace('\\', '/', $imagePath), '/');

        if (Str::startsWith($path, 'uploads/categories/')) {
            $publicPath = public_path($path);

            if (is_file($publicPath)) {
                @unlink($publicPath);
            }

            return;
        }

        if (Str::startsWith($path, 'categories/')) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Count descendants recursively
     */
    private function countDescendants(Category $category)
    {
        $count = $category->children()->count();
        
        foreach ($category->children as $child) {
            $count += $this->countDescendants($child);
        }

        return $count;
    }

    /**
     * Get ancestor IDs
     */
    private function getAncestorIds(Category $category)
    {
        $ids = [$category->id];
        
        foreach ($category->allChildren as $child) {
            $ids = array_merge($ids, $this->getAncestorIds($child));
        }

        return $ids;
    }

    /**
     * Build tree structure for JSON response
     */
    private function buildTree($categories)
    {
        return $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->display_name,
                'slug' => $category->slug,
                'is_active' => $category->is_active,
                'products_count' => $category->total_products_count,
                'children' => $this->buildTree($category->children)
            ];
        });
    }
}
