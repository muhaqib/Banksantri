@extends('layouts.app')

@section('header-title', 'Nota Laundry')
@php $activeRole = 'petugas'; @endphp

@section('content')
<div class="mx-auto max-w-2xl space-y-5">
    <div class="flex items-center justify-between print:hidden">
        <a href="{{ route('petugas.laundry.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-surface-container-high px-4 py-3 text-sm font-bold text-on-surface">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            Transaksi Baru
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-bold text-on-primary">
            <span class="material-symbols-outlined text-lg">print</span>
            Cetak Nota
        </button>
    </div>

    <article class="rounded-xl bg-white p-5 sm:p-6 shadow-sm print:rounded-none print:shadow-none">
        <header class="border-b border-dashed border-outline-variant pb-6 text-center">
            <p class="text-xs font-black uppercase tracking-[0.35em] text-primary">Mawa Smart</p>
            <h1 class="mt-2 font-headline text-2xl font-bold text-on-surface">Nota Laundry</h1>
            <p class="mt-1 text-sm text-on-surface-variant">#LDY-{{ str_pad((string) $transaction->id, 6, '0', STR_PAD_LEFT) }}</p>
        </header>

        <section class="grid grid-cols-2 gap-4 border-b border-dashed border-outline-variant py-6 text-sm">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Santri</p>
                <p class="mt-1 font-bold text-on-surface">{{ $transaction->santri?->name }}</p>
                <p class="text-on-surface-variant">NIS {{ $transaction->santri?->nis ?? '-' }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Tanggal</p>
                <p class="mt-1 font-bold text-on-surface">{{ $transaction->laundry_date->translatedFormat('d F Y') }}</p>
                <p class="text-on-surface-variant">{{ $transaction->created_at->format('H:i') }} WIB</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Petugas</p>
                <p class="mt-1 font-bold text-on-surface">{{ $transaction->petugas?->name }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Pembayaran</p>
                <p class="mt-1 font-bold text-primary">{{ $transaction->payment_method === 'saldo_tabungan' ? 'Saldo Tabungan' : ($transaction->payment_method === 'cash' ? 'Cash' : 'Kuota Bulanan') }}</p>
            </div>
        </section>

        <section class="py-6">
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                <div class="rounded-xl bg-surface-container-low p-4 text-center">
                    <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Berat</p>
                    <p class="mt-2 font-headline text-xl font-bold text-on-surface">{{ number_format((float) $transaction->weight_kg, 1, ',', '.') }} Kg</p>
                </div>
                <div class="rounded-xl bg-surface-container-low p-4 text-center">
                    <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Baju</p>
                    <p class="mt-2 font-headline text-xl font-bold text-on-surface">{{ $transaction->total_clothes }} pcs</p>
                </div>
                <div class="rounded-xl bg-surface-container-low p-4 text-center">
                    <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Jenis</p>
                    <p class="mt-2 font-headline text-xl font-extrabold text-primary">{{ $transaction->payment_type === 'bulanan' ? 'Bulanan' : 'Tunai' }}</p>
                </div>
                @if($transaction->payment_type === 'tunai')
                    <div class="rounded-xl bg-primary/5 p-4 text-center">
                        <p class="text-xs font-bold uppercase tracking-widest text-primary">Total Harga</p>
                        <p class="mt-2 font-headline text-xl font-extrabold text-primary">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</p>
                    </div>
                @endif
            </div>

            <div class="mt-6 overflow-hidden rounded-xl border border-outline-variant/20">
                <table class="w-full text-left text-sm">
                    <thead class="bg-surface-container-low text-xs font-black uppercase tracking-widest text-on-surface-variant">
                        <tr>
                            <th class="px-4 py-3">Rincian</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @foreach($transaction->clothes_detail ?? [] as $item)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-on-surface">{{ $item['label'] }}</td>
                                <td class="px-4 py-3 text-right font-bold">{{ $item['quantity'] }} pcs</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($transaction->payment_type === 'bulanan' && $transaction->subscription)
                <div class="mt-6 rounded-xl bg-primary/5 p-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-bold text-on-surface">Sisa kuota bulan ini</span>
                        <span class="font-headline text-xl font-extrabold text-primary">{{ number_format($transaction->subscription->remaining_kg, 1, ',', '.') }} Kg</span>
                    </div>
                    <div class="mt-3 h-3 overflow-hidden rounded-full bg-white">
                        @php $usedPercent = min(100, ((float) $transaction->subscription->used_kg / max((float) $transaction->subscription->quota_kg, 1)) * 100); @endphp
                        <div class="h-full rounded-full bg-primary" style="width: {{ $usedPercent }}%"></div>
                    </div>
                </div>
            @endif
        </section>

        <footer class="border-t border-dashed border-outline-variant pt-6 text-center">
            <p class="font-headline text-lg font-bold text-on-surface">Terima kasih</p>
            <p class="mt-1 text-xs text-on-surface-variant">Simpan nota ini sebagai bukti transaksi laundry.</p>
        </footer>
    </article>
</div>

@if(session('success'))
    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 400));
    </script>
@endif

<style>
@media print {
    body { background: white !important; }
    aside, header, .print\:hidden { display: none !important; }
    main { margin: 0 !important; padding: 0 !important; }
}
</style>
@endsection
