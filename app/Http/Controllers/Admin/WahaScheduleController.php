<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\WahaMessageLog;
use App\Services\WahaService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class WahaScheduleController extends Controller
{
    public function index(WahaService $wahaService)
    {
        return view('pages.admin.wa-schedules.index', [
            'activeRole' => 'admin',
            'connection' => $wahaService->getConnectionStatus(),
            'groups' => $wahaService->getWahaGroups(),
            'schedules' => Schedule::latest()->paginate(10, ['*'], 'schedule_page'),
            'messageLogs' => WahaMessageLog::with('schedule')
                ->latest('sent_at')
                ->latest()
                ->paginate(10, ['*'], 'log_page'),
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
}
