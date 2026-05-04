<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAuthFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function register_redirects_to_login_and_marks_email_verified()
    {
        $response = $this->post('/auth/register', [
            'name' => 'Le Thi B',
            'email' => 'register@example.com',
            'phone' => '0912345678',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('auth.customer.login'));
        $this->assertGuest();

        $user = User::where('email', 'register@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNotNull($user->email_verified_at);
    }
}
