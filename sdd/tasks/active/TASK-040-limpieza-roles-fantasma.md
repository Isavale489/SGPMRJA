# TASK-040: Limpieza de código muerto (roles fantasma Secretaria/Administrativa)

**Feature**: FEAT-005 — seguridad-roles-permisos
**Spec**: `sdd/specs/seguridad-roles-permisos.spec.md`
**Status**: pending
**Priority**: medium
**Esfuerzo estimado**: S (< 2h)
**Depends-on**: TASK-036
**Assigned-to**: unassigned

---

## Contexto

Módulo 6 (limpieza) del spec (§3, §6 "Código MUERTO detectado"). Existen 3 referencias a
roles que **nunca existieron** en el ENUM (`Administrativa`, `Secretaria`). Con el ENUM
vivo simplemente nunca matcheaban (queries muertas); tras migrar a `role_id` quedarían
como código muerto que confunde o, peor, comparaciones contra un `role` que ya no es
columna. Esta task las elimina/neutraliza.

Implementa §3 Módulo 6.

---

## Scope

- `app/Http/Middleware/CheckAdminRole.php:20`: compara `Auth::user()->role === 'Administrativa'` (rol inexistente). El middleware tiene alias `admin` en `Kernel:67`. **Verificar primero si está en uso** (`grep "'admin'"` / `->middleware('admin')` en rutas). Si NO se usa en ninguna ruta → eliminar el middleware y su alias del Kernel. Si se usa → corregir a `isAdmin()` real. (Hoy `web.php` usa `role:Administrador`, no `admin`, así que probablemente es eliminable.)
- `app/Traits/NotificaSecretarias.php:17`: `User::where('role','Secretaria')` (rol inexistente). Verificar si el trait se usa (`grep NotificaSecretarias`). Si es código muerto → eliminar el trait. Si se usa → neutralizar la query (devuelve vacío hoy; documentar/ajustar).
- `app/Jobs/EnviarNotificacionSolicitud.php:44`: `User::where('role','Secretaria')` (rol inexistente). Verificar uso (`grep EnviarNotificacionSolicitud`). Mismo criterio: eliminar si muerto, neutralizar si vivo.

**NO está en alcance**:
- La capa de compatibilidad de User (TASK-036).
- Cualquier otra migración de `where('role')` legítima (esas van en TASK-036).
- `SolicitudCredito` / `NuevaSolicitudCredito` (mail) salvo que queden huérfanos al borrar — en ese caso, solo notificar, no borrar por cuenta propia.

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `app/Http/Middleware/CheckAdminRole.php` | DELETE / MODIFY | rol `Administrativa` inexistente |
| `app/Http/Kernel.php` | MODIFY | quitar alias `'admin'` si se borra el middleware |
| `app/Traits/NotificaSecretarias.php` | DELETE / MODIFY | rol `Secretaria` inexistente |
| `app/Jobs/EnviarNotificacionSolicitud.php` | DELETE / MODIFY | rol `Secretaria` inexistente |

---

## Codebase Contract (Anti-Alucinación)

### Código muerto verificado (2026-06-15)
```php
// app/Http/Middleware/CheckAdminRole.php
:20  if (Auth::check() && Auth::user()->role === 'Administrativa') { return $next($request); }
// alias en app/Http/Kernel.php:67  'admin' => \App\Http\Middleware\CheckAdminRole::class,

// app/Traits/NotificaSecretarias.php
:6   use App\Jobs\EnviarNotificacionSolicitud;   // import presente
:17  $emailsSecretarias = User::where('role','Secretaria')->pluck('email')->toArray();
//   usa App\Mail\NuevaSolicitudCredito, register_shutdown_function

// app/Jobs/EnviarNotificacionSolicitud.php
:10  use App\Models\SolicitudCredito;
:44  $emailsSecretarias = User::where('role','Secretaria')->pluck('email')->toArray();
//   implements ShouldQueue
```

### Verificaciones obligatorias ANTES de borrar
```
grep -rn "->middleware('admin')" routes/        # ¿se usa el alias admin?
grep -rn "NotificaSecretarias" app/ routes/      # ¿se usa el trait?
grep -rn "EnviarNotificacionSolicitud" app/ routes/   # ¿se despacha el job?
grep -rn "SolicitudCredito" app/                 # ¿existe el flujo de solicitudes de crédito?
```

### Convenciones
- §6 spec: estas 3 referencias son las únicas a roles fantasma; objetivo "cero referencias rotas a `user.role`".

### NO existe — no referenciar
- ~~roles `Administrativa` / `Secretaria`~~ — nunca estuvieron en el ENUM; no recrearlos

---

## Notas de implementación

### Criterio borrar vs neutralizar
- **Borrar** (preferido) si el grep confirma que NADA usa el archivo/alias → es código muerto puro.
- **Neutralizar** solo si hay un consumidor vivo que rompería al borrar: en ese caso, ajustar la query para que no referencie un rol inexistente y dejar comentario.
- Si al borrar quedan huérfanos `SolicitudCredito`/`NuevaSolicitudCredito` sin otro uso, **NO** los borres en esta task; anótalo como hallazgo para una task de limpieza aparte.

### Restricciones clave
- No tocar lógica viva. Esta es limpieza pura, no debe cambiar comportamiento observable.

---

## Criterios de aceptación

- [ ] `grep -rn "'Administrativa'\|'Secretaria'" app/` queda limpio
- [ ] Si se borró `CheckAdminRole`, el alias `admin` ya no está en Kernel y ninguna ruta lo referencia
- [ ] `php artisan route:list` corre sin error (no hay alias roto)
- [ ] `php artisan config:clear && php artisan optimize:clear` sin errores de clase faltante
- [ ] Sin cambios de comportamiento en flujos vivos

---

## QA manual

1. Correr los greps del Contract y registrar qué está vivo/muerto.
2. Aplicar borrado/neutralización según criterio.
3. `php artisan route:list` y `composer dump-autoload` sin errores.
4. Smoke test: login admin, navegar dashboard/usuarios → sin 500.

---

## Instrucciones para el ejecutor

1. Lee el spec (§3 Módulo 6, §6 código muerto).
2. Confirma TASK-036 en `completed/`.
3. Corre las verificaciones del Contract ANTES de decidir borrar/neutralizar.
4. `Status: in-progress`, `Assigned-to: <tu-nombre>`.
5. Rama: `git checkout -b feat/TASK-040-limpieza-roles-fantasma`.
6. Implementa, verifica, mueve a `completed/`, rellena Nota.
7. PR.

---

## Nota de Completitud

**Completado por**:
**Fecha**:
**Commits**:
**Notas**:
**Desviaciones del spec**:
