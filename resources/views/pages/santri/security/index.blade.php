@extends('layouts.santri')

@section('title', 'Keamanan')

@section('content')
<div class="pb-24">
    <header class="w-full pt-12 pb-6 px-5 sticky top-0 z-40 bg-surface/80 backdrop-blur-md">
        <div class="flex items-center gap-3">
            <a href="{{ route('santri.home') }}" class="w-10 h-10 rounded-full hover:bg-surface-container-low flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-primary">arrow_back</span>
            </a>
            <div>
                <h1 class="font-headline font-bold text-xl text-primary">Keamanan Santri</h1>
                <p class="text-xs text-on-surface-variant">Riwayat pelanggaran dan pengurangan poin</p>
            </div>
        </div>
    </header>

    <main class="px-5 space-y-6">
        <section class="rounded-2xl bg-primary p-6 text-on-primary shadow-xl shadow-primary/10">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-primary-fixed/80">Poin Prestasi Aktif</p>
                    <p class="mt-2 font-headline text-5xl font-extrabold">{{ number_format($netPoint, 0, ',', '.') }}</p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl">local_police</span>
                </div>
            </div>
            <div class="mt-6 grid grid-cols-2 gap-3 border-t border-white/15 pt-5">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-primary-fixed/70">Prestasi</p>
                    <p class="font-bold">{{ number_format($prestasiPoint, 0, ',', '.') }} poin</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-primary-fixed/70">Pengurangan</p>
                    <p class="font-bold">-{{ number_format($deductionPoint, 0, ',', '.') }} poin</p>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-center justify-between px-1">
                <h2 class="font-headline text-lg font-extrabold text-on-surface">Riwayat Pelanggaran</h2>
                <span class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">{{ $violations->total() }} Data</span>
            </div>

            @forelse($violations as $violation)
                <article class="rounded-[1.5rem] bg-surface-container-lowest p-5 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-700 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined">gpp_bad</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-headline text-lg font-extrabold text-on-surface">{{ $violation->jenis_pelanggaran }}</h3>
                                    <p class="mt-1 text-xs font-semibold text-on-surface-variant">{{ $violation->waktu->translatedFormat('d F Y H:i') }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700">-{{ $violation->pengurangan_point }}</span>
                            </div>
                            @if($violation->keterangan)
                                <p class="mt-3 text-sm leading-relaxed text-on-surface-variant">{{ $violation->keterangan }}</p>
                            @endif
                            <p class="mt-3 text-[10px] font-bold uppercase tracking-widest text-primary">Petugas: {{ $violation->creator?->name ?? '-' }}</p>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-[1.75rem] bg-surface-container-lowest p-10 text-center">
                    <span class="material-symbols-outlined text-6xl text-outline">verified_user</span>
                    <p class="mt-3 text-sm font-bold text-on-surface">Belum ada pelanggaran</p>
                    <p class="mt-1 text-xs text-on-surface-variant">Riwayat keamanan akan tampil jika petugas mencatat pelanggaran.</p>
                </div>
            @endforelse
        </section>

        @if($violations->hasPages())
            <div>{{ $violations->links() }}</div>
        @endif
    </main>

    <x-santri.bottom-nav />
</div>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection
