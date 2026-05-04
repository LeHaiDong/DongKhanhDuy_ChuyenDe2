<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Coupon code');
            $table->string('name')->comment('Display name');
            $table->text('description')->nullable()->comment('Description for admin');
            $table->enum('type', ['fixed', 'percentage'])->comment('Discount type');
            $table->decimal('value', 15, 2)->comment('Discount value');
            $table->decimal('minimum_amount', 15, 2)->nullable()->comment('Minimum order amount');
            $table->decimal('maximum_discount', 15, 2)->nullable()->comment('Maximum discount amount for percentage');
            $table->integer('usage_limit')->nullable()->comment('Total usage limit');
            $table->integer('usage_limit_per_user')->nullable()->comment('Usage limit per user');
            $table->integer('used_count')->default(0)->comment('Number of times used');
            $table->datetime('starts_at')->comment('Start date');
            $table->datetime('expires_at')->comment('Expiry date');
            $table->boolean('is_active')->default(true);
            $table->json('applicable_products')->nullable()->comment('Specific product IDs');
            $table->json('applicable_categories')->nullable()->comment('Specific category IDs');
            $table->json('excluded_products')->nullable()->comment('Excluded product IDs');
            $table->json('user_restrictions')->nullable()->comment('User email/ID restrictions');
            $table->boolean('first_order_only')->default(false)->comment('Only for first orders');
            $table->text('admin_notes')->nullable()->comment('Internal notes');
            $table->timestamps();
            
            // Indexes
            $table->index(['code', 'is_active']);
            $table->index(['starts_at', 'expires_at']);
            $table->index(['type', 'is_active']);
            $table->index(['usage_limit', 'used_count']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupons');
    }
};