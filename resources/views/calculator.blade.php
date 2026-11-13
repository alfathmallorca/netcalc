@extends('layouts.app')

@section('title', 'VLSM & Subnetting')
@section('page-title', 'Kalkulator Jaringan')

@section('content')
        <div class="mb-10">
            <p class="mb-3 text-sm font-semibold uppercase tracking-widest text-cyan-400">
                Network planning made simple
            </p>

            <h1 class="text-3xl font-bold tracking-tight md:text-5xl">
                VLSM & Subnetting Calculator
            </h1>

            <p class="mt-4 max-w-2xl leading-relaxed text-slate-400">
                Hitung informasi jaringan dan rencanakan pembagian subnet
                berdasarkan kebutuhan host.
            </p>
        </div>

        <div class="grid items-start gap-6 lg:grid-cols-3">
            <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
                <h2 class="text-lg font-semibold">Konfigurasi jaringan</h2>

                <p class="mt-1 text-sm text-slate-400">
                    Masukkan alamat IPv4 dan prefix jaringan.
                </p>

                @if ($errors->any())
                    <div role="alert"
                         class="mt-5 rounded-xl border border-red-800 bg-red-950 p-4 text-sm text-red-200">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('calculator.calculate') }}"
                      method="POST"
                      class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <label for="mode" class="mb-2 block text-sm font-medium">
                            Mode perhitungan
                        </label>

                        <select id="mode" name="mode"
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 focus:border-cyan-400 focus:outline-none">
                            <option value="subnet"
                                @selected(old('mode', $mode ?? 'subnet') === 'subnet')>
                                Subnetting
                            </option>
                            <option value="vlsm"
                                @selected(old('mode', $mode ?? 'subnet') === 'vlsm')>
                                VLSM
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="ip" class="mb-2 block text-sm font-medium">
                            IP Address
                        </label>

                        <input id="ip" name="ip" type="text" required
                               value="{{ old('ip', '192.168.1.0') }}"
                               placeholder="192.168.1.0"
                               class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 font-mono focus:border-cyan-400 focus:outline-none">
                    </div>

                    <div>
                        <label for="cidr" class="mb-2 block text-sm font-medium">
                            CIDR / Prefix
                        </label>

                        <input id="cidr" name="cidr" type="text" required
                               value="{{ old('cidr', '/24') }}"
                               placeholder="/26"
                               aria-describedby="cidr-help"
                               class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 font-mono focus:border-cyan-400 focus:outline-none">

                        <p id="cidr-help" class="mt-2 text-xs text-slate-500">
                            Masukkan 0–32, dengan atau tanpa tanda /.
                        </p>
                    </div>

                    <div>
                        <label for="hosts" class="mb-2 block text-sm font-medium">
                            Kebutuhan host
                            <span class="font-normal text-slate-500">(khusus VLSM)</span>
                        </label>

                        <input id="hosts" name="hosts" type="text"
                               value="{{ old('hosts', '50, 25, 10') }}"
                               placeholder="50, 25, 10"
                               aria-describedby="hosts-help"
                               class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 font-mono focus:border-cyan-400 focus:outline-none">

                        <p id="hosts-help" class="mt-2 text-xs leading-relaxed text-slate-500">
                            Pisahkan dengan koma. Contoh: 50 host untuk subnet pertama,
                            25 untuk kedua, dan 10 untuk ketiga.
                        </p>
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-cyan-400 px-4 py-3 font-bold text-slate-950 transition hover:bg-cyan-300 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-cyan-300">
                        Hitung jaringan
                    </button>

                    <a href="{{ route('calculator') }}"
                       class="block text-center text-sm text-slate-400 hover:text-white">
                        Reset konfigurasi
                    </a>
                </form>
            </section>

            <section class="space-y-6 lg:col-span-2">
                @if ($result)
                    <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h2 class="text-lg font-semibold">
                                {{ $allocations !== null ? 'Jaringan induk' : 'Hasil subnetting' }}
                            </h2>

                            <span class="rounded-full bg-cyan-400/10 px-3 py-1 font-mono text-sm text-cyan-300">
                                {{ $result['network'] }}/{{ $result['prefix'] }}
                            </span>
                        </div>

                        @php
                            $cards = [
                                'Network ID' => $result['network'],
                                'Broadcast' => $result['broadcast'] ?? 'Tidak berlaku',
                                'Subnet Mask' => $result['mask'],
                                'Wildcard Mask' => $result['wildcard'],
                                'IP valid pertama' => $result['first'],
                                'IP valid terakhir' => $result['last'],
                                'Total alamat' => number_format($result['total'], 0, ',', '.'),
                                'Host usable' => number_format($result['usable'], 0, ',', '.'),
                            ];
                        @endphp

                        <dl class="mt-6 grid gap-3 sm:grid-cols-2">
                            @foreach ($cards as $label => $value)
                                <div class="rounded-xl border border-slate-800 bg-slate-950 p-4">
                                    <dt class="text-sm text-slate-400">{{ $label }}</dt>
                                    <dd class="mt-2 break-all font-mono text-lg font-semibold">
                                        {{ $value }}
                                    </dd>
                                </div>
                            @endforeach
                        </dl>

                        <p class="mt-4 text-xs leading-relaxed text-slate-500">
                            IP input dinormalisasi ke Network ID.
                            /31 memakai dua alamat endpoint point-to-point;
                            /32 mewakili satu alamat. Keduanya tidak memiliki
                            broadcast subnet pada hasil ini.
                        </p>
                    </div>

                    @if ($allocations !== null)
                        <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold">Pembagian VLSM</h2>
                                <p class="mt-1 text-sm text-slate-400">
                                    Diurutkan dari kebutuhan host terbesar.
                                    Nomor subnet mengikuti urutan input.
                                </p>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full whitespace-nowrap text-left text-sm">
                                    <thead class="bg-slate-800 text-slate-300">
                                        <tr>
                                            <th scope="col" class="px-5 py-3">Subnet</th>
                                            <th scope="col" class="px-5 py-3">Host diminta</th>
                                            <th scope="col" class="px-5 py-3">Network / CIDR</th>
                                            <th scope="col" class="px-5 py-3">Rentang IP valid</th>
                                            <th scope="col" class="px-5 py-3">Broadcast</th>
                                            <th scope="col" class="px-5 py-3">Kapasitas host</th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-800">
                                        @foreach ($allocations as $row)
                                            <tr class="hover:bg-slate-800/50">
                                                <th scope="row" class="px-5 py-4 font-medium">
                                                    {{ $row['name'] }}
                                                </th>
                                                <td class="px-5 py-4">{{ $row['requested'] }}</td>
                                                <td class="px-5 py-4 font-mono text-cyan-300">
                                                    {{ $row['network'] }}/{{ $row['prefix'] }}
                                                </td>
                                                <td class="px-5 py-4 font-mono">
                                                    {{ $row['first'] }} – {{ $row['last'] }}
                                                </td>
                                                <td class="px-5 py-4 font-mono">
                                                    {{ $row['broadcast'] }}
                                                </td>
                                                <td class="px-5 py-4">{{ $row['usable'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <p class="p-6 text-xs leading-relaxed text-slate-500">
                                Mode VLSM menggunakan subnet LAN konvensional,
                                minimum /30, dengan dua alamat dicadangkan
                                untuk network dan broadcast.
                            </p>
                        </div>
                    @endif
                @else
                    <div class="flex min-h-96 flex-col items-center justify-center rounded-2xl border border-dashed border-slate-700 bg-slate-900/50 p-8 text-center">
                        <span class="mb-5 rounded-2xl bg-cyan-400/10 p-5 font-mono text-3xl font-bold text-cyan-400">
                            /24
                        </span>

                        <h2 class="text-xl font-semibold">Siap menghitung jaringan</h2>

                        <p class="mt-3 max-w-md leading-relaxed text-slate-400">
                            Isi konfigurasi, pilih mode, lalu klik
                            “Hitung jaringan”. Hasil perhitungan akan muncul di sini.
                        </p>
                    </div>
                @endif
            </section>
        </div>
@endsection
