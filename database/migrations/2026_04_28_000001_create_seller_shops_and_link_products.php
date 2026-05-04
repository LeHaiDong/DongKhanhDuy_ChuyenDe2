<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('shop_name');
            $table->string('slug')->unique();
            $table->string('brand_name')->nullable();
            $table->string('phone', 30);
            $table->string('address')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['status', 'created_at']);
        });

        if (!Schema::hasColumn('camera_lenses', 'seller_shop_id')) {
            Schema::table('camera_lenses', function (Blueprint $table) {
                $table->foreignId('seller_shop_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('seller_shops')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('camera_lenses', 'seller_shop_id')) {
            Schema::table('camera_lenses', function (Blueprint $table) {
                $table->dropConstrainedForeignId('seller_shop_id');
            });
        }

        Schema::dropIfExists('seller_shops');
    }
};
