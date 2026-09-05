<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\KamarSantri;
use App\Services\AttendanceSessionExcelService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceSessionController extends Controller
{
    public function index(Request $request)
    {
        $sessions = AttendanceSession::withCount('records')
            ->latest('start_time')
            ->paginate(10);
            
        return view('pages.admin.attendance-sessions.index', compact('sessions'));
    }

    public function create()
    {
        return view('pages.admin.attendance-sessions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);
        
        $start_time = Carbon::parse($validated['start_time'])->timezone('Asia/Jakarta');
        $end_time = Carbon::parse($validated['end_time'])->timezone('Asia/Jakarta');

        $session = AttendanceSession::create([
            'title' => $validated['title'],
            'start_time' => $start_time,
            'end_time' => $end_time,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.attendance-sessions.index')->with('success', 'Sesi absensi berhasil dibuat.');
    }

    public function dashboard(AttendanceSession $session)
    {
        if ($session->isCompleted()) {
            return redirect()->route('admin.attendance-sessions.show', $session)
                ->with('error', 'Sesi sudah selesai, tidak dapat membuka dashboard scan.');
        }

        return view('pages.admin.attendance-sessions.scan-dashboard', compact('session'));
    }

    public function show(Request $request, AttendanceSession $session)
    {
        $kamar = $request->input('kamar');
        $kelas = $request->input('kelas');
        $status = $request->input('status');
        $search = $request->input('search');

        $recordsQuery = $session->records()->with('santri.kamarSantri');

        if ($search) {
            $recordsQuery->whereHas('santri', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('rfid_code', 'like', "%{$search}%");
            });
        }
        
        if ($kamar) {
            $recordsQuery->whereHas('santri.kamarSantri', function($q) use ($kamar) {
                $q->where('kamar', $kamar);
            });
        }
        
        if ($kelas) {
            $recordsQuery->whereHas('santri', function($q) use ($kelas) {
                $q->where('kelas', $kelas);
            });
        }

        if ($status) {
            $recordsQuery->where('status', $status);
        }

        $records = $recordsQuery->latest('scanned_at')->paginate(20)->withQueryString();
        
        $kamarList = KamarSantri::KAMAR_LIST;

        return view('pages.admin.attendance-sessions.show', compact('session', 'records', 'kamarList'));
    }

    public function finish(Request $request, AttendanceSession $session)
    {
        if (!$session->isCompleted()) {
            $session->update([
                'completed_at' => now('Asia/Jakarta')
            ]);
        }
        
        return redirect()->route('admin.attendance-sessions.show', $session)->with('success', 'Sesi absensi telah diselesaikan.');
    }

    public function export(AttendanceSession $session, AttendanceSessionExcelService $excelService)
    {
        return $excelService->export($session);
    }
}
