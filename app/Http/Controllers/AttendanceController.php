<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\KamarSantri;
use App\Models\SantriPermission;
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
        $attendanceWindow = $this->attendanceWindow($date);

        $kamar = $request->input('kamar');
        $santriList = User::query()
            ->activeSantri()
            ->with([
                'kamarSantri',
                'attendances' => fn ($query) => $query->whereDate('attendance_date', $date),
                'santriPermissions' => fn ($query) => $query->activeOn($date),
            ])
            ->when($request->filled('search'), fn ($query) => $query
                ->where(fn ($searchQuery) => $searchQuery
                    ->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('nis', 'like', '%'.$request->search.'%')))
            ->when($kamar, fn ($query) => $query
                ->whereHas('kamarSantri', fn ($q) => $q->where('kamar', $kamar)))
            ->orderBy('name')
            ->get();

        $stats = $this->getAttendanceStats($date);

        return view('pages.attendance.index', [
            'activeRole' => $this->routePrefix($request),
            'routePrefix' => $this->routePrefix($request),
            'date' => $date,
            'kamar' => $kamar,
            'santriList' => $santriList,
            'recentAttendances' => $stats['recentAttendances'],
            'recentAttendancesFormatted' => $stats['recentAttendancesFormatted'],
            'summary' => $stats['summary'],
            'kamarProgress' => $stats['kamarProgress'],
            'mode' => $mode,
            'attendanceWindow' => $attendanceWindow,
        ]);
    }

    public function scan(Request $request, AttendanceService $attendanceService)
    {
        $validated = $request->validate([
            'rfid_code' => ['required', 'string'],
            'date' => ['required', 'date'],
        ]);
        $date = Carbon::parse($validated['date']);
        $attendanceWindow = $this->attendanceWindow($date);

        if (! $attendanceWindow['can_scan']) {
            return response()->json([
                'message' => 'Absensi RFID baru bisa dibaca mulai jam 21:00 sampai 23:59 WIB pada tanggal hari ini.',
            ], 422);
        }

        $santri = User::query()
            ->activeSantri()
            ->where('rfid_code', $validated['rfid_code'])
            ->with('kamarSantri')
            ->first();

        if (! $santri) {
            return response()->json(['message' => 'RFID tidak ditemukan.'], 404);
        }

        $attendance = $attendanceService->record($santri, $date, 'hadir', 'rfid', $request->user());

        $stats = $this->getAttendanceStats($date);

        return response()->json([
            'message' => $santri->name.' berhasil ditandai hadir.',
            'attendance' => $attendance,
            'santri' => ['id' => $santri->id, 'name' => $santri->name, 'nis' => $santri->nis],
            'summary' => $stats['summary'],
            'recentAttendances' => $stats['recentAttendancesFormatted'],
            'kamarProgress' => $stats['kamarProgress'],
        ]);
    }

    private function getAttendanceStats(Carbon $date): array
    {
        $allSantri = User::query()
            ->activeSantri()
            ->with([
                'kamarSantri',
                'attendances' => fn ($query) => $query->whereDate('attendance_date', $date),
                'santriPermissions' => fn ($query) => $query->activeOn($date),
            ])
            ->get();

        $totalWithRfid = $allSantri->filter(fn ($santri) => filled($santri->rfid_code))->count();
        $summary = [
            'total' => $totalWithRfid,
            'hadir' => $allSantri->filter(fn ($santri) => $this->displayStatus($santri, $date) === 'hadir')->count(),
            'izin' => $allSantri->filter(fn ($santri) => $this->displayStatus($santri, $date) === 'izin')->count(),
            'ghoib' => $allSantri->filter(fn ($santri) => $this->displayStatus($santri, $date) === 'ghoib')->count(),
            'belum' => $allSantri
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

        $recentAttendancesFormatted = $recentAttendances->map(function ($a) {
            return [
                'id' => $a->id,
                'santri_id' => $a->santri_id,
                'name' => $a->santri?->name ?? 'Santri',
                'nis' => $a->santri?->nis ?? '-',
                'kamar' => ucwords(str_replace('_', ' ', $a->santri?->kamarSantri?->kamar ?? $a->kamar ?? '-')),
                'foto_url' => $a->santri?->foto ? \Illuminate\Support\Facades\Storage::url($a->santri->foto) : null,
                'initial' => strtoupper(substr($a->santri?->name ?? '?', 0, 1)),
                'recorded_at_human' => $a->recorded_at?->diffForHumans() ?? 'Baru saja',
            ];
        })->values();

        $kamarProgress = collect(KamarSantri::KAMAR_LIST)->map(function ($kamarKey, $index) use ($allSantri, $date) {
            $santriInKamar = $allSantri->filter(fn ($s) => $s->kamarSantri?->kamar === $kamarKey);
            $total = $santriInKamar->count();
            $hadir = $santriInKamar->filter(fn ($s) => $this->displayStatus($s, $date) === 'hadir')->count();
            $izin = $santriInKamar->filter(fn ($s) => $this->displayStatus($s, $date) === 'izin')->count();
            $ghoib = $santriInKamar->filter(fn ($s) => $this->displayStatus($s, $date) === 'ghoib')->count();
            $belum = $santriInKamar->filter(fn ($s) => $this->displayStatus($s, $date) === 'belum')->count();
            $percentage = $total > 0 ? round(($hadir / $total) * 100) : 0;

            return [
                'key' => $kamarKey,
                'number' => $index + 1,
                'label' => 'Kamar ' . ($index + 1),
                'total' => $total,
                'hadir' => $hadir,
                'izin' => $izin,
                'ghoib' => $ghoib,
                'belum' => $belum,
                'percentage' => $percentage,
            ];
        })->values();

        return [
            'summary' => $summary,
            'recentAttendances' => $recentAttendances,
            'recentAttendancesFormatted' => $recentAttendancesFormatted,
            'kamarProgress' => $kamarProgress,
        ];
    }

    public function update(Request $request, User $santri, AttendanceService $attendanceService)
    {
        abort_unless($santri->isActiveSantri(), 404);

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

    public function bulkUpdate(Request $request, AttendanceService $attendanceService)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'attendances' => ['required', 'array'],
            'attendances.*.santri_id' => ['required', 'integer', 'exists:users,id'],
            'attendances.*.status' => ['nullable', Rule::in(['hadir', 'ghoib', 'izin'])],
            'attendances.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $santriList = User::query()
            ->activeSantri()
            ->whereIn('id', collect($validated['attendances'])->pluck('santri_id'))
            ->with('kamarSantri')
            ->get()
            ->keyBy('id');

        $updated = 0;
        foreach ($validated['attendances'] as $attendanceData) {
            if (blank($attendanceData['status'] ?? null)) {
                continue;
            }

            $santri = $santriList->get($attendanceData['santri_id']);
            if (! $santri) {
                continue;
            }

            $attendanceService->record(
                $santri,
                $validated['date'],
                $attendanceData['status'],
                'manual',
                $request->user(),
                $attendanceData['notes'] ?? null
            );
            $updated++;
        }

        return back()->with('success', "{$updated} perubahan absensi berhasil disimpan.");
    }

    public function dashboard(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        if ($year < 2026) {
            $year = 2026;
            $month = 7;
        } elseif ($year === 2026 && $month < 7) {
            $month = 7;
        }
        $kamar = $request->input('kamar');

        // Check date filter and handle month/year syncing
        $dateInput = $request->input('date');
        if ($dateInput) {
            $date = Carbon::parse($dateInput);
            if ($date->month !== $month || $date->year !== $year) {
                // If user selected month/year that doesn't match the input date, sync date to month/year
                $date = Carbon::create($year, $month, 1);
            }
        } else {
            $date = today();
            if ($date->month !== $month || $date->year !== $year) {
                $date = Carbon::create($year, $month, 1);
            }
        }

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
            ->activeSantri()
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

        // Retrieve active permissions for the selected date
        $activePermissions = SantriPermission::query()
            ->activeOn($date)
            ->with(['santri.kamarSantri'])
            ->when($kamar, fn ($q) => $q->whereHas('santri.kamarSantri', fn ($qk) => $qk->where('kamar', $kamar)))
            ->get();

        // Retrieve overdue permissions (end_date < now and returned_at is null)
        $overduePermissions = SantriPermission::query()
            ->whereNull('returned_at')
            ->where('end_date', '<', now())
            ->with(['santri.kamarSantri'])
            ->when($kamar, fn ($q) => $q->whereHas('santri.kamarSantri', fn ($qk) => $qk->where('kamar', $kamar)))
            ->get();

        $monthlyGhoib = Attendance::query()
            ->whereBetween('attendance_date', [$start, $end])
            ->where('status', 'ghoib')
            ->when($kamar, fn ($query) => $query->where('kamar', $kamar))
            ->with(['santri.kamarSantri'])
            ->selectRaw('santri_id, kamar, COUNT(*) as count')
            ->groupBy('santri_id', 'kamar')
            ->orderByDesc('count')
            ->get();

        $monthlyIzin = Attendance::query()
            ->whereBetween('attendance_date', [$start, $end])
            ->where('status', 'izin')
            ->when($kamar, fn ($query) => $query->where('kamar', $kamar))
            ->with(['santri.kamarSantri'])
            ->selectRaw('santri_id, kamar, COUNT(*) as count')
            ->groupBy('santri_id', 'kamar')
            ->orderByDesc('count')
            ->get();

        return view('pages.attendance.dashboard', [
            'activeRole' => $this->routePrefix($request),
            'routePrefix' => $this->routePrefix($request),
            'month' => $month,
            'year' => $year,
            'date' => $date,
            'kamar' => $kamar,
            'kamarList' => KamarSantri::KAMAR_LIST,
            'totals' => $totals,
            'totalRecords' => $totalRecords,
            'attendanceRate' => $attendanceRate,
            'daily' => $daily,
            'daysInMonth' => $start->daysInMonth,
            'byKamar' => $byKamar,
            'mostAbsent' => $mostAbsent,
            'activePermissions' => $activePermissions,
            'totalActivePermissions' => $activePermissions->count(),
            'overduePermissions' => $overduePermissions,
            'totalOverduePermissions' => $overduePermissions->count(),
            'monthlyGhoib' => $monthlyGhoib,
            'monthlyIzin' => $monthlyIzin,
            'monthName' => $start->translatedFormat('F Y'),
        ]);
    }

    public function monthly(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        if ($year < 2026) {
            $year = 2026;
            $month = 7;
        } elseif ($year === 2026 && $month < 7) {
            $month = 7;
        }
        $kamar = $request->input('kamar');
        $search = $request->input('search');
        $start = Carbon::create($year, $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $monthlySantri = User::query()
            ->activeSantri()
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
            ->paginate(10)
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

    public function detail(Request $request, User $santri)
    {
        abort_unless($santri->isActiveSantri(), 404);

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        if ($year < 2026) {
            $year = 2026;
            $month = 7;
        } elseif ($year === 2026 && $month < 7) {
            $month = 7;
        }

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;
        $firstDayOfWeek = $startOfMonth->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.

        // Fetch attendances for this month
        $attendances = Attendance::where('santri_id', $santri->id)
            ->whereBetween('attendance_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(fn($a) => $a->attendance_date->format('Y-m-d'));

        // Fetch permissions for this month
        $permissions = SantriPermission::where('santri_id', $santri->id)
            ->where(function($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                      ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                      ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                          $q->where('start_date', '<=', $startOfMonth)
                            ->where('end_date', '>=', $endOfMonth);
                      });
            })
            ->get();

        $calendar = [];

        // Pad start of the month with empty slots (Sunday-based calendar)
        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            $calendar[] = null;
        }

        // Fill days of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $carbonDate = Carbon::parse($dateStr);
            $attendance = $attendances->get($dateStr);

            $status = 'belum';
            $notes = null;

            if ($attendance) {
                $status = $attendance->status;
                $notes = $attendance->notes;
            } else {
                // Fallback checks for past dates
                $hasPermission = $permissions->some(fn($p) => $carbonDate->betweenIncluded($p->start_date->startOfDay(), $p->end_date->endOfDay()));
                if ($hasPermission && !$carbonDate->isAfter(today())) {
                    $status = 'izin';
                } elseif ($carbonDate->isBefore(today())) {
                    $status = 'ghoib';
                }
            }

            $calendar[] = [
                'day' => $day,
                'date' => $dateStr,
                'status' => $status,
                'notes' => $notes,
            ];
        }

        $hadirCount = collect($calendar)->filter(fn($c) => $c && $c['status'] === 'hadir')->count();
        $izinCount = collect($calendar)->filter(fn($c) => $c && $c['status'] === 'izin')->count();
        $ghoibCount = collect($calendar)->filter(fn($c) => $c && $c['status'] === 'ghoib')->count();

        // Shifting months
        $prevMonth = $month == 1 ? 12 : $month - 1;
        $prevYear = $month == 1 ? $year - 1 : $year;
        $nextMonth = $month == 12 ? 1 : $month + 1;
        $nextYear = $month == 12 ? $year + 1 : $year;

        return response()->json([
            'santri' => [
                'id' => $santri->id,
                'name' => $santri->name,
                'nis' => $santri->nis,
                'kamar' => ucwords(str_replace('_', ' ', $santri->kamarSantri?->kamar ?? '-')),
            ],
            'calendar' => $calendar,
            'month' => $month,
            'year' => $year,
            'monthName' => $startOfMonth->translatedFormat('F Y'),
            'hadirCount' => $hadirCount,
            'izinCount' => $izinCount,
            'ghoibCount' => $ghoibCount,
            'prevMonth' => $prevMonth,
            'prevYear' => $prevYear,
            'nextMonth' => $nextMonth,
            'nextYear' => $nextYear,
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

    private function attendanceWindow(Carbon $date): array
    {
        $now = now('Asia/Jakarta');
        $selectedDate = Carbon::parse($date->toDateString(), 'Asia/Jakarta');
        $startsAt = $selectedDate->copy()->setTime(21, 0);
        $endsAt = $selectedDate->copy()->endOfDay();
        $canScan = $selectedDate->isSameDay($now) && $now->betweenIncluded($startsAt, $endsAt);

        return [
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'can_scan' => $canScan,
        ];
    }

    private function routePrefix(Request $request): string
    {
        return $request->user()->role === 'petugas' ? 'petugas' : 'admin';
    }
}
