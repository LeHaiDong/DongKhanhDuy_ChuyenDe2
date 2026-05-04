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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên danh mục
            $table->string('slug')->unique(); // URL-friendly name
            $table->text('description')->nullable(); // Mô tả danh mục
            $table->string('image')->nullable(); // Hình ảnh danh mục
            $table->string('icon')->nullable(); // Icon cho danh mục
            $table->unsignedBigInteger('parent_id')->nullable(); // Danh mục cha (cho cấu trúc cây)
            $table->integer('sort_order')->default(0); // Thứ tự hiển thị
            $table->boolean('is_active')->default(true); // Trạng thái hoạt động
            $table->string('meta_title')->nullable(); // SEO title
            $table->text('meta_description')->nullable(); // SEO description
            $table->timestamps();

            // Foreign keys
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
            
            // Indexes
            $table->index(['parent_id', 'is_active']);
            $table->index('sort_order');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
};
