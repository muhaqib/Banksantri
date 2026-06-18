<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaundrySubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LaundrySubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        return view('pages.admin.laundry-subscriptions.index', [
            'activeRole' => 'admin',
            'month' => $month,
            'year' => $year,
            'subscriptions' => LaundrySubscription::with(['santri', 'creator'])
                ->where('month', $month)
                ->where('year', $year)
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'santriList' => User::activeSantri()->orderBy('name')->get(['id', 'name', 'nis']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'santri_id' => ['required', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'santri')->where('santri_status', 'aktif'))],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2020,2100'],
            'monthly_fee' => ['required', 'integer', 'min:0'],
            'quota_kg' => ['nullable', 'numeric', 'min:1', 'max:99'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        LaundrySubscription::updateOrCreate(
            [
                'santri_id' => $validated['santri_id'],
                'month' => $validated['month'],
                'year' => $validated['year'],
            ],
            [
                'created_by' => $request->user()->id,
                'monthly_fee' => $validated['monthly_fee'],
                'quota_kg' => $validated['quota_kg'] ?? 20,
                'status' => 'active',
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return redirect()
            ->route('admin.laundry-subscriptions.index', ['month' => $validated['month'], 'year' => $validated['year']])
            ->with('success', 'Pendaftaran laundry bulanan berhasil disimpan.');
    }
}
