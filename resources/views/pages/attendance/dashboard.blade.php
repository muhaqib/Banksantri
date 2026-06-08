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

    <form method="GET" class="grid gap-3 rounded-xl bg-surface-container-lowest p-4 shadow-sm md:grid-cols-4">
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

    <div class="grid gap-4 md:grid-cols-4">
        @foreach([
            ['Kehadiran', $attendanceRate.'%', 'monitoring', 'text-primary'],
            ['Hadir', $totals['hadir'] ?? 0, 'check_circle', 'text-green-600'],
            ['Izin', $totals['izin'] ?? 0, 'badge', 'text-amber-600'],
            ['Ghoib', $totals['ghoib'] ?? 0, 'cancel', 'text-error'],
        ] as [$label, $value, $icon, $color])
            <div class="rounded-xl bg-surface-container-lowest p-5 shadow-sm">
                <span class="material-symbols-outlined {{ $color }}">{{ $icon }}</span>
                <p class="mt-4 text-3xl font-black {{ $color }}">{{ $value }}</p>
                <p class="text-sm font-semibold text-on-surface-variant">{{ $label }}</p>
            </div>
        @endforeach
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
                    <div class="w-full rounded-t bg-green-500" style="height: {{ ($hadir / $max) * 180 }}px"></div>
                    <div class="w-full bg-amber-400" style="height: {{ ($izin / $max) * 180 }}px"></div>
                    <div class="w-full bg-red-500" style="height: {{ ($ghoib / $max) * 180 }}px"></div>
                    <span class="mt-1 text-[9px] text-on-surface-variant">{{ $day }}</span>
                </div>
            @endforeach
        </div>
        <div class="mt-3 flex gap-4 text-xs font-bold"><span class="text-green-600">Hadir</span><span class="text-amber-600">Izin</span><span class="text-error">Ghoib</span></div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-xl bg-surface-container-lowest p-5 shadow-sm">
            <h2 class="font-headline text-lg font-black text-primary">Rekap Per Kamar</h2>
            <div class="mt-4 space-y-3">
                @forelse($byKamar as $room => $records)
                    @php $roomStatus = $records->keyBy('status'); @endphp
                    <div class="flex items-center justify-between rounded-lg bg-surface-container-low p-3 text-sm">
                        <strong>{{ ucwords(str_replace('_', ' ', $room)) }}</strong>
                        <span class="text-green-700">H {{ $roomStatus->get('hadir')?->total ?? 0 }}</span>
                        <span class="text-amber-700">I {{ $roomStatus->get('izin')?->total ?? 0 }}</span>
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
