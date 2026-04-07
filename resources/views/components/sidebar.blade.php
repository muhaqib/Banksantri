@props(['activeRole' => 'admin'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mawa Smart')</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-filled {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-surface font-body text-on-surface" x-data="{ sidebarOpen: false }">
    <!-- Mobile Header -->
    <header class="lg:hidden fixed top-0 left-0 right-0 z-50 bg-surface border-b border-outline-variant/10 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = true" class="p-2 hover:bg-surface-container-low rounded-lg transition-colors">
                <span class="material-symbols-outlined text-on-surface">menu</span>
            </button>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-primary-container rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-white text-sm">mh</span>
                </div>
                <h1 class="font-headline font-bold text-primary text-sm">Mawa Smart</h1>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center overflow-hidden">
                @if(auth()->user()->foto)
                    <img src="{{ Storage::url(auth()->user()->foto) }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                @else
                    <span class="material-symbols-outlined text-primary text-sm">account_circle</span>
                @endif
            </div>
        </div>
    </header>

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
         class="lg:hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 transition-opacity"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    <!-- SideNavBar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed left-0 top-0 bottom-0 w-72 border-r-0 bg-surface flex flex-col h-full z-50 lg:translate-x-0 lg:w-64 transition-transform duration-300 ease-in-out shadow-2xl lg:shadow-none">
        
        <!-- Logo Section -->
        <div class="px-6 py-6 border-b border-outline-variant/10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary-container rounded-xl flex items-center justify-center">
                        <span class="material-symbols-filled text-white">mh</span>
                    </div>
                    <div>
                        <h1 class="text-lg font-black text-primary font-headline tracking-tight leading-none">Mawa Smart</h1>
                        <p class="text-xs tracking-wide text-on-surface-variant">
                            @if($activeRole === 'admin')
                                Super Admin
                            @elseif($activeRole === 'petugas')
                                Unit Petugas
                            @endif
                        </p>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden p-2 hover:bg-surface-container-low rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-on-surface">close</span>
                </button>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 overflow-y-auto scrollbar-thin">
            @if($activeRole === 'admin')
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="{{ request()->routeIs('admin.dashboard') ? 'bg-primary text-on-primary shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-surface-container-low' }} rounded-xl px-4 py-3 flex items-center gap-3 font-body text-sm font-medium transition-all">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>


                <!-- Data Santri -->
                <div x-data="{ open: {{ request()->routeIs('admin.santri.*') ? 'true' : 'false' }} }" class="my-1">
                    <button @click="open = !open"
                            class="w-full {{ request()->routeIs('admin.santri.*') ? 'bg-primary text-on-primary' : 'text-on-surface-variant hover:bg-surface-container-low' }} rounded-xl px-4 py-3 flex items-center justify-between font-body text-sm font-medium transition-all">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">school</span>
                            <span>Data Santri</span>
                        </div>
                        <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="open" x-collapse class="mt-1 ml-4 space-y-1">
                        <a href="{{ route('admin.santri.index') }}" 
                           class="{{ request()->routeIs('admin.santri.index') ? 'text-primary font-bold bg-surface-container-low' : 'text-on-surface-variant hover:text-primary' }} block px-4 py-2 text-sm rounded-lg transition-all flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            <span>Semua Santri</span>
                        </a>
                        <a href="{{ route('admin.santri.create') }}" 
                           class="{{ request()->routeIs('admin.santri.create') ? 'text-primary font-bold bg-surface-container-low' : 'text-on-surface-variant hover:text-primary' }} block px-4 py-2 text-sm rounded-lg transition-all flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            <span>Tambah Santri</span>
                        </a>
                        <a href="{{ route('admin.transactions.topup') }}" 
                           class="{{ request()->routeIs('admin.transactions.topup') ? 'text-primary font-bold bg-surface-container-low' : 'text-on-surface-variant hover:text-primary' }} block px-4 py-2 text-sm rounded-lg transition-all flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            <span>Top Up Saldo</span>
                        </a>
                    </div>
                </div>

                <!-- Data Petugas -->
                <div x-data="{ open: {{ request()->routeIs('admin.petugas.*') ? 'true' : 'false' }} }" class="my-1">
                    <button @click="open = !open"
                            class="w-full {{ request()->routeIs('admin.petugas.*') ? 'bg-primary text-on-primary' : 'text-on-surface-variant hover:bg-surface-container-low' }} rounded-xl px-4 py-3 flex items-center justify-between font-body text-sm font-medium transition-all">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">group</span>
                            <span>Data Petugas</span>
                        </div>
                        <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="open" x-collapse class="mt-1 ml-4 space-y-1">
                        <a href="{{ route('admin.petugas.index') }}" 
                           class="{{ request()->routeIs('admin.petugas.index') ? 'text-primary font-bold bg-surface-container-low' : 'text-on-surface-variant hover:text-primary' }} block px-4 py-2 text-sm rounded-lg transition-all flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            <span>Semua Petugas</span>
                        </a>
                        <a href="{{ route('admin.petugas.create') }}" 
                           class="{{ request()->routeIs('admin.petugas.create') ? 'text-primary font-bold bg-surface-container-low' : 'text-on-surface-variant hover:text-primary' }} block px-4 py-2 text-sm rounded-lg transition-all flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            <span>Tambah Petugas</span>
                        </a>
                    </div>
                </div>

                <!-- Transactions & History -->
                <div x-data="{ open: {{ request()->routeIs('admin.transactions.*') ? 'true' : 'false' }} }" class="my-1">
                    <button @click="open = !open"
                            class="w-full {{ request()->routeIs('admin.transactions.*') ? 'bg-primary text-on-primary' : 'text-on-surface-variant hover:bg-surface-container-low' }} rounded-xl px-4 py-3 flex items-center justify-between font-body text-sm font-medium transition-all">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">receipt_long</span>
                            <span>Transaksi</span>
                        </div>
                        <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="open" x-collapse class="mt-1 ml-4 space-y-1">
                        <a href="{{ route('admin.transactions.santri') }}" 
                           class="{{ request()->routeIs('admin.transactions.santri') ? 'text-primary font-bold bg-surface-container-low' : 'text-on-surface-variant hover:text-primary' }} block px-4 py-2 text-sm rounded-lg transition-all flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            <span>Riwayat Transaksi</span>
                        </a>
                    </div>
                </div>

                <!-- Settlement -->
                <a href="{{ route('admin.settlement') }}"
                   class="{{ request()->routeIs('admin.settlement') ? 'bg-primary text-on-primary shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-surface-container-low' }} rounded-xl px-4 py-3 flex items-center gap-3 font-body text-sm font-medium transition-all">
                    <span class="material-symbols-outlined">verified_user</span>
                    <span>Settlement</span>
                    @if(($pendingSettlementCount ?? 0) > 0)
                        <span class="ml-auto bg-error text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingSettlementCount }}</span>
                    @endif
                </a>

                <!-- Top Up Verification -->
                <a href="{{ route('admin.topup') }}"
                   class="{{ request()->routeIs('admin.topup') ? 'bg-primary text-on-primary shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-surface-container-low' }} rounded-xl px-4 py-3 flex items-center gap-3 font-body text-sm font-medium transition-all">
                    <span class="material-symbols-outlined">add_circle</span>
                    <span>Verifikasi Top Up</span>
                    @if($pendingTopUpCount ?? 0 > 0)
                        <span class="ml-auto bg-error text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingTopUpCount ?? 0 }}</span>
                    @endif
                </a>

                <!-- Prestasi Santri -->
                <div x-data="{ open: {{ request()->routeIs('admin.prestasi.*') ? 'true' : 'false' }} }" class="my-1">
                    <button @click="open = !open"
                            class="w-full {{ request()->routeIs('admin.prestasi.*') ? 'bg-primary text-on-primary' : 'text-on-surface-variant hover:bg-surface-container-low' }} rounded-xl px-4 py-3 flex items-center justify-between font-body text-sm font-medium transition-all">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">military_tech</span>
                            <span>Prestasi Santri</span>
                        </div>
                        <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="open" x-collapse class="mt-1 ml-4 space-y-1">
                        <a href="{{ route('admin.prestasi.index') }}"
                           class="{{ request()->routeIs('admin.prestasi.index') ? 'text-primary font-bold bg-surface-container-low' : 'text-on-surface-variant hover:text-primary' }} block px-4 py-2 text-sm rounded-lg transition-all flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            <span>Semua Prestasi</span>
                        </a>
                        <a href="{{ route('admin.prestasi.create') }}"
                           class="{{ request()->routeIs('admin.prestasi.create') ? 'text-primary font-bold bg-surface-container-low' : 'text-on-surface-variant hover:text-primary' }} block px-4 py-2 text-sm rounded-lg transition-all flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            <span>Tambah Prestasi</span>
                        </a>
                    </div>
                </div>
            @elseif($activeRole === 'petugas')
                <a href="{{ route('petugas.dashboard') }}" 
                   class="{{ request()->routeIs('petugas.dashboard') ? 'bg-primary text-on-primary shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-surface-container-low' }} rounded-xl px-4 py-3 flex items-center gap-3 font-body text-sm font-medium transition-all">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('petugas.transaksi') }}" 
                   class="{{ request()->routeIs('petugas.transaksi') ? 'bg-primary text-on-primary shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-surface-container-low' }} rounded-xl px-4 py-3 flex items-center gap-3 font-body text-sm font-medium transition-all">
                    <span class="material-symbols-outlined">payments</span>
                    <span>Transaksi</span>
                </a>
                <a href="{{ route('petugas.riwayat') }}" 
                   class="{{ request()->routeIs('petugas.riwayat') ? 'bg-primary text-on-primary shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-surface-container-low' }} rounded-xl px-4 py-3 flex items-center gap-3 font-body text-sm font-medium transition-all">
                    <span class="material-symbols-outlined">history</span>
                    <span>Riwayat</span>
                </a>
                <a href="{{ route('petugas.tarik-tunai') }}" 
                   class="{{ request()->routeIs('petugas.tarik-tunai') ? 'bg-primary text-on-primary shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-surface-container-low' }} rounded-xl px-4 py-3 flex items-center gap-3 font-body text-sm font-medium transition-all">
                    <span class="material-symbols-outlined">download</span>
                    <span>Tarik Tunai</span>
                </a>
            @endif
        </nav>

        <!-- User Profile & Logout -->
        <div class="border-t border-outline-variant/10 p-3">
            @php
    $user = auth()->user();
    $profileRoute = route(($activeRole ?? 'santri') . '.profile');
@endphp

<a href="{{ $profileRoute }}" 
   class="block border-t border-outline-variant/10 p-3 hover:bg-surface-container-low transition-all rounded-xl">

    <div class="flex items-center gap-3">
        
        <!-- Foto -->
        <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden">
            @if($user && $user->foto)
                <img src="{{ Storage::url($user->foto) }}" 
                     alt="{{ $user->name }}" 
                     class="w-full h-full object-cover">
            @else
                <span class="material-symbols-outlined text-primary">account_circle</span>
            @endif
        </div>

        <!-- Nama & Role -->
        <div class="flex-1 min-w-0">
            <p class="font-headline font-bold text-sm text-on-surface truncate">
                {{ $user->name ?? 'User' }}
            </p>
            <p class="text-[10px] text-on-surface-variant uppercase tracking-widest">
                {{ $activeRole ?? 'santri' }}
            </p>
        </div>

        <!-- Icon panah (opsional biar keliatan bisa diklik) -->
        <span class="material-symbols-outlined text-on-surface-variant text-sm">
            chevron_right
        </span>

    </div>

</a>
            
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full text-error hover:bg-error/10 px-4 py-3 flex items-center gap-3 font-body text-sm font-medium rounded-xl transition-all">
                    <span class="material-symbols-outlined">logout</span>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Canvas -->
    <main class="lg:ml-64 pt-16 lg:pt-0 min-h-screen bg-surface">
 

        <!-- Page Content -->
        <div class="p-4 lg:p-8">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="mb-6 p-4 bg-primary-fixed rounded-xl border border-primary/20 text-on-primary-container flex items-center gap-3 animate-slide-in">
                    <span class="material-symbols-outlined text-primary">check_circle</span>
                    <div class="flex-1">
                        <p class="font-bold text-primary">{{ session('success') }}</p>
                    </div>
                    <button @click="show = false" class="text-primary hover:opacity-80">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="mb-6 p-4 bg-error-container rounded-xl border border-error/20 text-on-error-container flex items-center gap-3 animate-slide-in">
                    <span class="material-symbols-filled text-error">error</span>
                    <div class="flex-1">
                        <p class="font-bold">{{ session('error') }}</p>
                    </div>
                    <button @click="show = false" class="text-error hover:opacity-80">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script>
        document.addEventListener('alpine:init', () => {
            // Auto-hide sidebar on mobile when route changes
            document.addEventListener('click', (e) => {
                if (e.target.tagName === 'A' && window.innerWidth < 1024) {
                    window.Alpine.store('sidebar', false);
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
