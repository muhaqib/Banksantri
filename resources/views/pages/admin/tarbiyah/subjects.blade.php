@extends('layouts.app')

@section('title', 'Mapel Tarbiyah')
@section('header-title', 'Mapel Tarbiyah')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-bold text-primary">Master Akademik</p>
            <h1 class="font-headline text-2xl font-bold">Mata Pelajaran Tarbiyah</h1>
            <p class="text-sm text-on-surface-variant">Atur mata pelajaran berbeda untuk setiap kelas.</p>
        </div>
        <form method="GET" class="flex gap-2">
            <select name="class_level" class="input-field" onchange="this.form.submit()">
                @foreach($classLevels as $level)
                    <option value="{{ $level }}" @selected($classLevel === $level)>{{ $level }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <form method="POST" action="{{ route('admin.tarbiyah.subjects.store') }}" class="grid gap-3 rounded-xl bg-surface-container-lowest p-4 shadow-sm md:grid-cols-5">
        @csrf
        <input type="hidden" name="class_level" value="{{ $classLevel }}">
        <input name="name" required placeholder="Nama mata pelajaran" class="input-field md:col-span-2">
        <input type="number" name="sort_order" value="100" min="0" max="9999" class="input-field">
        <label class="flex items-center gap-2 rounded-xl bg-surface-container-low px-4 py-3 text-sm font-bold">
            <input type="checkbox" name="is_active" value="1" checked class="rounded border-outline-variant text-primary focus:ring-primary">
            Aktif
        </label>
        <button class="btn-primary"><span class="material-symbols-outlined">add</span> Tambah</button>
    </form>

    <div class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm">
        <div class="border-b border-outline-variant/10 px-5 py-4">
            <h2 class="font-headline text-lg font-bold text-primary">Mata Pelajaran Semester</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-surface-container-low text-xs uppercase text-on-surface-variant">
                    <tr>
                        <th class="px-5 py-4">Mapel</th>
                        <th class="px-5 py-4">Urutan</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    @forelse($subjects as $subject)
                        <tr>
                            <td class="px-5 py-4">
                                <form id="subject-{{ $subject->id }}" method="POST" action="{{ route('admin.tarbiyah.subjects.update', $subject) }}">
                                    @csrf @method('PATCH')
                                    <input name="name" value="{{ $subject->name }}" required class="input-field">
                                </form>
                            </td>
                            <td class="px-5 py-4">
                                <input form="subject-{{ $subject->id }}" type="number" name="sort_order" value="{{ $subject->sort_order }}" min="0" max="9999" class="input-field w-28">
                            </td>
                            <td class="px-5 py-4">
                                <label class="inline-flex items-center gap-2 text-sm font-bold">
                                    <input form="subject-{{ $subject->id }}" type="checkbox" name="is_active" value="1" @checked($subject->is_active) class="rounded border-outline-variant text-primary focus:ring-primary">
                                    Aktif
                                </label>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex gap-2">
                                    <button form="subject-{{ $subject->id }}" class="text-primary" title="Simpan"><span class="material-symbols-outlined">save</span></button>
                                    <form method="POST" action="{{ route('admin.tarbiyah.subjects.destroy', $subject) }}" onsubmit="return confirm('Hapus mata pelajaran ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-error" title="Hapus"><span class="material-symbols-outlined">delete</span></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-14 text-center text-on-surface-variant">Belum ada mata pelajaran untuk kelas ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <section class="grid gap-5 lg:grid-cols-3">
        <form method="POST" action="{{ route('admin.tarbiyah.monthly-exams.store') }}" class="rounded-xl bg-surface-container-lowest p-5 shadow-sm">
            @csrf
            <h2 class="font-headline text-lg font-bold text-primary">Buat Ujian Bulanan</h2>
            <p class="mb-4 text-xs text-on-surface-variant">Ujian yang dibuat admin akan muncul di form penilaian petugas.</p>
            <div class="space-y-3">
                <input name="name" required value="{{ old('name') }}" placeholder="Nama ujian" class="input-field w-full">
                <input type="date" name="exam_date" required value="{{ old('exam_date', now()->format('Y-m-d')) }}" class="input-field w-full">
                <button class="btn-primary w-full justify-center"><span class="material-symbols-outlined">add</span> Tambah Ujian</button>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between border-b border-outline-variant/10 px-5 py-4">
                <div>
                    <h2 class="font-headline text-lg font-bold text-primary">Daftar Ujian Bulanan</h2>
                    <p class="text-xs text-on-surface-variant">Mapel ujian bulanan tetap: Nahwu, Shorof, Fiqih.</p>
                </div>
                <span class="text-xs font-bold text-on-surface-variant">{{ $monthlyExams->total() }} ujian</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-surface-container-low text-xs uppercase text-on-surface-variant">
                        <tr>
                            <th class="px-5 py-4">Nama Ujian</th>
                            <th class="px-5 py-4">Waktu Ujian</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @forelse($monthlyExams as $exam)
                            <tr>
                                <td class="px-5 py-4">
                                    <form id="monthly-exam-{{ $exam->id }}" method="POST" action="{{ route('admin.tarbiyah.monthly-exams.update', $exam) }}">
                                        @csrf @method('PATCH')
                                        <input name="name" value="{{ $exam->name }}" required class="input-field w-full">
                                    </form>
                                </td>
                                <td class="px-5 py-4">
                                    <input form="monthly-exam-{{ $exam->id }}" type="date" name="exam_date" value="{{ $exam->exam_date?->format('Y-m-d') }}" required class="input-field">
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button form="monthly-exam-{{ $exam->id }}" class="text-primary" title="Simpan"><span class="material-symbols-outlined">save</span></button>
                                        <form method="POST" action="{{ route('admin.tarbiyah.monthly-exams.destroy', $exam) }}" onsubmit="return confirm('Hapus ujian bulanan ini? Nilai yang terkait juga akan terhapus.')">
                                            @csrf @method('DELETE')
                                            <button class="text-error" title="Hapus"><span class="material-symbols-outlined">delete</span></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-14 text-center text-on-surface-variant">Belum ada ujian bulanan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($monthlyExams->hasPages())
                <div class="border-t border-outline-variant/10 px-5 py-4">
                    {{ $monthlyExams->links() }}
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
