<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — NetCalc</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <a href="#main-content" class="skip-link">Lewati navigasi</a>

    <div class="app-shell" data-app-shell>
        <aside id="app-sidebar" class="app-sidebar border-r border-slate-800 bg-slate-900" aria-label="Menu utama" tabindex="-1">
            <div class="flex h-20 items-center justify-between border-b border-slate-800 px-6">
                <a href="{{ route('dashboard') }}" class="text-2xl font-bold tracking-tight" aria-label="NetCalc — Dashboard">
                    <span class="text-cyan-400">Net</span>Calc
                </a>
                <button type="button" data-sidebar-close class="sidebar-close rounded-lg p-2 text-slate-400 transition hover:bg-slate-800 hover:text-white focus-visible:outline-2 focus-visible:outline-cyan-400" aria-label="Tutup menu">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 6 12 12M6 18 18 6"/></svg>
                </button>
            </div>

            <nav class="space-y-2 px-4 py-6" aria-label="Navigasi utama">
                <p class="mb-4 px-3 text-xs font-semibold uppercase tracking-widest text-slate-500">Menu utama</p>
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('calculator') }}" class="sidebar-link {{ request()->routeIs('calculator*') ? 'is-active' : '' }}" @if(request()->routeIs('calculator*')) aria-current="page" @endif>
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="2" width="14" height="20" rx="2"/><path d="M8 6h8M8 11h2m4 0h2m-8 4h2m4 0h2m-8 4h2m4 0h2"/></svg>
                    Kalkulator Jaringan
                </a>
                <a href="{{ route('network-basics') }}" class="sidebar-link {{ request()->routeIs('network-basics*') ? 'is-active' : '' }}" @if(request()->routeIs('network-basics*')) aria-current="page" @endif>
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="18" height="6" rx="2"/><path d="M7 6h.01M11 6h6M12 9v6M5 15h14M5 15v3m14-3v3"/><rect x="2" y="18" width="6" height="4" rx="1"/><rect x="16" y="18" width="6" height="4" rx="1"/></svg>
                    Switching &amp; Routing
                </a>
            </nav>

            <div class="mt-auto border-t border-slate-800 p-6">
                <p class="text-sm font-medium text-slate-300">IPv4 Network Tools</p>
                <p class="mt-2 text-xs leading-relaxed text-slate-500">Perencanaan subnet dan pembagian jaringan dalam satu tempat.</p>
            </div>
        </aside>

        <button type="button" class="sidebar-overlay" data-sidebar-overlay aria-label="Tutup menu" tabindex="-1" hidden></button>

        <div class="app-content">
            <header class="border-b border-slate-800 bg-slate-950">
                <div class="flex h-20 items-center gap-4 px-4 sm:px-6 lg:px-8">
                    <button type="button" data-sidebar-toggle aria-controls="app-sidebar" aria-expanded="false" aria-label="Buka menu" class="rounded-xl border border-slate-700 p-2.5 text-slate-300 transition hover:border-cyan-400 hover:text-cyan-300 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-cyan-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <span class="font-semibold">@yield('page-title', 'Dashboard')</span>
                    <span class="ml-auto hidden rounded-full border border-slate-700 px-3 py-1 text-xs text-slate-400 sm:inline-flex">IPv4 Network Tools</span>
                </div>
            </header>

            <main id="main-content" tabindex="-1" class="page-enter mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                @yield('content')
            </main>

            <footer class="mx-4 border-t border-slate-800 py-6 text-center text-sm text-slate-400 sm:mx-6 lg:mx-8">
                <p>&copy; {{ date('Y') }} Muhammad Nur Alfath Mallorca. All rights reserved.</p>
                <a href="https://github.com/alfathmallorca" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block text-cyan-400 transition hover:text-cyan-300 hover:underline">GitHub · alfathmallorca</a>
            </footer>
        </div>
    </div>
    @isset($activity)
        <script type="application/json" id="netcalc-activity">@json($activity)</script>
        <p data-activity-save-status role="status" class="mx-4 mb-4 text-center text-xs text-amber-300"></p>
    @endisset
</body>
</html>
