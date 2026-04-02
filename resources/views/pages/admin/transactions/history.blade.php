@extends('layouts.app')

@section('header-title', 'Riwayat Transaksi')
@php $activeRole = 'admin'; @endphp

@section('content')
<div>
    <!-- Page Header -->
    <div class="mb-8">
        <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">Riwayat Transaksi</h2>
        <p class="text-on-surface-variant text-sm mt-1">Monitor semua transaksi masuk dan keluar santri.</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-primary to-primary-container p-6 rounded-xl shadow-lg shadow-primary/20">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-white text-sm" style="font-variation-settings: 'FILL' 1;">trending_up</span>
                <p class="text-xs text-primary-fixed font-medium">Total Pemasukan</p>
            </div>
            <p class="text-3xl font-extrabold text-white">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</p>
        </div>
        
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-error text-sm" style="font-variation-settings: 'FILL' 1;">trending_down</span>
                <p class="text-xs text-on-surface-variant font-medium">Total Pengeluaran</p>
            </div>
            <p class="text-3xl font-bold text-error">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</p>
        </div>
        
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">analytics</span>
                <p class="text-xs text-on-surface-variant font-medium">Total Transaksi</p>
            </div>
            <p class="text-3xl font-bold text-on-surface">{{ $transactions->total() }}</p>
        </div>
    </div>

    <!-- Transaction List -->
    <div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm">
        <div class="p-6 border-b border-surface-container flex items-center justify-between">
            <h3 class="font-headline font-bold text-xl text-primary">Semua Transaksi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-high/50 text-[10px] uppercase font-black text-on-surface-variant tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Santri</th>
                        <th class="px-6 py-4">Jenis</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Keterangan</th>
                        <th class="px-6 py-4 text-right">Nominal</th>
                        <th class="px-6 py-4 text-right">Saldo Setelah</th>
                        <th class="px-6 py-4">Petugas</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-outline-variant/10">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-surface transition-colors group">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-on-surface">{{ $trx->created_at->format('d M Y') }}</p>
                                <p class="text-xs text-outline">{{ $trx->created_at->format('H:i') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                                        {{ substr($trx->santri->name ?? '?', 0, 2) }}
                                    </div>
                                    <span class="font-medium text-on-surface">{{ $trx->santri->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($trx->jenis === 'masuk')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary-fixed text-on-primary-fixed-variant text-xs font-bold">
                                        <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">arrow_downward</span>
                                        Masuk
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-error-container text-on-error-container text-xs font-bold">
                                        <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">arrow_upward</span>
                                        Keluar
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-medium text-on-surface-variant px-2 py-1 bg-surface-container-low rounded">
                                    {{ ucfirst($trx->kategori) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-on-surface-variant max-w-xs truncate">
                                {{ $trx->keterangan ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-headline font-bold {{ $trx->jenis === 'masuk' ? 'text-primary' : 'text-error' }}">
                                    {{ $trx->jenis === 'masuk' ? '+' : '-' }} Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm text-on-surface-variant">Rp {{ number_format($trx->saldo_setelah, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-on-surface-variant">{{ $trx->petugas->name ?? 'System' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-4xl text-outline mb-3">receipt_long</span>
                                <p class="text-sm text-on-surface-variant">Belum ada transaksi</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($transactions->hasPages())
            <div class="p-6 border-t border-surface-container">
                {{ $transactions->links() }}
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
