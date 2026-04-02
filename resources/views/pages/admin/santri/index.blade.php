@extends('layouts.app')

@section('header-title', 'Data Santri')
@php $activeRole = 'admin'; @endphp

@section('content')
<div>
    <!-- Page Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">Data Santri</h2>
            <p class="text-on-surface-variant text-sm mt-1">Kelola data santri dan saldo mereka.</p>
        </div>
        <a href="{{ route('admin.santri.create') }}" class="bg-primary text-on-primary font-bold py-3 px-6 rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/30 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined">add</span>
            <span>Tambah Santri</span>
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">group</span>
                <p class="text-xs text-on-surface-variant font-medium">Total Santri</p>
            </div>
            <p class="text-3xl font-bold text-on-surface">{{ $santriList->total() }}</p>
        </div>
        
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">account_balance_wallet</span>
                <p class="text-xs text-on-surface-variant font-medium">Total Saldo Santri</p>
            </div>
            <p class="text-3xl font-bold text-primary">Rp {{ number_format($santriList->getCollection()->sum('saldo'), 0, ',', '.') }}</p>
        </div>
        
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">add_card</span>
                <p class="text-xs text-on-surface-variant font-medium">Quick Actions</p>
            </div>
            <a href="{{ route('admin.transactions.topup') }}" class="inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline">
                <span class="material-symbols-outlined text-sm">add</span>
                <span>Top Up Saldo</span>
            </a>
        </div>
    </div>

    <!-- Santri List Table -->
    <div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm">
        <div class="p-6 border-b border-surface-container flex items-center justify-between">
            <h3 class="font-headline font-bold text-xl text-primary">Daftar Santri</h3>
            <div class="flex gap-2">
                <input type="text" placeholder="Cari santri..." 
                       class="bg-surface-container-high border-none rounded-lg px-4 py-2 text-sm focus:ring-0">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-high/50 text-[10px] uppercase font-black text-on-surface-variant tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Santri</th>
                        <th class="px-6 py-4">NIS</th>
                        <th class="px-6 py-4">Kelas</th>
                        <th class="px-6 py-4">No HP</th>
                        <th class="px-6 py-4 text-right">Saldo</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-outline-variant/10">
                    @forelse($santriList as $santri)
                        <tr class="hover:bg-surface transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold overflow-hidden flex-shrink-0">
                                        @if($santri->foto)
                                            <img src="{{ Storage::url($santri->foto) }}" alt="{{ $santri->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($santri->name, 0, 2) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-on-surface truncate">{{ $santri->name }}</div>
                                        <div class="text-xs text-on-surface-variant truncate">{{ $santri->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant">{{ $santri->nis ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-medium text-on-surface-variant px-2 py-1 bg-surface-container-low rounded">
                                    {{ $santri->kelas ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant">{{ $santri->no_hp ?? '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-headline font-bold text-primary">Rp {{ number_format($santri->saldo, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.santri.show', $santri) }}" 
                                       class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Detail">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                    </a>
                                    <a href="{{ route('admin.santri.edit', $santri) }}" 
                                       class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </a>
                                    <form action="{{ route('admin.santri.destroy', $santri) }}" method="POST" 
                                          onsubmit="return confirm('Yakin ingin menghapus data santri ini?')">
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
                                <p class="text-sm text-on-surface-variant">Belum ada data santri</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($santriList->hasPages())
            <div class="p-6 border-t border-surface-container">
                {{ $santriList->links() }}
            </div>
        @endif
    </div>
</div>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection
