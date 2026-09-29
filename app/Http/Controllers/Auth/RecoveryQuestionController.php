<?php

namespace App\Http\Controllers\Auth;

use App\Rules\ContrasenaSegura;
use App\Http\Controllers\Controller;
use App\Models\RecoveryAttempt;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RecoveryQuestionController extends Controller
{
    private const SESSION_KEY_EMAIL = 'recovery.email';
    private const SESSION_KEY_TOKEN = 'recovery.reset_token';
    private const SESSION_KEY_TOKEN_EXPIRES = 'recovery.reset_token_expires';

    /**
     * Pantalla de selección de método (email vs preguntas).
     */
    public function showMethodSelection(): View
    {
        return view('auth.recovery.method');
    }

    /**
     * Formulario para ingresar email (flujo de preguntas).
     */
    public function showEmailForm(): View
    {
        return view('auth.recovery.email');
    }

    /**
     * Procesa el email: verifica usuario, bloqueos, y redirige a preguntas.
     */
    public function processEmail(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();

        // Mensaje genérico para no revelar existencia (mejora #5)
        $genericError = 'Si el correo está registrado y tiene preguntas configuradas, podrás continuar. Verifica e intenta de nuevo.';

        if (!$user || !$user->hasRecoveryQuestionsConfigured()) {
            $this->logAttempt(null, $request->email, 'fallo', $request);
            return back()->withErrors(['email' => $genericError])->withInput();
        }

        // Bloqueo total (requiere admin)
        if ($user->isRecoveryHardLocked()) {
            $this->logAttempt($user->id, $request->email, 'bloqueado', $request);
            return redirect()->route('recovery.locked')->with('lock_type', 'hard');
        }

        // Bloqueo temporal
        if ($user->isRecoveryLocked()) {
            $this->logAttempt($user->id, $request->email, 'bloqueado', $request);
            return redirect()->route('recovery.locked')->with([
                'lock_type' => 'soft',
                'until' => $user->recovery_locked_until,
            ]);
        }

        $request->session()->put(self::SESSION_KEY_EMAIL, $user->email);

        return redirect()->route('recovery.questions.show');
    }

    /**
     * Muestra las 3 preguntas del usuario.
     */
    public function showQuestions(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get(self::SESSION_KEY_EMAIL);
        if (!$email) {
            // Sesión vencida (419): que el aviso del Handler llegue al formulario de correo.
            $request->session()->keep(['aviso']);

            return redirect()->route('recovery.email.show');
        }

        $user = User::where('email', $email)->first();
        if (!$user || !$user->hasRecoveryQuestionsConfigured()) {
            return redirect()->route('recovery.email.show');
        }

        if ($user->isRecoveryLocked() || $user->isRecoveryHardLocked()) {
            return redirect()->route('recovery.locked');
        }

        $questions = $user->recoveryQuestions()->get()->map(function ($q) {
            return [
                'id'       => $q->id,
                'orden'    => $q->orden,
                'pregunta' => $q->pregunta_texto,
            ];
        });

        return view('auth.recovery.answers', compact('questions'));
    }

    /**
     * Valida las respuestas. Si las 3 son correctas, genera token de sesión.
     */
    public function validateAnswers(Request $request): RedirectResponse
    {
        $request->validate([
            'respuestas'   => ['required', 'array', 'size:3'],
            'respuestas.*' => ['required', 'string', 'max:255'],
        ]);

        $email = $request->session()->get(self::SESSION_KEY_EMAIL);
        if (!$email) {
            // Sesión vencida (419): que el aviso del Handler llegue al formulario de correo.
            $request->session()->keep(['aviso']);

            return redirect()->route('recovery.email.show');
        }

        $user = User::where('email', $email)->first();
        if (!$user || !$user->hasRecoveryQuestionsConfigured()) {
            return redirect()->route('recovery.email.show');
        }

        if ($user->isRecoveryLocked() || $user->isRecoveryHardLocked()) {
            return redirect()->route('recovery.locked');
        }

        $questions = $user->recoveryQuestions()->get()->keyBy('id');
        $allCorrect = true;

        foreach ($request->input('respuestas') as $questionId => $answer) {
            $q = $questions->get($questionId);
            if (!$q) {
                $allCorrect = false;
                break;
            }
            $normalized = $this->normalizeAnswer($answer);
            if (!Hash::check($normalized, $q->respuesta)) {
                $allCorrect = false;
                break;
            }
        }

        if (!$allCorrect) {
            $user->increment('recovery_failed_attempts');

            $maxSoft = config('recovery_questions.max_attempts_soft_lock', 5);
            $lockMin = config('recovery_questions.soft_lock_minutes', 15);

            if ($user->recovery_failed_attempts >= $maxSoft && !$user->isRecoveryHardLocked()) {
                $user->update([
                    'recovery_locked_until' => now()->addMinutes($lockMin),
                ]);
            }

            $this->logAttempt($user->id, $email, 'fallo', $request);

            return back()->withErrors([
                'respuestas' => 'Una o más respuestas son incorrectas.',
            ]);
        }

        // Éxito: limpiar contadores y generar token de reset
        $user->update([
            'recovery_failed_attempts' => 0,
            'recovery_locked_until'    => null,
        ]);

        $token = Str::random(64);
        $ttl   = config('recovery_questions.reset_token_ttl', 300);

        $request->session()->put(self::SESSION_KEY_TOKEN, hash('sha256', $token));
        $request->session()->put(self::SESSION_KEY_TOKEN_EXPIRES, now()->addSeconds($ttl)->timestamp);

        $this->logAttempt($user->id, $email, 'exito', $request);

        return redirect()->route('recovery.reset.show', ['token' => $token]);
    }

    /**
     * Formulario para establecer nueva contraseña (requiere token válido).
     */
    public function showResetForm(Request $request, string $token): View|RedirectResponse
    {
        if (!$this->isResetTokenValid($request, $token)) {
            return redirect()->route('recovery.email.show')
                ->withErrors(['email' => 'La sesión de recuperación expiró. Inténtalo de nuevo.']);
        }

        return view('auth.recovery.reset', ['token' => $token]);
    }

    /**
     * Procesa el reset de contraseña.
     * Cierra todas las sesiones (mejora #6) y marca must_reset_questions (mejora #8).
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required', 'string'],
            'password' => ['required', 'confirmed', new ContrasenaSegura],
        ]);

        if (!$this->isResetTokenValid($request, $request->token)) {
            return redirect()->route('recovery.email.show')
                ->withErrors(['email' => 'La sesión de recuperación expiró. Inténtalo de nuevo.']);
        }

        $email = $request->session()->get(self::SESSION_KEY_EMAIL);
        $user  = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('recovery.email.show');
        }

        $user->forceFill([
            'password'                      => Hash::make($request->password),
            'remember_token'                => Str::random(60),
            'recovery_failed_attempts'      => 0,
            'recovery_locked_until'         => null,
            'recovery_must_reset_questions' => true,
        ])->save();

        // Limpiar sesión de recuperación
        $request->session()->forget([
            self::SESSION_KEY_EMAIL,
            self::SESSION_KEY_TOKEN,
            self::SESSION_KEY_TOKEN_EXPIRES,
        ]);

        return redirect()->route('login')->with([
            'status' => 'Contraseña actualizada correctamente. Inicia sesión con tu nueva contraseña.',
        ]);
    }

    /**
     * Pantalla informativa cuando el usuario está bloqueado.
     */
    public function showLocked(): View
    {
        return view('auth.recovery.locked');
    }

    /**
     * Verifica que el token recibido coincide con el almacenado en sesión y no expiró.
     */
    private function isResetTokenValid(Request $request, string $token): bool
    {
        $stored  = $request->session()->get(self::SESSION_KEY_TOKEN);
        $expires = $request->session()->get(self::SESSION_KEY_TOKEN_EXPIRES);

        if (!$stored || !$expires) {
            return false;
        }
        if (now()->timestamp > $expires) {
            return false;
        }
        return hash_equals($stored, hash('sha256', $token));
    }

    /**
     * Normaliza la respuesta para comparación robusta:
     * - Normaliza Unicode a NFC (evita problemas con tildes precompuestas vs combinadas)
     * - Trim de espacios al inicio/final
     * - Colapsa espacios internos múltiples a uno solo
     * - Convierte a minúsculas
     */
    public static function normalizeAnswer(string $answer): string
    {
        if (class_exists(\Normalizer::class)) {
            $answer = \Normalizer::normalize($answer, \Normalizer::FORM_C) ?: $answer;
        }
        $answer = trim($answer);
        $answer = preg_replace('/\s+/u', ' ', $answer);
        return mb_strtolower($answer);
    }

    /**
     * Registra un intento en la bitácora.
     */
    private function logAttempt(?int $userId, string $email, string $resultado, Request $request): void
    {
        RecoveryAttempt::create([
            'user_id'    => $userId,
            'email'      => $email,
            'ip'         => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'tipo'       => 'preguntas',
            'resultado'  => $resultado,
            'created_at' => now(),
        ]);
    }
}
