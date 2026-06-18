<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\RecoveryQuestionController;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\UserRecoveryQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->load('recoveryQuestions');

        return view('profile.edit', [
            'user'              => $user,
            'recoveryQuestions' => config('recovery_questions.questions', []),
        ]);
    }

    /**
     * Guarda o actualiza las preguntas de seguridad del usuario.
     *
     * Modos:
     *  - Configuración inicial (sin preguntas previas): los 3 bloques son
     *    obligatorios. No requiere contraseña actual.
     *  - Edición (con preguntas previas): el usuario puede editar uno o más
     *    bloques individualmente. Requiere contraseña actual para autorizar.
     *    Los bloques no editados conservan su valor previo.
     *
     * Estructura del request:
     *  cambios[N][editing]      = "1" | "0"  (¿este bloque fue editado?)
     *  cambios[N][pregunta_id]  = int        (id de pregunta)
     *  cambios[N][respuesta]    = string     (respuesta sólo si editing=1)
     *  current_password         = string     (sólo si ya estaba configurado)
     */
    public function updateRecoveryQuestions(Request $request): RedirectResponse
    {
        $user        = $request->user();
        $configured  = $user->hasRecoveryQuestionsConfigured();
        $catalog     = config('recovery_questions.questions', []);
        $validIds    = array_keys($catalog);

        $cambios = $request->input('cambios', []);
        if (!is_array($cambios) || count($cambios) !== 3) {
            throw ValidationException::withMessages([
                'cambios' => 'Datos inválidos. Recarga la página e intenta de nuevo.',
            ]);
        }

        // Bloques editados (editing=1)
        $editedIndices = collect($cambios)
            ->filter(fn($c) => ($c['editing'] ?? '0') === '1')
            ->keys()
            ->all();

        // 1) Configuración inicial → los 3 bloques deben editarse
        if (!$configured && count($editedIndices) !== 3) {
            throw ValidationException::withMessages([
                'cambios' => 'Debes configurar las 3 preguntas de seguridad.',
            ]);
        }

        // 2) Edición sin cambios → no hay nada que guardar
        if ($configured && count($editedIndices) === 0) {
            return Redirect::route('profile.edit')
                ->with('warning_recovery_no_changes', '1');
        }

        // 3) Si está configurado y hay edición, exigir contraseña actual
        if ($configured && count($editedIndices) > 0) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
            ], [
                'current_password.required'         => 'Debes ingresar tu contraseña actual.',
                'current_password.current_password' => 'La contraseña actual no es correcta.',
            ]);
        }

        // 4) Validar cada bloque editado
        $rules = [];
        foreach ($editedIndices as $i) {
            $rules["cambios.$i.pregunta_id"] = ['required', 'integer', 'in:' . implode(',', $validIds)];
            $rules["cambios.$i.respuesta"]   = ['required', 'string', 'min:3', 'max:255'];
        }
        if (!empty($rules)) {
            $request->validate($rules, [
                'cambios.*.respuesta.required' => 'La respuesta es obligatoria.',
                'cambios.*.respuesta.min'      => 'La respuesta debe tener al menos 3 caracteres.',
                'cambios.*.pregunta_id.in'     => 'La pregunta seleccionada no es válida.',
            ]);
        }

        // 5) Calcular el estado FINAL de las 3 preguntas (mezcla de existentes + ediciones)
        $existingByOrden = $user->recoveryQuestions()->get()->keyBy('orden');
        $finalQuestionIds = [];
        foreach ([1, 2, 3] as $orden) {
            $i = $orden - 1;
            $editing = ($cambios[$i]['editing'] ?? '0') === '1';
            if ($editing) {
                $finalQuestionIds[$orden] = (int) $cambios[$i]['pregunta_id'];
            } else {
                $existing = $existingByOrden->get($orden);
                $finalQuestionIds[$orden] = $existing ? (int) $existing->pregunta_id : null;
            }
        }

        // 6) Las 3 preguntas finales deben ser distintas
        $uniqueIds = array_unique(array_filter($finalQuestionIds));
        if (count($uniqueIds) !== 3) {
            throw ValidationException::withMessages([
                'cambios' => 'No puedes repetir la misma pregunta entre los 3 bloques.',
            ]);
        }

        // 7) Verificar que las respuestas finales sean todas distintas entre sí.
        //    - Las nuevas (editadas) las comparamos normalizadas entre ellas
        //    - Cada nueva la comparamos contra los hashes existentes de los bloques NO editados
        $newAnswersByOrden = [];
        foreach ($editedIndices as $i) {
            $orden = $i + 1;
            $newAnswersByOrden[$orden] = RecoveryQuestionController::normalizeAnswer($cambios[$i]['respuesta']);
        }

        // Duplicados entre las propias respuestas nuevas
        if (count(array_unique($newAnswersByOrden)) !== count($newAnswersByOrden)) {
            throw ValidationException::withMessages([
                'cambios' => 'Las respuestas que estás cambiando no pueden ser iguales entre sí.',
            ]);
        }

        // Cada nueva respuesta debe diferir de las existentes (de bloques no editados)
        foreach ($newAnswersByOrden as $orden => $newAnswer) {
            foreach ([1, 2, 3] as $otherOrden) {
                if ($otherOrden === $orden) continue;
                if (in_array($otherOrden - 1, $editedIndices, true)) continue; // ese bloque también se está editando
                $existing = $existingByOrden->get($otherOrden);
                if (!$existing) continue;
                if (Hash::check($newAnswer, $existing->respuesta)) {
                    throw ValidationException::withMessages([
                        "cambios.$orden" => 'Esta respuesta es igual a la de otra pregunta. Las 3 respuestas deben ser distintas.',
                    ]);
                }
            }
        }

        // 8) Persistir cambios en transacción
        DB::transaction(function () use ($user, $cambios, $editedIndices, $configured) {
            foreach ($editedIndices as $i) {
                $orden = $i + 1;
                $preguntaId = (int) $cambios[$i]['pregunta_id'];
                $respuesta  = RecoveryQuestionController::normalizeAnswer($cambios[$i]['respuesta']);

                UserRecoveryQuestion::updateOrCreate(
                    ['user_id' => $user->id, 'orden' => $orden],
                    [
                        'pregunta_id' => $preguntaId,
                        'respuesta'   => Hash::make($respuesta),
                    ]
                );
            }

            if ($user->recovery_must_reset_questions) {
                $user->update(['recovery_must_reset_questions' => false]);
            }
        });

        return Redirect::route('profile.edit')
            ->with('status', 'recovery-questions-updated');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    // NOTA: el método destroy() (autoborrado de cuenta) se eliminó por política:
    // una cuenta de usuario NO se borra nunca. Solo un administrador puede
    // inhabilitarla (estado=0) desde el módulo de Usuarios (UserController@destroy).
}
