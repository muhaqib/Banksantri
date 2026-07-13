@extends('layouts.app')

@section('title', 'Kwitansi Top Up')
@section('header-title', 'Kwitansi Top Up')
@php
    $activeRole = 'admin';
    $academicYear = $transaction->created_at->month >= 7
        ? $transaction->created_at->year.'/'.($transaction->created_at->year + 1)
        : ($transaction->created_at->year - 1).'/'.$transaction->created_at->year;
    $logoPath = public_path('images/logo.png');
    $logoSrc = file_exists($logoPath)
        ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
        : asset('images/logo.png');
@endphp

@push('styles')
<style>
    .receipt-sheet {
        width: 210mm;
        min-height: 148mm;
        box-sizing: border-box;
        color: #202020;
        font-family: Arial, Helvetica, sans-serif;
        line-height: 1.25;
    }

    .receipt-header {
        display: grid;
        grid-template-columns: 26mm 1fr;
        gap: 8mm;
        align-items: center;
        border-bottom: 1.1mm solid #111;
        padding-bottom: 5mm;
    }

    .receipt-logo {
        width: 25mm;
        height: 25mm;
        object-fit: contain;
    }

    .receipt-title {
        font-size: 17pt;
        font-weight: 900;
        line-height: 1.05;
        text-transform: uppercase;
    }

    .receipt-year {
        margin-top: 1.5mm;
        font-size: 12pt;
        font-weight: 700;
        text-transform: uppercase;
    }

    .receipt-school {
        margin-top: 1.5mm;
        font-size: 14pt;
        font-weight: 900;
        text-transform: uppercase;
    }

    .receipt-address {
        margin-top: 1mm;
        font-size: 10pt;
        font-style: italic;
    }

    .receipt-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18mm;
        padding: 8mm 0 7mm;
        font-size: 10.5pt;
    }

    .receipt-info dl {
        display: grid;
        grid-template-columns: 32mm 1fr;
        row-gap: 2.5mm;
        margin: 0;
    }

    .receipt-info dt,
    .receipt-info dd {
        margin: 0;
    }

    .receipt-info dd::before {
        content: ": ";
    }

    .receipt-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        text-align: center;
        font-size: 10.5pt;
    }

    .receipt-table th,
    .receipt-table td {
        border: .55mm solid #111;
        padding: 3mm 2.5mm;
        vertical-align: middle;
    }

    .receipt-table th {
        background: #f5f5f5;
        font-size: 11pt;
        font-weight: 900;
    }

    .receipt-table .col-no { width: 11mm; }
    .receipt-table .col-name { width: 73mm; }
    .receipt-table .col-paid { width: 32mm; }
    .receipt-table .col-note { width: 63mm; }
    .receipt-table .col-status { width: 25mm; }

    .receipt-amount {
        text-align: right;
        font-weight: 800;
    }

    .receipt-words {
        margin-top: 8mm;
        font-size: 10.5pt;
        font-style: italic;
    }

    .receipt-signatures {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20mm;
        margin-top: 12mm;
        font-size: 10.5pt;
        text-align: center;
    }

    .signature-space {
        height: 20mm;
    }

    .receipt-footer {
        margin-top: 9mm;
        border-top: .35mm dashed #bdbdbd;
        padding-top: 2mm;
        color: #666;
        font-size: 8pt;
    }

    @media print {
        @page {
            size: A5 landscape;
            margin: 0;
        }

        html, body {
            width: 210mm;
            min-height: 148mm;
            background: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body > header, aside, .receipt-actions, main > div > div:first-child { display: none !important; }
        main { margin: 0 !important; padding: 0 !important; min-height: auto !important; }
        main > div { padding: 0 !important; }

        .receipt-sheet {
            width: 210mm !important;
            min-height: 148mm !important;
            margin: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            padding: 10mm 11mm 8mm !important;
        }

        .receipt-table th,
        .receipt-table td {
            border-color: #111 !important;
        }
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

<article class="receipt-sheet mx-auto rounded-xl border border-outline-variant/20 bg-white p-5 sm:p-6 shadow-sm">
    <header class="receipt-header">
        <img src="{{ $logoSrc }}" alt="Logo Pondok Pesantren Mambaul Hikmah" class="receipt-logo">
        <div>
            <h1 class="receipt-title">Kwitansi Pembayaran Santri</h1>
            <p class="receipt-year">Tahun Akademik {{ $academicYear }}</p>
            <h2 class="receipt-school">Pondok Pesantren Mambaul Hikmah</h2>
            <p class="receipt-address">Alamat: Jl Kh Muhammad Barmawi, Tegalwangi - Talang - Tegal. No Whatsapp +62 813-9375-0612.</p>
        </div>
    </header>

    <section class="receipt-info">
        <dl>
            <dt>Nomor Bukti</dt>
            <dd class="font-bold">KW-{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</dd>
            <dt>Tanggal</dt>
            <dd>{{ $transaction->created_at->translatedFormat('d F Y') }}</dd>
        </dl>
        <dl>
            <dt class="font-bold">NIS</dt>
            <dd class="font-bold">{{ $transaction->santri->nis }}</dd>
            <dt>Nama</dt>
            <dd>{{ $transaction->santri->name }}</dd>
            <dt class="font-bold">Alamat</dt>
            <dd class="font-bold">{{ $transaction->santri->alamat ?: '-' }}</dd>
        </dl>
    </section>

    <div>
        <table class="receipt-table">
            <thead>
                <tr>
                    <th class="col-no">No.</th>
                    <th class="col-name">Nama Pembayaran</th>
                    <th class="col-paid">Dibayarkan (Rp.)</th>
                    <th class="col-note">Keterangan</th>
                    <th class="col-status">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Top Up Saldo - {{ $transaction->santri->name }}</td>
                    <td class="receipt-amount">{{ number_format($transaction->nominal, 0, ',', '.') }}</td>
                    <td>{{ $transaction->keterangan ?: '-' }}</td>
                    <td class="font-black">LUNAS</td>
                </tr>
            </tbody>
        </table>
    </div>

    <p class="receipt-words">
        Terbilang di bayar: <span class="font-bold capitalize"># {{ $terbilang }} #</span>
    </p>

    <section class="receipt-signatures">
        <div>
            <p>Yang Menerima,</p>
            <p>Bag. Keuangan</p>
            <div class="signature-space"></div>
            <p class="font-bold underline">{{ $transaction->petugas->name }}</p>
        </div>
        <div>
            <p>Tegal, {{ $transaction->created_at->translatedFormat('d F Y') }}</p>
            <div class="signature-space"></div>
            <p class="font-bold">Saldo Akhir: Rp {{ number_format($transaction->saldo_setelah, 0, ',', '.') }}</p>
        </div>
    </section>

    <footer class="receipt-footer">
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
