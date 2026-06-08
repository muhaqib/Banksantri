@props(['activeRole' => 'admin'])
@php
    $currentUser = auth()->user();
    $displayRole = $currentUser?->getRoleNames()->first() ?? $currentUser?->role ?? $activeRole;
@endphp

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
                            @if($displayRole === 'admin')
                                Super Admin
                            @elseif($displayRole === 'petugas')
                                Unit Petugas
                            @elseif($displayRole === 'santri')
                                Santri
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
        @php
            $singleMenus = [
                ['permission' => 'admin.dashboard.view', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'dashboard', 'label' => 'Dashboard'],
                ['permission' => 'petugas.dashboard.view', 'route' => 'petugas.dashboard', 'active' => 'petugas.dashboard', 'icon' => 'dashboard', 'label' => 'Dashboard'],
            ];
            $menuGroups = [
                [
                    'label' => 'Data Santri',
                    'icon' => 'school',
                    'active' => ['admin.santri.*', 'admin.kamar.*'],
                    'children' => [
                        ['permission' => 'admin.santri.manage', 'route' => 'admin.santri.index', 'active' => 'admin.santri.index', 'label' => 'Semua Santri'],
                        ['permission' => 'admin.santri.manage', 'route' => 'admin.santri.create', 'active' => 'admin.santri.create', 'label' => 'Tambah Santri'],
                        ['permission' => 'admin.kamar.manage', 'route' => 'admin.kamar.index', 'active' => 'admin.kamar.*', 'label' => 'Data Kamar'],
                    ],
                ],
                [
                    'label' => 'Data Petugas',
                    'icon' => 'group',
                    'active' => ['admin.petugas.*'],
                    'children' => [
                        ['permission' => 'admin.petugas.manage', 'route' => 'admin.petugas.index', 'active' => 'admin.petugas.index', 'label' => 'Semua Petugas'],
                        ['permission' => 'admin.petugas.manage', 'route' => 'admin.petugas.create', 'active' => 'admin.petugas.create', 'label' => 'Tambah Petugas'],
                    ],
                ],
                [
                    'label' => 'Keuangan',
                    'icon' => 'receipt_long',
                    'active' => ['admin.transactions.*', 'admin.kas', 'admin.settlement', 'admin.topup'],
                    'children' => [
                        ['permission' => 'admin.finance.manage', 'route' => 'admin.transactions.santri', 'active' => 'admin.transactions.santri', 'label' => 'Riwayat Transaksi'],
                        ['permission' => 'admin.finance.manage', 'route' => 'admin.transactions.topup', 'active' => 'admin.transactions.topup', 'label' => 'Top Up Saldo'],
                        ['permission' => 'admin.finance.manage', 'route' => 'admin.topup', 'active' => 'admin.topup', 'label' => 'Verifikasi Top Up'],
                        ['permission' => 'admin.finance.manage', 'route' => 'admin.settlement', 'active' => 'admin.settlement', 'label' => 'Settlement'],
                        ['permission' => 'admin.finance.manage', 'route' => 'admin.kas', 'active' => 'admin.kas', 'label' => 'Kas'],
                    ],
                ],
                [
                    'label' => 'Tarbiyah',
                    'icon' => 'military_tech',
                    'active' => ['admin.prestasi.*'],
                    'children' => [
                        ['permission' => 'admin.prestasi.manage', 'route' => 'admin.prestasi.index', 'active' => 'admin.prestasi.index', 'label' => 'Tahfidz'],
                        ['permission' => 'admin.prestasi.manage', 'route' => 'admin.prestasi.create', 'active' => 'admin.prestasi.create', 'label' => 'Tambah Tahfidz'],
                    ],
                ],
                [
                    'label' => 'Kesiswaan',
                    'icon' => 'fact_check',
                    'active' => ['admin.attendance.*', 'admin.permissions.*'],
                    'children' => [
                        ['permission' => 'admin.attendance.manage', 'route' => 'admin.attendance.index', 'active' => 'admin.attendance.index', 'label' => 'Absensi Harian'],
                        ['permission' => 'admin.attendance.manage', 'route' => 'admin.attendance.dashboard', 'active' => 'admin.attendance.dashboard', 'label' => 'Dashboard Kehadiran'],
                        ['permission' => 'admin.attendance.manage', 'route' => 'admin.permissions.index', 'active' => 'admin.permissions.*', 'label' => 'Perizinan Santri'],
                    ],
                ],
                [
                    'label' => 'Prestasi Santri',
                    'icon' => 'military_tech',
                    'active' => ['petugas.prestasi.*'],
                    'children' => [
                        ['permission' => 'petugas.prestasi.manage', 'route' => 'petugas.prestasi.index', 'active' => 'petugas.prestasi.index', 'label' => 'Semua Prestasi'],
                        ['permission' => 'petugas.prestasi.manage', 'route' => 'petugas.prestasi.create', 'active' => 'petugas.prestasi.create', 'label' => 'Input Prestasi'],
                    ],
                ],
                [
                    'label' => 'Absensi Santri',
                    'icon' => 'fact_check',
                    'active' => ['petugas.attendance.*', 'petugas.permissions.*'],
                    'children' => [
                        ['permission' => 'petugas.attendance.manage', 'route' => 'petugas.attendance.index', 'active' => 'petugas.attendance.index', 'label' => 'Absensi Harian'],
                        ['permission' => 'petugas.attendance.manage', 'route' => 'petugas.attendance.dashboard', 'active' => 'petugas.attendance.dashboard', 'label' => 'Dashboard Kehadiran'],
                        ['permission' => 'petugas.attendance.manage', 'route' => 'petugas.permissions.index', 'active' => 'petugas.permissions.*', 'label' => 'Perizinan Santri'],
                    ],
                ],
                [
                    'label' => 'Blog & Artikel',
                    'icon' => 'article',
                    'active' => ['admin.blog.*'],
                    'children' => [
                        ['permission' => 'admin.blog.manage', 'route' => 'admin.blog.index', 'active' => 'admin.blog.index', 'label' => 'Semua Blog'],
                        ['permission' => 'admin.blog.manage', 'route' => 'admin.blog.create', 'active' => 'admin.blog.create', 'label' => 'Tambah Blog'],
                    ],
                ],
                [
                    'label' => 'Keuangan',
                    'icon' => 'payments',
                    'active' => ['petugas.transaksi', 'petugas.riwayat', 'petugas.tarik-tunai'],
                    'children' => [
                        ['permission' => 'petugas.transactions.manage', 'route' => 'petugas.transaksi', 'active' => 'petugas.transaksi', 'label' => 'Transaksi'],
                        ['permission' => 'petugas.history.view', 'route' => 'petugas.riwayat', 'active' => 'petugas.riwayat', 'label' => 'Riwayat'],
                        ['permission' => 'petugas.withdrawals.manage', 'route' => 'petugas.tarik-tunai', 'active' => 'petugas.tarik-tunai', 'label' => 'Tarik Tunai'],
                    ],
                ],
            ];
        @endphp
        <nav class="flex-1 px-3 py-4 overflow-y-auto scrollbar-thin">
            @foreach($singleMenus as $item)
                @can($item['permission'])
                    <a href="{{ route($item['route']) }}"
                       class="{{ request()->routeIs($item['active']) ? 'bg-primary text-on-primary shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-surface-container-low' }} rounded-xl px-4 py-3 flex items-center gap-3 font-body text-sm font-medium transition-all">
                        <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endcan
            @endforeach

            @foreach($menuGroups as $group)
                @php
                    $groupPermissions = collect($group['children'])->pluck('permission')->unique()->all();
                    $isOpen = request()->routeIs(...$group['active']);
                @endphp
                @canany($groupPermissions)
                    <div x-data="{ open: {{ $isOpen ? 'true' : 'false' }} }" class="my-1">
                        <button @click="open = !open"
                                class="w-full {{ $isOpen ? 'bg-primary text-on-primary' : 'text-on-surface-variant hover:bg-surface-container-low' }} rounded-xl px-4 py-3 flex items-center justify-between font-body text-sm font-medium transition-all">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">{{ $group['icon'] }}</span>
                                <span>{{ $group['label'] }}</span>
                            </div>
                            <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                        </button>
                        <div x-show="open" x-collapse class="mt-1 ml-4 space-y-1">
                            @foreach($group['children'] as $child)
                                @can($child['permission'])
                                    <a href="{{ route($child['route']) }}"
                                       class="{{ request()->routeIs($child['active']) ? 'text-primary font-bold bg-surface-container-low' : 'text-on-surface-variant hover:text-primary' }} block px-4 py-2 text-sm rounded-lg transition-all flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        <span>{{ $child['label'] }}</span>
                                    </a>
                                @endcan
                            @endforeach
                        </div>
                    </div>
                @endcanany
            @endforeach

            @can('admin.access.manage')
                <a href="{{ route('admin.access.index') }}"
                   class="{{ request()->routeIs('admin.access.*') ? 'bg-primary text-on-primary shadow-lg shadow-primary/20' : 'text-on-surface-variant hover:bg-surface-container-low' }} rounded-xl px-4 py-3 flex items-center gap-3 font-body text-sm font-medium transition-all">
                    <span class="material-symbols-outlined">admin_panel_settings</span>
                    <span>Manajemen Akses</span>
                </a>
            @endcan
        </nav>

        <!-- User Profile & Logout -->
        <div class="border-t border-outline-variant/10 p-3">
            @php
    $user = $currentUser;
    $profileRoute = null;
    if ($user?->can('admin.profile.manage')) {
        $profileRoute = route('admin.profile');
    } elseif ($user?->can('petugas.profile.manage')) {
        $profileRoute = route('petugas.profile');
    } elseif ($user?->can('santri.profile.manage')) {
        $profileRoute = route('santri.profile');
    }
@endphp

@if($profileRoute)
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
                {{ $displayRole }}
            </p>
        </div>

        <!-- Icon panah (opsional biar keliatan bisa diklik) -->
        <span class="material-symbols-outlined text-on-surface-variant text-sm">
            chevron_right
        </span>

    </div>

</a>
@endif
            
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
