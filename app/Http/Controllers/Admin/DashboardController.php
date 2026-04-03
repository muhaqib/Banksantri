<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TopUpRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Get real data from database
        $masuk = Transaction::where('jenis', 'masuk')
            ->whereDate('created_at', today())
            ->sum('nominal');

        $keluar = Transaction::where('jenis', 'keluar')
            ->whereDate('created_at', today())
            ->sum('nominal');

        $transaksiHariIni = Transaction::whereDate('created_at', today())->count();

        // Get kas balance
        $kasMasuk = \App\Models\KasTransaction::where('jenis', 'masuk')->sum('nominal');
        $kasKeluar = \App\Models\KasTransaction::where('jenis', 'keluar')->sum('nominal');
        $saldoKasUtama = $kasMasuk - $kasKeluar;

        // Get recent transactions
        $transaksiTerakhir = Transaction::with(['santri', 'petugas'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get petugas list with performance
        $petugasList = User::where('role', 'petugas')
            ->withCount(['transactions as total_transaksi' => function($query) {
                $query->whereDate('created_at', today());
            }])
            ->withSum(['transactions as total_nominal' => function($query) {
                $query->whereDate('created_at', today());
            }], 'nominal')
            ->get()
            ->map(function($petugas) {
                $petugas->saldo_digital = $petugas->saldo ?? 0;
                $petugas->is_active = true;
                return $petugas;
            });

        // Get pending settlement requests
        $pendingRequests = WithdrawalRequest::with('petugas')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get pending top-up count
        $pendingTopUpCount = TopUpRequest::where('status', 'pending')->count();

        return view('pages.admin.dashboard', [
            'pemasukanHariIni' => $masuk,
            'pengeluaranHariIni' => $keluar,
            'transaksiHariIni' => $transaksiHariIni,
            'saldoKasUtama' => $saldoKasUtama,
            'transaksiTerakhir' => $transaksiTerakhir,
            'petugasList' => $petugasList,
            'pendingRequests' => $pendingRequests,
            'pendingTopUpCount' => $pendingTopUpCount,
        ]);
    }
}
