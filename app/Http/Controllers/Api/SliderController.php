<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    /**
     * Display slider data.
     */
    public function index()
    {
        // Return placeholder slider data
        // In production, you would create a Slider model and fetch from database
        return response()->json([
            [
                'id' => 1,
                'title' => 'Selamat Datang di Pondok Pesantren Mambaul Hikmah',
                'description' => 'Membentuk generasi Qurani yang berakhlakul karimah',
                'image' => '/images/banners/banner1.jpg',
                'link' => '/profile',
            ],
            [
                'id' => 2,
                'title' => 'Pendidikan Berkualitas',
                'description' => 'Kurikulum terpadu dengan fasilitas modern',
                'image' => '/images/banners/banner2.jpg',
                'link' => '/profile',
            ],
        ]);
    }
}
