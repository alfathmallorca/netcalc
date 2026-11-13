const value = (input, key) => String(input[key] ?? '').trim().slice(0, 64) || 'Belum diisi';

function networkLabel(ip, prefix) {
    const octets = ip.split('.').map(Number);
    const bits = Number(prefix);
    if (!/^\d+\.\d+\.\d+\.\d+$/.test(ip) || octets.some((n) => n < 0 || n > 255) || prefix === '' || !Number.isInteger(bits) || bits < 0 || bits > 32) return `${ip}/${prefix || '?'}`;
    const address = octets.reduce((sum, octet) => sum * 256 + octet, 0);
    const size = 2 ** (32 - bits);
    const network = Math.floor(address / size) * size;
    return `${[24, 16, 8, 0].map((shift) => Math.floor(network / 2 ** shift) % 256).join('.')}/${bits}`;
}

export function buildTopology(type, input) {
    const hostname = value(input, 'hostname');
    if (type === 'switching') {
        const vlan = value(input, 'vlan_id'), name = value(input, 'vlan_name');
        const access = value(input, 'access_port'), trunk = value(input, 'trunk_port');
        return {
            nodes: [
                { title: 'PC / End device', subtitle: `VLAN ${vlan}`, detail: `PC terhubung ke port ${access} pada ${hostname}. Atur IP PC sesuai subnet VLAN ${vlan}; generator ini belum mengatur IP PC.` },
                { title: hostname, subtitle: `VLAN ${vlan} · ${name}`, detail: `${hostname} menempatkan ${access} pada VLAN ${vlan} (${name}), lalu membawa VLAN melalui trunk ${trunk}.` },
                { title: 'Switch uplink', subtitle: `Trunk · VLAN ${vlan}`, detail: `Perangkat lawan pada uplink ${trunk} perlu memiliki VLAN ${vlan} dan konfigurasi trunk yang sesuai. Konfigurasi perangkat lawan dibuat secara terpisah.` },
            ],
            links: [`Access\n${access}`, `Trunk\n${trunk}`],
        };
    }

    const ip = value(input, 'ip'), prefix = String(input.prefix ?? '');
    const nextHop = value(input, 'next_hop'), port = value(input, 'interface');
    const destination = networkLabel(value(input, 'destination'), String(input.route_prefix ?? ''));
    return {
        nodes: [
            { title: hostname, subtitle: `${ip}/${prefix || '?'}`, detail: `${hostname} menggunakan ${port} dengan IP ${ip}/${prefix || '?'}. Interface ini menghubungkan router ke next hop ${nextHop}.` },
            { title: 'Router tetangga', subtitle: nextHop, detail: `Next hop ${nextHop} harus berada pada subnet interface ${port}. Router ini memerlukan koneksi menuju ${destination} dan route balik menuju jaringan asal.` },
            { title: 'Jaringan tujuan', subtitle: destination, detail: `Static route pada ${hostname} mengarahkan trafik ke ${destination} melalui ${nextHop}. Ini menunjukkan arah routing, bukan hasil pengujian koneksi.` },
        ],
        links: [port, 'Static route'],
    };
}
