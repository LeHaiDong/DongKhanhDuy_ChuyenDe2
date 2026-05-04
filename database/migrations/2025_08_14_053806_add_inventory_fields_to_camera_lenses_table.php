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
        Schema::table('camera_lenses', function (Blueprint $table) {
            // Inventory Management Fields
            $table->string('sku')->unique()->nullable()->after('id')->comment('Stock Keeping Unit');
            $table->integer('low_stock_threshold')->default(5)->after('stock_quantity')->comment('Alert when stock below this');
            $table->integer('reserved_quantity')->default(0)->after('low_stock_threshold')->comment('Quantity reserved for pending orders');
            $table->decimal('cost_price', 15, 2)->nullable()->after('reserved_quantity')->comment('Purchase/cost price');
            $table->string('supplier')->nullable()->after('cost_price')->comment('Main supplier');
            $table->string('supplier_sku')->nullable()->after('supplier')->comment('Supplier product code');
            $table->date('last_restocked_at')->nullable()->after('supplier_sku')->comment('Last restock date');
            $table->integer('reorder_point')->default(10)->after('last_restocked_at')->comment('Automatic reorder threshold');
            $table->integer('reorder_quantity')->default(50)->after('reorder_point')->comment('Quantity to order when restocking');
            $table->enum('stock_status', ['in_stock', 'low_stock', 'out_of_stock', 'discontinued'])->default('in_stock')->after('reorder_quantity');
            $table->text('stock_notes')->nullable()->after('stock_status')->comment('Internal notes about stock');
            
            // Indexes for inventory queries
            $table->index(['stock_status', 'is_active']);
            $table->index(['stock_quantity', 'low_stock_threshold']);
            $table->index(['supplier', 'supplier_sku']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('camera_lenses', function (Blueprint $table) {
            $table->dropIndex(['stock_status', 'is_active']);
            $table->dropIndex(['stock_quantity', 'low_stock_threshold']);
            $table->dropIndex(['supplier', 'supplier_sku']);
            
            $table->dropColumn([
                'sku',
                'low_stock_threshold', 
                'reserved_quantity',
                'cost_price',
                'supplier',
                'supplier_sku',
                'last_restocked_at',
                'reorder_point',
                'reorder_quantity',
                'stock_status',
                'stock_notes',
            ]);
        });
    }
};