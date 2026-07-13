<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Models\SantriPermission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $santri = Auth::user();
        $monthPicker = $request->input('month_picker', now()->format('Y-m'));
        [$year, $month] = array_pad(explode('-', $monthPicker), 2, null);

        $year = (int) ($request->input('year') ?: $year ?: now()->year);
        $month = (int) ($request->input('month') ?: $month ?: now()->month);
        $selectedDate = Carbon::create($year, $month, 1);
        $startOfMonth = $selectedDate->copy()->startOfMonth()->toDateString();
        $endOfMonth = $selectedDate->copy()->endOfMonth()->toDateString();

        $permissions = SantriPermission::query()
            ->with('creator')
            ->where('santri_id', $santri->id)
            ->whereDate('start_date', '<=', $endOfMonth)
            ->whereDate('end_date', '>=', $startOfMonth)
            ->latest('start_date')
            ->paginate(10)
            ->withQueryString();

        return view('pages.santri.permissions.index', [
            'permissions' => $permissions,
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'monthPicker' => $selectedDate->format('Y-m'),
        ]);
    }
}
