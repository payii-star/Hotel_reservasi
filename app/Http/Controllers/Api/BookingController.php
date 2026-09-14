<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class BookingController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    // POST /api/bookings
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email',
            'guest_phone' => 'required|string|max:20',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'total_guest' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $room = Room::findOrFail($validated['room_id']);

        $available = $room->availableUnits($validated['check_in'], $validated['check_out']);
        if ($available <= 0) {
            return response()->json([
                'message' => 'Maaf, kamar tidak tersedia di tanggal tersebut.'
            ], 422);
        }

        $nights = Carbon::parse($validated['check_in'])->diffInDays(Carbon::parse($validated['check_out']));
        $totalPrice = $nights * $room->price;

        $booking = Booking::create([
            'booking_code' => Booking::generateCode(),
            'user_id' => auth('sanctum')->id(),
            'room_id' => $room->id,
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'],
            'guest_phone' => $validated['guest_phone'],
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'total_guest' => $validated['total_guest'],
            'total_price' => $totalPrice,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        // Bikin transaksi Midtrans
        $orderId = $booking->booking_code . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $totalPrice,
            ],
            'customer_details' => [
                'first_name' => $validated['guest_name'],
                'email' => $validated['guest_email'],
                'phone' => $validated['guest_phone'],
            ],
            'item_details' => [
                [
                    'id' => $room->id,
                    'price' => (int) $room->price,
                    'quantity' => $nights,
                    'name' => $room->name . ' (' . $nights . ' malam)',
                ],
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
        } catch (\Exception $e) {
            Log::error('Midtrans error: ' . $e->getMessage());
            $booking->delete();
            return response()->json([
                'message' => 'Gagal membuat transaksi pembayaran. Coba lagi.'
            ], 500);
        }

        $booking->update([
            'midtrans_order_id' => $orderId,
            'snap_token' => $snapToken,
        ]);

        return response()->json([
            'message' => 'Reservasi berhasil dibuat! Silakan lanjutkan pembayaran.',
            'booking' => $booking,
            'snap_token' => $snapToken,
        ], 201);
    }

    // GET /api/bookings/{code} -- cek status booking pakai kode booking
    public function show(string $code)
    {
        $booking = Booking::with('room')->where('booking_code', $code)->firstOrFail();
        return response()->json($booking);
    }

    // POST /api/bookings/{code}/pay -- generate ulang Snap token buat booking yang masih pending
    public function pay(string $code)
    {
        $booking = Booking::where('booking_code', $code)->firstOrFail();

        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'Booking ini sudah tidak bisa dibayar (status: ' . $booking->status . ').'
            ], 422);
        }

        // Order ID baru tiap kali generate ulang, biar gak bentrok sama transaksi Midtrans sebelumnya
        $orderId = $booking->booking_code . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $booking->total_price,
            ],
            'customer_details' => [
                'first_name' => $booking->guest_name,
                'email' => $booking->guest_email,
                'phone' => $booking->guest_phone,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
        } catch (\Exception $e) {
            Log::error('Midtrans error (re-pay): ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal membuat ulang transaksi pembayaran. Coba lagi.'
            ], 500);
        }

        $booking->update([
            'midtrans_order_id' => $orderId,
            'snap_token' => $snapToken,
        ]);

        return response()->json([
            'snap_token' => $snapToken,
        ]);
    }

    // PATCH /api/bookings/{code}/reschedule -- guest reschedule booking sendiri pakai kode booking
    public function reschedule(Request $request, string $code)
    {
        $booking = Booking::with('room')->where('booking_code', $code)->firstOrFail();

        // 1) Tolak kalau udah pernah direschedule sebelumnya
        if (!is_null($booking->rescheduled_at)) {
            return response()->json([
                'message' => 'Booking ini sudah pernah direschedule dan tidak bisa diubah lagi.',
            ], 422);
        }

        // 2) Cuma booking yang sudah dibayar dan belum checkout/cancelled yang boleh direschedule
        if (!in_array($booking->status, ['confirmed', 'checked_in'])) {
            return response()->json([
                'message' => "Booking dengan status '{$booking->status}' tidak bisa direschedule.",
            ], 422);
        }

        $validated = $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
        ]);

        // 3) Durasi baru wajib sama persis kayak durasi booking asli, harga TIDAK berubah
        $nights = Carbon::parse($booking->check_in)->diffInDays(Carbon::parse($booking->check_out));

        $newCheckIn  = Carbon::parse($validated['check_in']);
        $newCheckOut = $newCheckIn->copy()->addDays($nights);

        // 4) Cek ketersediaan kamar di tanggal baru, exclude booking ini sendiri dari hitungan
        $room = $booking->room;
        $available = $room->availableUnits(
            $newCheckIn->toDateString(),
            $newCheckOut->toDateString(),
            $booking->id
        );

        if ($available <= 0) {
            return response()->json([
                'message' => 'Maaf, kamar tidak tersedia di tanggal yang dipilih.',
            ], 422);
        }

        // 5) Update booking. Tanggal lama otomatis "lepas" karena baris ini di-update, bukan dibuat baru.
        //    total_price sengaja TIDAK diubah -- reschedule bukan re-charge.
        $booking->update([
            'check_in'       => $newCheckIn,
            'check_out'      => $newCheckOut,
            'rescheduled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Reschedule berhasil.',
            'booking' => $booking->fresh('room'),
        ]);
    }

    // GET /api/my-bookings -- riwayat booking milik user yang login
    public function myBookings(Request $request)
    {
        $bookings = Booking::with('room')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($bookings);
    }
}