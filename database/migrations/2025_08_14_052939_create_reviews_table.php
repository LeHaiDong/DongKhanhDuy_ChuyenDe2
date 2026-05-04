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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('camera_lens_id')->constrained('camera_lenses')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('rating')->unsigned()->comment('1-5 stars');
            $table->string('title')->nullable();
            $table->text('content');
            $table->json('pros')->nullable()->comment('Array of positive points');
            $table->json('cons')->nullable()->comment('Array of negative points');
            $table->json('images')->nullable()->comment('Array of review image paths');
            $table->boolean('is_verified_purchase')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_approved')->default(true);
            $table->integer('helpful_votes')->default(0);
            $table->integer('unhelpful_votes')->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['camera_lens_id', 'is_approved', 'created_at']);
            $table->index(['user_id', 'camera_lens_id']);
            $table->index(['rating', 'is_approved']);
            $table->index(['is_featured', 'is_approved']);
            
            // Ensure one review per user per product
            $table->unique(['user_id', 'camera_lens_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
};
