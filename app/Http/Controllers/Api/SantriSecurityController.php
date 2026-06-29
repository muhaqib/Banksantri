<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrestasiSantri;
use App\Models\SantriViolation;
use Illuminate\Http\Request;

class SantriSecurityController extends Controller
{
    /**
     * Get security violation history and point summary.
     */
    public function index(Request $request)
    {
        $santri = $request->user();

        $prestasiPoint = (int) PrestasiSantri::where('santri_id', $santri->id)->sum('poin');
        $deductionPoint = (int) SantriViolation::where('santri_id', $santri->id)->sum('pengurangan_point');

        $violations = SantriViolation::query()
            ->with('creator:id,name')
            ->where('santri_id', $santri->id)
            ->latest('waktu')
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'data' => $violations->getCollection()->map(fn (SantriViolation $violation) => $this->formatViolation($violation)),
            'current_page' => $violations->currentPage(),
            'last_page' => $violations->lastPage(),
            'per_page' => $violations->perPage(),
            'total' => $violations->total(),
            'summary' => [
                'prestasi_point' => $prestasiPoint,
                'deduction_point' => $deductionPoint,
                'net_point' => max(0, $prestasiPoint - $deductionPoint),
            ],
        ]);
    }

    /**
     * Get a single violation detail.
     */
    public function show(Request $request, SantriViolation $violation)
    {
        if ($violation->santri_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $violation->load('creator:id,name');

        return response()->json([
            'data' => $this->formatViolation($violation),
        ]);
    }

    private function formatViolation(SantriViolation $violation): array
    {
        return [
            'id' => $violation->id,
            'jenis_pelanggaran' => $violation->jenis_pelanggaran,
            'waktu' => $violation->waktu?->toISOString(),
            'pengurangan_point' => (int) $violation->pengurangan_point,
            'keterangan' => $violation->keterangan,
            'created_by' => $violation->creator?->name,
            'created_at' => $violation->created_at?->toISOString(),
            'updated_at' => $violation->updated_at?->toISOString(),
        ];
    }
}
