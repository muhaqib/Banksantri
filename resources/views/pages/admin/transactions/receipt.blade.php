@extends('layouts.app')

@section('title', 'Kwitansi Top Up')
@section('header-title', 'Kwitansi Top Up')
@php
    $activeRole = 'admin';
    $academicYear = $transaction->created_at->month >= 7
        ? $transaction->created_at->year.'/'.($transaction->created_at->year + 1)
        : ($transaction->created_at->year - 1).'/'.$transaction->created_at->year;
@endphp

@push('styles')
<style>
    @media print {
        body { background: white !important; }
        body > header, aside, .receipt-actions, main > div > div:first-child { display: none !important; }
        main { margin: 0 !important; padding: 0 !important; min-height: auto !important; }
        main > div { padding: 0 !important; }
        .receipt-sheet { box-shadow: none !important; border: 0 !important; max-width: none !important; }
    }
</style>
@endpush

@section('content')
<div class="receipt-actions mx-auto mb-6 flex max-w-6xl flex-wrap items-center justify-between gap-3">
    <a href="{{ route('admin.transactions.topup') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-surface-container-high px-5 py-3 text-sm font-bold text-on-surface">
        <span class="material-symbols-outlined">arrow_back</span>
        Top Up Lagi
    </a>
    <button type="button" onclick="window.print()"
            class="inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-bold text-on-primary shadow-lg shadow-primary/20">
        <span class="material-symbols-outlined">print</span>
        Cetak Kwitansi
    </button>
</div>

<article class="receipt-sheet mx-auto max-w-6xl rounded-xl border border-outline-variant/20 bg-white p-6 text-black shadow-sm md:p-10">
    <header class="flex items-center gap-5 border-b-4 border-black pb-5">
        <img src="{{ asset('images/logo.png') }}" alt="Logo Pondok Pesantren Mambaul Hikmah" class="h-24 w-24 object-contain">
        <div>
            <h1 class="text-2xl font-black uppercase md:text-3xl">Kwitansi Top Up Santri</h1>
            <p class="text-lg font-semibold uppercase">Tahun Akademik {{ $academicYear }}</p>
            <h2 class="mt-1 text-xl font-black uppercase md:text-2xl">Pondok Pesantren Mambaul Hikmah</h2>
            <p class="italic">Jl. Kh Muhammad Barmawi, Tegalwangi - Talang - Tegal</p>
        </div>
    </header>

    <section class="grid gap-4 py-7 text-sm md:grid-cols-2 md:text-base">
        <dl class="grid grid-cols-[140px_1fr] gap-y-2">
            <dt>Nomor Bukti</dt>
            <dd class="font-bold">: KW-{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</dd>
            <dt>Tanggal</dt>
            <dd>: {{ $transaction->created_at->format('d/m/Y H:i') }}</dd>
        </dl>
        <dl class="grid grid-cols-[100px_1fr] gap-y-2">
            <dt class="font-bold">NIS</dt>
            <dd class="font-bold">: {{ $transaction->santri->nis }}</dd>
            <dt>Nama</dt>
            <dd>: {{ $transaction->santri->name }}</dd>
            <dt class="font-bold">Alamat</dt>
            <dd class="font-bold">: {{ $transaction->santri->alamat ?: '-' }}</dd>
        </dl>
    </section>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] border-collapse text-center">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border-2 border-black px-3 py-3">No.</th>
                    <th class="border-2 border-black px-3 py-3">Nama Pembayaran</th>
                    <th class="border-2 border-black px-3 py-3">Dibayarkan (Rp.)</th>
                    <th class="border-2 border-black px-3 py-3">Keterangan</th>
                    <th class="border-2 border-black px-3 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border-2 border-black px-3 py-4">1</td>
                    <td class="border-2 border-black px-3 py-4">Top Up Saldo - {{ $transaction->santri->name }}</td>
                    <td class="border-2 border-black px-3 py-4 text-right font-bold">{{ number_format($transaction->nominal, 0, ',', '.') }}</td>
                    <td class="border-2 border-black px-3 py-4">{{ $transaction->keterangan ?: '-' }}</td>
                    <td class="border-2 border-black px-3 py-4 font-black">LUNAS</td>
                </tr>
            </tbody>
        </table>
    </div>

    <p class="mt-8 text-base italic">
        Terbilang dibayar: <span class="font-bold capitalize"># {{ $terbilang }} #</span>
    </p>

    <section class="mt-14 grid gap-12 text-center md:grid-cols-2">
        <div>
            <p>Yang Menerima,</p>
            <p>Bag. Keuangan</p>
            <div class="h-24"></div>
            <p class="font-bold underline">{{ $transaction->petugas->name }}</p>
        </div>
        <div>
            <p>Tegal, {{ $transaction->created_at->format('d/m/Y') }}</p>
            <div class="h-32"></div>
            <p class="font-bold">Saldo setelah top up: Rp {{ number_format($transaction->saldo_setelah, 0, ',', '.') }}</p>
        </div>
    </section>

    <footer class="mt-10 border-t border-dashed border-gray-400 pt-3 text-xs text-gray-600">
        Simpanlah kwitansi ini sebagai bukti top up yang sah. Dicetak otomatis oleh Mawa Smart.
    </footer>
</article>
@endsection

@if(request()->boolean('print'))
    @push('scripts')
        <script>
            window.addEventListener('load', () => window.print());
        </script>
    @endpush
@endif
