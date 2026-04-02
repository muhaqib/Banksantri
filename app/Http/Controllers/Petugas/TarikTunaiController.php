<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TarikTunaiController extends Controller
{
    public function index()
    {
        $petugas = Auth::user();

        $pendingRequests = WithdrawalRequest::where('petugas_id', $petugas->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $riwayatPenarikan = WithdrawalRequest::with('approver')
            ->where('petugas_id', $petugas->id)
            ->where('status', '!=', 'pending')
            ->orderBy('approved_at', 'desc')
            ->limit(20)
            ->get();

        // Calculate total settled this month
        $totalSettled = WithdrawalRequest::where('petugas_id', $petugas->id)
            ->where('status', 'approved')
            ->whereMonth('approved_at', now()->month)
            ->whereYear('approved_at', now()->year)
            ->sum('nominal');

        return view('pages.petugas.tarik-tunai', [
            'saldoDigital' => $petugas->saldo ?? 0,
            'pendingRequests' => $pendingRequests,
            'riwayatPenarikan' => $riwayatPenarikan,
            'totalSettled' => $totalSettled
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nominal' => 'required|numeric|min:10000',
            'catatan' => 'nullable|string|max:500'
        ]);

        $petugas = Auth::user();

        if ($petugas->saldo < $validated['nominal']) {
            return back()->withErrors(['nominal' => 'Saldo digital tidak mencukupi'])->withInput();
        }

        WithdrawalRequest::create([
            'petugas_id' => $petugas->id,
            'nominal' => $validated['nominal'],
            'catatan' => $validated['catatan'],
            'status' => 'pending',
        ]);

        return redirect()->route('petugas.tarik-tunai')->with('success', 'Permintaan penarikan diajukan. Menunggu approval admin.');
    }
}
