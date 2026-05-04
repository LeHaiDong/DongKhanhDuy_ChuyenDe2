<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Support\ProductCategoryClassifier;
use Illuminate\Console\Command;

class NormalizeProductCategories extends Command
{
    protected $signature = 'products:normalize-categories {--dry-run : Chi xem truoc, khong luu du lieu}';

    protected $description = 'Dua moi san pham ve dung mot danh muc hien thi chinh tren trang khach hang.';

    public function handle(ProductCategoryClassifier $classifier): int
    {
        $visibleCategories = Category::query()
            ->whereIn('slug', ProductCategoryClassifier::visibleCategorySlugs())
            ->get()
            ->keyBy('slug');

        $missingSlugs = collect(ProductCategoryClassifier::visibleCategorySlugs())
            ->reject(fn (string $slug) => $visibleCategories->has($slug))
            ->values();

        if ($missingSlugs->isNotEmpty()) {
            $this->error('Thieu danh muc hien thi: ' . $missingSlugs->implode(', '));

            return self::FAILURE;
        }

        $visibleIds = $visibleCategories->pluck('id')->all();
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;
        $unchanged = 0;

        Product::query()
            ->with('categories')
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($classifier, $visibleCategories, $visibleIds, $dryRun, &$updated, &$unchanged) {
                foreach ($products as $product) {
                    $targetSlug = $classifier->classify($product);
                    $targetCategory = $visibleCategories->get($targetSlug);

                    if (! $targetCategory) {
                        $this->warn("Bo qua san pham #{$product->id} vi khong tim thay danh muc {$targetSlug}.");
                        continue;
                    }

                    $nonVisibleIds = $product->categories
                        ->pluck('id')
                        ->reject(fn (int $id) => in_array($id, $visibleIds, true))
                        ->values()
                        ->all();

                    $newCategoryIds = collect($nonVisibleIds)
                        ->push($targetCategory->id)
                        ->unique()
                        ->values()
                        ->all();

                    $currentCategoryIds = $product->categories
                        ->pluck('id')
                        ->sort()
                        ->values()
                        ->all();

                    $sortedNewIds = collect($newCategoryIds)->sort()->values()->all();

                    if ($currentCategoryIds === $sortedNewIds) {
                        $unchanged++;
                        continue;
                    }

                    if (! $dryRun) {
                        $product->categories()->sync($newCategoryIds);
                    }

                    $updated++;
                    $this->line("#{$product->id} {$product->name} => {$targetCategory->display_name}");
                }
            });

        $summary = $dryRun ? 'Xem truoc hoan tat.' : 'Chuan hoa danh muc hoan tat.';
        $this->info($summary . " Da cap nhat {$updated} san pham, giu nguyen {$unchanged} san pham.");

        return self::SUCCESS;
    }
}
