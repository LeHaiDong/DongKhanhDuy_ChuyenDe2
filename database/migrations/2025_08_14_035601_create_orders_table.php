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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // Mã đơn hàng (VD: ORD-2024-001)
            $table->unsignedBigInteger('user_id'); // Khách hàng
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'])->default('pending');
            
            // Thông tin thanh toán
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->enum('payment_method', ['cod', 'bank_transfer', 'vnpay', 'momo', 'credit_card'])->default('cod');
            $table->string('payment_reference')->nullable(); // Mã giao dịch thanh toán
            
            // Thông tin giao hàng
            $table->string('shipping_name'); // Tên người nhận
            $table->string('shipping_phone'); // SĐT người nhận
            $table->string('shipping_email')->nullable(); // Email người nhận
            $table->text('shipping_address'); // Địa chỉ giao hàng
            $table->string('shipping_province'); // Tỉnh/thành
            $table->string('shipping_district'); // Quận/huyện
            $table->string('shipping_ward')->nullable(); // Phường/xã
            $table->string('shipping_method')->default('standard'); // Phương thức vận chuyển
            $table->decimal('shipping_fee', 10, 2)->default(0); // Phí vận chuyển
            
            // Thông tin giá
            $table->decimal('subtotal', 12, 2); // Tổng tiền hàng
            $table->decimal('tax_amount', 10, 2)->default(0); // Thuế
            $table->decimal('discount_amount', 10, 2)->default(0); // Giảm giá
            $table->decimal('total_amount', 12, 2); // Tổng thanh toán
            
            // Thông tin bổ sung
            $table->text('notes')->nullable(); // Ghi chú
            $table->string('coupon_code')->nullable(); // Mã giảm giá
            $table->json('tracking_info')->nullable(); // Thông tin vận chuyển (JSON)
            
            // Timestamps cho các sự kiện
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('order_number');
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
