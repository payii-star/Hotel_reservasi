<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'image' => 'required|image|max:2048',
        ]);

        $path = $request->file('image')->store('gallery', 'public');

        $gallery = Gallery::create([
            'title' => $validated['title'],
            'category' => $validated['category'] ?? null,
            'image_path' => $path,
        ]);

        return response()->json($gallery, 201);
    }

    // POST /api/admin/gallery/{gallery} (dengan _method=PUT)
    public function update(Request $request, Gallery $gallery)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = [
            'title' => $validated['title'],
            'category' => $validated['category'] ?? null,
        ];

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('gallery', 'public');
        }

        $gallery->update($data);

        return response()->json($gallery);
    }

    public function destroy(Gallery $gallery)
    {
        $gallery->delete();
        return response()->json(['message' => 'Foto berhasil dihapus.']);
    }
}