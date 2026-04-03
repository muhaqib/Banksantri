<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class RiwayatController extends Controller
{
    public function index()
    {
        $petugas = Auth::user();

        $transaksiList = Transaction::with('santri')
            ->where('petugas_id', $petugas->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalKeluar = Transaction::where('petugas_id', $petugas->id)
            ->where('jenis', 'keluar')
            ->sum('nominal');

        $totalMasuk = Transaction::where('petugas_id', $petugas->id)
            ->where('jenis', 'masuk')
            ->sum('nominal');

        return view('pages.petugas.riwayat', [
            'transaksiList' => $transaksiList,
            'totalKeluar' => $totalKeluar,
            'totalMasuk' => $totalMasuk,
            'activeRole' => 'petugas',
        ]);
    }
}
