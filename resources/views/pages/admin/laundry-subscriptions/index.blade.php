@extends('layouts.app')

@section('header-title', 'Laundry Bulanan')
@php $activeRole = 'admin'; @endphp

@section('content')
<div class="space-y-6">
    <header class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h2 class="font-headline text-3xl font-extrabold tracking-tight text-primary">Pendaftaran Laundry Bulanan</h2>
            <p class="mt-1 text-sm text-on-surface-variant">Top up paket laundry khusus bulanan tanpa mengubah saldo santri.</p>
        </div>
    </header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <form action="{{ route('admin.laundry-subscriptions.store') }}" method="POST" class="xl:col-span-5 rounded-xl bg-surface-container-lowest p-6 shadow-sm space-y-5">
            @csrf
            <div>
                <h3 class="font-headline text-lg font-bold text-primary">Data Pendaftaran</h3>
                <p class="mt-1 text-xs text-on-surface-variant">Satu santri hanya punya satu paket aktif per bulan.</p>
            </div>

            <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant">Santri
                <select name="santri_id" required class="input-field mt-2">
                    <option value="">Pilih santri</option>
                    @foreach($santriList as $santri)
                        <option value="{{ $santri->id }}" @selected(old('santri_id') == $santri->id)>{{ $santri->name }} - {{ $santri->nis ?? '-' }}</option>
                    @endforeach
                </select>
                @error('santri_id') <span class="mt-1 block text-xs text-error">{{ $message }}</span> @enderror
            </label>

            <div class="grid grid-cols-2 gap-4">
                <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant">Bulan
                    <select name="month" required class="input-field mt-2">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" @selected((int) old('month', $month) === $i)>{{ \Carbon\Carbon::create(null, $i)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                    @error('month') <span class="mt-1 block text-xs text-error">{{ $message }}</span> @enderror
                </label>
                <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant">Tahun
                    <input type="number" name="year" value="{{ old('year', $year) }}" required class="input-field mt-2">
                    @error('year') <span class="mt-1 block text-xs text-error">{{ $message }}</span> @enderror
                </label>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant">Biaya Bulanan
                    <input type="number" name="monthly_fee" value="{{ old('monthly_fee', 0) }}" min="0" required class="input-field mt-2">
                    @error('monthly_fee') <span class="mt-1 block text-xs text-error">{{ $message }}</span> @enderror
                </label>
                <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant">Kuota Kg
                    <input type="number" name="quota_kg" value="{{ old('quota_kg', 20) }}" min="1" max="99" step="0.1" class="input-field mt-2">
                    @error('quota_kg') <span class="mt-1 block text-xs text-error">{{ $message }}</span> @enderror
                </label>
            </div>

            <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant">Catatan
                <textarea name="notes" rows="3" class="input-field mt-2">{{ old('notes') }}</textarea>
                @error('notes') <span class="mt-1 block text-xs text-error">{{ $message }}</span> @enderror
            </label>

            <button class="w-full rounded-xl bg-primary py-4 font-headline font-bold text-on-primary shadow-lg shadow-primary/20">
                Simpan Pendaftaran
            </button>
        </form>

        <section class="xl:col-span-7 rounded-xl bg-surface-container-lowest shadow-sm">
            <div class="border-b border-surface-container p-6">
                <form method="GET" action="{{ route('admin.laundry-subscriptions.index') }}" class="flex flex-col gap-3 md:flex-row md:items-end">
                    <label class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Bulan
                        <select name="month" class="input-field mt-2">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" @selected($month === $i)>{{ \Carbon\Carbon::create(null, $i)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                    </label>
                    <label class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Tahun
                        <input type="number" name="year" value="{{ $year }}" class="input-field mt-2">
                    </label>
                    <button class="rounded-xl bg-surface-container-high px-5 py-3 text-sm font-bold text-on-surface">Terapkan</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-surface-container-high/50 text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                        <tr>
                            <th class="px-5 py-4">Santri</th>
                            <th class="px-5 py-4">Biaya</th>
                            <th class="px-5 py-4">Kuota</th>
                            <th class="px-5 py-4">Terpakai</th>
                            <th class="px-5 py-4">Sisa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @forelse($subscriptions as $subscription)
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-bold text-on-surface">{{ $subscription->santri?->name }}</p>
                                    <p class="text-xs text-on-surface-variant">NIS {{ $subscription->santri?->nis ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 font-bold">Rp {{ number_format($subscription->monthly_fee, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">{{ number_format((float) $subscription->quota_kg, 1, ',', '.') }} Kg</td>
                                <td class="px-5 py-4">{{ number_format((float) $subscription->used_kg, 1, ',', '.') }} Kg</td>
                                <td class="px-5 py-4 font-bold text-primary">{{ number_format($subscription->remaining_kg, 1, ',', '.') }} Kg</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-on-surface-variant">Belum ada pendaftaran laundry bulanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($subscriptions->hasPages())
                <div class="border-t border-surface-container p-6">{{ $subscriptions->links() }}</div>
            @endif
        </section>
    </div>
</div>
@endsection
