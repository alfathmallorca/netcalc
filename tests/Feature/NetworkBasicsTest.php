<?php

namespace Tests\Feature;

use Tests\TestCase;

class NetworkBasicsTest extends TestCase
{
    public function test_dashboard_and_sidebar_link_to_network_basics(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('network-basics'))
            ->assertSee('Jeda animasi');

        $this->get('/network-basics')->assertOk()->assertViewHas('type', 'switching');
        $this->get('/network-basics?type=routing')->assertOk()->assertViewHas('type', 'routing');
    }

    public function test_switching_generates_vlan_access_and_trunk_commands(): void
    {
        $this->post('/network-basics', $this->switching())
            ->assertOk()
            ->assertViewHas('commands', fn (string $commands) => str_contains($commands, "vlan 10\nname STAFF")
                && str_contains($commands, "interface FastEthernet0/1\nswitchport mode access\nswitchport access vlan 10")
                && str_contains($commands, "interface GigabitEthernet0/1\nswitchport mode trunk\nswitchport trunk allowed vlan add 10"))
            ->assertSee('Salin perintah');
    }

    public function test_switching_rejects_the_same_access_and_trunk_port_regardless_of_case(): void
    {
        $this->from('/network-basics')->post('/network-basics', array_replace($this->switching(), [
            'trunk_port' => 'fastethernet0/1',
        ]))->assertRedirect('/network-basics')->assertSessionHasErrors('trunk_port');
    }

    public function test_command_fields_reject_extra_cli_instructions(): void
    {
        $this->from('/network-basics')->post('/network-basics', array_replace($this->switching(), [
            'hostname' => "SW-LAB\nreload",
            'access_port' => "FastEthernet0/1\nshutdown",
            'vlan_name' => "STAFF\nexit",
            'vlan_id' => 1002,
        ]))->assertSessionHasErrors(['hostname', 'access_port', 'vlan_name', 'vlan_id']);
    }

    public function test_routing_builds_masks_and_normalizes_the_destination_network(): void
    {
        $this->post('/network-basics', $this->routing())
            ->assertOk()
            ->assertViewHas('type', 'routing')
            ->assertViewHas('commands', fn (string $commands) => str_contains($commands, 'ip address 10.0.0.1 255.255.255.252')
                && str_contains($commands, 'ip route 192.168.20.0 255.255.255.0 10.0.0.2')
                && str_ends_with($commands, 'copy running-config startup-config'))
            ->assertSee('route balik');
    }

    public function test_routing_supports_a_default_route_and_a_host_route(): void
    {
        foreach ([0 => '0.0.0.0 0.0.0.0', 32 => '192.168.20.15 255.255.255.255'] as $prefix => $expected) {
            $this->post('/network-basics', array_replace($this->routing(), ['route_prefix' => $prefix]))
                ->assertOk()
                ->assertViewHas('commands', fn (string $commands) => str_contains($commands, 'ip route '.$expected.' 10.0.0.2'));
        }
    }

    public function test_switching_and_routing_keep_their_own_default_values(): void
    {
        $this->post('/network-basics', $this->routing())->assertOk();

        $this->get('/network-basics?type=switching')
            ->assertOk()
            ->assertSee('value="SW-LAB"', false)
            ->assertDontSee('value="R-LAB"', false);
    }

    public function test_routing_rejects_an_unreachable_next_hop(): void
    {
        $this->from('/network-basics?type=routing')->post('/network-basics', array_replace($this->routing(), [
            'next_hop' => '10.0.1.2',
        ]))->assertRedirect('/network-basics?type=routing')->assertSessionHasErrors('next_hop');

        $this->get('/network-basics?type=routing')
            ->assertOk()
            ->assertViewHas('type', 'routing')
            ->assertSee('Next hop harus berupa alamat host pada subnet interface yang sama.');
    }

    public function test_routing_rejects_network_broadcast_and_local_next_hop_addresses(): void
    {
        foreach ([['ip' => '10.0.0.0'], ['ip' => '10.0.0.3'], ['next_hop' => '10.0.0.0'], ['next_hop' => '10.0.0.3'], ['next_hop' => '10.0.0.1']] as $change) {
            $this->from('/network-basics?type=routing')->post('/network-basics', array_replace($this->routing(), $change))
                ->assertSessionHasErrors(array_key_first($change));
        }
    }

    private function switching(): array
    {
        return ['type' => 'switching', 'hostname' => 'SW-LAB', 'vlan_id' => 10, 'vlan_name' => 'STAFF', 'access_port' => 'FastEthernet0/1', 'trunk_port' => 'GigabitEthernet0/1'];
    }

    private function routing(): array
    {
        return ['type' => 'routing', 'hostname' => 'R-LAB', 'interface' => 'GigabitEthernet0/0', 'ip' => '10.0.0.1', 'prefix' => 30, 'destination' => '192.168.20.15', 'route_prefix' => 24, 'next_hop' => '10.0.0.2'];
    }
}
