<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
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
            'keterangan' => 'required|string|max:500'
        ]);

        $santri = User::where('nis', $validated['nis'])
            ->where('role', 'santri')
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $saldoSebelum = $santri->saldo;
            $saldoSetelah = $saldoSebelum + $validated['nominal'];

            // Create transaction
            Transaction::create([
                'santri_id' => $santri->id,
                'petugas_id' => Auth::id(), // Admin who processed
                'jenis' => 'masuk',
                'nominal' => $validated['nominal'],
                'kategori' => 'top_up',
                'keterangan' => $validated['keterangan'] . ' - Sumber: ' . $validated['sumber_dana'],
                'saldo_sebelum' => $saldoSebelum,
                'saldo_setelah' => $saldoSetelah,
            ]);

            // Update santri saldo
            $santri->update([
                'saldo' => $saldoSetelah
            ]);

            DB::commit();

            return redirect()->route('admin.transactions.topup')
                ->with('success', 'Top up berhasil! Saldo ' . $santri->name . ' bertambah Rp ' . number_format($validated['nominal'], 0, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display santri list
     */
    public function santriList(Request $request)
    {
        $query = User::where('role', 'santri');
        
        // Add search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('nis', 'like', '%' . $search . '%');
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
    public function history()
    {
        $transactions = Transaction::with(['santri', 'petugas'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalMasuk = Transaction::where('jenis', 'masuk')->sum('nominal');
        $totalKeluar = Transaction::where('jenis', 'keluar')->sum('nominal');

        return view('pages.admin.transactions.history', [
            'transactions' => $transactions,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'activeRole' => 'admin',
        ]);
    }

    /**
     * Search santri by NIS or Name
     */
    public function searchSantri(Request $request)
    {
        $request->validate([
            'search' => 'required|string'
        ]);

        $santri = User::where('role', 'santri')
            ->where(function($query) use ($request) {
                $query->where('nis', $request->search)
                      ->orWhere('name', 'like', '%' . $request->search . '%');
            })
            ->first();

        if (!$santri) {
            return response()->json([
                'success' => false,
                'message' => 'Santri tidak ditemukan'
            ], 404);
        }

        $riwayat = Transaction::where('santri_id', $santri->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $santri->id,
                'nis' => $santri->nis,
                'nama' => $santri->name,
                'email' => $santri->email,
                'saldo' => $santri->saldo,
                'foto_url' => $santri->foto ? asset('storage/' . $santri->foto) : null,
                'riwayat' => $riwayat
            ]
        ]);
    }
}
