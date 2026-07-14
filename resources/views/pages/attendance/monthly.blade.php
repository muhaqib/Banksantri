@extends('layouts.app')

@section('title', 'Rekap Bulanan')
@section('header-title', 'Rekap Bulanan')

@section('content')
<div class="space-y-6" x-data="monthlyAttendance()">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-bold text-primary">Kesiswaan</p>
            <h1 class="font-headline text-2xl font-bold">
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
                @if($year > 2026 || $number >= 7)
                    <option value="{{ $number }}" @selected($month === $number)>{{ Carbon\Carbon::create(null, $number)->translatedFormat('F') }}</option>
                @endif
            @endforeach
        </select>
        <select name="year" class="input-field">
            @foreach(range(2026, max(2026, now()->year) + 1) as $y)
                <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
            @endforeach
        </select>
        <select name="kamar" class="input-field">
            <option value="">Semua Kamar</option>
            @foreach($kamarList as $room)
                <option value="{{ $room }}" @selected($kamar === $room)>{{ ucwords(str_replace('_', ' ', $room)) }}</option>
            @endforeach
        </select>
        <button class="btn-primary"><span class="material-symbols-outlined">filter_alt</span> Terapkan</button>
    </form>

    <section class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm">
        <div class="flex flex-col gap-4 border-b border-outline-variant/10 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="font-headline text-xl font-black text-primary">Rekap Absensi Bulanan</h2>
                <p class="mt-1 text-sm text-on-surface-variant">
                    Menampilkan {{ $monthlySantri->firstItem() ?? 0 }} sampai {{ $monthlySantri->lastItem() ?? 0 }} dari {{ $monthlySantri->total() }} santri
                </p>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full border-separate border-spacing-0 text-left">
                <thead>
                    <tr class="bg-surface-container-low text-xs font-black uppercase text-on-surface">
                        <th class="px-5 py-4">Nama Santri</th>
                        <th class="px-5 py-4 text-center w-[120px]">Hadir</th>
                        <th class="px-5 py-4 text-center w-[120px]">Izin</th>
                        <th class="px-5 py-4 text-center w-[120px]">Ghoib</th>
                        <th class="px-5 py-4 text-center w-[150px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    @forelse($monthlySantri as $santri)
                        @php
                            $statusCounts = $santri->attendances->countBy('status');
                        @endphp
                        <tr class="bg-surface-container-lowest hover:bg-surface-container-low/40">
                            <td class="px-5 py-4">
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
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center justify-center rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-700 border border-green-200/30">
                                    {{ $statusCounts->get('hadir', 0) }} Hari
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center justify-center rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 border border-amber-200/30">
                                    {{ $statusCounts->get('izin', 0) }} Hari
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center justify-center rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700 border border-red-200/30">
                                    {{ $statusCounts->get('ghoib', 0) }} Hari
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <button type="button" @click="openDetail({{ $santri->id }}, {{ $month }}, {{ $year }})" class="btn-secondary py-1.5 px-3 text-xs inline-flex items-center gap-1.5 justify-center shadow-sm cursor-pointer">
                                    <span class="material-symbols-outlined text-sm">calendar_month</span>
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-14 text-center text-on-surface-variant">
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

    <!-- Modal Dialog -->
    <div x-show="isModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="isModalOpen = false"></div>

        <!-- Modal Container -->
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-2xl rounded-2xl bg-surface-container-lowest p-6 shadow-xl border border-outline-variant/10 transition-all transform"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="scale-95 translate-y-4"
                 x-transition:enter-end="scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="scale-100 translate-y-0"
                 x-transition:leave-end="scale-95 translate-y-4">
                
                <!-- Close Button -->
                <button @click="isModalOpen = false" class="absolute top-4 right-4 text-outline hover:text-on-surface p-1.5 hover:bg-surface-container-low rounded-lg transition-colors cursor-pointer">
                    <span class="material-symbols-outlined">close</span>
                </button>

                <!-- Loading State -->
                <div x-show="isLoading" class="flex flex-col items-center justify-center py-20 space-y-4">
                    <div class="w-10 h-10 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-xs text-on-surface-variant font-bold">Memuat data absensi...</p>
                </div>

                <!-- Modal Content -->
                <div x-show="!isLoading" class="space-y-6">
                    <!-- Title -->
                    <div>
                        <p class="text-xs font-bold text-primary uppercase tracking-wider">Detail Kehadiran Santri</p>
                        <h3 class="font-headline text-2xl font-bold text-on-surface mt-1" x-text="santriName"></h3>
                        <p class="text-xs text-on-surface-variant mt-0.5">
                            NIS: <span x-text="santriNis"></span> | Kamar: <span x-text="santriKamar"></span>
                        </p>
                    </div>

                    <!-- Month Navigation -->
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between bg-surface-container-low/50 p-3 rounded-xl border border-outline-variant/5">
                        <div class="flex items-center gap-3">
                            <button @click="fetchMonth(prevMonth, prevYear)" class="btn-secondary p-1.5 rounded-lg flex items-center justify-center hover:bg-surface-container-high transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-base">chevron_left</span>
                            </button>
                            <span class="font-headline font-extrabold text-sm text-primary min-w-[120px] text-center uppercase tracking-wide" x-text="monthName"></span>
                            <button @click="fetchMonth(nextMonth, nextYear)" class="btn-secondary p-1.5 rounded-lg flex items-center justify-center hover:bg-surface-container-high transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-base">chevron_right</span>
                            </button>
                        </div>

                        <!-- Mini Selector Filter -->
                        <div class="flex items-center gap-1.5">
                            <select x-model="month" @change="if (year == 2026 && month < 7) { month = 7; }; fetchMonth(month, year)" class="input-field w-[110px] py-1 text-xs font-semibold">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" x-show="year > 2026 || {{ $m }} >= 7">{{ Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                                @endforeach
                            </select>
                            <select x-model="year" @change="if (year == 2026 && month < 7) { month = 7; }; fetchMonth(month, year)" class="input-field w-[80px] py-1 text-xs font-semibold">
                                @foreach(range(2026, max(2026, now()->year) + 1) as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Statistics Summary -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-green-50/30 border border-green-200/20 p-2.5 rounded-xl text-center shadow-sm">
                            <span class="text-[9px] font-bold text-green-700 uppercase tracking-wider block">Hadir</span>
                            <span class="text-lg font-extrabold text-green-600 mt-0.5 block" x-text="hadirCount + ' Hari'"></span>
                        </div>
                        <div class="bg-amber-50/30 border border-amber-200/20 p-2.5 rounded-xl text-center shadow-sm">
                            <span class="text-[9px] font-bold text-amber-700 uppercase tracking-wider block">Izin</span>
                            <span class="text-lg font-extrabold text-amber-500 mt-0.5 block" x-text="izinCount + ' Hari'"></span>
                        </div>
                        <div class="bg-red-50/30 border border-red-200/20 p-2.5 rounded-xl text-center shadow-sm">
                            <span class="text-[9px] font-bold text-red-700 uppercase tracking-wider block">Ghoib</span>
                            <span class="text-lg font-extrabold text-red-600 mt-0.5 block" x-text="ghoibCount + ' Hari'"></span>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="space-y-3">
                        <div class="grid grid-cols-7 gap-2 text-center text-[10px] font-bold text-outline uppercase tracking-wider">
                            <div>Min</div>
                            <div>Sen</div>
                            <div>Sel</div>
                            <div>Rab</div>
                            <div>Kam</div>
                            <div>Jum</div>
                            <div>Sab</div>
                        </div>

                        <div class="grid grid-cols-7 gap-2">
                            <template x-for="(cell, index) in calendar" :key="index">
                                <div>
                                    <template x-if="cell === null">
                                        <div class="aspect-square bg-surface-container-low/10 rounded-lg"></div>
                                    </template>
                                    <template x-if="cell !== null">
                                        <div>
                                            <template x-if="cell.status === 'hadir'">
                                                <div class="aspect-square flex flex-col items-center justify-center rounded-lg bg-green-500 text-white font-bold shadow-sm relative group cursor-default">
                                                    <span class="text-xs font-extrabold" x-text="cell.day"></span>
                                                    <span class="text-[7px] font-medium opacity-90 leading-none mt-0.5">Hadir</span>
                                                </div>
                                            </template>
                                            <template x-if="cell.status === 'izin'">
                                                <div class="aspect-square flex flex-col items-center justify-center rounded-lg bg-amber-400 text-amber-950 font-bold shadow-sm relative group cursor-default">
                                                    <span class="text-xs font-extrabold" x-text="cell.day"></span>
                                                    <span class="text-[7px] font-medium opacity-90 leading-none mt-0.5">Izin</span>
                                                </div>
                                            </template>
                                            <template x-if="cell.status === 'ghoib'">
                                                <div class="aspect-square flex flex-col items-center justify-center rounded-lg bg-red-500 text-white font-bold shadow-sm relative group cursor-default">
                                                    <span class="text-xs font-extrabold" x-text="cell.day"></span>
                                                    <span class="text-[7px] font-medium opacity-90 leading-none mt-0.5">Ghoib</span>
                                                </div>
                                            </template>
                                            <template x-if="cell.status === 'belum'">
                                                <div class="aspect-square flex flex-col items-center justify-center rounded-lg bg-surface-container-low text-on-surface-variant/60 border border-outline-variant/10">
                                                    <span class="text-xs font-medium" x-text="cell.day"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div class="space-y-2 pt-2 border-t border-outline-variant/10">
                        <h4 class="font-headline font-bold text-xs text-on-surface">Catatan Khusus</h4>
                        <div class="max-h-[140px] overflow-y-auto space-y-1.5 scrollbar-thin">
                            <template x-for="(cell, index) in calendar.filter(c => c && (c.notes || c.status === 'izin'))" :key="index">
                                <div class="bg-surface-container-low/50 p-2.5 rounded-lg flex items-start justify-between gap-3 text-xs border border-outline-variant/5">
                                    <div class="min-w-0 flex-1">
                                        <span class="font-bold text-on-surface" x-text="'Tanggal ' + cell.day"></span>
                                        <p class="text-on-surface-variant mt-0.5 break-words" x-text="cell.notes || 'Santri terdaftar memiliki perizinan aktif'"></p>
                                    </div>
                                    <span class="shrink-0 px-2 py-0.5 text-[8px] font-bold rounded-full uppercase"
                                          :class="{
                                              'bg-green-50 text-green-700': cell.status === 'hadir',
                                              'bg-amber-50 text-amber-700': cell.status === 'izin',
                                              'bg-red-50 text-red-700': cell.status === 'ghoib'
                                          }"
                                          x-text="cell.status">
                                    </span>
                                </div>
                            </template>
                            <template x-if="calendar.filter(c => c && (c.notes || c.status === 'izin')).length === 0">
                                <p class="text-xs text-on-surface-variant/60 italic text-center py-2">Tidak ada catatan khusus pada bulan ini.</p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function monthlyAttendance() {
    return {
        isModalOpen: false,
        isLoading: false,
        routePrefix: '{{ $routePrefix }}',
        selectedSantriId: null,
        month: {{ $month }},
        year: {{ $year }},
        monthName: '',
        santriName: '',
        santriNis: '',
        santriKamar: '',
        calendar: [],
        hadirCount: 0,
        izinCount: 0,
        ghoibCount: 0,
        prevMonth: null,
        prevYear: null,
        nextMonth: null,
        nextYear: null,

        openDetail(santriId, initialMonth, initialYear) {
            this.selectedSantriId = santriId;
            this.isModalOpen = true;
            this.fetchMonth(initialMonth, initialYear);
        },

        async fetchMonth(m, y) {
            this.isLoading = true;
            this.month = parseInt(m);
            this.year = parseInt(y);
            try {
                const response = await fetch(`/${this.routePrefix}/attendance/${this.selectedSantriId}/detail?month=${m}&year=${y}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();
                
                this.santriName = data.santri.name;
                this.santriNis = data.santri.nis || '-';
                this.santriKamar = data.santri.kamar;
                this.calendar = data.calendar;
                this.monthName = data.monthName;
                this.hadirCount = data.hadirCount;
                this.izinCount = data.izinCount;
                this.ghoibCount = data.ghoibCount;
                this.prevMonth = data.prevMonth;
                this.prevYear = data.prevYear;
                this.nextMonth = data.nextMonth;
                this.nextYear = data.nextYear;
            } catch (error) {
                console.error('Error fetching calendar:', error);
            } finally {
                this.isLoading = false;
            }
        }
    }
}
</script>
@endpush
@endsection
