<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrestasiSantri;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PrestasiSantriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $prestasiList = PrestasiSantri::with('santri')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $santriList = User::where('role', 'santri')
            ->orderBy('name', 'asc')
            ->get();

        return view('pages.admin.prestasi.index', [
            'prestasiList' => $prestasiList,
            'santriList' => $santriList,
            'activeRole' => 'admin',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $santriList = User::where('role', 'santri')
            ->orderBy('name', 'asc')
            ->get();

        return view('pages.admin.prestasi.create', [
            'santriList' => $santriList,
            'activeRole' => 'admin',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'santri_id' => 'required|exists:users,id',
            'nama_kitab' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:belum_dihafal,sedang_dihafal,telah_dihafalkan',
            'nilai' => 'nullable|string|max:50',
            'skor' => 'nullable|integer|min:0|max:100',
            'tanggal_selesai' => 'nullable|date',
            'bulan_tahun_selesai' => 'nullable|string|max:100',
            'ustadz_pembimbing' => 'nullable|string|max:255',
            'foto_kitab' => 'nullable|image|max:2048',
            'catatan_ustadz' => 'nullable|string',
            'poin' => 'nullable|integer|min:0',
            'tags' => 'nullable|string',
        ]);

        // Handle foto_kitab upload
        $fotoPath = null;
        if ($request->hasFile('foto_kitab')) {
            $fotoPath = $request->file('foto_kitab')->store('fotos/kitab', 'public');
        }

        PrestasiSantri::create([
            'santri_id' => $validated['santri_id'],
            'nama_kitab' => $validated['nama_kitab'],
            'kategori' => $validated['kategori'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
            'status' => $validated['status'],
            'nilai' => $validated['nilai'] ?? null,
            'skor' => $validated['skor'] ?? null,
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
            'bulan_tahun_selesai' => $validated['bulan_tahun_selesai'] ?? null,
            'ustadz_pembimbing' => $validated['ustadz_pembimbing'] ?? null,
            'foto_kitab' => $fotoPath,
            'catatan_ustadz' => $validated['catatan_ustadz'] ?? null,
            'poin' => $validated['poin'] ?? 0,
            'tags' => $validated['tags'] ?? null,
        ]);

        return redirect()->route('admin.prestasi.index')
            ->with('success', 'Prestasi santri berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PrestasiSantri $prestasi)
    {
        $santriList = User::where('role', 'santri')
            ->orderBy('name', 'asc')
            ->get();

        return view('pages.admin.prestasi.edit', [
            'prestasi' => $prestasi,
            'santriList' => $santriList,
            'activeRole' => 'admin',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PrestasiSantri $prestasi)
    {
        $validated = $request->validate([
            'santri_id' => 'required|exists:users,id',
            'nama_kitab' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:belum_dihafal,sedang_dihafal,telah_dihafalkan',
            'nilai' => 'nullable|string|max:50',
            'skor' => 'nullable|integer|min:0|max:100',
            'tanggal_selesai' => 'nullable|date',
            'bulan_tahun_selesai' => 'nullable|string|max:100',
            'ustadz_pembimbing' => 'nullable|string|max:255',
            'foto_kitab' => 'nullable|image|max:2048',
            'catatan_ustadz' => 'nullable|string',
            'poin' => 'nullable|integer|min:0',
            'tags' => 'nullable|string',
        ]);

        // Build data array for update
        $data = [
            'santri_id' => $validated['santri_id'],
            'nama_kitab' => $validated['nama_kitab'],
            'kategori' => $validated['kategori'] ?? $prestasi->kategori,
            'keterangan' => $validated['keterangan'] ?? $prestasi->keterangan,
            'status' => $validated['status'],
            'nilai' => $validated['nilai'] ?? $prestasi->nilai,
            'skor' => $validated['skor'] ?? $prestasi->skor,
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? $prestasi->tanggal_selesai,
            'bulan_tahun_selesai' => $validated['bulan_tahun_selesai'] ?? $prestasi->bulan_tahun_selesai,
            'ustadz_pembimbing' => $validated['ustadz_pembimbing'] ?? $prestasi->ustadz_pembimbing,
            'catatan_ustadz' => $validated['catatan_ustadz'] ?? $prestasi->catatan_ustadz,
            'poin' => $validated['poin'] ?? $prestasi->poin,
            'tags' => $validated['tags'] ?? $prestasi->tags,
            'updated_at' => now(),
        ];

        // Handle foto_kitab upload
        if ($request->hasFile('foto_kitab')) {
            // Delete old foto
            if ($prestasi->foto_kitab) {
                Storage::disk('public')->delete($prestasi->foto_kitab);
            }
            $data['foto_kitab'] = $request->file('foto_kitab')->store('fotos/kitab', 'public');
        }

        DB::table('prestasi_santris')
            ->where('id', $prestasi->id)
            ->update($data);

        return redirect()->route('admin.prestasi.index')
            ->with('success', 'Prestasi santri berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PrestasiSantri $prestasi)
    {
        // Delete foto if exists
        if ($prestasi->foto_kitab) {
            Storage::disk('public')->delete($prestasi->foto_kitab);
        }

        $prestasi->delete();

        return redirect()->route('admin.prestasi.index')
            ->with('success', 'Prestasi santri berhasil dihapus!');
    }

    /**
     * Get prestasi data for modal (AJAX).
     */
    public function getModalData(PrestasiSantri $prestasi)
    {
        return response()->json([
            'prestasi' => $prestasi,
            'santri' => $prestasi->santri,
            'foto_url' => $prestasi->foto_kitab ? Storage::url($prestasi->foto_kitab) : null
        ]);
    }
}
