<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KamarSantri;
use App\Models\User;
use App\Services\SantriExcelService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SantriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $routePrefix = $this->routePrefix($request);
        $activeRole = $this->activeRole($request);
        $status = in_array($request->query('status'), ['aktif', 'alumni'], true)
            ? $request->query('status')
            : 'aktif';

        $query = User::where('role', 'santri')->with('kamarSantri');
        $query->where('santri_status', $status);

        // Search by name or NIS if provided
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('nis', 'like', '%'.$search.'%');
            });
        }

        $santriList = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();

        return view('pages.admin.santri.index', [
            'santriList' => $santriList,
            'activeCount' => User::activeSantri()->count(),
            'alumniCount' => User::santri()->where('santri_status', 'alumni')->count(),
            'currentStatus' => $status,
            'activeRole' => $activeRole,
            'routePrefix' => $routePrefix,
        ]);
    }

    /**
     * Get santri data for autocomplete (AJAX).
     */
    public function search(Request $request)
    {
        $query = User::activeSantri();

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('nis', 'like', '%'.$search.'%');
            });
        }

        $santri = $query->orderBy('name', 'asc')->limit(10)->get();

        return response()->json([
            'success' => true,
            'data' => $santri->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'nis' => $s->nis,
                    'email' => $s->email,
                    'saldo' => $s->saldo,
                    'foto_url' => $s->foto ? asset('storage/'.$s->foto) : null,
                ];
            }),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.admin.santri.create', [
            'activeRole' => $this->activeRole(request()),
            'routePrefix' => $this->routePrefix(request()),
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
            'rfid_code' => 'nullable|string|max:100|unique:users',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'nama_wali' => 'nullable|string|max:255',
            'no_hp_wali' => 'nullable|string|max:20',
            'asal_sekolah' => 'nullable|string|max:255',
            'kelas' => 'nullable|string|max:50',
            'kamar' => 'nullable|in:'.implode(',', KamarSantri::KAMAR_LIST),
        ]);

        // Handle foto upload
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $this->resizeAndSaveImage($request->file('foto'), 'fotos/santri');
        }

        $santri = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nis' => $validated['nis'],
            'password' => Hash::make($validated['password']),
            'pin' => Hash::make($validated['pin']),
            'saldo' => $validated['saldo'] ?? 0,
            'role' => 'santri',
            'foto' => $fotoPath,
            'rfid_code' => $validated['rfid_code'] ?? null,
            'tempat_lahir' => $validated['tempat_lahir'] ?? null,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'nama_wali' => $validated['nama_wali'] ?? null,
            'no_hp_wali' => $validated['no_hp_wali'] ?? null,
            'asal_sekolah' => $validated['asal_sekolah'] ?? null,
            'kelas' => $validated['kelas'] ?? null,
        ]);
        $santri->syncRoles(['santri']);

        if (! empty($validated['kamar'] ?? null)) {
            KamarSantri::create([
                'user_id' => $santri->id,
                'kamar' => $validated['kamar'],
            ]);
        }

        return redirect()->route($this->routePrefix($request).'.index')
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
            'activeRole' => $this->activeRole(request()),
            'routePrefix' => $this->routePrefix(request()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $santri)
    {
        if ($santri->role !== 'santri') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$santri->id,
            'nis' => 'required|string|max:20|unique:users,nis,'.$santri->id,
            'password' => 'nullable|string|min:6',
            'pin' => 'nullable|string|size:6',
            'saldo' => 'nullable|numeric|min:0',
            'foto' => 'nullable|image|max:2048',
            'rfid_code' => 'nullable|string|max:100|unique:users,rfid_code,'.$santri->id,
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'nama_wali' => 'nullable|string|max:255',
            'no_hp_wali' => 'nullable|string|max:20',
            'asal_sekolah' => 'nullable|string|max:255',
            'kelas' => 'nullable|string|max:50',
        ]);

        // Build data array for update
        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nis' => $validated['nis'],
            'saldo' => $validated['saldo'] ?? $santri->saldo,
            'rfid_code' => $validated['rfid_code'] ?? $santri->rfid_code,
            'alamat' => $validated['alamat'] ?? $santri->alamat,
            'tempat_lahir' => $validated['tempat_lahir'] ?? $santri->tempat_lahir,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? $santri->tanggal_lahir,
            'nama_wali' => $validated['nama_wali'] ?? $santri->nama_wali,
            'no_hp_wali' => $validated['no_hp_wali'] ?? $santri->no_hp_wali,
            'asal_sekolah' => $validated['asal_sekolah'] ?? $santri->asal_sekolah,
            'kelas' => $validated['kelas'] ?? $santri->kelas,
            'updated_at' => now(),
        ];

        // Handle foto upload
        if ($request->hasFile('foto')) {
            // Delete old foto
            if ($santri->foto) {
                Storage::disk('public')->delete($santri->foto);
            }
            $data['foto'] = $this->resizeAndSaveImage($request->file('foto'), 'fotos/santri');
        }

        // Update password if provided
        if (! empty($validated['password'] ?? null)) {
            $data['password'] = Hash::make($validated['password']);
        }

        if (! empty($validated['pin'] ?? null)) {
            $data['pin'] = Hash::make($validated['pin']);
        }

        // Use direct database update to avoid issues with Eloquent model casts
        DB::table('users')
            ->where('id', $santri->id)
            ->update($data);

        return redirect()->route($this->routePrefix($request).'.index')
            ->with('success', 'Data santri berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $santri)
    {
        if ($santri->role !== 'santri') {
            abort(404);
        }

        // Delete foto if exists
        if ($santri->foto) {
            Storage::disk('public')->delete($santri->foto);
        }

        // Use direct database delete to avoid any Eloquent model issues
        DB::table('users')
            ->where('id', $santri->id)
            ->delete();

        return redirect()->route($this->routePrefix(request()).'.index')
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

        $santri->load('kamarSantri');

        return response()->json([
            'santri' => $santri,
            'foto_url' => $santri->foto ? Storage::url($santri->foto) : null,
            'kamar_text' => $santri->kamarSantri?->kamar
                ? ucfirst(str_replace('_', ' ', $santri->kamarSantri->kamar))
                : '-',
        ]);
    }

    public function import(Request $request, SantriExcelService $excelService)
    {
        $validated = $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        $result = $excelService->import($validated['excel_file']);

        return back()
            ->with('success', "Import selesai: {$result['created']} dibuat, {$result['updated']} diperbarui, {$result['failed']} gagal.")
            ->with('import_errors', $result['errors']);
    }

    public function export(Request $request, SantriExcelService $excelService)
    {
        return $excelService->export($request->query('status'));
    }

    public function template(SantriExcelService $excelService)
    {
        return $excelService->template();
    }

    public function graduate(User $santri)
    {
        abort_unless($santri->role === 'santri', 404);

        DB::transaction(function () use ($santri): void {
            $kamar = $santri->kamarSantri?->kamar;
            $santri->forceFill([
                'santri_status' => 'alumni',
                'alumni_at' => now(),
                'kamar_terakhir' => $kamar ?: $santri->kamar_terakhir,
            ])->save();
            $santri->kamarSantri()->delete();
            if (Schema::hasTable('personal_access_tokens')) {
                $santri->tokens()->delete();
            }
        });

        return back()->with('success', "{$santri->name} berhasil dimigrasikan menjadi alumni.");
    }

    public function activate(User $santri)
    {
        abort_unless($santri->role === 'santri', 404);

        $santri->forceFill([
            'santri_status' => 'aktif',
            'alumni_at' => null,
        ])->save();

        return back()->with('success', "{$santri->name} berhasil diaktifkan kembali sebagai santri.");
    }

    /**
     * Resize and save uploaded image.
     *
     * @param  UploadedFile  $image
     * @param  string  $directory
     * @param  int  $maxWidth
     * @param  int  $quality
     * @return string|null
     */
    private function resizeAndSaveImage($image, $directory, $maxWidth = 800, $quality = 70)
    {
        // Get original dimensions
        $img = imagecreatefromstring(file_get_contents($image->getRealPath()));
        $originalWidth = imagesx($img);
        $originalHeight = imagesy($img);

        // Calculate new dimensions (maintain aspect ratio)
        if ($originalWidth > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = intval($originalHeight * ($maxWidth / $originalWidth));
        } else {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        }

        // Create resized image
        $resizedImg = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG
        imagealphablending($resizedImg, false);
        imagesavealpha($resizedImg, true);

        // Resize
        imagecopyresampled($resizedImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        // Generate filename
        $filename = time().'_'.uniqid().'.jpg';
        $fullPath = $directory.'/'.$filename;
        $storagePath = storage_path('app/public/'.$fullPath);

        // Ensure directory exists
        File::ensureDirectoryExists(dirname($storagePath));

        // Save as JPEG with specified quality
        imagejpeg($resizedImg, $storagePath, $quality);

        // Free memory
        imagedestroy($img);
        imagedestroy($resizedImg);

        return $fullPath;
    }

    private function activeRole(Request $request): string
    {
        return $request->routeIs('petugas.*') ? 'petugas' : 'admin';
    }

    private function routePrefix(Request $request): string
    {
        return $this->activeRole($request).'.santri';
    }
}
