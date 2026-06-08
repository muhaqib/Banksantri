@extends('layouts.app')

@section('title', 'Rekap Bulanan')
@section('header-title', 'Rekap Bulanan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-bold text-primary">Kesiswaan</p>
            <h1 class="font-headline text-3xl font-black">
                {{ $kamar ? ucwords(str_replace('_', ' ', $kamar)) : 'Rekap Bulanan Santri' }}
            </h1>
            <p class="mt-1 text-sm text-on-surface-variant">
                Rekap absensi {{ $monthStart->translatedFormat('F Y') }} dalam tampilan harian per santri.
            </p>
        </div>
    </div>

    <form method="GET" class="grid gap-3 rounded-xl bg-surface-container-lowest p-4 shadow-sm md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_160px_140px_180px_auto]">
        <div class="relative">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-xl">search</span>
            <input name="search" value="{{ $search }}" placeholder="Cari nama atau NIS santri..." class="input-field pl-12">
        </div>
        <select name="month" class="input-field">
            @foreach(range(1, 12) as $number)
                <option value="{{ $number }}" @selected($month === $number)>{{ Carbon\Carbon::create(null, $number)->translatedFormat('F') }}</option>
            @endforeach
        </select>
        <input type="number" name="year" value="{{ $year }}" min="2020" max="2100" class="input-field">
        <select name="kamar" class="input-field">
            <option value="">Semua Kamar</option>
            @foreach($kamarList as $room)
                <option value="{{ $room }}" @selected($kamar === $room)>{{ ucwords(str_replace('_', ' ', $room)) }}</option>
            @endforeach
        </select>
        <button class="btn-primary"><span class="material-symbols-outlined">filter_alt</span> Terapkan</button>
    </form>

    <section class="overflow-hidden rounded-2xl bg-surface-container-lowest shadow-sm">
        <div class="flex flex-col gap-4 border-b border-outline-variant/10 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="font-headline text-xl font-black text-primary">Rekap Absensi Bulanan</h2>
                <p class="mt-1 text-sm text-on-surface-variant">
                    Menampilkan {{ $monthlySantri->firstItem() ?? 0 }} sampai {{ $monthlySantri->lastItem() ?? 0 }} dari {{ $monthlySantri->total() }} santri
                </p>
            </div>
            <div class="flex flex-wrap gap-3 text-xs font-bold text-on-surface-variant">
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-slate-300"></span> Belum Absen</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-[#6f9999]"></span> Hadir</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-yellow-400"></span> Izin</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-red-500"></span> Ghoib</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1180px] border-separate border-spacing-0 text-left">
                <thead>
                    <tr class="bg-surface-container-low text-xs font-black uppercase text-on-surface">
                        <th class="sticky left-0 z-20 w-[330px] bg-surface-container-low px-5 py-4">Nama Santri</th>
                        @foreach(range(1, $daysInMonth) as $day)
                            <th class="px-2 py-4 text-center text-base font-bold">{{ $day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    @forelse($monthlySantri as $santri)
                        @php
                            $attendanceByDay = $santri->attendances->keyBy(fn ($attendance) => $attendance->attendance_date->day);
                            $statusCounts = $santri->attendances->countBy('status');
                        @endphp
                        <tr class="bg-surface-container-lowest hover:bg-surface-container-low/40">
                            <td class="sticky left-0 z-10 bg-inherit px-5 py-4">
                                <div class="flex min-w-0 items-center gap-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                        <span class="material-symbols-outlined">account_circle</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-bold text-on-surface">{{ $santri->name }}</p>
                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] font-semibold text-on-surface-variant">
                                            <span>NIS {{ $santri->nis ?? '-' }}</span>
                                            <span class="h-1 w-1 rounded-full bg-outline-variant"></span>
                                            <span>{{ ucwords(str_replace('_', ' ', $santri->kamarSantri?->kamar ?? '-')) }}</span>
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-1 text-[10px] font-black">
                                            <span class="rounded-full bg-green-50 px-2 py-0.5 text-green-700">H {{ $statusCounts->get('hadir', 0) }}</span>
                                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-amber-700">I {{ $statusCounts->get('izin', 0) }}</span>
                                            <span class="rounded-full bg-red-50 px-2 py-0.5 text-red-700">G {{ $statusCounts->get('ghoib', 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @foreach(range(1, $daysInMonth) as $day)
                                @php
                                    $attendance = $attendanceByDay->get($day);
                                    $status = $attendance?->status ?? 'belum';
                                    $statusLabel = match($status) {
                                        'hadir' => 'Hadir',
                                        'izin' => 'Izin',
                                        'ghoib' => 'Ghoib',
                                        default => 'Belum Absen',
                                    };
                                    $dotClass = match($status) {
                                        'hadir' => 'bg-[#6f9999]',
                                        'izin' => 'bg-yellow-400',
                                        'ghoib' => 'bg-red-500',
                                        default => 'bg-slate-300',
                                    };
                                    $currentDate = $monthStart->copy()->day($day);
                                @endphp
                                <td class="px-2 py-4 text-center">
                                    <span class="mx-auto block h-5 w-5 rounded-full {{ $dotClass }} shadow-sm"
                                          title="{{ $santri->name }} - {{ $currentDate->format('d/m/Y') }}: {{ $statusLabel }}"></span>
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $daysInMonth + 1 }}" class="px-5 py-14 text-center text-on-surface-variant">
                                Tidak ada santri yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($monthlySantri->hasPages())
            <div class="border-t border-outline-variant/10 p-5">
                {{ $monthlySantri->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
