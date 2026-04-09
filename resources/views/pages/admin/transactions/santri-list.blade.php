@extends('layouts.app')

@section('header-title', 'Data Santri')
@php $activeRole = 'admin'; @endphp

@section('content')
<div>
    <!-- Page Header -->
    <div class="mb-8">
        <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">Data Santri</h2>
        <p class="text-on-surface-variant text-sm mt-1">Kelola data santri dan saldo mereka.</p>
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
            <form method="GET" action="{{ route('admin.transactions.santri') }}" class="flex gap-2">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari nama atau NIS..."
                       class="bg-surface-container-high border-none rounded-lg px-4 py-2 text-sm focus:ring-0 focus:outline-none w-64">
                <button type="submit"
                        class="bg-primary text-on-primary px-4 py-2 rounded-lg text-sm font-semibold hover:bg-primary/90 transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">search</span>
                    <span>Cari</span>
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.transactions.santri') }}"
                       class="bg-surface-container-high text-on-surface-variant px-4 py-2 rounded-lg text-sm font-semibold hover:bg-surface-container transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </a>
                @endif
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-high/50 text-[10px] uppercase font-black text-on-surface-variant tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Santri</th>
                        <th class="px-6 py-4">NIS</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4 text-right">Saldo</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-outline-variant/10">
                    @forelse($santriList as $santri)
                        <tr class="hover:bg-surface transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                                        {{ substr($santri->name, 0, 2) }}
                                    </div>
                                    <span class="font-bold text-on-surface">{{ $santri->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant">{{ $santri->nis ?? '-' }}</td>
                            <td class="px-6 py-4 text-on-surface-variant">{{ $santri->email }}</td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-headline font-bold text-primary">Rp {{ number_format($santri->saldo, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.transactions.topup') }}?nis={{ $santri->nis }}" 
                                       class="px-3 py-1.5 bg-primary text-on-primary text-xs font-bold rounded-lg hover:bg-primary-container transition-colors">
                                        Top Up
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-4xl text-outline mb-3">
                                    @if(request('search'))
                                        search_off
                                    @else
                                        group_off
                                    @endif
                                </span>
                                <p class="text-sm text-on-surface-variant">
                                    @if(request('search'))
                                        Tidak ada santri yang sesuai dengan pencarian "{{ request('search') }}"
                                    @else
                                        Belum ada data santri
                                    @endif
                                </p>
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
