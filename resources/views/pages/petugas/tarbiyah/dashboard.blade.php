@extends('layouts.app')

@section('title', 'Dashboard Nilai Iktibar')
@section('header-title', 'Dashboard Nilai Iktibar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-bold text-primary">Akademik Tarbiyah</p>
            <h1 class="font-headline text-3xl font-black">Dashboard Nilai Iktibar</h1>
            <p class="text-sm text-on-surface-variant">Pantau sebaran total nilai ujian bulanan Tarbiyah per santri.</p>
        </div>
    </div>

    <form method="GET" class="grid gap-3 rounded-xl bg-surface-container-lowest p-4 shadow-sm md:grid-cols-4">
        <select name="class_level" class="input-field">
            <option value="all" @selected($classLevel === 'all')>Semua Kelas</option>
            @foreach($classLevels as $level)
                <option value="{{ $level }}" @selected($classLevel === $level)>{{ $level }}</option>
            @endforeach
        </select>
        <select name="monthly_exam_id" class="input-field md:col-span-2">
            @forelse($monthlyExams as $exam)
                <option value="{{ $exam->id }}" @selected($monthlyExam?->id === $exam->id)>{{ $exam->name }} - {{ $exam->exam_date?->format('d/m/Y') }}</option>
            @empty
                <option value="">Belum ada data iktibar</option>
            @endforelse
        </select>
        <button class="btn-primary"><span class="material-symbols-outlined">filter_alt</span> Tampilkan</button>
    </form>

    <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-xl bg-surface-container-lowest p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Jumlah Murid</p>
            <p class="mt-2 text-3xl font-black text-on-surface">{{ $totalSantri }}</p>
        </div>
        <div class="rounded-xl bg-surface-container-lowest p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Nilai Lengkap</p>
            <p class="mt-2 text-3xl font-black text-primary">{{ $recordedSantri }}</p>
        </div>
        <div class="rounded-xl bg-surface-container-lowest p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Belum Lengkap</p>
            <p class="mt-2 text-3xl font-black text-error">{{ $unrecordedSantri }}</p>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-12">
        <div class="rounded-xl bg-surface-container-lowest p-6 shadow-sm lg:col-span-5">
            <div class="mx-auto flex aspect-square max-w-xs items-center justify-center rounded-full" style="background: conic-gradient({{ $chartGradient }});">
                <div class="flex h-36 w-36 flex-col items-center justify-center rounded-full bg-surface-container-lowest text-center shadow-inner">
                    <span class="text-3xl font-black text-on-surface">{{ $recordedSantri }}</span>
                    <span class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Santri</span>
                </div>
            </div>
        </div>

        <div class="space-y-3 lg:col-span-7">
            @foreach($buckets as $bucket)
                @php $percentage = $recordedSantri > 0 ? round(($bucket['count'] / $recordedSantri) * 100, 1) : 0; @endphp
                <article class="rounded-xl bg-surface-container-lowest p-5 shadow-sm">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center gap-3">
                            <span class="h-4 w-4 rounded-full" style="background-color: {{ $bucket['color'] }}"></span>
                            <div>
                                <p class="font-headline text-lg font-black text-on-surface">{{ $bucket['label'] }}</p>
                                <p class="text-xs text-on-surface-variant">{{ $bucket['range'] }}</p>
                            </div>
                        </div>
                        <div class="text-left md:text-right">
                            <p class="text-2xl font-black text-on-surface">{{ $bucket['count'] }}</p>
                            <p class="text-xs font-bold text-on-surface-variant">{{ $percentage }}%</p>
                        </div>
                    </div>
                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-surface-container-high">
                        <div class="h-full rounded-full" style="width: {{ $percentage }}%; background-color: {{ $bucket['color'] }}"></div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</div>
@endsection
