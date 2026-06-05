@extends('layouts.guest')

@section('title', 'Pilih Role')

@section('content')
<div class="w-full max-w-3xl mx-auto px-4">
    <div class="text-center mb-8">
        <div class="w-20 h-20 bg-gradient-to-br from-primary to-primary-container rounded-xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-primary/10">
            <span class="material-symbols-outlined text-white text-5xl" style="font-variation-settings: 'FILL' 1;">shield</span>
        </div>
        <h1 class="font-headline text-3xl font-extrabold tracking-tight text-primary">Mawa Smart</h1>
        <p class="font-headline text-sm font-semibold tracking-[0.2em] text-outline mt-1 uppercase">Ponpes Mambaul Hikmah</p>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        @foreach($roles as $role => $label)
            @php
                $icons = ['santri' => 'school', 'admin' => 'admin_panel_settings', 'petugas' => 'badge'];
                $descriptions = [
                    'santri' => 'Tabungan, top up, riwayat, dan prestasi.',
                    'admin' => 'Kontrol data, kas, settlement, dan akses.',
                    'petugas' => 'Transaksi, riwayat, dan tarik tunai.',
                ];
            @endphp
            <a href="{{ route('login.role', $role) }}"
               class="group bg-surface-container-low hover:bg-surface-container-high rounded-xl border border-outline-variant/20 p-5 transition-all active:scale-[0.99]">
                <span class="material-symbols-outlined text-primary text-4xl">{{ $icons[$role] }}</span>
                <span class="block font-headline text-xl font-black text-on-surface mt-4">{{ $label }}</span>
                <span class="block text-sm text-on-surface-variant mt-1 min-h-10">{{ $descriptions[$role] }}</span>
                <span class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-primary">
                    Lanjut Login
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </span>
            </a>
        @endforeach
    </div>
</div>
@endsection
