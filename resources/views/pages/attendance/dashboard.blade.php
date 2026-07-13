@extends('layouts.app')

@section('title', 'Dashboard Kehadiran')
@section('header-title', 'Dashboard Kehadiran')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold text-primary uppercase tracking-wider">Dashboard Kehadiran</p>
            <h1 class="font-headline text-2xl font-bold text-primary">
                {{ $kamar ? ucwords(str_replace('_', ' ', $kamar)) : 'Dashboard Kehadiran Santri' }}
            </h1>
            <p class="mt-0.5 text-xs text-on-surface-variant">
                Pantau tren, persentase, rekap kamar, dan santri dengan ghoib terbanyak secara ringkas.
            </p>
        </div>
    </div>

    <form method="GET" class="grid gap-2.5 rounded-xl bg-surface-container-lowest p-3 shadow-sm border border-outline-variant/10 md:grid-cols-5">
        <input type="date" name="date" value="{{ $date->toDateString() }}" class="input-field py-2.5">
        <select name="month" class="input-field py-2.5">
            @foreach(range(1, 12) as $number)
                <option value="{{ $number }}" @selected($month === $number)>{{ Carbon\Carbon::create(null, $number)->translatedFormat('F') }}</option>
            @endforeach
        </select>
        <input type="number" name="year" value="{{ $year }}" min="2020" max="2100" class="input-field py-2.5">
        <select name="kamar" class="input-field py-2.5">
            <option value="">Semua Kamar</option>
            @foreach($kamarList as $room)<option value="{{ $room }}" @selected($kamar === $room)>{{ ucwords(str_replace('_', ' ', $room)) }}</option>@endforeach
        </select>
        <button class="btn-primary py-2.5"><span class="material-symbols-outlined text-sm">filter_alt</span> Terapkan</button>
    </form>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-xl bg-surface-container-lowest p-4 border border-outline-variant/10 shadow-sm flex flex-col justify-between min-h-[110px]">
            <div class="flex items-center justify-between">
                <span class="material-symbols-outlined text-primary text-lg">monitoring</span>
                <span class="text-[9px] font-bold text-on-surface-variant uppercase tracking-wider">Rate</span>
            </div>
            <div>
                <p class="text-xl font-extrabold text-primary">{{ $attendanceRate }}%</p>
                <p class="text-[10px] font-medium text-on-surface-variant">Kehadiran (Bulanan)</p>
            </div>
        </div>

        <div class="rounded-xl bg-surface-container-lowest p-4 border border-outline-variant/10 shadow-sm flex flex-col justify-between min-h-[110px]">
            <div class="flex items-center justify-between">
                <span class="material-symbols-outlined text-green-600 text-lg">check_circle</span>
                <span class="text-[9px] font-bold text-green-700 uppercase tracking-wider">Hadir</span>
            </div>
            <div>
                <p class="text-xl font-extrabold text-green-600">{{ $totals['hadir'] ?? 0 }}</p>
                <p class="text-[10px] font-medium text-on-surface-variant">Hadir (Bulanan)</p>
            </div>
        </div>

        <div class="rounded-xl bg-surface-container-lowest p-4 border border-outline-variant/10 shadow-sm flex flex-col justify-between min-h-[110px]">
            <div class="flex items-center justify-between">
                <span class="material-symbols-outlined text-error text-lg">cancel</span>
                <span class="text-[9px] font-bold text-error uppercase tracking-wider">Ghoib</span>
            </div>
            <div>
                <p class="text-xl font-extrabold text-error">{{ $totals['ghoib'] ?? 0 }}</p>
                <p class="text-[10px] font-medium text-on-surface-variant">Ghoib (Bulanan)</p>
            </div>
        </div>

        <div class="rounded-xl bg-surface-container-lowest p-4 border border-outline-variant/10 shadow-sm flex flex-col justify-between min-h-[110px]">
            <div class="flex items-center justify-between">
                <span class="material-symbols-outlined text-amber-600 text-lg">badge</span>
                <span class="text-[9px] font-bold text-amber-700 uppercase tracking-wider">Izin</span>
            </div>
            <div>
                <p class="text-xl font-extrabold text-amber-600">{{ $totals['izin'] ?? 0 }}</p>
                <p class="text-[10px] font-medium text-on-surface-variant">Total Izin (Bulanan)</p>
            </div>
        </div>

        <!-- Clickable Active Permissions Card -->
        <div x-data="{ open: false }">
            <div @click="open = true" class="rounded-xl bg-surface-container-lowest p-4 shadow-sm cursor-pointer border border-outline-variant/10 hover:border-amber-500/30 hover:bg-amber-50/5 transition-all group h-full flex flex-col justify-between min-h-[110px]">
                <div class="flex items-center justify-between">
                    <span class="material-symbols-outlined text-amber-600 text-lg" style="font-variation-settings: 'FILL' 1;">badge</span>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-amber-800 bg-amber-50 border border-amber-200/50 px-2 py-0.5 rounded-md group-hover:scale-105 transition-all">Detail</span>
                </div>
                <div>
                    <p class="text-xl font-extrabold text-amber-600">{{ $totalActivePermissions }}</p>
                    <p class="text-[10px] font-medium text-on-surface-variant mt-0.5">Izin Hari Ini ({{ $date->translatedFormat('d M Y') }})</p>
                </div>
            </div>

            <!-- Modal -->
            <div x-show="open" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" @keydown.escape.window="open = false; document.body.classList.remove('overflow-hidden')" x-init="$watch('open', value => { if (value) document.body.classList.add('overflow-hidden'); else document.body.classList.remove('overflow-hidden'); })">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="open = false"></div>
                <div class="relative min-h-screen flex items-center justify-center p-4">
                    <div class="w-full max-w-xl rounded-xl bg-surface p-5 md:p-4 sm:p-5 shadow-2xl border border-outline-variant/10" @click.stop>
                        <div class="flex items-center justify-between mb-5 border-b border-outline-variant/10 pb-3">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-primary">Daftar Santri Izin</p>
                                <h3 class="font-headline font-bold text-lg text-on-surface mt-0.5">Izin Aktif Tanggal {{ $date->translatedFormat('d F Y') }}</h3>
                            </div>
                            <button @click="open = false" class="p-1.5 rounded-lg hover:bg-surface-container">
                                <span class="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>
                        <div class="space-y-3 max-h-[50vh] overflow-y-auto pr-1 text-left">
                            @forelse($activePermissions as $permission)
                                <div class="flex items-start justify-between p-3.5 bg-surface-container-low rounded-xl border border-outline-variant/10 hover:border-primary/20 transition-colors">
                                    <div class="space-y-1">
                                        <p class="font-semibold text-on-surface text-sm">{{ $permission->santri->name }}</p>
                                        <p class="text-[10px] text-on-surface-variant font-medium">
                                            NIS: {{ $permission->santri->nis ?? '-' }} | Kamar: {{ ucwords(str_replace('_', ' ', $permission->kamar)) }}
                                        </p>
                                        @if($permission->reason)
                                            <p class="text-[11px] text-on-surface-variant mt-1.5 leading-relaxed">
                                                <span class="font-semibold text-on-surface">Alasan:</span> {{ $permission->reason }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200/50 whitespace-nowrap">
                                            {{ $permission->start_date->translatedFormat('d M') }} - {{ $permission->end_date->translatedFormat('d M Y') }}
                                        </span>
                                        @if($permission->approved_by)
                                            <p class="text-[9px] text-on-surface-variant mt-1.5">
                                                Oleh: <span class="font-medium">{{ $permission->approved_by }}</span>
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10">
                                    <span class="material-symbols-outlined text-4xl text-primary/30">badge</span>
                                    <p class="mt-2 text-xs font-semibold text-on-surface">Tidak ada santri yang izin</p>
                                    <p class="text-[10px] text-on-surface-variant">Tidak ada data perizinan aktif untuk tanggal ini.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-xl bg-surface-container-lowest p-4 sm:p-5 border border-outline-variant/10 shadow-sm">
        <h2 class="font-headline text-base font-bold text-primary">Tren Harian</h2>
        <div class="mt-4 flex h-60 items-end gap-1 overflow-x-auto border-b border-outline-variant/20 pb-2">
            @foreach(range(1, $daysInMonth) as $day)
                @php
                    $records = $daily->get($day, collect())->keyBy('status');
                    $hadir = $records->get('hadir')?->total ?? 0;
                    $izin = $records->get('izin')?->total ?? 0;
                    $ghoib = $records->get('ghoib')?->total ?? 0;
                    $max = max(1, $hadir + $izin + $ghoib);
                @endphp
                <div class="flex min-w-5 flex-1 flex-col items-center justify-end gap-px" title="Tanggal {{ $day }}: Hadir {{ $hadir }}, Izin {{ $izin }}, Ghoib {{ $ghoib }}">
                    <div class="w-full bg-primary" style="height: {{ ($hadir / $max) * 160 }}px"></div>
                    <div class="w-full bg-amber-400" style="height: {{ ($izin / $max) * 160 }}px"></div>
                    <div class="w-full bg-error" style="height: {{ ($ghoib / $max) * 160 }}px"></div>
                    <span class="mt-1 text-[8px] font-bold text-on-surface-variant">{{ $day }}</span>
                </div>
            @endforeach
        </div>
        <div class="mt-2.5 flex gap-4 text-[10px] font-bold uppercase tracking-wider">
            <span class="text-primary flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-primary"></span>Hadir</span>
            <span class="text-amber-500 flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-amber-400"></span>Izin</span>
            <span class="text-error flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-error"></span>Ghoib</span>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <div class="rounded-xl bg-surface-container-lowest p-4 border border-outline-variant/10 shadow-sm">
            <h2 class="font-headline text-base font-bold text-primary">Rekap Per Kamar</h2>
            <div class="mt-3 space-y-2">
                @forelse($byKamar as $room => $records)
                    @php $roomStatus = $records->keyBy('status'); @endphp
                    <div class="flex items-center justify-between rounded-lg bg-surface-container-low p-2.5 text-xs font-semibold">
                        <strong class="text-on-surface">{{ ucwords(str_replace('_', ' ', $room)) }}</strong>
                        <div class="flex gap-3 text-[10px] font-bold">
                            <span class="text-green-800 bg-green-50 px-2 py-0.5 rounded border border-green-200/50">H {{ $roomStatus->get('hadir')?->total ?? 0 }}</span>
                            <span class="text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200/50">I {{ $roomStatus->get('izin')?->total ?? 0 }}</span>
                            <span class="text-red-700 bg-red-50 px-2 py-0.5 rounded border border-red-200/50">G {{ $roomStatus->get('ghoib')?->total ?? 0 }}</span>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-xs text-on-surface-variant font-medium">Belum ada data absensi.</p>
                @endforelse
            </div>
        </div>
        
        <div class="rounded-xl bg-surface-container-lowest p-4 border border-outline-variant/10 shadow-sm">
            <h2 class="font-headline text-base font-bold text-primary">Santri Dengan Ghoib Terbanyak</h2>
            <div class="mt-3 divide-y divide-outline-variant/10">
                @forelse($mostAbsent as $index => $santri)
                    <div class="flex items-center gap-3 py-2.5">
                        <span class="w-5 text-xs font-black text-on-surface-variant">{{ $index + 1 }}</span>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-on-surface">{{ $santri->name }}</p>
                            <p class="text-[10px] text-on-surface-variant font-medium">{{ ucwords(str_replace('_', ' ', $santri->kamarSantri?->kamar ?? '-')) }}</p>
                        </div>
                        <strong class="text-xs text-error font-bold bg-red-50 px-2.5 py-0.5 border border-red-200/30 rounded-md">{{ $santri->ghoib_count }} hari</strong>
                    </div>
                @empty
                    <p class="py-6 text-center text-xs text-on-surface-variant font-medium">Belum ada data absensi ghoib.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
