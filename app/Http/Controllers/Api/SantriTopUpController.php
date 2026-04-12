<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TopUpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SantriTopUpController extends Controller
{
    /**
     * Get list of top-up requests for the authenticated santri.
     */
    public function index(Request $request)
    {
        $santri = $request->user();

        $topUps = TopUpRequest::with('admin:id,name')
            ->where('santri_id', $santri->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 10));

        return response()->json([
            'data' => $topUps->getCollection()->map(fn($topUp) => $this->formatTopUp($topUp)),
            'current_page' => $topUps->currentPage(),
            'last_page' => $topUps->lastPage(),
            'per_page' => $topUps->perPage(),
            'total' => $topUps->total(),
        ]);
    }

    /**
     * Create a new top-up request.
     */
    public function store(Request $request)
    {
        $santri = $request->user();

        $validated = $request->validate([
            'nominal' => 'required|numeric|min:1000|max:10000000',
            'bukti_pembayaran' => 'required|image|max:2048',
        ]);

        $buktiPath = $request->file('bukti_pembayaran')->store('bukti_pembayaran', 'public');

        $topUp = TopUpRequest::create([
            'santri_id' => $santri->id,
            'nominal' => $validated['nominal'],
            'bukti_pembayaran' => $buktiPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Permintaan top-up berhasil dikirim. Menunggu verifikasi admin.',
            'data' => $this->formatTopUp($topUp),
        ], 201);
    }

    /**
     * Get detail of a specific top-up request.
     */
    public function show(Request $request, TopUpRequest $topUp)
    {
        if ($topUp->santri_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $this->formatTopUp($topUp),
        ]);
    }

    /**
     * Get pending top-up count.
     */
    public function pendingCount(Request $request)
    {
        $count = TopUpRequest::where('santri_id', $request->user()->id)
            ->where('status', 'pending')
            ->count();

        return response()->json([
            'pending_count' => $count,
        ]);
    }

    /**
     * Format top-up data for API response.
     */
    private function formatTopUp(TopUpRequest $topUp): array
    {
        return [
            'id' => $topUp->id,
            'nominal' => (int) $topUp->nominal,
            'bukti_pembayaran' => $topUp->bukti_pembayaran ? asset('storage/' . $topUp->bukti_pembayaran) : null,
            'status' => $topUp->status,
            'status_text' => $topUp->status_text,
            'admin_note' => $topUp->admin_note,
            'admin' => $topUp->admin ? $topUp->admin->name : null,
            'verified_at' => $topUp->verified_at?->toISOString(),
            'created_at' => $topUp->created_at->toISOString(),
        ];
    }
}
