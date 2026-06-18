@extends('layouts.guest')

@section('title', 'Pilih Peran')

@section('content')

<div class="w-full max-w-5xl mx-auto px-4 py-8 md:py-16">

{{-- Header --}}
<div class="text-center mb-10 md:mb-14">


    <div class="text-center mb-8">
        <div class="w-20 h-20 bg-gradient-to-br from-primary to-primary-container rounded-xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-primary/10">
            <span class="material-symbols-outlined text-white text-5xl" style="font-variation-settings: 'FILL' 1;">shield</span>
        </div>
        <h1 class="font-headline text-3xl font-extrabold tracking-tight text-primary">Mawa Smart</h1>
        <p class="font-headline text-sm font-semibold tracking-[0.2em] text-outline mt-1 uppercase">Ponpes Mambaul Hikmah</p>
    </div>

    <h2 class="text-xl md:text-2xl font-extrabold tracking-tight text-primary">
        Pilih Peran Anda
    </h2>
</div>

@php
    $roleCards = [
        'admin' => [
            'label' => 'Admin',
            'icon' => 'business_center',
        ],
        'petugas' => [
            'label' => 'Petugas',
            'icon' => 'how_to_reg',
        ],
        'santri' => [
            'label' => 'Wali Santri / Santri',
            'icon' => 'school',
        ],
    ];
@endphp

{{-- Role Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">

    @foreach($roleCards as $role => $card)

        <a href="{{ route('login.role', $role) }}"
           class="group
                  relative
                  flex md:flex-col
                  items-center md:justify-center
                  gap-4
                  p-5 md:p-8
                  min-h-[95px]
                  md:h-56
                  bg-white
                  border border-gray-200
                  rounded-3xl
                  shadow-sm
                  overflow-hidden
                  transition-all duration-300 ease-out
                  hover:-translate-y-2
                  hover:scale-[1.02]
                  hover:border-primary/30
                  hover:shadow-[0_20px_40px_-15px_rgba(0,0,0,0.15)]
                  active:scale-[0.98]">

            {{-- Glow Background --}}
            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-gradient-to-br from-primary/5 via-transparent to-primary/5"></div>

            {{-- Icon --}}
            <div class="relative z-10
                        w-14 h-14
                        md:w-16 md:h-16
                        flex items-center justify-center
                        rounded-2xl
                        bg-primary/10
                        text-primary
                        transition-all duration-300
                        group-hover:bg-primary
                        group-hover:text-white
                        group-hover:scale-110">

                <span class="material-symbols-outlined text-3xl">
                    {{ $card['icon'] }}
                </span>

            </div>

            {{-- Label --}}
            <div class="relative z-10 text-left md:text-center">

                <h2 class="text-xl md:text-2xl font-semibold text-on-surface transition-colors duration-300">
                    {{ $card['label'] }}
                </h2>

            </div>

        </a>

    @endforeach

</div>

{{-- Register --}}
<div class="text-center mt-8 md:mt-12">

    <a href="{{ route('register') }}"
       class="inline-flex items-center gap-2 text-primary font-medium hover:opacity-80 transition">

        <span class="material-symbols-outlined text-lg">
            person_add
        </span>

        Pendaftaran Santri Baru

    </a>

</div>

</div>
@endsection
