<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KamarSantri;
use App\Models\User;
use Illuminate\Http\Request;

class KamarSantriController extends Controller
{
    /**
     * Display a listing of all kamar with member count.
     */
    public function index()
    {
        $kamarData = [];
        
        foreach (KamarSantri::KAMAR_LIST as $kamar) {
            $count = KamarSantri::where('kamar', $kamar)->count();
            $kamarData[] = [
                'name' => $kamar,
                'label' => ucfirst(str_replace('_', ' ', $kamar)),
                'count' => $count,
            ];
        }
        
        return view('pages.admin.kamar.index', [
            'kamarData' => $kamarData,
            'activeRole' => 'admin',
        ]);
    }

    /**
     * Show the members of a specific kamar.
     */
    public function show($kamar)
    {
        if (!in_array($kamar, KamarSantri::KAMAR_LIST)) {
            abort(404);
        }
        
        $kamarSantris = KamarSantri::where('kamar', $kamar)
            ->with('user')
            ->paginate(15);
        
        return view('pages.admin.kamar.show', [
            'kamar' => $kamar,
            'kamarLabel' => ucfirst(str_replace('_', ' ', $kamar)),
            'kamarSantris' => $kamarSantris,
            'activeRole' => 'admin',
        ]);
    }

    /**
     * Store a new kamar assignment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kamar' => 'required|in:' . implode(',', KamarSantri::KAMAR_LIST),
            'user_id' => 'required|exists:users,id',
        ]);
        
        // Check if user already has a kamar
        $existing = KamarSantri::where('user_id', $request->user_id)->first();
        if ($existing) {
            return redirect()->back()->with('error', 'Santri sudah terdaftar di ' . ucfirst(str_replace('_', ' ', $existing->kamar)));
        }
        
        KamarSantri::create([
            'user_id' => $request->user_id,
            'kamar' => $request->kamar,
        ]);
        
        return redirect()->back()->with('success', 'Anggota berhasil ditambahkan ke ' . ucfirst(str_replace('_', ' ', $request->kamar)));
    }

    /**
     * Remove a member from a kamar.
     */
    public function destroy($id)
    {
        $kamarSantri = KamarSantri::findOrFail($id);
        $kamarSantri->delete();
        
        return redirect()->back()->with('success', 'Anggota berhasil dihapus dari kamar');
    }

    /**
     * Get all santri who don't have a kamar assigned (for AJAX).
     */
    public function getAvailableSantri()
    {
        $assignedUserIds = KamarSantri::pluck('user_id')->toArray();
        
        $santri = User::where('role', 'santri')
            ->whereNotIn('id', $assignedUserIds)
            ->select('id', 'name', 'nis')
            ->get();
        
        return response()->json(['success' => true, 'data' => $santri]);
    }
}
