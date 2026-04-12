<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SantriProfileController extends Controller
{
    /**
     * Get authenticated user's profile.
     */
    public function index(Request $request)
    {
        $santri = $request->user();

        return response()->json([
            'user' => [
                'id' => $santri->id,
                'name' => $santri->name,
                'email' => $santri->email,
                'nis' => $santri->nis,
                'role' => $santri->role,
                'saldo' => (int) $santri->saldo,
                'foto' => $santri->foto ? asset('storage/' . $santri->foto) : null,
                'no_hp' => $santri->no_hp,
                'alamat' => $santri->alamat,
                'tempat_lahir' => $santri->tempat_lahir,
                'tanggal_lahir' => $santri->tanggal_lahir,
                'nama_wali' => $santri->nama_wali,
                'no_hp_wali' => $santri->no_hp_wali,
                'asal_sekolah' => $santri->asal_sekolah,
                'kelas' => $santri->kelas,
                'created_at' => $santri->created_at->toISOString(),
                'updated_at' => $santri->updated_at->toISOString(),
            ],
        ]);
    }

    /**
     * Change PIN.
     */
    public function changePin(Request $request)
    {
        $santri = $request->user();

        $validated = $request->validate([
            'old_pin' => 'required|string|size:6',
            'new_pin' => 'required|string|size:6|min:6',
            'new_pin_confirmation' => 'required|string|size:6|same:new_pin',
        ]);

        if (!Hash::check($validated['old_pin'], $santri->pin)) {
            return response()->json([
                'message' => 'PIN lama salah',
                'errors' => ['old_pin' => ['PIN lama yang Anda masukkan salah']],
            ], 422);
        }

        $santri->pin = Hash::make($validated['new_pin']);
        $santri->save();

        return response()->json([
            'message' => 'PIN berhasil diubah',
        ]);
    }

    /**
     * Update email.
     */
    public function updateEmail(Request $request)
    {
        $santri = $request->user();

        $validated = $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email,' . $santri->id,
        ]);

        $santri->email = $validated['email'];
        $santri->save();

        return response()->json([
            'message' => 'Email berhasil diperbarui',
            'email' => $santri->email,
        ]);
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request)
    {
        $santri = $request->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'string', 'confirmed', Password::min(6)],
        ]);

        if (!Hash::check($validated['current_password'], $santri->password)) {
            return response()->json([
                'message' => 'Password saat ini salah',
                'errors' => ['current_password' => ['Password saat ini salah']],
            ], 422);
        }

        $santri->password = Hash::make($validated['password']);
        $santri->save();

        return response()->json([
            'message' => 'Password berhasil diperbarui',
        ]);
    }
}
