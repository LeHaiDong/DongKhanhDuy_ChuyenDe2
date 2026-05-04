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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camera_lens_id')->constrained('camera_lenses')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')->comment('User who made the movement');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null')->comment('Related order if applicable');
            $table->enum('type', [
                'initial_stock',    // Initial stock entry
                'purchase',         // Stock purchase/restock
                'sale',            // Stock sold (order)
                'adjustment',      // Manual adjustment
                'return',          // Customer return
                'damage',          // Damaged goods
                'transfer',        // Transfer between locations
                'reserve',         // Reserve for pending order
                'unreserve',       // Release reservation
            ]);
            $table->integer('quantity')->comment('Positive for in, negative for out');
            $table->integer('quantity_before')->comment('Stock before this movement');
            $table->integer('quantity_after')->comment('Stock after this movement');
            $table->decimal('unit_cost', 15, 2)->nullable()->comment('Cost per unit for purchases');
            $table->string('reference_number')->nullable()->comment('Invoice, PO number, etc.');
            $table->string('location')->nullable()->comment('Warehouse/location');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->json('metadata')->nullable()->comment('Additional data like supplier info');
            $table->timestamps();
            
            // Indexes
            $table->index(['camera_lens_id', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['order_id']);
            $table->index(['reference_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_movements');
    }
};