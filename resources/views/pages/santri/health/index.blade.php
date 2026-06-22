@extends('layouts.santri')

@section('title', 'Kesehatan')

@section('content')
@php
    $status = $latestRecord?->status ?? 'sehat';
    $statusLabel = $latestRecord?->status_label ?? 'Sehat';
    $lastUpdate = $latestRecord?->checkup_date?->translatedFormat('d M') ?? '-';
@endphp
<div class="pb-24">
    <header class="w-full pt-12 pb-6 px-5 sticky top-0 z-40 bg-surface/80 backdrop-blur-md">
        <div class="flex items-center gap-3">
            <a href="{{ route('santri.home') }}" class="w-10 h-10 rounded-full hover:bg-surface-container-low flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-primary">arrow_back</span>
            </a>
            <div>
                <h1 class="font-headline font-bold text-xl text-primary">Kesehatan Santri</h1>
                <p class="text-xs text-on-surface-variant">Riwayat medis dan status kesehatan</p>
            </div>
        </div>
    </header>

    <main class="px-5 space-y-9">
        <section class="rounded-2xl bg-primary p-7 text-on-primary shadow-xl shadow-primary/10">
            <div class="border-y border-white/15 py-7">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs font-bold text-primary-fixed/80">Status Kesehatan</p>
                        <h2 class="mt-1 font-headline text-xl font-extrabold">{{ auth()->user()->name }}</h2>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-primary-fixed/80">Lokasi</p>
                        <p class="mt-1 font-headline text-base font-extrabold">{{ $latestRecord?->location ?? 'Klinik Pusat Santri' }}</p>
                    </div>
                </div>
                <div class="mt-8 grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-primary-fixed/70">Berat</p>
                        <p class="mt-1 font-extrabold">
                            @if($latestRecord?->weight_kg)
                                {{ rtrim(rtrim($latestRecord->weight_kg, '0'), '.') }} <span class="text-sm">kg</span>
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-primary-fixed/70">Tinggi</p>
                        <p class="mt-1 font-extrabold">
                            @if($latestRecord?->height_cm)
                                {{ rtrim(rtrim($latestRecord->height_cm, '0'), '.') }} <span class="text-sm">cm</span>
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-primary-fixed/70">Tekanan</p>
                        <p class="mt-1 font-extrabold">{{ $latestRecord?->blood_pressure ?? '-' }}</p>
                    </div>
                </div>
            </div>
            <a href="#" class="bg-primary-fixed-dim text-on-primary-fixed-variant px-3 py-1.5 rounded-lg text-xs font-bold hover:opacity-90 transition-opacity">Lihat Statistik</a>
        </section>

        <div class="inline-flex rounded-lg bg-primary-fixed px-3 py-2 text-sm font-bold text-primary">
            Update Terakhir: {{ $lastUpdate }}
        </div>

        <section class="rounded-[1.75rem] bg-surface-container-low p-7">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-headline text-2xl font-extrabold text-on-surface">Riwayat Medis</h2>
                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $status === 'sehat' ? 'bg-green-100 text-green-700' : ($status === 'sakit' || $status === 'dirawat' ? 'bg-red-100 text-red-700' : 'bg-primary-fixed text-primary') }}">{{ $statusLabel }}</span>
            </div>

            <div class="relative mt-7 space-y-7 pl-9 before:absolute before:left-[11px] before:top-2 before:h-[calc(100%-16px)] before:w-0.5 before:bg-outline-variant/50">
                @forelse($records as $record)
                    <article class="relative">
                        <span class="absolute -left-9 top-1 w-5 h-5 rounded-full {{ $loop->first ? 'bg-primary-fixed ring-4 ring-primary-fixed/30' : 'bg-outline-variant' }}"></span>
                        <p class="text-xs font-extrabold uppercase text-primary">{{ $record->checkup_date->translatedFormat('d F Y') }}</p>
                        <div class="mt-2 flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-headline text-lg font-extrabold text-on-surface">{{ $record->title }}</h3>
                                <p class="mt-1 text-sm leading-relaxed text-on-surface-variant">{{ $record->treatment ?: ($record->complaint ?: $record->notes ?: 'Tidak ada catatan tambahan.') }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-3 py-1 text-[10px] font-bold uppercase {{ $record->status === 'sehat' ? 'bg-green-100 text-green-700' : ($record->status === 'sakit' || $record->status === 'dirawat' ? 'bg-red-100 text-red-700' : 'bg-primary-fixed text-primary') }}">{{ $record->status_label }}</span>
                        </div>
                    </article>
                @empty
                    <div class="py-10 text-center">
                        <span class="material-symbols-outlined text-5xl text-outline">clinical_notes</span>
                        <p class="mt-2 text-sm font-bold text-on-surface">Belum ada riwayat medis</p>
                        <p class="text-xs text-on-surface-variant">Data akan muncul setelah petugas menambahkan pemeriksaan.</p>
                    </div>
                @endforelse
            </div>
        </section>

        @if($records->hasPages())
            <div>{{ $records->links() }}</div>
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
