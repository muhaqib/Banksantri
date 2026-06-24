<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Models\PrestasiSantri;
use App\Models\SantriViolation;
use Illuminate\Http\Request;

class PrestasiController extends Controller
{
    /**
     * Display prestasi list for santri.
     */
    public function index()
    {
        $santri = auth()->user();
        
        $allPrestasi = PrestasiSantri::where('santri_id', $santri->id)->get();
        $prestasiList = PrestasiSantri::where('santri_id', $santri->id)
            ->whereNull('tarbiyah_monthly_exam_id')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPenguranganPoin = SantriViolation::where('santri_id', $santri->id)->sum('pengurangan_point');
        $totalPoin = max(0, $allPrestasi->sum('poin') - $totalPenguranganPoin);

        return view('pages.santri.prestasi.index', [
            'prestasiList' => $prestasiList,
            'totalPoin' => $totalPoin,
            'totalPenguranganPoin' => $totalPenguranganPoin,
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
