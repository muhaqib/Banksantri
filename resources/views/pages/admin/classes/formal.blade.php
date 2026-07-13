@extends('layouts.app')

@section('title', 'Kelas Formal')
@section('header-title', 'Kelas Formal')
@php $activeRole = 'admin'; @endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-bold text-primary">Master Akademik</p>
            <h1 class="font-headline text-2xl font-bold text-on-surface">Kelas Formal</h1>
            <p class="text-sm text-on-surface-variant">Kelola urutan kelas formal dan naikkan semua santri otomatis.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.classes.pondok.index') }}" class="btn-secondary inline-flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">menu_book</span>
                Kelas Pondok
            </a>
            <form method="POST" action="{{ route('admin.classes.formal.promote-all') }}" onsubmit="return confirm('Naikkan semua santri sesuai kelas tujuan formal yang sudah diatur?')">
                @csrf
                <button class="btn-primary"><span class="material-symbols-outlined">trending_up</span> Naikkan Semua</button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.classes.formal.store') }}" class="grid gap-3 rounded-xl bg-surface-container-lowest p-4 shadow-sm md:grid-cols-5">
        @csrf
        <input name="name" required value="{{ old('name') }}" placeholder="Nama kelas formal" class="input-field md:col-span-2">
        <select name="next_class_id" class="input-field">
            <option value="">Kelas tujuan berikutnya</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected(old('next_class_id') == $class->id)>{{ $class->name }}</option>
            @endforeach
        </select>
        <input type="number" name="sort_order" value="{{ old('sort_order', 100) }}" min="0" max="9999" class="input-field">
        <label class="flex items-center gap-2 rounded-xl bg-surface-container-low px-4 py-3 text-sm font-bold">
            <input type="checkbox" name="is_active" value="1" checked class="rounded border-outline-variant text-primary focus:ring-primary">
            Aktif
        </label>
        <button class="btn-primary md:col-span-5"><span class="material-symbols-outlined">add</span> Tambah Kelas Formal</button>
    </form>

    <div class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm">
        <div class="border-b border-outline-variant/10 px-5 py-4">
            <h2 class="font-headline text-lg font-bold text-primary">Daftar Kelas Formal</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-surface-container-low text-xs uppercase text-on-surface-variant">
                    <tr>
                        <th class="px-5 py-4">Kelas</th>
                        <th class="px-5 py-4">Kelas Berikutnya</th>
                        <th class="px-5 py-4">Urutan</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Santri</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    @forelse($classes as $class)
                        <tr>
                            <td class="px-5 py-4">
                                <form id="formal-class-{{ $class->id }}" method="POST" action="{{ route('admin.classes.formal.update', $class) }}">
                                    @csrf @method('PATCH')
                                    <input name="name" value="{{ $class->name }}" required class="input-field min-w-40">
                                </form>
                            </td>
                            <td class="px-5 py-4">
                                <select form="formal-class-{{ $class->id }}" name="next_class_id" class="input-field min-w-44">
                                    <option value="">Tidak ada</option>
                                    @foreach($classes as $target)
                                        @if($target->id !== $class->id)
                                            <option value="{{ $target->id }}" @selected($class->next_class_id === $target->id)>{{ $target->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-5 py-4">
                                <input form="formal-class-{{ $class->id }}" type="number" name="sort_order" value="{{ $class->sort_order }}" min="0" max="9999" class="input-field w-24">
                            </td>
                            <td class="px-5 py-4">
                                <label class="inline-flex items-center gap-2 font-semibold">
                                    <input form="formal-class-{{ $class->id }}" type="checkbox" name="is_active" value="1" @checked($class->is_active) class="rounded border-outline-variant text-primary focus:ring-primary">
                                    Aktif
                                </label>
                            </td>
                            <td class="px-5 py-4 font-bold text-primary">{{ $studentCounts[$class->name] ?? 0 }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <button form="formal-class-{{ $class->id }}" class="text-primary" title="Simpan"><span class="material-symbols-outlined">save</span></button>
                                    <form method="POST" action="{{ route('admin.classes.formal.promote', $class) }}" onsubmit="return confirm('Naikkan semua santri dari kelas {{ $class->name }}?')">
                                        @csrf
                                        <button class="text-primary" title="Naikkan kelas"><span class="material-symbols-outlined">trending_up</span></button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.classes.formal.destroy', $class) }}" onsubmit="return confirm('Hapus kelas formal ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-error" title="Hapus"><span class="material-symbols-outlined">delete</span></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-14 text-center text-on-surface-variant">Belum ada kelas formal.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
