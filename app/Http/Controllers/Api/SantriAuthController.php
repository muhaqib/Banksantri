<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SantriAuthController extends Controller
{
    /**
     * Login santri with NIS and PIN.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'nis' => 'required|string',
            'pin' => 'required|string|size:6',
        ]);

        $user = User::where('role', 'santri')
            ->where('nis', $validated['nis'])
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'nis' => ['NIS atau PIN salah.'],
            ]);
        }

        // Handle both plain text and hashed PINs
        if ($user->pin === $validated['pin']) {
            // It's a plaintext match, update to hash
            $user->pin = Hash::make($validated['pin']);
            $user->save();
        } elseif (!Hash::check($validated['pin'], $user->pin)) {
            throw ValidationException::withMessages([
                'nis' => ['NIS atau PIN salah.'],
            ]);
        }

        // Revoke existing tokens to prevent accumulation
        $user->tokens()->delete();

        // Create Sanctum token
        $token = $user->createToken('santri-mobile', ['santri'])->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->formatUser($request->user()),
        ]);
    }

    /**
     * Logout and revoke token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }

    /**
     * Format user data for API response.
     */
    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'nis' => $user->nis,
            'role' => $user->role,
            'saldo' => (int) $user->saldo,
            'foto' => $user->foto ? asset('storage/' . $user->foto) : null,
            'no_hp' => $user->no_hp,
            'alamat' => $user->alamat,
            'tempat_lahir' => $user->tempat_lahir,
            'tanggal_lahir' => $user->tanggal_lahir,
            'asal_sekolah' => $user->asal_sekolah,
            'kelas' => $user->kelas,
        ];
    }
}
