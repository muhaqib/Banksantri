@extends('layouts.app')

@section('header-title', 'Data Petugas')
@php $activeRole = 'admin'; @endphp

@section('content')
<div>
    <!-- Page Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">Data Petugas</h2>
            <p class="text-on-surface-variant text-sm mt-1">Kelola data petugas bank pesantren.</p>
        </div>
        <a href="{{ route('admin.petugas.create') }}" class="btn-primary">
            <span class="material-symbols-filled">add</span>
            <span>Tambah Petugas</span>
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-filled text-primary">group</span>
                <p class="text-xs text-on-surface-variant font-medium">Total Petugas</p>
            </div>
            <p class="text-3xl font-bold text-on-surface">{{ $petugasList->total() }}</p>
        </div>
        
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-filled text-primary">verified_user</span>
                <p class="text-xs text-on-surface-variant font-medium">Petugas Aktif</p>
            </div>
            <p class="text-3xl font-bold text-primary">{{ $petugasList->total() }}</p>
        </div>
        
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-filled text-primary">work</span>
                <p class="text-xs text-on-surface-variant font-medium">Jabatan</p>
            </div>
            <p class="text-lg font-bold text-on-surface">{{ $petugasList->getCollection()->pluck('jabatan')->unique()->count() }} Tipe</p>
        </div>
    </div>

    <!-- Petugas List Table -->
    <div class="card !p-0 overflow-hidden">
        <div class="p-6 border-b border-outline-variant/10 flex items-center justify-between">
            <h3 class="font-headline font-bold text-xl text-primary">Daftar Petugas</h3>
            <div class="flex gap-2">
                <input type="text" placeholder="Cari petugas..." 
                       class="input-field !py-2 !text-sm" style="width: 250px;">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-high/50 text-[10px] uppercase font-black text-on-surface-variant tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Petugas</th>
                        <th class="px-6 py-4">NIP</th>
                        <th class="px-6 py-4">Jabatan</th>
                        <th class="px-6 py-4">No HP</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-outline-variant/10">
                    @forelse($petugasList as $petugas)
                        <tr class="hover:bg-surface transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold overflow-hidden flex-shrink-0">
                                        @if($petugas->foto)
                                            <img src="{{ Storage::url($petugas->foto) }}" alt="{{ $petugas->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($petugas->name, 0, 2) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-on-surface truncate">{{ $petugas->name }}</div>
                                        <div class="text-xs text-on-surface-variant truncate">{{ $petugas->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-on-surface-variant">{{ $petugas->nip ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-medium text-on-surface-variant px-2 py-1 bg-surface-container-low rounded">
                                    {{ $petugas->jabatan ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant">{{ $petugas->no_hp ?? '-' }}</td>
                            <td class="px-6 py-4 text-on-surface-variant">{{ $petugas->email }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.petugas.show', $petugas) }}" 
                                       class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Detail">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                    </a>
                                    <a href="{{ route('admin.petugas.edit', $petugas) }}" 
                                       class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </a>
                                    <form action="{{ route('admin.petugas.destroy', $petugas) }}" method="POST" 
                                          onsubmit="return confirm('Yakin ingin menghapus data petugas ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-error hover:bg-error/10 rounded-lg transition-colors" title="Hapus">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-4xl text-outline mb-3">group_off</span>
                                <p class="text-sm text-on-surface-variant">Belum ada data petugas</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($petugasList->hasPages())
            <div class="p-6 border-t border-outline-variant/10">
                {{ $petugasList->links() }}
            </div>
        @endif
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
