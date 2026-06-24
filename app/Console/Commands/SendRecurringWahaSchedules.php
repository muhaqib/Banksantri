<?php

namespace App\Console\Commands;

use App\Models\Schedule as WahaSchedule;
use App\Models\WahaMessageLog;
use App\Services\WahaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendRecurringWahaSchedules extends Command
{
    private const AUTO_SIGNATURE = 'Pesan otomatis by: MawaSmart.';

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
            $message = $this->appendAutoSignature(str_replace('[nama_guru]', $schedule->teacher_name, $schedule->message_content));
            $result = $wahaService->sendMessageResult($schedule->target_id, $message);

            WahaMessageLog::create([
                'schedule_id' => $schedule->id,
                'teacher_name' => $schedule->teacher_name,
                'target_id' => $schedule->target_id,
                'session' => $result['session'],
                'message_content' => $message,
                'status' => $result['success'] ? WahaMessageLog::STATUS_SUCCESS : WahaMessageLog::STATUS_FAILED,
                'http_status' => $result['http_status'],
                'response_body' => $result['response_body'],
                'error_message' => $result['error_message'],
                'sent_at' => now('Asia/Jakarta'),
            ]);

            Log::info('WA recurring schedule processed.', [
                'schedule_id' => $schedule->id,
                'target_id' => $schedule->target_id,
                'sent' => $result['success'],
                'session' => $result['session'],
                'http_status' => $result['http_status'],
            ]);

            $this->line(($result['success'] ? 'Terkirim' : 'Gagal').": {$schedule->teacher_name} -> {$schedule->target_id}");

            if ($index < $schedules->count() - 1) {
                sleep(3);
            }
        }

        return self::SUCCESS;
    }

    private function appendAutoSignature(string $message): string
    {
        $message = trim($message);

        if (str_contains($message, self::AUTO_SIGNATURE)) {
            return $message;
        }

        return $message."\n\n".self::AUTO_SIGNATURE;
    }
}
