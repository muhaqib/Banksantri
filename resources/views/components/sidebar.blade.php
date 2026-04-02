@props(['activeRole' => 'admin'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bank Pesantren Admin')</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface-variant": "#e1e3e3",
                        "primary-fixed-dim": "#86d4d2",
                        "on-tertiary": "#ffffff",
                        "error-container": "#ffdad6",
                        "primary-fixed": "#a2f0ee",
                        "inverse-on-surface": "#eff1f1",
                        "on-secondary-fixed": "#191d0e",
                        "secondary": "#5c614d",
                        "surface-tint": "#076968",
                        "surface": "#f8fafa",
                        "on-tertiary-fixed-variant": "#584400",
                        "on-primary-container": "#94e2e0",
                        "primary-container": "#006766",
                        "surface-dim": "#d8dada",
                        "on-primary-fixed-variant": "#00504f",
                        "on-background": "#191c1d",
                        "tertiary-fixed-dim": "#eac254",
                        "surface-container": "#eceeee",
                        "tertiary": "#755b00",
                        "on-surface": "#191c1d",
                        "surface-bright": "#f8fafa",
                        "secondary-fixed-dim": "#c4c9b1",
                        "on-surface-variant": "#3e4948",
                        "surface-container-highest": "#e1e3e3",
                        "outline": "#6f7978",
                        "secondary-fixed": "#e0e5cc",
                        "on-secondary": "#ffffff",
                        "secondary-container": "#dee2c9",
                        "outline-variant": "#bec9c8",
                        "on-secondary-container": "#606551",
                        "on-tertiary-fixed": "#241a00",
                        "on-primary": "#ffffff",
                        "inverse-surface": "#2e3131",
                        "inverse-primary": "#86d4d2",
                        "on-error-container": "#93000a",
                        "primary": "#004d4c",
                        "background": "#f8fafa",
                        "on-primary-fixed": "#00201f",
                        "surface-container-high": "#e6e8e8",
                        "on-error": "#ffffff",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f2f4f4",
                        "on-tertiary-container": "#503d00",
                        "error": "#ba1a1a",
                        "tertiary-fixed": "#ffdf90",
                        "on-secondary-fixed-variant": "#444936",
                        "tertiary-container": "#cca73b"
                    },
                    fontFamily: {
                        "headline": ["Manrope"],
                        "body": ["Inter"],
                        "label": ["Inter"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
    
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    
    @stack('styles')
</head>
<body class="bg-surface font-body text-on-surface">
    <!-- SideNavBar -->
    <aside class="fixed left-0 top-0 bottom-0 w-64 border-r-0 bg-surface flex flex-col h-full py-6 z-50">
        <div class="px-6 mb-8 flex items-center gap-3">
            <div class="w-10 h-10 bg-primary-container rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-white" style="font-variation-settings: 'FILL' 1;">account_balance</span>
            </div>
            <div>
                <h1 class="text-lg font-black text-primary font-headline tracking-tight leading-none">Bank Pesantren</h1>
                <p class="text-xs tracking-wide text-slate-500">
                    @if($activeRole === 'admin')
                        Super Admin
                    @elseif($activeRole === 'petugas')
                        Unit Petugas
                    @endif
                </p>
            </div>
        </div>
        
        <nav class="flex-1 px-2">
            @if($activeRole === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-primary text-on-primary' : 'text-slate-600 hover:bg-surface-container-low' }} rounded-xl mx-2 my-1 px-4 py-3 flex items-center gap-3 font-body text-sm tracking-wide transition-all">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.kas') }}" class="{{ request()->routeIs('admin.kas') ? 'bg-primary text-on-primary' : 'text-slate-600 hover:bg-surface-container-low' }} rounded-xl mx-2 my-1 px-4 py-3 flex items-center gap-3 font-body text-sm tracking-wide transition-all">
                    <span class="material-symbols-outlined">account_balance_wallet</span>
                    <span>Cash Control</span>
                </a>
                
                <!-- Top Up & Transaksi Menu -->
                <div x-data="{ open: {{ request()->routeIs('admin.transactions.*') ? 'true' : 'false' }} }" class="mx-2 my-1">
                    <button @click="open = !open"
                            class="w-full {{ request()->routeIs('admin.transactions.*') ? 'bg-primary text-on-primary' : 'text-slate-600 hover:bg-surface-container-low' }} rounded-xl px-4 py-3 flex items-center justify-between font-body text-sm tracking-wide transition-all">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">add_card</span>
                            <span>Top Up & Transaksi</span>
                        </div>
                        <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="open" x-collapse class="mt-1 ml-4 space-y-1">
                        <a href="{{ route('admin.transactions.topup') }}" class="{{ request()->routeIs('admin.transactions.topup') ? 'text-primary font-bold' : 'text-slate-500 hover:text-primary' }} block px-4 py-2 text-sm rounded-lg hover:bg-surface-container-low transition-all">
                            • Top Up Saldo
                        </a>
                        <a href="{{ route('admin.transactions.santri') }}" class="{{ request()->routeIs('admin.transactions.santri') ? 'text-primary font-bold' : 'text-slate-500 hover:text-primary' }} block px-4 py-2 text-sm rounded-lg hover:bg-surface-container-low transition-all">
                            • Data Santri
                        </a>
                        <a href="{{ route('admin.transactions.history') }}" class="{{ request()->routeIs('admin.transactions.history') ? 'text-primary font-bold' : 'text-slate-500 hover:text-primary' }} block px-4 py-2 text-sm rounded-lg hover:bg-surface-container-low transition-all">
                            • Riwayat Transaksi
                        </a>
                    </div>
                </div>
                
                <a href="{{ route('admin.petugas.index') }}" class="{{ request()->routeIs('admin.petugas.*') ? 'bg-primary text-on-primary' : 'text-slate-600 hover:bg-surface-container-low' }} rounded-xl mx-2 my-1 px-4 py-3 flex items-center gap-3 font-body text-sm tracking-wide transition-all">
                    <span class="material-symbols-outlined">group</span>
                    <span>User Management</span>
                </a>
                <a href="{{ route('admin.settlement') }}" class="{{ request()->routeIs('admin.settlement') ? 'bg-primary text-on-primary' : 'text-slate-600 hover:bg-surface-container-low' }} rounded-xl mx-2 my-1 px-4 py-3 flex items-center gap-3 font-body text-sm tracking-wide transition-all">
                    <span class="material-symbols-outlined">analytics</span>
                    <span>Reports</span>
                </a>
            @elseif($activeRole === 'petugas')
                <a href="{{ route('petugas.dashboard') }}" class="{{ request()->routeIs('petugas.dashboard') ? 'bg-primary text-on-primary' : 'text-slate-600 hover:bg-surface-container-low' }} rounded-xl mx-2 my-1 px-4 py-3 flex items-center gap-3 font-body text-sm tracking-wide transition-all">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('petugas.transaksi') }}" class="{{ request()->routeIs('petugas.transaksi') ? 'bg-primary text-on-primary' : 'text-slate-600 hover:bg-surface-container-low' }} rounded-xl mx-2 my-1 px-4 py-3 flex items-center gap-3 font-body text-sm tracking-wide transition-all">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">payments</span>
                    <span>Transactions</span>
                </a>
                <a href="{{ route('petugas.riwayat') }}" class="{{ request()->routeIs('petugas.riwayat') ? 'bg-primary text-on-primary' : 'text-slate-600 hover:bg-surface-container-low' }} rounded-xl mx-2 my-1 px-4 py-3 flex items-center gap-3 font-body text-sm tracking-wide transition-all">
                    <span class="material-symbols-outlined">analytics</span>
                    <span>Report</span>
                </a>
                <a href="{{ route('petugas.tarik-tunai') }}" class="{{ request()->routeIs('petugas.tarik-tunai') ? 'bg-primary text-on-primary' : 'text-slate-600 hover:bg-surface-container-low' }} rounded-xl mx-2 my-1 px-4 py-3 flex items-center gap-3 font-body text-sm tracking-wide transition-all">
                    <span class="material-symbols-outlined">download</span>
                    <span>Tarik Tunai</span>
                </a>
            @endif
        </nav>
        
        <div class="px-6 mt-auto">
            <div class="border-t border-surface-container pt-4 space-y-2">
                <div class="flex items-center gap-3 px-2 py-2">
                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary text-sm">account_circle</span>
                    </div>
                    <div class="flex-1">
                        <p class="font-headline font-bold text-sm text-primary">{{ auth()->user()->name ?? 'User' }}</p>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest">{{ $activeRole }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-slate-600 mx-2 my-1 px-4 py-3 flex items-center gap-3 font-body text-sm tracking-wide hover:bg-surface-container-low rounded-xl transition-all">
                        <span class="material-symbols-outlined">logout</span>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- TopAppBar -->
    <header class="fixed top-0 right-0 left-0 z-40 bg-surface/80 backdrop-blur-xl flex justify-between items-center w-full px-6 py-3 ml-64 max-w-[calc(100%-16rem)]">
        <div class="flex items-center gap-4 flex-1">
            <div class="relative w-full max-w-md">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                <input class="w-full pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all" placeholder="Search..." type="text"/>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <button class="p-2 text-slate-500 hover:bg-surface-container-low rounded-full transition-colors">
                <span class="material-symbols-outlined">notifications</span>
            </button>
            <div class="flex items-center gap-3 pl-4 border-l border-outline-variant/30">
                <div class="text-right">
                    <p class="font-headline font-bold text-sm text-primary">{{ auth()->user()->name ?? 'Admin' }}</p>
                    <p class="text-[10px] text-slate-500 font-medium tracking-widest uppercase">{{ $activeRole === 'admin' ? 'Super Admin' : 'Petugas' }}</p>
                </div>
                <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center border-2 border-primary-container/20">
                    <span class="material-symbols-outlined text-primary text-sm">account_circle</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Canvas -->
    <main class="ml-64 pt-20 px-8 pb-12 min-h-screen bg-surface">
        <div class="max-w-7xl mx-auto space-y-8">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                     class="p-4 bg-primary-container/10 border border-primary/20 text-on-primary-container rounded-xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                        {{ session('success') }}
                    </div>
                    <button @click="show = false" class="text-primary hover:opacity-80">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                     class="p-4 bg-error-container border border-error/20 text-on-error-container rounded-xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-error" style="font-variation-settings: 'FILL' 1;">error</span>
                        {{ session('error') }}
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
        // Alpine.js for notifications
        document.addEventListener('alpine:init', () => {
            // Custom Alpine directives if needed
        });
    </script>
    @stack('scripts')
</body>
</html>
