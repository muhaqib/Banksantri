<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\SantriHealthRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HealthRecordController extends Controller
{
    public function index(Request $request)
    {
        $records = SantriHealthRecord::query()
            ->with(['santri.kamarSantri', 'creator'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('month'), fn ($query) => $query
                ->whereMonth('checkup_date', $request->month)
                ->whereYear('checkup_date', $request->input('year', now()->year)))
            ->when($request->filled('search'), fn ($query) => $query
                ->whereHas('santri', fn ($santriQuery) => $santriQuery
                    ->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('nis', 'like', '%'.$request->search.'%')))
            ->latest('checkup_date')
            ->paginate(15)
            ->withQueryString();

        return view('pages.petugas.health.index', [
            'records' => $records,
            'statuses' => SantriHealthRecord::STATUSES,
            'activeRole' => 'petugas',
        ]);
    }

    public function create()
    {
        return view('pages.petugas.health.form', [
            'record' => null,
            'santriList' => $this->santriList(),
            'statuses' => SantriHealthRecord::STATUSES,
            'activeRole' => 'petugas',
        ]);
    }

    public function store(Request $request)
    {
        SantriHealthRecord::create([
            ...$this->validateRecord($request),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('petugas.health.index')
            ->with('success', 'Data kesehatan santri berhasil ditambahkan.');
    }

    public function edit(SantriHealthRecord $health)
    {
        return view('pages.petugas.health.form', [
            'record' => $health,
            'santriList' => $this->santriList(),
            'statuses' => SantriHealthRecord::STATUSES,
            'activeRole' => 'petugas',
        ]);
    }

    public function update(Request $request, SantriHealthRecord $health)
    {
        $health->update([
            ...$this->validateRecord($request),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('petugas.health.index')
            ->with('success', 'Data kesehatan santri berhasil diperbarui.');
    }

    public function destroy(SantriHealthRecord $health)
    {
        $health->delete();

        return back()->with('success', 'Data kesehatan santri berhasil dihapus.');
    }

    private function validateRecord(Request $request): array
    {
        return $request->validate([
            'santri_id' => ['required', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'santri')->where('santri_status', 'aktif'))],
            'checkup_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys(SantriHealthRecord::STATUSES))],
            'location' => ['nullable', 'string', 'max:255'],
            'weight_kg' => ['nullable', 'numeric', 'min:1', 'max:300'],
            'height_cm' => ['nullable', 'numeric', 'min:30', 'max:250'],
            'blood_pressure' => ['nullable', 'string', 'max:20'],
            'temperature_c' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'complaint' => ['nullable', 'string', 'max:1000'],
            'treatment' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function santriList()
    {
        return User::query()
            ->activeSantri()
            ->with(['kamarSantri', 'latestHealthRecord'])
            ->orderBy('name')
            ->get();
    }
}
