<?php

namespace Tests\Feature\Flujos;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Perfil: preguntas de seguridad (configuración inicial obligatoria y edición
 * con contraseña), foto y el desvío forzoso a /profile. Escritos ANTES de
 * migrar a Inertia.
 */
class PerfilFlujoTest extends TestCase
{
    use RefreshDatabase;

    private function bloques(array $editados, array $preguntas = [4, 5, 6], array $respuestas = ['Firulais', 'Acarigua', 'Portuguesa FC']): array
    {
        return collect([0, 1, 2])->map(fn ($i) => in_array($i, $editados, true)
            ? ['editing' => '1', 'pregunta_id' => $preguntas[$i], 'respuesta' => $respuestas[$i]]
            : ['editing' => '0'])->all();
    }

    public function test_sin_preguntas_se_desvia_al_perfil_y_al_configurarlas_se_libera(): void
    {
        $u = User::factory()->sinPreguntasSeguridad()->create();

        $this->actingAs($u)->get(route('dashboard'))
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('warning_recovery');
        $this->actingAs($u)->get(route('profile.edit'))->assertOk();

        // Configuración inicial: las 3 son obligatorias.
        $this->actingAs($u)->patch(route('profile.recovery-questions.update'), ['cambios' => $this->bloques([0, 1])])
            ->assertSessionHasErrors('cambios');
        $this->assertFalse($u->fresh()->hasRecoveryQuestionsConfigured());

        $this->actingAs($u)->patch(route('profile.recovery-questions.update'), ['cambios' => $this->bloques([0, 1, 2])])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'recovery-questions-updated');

        $u->refresh();
        $this->assertTrue($u->hasRecoveryQuestionsConfigured());
        // Se guarda normalizada (minúsculas, espacios colapsados) y con hash.
        $this->assertTrue(Hash::check('firulais', $u->recoveryQuestions()->where('orden', 1)->value('respuesta')));
        $this->actingAs($u)->get(route('dashboard'))->assertOk();
    }

    public function test_preguntas_y_respuestas_deben_ser_distintas(): void
    {
        $u = User::factory()->sinPreguntasSeguridad()->create();

        $this->actingAs($u)->patch(route('profile.recovery-questions.update'), ['cambios' => $this->bloques([0, 1, 2], [4, 4, 6])])
            ->assertSessionHasErrors('cambios');
        $this->actingAs($u)->patch(route('profile.recovery-questions.update'), ['cambios' => $this->bloques([0, 1, 2], [4, 5, 6], ['Igual', 'igual ', 'Otra'])])
            ->assertSessionHasErrors('cambios');

        $this->assertSame(0, $u->recoveryQuestions()->count());
    }

    public function test_editar_una_pregunta_exige_la_contrasena_actual(): void
    {
        $u = User::factory()->create(['password' => Hash::make('Clave.Segura1')]);
        $cambio = ['cambios' => $this->bloques([1], [0, 9, 0], ['', 'Guanare', ''])];

        $this->actingAs($u)->patch(route('profile.recovery-questions.update'), $cambio)->assertSessionHasErrors('current_password');
        $this->actingAs($u)->patch(route('profile.recovery-questions.update'), [...$cambio, 'current_password' => 'otra'])->assertSessionHasErrors('current_password');

        $this->actingAs($u)->patch(route('profile.recovery-questions.update'), [...$cambio, 'current_password' => 'Clave.Segura1'])
            ->assertSessionHas('status', 'recovery-questions-updated');
        $this->assertSame(9, (int) $u->recoveryQuestions()->where('orden', 2)->value('pregunta_id'));
        $this->assertSame(1, (int) $u->recoveryQuestions()->where('orden', 1)->value('pregunta_id')); // intacta

        // Sin cambios: aviso, no error.
        $this->actingAs($u)->patch(route('profile.recovery-questions.update'), ['cambios' => $this->bloques([])])
            ->assertSessionHas('warning_recovery_no_changes');
    }

    /** Regresión: la ruta no estaba en las comunes de config/modulos.php y daba 403 a todos. */
    public function test_subir_la_foto_de_perfil(): void
    {
        Storage::fake('public');
        $u = User::factory()->create();

        $this->actingAs($u)->post(route('profile.avatar.update'), ['avatar' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])
            ->assertStatus(422);

        $this->actingAs($u)->post(route('profile.avatar.update'), ['avatar' => UploadedFile::fake()->image('yo.png', 200, 200)], ['Accept' => 'application/json'])
            ->assertOk();

        $u->refresh();
        $this->assertNotNull($u->avatar);
        Storage::disk('public')->assertExists($u->avatar);
    }
}
