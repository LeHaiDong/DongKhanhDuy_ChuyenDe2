<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'MIENTAY10',
                'name' => 'Giảm 10% toàn shop',
                'description' => 'Giảm 10% cho đơn từ 300.000 VNĐ, tối đa 120.000 VNĐ.',
                'type' => Coupon::TYPE_PERCENTAGE,
                'value' => 10,
                'minimum_amount' => 300000,
                'maximum_discount' => 120000,
                'usage_limit' => 500,
                'usage_limit_per_user' => 1,
                'first_order_only' => false,
            ],
            [
                'code' => 'FREESHIP30',
                'name' => 'Voucher giảm 30.000 VNĐ',
                'description' => 'Giảm trực tiếp 30.000 VNĐ cho đơn từ 150.000 VNĐ.',
                'type' => Coupon::TYPE_FIXED,
                'value' => 30000,
                'minimum_amount' => 150000,
                'maximum_discount' => null,
                'usage_limit' => 600,
                'usage_limit_per_user' => 2,
                'first_order_only' => false,
            ],
            [
                'code' => 'WELCOME15',
                'name' => 'Ưu đãi khách mới 15%',
                'description' => 'Dành cho đơn đầu tiên, giảm 15% tối đa 180.000 VNĐ.',
                'type' => Coupon::TYPE_PERCENTAGE,
                'value' => 15,
                'minimum_amount' => 500000,
                'maximum_discount' => 180000,
                'usage_limit' => 300,
                'usage_limit_per_user' => 1,
                'first_order_only' => true,
            ],
            [
                'code' => 'MEKONG50',
                'name' => 'Giảm 50.000 VNĐ cuối tuần',
                'description' => 'Giảm 50.000 VNĐ cho đơn từ 700.000 VNĐ.',
                'type' => Coupon::TYPE_FIXED,
                'value' => 50000,
                'minimum_amount' => 700000,
                'maximum_discount' => null,
                'usage_limit' => 400,
                'usage_limit_per_user' => 1,
                'first_order_only' => false,
            ],
        ];

        foreach ($coupons as $couponData) {
            Coupon::updateOrCreate(
                ['code' => $couponData['code']],
                array_merge($couponData, [
                    'used_count' => 0,
                    'starts_at' => now()->subDays(3),
                    'expires_at' => now()->addMonths(6),
                    'is_active' => true,
                    'applicable_products' => null,
                    'applicable_categories' => null,
                    'excluded_products' => null,
                    'user_restrictions' => null,
                    'admin_notes' => 'Voucher demo cho đồ án MienTayShop.',
                ])
            );
        }
    }
}
