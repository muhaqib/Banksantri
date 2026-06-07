<?php

namespace App\Http\Controllers;

use App\Models\Kitab;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KitabController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('kitabs', 'nama')],
            'kategori' => ['required', 'string', 'max:100'],
            'gambar' => ['nullable', 'image', 'max:2048'],
        ]);

        $kitab = Kitab::create([
            'nama' => $validated['nama'],
            'kategori' => $validated['kategori'],
            'gambar' => $request->file('gambar')?->store('fotos/kitab', 'public'),
            'created_by' => $request->user()->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Kitab baru berhasil ditambahkan.',
                'kitab' => $kitab,
            ], 201);
        }

        return back()->with('success', 'Kitab baru berhasil ditambahkan.');
    }
}
