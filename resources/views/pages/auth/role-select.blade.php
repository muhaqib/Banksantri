@extends('layouts.guest')

@section('title', 'Pilih Peran')

@section('content')
<div class="relative w-full max-w-6xl mx-auto px-2 py-3 sm:px-4 sm:py-12 overflow-hidden">
    <div class="hidden md:block absolute -top-24 -left-24 w-72 h-72 bg-primary-fixed/40 rounded-full blur-3xl pointer-events-none"></div>
    <div class="hidden md:block absolute -bottom-24 -right-24 w-80 h-80 bg-primary-container/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative text-center mb-7 md:mb-14">
        <div class="hidden md:flex w-16 h-16 bg-gradient-to-br from-primary to-primary-container rounded-2xl items-center justify-center mx-auto mb-5 shadow-xl shadow-primary/20">
            <span class="material-symbols-outlined text-white text-4xl" style="font-variation-settings: 'FILL' 1;">account_balance</span>
        </div>
        <p class="hidden md:block font-headline text-sm font-bold tracking-[0.2em] text-primary uppercase mb-2">Mawa Smart</p>
        <h1 class="font-headline text-2xl md:text-4xl font-extrabold tracking-tight text-on-surface">Pilih Peran Anda</h1>
        <p class="hidden md:block text-on-surface-variant mt-3">Masuk ke layanan sesuai peran Anda di pesantren.</p>
    </div>

    @php
        $roleCards = [
            'admin' => ['label' => 'Admin', 'icon' => 'business_center', 'description' => 'Kelola data dan operasional pesantren.'],
            'petugas' => ['label' => 'Petugas', 'icon' => 'how_to_reg', 'description' => 'Kelola transaksi dan prestasi santri.'],
            'santri' => ['label' => 'Santri', 'icon' => 'school', 'description' => 'Akses tabungan dan pencapaian Anda.'],
        ];
    @endphp

    <div class="relative grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-7">
        @foreach($roleCards as $role => $card)
            <a href="{{ route('login.role', $role) }}"
               class="group min-h-24 md:min-h-64 flex flex-row md:flex-col items-center justify-start md:justify-center text-left md:text-center p-4 md:p-8 bg-surface-container-low md:bg-surface-container-lowest/90 border border-outline-variant/30 rounded-2xl md:rounded-3xl shadow-sm transition-all duration-300 hover:shadow-lg md:hover:shadow-2xl hover:shadow-primary/10 hover:border-primary/30 md:hover:-translate-y-2 focus:outline-none focus:ring-2 focus:ring-primary">
                <div class="w-16 h-16 md:w-20 md:h-20 shrink-0 mr-5 md:mr-0 md:mb-7 flex items-center justify-center bg-surface-container-lowest md:bg-primary-fixed/40 text-primary rounded-xl md:rounded-2xl shadow-sm group-hover:bg-primary group-hover:text-on-primary group-hover:shadow-lg group-hover:shadow-primary/20 transition-all duration-300">
                    <span class="material-symbols-outlined text-3xl md:text-4xl">{{ $card['icon'] }}</span>
                </div>
                <span class="font-headline text-xl md:text-2xl font-extrabold text-on-surface">{{ $card['label'] }}</span>
                <span class="hidden md:block text-sm text-on-surface-variant mt-2">{{ $card['description'] }}</span>
                <span class="hidden md:inline-flex items-center gap-1 text-sm font-bold text-primary mt-6 opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all">
                    Lanjut Login
                    <span class="material-symbols-outlined text-lg">arrow_forward</span>
                </span>
            </a>
        @endforeach
    </div>

    <div class="relative text-center mt-7 md:mt-14">
        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 text-sm md:text-base font-headline font-bold text-primary hover:text-primary-container transition-colors">
            <span class="hidden md:inline material-symbols-outlined text-xl">person_add</span>
            Pendaftaran Santri Baru
        </a>
    </div>
</div>
@endsection
