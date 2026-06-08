<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;
class UserController extends Controller
{
    public function index(Request $request)
    {
        $historial = $request->has('historial');
        return view('admin.users.index', compact('historial'));
    }

    public function getUsers(Request $request)
    {
        $users = User::query();

        if ($request->filled('filter_role')) {
            $users->where('role', $request->input('filter_role'));
        }

        // Activos vs Historial: lo define la página (no un filtro). La principal
        // muestra solo usuarios activos; el historial (?historial=true) solo los
        // inhabilitados (estado=0, sin acceso al sistema).
        $users->where('estado', $request->boolean('historial') ? 0 : 1);

        $users->orderBy('created_at', 'desc'); // más reciente primero (estándar del sistema)

        return DataTables::of($users)
            // Búsqueda estricta: solo por nombre, email y rol del usuario. Sobrescribe
            // el buscador global para no romper sobre las columnas computadas (estado
            // de recuperación) ni matchear datos no indicados en la barra.
            ->filter(function ($query) use ($request) {
                $keyword = trim((string) $request->input('search.value'));
                if ($keyword === '') {
                    return;
                }
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%")
                      ->orWhere('role', 'like', "%{$keyword}%");
                });
            }, true)
            ->editColumn('avatar', function ($user) {
                return $user->avatar ? asset('storage/' . $user->avatar) : null;
            })
            ->addColumn('recovery_locked', function ($user) {
                return $user->isRecoveryLocked() || $user->isRecoveryHardLocked();
            })
            ->addColumn('recovery_failed_attempts', function ($user) {
                return (int) $user->recovery_failed_attempts;
            })
            ->make(true);
    }

    private function handleFileUpload($file, $oldPath, $directory)
    {
        // Delete old file if exists
        if ($oldPath && \Storage::disk('public')->exists($oldPath)) {
            \Storage::disk('public')->delete($oldPath);
        }

        // Upload new file via Storage (stored in storage/app/public/)
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'public');
        return $path;
    }

    public function store(StoreUserRequest $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = $request->role;
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

        return response()->json(['success' => 'User created successfully.']);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $data = $user->toArray();
        $data['avatar'] = $user->avatar ? asset('storage/' . $user->avatar) : null;
        return response()->json($data);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
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

        return response()->json(['success' => 'User updated successfully.']);
    }

    /**
     * Inhabilitar un usuario = estado=0 (bloquea su login). No se borra la
     * cuenta para preservar referencias (created_by, auditoría). Salvaguardas:
     * no auto-inhabilitarse y no dejar al sistema sin Administrador activo.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() === (int) $user->id) {
            return response()->json(['message' => 'No puedes inhabilitar tu propia cuenta.'], 422);
        }

        if ($user->isAdmin() && $user->estado) {
            $adminsActivos = User::where('role', 'Administrador')->where('estado', 1)->count();
            if ($adminsActivos <= 1) {
                return response()->json(['message' => 'No puedes inhabilitar al último administrador activo.'], 422);
            }
        }

        $user->estado = 0;
        $user->save();
        return response()->json(['success' => 'Usuario inhabilitado exitosamente.']);
    }

    /**
     * Habilitar (restaurar) un usuario inhabilitado → estado=1.
     */
    public function restore($id)
    {
        $user = User::findOrFail($id);
        $user->estado = 1;
        $user->save();
        return response()->json(['success' => 'Usuario habilitado exitosamente.']);
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'estado' => $user->estado,
            'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,

            'created_at' => $user->created_at->format('d/m/Y'),
            'updated_at' => $user->updated_at->format('d/m/Y H:i:s')
        ]);
    }

    public function reportePdf()
    {
        $users = User::all();
        $pdf = \PDF::loadView('admin.users.reporte_pdf', compact('users'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('usuarios_' . now()->format('Y-m-d_H-i-s') . '.pdf');
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
    public function unlockRecovery($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'recovery_failed_attempts' => 0,
            'recovery_locked_until'    => null,
        ]);

        return response()->json([
            'message' => 'Recuperación desbloqueada correctamente.',
        ]);
    }

    /**
     * Reset de contraseña por admin: asigna una contraseña temporal y
     * marca al usuario para que la cambie en su próximo login.
     */
    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:191'],
        ]);

        $user = User::findOrFail($id);

        if (auth()->id() === $user->id) {
            return response()->json([
                'message' => 'No puedes resetear tu propia contraseña desde este panel.',
            ], 422);
        }

        $user->forceFill([
            'password'                      => Hash::make($request->password),
            'remember_token'                => null,
            'password_reset_by_admin'       => true,
            'recovery_must_reset_questions' => true,
            'recovery_failed_attempts'      => 0,
            'recovery_locked_until'         => null,
        ])->save();

        return response()->json([
            'message' => 'Contraseña reseteada. El usuario deberá cambiarla en su próximo inicio de sesión.',
        ]);
    }
}

