<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KasTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KasController extends Controller
{
    public function index()
    {
        // Get current kas balance
        $masuk = KasTransaction::where('jenis', 'masuk')->sum('nominal');
        $keluar = KasTransaction::where('jenis', 'keluar')->sum('nominal');
        $saldoKas = $masuk - $keluar;

        // Get recent transactions
        $riwayatKas = KasTransaction::with('creator')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('pages.admin.kas', [
            'saldoKas' => $saldoKas,
            'riwayatKas' => $riwayatKas
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis' => 'required|in:masuk,keluar',
            'nominal' => 'required|numeric|min:1000',
            'sumber_dana' => 'required_if:jenis,masuk|nullable|string|max:100',
            'keperluan' => 'required_if:jenis,keluar|nullable|string|max:100',
            'keterangan' => 'required|string|max:500'
        ]);

        // Get current balance
        $masuk = KasTransaction::where('jenis', 'masuk')->sum('nominal');
        $keluar = KasTransaction::where('jenis', 'keluar')->sum('nominal');
        $saldoSebelum = $masuk - $keluar;

        DB::beginTransaction();
        try {
            $saldoSetelah = $validated['jenis'] === 'masuk' 
                ? $saldoSebelum + $validated['nominal']
                : $saldoSebelum - $validated['nominal'];

            if ($saldoSetelah < 0) {
                throw new \Exception('Saldo kas tidak mencukupi');
            }

            KasTransaction::create([
                'jenis' => $validated['jenis'],
                'nominal' => $validated['nominal'],
                'sumber_dana' => $validated['sumber_dana'] ?? null,
                'keperluan' => $validated['keperluan'] ?? null,
                'keterangan' => $validated['keterangan'],
                'saldo_sebelum' => $saldoSebelum,
                'saldo_setelah' => $saldoSetelah,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('admin.kas')->with('success', 'Transaksi kas berhasil diproses');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.kas')->with('error', $e->getMessage());
        }
    }
}
