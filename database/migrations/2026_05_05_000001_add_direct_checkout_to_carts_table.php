<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('carts', 'is_direct_checkout')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->boolean('is_direct_checkout')->default(false);
                $table->index('is_direct_checkout');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('carts', 'is_direct_checkout')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropIndex(['is_direct_checkout']);
                $table->dropColumn('is_direct_checkout');
            });
        }
    }
};
