<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CameraLens;

class TestComparisonSeeder extends Seeder
{
    public function run()
    {
        // Sony FE 70-200mm
        CameraLens::create([
            'name' => 'Sony FE 70-200mm f/2.8 GM OSS',
            'brand' => 'Sony', 
            'mount' => 'FE',
            'focal_length' => '70-200mm',
            'aperture' => 'f/2.8',
            'price' => 65000000,
            'stock_quantity' => 5,
            'description' => 'Professional telephoto zoom lens with constant f/2.8 aperture and optical image stabilization'
        ]);

        // Canon RF 100-500mm  
        CameraLens::create([
            'name' => 'Canon RF 100-500mm f/4.5-7.1L IS USM',
            'brand' => 'Canon',
            'mount' => 'RF', 
            'focal_length' => '100-500mm',
            'aperture' => 'f/4.5-7.1',
            'price' => 78000000,
            'stock_quantity' => 3,
            'description' => 'Super telephoto zoom lens for wildlife and sports photography with image stabilization'
        ]);

        echo "✓ Added comparison test products\n";
    }
}


