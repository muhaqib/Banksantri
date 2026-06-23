@extends('layouts.app')

@section('title', 'WA Recurring')
@section('header-title', 'WA Recurring')

@section('content')
<div x-data="waScheduleManager(@js([
    'connection' => $connection,
    'groups' => $groups,
    'schedules' => $schedules->getCollection()->map(fn ($schedule) => [
        'id' => $schedule->id,
        'teacher_name' => $schedule->teacher_name,
        'recipient_type' => $schedule->recipient_type,
        'target_id' => $schedule->target_id,
        'day_of_week' => $schedule->day_of_week,
        'send_time' => $schedule->send_time?->format('H:i'),
        'message_content' => $schedule->message_content,
        'is_active' => $schedule->is_active,
    ])->values(),
    'storeUrl' => route('admin.wa-schedules.store'),
    'updateUrlTemplate' => route('admin.wa-schedules.update', ['waSchedule' => '__ID__']),
    'statusUrl' => route('admin.wa-schedules.status'),
    'groupsUrl' => route('admin.wa-schedules.groups'),
]))" class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-bold text-primary">WhatsApp Automation</p>
            <h1 class="font-headline text-3xl font-black">Jadwal WA Berulang</h1>
            <p class="text-sm text-on-surface-variant">Kirim pesan mingguan otomatis ke nomor pribadi atau grup WhatsApp.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-primary/10 px-4 py-3 text-sm font-bold text-primary">{{ session('success') }}</div>
    @endif

    <div class="grid gap-5 xl:grid-cols-5">
        <section class="rounded-xl bg-surface-container-lowest p-5 shadow-sm xl:col-span-3">
            <div class="flex items-start justify-between gap-4 border-b border-outline-variant/10 pb-4">
                <div>
                    <h2 class="font-headline text-lg font-bold text-primary">Status Koneksi WAHA</h2>
                    <div class="mt-3 flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full" :class="connection.connected ? 'bg-emerald-500' : 'bg-red-500'"></span>
                        <span class="text-sm font-bold" x-text="connection.connected ? `Terhubung: ${connection.device || 'Perangkat WhatsApp'}` : 'Terputus'"></span>
                    </div>
                    <p class="mt-2 text-xs text-on-surface-variant" x-show="connection.error" x-text="connection.error"></p>
                </div>
                <button type="button" @click="refreshStatus" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-bold text-on-primary shadow-sm">
                    <span class="material-symbols-outlined text-base">refresh</span>
                    Refresh
                </button>
            </div>

            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div class="flex min-h-52 items-center justify-center rounded-xl border border-dashed border-outline-variant bg-surface-container-low p-4">
                    <template x-if="!connection.connected && connection.qr">
                        <img :src="connection.qr" alt="QR Code WAHA" class="max-h-48 rounded-lg bg-white p-2 shadow-sm">
                    </template>
                    <template x-if="!connection.connected && !connection.qr">
                        <div class="text-center text-sm text-on-surface-variant">
                            <span class="material-symbols-outlined mb-2 text-5xl text-outline">qr_code_2</span>
                            <p class="font-bold">QR Code akan muncul di sini</p>
                        </div>
                    </template>
                    <template x-if="connection.connected">
                        <div class="text-center text-primary">
                            <span class="material-symbols-outlined mb-2 text-5xl">check_circle</span>
                            <p class="text-sm font-bold">Sesi WAHA sudah aktif</p>
                        </div>
                    </template>
                </div>
                <div class="space-y-3 text-sm text-on-surface-variant">
                    <p>Jika status terputus, scan QR Code menggunakan WhatsApp di ponsel yang akan dipakai sebagai pengirim.</p>
                    <div class="rounded-xl bg-primary/10 p-4 text-primary">
                        <div class="flex gap-2">
                            <span class="material-symbols-outlined text-lg">info</span>
                            <p class="text-xs font-bold">Pastikan Docker WAHA berjalan dan koneksi internet stabil sebelum jadwal otomatis aktif.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-xl bg-surface-container-lowest p-5 shadow-sm xl:col-span-2">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="font-headline text-lg font-bold text-primary" x-text="editingId ? 'Edit Jadwal' : 'Tambah Jadwal'"></h2>
                <button type="button" x-show="editingId" @click="resetForm" class="text-xs font-bold text-primary">Batal Edit</button>
            </div>

            <form method="POST" :action="formAction" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" :value="editingId ? 'PATCH' : 'POST'">

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-on-surface-variant">Nama Guru</label>
                    <input name="teacher_name" x-model="form.teacher_name" required class="input-field w-full" placeholder="Masukkan nama guru">
                    @error('teacher_name') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-on-surface-variant">Opsi Pengiriman</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl bg-surface-container-low px-3 py-3 text-sm font-bold">
                            <input type="radio" name="recipient_type" value="personal" x-model="form.recipient_type" class="text-primary focus:ring-primary">
                            Nomor Pribadi
                        </label>
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl bg-surface-container-low px-3 py-3 text-sm font-bold">
                            <input type="radio" name="recipient_type" value="group" x-model="form.recipient_type" class="text-primary focus:ring-primary">
                            Grup WhatsApp
                        </label>
                    </div>
                    @error('recipient_type') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>

                <div x-show="form.recipient_type === 'personal'">
                    <label class="mb-2 block text-xs font-bold uppercase text-on-surface-variant">Nomor WhatsApp</label>
                    <input name="phone_number" x-model="form.phone_number" class="input-field w-full" placeholder="081234...">
                    @error('phone_number') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>

                <div x-show="form.recipient_type === 'group'">
                    <div class="mb-2 flex items-center justify-between">
                        <label class="block text-xs font-bold uppercase text-on-surface-variant">Grup WhatsApp</label>
                        <button type="button" @click="refreshGroups" class="text-xs font-bold text-primary">Refresh Grup</button>
                    </div>
                    <select name="group_id" x-model="form.group_id" class="input-field w-full">
                        <option value="">Pilih grup</option>
                        <template x-for="group in groups" :key="group.id">
                            <option :value="group.id" x-text="group.name"></option>
                        </template>
                    </select>
                    @error('group_id') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    <p x-show="groups.length === 0" class="mt-2 text-xs text-on-surface-variant">Belum ada grup yang terbaca dari WAHA.</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-on-surface-variant">Hari</label>
                        <select name="day_of_week" x-model="form.day_of_week" required class="input-field w-full">
                            @foreach($days as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-on-surface-variant">Jam Kirim</label>
                        <input type="time" name="send_time" x-model="form.send_time" required class="input-field w-full">
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-on-surface-variant">Isi Pesan</label>
                    <textarea name="message_content" x-model="form.message_content" required rows="5" class="input-field w-full resize-none" placeholder="Halo Bapak/Ibu [nama_guru], jadwal mengajar Anda..."></textarea>
                    <p class="mt-1 text-xs text-on-surface-variant">Variabel tersedia: <span class="font-bold">[nama_guru]</span></p>
                    @error('message_content') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center justify-between rounded-xl bg-surface-container-low px-4 py-3 text-sm font-bold">
                    Status Aktif
                    <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded border-outline-variant text-primary focus:ring-primary">
                </label>

                <button class="btn-primary w-full justify-center">
                    <span class="material-symbols-outlined">save</span>
                    Simpan Jadwal
                </button>
            </form>
        </section>
    </div>

    <section class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm">
        <div class="flex items-center justify-between border-b border-outline-variant/10 px-5 py-4">
            <h2 class="font-headline text-lg font-bold text-primary">Daftar Jadwal Mengajar</h2>
            <span class="text-xs font-bold text-on-surface-variant">{{ $schedules->total() }} jadwal</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-surface-container-low text-xs uppercase text-on-surface-variant">
                    <tr>
                        <th class="px-5 py-4">Guru</th>
                        <th class="px-5 py-4">Tujuan</th>
                        <th class="px-5 py-4">Hari</th>
                        <th class="px-5 py-4">Jam</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    @forelse($schedules as $schedule)
                        <tr>
                            <td class="px-5 py-4 font-bold text-on-surface">{{ $schedule->teacher_name }}</td>
                            <td class="px-5 py-4">
                                <div class="font-semibold">{{ $schedule->recipient_type === 'group' ? 'Grup WhatsApp' : 'Nomor Pribadi' }}</div>
                                <div class="text-xs text-on-surface-variant">{{ $schedule->target_label }}</div>
                            </td>
                            <td class="px-5 py-4">{{ $schedule->day_label }}</td>
                            <td class="px-5 py-4">{{ $schedule->send_time?->format('H:i') }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $schedule->is_active ? 'bg-primary/10 text-primary' : 'bg-surface-container-high text-on-surface-variant' }}">
                                    {{ $schedule->is_active ? 'Aktif' : 'Non-aktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="editSchedule({{ $schedule->id }})" class="text-primary" title="Edit">
                                        <span class="material-symbols-outlined">edit</span>
                                    </button>
                                    <form method="POST" action="{{ route('admin.wa-schedules.destroy', $schedule) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-error" title="Hapus"><span class="material-symbols-outlined">delete</span></button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.wa-schedules.toggle', $schedule) }}">
                                        @csrf @method('PATCH')
                                        <button class="{{ $schedule->is_active ? 'text-primary' : 'text-on-surface-variant' }}" title="Ubah status">
                                            <span class="material-symbols-outlined">{{ $schedule->is_active ? 'toggle_on' : 'toggle_off' }}</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14 text-center text-on-surface-variant">Belum ada jadwal WA berulang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($schedules->hasPages())
            <div class="border-t border-outline-variant/10 px-5 py-4">
                {{ $schedules->links() }}
            </div>
        @endif
    </section>

    <section class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm">
        <div class="flex items-center justify-between border-b border-outline-variant/10 px-5 py-4">
            <div>
                <h2 class="font-headline text-lg font-bold text-primary">Riwayat Pengiriman</h2>
                <p class="text-xs text-on-surface-variant">Catatan hasil pengiriman otomatis dari scheduler.</p>
            </div>
            <span class="text-xs font-bold text-on-surface-variant">{{ $messageLogs->total() }} riwayat</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-surface-container-low text-xs uppercase text-on-surface-variant">
                    <tr>
                        <th class="px-5 py-4">Waktu</th>
                        <th class="px-5 py-4">Guru</th>
                        <th class="px-5 py-4">Target</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    @forelse($messageLogs as $log)
                        <tr>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-bold">{{ $log->sent_at?->timezone('Asia/Jakarta')->format('d/m/Y') }}</div>
                                <div class="text-xs text-on-surface-variant">{{ $log->sent_at?->timezone('Asia/Jakarta')->format('H:i:s') }}</div>
                            </td>
                            <td class="px-5 py-4 font-semibold">{{ $log->teacher_name ?? '-' }}</td>
                            <td class="px-5 py-4">
                                <div class="font-mono text-xs">{{ $log->target_id }}</div>
                                <div class="text-xs text-on-surface-variant">Session: {{ $log->session ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $log->status === 'success' ? 'bg-primary/10 text-primary' : 'bg-error/10 text-error' }}">
                                    {{ $log->status_label }}
                                </span>
                                @if($log->http_status)
                                    <div class="mt-1 text-xs text-on-surface-variant">HTTP {{ $log->http_status }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 max-w-md">
                                <p class="line-clamp-2 text-xs text-on-surface-variant">{{ $log->message_content }}</p>
                                @if($log->error_message)
                                    <p class="mt-2 line-clamp-2 text-xs font-semibold text-error">{{ $log->error_message }}</p>
                                @elseif($log->response_body)
                                    <p class="mt-2 line-clamp-1 text-xs text-on-surface-variant">{{ $log->response_body }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-14 text-center text-on-surface-variant">Belum ada riwayat pengiriman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($messageLogs->hasPages())
            <div class="border-t border-outline-variant/10 px-5 py-4">
                {{ $messageLogs->links() }}
            </div>
        @endif
    </section>
</div>

<script>
function waScheduleManager(config) {
    return {
        connection: config.connection || { connected: false, status: 'DISCONNECTED', qr: null, device: null },
        groups: config.groups || [],
        schedules: config.schedules || [],
        storeUrl: config.storeUrl,
        updateUrlTemplate: config.updateUrlTemplate,
        statusUrl: config.statusUrl,
        groupsUrl: config.groupsUrl,
        editingId: null,
        form: {
            teacher_name: '',
            recipient_type: 'personal',
            phone_number: '',
            group_id: '',
            day_of_week: 'Monday',
            send_time: '07:00',
            message_content: '',
            is_active: true,
        },

        get formAction() {
            return this.editingId
                ? this.updateUrlTemplate.replace('__ID__', this.editingId)
                : this.storeUrl;
        },

        async refreshStatus() {
            const response = await fetch(this.statusUrl, { headers: { Accept: 'application/json' } });
            this.connection = await response.json();
        },

        async refreshGroups() {
            const response = await fetch(this.groupsUrl, { headers: { Accept: 'application/json' } });
            const data = await response.json();
            this.groups = data.groups || [];
        },

        editSchedule(id) {
            const schedule = this.schedules.find((item) => item.id === id);
            if (!schedule) return;

            this.editingId = schedule.id;
            this.form.teacher_name = schedule.teacher_name;
            this.form.recipient_type = schedule.recipient_type;
            this.form.phone_number = schedule.recipient_type === 'personal' ? schedule.target_id.replace('@c.us', '') : '';
            this.form.group_id = schedule.recipient_type === 'group' ? schedule.target_id : '';
            this.form.day_of_week = schedule.day_of_week;
            this.form.send_time = schedule.send_time || '07:00';
            this.form.message_content = schedule.message_content;
            this.form.is_active = Boolean(schedule.is_active);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        resetForm() {
            this.editingId = null;
            this.form = {
                teacher_name: '',
                recipient_type: 'personal',
                phone_number: '',
                group_id: '',
                day_of_week: 'Monday',
                send_time: '07:00',
                message_content: '',
                is_active: true,
            };
        },
    };
}
</script>
@endsection
