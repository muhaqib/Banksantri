<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KasTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display top up form
     */
    public function createTopUp(Request $request)
    {
        $nis = $request->query('nis');

        return view('pages.admin.transactions.topup', [
            'activeRole' => 'admin',
            'nis' => $nis,
        ]);
    }

    /**
     * Process top up
     */
    public function storeTopUp(Request $request)
    {
        $validated = $request->validate([
            'nis' => 'required|string|exists:users,nis',
            'nominal' => 'required|numeric|min:10000',
            'sumber_dana' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $santri = User::activeSantri()
            ->where('nis', $validated['nis'])
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $saldoSebelum = $santri->saldo;
            $saldoSetelah = $saldoSebelum + $validated['nominal'];

            // Create transaction
            $transaction = Transaction::create([
                'santri_id' => $santri->id,
                'petugas_id' => Auth::id(), // Admin who processed
                'jenis' => 'masuk',
                'nominal' => $validated['nominal'],
                'kategori' => 'top_up',
                'keterangan' => collect([
                    $validated['keterangan'] ?? null,
                    'Sumber: '.$validated['sumber_dana'],
                ])->filter()->implode(' - '),
                'saldo_sebelum' => $saldoSebelum,
                'saldo_setelah' => $saldoSetelah,
            ]);

            // Update santri saldo
            $santri->update([
                'saldo' => $saldoSetelah,
            ]);

            DB::commit();

            return redirect()->route('admin.transactions.receipt', $transaction)
                ->with('success', 'Top up berhasil. Kwitansi siap dicetak.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Terjadi kesalahan: '.$e->getMessage()])->withInput();
        }
    }

    /**
     * Display santri list
     */
    public function santriList(Request $request)
    {
        $query = User::activeSantri();

        // Add search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('nis', 'like', '%'.$search.'%');
            });
        }

        $santriList = $query->orderBy('name', 'asc')->paginate(20);

        return view('pages.admin.transactions.santri-list', [
            'santriList' => $santriList,
            'activeRole' => 'admin',
        ]);
    }

    /**
     * Display transaction history
     */
    public function receipt(Transaction $transaction)
    {
        abort_unless($transaction->petugas_id === Auth::id() && $transaction->kategori === 'top_up', 403);

        $transaction->load(['santri', 'petugas']);

        return view('pages.admin.transactions.receipt', [
            'transaction' => $transaction,
            'terbilang' => $this->terbilang($transaction->nominal).' rupiah',
            'activeRole' => 'admin',
        ]);
    }

    /**
     * Display activities executed by the authenticated admin.
     */
    public function history(Request $request)
    {
        $adminId = Auth::id();
        $perPage = 20;

        $transactionActivities = Transaction::where('petugas_id', $adminId)
            ->get()
            ->map(fn (Transaction $transaction) => (object) [
                'executed_at' => $transaction->created_at,
                'activity' => $transaction->kategori === 'top_up' ? 'Top Up' : 'Transaksi '.ucfirst($transaction->kategori),
                'status' => 'Berhasil',
                'nominal' => $transaction->nominal,
                'direction' => $transaction->jenis,
                'description' => $transaction->keterangan,
                'receipt_url' => $transaction->kategori === 'top_up'
                    ? route('admin.transactions.receipt', ['transaction' => $transaction, 'print' => 1])
                    : null,
            ]);

        $settlementActivities = WithdrawalRequest::with('petugas')
            ->where('approved_by', $adminId)
            ->where('status', '!=', 'pending')
            ->get()
            ->map(fn (WithdrawalRequest $settlement) => (object) [
                'executed_at' => $settlement->approved_at ?? $settlement->updated_at,
                'activity' => 'Settlement',
                'status' => $settlement->status === 'approved' ? 'Disetujui' : 'Ditolak',
                'nominal' => $settlement->nominal,
                'direction' => 'keluar',
                'description' => collect([
                    $settlement->petugas?->name ? 'Petugas: '.$settlement->petugas->name : null,
                    $settlement->catatan,
                ])->filter()->implode(' - '),
                'receipt_url' => null,
            ]);

        $cashActivities = KasTransaction::where('created_by', $adminId)
            ->get()
            ->map(fn (KasTransaction $cashTransaction) => (object) [
                'executed_at' => $cashTransaction->created_at,
                'activity' => 'Transaksi Kas',
                'status' => 'Berhasil',
                'nominal' => $cashTransaction->nominal,
                'direction' => $cashTransaction->jenis,
                'description' => collect([
                    $cashTransaction->sumber_dana ? 'Sumber: '.$cashTransaction->sumber_dana : null,
                    $cashTransaction->keperluan ? 'Keperluan: '.$cashTransaction->keperluan : null,
                    $cashTransaction->keterangan,
                ])->filter()->implode(' - '),
                'receipt_url' => null,
            ]);

        $activities = $transactionActivities
            ->concat($settlementActivities)
            ->concat($cashActivities)
            ->sortByDesc('executed_at')
            ->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginatedActivities = new LengthAwarePaginator(
            $activities->forPage($page, $perPage)->values(),
            $activities->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('pages.admin.transactions.history', [
            'activities' => $paginatedActivities,
            'totalTopUp' => $transactionActivities->where('activity', 'Top Up')->sum('nominal'),
            'totalSettlement' => $settlementActivities->where('status', 'Disetujui')->sum('nominal'),
            'activeRole' => 'admin',
        ]);
    }

    /**
     * Search santri by NIS or Name
     */
    public function searchSantri(Request $request)
    {
        $request->validate([
            'search' => 'required|string',
        ]);

        $santriList = User::activeSantri()
            ->where(function ($query) use ($request) {
                $query->where('nis', 'like', '%'.$request->search.'%')
                    ->orWhere('name', 'like', '%'.$request->search.'%');
            })
            ->orderByRaw('CASE WHEN nis = ? THEN 0 ELSE 1 END', [$request->search])
            ->orderBy('name')
            ->limit(8)
            ->get();

        if ($santriList->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Santri tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $santriList->map(fn (User $santri) => [
                'id' => $santri->id,
                'nis' => $santri->nis,
                'nama' => $santri->name,
                'email' => $santri->email,
                'alamat' => $santri->alamat,
                'saldo' => $santri->saldo,
                'foto_url' => $santri->foto ? asset('storage/'.$santri->foto) : null,
            ]),
        ]);
    }

    private function terbilang(int $number): string
    {
        $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($number < 12) {
            return $words[$number];
        }

        if ($number < 20) {
            return $this->terbilang($number - 10).' belas';
        }

        if ($number < 100) {
            return $this->terbilang(intdiv($number, 10)).' puluh'.($number % 10 ? ' '.$this->terbilang($number % 10) : '');
        }

        if ($number < 200) {
            return 'seratus'.($number > 100 ? ' '.$this->terbilang($number - 100) : '');
        }

        if ($number < 1000) {
            return $this->terbilang(intdiv($number, 100)).' ratus'.($number % 100 ? ' '.$this->terbilang($number % 100) : '');
        }

        if ($number < 2000) {
            return 'seribu'.($number > 1000 ? ' '.$this->terbilang($number - 1000) : '');
        }

        if ($number < 1000000) {
            return $this->terbilang(intdiv($number, 1000)).' ribu'.($number % 1000 ? ' '.$this->terbilang($number % 1000) : '');
        }

        if ($number < 1000000000) {
            return $this->terbilang(intdiv($number, 1000000)).' juta'.($number % 1000000 ? ' '.$this->terbilang($number % 1000000) : '');
        }

        return number_format($number, 0, ',', '.');
    }
}
