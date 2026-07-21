@extends('layouts.app')

@section('title', ($mode ?? 'rfid') === 'manual' ? 'Presensi Manual' : 'RFID Presensi')
@section('header-title', ($mode ?? 'rfid') === 'manual' ? 'Presensi Manual' : 'RFID Presensi')

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

    /* Fullscreen Mode Overrides */
    body.is-fullscreen header.sticky,
    body.is-fullscreen aside.fixed,
    body.is-fullscreen header.lg\:hidden {
        display: none !important;
    }

    body.is-fullscreen main {
        margin-left: 0 !important;
        padding-top: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        min-height: 100vh !important;
        background-color: var(--color-surface, #f8fafc);
    }

    body.is-fullscreen main > div {
        padding: 1rem !important;
    }

    /* Minimalist Progress Per Kamar (1-8) on Fullscreen */
    body.is-fullscreen .kamar-progress-section {
        padding: 0.5rem 0.75rem !important;
        border-radius: 0.75rem !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
    }
    body.is-fullscreen .kamar-progress-header {
        padding-bottom: 0.25rem !important;
        margin-bottom: 0.25rem !important;
    }
    body.is-fullscreen .kamar-progress-header h2 {
        font-size: 0.75rem !important;
    }
    body.is-fullscreen .kamar-progress-grid {
        grid-template-columns: repeat(8, minmax(0, 1fr)) !important;
        gap: 0.375rem !important;
    }
    body.is-fullscreen .kamar-card {
        padding: 0.35rem 0.5rem !important;
        border-radius: 0.5rem !important;
    }
    body.is-fullscreen .kamar-card-detail {
        display: none !important;
    }
</style>
@endpush

@section('content')
@php
    $isManualMode = ($mode ?? 'rfid') === 'manual';
    $canScanAttendance = $attendanceWindow['can_scan'] ?? false;
@endphp
<div class="space-y-6" x-data="attendancePage()">
    <!-- Floating Exit Fullscreen Button -->

    <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-semibold text-primary uppercase tracking-wider">Kesiswaan</p>
            <h1 class="font-headline text-2xl font-bold text-primary">{{ $isManualMode ? 'Presensi Manual Santri' : 'RFID Presensi Santri' }}</h1>
            <p class="mt-1 text-xs text-on-surface-variant">
                {{ $isManualMode ? 'Ubah status hadir, izin, atau ghoib santri secara manual berdasarkan tanggal.' : 'Tempelkan kartu RFID santri untuk mencatat kehadiran hari ini.' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <!-- Fullscreen Toggle Button -->
            <button type="button" 
                    @click="toggleFullscreen()" 
                    class="inline-flex items-center gap-2 rounded-xl border border-outline-variant/20 bg-surface-container-lowest px-4 py-2.5 text-xs font-bold text-on-surface shadow-sm transition-all hover:bg-primary/10 hover:border-primary/30 hover:text-primary active:scale-95">
                <span class="material-symbols-outlined text-lg text-primary" x-text="isFullscreen ? 'fullscreen_exit' : 'fullscreen'"></span>
                <span x-text="isFullscreen ? 'Keluar Layar Penuh' : 'Layar Penuh'"></span>
                <kbd x-show="!isFullscreen" class="hidden sm:inline-block rounded bg-surface-container-high px-1.5 py-0.5 text-[10px] font-semibold text-on-surface-variant">ESC</kbd>
            </button>
        </div>
    </header>

    @unless($isManualMode)
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
        <section class="relative flex min-h-[460px] flex-col items-center justify-center overflow-hidden rounded-xl bg-surface-container-lowest border border-outline-variant/10 p-4 sm:p-5 sm:p-5 sm:p-6 xl:col-span-7">
            <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent"></div>
            <div class="relative flex h-56 w-56 items-center justify-center">
                <div class="scan-ring absolute h-full w-full rounded-full border-2 border-primary/10"></div>
                <div class="scan-ring absolute h-4/5 w-4/5 rounded-full border border-primary/20" style="animation-delay: .5s"></div>
                <div class="relative z-10 flex h-28 w-44 rotate-[-4deg] flex-col justify-between rounded-xl bg-gradient-to-br from-primary to-primary-container p-4 shadow-xl transition-transform duration-500 hover:rotate-0">
                    <div class="flex items-start justify-between">
                        <span class="material-symbols-outlined material-symbols-filled text-3xl text-on-primary/30">contactless</span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/10">
                            <span class="material-symbols-outlined text-xs text-on-primary">lock</span>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <div class="h-1.5 w-12 rounded-full bg-white/20"></div>
                        <div class="h-2.5 w-24 rounded-full bg-white/40"></div>
                    </div>
                    <div class="scan-line absolute inset-x-0 h-0.5 bg-primary-fixed opacity-70 shadow-[0_0_10px_#a2f0ee]"></div>
                </div>
            </div>

            <div class="relative z-10 mt-8 w-full max-w-md text-center">
                <h2 class="font-headline text-xl font-bold text-on-surface">Tap RFID Reader</h2>
                <div class="mx-auto mt-3 inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider {{ $canScanAttendance ? 'bg-primary-fixed/40 text-primary' : 'bg-surface-container-high text-on-surface-variant' }}">
                    <span class="material-symbols-outlined text-sm">{{ $canScanAttendance ? 'wifi_tethering' : 'schedule' }}</span>
                    {{ $canScanAttendance ? 'Siap Baca' : 'Belum Siap Baca' }}
                </div>
                <p class="mt-2 text-[10px] font-medium text-on-surface-variant">Absensi RFID dimulai pada 21:00-23:59 WIB.</p>
                <form @submit.prevent="scan" class="mt-5 flex gap-2 rounded-xl bg-surface-container-low p-1.5 border border-outline-variant/10 shadow-sm">
                    <input x-ref="rfid" x-model="rfid" :disabled="!canScan" autofocus autocomplete="off" placeholder="Tempelkan kartu RFID..." class="min-w-0 flex-1 rounded-lg border-none bg-transparent px-3 py-2 text-sm text-on-surface outline-none disabled:cursor-not-allowed disabled:text-on-surface-variant">
                    <button :disabled="loading || !canScan" class="flex h-10 w-12 shrink-0 items-center justify-center rounded-lg bg-primary text-on-primary transition disabled:opacity-50 hover:bg-primary-container">
                        <span class="material-symbols-outlined text-lg" :class="loading && 'animate-spin'" x-text="loading ? 'progress_activity' : 'sensors'"></span>
                    </button>
                </form>
                <p x-show="message" x-cloak x-text="message" :class="success ? 'bg-primary-fixed/40 text-primary' : 'bg-error-container text-on-error-container'" class="mt-3 rounded-lg px-3 py-2.5 text-xs font-semibold"></p>
                <div class="mt-6 flex items-center justify-center gap-5">
                    <div class="flex flex-col items-center">
                        <div class="mb-1.5 flex h-10 w-10 items-center justify-center rounded-full bg-surface-container-low border border-outline-variant/10 {{ $canScanAttendance ? 'text-primary' : 'text-on-surface-variant' }}">
                            <span class="material-symbols-outlined text-lg">wifi_tethering</span>
                        </div>
                        <span class="text-[9px] font-bold uppercase tracking-wider text-on-surface-variant">{{ $canScanAttendance ? 'Siap Baca' : 'Mulai 21:00' }}</span>
                    </div>
                    <div class="h-6 w-px bg-outline-variant/30"></div>
                    <div class="flex flex-col items-center">
                        <div class="mb-1.5 flex h-10 w-10 items-center justify-center rounded-full bg-surface-container-low border border-outline-variant/10 text-on-surface-variant">
                            <span class="material-symbols-outlined text-lg">sync</span>
                        </div>
                        <span class="text-[9px] font-bold uppercase tracking-wider text-on-surface-variant">Real-time Sync</span>
                    </div>
                </div>
            </div>
        </section>
        

        <aside class="xl:col-span-5">
            <div class="h-full rounded-xl bg-surface-container-lowest border border-outline-variant/10 p-5 shadow-sm sm:p-4 sm:p-5">
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="font-headline text-lg font-bold text-on-surface">5 Nama Terakhir</h2>
                    <span class="rounded-full bg-tertiary-container/20 px-2.5 py-0.5 text-[10px] font-bold text-tertiary">LIVE</span>
                </div>
                <div class="space-y-4">
                    @forelse($recentAttendances as $attendance)
                        <div class="flex items-center gap-3.5 {{ $loop->first ? 'animate-slide-in' : '' }}" id="recent-{{ $attendance->santri_id }}">
                            <div class="relative flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-secondary-container font-headline text-sm font-bold text-secondary">
                                @if($attendance->santri?->foto)
                                    <img src="{{ Storage::url($attendance->santri->foto) }}" alt="{{ $attendance->santri->name }}" class="h-full w-full object-cover">
                                @else
                                    {{ str($attendance->santri?->name ?? '?')->substr(0, 1)->upper() }}
                                @endif
                                <div class="absolute -bottom-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full border-2 border-surface-container-lowest bg-primary">
                                    <span class="material-symbols-outlined text-[8px] text-on-primary">check</span>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="truncate text-sm font-semibold leading-tight text-on-surface">{{ $attendance->santri?->name ?? 'Santri' }}</h3>
                                    <span class="shrink-0 rounded-full bg-primary-fixed/20 px-1.5 py-0.5 text-[9px] font-medium text-primary">{{ $attendance->recorded_at?->diffForHumans() }}</span>
                                </div>
                                <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-xs text-on-surface-variant">
                                    <span>NIS: {{ $attendance->santri?->nis ?? '-' }}</span>
                                    <span class="h-0.5 w-0.5 rounded-full bg-outline-variant"></span>
                                    <span class="font-medium text-secondary">{{ ucwords(str_replace('_', ' ', $attendance->kamar)) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl bg-surface-container-low border border-outline-variant/10 p-5 text-center text-xs font-semibold text-on-surface-variant">
                            Belum ada kartu yang tap pada tanggal ini.
                        </div>
                    @endforelse
                </div>
                <div class="mt-8 border-t border-outline-variant/10 pt-6">
                    <div class="flex items-center justify-between text-on-surface-variant">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">group</span>
                            <span class="text-xs font-medium">Total Kehadiran Hari Ini:</span>
                        </div>
                        <span class="text-sm font-extrabold text-primary">{{ $summary['hadir'] }} / {{ max($summary['total'], $summary['hadir']) }}</span>
                    </div>
                    @php $attendancePercent = max($summary['total'], $summary['hadir']) > 0 ? min(100, round(($summary['hadir'] / max($summary['total'], $summary['hadir'])) * 100)) : 0; @endphp
                    <div class="mt-2.5 h-1.5 w-full overflow-hidden rounded-full bg-surface-container">
                        <div class="h-full rounded-full bg-primary" style="width: {{ $attendancePercent }}%"></div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <!-- Progress Presensi Per Kamar (1 - 8) - Static Display (Non-Clickable) -->
    <div class="rounded-xl bg-surface-container-lowest border border-outline-variant/10 p-3.5 sm:p-4 shadow-sm space-y-3 kamar-progress-section select-none">
        <!-- Section Header -->
        <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-outline-variant/10 text-xs kamar-progress-header">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-base text-primary">meeting_room</span>
                <h2 class="font-headline font-bold text-on-surface">Progress Presensi Per Kamar</h2>
            </div>

            <!-- Compact Global Stats -->
            <div class="flex items-center gap-2 font-medium text-[11px]">
                <span class="text-emerald-600">Hadir: <strong>{{ $summary['hadir'] }}</strong></span>
                <span class="text-outline-variant/40">•</span>
                <span class="text-amber-600">Izin: <strong>{{ $summary['izin'] }}</strong></span>
                <span class="text-outline-variant/40">•</span>
                <span class="text-rose-600">Ghoib: <strong>{{ $summary['ghoib'] }}</strong></span>
                <span class="text-outline-variant/40">•</span>
                <span class="text-on-surface-variant">Belum: <strong>{{ $summary['belum'] }}</strong></span>
            </div>
        </div>

        <!-- 8 Rooms Static Cards Grid (Non-Clickable div) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2 kamar-progress-grid">
            @php
                $roomsProgress = $kamarProgress ?? collect(\App\Models\KamarSantri::KAMAR_LIST)->map(function ($kamarKey, $index) use ($santriList, $date) {
                    $santriInKamar = $santriList->filter(fn ($s) => ($s->kamarSantri?->kamar ?? '') === $kamarKey);
                    $total = $santriInKamar->count();
                    $hadir = $santriInKamar->filter(fn ($s) => ($s->attendances->first()?->status) === 'hadir')->count();
                    $izin = $santriInKamar->filter(fn ($s) => ($s->attendances->first()?->status ?? ($s->santriPermissions->isNotEmpty() ? 'izin' : null)) === 'izin')->count();
                    $ghoib = $santriInKamar->filter(fn ($s) => ($s->attendances->first()?->status ?? ($date->isBefore(today()) && $s->santriPermissions->isEmpty() ? 'ghoib' : null)) === 'ghoib')->count();
                    $belum = max(0, $total - ($hadir + $izin + $ghoib));
                    $percentage = $total > 0 ? round(($hadir / $total) * 100) : 0;
                    return [
                        'key' => $kamarKey,
                        'number' => $index + 1,
                        'total' => $total,
                        'hadir' => $hadir,
                        'izin' => $izin,
                        'ghoib' => $ghoib,
                        'belum' => $belum,
                        'percentage' => $percentage,
                    ];
                });
            @endphp

            @foreach($roomsProgress as $item)
                @php
                    $pct = $item['percentage'];
                    $textColor = $pct >= 100 ? 'text-emerald-600' : ($pct >= 50 ? 'text-primary' : ($pct > 0 ? 'text-amber-600' : 'text-on-surface-variant'));
                @endphp

                <div class="flex flex-col justify-between rounded-lg border border-outline-variant/15 p-2 text-left bg-surface shadow-2xs kamar-card">
                    <!-- Top Info: Kamar & Percent -->
                    <div class="flex items-center justify-between gap-1 mb-1">
                        <span class="text-[11px] font-bold text-on-surface truncate">
                            <span class="hidden sm:inline">Kamar </span>{{ $item['number'] }}
                        </span>
                        <span class="text-[10px] font-extrabold {{ $textColor }}">
                            {{ $pct }}%
                        </span>
                    </div>

                    <!-- Micro Progress Bar -->
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-container flex gap-0.5 my-1">
                        @if($item['total'] > 0)
                            @if($item['hadir'] > 0)
                                <div class="h-full bg-emerald-500" style="width: {{ ($item['hadir'] / $item['total']) * 100 }}%" title="Hadir: {{ $item['hadir'] }}"></div>
                            @endif
                            @if($item['izin'] > 0)
                                <div class="h-full bg-amber-500" style="width: {{ ($item['izin'] / $item['total']) * 100 }}%" title="Izin: {{ $item['izin'] }}"></div>
                            @endif
                            @if($item['ghoib'] > 0)
                                <div class="h-full bg-rose-500" style="width: {{ ($item['ghoib'] / $item['total']) * 100 }}%" title="Ghoib: {{ $item['ghoib'] }}"></div>
                            @endif
                        @else
                            <div class="h-full w-full bg-surface-container"></div>
                        @endif
                    </div>

                    <!-- Bottom Count: Hadir/Total -->
                    <div class="flex items-center justify-between text-[10px] text-on-surface-variant font-medium mt-0.5">
                        <span>{{ $item['hadir'] }}/{{ $item['total'] }} <span class="kamar-card-detail">Santri</span></span>
                        @if($item['izin'] > 0 || $item['ghoib'] > 0)
                            <span class="text-[9px] font-bold text-amber-600 kamar-card-detail">
                                @if($item['izin'] > 0)+{{ $item['izin'] }}I @endif
                                @if($item['ghoib'] > 0)+{{ $item['ghoib'] }}G @endif
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endunless

    @if($isManualMode)
    <form method="GET" action="{{ route($routePrefix.'.attendance.manual') }}" class="flex flex-col gap-3.5 rounded-xl bg-surface-container-lowest p-4 shadow-sm border border-outline-variant/10">
        <div class="grid gap-3 md:grid-cols-[200px_1fr_auto]">
            <label class="text-xs font-bold text-on-surface-variant flex flex-col gap-1">
                Tanggal
                <input type="date" name="date" value="{{ $date->toDateString() }}" class="input-field py-2">
            </label>
            <label class="text-xs font-bold text-on-surface-variant flex flex-col gap-1">
                Cari Santri
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Nama atau NIS" class="input-field py-2">
            </label>
            <button class="btn-primary self-end h-[38px]"><span class="material-symbols-outlined text-sm">filter_alt</span> Terapkan</button>
        </div>
        
        <div class="border-t border-outline-variant/10 pt-3 flex flex-col sm:flex-row sm:items-center gap-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-on-surface-variant flex items-center gap-1.5 shrink-0 select-none">
                <span class="material-symbols-outlined text-sm">meeting_room</span> Filter Kamar:
            </span>
            <div class="flex flex-wrap items-center gap-1 overflow-x-auto whitespace-nowrap py-0.5">
                <!-- All Rooms Option -->
                <label class="relative flex items-center cursor-pointer select-none">
                    <input type="radio" name="kamar" value="" onchange="this.form.submit()" @checked(!request('kamar')) class="peer sr-only">
                    <div class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider transition-all bg-surface-container text-on-surface-variant peer-checked:bg-primary peer-checked:text-on-primary hover:bg-surface-container-high active:scale-[0.97]">
                        Semua Kamar
                    </div>
                </label>

                @foreach(range(1, 8) as $roomNum)
                    @php
                        $roomVal = 'kamar_'.$roomNum;
                    @endphp
                    <label class="relative flex items-center cursor-pointer select-none">
                        <input type="radio" name="kamar" value="{{ $roomVal }}" onchange="this.form.submit()" @checked(request('kamar') === $roomVal) class="peer sr-only">
                        <div class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider transition-all bg-surface-container text-on-surface-variant peer-checked:bg-primary peer-checked:text-on-primary hover:bg-surface-container-high active:scale-[0.97]">
                            Kamar {{ $roomNum }}
                        </div>
                    </label>
                @endforeach
            </div>
        </div>
    </form>

    <!-- Main Content Table -->
    <div class="w-full mt-4">
        <form method="POST" action="{{ route($routePrefix.'.attendance.bulk-update') }}" class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm border border-outline-variant/10">
            @csrf
            @method('PUT')
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">
            <div class="flex flex-col gap-3 border-b border-outline-variant/10 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-headline text-base font-bold text-primary">Daftar Absensi Santri</h2>
                    <p class="mt-0.5 text-xs text-on-surface-variant">
                        {{ $date->translatedFormat('d F Y') }} 
                        @if(request('kamar'))
                             · {{ ucwords(str_replace('_', ' ', request('kamar'))) }}
                        @endif
                    </p>
                </div>
                @if($santriList->isNotEmpty())
                    <button class="btn-primary justify-center py-1.5 px-3">
                        <span class="material-symbols-outlined text-sm">save</span>
                        Simpan Semua
                    </button>
                @endif
            </div>
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-surface-container-low/50 text-[10px] uppercase tracking-wider text-on-surface-variant">
                        <tr>
                            <th class="px-4 py-3">Santri</th>
                            <th class="px-4 py-3">Kamar</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Metode / Izin</th>
                            <th class="px-4 py-3">Ubah Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @forelse($santriList as $santri)
                            @php
                                $attendance = $santri->attendances->first();
                                $activePermission = $santri->santriPermissions->first();
                                $status = $attendance?->status ?? ($activePermission ? 'izin' : ($date->isBefore(today()) ? 'ghoib' : 'belum'));
                                $statusStyle = match($status) {
                                    'hadir' => 'bg-green-50 text-green-700 border-green-200/50',
                                    'izin' => 'bg-amber-50 text-amber-700 border-amber-200/50',
                                    'ghoib' => 'bg-red-50 text-red-700 border-red-200/50',
                                    default => 'bg-surface-container text-on-surface-variant border-outline-variant/20',
                                };
                            @endphp
                            <tr id="santri-{{ $santri->id }}" class="hover:bg-surface-container-low/30">
                                <td class="px-4 py-2.5">
                                    <p class="text-sm font-semibold text-on-surface">{{ $santri->name }}</p>
                                    <p class="text-[10px] text-on-surface-variant">NIS {{ $santri->nis ?? '-' }} · RFID {{ filled($santri->rfid_code) ? 'Terdaftar' : 'Belum ada' }}</p>
                                </td>
                                <td class="px-4 py-2.5 text-xs font-semibold text-on-surface-variant">{{ ucwords(str_replace('_', ' ', $santri->kamarSantri?->kamar ?? '-')) }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex rounded-md border px-2 py-0.5 text-[9px] font-bold uppercase {{ $statusStyle }}">{{ $status }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-xs text-on-surface-variant">
                                    @if($activePermission)
                                        Izin s.d. {{ $activePermission->end_date->format('d/m/Y') }}<br>
                                        <span class="text-[10px]">{{ $activePermission->reason }}</span>
                                    @else
                                        {{ ucfirst($attendance?->method ?? 'Belum dicatat') }}
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-2.5 min-w-[390px]">
                                        <input type="hidden" name="attendances[{{ $santri->id }}][santri_id]" value="{{ $santri->id }}">
                                        
                                        <!-- Radio Status Group -->
                                        <div class="flex items-center gap-1 bg-surface-container-low p-1 rounded-lg border border-outline-variant/10">
                                            @foreach(['hadir' => ['Hadir', 'bg-green-50/40 text-green-700/80 peer-checked:bg-green-600 peer-checked:text-white', 'check_circle'], 
                                                      'izin' => ['Izin', 'bg-amber-50/40 text-amber-700/80 peer-checked:bg-amber-500 peer-checked:text-white', 'badge'], 
                                                      'ghoib' => ['Ghoib', 'bg-red-50/40 text-red-700/80 peer-checked:bg-error peer-checked:text-white', 'cancel']] as $value => [$label, $styleClasses, $icon])
                                                <label class="relative flex items-center cursor-pointer select-none">
                                                    <input type="radio" 
                                                           name="attendances[{{ $santri->id }}][status]" 
                                                           value="{{ $value }}" 
                                                           @checked($status === $value)
                                                           class="peer sr-only">
                                                    <div class="flex items-center gap-1 px-2 py-1 rounded-md text-[10px] font-bold uppercase transition-all {{ $styleClasses }} hover:opacity-90 active:scale-[0.97]">
                                                        <span class="material-symbols-outlined text-[12px]" style="font-size: 12px;">{{ $icon }}</span>
                                                        <span>{{ $label }}</span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>

                                        <input name="attendances[{{ $santri->id }}][notes]" value="{{ $attendance?->notes }}" placeholder="Catatan" class="input-field py-1 px-2 text-xs flex-1 max-w-[160px] h-[30px]">
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-xs text-on-surface-variant">Tidak ada santri yang sesuai.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile View (Card List) -->
            <div class="block md:hidden divide-y divide-outline-variant/10">
                @forelse($santriList as $santri)
                    @php
                        $attendance = $santri->attendances->first();
                        $activePermission = $santri->santriPermissions->first();
                        $status = $attendance?->status ?? ($activePermission ? 'izin' : ($date->isBefore(today()) ? 'ghoib' : 'belum'));
                        $statusStyle = match($status) {
                            'hadir' => 'bg-green-50 text-green-700 border-green-200/50',
                            'izin' => 'bg-amber-50 text-amber-700 border-amber-200/50',
                            'ghoib' => 'bg-red-50 text-red-700 border-red-200/50',
                            default => 'bg-surface-container text-on-surface-variant border-outline-variant/20',
                        };
                    @endphp
                    <div class="p-4 space-y-3 bg-surface-container-lowest">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="font-bold text-sm text-on-surface">{{ $santri->name }}</h4>
                                <p class="text-[10px] text-on-surface-variant mt-0.5">
                                    NIS {{ $santri->nis ?? '-' }} · {{ ucwords(str_replace('_', ' ', $santri->kamarSantri?->kamar ?? '-')) }}
                                </p>
                            </div>
                            <span class="inline-flex rounded-md border px-2 py-0.5 text-[8px] font-bold uppercase {{ $statusStyle }}">{{ $status }}</span>
                        </div>

                        @if($activePermission)
                            <div class="bg-amber-50/20 text-amber-800 border border-amber-200/30 p-2 rounded-lg text-[10px] font-medium leading-relaxed">
                                <span class="font-bold">Izin s.d. {{ $activePermission->end_date->format('d/m/Y') }}</span>: {{ $activePermission->reason }}
                            </div>
                        @endif

                        <div class="space-y-2">
                            <input type="hidden" name="attendances[{{ $santri->id }}][santri_id]" value="{{ $santri->id }}">
                            
                            <!-- Radio Status Group (Mobile full width) -->
                            <div class="flex items-center gap-1 bg-surface-container-low p-1 rounded-lg border border-outline-variant/10">
                                @foreach(['hadir' => ['Hadir', 'bg-green-50/40 text-green-700/80 peer-checked:bg-green-600 peer-checked:text-white', 'check_circle'], 
                                          'izin' => ['Izin', 'bg-amber-50/40 text-amber-700/80 peer-checked:bg-amber-500 peer-checked:text-white', 'badge'], 
                                          'ghoib' => ['Ghoib', 'bg-red-50/40 text-red-700/80 peer-checked:bg-error peer-checked:text-white', 'cancel']] as $value => [$label, $styleClasses, $icon])
                                    <label class="relative flex-1 flex items-center justify-center cursor-pointer select-none">
                                        <input type="radio" 
                                               name="attendances[{{ $santri->id }}][status]" 
                                               value="{{ $value }}" 
                                               @checked($status === $value)
                                               class="peer sr-only">
                                        <div class="w-full flex items-center justify-center gap-1 py-2 rounded-md text-[10px] font-black uppercase transition-all {{ $styleClasses }} hover:opacity-90 active:scale-[0.97]">
                                            <span class="material-symbols-outlined text-[12px]" style="font-size: 12px;">{{ $icon }}</span>
                                            <span>{{ $label }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <input name="attendances[{{ $santri->id }}][notes]" value="{{ $attendance?->notes }}" placeholder="Catatan khusus absensi santri..." class="input-field w-full py-1.5 px-3 text-xs h-[36px]">
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center text-xs text-on-surface-variant bg-surface-container-lowest">Tidak ada santri yang sesuai.</div>
                @endforelse
            </div>
        </form>
    </div>
    @endif
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
        canScan: @js($canScanAttendance),
        isFullscreen: false,
        init() {
            const syncFullscreenState = () => {
                const fs = !!(document.fullscreenElement || document.webkitFullscreenElement);
                this.isFullscreen = fs || document.body.classList.contains('is-fullscreen');
                if (!this.isFullscreen) {
                    document.body.classList.remove('is-fullscreen');
                }
            };
            document.addEventListener('fullscreenchange', syncFullscreenState);
            document.addEventListener('webkitfullscreenchange', syncFullscreenState);
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isFullscreen) {
                    this.exitFullscreen();
                }
            });
        },
        toggleFullscreen() {
            if (!this.isFullscreen) {
                this.enterFullscreen();
            } else {
                this.exitFullscreen();
            }
        },
        enterFullscreen() {
            document.body.classList.add('is-fullscreen');
            this.isFullscreen = true;
            if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else if (document.documentElement.webkitRequestFullscreen) {
                document.documentElement.webkitRequestFullscreen();
            }
        },
        exitFullscreen() {
            document.body.classList.remove('is-fullscreen');
            this.isFullscreen = false;
            if (document.fullscreenElement && document.exitFullscreen) {
                document.exitFullscreen().catch(() => {});
            } else if (document.webkitFullscreenElement && document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
        },
        async scan() {
            if (!this.canScan) {
                this.success = false;
                this.message = 'Absensi RFID baru bisa dibaca mulai jam 21:00 sampai 23:59 WIB.';
                return;
            }
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
                if (this.canScan) this.$refs.rfid?.focus();
            }
        }
    }
}
</script>
@endpush
