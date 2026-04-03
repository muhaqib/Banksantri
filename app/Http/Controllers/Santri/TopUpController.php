<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Models\TopUpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TopUpController extends Controller
{
    /**
     * Show top-up form.
     */
    public function create()
    {
        $recentTopUps = TopUpRequest::where('santri_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('pages.santri.topup', [
            'recentTopUps' => $recentTopUps
        ]);
    }

    /**
     * Store a new top-up request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nominal' => 'required|numeric|min:1000|max:10000000',
            'bukti_pembayaran' => 'required|image|max:2048',
        ]);

        // Handle file upload
        $buktiPath = $request->file('bukti_pembayaran')->store('bukti_pembayaran', 'public');

        // Create top-up request with pending status
        TopUpRequest::create([
            'santri_id' => Auth::id(),
            'nominal' => $validated['nominal'],
            'bukti_pembayaran' => $buktiPath,
            'status' => 'pending',
        ]);

        return redirect()->route('santri.topup')
            ->with('success', 'Permintaan top-up berhasil dikirim. Menunggu verifikasi admin.');
    }

    /**
     * Get top-up request status via AJAX.
     */
    public function getStatus()
    {
        $topUps = TopUpRequest::where('santri_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'top_ups' => $topUps
        ]);
    }
}
