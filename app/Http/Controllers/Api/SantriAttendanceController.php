<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SantriPermission;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SantriAttendanceController extends Controller
{
    /**
     * Get attendance history and statistics for the authenticated santri.
     */
    public function index(Request $request)
    {
        $santri = $request->user();

        // Support both "YYYY-MM" format and separate query parameters
        $monthInput = $request->input('month');
        if ($monthInput && preg_match('/^(\d{4})-(\d{2})$/', $monthInput, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
        } else {
            $month = (int) $request->input('month', now()->month);
            $year = (int) $request->input('year', now()->year);
        }

        // Apply boundary constraint matching the web controller
        if ($year < 2026) {
            $year = 2026;
            $month = 7;
        } elseif ($year === 2026 && $month < 7) {
            $month = 7;
        }

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

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

        // Fill days of the month (flat list for API without null padding)
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

        $hadirCount = collect($calendar)->filter(fn($c) => $c['status'] === 'hadir')->count();
        $izinCount = collect($calendar)->filter(fn($c) => $c['status'] === 'izin')->count();
        $ghoibCount = collect($calendar)->filter(fn($c) => $c['status'] === 'ghoib')->count();

        return response()->json([
            'summary' => [
                'hadir' => $hadirCount,
                'izin' => $izinCount,
                'ghoib' => $ghoibCount,
            ],
            'data' => $calendar,
        ]);
    }
}
