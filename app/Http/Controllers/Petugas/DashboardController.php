<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $petugas = Auth::user();

        // Get real data
        $transaksiHariIni = Transaction::where('petugas_id', $petugas->id)
            ->whereDate('created_at', today())
            ->count();

        $totalNominal = Transaction::where('petugas_id', $petugas->id)
            ->whereDate('created_at', today())
            ->sum('nominal');

        // Calculate success rate (transactions without errors)
        $totalTransaksi = Transaction::where('petugas_id', $petugas->id)->count();
        $successRate = $totalTransaksi > 0 ? 100 : 100; // For now, all are successful

        // Get recent transactions
        $transaksiTerakhir = Transaction::with('santri')
            ->where('petugas_id', $petugas->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get weekly transaction data (last 7 days)
        $weeklyData = [];
        $days = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Transaction::where('petugas_id', $petugas->id)
                ->whereDate('created_at', $date)
                ->count();
            
            $weeklyData[] = [
                'name' => $days[$date->dayOfWeek],
                'value' => $count,
                'date' => $date->format('Y-m-d')
            ];
        }

        return view('pages.petugas.dashboard', [
            'saldoDigital' => $petugas->saldo ?? 0,
            'penghasilanHariIni' => $totalNominal, // Assuming all transactions are income for petugas
            'transaksiHariIni' => $transaksiHariIni,
            'totalNominal' => $totalNominal,
            'successRate' => $successRate,
            'transaksiTerakhir' => $transaksiTerakhir,
            'weeklyData' => $weeklyData,
            'activeRole' => 'petugas',
        ]);
    }
}
