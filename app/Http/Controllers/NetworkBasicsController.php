<?php

namespace App\Http\Controllers;

use App\Support\NetworkLesson;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NetworkBasicsController extends Controller
{
    public function index(Request $request)
    {
        return view('network-basics', [
            'type' => $request->query('type') === 'routing' ? 'routing' : 'switching',
            'commands' => null,
        ]);
    }

    public function generate(Request $request)
    {
        $interface = ['required', 'string', 'max:64', 'regex:/^(?:FastEthernet|GigabitEthernet|TenGigabitEthernet|Ethernet)\d+(?:\/\d+){0,2}$/iD'];
        $rules = [
            'type' => ['required', 'in:switching,routing'],
            'hostname' => ['required', 'string', 'max:63', 'regex:/^[a-zA-Z][a-zA-Z0-9-]*$/D'],
        ];

        if ($request->input('type') === 'switching') {
            $rules += [
                'vlan_id' => ['required', 'integer', 'between:2,1001'],
                'vlan_name' => ['required', 'string', 'max:32', 'regex:/^[a-zA-Z0-9_-]+$/D'],
                'access_port' => $interface,
                'trunk_port' => [...$interface, 'different:access_port'],
            ];
        } else {
            $rules += [
                'interface' => $interface,
                'ip' => ['required', 'ipv4'],
                'prefix' => ['required', 'integer', 'between:1,30'],
                'destination' => ['required', 'ipv4'],
                'route_prefix' => ['required', 'integer', 'between:0,32'],
                'next_hop' => ['required', 'ipv4', 'different:ip'],
            ];
        }

        $data = $request->validate($rules, [
            'regex' => ':attribute berisi format yang tidak didukung. Gunakan contoh pada kolom.',
            'trunk_port.different' => 'Port trunk harus berbeda dari port access.',
            'next_hop.different' => 'Next hop harus berbeda dari IP router ini.',
        ]);

        $lines = ['enable', 'configure terminal', 'hostname '.$data['hostname']];

        if ($data['type'] === 'switching') {
            if (strcasecmp($data['access_port'], $data['trunk_port']) === 0) {
                throw ValidationException::withMessages(['trunk_port' => 'Port trunk harus berbeda dari port access.']);
            }

            $lines = array_merge($lines, [
                'vlan '.$data['vlan_id'],
                'name '.$data['vlan_name'],
                'exit',
                'interface '.$data['access_port'],
                'switchport mode access',
                'switchport access vlan '.$data['vlan_id'],
                'no shutdown',
                'exit',
                'interface '.$data['trunk_port'],
                'switchport mode trunk',
                'switchport trunk allowed vlan add '.$data['vlan_id'],
                'no shutdown',
                'exit',
            ]);
        } else {
            $prefix = (int) $data['prefix'];
            $ip = $this->address($data['ip']);
            $hop = $this->address($data['next_hop']);
            $size = 1 << (32 - $prefix);
            $network = intdiv($ip, $size) * $size;

            if ($ip === $network || $ip === $network + $size - 1) {
                throw ValidationException::withMessages(['ip' => 'IP interface harus berupa alamat host, bukan network atau broadcast.']);
            }

            if ($hop <= $network || $hop >= $network + $size - 1) {
                throw ValidationException::withMessages(['next_hop' => 'Next hop harus berupa alamat host pada subnet interface yang sama.']);
            }

            $routePrefix = (int) $data['route_prefix'];
            $routeSize = 1 << (32 - $routePrefix);
            $destination = long2ip(intdiv($this->address($data['destination']), $routeSize) * $routeSize);

            $lines = array_merge($lines, [
                'interface '.$data['interface'],
                'ip address '.$data['ip'].' '.$this->mask($prefix),
                'no shutdown',
                'exit',
                'ip route '.$destination.' '.$this->mask($routePrefix).' '.$data['next_hop'],
            ]);
        }

        $lines[] = 'end';
        $lines[] = 'copy running-config startup-config';
        $request->flash();

        $commands = implode("\n", $lines);

        return view('network-basics', [
            'type' => $data['type'],
            'commands' => $commands,
            'lessons' => NetworkLesson::explain($commands),
            'activity' => ['kind' => 'configuration', 'input' => $data],
        ]);
    }

    private function address(string $ip): int
    {
        return (int) sprintf('%u', ip2long($ip));
    }

    private function mask(int $prefix): string
    {
        return long2ip(4294967295 - ((1 << (32 - $prefix)) - 1));
    }
}
