<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrestasiSantri;
use App\Models\SantriViolation;
use Illuminate\Http\Request;

class SantriPrestasiController extends Controller
{
    /**
     * Get list of achievements for the authenticated santri.
     */
    public function index(Request $request)
    {
        $santri = $request->user();

        $allPrestasi = PrestasiSantri::where('santri_id', $santri->id)->get();
        $query = PrestasiSantri::where('santri_id', $santri->id)
            ->whereNull('tarbiyah_monthly_exam_id')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $prestasiList = $query->get();

        $totalPenguranganPoin = SantriViolation::where('santri_id', $santri->id)->sum('pengurangan_point');
        $totalPoin = max(0, $allPrestasi->sum('poin') - $totalPenguranganPoin);

        return response()->json([
            'data' => $prestasiList->map(fn($prestasi) => $this->formatPrestasi($prestasi)),
            'total_poin' => $totalPoin,
            'total_pengurangan_poin' => $totalPenguranganPoin,
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
