<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SantriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $santriList = User::where('role', 'santri')
            ->orderBy('name', 'asc')
            ->paginate(15);

        return view('pages.admin.santri.index', [
            'santriList' => $santriList,
            'activeRole' => 'admin',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.admin.santri.create', [
            'activeRole' => 'admin',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'nis' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:6',
            'pin' => 'required|string|size:6',
            'saldo' => 'nullable|numeric|min:0',
            'foto' => 'nullable|image|max:2048',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'nama_wali' => 'nullable|string|max:255',
            'no_hp_wali' => 'nullable|string|max:20',
            'asal_sekolah' => 'nullable|string|max:255',
            'kelas' => 'nullable|string|max:50',
        ]);

        // Handle foto upload
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('fotos/santri', 'public');
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nis' => $validated['nis'],
            'password' => Hash::make($validated['password']),
            'pin' => $validated['pin'],
            'saldo' => $validated['saldo'] ?? 0,
            'role' => 'santri',
            'foto' => $fotoPath,
            'no_hp' => $validated['no_hp'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'tempat_lahir' => $validated['tempat_lahir'] ?? null,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'nama_wali' => $validated['nama_wali'] ?? null,
            'no_hp_wali' => $validated['no_hp_wali'] ?? null,
            'asal_sekolah' => $validated['asal_sekolah'] ?? null,
            'kelas' => $validated['kelas'] ?? null,
        ]);

        return redirect()->route('admin.santri.index')
            ->with('success', 'Data santri berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $santri)
    {
        if ($santri->role !== 'santri') {
            abort(403);
        }

        return view('pages.admin.santri.edit', [
            'santri' => $santri,
            'activeRole' => 'admin',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $santri)
    {
        if ($santri->role !== 'santri') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $santri->id,
            'nis' => 'required|string|max:20|unique:users,nis,' . $santri->id,
            'password' => 'nullable|string|min:6',
            'pin' => 'nullable|string|size:6',
            'saldo' => 'nullable|numeric|min:0',
            'foto' => 'nullable|image|max:2048',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'nama_wali' => 'nullable|string|max:255',
            'no_hp_wali' => 'nullable|string|max:20',
            'asal_sekolah' => 'nullable|string|max:255',
            'kelas' => 'nullable|string|max:50',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nis' => $validated['nis'],
            'saldo' => $validated['saldo'] ?? $santri->saldo,
            'no_hp' => $validated['no_hp'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'tempat_lahir' => $validated['tempat_lahir'] ?? null,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'nama_wali' => $validated['nama_wali'] ?? null,
            'no_hp_wali' => $validated['no_hp_wali'] ?? null,
            'asal_sekolah' => $validated['asal_sekolah'] ?? null,
            'kelas' => $validated['kelas'] ?? null,
        ];

        // Handle foto upload
        if ($request->hasFile('foto')) {
            // Delete old foto
            if ($santri->foto) {
                Storage::disk('public')->delete($santri->foto);
            }
            $data['foto'] = $request->file('foto')->store('fotos/santri', 'public');
        }

        // Update password if provided
        if ($validated['password']) {
            $data['password'] = Hash::make($validated['password']);
        }

        // Update PIN if provided
        if ($validated['pin']) {
            $data['pin'] = $validated['pin'];
        }

        $santri->update($data);

        return redirect()->route('admin.santri.index')
            ->with('success', 'Data santri berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $santri)
    {
        if ($santri->role !== 'santri') {
            abort(403);
        }

        // Delete foto if exists
        if ($santri->foto) {
            Storage::disk('public')->delete($santri->foto);
        }

        $santri->delete();

        return redirect()->route('admin.santri.index')
            ->with('success', 'Data santri berhasil dihapus!');
    }

    /**
     * Get santri data for modal (AJAX).
     */
    public function getModalData(User $santri)
    {
        if ($santri->role !== 'santri') {
            return response()->json(['error' => 'Invalid santri'], 403);
        }

        return response()->json([
            'santri' => $santri,
            'foto_url' => $santri->foto ? Storage::url($santri->foto) : null
        ]);
    }
}
