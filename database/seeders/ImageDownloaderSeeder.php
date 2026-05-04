<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CameraLens;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class ImageDownloaderSeeder extends Seeder
{
    /**
     * Download high-quality images for camera lenses
     */
    public function run()
    {
        echo "🖼️  Bắt đầu tải hình ảnh chất lượng cao cho ống kính...\n";

        // Get lenses without images
        $lensesWithoutImages = CameraLens::whereNull('image')->get();
        
        echo "📊 Tìm thấy {$lensesWithoutImages->count()} ống kính cần tải hình ảnh\n\n";

        // High-quality image sources for different lens types
        $imageUrls = [
            // Canon lenses
            'https://images.unsplash.com/photo-1606983340126-99ab4feaa64a?w=800&h=800&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=800&h=800&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1617005082133-548c4dd27f35?w=800&h=800&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1606577924006-27d39b132043?w=800&h=800&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1615463120034-b2b8daba1b62?w=800&h=800&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=800&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1509131207317-675510df1745?w=800&h=800&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1605460375648-278bcbd579a6?w=800&h=800&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1625177936374-d52c25780b0c?w=800&h=800&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1617462332970-748c95e4d7a5?w=800&h=800&fit=crop&auto=format',
            
            // Alternative high-quality camera lens images
            'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=800&h=800&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=800&h=800&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1581833971358-2c8b550f87b3?w=800&h=800&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=800&h=800&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=800&h=800&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1581833971358-2c8b550f87b3?w=800&h=800&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=800&h=800&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=800&h=800&fit=crop&auto=format',
        ];

        $imageIndex = 0;
        foreach ($lensesWithoutImages as $lens) {
            echo "🔧 Đang tải hình ảnh cho: {$lens->name}\n";
            
            // Try multiple image URLs if one fails
            $imageDownloaded = false;
            $attempts = 0;
            $maxAttempts = 3;
            
            while (!$imageDownloaded && $attempts < $maxAttempts) {
                $imageUrl = $imageUrls[$imageIndex % count($imageUrls)];
                $imagePath = $this->downloadImage($imageUrl, $lens->name, $attempts);
                
                if ($imagePath) {
                    $lens->update(['image' => $imagePath]);
                    echo "  ✅ Thành công: {$imagePath}\n";
                    $imageDownloaded = true;
                } else {
                    $attempts++;
                    $imageIndex++;
                    echo "  ⚠️  Thử lại lần {$attempts}...\n";
                }
            }
            
            if (!$imageDownloaded) {
                echo "  ❌ Không thể tải hình ảnh cho: {$lens->name}\n";
            }
            
            $imageIndex++;
            
            // Small delay to avoid overwhelming the server
            usleep(500000); // 0.5 second delay
        }

        echo "\n🎉 Hoàn thành tải hình ảnh!\n";
        
        // Show final statistics
        $totalLenses = CameraLens::count();
        $lensesWithImages = CameraLens::whereNotNull('image')->count();
        echo "📊 Thống kê cuối cùng:\n";
        echo "   - Tổng số ống kính: {$totalLenses}\n";
        echo "   - Có hình ảnh: {$lensesWithImages}\n";
        echo "   - Không có hình ảnh: " . ($totalLenses - $lensesWithImages) . "\n";
    }

    /**
     * Download image from URL with retry mechanism
     */
    private function downloadImage($imageUrl, $productName, $attempt = 0)
    {
        try {
            // Create unique filename
            $fileName = 'lens_' . time() . '_' . rand(1000, 9999) . '_' . $attempt . '.jpg';
            $filePath = 'camera-lenses/' . $fileName;

            // Download image with longer timeout and user agent
            $response = Http::timeout(45)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                ])
                ->get($imageUrl);
            
            if ($response->successful() && $response->body()) {
                // Validate that it's actually an image
                $imageData = $response->body();
                if (strlen($imageData) > 1000) { // Basic size check
                    Storage::disk('public')->put($filePath, $imageData);
                    return $filePath;
                }
            }
            
            return null;
        } catch (\Exception $e) {
            echo "    ⚠️  Exception: " . $e->getMessage() . "\n";
            return null;
        }
    }
}
