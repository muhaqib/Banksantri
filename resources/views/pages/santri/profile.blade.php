@extends('layouts.santri')

@section('title', 'Profil')

@section('content')
<div x-data="profileSantri()" class="pb-20">
    <!-- Header -->
    <header class="sanctuary-gradient text-white pt-12 pb-20 px-6 rounded-b-3xl shadow-xl shadow-primary/20">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('santri.home') }}" class="p-2 hover:bg-white/10 rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-white">arrow_back</span>
                </a>
                <h1 class="font-headline font-bold text-xl">Profil Saya</h1>
            </div>
        </div>
        
        <div class="flex flex-col items-center text-center">
            <div class="relative mb-4">
                <div class="w-24 h-24 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center ring-4 ring-white/30">
                    @if(auth()->user()->foto)
                        <img src="{{ Storage::url(auth()->user()->foto) }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover rounded-full">
                    @else
                        <span class="material-symbols-filled text-white text-5xl">account_circle</span>
                    @endif
                </div>
                <div class="absolute -bottom-2 -right-2 bg-tertiary-container text-on-tertiary-container w-8 h-8 rounded-full flex items-center justify-center border-4 border-surface">
                    <span class="material-symbols-filled text-sm">verified</span>
                </div>
            </div>
            
            <h2 class="font-headline font-bold text-2xl mb-1">{{ auth()->user()->name ?? 'Santri' }}</h2>
            <p class="text-primary-fixed text-sm">NIS: {{ auth()->user()->nis ?? '-' }}</p>
        </div>
    </header>

    <!-- Profile Content -->
    <div class="px-6 -mt-8 space-y-4">
        <!-- Account Info -->
        <div class="card">
            <h3 class="font-headline font-bold text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">person</span>
                <span>Informasi Akun</span>
            </h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between py-3 border-b border-outline-variant/10">
                    <span class="text-sm text-on-surface-variant">Nama Lengkap</span>
                    <span class="font-medium text-on-surface text-sm">{{ auth()->user()->name ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between py-3 border-b border-outline-variant/10">
                    <span class="text-sm text-on-surface-variant">NIS / ID</span>
                    <span class="font-medium text-on-surface text-sm">{{ auth()->user()->nis ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between py-3 border-b border-outline-variant/10">
                    <span class="text-sm text-on-surface-variant">Email</span>
                    <span class="font-medium text-on-surface text-sm">{{ auth()->user()->email ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm text-on-surface-variant">Bergabung</span>
                    <span class="font-medium text-on-surface text-sm">{{ auth()->user()->created_at?->format('d M Y') ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="card">
            <h3 class="font-headline font-bold text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">lock</span>
                <span>Keamanan</span>
            </h3>
            <div class="space-y-2">
                <button @click="showChangePin = true" class="w-full flex items-center justify-between p-4 bg-surface-container-low rounded-xl hover:bg-surface-container transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary">lock</span>
                        </div>
                        <span class="font-medium text-on-surface">Ganti PIN</span>
                    </div>
                    <span class="material-symbols-outlined text-on-surface-variant">chevron_right</span>
                </button>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <h3 class="font-headline font-bold text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">settings</span>
                <span>Lainnya</span>
            </h3>
            <div class="space-y-2">
                <a href="#" class="w-full flex items-center justify-between p-4 bg-surface-container-low rounded-xl hover:bg-surface-container transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-secondary/10 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-secondary">help</span>
                        </div>
                        <span class="font-medium text-on-surface">Bantuan</span>
                    </div>
                    <span class="material-symbols-outlined text-on-surface-variant">chevron_right</span>
                </a>
                <a href="#" class="w-full flex items-center justify-between p-4 bg-surface-container-low rounded-xl hover:bg-surface-container transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-tertiary/10 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-tertiary">info</span>
                        </div>
                        <span class="font-medium text-on-surface">Tentang</span>
                    </div>
                    <span class="material-symbols-outlined text-on-surface-variant">chevron_right</span>
                </a>
            </div>
        </div>

        <!-- Logout -->
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full bg-error-container text-on-error-container font-bold py-4 rounded-xl hover:bg-error/10 transition-colors flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">logout</span>
                <span>Logout</span>
            </button>
        </form>
    </div>

    <!-- Change PIN Modal -->
    <div x-show="showChangePin" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-surface rounded-2xl shadow-2xl max-w-md w-full p-6 animate-scale-in">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-primary-fixed rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-filled text-primary text-3xl">lock</span>
                    </div>
                    <h3 class="font-headline font-bold text-xl text-on-surface">Ganti PIN</h3>
                    <p class="text-sm text-on-surface-variant mt-1">Masukkan PIN baru Anda</p>
                </div>

                <form action="{{ route('santri.change-pin') }}" method="POST">
                    @csrf
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">PIN Lama</label>
                            <input type="password"
                                   name="old_pin"
                                   required
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   class="input-field text-center text-2xl font-bold tracking-widest"
                                   placeholder="••••••">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">PIN Baru</label>
                            <input type="password"
                                   name="new_pin"
                                   required
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   class="input-field text-center text-2xl font-bold tracking-widest"
                                   placeholder="••••••">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Konfirmasi PIN Baru</label>
                            <input type="password"
                                   name="new_pin_confirmation"
                                   required
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   class="input-field text-center text-2xl font-bold tracking-widest"
                                   placeholder="••••••">
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="button"
                                @click="showChangePin = false"
                                class="flex-1 px-4 py-3 btn-secondary">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 px-4 py-3 btn-primary">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-surface/80 backdrop-blur-xl border-t border-outline-variant/10 px-6 py-2 max-w-md mx-auto rounded-t-3xl shadow-lg">
        <div class="flex items-center justify-around">
            <a href="{{ route('santri.home') }}" class="flex flex-col items-center py-2 text-on-surface-variant opacity-60 hover:opacity-100 hover:bg-surface-container-low transition-all p-2 rounded-xl">
                <span class="material-symbols-outlined text-lg">home</span>
                <span class="font-body text-[10px] font-medium uppercase tracking-widest mt-0.5">Beranda</span>
            </a>
            <a href="{{ route('santri.riwayat') }}" class="flex flex-col items-center py-2 text-on-surface-variant opacity-60 hover:opacity-100 hover:bg-surface-container-low transition-all p-2 rounded-xl">
                <span class="material-symbols-outlined text-lg">history</span>
                <span class="font-body text-[10px] font-medium uppercase tracking-widest mt-0.5">Riwayat</span>
            </a>
            <a href="{{ route('santri.profile') }}" class="flex flex-col items-center py-2 bg-primary text-on-primary rounded-2xl px-4 transition-all shadow-lg shadow-primary/20">
                <span class="material-symbols-filled text-lg">account_circle</span>
                <span class="font-body text-[10px] font-medium uppercase tracking-widest mt-0.5">Profil</span>
            </a>
        </div>
    </nav>
</div>

<script>
function profileSantri() {
    return {
        showChangePin: false
    }
}
</script>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
.material-symbols-filled {
    font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection
