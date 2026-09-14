<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    // Peta transisi status yang diizinkan buat admin.
    // Konfirmasi pembayaran (pending -> confirmed) TIDAK ada di sini
    // karena itu hanya boleh terjadi otomatis lewat callback Midtrans.
    private array $allowedTransitions = [
        'pending'     => ['cancelled'],
        'confirmed'   => ['checked_in'],
        'checked_in'  => ['checked_out'],
        'checked_out' => [],
        'cancelled'   => [],
    ];

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

        $newStatus = $validated['status'];
        $currentStatus = $booking->status;

        $allowed = $this->allowedTransitions[$currentStatus] ?? [];

        if (!in_array($newStatus, $allowed)) {
            return response()->json([
                'message' => "Tidak bisa mengubah status dari '{$currentStatus}' ke '{$newStatus}'. Perubahan status hanya bisa mengikuti alur normal (dan konfirmasi pembayaran terjadi otomatis lewat Midtrans).",
            ], 422);
        }

        $booking->update(['status' => $newStatus]);

        return response()->json([
            'message' => 'Status booking berhasil diupdate.',
            'booking' => $booking,
        ]);
    }
}