<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE camera_lenses MODIFY price DECIMAL(15, 2) NOT NULL');
        DB::statement('ALTER TABLE order_items MODIFY unit_price DECIMAL(15, 2) NOT NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE camera_lenses MODIFY price DECIMAL(10, 2) NOT NULL');
        DB::statement('ALTER TABLE order_items MODIFY unit_price DECIMAL(10, 2) NOT NULL');
    }
};
