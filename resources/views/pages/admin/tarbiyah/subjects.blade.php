@extends('layouts.app')

@section('title', 'Mapel Tarbiyah')
@section('header-title', 'Mapel Tarbiyah')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-bold text-primary">Master Akademik</p>
            <h1 class="font-headline text-3xl font-black">Mata Pelajaran Tarbiyah</h1>
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
</div>
@endsection
