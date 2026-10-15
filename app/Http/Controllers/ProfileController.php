<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\RecoveryQuestionController;
use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\UserRecoveryQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    use RespondeSegunCliente;

    /**
     * Mi perfil (Inertia): datos, contraseña, preguntas de seguridad y foto.
     * El middleware EnsureRecoveryQuestionsConfigured trae aquí (con
     * `warning_recovery`) a quien aún no configuró sus preguntas.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user()->load('recoveryQuestions');

        return Inertia::render('Perfil/Index', [
            'usuario' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatarSubido(),
                'rol' => $user->role,
                'activo' => (bool) $user->estado,
                'desde' => $user->created_at?->toDateString(),
            ],
            'catalogo' => collect(config('recovery_questions.questions', []))->map(fn ($texto, $id) => ['id' => (int) $id, 'texto' => $texto])->values()->all(),
            // Solo qué pregunta tiene cada bloque: las respuestas están cifradas.
            'preguntas' => $user->recoveryQuestions->sortBy('orden')->map(fn ($q) => ['orden' => (int) $q->orden, 'pregunta_id' => (int) $q->pregunta_id])->values()->all(),
            'configuradas' => $user->hasRecoveryQuestionsConfigured(),
            'debeReconfigurar' => (bool) $user->recovery_must_reset_questions,
            'forzado' => session('warning_recovery'),
            'sinCambios' => (bool) session('warning_recovery_no_changes'),
            'urls' => [
                'perfil' => route('profile.update', absolute: false),
                'contrasena' => route('password.update', absolute: false),
                'preguntas' => route('profile.recovery-questions.update', absolute: false),
                'avatar' => route('profile.avatar.update', absolute: false),
            ],
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

        // 2b) Reconfiguración obligatoria (se usó la recuperación o un
        //     administrador la pidió): se responden de nuevo las 3.
        if ($configured && $user->recovery_must_reset_questions && count($editedIndices) !== 3) {
            throw ValidationException::withMessages([
                'cambios' => 'Por seguridad, debes volver a responder las 3 preguntas.',
            ]);
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
            ->with('status', 'recovery-questions-updated')
            ->with('success', 'Preguntas de seguridad guardadas.');
    }

    /**
     * Actualiza la foto de perfil del usuario (Inertia: aviso flash; JSON para clientes antiguos).
     */
    public function updateAvatar(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ], [
            'avatar.required' => 'Selecciona una imagen.',
            'avatar.image'    => 'El archivo debe ser una imagen.',
            'avatar.mimes'    => 'Formatos permitidos: JPG, PNG o GIF.',
            'avatar.max'      => 'La imagen no debe superar los 2 MB.',
        ]);

        $user = $request->user();
        $anterior = $user->avatar;

        // store() nombra el archivo con la extensión que corresponde a su
        // contenido real (no la que trae el nombre del cliente): un .html
        // disfrazado no queda servido como página desde /storage.
        $user->avatar = $request->file('avatar')->store('avatars', 'public');
        $user->save();

        // La foto vieja se borra solo cuando la nueva ya quedó guardada.
        if ($anterior && $anterior !== $user->avatar && Storage::disk('public')->exists($anterior)) {
            Storage::disk('public')->delete($anterior);
        }

        return $this->responder($request, 'Foto de perfil actualizada.', [
            'success'    => true,
            'avatar_url' => $user->avatar_url,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $correoAnterior = mb_strtolower((string) $user->email);
        $user->fill($request->datos());

        // Pasar un correo viejo a minúsculas no es cambiarlo: no pierde la verificación.
        if ($user->isDirty('email') && $user->email !== $correoAnterior) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated')->with('success', 'Datos actualizados.');
    }

    // NOTA: el método destroy() (autoborrado de cuenta) se eliminó por política:
    // una cuenta de usuario NO se borra nunca. Solo un administrador puede
    // inhabilitarla (estado=0) desde el módulo de Usuarios (UserController@destroy).
}
