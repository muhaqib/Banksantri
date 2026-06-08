<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Illuminate\Console\Command;

class FinalizeDailyAttendance extends Command
{
    protected $signature = 'attendance:finalize {date? : Tanggal yang difinalisasi (Y-m-d), default kemarin}';

    protected $description = 'Tandai santri yang tidak hadir sebagai izin atau ghoib';

    public function handle(AttendanceService $attendanceService): int
    {
        $date = $this->argument('date') ?? yesterday()->toDateString();
        $count = $attendanceService->finalizeDate($date);

        $this->info("Absensi {$date} selesai difinalisasi. {$count} data dibuat.");

        return self::SUCCESS;
    }
}
