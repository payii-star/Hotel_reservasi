<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gallery;

class GalleryController extends Controller
{
    // GET /api/gallery
    public function index()
    {
        return response()->json(Gallery::latest()->get());
    }
}