@extends('layouts.app')

@section('title', 'Kelas Pondok')
@section('header-title', 'Kelas Pondok')
@php $activeRole = 'admin'; @endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-bold text-primary">Master Akademik</p>
            <h1 class="font-headline text-3xl font-black text-on-surface">Kelas Pondok Pesantren</h1>
            <p class="text-sm text-on-surface-variant">Kelola kelas pondok, wali kelas, dan jenis ujian yang berlaku.</p>
        </div>
        <a href="{{ route('admin.classes.formal.index') }}" class="btn-secondary inline-flex items-center justify-center gap-2">
            <span class="material-symbols-outlined">school</span>
            Kelas Formal
        </a>
    </div>

    <form method="POST" action="{{ route('admin.classes.pondok.store') }}" class="grid gap-3 rounded-xl bg-surface-container-lowest p-4 shadow-sm lg:grid-cols-7">
        @csrf
        <input name="name" required value="{{ old('name') }}" placeholder="Nama kelas" class="input-field lg:col-span-2">
        <input name="homeroom_teacher" value="{{ old('homeroom_teacher') }}" placeholder="Nama wali kelas" class="input-field lg:col-span-2">
        <input type="number" name="sort_order" value="{{ old('sort_order', 100) }}" min="0" max="9999" class="input-field">
        <label class="flex items-center gap-2 rounded-xl bg-surface-container-low px-4 py-3 text-sm font-bold">
            <input type="checkbox" name="uses_monthly_exam" value="1" checked class="rounded border-outline-variant text-primary focus:ring-primary">
            Bulanan
        </label>
        <label class="flex items-center gap-2 rounded-xl bg-surface-container-low px-4 py-3 text-sm font-bold">
            <input type="checkbox" name="uses_semester_exam" value="1" checked class="rounded border-outline-variant text-primary focus:ring-primary">
            Semester
        </label>
        <label class="flex items-center gap-2 rounded-xl bg-surface-container-low px-4 py-3 text-sm font-bold lg:col-span-2">
            <input type="checkbox" name="is_active" value="1" checked class="rounded border-outline-variant text-primary focus:ring-primary">
            Aktif
        </label>
        <button class="btn-primary lg:col-span-5"><span class="material-symbols-outlined">add</span> Tambah Kelas Pondok</button>
    </form>

    <div class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm">
        <div class="border-b border-outline-variant/10 px-5 py-4">
            <h2 class="font-headline text-lg font-bold text-primary">Daftar Kelas Pondok</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-surface-container-low text-xs uppercase text-on-surface-variant">
                    <tr>
                        <th class="px-5 py-4">Kelas</th>
                        <th class="px-5 py-4">Wali Kelas</th>
                        <th class="px-5 py-4">Urutan</th>
                        <th class="px-5 py-4">Ujian</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Santri</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    @forelse($classes as $class)
                        <tr>
                            <td class="px-5 py-4">
                                <form id="pondok-class-{{ $class->id }}" method="POST" action="{{ route('admin.classes.pondok.update', $class) }}">
                                    @csrf @method('PATCH')
                                    <input name="name" value="{{ $class->name }}" required class="input-field min-w-40">
                                </form>
                            </td>
                            <td class="px-5 py-4">
                                <input form="pondok-class-{{ $class->id }}" name="homeroom_teacher" value="{{ $class->homeroom_teacher }}" placeholder="-" class="input-field min-w-48">
                            </td>
                            <td class="px-5 py-4">
                                <input form="pondok-class-{{ $class->id }}" type="number" name="sort_order" value="{{ $class->sort_order }}" min="0" max="9999" class="input-field w-24">
                            </td>
                            <td class="px-5 py-4">
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 font-semibold">
                                        <input form="pondok-class-{{ $class->id }}" type="checkbox" name="uses_monthly_exam" value="1" @checked($class->uses_monthly_exam) class="rounded border-outline-variant text-primary focus:ring-primary">
                                        Bulanan
                                    </label>
                                    <label class="flex items-center gap-2 font-semibold">
                                        <input form="pondok-class-{{ $class->id }}" type="checkbox" name="uses_semester_exam" value="1" @checked($class->uses_semester_exam) class="rounded border-outline-variant text-primary focus:ring-primary">
                                        Semester
                                    </label>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <label class="inline-flex items-center gap-2 font-semibold">
                                    <input form="pondok-class-{{ $class->id }}" type="checkbox" name="is_active" value="1" @checked($class->is_active) class="rounded border-outline-variant text-primary focus:ring-primary">
                                    Aktif
                                </label>
                            </td>
                            <td class="px-5 py-4 font-bold text-primary">{{ $class->students_count }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <button form="pondok-class-{{ $class->id }}" class="text-primary" title="Simpan"><span class="material-symbols-outlined">save</span></button>
                                    <form method="POST" action="{{ route('admin.classes.pondok.destroy', $class) }}" onsubmit="return confirm('Hapus kelas pondok ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-error" title="Hapus"><span class="material-symbols-outlined">delete</span></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-14 text-center text-on-surface-variant">Belum ada kelas pondok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
