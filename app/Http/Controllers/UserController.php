<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Http\Requests\ResetearClaveUsuarioRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
class UserController extends Controller
{
    use RespondeSegunCliente;

    /** Filtros de la tabla (query string): la URL es el estado de la vista. */
    private const FILTROS = ['buscar', 'rol', 'historial'];

    public function index(Request $request): Response
    {
        $filtros = array_filter($request->only(self::FILTROS), fn ($v) => $v !== null && $v !== '');
        // Eager-load del rol: el accessor $user->role lo resuelve sin N+1.
        $query = User::query()->with('rol')
            // Activos vs historial: los usuarios nunca se borran; inhabilitado = estado 0.
            ->where('estado', empty($filtros['historial']) ? 1 : 0)
            ->when($filtros['rol'] ?? null, fn ($q, $rol) => $q->where('role_id', $rol))
            ->when($filtros['buscar'] ?? null, function ($q, $buscar) {
                $buscar = trim($buscar);
                $q->where(fn ($w) => $w->where('name', 'like', "%{$buscar}%")
                    ->orWhere('email', 'like', "{$buscar}%")
                    ->orWhereHas('rol', fn ($r) => $r->where('nombre', 'like', "{$buscar}%")));
            })
            ->orderByDesc('created_at')->orderByDesc('id');

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $query->paginate(15)->withQueryString()->through(fn (User $u) => $this->fila($u, $request)),
            'filtros' => (object) $filtros,
            'roles' => fn () => Rol::orderBy('nombre')->get(['id', 'nombre']),
            'urls' => [
                'index' => route('users.index', absolute: false),
                'reportePdf' => route('users.reporte.pdf', absolute: false),
                'checkEmail' => route('users.check-email', absolute: false),
            ],
        ]);
    }

    /**
     * Fila de la tabla (todo lo que usan Ver y Editar). Espejo de `UsuarioFila`
     * en resources/js/pages/Usuarios/tipos.ts (lo verifica UsuarioPaginaTest).
     */
    private function fila(User $u, Request $request): array
    {
        return [
            'id' => $u->id,
            'nombre' => $u->name,
            'email' => $u->email,
            'rol_id' => $u->role_id,
            'rol' => $u->role,
            // Solo la foto subida; sin foto, la página muestra las iniciales (sin servicios externos).
            'avatar' => $u->avatar && Storage::disk('public')->exists($u->avatar) ? asset('storage/'.$u->avatar) : null,
            'inhabilitado' => ! $u->estado,
            'recuperacion_bloqueada' => $u->isRecoveryLocked() || $u->isRecoveryHardLocked(),
            'intentos_fallidos' => (int) $u->recovery_failed_attempts,
            'debe_cambiar_clave' => (bool) $u->password_reset_by_admin,
            // La propia cuenta no se inhabilita ni se le resetea la clave desde aquí.
            'es_propio' => $request->user()?->id === $u->id,
            'creado' => $u->created_at?->format('Y-m-d H:i'),
        ];
    }

    private function handleFileUpload($file, $oldPath, $directory)
    {
        // Delete old file if exists
        if ($oldPath && \Storage::disk('public')->exists($oldPath)) {
            \Storage::disk('public')->delete($oldPath);
        }

        // Upload new file via Storage (stored in storage/app/public/)
        // La extensión sale del contenido, no del nombre: un «x.html» no se sirve como página.
        $filename = uniqid() . '.' . ($file->guessExtension() ?: 'jpg');
        $path = $file->storeAs($directory, $filename, 'public');
        return $path;
    }

    public function store(StoreUserRequest $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role_id = $request->role_id;
        // estado=1 (activo) por defecto; la baja se hace con Inhabilitar/Habilitar, no en el form
        $user->estado = 1;

        // Manejar la subida del avatar
        if ($request->hasFile('avatar')) {
            $user->avatar = $this->handleFileUpload(
                $request->file('avatar'),
                null,
                'avatars'
            );
        }



        $user->save();

        return $this->responder($request, 'Usuario creado exitosamente.', ['success' => 'User created successfully.']);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role_id = $request->role_id;
        // 'estado' no se edita aquí; se gobierna con Inhabilitar/Habilitar

        // Manejar la subida del avatar
        if ($request->hasFile('avatar')) {
            $user->avatar = $this->handleFileUpload(
                $request->file('avatar'),
                $user->avatar,
                'avatars'
            );
        }



        $user->save();

        return $this->responder($request, 'Usuario actualizado exitosamente.', ['success' => 'User updated successfully.']);
    }

    /**
     * Inhabilitar un usuario = estado=0 (bloquea su login). No se borra la
     * cuenta para preservar referencias (created_by, auditoría). Salvaguardas:
     * no auto-inhabilitarse y no dejar al sistema sin Administrador activo.
     */
    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() === (int) $user->id) {
            return $this->rechazar($request, 'No puedes inhabilitar tu propia cuenta.');
        }

        if ($user->isAdmin() && $user->estado) {
            $adminsActivos = User::whereHas('rol', function ($q) {
                $q->where('nombre', 'Administrador');
            })->where('estado', 1)->count();
            if ($adminsActivos <= 1) {
                return $this->rechazar($request, 'No puedes inhabilitar al último administrador activo.');
            }
        }

        $user->estado = 0;
        $user->save();
        return $this->responder($request, 'Usuario inhabilitado exitosamente.', ['success' => 'Usuario inhabilitado exitosamente.']);
    }

    /**
     * Habilitar (restaurar) un usuario inhabilitado → estado=1.
     */
    public function restore(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->estado = 1;
        $user->save();
        return $this->responder($request, 'Usuario habilitado exitosamente.', ['success' => 'Usuario habilitado exitosamente.']);
    }

    public function reportePdf(Request $request)
    {
        // Eager-load del rol para que el accessor $user->role no genere N+1 en la vista.
        $query = User::query()->with('rol');
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }
        // Estatus: 1 = activos, 0 = inhabilitados; sin valor = todos.
        if ($request->input('estatus') === '1') {
            $query->where('estado', 1);
        } elseif ($request->input('estatus') === '0') {
            $query->where('estado', 0);
        }
        // Rango por fecha de registro (created_at)
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }
        $users = $query->orderBy('name')->get();

        $filtros = [];
        if ($request->filled('role_id')) {
            $filtros['Rol'] = optional(\App\Models\Rol::find($request->role_id))->nombre
                ?? ('#' . $request->role_id);
        }
        if ($request->input('estatus') === '1') {
            $filtros['Estatus'] = 'Activos';
        } elseif ($request->input('estatus') === '0') {
            $filtros['Estatus'] = 'Inhabilitados';
        }
        if ($rango = \App\Support\ReporteFiltros::rango($request->fecha_desde, $request->fecha_hasta)) {
            $filtros['Fecha de registro'] = $rango;
        }

        $pdf = \PDF::loadView('admin.users.reporte_pdf', compact('users', 'filtros'))
            ->setPaper('a4', 'landscape');
        return $pdf->stream('usuarios_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Verificar email (AJAX)
     */
    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        if (!$email)
            return response()->json(['exists' => false]);

        $query = User::where('email', $email);

        $excludeId = $request->input('exclude_id');
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return response()->json(['exists' => $query->exists()]);
    }

    /**
     * Desbloquea la recuperación de contraseña del usuario (admin).
     * Limpia el contador de intentos fallidos y el bloqueo temporal.
     */
    public function unlockRecovery(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'recovery_failed_attempts' => 0,
            'recovery_locked_until'    => null,
        ]);

        return $this->responder($request, 'Recuperación desbloqueada correctamente.');
    }

    /**
     * Reset de contraseña por admin: asigna una contraseña temporal y
     * marca al usuario para que la cambie en su próximo login.
     */
    public function resetPassword(ResetearClaveUsuarioRequest $request, $id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() === $user->id) {
            return $this->rechazar($request, 'No puedes resetear tu propia contraseña desde este panel.');
        }

        $user->forceFill([
            'password'                      => Hash::make($request->password),
            'remember_token'                => null,
            'password_reset_by_admin'       => true,
            'recovery_must_reset_questions' => true,
            'recovery_failed_attempts'      => 0,
            'recovery_locked_until'         => null,
        ])->save();

        return $this->responder($request, 'Contraseña reseteada. El usuario deberá cambiarla en su próximo inicio de sesión.');
    }
}

