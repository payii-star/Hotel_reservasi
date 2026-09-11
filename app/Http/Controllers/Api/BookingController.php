<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
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

        return response()->json([
            'message' => 'Reservasi berhasil dibuat! Silakan tunggu konfirmasi dari pihak hotel.',
            'booking' => $booking,
        ], 201);
    }

    // GET /api/bookings/{code} -- cek status booking pakai kode booking
    public function show(string $code)
    {
        $booking = Booking::with('room')->where('booking_code', $code)->firstOrFail();
        return response()->json($booking);
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