@extends('layouts.app')

@section('title', 'Manajemen Akses Petugas')
@php $activeRole = 'admin'; @endphp

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-primary">Super Admin</p>
            <h1 class="font-headline text-2xl font-black text-on-surface">Manajemen Akses Petugas</h1>
            <p class="text-sm text-on-surface-variant mt-1">Atur izin menu untuk setiap akun petugas secara terpisah.</p>
        </div>
        <a href="{{ route('admin.petugas.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-surface-container-low px-4 py-3 text-sm font-bold text-primary hover:bg-surface-container-high transition-all">
            <span class="material-symbols-outlined text-[18px]">group</span>
            Data Petugas
        </a>
    </div>

    <form action="{{ route('admin.access.update', $petugas) }}" method="POST" class="bg-surface-container-low rounded-xl border border-primary ring-2 ring-primary/20 p-5">
        @csrf
        @method('PUT')

        <div class="flex items-center gap-3 mb-5">
            <div class="w-11 h-11 bg-primary/10 rounded-full flex items-center justify-center overflow-hidden">
                @if($petugas->foto)
                    <img src="{{ Storage::url($petugas->foto) }}" alt="{{ $petugas->name }}" class="w-full h-full object-cover">
                @else
                    <span class="material-symbols-outlined text-primary">badge</span>
                @endif
            </div>
            <div class="min-w-0">
                <h2 class="font-headline font-bold text-lg text-on-surface truncate">{{ $petugas->name }}</h2>
                <p class="text-xs text-on-surface-variant">{{ $petugas->jabatan ?? 'Petugas' }} · {{ $petugas->permissions->count() }} permission aktif</p>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            @foreach($groups as $group => $permissions)
                <section>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">{{ $group }}</h3>
                    <div class="space-y-2">
                        @foreach($permissions as $permission => $label)
                            <label class="flex items-start gap-3 rounded-lg bg-surface px-3 py-3 border border-outline-variant/10">
                                <input type="checkbox"
                                       name="permissions[]"
                                       value="{{ $permission }}"
                                       @checked($petugas->hasDirectPermission($permission))
                                       class="mt-1 rounded border-outline-variant text-primary focus:ring-primary">
                                <span>
                                    <span class="block text-sm font-semibold text-on-surface">{{ $label }}</span>
                                    <span class="block text-[11px] text-on-surface-variant">{{ $permission }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <button type="submit" class="mt-5 w-full bg-primary text-on-primary rounded-xl px-4 py-3 font-bold hover:opacity-90 transition-all">
            Simpan Akses Petugas
        </button>
    </form>
</div>
@endsection
