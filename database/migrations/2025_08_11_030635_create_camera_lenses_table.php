<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('camera_lenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand');
            $table->string('focal_length'); // VD: "24-70mm", "50mm"
            $table->string('max_aperture'); // VD: "f/1.4", "f/2.8"
            $table->string('mount_type'); // VD: "Canon EF", "Nikon F", "Sony E"
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('condition')->default('new'); // new, used, refurbished
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('camera_lenses');
    }
};
