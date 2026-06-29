<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SantriHealthRecord;
use Illuminate\Http\Request;

class SantriHealthController extends Controller
{
    /**
     * Get health record history for the authenticated santri.
     */
    public function index(Request $request)
    {
        $santri = $request->user();

        $latestRecord = SantriHealthRecord::query()
            ->with('creator:id,name')
            ->where('santri_id', $santri->id)
            ->latest('checkup_date')
            ->first();

        $records = SantriHealthRecord::query()
            ->with('creator:id,name')
            ->where('santri_id', $santri->id)
            ->latest('checkup_date')
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'latest_record' => $latestRecord ? $this->formatRecord($latestRecord) : null,
            'data' => $records->getCollection()->map(fn (SantriHealthRecord $record) => $this->formatRecord($record)),
            'current_page' => $records->currentPage(),
            'last_page' => $records->lastPage(),
            'per_page' => $records->perPage(),
            'total' => $records->total(),
            'statuses' => SantriHealthRecord::STATUSES,
        ]);
    }

    /**
     * Get a single health record detail.
     */
    public function show(Request $request, SantriHealthRecord $health)
    {
        if ($health->santri_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $health->load('creator:id,name');

        return response()->json([
            'data' => $this->formatRecord($health),
        ]);
    }

    private function formatRecord(SantriHealthRecord $record): array
    {
        return [
            'id' => $record->id,
            'checkup_date' => $record->checkup_date?->format('Y-m-d'),
            'title' => $record->title,
            'status' => $record->status,
            'status_label' => $record->status_label,
            'location' => $record->location,
            'weight_kg' => $record->weight_kg !== null ? (float) $record->weight_kg : null,
            'height_cm' => $record->height_cm !== null ? (float) $record->height_cm : null,
            'blood_pressure' => $record->blood_pressure,
            'temperature_c' => $record->temperature_c !== null ? (float) $record->temperature_c : null,
            'complaint' => $record->complaint,
            'treatment' => $record->treatment,
            'notes' => $record->notes,
            'created_by' => $record->creator?->name,
            'created_at' => $record->created_at?->toISOString(),
            'updated_at' => $record->updated_at?->toISOString(),
        ];
    }
}
