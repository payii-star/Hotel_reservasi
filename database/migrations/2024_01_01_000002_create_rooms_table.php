<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // ex: Deluxe Room
            $table->string('type');            // ex: Standard, Deluxe, Suite
            $table->text('description')->nullable();
            $table->unsignedInteger('capacity')->default(2);
            $table->decimal('price', 12, 2);   // harga per malam
            $table->unsignedInteger('total_unit')->default(1); // jumlah kamar tipe ini
            $table->string('main_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('room_facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // ex: AC, WiFi, TV, Breakfast
            $table->timestamps();
        });

        Schema::create('room_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_images');
        Schema::dropIfExists('room_facilities');
        Schema::dropIfExists('rooms');
    }
};
