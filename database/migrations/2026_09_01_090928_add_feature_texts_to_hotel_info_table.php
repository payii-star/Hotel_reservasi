<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotel_info', function (Blueprint $table) {
            $table->text('facilities_text')->nullable()->after('address');
            $table->text('booking_text')->nullable()->after('facilities_text');
        });
    }

    public function down(): void
    {
        Schema::table('hotel_info', function (Blueprint $table) {
            $table->dropColumn(['facilities_text', 'booking_text']);
        });
    }
};