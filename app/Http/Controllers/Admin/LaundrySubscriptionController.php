<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaundryCloth;
use App\Models\LaundrySubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
                ->paginate(10)
                ->withQueryString(),
            'santriList' => User::activeSantri()->orderBy('name')->get(['id', 'name', 'nis']),
            'clothes' => LaundryCloth::orderBy('sort_order')->orderBy('label')->get(),
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
                'quota_kg' => $validated['quota_kg'] ?? 12,
                'status' => 'active',
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return redirect()
            ->route('admin.laundry-subscriptions.index', ['month' => $validated['month'], 'year' => $validated['year']])
            ->with('success', 'Pendaftaran laundry bulanan berhasil disimpan.');
    }

    public function storeCloth(Request $request)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $baseKey = Str::slug($validated['label'], '_');
        $key = $baseKey;
        $suffix = 2;

        while (LaundryCloth::where('key', $key)->exists()) {
            $key = $baseKey.'_'.$suffix++;
        }

        LaundryCloth::create([
            'key' => $key,
            'label' => $validated['label'],
            'icon' => $validated['icon'] ?: 'checkroom',
            'sort_order' => $validated['sort_order'] ?? 100,
            'is_active' => true,
        ]);

        return back()->with('success', 'Rincian baju berhasil ditambahkan.');
    }

    public function updateCloth(Request $request, LaundryCloth $cloth)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $cloth->update([
            'label' => $validated['label'],
            'icon' => $validated['icon'] ?: 'checkroom',
            'sort_order' => $validated['sort_order'] ?? 100,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Rincian baju berhasil diperbarui.');
    }
}
