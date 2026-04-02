@extends('layouts.app')

@section('header-title', 'Detail Petugas')
@php $activeRole = 'admin'; @endphp

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <a href="{{ route('admin.petugas.index') }}" class="text-primary hover:underline flex items-center gap-1 mb-4">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Data Petugas</span>
        </a>
        <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">Detail Petugas</h2>
    </div>

    <!-- Profile Card -->
    <div class="card mb-6">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
            <!-- Foto Profil -->
            <div class="w-32 h-32 rounded-full bg-primary/10 flex items-center justify-center overflow-hidden flex-shrink-0">
                @if($petugas->foto)
                    <img src="{{ Storage::url($petugas->foto) }}" alt="{{ $petugas->name }}" class="w-full h-full object-cover">
                @else
                    <span class="material-symbols-filled text-primary text-6xl">account_circle</span>
                @endif
            </div>
            
            <!-- Info -->
            <div class="flex-1 text-center md:text-left">
                <h3 class="font-headline font-bold text-2xl text-on-surface mb-2">{{ $petugas->name }}</h3>
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 mb-4">
                    <span class="text-xs font-medium text-on-surface-variant px-3 py-1 bg-surface-container-low rounded-full">
                        {{ $petugas->jabatan ?? 'Tidak ada jabatan' }}
                    </span>
                    <span class="text-xs font-medium text-primary px-3 py-1 bg-primary-fixed rounded-full">
                        Petugas
                    </span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="flex items-center gap-2 justify-center md:justify-start">
                        <span class="material-symbols-outlined text-on-surface-variant text-sm">badge</span>
                        <span class="text-on-surface-variant">NIP: {{ $petugas->nip ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-2 justify-center md:justify-start">
                        <span class="material-symbols-outlined text-on-surface-variant text-sm">email</span>
                        <span class="text-on-surface-variant">{{ $petugas->email }}</span>
                    </div>
                    <div class="flex items-center gap-2 justify-center md:justify-start">
                        <span class="material-symbols-outlined text-on-surface-variant text-sm">phone</span>
                        <span class="text-on-surface-variant">{{ $petugas->no_hp ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-2 justify-center md:justify-start">
                        <span class="material-symbols-outlined text-on-surface-variant text-sm">event</span>
                        <span class="text-on-surface-variant">Bergabung: {{ $petugas->created_at->format('d M Y') }}</span>
                    </div>
                </div>
                
                @if($petugas->alamat)
                    <div class="mt-4 flex items-start gap-2 justify-center md:justify-start">
                        <span class="material-symbols-outlined text-on-surface-variant text-sm">location_on</span>
                        <span class="text-on-surface-variant">{{ $petugas->alamat }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-4">
        <a href="{{ route('admin.petugas.edit', $petugas) }}" class="btn-primary flex-1">
            <span class="material-symbols-filled">edit</span>
            <span>Edit Data</span>
        </a>
        <form action="{{ route('admin.petugas.destroy', $petugas) }}" method="POST" class="flex-1" onsubmit="return confirm('Yakin ingin menghapus data petugas ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full btn-error">
                <span class="material-symbols-filled">delete</span>
                <span>Hapus Data</span>
            </button>
        </form>
    </div>
</div>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
.material-symbols-filled {
    font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection
