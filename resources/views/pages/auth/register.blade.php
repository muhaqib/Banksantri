@extends('layouts.guest')

@section('title', 'Registrasi Admin')

@section('content')
<div class="w-full max-w-md mx-auto px-4 sm:px-6 transition-all duration-300" x-data="{ showPassword: false, showConfirmPassword: false }">
    <!-- Logo Card -->
    <div class="text-center mb-8">
        <div class="w-20 h-20 bg-gradient-to-br from-primary to-primary-container rounded-xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-primary/10">
            <span class="material-symbols-outlined text-white text-5xl" style="font-variation-settings: 'FILL' 1;">admin_panel_settings</span>
        </div>
        <h1 class="font-headline text-3xl font-extrabold tracking-tight text-primary">Mawa Smart</h1>
        <p class="font-headline text-sm font-semibold tracking-[0.2em] text-outline mt-1 uppercase">Registrasi Admin</p>
    </div>

    <!-- Registration Form -->
    <form action="{{ route('register') }}" method="POST">
        @csrf

        <!-- Name Input -->
        <div class="mb-4">
            <label class="font-label text-xs font-semibold text-on-surface-variant ml-1 block mb-2">
                Nama Lengkap
            </label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 w-1 bg-primary rounded-full scale-y-0 group-focus-within:scale-y-75 transition-transform duration-300"></div>
                <input type="text"
                       name="name"
                       value="{{ old('name') }}"
                       required
                       placeholder="Masukkan nama lengkap"
                       class="w-full bg-surface-container-high border-none rounded-xl py-4 px-5 pl-6 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all placeholder:text-outline/50 font-medium @error('name') ring-2 ring-error @enderror">
            </div>
            @error('name')
                <p class="mt-1 text-xs text-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email Input -->
        <div class="mb-4">
            <label class="font-label text-xs font-semibold text-on-surface-variant ml-1 block mb-2">
                Alamat Email
            </label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 w-1 bg-primary rounded-full scale-y-0 group-focus-within:scale-y-75 transition-transform duration-300"></div>
                <input type="email"
                       name="email"
                       value="{{ old('email') }}"
                       required
                       placeholder="contoh@email.com"
                       class="w-full bg-surface-container-high border-none rounded-xl py-4 px-5 pl-6 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all placeholder:text-outline/50 font-medium @error('email') ring-2 ring-error @enderror">
            </div>
            @error('email')
                <p class="mt-1 text-xs text-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Username Input -->
        <div class="mb-4">
            <label class="font-label text-xs font-semibold text-on-surface-variant ml-1 block mb-2">
                Username
            </label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 w-1 bg-primary rounded-full scale-y-0 group-focus-within:scale-y-75 transition-transform duration-300"></div>
                <input type="text"
                       name="username"
                       value="{{ old('username') }}"
                       required
                       placeholder="Masukkan username"
                       class="w-full bg-surface-container-high border-none rounded-xl py-4 px-5 pl-6 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all placeholder:text-outline/50 font-medium @error('username') ring-2 ring-error @enderror">
            </div>
            @error('username')
                <p class="mt-1 text-xs text-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Input -->
        <div class="mb-4">
            <label class="font-label text-xs font-semibold text-on-surface-variant ml-1 block mb-2">
                Kata Sandi
            </label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 w-1 bg-primary rounded-full scale-y-0 group-focus-within:scale-y-75 transition-transform duration-300"></div>
                <input :type="showPassword ? 'text' : 'password'"
                       name="password"
                       required
                       placeholder="Minimal 6 karakter"
                       class="w-full bg-surface-container-high border-none rounded-xl py-4 px-5 pl-6 pr-12 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all placeholder:text-outline/50 font-medium @error('password') ring-2 ring-error @enderror">
                <button type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[20px]" x-text="showPassword ? 'visibility_off' : 'visibility'"></span>
                </button>
            </div>
            @error('password')
                <p class="mt-1 text-xs text-error">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password Input -->
        <div class="mb-6">
            <label class="font-label text-xs font-semibold text-on-surface-variant ml-1 block mb-2">
                Konfirmasi Kata Sandi
            </label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 w-1 bg-primary rounded-full scale-y-0 group-focus-within:scale-y-75 transition-transform duration-300"></div>
                <input :type="showConfirmPassword ? 'text' : 'password'"
                       name="password_confirmation"
                       required
                       placeholder="Ulangi kata sandi"
                       class="w-full bg-surface-container-high border-none rounded-xl py-4 px-5 pl-6 pr-12 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all placeholder:text-outline/50 font-medium">
                <button type="button"
                        @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[20px]" x-text="showConfirmPassword ? 'visibility_off' : 'visibility'"></span>
                </button>
            </div>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="mb-4 p-4 bg-error-container text-on-error-container rounded-xl text-sm font-medium">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-4 p-4 bg-success-container text-on-success-container rounded-xl text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <!-- Submit Button -->
        <button type="submit"
                class="w-full bg-primary text-on-primary font-headline font-bold py-4 rounded-xl shadow-lg shadow-primary/20 hover:opacity-90 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
            <span>Daftar Sebagai Admin</span>
            <span class="material-symbols-outlined text-[20px]">person_add</span>
        </button>
    </form>

    <!-- Login Link -->
    <div class="mt-6 text-center">
        <p class="text-sm text-on-surface-variant">
            Sudah punya akun? 
            <a href="{{ route('login') }}" class="text-primary font-semibold hover:text-primary-container transition-colors">
                Login di sini
            </a>
        </p>
    </div>

    <!-- Footer -->
    <footer class="mt-8 pt-6 border-t border-surface-container">
        <div class="text-center">
            <p class="text-[10px] text-outline font-medium max-w-[200px] mx-auto">
                Akun admin memiliki akses penuh ke semua fitur sistem.
            </p>
        </div>
    </footer>
</div>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection
