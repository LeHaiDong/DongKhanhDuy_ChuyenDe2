<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_shops', function (Blueprint $table) {
            if (!Schema::hasColumn('seller_shops', 'primary_category_id')) {
                $table->foreignId('primary_category_id')
                    ->nullable()
                    ->after('brand_name')
                    ->constrained('categories')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('seller_shops', 'document_type')) {
                $table->string('document_type')->nullable()->after('description');
            }

            if (!Schema::hasColumn('seller_shops', 'document_number')) {
                $table->string('document_number')->nullable()->after('document_type');
            }

            if (!Schema::hasColumn('seller_shops', 'document_note')) {
                $table->text('document_note')->nullable()->after('document_number');
            }

            if (!Schema::hasColumn('seller_shops', 'document_image')) {
                $table->string('document_image')->nullable()->after('document_note');
            }

            if (!Schema::hasColumn('seller_shops', 'shop_image')) {
                $table->string('shop_image')->nullable()->after('document_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('seller_shops', function (Blueprint $table) {
            foreach (['shop_image', 'document_image', 'document_note', 'document_number', 'document_type'] as $column) {
                if (Schema::hasColumn('seller_shops', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('seller_shops', 'primary_category_id')) {
                $table->dropConstrainedForeignId('primary_category_id');
            }
        });
    }
};
