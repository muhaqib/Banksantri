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

        // Handle AJAX request for chart data
        if ($request->ajax() && $request->filled('month') && $request->filled('year')) {
            return $this->getChartData($santri, $request);
        }

        $query = Transaction::with('petugas')
            ->where('santri_id', $santri->id)
            ->orderBy('created_at', 'desc');

        // Filter by selected month and year (from month selector)
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('created_at', $request->month)
                  ->whereYear('created_at', $request->year);
        }

        // Filter by type (masuk/keluar)
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        // Filter by period (only if month/year not specified)
        if ($request->filled('periode') && !$request->filled('month')) {
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

        // Calculate summary for selected month or current month
        $month = $request->filled('month') ? $request->month : now()->month;
        $year = $request->filled('year') ? $request->year : now()->year;

        $pemasukanBulanIni = Transaction::where('santri_id', $santri->id)
            ->where('jenis', 'masuk')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('nominal');

        $pengeluaranBulanIni = Transaction::where('santri_id', $santri->id)
            ->where('jenis', 'keluar')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
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

    private function getChartData($santri, Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');

        // Get days in the selected month
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        
        $labels = [];
        $pemasukan = [];
        $pengeluaran = [];

        // Generate data for each day
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $labels[] = $day;

            // Get transactions for this day
            $dailyPemasukan = Transaction::where('santri_id', $santri->id)
                ->where('jenis', 'masuk')
                ->whereDate('created_at', $date)
                ->sum('nominal');

            $dailyPengeluaran = Transaction::where('santri_id', $santri->id)
                ->where('jenis', 'keluar')
                ->whereDate('created_at', $date)
                ->sum('nominal');

            $pemasukan[] = $dailyPemasukan;
            $pengeluaran[] = $dailyPengeluaran;
        }

        // Calculate totals for the month
        $totalPemasukan = Transaction::where('santri_id', $santri->id)
            ->where('jenis', 'masuk')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('nominal');

        $totalPengeluaran = Transaction::where('santri_id', $santri->id)
            ->where('jenis', 'keluar')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('nominal');

        return response()->json([
            'success' => true,
            'pemasukan' => $totalPemasukan,
            'pengeluaran' => $totalPengeluaran,
            'chartData' => [
                'labels' => $labels,
                'pemasukan' => $pemasukan,
                'pengeluaran' => $pengeluaran
            ]
        ]);
    }
}
