<?php

namespace Tests\Feature;

use App\Livewire\Perfil\Index as PerfilIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get(route('profiles.index'))->assertRedirect(route('login'));
    }

    public function test_actualiza_nombre_y_email(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(PerfilIndex::class)
            ->set('name', 'Nuevo Nombre')
            ->set('email', 'nuevo@correo.com')
            ->call('save');

        $user->refresh();
        $this->assertSame('Nuevo Nombre', $user->name);
        $this->assertSame('nuevo@correo.com', $user->email);
    }

    public function test_cambio_de_password_no_queda_doble_hasheado(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(PerfilIndex::class)
            ->set('password', 'claveNueva123')
            ->call('save');

        $this->assertTrue(Auth::validate(['email' => $user->email, 'password' => 'claveNueva123']));
    }

    public function test_password_vacio_no_cambia_la_actual(): void
    {
        $user = User::factory()->create(['password' => 'original123']);

        Livewire::actingAs($user)->test(PerfilIndex::class)
            ->set('name', 'X')
            ->set('password', '')
            ->call('save');

        $this->assertTrue(Auth::validate(['email' => $user->email, 'password' => 'original123']));
    }
}
