<?php

namespace App\Http\Controllers;

use App\Models\KamarSantri;
use App\Models\SantriPermission;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SantriPermissionController extends Controller
{
    public function index(Request $request)
    {
        $permissions = SantriPermission::query()
            ->with(['santri.kamarSantri', 'creator'])
            ->when($request->filled('kamar'), fn ($query) => $query->where('kamar', $request->kamar))
            ->when($request->filled('month'), fn ($query) => $query
                ->whereMonth('start_date', $request->month)
                ->whereYear('start_date', $request->input('year', now()->year)))
            ->when($request->filled('search'), fn ($query) => $query
                ->whereHas('santri', fn ($santriQuery) => $santriQuery
                    ->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('nis', 'like', '%'.$request->search.'%')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.permissions.index', [
            'permissions' => $permissions,
            'activeRole' => $this->routePrefix($request),
            'routePrefix' => $this->routePrefix($request),
            'kamarList' => KamarSantri::KAMAR_LIST,
        ]);
    }

    public function create(Request $request)
    {
        return view('pages.permissions.form', [
            'permission' => null,
            'santriList' => $this->santriList(),
            'activeRole' => $this->routePrefix($request),
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    public function store(Request $request, AttendanceService $attendanceService)
    {
        $validated = $this->validatePermission($request);
        $santri = User::with('kamarSantri')->findOrFail($validated['santri_id']);
        abort_unless($santri->kamarSantri, 422, 'Santri belum memiliki kamar.');

        $permission = SantriPermission::create([
            ...$validated,
            'permission_number' => 'IZN-'.now()->format('Ymd-His').'-'.random_int(100, 999),
            'kamar' => $santri->kamarSantri->kamar,
            'created_by' => $request->user()->id,
        ]);

        $attendanceService->syncPermission($permission->load('santri.kamarSantri'));

        return redirect()->route($this->routePrefix($request).'.permissions.print', $permission)
            ->with('success', 'Perizinan berhasil dibuat.');
    }

    public function edit(Request $request, SantriPermission $permission)
    {
        return view('pages.permissions.form', [
            'permission' => $permission,
            'santriList' => $this->santriList(),
            'activeRole' => $this->routePrefix($request),
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    public function update(Request $request, SantriPermission $permission, AttendanceService $attendanceService)
    {
        $validated = $this->validatePermission($request);
        $permission->load('santri.kamarSantri');
        $oldPermission = clone $permission;
        $santri = User::with('kamarSantri')->findOrFail($validated['santri_id']);
        abort_unless($santri->kamarSantri, 422, 'Santri belum memiliki kamar.');

        $permission->update([
            ...$validated,
            'kamar' => $santri->kamarSantri->kamar,
            'created_by' => $request->user()->id,
        ]);

        $attendanceService->removePermission($oldPermission);
        $attendanceService->syncPermission($permission->fresh()->load('santri.kamarSantri'));

        return redirect()->route($this->routePrefix($request).'.permissions.index')
            ->with('success', 'Perizinan berhasil diperbarui.');
    }

    public function destroy(Request $request, SantriPermission $permission, AttendanceService $attendanceService)
    {
        $permission->load('santri.kamarSantri');
        $snapshot = clone $permission;
        $permission->delete();
        $attendanceService->removePermission($snapshot);

        return back()->with('success', 'Perizinan berhasil dihapus dan absensi terkait dihitung ulang.');
    }

    public function print(Request $request, SantriPermission $permission)
    {
        return view('pages.permissions.print', [
            'permission' => $permission->load(['santri.kamarSantri', 'creator']),
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    private function validatePermission(Request $request): array
    {
        return $request->validate([
            'santri_id' => ['required', Rule::exists('users', 'id')->where('role', 'santri')],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function santriList()
    {
        return User::query()
            ->where('role', 'santri')
            ->whereHas('kamarSantri')
            ->with('kamarSantri')
            ->orderBy('name')
            ->get();
    }

    private function routePrefix(Request $request): string
    {
        return $request->user()->role === 'petugas' ? 'petugas' : 'admin';
    }
}
