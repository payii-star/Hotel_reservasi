<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HotelInfo;

class HotelInfoController extends Controller
{
    // GET /api/hotel-info
    public function show()
    {
        return response()->json(HotelInfo::first());
    }
}