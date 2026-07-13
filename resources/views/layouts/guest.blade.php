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
    <title>@yield('title', 'Login') - Mawa Smart</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=block" rel="stylesheet">
    <script>
        document.documentElement.classList.add('material-symbols-loading');
    </script>

    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }

        .material-symbols-outlined,
        .material-symbols-filled {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            width: 1em;
            min-width: 1em;
            overflow: hidden;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
            font-feature-settings: 'liga';
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .material-symbols-filled {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .material-symbols-loading .material-symbols-outlined,
        .material-symbols-loading .material-symbols-filled {
            visibility: hidden;
        }

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
    <script>
        (function () {
            var root = document.documentElement;
            var markReady = function () {
                root.classList.remove('material-symbols-loading');
                root.classList.add('material-symbols-ready');
            };

            if (!document.fonts || !document.fonts.load) {
                markReady();
                return;
            }

            Promise.race([
                document.fonts.load('400 24px "Material Symbols Outlined"'),
                new Promise(function (resolve) {
                    window.setTimeout(resolve, 2500);
                })
            ]).then(markReady, markReady);
        })();
    </script>
</head>
<body class="bg-surface font-body text-on-surface min-h-screen flex flex-col" x-data="{ theme: 'light' }">

    <div class="flex-1 flex items-center justify-center p-4 sm:p-4 sm:p-5 py-8 sm:py-6">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
