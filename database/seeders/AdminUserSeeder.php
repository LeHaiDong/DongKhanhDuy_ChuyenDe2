<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Tạo tài khoản quản trị mặc định cho MienTayShop.
        User::firstOrCreate(
            ['email' => 'admin@mientayshop.com'],
            [
                'name' => 'Administrator',
                'email' => 'admin@mientayshop.com',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        echo "Admin user created: admin@mientayshop.com / admin123\n";
    }
}
