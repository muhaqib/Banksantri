<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\SantriViolation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SecurityViolationController extends Controller
{
    public function index(Request $request)
    {
        $violations = SantriViolation::query()
            ->with(['santri.kamarSantri', 'creator'])
            ->when($request->filled('month'), fn ($query) => $query
                ->whereMonth('waktu', $request->month)
                ->whereYear('waktu', $request->input('year', now()->year)))
            ->when($request->filled('search'), fn ($query) => $query
                ->whereHas('santri', fn ($santriQuery) => $santriQuery
                    ->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('nis', 'like', '%'.$request->search.'%')))
            ->latest('waktu')
            ->paginate(10)
            ->withQueryString();

        return view('pages.petugas.security.index', [
            'violations' => $violations,
            'activeRole' => 'petugas',
        ]);
    }

    public function create()
    {
        return view('pages.petugas.security.form', [
            'violation' => null,
            'santriList' => $this->santriList(),
            'activeRole' => 'petugas',
        ]);
    }

    public function store(Request $request)
    {
        SantriViolation::create([
            ...$this->validateViolation($request),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('petugas.security.index')
            ->with('success', 'Pelanggaran santri berhasil dicatat dan poin prestasi dikurangi.');
    }

    public function edit(SantriViolation $security)
    {
        return view('pages.petugas.security.form', [
            'violation' => $security,
            'santriList' => $this->santriList(),
            'activeRole' => 'petugas',
        ]);
    }

    public function update(Request $request, SantriViolation $security)
    {
        $security->update([
            ...$this->validateViolation($request),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('petugas.security.index')
            ->with('success', 'Data pelanggaran santri berhasil diperbarui.');
    }

    public function destroy(SantriViolation $security)
    {
        $security->delete();

        return back()->with('success', 'Data pelanggaran santri berhasil dihapus.');
    }

    private function validateViolation(Request $request): array
    {
        return $request->validate([
            'santri_id' => ['required', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'santri')->where('santri_status', 'aktif'))],
            'jenis_pelanggaran' => ['required', 'string', 'max:255'],
            'waktu' => ['required', 'date'],
            'pengurangan_point' => ['required', 'integer', 'min:1', 'max:10'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function santriList()
    {
        return User::query()
            ->activeSantri()
            ->with('kamarSantri')
            ->orderBy('name')
            ->get();
    }
}
