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
        Schema::create('camera_lens_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('camera_lens_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('camera_lens_id')->references('id')->on('camera_lenses')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

            // Composite unique index
            $table->unique(['camera_lens_id', 'category_id']);
            
            // Individual indexes for performance
            $table->index('camera_lens_id');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('camera_lens_categories');
    }
};