<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TopUpRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TopUpController extends Controller
{
    /**
     * Display pending top-up requests.
     */
    public function index()
    {
        $pendingTopUps = TopUpRequest::with('santri')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $recentTopUps = TopUpRequest::with(['santri', 'admin'])
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('pages.admin.topup.index', [
            'pendingTopUps' => $pendingTopUps,
            'recentTopUps' => $recentTopUps,
        ]);
    }

    /**
     * Show top-up request detail.
     */
    public function show(TopUpRequest $topUp)
    {
        return response()->json([
            'top_up' => $topUp->load('santri'),
            'bukti_url' => $topUp->bukti_pembayaran ? Storage::url($topUp->bukti_pembayaran) : null
        ]);
    }

    /**
     * Approve top-up request.
     */
    public function approve(TopUpRequest $topUp)
    {
        if (!$topUp->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Top-up request sudah diproses.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Get current balance before update
            $santri = $topUp->santri;
            $saldoSebelum = $santri->saldo;
            
            // Add balance to santri
            $santri->increment('saldo', $topUp->nominal);
            
            // Refresh to get updated balance
            $santri->refresh();
            $saldoSetelah = $santri->saldo;

            // Record in transactions table
            Transaction::create([
                'santri_id' => $topUp->santri_id,
                'petugas_id' => Auth::id(),
                'jenis' => 'masuk',
                'nominal' => $topUp->nominal,
                'kategori' => 'top_up',
                'keterangan' => 'Top up saldo - Verifikasi oleh ' . Auth::user()->name,
                'saldo_sebelum' => $saldoSebelum,
                'saldo_setelah' => $saldoSetelah,
            ]);

            // Update top-up status
            $topUp->update([
                'status' => 'approved',
                'admin_id' => Auth::id(),
                'verified_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Top-up berhasil diverifikasi. Saldo santri telah bertambah.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memverifikasi top-up: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject top-up request.
     */
    public function reject(TopUpRequest $topUp, Request $request)
    {
        if (!$topUp->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Top-up request sudah diproses.'
            ], 400);
        }

        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $topUp->update([
            'status' => 'rejected',
            'admin_id' => Auth::id(),
            'admin_note' => $validated['admin_note'] ?? null,
            'verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Top-up berhasil ditolak.'
        ]);
    }

    /**
     * Get modal data for top-up detail.
     */
    public function getModalData(TopUpRequest $topUp)
    {
        return response()->json([
            'top_up' => $topUp->load('santri'),
            'bukti_url' => $topUp->bukti_pembayaran ? Storage::url($topUp->bukti_pembayaran) : null,
            'santri_foto_url' => $topUp->santri->foto ? Storage::url($topUp->santri->foto) : null
        ]);
    }
}
