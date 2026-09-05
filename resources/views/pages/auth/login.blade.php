@extends('layouts.guest')

@section('title', 'Login ' . $selectedRoleLabel)

@section('content')
<div class="login-stage fixed inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
    <div class="login-orb login-orb-primary"></div>
    <div class="login-orb login-orb-secondary"></div>
    <div class="login-grid"></div>
</div>

<div class="login-shell w-full max-w-md mx-auto px-4 sm:px-6 transition-all duration-300 relative z-10" x-data="{ showPassword: false }">
    <div class="text-center mb-8 login-reveal" style="--delay: 80ms;">
        <div class="login-logo w-20 h-20 bg-gradient-to-br from-primary to-primary-container rounded-xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-primary/10 p-2">
            <img src="{{ asset('images/logo.png') }}" alt="Mawa Smart" class="h-full w-auto object-contain">
        </div>
        <h1 class="font-headline text-2xl font-bold tracking-tight text-primary login-title">Mawa Smart</h1>
        <p class="font-headline text-sm font-semibold tracking-[0.2em] text-outline mt-1 uppercase login-subtitle">Ponpes Mambaul Hikmah</p>
    </div>

    <form action="{{ url('/login') }}" method="POST">
        @csrf
        <input type="hidden" name="role" value="{{ $selectedRole }}">

        <div class="login-reveal mb-6 bg-surface-container-low border border-outline-variant/20 rounded-[28px] p-4 flex items-center gap-4 shadow-sm shadow-primary/5 backdrop-blur-sm" style="--delay: 180ms;">
            <a href="{{ route('login') }}" class="login-back inline-flex h-11 w-11 items-center justify-center rounded-xl bg-surface-container-high text-primary shadow-sm transition hover:bg-primary/10">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
            </a>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-on-surface-variant mb-1">Login sebagai</p>
                <p class="font-headline font-bold text-xl text-on-surface truncate">{{ $selectedRoleLabel }}</p>
            </div>
        </div>

        <div class="login-reveal mb-4" style="--delay: 280ms;">
            <label class="font-label text-xs font-semibold text-on-surface-variant ml-1 block mb-2">
                {{ $selectedRole === 'santri' ? 'Nomor Induk Santri (NIS)' : 'Email/Username' }}
            </label>
            <div class="login-field relative group">
                <div class="absolute inset-y-0 left-0 w-1 bg-primary rounded-full scale-y-0 group-focus-within:scale-y-75 transition-transform duration-300"></div>
                <input type="text"
                       name="username"
                       value="{{ old('username') }}"
                       required
                       placeholder="{{ $selectedRole === 'santri' ? 'Masukkan NIS' : 'muhammad@mawa.com' }}"
                       class="w-full bg-surface-container-high border-none rounded-xl py-4 px-5 pl-6 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all placeholder:text-outline/50 font-medium">
            </div>
        </div>

        @if($selectedRole !== 'santri')
        <div class="login-reveal mb-6" style="--delay: 380ms;">
            <label class="font-label text-xs font-semibold text-on-surface-variant ml-1 block mb-2">Kata Sandi</label>
            <div class="login-field relative group">
                <div class="absolute inset-y-0 left-0 w-1 bg-primary rounded-full scale-y-0 group-focus-within:scale-y-75 transition-transform duration-300"></div>
                <input :type="showPassword ? 'text' : 'password'"
                       name="password"
                       required
                       placeholder="Masukkan kata sandi"
                       class="w-full bg-surface-container-high border-none rounded-xl py-4 px-5 pl-6 pr-12 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all placeholder:text-outline/50 font-medium">
                <button type="button"
                        @click="showPassword = !showPassword"
                        class="login-eye absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[20px]" x-text="showPassword ? 'visibility_off' : 'visibility'"></span>
                </button>
            </div>
        </div>
        @else
            <div class="login-reveal mb-6 px-4 py-3 bg-primary-fixed/35 border border-primary/10 rounded-xl flex items-start gap-3 text-sm text-on-primary-fixed-variant" style="--delay: 380ms;">
                <span class="material-symbols-outlined text-primary text-xl">info</span>
                <p>Masukkan NIS Santri.</p>
            </div>
        @endif

        @if($errors->any())
            <div class="login-error mb-4 p-4 bg-error-container text-on-error-container rounded-xl text-sm font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <button type="submit"
                class="login-reveal login-submit w-full bg-primary text-on-primary font-headline font-bold py-4 rounded-xl shadow-lg shadow-primary/20 hover:opacity-90 active:scale-[0.98] transition-all flex items-center justify-center gap-2" style="--delay: 480ms;">
            <span>Masuk Ke Akun</span>
            <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
        </button>
    </form>

    {{-- [DISABLED] Fitur registrasi admin dinonaktifkan --}}
</div>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}

.login-stage {
    background:
        radial-gradient(circle at 50% 0%, rgba(162, 240, 238, 0.38), transparent 34%),
        linear-gradient(180deg, rgba(248, 250, 250, 0.24), rgba(248, 250, 250, 0));
}

.login-orb {
    position: absolute;
    width: 19rem;
    height: 19rem;
    border-radius: 9999px;
    filter: blur(34px);
    opacity: 0.42;
    animation: login-float 8s ease-in-out infinite;
}

.login-orb-primary {
    top: -6rem;
    left: max(-7rem, calc(50% - 34rem));
    background: rgba(0, 103, 102, 0.22);
}

.login-orb-secondary {
    right: max(-8rem, calc(50% - 34rem));
    bottom: -7rem;
    background: rgba(204, 167, 59, 0.18);
    animation-delay: -3s;
}

.login-grid {
    position: absolute;
    inset: 0;
    opacity: 0.38;
    background-image:
        linear-gradient(rgba(0, 77, 76, 0.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 77, 76, 0.05) 1px, transparent 1px);
    background-size: 42px 42px;
    mask-image: radial-gradient(circle at center, black 0%, transparent 70%);
}

.login-shell {
    animation: login-shell-in 680ms cubic-bezier(0.16, 1, 0.3, 1) both;
}

.login-reveal {
    opacity: 0;
    transform: translateY(18px) scale(0.98);
    animation: login-reveal-in 620ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
    animation-delay: var(--delay, 0ms);
}

.login-logo {
    position: relative;
    overflow: hidden;
    animation: login-logo-pop 720ms cubic-bezier(0.34, 1.56, 0.64, 1) both 140ms;
}

.login-logo::after {
    content: '';
    position: absolute;
    inset: -45%;
    background: linear-gradient(105deg, transparent 35%, rgba(255, 255, 255, 0.52) 50%, transparent 65%);
    transform: translateX(-70%) rotate(8deg);
    animation: login-shine 1.9s ease-out 760ms both;
}

.login-shield {
    animation: login-shield-breathe 3.2s ease-in-out 1.5s infinite;
}

.login-title {
    animation: login-text-focus 700ms ease-out both 260ms;
}

.login-subtitle {
    animation: login-text-focus 700ms ease-out both 360ms;
}

.login-field input {
    box-shadow: inset 0 0 0 1px rgba(190, 201, 200, 0);
}

.login-field input:focus {
    box-shadow: 0 16px 36px rgba(0, 77, 76, 0.09), inset 0 0 0 1px rgba(0, 77, 76, 0.18);
    transform: translateY(-1px);
}

.login-back:hover {
    transform: translateX(-2px) scale(1.04);
}

.login-eye:hover {
    transform: translateY(-50%) scale(1.08);
}

.login-submit {
    position: relative;
    overflow: hidden;
}

.login-submit::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transform: translateX(-100%);
    transition: transform 600ms ease;
}

.login-submit:hover::before {
    transform: translateX(100%);
}

.login-error {
    animation: login-error-in 420ms cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

@keyframes login-shell-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes login-reveal-in {
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes login-logo-pop {
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

@keyframes login-shine {
    to {
        transform: translateX(70%) rotate(8deg);
    }
}

@keyframes login-shield-breathe {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.08);
    }
}

@keyframes login-text-focus {
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

@keyframes login-error-in {
    0% {
        opacity: 0;
        transform: translateX(-8px);
    }
    70% {
        transform: translateX(3px);
    }
    100% {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes login-float {
    0%, 100% {
        transform: translate3d(0, 0, 0) scale(1);
    }
    50% {
        transform: translate3d(1.2rem, -0.9rem, 0) scale(1.06);
    }
}

@media (prefers-reduced-motion: reduce) {
    .login-shell,
    .login-reveal,
    .login-logo,
    .login-logo::after,
    .login-shield,
    .login-title,
    .login-subtitle,
    .login-error,
    .login-orb {
        animation: none;
    }

    .login-reveal,
    .login-shell {
        opacity: 1;
        transform: none;
    }
}
</style>
@endsection
