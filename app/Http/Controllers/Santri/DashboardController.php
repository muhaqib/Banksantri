<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $santri = Auth::user();

        // Get real data
        $pemasukanBulanIni = Transaction::where('santri_id', $santri->id)
            ->where('jenis', 'masuk')
            ->whereMonth('created_at', now()->month)
            ->sum('nominal');

        $pengeluaranBulanIni = Transaction::where('santri_id', $santri->id)
            ->where('jenis', 'keluar')
            ->whereMonth('created_at', now()->month)
            ->sum('nominal');

        // Get recent transactions
        $transaksiTerakhir = Transaction::with('petugas')
            ->where('santri_id', $santri->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('pages.santri.home', [
            'saldo' => $santri->saldo ?? 0,
            'pemasukanBulanIni' => $pemasukanBulanIni,
            'pengeluaranBulanIni' => $pengeluaranBulanIni,
            'transaksiTerakhir' => $transaksiTerakhir,
        ]);
    }
}
