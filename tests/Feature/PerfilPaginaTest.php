<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/** Página Inertia de Mi perfil: contrato, desvío forzoso y respuestas a Inertia. */
class PerfilPaginaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function clavesTs(): array
    {
        $ts = file_get_contents(resource_path('js/pages/Perfil/Index.tsx'));
        preg_match('/export interface PaginaPerfil \{(.*?)\n\}/s', $ts, $m);
        preg_match_all('/^    (\w+)\??:/m', $m[1] ?? '', $claves);
        $this->assertNotEmpty($claves[1]);

        return $claves[1];
    }

    private function inertia(): array
    {
        return ['X-Inertia' => 'true', 'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request()), 'X-Requested-With' => 'XMLHttpRequest'];
    }

    public function test_la_pagina_cumple_el_contrato_y_no_expone_respuestas(): void
    {
        $u = User::factory()->create();

        $props = $this->actingAs($u)->get(route('profile.edit'))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Perfil/Index')
                ->where('configuradas', true)
                ->has('preguntas', 3)
                ->where('preguntas.0', ['orden' => 1, 'pregunta_id' => 1])
                ->where('usuario.avatar', null))
            ->viewData('page')['props'];

        foreach ($this->clavesTs() as $clave) {
            $this->assertArrayHasKey($clave, $props);
        }
        $this->assertStringNotContainsString('respuesta', json_encode($props['preguntas']));
    }

    public function test_el_desvio_forzoso_llega_como_aviso(): void
    {
        $u = User::factory()->sinPreguntasSeguridad()->create();

        $this->actingAs($u)->get(route('dashboard'))->assertRedirect(route('profile.edit'));
        $this->actingAs($u)->get(route('profile.edit'))
            ->assertInertia(fn (Assert $p) => $p->where('configuradas', false)->whereType('forzado', 'string'));
    }

    public function test_foto_y_contrasena_desde_inertia_avisan_con_flash(): void
    {
        Storage::fake('public');
        $u = User::factory()->create(['password' => bcrypt('Clave.Segura1')]);

        $this->actingAs($u)->from(route('profile.edit'))->withHeaders($this->inertia())
            ->post(route('profile.avatar.update'), ['avatar' => UploadedFile::fake()->image('yo.png')])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success', 'Foto de perfil actualizada.');

        $this->actingAs($u)->from(route('profile.edit'))->withHeaders($this->inertia())
            ->put(route('password.update'), ['current_password' => 'mala', 'password' => 'Nueva.Clave2', 'password_confirmation' => 'Nueva.Clave2'])
            ->assertSessionHasErrorsIn('updatePassword', ['current_password' => 'La contraseña actual no es correcta.']); // regresión: salía la clave cruda

        $this->actingAs($u)->from(route('profile.edit'))->withHeaders($this->inertia())
            ->put(route('password.update'), ['current_password' => 'Clave.Segura1', 'password' => 'Nueva.Clave2', 'password_confirmation' => 'Nueva.Clave2'])
            ->assertSessionHas('success', 'Contraseña actualizada.');

        // La plataforma comparte la foto subida.
        $this->flushHeaders();
        $this->actingAs($u->fresh())->get(route('profile.edit'))
            ->assertInertia(fn (Assert $p) => $p->whereType('auth.user.avatar_url', 'string'));
    }
}
