<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_home_page_opens_the_dashboard(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertViewIs('dashboard')
            ->assertSee('Selamat datang di NetCalc')
            ->assertSee(route('calculator'));
    }

    public function test_the_calculator_has_its_own_page(): void
    {
        $this->get('/calculator')
            ->assertOk()
            ->assertViewIs('calculator')
            ->assertViewHas('result', null)
            ->assertSee(route('dashboard'));
    }

    public function test_the_dashboard_can_open_vlsm_mode(): void
    {
        $this->get('/calculator?mode=vlsm')
            ->assertOk()
            ->assertViewHas('mode', 'vlsm');
    }

    public function test_subnet_calculation_still_returns_its_result(): void
    {
        $this->from('/calculator')->post('/calculate', [
            'mode' => 'subnet',
            'ip' => '192.168.1.15',
            'cidr' => '/24',
        ])
            ->assertOk()
            ->assertViewIs('calculator')
            ->assertViewHas('result', fn (array $result) => $result['network'] === '192.168.1.0')
            ->assertSee('Hasil subnetting');
    }

    public function test_vlsm_calculation_still_returns_allocations(): void
    {
        $this->from('/calculator')->post('/calculate', [
            'mode' => 'vlsm',
            'ip' => '192.168.1.0',
            'cidr' => '/24',
            'hosts' => '50, 25, 10',
        ])
            ->assertOk()
            ->assertViewHas('allocations', fn (array $rows) => count($rows) === 3)
            ->assertSee('Pembagian VLSM');
    }

    public function test_invalid_input_returns_to_the_calculator_with_errors(): void
    {
        $this->from('/calculator')->post('/calculate', [
            'mode' => 'subnet',
            'ip' => 'invalid-ip',
            'cidr' => '/24',
        ])
            ->assertRedirect('/calculator')
            ->assertSessionHasErrors('ip');

        $this->get('/calculator')
            ->assertOk()
            ->assertSee('role="alert"', false)
            ->assertSee('invalid-ip');
    }
}
