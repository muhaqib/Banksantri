@extends('layouts.santri')

@section('title', 'Prestasi Santri')

@section('content')
<div class="pb-24">
    <!-- TopAppBar -->
    <header class="w-full pt-4 pb-2 flex items-center justify-between px-5 sticky top-0 z-40 bg-surface">
        <div class="flex items-center gap-3">
            
            <div class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center ring-4 ring-white/30">
                    @if(auth()->user()->foto)
                        <img src="{{ Storage::url(auth()->user()->foto) }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover rounded-full">
                    @else
                        <span class="material-symbols-outlined text-white text-5xl">account_circle</span>
                    @endif
                </div>
            <div>
                <span class="block text-xs font-medium text-on-surface-variant opacity-70">Assalamu'alaikum,</span>
                <h1 class="font-headline text-xl font-bold tracking-tight text-primary">{{ auth()->user()->name ?? 'Santri' }}</h1>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <button class="relative hover:opacity-80 transition-opacity">
                <span class="material-symbols-outlined text-primary">notifications</span>
                <span class="absolute top-0 right-0 w-2 h-2 bg-error rounded-full"></span>
            </button>
        </div>
    </header>

    <main class="px-5 pt-6">
        <!-- Hero Banner -->
        <section class="bg-gradient-to-br from-primary to-primary-container rounded-3xl p-6 text-white mb-8 shadow-xl">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="font-headline text-2xl font-extrabold tracking-tight">Prestasi Santri</h2>
                    <p class="text-primary-fixed text-sm opacity-80 mt-1">Pencapaian Kurikulum Kitab</p>
                </div>
                <div class="bg-white/10 backdrop-blur-md rounded-xl p-2">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">military_tech</span>
                </div>
            </div>
            <div class="flex items-end">
                <span class="text-5xl font-headline font-extrabold">{{ number_format($totalPoin, 0, ',', '.') }}</span>
                <span class="text-primary-fixed text-lg font-bold ml-2 mb-1.5">Poin</span>
            </div>
        </section>

        <!-- Kitab List -->
        <section class="space-y-4">
            <h3 class="font-headline text-lg font-bold px-1 text-on-surface">Daftar Hafalan Kitab</h3>

            @forelse($prestasiList as $prestasi)
                <!-- Kitab Item -->
                <a href="{{ route('santri.prestasi.show', $prestasi) }}" class="block bg-surface-container-lowest rounded-3xl p-4 shadow-sm border border-surface-container flex items-center justify-between gap-4 transition-transform active:scale-[0.98]">
                    <div class="flex gap-4">
                        <div class="w-24 h-24 rounded-2xl overflow-hidden flex-shrink-0 {{ $prestasi->foto_kitab ? '' : 'bg-surface-container-highest flex items-center justify-center' }}">
                            @if($prestasi->foto_kitab)
                                <img src="{{ Storage::url($prestasi->foto_kitab) }}" alt="{{ $prestasi->nama_kitab }}" class="w-full h-full object-cover">
                            @else
                                <span class="material-symbols-outlined text-outline-variant text-4xl">menu_book</span>
                            @endif
                        </div>
                        <div class="flex flex-col justify-center">
                            <h4 class="font-headline font-bold text-lg text-on-surface">{{ $prestasi->nama_kitab }}</h4>
                            <div class="flex items-center gap-1.5 mt-1 text-on-surface-variant">
                                <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                                <p class="text-xs">
                                    @if($prestasi->tanggal_selesai)
                                        {{ $prestasi->tanggal_selesai->format('d F Y') }}
                                    @else
                                        Belum Dimulai
                                    @endif
                                </p>
                            </div>
                            <div class="mt-2 flex items-center gap-1.5">
                                @if($prestasi->status === 'telah_dihafalkan')
                                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                                    <span class="text-xs font-semibold text-primary">Telah Dihafalkan</span>
                                @elseif($prestasi->status === 'sedang_dihafal')
                                    <span class="w-2 h-2 rounded-full bg-tertiary animate-pulse"></span>
                                    <span class="text-xs font-semibold text-tertiary">Sedang Dihafal</span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-outline-variant"></span>
                                    <span class="text-xs font-semibold text-on-surface-variant">Belum Dihafal</span>
                                @endif
                            </div>
                            @if($prestasi->nilai)
                                <div class="mt-1 text-xs font-bold text-primary">{{ $prestasi->nilai }} {{ $prestasi->skor ? "({$prestasi->skor}/100)" : '' }}</div>
                            @endif
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-outline-variant">chevron_right</span>
                </a>
            @empty
                <div class="text-center py-12 bg-surface-container-lowest rounded-3xl">
                    <span class="material-symbols-outlined text-6xl text-outline-variant mb-4">military_tech</span>
                    <p class="text-on-surface-variant font-medium">Belum ada prestasi</p>
                    <p class="text-xs text-on-surface-variant opacity-60 mt-1">Prestasi akan muncul setelah ustadz menambahkan</p>
                </div>
            @endforelse
        </section>
    </main>

    <!-- BottomNavBar -->
    <nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-6 py-4 bg-surface/80 backdrop-blur-xl shadow-[0_-4px_24px_-4px_rgba(25,28,29,0.06)] rounded-t-[1.5rem]">
        <a href="{{ route('santri.home') }}" class="flex flex-col items-center justify-center text-on-surface opacity-60 hover:bg-surface-container-low transition-all p-2 rounded-xl">
            <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 1;">home</span>
            <span class="font-body text-[10px] font-medium uppercase tracking-widest mt-0.5">Beranda</span>
        </a>
        <a href="{{ route('santri.riwayat') }}" class="flex flex-col items-center justify-center text-on-surface opacity-60 hover:bg-surface-container-low transition-all p-2 rounded-xl">
            <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 1;">history</span>
            <span class="font-body text-[10px] font-medium uppercase tracking-widest mt-0.5">Riwayat</span>
        </a>
        <a href="{{ route('santri.prestasi') }}" class="flex flex-col items-center justify-center bg-primary text-on-primary rounded-[0.75rem] px-4 py-1.5 transition-all">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">military_tech</span>
            <span class="font-manrope text-[10px] font-semibold uppercase tracking-widest mt-1">Kompetensi</span>
        </a>
        <a href="{{ route('santri.profile') }}" class="flex flex-col items-center justify-center text-on-surface opacity-60 hover:bg-surface-container-low transition-all p-2 rounded-xl">
            <span class="material-symbols-outlined text-lg">person</span>
            <span class="font-body text-[10px] font-medium uppercase tracking-widest mt-0.5">Profil</span>
        </a>
    </nav>
</div>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection
