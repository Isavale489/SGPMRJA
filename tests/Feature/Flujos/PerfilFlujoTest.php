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

    /** Regresión: con 3 preguntas y la reconfiguración pedida, el formulario no mandaba la contraseña y el servidor la exigía. */
    public function test_la_reconfiguracion_obligatoria_pide_las_3_y_la_contrasena(): void
    {
        $u = User::factory()->create(['password' => Hash::make('Clave.Segura1'), 'recovery_must_reset_questions' => true]);
        $this->actingAs($u)->get(route('dashboard'))->assertRedirect(route('profile.edit'));
        $this->actingAs($u)->get(route('profile.edit'))->assertInertia(fn ($p) => $p->where('configuradas', true)->where('debeReconfigurar', true));

        $this->actingAs($u)->patch(route('profile.recovery-questions.update'), ['cambios' => $this->bloques([0]), 'current_password' => 'Clave.Segura1'])
            ->assertSessionHasErrors('cambios');
        $this->actingAs($u)->patch(route('profile.recovery-questions.update'), ['cambios' => $this->bloques([0, 1, 2])])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($u)->patch(route('profile.recovery-questions.update'), ['cambios' => $this->bloques([0, 1, 2]), 'current_password' => 'Clave.Segura1'])
            ->assertSessionHas('status', 'recovery-questions-updated');
        $this->assertFalse((bool) $u->fresh()->recovery_must_reset_questions);
        $this->actingAs($u)->get(route('dashboard'))->assertOk();
    }

    /** Regresión: se guardaba con la extensión que mandaba el cliente (un .html quedaba servido desde /storage). */
    public function test_la_foto_se_guarda_con_la_extension_de_su_contenido(): void
    {
        Storage::fake('public');
        $u = User::factory()->create(['avatar' => 'avatars/vieja.png']);
        Storage::disk('public')->put('avatars/vieja.png', 'x');

        // Un PNG real que llega con nombre .html.
        $png = UploadedFile::fake()->image('yo.png', 50, 50);
        $disfrazado = new UploadedFile($png->getRealPath(), 'pagina.html', 'text/html', null, true);

        $this->actingAs($u)->post(route('profile.avatar.update'), ['avatar' => $disfrazado], ['Accept' => 'application/json'])
            ->assertOk();

        $u->refresh();
        $this->assertStringEndsWith('.png', $u->avatar);
        Storage::disk('public')->assertExists($u->avatar);
        Storage::disk('public')->assertMissing('avatars/vieja.png');
    }

    public function test_en_modo_forzado_tambien_se_sube_la_foto(): void
    {
        Storage::fake('public');
        $u = User::factory()->sinPreguntasSeguridad()->create();

        $this->actingAs($u)->post(route('profile.avatar.update'), ['avatar' => UploadedFile::fake()->image('yo.png', 50, 50)], ['Accept' => 'application/json'])
            ->assertOk();
        $this->assertNotNull($u->fresh()->avatar);
    }

    public function test_cambiar_el_correo_exige_la_contrasena_actual(): void
    {
        $u = User::factory()->create(['email' => 'ana@atlantico.test', 'password' => Hash::make('Clave.Segura1')]);

        // Solo el nombre: sin contraseña.
        $this->actingAs($u)->patch(route('profile.update'), ['name' => 'Ana Rojas', 'email' => 'ana@atlantico.test'])->assertSessionHasNoErrors();
        $this->assertSame('Ana Rojas', $u->fresh()->name);

        $this->actingAs($u)->patch(route('profile.update'), ['name' => 'Ana Rojas', 'email' => 'otra@atlantico.test'])->assertSessionHasErrors('current_password');
        $this->actingAs($u)->patch(route('profile.update'), ['name' => 'Ana Rojas', 'email' => 'otra@atlantico.test', 'current_password' => 'mala'])->assertSessionHasErrors('current_password');
        $this->assertSame('ana@atlantico.test', $u->fresh()->email);

        // Con la contraseña; el correo se guarda en minúsculas.
        $this->actingAs($u)->patch(route('profile.update'), ['name' => 'Ana Rojas', 'email' => ' Otra@Atlantico.test', 'current_password' => 'Clave.Segura1'])->assertSessionHasNoErrors();
        $this->assertSame('otra@atlantico.test', $u->fresh()->email);
    }

    public function test_un_correo_viejo_en_mayusculas_se_normaliza_sin_pedir_contrasena(): void
    {
        $u = User::factory()->create(['email' => 'Ana@Atlantico.test']);

        $this->actingAs($u)->patch(route('profile.update'), ['name' => 'Ana', 'email' => 'Ana@Atlantico.test'])->assertSessionHasNoErrors();
        $u->refresh();
        $this->assertSame('ana@atlantico.test', $u->email);
        $this->assertNotNull($u->email_verified_at);
    }
}
