<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PetugasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $petugasList = User::where('role', 'petugas')
            ->orderBy('name', 'asc')
            ->paginate(15);

        return view('pages.admin.petugas.index', [
            'petugasList' => $petugasList,
            'activeRole' => 'admin',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.admin.petugas.create', [
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
            'nip' => 'nullable|string|max:50|unique:users',
            'jabatan' => 'required|string|max:100',
            'password' => 'required|string|min:6',
            'foto' => 'nullable|image|max:2048',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        // Handle foto upload
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('fotos/petugas', 'public');
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nip' => $validated['nip'] ?? null,
            'jabatan' => $validated['jabatan'],
            'password' => Hash::make($validated['password']),
            'role' => 'petugas',
            'foto' => $fotoPath,
            'no_hp' => $validated['no_hp'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
        ]);

        return redirect()->route('admin.petugas.index')
            ->with('success', 'Data petugas berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $petugas)
    {
        if ($petugas->role !== 'petugas') {
            abort(403);
        }

        return view('pages.admin.petugas.edit', [
            'petugas' => $petugas,
            'activeRole' => 'admin',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $petugas)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $petugas->id,
            'nip' => 'nullable|string|max:50|unique:users,nip,' . $petugas->id,
            'jabatan' => 'required|string|max:100',
            'password' => 'nullable|string|min:6',
            'foto' => 'nullable|image|max:2048',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        // Build data array for update
        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nip' => $validated['nip'] ?? $petugas->nip,
            'jabatan' => $validated['jabatan'],
            'no_hp' => $validated['no_hp'] ?? $petugas->no_hp,
            'alamat' => $validated['alamat'] ?? $petugas->alamat,
            'updated_at' => now(),
        ];

        // Handle foto upload
        if ($request->hasFile('foto')) {
            if ($petugas->foto) {
                Storage::disk('public')->delete($petugas->foto);
            }
            $fotoPath = $request->file('foto')->store('fotos/petugas', 'public');
            $data['foto'] = $fotoPath;
        }

        // Update password only if provided
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        // Log the update for debugging
        \Log::info('Updating petugas', [
            'id' => $petugas->id,
            'name' => $validated['name'],
            'email' => $validated['email']
        ]);

        // Use direct database update to avoid issues with Eloquent model casts
        $updated = DB::table('users')
            ->where('id', $petugas->id)
            ->update($data);

        if (!$updated) {
            \Log::error('Failed to update petugas', ['id' => $petugas->id]);
            return back()->withErrors(['error' => 'Gagal mengupdate data petugas']);
        }

        return redirect()->route('admin.petugas.index')
            ->with('success', 'Data petugas berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $petugas)
    {
        if ($petugas->foto) {
            Storage::disk('public')->delete($petugas->foto);
        }

        // Log the delete for debugging
        \Log::info('Deleting petugas', [
            'id' => $petugas->id,
            'name' => $petugas->name
        ]);

        // Use direct database delete to avoid any Eloquent model issues
        $deleted = DB::table('users')
            ->where('id', $petugas->id)
            ->delete();

        if (!$deleted) {
            \Log::error('Failed to delete petugas', ['id' => $petugas->id]);
            return back()->withErrors(['error' => 'Gagal menghapus data petugas']);
        }

        return redirect()->route('admin.petugas.index')
            ->with('success', 'Data petugas berhasil dihapus!');
    }

    /**
     * Get petugas data for modal (AJAX).
     */
    public function getModalData(User $petugas)
    {
        if ($petugas->role !== 'petugas') {
            return response()->json(['error' => 'Invalid petugas'], 403);
        }

        return response()->json([
            'petugas' => $petugas,
            'foto_url' => $petugas->foto ? Storage::url($petugas->foto) : null
        ]);
    }
}