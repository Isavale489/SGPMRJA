<?php

namespace Tests\Feature\Flujos;

use App\Http\Controllers\Auth\RecoveryQuestionController;
use App\Models\RecoveryAttempt;
use App\Models\User;
use App\Models\UserRecoveryQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Recuperación de contraseña por preguntas de seguridad: mensaje genérico,
 * normalización de respuestas, bloqueo temporal y total, token de un solo uso
 * con vencimiento y cierre del resto de sesiones. Escritos ANTES de migrar las
 * pantallas de acceso a Inertia: verifican BD y redirecciones, no el HTML.
 */
class RecuperacionFlujoTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    private const CLAVE = 'Nueva.Clave1';

    /** Usuario con sus 3 preguntas: «Firulais», «Acarigua», «Caracas FC». */
    private function usuarioConPreguntas(array $attrs = []): User
    {
        // El factory ya trae preguntas genéricas: se reemplazan por unas con respuesta conocida.
        $u = User::factory()->sinPreguntasSeguridad()->create($attrs);
        foreach ([[1, 'Firulais'], [2, 'Acarigua'], [3, 'Caracas FC']] as $i => [$pregunta, $respuesta]) {
            UserRecoveryQuestion::create([
                'user_id' => $u->id,
                'pregunta_id' => $pregunta,
                'respuesta' => Hash::make(RecoveryQuestionController::normalizeAnswer($respuesta)),
                'orden' => $i + 1,
            ]);
        }

        return $u;
    }

    /** @return array<int, string> id de la pregunta => respuesta */
    private function respuestas(User $u, array $textos = ['Firulais', 'Acarigua', 'Caracas FC']): array
    {
        return $u->recoveryQuestions()->orderBy('orden')->pluck('id')->values()
            ->mapWithKeys(fn ($id, $i) => [$id => $textos[$i]])->all();
    }

    public function test_un_correo_sin_preguntas_recibe_el_mismo_mensaje_generico_que_uno_inexistente(): void
    {
        $sinPreguntas = User::factory()->sinPreguntasSeguridad()->create();

        foreach (['nadie@atlantico.test', $sinPreguntas->email] as $email) {
            $this->from(route('recovery.email.show'))->post(route('recovery.email.process'), ['email' => $email])
                ->assertRedirect(route('recovery.email.show'))
                ->assertSessionHasErrors(['email' => 'Si el correo está registrado y tiene preguntas configuradas, podrás continuar. Verifica e intenta de nuevo.']);
        }
        $this->assertSame(2, RecoveryAttempt::where('resultado', 'fallo')->count());
    }

    public function test_flujo_completo_normaliza_respuestas_cambia_la_clave_y_cierra_las_sesiones(): void
    {
        $u = $this->usuarioConPreguntas();
        $rememberAntes = $u->remember_token;

        $this->post(route('recovery.email.process'), ['email' => $u->email])->assertRedirect(route('recovery.questions.show'));
        $this->get(route('recovery.questions.show'))->assertOk()->assertSee('¿Cuál es el nombre de tu primera mascota?');

        // Mayúsculas, espacios de más y espacios internos repetidos no importan.
        $r = $this->post(route('recovery.questions.validate'), ['respuestas' => $this->respuestas($u, ['  FIRULAIS ', 'acarigua', 'caracas    fc'])]);
        $r->assertRedirect();
        $this->assertStringContainsString('/recovery/reset/', $r->headers->get('Location'));
        $token = basename(parse_url($r->headers->get('Location'), PHP_URL_PATH));

        $this->get(route('recovery.reset.show', ['token' => $token]))->assertOk();
        $this->post(route('recovery.reset.process'), ['token' => $token, 'password' => self::CLAVE, 'password_confirmation' => self::CLAVE])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $u->refresh();
        $this->assertTrue(Hash::check(self::CLAVE, $u->password));
        $this->assertNotSame($rememberAntes, $u->remember_token); // «Recuérdame» de otros equipos queda sin efecto
        $this->assertTrue((bool) $u->recovery_must_reset_questions);
        $this->assertSame(0, (int) $u->recovery_failed_attempts);
        $this->assertSame(1, RecoveryAttempt::where('user_id', $u->id)->where('resultado', 'exito')->count());

        // El token es de un solo uso: la sesión de recuperación se limpió.
        $this->get(route('recovery.reset.show', ['token' => $token]))->assertRedirect(route('recovery.email.show'));
    }

    public function test_respuestas_incorrectas_bloquean_temporalmente_al_quinto_intento(): void
    {
        $u = $this->usuarioConPreguntas();
        $this->post(route('recovery.email.process'), ['email' => $u->email]);

        foreach (range(1, 5) as $n) {
            $this->from(route('recovery.questions.show'))
                ->post(route('recovery.questions.validate'), ['respuestas' => $this->respuestas($u, ['Firulais', 'Acarigua', 'Magallanes'])])
                ->assertSessionHasErrors(['respuestas' => 'Una o más respuestas son incorrectas.']);
        }

        $u->refresh();
        $this->assertSame(5, (int) $u->recovery_failed_attempts);
        $this->assertTrue($u->isRecoveryLocked());
        $this->assertSame(5, RecoveryAttempt::where('user_id', $u->id)->where('resultado', 'fallo')->count());

        // Bloqueado: ni con las respuestas correctas, y al volver a empezar va a la pantalla de bloqueo.
        $this->post(route('recovery.questions.validate'), ['respuestas' => $this->respuestas($u)])->assertRedirect(route('recovery.locked'));
        $this->post(route('recovery.email.process'), ['email' => $u->email])
            ->assertRedirect(route('recovery.locked'))
            ->assertSessionHas('lock_type', 'soft');
        $this->get(route('recovery.locked'))->assertOk();
    }

    public function test_al_decimo_intento_el_bloqueo_es_total_aunque_venza_el_temporal(): void
    {
        $u = $this->usuarioConPreguntas(['recovery_failed_attempts' => 10, 'recovery_locked_until' => now()->subMinute()]);

        $this->post(route('recovery.email.process'), ['email' => $u->email])
            ->assertRedirect(route('recovery.locked'))
            ->assertSessionHas('lock_type', 'hard');
        $this->assertSame(1, RecoveryAttempt::where('user_id', $u->id)->where('resultado', 'bloqueado')->count());
    }

    public function test_el_token_vence_y_uno_inventado_no_sirve(): void
    {
        $u = $this->usuarioConPreguntas();
        $this->post(route('recovery.email.process'), ['email' => $u->email]);
        $r = $this->post(route('recovery.questions.validate'), ['respuestas' => $this->respuestas($u)]);
        $token = basename(parse_url($r->headers->get('Location'), PHP_URL_PATH));

        $this->get(route('recovery.reset.show', ['token' => 'inventado']))
            ->assertRedirect(route('recovery.email.show'))
            ->assertSessionHasErrors('email');

        $this->travel((int) config('recovery_questions.reset_token_ttl', 300) + 1)->seconds();
        $this->post(route('recovery.reset.process'), ['token' => $token, 'password' => self::CLAVE, 'password_confirmation' => self::CLAVE])
            ->assertRedirect(route('recovery.email.show'))
            ->assertSessionHasErrors('email');
        $this->assertFalse(Hash::check(self::CLAVE, $u->fresh()->password));
    }

    public function test_sin_pasar_por_el_correo_no_se_ven_ni_validan_preguntas(): void
    {
        $u = $this->usuarioConPreguntas();

        $this->get(route('recovery.questions.show'))->assertRedirect(route('recovery.email.show'));
        $this->post(route('recovery.questions.validate'), ['respuestas' => $this->respuestas($u)])->assertRedirect(route('recovery.email.show'));
    }

    public function test_el_login_rechaza_cuentas_inhabilitadas_y_limita_los_intentos(): void
    {
        $inactivo = User::factory()->create(['password' => Hash::make(self::CLAVE), 'estado' => false]);
        $this->post(route('login'), ['email' => $inactivo->email, 'password' => self::CLAVE])
            ->assertSessionHasErrors(['email' => 'Tu cuenta ha sido desactivada. Por favor contacta al administrador.']);
        $this->assertGuest();

        $u = User::factory()->create(['password' => Hash::make(self::CLAVE)]);
        foreach (range(1, 5) as $n) {
            $this->post(route('login'), ['email' => $u->email, 'password' => 'Incorrecta.1']);
        }
        // Al sexto, bloqueado aunque la clave sea la correcta.
        $this->post(route('login'), ['email' => $u->email, 'password' => self::CLAVE])->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
