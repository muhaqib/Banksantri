@props(['activeRole' => 'admin'])
@php
    $currentUser = auth()->user();
    $displayRole = $currentUser?->getRoleNames()->first() ?? $currentUser?->role ?? $activeRole;
    $profileRoute = null;

    if ($currentUser?->can('admin.profile.manage')) {
        $profileRoute = route('admin.profile');
    } elseif ($currentUser?->can('petugas.profile.manage')) {
        $profileRoute = route('petugas.profile');
    } elseif ($currentUser?->can('santri.profile.manage')) {
        $profileRoute = route('santri.profile');
    }

    $profileName = $currentUser?->name ?? 'User';
    $profileInitials = strtoupper(substr($profileName, 0, 2));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mawa Smart')</title>
    
    <script>
        // Immediately apply theme to avoid flash of light theme
        if (localStorage.getItem('darkMode') === 'true' || 
            (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
        } else {
            document.documentElement.classList.add('light');
            document.documentElement.classList.remove('dark');
        }
    </script>

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
<body class="bg-surface font-body text-on-surface" x-data="{ sidebarOpen: false, darkMode: false, toggleDarkMode() { this.darkMode = !this.darkMode; localStorage.setItem('darkMode', this.darkMode ? 'true' : 'false'); this.applyTheme(); }, applyTheme() { if (this.darkMode) { document.documentElement.classList.add('dark'); document.documentElement.classList.remove('light'); } else { document.documentElement.classList.add('light'); document.documentElement.classList.remove('dark'); } } }" x-init="darkMode = localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches); applyTheme();">
    <!-- Mobile Header -->
    <header class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-surface border-b border-outline-variant/10 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = true" class="p-2 hover:bg-surface-container-low rounded-lg transition-colors">
                <span class="material-symbols-outlined text-on-surface">menu</span>
            </button>
            <div class="flex items-center gap-2">
                <img src="{{ asset('mawablack.png') }}" alt="Mawa Smart" class="h-8 w-auto object-contain dark:hidden">
                <img src="{{ asset('mawagold.png') }}" alt="Mawa Smart" class="h-8 w-auto object-contain hidden dark:block">
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- Toggle Dark Mode Mobile -->
            <button @click="toggleDarkMode()" class="p-2 hover:bg-surface-container-low rounded-lg text-on-surface-variant hover:text-on-surface transition-colors flex items-center justify-center" aria-label="Toggle tema">
                <span class="material-symbols-outlined text-[20px]" x-text="darkMode ? 'light_mode' : 'dark_mode'"></span>
            </button>

            <div x-data="{ open: false }" class="relative">
            <button type="button"
                    @click="open = !open"
                    @keydown.escape.window="open = false"
                    class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-primary/10 text-sm font-bold text-primary ring-1 ring-primary/10 transition-all hover:bg-primary/15 focus:outline-none focus:ring-2 focus:ring-primary/30"
                    aria-label="Menu profil">
                @if($currentUser && $currentUser->foto)
                    <img src="{{ Storage::url($currentUser->foto) }}" alt="{{ $profileName }}" class="h-full w-full object-cover">
                @else
                    {{ $profileInitials }}
                @endif
            </button>

            <div x-show="open"
                 x-cloak
                 @click.outside="open = false"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-1"
                 class="absolute right-0 mt-3 w-64 overflow-hidden rounded-xl border border-outline-variant/20 bg-surface-container-lowest shadow-xl">
                <div class="border-b border-outline-variant/10 px-4 py-3">
                    <p class="truncate text-sm font-bold text-on-surface">{{ $profileName }}</p>
                    <p class="truncate text-xs text-on-surface-variant">{{ $currentUser?->email }}</p>
                </div>
                @if($profileRoute)
                    <a href="{{ $profileRoute }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-on-surface transition-colors hover:bg-surface-container-low">
                        <span class="material-symbols-outlined text-lg text-primary">manage_accounts</span>
                        <span>Pengaturan Akun</span>
                    </a>
                @endif
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm font-semibold text-error transition-colors hover:bg-error/10">
                        <span class="material-symbols-outlined text-lg">logout</span>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
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
           class="fixed left-0 top-0 bottom-0 w-72 border-r border-outline-variant/10 bg-surface-container-lowest flex flex-col h-full z-50 lg:translate-x-0 lg:w-60 transition-transform duration-300 ease-in-out shadow-2xl lg:shadow-none">
        
        <!-- Logo Section -->
        <div class="px-6 py-5 border-b border-outline-variant/10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center shrink-0">
                        <img src="{{ asset('mawablack.png') }}" alt="Mawa Smart" class="h-10 w-auto object-contain dark:hidden">
                        <img src="{{ asset('mawagold.png') }}" alt="Mawa Smart" class="h-10 w-auto object-contain hidden dark:block">
                    </div>
                    <div>
                        <h1 class="text-base font-black text-primary font-headline tracking-tight leading-none">Mawa Smart</h1>
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
                ['permission' => 'admin.dashboard-content.manage', 'route' => 'admin.dashboard-content.index', 'active' => 'admin.dashboard-content.*', 'icon' => 'dynamic_feed', 'label' => 'Konten Dashboard'],
                ['permission' => 'admin.wa-schedules.manage', 'route' => 'admin.wa-schedules.index', 'active' => 'admin.wa-schedules.*', 'icon' => 'forum', 'label' => 'WA Mawasmart'],
            ];
            $menuGroups = [
                [
                    'label' => 'Data Santri',
                    'icon' => 'school',
                    'active' => ['admin.santri.*', 'admin.kamar.*', 'petugas.santri.*'],
                    'children' => [
                        ['permission' => 'admin.santri.manage', 'route' => 'admin.santri.index', 'active' => 'admin.santri.index', 'label' => 'Semua Santri'],
                        ['permission' => 'admin.santri.manage', 'route' => 'admin.santri.create', 'active' => 'admin.santri.create', 'label' => 'Tambah Santri'],
                        ['permission' => 'admin.kamar.manage', 'route' => 'admin.kamar.index', 'active' => 'admin.kamar.*', 'label' => 'Data Kamar'],
                        ['permission' => 'petugas.santri.manage', 'route' => 'petugas.santri.index', 'active' => 'petugas.santri.index', 'label' => 'Data Santri'],
                        ['permission' => 'petugas.santri.manage', 'route' => 'petugas.santri.master', 'active' => 'petugas.santri.master', 'label' => 'Master Santri'],
                        ['permission' => 'petugas.santri.manage', 'route' => 'petugas.santri.create', 'active' => 'petugas.santri.create', 'label' => 'Tambah Santri'],
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
                        ['permission' => 'admin.finance.manage', 'route' => 'admin.transactions.history', 'active' => 'admin.transactions.history', 'label' => 'Riwayat Eksekusi'],
                        ['permission' => 'admin.finance.manage', 'route' => 'admin.transactions.topup', 'active' => 'admin.transactions.topup', 'label' => 'Top Up Saldo'],
                        ['permission' => 'admin.finance.manage', 'route' => 'admin.topup', 'active' => 'admin.topup', 'label' => 'Verifikasi Top Up'],
                        ['permission' => 'admin.finance.manage', 'route' => 'admin.settlement', 'active' => 'admin.settlement', 'label' => 'Penarikan Tunai'],
                        ['permission' => 'admin.finance.manage', 'route' => 'admin.kas', 'active' => 'admin.kas', 'label' => 'Kas'],
                    ],
                ],
                [
                    'label' => 'Laundry',
                    'icon' => 'local_laundry_service',
                    'active' => ['admin.laundry-subscriptions.*', 'admin.laundry-clothes.*'],
                    'children' => [
                        ['permission' => 'admin.laundry.manage', 'route' => 'admin.laundry-subscriptions.index', 'active' => 'admin.laundry-subscriptions.*', 'label' => 'Laundry Bulanan'],
                    ],
                ],
                [
                    'label' => 'Tarbiyah',
                    'icon' => 'military_tech',
                    'active' => ['admin.prestasi.*', 'admin.tarbiyah.*', 'admin.classes.*'],
                    'children' => [
                        ['permission' => 'admin.tarbiyah.manage', 'route' => 'admin.classes.pondok.index', 'active' => 'admin.classes.pondok.*', 'label' => 'Kelas Pondok'],
                        ['permission' => 'admin.tarbiyah.manage', 'route' => 'admin.classes.formal.index', 'active' => 'admin.classes.formal.*', 'label' => 'Kelas Formal'],
                        ['permission' => 'admin.tarbiyah.manage', 'route' => 'admin.tarbiyah.subjects.index', 'active' => 'admin.tarbiyah.*', 'label' => 'Mapel Tarbiyah'],
                        ['permission' => 'admin.prestasi.manage', 'route' => 'admin.prestasi.index', 'active' => 'admin.prestasi.index', 'label' => 'Tahfidz'],
                        ['permission' => 'admin.prestasi.manage', 'route' => 'admin.prestasi.create', 'active' => 'admin.prestasi.create', 'label' => 'Tambah Tahfidz'],
                    ],
                ],
                [
                    'label' => 'Kesiswaan',
                    'icon' => 'fact_check',
                    'active' => ['admin.attendance.*', 'admin.permissions.*'],
                    'children' => [
                        ['permission' => 'admin.attendance.dashboard', 'route' => 'admin.attendance.dashboard', 'active' => 'admin.attendance.dashboard', 'label' => 'Dashboard Kehadiran'],
                        ['permission' => 'admin.attendance.rfid', 'route' => 'admin.attendance.rfid', 'active' => 'admin.attendance.rfid', 'label' => 'RFID Presensi'],
                        ['permission' => 'admin.attendance.manual', 'route' => 'admin.attendance.manual', 'active' => 'admin.attendance.manual', 'label' => 'Presensi Manual'],
                        ['permission' => 'admin.attendance.monthly', 'route' => 'admin.attendance.monthly', 'active' => 'admin.attendance.monthly', 'label' => 'Rekap Bulanan'],
                        ['permission' => 'admin.permissions.manage', 'route' => 'admin.permissions.index', 'active' => 'admin.permissions.*', 'label' => 'Perizinan Santri'],
                    ],
                ],
                [
                    'label' => 'Keuangan',
                    'icon' => 'payments',
                    'active' => ['petugas.finance-dashboard', 'petugas.transaksi', 'petugas.riwayat', 'petugas.tarik-tunai'],
                    'children' => [
                        ['permission' => 'petugas.finance.dashboard', 'route' => 'petugas.finance-dashboard', 'active' => 'petugas.finance-dashboard', 'label' => 'Dashboard Keuangan'],
                        ['permission' => 'petugas.transactions.manage', 'route' => 'petugas.transaksi', 'active' => 'petugas.transaksi', 'label' => 'Transaksi'],
                        ['permission' => 'petugas.history.view', 'route' => 'petugas.riwayat', 'active' => 'petugas.riwayat', 'label' => 'Riwayat'],
                        ['permission' => 'petugas.withdrawals.manage', 'route' => 'petugas.tarik-tunai', 'active' => 'petugas.tarik-tunai', 'label' => 'Tarik Tunai'],
                    ],
                ],
                [
                    'label' => 'Laundry',
                    'icon' => 'local_laundry_service',
                    'active' => ['petugas.laundry.*', 'petugas.laundry.history'],
                    'children' => [
                        ['permission' => 'petugas.laundry.manage', 'route' => 'petugas.laundry.index', 'active' => 'petugas.laundry.index', 'label' => 'Transaksi Laundry'],
                        ['permission' => 'petugas.laundry.history', 'route' => 'petugas.laundry.history', 'active' => 'petugas.laundry.history', 'label' => 'Riwayat Laundry'],
                    ],
                ],
                [
                    'label' => 'Tarbiyah',
                    'icon' => 'menu_book',
                    'active' => ['petugas.tarbiyah.*'],
                    'children' => [
                        ['permission' => 'petugas.tarbiyah.manage', 'route' => 'petugas.tarbiyah.dashboard', 'active' => 'petugas.tarbiyah.dashboard', 'label' => 'Dashboard Nilai Iktibar'],
                        ['permission' => 'petugas.tarbiyah.manage', 'route' => 'petugas.tarbiyah.index', 'active' => 'petugas.tarbiyah.index', 'label' => 'Input Nilai Iktibar'],
                    ],
                ],
                [
                    'label' => 'Tahfidz',
                    'icon' => 'military_tech',
                    'active' => ['petugas.prestasi.*'],
                    'children' => [
                        ['permission' => 'petugas.prestasi.manage', 'route' => 'petugas.prestasi.index', 'active' => 'petugas.prestasi.index', 'label' => 'Dashboard Tahfidz'],
                        ['permission' => 'petugas.prestasi.manage', 'route' => 'petugas.prestasi.create', 'active' => 'petugas.prestasi.create', 'label' => 'Input Tahfidz'],
                    ],
                ],
                [
                    'label' => 'Kesiswaan',
                    'icon' => 'groups',
                    'active' => ['petugas.health.*', 'petugas.security.*', 'petugas.permissions.*'],
                    'children' => [
                        ['permission' => 'petugas.health.manage', 'route' => 'petugas.health.index', 'active' => 'petugas.health.*', 'label' => 'Kesehatan Santri'],
                        ['permission' => 'petugas.security.manage', 'route' => 'petugas.security.index', 'active' => 'petugas.security.*', 'label' => 'Pelanggaran Santri'],
                        ['permission' => 'petugas.permissions.manage', 'route' => 'petugas.permissions.index', 'active' => 'petugas.permissions.*', 'label' => 'Perizinan Santri'],
                    ],
                ],
                [
                    'label' => 'Absen Kamar',
                    'icon' => 'fact_check',
                    'active' => ['petugas.attendance.*'],
                    'children' => [
                        ['permission' => 'petugas.attendance.dashboard', 'route' => 'petugas.attendance.dashboard', 'active' => 'petugas.attendance.dashboard', 'label' => 'Dashboard Absen'],
                        ['permission' => 'petugas.attendance.rfid', 'route' => 'petugas.attendance.rfid', 'active' => 'petugas.attendance.rfid', 'label' => 'RFID Presensi'],
                        ['permission' => 'petugas.attendance.manual', 'route' => 'petugas.attendance.manual', 'active' => 'petugas.attendance.manual', 'label' => 'Presensi Manual'],
                        ['permission' => 'petugas.attendance.monthly', 'route' => 'petugas.attendance.monthly', 'active' => 'petugas.attendance.monthly', 'label' => 'Rekap Bulanan'],
                    ],
                ],
                [
                    'label' => 'Blog',
                    'icon' => 'article',
                    'active' => ['admin.blog.*', 'petugas.blog.*'],
                    'children' => [
                        ['permission' => 'admin.blog.manage', 'route' => 'admin.blog.index', 'active' => 'admin.blog.index', 'label' => 'Semua Blog'],
                        ['permission' => 'admin.blog.manage', 'route' => 'admin.blog.create', 'active' => 'admin.blog.create', 'label' => 'Tambah Blog'],
                        ['permission' => 'petugas.blog.manage', 'route' => 'petugas.blog.index', 'active' => 'petugas.blog.index', 'label' => 'Semua Blog'],
                        ['permission' => 'petugas.blog.manage', 'route' => 'petugas.blog.create', 'active' => 'petugas.blog.create', 'label' => 'Tambah Blog'],
                    ],
                ],
            ];
        @endphp
        <nav class="flex-1 px-2.5 py-4 overflow-y-auto scrollbar-thin space-y-1">
            @foreach($singleMenus as $item)
                @can($item['permission'])
                    <a href="{{ route($item['route']) }}"
                       class="{{ request()->routeIs($item['active']) ? 'bg-primary text-on-primary shadow-sm shadow-primary/5' : 'text-on-surface-variant hover:bg-surface-container-low' }} rounded-lg px-3.5 py-2.5 flex items-center gap-3 font-body text-sm font-medium transition-all">
                        <span class="material-symbols-outlined text-[20px]">{{ $item['icon'] }}</span>
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
                    <div x-data="{ open: {{ $isOpen ? 'true' : 'false' }} }" class="my-0.5">
                        <button @click="open = !open"
                                class="w-full {{ $isOpen ? 'bg-primary text-on-primary shadow-sm shadow-primary/5' : 'text-on-surface-variant hover:bg-surface-container-low' }} rounded-lg px-3.5 py-2.5 flex items-center justify-between font-body text-sm font-medium transition-all">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">{{ $group['icon'] }}</span>
                                <span>{{ $group['label'] }}</span>
                            </div>
                            <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                        </button>
                        <div x-show="open" x-collapse class="mt-0.5 ml-3.5 space-y-0.5 pl-2 border-l border-outline-variant/10">
                            @foreach($group['children'] as $child)
                                @can($child['permission'])
                                    <a href="{{ route($child['route']) }}"
                                       class="{{ request()->routeIs($child['active']) ? 'text-primary font-semibold bg-surface-container-low' : 'text-on-surface-variant hover:text-primary' }} block px-3 py-2 text-xs rounded-md transition-all flex items-center gap-2">
                                        <span class="w-1 h-1 rounded-full bg-current"></span>
                                        <span>{{ $child['label'] }}</span>
                                    </a>
                                @endcan
                            @endforeach
                        </div>
                    </div>
                @endcanany
            @endforeach
        </nav>

    </aside>

    <!-- Main Content Canvas -->
    <main class="lg:ml-60 pt-16 lg:pt-0 min-h-screen bg-surface">
        <!-- Desktop Header -->
        <header class="sticky top-0 z-30 hidden h-16 items-center justify-end border-b border-outline-variant/10 bg-surface px-8 lg:flex gap-4">
            <!-- Toggle Dark Mode Desktop -->
            <button @click="toggleDarkMode()" class="p-2.5 hover:bg-surface-container-low rounded-lg text-on-surface-variant hover:text-on-surface transition-colors flex items-center justify-center" aria-label="Toggle tema">
                <span class="material-symbols-outlined text-[20px]" x-text="darkMode ? 'light_mode' : 'dark_mode'"></span>
            </button>

            <div x-data="{ open: false }" class="relative">
                <button type="button"
                        @click="open = !open"
                        @keydown.escape.window="open = false"
                        class="flex min-w-0 items-center gap-3 rounded-xl px-3 py-2 transition-all hover:bg-surface-container-low focus:outline-none focus:ring-2 focus:ring-primary/20"
                        aria-label="Menu profil">
                    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary/10 text-sm font-bold text-primary ring-1 ring-primary/10">
                        @if($currentUser && $currentUser->foto)
                            <img src="{{ Storage::url($currentUser->foto) }}" alt="{{ $profileName }}" class="h-full w-full object-cover">
                        @else
                            {{ $profileInitials }}
                        @endif
                    </div>
                    <div class="hidden min-w-0 text-left sm:block">
                        <p class="max-w-44 truncate text-sm font-bold text-on-surface">{{ $profileName }}</p>
                        <p class="text-[10px] uppercase tracking-widest text-on-surface-variant">{{ $displayRole }}</p>
                    </div>
                    <span class="material-symbols-outlined text-on-surface-variant transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                </button>

                <div x-show="open"
                     x-cloak
                     @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-1"
                     class="absolute right-0 mt-3 w-72 overflow-hidden rounded-xl border border-outline-variant/20 bg-surface-container-lowest shadow-xl">
                    <div class="border-b border-outline-variant/10 px-4 py-3">
                        <p class="truncate text-sm font-bold text-on-surface">{{ $profileName }}</p>
                        <p class="truncate text-xs text-on-surface-variant">{{ $currentUser?->email }}</p>
                    </div>
                    @if($profileRoute)
                        <a href="{{ $profileRoute }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-on-surface transition-colors hover:bg-surface-container-low">
                            <span class="material-symbols-outlined text-lg text-primary">manage_accounts</span>
                            <span>Pengaturan Akun</span>
                        </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm font-semibold text-error transition-colors hover:bg-error/10">
                            <span class="material-symbols-outlined text-lg">logout</span>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="p-4 lg:p-5 sm:p-6">
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
