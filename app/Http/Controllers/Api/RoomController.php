<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    // GET /api/rooms
    public function index(Request $request)
    {
        $query = Room::with(['facilities', 'images'])->where('is_active', true);

        if ($request->has(['check_in', 'check_out'])) {
            $rooms = $query->get()->map(function ($room) use ($request) {
                $room->available_units = $room->availableUnits(
                    $request->check_in,
                    $request->check_out
                );
                return $room;
            })->filter(fn ($r) => $r->available_units > 0)->values();

            return response()->json($rooms);
        }

        return response()->json($query->get());
    }

    // GET /api/rooms/{id}
    public function show(Request $request, Room $room)
    {
        $room->load(['facilities', 'images']);

        // Kalau ada tanggal dikirim, hitung stok kamar yang masih available
        if ($request->has(['check_in', 'check_out'])) {
            $room->available_units = $room->availableUnits(
                $request->check_in,
                $request->check_out
            );
        }

        return response()->json($room);
    }
}