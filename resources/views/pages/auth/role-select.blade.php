@extends('layouts.guest')

@section('title', 'Pilih Peran')

@section('content')

<div class="role-stage fixed inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
    <div class="role-orb role-orb-primary"></div>
    <div class="role-orb role-orb-secondary"></div>
    <div class="role-grid"></div>
</div>

<div class="role-shell w-full max-w-5xl mx-auto px-4 py-8 md:py-16 relative z-10">

{{-- Header --}}
<div class="text-center mb-10 md:mb-14 role-reveal" style="--delay: 80ms;">


    <div class="text-center mb-8">
        <div class="role-logo w-20 h-20 bg-gradient-to-br from-primary to-primary-container rounded-xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-primary/10">
            <span class="material-symbols-outlined text-white text-5xl role-shield" style="font-variation-settings: 'FILL' 1;">shield</span>
        </div>
        <h1 class="font-headline text-2xl font-bold tracking-tight text-primary role-title">Mawa Smart</h1>
        <p class="font-headline text-sm font-semibold tracking-[0.2em] text-outline mt-1 uppercase role-subtitle">Ponpes Mambaul Hikmah</p>
    </div>

    <h2 class="role-heading text-xl md:text-xl font-bold tracking-tight text-primary">
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
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-4">

    @foreach($roleCards as $role => $card)

        <a href="{{ route('login.role', $role) }}"
           class="group
                  role-card
                  role-reveal
                  relative
                  flex md:flex-col
                  items-center md:justify-center
                  gap-4
                  p-5 md:p-5 sm:p-6
                  min-h-[95px]
                  md:h-56
                  bg-white
                  border border-gray-200
                  rounded-xl
                  shadow-sm
                  overflow-hidden
                  transition-all duration-300 ease-out
                  hover:-translate-y-2
                  hover:scale-[1.02]
                  hover:border-primary/30
                  hover:shadow-[0_20px_40px_-15px_rgba(0,0,0,0.15)]
                  active:scale-[0.98]"
           style="--delay: {{ 220 + ($loop->index * 110) }}ms;">

            {{-- Glow Background --}}
            <div class="role-card-glow absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-gradient-to-br from-primary/5 via-transparent to-primary/5"></div>

            {{-- Icon --}}
            <div class="role-icon relative z-10
                        h-11 w-11
                        md:w-16 md:h-16
                        flex items-center justify-center
                        rounded-xl
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
<div class="role-reveal text-center mt-8 md:mt-12" style="--delay: 620ms;">

    <a href="{{ route('register') }}"
       class="role-register inline-flex items-center gap-2 text-primary font-medium hover:opacity-80 transition">

        <span class="material-symbols-outlined text-lg">
            person_add
        </span>

        Pendaftaran Santri Baru

    </a>

</div>

</div>
@endsection

@push('styles')
<style>
.role-stage {
    background:
        radial-gradient(circle at 50% 0%, rgba(162, 240, 238, 0.38), transparent 34%),
        linear-gradient(180deg, rgba(248, 250, 250, 0.24), rgba(248, 250, 250, 0));
}

.role-orb {
    position: absolute;
    width: 19rem;
    height: 19rem;
    border-radius: 9999px;
    filter: blur(34px);
    opacity: 0.42;
    animation: role-float 8s ease-in-out infinite;
}

.role-orb-primary {
    top: -6rem;
    left: max(-7rem, calc(50% - 34rem));
    background: rgba(0, 103, 102, 0.22);
}

.role-orb-secondary {
    right: max(-8rem, calc(50% - 34rem));
    bottom: -7rem;
    background: rgba(204, 167, 59, 0.18);
    animation-delay: -3s;
}

.role-grid {
    position: absolute;
    inset: 0;
    opacity: 0.38;
    background-image:
        linear-gradient(rgba(0, 77, 76, 0.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 77, 76, 0.05) 1px, transparent 1px);
    background-size: 42px 42px;
    mask-image: radial-gradient(circle at center, black 0%, transparent 70%);
}

.role-shell {
    animation: role-shell-in 680ms cubic-bezier(0.16, 1, 0.3, 1) both;
}

.role-reveal {
    opacity: 0;
    transform: translateY(18px) scale(0.98);
    animation: role-reveal-in 620ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
    animation-delay: var(--delay, 0ms);
}

.role-logo {
    position: relative;
    overflow: hidden;
    animation: role-logo-pop 720ms cubic-bezier(0.34, 1.56, 0.64, 1) both 140ms;
}

.role-logo::after {
    content: '';
    position: absolute;
    inset: -45%;
    background: linear-gradient(105deg, transparent 35%, rgba(255, 255, 255, 0.52) 50%, transparent 65%);
    transform: translateX(-70%) rotate(8deg);
    animation: role-shine 1.9s ease-out 760ms both;
}

.role-shield {
    animation: role-shield-breathe 3.2s ease-in-out 1.5s infinite;
}

.role-title {
    animation: role-text-focus 700ms ease-out both 260ms;
}

.role-subtitle {
    animation: role-text-focus 700ms ease-out both 360ms;
}

.role-heading {
    animation: role-heading-in 620ms cubic-bezier(0.16, 1, 0.3, 1) both 460ms;
}

.role-card {
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04), 0 18px 44px rgba(0, 77, 76, 0);
}

.role-card::after {
    content: '';
    position: absolute;
    inset: auto 1.25rem 0;
    height: 3px;
    border-radius: 9999px 9999px 0 0;
    background: linear-gradient(90deg, transparent, rgba(0, 77, 76, 0.48), transparent);
    opacity: 0;
    transform: scaleX(0.35);
    transition: opacity 300ms ease, transform 300ms ease;
}

.role-card:hover::after {
    opacity: 1;
    transform: scaleX(1);
}

.role-card:hover .role-icon {
    box-shadow: 0 14px 28px rgba(0, 77, 76, 0.18);
}

.role-card:hover .material-symbols-outlined {
    animation: role-icon-pulse 520ms ease-out both;
}

.role-card-glow {
    transform: translateY(12px);
    transition: opacity 300ms ease, transform 300ms ease;
}

.role-card:hover .role-card-glow {
    transform: translateY(0);
}

.role-register:hover {
    transform: translateY(-1px);
}

.role-register .material-symbols-outlined {
    transition: transform 240ms ease;
}

.role-register:hover .material-symbols-outlined {
    transform: rotate(-6deg) scale(1.08);
}

@keyframes role-shell-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes role-reveal-in {
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes role-logo-pop {
    0% {
        opacity: 0;
        transform: translateY(14px) scale(0.74) rotate(-8deg);
    }
    70% {
        transform: translateY(0) scale(1.06) rotate(1deg);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1) rotate(0deg);
    }
}

@keyframes role-shine {
    to {
        transform: translateX(70%) rotate(8deg);
    }
}

@keyframes role-shield-breathe {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.08);
    }
}

@keyframes role-text-focus {
    from {
        opacity: 0;
        letter-spacing: 0.04em;
        filter: blur(4px);
    }
    to {
        opacity: 1;
        filter: blur(0);
    }
}

@keyframes role-heading-in {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes role-icon-pulse {
    0% {
        transform: scale(1);
    }
    45% {
        transform: scale(1.14);
    }
    100% {
        transform: scale(1);
    }
}

@keyframes role-float {
    0%, 100% {
        transform: translate3d(0, 0, 0) scale(1);
    }
    50% {
        transform: translate3d(1.2rem, -0.9rem, 0) scale(1.06);
    }
}

@media (prefers-reduced-motion: reduce) {
    .role-shell,
    .role-reveal,
    .role-logo,
    .role-logo::after,
    .role-shield,
    .role-title,
    .role-subtitle,
    .role-heading,
    .role-card:hover .material-symbols-outlined,
    .role-orb {
        animation: none;
    }

    .role-reveal,
    .role-shell,
    .role-heading {
        opacity: 1;
        transform: none;
    }
}
</style>
@endpush
