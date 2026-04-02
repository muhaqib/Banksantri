<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        return view('pages.santri.profile');
    }

    public function changePin(Request $request)
    {
        $validated = $request->validate([
            'old_pin' => 'required|string|size:6',
            'new_pin' => 'required|string|size:6|min:6',
            'new_pin_confirmation' => 'required|string|size:6|same:new_pin'
        ]);

        // TODO: Verify old PIN
        // TODO: Update PIN

        return redirect()->route('santri.profile')->with('success', 'PIN berhasil diubah');
    }
}
