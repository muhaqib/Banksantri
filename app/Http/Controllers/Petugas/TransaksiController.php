<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index()
    {
        return view('pages.petugas.transaksi', [
            'activeRole' => 'petugas',
        ]);
    }

    public function scanRfid(Request $request)
    {
        return $this->cariSantri($request);
    }

    public function cariSantri(Request $request)
    {
        \Log::info('Cari Santri - NIS: ' . $request->nis);
        
        $request->validate([
            'nis' => 'required|string'
        ]);

        $santri = User::where('nis', $request->nis)
            ->where('role', 'santri')
            ->first();

        \Log::info('Santri found: ' . ($santri ? $santri->name : 'null'));

        if (!$santri) {
            return response()->json([
                'success' => false,
                'message' => 'Santri tidak ditemukan'
            ], 404);
        }

        // Get recent transactions
        $riwayat = Transaction::where('santri_id', $santri->id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $santri->id,
                'nis' => $santri->nis,
                'nama' => $santri->name,
                'saldo' => $santri->saldo,
                'riwayat' => $riwayat
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'santri_id' => 'required|exists:users,id',
            'nominal' => 'required|numeric|min:1000',
            'kategori' => 'required|in:kantin,koperasi,laundry,fotokopi,lainnya',
            'keterangan' => 'nullable|string|max:500',
            'pin' => 'required|string|size:6'
        ]);

        $santri = User::findOrFail($request->santri_id);
        
        // Verify PIN
        if ($santri->pin !== $request->pin) {
            return back()->withErrors(['pin' => 'PIN salah'])->withInput();
        }

        // Check saldo
        if ($santri->saldo < $request->nominal) {
            return back()->withErrors(['nominal' => 'Saldo santri tidak mencukupi'])->withInput();
        }

        DB::beginTransaction();
        try {
            $saldoSebelum = $santri->saldo;
            $saldoSetelah = $saldoSebelum - $request->nominal;

            // Create transaction
            Transaction::create([
                'santri_id' => $santri->id,
                'petugas_id' => Auth::id(),
                'jenis' => 'keluar',
                'nominal' => $request->nominal,
                'kategori' => $request->kategori,
                'keterangan' => $request->keterangan,
                'saldo_sebelum' => $saldoSebelum,
                'saldo_setelah' => $saldoSetelah,
            ]);

            // Update santri saldo
            $santri->update([
                'saldo' => $saldoSetelah
            ]);

            // Update petugas saldo (for certain categories)
            if (in_array($request->kategori, ['kantin', 'koperasi', 'laundry', 'fotokopi'])) {
                $petugas = Auth::user();
                $petugas->update([
                    'saldo' => $petugas->saldo + $request->nominal
                ]);
            }

            DB::commit();

            return redirect()->route('petugas.transaksi')->with('success', 'Transaksi berhasil diproses');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }
}
