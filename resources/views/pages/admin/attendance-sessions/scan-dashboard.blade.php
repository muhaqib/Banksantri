@extends('layouts.kiosk')

@section('title', 'Scan RFID - ' . $session->title)

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
        0%, 100% { top: 10%; }
        50% { top: 90%; }
    }

    @keyframes fadeInScale {
        from { opacity: 0; transform: scale(0.95); }
        to   { opacity: 1; transform: scale(1); }
    }

    .result-appear {
        animation: fadeInScale 0.4s ease-out;
    }
</style>
@endpush

@section('content')
<div x-data="scanDashboard()" x-init="init()" class="flex h-screen flex-col">

    <!-- Top Bar -->
    <div class="flex shrink-0 items-center justify-between border-b border-outline-variant/10 bg-surface-container-lowest px-6 py-3 shadow-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.attendance-sessions.index') }}"
                class="flex h-9 w-9 items-center justify-center rounded-xl border border-outline-variant/20 text-on-surface-variant transition hover:bg-surface-container-low">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
            </a>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-primary">Kedatangan Santri</p>
                <h1 class="font-headline text-lg font-bold leading-tight text-on-surface">{{ $session->title }}</h1>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <!-- Realtime Clock -->
            <div class="flex items-center gap-2 rounded-xl bg-surface-container-high px-4 py-2">
                <span class="material-symbols-outlined text-base text-primary">schedule</span>
                <span class="font-mono text-sm font-bold text-on-surface" x-text="currentTime"></span>
            </div>
            <!-- Stat badges -->
            <div class="hidden items-center gap-3 sm:flex">
                <span class="rounded-xl bg-green-100 px-3 py-1.5 text-sm font-black text-green-700">
                    Hadir: <span x-text="stats.hadir"></span>
                </span>
                <span class="rounded-xl bg-amber-100 px-3 py-1.5 text-sm font-black text-amber-700">
                    Terlambat: <span x-text="stats.terlambat"></span>
                </span>
                <span class="rounded-xl bg-surface-container px-3 py-1.5 text-sm font-black text-on-surface-variant">
                    Belum: <span x-text="stats.belum"></span>
                </span>
            </div>
            <!-- Actions -->
            <div class="flex gap-2">
                <a href="{{ route('admin.attendance-sessions.show', $session) }}"
                    class="hidden items-center gap-1.5 rounded-xl border border-outline-variant/20 bg-surface-container-lowest px-3 py-2 text-xs font-semibold text-on-surface transition hover:bg-surface-container-low sm:flex">
                    <span class="material-symbols-outlined text-base text-primary">list_alt</span>
                    Rekap
                </a>
                <form action="{{ route('admin.attendance-sessions.finish', $session) }}" method="POST"
                    onsubmit="return confirm('Akhiri sesi ini? Pemindaian akan ditutup permanen.')">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                        class="flex items-center gap-1.5 rounded-xl bg-error px-3 py-2 text-xs font-semibold text-on-error transition hover:opacity-90">
                        <span class="material-symbols-outlined text-base">stop_circle</span>
                        Akhiri
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Two-Panel Grid (fills remaining height) -->
    <div class="grid min-h-0 flex-1 grid-cols-1 lg:grid-cols-[420px_1fr]">

        <!-- LEFT PANEL: Foto, Status, RFID -->
        <div class="flex flex-col items-center justify-center gap-5 overflow-y-auto border-r border-outline-variant/10 bg-surface-container-lowest px-8 py-10">

            <!-- Waiting state -->
            <template x-if="!showResult">
                <div class="flex w-full max-w-xs flex-col items-center gap-4 text-center">
                    <h2 class="font-headline text-xl font-extrabold text-on-surface">{{ $session->title }}</h2>

                    <div class="relative flex h-52 w-52 items-center justify-center overflow-hidden rounded-2xl border border-outline-variant/10 bg-surface-container shadow-inner">
                        <div class="relative flex h-36 w-36 items-center justify-center rounded-full" style="background-color: #c6f6d5;">
                            <div class="scan-ring absolute inset-0 rounded-full" style="background-color: #38a169;"></div>
                            <div class="relative flex h-24 w-24 items-center justify-center overflow-hidden rounded-full shadow-lg" style="background-color: #2f855a;">
                                <span class="material-symbols-outlined text-5xl text-white">rfid</span>
                                <div class="scan-line absolute inset-x-0 h-0.5 opacity-70" style="background-color: #9ae6b4; box-shadow: 0 0 10px #9ae6b4;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full rounded-2xl border border-outline-variant/10 bg-surface-container px-6 py-4 text-center">
                        <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-on-surface-variant">Status</p>
                        <p class="text-xl font-bold text-on-surface">Menunggu Scan...</p>
                    </div>

                    <form @submit.prevent="submitScan" class="flex w-full gap-2 rounded-2xl border border-outline-variant/10 bg-surface-container p-1.5 shadow-sm">
                        <input x-ref="rfidInput" x-model="rfidCode" :disabled="loading"
                            autofocus autocomplete="off"
                            placeholder="RFID : ..........."
                            class="min-w-0 flex-1 rounded-xl border-none bg-transparent px-4 py-3 text-center text-base font-bold text-on-surface outline-none focus:ring-0 disabled:opacity-50">
                        <button type="submit" :disabled="loading || !rfidCode"
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-white transition disabled:opacity-50 hover:opacity-90"
                            style="background-color: #2f855a;">
                            <span class="material-symbols-outlined text-xl" :class="loading && 'animate-spin'"
                                x-text="loading ? 'progress_activity' : 'sensors'"></span>
                        </button>
                    </form>
                </div>
            </template>

            <!-- Result state -->
            <template x-if="showResult">
                <div class="flex w-full max-w-xs flex-col items-center gap-4 text-center result-appear">
                    <h2 class="font-headline text-xl font-extrabold text-on-surface">{{ $session->title }}</h2>

                    <div class="relative h-52 w-52 overflow-hidden rounded-2xl border-4 shadow-xl"
                        :style="resultData.record.status === 'hadir' ? 'border-color: #2f855a;' : 'border-color: #e53e3e;'">
                        <img :src="resultData.santri.foto || '/images/default-avatar.png'"
                            :alt="resultData.santri.name"
                            class="h-full w-full object-cover">
                    </div>

                    <div class="w-full rounded-2xl px-6 py-4 text-center"
                        :class="resultData.record.status === 'hadir'
                            ? 'bg-green-50 border-2 border-green-300'
                            : 'bg-red-50 border-2 border-red-300'">
                        <p class="mb-1 text-xs font-bold uppercase tracking-wider"
                            :class="resultData.record.status === 'hadir' ? 'text-green-600' : 'text-red-500'"
                            x-text="resultData.record.status === 'hadir' ? 'Berhasil' : 'Peringatan'"></p>
                        <p class="text-2xl font-black"
                            :class="resultData.record.status === 'hadir' ? 'text-green-700' : 'text-red-700'"
                            x-text="'Status : ' + (resultData.record.status === 'hadir' ? 'Hadir' : 'Terlambat')"></p>
                        <div class="mt-2 flex items-center justify-center gap-1.5 text-sm font-semibold"
                            :class="resultData.record.status === 'hadir' ? 'text-green-600' : 'text-red-600'">
                            <span class="material-symbols-outlined text-base"
                                x-text="resultData.record.status === 'hadir' ? 'check_circle' : 'warning'"></span>
                            <span x-text="resultData.record.status === 'hadir' ? 'Silakan masuk.' : 'Mohon lapor ke asrama.'"></span>
                        </div>
                    </div>

                    <!-- RFID Input tetap aktif -->
                    <form @submit.prevent="submitScan" class="flex w-full gap-2 rounded-2xl border border-outline-variant/10 bg-surface-container p-1.5 shadow-sm">
                        <input x-ref="rfidInputResult" x-model="rfidCode" :disabled="loading"
                            autofocus autocomplete="off"
                            placeholder="RFID : ..........."
                            class="min-w-0 flex-1 rounded-xl border-none bg-transparent px-4 py-3 text-center text-base font-bold text-on-surface outline-none focus:ring-0 disabled:opacity-50">
                        <button type="submit" :disabled="loading || !rfidCode"
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-white transition disabled:opacity-50 hover:opacity-90"
                            style="background-color: #2f855a;">
                            <span class="material-symbols-outlined text-xl" :class="loading && 'animate-spin'"
                                x-text="loading ? 'progress_activity' : 'sensors'"></span>
                        </button>
                    </form>

                    <p class="text-xs text-on-surface-variant">
                        Kembali otomatis dalam <span x-text="countdown" class="font-bold text-primary"></span> detik
                        &bull;
                        <button type="button" @click="resetToScanMode()" class="font-semibold text-primary hover:underline">Reset</button>
                    </p>
                </div>
            </template>
        </div>

        <!-- RIGHT PANEL: Greeting & Info -->
        <div class="flex flex-col items-start justify-center overflow-y-auto px-12 py-12 lg:px-16" style="background-color: #f9fafb;">

            <!-- Idle state -->
            <template x-if="!showResult">
                <div class="w-full">
                    <p class="mb-2 text-[10px] font-bold uppercase tracking-widest" style="color: #2f855a;">Selamat Datang</p>
                    <h2 class="mb-8 text-right text-6xl font-medium leading-tight md:text-7xl"
                        style="color: #1a202c; font-family: 'Amiri', serif;">
                        أَهْلاً وَسَهْلاً وَمَرْحَبًا
                    </h2>
                    <div class="space-y-3">
                        <div class="h-2 w-3/4 animate-pulse rounded-full bg-gray-200"></div>
                        <div class="h-2 w-1/2 animate-pulse rounded-full bg-gray-200"></div>
                        <div class="h-2 w-2/3 animate-pulse rounded-full bg-gray-200"></div>
                    </div>
                    <div class="mt-8 grid grid-cols-2 gap-4">
                        <div class="h-20 animate-pulse rounded-2xl bg-gray-200"></div>
                        <div class="h-20 animate-pulse rounded-2xl bg-gray-200"></div>
                    </div>
                </div>
            </template>

            <!-- Result state -->
            <template x-if="showResult">
                <div class="w-full result-appear">
                    <p class="mb-2 text-[10px] font-bold uppercase tracking-widest" style="color: #2f855a;">Selamat Datang</p>
                    <h2 class="mb-6 text-right text-5xl font-medium leading-tight md:text-6xl"
                        style="color: #1a202c; font-family: 'Amiri', serif;">
                        أَهْلاً وَسَهْلاً وَمَرْحَبًا
                    </h2>

                    <h3 class="mb-1 text-4xl font-extrabold leading-tight md:text-5xl"
                        style="color: #1a202c;" x-text="resultData.santri.name"></h3>
                    <p class="mb-6 text-2xl font-semibold"
                        style="color: #2f855a;" x-text="resultData.santri.asal"></p>

                    <div class="mb-6 h-1 w-2/3 rounded-full" style="background-color: #2f855a;"></div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl text-white" style="background-color: #2f855a;">
                                <span class="material-symbols-outlined text-2xl">meeting_room</span>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Kamar</p>
                                <p class="text-xl font-extrabold text-gray-900" x-text="resultData.santri.kamar"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl text-white" style="background-color: #2f855a;">
                                <span class="material-symbols-outlined text-2xl">school</span>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Jenjang</p>
                                <p class="text-xl font-extrabold text-gray-900" x-text="resultData.santri.kelas"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function scanDashboard() {
    return {
        currentTime: '--:--:--',
        rfidCode: '',
        loading: false,
        showResult: false,
        resultData: null,
        countdown: 5,
        countdownInterval: null,
        returnTimeout: null,
        stats: {
            hadir: {{ $session->records()->where('status', 'hadir')->count() }},
            terlambat: {{ $session->records()->where('status', 'terlambat')->count() }},
            belum: {{ \App\Models\User::activeSantri()->whereNotNull('rfid_code')->count() - $session->records()->count() }}
        },

        init() {
            // Realtime clock
            setInterval(() => {
                const now = new Date();
                this.currentTime = now.toLocaleTimeString('id-ID', {
                    hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
                });
            }, 1000);

            // Auto-focus on input
            document.addEventListener('click', (e) => {
                if (e.target.closest('button') || e.target.closest('a') || e.target.closest('form')) return;
                const inp = this.showResult ? this.$refs.rfidInputResult : this.$refs.rfidInput;
                if (inp) inp.focus();
            });
        },

        async submitScan() {
            if (!this.rfidCode.trim()) return;
            const code = this.rfidCode.trim();
            this.rfidCode = '';
            this.loading = true;

            try {
                const response = await fetch(`{{ url('api/attendance-sessions/'.$session->id.'/scan') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ rfid_code: code })
                });

                const data = await response.json();

                if (data.status === 'error') {
                    alert(data.message);
                    this.loading = false;
                    this.$nextTick(() => {
                        const inp = this.showResult ? this.$refs.rfidInputResult : this.$refs.rfidInput;
                        if (inp) inp.focus();
                    });
                    return;
                }

                if (data.status === 'success') {
                    this.resultData = data;
                    this.stats = data.stats;
                    this.showResult = true;
                    this.loading = false;
                    this.startCountdown();
                    this.$nextTick(() => {
                        if (this.$refs.rfidInputResult) this.$refs.rfidInputResult.focus();
                    });
                }
            } catch (error) {
                alert('Terjadi kesalahan koneksi saat mengirim data.');
                this.loading = false;
            }
        },

        startCountdown() {
            this.countdown = 5;
            if (this.countdownInterval) clearInterval(this.countdownInterval);
            if (this.returnTimeout) clearTimeout(this.returnTimeout);

            this.countdownInterval = setInterval(() => {
                this.countdown--;
                if (this.countdown <= 0) clearInterval(this.countdownInterval);
            }, 1000);

            this.returnTimeout = setTimeout(() => this.resetToScanMode(), 5000);
        },

        resetToScanMode() {
            if (this.countdownInterval) clearInterval(this.countdownInterval);
            if (this.returnTimeout) clearTimeout(this.returnTimeout);
            this.showResult = false;
            this.resultData = null;
            this.$nextTick(() => {
                if (this.$refs.rfidInput) this.$refs.rfidInput.focus();
            });
        }
    }
}
</script>
@endsection
