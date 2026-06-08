<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        return view('pages.santri.profile');
    }

    public function changePin(Request $request)
    {
        $santri = Auth::user();

        $validated = $request->validate([
            'old_pin' => 'required|string|size:6',
            'new_pin' => 'required|string|size:6|min:6',
            'new_pin_confirmation' => 'required|string|size:6|same:new_pin',
        ]);

        if (! $santri->verifyPin($validated['old_pin'])) {
            return back()->withErrors(['old_pin' => 'PIN lama salah'])->withInput();
        }

        $santri->pin = Hash::make($validated['new_pin']);
        $santri->save();

        return redirect()->route('santri.profile')->with('success', 'PIN berhasil diubah');
    }

    /**
     * Update the user's email.
     */
    public function updateEmail(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
        ]);

        $user->email = $validated['email'];
        $user->save();

        return redirect()->back()->with('success', 'Email berhasil diperbarui');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'string', 'confirmed', Password::min(6)],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah'])->withInput();
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->back()->with('success', 'Password berhasil diperbarui');
    }
}
