<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CameraLens;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class FixMissingImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // URLs hình ảnh dự phòng từ Unsplash
        $fallbackImages = [
            'https://images.unsplash.com/photo-1606983340126-99ab4feaa64a?w=600&h=600&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=600&h=600&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1606577924006-27d39b132043?w=600&h=600&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1615463120034-b2b8daba1b62?w=600&h=600&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1617005082133-548c4dd27f35?w=600&h=600&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1509131207317-675510df1745?w=600&h=600&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1605460375648-278bcbd579a6?w=600&h=600&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1625177936374-d52c25780b0c?w=600&h=600&fit=crop&auto=format',
            'https://images.unsplash.com/photo-1617462332970-748c95e4d7a5?w=600&h=600&fit=crop&auto=format',
        ];

        // Lấy tất cả ống kính không có hình ảnh
        $lensesWithoutImages = CameraLens::whereNull('image')->get();

        echo "Tìm thấy {$lensesWithoutImages->count()} ống kính không có hình ảnh.\n";

        foreach ($lensesWithoutImages as $index => $lens) {
            echo "Đang tải ảnh cho: {$lens->name}\n";
            
            // Chọn một ảnh ngẫu nhiên từ danh sách
            $imageUrl = $fallbackImages[$index % count($fallbackImages)];
            
            // Download và lưu hình ảnh
            $imagePath = $this->downloadAndSaveImage($imageUrl, $lens->name);
            
            if ($imagePath) {
                $lens->update(['image' => $imagePath]);
                echo "✓ Đã cập nhật hình ảnh cho: {$lens->name}\n";
            } else {
                echo "✗ Không thể tải ảnh cho: {$lens->name}\n";
            }
        }

        echo "\n🎉 Hoàn thành! Đã cập nhật hình ảnh cho tất cả ống kính.\n";
    }

    /**
     * Download và lưu hình ảnh từ URL
     */
    private function downloadAndSaveImage($imageUrl, $productName)
    {
        try {
            // Tạo tên file duy nhất
            $fileName = 'lens_' . time() . '_' . rand(1000, 9999) . '.jpg';
            $filePath = 'camera-lenses/' . $fileName;

            // Download hình ảnh với retry
            $maxRetries = 3;
            $response = null;
            
            for ($i = 0; $i < $maxRetries; $i++) {
                try {
                    $response = Http::timeout(30)->get($imageUrl);
                    if ($response->successful()) {
                        break;
                    }
                } catch (\Exception $e) {
                    if ($i === $maxRetries - 1) {
                        throw $e;
                    }
                    sleep(1); // Chờ 1 giây trước khi thử lại
                }
            }
            
            if ($response && $response->successful()) {
                // Lưu vào storage
                Storage::disk('public')->put($filePath, $response->body());
                echo "  ✓ Đã tải hình ảnh: {$fileName}\n";
                return $filePath;
            } else {
                echo "  ✗ Lỗi tải hình ảnh cho: {$productName}\n";
                return null;
            }
        } catch (\Exception $e) {
            echo "  ✗ Exception khi tải hình ảnh cho {$productName}: " . $e->getMessage() . "\n";
            return null;
        }
    }
}



