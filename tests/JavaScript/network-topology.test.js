import test from 'node:test';
import assert from 'node:assert/strict';
import { buildTopology } from '../../resources/js/network-topology.js';

test('switching topology follows hostname VLAN and both ports', () => {
    const result = buildTopology('switching', { hostname: 'SW-SEKOLAH', vlan_id: '25', vlan_name: 'SISWA', access_port: 'FastEthernet0/7', trunk_port: 'GigabitEthernet0/2' });
    assert.equal(result.nodes[1].title, 'SW-SEKOLAH');
    assert.equal(result.nodes[1].subtitle, 'VLAN 25 · SISWA');
    assert.match(result.links[0], /FastEthernet0\/7/);
    assert.match(result.links[1], /GigabitEthernet0\/2/);
    assert.match(result.nodes[2].detail, /perangkat lawan dibuat secara terpisah/);
});

test('routing topology uses the configured interface and normalizes destination', () => {
    const result = buildTopology('routing', { hostname: 'R-1', interface: 'GigabitEthernet0/0', ip: '10.0.0.1', prefix: '30', next_hop: '10.0.0.2', destination: '192.168.20.15', route_prefix: '24' });
    assert.equal(result.nodes[0].subtitle, '10.0.0.1/30');
    assert.equal(result.nodes[1].subtitle, '10.0.0.2');
    assert.equal(result.nodes[2].subtitle, '192.168.20.0/24');
    assert.match(result.nodes[1].detail, /route balik/);
});

test('destination normalization handles default routes, host routes and high addresses', () => {
    for (const [ip, prefix, expected] of [['192.168.20.15', '0', '0.0.0.0/0'], ['192.168.20.15', '32', '192.168.20.15/32'], ['203.0.113.9', '24', '203.0.113.0/24']]) {
        assert.equal(buildTopology('routing', { destination: ip, route_prefix: prefix }).nodes[2].subtitle, expected);
    }
});

test('incomplete or invalid input stays a preview without crashing', () => {
    assert.equal(buildTopology('switching', {}).nodes[1].title, 'Belum diisi');
    assert.equal(buildTopology('routing', { destination: 'invalid', route_prefix: '' }).nodes[2].subtitle, 'invalid/?');
});
