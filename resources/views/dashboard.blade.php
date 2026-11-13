@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="dashboard-scene">
    <div class="dashboard-background" aria-hidden="true">
        <div class="ambient-orb orb-one"></div><div class="ambient-orb orb-two"></div><div class="ambient-orb orb-three"></div><div class="ambient-grid"></div>
        @for ($i = 0; $i < 8; $i++)
            <span class="ambient-particle" style="--particle: {{ $i }}"></span>
        @endfor
    </div>
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <span class="inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/5 px-3 py-1.5 text-xs text-cyan-300"><span class="status-light"></span>Teman belajar jaringanmu</span>
        <button type="button" data-motion-toggle aria-pressed="false" class="motion-control rounded-full border border-slate-700 bg-slate-950/70 px-3 py-1.5 text-xs text-slate-300 hover:border-cyan-400 hover:text-cyan-300">Jeda animasi</button>
    </div>
    <section class="dashboard-hero mb-8 grid items-center gap-6 rounded-3xl border border-slate-700/70 p-6 sm:p-8 xl:grid-cols-2">
        <div>
        <p class="mb-3 text-sm font-semibold uppercase tracking-widest text-cyan-400">IPv4 Network Tools</p>
        <h1 class="text-3xl font-bold tracking-tight md:text-4xl">Selamat datang di NetCalc</h1>
        <p class="mt-4 max-w-2xl leading-relaxed text-slate-400">Dari menghitung subnet hingga konfigurasi perangkat pertamamu. Yuk, rencanakan dan bangun jaringan selangkah demi selangkah.</p>
        <a href="{{ route('calculator') }}" class="action-button mt-6 inline-flex items-center gap-2 rounded-xl bg-cyan-400 px-5 py-3 text-sm font-bold text-slate-950 hover:bg-cyan-300 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-cyan-300">
            Buka kalkulator
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
        </a>
        <a href="{{ route('network-basics') }}" class="action-button ml-2 mt-3 inline-flex rounded-xl border border-slate-600 px-4 py-3 text-sm font-semibold text-slate-200 hover:border-cyan-400 hover:text-cyan-300">Belajar konfigurasi &rarr;</a>
        </div>
        @include('components.network-friends')
    </section>

    @include('components.activity-dashboard')

    <div class="mb-5"><p class="mb-2 text-xs font-semibold uppercase tracking-widest text-cyan-400">Ruang eksplorasi</p><h2 class="text-xl font-semibold">Mau mulai dari mana?</h2></div>
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        <a href="{{ route('calculator', ['mode' => 'subnet']) }}" class="feature-card stagger-one group rounded-2xl border border-slate-800 bg-slate-900/85 p-6 transition hover:border-cyan-400/60 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-cyan-400">
            <span class="inline-flex rounded-xl bg-cyan-400/10 px-4 py-3 font-mono text-xl font-bold text-cyan-400">/24</span>
            <h2 class="mt-5 text-xl font-semibold">Subnetting Calculator</h2>
            <p class="mt-3 leading-relaxed text-slate-400">Temukan Network ID, subnet mask, broadcast, dan rentang IP valid dari alamat IPv4 dan prefix jaringan.</p>
            <span class="mt-5 inline-block text-sm font-semibold text-cyan-400 group-hover:text-cyan-300">Hitung subnet &rarr;</span>
        </a>

        <a href="{{ route('calculator', ['mode' => 'vlsm']) }}" class="feature-card stagger-two group rounded-2xl border border-slate-800 bg-slate-900/85 p-6 transition hover:border-cyan-400/60 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-cyan-400">
            <span class="inline-flex rounded-xl bg-cyan-400/10 px-4 py-3 font-mono text-xl font-bold text-cyan-400">VLSM</span>
            <h2 class="mt-5 text-xl font-semibold">VLSM Calculator</h2>
            <p class="mt-3 leading-relaxed text-slate-400">Bagi jaringan induk menjadi beberapa subnet berdasarkan jumlah host yang dibutuhkan untuk setiap jaringan.</p>
            <span class="mt-5 inline-block text-sm font-semibold text-cyan-400 group-hover:text-cyan-300">Rencanakan VLSM &rarr;</span>
        </a>
        <a href="{{ route('network-basics') }}" class="feature-card stagger-three group rounded-2xl border border-slate-800 bg-slate-900/85 p-6 md:col-span-2 xl:col-span-1">
            <span class="inline-flex rounded-xl bg-emerald-400/10 px-4 py-3 font-mono text-xl font-bold text-emerald-300">&gt;_</span>
            <h2 class="mt-5 text-xl font-semibold">Basic Switching &amp; Routing</h2>
            <p class="mt-3 leading-relaxed text-slate-400">Belajar VLAN, trunk, IP interface, dan static route dengan konfigurasi Cisco IOS yang bisa disalin.</p>
            <span class="mt-5 inline-block text-sm font-semibold text-emerald-300">Mulai konfigurasi &rarr;</span>
        </a>
    </div>

    <section class="mt-8 rounded-2xl border border-slate-800 bg-slate-950/70 p-6">
        <h2 class="text-lg font-semibold">Pelan-pelan, pasti terhubung.</h2>
        <ol class="mt-5 grid gap-5 text-sm md:grid-cols-3">
            <li class="flex gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-800 font-semibold text-cyan-400">1</span><p class="pt-1 leading-relaxed text-slate-400">Rencanakan subnet dan kebutuhan alamat IP dengan kalkulator.</p></li>
            <li class="flex gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-800 font-semibold text-cyan-400">2</span><p class="pt-1 leading-relaxed text-slate-400">Buat konfigurasi dasar switching atau routing sesuai kebutuhanmu.</p></li>
            <li class="flex gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-800 font-semibold text-cyan-400">3</span><p class="pt-1 leading-relaxed text-slate-400">Coba perintah dan cek koneksi antardevice di Packet Tracer.</p></li>
        </ol>
    </section>
</div>
@endsection
