<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CameraLens;

try {
    echo "Testing CameraLens model...\n";
    
    $totalProducts = CameraLens::count();
    echo "Total products: $totalProducts\n";
    
    $activeProducts = CameraLens::active()->count();
    echo "Active products: $activeProducts\n";
    
    $inStockProducts = CameraLens::inStock()->count();
    echo "In stock products: $inStockProducts\n";
    
    $featuredLenses = CameraLens::active()->inStock()->take(6)->get();
    echo "Featured lenses count: " . $featuredLenses->count() . "\n";
    
    if ($featuredLenses->count() > 0) {
        echo "First featured lens: " . $featuredLenses->first()->name . "\n";
    }
    
    echo "✓ All tests passed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}


