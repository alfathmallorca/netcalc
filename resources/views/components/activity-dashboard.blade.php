<section class="mb-8" data-activity-dashboard data-calculator-action="{{ route('calculator.calculate') }}" data-configuration-action="{{ route('network-basics.generate') }}">
    <div class="mb-4"><h2 class="text-xl font-semibold">Ruang belajarmu</h2><p class="mt-2 text-sm text-slate-400">Aktivitas berhasil tersimpan otomatis di browser ini, hingga 50 latihan terakhir.</p></div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach (['total' => 'Latihan tersimpan', 'calculator' => 'Perhitungan tersimpan', 'configuration' => 'Konfigurasi tersimpan'] as $key => $label)
            <div class="rounded-2xl border border-slate-800 bg-slate-900/85 p-5"><p class="text-xs text-slate-400">{{ $label }}</p><p data-activity-count="{{ $key }}" class="mt-2 text-3xl font-bold text-cyan-300">0</p></div>
        @endforeach
    </div>

    <div data-latest-activity hidden class="mt-4 rounded-2xl border border-cyan-400/20 bg-cyan-400/5 p-5">
        <p class="text-xs font-semibold uppercase tracking-widest text-cyan-400">Lanjut dari latihan terakhir</p>
        <div class="mt-3 flex flex-wrap items-center justify-between gap-4"><div class="min-w-0"><h3 data-latest-title class="break-words font-semibold"></h3><p data-latest-description class="mt-2 break-all text-sm text-slate-400"></p></div><button type="button" data-resume-latest class="action-button rounded-xl bg-cyan-400 px-4 py-2.5 text-sm font-bold text-slate-950 hover:bg-cyan-300">Lanjutkan latihan &rarr;</button></div>
    </div>

    <div class="mt-4 rounded-2xl border border-slate-800 bg-slate-950/75 p-5">
        <h3 class="font-semibold">Aktivitas terbaru</h3>
        <p data-activity-empty class="mt-3 text-sm leading-relaxed text-slate-400">Belum ada aktivitas. Coba hitung subnet atau buat konfigurasi pertama. Hasil yang sama akan memperbarui latihan sebelumnya.</p>
        <ul data-activity-list class="mt-3 divide-y divide-slate-800"></ul>
        <p data-activity-dashboard-status role="status" aria-live="polite" class="mt-3 text-xs leading-relaxed text-amber-300"></p>
        <noscript><p class="mt-3 text-xs text-slate-400">Aktifkan JavaScript untuk menyimpan aktivitas dan melanjutkan latihan di browser ini.</p></noscript>
    </div>
</section>
