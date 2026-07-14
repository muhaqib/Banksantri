<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SantriPermission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $santri = Auth::user();
        
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

        return view('pages.santri.attendance.index', [
            'calendar' => $calendar,
            'month' => $month,
            'year' => $year,
            'monthName' => $startOfMonth->translatedFormat('F Y'),
            'hadirCount' => $hadirCount,
            'izinCount' => $izinCount,
            'ghoibCount' => $ghoibCount,
        ]);
    }
}
