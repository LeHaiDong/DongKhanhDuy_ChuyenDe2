<?php

namespace Tests\Feature;

use App\Models\CameraLens;
use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotResponseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function chatbot_suggests_products_that_match_customer_message()
    {
        CameraLens::create([
            'name' => 'iPhone 15 128GB',
            'description' => 'Dien thoai Apple',
            'price' => 19990000,
            'stock_quantity' => 8,
            'brand' => 'Apple',
            'product_type' => 'Dien thoai smartphone',
            'focal_length' => '6.1 inch',
            'max_aperture' => '128GB',
            'mount_type' => '5G',
            'is_active' => true,
        ]);

        $response = $this->postJson(route('chat.send'), [
            'message' => 'Tôi muốn tìm iPhone 15',
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertStringContainsString('iPhone 15 128GB', $response->json('message'));
    }

    /** @test */
    public function chatbot_lists_available_vouchers()
    {
        Coupon::create([
            'code' => 'WELCOME10',
            'name' => 'Uu dai khach moi',
            'description' => 'Voucher test',
            'type' => Coupon::TYPE_PERCENTAGE,
            'value' => 10,
            'minimum_amount' => 300000,
            'maximum_discount' => 120000,
            'usage_limit' => 100,
            'usage_limit_per_user' => 1,
            'used_count' => 0,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'is_active' => true,
            'first_order_only' => false,
        ]);

        $response = $this->postJson(route('chat.send'), [
            'message' => 'Shop có voucher nào không?',
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertStringContainsString('WELCOME10', $response->json('message'));
    }
}
