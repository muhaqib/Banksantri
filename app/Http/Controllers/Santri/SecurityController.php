<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Models\PrestasiSantri;
use App\Models\SantriViolation;

class SecurityController extends Controller
{
    public function index()
    {
        $santri = auth()->user();
        $prestasiPoint = PrestasiSantri::where('santri_id', $santri->id)->sum('poin');
        $deductionPoint = SantriViolation::where('santri_id', $santri->id)->sum('pengurangan_point');

        return view('pages.santri.security.index', [
            'violations' => SantriViolation::query()
                ->with('creator')
                ->where('santri_id', $santri->id)
                ->latest('waktu')
                ->paginate(10),
            'prestasiPoint' => $prestasiPoint,
            'deductionPoint' => $deductionPoint,
            'netPoint' => max(0, $prestasiPoint - $deductionPoint),
        ]);
    }
}
