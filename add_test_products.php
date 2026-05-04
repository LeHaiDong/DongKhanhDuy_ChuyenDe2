<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CameraLens;

// Add Sony FE 70-200mm
CameraLens::create([
    'name' => 'Sony FE 70-200mm f/2.8 GM OSS',
    'brand' => 'Sony', 
    'mount' => 'FE',
    'focal_length' => '70-200mm',
    'aperture' => 'f/2.8',
    'price' => 65000000,
    'stock_quantity' => 5,
    'description' => 'Professional telephoto zoom lens with constant f/2.8 aperture'
]);

// Add Canon RF 100-500mm  
CameraLens::create([
    'name' => 'Canon RF 100-500mm f/4.5-7.1L IS USM',
    'brand' => 'Canon',
    'mount' => 'RF', 
    'focal_length' => '100-500mm',
    'aperture' => 'f/4.5-7.1',
    'price' => 78000000,
    'stock_quantity' => 3,
    'description' => 'Super telephoto zoom lens for wildlife and sports photography'
]);

echo "✓ Added test products for comparison\n";

