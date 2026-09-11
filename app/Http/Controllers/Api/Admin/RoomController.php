<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomFacility;
use App\Models\RoomImage;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        return response()->json(Room::with(['facilities', 'images'])->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'total_unit' => 'required|integer|min:1',
            'main_image' => 'nullable|image|max:2048',
            'facilities' => 'nullable|array',
            'facilities.*' => 'string|max:100',
        ]);

        if ($request->hasFile('main_image')) {
            $validated['main_image'] = $request->file('main_image')->store('rooms', 'public');
        }

        $room = Room::create($validated);

        if (!empty($validated['facilities'])) {
            foreach ($validated['facilities'] as $facility) {
                RoomFacility::create(['room_id' => $room->id, 'name' => $facility]);
            }
        }

        return response()->json($room->load('facilities'), 201);
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'capacity' => 'sometimes|integer|min:1',
            'price' => 'sometimes|numeric|min:0',
            'total_unit' => 'sometimes|integer|min:1',
            'is_active' => 'sometimes|boolean',
            'main_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('main_image')) {
            $validated['main_image'] = $request->file('main_image')->store('rooms', 'public');
        }

        $room->update($validated);

        return response()->json($room);
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return response()->json(['message' => 'Kamar berhasil dihapus.']);
    }

    // POST /api/admin/rooms/{room}/images
    public function addImage(Request $request, Room $room)
    {
        $request->validate(['image' => 'required|image|max:2048']);
        $path = $request->file('image')->store('rooms/gallery', 'public');

        $image = RoomImage::create(['room_id' => $room->id, 'image_path' => $path]);

        return response()->json($image, 201);
    }
}
