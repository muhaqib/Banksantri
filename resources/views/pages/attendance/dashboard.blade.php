@extends('layouts.app')

@section('title', 'Dashboard Kehadiran')
@section('header-title', 'Dashboard Kehadiran')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-bold text-primary">Dashboard Kehadiran</p>
            <h1 class="font-headline text-3xl font-black">
                {{ $kamar ? ucwords(str_replace('_', ' ', $kamar)) : 'Dashboard Kehadiran Santri' }}
            </h1>
            <p class="mt-1 text-sm text-on-surface-variant">
                Pantau tren, persentase, rekap kamar, dan santri dengan ghoib terbanyak.
            </p>
        </div>
    </div>

    <form method="GET" class="grid gap-3 rounded-xl bg-surface-container-lowest p-4 shadow-sm md:grid-cols-5">
        <input type="date" name="date" value="{{ $date->toDateString() }}" class="input-field">
        <select name="month" class="input-field">
            @foreach(range(1, 12) as $number)
                <option value="{{ $number }}" @selected($month === $number)>{{ Carbon\Carbon::create(null, $number)->translatedFormat('F') }}</option>
            @endforeach
        </select>
        <input type="number" name="year" value="{{ $year }}" min="2020" max="2100" class="input-field">
        <select name="kamar" class="input-field">
            <option value="">Semua Kamar</option>
            @foreach($kamarList as $room)<option value="{{ $room }}" @selected($kamar === $room)>{{ ucwords(str_replace('_', ' ', $room)) }}</option>@endforeach
        </select>
        <button class="btn-primary"><span class="material-symbols-outlined">filter_alt</span> Terapkan</button>
    </form>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-xl bg-surface-container-lowest p-5 shadow-sm">
            <span class="material-symbols-outlined text-primary">monitoring</span>
            <p class="mt-4 text-3xl font-black text-primary">{{ $attendanceRate }}%</p>
            <p class="text-sm font-semibold text-on-surface-variant">Kehadiran (Bulanan)</p>
        </div>

        <div class="rounded-xl bg-surface-container-lowest p-5 shadow-sm">
            <span class="material-symbols-outlined text-green-600">check_circle</span>
            <p class="mt-4 text-3xl font-black text-green-600">{{ $totals['hadir'] ?? 0 }}</p>
            <p class="text-sm font-semibold text-on-surface-variant">Hadir (Bulanan)</p>
        </div>

        <div class="rounded-xl bg-surface-container-lowest p-5 shadow-sm">
            <span class="material-symbols-outlined text-error">cancel</span>
            <p class="mt-4 text-3xl font-black text-error">{{ $totals['ghoib'] ?? 0 }}</p>
            <p class="text-sm font-semibold text-on-surface-variant">Ghoib (Bulanan)</p>
        </div>

        <div class="rounded-xl bg-surface-container-lowest p-5 shadow-sm">
            <span class="material-symbols-outlined text-amber-600">badge</span>
            <p class="mt-4 text-3xl font-black text-amber-600">{{ $totals['izin'] ?? 0 }}</p>
            <p class="text-sm font-semibold text-on-surface-variant">Total Izin (Bulanan)</p>
        </div>

        <!-- Clickable Active Permissions Card -->
        <div x-data="{ open: false }">
            <div @click="open = true" class="rounded-xl bg-surface-container-lowest p-5 shadow-sm cursor-pointer border border-transparent hover:border-amber-500/30 hover:bg-amber-50/5 transition-all group h-full flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="material-symbols-outlined text-amber-600" style="font-variation-settings: 'FILL' 1;">badge</span>
                    <span class="text-[9px] font-black uppercase tracking-wider text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full group-hover:scale-105 transition-transform">Detail</span>
                </div>
                <div class="mt-4 text-left">
                    <p class="text-3xl font-black text-amber-600">{{ $totalActivePermissions }}</p>
                    <p class="text-xs font-semibold text-on-surface-variant mt-1">Izin Hari Ini ({{ $date->translatedFormat('d M Y') }})</p>
                </div>
            </div>

            <!-- Modal -->
            <div x-show="open" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" @keydown.escape.window="open = false; document.body.classList.remove('overflow-hidden')" x-init="$watch('open', value => { if (value) document.body.classList.add('overflow-hidden'); else document.body.classList.remove('overflow-hidden'); })">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="open = false"></div>
                <div class="relative min-h-screen flex items-center justify-center p-4">
                    <div class="w-full max-w-2xl rounded-3xl bg-surface p-6 md:p-8 shadow-2xl" @click.stop>
                        <div class="flex items-center justify-between mb-6 border-b border-outline-variant/10 pb-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-widest text-primary">Daftar Santri Izin</p>
                                <h3 class="font-headline font-extrabold text-2xl text-on-surface mt-1">Izin Aktif Tanggal {{ $date->translatedFormat('d F Y') }}</h3>
                            </div>
                            <button @click="open = false" class="p-2 rounded-xl hover:bg-surface-container">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>
                        <div class="space-y-4 max-h-[50vh] overflow-y-auto pr-1 text-left">
                            @forelse($activePermissions as $permission)
                                <div class="flex items-start justify-between p-4 bg-surface-container-low rounded-xl border border-outline-variant/10 hover:border-primary/20 transition-colors">
                                    <div class="space-y-1">
                                        <p class="font-bold text-on-surface text-base">{{ $permission->santri->name }}</p>
                                        <p class="text-xs text-on-surface-variant font-medium">
                                            NIS: {{ $permission->santri->nis ?? '-' }} | Kamar: {{ ucwords(str_replace('_', ' ', $permission->kamar)) }}
                                        </p>
                                        @if($permission->reason)
                                            <p class="text-xs text-on-surface-variant mt-2 leading-relaxed">
                                                <span class="font-bold">Alasan:</span> {{ $permission->reason }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 whitespace-nowrap">
                                            {{ $permission->start_date->translatedFormat('d M') }} - {{ $permission->end_date->translatedFormat('d M Y') }}
                                        </span>
                                        @if($permission->approved_by)
                                            <p class="text-[10px] text-on-surface-variant mt-2">
                                                Pemberi Izin: <span class="font-semibold">{{ $permission->approved_by }}</span>
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12">
                                    <span class="material-symbols-outlined text-5xl text-primary/30">badge</span>
                                    <p class="mt-3 font-bold text-on-surface">Tidak ada santri yang izin</p>
                                    <p class="text-xs text-on-surface-variant">Tidak ada data perizinan aktif untuk tanggal ini.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-xl bg-surface-container-lowest p-5 shadow-sm">
        <h2 class="font-headline text-lg font-black text-primary">Tren Harian</h2>
        <div class="mt-5 flex h-64 items-end gap-1 overflow-x-auto border-b border-outline-variant/20 pb-2">
            @foreach(range(1, $daysInMonth) as $day)
                @php
                    $records = $daily->get($day, collect())->keyBy('status');
                    $hadir = $records->get('hadir')?->total ?? 0;
                    $izin = $records->get('izin')?->total ?? 0;
                    $ghoib = $records->get('ghoib')?->total ?? 0;
                    $max = max(1, $hadir + $izin + $ghoib);
                @endphp
                <div class="flex min-w-5 flex-1 flex-col items-center justify-end gap-px" title="Tanggal {{ $day }}: Hadir {{ $hadir }}, Izin {{ $izin }}, Ghoib {{ $ghoib }}">
                    <div class="w-full rounded- bg-primary" style="height: {{ ($hadir / $max) * 180 }}px"></div>
                    <div class="w-full bg-yellow-300" style="height: {{ ($izin / $max) * 180 }}px"></div>
                    <div class="w-full bg-red-500" style="height: {{ ($ghoib / $max) * 180 }}px"></div>
                    <span class="mt-1 text-[9px] text-on-surface-variant">{{ $day }}</span>
                </div>
            @endforeach
        </div>
        <div class="mt-3 flex gap-4 text-xs font-bold"><span class="text-primary">Hadir</span><span class="text-amber-600">Izin</span><span class="text-error">Ghoib</span></div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-xl bg-surface-container-lowest p-5 shadow-sm">
            <h2 class="font-headline text-lg font-black text-primary">Rekap Per Kamar</h2>
            <div class="mt-4 space-y-3">
                @forelse($byKamar as $room => $records)
                    @php $roomStatus = $records->keyBy('status'); @endphp
                    <div class="flex items-center justify-between rounded-lg bg-surface-container-low p-3 text-sm">
                        <strong>{{ ucwords(str_replace('_', ' ', $room)) }}</strong>
                        <span class="text-green-800">H {{ $roomStatus->get('hadir')?->total ?? 0 }}</span>
                        <span class="text-yellow-500">I {{ $roomStatus->get('izin')?->total ?? 0 }}</span>
                        <span class="text-red-700">G {{ $roomStatus->get('ghoib')?->total ?? 0 }}</span>
                    </div>
                @empty
                    <p class="py-8 text-center text-on-surface-variant">Belum ada data absensi.</p>
                @endforelse
            </div>
        </div>
        <div class="rounded-xl bg-surface-container-lowest p-5 shadow-sm">
            <h2 class="font-headline text-lg font-black text-primary">Santri Dengan Ghoib Terbanyak</h2>
            <div class="mt-4 divide-y divide-outline-variant/10">
                @foreach($mostAbsent as $index => $santri)
                    <div class="flex items-center gap-3 py-3">
                        <span class="w-7 font-black text-on-surface-variant">{{ $index + 1 }}</span>
                        <div class="flex-1"><p class="font-bold">{{ $santri->name }}</p><p class="text-xs text-on-surface-variant">{{ ucwords(str_replace('_', ' ', $santri->kamarSantri?->kamar ?? '-')) }}</p></div>
                        <strong class="text-error">{{ $santri->ghoib_count }} hari</strong>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
