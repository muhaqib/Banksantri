<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Models\SantriHealthRecord;
use Illuminate\Support\Facades\Auth;

class HealthController extends Controller
{
    public function index()
    {
        $santri = Auth::user();
        $latestRecord = SantriHealthRecord::query()
            ->where('santri_id', $santri->id)
            ->latest('checkup_date')
            ->first();

        $records = SantriHealthRecord::query()
            ->with('creator')
            ->where('santri_id', $santri->id)
            ->latest('checkup_date')
            ->paginate(10);

        return view('pages.santri.health.index', [
            'latestRecord' => $latestRecord,
            'records' => $records,
        ]);
    }
}
