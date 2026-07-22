@extends('layouts.santri')

@section('title', 'Profil')

@section('content')
<div x-data="profileSantri()" class="pb-20">
    <!-- Header -->
    <header class="sanctuary-gradient text-white pt-4 pb-20 px-6 rounded-b-3xl shadow-xl shadow-primary/20">
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
                        <span class="material-symbols-outlined text-white text-5xl">account_circle</span>
                    @endif
                </div>
                <div class="absolute -bottom-2 -right-2 bg-tertiary-container text-on-tertiary-container w-8 h-8 rounded-full flex items-center justify-center border-4 border-surface">
                    <span class="material-symbols-outlined text-sm">verified</span>
                </div>
            </div>
            
            <h2 class="font-headline font-bold text-2xl mb-1">{{ auth()->user()->name ?? 'Santri' }}</h2>
            <p class="text-primary-fixed text-sm">NIS: {{ auth()->user()->nis ?? '-' }}</p>
            @if(auth()->user()->isAlumni())
                <span class="mt-2 px-3 py-1 rounded-full bg-white/20 text-xs font-bold">Alumni · Read-only</span>
            @endif
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
                    <span class="text-sm text-on-surface-variant">Nama</span>
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
        <!-- Actions -->
        <div class="card">
            <h3 class="font-headline font-bold text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">settings</span>
                <span>Lainnya</span>
            </h3>
            <div class="space-y-2">
                <a href="https://api.whatsapp.com/send/?phone=6281393750612" target="_blank" rel="noopener noreferrer" class="w-full flex items-center justify-between p-4 bg-surface-container-low rounded-xl hover:bg-surface-container transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-500/10 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-emerald-600">chat</span>
                        </div>
                        <div>
                            <span class="font-medium text-on-surface block">Tanya Admin</span>
                            <span class="text-xs text-on-surface-variant">Hubungi via WhatsApp </span>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-on-surface-variant">open_in_new</span>
                </a>
                <a href="#" class="w-full flex items-center justify-between p-4 bg-surface-container-low rounded-xl hover:bg-surface-container transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-secondary/10 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-secondary">help</span>
                        </div>
                        <span class="font-medium text-on-surface">Bantuan</span>
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

    <!-- Bottom Navigation -->
    <x-santri.bottom-nav />
</div>

<script>
function profileSantri() {
    return {}
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
