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
        // Total Top Up Santri Hari Ini (kategori: top_up)
        $totalTopUpHariIni = Transaction::where('jenis', 'masuk')
            ->where('kategori', 'top_up')
            ->whereDate('created_at', today())
            ->sum('nominal');

        // Total Transaksi Hari Ini (semua transaksi keluar)
        $totalTransaksiHariIni = Transaction::where('jenis', 'keluar')
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

        // Count pending settlements for sidebar badge
        $pendingSettlementCount = WithdrawalRequest::where('status', 'pending')->count();

        // Get pending top-up count
        $pendingTopUpCount = TopUpRequest::where('status', 'pending')->count();

        // Get recent top-up activity
        $recentTopUps = TopUpRequest::with(['santri'])
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Get weekly transaction trends (last 7 days)
        $weeklyTrends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayName = $date->locale('id')->isoFormat('ddd');
            
            // Top up santri (hijau)
            $topUpAmount = Transaction::where('jenis', 'masuk')
                ->where('kategori', 'top_up')
                ->whereDate('created_at', $date)
                ->sum('nominal');
            
            // Transaksi keluar (merah)
            $transaksiAmount = Transaction::where('jenis', 'keluar')
                ->whereDate('created_at', $date)
                ->sum('nominal');
            
            $weeklyTrends[] = [
                'name' => strtoupper($dayName),
                'topup' => $topUpAmount,
                'transaksi' => $transaksiAmount,
            ];
        }

        // Calculate max value for chart scaling
        $maxValue = max(
            collect($weeklyTrends)->max('topup') ?? 0,
            collect($weeklyTrends)->max('transaksi') ?? 0,
            1 // Prevent division by zero
        );

        // Normalize values to percentage (max 100%)
        foreach ($weeklyTrends as &$trend) {
            $trend['topup_percent'] = ($trend['topup'] / $maxValue) * 100;
            $trend['transaksi_percent'] = ($trend['transaksi'] / $maxValue) * 100;
        }

        return view('pages.admin.dashboard', [
            'totalTopUpHariIni' => $totalTopUpHariIni,
            'totalTransaksiHariIni' => $totalTransaksiHariIni,
            'transaksiHariIni' => $transaksiHariIni,
            'saldoKasUtama' => $saldoKasUtama,
            'transaksiTerakhir' => $transaksiTerakhir,
            'petugasList' => $petugasList,
            'pendingRequests' => $pendingRequests,
            'pendingSettlementCount' => $pendingSettlementCount,
            'pendingTopUpCount' => $pendingTopUpCount,
            'recentTopUps' => $recentTopUps,
            'weeklyTrends' => $weeklyTrends,
            'activeRole' => 'admin',
        ]);
    }
}
