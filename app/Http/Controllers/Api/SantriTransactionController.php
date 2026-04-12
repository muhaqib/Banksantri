<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class SantriTransactionController extends Controller
{
    /**
     * Get transaction history with filters and pagination.
     */
    public function index(Request $request)
    {
        $santri = $request->user();

        $query = Transaction::with('petugas:id,name')
            ->where('santri_id', $santri->id)
            ->orderBy('created_at', 'desc');

        // Filter by type (masuk/keluar)
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        // Filter by category
        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }

        // Filter by month and year
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('created_at', $request->month)
                ->whereYear('created_at', $request->year);
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

        $perPage = $request->query('per_page', 20);
        $transactions = $query->paginate($perPage);

        $month = $request->filled('month') ? (int) $request->month : now()->month;
        $year = $request->filled('year') ? (int) $request->year : now()->year;

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

        return response()->json([
            'data' => $transactions->getCollection()->map(fn($tx) => $this->formatTransaction($tx)),
            'current_page' => $transactions->currentPage(),
            'last_page' => $transactions->lastPage(),
            'per_page' => $transactions->perPage(),
            'total' => $transactions->total(),
            'summary' => [
                'pemasukan_bulan_ini' => (int) $pemasukanBulanIni,
                'pengeluaran_bulan_ini' => (int) $pengeluaranBulanIni,
            ],
        ]);
    }

    /**
     * Get chart data for a specific month/year.
     */
    public function chartData(Request $request)
    {
        $santri = $request->user();

        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000',
        ]);

        $month = $validated['month'];
        $year = $validated['year'];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $labels = [];
        $pemasukan = [];
        $pengeluaran = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $labels[] = $day;

            $dailyPemasukan = Transaction::where('santri_id', $santri->id)
                ->where('jenis', 'masuk')
                ->whereDate('created_at', $date)
                ->sum('nominal');

            $dailyPengeluaran = Transaction::where('santri_id', $santri->id)
                ->where('jenis', 'keluar')
                ->whereDate('created_at', $date)
                ->sum('nominal');

            $pemasukan[] = (int) $dailyPemasukan;
            $pengeluaran[] = (int) $dailyPengeluaran;
        }

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
            'chart_data' => [
                'labels' => $labels,
                'pemasukan' => $pemasukan,
                'pengeluaran' => $pengeluaran,
            ],
            'total_pemasukan' => (int) $totalPemasukan,
            'total_pengeluaran' => (int) $totalPengeluaran,
        ]);
    }

    /**
     * Get a single transaction detail.
     */
    public function show(Request $request, Transaction $transaction)
    {
        if ($transaction->santri_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $this->formatTransaction($transaction),
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
