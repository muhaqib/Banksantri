@extends('layouts.app')

@section('header-title', 'Riwayat Eksekusi')
@php $activeRole = 'admin'; @endphp

@section('content')
<div>
    <div class="mb-8">
        <h2 class="font-headline text-2xl font-bold tracking-tight text-primary">Riwayat Eksekusi Saya</h2>
        <p class="mt-1 text-sm text-on-surface-variant">Aktivitas transaksi dan penarikan tunai yang Anda proses.</p>
    </div>

    <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-xl bg-gradient-to-br from-primary to-primary-container p-4 sm:p-5 shadow-lg shadow-primary/20">
            <p class="text-xs font-medium text-primary-fixed">Total Top Up Dieksekusi</p>
            <p class="mt-2 text-2xl font-bold text-white">Rp {{ number_format($totalTopUp, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl bg-surface-container-lowest p-4 sm:p-5 shadow-sm">
            <p class="text-xs font-medium text-on-surface-variant">Penarikan Tunai Disetujui</p>
            <p class="mt-2 text-3xl font-bold text-error">Rp {{ number_format($totalSettlement, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl bg-surface-container-lowest p-4 sm:p-5 shadow-sm">
            <p class="text-xs font-medium text-on-surface-variant">Total Aktivitas</p>
            <p class="mt-2 text-3xl font-bold text-on-surface">{{ $activities->total() }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm">
        <div class="border-b border-surface-container p-4 sm:p-5">
            <h3 class="font-headline text-xl font-bold text-primary">Semua Aktivitas Saya</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-high/50 text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                    <tr>
                        <th class="px-6 py-4">Waktu Eksekusi</th>
                        <th class="px-6 py-4">Aktivitas</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Keterangan</th>
                        <th class="px-6 py-4 text-right">Nominal</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10 text-sm">
                    @forelse($activities as $activity)
                        <tr class="transition-colors hover:bg-surface">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-on-surface">{{ $activity->executed_at->format('d M Y') }}</p>
                                <p class="text-xs text-outline">{{ $activity->executed_at->format('H:i') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-on-surface">{{ $activity->activity }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ in_array($activity->status, ['Berhasil', 'Disetujui']) ? 'bg-primary-fixed text-on-primary-fixed-variant' : 'bg-error-container text-on-error-container' }}">
                                    {{ $activity->status }}
                                </span>
                            </td>
                            <td class="max-w-md px-6 py-4 text-on-surface-variant">
                                {{ $activity->description ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-right font-headline font-bold {{ $activity->direction === 'masuk' ? 'text-primary' : 'text-error' }}">
                                Rp {{ number_format($activity->nominal, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($activity->receipt_url)
                                    <a href="{{ $activity->receipt_url }}"
                                       target="_blank"
                                       rel="noopener"
                                       class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-xs font-bold text-on-primary transition-opacity hover:opacity-90">
                                        <span class="material-symbols-outlined text-base">print</span>
                                        Print Kwitansi
                                    </a>
                                @else
                                    <span class="text-outline">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined mb-3 text-4xl text-outline">receipt_long</span>
                                <p class="text-sm text-on-surface-variant">Belum ada aktivitas yang Anda eksekusi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activities->hasPages())
            <div class="border-t border-surface-container p-4 sm:p-5">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
