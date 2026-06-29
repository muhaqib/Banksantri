<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SantriPermission;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SantriPermissionController extends Controller
{
    /**
     * Get permission history for the authenticated santri.
     */
    public function index(Request $request)
    {
        $santri = $request->user();

        $monthPicker = $request->input('month_picker', now()->format('Y-m'));
        [$year, $month] = array_pad(explode('-', $monthPicker), 2, null);

        $year = (int) ($request->input('year') ?: $year ?: now()->year);
        $month = (int) ($request->input('month') ?: $month ?: now()->month);
        $selectedDate = Carbon::create($year, $month, 1);
        $startOfMonth = $selectedDate->copy()->startOfMonth()->toDateString();
        $endOfMonth = $selectedDate->copy()->endOfMonth()->toDateString();

        $permissions = SantriPermission::query()
            ->with('creator:id,name')
            ->where('santri_id', $santri->id)
            ->whereDate('start_date', '<=', $endOfMonth)
            ->whereDate('end_date', '>=', $startOfMonth)
            ->latest('start_date')
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'data' => $permissions->getCollection()->map(fn (SantriPermission $permission) => $this->formatPermission($permission)),
            'current_page' => $permissions->currentPage(),
            'last_page' => $permissions->lastPage(),
            'per_page' => $permissions->perPage(),
            'total' => $permissions->total(),
            'filters' => [
                'month' => $month,
                'year' => $year,
                'month_picker' => $selectedDate->format('Y-m'),
            ],
        ]);
    }

    /**
     * Get a single permission detail.
     */
    public function show(Request $request, SantriPermission $permission)
    {
        if ($permission->santri_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $permission->load('creator:id,name');

        return response()->json([
            'data' => $this->formatPermission($permission),
        ]);
    }

    private function formatPermission(SantriPermission $permission): array
    {
        return [
            'id' => $permission->id,
            'permission_number' => $permission->permission_number,
            'kamar' => $permission->kamar,
            'start_date' => $permission->start_date?->format('Y-m-d'),
            'end_date' => $permission->end_date?->format('Y-m-d'),
            'reason' => $permission->reason,
            'notes' => $permission->notes,
            'approved_by' => $permission->approved_by,
            'created_by' => $permission->creator?->name,
            'is_active' => $permission->is_active,
            'created_at' => $permission->created_at?->toISOString(),
            'updated_at' => $permission->updated_at?->toISOString(),
        ];
    }
}
