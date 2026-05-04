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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id'); // Đơn hàng
            $table->unsignedBigInteger('camera_lens_id'); // Sản phẩm
            
            // Thông tin sản phẩm tại thời điểm đặt hàng (lưu trữ để tránh thay đổi sau này)
            $table->string('product_name'); // Tên sản phẩm
            $table->string('product_brand'); // Thương hiệu
            $table->string('product_sku')->nullable(); // Mã sản phẩm
            $table->text('product_description')->nullable(); // Mô tả sản phẩm
            $table->string('product_image')->nullable(); // Hình ảnh sản phẩm
            
            // Thông tin đặt hàng
            $table->integer('quantity'); // Số lượng
            $table->decimal('unit_price', 10, 2); // Giá đơn vị tại thời điểm đặt hàng
            $table->decimal('total_price', 12, 2); // Tổng giá (quantity * unit_price)
            
            // Thông tin giảm giá cho item (nếu có)
            $table->decimal('discount_amount', 10, 2)->default(0); // Giảm giá cho item này
            $table->string('discount_type')->nullable(); // percentage hoặc fixed
            $table->decimal('final_price', 12, 2); // Giá cuối cùng sau giảm giá
            
            $table->timestamps();

            // Foreign keys
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('camera_lens_id')->references('id')->on('camera_lenses')->onDelete('cascade');
            
            // Indexes
            $table->index(['order_id', 'camera_lens_id']);
            $table->index('order_id');
            $table->index('camera_lens_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_items');
    }
};
