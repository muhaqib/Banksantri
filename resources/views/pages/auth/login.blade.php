@extends('layouts.guest')

@section('title', 'Login ' . $selectedRoleLabel)

@section('content')
<div class="w-full max-w-md mx-auto px-4 sm:px-6 transition-all duration-300" x-data="{ showPassword: false }">
    <div class="text-center mb-8">
        <div class="w-20 h-20 bg-gradient-to-br from-primary to-primary-container rounded-xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-primary/10">
            <span class="material-symbols-outlined text-white text-5xl" style="font-variation-settings: 'FILL' 1;">shield</span>
        </div>
        <h1 class="font-headline text-3xl font-extrabold tracking-tight text-primary">Mawa Smart</h1>
        <p class="font-headline text-sm font-semibold tracking-[0.2em] text-outline mt-1 uppercase">Ponpes Mambaul Hikmah</p>
    </div>

    <form action="{{ url('/login') }}" method="POST">
        @csrf
        <input type="hidden" name="role" value="{{ $selectedRole }}">

        <div class="mb-6 bg-surface-container-low border border-outline-variant/20 rounded-xl p-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-on-surface-variant">Login Sebagai</p>
                <p class="font-headline font-black text-lg text-on-surface">{{ $selectedRoleLabel }}</p>
            </div>
            <a href="{{ route('login') }}" class="text-sm font-bold text-primary hover:opacity-80">Ganti</a>
        </div>

        <div class="mb-4">
            <label class="font-label text-xs font-semibold text-on-surface-variant ml-1 block mb-2">
                {{ $selectedRole === 'santri' ? 'Nomor Induk Santri (NIS)' : 'Username' }}
            </label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 w-1 bg-primary rounded-full scale-y-0 group-focus-within:scale-y-75 transition-transform duration-300"></div>
                <input type="text"
                       name="username"
                       value="{{ old('username') }}"
                       required
                       placeholder="{{ $selectedRole === 'santri' ? 'Masukkan NIS' : 'Masukkan username' }}"
                       class="w-full bg-surface-container-high border-none rounded-xl py-4 px-5 pl-6 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all placeholder:text-outline/50 font-medium">
            </div>
        </div>

        @if($selectedRole !== 'santri')
        <div class="mb-6">
            <label class="font-label text-xs font-semibold text-on-surface-variant ml-1 block mb-2">Kata Sandi</label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 w-1 bg-primary rounded-full scale-y-0 group-focus-within:scale-y-75 transition-transform duration-300"></div>
                <input :type="showPassword ? 'text' : 'password'"
                       name="password"
                       required
                       placeholder="Masukkan kata sandi"
                       class="w-full bg-surface-container-high border-none rounded-xl py-4 px-5 pl-6 pr-12 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all placeholder:text-outline/50 font-medium">
                <button type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[20px]" x-text="showPassword ? 'visibility_off' : 'visibility'"></span>
                </button>
            </div>
        </div>
        @else
            <div class="mb-6 px-4 py-3 bg-primary-fixed/35 border border-primary/10 rounded-xl flex items-start gap-3 text-sm text-on-primary-fixed-variant">
                <span class="material-symbols-outlined text-primary text-xl">info</span>
                <p>Santri cukup memasukkan NIS untuk masuk tanpa kata sandi.</p>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-4 bg-error-container text-on-error-container rounded-xl text-sm font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <button type="submit"
                class="w-full bg-primary text-on-primary font-headline font-bold py-4 rounded-xl shadow-lg shadow-primary/20 hover:opacity-90 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
            <span>Masuk Ke Akun</span>
            <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
        </button>
    </form>

    @if($selectedRole === 'admin')
        <div class="mt-6 text-center">
            <p class="text-sm text-on-surface-variant">
                Belum punya akun admin?
                <a href="{{ route('register') }}" class="text-primary font-semibold hover:text-primary-container transition-colors">
                    Daftar Sekarang
                </a>
            </p>
        </div>
    @endif
</div>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection
