<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\KamarSantri;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route($this->routePrefix($request).'.attendance.rfid');
    }

    public function rfid(Request $request)
    {
        return $this->dailyPage($request, 'rfid');
    }

    public function manual(Request $request)
    {
        return $this->dailyPage($request, 'manual');
    }

    private function dailyPage(Request $request, string $mode)
    {
        $date = Carbon::parse($request->input('date', today()->toDateString()));

        $santriList = User::query()
            ->where('role', 'santri')
            ->with([
                'kamarSantri',
                'attendances' => fn ($query) => $query->whereDate('attendance_date', $date),
                'santriPermissions' => fn ($query) => $query->activeOn($date),
            ])
            ->when($request->filled('search'), fn ($query) => $query
                ->where(fn ($searchQuery) => $searchQuery
                    ->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('nis', 'like', '%'.$request->search.'%')))
            ->orderBy('name')
            ->get();

        $totalWithRfid = $santriList->filter(fn ($santri) => filled($santri->rfid_code))->count();
        $summary = [
            'total' => $totalWithRfid,
            'hadir' => $santriList->filter(fn ($santri) => $this->displayStatus($santri, $date) === 'hadir')->count(),
            'izin' => $santriList->filter(fn ($santri) => $this->displayStatus($santri, $date) === 'izin')->count(),
            'ghoib' => $santriList->filter(fn ($santri) => $this->displayStatus($santri, $date) === 'ghoib')->count(),
            'belum' => $santriList
                ->filter(fn ($santri) => filled($santri->rfid_code))
                ->filter(fn ($santri) => $this->displayStatus($santri, $date) === 'belum')
                ->count(),
        ];
        $recentAttendances = Attendance::query()
            ->whereDate('attendance_date', $date)
            ->where('status', 'hadir')
            ->with('santri.kamarSantri')
            ->latest('recorded_at')
            ->limit(5)
            ->get();

        return view('pages.attendance.index', [
            'activeRole' => $this->routePrefix($request),
            'routePrefix' => $this->routePrefix($request),
            'date' => $date,
            'santriList' => $santriList,
            'recentAttendances' => $recentAttendances,
            'summary' => $summary,
            'mode' => $mode,
        ]);
    }

    public function scan(Request $request, AttendanceService $attendanceService)
    {
        $validated = $request->validate([
            'rfid_code' => ['required', 'string'],
            'date' => ['required', 'date'],
        ]);

        $santri = User::query()
            ->where('role', 'santri')
            ->where('rfid_code', $validated['rfid_code'])
            ->with('kamarSantri')
            ->first();

        if (! $santri) {
            return response()->json(['message' => 'RFID tidak ditemukan.'], 404);
        }

        $attendance = $attendanceService->record($santri, $validated['date'], 'hadir', 'rfid', $request->user());

        return response()->json([
            'message' => $santri->name.' berhasil ditandai hadir.',
            'attendance' => $attendance,
            'santri' => ['id' => $santri->id, 'name' => $santri->name, 'nis' => $santri->nis],
        ]);
    }

    public function update(Request $request, User $santri, AttendanceService $attendanceService)
    {
        abort_unless($santri->role === 'santri', 404);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'status' => ['required', Rule::in(['hadir', 'ghoib', 'izin'])],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $attendanceService->record(
            $santri->load('kamarSantri'),
            $validated['date'],
            $validated['status'],
            'manual',
            $request->user(),
            $validated['notes'] ?? null
        );

        return back()->with('success', 'Status absensi '.$santri->name.' berhasil diperbarui.');
    }

    public function dashboard(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $kamar = $request->input('kamar');
        $start = Carbon::create($year, $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $query = Attendance::query()
            ->whereBetween('attendance_date', [$start, $end])
            ->when($kamar, fn ($query) => $query->where('kamar', $kamar));

        $totals = (clone $query)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $daily = (clone $query)
            ->selectRaw('attendance_date, status, COUNT(*) as total')
            ->groupBy('attendance_date', 'status')
            ->orderBy('attendance_date')
            ->get()
            ->groupBy(fn ($item) => Carbon::parse($item->attendance_date)->day);

        $byKamar = Attendance::query()
            ->whereBetween('attendance_date', [$start, $end])
            ->when($kamar, fn ($query) => $query->where('kamar', $kamar))
            ->selectRaw('kamar, status, COUNT(*) as total')
            ->groupBy('kamar', 'status')
            ->get()
            ->groupBy('kamar');

        $mostAbsent = User::query()
            ->where('role', 'santri')
            ->with('kamarSantri')
            ->withCount(['attendances as ghoib_count' => fn ($query) => $query
                ->where('status', 'ghoib')
                ->whereBetween('attendance_date', [$start, $end])
                ->when($kamar, fn ($attendanceQuery) => $attendanceQuery->where('kamar', $kamar))])
            ->orderByDesc('ghoib_count')
            ->limit(10)
            ->get();

        $totalRecords = $totals->sum();
        $attendanceRate = $totalRecords > 0 ? round((($totals['hadir'] ?? 0) / $totalRecords) * 100, 1) : 0;

        return view('pages.attendance.dashboard', [
            'activeRole' => $this->routePrefix($request),
            'routePrefix' => $this->routePrefix($request),
            'month' => $month,
            'year' => $year,
            'kamar' => $kamar,
            'kamarList' => KamarSantri::KAMAR_LIST,
            'totals' => $totals,
            'totalRecords' => $totalRecords,
            'attendanceRate' => $attendanceRate,
            'daily' => $daily,
            'daysInMonth' => $start->daysInMonth,
            'byKamar' => $byKamar,
            'mostAbsent' => $mostAbsent,
        ]);
    }

    public function monthly(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $kamar = $request->input('kamar');
        $search = $request->input('search');
        $start = Carbon::create($year, $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $monthlySantri = User::query()
            ->where('role', 'santri')
            ->with([
                'kamarSantri',
                'attendances' => fn ($query) => $query
                    ->whereBetween('attendance_date', [$start, $end])
                    ->select(['id', 'santri_id', 'attendance_date', 'status', 'method', 'notes']),
            ])
            ->when($search, fn ($query) => $query
                ->where(fn ($searchQuery) => $searchQuery
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('nis', 'like', '%'.$search.'%')))
            ->when($kamar, fn ($query) => $query
                ->whereHas('kamarSantri', fn ($roomQuery) => $roomQuery->where('kamar', $kamar)))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('pages.attendance.monthly', [
            'activeRole' => $this->routePrefix($request),
            'routePrefix' => $this->routePrefix($request),
            'month' => $month,
            'year' => $year,
            'kamar' => $kamar,
            'search' => $search,
            'kamarList' => KamarSantri::KAMAR_LIST,
            'daysInMonth' => $start->daysInMonth,
            'monthlySantri' => $monthlySantri,
            'monthStart' => $start,
        ]);
    }

    private function displayStatus(User $santri, Carbon $date): string
    {
        $attendance = $santri->attendances->first();
        if ($attendance) {
            return $attendance->status;
        }

        if ($santri->santriPermissions->isNotEmpty()) {
            return 'izin';
        }

        return $date->isBefore(today()) ? 'ghoib' : 'belum';
    }

    private function routePrefix(Request $request): string
    {
        return $request->user()->role === 'petugas' ? 'petugas' : 'admin';
    }
}
