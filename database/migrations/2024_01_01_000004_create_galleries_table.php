<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->nullable(); // ex: Lobby, Kolam Renang, Restoran
            $table->string('image_path');
            $table->timestamps();
        });

        Schema::create('hotel_info', function (Blueprint $table) {
            $table->id();
            $table->string('hotel_name');
            $table->text('description');
            $table->string('address');
            $table->string('phone');
            $table->string('email');
            $table->string('logo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_info');
        Schema::dropIfExists('galleries');
    }
};
