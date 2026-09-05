<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\AttendanceSessionRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceSessionScanController extends Controller
{
    public function scan(Request $request, AttendanceSession $session)
    {
        $validated = $request->validate([
            'rfid_code' => ['required', 'string'],
        ]);

        if ($session->isCompleted()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi absensi ini sudah selesai.'
            ], 422);
        }

        $now = now('Asia/Jakarta');

        if ($now->isBefore($session->start_time)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi absensi belum dimulai.'
            ], 422);
        }

        $santri = User::activeSantri()
            ->where('rfid_code', $validated['rfid_code'])
            ->with('kamarSantri')
            ->first();

        if (!$santri) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kartu RFID tidak ditemukan.'
            ], 404);
        }

        $result = DB::transaction(function () use ($session, $santri, $now) {
            AttendanceSession::where('id', $session->id)->lockForUpdate()->first();

            $existing = AttendanceSessionRecord::where('attendance_session_id', $session->id)
                ->where('santri_id', $santri->id)
                ->first();

            if ($existing) {
                return [
                    'success' => false,
                    'message' => 'Santri sudah melakukan absensi pada sesi ini.',
                ];
            }

            $status = $now->isAfter($session->end_time) ? 'terlambat' : 'hadir';

            $record = AttendanceSessionRecord::create([
                'attendance_session_id' => $session->id,
                'santri_id' => $santri->id,
                'status' => $status,
                'scanned_at' => $now,
            ]);

            return [
                'success' => true,
                'record' => $record,
            ];
        });

        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message'],
            ], 422);
        }

        $stats = [
            'hadir' => $session->records()->where('status', 'hadir')->count(),
            'terlambat' => $session->records()->where('status', 'terlambat')->count(),
            'belum' => User::activeSantri()->whereNotNull('rfid_code')->count() - $session->records()->count(),
        ];

        return response()->json([
            'status' => 'success',
            'record' => $result['record'],
            'santri' => [
                'name' => $santri->name,
                'foto' => $santri->foto ? \Illuminate\Support\Facades\Storage::url($santri->foto) : null,
                'asal' => $santri->asal_sekolah ?? $santri->tempat_lahir ?? '-',
                'kamar' => ucwords(str_replace('_', ' ', $santri->kamarSantri?->kamar ?? $santri->kamar_terakhir ?? '-')),
                'kelas' => $santri->kelas ?? '-',
            ],
            'stats' => $stats,
        ]);
    }
}
