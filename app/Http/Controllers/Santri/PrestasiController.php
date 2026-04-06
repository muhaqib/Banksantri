<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Models\PrestasiSantri;
use Illuminate\Http\Request;

class PrestasiController extends Controller
{
    /**
     * Display prestasi list for santri.
     */
    public function index()
    {
        $santri = auth()->user();
        
        $prestasiList = PrestasiSantri::where('santri_id', $santri->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPoin = $prestasiList->sum('poin');

        return view('pages.santri.prestasi.index', [
            'prestasiList' => $prestasiList,
            'totalPoin' => $totalPoin,
        ]);
    }

    /**
     * Display detail of a specific prestasi.
     */
    public function show(PrestasiSantri $prestasi)
    {
        $santri = auth()->user();
        
        // Ensure santri can only view their own prestasi
        if ($prestasi->santri_id !== $santri->id) {
            abort(403);
        }

        return view('pages.santri.prestasi.show', [
            'prestasi' => $prestasi,
        ]);
    }
}
