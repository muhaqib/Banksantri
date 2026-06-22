@extends('layouts.santri')

@section('title', 'Tarbiyah')

@section('content')
<div class="pb-24">
    <header class="w-full pt-12 pb-6 px-5 sticky top-0 z-40 bg-surface/80 backdrop-blur-md">
        <div class="flex items-center gap-3">
            <a href="{{ route('santri.home') }}" class="w-10 h-10 rounded-full hover:bg-surface-container-low flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-primary">arrow_back</span>
            </a>
            <div>
                <h1 class="font-headline font-bold text-xl text-primary">Nilai Tarbiyah</h1>
                <p class="text-xs text-on-surface-variant">{{ $classLevel ?? 'Belum ada kelas' }}</p>
            </div>
        </div>
    </header>

    <main class="px-5 space-y-6">
        <section class="rounded-2xl bg-primary p-6 text-on-primary shadow-xl shadow-primary/10">
            <p class="text-xs font-bold uppercase tracking-widest text-primary-fixed/80">Rata-rata Nilai</p>
            <div class="mt-5 grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-primary-fixed/70">Semester 1</p>
                    <p class="mt-1 font-headline text-4xl font-extrabold">{{ $semesterAverages[1] ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-primary-fixed/70">Semester 2</p>
                    <p class="mt-1 font-headline text-4xl font-extrabold">{{ $semesterAverages[2] ?? '-' }}</p>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="font-headline text-lg font-extrabold text-on-surface">Daftar Nilai</h2>
            @forelse($subjects as $subject)
                @php
                    $subjectGrades = $grades->where('subject_id', $subject->id)->keyBy('semester');
                @endphp
                <article class="rounded-[1.5rem] bg-surface-container-lowest p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-headline text-lg font-extrabold text-on-surface">{{ $subject->name }}</h3>
                            <p class="mt-1 text-xs text-on-surface-variant">Mata pelajaran kelas {{ $classLevel }}</p>
                        </div>
                        <span class="material-symbols-outlined text-primary">menu_book</span>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-surface-container-low p-4">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Semester 1</p>
                            <p class="mt-1 text-2xl font-extrabold text-primary">{{ isset($subjectGrades[1]) ? rtrim(rtrim($subjectGrades[1]->score, '0'), '.') : '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-surface-container-low p-4">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Semester 2</p>
                            <p class="mt-1 text-2xl font-extrabold text-primary">{{ isset($subjectGrades[2]) ? rtrim(rtrim($subjectGrades[2]->score, '0'), '.') : '-' }}</p>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-[1.75rem] bg-surface-container-lowest p-10 text-center">
                    <span class="material-symbols-outlined text-6xl text-outline">school</span>
                    <p class="mt-3 text-sm font-bold text-on-surface">Belum ada nilai Tarbiyah</p>
                    <p class="mt-1 text-xs text-on-surface-variant">Nilai akan muncul setelah petugas menginput data semester.</p>
                </div>
            @endforelse
        </section>
    </main>

    <x-santri.bottom-nav />
</div>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection
