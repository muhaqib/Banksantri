@extends('layouts.santri')

@section('title', 'Detail Hafalan - ' . $prestasi->nama_kitab)

@section('content')
<div class="pb-24">
    <!-- TopAppBar -->
    <header class="bg-surface w-full top-0 sticky z-50 flex justify-between items-center px-5 h-16">
        <div class="flex items-center gap-4">
            <a href="{{ route('santri.prestasi') }}" class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-surface-container/50 transition-transform active:scale-95 duration-200">
                <span class="material-symbols-outlined text-primary">arrow_back</span>
            </a>
            <h1 class="font-manrope text-xl font-bold tracking-tight text-primary">Detail Hafalan</h1>
        </div>
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-primary-fixed-dim">
                @if(auth()->user()->foto)
                    <img src="{{ Storage::url(auth()->user()->foto) }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                @else
                    <span class="material-symbols-outlined text-primary">account_circle</span>
                @endif
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section: Featured Kitab Image -->
        <section class="px-5 mt-4">
            <div class="relative w-full aspect-[4/3] rounded-[2rem] overflow-hidden shadow-2xl">
                @if($prestasi->foto_kitab)
                    <img src="{{ Storage::url($prestasi->foto_kitab) }}" alt="{{ $prestasi->nama_kitab }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-primary/20 to-primary-container/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary text-8xl opacity-30">menu_book</span>
                    </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-primary/80 via-transparent to-transparent"></div>
                <div class="absolute bottom-8 left-8">
                    @if($prestasi->isCompleted())
                        <span class="bg-tertiary-container text-on-tertiary-container px-4 py-1 rounded-full text-xs font-bold font-headline uppercase tracking-widest mb-2 inline-block">
                            Achievement Unlocked
                        </span>
                    @endif
                    <h2 class="text-white font-headline text-3xl font-extrabold tracking-tight">{{ $prestasi->nama_kitab }}</h2>
                    @if($prestasi->kategori)
                        <p class="text-primary-fixed font-medium">{{ ucfirst($prestasi->kategori) }} Excellence</p>
                    @else
                        <p class="text-primary-fixed font-medium">Kurikulum Kitab</p>
                    @endif
                </div>
            </div>
        </section>

        <!-- Stats Bento Grid -->
        <section class="px-5 mt-8 grid grid-cols-2 gap-4">
            <!-- Nilai Card -->
            <div class="bg-surface-container-lowest p-6 rounded-[1.5rem] flex flex-col justify-between h-40">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-primary/5 rounded-xl">
                        <span class="material-symbols-outlined text-primary">military_tech</span>
                    </div>
                    <span class="text-[10px] font-bold font-headline text-primary/40 uppercase tracking-widest">Grade</span>
                </div>
                <div>
                    @if($prestasi->nilai)
                        <div class="text-3xl font-headline font-extrabold text-primary">{{ $prestasi->nilai }}</div>
                        @if($prestasi->skor)
                            <div class="text-sm font-semibold text-primary-container">Score: {{ $prestasi->skor }}/100</div>
                        @endif
                    @else
                        <div class="text-2xl font-headline font-bold text-on-surface-variant">-</div>
                        <div class="text-xs text-on-surface-variant">Belum Dinilai</div>
                    @endif
                </div>
            </div>

            <!-- Tanggal Selesai Card -->
            <div class="bg-surface-container-low p-6 rounded-[1.5rem] flex flex-col justify-between h-40">
                <div class="flex justify-between items-start">
                    <div class="p-2 bg-primary/5 rounded-xl">
                        <span class="material-symbols-outlined text-primary">event_available</span>
                    </div>
                    <span class="text-[10px] font-bold font-headline text-primary/40 uppercase tracking-widest">Completed</span>
                </div>
                <div>
                    @if($prestasi->tanggal_selesai)
                        <div class="text-xl font-headline font-bold text-on-surface">{{ $prestasi->tanggal_selesai->format('d M Y') }}</div>
                        @if($prestasi->bulan_tahun_selesai)
                            <div class="text-xs text-on-surface-variant">{{ $prestasi->bulan_tahun_selesai }}</div>
                        @endif
                    @else
                        <div class="text-lg font-bold text-on-surface-variant">Belum Selesai</div>
                        <div class="text-xs text-on-surface-variant">{{ $prestasi->status_text }}</div>
                    @endif
                </div>
            </div>
        </section>

        <!-- Ustadz Section -->
        @if($prestasi->ustadz_pembimbing)
            <section class="px-5 mt-4">
                <div class="bg-surface-container-lowest p-6 rounded-[1.5rem] flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full overflow-hidden bg-surface-container-high flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary text-3xl">person</span>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-[10px] font-bold font-headline text-primary/40 uppercase tracking-widest">Ustadz Pembimbing</h4>
                        <p class="text-lg font-bold text-on-surface font-headline">{{ $prestasi->ustadz_pembimbing }}</p>
                        @if($prestasi->isCompleted())
                            <div class="flex items-center gap-1 text-tertiary">
                                <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">verified</span>
                                <span class="text-xs font-semibold">Certified Muqri'</span>
                            </div>
                        @endif
                    </div>
                    <button class="bg-primary-fixed-dim text-on-primary-fixed-variant p-3 rounded-2xl active:scale-90 transition-all">
                        <span class="material-symbols-outlined">forum</span>
                    </button>
                </div>
            </section>
        @endif

        <!-- Keterangan / Feedback Section -->
        @if($prestasi->catatan_ustadz)
            <section class="px-5 mt-4">
                <div class="bg-primary text-white p-8 rounded-[2rem] relative overflow-hidden">
                    <!-- Decorative background pattern -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="material-symbols-outlined text-primary-fixed">auto_awesome</span>
                            <h3 class="font-headline font-bold uppercase tracking-widest text-xs text-primary-fixed">Catatan Pembimbing</h3>
                        </div>
                        <p class="font-body text-lg leading-relaxed italic opacity-90">
                            "{{ $prestasi->catatan_ustadz }}"
                        </p>
                        @if($prestasi->tags)
                            <div class="mt-6 flex gap-2 flex-wrap">
                                @foreach($prestasi->tags_array as $tag)
                                    <div class="px-3 py-1 bg-white/10 rounded-full text-[10px] font-bold">{{ trim($tag) }}</div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        <!-- Additional Info Section -->
        @if($prestasi->keterangan)
            <section class="px-5 mt-4">
                <div class="bg-surface-container-low p-6 rounded-[1.5rem]">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-primary">info</span>
                        <h3 class="font-headline font-bold text-sm text-on-surface">Keterangan</h3>
                    </div>
                    <p class="text-on-surface-variant text-sm leading-relaxed">{{ $prestasi->keterangan }}</p>
                </div>
            </section>
        @endif

        <!-- Poin & Status Info -->
        <section class="px-5 mt-4">
            <div class="bg-surface-container-lowest p-6 rounded-[1.5rem]">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] font-bold font-headline text-primary/40 uppercase tracking-widest mb-1">Poin Diperoleh</p>
                        <p class="text-2xl font-headline font-extrabold text-primary">{{ number_format($prestasi->poin, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold font-headline text-primary/40 uppercase tracking-widest mb-1">Status</p>
                        @if($prestasi->status === 'telah_dihafalkan')
                            <span class="inline-flex items-center gap-1 text-sm font-bold text-primary">
                                <span class="w-2 h-2 rounded-full bg-primary"></span>
                                {{ $prestasi->status_text }}
                            </span>
                        @elseif($prestasi->status === 'sedang_dihafal')
                            <span class="inline-flex items-center gap-1 text-sm font-bold text-tertiary">
                                <span class="w-2 h-2 rounded-full bg-tertiary animate-pulse"></span>
                                {{ $prestasi->status_text }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-sm font-bold text-on-surface-variant">
                                <span class="w-2 h-2 rounded-full bg-outline-variant"></span>
                                {{ $prestasi->status_text }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <!-- Action Area -->
        <section class="px-5 mt-8 flex flex-col gap-3">
            <button class="w-full py-4 bg-surface-container-high text-primary rounded-xl font-headline font-bold flex items-center justify-center gap-2 active:scale-[0.98] transition-all">
                <span class="material-symbols-outlined">share</span>
                Bagikan ke Orang Tua
            </button>
        </section>
    </main>

    <!-- BottomNavBar -->
    <x-santri.bottom-nav />
</div>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection
