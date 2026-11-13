<?php

namespace Tests\Feature;

use Tests\TestCase;

class LearningFeaturesTest extends TestCase
{
    public function test_dashboard_starts_with_an_honest_empty_state(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Ruang belajarmu')
            ->assertSee('Belum ada aktivitas.')
            ->assertSee('Lanjutkan latihan')
            ->assertSee('data-latest-activity hidden', false);
    }

    public function test_configuration_has_an_explanation_for_every_command(): void
    {
        $response = $this->post('/network-basics', $this->switching());
        $response->assertOk()->assertSee('Mode belajar')->assertSee('Topologi interaktif');
        $commands = explode("\n", $response->viewData('commands'));
        $lessons = $response->viewData('lessons');
        $this->assertSame($commands, array_column($lessons, 'command'));
        foreach ($lessons as $step) {
            $this->assertNotEmpty($step['purpose']);
            $this->assertNotEmpty($step['check']);
            $this->assertNotEmpty($step['expected']);
        }
    }

    public function test_successful_configuration_emits_only_validated_activity_data(): void
    {
        $this->post('/network-basics', $this->switching() + ['extra' => 'not-stored'])
            ->assertOk()
            ->assertViewHas('activity', fn (array $activity) => $activity['kind'] === 'configuration'
                && $activity['input']['hostname'] === 'SW-LAB'
                && !isset($activity['input']['extra'])
                && !isset($activity['input']['_token']))
            ->assertSee('id="netcalc-activity"', false);
    }

    public function test_calculator_results_can_be_resumed_using_validated_inputs(): void
    {
        $this->post('/calculate', ['mode' => 'vlsm', 'ip' => '192.168.1.0', 'cidr' => '/24', 'hosts' => '50, 25, 10'])
            ->assertOk()
            ->assertViewHas('activity', fn (array $activity) => $activity['kind'] === 'calculator'
                && $activity['input']['cidr'] === '24'
                && $activity['input']['hosts'] === '50, 25, 10');
    }

    public function test_invalid_configuration_does_not_emit_a_saved_activity(): void
    {
        $this->from('/network-basics')->post('/network-basics', array_replace($this->switching(), ['vlan_id' => 9999]))
            ->assertRedirect('/network-basics');
        $this->get('/network-basics')->assertOk()->assertDontSee('id="netcalc-activity"', false);
    }

    private function switching(): array
    {
        return ['type' => 'switching', 'hostname' => 'SW-LAB', 'vlan_id' => 10, 'vlan_name' => 'STAFF', 'access_port' => 'FastEthernet0/1', 'trunk_port' => 'GigabitEthernet0/1'];
    }
}
