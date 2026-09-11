<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    // GET /api/admin/bookings?status=pending
    public function index(Request $request)
    {
        $query = Booking::with('room')->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->get());
    }

    public function show(Booking $booking)
    {
        return response()->json($booking->load('room'));
    }

    // PATCH /api/admin/bookings/{booking}/status
    public function updateStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled',
        ]);

        $booking->update($validated);

        return response()->json([
            'message' => 'Status booking berhasil diupdate.',
            'booking' => $booking,
        ]);
    }
}
