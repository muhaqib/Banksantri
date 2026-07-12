<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\SantriPermission;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function record(User $santri, Carbon|string $date, string $status, string $method, ?User $recorder = null, ?string $notes = null): Attendance
    {
        $date = Carbon::parse($date)->toDateString();
        $kamar = $santri->kamarSantri?->kamar ?? 'tanpa_kamar';

        abort_unless($santri->isActiveSantri(), 422, 'Absensi hanya dapat dicatat untuk santri aktif.');

        $attendance = Attendance::where('santri_id', $santri->id)
            ->whereDate('attendance_date', $date)
            ->first();

        $data = [
            'kamar' => $kamar,
            'status' => $status,
            'method' => $method,
            'notes' => $notes,
            'recorded_by' => $recorder?->id,
            'recorded_at' => now(),
        ];

        if ($attendance) {
            $attendance->update($data);

            return $attendance;
        }

        return Attendance::create([
            'santri_id' => $santri->id,
            'attendance_date' => $date,
            ...$data,
        ]);
    }

    public function finalizeDate(Carbon|string $date): int
    {
        $date = Carbon::parse($date)->toDateString();
        $created = 0;

        User::query()
            ->activeSantri()
            ->whereHas('kamarSantri')
            ->with('kamarSantri')
            ->chunkById(100, function ($santriList) use ($date, &$created): void {
                foreach ($santriList as $santri) {
                    if (Attendance::where('santri_id', $santri->id)->whereDate('attendance_date', $date)->exists()) {
                        continue;
                    }

                    $hasPermission = SantriPermission::where('santri_id', $santri->id)->activeOn($date)->exists();
                    $this->record($santri, $date, $hasPermission ? 'izin' : 'ghoib', $hasPermission ? 'permission' : 'automatic');
                    $created++;
                }
            });

        return $created;
    }

    public function syncPermission(SantriPermission $permission, ?Carbon $oldStart = null, ?Carbon $oldEnd = null): void
    {
        $start = $oldStart && $oldStart->lt($permission->start_date) ? $oldStart : $permission->start_date;
        $end = $oldEnd && $oldEnd->gt($permission->end_date) ? $oldEnd : $permission->end_date;

        $this->reconcilePermissionDates($permission->santri, $start, $end);
    }

    public function removePermission(SantriPermission $permission): void
    {
        $this->reconcilePermissionDates($permission->santri, $permission->start_date, $permission->end_date);
    }

    private function reconcilePermissionDates(User $santri, Carbon $start, Carbon $end): void
    {
        DB::transaction(function () use ($santri, $start, $end): void {
            // Use start of day for period boundaries to ensure all dates are processed correctly
            $period = CarbonPeriod::create($start->copy()->startOfDay(), $end->copy()->startOfDay());
            foreach ($period as $date) {
                $dateString = $date->toDateString();
                $attendance = Attendance::where('santri_id', $santri->id)
                    ->whereDate('attendance_date', $dateString)
                    ->first();

                if ($attendance && in_array($attendance->method, ['rfid', 'manual'], true)) {
                    continue;
                }

                $hasPermission = SantriPermission::where('santri_id', $santri->id)->activeOn($dateString)->exists();
                $dateAtStart = $date->copy()->startOfDay();

                if ($hasPermission && ! $dateAtStart->isAfter(today())) {
                    $this->record($santri, $dateString, 'izin', 'permission');
                } elseif ($dateAtStart->isBefore(today())) {
                    $this->record($santri, $dateString, 'ghoib', 'automatic');
                } else {
                    $attendance?->delete();
                }
            }
        });
    }
}
