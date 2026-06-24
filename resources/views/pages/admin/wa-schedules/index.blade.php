@extends('layouts.app')

@section('title', 'WA Mawasmart')
@section('header-title', 'WA Mawasmart')

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
            <p class="text-sm font-bold text-primary">WA Mawasmart</p>
            <h1 class="font-headline text-3xl font-black">WhatsApp Broadcast & Recurring</h1>
            <p class="text-sm text-on-surface-variant">Kirim pesan manual, jadwalkan pesan berulang, dan pantau riwayat pengiriman.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-primary/10 px-4 py-3 text-sm font-bold text-primary">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-xl bg-error/10 px-4 py-3 text-sm font-bold text-error">{{ session('error') }}</div>
    @endif
    @if(session('import_errors'))
        <div class="rounded-xl bg-error/10 px-4 py-3 text-sm font-semibold text-error">
            @foreach(session('import_errors') as $importError)
                <p>{{ $importError }}</p>
            @endforeach
        </div>
    @endif

    <section class="rounded-xl bg-surface-container-lowest p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="font-headline text-lg font-bold text-primary">Status Koneksi WAHA</h2>
                <div class="mt-2 flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full" :class="connection.connected ? 'bg-emerald-500' : 'bg-red-500'"></span>
                    <span class="text-sm font-bold" x-text="connection.connected ? `Terhubung: ${connection.device || 'Perangkat WhatsApp'}` : 'Terputus'"></span>
                </div>
                <p class="mt-1 text-xs text-on-surface-variant" x-show="connection.error" x-text="connection.error"></p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <template x-if="!connection.connected && connection.qr">
                    <img :src="connection.qr" alt="QR Code WAHA" class="h-24 rounded-lg bg-white p-2 shadow-sm">
                </template>
                <button type="button" @click="refreshStatus" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-bold text-on-primary shadow-sm">
                    <span class="material-symbols-outlined text-base">refresh</span>
                    Refresh
                </button>
            </div>
        </div>
    </section>

    <div class="rounded-xl bg-surface-container-lowest p-2 shadow-sm">
        <div class="grid gap-2 md:grid-cols-3">
            <button type="button" @click="tab = 'send'" class="rounded-lg px-4 py-3 text-sm font-bold transition" :class="tab === 'send' ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-on-surface-variant'">
                Kirim Pesan
            </button>
            <button type="button" @click="tab = 'recurring'" class="rounded-lg px-4 py-3 text-sm font-bold transition" :class="tab === 'recurring' ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-on-surface-variant'">
                WA Recurring
            </button>
            <button type="button" @click="tab = 'history'" class="rounded-lg px-4 py-3 text-sm font-bold transition" :class="tab === 'history' ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-on-surface-variant'">
                Riwayat Pesan
            </button>
        </div>
    </div>

    <section x-show="tab === 'send'" x-cloak class="grid gap-5 xl:grid-cols-5">
        <form method="POST" action="{{ route('admin.wa-schedules.broadcast') }}" enctype="multipart/form-data" class="rounded-xl bg-surface-container-lowest p-5 shadow-sm xl:col-span-3">
            @csrf
            <div class="mb-5">
                <h2 class="font-headline text-lg font-bold text-primary">Kirim Pesan Broadcast</h2>
                <p class="text-xs text-on-surface-variant">Delay otomatis 10 detik antar nomor. Footer otomatis ditambahkan: Pesan otomatis by: MawaSmart.</p>
            </div>

            <div class="space-y-5">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-on-surface-variant">Sumber Penerima</label>
                    <div class="grid gap-2 md:grid-cols-2">
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl bg-surface-container-low px-3 py-3 text-sm font-bold">
                            <input type="radio" name="source" value="database" x-model="broadcast.source" class="text-primary focus:ring-primary">
                            Data Santri/Petugas
                        </label>
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl bg-surface-container-low px-3 py-3 text-sm font-bold">
                            <input type="radio" name="source" value="excel" x-model="broadcast.source" class="text-primary focus:ring-primary">
                            Upload Excel
                        </label>
                    </div>
                    @error('source') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>

                <div x-show="broadcast.source === 'database'" class="space-y-4">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-on-surface-variant">Target</label>
                        <select name="audience" x-model="broadcast.audience" class="input-field w-full">
                            <option value="all_santri">Seluruh wali santri aktif</option>
                            <option value="all_petugas">Seluruh petugas</option>
                            <option value="all_users">Seluruh wali santri & petugas</option>
                            <option value="selected_santri">Pilih beberapa santri</option>
                            <option value="selected_petugas">Pilih beberapa petugas</option>
                        </select>
                        @error('audience') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="broadcast.audience === 'selected_santri'" class="rounded-xl bg-surface-container-low p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <p class="text-xs font-bold uppercase text-on-surface-variant">Pilih Santri</p>
                            <span class="text-xs text-on-surface-variant">{{ $santriTargets->count() }} tersedia</span>
                        </div>
                        <div class="grid max-h-72 gap-2 overflow-y-auto pr-1 md:grid-cols-2">
                            @forelse($santriTargets as $santri)
                                <label class="flex cursor-pointer items-start gap-2 rounded-lg bg-surface-container-lowest px-3 py-2 text-sm">
                                    <input type="checkbox" name="santri_ids[]" value="{{ $santri->id }}" class="mt-1 rounded border-outline-variant text-primary focus:ring-primary">
                                    <span>
                                        <span class="block font-bold">{{ $santri->name }}</span>
                                        <span class="block text-xs text-on-surface-variant">{{ $santri->nis ?? '-' }} | Wali: {{ $santri->no_hp_wali }}</span>
                                    </span>
                                </label>
                            @empty
                                <p class="text-sm text-on-surface-variant">Belum ada santri dengan no_hp_wali.</p>
                            @endforelse
                        </div>
                        @error('santri_ids') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="broadcast.audience === 'selected_petugas'" class="rounded-xl bg-surface-container-low p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <p class="text-xs font-bold uppercase text-on-surface-variant">Pilih Petugas</p>
                            <span class="text-xs text-on-surface-variant">{{ $petugasTargets->count() }} tersedia</span>
                        </div>
                        <div class="grid max-h-72 gap-2 overflow-y-auto pr-1 md:grid-cols-2">
                            @forelse($petugasTargets as $petugas)
                                <label class="flex cursor-pointer items-start gap-2 rounded-lg bg-surface-container-lowest px-3 py-2 text-sm">
                                    <input type="checkbox" name="petugas_ids[]" value="{{ $petugas->id }}" class="mt-1 rounded border-outline-variant text-primary focus:ring-primary">
                                    <span>
                                        <span class="block font-bold">{{ $petugas->name }}</span>
                                        <span class="block text-xs text-on-surface-variant">{{ $petugas->jabatan ?? '-' }} | {{ $petugas->no_hp }}</span>
                                    </span>
                                </label>
                            @empty
                                <p class="text-sm text-on-surface-variant">Belum ada petugas dengan no_hp.</p>
                            @endforelse
                        </div>
                        @error('petugas_ids') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div x-show="broadcast.source === 'excel'" class="rounded-xl bg-surface-container-low p-4">
                    <label class="mb-2 block text-xs font-bold uppercase text-on-surface-variant">File Excel/CSV</label>
                    <input type="file" name="excel_file" accept=".xlsx,.xls,.csv,.txt" class="input-field w-full bg-surface-container-lowest">
                    <p class="mt-2 text-xs text-on-surface-variant">Header wajib: <span class="font-bold">no_hp</span>. Header lain bisa dipakai sebagai variabel, misalnya <span class="font-bold">var1, var2, nama, tagihan</span>.</p>
                    @error('excel_file') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-on-surface-variant">Isi Pesan</label>
                    <textarea name="message_content" required rows="7" class="input-field w-full resize-none" placeholder="Assalamu'alaikum Bapak/Ibu [nama], tagihan bulan ini [var1].">{{ old('message_content') }}</textarea>
                    <p class="mt-1 text-xs text-on-surface-variant">Variabel database: [nama], [nis], [kelas], [jabatan], [role]. Variabel Excel mengikuti header: [var1], [var2], [tagihan], atau [var 1].</p>
                    @error('message_content') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                </div>

                <button class="btn-primary w-full justify-center" onclick="return confirm('Kirim broadcast sekarang? Proses akan menunggu 10 detik antar nomor.')">
                    <span class="material-symbols-outlined">send</span>
                    Kirim Broadcast
                </button>
            </div>
        </form>

        <aside class="rounded-xl bg-surface-container-lowest p-5 shadow-sm xl:col-span-2">
            <h3 class="font-headline text-lg font-bold text-primary">Format Excel</h3>
            <div class="mt-4 overflow-hidden rounded-xl border border-outline-variant/20">
                <table class="w-full text-left text-xs">
                    <thead class="bg-surface-container-low font-bold uppercase text-on-surface-variant">
                        <tr>
                            <th class="px-3 py-2">no_hp</th>
                            <th class="px-3 py-2">nama</th>
                            <th class="px-3 py-2">var1</th>
                            <th class="px-3 py-2">var2</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-3 py-2">081234567890</td>
                            <td class="px-3 py-2">Ahmad</td>
                            <td class="px-3 py-2">Rp50.000</td>
                            <td class="px-3 py-2">Juni</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 rounded-xl bg-primary/10 p-4 text-xs font-semibold text-primary">
                Nomor 08... dan 8... otomatis dinormalisasi ke format Indonesia 62... sebelum dikirim ke WAHA.
            </div>
        </aside>
    </section>

    <section x-show="tab === 'recurring'" x-cloak class="space-y-5">
        <div class="grid gap-5 xl:grid-cols-5">
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
                        <p class="mt-1 text-xs text-on-surface-variant">Variabel tersedia: <span class="font-bold">[nama_guru]</span>. Footer otomatis ditambahkan saat dikirim.</p>
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

            <section class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm xl:col-span-3">
                <div class="flex items-center justify-between border-b border-outline-variant/10 px-5 py-4">
                    <h2 class="font-headline text-lg font-bold text-primary">Daftar WA Recurring</h2>
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
                                            <form method="POST" action="{{ route('admin.wa-schedules.send-now', $schedule) }}" onsubmit="return confirm('Kirim pesan jadwal ini sekarang?')">
                                                @csrf
                                                <button class="text-primary" title="Kirim sekarang">
                                                    <span class="material-symbols-outlined">send</span>
                                                </button>
                                            </form>
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
        </div>
    </section>

    <section x-show="tab === 'history'" x-cloak class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm">
        <div class="flex flex-col gap-3 border-b border-outline-variant/10 px-5 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-headline text-lg font-bold text-primary">Riwayat Pesan</h2>
                <p class="text-xs text-on-surface-variant">Catatan hasil pengiriman broadcast dan WA recurring.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-on-surface-variant">{{ $messageLogs->total() }} riwayat</span>
                <form method="POST" action="{{ route('admin.wa-schedules.logs.clear') }}" onsubmit="return confirm('Hapus semua riwayat pesan?')">
                    @csrf @method('DELETE')
                    <button class="inline-flex items-center gap-2 rounded-xl bg-error px-4 py-2 text-xs font-bold text-on-error">
                        <span class="material-symbols-outlined text-base">delete_sweep</span>
                        Hapus semua Riwayat
                    </button>
                </form>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-surface-container-low text-xs uppercase text-on-surface-variant">
                    <tr>
                        <th class="px-5 py-4">Waktu</th>
                        <th class="px-5 py-4">Jenis/Nama</th>
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
                            <td class="px-5 py-4">
                                <div class="font-semibold">{{ $log->teacher_name ?? '-' }}</div>
                                <div class="text-xs text-on-surface-variant">{{ $log->schedule_id ? 'WA Recurring' : 'Broadcast' }}</div>
                            </td>
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
        tab: new URLSearchParams(window.location.search).has('log_page')
            ? 'history'
            : (new URLSearchParams(window.location.search).has('schedule_page') ? 'recurring' : 'send'),
        connection: config.connection || { connected: false, status: 'DISCONNECTED', qr: null, device: null },
        groups: config.groups || [],
        schedules: config.schedules || [],
        storeUrl: config.storeUrl,
        updateUrlTemplate: config.updateUrlTemplate,
        statusUrl: config.statusUrl,
        groupsUrl: config.groupsUrl,
        editingId: null,
        broadcast: {
            source: 'database',
            audience: 'all_santri',
        },
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

            this.tab = 'recurring';
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
