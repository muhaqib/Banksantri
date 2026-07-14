@extends('layouts.santri')

@section('title', 'Kehadiran')

@section('content')
<div class="pb-24">
    <!-- Header -->
    <header class="w-full pt-12 pb-6 px-5 sticky top-0 z-40 bg-surface/80 backdrop-blur-md">
        <div class="flex items-center gap-3">
            <a href="{{ route('santri.home') }}" class="w-10 h-10 rounded-full hover:bg-surface-container-low flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-primary">arrow_back</span>
            </a>
            <div>
                <h1 class="font-headline font-bold text-xl text-primary">Kehadiran Santri</h1>
                <p class="text-xs text-on-surface-variant">Laporan Absensi Bulanan Anda</p>
            </div>
        </div>
    </header>

    <main class="px-5 space-y-6">
        <!-- Monthly Filter Form -->
        <form method="GET" action="{{ route('santri.attendance.index') }}" class="rounded-xl bg-surface-container-lowest p-4 border border-outline-variant/10 shadow-sm space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Bulan</label>
                    <select name="month" class="input-field w-full text-xs font-semibold" onchange="this.form.submit()">
                        @foreach(range(1, 12) as $m)
                            @if($year > 2026 || $m >= 7)
                                <option value="{{ $m }}" @selected($month == $m)>
                                    {{ Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Tahun</label>
                    <select name="year" class="input-field w-full text-xs font-semibold" onchange="this.form.submit()">
                        @foreach(range(2026, max(2026, now()->year) + 1) as $y)
                            <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <!-- Summary Statistics Card -->
        <section class="grid grid-cols-3 gap-3">
            <div class="bg-surface-container-lowest border border-outline-variant/10 p-3 rounded-xl text-center shadow-sm">
                <span class="text-[10px] font-bold text-green-700 uppercase tracking-wider block">Hadir</span>
                <span class="text-xl font-extrabold text-green-600 mt-1 block">{{ $hadirCount }}</span>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant/10 p-3 rounded-xl text-center shadow-sm">
                <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider block">Izin</span>
                <span class="text-xl font-extrabold text-amber-500 mt-1 block">{{ $izinCount }}</span>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant/10 p-3 rounded-xl text-center shadow-sm">
                <span class="text-[10px] font-bold text-red-700 uppercase tracking-wider block">Ghoib</span>
                <span class="text-xl font-extrabold text-red-600 mt-1 block">{{ $ghoibCount }}</span>
            </div>
        </section>

        <!-- Calendar Container Card -->
        <section class="bg-surface-container-lowest rounded-xl border border-outline-variant/10 shadow-sm p-4 space-y-4">
            <div class="flex items-center justify-between border-b border-outline-variant/10 pb-3">
                <h2 class="font-headline font-bold text-sm text-primary flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">calendar_month</span>
                    {{ $monthName }}
                </h2>
                <div class="flex gap-2 text-[8px] font-bold text-on-surface-variant">
                    <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> H</span>
                    <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> I</span>
                    <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> G</span>
                </div>
            </div>

            <!-- Calendar Grid -->
            <div class="space-y-2">
                <!-- Weekday Headers -->
                <div class="grid grid-cols-7 gap-2 text-center text-[10px] font-bold text-outline uppercase tracking-wider">
                    <div>Min</div>
                    <div>Sen</div>
                    <div>Sel</div>
                    <div>Rab</div>
                    <div>Kam</div>
                    <div>Jum</div>
                    <div>Sab</div>
                </div>

                <!-- Grid Days -->
                <div class="grid grid-cols-7 gap-2">
                    @foreach($calendar as $cell)
                        @if(is_null($cell))
                            <!-- Empty Cell for padding -->
                            <div class="aspect-square"></div>
                        @else
                            @if($cell['status'] === 'hadir')
                                <div class="aspect-square flex flex-col items-center justify-center rounded-xl bg-green-500 text-white font-bold shadow-sm relative">
                                    <span class="text-xs font-extrabold">{{ $cell['day'] }}</span>
                                    <span class="text-[7px] font-medium opacity-90 leading-none mt-0.5">Hadir</span>
                                </div>
                            @elseif($cell['status'] === 'izin')
                                <div class="aspect-square flex flex-col items-center justify-center rounded-xl bg-amber-400 text-amber-950 font-bold shadow-sm relative">
                                    <span class="text-xs font-extrabold">{{ $cell['day'] }}</span>
                                    <span class="text-[7px] font-medium opacity-90 leading-none mt-0.5">Izin</span>
                                </div>
                            @elseif($cell['status'] === 'ghoib')
                                <div class="aspect-square flex flex-col items-center justify-center rounded-xl bg-red-500 text-white font-bold shadow-sm relative">
                                    <span class="text-xs font-extrabold">{{ $cell['day'] }}</span>
                                    <span class="text-[7px] font-medium opacity-90 leading-none mt-0.5">Ghoib</span>
                                </div>
                            @else
                                <div class="aspect-square flex flex-col items-center justify-center rounded-xl bg-surface-container-low text-on-surface-variant/70 border border-outline-variant/5">
                                    <span class="text-xs font-semibold">{{ $cell['day'] }}</span>
                                </div>
                            @endif
                        @endif
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Explanations & Notes -->
        @php
            $daysWithNotes = collect($calendar)->filter(fn($c) => $c && ($c['notes'] || $c['status'] === 'izin'));
        @endphp
        @if($daysWithNotes->isNotEmpty())
            <section class="space-y-3">
                <h3 class="font-headline font-bold text-sm text-on-surface">Catatan & Keterangan</h3>
                <div class="space-y-2">
                    @foreach($daysWithNotes as $item)
                        <div class="bg-surface-container-lowest p-3 rounded-xl border border-outline-variant/10 flex items-start justify-between gap-3 shadow-sm">
                            <div class="min-w-0 flex-1">
                                <span class="text-xs font-bold text-on-surface block">Tanggal {{ Carbon\Carbon::parse($item['date'])->translatedFormat('d F Y') }}</span>
                                <p class="text-xs text-on-surface-variant mt-0.5 break-words">
                                    {{ $item['notes'] ?? ($item['status'] === 'izin' ? 'Santri terdaftar memiliki perizinan aktif' : '-') }}
                                </p>
                             </div>
                             <span class="shrink-0 px-2.5 py-0.5 text-[9px] font-bold rounded-full uppercase
                                 @if($item['status'] === 'hadir') bg-green-50 text-green-700 border border-green-200/50
                                 @elseif($item['status'] === 'izin') bg-amber-50 text-amber-700 border border-amber-200/50
                                 @else bg-red-50 text-red-700 border border-red-200/50
                                 @endif">
                                 {{ $item['status'] }}
                             </span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    <!-- Bottom Nav -->
    <x-santri.bottom-nav />
</div>
@endsection
