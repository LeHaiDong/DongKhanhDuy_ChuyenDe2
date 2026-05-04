<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('camera_lenses', function (Blueprint $table) {
            $table->string('product_type')->default('San pham')->after('brand');
            $table->string('spec_label_1')->nullable()->after('product_type');
            $table->string('spec_label_2')->nullable()->after('spec_label_1');
            $table->string('spec_label_3')->nullable()->after('spec_label_2');
            $table->text('search_keywords')->nullable()->after('description');

            $table->index(['product_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('camera_lenses', function (Blueprint $table) {
            $table->dropIndex(['product_type', 'is_active']);
            $table->dropColumn([
                'product_type',
                'spec_label_1',
                'spec_label_2',
                'spec_label_3',
                'search_keywords',
            ]);
        });
    }
};
