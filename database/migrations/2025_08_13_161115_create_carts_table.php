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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable(); // Cho guest users
            $table->unsignedBigInteger('user_id')->nullable(); // Cho logged users
            $table->unsignedBigInteger('camera_lens_id');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 0); // Giá tại thời điểm thêm vào giỏ
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('camera_lens_id')->references('id')->on('camera_lenses')->onDelete('cascade');
            
            // Index cho performance
            $table->index(['session_id']);
            $table->index(['user_id']);
            $table->index(['camera_lens_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carts');
    }
};
