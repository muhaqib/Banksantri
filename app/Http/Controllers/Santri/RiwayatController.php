<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class RiwayatController extends Controller
{
    public function index()
    {
        $santri = Auth::user();

        $transaksiList = Transaction::with('petugas')
            ->where('santri_id', $santri->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('pages.santri.riwayat', [
            'transaksiList' => $transaksiList
        ]);
    }
}
