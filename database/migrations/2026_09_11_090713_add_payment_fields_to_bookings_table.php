<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('snap_token')->nullable()->after('status');
            $table->string('midtrans_order_id')->nullable()->unique()->after('snap_token');
            $table->string('payment_type')->nullable()->after('midtrans_order_id');
            $table->timestamp('paid_at')->nullable()->after('payment_type');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['snap_token', 'midtrans_order_id', 'payment_type', 'paid_at']);
        });
    }
};