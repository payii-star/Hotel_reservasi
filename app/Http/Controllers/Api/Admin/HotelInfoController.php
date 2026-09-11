<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HotelInfo;
use Illuminate\Http\Request;

class HotelInfoController extends Controller
{
    // POST /api/admin/hotel-info (dengan _method=PUT dari frontend)
    public function update(Request $request)
    {
        $validated = $request->validate([
            'hotel_name' => 'required|string|max:255',
            'welcome_text' => 'nullable|string|max:100',
            'description' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email',
            'facilities_text' => 'nullable|string',
            'booking_text' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'main_photo' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('hotel', 'public');
        }
        if ($request->hasFile('main_photo')) {
            $validated['main_photo'] = $request->file('main_photo')->store('hotel', 'public');
        }

        $info = HotelInfo::first();
        if ($info) {
            $info->update($validated);
        } else {
            $info = HotelInfo::create($validated);
        }

        return response()->json($info);
    }
}