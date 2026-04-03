<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $santri = Auth::user();

        $query = Transaction::with('petugas')
            ->where('santri_id', $santri->id)
            ->orderBy('created_at', 'desc');

        // Filter by type (masuk/keluar)
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        // Filter by period
        if ($request->filled('periode')) {
            switch ($request->periode) {
                case 'hari':
                    $query->whereDate('created_at', today());
                    break;
                case 'minggu':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'bulan':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'tahun':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        // Filter by category
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $transaksiList = $query->paginate(20);

        // Calculate summary for current month
        $pemasukanBulanIni = Transaction::where('santri_id', $santri->id)
            ->where('jenis', 'masuk')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('nominal');

        $pengeluaranBulanIni = Transaction::where('santri_id', $santri->id)
            ->where('jenis', 'keluar')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('nominal');

        return view('pages.santri.riwayat', [
            'transaksiList' => $transaksiList,
            'pemasukanBulanIni' => $pemasukanBulanIni,
            'pengeluaranBulanIni' => $pengeluaranBulanIni,
            'currentFilter' => $request->input('jenis', 'all'),
            'currentPeriode' => $request->input('periode', 'bulan'),
            'currentKategori' => $request->input('kategori', 'all'),
        ]);
    }
}
