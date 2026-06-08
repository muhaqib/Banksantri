@extends('layouts.app')

@section('title', 'Absensi Harian')
@section('header-title', 'Absensi Harian')

@push('styles')
<style>
    .scan-ring {
        animation: pulse-ring 2s cubic-bezier(0.455, 0.03, 0.515, 0.955) infinite;
    }

    .scan-line {
        animation: scan-move 3s ease-in-out infinite;
    }

    @keyframes pulse-ring {
        0% {
            opacity: .5;
            transform: scale(.8);
        }

        80%, 100% {
            opacity: 0;
            transform: scale(1.3);
        }
    }

    @keyframes scan-move {
        0%, 100% {
            top: 10%;
        }

        50% {
            top: 90%;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-8" x-data="attendancePage()">
    <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-bold text-primary">Absensi Harian</p>
            <h1 class="font-headline text-3xl font-black text-primary">Absensi Santri RFID</h1>
            <p class="mt-1 text-sm text-on-surface-variant">Tempelkan kartu RFID santri untuk mencatat kehadiran hari ini tanpa memilih kamar.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route($routePrefix.'.permissions.create') }}" class="btn-secondary">
                <span class="material-symbols-outlined">badge</span> Buat Izin
            </a>
            <a href="{{ route($routePrefix.'.attendance.dashboard') }}" class="btn-primary">
                <span class="material-symbols-outlined">analytics</span> Dashboard Bulanan
            </a>
        </div>
    </header>

    

    <div class="grid grid-cols-1 gap-8 xl:grid-cols-12">
        <section class="relative flex min-h-[520px] flex-col items-center justify-center overflow-hidden rounded-[2rem] bg-surface-container-low p-6 sm:p-10 xl:col-span-7">
            <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent"></div>
            <div class="relative flex h-72 w-72 items-center justify-center">
                <div class="scan-ring absolute h-full w-full rounded-full border-4 border-primary/10"></div>
                <div class="scan-ring absolute h-4/5 w-4/5 rounded-full border-2 border-primary/20" style="animation-delay: .5s"></div>
                <div class="relative z-10 flex h-36 w-56 rotate-[-5deg] flex-col justify-between rounded-2xl bg-gradient-to-br from-primary to-primary-container p-5 shadow-2xl transition-transform duration-500 hover:rotate-0">
                    <div class="flex items-start justify-between">
                        <span class="material-symbols-outlined material-symbols-filled text-4xl text-on-primary/30">contactless</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10">
                            <span class="material-symbols-outlined text-sm text-on-primary">lock</span>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <div class="h-2 w-16 rounded-full bg-white/20"></div>
                        <div class="h-3 w-32 rounded-full bg-white/40"></div>
                    </div>
                    <div class="scan-line absolute inset-x-0 h-1 bg-primary-fixed opacity-70 shadow-[0_0_15px_#a2f0ee]"></div>
                </div>
            </div>

            <div class="relative z-10 mt-10 w-full max-w-xl text-center">
                <h2 class="font-headline text-2xl font-black text-on-surface">Tap RFID Reader</h2>
                <p class="mx-auto mt-2 max-w-sm text-sm text-on-surface-variant">Setiap kartu RFID santri yang berhasil terbaca akan langsung dianggap hadir pada tanggal terpilih.</p>
                <form @submit.prevent="scan" class="mt-6 flex gap-2 rounded-2xl bg-surface-container-lowest p-2 shadow-sm">
                    <input x-ref="rfid" x-model="rfid" autofocus autocomplete="off" placeholder="Tempelkan kartu RFID..." class="min-w-0 flex-1 rounded-xl border-none bg-transparent px-4 py-3 text-on-surface outline-none focus:ring-2 focus:ring-primary/20">
                    <button :disabled="loading" class="flex h-12 w-14 shrink-0 items-center justify-center rounded-xl bg-primary text-on-primary transition disabled:opacity-50">
                        <span class="material-symbols-outlined" :class="loading && 'animate-spin'" x-text="loading ? 'progress_activity' : 'sensors'"></span>
                    </button>
                </form>
                <p x-show="message" x-cloak x-text="message" :class="success ? 'bg-primary-fixed/40 text-primary' : 'bg-error-container text-on-error-container'" class="mt-4 rounded-xl px-4 py-3 text-sm font-bold"></p>
                <div class="mt-8 flex items-center justify-center gap-6">
                    <div class="flex flex-col items-center">
                        <div class="mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-surface-container-lowest text-primary">
                            <span class="material-symbols-outlined">wifi_tethering</span>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-on-surface-variant">Siap Baca</span>
                    </div>
                    <div class="h-8 w-px bg-outline-variant/30"></div>
                    <div class="flex flex-col items-center">
                        <div class="mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-surface-container-lowest text-on-surface-variant">
                            <span class="material-symbols-outlined">sync</span>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-on-surface-variant">Real-time Sync</span>
                    </div>
                </div>
            </div>
        </section>
        

        <aside class="xl:col-span-5">
            <div class="h-full rounded-[2rem] bg-surface-container-lowest p-6 shadow-sm sm:p-8">
                <div class="mb-8 flex items-center justify-between">
                    <h2 class="font-headline text-xl font-bold text-on-surface">5 Nama Terakhir</h2>
                    <span class="rounded-full bg-tertiary-container/20 px-3 py-1 text-xs font-bold text-tertiary">LIVE</span>
                </div>
                <div class="space-y-5">
                    @forelse($recentAttendances as $attendance)
                        <div class="flex items-center gap-4 {{ $loop->first ? 'animate-slide-in' : '' }}" id="recent-{{ $attendance->santri_id }}">
                            <div class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-secondary-container font-headline text-lg font-black text-secondary">
                                {{ str($attendance->santri?->name ?? '?')->substr(0, 1)->upper() }}
                                <div class="absolute -bottom-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full border-4 border-surface-container-lowest bg-primary">
                                    <span class="material-symbols-outlined text-[10px] text-on-primary">check</span>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="truncate font-bold leading-tight text-on-surface">{{ $attendance->santri?->name ?? 'Santri' }}</h3>
                                    <span class="shrink-0 rounded-full bg-primary-fixed/30 px-2 py-0.5 text-[10px] font-medium text-primary">{{ $attendance->recorded_at?->diffForHumans() }}</span>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-on-surface-variant">
                                    <span>NIS: {{ $attendance->santri?->nis ?? '-' }}</span>
                                    <span class="h-1 w-1 rounded-full bg-outline-variant"></span>
                                    <span class="font-semibold text-secondary">{{ ucwords(str_replace('_', ' ', $attendance->kamar)) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl bg-surface-container-low p-6 text-center text-sm font-semibold text-on-surface-variant">
                            Belum ada kartu yang tap pada tanggal ini.
                        </div>
                    @endforelse
                </div>
                <div class="mt-10 border-t border-outline-variant/10 pt-8">
                    <div class="flex items-center justify-between text-on-surface-variant">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">group</span>
                            <span class="text-xs font-medium">Total Kehadiran Hari Ini:</span>
                        </div>
                        <span class="text-lg font-extrabold text-primary">{{ $summary['hadir'] }} / {{ max($summary['total'], $summary['hadir']) }}</span>
                    </div>
                    @php $attendancePercent = max($summary['total'], $summary['hadir']) > 0 ? min(100, round(($summary['hadir'] / max($summary['total'], $summary['hadir'])) * 100)) : 0; @endphp
                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-surface-container">
                        <div class="h-full rounded-full bg-primary" style="width: {{ $attendancePercent }}%"></div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
        @foreach([
            ['Kartu Terdaftar', $summary['total'], 'contactless', 'text-primary', 'bg-primary-container/10'],
            ['Hadir', $summary['hadir'], 'event_available', 'text-green-600', 'bg-green-50'],
            ['Izin', $summary['izin'], 'badge', 'text-amber-600', 'bg-amber-50'],
            ['Ghoib', $summary['ghoib'], 'cancel', 'text-error', 'bg-red-50'],
            ['Belum Tap', $summary['belum'], 'schedule', 'text-on-surface-variant', 'bg-surface-container-low'],
        ] as [$label, $value, $icon, $color, $background])
            <div class="{{ $background }} flex items-center gap-5 rounded-[2rem] p-5">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-surface-container-lowest {{ $color }}">
                    <span class="material-symbols-outlined text-3xl">{{ $icon }}</span>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">{{ $label }}</p>
                    <p class="text-2xl font-extrabold {{ $color }}">{{ $value }}</p>
                </div>
            </div>
        @endforeach
    </div>
    <form method="GET" action="{{ route($routePrefix.'.attendance.index') }}" class="grid gap-3 rounded-xl bg-surface-container-lowest p-4 shadow-sm md:grid-cols-[220px_minmax(0,1fr)_auto]">
        <label class="text-xs font-bold text-on-surface-variant">Tanggal
            <input type="date" name="date" value="{{ $date->toDateString() }}" class="input-field mt-1">
        </label>
        <label class="text-xs font-bold text-on-surface-variant">Cari Santri
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Nama atau NIS" class="input-field mt-1">
        </label>
        <button class="btn-primary self-end"><span class="material-symbols-outlined">filter_alt</span> Terapkan</button>
    </form>

    <div class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm">
        <div class="border-b border-outline-variant/10 p-5">
            <h2 class="font-headline text-lg font-black text-primary">Daftar Absensi Santri</h2>
            <p class="mt-1 text-sm text-on-surface-variant">{{ $date->translatedFormat('d F Y') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low text-xs uppercase tracking-wider text-on-surface-variant">
                    <tr>
                        <th class="px-5 py-4">Santri</th>
                        <th class="px-5 py-4">Kamar</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Metode / Izin</th>
                        <th class="px-5 py-4">Ubah Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    @forelse($santriList as $santri)
                        @php
                            $attendance = $santri->attendances->first();
                            $activePermission = $santri->santriPermissions->first();
                            $status = $attendance?->status ?? ($activePermission ? 'izin' : ($date->isBefore(today()) ? 'ghoib' : 'belum'));
                            $statusStyle = match($status) {
                                'hadir' => 'bg-green-50 text-green-700',
                                'izin' => 'bg-amber-50 text-amber-700',
                                'ghoib' => 'bg-red-50 text-red-700',
                                default => 'bg-surface-container text-on-surface-variant',
                            };
                        @endphp
                        <tr id="santri-{{ $santri->id }}" class="hover:bg-surface-container-low/40">
                            <td class="px-5 py-4">
                                <p class="font-bold text-on-surface">{{ $santri->name }}</p>
                                <p class="text-xs text-on-surface-variant">NIS {{ $santri->nis ?? '-' }} · RFID {{ filled($santri->rfid_code) ? 'Terdaftar' : 'Belum ada' }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm font-semibold text-on-surface-variant">{{ ucwords(str_replace('_', ' ', $santri->kamarSantri?->kamar ?? '-')) }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-black uppercase {{ $statusStyle }}">{{ $status }}</span>
                            </td>
                            <td class="px-5 py-4 text-sm text-on-surface-variant">
                                @if($activePermission)
                                    Izin s.d. {{ $activePermission->end_date->format('d/m/Y') }}<br>
                                    <span class="text-xs">{{ $activePermission->reason }}</span>
                                @else
                                    {{ ucfirst($attendance?->method ?? 'Belum dicatat') }}
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <form method="POST" action="{{ route($routePrefix.'.attendance.update', $santri) }}" class="flex min-w-[310px] gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                                    <select name="status" class="input-field py-2 text-sm">
                                        @foreach(['hadir' => 'Hadir', 'izin' => 'Izin', 'ghoib' => 'Ghoib'] as $value => $label)
                                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <input name="notes" value="{{ $attendance?->notes }}" placeholder="Catatan" class="input-field py-2 text-sm">
                                    <button class="rounded-lg bg-primary px-3 text-on-primary"><span class="material-symbols-outlined text-lg">save</span></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-14 text-center text-on-surface-variant">Tidak ada santri yang sesuai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function attendancePage() {
    return {
        rfid: '',
        loading: false,
        message: '',
        success: false,
        async scan() {
            if (!this.rfid) return;
            this.loading = true;
            this.message = '';
            try {
                const response = await fetch(@js(route($routePrefix.'.attendance.scan')), {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token())},
                    body: JSON.stringify({rfid_code: this.rfid, date: @js($date->toDateString())})
                });
                const data = await response.json();
                this.success = response.ok;
                this.message = data.message || 'RFID berhasil diproses.';
                if (response.ok) setTimeout(() => window.location.reload(), 650);
            } catch (error) {
                this.success = false;
                this.message = 'Gagal memproses RFID.';
            } finally {
                this.rfid = '';
                this.loading = false;
                this.$refs.rfid.focus();
            }
        }
    }
}
</script>
@endpush
