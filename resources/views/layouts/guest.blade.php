<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#004d4c">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>@yield('title', 'Login') - Bank Pesantren</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">

    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        .sanctuary-gradient {
            background: linear-gradient(135deg, #004d4c 0%, #006766 100%);
        }
        
        /* Prevent pull-to-refresh on mobile */
        body {
            overscroll-behavior-y: contain;
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Better tap targets */
        @media (max-width: 768px) {
            * {
                -webkit-tap-highlight-color: transparent;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="bg-surface font-body text-on-surface min-h-screen flex flex-col" x-data="{ theme: 'light' }">
    <div class="fixed top-0 left-0 w-full h-1 bg-surface-container overflow-hidden">
        <div class="h-full sanctuary-gradient w-1/3 animate-pulse"></div>
    </div>

    <div class="flex-1 flex items-center justify-center p-4 sm:p-6 py-8 sm:py-6">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
