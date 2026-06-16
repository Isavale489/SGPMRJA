# TASK-036: Capa de compatibilidad en User + migración de consumidores SQL del rol

**Feature**: FEAT-005 — seguridad-roles-permisos
**Spec**: `sdd/specs/seguridad-roles-permisos.spec.md`
**Status**: pending
**Priority**: high
**Esfuerzo estimado**: M (2-4h)
**Depends-on**: TASK-035
**Assigned-to**: unassigned

---

## Contexto

Módulo 2 del spec (§3). Tras dropear el ENUM `user.role` (TASK-035), las ~20 lecturas
existentes de `$user->role` y las 4 queries SQL `where('role', ...)` se rompen. Esta
task introduce la **capa de compatibilidad**: relación `rol()` + accessor `role` que
devuelve `rol->nombre`, y reimplementa `hasRole()`/`isAdmin()`/`isSupervisor()` sobre la
relación **con la firma intacta** → las lecturas en vistas/controllers siguen funcionando
sin tocarse. Las 4 queries SQL que sí rompen se migran a `role_id`/`whereHas`.

Implementa §2 punto 2 y §3 Módulo 2.

---

## Scope

- En `app/Models/User.php`:
  - Quitar `'role'` de `$fillable`, añadir `'role_id'`.
  - Añadir relación `rol()` → `belongsTo(Rol::class, 'role_id')`.
  - Añadir accessor `getRoleAttribute()` → `$this->rol?->nombre` (null-safe: rol soft-deleted no debe tirar 500).
  - Reimplementar `isAdmin()`, `isSupervisor()`, `hasRole($roles)` sobre el accessor/relación, **misma firma exacta**.
- Migrar las 4 queries SQL en `app/Http/Controllers/UserController.php`:
  - `:24` `->where('role', $request->input('filter_role'))` → filtrar por `role_id` (o `whereHas('rol', fn($q)=>$q->where('nombre', ...))`).
  - `:46` `->orWhere('role', 'like', "{$keyword}%")` → `orWhereHas('rol', fn($q)=>$q->where('nombre','like',"{$keyword}%"))`.
  - `:146` `User::where('role','Administrador')->where('estado',1)->count()` → equivalente por `role_id` (conservar protección "último admin activo").
  - `:195` `->where('role', $request->role)` (filtro PDF) → equivalente por rol.
- Migrar escrituras en UserController: `:80` y `:113` `$user->role = $request->role;` → `$user->role_id = $request->role_id;` (el form ahora envía `role_id`).
- En `StoreUserRequest.php:21` y `UpdateUserRequest.php:22`: `'role' => 'required|in:Administrador,Supervisor'` → `'role_id' => 'required|exists:rol,id'` (y actualizar mensajes).
- En vistas de `resources/views/admin/users/` (modal de crear/editar + filtros de la tabla y del PDF): poblar el `<select>` de rol desde la tabla `rol` (todos los roles, no solo 2). El controller `index`/`reportePdf` debe pasar los roles disponibles.

**NO está en alcance**:
- Lecturas que ya funcionan vía accessor (NO tocarlas): `UserController:182`, `header.blade.php:310`, `profile/edit.blade.php:534`, `users/reporte_pdf.blade.php:59`.
- Gates de UI `isAdmin()`/`hasRole()` quemados en sidebar/pedidos/cotizaciones (TASK-038).
- Registry/middleware/Gate (TASK-037).
- Código muerto de roles fantasma (TASK-040).

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `app/Models/User.php` | MODIFY | relación `rol()`, accessor `role`, `isAdmin/isSupervisor/hasRole` sobre relación; `$fillable` |
| `app/Http/Controllers/UserController.php` | MODIFY | 4 queries SQL + 2 escrituras → `role_id`; pasar roles a las vistas |
| `app/Http/Requests/StoreUserRequest.php` | MODIFY | `role_id` exists:rol,id |
| `app/Http/Requests/UpdateUserRequest.php` | MODIFY | `role_id` exists:rol,id |
| `resources/views/admin/users/*` | MODIFY | select de rol desde tabla `rol` (modal + filtro tabla + filtro PDF) |

---

## Codebase Contract (Anti-Alucinación)

### app/Models/User.php (verificado 2026-06-15)
```php
:15  use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
:17  protected $table = 'user';
:24  $fillable = ['name','persona_id','avatar','role','email','password','estado','recovery_locked_until','recovery_failed_attempts','recovery_must_reset_questions','password_reset_by_admin'];
:41  persona()  => belongsTo(Persona::class)
:101 isAdmin()      { return $this->role === 'Administrador'; }
:106 isSupervisor() { return $this->role === 'Supervisor'; }
:113 hasRole($roles){ is_array? in_array($this->role,$roles) : $this->role===$roles; }
// NO hay relación rol() hoy; role es columna string (la dropea TASK-035)
```

### app/Http/Controllers/UserController.php (verificado 2026-06-15)
```php
:5  use App\Models\User;
:21 $users = User::query();
:24 if ($request->filled('filter_role')) { $users->where('role', $request->input('filter_role')); }
:38 ->filter(function ($query) use ($request) { ... }, true)  // búsqueda estricta Yajra
:46 ->orWhere('role', 'like', "{$keyword}%")
:80 $user->role = $request->role;                 // store (escritura → role_id)
:113 $user->role = $request->role;                // update (escritura → role_id)
:145 if ($user->isAdmin() && $user->estado) {
:146   $adminsActivos = User::where('role','Administrador')->where('estado',1)->count();
:147   if ($adminsActivos <= 1) { abort 422 "último administrador activo" }
:182 'role' => $user->role,                        // LECTURA (accessor cubre, NO tocar)
:195 if ($request->filled('role')) { $query->where('role', $request->role); }   // PDF
```

### Requests (verificado 2026-06-15)
```php
// StoreUserRequest.php:21   'role' => 'required|in:Administrador,Supervisor',
// UpdateUserRequest.php:22  'role' => 'required|in:Administrador,Supervisor',
```

### De TASK-035 (dependencia)
```php
// app/Models/Rol.php : $table='rol', SoftDeletes, $fillable=['nombre','descripcion','es_sistema']
// user.role_id : FK NOT NULL a rol.id ; columna user.role YA NO EXISTE
```

### Convenciones a respetar
- `docs/conventions/modal-system.md` — modal de usuario `atlantico-modal` + `#id-field`
- `docs/conventions/js-validations.md`

### NO existe — no referenciar
- ~~`user.role` como columna~~ — dropeada en TASK-035; usar accessor o `role_id`
- ~~middleware `permiso` / `tienePermiso()`~~ — TASK-037

---

## Notas de implementación

### Accessor null-safe (crítico, §7 del spec)
```php
public function getRoleAttribute() { return $this->rol?->nombre; }
```
Un usuario con rol soft-deleted no debe romper las vistas que leen `$user->role`.

### Protección "último admin activo" (UserController:145)
Debe seguir funcionando: contar admins activos por `role_id` del rol Administrador.
No degradar esta salvaguarda.

### Restricciones clave
- Firma de `hasRole`/`isAdmin`/`isSupervisor` IDÉNTICA (no cambiar parámetros ni nombre).
- El select de rol en el modal de usuarios usa `#id-field` y AtlanticoGuard aplica solo.

---

## Criterios de aceptación

- [ ] `$user->role` devuelve el nombre del rol (vía accessor) en todas las vistas existentes
- [ ] `grep "where('role'"` en `app/` queda limpio (solo `role_id`/`whereHas`)
- [ ] Buscar "Admin" en `/users` sigue filtrando (UserController:46 migrado)
- [ ] Filtro por rol en tabla y PDF de usuarios funciona
- [ ] Crear/editar usuario asigna `role_id`; validación `exists:rol,id`
- [ ] Protección último admin activo intacta (no se puede inhabilitar al último)
- [ ] PDF de usuarios muestra el nombre del rol

---

## QA manual

1. Login admin → `/users`: tabla carga, filtro por rol funciona, búsqueda "Admin" filtra.
2. Crear usuario con rol existente → se guarda con `role_id`.
3. Editar usuario, cambiar rol → persiste.
4. Intentar inhabilitar al último admin activo → bloqueado 422.
5. Exportar PDF de usuarios → columna rol muestra el nombre.
6. Usuario con rol soft-deleted (simular) → vistas no tiran 500.

---

## Instrucciones para el ejecutor

1. Lee el spec (§2, §3 Módulo 2, §6 consumidores SQL).
2. Confirma que TASK-035 está en `completed/`.
3. Verifica el Codebase Contract con `grep`/`read`.
4. `Status: in-progress`, `Assigned-to: <tu-nombre>`.
5. Rama: `git checkout -b feat/TASK-036-user-compat`.
6. Implementa, verifica, mueve a `completed/`, rellena Nota.
7. PR.

---

## Nota de Completitud

**Completado por**:
**Fecha**:
**Commits**:
**Notas**:
**Desviaciones del spec**:
