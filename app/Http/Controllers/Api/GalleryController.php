<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    /**
     * Display a listing of the gallery items.
     */
    public function index(Request $request)
    {
        $limit = $request->query('limit');
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 12);
        $category = $request->query('category');

        // For now, return placeholder gallery data
        // In production, you would create a Gallery model and fetch from database
        $galleryItems = collect([]);

        // If only limit is provided, return simple array
        if ($limit && !$request->query('page') && !$request->query('per_page')) {
            return response()->json($galleryItems);
        }

        // Otherwise return paginated response
        return response()->json([
            'data' => $galleryItems,
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => $perPage,
            'total' => 0,
        ]);
    }
}
