@extends('layouts.app')

@section('header-title', 'Riwayat Laundry')
@php $activeRole = 'petugas'; @endphp

@section('content')
<div class="space-y-6">
    <header class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="font-headline text-2xl font-bold tracking-tight text-primary">Dashboard Riwayat Laundry</h1>
            <p class="mt-1 text-sm text-on-surface-variant">Ringkasan transaksi laundry khusus petugas.</p>
        </div>
        <form method="GET" class="flex items-end gap-2">
            <label class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Tanggal
                <input type="date" name="date" value="{{ $date->toDateString() }}" class="input-field mt-2">
            </label>
            <button class="rounded-xl bg-primary px-5 py-3 text-sm font-bold text-on-primary">Terapkan</button>
        </form>
    </header>

    <div class="flex gap-3 overflow-x-auto pb-1">
        <div class="min-w-[220px] rounded-xl bg-surface-container-lowest p-4 md:p-5 shadow-sm flex-shrink-0">
            <p class="text-[10px] md:text-xs font-bold uppercase tracking-widest text-on-surface-variant">Transaksi</p>
            <p class="mt-2 font-headline text-2xl md:text-2xl font-bold text-primary">{{ number_format($totalTransactions, 0, ',', '.') }}</p>
        </div>
        <div class="min-w-[220px] rounded-xl bg-surface-container-lowest p-4 md:p-5 shadow-sm flex-shrink-0">
            <p class="text-[10px] md:text-xs font-bold uppercase tracking-widest text-on-surface-variant">Berat</p>
            <p class="mt-2 font-headline text-2xl md:text-2xl font-bold text-on-surface">{{ number_format($totalWeight, 1, ',', '.') }} Kg</p>
        </div>
        <div class="min-w-[220px] rounded-xl bg-surface-container-lowest p-4 md:p-5 shadow-sm flex-shrink-0">
            <p class="text-[10px] md:text-xs font-bold uppercase tracking-widest text-on-surface-variant">Cash</p>
            <p class="mt-2 font-headline text-xl md:text-xl font-bold text-primary">Rp {{ number_format($totalCash, 0, ',', '.') }}</p>
        </div>
        <div class="min-w-[220px] rounded-xl bg-surface-container-lowest p-4 md:p-5 shadow-sm flex-shrink-0">
            <p class="text-[10px] md:text-xs font-bold uppercase tracking-widest text-on-surface-variant">Saldo Tabungan</p>
            <p class="mt-2 font-headline text-xl md:text-xl font-bold text-primary">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</p>
        </div>
    </div>

    <section class="rounded-xl bg-surface-container-lowest shadow-sm">
        <div class="border-b border-surface-container p-4 sm:p-5">
            <h2 class="font-headline text-lg font-bold text-on-surface">Riwayat {{ $date->translatedFormat('d F Y') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-surface-container-high/50 text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                    <tr>
                        <th class="px-5 py-4">Santri</th>
                        <th class="px-5 py-4">Jenis</th>
                        <th class="px-5 py-4">Berat</th>
                        <th class="px-5 py-4">Baju</th>
                        <th class="px-5 py-4">Pembayaran</th>
                        <th class="px-5 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    @forelse($transactions as $transaction)
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-bold text-on-surface">{{ $transaction->santri?->name }}</p>
                                <p class="text-xs text-on-surface-variant">NIS {{ $transaction->santri?->nis ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-4 font-bold">{{ $transaction->payment_type === 'bulanan' ? 'Bulanan' : 'Tunai' }}</td>
                            <td class="px-5 py-4">{{ number_format((float) $transaction->weight_kg, 1, ',', '.') }} Kg</td>
                            <td class="px-5 py-4">{{ $transaction->total_clothes }} pcs</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-bold text-primary">
                                    {{ $transaction->payment_method === 'saldo_tabungan' ? 'Saldo Tabungan' : ($transaction->payment_method === 'cash' ? 'Cash' : 'Kuota Bulanan') }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('petugas.laundry.receipt', $transaction) }}" class="inline-flex items-center gap-1 rounded-lg bg-surface-container-high px-3 py-2 text-xs font-bold text-on-surface">
                                    <span class="material-symbols-outlined text-sm">print</span>
                                    Nota
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-on-surface-variant">Belum ada transaksi laundry pada tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="border-t border-surface-container p-4 sm:p-5">{{ $transactions->links() }}</div>
        @endif
    </section>
</div>
@endsection
