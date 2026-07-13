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
        <section class="grid grid-cols-2 gap-3">
            <a href="{{ route('santri.tarbiyah.index', ['mode' => 'monthly', 'class_level' => $classLevel, 'month' => $month]) }}" class="rounded-xl px-4 py-4 text-center text-xs font-extrabold {{ $mode === 'monthly' ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface-variant' }}">
                Lihat Nilai Bulanan
            </a>
            <a href="{{ route('santri.tarbiyah.index', ['mode' => 'semester', 'class_level' => $classLevel]) }}" class="rounded-xl px-4 py-4 text-center text-xs font-extrabold {{ $mode === 'semester' ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface-variant' }}">
                Lihat Nilai Semester
            </a>
        </section>

        @if($mode === 'semester')
            <form method="GET" class="rounded-xl bg-surface-container-lowest p-4 shadow-sm">
                <input type="hidden" name="mode" value="semester">
                <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Filter Kelas</label>
                <select name="class_level" class="input-field w-full" onchange="this.form.submit()">
                    @foreach($classLevels as $level)
                        <option value="{{ $level }}" @selected($classLevel === $level)>{{ $level }}</option>
                    @endforeach
                </select>
            </form>

            <section class="rounded-xl bg-primary p-4 sm:p-5 text-on-primary shadow-xl shadow-primary/10">
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
                <h2 class="font-headline text-lg font-extrabold text-on-surface">Nilai Semester</h2>
                @forelse($subjects as $subject)
                    @php $subjectGrades = $grades->where('subject_id', $subject->id)->keyBy('semester'); @endphp
                    <article class="rounded-xl bg-surface-container-lowest p-5 shadow-sm">
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
                                <p class="mt-1 text-xl font-bold text-primary">{{ isset($subjectGrades[1]) ? rtrim(rtrim($subjectGrades[1]->score, '0'), '.') : '-' }}</p>
                            </div>
                            <div class="rounded-xl bg-surface-container-low p-4">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Semester 2</p>
                                <p class="mt-1 text-xl font-bold text-primary">{{ isset($subjectGrades[2]) ? rtrim(rtrim($subjectGrades[2]->score, '0'), '.') : '-' }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[1.75rem] bg-surface-container-lowest p-10 text-center">
                        <span class="material-symbols-outlined text-6xl text-outline">school</span>
                        <p class="mt-3 text-sm font-bold text-on-surface">Belum ada nilai semester</p>
                    </div>
                @endforelse
            </section>
        @else
            <form method="GET" class="grid gap-3 rounded-xl bg-surface-container-lowest p-4 shadow-sm">
                <input type="hidden" name="mode" value="monthly">
                <div>
                    <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Filter Kelas</label>
                    <select name="class_level" class="input-field w-full">
                        @foreach($classLevels as $level)
                            <option value="{{ $level }}" @selected($classLevel === $level)>{{ $level }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Filter Bulan</label>
                    <input type="month" name="month" value="{{ $month }}" class="input-field w-full">
                </div>
                <button class="btn-primary justify-center"><span class="material-symbols-outlined">filter_alt</span> Tampilkan</button>
            </form>

            <section class="space-y-4">
                <h2 class="font-headline text-lg font-extrabold text-on-surface">Nilai Bulanan</h2>
                @forelse($monthlyExams as $exam)
                    @php
                        $examGrades = $monthlyGrades->where('monthly_exam_id', $exam->id)->keyBy('subject');
                        $complete = collect($monthlySubjects)->every(fn ($subject) => isset($examGrades[$subject]));
                        $total = collect($monthlySubjects)->sum(fn ($subject) => (float) ($examGrades[$subject]->score ?? 0));
                        $point = $total >= 300 ? 10 : ($total > 180 ? 5 : ($total >= 90 ? 3 : ($complete ? -3 : 0)));
                    @endphp
                    <article class="rounded-xl bg-surface-container-lowest p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-headline text-lg font-extrabold text-on-surface">{{ $exam->name }}</h3>
                                <p class="mt-1 text-xs text-on-surface-variant">{{ $exam->exam_date?->format('d M Y') }} | Kelas {{ $classLevel }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $point >= 0 ? 'bg-primary/10 text-primary' : 'bg-error/10 text-error' }}">
                                {{ $complete ? ($point > 0 ? '+'.$point : $point).' poin' : 'Belum lengkap' }}
                            </span>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-2">
                            @foreach($monthlySubjects as $subject)
                                <div class="rounded-xl bg-surface-container-low p-3">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">{{ $subject }}</p>
                                    <p class="mt-1 text-xl font-bold text-primary">{{ isset($examGrades[$subject]) ? rtrim(rtrim($examGrades[$subject]->score, '0'), '.') : '-' }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3 rounded-xl bg-primary/10 px-4 py-3 text-sm font-extrabold text-primary">
                            Total: {{ $complete ? rtrim(rtrim(number_format($total, 2, '.', ''), '0'), '.') : '-' }}
                        </div>
                    </article>
                @empty
                    <div class="rounded-[1.75rem] bg-surface-container-lowest p-10 text-center">
                        <span class="material-symbols-outlined text-6xl text-outline">calendar_month</span>
                        <p class="mt-3 text-sm font-bold text-on-surface">Belum ada nilai bulanan</p>
                        <p class="mt-1 text-xs text-on-surface-variant">Pilih bulan lain atau tunggu petugas menginput nilai.</p>
                    </div>
                @endforelse
            </section>
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
