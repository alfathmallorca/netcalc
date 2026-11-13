@extends('layouts.app')

@section('title', 'Basic Switching & Routing')
@section('page-title', 'Switching & Routing')

@section('content')
    @php
        $sameType = old('type') === $type;
    @endphp
    <div class="mb-8">
        <p class="mb-3 text-sm font-semibold uppercase tracking-widest text-cyan-400">Belajar dari konfigurasi pertama</p>
        <h1 class="text-3xl font-bold tracking-tight md:text-4xl">Basic Switching &amp; Routing</h1>
        <p class="mt-4 max-w-3xl leading-relaxed text-slate-400">Buat konfigurasi dasar Cisco IOS untuk latihan di Packet Tracer. Pilih perangkat, isi parameter, lalu salin perintah ke CLI. Sesuaikan nama interface dengan perangkat yang digunakan.</p>
    </div>

    <nav class="mb-6 flex flex-wrap gap-3" aria-label="Jenis konfigurasi">
        @foreach (['switching' => 'Switching · VLAN & Trunk', 'routing' => 'Routing · IP & Static Route'] as $value => $label)
            <a href="{{ route('network-basics', ['type' => $value]) }}" @if($type === $value) aria-current="page" @endif class="action-button rounded-xl border px-4 py-3 text-sm font-semibold {{ $type === $value ? 'border-cyan-400/50 bg-cyan-400/10 text-cyan-300' : 'border-slate-700 bg-slate-900 text-slate-400 hover:text-white' }}">{{ $label }}</a>
        @endforeach
    </nav>

    @include('components.network-topology')

    <div class="grid items-start gap-6 xl:grid-cols-2">
        <section class="min-w-0 rounded-2xl border border-slate-800 bg-slate-900 p-6">
            <h2 class="text-lg font-semibold">Parameter {{ $type === 'switching' ? 'switch' : 'router' }}</h2>
            <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $type === 'switching' ? 'Satu VLAN untuk port access dan uplink trunk. VLAN latihan: 2–1001.' : 'Satu interface IPv4 dan satu static route melalui router tetangga.' }}</p>

            @if ($sameType && $errors->any())
                <div role="alert" class="mt-5 rounded-xl border border-red-800 bg-red-950 p-4 text-sm text-red-200">
                    <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            @php
                $fields = $type === 'switching'
                    ? [
                        ['hostname', 'Hostname', 'SW-LAB', 'text', 'Contoh: SW-LAB. Huruf, angka, dan tanda hubung; diawali huruf.'],
                        ['vlan_id', 'VLAN ID', '10', 'number', 'Gunakan VLAN 2–1001 untuk latihan ini.'],
                        ['vlan_name', 'Nama VLAN', 'STAFF', 'text', 'Maksimal 32 karakter: huruf, angka, tanda hubung, atau underscore.'],
                        ['access_port', 'Port access (ke PC)', 'FastEthernet0/1', 'text', 'Contoh: FastEthernet0/1 atau GigabitEthernet0/1.'],
                        ['trunk_port', 'Port trunk (uplink)', 'GigabitEthernet0/1', 'text', 'Port berbeda dari access. Hubungkan ke trunk perangkat lain.'],
                    ]
                    : [
                        ['hostname', 'Hostname', 'R-LAB', 'text', 'Contoh: R-LAB. Huruf, angka, dan tanda hubung; diawali huruf.'],
                        ['interface', 'Interface ke router tetangga', 'GigabitEthernet0/0', 'text', 'Contoh: GigabitEthernet0/0 atau GigabitEthernet0/0/0.'],
                        ['ip', 'IP interface router ini', '10.0.0.1', 'text', 'Gunakan alamat host pada jaringan penghubung.'],
                        ['prefix', 'Prefix interface', '30', 'number', 'Angka 1–30, tanpa /. Contoh: 30 = 255.255.255.252.'],
                        ['destination', 'Jaringan tujuan', '192.168.20.0', 'text', 'Dinormalisasi ke Network ID. Untuk default route: 0.0.0.0 dengan prefix 0.'],
                        ['route_prefix', 'Prefix jaringan tujuan', '24', 'number', 'Angka 0–32, tanpa /.'],
                        ['next_hop', 'Next hop (IP router tetangga)', '10.0.0.2', 'text', 'Harus satu subnet dengan IP interface, tetapi berbeda alamat.'],
                    ];
            @endphp

            <form action="{{ route('network-basics.generate') }}" method="POST" data-network-form class="mt-6 space-y-5">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                @foreach ($fields as [$name, $label, $default, $inputType, $help])
                    <div>
                        <label for="{{ $name }}" class="mb-2 block text-sm font-medium">{{ $label }}</label>
                        <input id="{{ $name }}" name="{{ $name }}" type="{{ $inputType }}" required value="{{ $sameType ? old($name, $default) : $default }}" placeholder="{{ $default }}" aria-describedby="{{ $name }}-help" @if($sameType && $errors->has($name)) aria-invalid="true" @endif
                            @if($inputType === 'number') min="{{ $name === 'vlan_id' ? 2 : ($name === 'prefix' ? 1 : 0) }}" max="{{ $name === 'vlan_id' ? 1001 : ($name === 'prefix' ? 30 : 32) }}" @endif
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 font-mono text-sm focus:border-cyan-400 focus:outline-none">
                        <p id="{{ $name }}-help" class="mt-2 text-xs leading-relaxed text-slate-500">{{ $help }}</p>
                    </div>
                @endforeach
                <button type="submit" class="action-button w-full rounded-xl bg-cyan-400 px-4 py-3 font-bold text-slate-950 hover:bg-cyan-300">Buat konfigurasi</button>
            </form>
        </section>

        <div class="min-w-0 space-y-6">
            <section class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-950">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 bg-slate-900 px-5 py-4">
                    <h2 class="flex items-center gap-2 text-sm font-semibold"><span class="h-2 w-2 rounded-full bg-emerald-400"></span>Konfigurasi Cisco IOS</h2>
                    @if ($commands)
                        <button type="button" data-copy-target="configuration-output" class="action-button rounded-lg border border-slate-600 px-3 py-1.5 text-xs text-cyan-300 hover:border-cyan-400">Salin perintah</button>
                    @endif
                </div>
                @if ($commands)
                    <pre id="configuration-output" tabindex="0" aria-label="Perintah konfigurasi" class="overflow-x-auto p-5 font-mono text-sm leading-7 text-cyan-200"><code>{{ $commands }}</code></pre>
                    <p class="px-5 pb-5 text-xs leading-relaxed text-slate-500">Mulai dari prompt user EXEC (&gt;). Saat diminta nama file tujuan oleh perintah copy, tekan Enter untuk menerima default. {{ $type === 'routing' ? 'Siapkan juga route balik pada router tetangga agar komunikasi dua arah berhasil.' : 'VLAN ditambahkan pada allowed list trunk. Konfigurasikan VLAN dan trunk yang sama pada perangkat di ujung uplink.' }}</p>
                @else
                    <div class="flex min-h-64 flex-col items-center justify-center p-8 text-center"><span class="terminal-cursor mb-4 font-mono text-3xl text-cyan-400" aria-hidden="true">&gt;_</span><h3 class="font-semibold">Konfigurasi pertamamu dimulai di sini</h3><p class="mt-3 max-w-sm text-sm leading-relaxed text-slate-400">Contoh parameter sudah terisi. Klik Buat konfigurasi untuk melihat perintah sesuai pilihanmu.</p></div>
                @endif
                <p data-copy-status role="status" aria-live="polite" class="px-5 pb-4 text-xs text-slate-400"></p>
            </section>

            @if ($commands)
                <details class="lesson-panel rounded-2xl border border-cyan-400/20 bg-slate-900 p-6" data-command-lessons>
                    <summary class="cursor-pointer font-semibold text-cyan-300">Mode belajar · Penjelasan {{ count($lessons) }} perintah</summary>
                    <p class="mt-3 text-sm leading-relaxed text-slate-400">Buka setiap langkah untuk melihat fungsinya, cara memeriksa, dan hasil yang diharapkan. Perintah show dijalankan setelah kembali ke privileged EXEC (#).</p>
                    <ol class="mt-5 space-y-3">
                        @foreach ($lessons as $step)
                            <li>
                                <details class="rounded-xl border border-slate-800 bg-slate-950 p-4">
                                    <summary class="cursor-pointer text-sm text-slate-300"><span class="mr-2 text-slate-500">{{ $loop->iteration }}.</span><code class="break-all text-cyan-200">{{ $step['command'] }}</code></summary>
                                    <p class="mt-3 text-sm leading-relaxed text-slate-300">{{ $step['purpose'] }}</p>
                                    <dl class="mt-3 space-y-3 text-xs leading-relaxed"><div><dt class="font-semibold text-slate-200">Cara memeriksa</dt><dd class="mt-1 text-slate-400">{{ $step['check'] }}</dd></div><div><dt class="font-semibold text-emerald-300">Hasil yang diharapkan</dt><dd class="mt-1 text-slate-400">{{ $step['expected'] }}</dd></div></dl>
                                </details>
                            </li>
                        @endforeach
                    </ol>
                </details>
            @endif

            <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
                <h2 class="text-lg font-semibold">{{ $type === 'switching' ? 'Kenali alur switching' : 'Kenali alur routing' }}</h2>
                <ol class="mt-5 space-y-4 text-sm text-slate-400">
                    @if ($type === 'switching')
                        <li><strong class="text-slate-200">1. Buat VLAN.</strong> Pisahkan jaringan secara logis dengan ID dan nama VLAN.</li>
                        <li><strong class="text-slate-200">2. Tentukan access.</strong> Port ke PC membawa trafik dari satu VLAN. Atur IP PC dalam subnet yang sama untuk uji koneksi.</li>
                        <li><strong class="text-slate-200">3. Siapkan trunk.</strong> Uplink membawa VLAN ke switch lain. Samakan pengaturan di kedua ujung.</li>
                    @else
                        <li><strong class="text-slate-200">1. Beri IP interface.</strong> Alamat router ini dan router tetangga berada pada subnet penghubung yang sama.</li>
                        <li><strong class="text-slate-200">2. Aktifkan interface.</strong> Perintah no shutdown mengaktifkan interface secara administratif.</li>
                        <li><strong class="text-slate-200">3. Tambahkan static route.</strong> Arahkan jaringan tujuan ke next hop. Router tetangga memerlukan route balik menuju jaringan asal.</li>
                    @endif
                </ol>
            </section>

            <section class="rounded-2xl border border-slate-800 p-6">
                <h2 class="font-semibold">Cek hasil latihan</h2>
                <p class="mt-2 text-sm text-slate-400">Jalankan perintah ini dari privileged EXEC (#) setelah konfigurasi:</p>
                @php
                    $checks = $type === 'switching'
                        ? ['show vlan brief' => 'Cek VLAN dan port access.', 'show interfaces trunk' => 'Cek trunk yang aktif; link uplink harus tersambung.', 'show running-config' => 'Tinjau konfigurasi perangkat.']
                        : ['show ip interface brief' => 'Cek alamat dan status interface.', 'show ip route' => 'Cek connected route dan static route (S).', 'ping '.($sameType ? old('next_hop', '10.0.0.2') : '10.0.0.2') => 'Uji koneksi ke router tetangga.'];
                @endphp
                <ul class="mt-4 space-y-3 text-sm">
                    @foreach ($checks as $command => $description)
                        <li><code class="text-cyan-300">{{ $command }}</code><p class="mt-1 text-xs text-slate-500">{{ $description }}</p></li>
                    @endforeach
                </ul>
                <p class="mt-5 text-xs leading-relaxed text-slate-500">Referensi: <a class="text-cyan-400 hover:underline" href="{{ $type === 'switching' ? 'https://www.cisco.com/c/en/us/td/docs/switches/lan/catalyst2960x/software/15-2_3_e/consolidated_guide/b_1523e_consolidated_2960-X_cg/b_consolidated_152ex_2960-X_cg_chapter_0111100.html' : 'https://www.cisco.com/c/en/us/td/docs/routers/ios-xe/ip-routing/b-ip-routing/m_iri-iprouting.html' }}" target="_blank" rel="noopener noreferrer">Panduan Cisco {{ $type === 'switching' ? 'VLAN & Trunk' : 'Basic IP Routing' }}</a></p>
            </section>
        </div>
    </div>
@endsection
