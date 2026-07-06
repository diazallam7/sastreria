<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShellUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_renderiza_con_el_shell_para_autenticado(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/')
            ->assertOk()
            ->assertSee('Panel')
            ->assertSee('Medina')          // marca en el sidebar
            ->assertSee('Ventas del mes');  // tarjeta del dashboard
    }
}
