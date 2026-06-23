<?php

namespace App\Console\Commands;

use App\Models\Schedule as WahaSchedule;
use App\Services\WahaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendRecurringWahaSchedules extends Command
{
    protected $signature = 'waha:send-recurring-schedules';

    protected $description = 'Send active recurring WhatsApp schedules for the current day and minute.';

    public function handle(WahaService $wahaService): int
    {
        $now = now('Asia/Jakarta');
        $schedules = WahaSchedule::query()
            ->where('is_active', true)
            ->where('day_of_week', $now->format('l'))
            ->whereTime('send_time', $now->format('H:i:00'))
            ->orderBy('id')
            ->get();

        if ($schedules->isEmpty()) {
            $this->info('Tidak ada jadwal WA yang cocok.');

            return self::SUCCESS;
        }

        foreach ($schedules as $index => $schedule) {
            $message = str_replace('[nama_guru]', $schedule->teacher_name, $schedule->message_content);
            $sent = $wahaService->sendMessage($schedule->target_id, $message);

            Log::info('WA recurring schedule processed.', [
                'schedule_id' => $schedule->id,
                'target_id' => $schedule->target_id,
                'sent' => $sent,
            ]);

            $this->line(($sent ? 'Terkirim' : 'Gagal').": {$schedule->teacher_name} -> {$schedule->target_id}");

            if ($index < $schedules->count() - 1) {
                sleep(3);
            }
        }

        return self::SUCCESS;
    }
}
