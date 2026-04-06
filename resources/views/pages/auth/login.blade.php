@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="w-full max-w-md mx-auto px-4 sm:px-6 transition-all duration-300" x-data="{ role: 'admin', showPassword: false }">
    <!-- Logo Card -->
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-br from-primary to-primary-container rounded-xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-primary/10">
                <span class="material-symbols-outlined text-white text-5xl" style="font-variation-settings: 'FILL' 1;">shield</span>
            </div>
            <h1 class="font-headline text-3xl font-extrabold tracking-tight text-primary">Mawa Smart</h1>
            <p class="font-headline text-sm font-semibold tracking-[0.2em] text-outline mt-1 uppercase">Ponpes Mambaul Hikmah</p>
        </div>

        <!-- Login Form -->
        <form action="{{ route('login') }}" method="POST">
            @csrf
            
            <!-- Hidden Role Input -->
            <input type="hidden" name="role" x-model="role">

            <!-- Role Selector -->
            <div class="mb-6">
                <label class="font-label text-xs font-semibold text-on-surface-variant ml-1 block mb-3">
                    Login Sebagai
                </label>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button"
                            @click="role = 'admin'"
                            :class="{
                                'bg-primary text-on-primary shadow-lg shadow-primary/20': role === 'admin',
                                'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high': role !== 'admin'
                            }"
                            class="px-4 py-3 rounded-xl font-semibold text-sm transition-all transform active:scale-95">
                        Admin
                    </button>
                    <button type="button"
                            @click="role = 'petugas'"
                            :class="{
                                'bg-primary text-on-primary shadow-lg shadow-primary/20': role === 'petugas',
                                'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high': role !== 'petugas'
                            }"
                            class="px-4 py-3 rounded-xl font-semibold text-sm transition-all transform active:scale-95">
                        Petugas
                    </button>
                    <button type="button"
                            @click="role = 'santri'"
                            :class="{
                                'bg-primary text-on-primary shadow-lg shadow-primary/20': role === 'santri',
                                'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high': role !== 'santri'
                            }"
                            class="px-4 py-3 rounded-xl font-semibold text-sm transition-all transform active:scale-95">
                        Santri
                    </button>
                </div>
            </div>

            <!-- Username/ID Input -->
            <div class="mb-4">
                <label class="font-label text-xs font-semibold text-on-surface-variant ml-1 block mb-2">
                    <span x-text="role === 'santri' ? 'Nomor Induk Santri (NIS)' : 'Username'"></span>
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 w-1 bg-primary rounded-full scale-y-0 group-focus-within:scale-y-75 transition-transform duration-300"></div>
                    <input type="text"
                           name="username"
                           required
                           x-bind:placeholder="role === 'santri' ? 'Contoh: 12345678' : 'Masukkan username'"
                           class="w-full bg-surface-container-high border-none rounded-xl py-4 px-5 pl-6 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all placeholder:text-outline/50 font-medium">
                </div>
            </div>

            <!-- Password Input -->
            <div class="mb-6">
                <label class="font-label text-xs font-semibold text-on-surface-variant ml-1 block mb-2">
                    Kata Sandi / PIN
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 w-1 bg-primary rounded-full scale-y-0 group-focus-within:scale-y-75 transition-transform duration-300"></div>
                    <input :type="showPassword ? 'text' : 'password'"
                           name="password"
                           required
                           placeholder="••••••••"
                           class="w-full bg-surface-container-high border-none rounded-xl py-4 px-5 pl-6 pr-12 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all placeholder:text-outline/50 font-medium">
                    <button type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px]" x-text="showPassword ? 'visibility_off' : 'visibility'"></span>
                    </button>
                </div>
            </div>

            <!-- Forgot Password -->
            <div class="flex justify-end mb-6">
                <button type="button" class="text-sm font-semibold text-primary hover:text-primary-container transition-colors">
                    Lupa PIN / Bermasalah?
                </button>
            </div>

            <!-- Error Message -->
            @if($errors->any())
                <div class="mb-4 p-4 bg-error-container text-on-error-container rounded-xl text-sm font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Submit Button -->
            <button type="submit"
                    class="w-full bg-primary text-on-primary font-headline font-bold py-4 rounded-xl shadow-lg shadow-primary/20 hover:opacity-90 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                <span>Masuk Ke Akun</span>
                <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
            </button>
        </form>

        <!-- Register Link (Admin Only) -->
        <div class="mt-6 text-center" x-show="role === 'admin'">
            <p class="text-sm text-on-surface-variant">
                Belum punya akun admin? 
                <a href="{{ route('register') }}" class="text-primary font-semibold hover:text-primary-container transition-colors">
                    Daftar Sekarang
                </a>
            </p>
        </div>

        <!-- Biometric Section (Optional) -->
        <section class="mt-8 mb-6" x-data="{ biometricEnabled: false }">
            <div class="bg-surface-container-low rounded-2xl p-6 relative overflow-hidden flex items-center gap-4">
                <div class="relative z-10 w-12 h-12 rounded-full bg-surface-container-highest flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">fingerprint</span>
                </div>
                <div class="relative z-10">
                    <p class="text-sm font-bold text-on-surface">Biometric Login</p>
                    <p class="text-xs text-on-surface-variant">Aktifkan untuk akses lebih cepat</p>
                </div>
                <button type="button" 
                        @click="biometricEnabled = !biometricEnabled"
                        class="ml-auto relative z-10 focus:outline-none">
                    <div class="w-10 h-6 bg-outline-variant/30 rounded-full flex items-center px-1 transition-colors"
                         :class="biometricEnabled ? 'bg-primary/50' : ''">
                        <div class="w-4 h-4 bg-white rounded-full shadow-sm transition-transform"
                             :class="biometricEnabled ? 'translate-x-4' : ''"></div>
                    </div>
                </button>
                <div class="absolute right-0 top-0 w-24 h-24 bg-primary/5 rounded-full -mr-8 -mt-8"></div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="mt-8 pt-6 border-t border-surface-container">
            <div class="flex flex-col items-center gap-4">
                <button class="flex items-center gap-2 text-primary font-semibold text-sm py-2 px-4 rounded-full border border-primary/10 hover:bg-primary/5 transition-all">
                    <span class="material-symbols-outlined text-[18px]">contact_support</span>
                    <span>Butuh Bantuan?</span>
                </button>
                <div class="mt-4 px-8 text-center">
                    <p class="text-[10px] text-outline font-medium max-w-[200px]">
                        Aplikasi ini dilindungi oleh enkripsi 256-bit berstandar perbankan syariah.
                    </p>
                </div>
            </div>
        </footer>
</div>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection