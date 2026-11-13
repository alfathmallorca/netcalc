<section data-network-topology data-type="{{ $type }}" class="mb-6 min-w-0 overflow-hidden rounded-2xl border border-slate-700 bg-slate-900/70 p-5 sm:p-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div><h2 class="text-lg font-semibold">Topologi interaktif</h2><p class="mt-2 text-sm text-slate-400">Mengikuti isian form. Klik perangkat untuk melihat perannya.</p></div>
        <span class="rounded-full border border-slate-700 px-3 py-1 text-xs text-slate-400">{{ $type === 'switching' ? 'Access → Switch → Trunk' : 'Router → Next hop → Tujuan' }}</span>
    </div>
    <div class="mt-5 overflow-x-auto rounded-xl border border-slate-800 bg-slate-950 p-4" tabindex="0" role="region" aria-label="Diagram topologi jaringan, dapat digeser secara horizontal">
        <div class="topology-strip">
            @for ($i = 0; $i < 3; $i++)
                <button type="button" class="topology-node" data-topology-node="{{ $i }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}">
                    <span class="topology-device" aria-hidden="true">{{ $type === 'switching' ? ['▣', '⇄', '⇄'][$i] : ['⇆', '⇆', '◎'][$i] }}</span>
                    <span data-node-title class="block break-all font-semibold text-slate-100">{{ $type === 'switching' ? ['PC', 'Switch', 'Switch uplink'][$i] : ['Router ini', 'Router tetangga', 'Jaringan tujuan'][$i] }}</span>
                    <span data-node-subtitle class="mt-2 block break-all font-mono text-xs text-slate-400"></span>
                </button>
                @if ($i < 2)
                    <div class="topology-link"><span aria-hidden="true">→</span><span data-link-label="{{ $i }}" class="block break-all text-center font-mono text-xs text-slate-400"></span></div>
                @endif
            @endfor
        </div>
    </div>
    <p class="mt-2 text-xs text-slate-500 sm:hidden">Geser diagram untuk melihat seluruh perangkat.</p>
    <div class="mt-4 rounded-xl border border-slate-800 bg-slate-950/60 p-4" role="status" aria-live="polite" aria-atomic="true"><p data-topology-title class="text-sm font-semibold text-cyan-300">Detail perangkat</p><p data-topology-detail class="mt-2 text-sm leading-relaxed text-slate-400">Aktifkan JavaScript untuk memperbarui topologi dari isian form.</p></div>
    <p class="mt-3 text-xs leading-relaxed text-slate-500">Diagram menunjukkan hubungan logis, bukan simulasi koneksi. Parameter diperiksa saat Buat konfigurasi diklik. IP PC dan interface lawan perlu diatur pada perangkat latihan.</p>
</section>
