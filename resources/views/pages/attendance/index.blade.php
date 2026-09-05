@extends('layouts.app')

@section('title', ($mode ?? 'rfid') === 'manual' ? 'Presensi Manual' : 'RFID Presensi')
@section('header-title', ($mode ?? 'rfid') === 'manual' ? 'Presensi Manual' : 'RFID Presensi')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Amiri&display=swap" rel="stylesheet">
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

    @keyframes fadeInScale {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    .result-appear {
        animation: fadeInScale 0.4s ease-out;
    }
</style>
@endpush

@section('content')
@php
    $isManualMode = ($mode ?? 'rfid') === 'manual';
    $canScanAttendance = $attendanceWindow['can_scan'] ?? false;
@endphp
<div class="space-y-6" x-data="attendancePage({
    summary: @js($summary),
    kamarProgress: @js($kamarProgress),
    recentAttendances: @js($recentAttendancesFormatted ?? [])
})">
    <!-- Floating Exit Fullscreen Button -->

    <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-semibold text-primary uppercase tracking-wider">Kesiswaan</p>
            <h1 class="font-headline text-2xl font-bold text-primary">{{ $isManualMode ? 'Presensi Manual Santri' : 'RFID Presensi Santri' }}</h1>
            <p class="mt-1 text-xs text-on-surface-variant">
                {{ $isManualMode ? 'Ubah status hadir, izin, atau ghoib santri secara manual berdasarkan tanggal.' : 'Tempelkan kartu RFID santri untuk mencatat kehadiran hari ini tanpa memilih kamar.' }}
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
    {{-- RFID Scan: Two-panel layout mirroring kedatangan design --}}
    <div class="rounded-xl overflow-hidden border border-outline-variant/10 shadow-sm grid grid-cols-1 lg:grid-cols-[400px_1fr]">

        <!-- LEFT: Photo, Status, RFID Input -->
        <div class="flex flex-col items-center justify-center gap-5 bg-surface-container-lowest border-r border-outline-variant/10 px-8 py-10">

            <!-- Idle state -->
            <template x-if="!showResult">
                <div class="flex flex-col items-center text-center gap-4 w-full max-w-xs">
                    <h2 class="font-headline text-xl font-extrabold text-on-surface">RFID Presensi Santri</h2>

                    <div class="relative flex h-48 w-48 items-center justify-center rounded-2xl bg-surface-container shadow-inner border border-outline-variant/10 overflow-hidden">
                        <div class="relative flex h-32 w-32 items-center justify-center rounded-full" style="background-color: #c6f6d5;">
                            <div class="scan-ring absolute inset-0 rounded-full" style="background-color: #38a169;"></div>
                            <div class="relative flex h-20 w-20 items-center justify-center rounded-full shadow-lg overflow-hidden" style="background-color: #2f855a;">
                                <span class="material-symbols-outlined text-4xl text-white">rfid</span>
                                <div class="scan-line absolute inset-x-0 h-0.5 opacity-70" style="background-color: #9ae6b4; box-shadow: 0 0 10px #9ae6b4;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full rounded-2xl bg-surface-container border border-outline-variant/10 py-4 px-6 text-center">
                        <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Status</p>
                        <p class="text-lg font-bold text-on-surface">Menunggu Scan...</p>
                        @if(!$canScanAttendance)
                        <p class="mt-1 text-[10px] text-amber-600 font-semibold">Mulai 21:00 - 23:59 WIB</p>
                        @endif
                    </div>

                    <form @submit.prevent="scan" class="flex w-full gap-2 rounded-2xl bg-surface-container p-1.5 border border-outline-variant/10 shadow-sm">
                        <input x-ref="rfid" x-model="rfid" :disabled="!canScan || loading"
                            autofocus autocomplete="off"
                            placeholder="RFID : ..........."
                            class="min-w-0 flex-1 rounded-xl border-none bg-transparent px-4 py-3 text-base font-bold text-center text-on-surface outline-none focus:ring-0 disabled:opacity-50 disabled:cursor-not-allowed">
                        <button type="submit" :disabled="loading || !rfid || !canScan"
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-white transition disabled:opacity-50"
                            style="background-color: #2f855a;">
                            <span class="material-symbols-outlined text-xl" :class="loading && 'animate-spin'"
                                x-text="loading ? 'progress_activity' : 'sensors'"></span>
                        </button>
                    </form>

                    <p x-show="message" x-cloak x-text="message"
                        :class="success ? 'text-green-600' : 'text-red-600'"
                        class="text-xs font-semibold text-center"></p>
                </div>
            </template>

            <!-- Result state -->
            <template x-if="showResult">
                <div class="flex flex-col items-center text-center gap-4 w-full max-w-xs result-appear">
                    <h2 class="font-headline text-xl font-extrabold text-on-surface">RFID Presensi Santri</h2>

                    <div class="relative h-48 w-48 rounded-2xl overflow-hidden border-4 shadow-xl"
                        :style="resultData.status === 'hadir' ? 'border-color: #2f855a;' : 'border-color: #e53e3e;'">
                        <img :src="resultData.foto_url || '/images/default-avatar.png'"
                            :alt="resultData.name" class="h-full w-full object-cover">
                    </div>

                    <div class="w-full rounded-2xl py-4 px-6 text-center"
                        :class="resultData.status === 'hadir'
                            ? 'bg-green-50 border-2 border-green-300'
                            : 'bg-red-50 border-2 border-red-300'">
                        <p class="text-xs font-bold uppercase tracking-wider mb-1"
                            :class="resultData.status === 'hadir' ? 'text-green-600' : 'text-red-500'"
                            x-text="resultData.status === 'hadir' ? 'Berhasil' : 'Peringatan'"></p>
                        <p class="text-2xl font-black"
                            :class="resultData.status === 'hadir' ? 'text-green-700' : 'text-red-700'"
                            x-text="'Status : ' + (resultData.status === 'hadir' ? 'Hadir' : 'Terlambat')"></p>
                        <div class="mt-2 flex items-center justify-center gap-1.5 text-sm font-semibold"
                            :class="resultData.status === 'hadir' ? 'text-green-600' : 'text-red-600'">
                            <span class="material-symbols-outlined text-base"
                                x-text="resultData.status === 'hadir' ? 'check_circle' : 'warning'"></span>
                            <span x-text="resultData.status === 'hadir' ? 'Silakan masuk.' : 'Mohon lapor ke asrama.'"></span>
                        </div>
                    </div>

                    <form @submit.prevent="scan" class="flex w-full gap-2 rounded-2xl bg-surface-container p-1.5 border border-outline-variant/10 shadow-sm">
                        <input x-ref="rfidResult" x-model="rfid" :disabled="!canScan || loading"
                            autofocus autocomplete="off"
                            placeholder="RFID : ..........."
                            class="min-w-0 flex-1 rounded-xl border-none bg-transparent px-4 py-3 text-base font-bold text-center text-on-surface outline-none focus:ring-0 disabled:opacity-50">
                        <button type="submit" :disabled="loading || !rfid || !canScan"
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-white transition disabled:opacity-50"
                            style="background-color: #2f855a;">
                            <span class="material-symbols-outlined text-xl" :class="loading && 'animate-spin'"
                                x-text="loading ? 'progress_activity' : 'sensors'"></span>
                        </button>
                    </form>

                    <p class="text-xs text-on-surface-variant">
                        Kembali dalam <span x-text="countdown" class="font-bold text-primary"></span> dtk
                        &bull;
                        <button type="button" @click="resetResult()" class="font-semibold text-primary hover:underline">Reset</button>
                    </p>
                </div>
            </template>
        </div>

        <!-- RIGHT: Greeting & Santri Info -->
        <div class="flex flex-col items-start justify-center px-10 py-10 lg:px-12 lg:py-12" style="background-color: #f9fafb;">

            <!-- Idle -->
            <template x-if="!showResult">
                <div class="w-full">
                    <p class="text-[10px] font-bold uppercase tracking-widest mb-2" style="color: #2f855a;">Selamat Datang</p>
                    <h2 class="text-5xl md:text-6xl font-medium leading-tight text-right mb-6" style="color: #1a202c; font-family: 'Amiri', serif;">
                        أَهْلاً وَسَهْلاً وَمَرْحَبًا
                    </h2>
                    <div class="space-y-3">
                        <div class="h-2 w-3/4 rounded-full bg-gray-200 animate-pulse"></div>
                        <div class="h-2 w-1/2 rounded-full bg-gray-200 animate-pulse"></div>
                    </div>
                    <div class="mt-6 grid grid-cols-2 gap-4">
                        <div class="h-14 rounded-2xl bg-gray-200 animate-pulse"></div>
                        <div class="h-14 rounded-2xl bg-gray-200 animate-pulse"></div>
                    </div>
                </div>
            </template>

            <!-- Result -->
            <template x-if="showResult">
                <div class="w-full result-appear">
                    <p class="text-[10px] font-bold uppercase tracking-widest mb-2" style="color: #2f855a;">Selamat Datang</p>
                    <h2 class="text-5xl md:text-6xl font-medium leading-tight text-right mb-6" style="color: #1a202c; font-family: 'Amiri', serif;">
                        أَهْلاً وَسَهْلاً وَمَرْحَبًا
                    </h2>

                    <h3 class="text-3xl md:text-4xl font-extrabold leading-tight mb-1" style="color: #1a202c;" x-text="resultData.name"></h3>
                    <p class="text-xl font-semibold mb-6" style="color: #2f855a;" x-text="resultData.asal"></p>

                    <div class="mb-6 h-1 w-2/3 rounded-full" style="background-color: #2f855a;"></div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center gap-3 rounded-2xl bg-white border border-gray-200 p-4 shadow-sm">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl text-white" style="background-color: #2f855a;">
                                <span class="material-symbols-outlined">meeting_room</span>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Kamar</p>
                                <p class="text-lg font-extrabold text-gray-900" x-text="resultData.kamar"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-2xl bg-white border border-gray-200 p-4 shadow-sm">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl text-white" style="background-color: #2f855a;">
                                <span class="material-symbols-outlined">school</span>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Jenjang</p>
                                <p class="text-lg font-extrabold text-gray-900" x-text="resultData.kelas"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Progress Presensi Per Kamar (1 - 8) - Clickable untuk detail -->
    <div class="rounded-xl bg-surface-container-lowest border border-outline-variant/10 p-3.5 sm:p-4 shadow-sm space-y-3 kamar-progress-section select-none">
        <!-- Section Header -->
        <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-outline-variant/10 text-xs kamar-progress-header">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-base text-primary">meeting_room</span>
                <h2 class="font-headline font-bold text-on-surface">Progress Presensi Per Kamar</h2>
                <span class="hidden sm:inline text-[10px] text-on-surface-variant font-normal">(Klik kamar untuk detail)</span>
            </div>

            <!-- Compact Global Stats -->
            <div class="flex items-center gap-2 font-medium text-[11px]">
                <span class="text-emerald-600">Hadir: <strong x-text="summary.hadir || 0"></strong></span>
                <span class="text-outline-variant/40">•</span>
                <span class="text-amber-600">Izin: <strong x-text="summary.izin || 0"></strong></span>
                <span class="text-outline-variant/40">•</span>
                <span class="text-rose-600">Ghoib: <strong x-text="summary.ghoib || 0"></strong></span>
                <span class="text-outline-variant/40">•</span>
                <span class="text-on-surface-variant">Belum: <strong x-text="summary.belum || 0"></strong></span>
            </div>
        </div>

        <!-- 8 Rooms Clickable Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2 kamar-progress-grid">
            <template x-for="item in kamarProgress" :key="item.key">
                <button type="button"
                    @click="openKamarModal(item)"
                    class="flex flex-col justify-between rounded-lg border border-outline-variant/15 p-2 text-left bg-surface shadow-2xs kamar-card transition-all hover:border-primary/40 hover:shadow-md hover:bg-surface-container-low active:scale-[0.97] cursor-pointer group">
                    <!-- Top Info: Kamar & Percent -->
                    <div class="flex items-center justify-between gap-1 mb-1">
                        <span class="text-[11px] font-bold text-on-surface truncate group-hover:text-primary transition-colors">
                            <span class="hidden sm:inline">Kamar </span><span x-text="item.number"></span>
                        </span>
                        <span class="text-[10px] font-extrabold" 
                              :class="item.percentage >= 100 ? 'text-emerald-600' : (item.percentage >= 50 ? 'text-primary' : (item.percentage > 0 ? 'text-amber-600' : 'text-on-surface-variant'))" 
                              x-text="item.percentage + '%'">
                        </span>
                    </div>

                    <!-- Micro Progress Bar -->
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-container flex gap-0.5 my-1">
                        <template x-if="item.total > 0">
                            <div class="w-full flex h-full gap-0.5">
                                <template x-if="item.hadir > 0">
                                    <div class="h-full bg-emerald-500 transition-all duration-300" :style="'width: ' + ((item.hadir / item.total) * 100) + '%'" :title="'Hadir: ' + item.hadir"></div>
                                </template>
                                <template x-if="item.izin > 0">
                                    <div class="h-full bg-amber-500 transition-all duration-300" :style="'width: ' + ((item.izin / item.total) * 100) + '%'" :title="'Izin: ' + item.izin"></div>
                                </template>
                                <template x-if="item.ghoib > 0">
                                    <div class="h-full bg-rose-500 transition-all duration-300" :style="'width: ' + ((item.ghoib / item.total) * 100) + '%'" :title="'Ghoib: ' + item.ghoib"></div>
                                </template>
                            </div>
                        </template>
                        <template x-if="item.total === 0">
                            <div class="h-full w-full bg-surface-container"></div>
                        </template>
                    </div>

                    <!-- Bottom Count: Hadir/Total + hint -->
                    <div class="flex items-center justify-between text-[10px] text-on-surface-variant font-medium mt-0.5">
                        <span><span x-text="item.hadir"></span>/<span x-text="item.total"></span> <span class="kamar-card-detail">Santri</span></span>
                        <span class="material-symbols-outlined text-[11px] opacity-0 group-hover:opacity-60 transition-opacity kamar-card-detail" style="font-size:11px">open_in_full</span>
                    </div>
                </button>
            </template>
        </div>
    </div>

    <!-- ===== Modal Detail Kamar ===== -->
    <div x-show="kamarModal.open" x-cloak
         class="fixed inset-0 z-50 flex"
         @keydown.escape.window="closeKamarModal()">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
             @click="closeKamarModal()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
        </div>

        <!-- Panel -->
        <div class="relative ml-auto flex h-full w-full max-w-md flex-col bg-surface shadow-2xl"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-x-full"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-full">

            <!-- Panel Header -->
            <div class="flex items-center justify-between gap-3 border-b border-outline-variant/10 px-5 py-4 bg-surface-container-lowest">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                        <span class="material-symbols-outlined text-primary" style="font-size:20px">meeting_room</span>
                    </div>
                    <div>
                        <h3 class="font-headline text-base font-bold text-on-surface" x-text="'Detail ' + (kamarModal.kamar?.label ?? '')"></h3>
                        <p class="text-[11px] text-on-surface-variant">
                            <span class="text-emerald-600 font-semibold" x-text="(kamarModal.kamar?.hadir ?? 0) + ' Hadir'"></span>
                            <span class="mx-1 opacity-40">·</span>
                            <span class="text-amber-600 font-semibold" x-text="(kamarModal.kamar?.izin ?? 0) + ' Izin'"></span>
                            <span class="mx-1 opacity-40">·</span>
                            <span class="text-rose-600 font-semibold" x-text="(kamarModal.kamar?.ghoib ?? 0) + ' Ghoib'"></span>
                            <span class="mx-1 opacity-40">·</span>
                            <span class="text-on-surface-variant font-semibold" x-text="(kamarModal.kamar?.belum ?? 0) + ' Belum'"></span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="closeKamarModal()"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-all">
                    <span class="material-symbols-outlined" style="font-size:20px">close</span>
                </button>
            </div>

            <!-- Tanggal & Progress Bar -->
            <div class="px-5 py-3 bg-surface-container-lowest border-b border-outline-variant/10">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="text-on-surface-variant">{{ $date->translatedFormat('d F Y') }}</span>
                    <span class="font-bold text-primary" x-text="(kamarModal.kamar?.percentage ?? 0) + '% hadir'"></span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-surface-container flex gap-0.5">
                    <template x-if="(kamarModal.kamar?.hadir ?? 0) > 0">
                        <div class="h-full bg-emerald-500 transition-all duration-500 rounded-full"
                             :style="'width:' + ((kamarModal.kamar.hadir / kamarModal.kamar.total) * 100) + '%'"></div>
                    </template>
                    <template x-if="(kamarModal.kamar?.izin ?? 0) > 0">
                        <div class="h-full bg-amber-500 transition-all duration-500 rounded-full"
                             :style="'width:' + ((kamarModal.kamar.izin / kamarModal.kamar.total) * 100) + '%'"></div>
                    </template>
                    <template x-if="(kamarModal.kamar?.ghoib ?? 0) > 0">
                        <div class="h-full bg-rose-500 transition-all duration-500 rounded-full"
                             :style="'width:' + ((kamarModal.kamar.ghoib / kamarModal.kamar.total) * 100) + '%'"></div>
                    </template>
                </div>
            </div>

            <!-- Santri List -->
            <div class="flex-1 overflow-y-auto divide-y divide-outline-variant/10">
                <template x-if="!kamarModal.kamar?.santriDetails?.length">
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <span class="material-symbols-outlined text-4xl text-on-surface-variant/30 mb-2">person_off</span>
                        <p class="text-sm text-on-surface-variant">Belum ada santri di kamar ini.</p>
                    </div>
                </template>

                <template x-for="santri in (kamarModal.kamar?.santriDetails ?? [])" :key="santri.id">
                    <div class="flex items-center gap-3 px-5 py-3 hover:bg-surface-container-low/50 transition-colors">
                        <!-- Foto/Avatar -->
                        <div class="relative shrink-0">
                            <template x-if="santri.foto_url">
                                <img :src="santri.foto_url" :alt="santri.name"
                                     class="h-10 w-10 rounded-full object-cover border-2"
                                     :class="{
                                         'border-emerald-400': santri.status === 'hadir',
                                         'border-amber-400': santri.status === 'izin',
                                         'border-rose-400': santri.status === 'ghoib',
                                         'border-outline-variant/30': santri.status === 'belum'
                                     }">
                            </template>
                            <template x-if="!santri.foto_url">
                                <div class="h-10 w-10 rounded-full border-2 flex items-center justify-center text-sm font-bold text-white"
                                     :class="{
                                         'bg-emerald-500 border-emerald-400': santri.status === 'hadir',
                                         'bg-amber-500 border-amber-400': santri.status === 'izin',
                                         'bg-rose-500 border-rose-400': santri.status === 'ghoib',
                                         'bg-surface-container border-outline-variant/30 !text-on-surface-variant': santri.status === 'belum'
                                     }"
                                     x-text="santri.name.charAt(0).toUpperCase()">
                                </div>
                            </template>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-on-surface truncate" x-text="santri.name"></p>
                            <p class="text-[10px] text-on-surface-variant" x-text="'NIS ' + santri.nis + ' · ' + santri.kelas"></p>
                        </div>

                        <!-- Status Badge -->
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[9px] font-bold uppercase border"
                                  :class="{
                                      'bg-emerald-50 text-emerald-700 border-emerald-200': santri.status === 'hadir',
                                      'bg-amber-50 text-amber-700 border-amber-200': santri.status === 'izin',
                                      'bg-rose-50 text-rose-700 border-rose-200': santri.status === 'ghoib',
                                      'bg-surface-container text-on-surface-variant border-outline-variant/20': santri.status === 'belum'
                                  }">
                                <span class="material-symbols-outlined" style="font-size:10px"
                                      x-text="santri.status === 'hadir' ? 'check_circle' : (santri.status === 'izin' ? 'badge' : (santri.status === 'ghoib' ? 'cancel' : 'schedule'))">
                                </span>
                                <span x-text="santri.status === 'belum' ? 'Belum' : santri.status.charAt(0).toUpperCase() + santri.status.slice(1)"></span>
                            </span>

                            <!-- Tombol Hadir (hanya jika bukan hadir) -->
                            <template x-if="santri.status !== 'hadir'">
                                <button type="button"
                                    @click="markHadir(santri)"
                                    :disabled="santri._loading"
                                    class="flex items-center gap-1 rounded-lg bg-emerald-600 px-2.5 py-1 text-[10px] font-bold text-white shadow-sm transition hover:bg-emerald-700 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed">
                                    <span class="material-symbols-outlined" style="font-size:12px"
                                          :class="santri._loading ? 'animate-spin' : ''"
                                          x-text="santri._loading ? 'progress_activity' : 'check'">
                                    </span>
                                    <span x-show="!santri._loading">Hadir</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Panel Footer -->
            <div class="border-t border-outline-variant/10 px-5 py-3 bg-surface-container-lowest">
                <p class="text-[10px] text-on-surface-variant text-center">
                    Tombol Hadir akan mencatat absensi manual pada
                    <span class="font-semibold text-on-surface">{{ $date->translatedFormat('d F Y') }}</span>
                </p>
            </div>
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

    {{-- ===== Hidden Bulk Action Forms (di LUAR form bulk-update utama) ===== --}}
    @if($santriList->isNotEmpty())
    <form id="form-bulk-hadir" method="POST" action="{{ route($routePrefix.'.attendance.bulk-hadir') }}" class="hidden">
        @csrf
        <input type="hidden" name="date" value="{{ $date->toDateString() }}">
        @if(request('kamar'))
            <input type="hidden" name="kamar" value="{{ request('kamar') }}">
        @endif
    </form>
    <form id="form-bulk-izin" method="POST" action="{{ route($routePrefix.'.attendance.bulk-izin') }}" class="hidden">
        @csrf
        <input type="hidden" name="date" value="{{ $date->toDateString() }}">
        @if(request('kamar'))
            <input type="hidden" name="kamar" value="{{ request('kamar') }}">
        @endif
    </form>
    <form id="form-bulk-ghoib" method="POST" action="{{ route($routePrefix.'.attendance.bulk-ghoib') }}" class="hidden">
        @csrf
        <input type="hidden" name="date" value="{{ $date->toDateString() }}">
        @if(request('kamar'))
            <input type="hidden" name="kamar" value="{{ request('kamar') }}">
        @endif
    </form>
    @endif

    <!-- Main Content Table -->
    <div class="w-full mt-4">
        <form id="form-bulk-update" method="POST" action="{{ route($routePrefix.'.attendance.bulk-update') }}" class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm border border-outline-variant/10">
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
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Hadir Semua --}}
                    <button type="button"
                        onclick="if(confirm('Tandai HADIR semua santri yang belum absen pada tanggal ini?')) document.getElementById('form-bulk-hadir').submit();"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700 active:scale-95">
                        <span class="material-symbols-outlined" style="font-size:15px">check_circle</span>
                        Hadir Semua
                    </button>
                    {{-- Izin Semua --}}
                    <button type="button"
                        onclick="if(confirm('Tandai IZIN semua santri yang belum absen pada tanggal ini?')) document.getElementById('form-bulk-izin').submit();"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-amber-500 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-amber-600 active:scale-95">
                        <span class="material-symbols-outlined" style="font-size:15px">badge</span>
                        Izin Semua
                    </button>
                    {{-- Ghoib Semua --}}
                    <button type="button"
                        onclick="if(confirm('Tandai GHOIB semua santri yang belum absen pada tanggal ini?')) document.getElementById('form-bulk-ghoib').submit();"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-rose-700 active:scale-95">
                        <span class="material-symbols-outlined" style="font-size:15px">cancel</span>
                        Ghoib Semua
                    </button>
                    {{-- Simpan Manual --}}
                    <button type="submit" form="form-bulk-update" class="btn-primary justify-center py-1.5 px-3">
                        <span class="material-symbols-outlined text-sm">save</span>
                        Simpan Semua
                    </button>
                </div>
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
                                $status = $attendance?->status ?? ($activePermission ? 'izin' : 'belum');
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
                        $status = $attendance?->status ?? ($activePermission ? 'izin' : 'belum');
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
function attendancePage(initialData = {}) {
    return {
        rfid: '',
        loading: false,
        message: '',
        success: false,
        canScan: @js($canScanAttendance),
        isFullscreen: false,
        summary: initialData.summary || { total: 0, hadir: 0, izin: 0, ghoib: 0, belum: 0 },
        kamarProgress: initialData.kamarProgress || [],
        recentAttendances: initialData.recentAttendances || [],
        showResult: false,
        resultData: null,
        countdown: 5,
        countdownInterval: null,
        returnTimeout: null,
        get attendancePercent() {
            const maxVal = Math.max(this.summary.total || 0, this.summary.hadir || 0);
            return maxVal > 0 ? Math.min(100, Math.round((this.summary.hadir / maxVal) * 100)) : 0;
        },
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
        // ====== Kamar Modal State & Methods ======
        kamarModal: {
            open: false,
            kamar: null,
        },
        openKamarModal(item) {
            this.kamarModal.kamar = item;
            this.kamarModal.open = true;
            document.body.style.overflow = 'hidden';
        },
        closeKamarModal() {
            this.kamarModal.open = false;
            document.body.style.overflow = '';
        },
        async markHadir(santri) {
            if (santri._loading) return;
            santri._loading = true;
            const prevStatus = santri.status; // simpan status lama sebelum AJAX
            try {
                const res = await fetch(@js(route($routePrefix.'.attendance.update', ['santri' => '__SANTRI_ID__'])).replace('__SANTRI_ID__', santri.id), {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()) },
                    body: JSON.stringify({ date: @js($date->toDateString()), status: 'hadir', notes: '' })
                });
                if (res.ok) {
                    // Update status santri di modal secara reaktif
                    santri.status = 'hadir';
                    // Update counter kamar
                    if (this.kamarModal.kamar) {
                        const k = this.kamarModal.kamar;
                        if (prevStatus === 'ghoib') k.ghoib = Math.max(0, k.ghoib - 1);
                        else if (prevStatus === 'izin') k.izin = Math.max(0, k.izin - 1);
                        else k.belum = Math.max(0, k.belum - 1);
                        k.hadir = (k.hadir ?? 0) + 1;
                        k.percentage = k.total > 0 ? Math.round((k.hadir / k.total) * 100) : 0;
                        // Sync kamarProgress array agar progress bar di grid juga update
                        const idx = this.kamarProgress.findIndex(kp => kp.key === k.key);
                        if (idx !== -1) this.kamarProgress[idx] = { ...k };
                    }
                    // Update global summary
                    if (prevStatus === 'ghoib') this.summary.ghoib = Math.max(0, (this.summary.ghoib ?? 0) - 1);
                    else if (prevStatus === 'izin') this.summary.izin = Math.max(0, (this.summary.izin ?? 0) - 1);
                    else this.summary.belum = Math.max(0, (this.summary.belum ?? 0) - 1);
                    this.summary.hadir = (this.summary.hadir ?? 0) + 1;
                } else {
                    alert('Gagal menghadirkan santri. Silakan coba lagi.');
                }
            } catch (e) {
                alert('Terjadi error koneksi. Silakan coba lagi.');
            } finally {
                santri._loading = false;
            }
        },
        // ====== /Kamar Modal ======
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
                if (response.ok) {
                    if (data.summary) this.summary = data.summary;
                    if (data.recentAttendances) {
                        this.recentAttendances = data.recentAttendances;
                        // Show result card from latest attendance
                        if (data.recentAttendances.length > 0) {
                            const latest = data.recentAttendances[0];
                            this.resultData = {
                                name: latest.name,
                                foto_url: latest.foto_url,
                                kamar: latest.kamar,
                                asal: latest.asal || '-',
                                kelas: latest.kelas || '-',
                                status: 'hadir'
                            };
                            this.showResult = true;
                            if (this.countdownInterval) clearInterval(this.countdownInterval);
                            if (this.returnTimeout) clearTimeout(this.returnTimeout);
                            this.countdown = 5;
                            this.countdownInterval = setInterval(() => {
                                this.countdown--;
                                if (this.countdown <= 0) clearInterval(this.countdownInterval);
                            }, 1000);
                            this.returnTimeout = setTimeout(() => this.resetResult(), 5000);
                            this.$nextTick(() => { if (this.$refs.rfidResult) this.$refs.rfidResult.focus(); });
                        }
                    }
                    if (data.kamarProgress) this.kamarProgress = data.kamarProgress;
                }
            } catch (error) {
                this.success = false;
                this.message = 'Gagal memproses RFID.';
            } finally {
                this.rfid = '';
                this.loading = false;
                if (this.canScan && !this.showResult) this.$refs.rfid?.focus();
            }
        },
        resetResult() {
            if (this.countdownInterval) clearInterval(this.countdownInterval);
            if (this.returnTimeout) clearTimeout(this.returnTimeout);
            this.showResult = false;
            this.resultData = null;
            this.$nextTick(() => { if (this.$refs.rfid) this.$refs.rfid.focus(); });
        }
    }
}
</script>
@endpush
