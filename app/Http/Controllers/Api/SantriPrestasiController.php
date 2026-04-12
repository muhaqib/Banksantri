<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrestasiSantri;
use Illuminate\Http\Request;

class SantriPrestasiController extends Controller
{
    /**
     * Get list of achievements for the authenticated santri.
     */
    public function index(Request $request)
    {
        $santri = $request->user();

        $query = PrestasiSantri::where('santri_id', $santri->id)
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $prestasiList = $query->get();

        $totalPoin = $prestasiList->sum('poin');

        return response()->json([
            'data' => $prestasiList->map(fn($prestasi) => $this->formatPrestasi($prestasi)),
            'total_poin' => $totalPoin,
        ]);
    }

    /**
     * Get detail of a specific achievement.
     */
    public function show(Request $request, PrestasiSantri $prestasi)
    {
        if ($prestasi->santri_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $this->formatPrestasi($prestasi),
        ]);
    }

    /**
     * Format prestasi data for API response.
     */
    private function formatPrestasi(PrestasiSantri $prestasi): array
    {
        return [
            'id' => $prestasi->id,
            'nama_kitab' => $prestasi->nama_kitab,
            'kategori' => $prestasi->kategori,
            'keterangan' => $prestasi->keterangan,
            'status' => $prestasi->status,
            'status_text' => $prestasi->status_text,
            'nilai' => $prestasi->nilai,
            'skor' => $prestasi->skor,
            'poin' => $prestasi->poin,
            'tanggal_selesai' => $prestasi->tanggal_selesai?->format('Y-m-d'),
            'bulan_tahun_selesai' => $prestasi->bulan_tahun_selesai,
            'ustadz_pembimbing' => $prestasi->ustadz_pembimbing,
            'foto_kitab' => $prestasi->foto_kitab ? asset('storage/' . $prestasi->foto_kitab) : null,
            'catatan_ustadz' => $prestasi->catatan_ustadz,
            'tags' => $prestasi->tags ? explode(',', $prestasi->tags) : [],
            'created_at' => $prestasi->created_at->toISOString(),
            'updated_at' => $prestasi->updated_at->toISOString(),
        ];
    }
}
