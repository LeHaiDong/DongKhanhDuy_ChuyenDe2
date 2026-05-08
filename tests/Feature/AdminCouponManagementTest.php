<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCouponManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_save_percentage_coupon_with_comma_decimal_inputs()
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.coupons.store'), [
                'code' => 'TEST10',
                'name' => 'Giam 10 phan tram',
                'description' => 'Voucher test',
                'type' => Coupon::TYPE_PERCENTAGE,
                'value' => '10,5',
                'minimum_amount' => '300.000,00',
                'maximum_discount' => '120.000',
                'usage_limit' => 500,
                'usage_limit_per_user' => 1,
                'starts_at' => now()->format('Y-m-d H:i:s'),
                'expires_at' => now()->addMonth()->format('Y-m-d H:i:s'),
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.coupons.index'))
            ->assertSessionHasNoErrors();

        $coupon = Coupon::where('code', 'TEST10')->firstOrFail();

        $this->assertSame(10.5, (float) $coupon->value);
        $this->assertSame(300000.0, (float) $coupon->minimum_amount);
        $this->assertSame(120000.0, (float) $coupon->maximum_discount);
    }

    /** @test */
    public function percentage_coupon_value_cannot_be_greater_than_one_hundred()
    {
        $admin = $this->makeAdmin('admin-coupon-limit@example.com');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.coupons.store'), [
                'code' => 'BAD101',
                'name' => 'Giam qua muc',
                'type' => Coupon::TYPE_PERCENTAGE,
                'value' => '101',
                'starts_at' => now()->format('Y-m-d H:i:s'),
                'expires_at' => now()->addMonth()->format('Y-m-d H:i:s'),
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('value');

        $this->assertDatabaseMissing('coupons', [
            'code' => 'BAD101',
        ]);
    }

    private function makeAdmin(string $email = 'admin-coupon@example.com'): User
    {
        return User::create([
            'name' => 'Administrator',
            'email' => $email,
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);
    }
}
