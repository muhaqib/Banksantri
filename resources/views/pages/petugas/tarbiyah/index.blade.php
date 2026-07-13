@extends('layouts.app')

@section('title', 'Nilai Tarbiyah')
@section('header-title', 'Nilai Tarbiyah')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-bold text-primary">Akademik Tarbiyah</p>
            <h1 class="font-headline text-2xl font-bold">Input Nilai Santri</h1>
            <p class="text-sm text-on-surface-variant">Kelola nilai semester dan ujian bulanan santri.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-primary/10 px-4 py-3 text-sm font-bold text-primary">{{ session('success') }}</div>
    @endif

    <div class="rounded-xl bg-surface-container-lowest p-2 shadow-sm">
        <div class="grid gap-2 md:grid-cols-2">
            <a href="{{ route('petugas.tarbiyah.index', ['mode' => 'semester', 'class_level' => $classLevel, 'semester' => $semester, 'academic_year' => $academicYear]) }}" class="rounded-lg px-4 py-3 text-center text-sm font-bold {{ $mode === 'semester' ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-on-surface-variant' }}">
                Ujian Semester
            </a>
            <a href="{{ route('petugas.tarbiyah.index', ['mode' => 'monthly', 'class_level' => $classLevel, 'monthly_exam_id' => $monthlyExam?->id]) }}" class="rounded-lg px-4 py-3 text-center text-sm font-bold {{ $mode === 'monthly' ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-on-surface-variant' }}">
                Ujian Bulanan
            </a>
        </div>
    </div>

    @if($mode === 'monthly')
        <form method="GET" class="grid gap-3 rounded-xl bg-surface-container-lowest p-4 shadow-sm md:grid-cols-4">
            <input type="hidden" name="mode" value="monthly">
            <select name="class_level" class="input-field">
                @foreach($classLevels as $level)
                    <option value="{{ $level }}" @selected($classLevel === $level)>{{ $level }}</option>
                @endforeach
            </select>
            <select name="monthly_exam_id" class="input-field md:col-span-2">
                @forelse($monthlyExams as $exam)
                    <option value="{{ $exam->id }}" @selected($monthlyExam?->id === $exam->id)>{{ $exam->name }} - {{ $exam->exam_date?->format('d/m/Y') }}</option>
                @empty
                    <option value="">Belum ada ujian bulanan</option>
                @endforelse
            </select>
            <button class="btn-primary"><span class="material-symbols-outlined">filter_alt</span> Tampilkan</button>
        </form>

        <div class="grid gap-4 lg:grid-cols-3">
            <form method="POST" action="{{ route('petugas.tarbiyah.monthly.import') }}" enctype="multipart/form-data" class="rounded-xl bg-surface-container-lowest p-4 shadow-sm lg:col-span-2">
                @csrf
                <input type="hidden" name="class_level" value="{{ $classLevel }}">
                <input type="hidden" name="monthly_exam_id" value="{{ $monthlyExam?->id }}">
                <p class="font-bold text-on-surface">Import Nilai Bulanan</p>
                <p class="mb-3 text-xs text-on-surface-variant">Header wajib: NIS, Nama Santri, Nahwu, Shorof, Fiqih.</p>
                <div class="flex flex-col gap-3 md:flex-row">
                    <input type="file" name="excel_file" required accept=".xlsx,.xls" class="input-field flex-1" @disabled(! $monthlyExam)>
                    <button class="btn-primary" @disabled(! $monthlyExam)><span class="material-symbols-outlined">upload_file</span> Import</button>
                    <a href="{{ route('petugas.tarbiyah.monthly.export', ['class_level' => $classLevel, 'monthly_exam_id' => $monthlyExam?->id]) }}" class="btn-secondary"><span class="material-symbols-outlined">download</span> Export Data Kelas</a>
                </div>
                @if(session('import_errors'))
                    <div class="mt-3 rounded-xl bg-error-container p-3 text-xs text-on-error-container">
                        @foreach(session('import_errors') as $error)<p>{{ $error }}</p>@endforeach
                    </div>
                @endif
            </form>

            <div class="rounded-xl bg-surface-container-lowest p-4 shadow-sm">
                <p class="font-bold text-on-surface">Aturan Poin Otomatis</p>
                <div class="mt-3 space-y-2 text-xs text-on-surface-variant">
                    <p>Total &lt; 90: <span class="font-bold text-error">-3 poin</span></p>
                    <p>Total 90 - 180: <span class="font-bold text-primary">+3 poin</span></p>
                    <p>Total &gt; 180: <span class="font-bold text-primary">+5 poin</span></p>
                    <p>Total 300: <span class="font-bold text-primary">+10 poin</span></p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('petugas.tarbiyah.monthly.store') }}" class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm">
            @csrf
            <input type="hidden" name="class_level" value="{{ $classLevel }}">
            <input type="hidden" name="monthly_exam_id" value="{{ $monthlyExam?->id }}">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-surface-container-low text-xs uppercase text-on-surface-variant">
                        <tr>
                            <th class="sticky left-0 z-10 bg-surface-container-low px-4 py-3">Santri</th>
                            @foreach($monthlySubjects as $subject)
                                <th class="px-3 py-3 text-center">{{ $subject }}</th>
                            @endforeach
                            <th class="px-3 py-3 text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @forelse($santriList as $santri)
                            @php
                                $gradeMap = $santri->tarbiyahMonthlyGrades->keyBy('subject');
                                $total = collect($monthlySubjects)->sum(fn ($subject) => (float) ($gradeMap[$subject]->score ?? 0));
                                $isComplete = collect($monthlySubjects)->every(fn ($subject) => isset($gradeMap[$subject]));
                            @endphp
                            <tr>
                                <td class="sticky left-0 z-10 bg-surface-container-lowest px-4 py-3">
                                    <p class="font-bold">{{ $santri->name }}</p>
                                    <p class="text-xs text-on-surface-variant">{{ $santri->nis }}</p>
                                </td>
                                @foreach($monthlySubjects as $subject)
                                    <td class="px-3 py-3">
                                        <input type="number" step="0.01" min="0" max="100" name="grades[{{ $santri->id }}][{{ $subject }}]" value="{{ old("grades.{$santri->id}.{$subject}", $gradeMap[$subject]->score ?? '') }}" class="input-field h-11 w-24 text-center" @disabled(! $monthlyExam)>
                                    </td>
                                @endforeach
                                <td class="px-3 py-3 text-center font-bold text-primary">{{ $isComplete ? rtrim(rtrim(number_format($total, 2, '.', ''), '0'), '.') : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($monthlySubjects) + 2 }}" class="px-5 py-14 text-center text-on-surface-variant">Tidak ada santri aktif di kelas {{ $classLevel }}.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-3 border-t border-surface-container p-4 md:flex-row md:items-center md:justify-between">
                <div>{{ $santriList->links() }}</div>
                <button class="btn-primary justify-center" @disabled(! $monthlyExam)><span class="material-symbols-outlined">save</span> Simpan Nilai Bulanan</button>
            </div>
        </form>
    @else
        <form method="GET" class="grid gap-3 rounded-xl bg-surface-container-lowest p-4 shadow-sm md:grid-cols-4">
            <input type="hidden" name="mode" value="semester">
            <select name="class_level" class="input-field">
                @foreach($classLevels as $level)
                    <option value="{{ $level }}" @selected($classLevel === $level)>{{ $level }}</option>
                @endforeach
            </select>
            <select name="semester" class="input-field">
                <option value="1" @selected($semester === 1)>Semester 1</option>
                <option value="2" @selected($semester === 2)>Semester 2</option>
            </select>
            <input name="academic_year" value="{{ $academicYear }}" class="input-field" placeholder="2026/2027">
            <button class="btn-primary"><span class="material-symbols-outlined">filter_alt</span> Tampilkan</button>
        </form>

        <div class="grid gap-4 lg:grid-cols-3">
            <form method="POST" action="{{ route('petugas.tarbiyah.import') }}" enctype="multipart/form-data" class="rounded-xl bg-surface-container-lowest p-4 shadow-sm lg:col-span-2">
                @csrf
                <input type="hidden" name="class_level" value="{{ $classLevel }}">
                <input type="hidden" name="semester" value="{{ $semester }}">
                <input type="hidden" name="academic_year" value="{{ $academicYear }}">
                <p class="font-bold text-on-surface">Import Excel Semester</p>
                <p class="mb-3 text-xs text-on-surface-variant">Header wajib: NIS dan nama mata pelajaran kelas {{ $classLevel }}.</p>
                <div class="flex flex-col gap-3 md:flex-row">
                    <input type="file" name="excel_file" required accept=".xlsx,.xls" class="input-field flex-1">
                    <button class="btn-primary"><span class="material-symbols-outlined">upload_file</span> Import</button>
                    <a href="{{ route('petugas.tarbiyah.template', ['class_level' => $classLevel]) }}" class="btn-secondary"><span class="material-symbols-outlined">download</span> Template</a>
                </div>
                @if(session('import_errors'))
                    <div class="mt-3 rounded-xl bg-error-container p-3 text-xs text-on-error-container">
                        @foreach(session('import_errors') as $error)<p>{{ $error }}</p>@endforeach
                    </div>
                @endif
            </form>

            <form method="POST" action="{{ route('petugas.tarbiyah.promote') }}" class="rounded-xl bg-surface-container-lowest p-4 shadow-sm">
                @csrf
                <input type="hidden" name="class_level" value="{{ $classLevel }}">
                <input type="hidden" name="academic_year" value="{{ $academicYear }}">
                <p class="font-bold text-on-surface">Kenaikan Kelas</p>
                <p class="mb-3 text-xs text-on-surface-variant">Santri naik jika semua nilai semester 1 dan 2 lengkap.</p>
                <button class="btn-primary w-full justify-center" onclick="return confirm('Naikkan santri yang nilai dua semesternya lengkap?')"><span class="material-symbols-outlined">trending_up</span> Naikkan Kelas</button>
            </form>
        </div>

        <form method="POST" action="{{ route('petugas.tarbiyah.store') }}" class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm">
            @csrf
            <input type="hidden" name="class_level" value="{{ $classLevel }}">
            <input type="hidden" name="semester" value="{{ $semester }}">
            <input type="hidden" name="academic_year" value="{{ $academicYear }}">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="bg-surface-container-low text-xs uppercase text-on-surface-variant">
                        <tr>
                            <th class="sticky left-0 z-10 bg-surface-container-low px-4 py-3">Santri</th>
                            @foreach($subjects as $subject)
                                <th class="px-3 py-3 text-center">{{ $subject->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @forelse($santriList as $santri)
                            @php $gradeMap = $santri->tarbiyahGrades->keyBy('subject_id'); @endphp
                            <tr>
                                <td class="sticky left-0 z-10 bg-surface-container-lowest px-4 py-3">
                                    <p class="font-bold">{{ $santri->name }}</p>
                                    <p class="text-xs text-on-surface-variant">{{ $santri->nis }}</p>
                                </td>
                                @foreach($subjects as $subject)
                                    <td class="px-3 py-3">
                                        <input type="number" step="0.01" min="0" max="100" name="grades[{{ $santri->id }}][{{ $subject->id }}]" value="{{ old("grades.{$santri->id}.{$subject->id}", $gradeMap[$subject->id]->score ?? '') }}" class="input-field h-11 w-24 text-center">
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ $subjects->count() + 1 }}" class="px-5 py-14 text-center text-on-surface-variant">Tidak ada santri aktif di kelas {{ $classLevel }}.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-3 border-t border-surface-container p-4 md:flex-row md:items-center md:justify-between">
                <div>{{ $santriList->links() }}</div>
                <button class="btn-primary justify-center"><span class="material-symbols-outlined">save</span> Simpan Nilai Semester</button>
            </div>
        </form>
    @endif
</div>
@endsection
