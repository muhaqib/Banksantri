<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class SantriDashboardController extends Controller
{
    /**
     * Get dashboard data: balance, monthly summary, recent transactions.
     */
    public function index(Request $request)
    {
        $santri = $request->user();

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

        $transaksiTerakhir = Transaction::with('petugas:id,name')
            ->where('santri_id', $santri->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($tx) => $this->formatTransaction($tx));

        return response()->json([
            'saldo' => (int) $santri->saldo,
            'pemasukan_bulan_ini' => (int) $pemasukanBulanIni,
            'pengeluaran_bulan_ini' => (int) $pengeluaranBulanIni,
            'transaksi_terakhir' => $transaksiTerakhir,
        ]);
    }

    /**
     * Format transaction data for API response.
     */
    private function formatTransaction(Transaction $tx): array
    {
        return [
            'id' => $tx->id,
            'jenis' => $tx->jenis,
            'nominal' => (int) $tx->nominal,
            'kategori' => $tx->kategori,
            'keterangan' => $tx->keterangan,
            'saldo_sebelum' => (int) $tx->saldo_sebelum,
            'saldo_setelah' => (int) $tx->saldo_setelah,
            'petugas' => $tx->petugas ? $tx->petugas->name : null,
            'created_at' => $tx->created_at->toISOString(),
        ];
    }
}
