<?php

namespace Tests\Feature;

use App\Models\CameraLens;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        CameraLens::create([
            'name' => 'Demo Creator Camera',
            'brand' => 'Demo Brand',
            'product_type' => 'Máy ảnh mirrorless',
            'spec_label_1' => 'Cảm biến',
            'spec_label_2' => 'Độ phân giải',
            'spec_label_3' => 'Ngàm',
            'focal_length' => 'APS-C',
            'max_aperture' => '24MP',
            'mount_type' => 'Demo Mount',
            'price' => 19990000,
            'stock_quantity' => 5,
            'condition' => 'new',
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
