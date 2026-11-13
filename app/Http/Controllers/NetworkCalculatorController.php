<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NetworkCalculatorController extends Controller
{
    public function index(Request $request)
    {
        return view('calculator', [
            'result' => null,
            'allocations' => null,
            'mode' => $request->query('mode') === 'vlsm' ? 'vlsm' : 'subnet',
        ]);
    }

    public function calculate(Request $request)
    {
        // Mendukung input CIDR "26" maupun "/26".
        $request->merge([
            'cidr' => ltrim(trim((string) $request->input('cidr')), '/'),
        ]);

        $data = $request->validate([
            'mode' => ['required', 'in:subnet,vlsm'],
            'ip' => ['required', 'ipv4'],
            'cidr' => ['required', 'integer', 'between:0,32'],
            'hosts' => [
                'nullable',
                'required_if:mode,vlsm',
                'string',
                'max:2000',
            ],
        ]);

        $result = $this->subnet(
            $data['ip'],
            (int) $data['cidr']
        );

        $allocations = null;

        if ($data['mode'] === 'vlsm') {
            $allocations = $this->allocate(
                $result,
                $data['hosts']
            );
        }

        // Mempertahankan input pada form setelah perhitungan.
        $request->flash();

        return view('calculator', [
            'result' => $result,
            'allocations' => $allocations,
            'activity' => ['kind' => 'calculator', 'input' => $data],
        ]);
    }

    private function subnet(string $ip, int $prefix): array
    {
        $address = (int) sprintf('%u', ip2long($ip));
        $size = 1 << (32 - $prefix);

        $network = intdiv($address, $size) * $size;
        $last = $network + $size - 1;

        // /31 digunakan untuk point-to-point; /32 satu alamat.
        $usable = $prefix >= 31 ? $size : $size - 2;

        $firstHost = $prefix >= 31 ? $network : $network + 1;
        $lastHost = $prefix >= 31 ? $last : $last - 1;

        return [
            'network' => long2ip($network),
            'prefix' => $prefix,
            'mask' => long2ip(4294967295 - ($size - 1)),
            'wildcard' => long2ip($size - 1),
            'broadcast' => $prefix >= 31 ? null : long2ip($last),
            'first' => long2ip($firstHost),
            'last' => long2ip($lastHost),
            'total' => $size,
            'usable' => $usable,
            'start_number' => $network,
            'end_number' => $last,
        ];
    }

    private function allocate(array $parent, string $input): array
    {
        // Contoh input: 50, 25, 10
        if (! preg_match('/^\s*\d+\s*(,\s*\d+\s*)*$/D', $input)) {
            throw ValidationException::withMessages([
                'hosts' => 'Masukkan jumlah host dipisahkan koma, contoh: 50, 25, 10.',
            ]);
        }

        $values = array_map('trim', explode(',', $input));

        if (count($values) > 100) {
            throw ValidationException::withMessages([
                'hosts' => 'Maksimal 100 kebutuhan subnet.',
            ]);
        }

        $requirements = [];

        foreach ($values as $index => $value) {
            // Validasi sebelum konversi untuk mencegah integer overflow.
            $normalized = ltrim($value, '0');

            if (
                $normalized === '' ||
                strlen($normalized) > 10 ||
                (float) $normalized > 4294967294
            ) {
                throw ValidationException::withMessages([
                    'hosts' => 'Jumlah host harus antara 1 dan 4.294.967.294.',
                ]);
            }

            $requirements[] = [
                'name' => 'Subnet ' . ($index + 1),
                'hosts' => (int) $normalized,
            ];
        }

        // Alokasikan kebutuhan terbesar terlebih dahulu.
        usort(
            $requirements,
            fn (array $a, array $b) => $b['hosts'] <=> $a['hosts']
        );

        $cursor = $parent['start_number'];
        $rows = [];

        foreach ($requirements as $requirement) {
            // VLSM memakai subnet LAN konvensional:
            // tambahan dua alamat untuk network dan broadcast.
            $block = 4;
            $prefix = 30;

            while ($block < $requirement['hosts'] + 2) {
                $block *= 2;
                $prefix--;
            }

            // Pastikan awal alokasi sesuai batas blok subnet.
            $start = intdiv($cursor + $block - 1, $block) * $block;

            if ($start + $block - 1 > $parent['end_number']) {
                throw ValidationException::withMessages([
                    'hosts' => 'Kapasitas jaringan induk tidak cukup. '
                        . 'Kurangi kebutuhan host atau gunakan jaringan lebih besar.',
                ]);
            }

            $rows[] = array_merge(
                $this->subnet(long2ip($start), $prefix),
                [
                    'name' => $requirement['name'],
                    'requested' => $requirement['hosts'],
                ]
            );

            $cursor = $start + $block;
        }

        return $rows;
    }
}
