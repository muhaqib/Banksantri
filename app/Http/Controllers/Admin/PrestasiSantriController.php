<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kitab;
use App\Models\PrestasiSantri;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PrestasiSantriController extends Controller
{
    private const PREDIKAT = [
        'Mumtaz' => ['skor' => 100, 'poin' => 10],
        'Jayyid Jiddan' => ['skor' => 90, 'poin' => 9],
        'Jayyid' => ['skor' => 75, 'poin' => 7],
        'Maqbul' => ['skor' => 60, 'poin' => 6],
    ];

    public function index(Request $request)
    {
        return view('pages.admin.prestasi.index', [
            'prestasiList' => PrestasiSantri::with(['santri', 'kitab', 'pembimbing'])
                ->whereNull('tarbiyah_monthly_exam_id')
                ->latest()
                ->paginate(10),
            ...$this->viewContext($request),
        ]);
    }

    public function create(Request $request)
    {
        return view('pages.admin.prestasi.create', [
            ...$this->formData(),
            ...$this->viewContext($request),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePrestasi($request);
        $kitab = Kitab::findOrFail($validated['kitab_id']);
        $predikat = self::PREDIKAT[$validated['nilai']];

        PrestasiSantri::create([
            'santri_id' => $validated['santri_id'],
            'kitab_id' => $kitab->id,
            'pembimbing_id' => $request->user()->id,
            'nama_kitab' => $kitab->nama,
            'kategori' => $kitab->kategori,
            'foto_kitab' => $kitab->gambar,
            'status' => $this->statusFromProgress($validated['progress']),
            'progress' => $validated['progress'],
            'nilai' => $validated['nilai'],
            'skor' => $predikat['skor'],
            'poin' => $predikat['poin'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'ustadz_pembimbing' => $request->user()->name,
            'catatan_ustadz' => $validated['catatan_ustadz'] ?? null,
        ]);

        return redirect()
            ->route($this->routePrefix($request).'.prestasi.index')
            ->with('success', 'Prestasi santri berhasil ditambahkan.');
    }

    public function edit(Request $request, PrestasiSantri $prestasi)
    {
        return view('pages.admin.prestasi.edit', [
            'prestasi' => $prestasi,
            ...$this->formData(),
            ...$this->viewContext($request),
        ]);
    }

    public function update(Request $request, PrestasiSantri $prestasi)
    {
        $validated = $this->validatePrestasi($request);
        $kitab = Kitab::findOrFail($validated['kitab_id']);
        $predikat = self::PREDIKAT[$validated['nilai']];

        $prestasi->update([
            'santri_id' => $validated['santri_id'],
            'kitab_id' => $kitab->id,
            'pembimbing_id' => $request->user()->id,
            'nama_kitab' => $kitab->nama,
            'kategori' => $kitab->kategori,
            'foto_kitab' => $kitab->gambar,
            'status' => $this->statusFromProgress($validated['progress']),
            'progress' => $validated['progress'],
            'nilai' => $validated['nilai'],
            'skor' => $predikat['skor'],
            'poin' => $predikat['poin'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'ustadz_pembimbing' => $request->user()->name,
            'catatan_ustadz' => $validated['catatan_ustadz'] ?? null,
        ]);

        return redirect()
            ->route($this->routePrefix($request).'.prestasi.index')
            ->with('success', 'Prestasi santri berhasil diperbarui.');
    }

    public function destroy(Request $request, PrestasiSantri $prestasi)
    {
        abort_unless($prestasi->santri?->isActiveSantri(), 422, 'Riwayat prestasi alumni tidak dapat diubah.');
        $prestasi->delete();

        return redirect()
            ->route($this->routePrefix($request).'.prestasi.index')
            ->with('success', 'Prestasi santri berhasil dihapus.');
    }

    public function getModalData(PrestasiSantri $prestasi)
    {
        $prestasi->load(['santri', 'kitab', 'pembimbing']);

        return response()->json([
            'prestasi' => [
                ...$prestasi->toArray(),
                'status_text' => $prestasi->status_text,
            ],
            'santri' => $prestasi->santri,
            'foto_url' => $prestasi->foto_kitab ? Storage::url($prestasi->foto_kitab) : null,
        ]);
    }

    private function validatePrestasi(Request $request): array
    {
        return $request->validate([
            'santri_id' => ['required', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'santri')->where('santri_status', 'aktif'))],
            'kitab_id' => ['required', 'exists:kitabs,id'],
            'tanggal_selesai' => ['required', 'date'],
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'nilai' => ['required', Rule::in(array_keys(self::PREDIKAT))],
            'catatan_ustadz' => ['nullable', 'string'],
        ]);
    }

    private function formData(): array
    {
        return [
            'santriList' => User::activeSantri()->orderBy('name')->get(),
            'kitabList' => Kitab::orderBy('nama')->get(),
            'predikatList' => self::PREDIKAT,
        ];
    }

    private function statusFromProgress(int $progress): string
    {
        return match (true) {
            $progress === 100 => 'telah_dihafalkan',
            $progress > 0 => 'sedang_dihafal',
            default => 'belum_dihafal',
        };
    }

    private function routePrefix(Request $request): string
    {
        return $request->user()->role === 'petugas' ? 'petugas' : 'admin';
    }

    private function viewContext(Request $request): array
    {
        $routePrefix = $this->routePrefix($request);

        return [
            'activeRole' => $routePrefix,
            'prestasiRoutePrefix' => $routePrefix,
        ];
    }
}
