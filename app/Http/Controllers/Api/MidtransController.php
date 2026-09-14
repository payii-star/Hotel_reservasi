<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    // POST /api/midtrans/callback -- dipanggil OTOMATIS oleh server Midtrans
    public function callback(Request $request)
    {
        try {
            $notification = new Notification();
        } catch (\Exception $e) {
            Log::error('Midtrans notification error: ' . $e->getMessage());
            return response()->json(['message' => 'Invalid notification'], 400);
        }

        $orderId = $notification->order_id;
        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status ?? null;
        $paymentType = $notification->payment_type ?? null;

        $booking = Booking::where('midtrans_order_id', $orderId)->first();

        if (!$booking) {
            Log::warning('Booking tidak ditemukan untuk order_id: ' . $orderId);
            return response()->json(['message' => 'Booking not found'], 404);
        }

        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'accept') {
                $booking->update([
                    'status' => 'confirmed',
                    'payment_type' => $paymentType,
                    'paid_at' => now(),
                ]);
            }
        } elseif ($transactionStatus === 'settlement') {
            $booking->update([
                'status' => 'confirmed',
                'payment_type' => $paymentType,
                'paid_at' => now(),
            ]);
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $booking->update([
                'status' => 'cancelled',
            ]);
        } elseif ($transactionStatus === 'pending') {
            $booking->update([
                'status' => 'pending',
            ]);
        }

        return response()->json(['message' => 'Notification handled']);
    }
}