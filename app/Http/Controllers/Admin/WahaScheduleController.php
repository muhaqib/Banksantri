<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use App\Models\WahaMessageLog;
use App\Services\WahaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WahaScheduleController extends Controller
{
    private const AUTO_SIGNATURE = 'Pesan otomatis by: MawaSmart.';
    private const BROADCAST_DELAY_SECONDS = 10;

    public function index(WahaService $wahaService)
    {
        return view('pages.admin.wa-schedules.index', [
            'activeRole' => 'admin',
            'connection' => $wahaService->getConnectionStatus(),
            'groups' => $wahaService->getWahaGroups(),
            'santriTargets' => User::activeSantri()
                ->whereNotNull('no_hp_wali')
                ->where('no_hp_wali', '!=', '')
                ->orderBy('name')
                ->get(['id', 'name', 'nis', 'kelas', 'no_hp_wali']),
            'petugasTargets' => User::where('role', 'petugas')
                ->whereNotNull('no_hp')
                ->where('no_hp', '!=', '')
                ->orderBy('name')
                ->get(['id', 'name', 'nip', 'jabatan', 'no_hp']),
            'schedules' => Schedule::latest()->paginate(10, ['*'], 'schedule_page'),
            'messageLogs' => WahaMessageLog::with('schedule')
                ->latest('sent_at')
                ->latest()
                ->paginate(10, ['*'], 'log_page')
                ->withQueryString(),
            'days' => Schedule::DAYS,
        ]);
    }

    public function status(WahaService $wahaService)
    {
        return response()->json($wahaService->getConnectionStatus());
    }

    public function groups(WahaService $wahaService)
    {
        return response()->json([
            'groups' => $wahaService->getWahaGroups(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);

        Schedule::create($this->payload($validated));

        return redirect()->route('admin.wa-schedules.index')
            ->with('success', 'Jadwal WA berulang berhasil dibuat.');
    }

    public function update(Request $request, Schedule $waSchedule)
    {
        $validated = $this->validatedData($request);

        $waSchedule->update($this->payload($validated));

        return redirect()->route('admin.wa-schedules.index')
            ->with('success', 'Jadwal WA berulang berhasil diperbarui.');
    }

    public function toggle(Schedule $waSchedule)
    {
        $waSchedule->update(['is_active' => ! $waSchedule->is_active]);

        return back()->with('success', 'Status jadwal berhasil diubah.');
    }

    public function sendNow(Schedule $waSchedule, WahaService $wahaService)
    {
        $message = $this->appendAutoSignature(str_replace('[nama_guru]', $waSchedule->teacher_name, $waSchedule->message_content));
        $result = $wahaService->sendMessageResult($waSchedule->target_id, $message);

        WahaMessageLog::create([
            'schedule_id' => $waSchedule->id,
            'teacher_name' => $waSchedule->teacher_name,
            'target_id' => $waSchedule->target_id,
            'session' => $result['session'],
            'message_content' => $message,
            'status' => $result['success'] ? WahaMessageLog::STATUS_SUCCESS : WahaMessageLog::STATUS_FAILED,
            'http_status' => $result['http_status'],
            'response_body' => $result['response_body'],
            'error_message' => $result['error_message'],
            'sent_at' => now('Asia/Jakarta'),
        ]);

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['success']
                ? 'Pesan berhasil dikirim sekarang.'
                : 'Pesan gagal dikirim. Detailnya sudah tercatat di riwayat.'
        );
    }

    public function broadcast(Request $request, WahaService $wahaService)
    {
        $validated = $request->validate([
            'source' => ['required', Rule::in(['database', 'excel'])],
            'audience' => ['required_if:source,database', Rule::in(['all_santri', 'all_petugas', 'all_users', 'selected_santri', 'selected_petugas'])],
            'santri_ids' => ['nullable', 'array'],
            'santri_ids.*' => ['integer', 'exists:users,id'],
            'petugas_ids' => ['nullable', 'array'],
            'petugas_ids.*' => ['integer', 'exists:users,id'],
            'message_content' => ['required', 'string'],
            'excel_file' => ['required_if:source,excel', 'nullable', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ]);

        $targets = $validated['source'] === 'excel'
            ? $this->excelBroadcastTargets($request->file('excel_file'), $validated['message_content'])
            : $this->databaseBroadcastTargets($validated);

        if (empty($targets)) {
            return back()
                ->withInput()
                ->with('error', 'Tidak ada target dengan nomor WhatsApp valid.');
        }

        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }

        $success = 0;
        $failed = 0;

        foreach ($targets as $index => $target) {
            $result = $wahaService->sendMessageResult($target['chat_id'], $target['message']);
            $result['success'] ? $success++ : $failed++;

            WahaMessageLog::create([
                'schedule_id' => null,
                'teacher_name' => $target['label'],
                'target_id' => $target['chat_id'],
                'session' => $result['session'],
                'message_content' => $target['message'],
                'status' => $result['success'] ? WahaMessageLog::STATUS_SUCCESS : WahaMessageLog::STATUS_FAILED,
                'http_status' => $result['http_status'],
                'response_body' => $result['response_body'],
                'error_message' => $result['error_message'],
                'sent_at' => now('Asia/Jakarta'),
            ]);

            if ($index < count($targets) - 1) {
                sleep(self::BROADCAST_DELAY_SECONDS);
            }
        }

        return back()->with(
            $failed === 0 ? 'success' : 'error',
            "Broadcast selesai. Berhasil: {$success}, gagal: {$failed}."
        );
    }

    public function clearLogs()
    {
        WahaMessageLog::query()->delete();

        return back()->with('success', 'Semua riwayat pesan berhasil dihapus.');
    }

    public function destroy(Schedule $waSchedule)
    {
        $waSchedule->delete();

        return redirect()->route('admin.wa-schedules.index')
            ->with('success', 'Jadwal WA berulang berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'teacher_name' => ['required', 'string', 'max:255'],
            'recipient_type' => ['required', Rule::in([Schedule::RECIPIENT_PERSONAL, Schedule::RECIPIENT_GROUP])],
            'phone_number' => ['required_if:recipient_type,personal', 'nullable', 'string', 'max:30'],
            'group_id' => ['required_if:recipient_type,group', 'nullable', 'string', 'max:255'],
            'day_of_week' => ['required', Rule::in(array_keys(Schedule::DAYS))],
            'send_time' => ['required', 'date_format:H:i'],
            'message_content' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function payload(array $validated): array
    {
        return [
            'teacher_name' => $validated['teacher_name'],
            'recipient_type' => $validated['recipient_type'],
            'target_id' => $validated['recipient_type'] === Schedule::RECIPIENT_PERSONAL
                ? $this->normalizePhone($validated['phone_number'])
                : $this->normalizeGroup($validated['group_id']),
            'day_of_week' => $validated['day_of_week'],
            'send_time' => $validated['send_time'].':00',
            'message_content' => $validated['message_content'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];
    }

    private function normalizePhone(?string $phone): string
    {
        $number = preg_replace('/\D+/', '', (string) $phone);

        if (str_starts_with($number, '0')) {
            $number = '62'.substr($number, 1);
        } elseif (str_starts_with($number, '8')) {
            $number = '62'.$number;
        }

        if (strlen($number) < 10) {
            throw ValidationException::withMessages([
                'phone_number' => 'Nomor WhatsApp tidak valid.',
            ]);
        }

        return $number.'@c.us';
    }

    private function normalizeGroup(?string $groupId): string
    {
        if (! is_string($groupId) || ! str_ends_with($groupId, '@g.us')) {
            throw ValidationException::withMessages([
                'group_id' => 'ID grup WhatsApp tidak valid.',
            ]);
        }

        return $groupId;
    }

    private function databaseBroadcastTargets(array $validated): array
    {
        $query = User::query();

        match ($validated['audience']) {
            'all_santri' => $query->activeSantri()->whereNotNull('no_hp_wali')->where('no_hp_wali', '!=', ''),
            'all_petugas' => $query->where('role', 'petugas')->whereNotNull('no_hp')->where('no_hp', '!=', ''),
            'all_users' => $query->where(function ($query): void {
                $query->where(fn ($subQuery) => $subQuery->activeSantri()->whereNotNull('no_hp_wali')->where('no_hp_wali', '!=', ''))
                    ->orWhere(fn ($subQuery) => $subQuery->where('role', 'petugas')->whereNotNull('no_hp')->where('no_hp', '!=', ''));
            }),
            'selected_santri' => $query->activeSantri()->whereIn('id', $validated['santri_ids'] ?? [])->whereNotNull('no_hp_wali')->where('no_hp_wali', '!=', ''),
            'selected_petugas' => $query->where('role', 'petugas')->whereIn('id', $validated['petugas_ids'] ?? [])->whereNotNull('no_hp')->where('no_hp', '!=', ''),
        };

        return $query->orderBy('name')->get()
            ->map(function (User $user) use ($validated): array {
                $phone = $user->role === 'santri' ? $user->no_hp_wali : $user->no_hp;
                $variables = [
                    'nama' => $user->name,
                    'name' => $user->name,
                    'nis' => $user->nis,
                    'nip' => $user->nip,
                    'kelas' => $user->kelas,
                    'jabatan' => $user->jabatan,
                    'role' => $user->role,
                    'no_hp' => $phone,
                ];

                return [
                    'chat_id' => $this->normalizePhone($phone),
                    'label' => 'Broadcast - '.$user->name,
                    'message' => $this->appendAutoSignature($this->replaceVariables($validated['message_content'], $variables)),
                ];
            })
            ->all();
    }

    private function excelBroadcastTargets(?UploadedFile $file, string $messageTemplate): array
    {
        if (! $file instanceof UploadedFile) {
            return [];
        }

        $sheet = IOFactory::load($file->getRealPath())->getActiveSheet();
        $headers = array_map(
            fn ($value): string => $this->normalizeHeader($value),
            $sheet->rangeToArray('A1:'.$sheet->getHighestColumn().'1')[0]
        );
        $phoneIndex = collect($headers)->search(fn (string $header): bool => in_array($header, ['no_hp', 'nomor_hp', 'nomor_wa', 'whatsapp', 'phone'], true));

        if ($phoneIndex === false) {
            throw ValidationException::withMessages([
                'excel_file' => 'Header Excel wajib memiliki kolom no_hp.',
            ]);
        }

        $targets = [];

        for ($rowNumber = 2; $rowNumber <= $sheet->getHighestDataRow(); $rowNumber++) {
            $values = $sheet->rangeToArray('A'.$rowNumber.':'.$sheet->getHighestColumn().$rowNumber, null, true, false)[0];
            $row = array_combine($headers, array_pad($values, count($headers), null));

            if (! collect($row)->filter(fn ($value): bool => filled($value))->count()) {
                continue;
            }

            $phone = $row[$headers[$phoneIndex]] ?? null;
            if (! filled($phone)) {
                continue;
            }

            $targets[] = [
                'chat_id' => $this->normalizePhone($phone),
                'label' => 'Broadcast Excel baris '.$rowNumber,
                'message' => $this->appendAutoSignature($this->replaceVariables($messageTemplate, $row)),
            ];
        }

        return $targets;
    }

    private function replaceVariables(string $message, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $key = $this->normalizeHeader($key);
            $value = (string) ($value ?? '');

            foreach (["[{$key}]", '{'.$key.'}', '{{'.$key.'}}'] as $placeholder) {
                $message = str_replace($placeholder, $value, $message);
            }

            if (preg_match('/^var_?(\d+)$/', $key, $matches)) {
                $message = str_replace('[var '.$matches[1].']', $value, $message);
            }
        }

        return $message;
    }

    private function normalizeHeader(mixed $value): string
    {
        return str((string) $value)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();
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
