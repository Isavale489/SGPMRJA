# TASK-037: Registry `config/modulos.php` + autorización en runtime (helper, middleware, Gate)

**Feature**: FEAT-005 — seguridad-roles-permisos
**Spec**: `sdd/specs/seguridad-roles-permisos.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: L (4-8h)
**Depends-on**: TASK-035
**Assigned-to**: claude

---

## Contexto

Módulo 3 del spec (§3). Es el corazón de la autorización: el catálogo de módulos/
acciones/rutas en código (misma filosofía que FEAT-004 con `config/parametros.php`),
el helper `tienePermiso()` con caché por rol, el middleware `permiso` que resuelve el
permiso requerido **desde el nombre de la ruta actual**, y `Gate::before` que da bypass
total al Administrador (anti-lockout estructural).

Implementa §2 puntos 3, 4, 5 y §3 Módulo 3.

---

## Scope

- Crear `config/modulos.php`: registry con cada módulo → `nombre`, `acciones` (clave→descripción) y `rutas` (patrón `nombre.de.ruta|otro` → acción). Incluir TODOS los módulos de `web.php` (ver Contract para la lista completa). Más una clave `comunes` con las rutas transversales accesibles a todo autenticado (dashboard, perfil, logout, `personas.search`, `empleados.get-cargos`, `notificaciones.sistema`, `clientes.search`, `proveedores.search`, etc.).
- Crear helper `tienePermiso(string $permiso): bool` en `app/Support/helpers.php` (el archivo ya existe — FEAT-004):
  - `Gate::before`/`isAdmin()` ⇒ true.
  - Lee permisos del rol del usuario desde caché `permisos.rol_{id}` (array de strings); si miss, query a `permiso_rol` y cachea.
  - Sin sesión ⇒ false.
- Crear middleware `app/Http/Middleware/CheckPermiso.php` (alias `permiso`):
  - Resuelve el `nombre` de la ruta actual (`$request->route()->getName()`).
  - Busca en el registry qué permiso exige ese nombre de ruta (match contra los patrones `rutas`); si la ruta está en `comunes` ⇒ permite.
  - Llama `tienePermiso($permisoRequerido)`; si no ⇒ `abort(403)`.
  - **Denegar por defecto**: ruta autenticada SIN mapeo en el registry ⇒ 403 + `Log::warning` (para detectar huecos en QA).
- Registrar alias `'permiso'` en `app/Http/Kernel.php:55` (`$middlewareAliases`).
- En `app/Providers/AuthServiceProvider.php`: añadir `Gate::before(fn($user) => $user->isAdmin() ? true : null)` en `boot()` (devolver `null`, nunca `false`).
- Flush de caché: exponer la key/forma para que TASK-039 (`guardarMatriz`) y el CRUD de roles hagan `Cache::forget("permisos.rol_{id}")`. Documentar la convención en el helper.

**NO está en alcance**:
- Aplicar el middleware a `web.php` ni fundir los grupos `role:` (TASK-038).
- La página de seguridad / `guardarMatriz` (TASK-039) — solo se define la convención de caché.
- Gates de UI en vistas (TASK-038).

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `config/modulos.php` | CREATE | Registry módulos/acciones/rutas + `comunes` |
| `app/Support/helpers.php` | MODIFY | helper `tienePermiso()` con caché por rol |
| `app/Http/Middleware/CheckPermiso.php` | CREATE | resuelve permiso por nombre de ruta; deny-by-default + log |
| `app/Http/Kernel.php` | MODIFY | alias `'permiso'` en `$middlewareAliases` |
| `app/Providers/AuthServiceProvider.php` | MODIFY | `Gate::before` bypass admin |

---

## Codebase Contract (Anti-Alucinación)

### app/Http/Kernel.php (verificado 2026-06-15)
```php
:55 protected $middlewareAliases = [
:60   'can' => \Illuminate\Auth\Middleware\Authorize::class,
:67   'admin' => \App\Http\Middleware\CheckAdminRole::class,
:68   'role' => \App\Http\Middleware\CheckRole::class,
:70   'active.user' => \App\Http\Middleware\EnsureUserIsActive::class,
];  // añadir 'permiso' => \App\Http\Middleware\CheckPermiso::class
```

### Patrón de middleware existente (app/Http/Middleware/CheckRole.php)
```php
public function handle(Request $request, Closure $next, ...$roles): Response {
    if (!$request->user()) { return redirect()->route('login'); }
    if ($request->user()->hasRole($roles)) { return $next($request); }
    abort(403, 'No tiene permiso para acceder a esta sección.');
}
```

### app/Support/helpers.php (existe — creado por FEAT-004)
```php
// contiene helper parametro('clave') de FEAT-004; añadir tienePermiso() aquí
// patrón de caché de FEAT-004 como referencia
```

### Nombres de ruta a mapear (de routes/web.php, verificado 2026-06-15)
```
// Solo-admin (web.php:63-143): configuracion.*, users.*, clientes.*, personas.search,
//   empleados.*, departamentos.*, cargos.*, pedidos.{store,create,update,cancelar,reactivar,destroy,edit},
//   cotizaciones.{store,create,update,destroy,edit}, proveedores.{store,from-persona,create,update,destroy,edit,restore}
// Admin+Supervisor (web.php:148-311): pedidos.{index,data,cotizacionesDisponibles,reporte.pdf,reporteGeneral,show,pdf},
//   cotizaciones.{index,data,reporte.pdf,reporteGeneral,show,pdf,updateEstado,datosParaPedido,convertirAPedido,reactivar,ubicacionesBordado.data},
//   proveedores.{index,data,check-*,reporte.pdf,search,show}, logos.data, colores.*, tallas.data,
//   productos.* (+resolver-variante,sugerir-precio,preview-codigo), tipo-productos.*, atributos.* + valores.*,
//   insumos.* , tipo-insumos.*, ordenes.* (+subordenes,avance,cancelar,batch,por-empleado,pedidos-disponibles),
//   ordenes.insumos.*, compras.{index,store,update,data,tasa,reporte.pdf,editar-datos,detalle,pdf,procesar,anular,clonar,destroy},
//   movimiento-insumo.* , notificaciones.sistema, reportes.{produccion,eficiencia,insumos,empleados}
```
> El spec §2 da el ejemplo canónico del módulo `compras` con sus 6 acciones
> (ver/gestionar/procesar/anular/clonar/pdf) y su mapeo de rutas. Granularidad:
> fina en transaccionales, gruesa (ver/gestionar) en maestros (§8 resuelto).
> Reportes = módulo único con permiso `reportes.ver` (§8 resuelto).

### De TASK-035 (dependencia)
```php
// tabla permiso_rol(rol_id, permiso UNIQUE por rol)
// app/Models/Rol.php con relación permisos()
```

### Convenciones / patrones
- `docs/conventions/system-config.md` (FEAT-004) — filosofía registry-en-código + caché
- §7 del spec: deny-by-default, caché POR ROL (no por usuario), `Gate::before` nunca devuelve false

### NO existe — no referenciar
- ~~middleware alias `permiso`~~ — se registra aquí
- ~~`Gate::define`/`Gate::before` en AuthServiceProvider~~ — hoy no hay; se introduce aquí
- ~~`config/modulos.php`~~ — se crea aquí (distinto de `config/parametros.php` de FEAT-004)

---

## Notas de implementación

### Forma de una entrada del registry (del spec §2)
```php
'compras' => [
    'nombre'   => 'Compras',
    'acciones' => ['ver'=>'...','gestionar'=>'...','procesar'=>'...','anular'=>'...','clonar'=>'...','pdf'=>'...'],
    'rutas' => [
        'compras.index|compras.data|compras.show|compras.detalle' => 'ver',
        'compras.store|compras.update|compras.editar-datos'       => 'gestionar',
        'compras.procesar' => 'procesar',
        'compras.anular'   => 'anular',
        'compras.clonar'   => 'clonar',
        'compras.pdf|compras.reporte.pdf' => 'pdf',
    ],
],
```

### Caché por rol
Key `permisos.rol_{id}` = array de strings `'modulo.accion'`. Flush en cada escritura
de la matriz/CRUD de rol (TASK-039 hace el forget; aquí solo se define la convención).
NO cachear por usuario (§7).

### Restricciones clave
- `Gate::before` devuelve `true` solo para admin; en otro caso `null` (deja seguir la cadena).
- Deny-by-default + log para rutas sin mapeo.

---

## Criterios de aceptación

- [ ] `config/modulos.php` cubre TODOS los nombres de ruta autenticados de `web.php`
- [ ] `tienePermiso('compras.procesar')` devuelve true para admin sin consultar `permiso_rol`
- [ ] Middleware `permiso` resuelve el permiso por nombre de ruta y deniega por defecto (+log) si no hay mapeo
- [ ] Alias `permiso` registrado en Kernel
- [ ] `Gate::before` admin bypass activo; nunca devuelve false
- [ ] Caché por rol con convención de flush documentada
- [ ] Agregar un módulo nuevo al registry no requiere tocar código del middleware

---

## QA manual

1. `php artisan tinker` → `tienePermiso('compras.ver')` con admin logueado ⇒ true.
2. Aplicar el middleware a una ruta de prueba (temporal) y entrar como rol sin permiso ⇒ 403.
3. Ruta sin mapeo en el registry ⇒ 403 + entrada en `storage/logs/laravel.log`.
4. Cambiar `permiso_rol` y hacer `Cache::forget('permisos.rol_{id}')` ⇒ el siguiente request refleja el cambio.

---

## Instrucciones para el ejecutor

1. Lee el spec (§2 puntos 3-5, §3 Módulo 3, §7).
2. Confirma TASK-035 en `completed/`.
3. Verifica el Codebase Contract con `grep`/`read`.
4. `Status: in-progress`, `Assigned-to: <tu-nombre>`.
5. Rama: `git checkout -b feat/TASK-037-registry-runtime`.
6. Implementa, verifica, mueve a `completed/`, rellena Nota.
7. PR.

---

## Nota de Completitud

**Completado por**: claude (Opus 4.8)
**Fecha**: 2026-06-15

**Commits**: ver rama `feat/TASK-037-registry-runtime` (1 commit).

**Archivos**:
- CREATE `config/modulos.php` — registry de 21 módulos + clave `comunes`. Cada módulo con `nombre`, `acciones` (clave→descripción) y `rutas` (patrón `a|b|c` → acción). Cubre los 230 nombres de ruta autenticados de `web.php` (0 sin mapear, excluyendo el flujo de auth.php que no pasa por el grupo `auth` de web.php:48).
- MODIFY `app/Support/helpers.php` — añadidos `tienePermiso(string): bool` y el helper interno `esUsuarioAdministrador($user): bool`, sin tocar el `parametro()` de FEAT-004 (ambos guardados con `function_exists`). Caché por rol documentada en el docblock.
- CREATE `app/Http/Middleware/CheckPermiso.php` — alias `permiso`. Resuelve el permiso por nombre de ruta contra el registry; `comunes` ⇒ pasa; sin mapeo ⇒ `abort(403)` + `Log::warning` (deny-by-default).
- MODIFY `app/Http/Kernel.php` — alias `'permiso' => CheckPermiso::class` junto a `'role'`.
- MODIFY `app/Providers/AuthServiceProvider.php` — `Gate::before` con bypass del admin (devuelve `true`/`null`, nunca `false`).
- `composer.json` NO se tocó: el bloque `autoload.files` con `app/Support/helpers.php` ya existía (FEAT-004).

**Verificaciones** (`php artisan tinker`):
- Admin: `tienePermiso('compras.procesar')` ⇒ true y `tienePermiso('users.gestionar')` ⇒ true SIN fila en `permiso_rol` (bypass). `Gate::allows(<ability inexistente>)` ⇒ true.
- Supervisor (rol id 2, 34 filas en `permiso_rol`): `compras.ver` ⇒ true, `cotizaciones.convertir` ⇒ true; solo-admin (`users.gestionar`, `pedidos.gestionar`) ⇒ false; `Gate::allows(<inexistente>)` ⇒ false.
- Invitado ⇒ false.
- Paridad: las 34 claves de `PERMISOS_SUPERVISOR` están definidas en el registry y cada una tiene ≥1 ruta mapeada (0 discrepancias).
- Middleware: SUP `compras.index` PASA, `users.store` 403, `dashboard` PASA, ruta sin mapeo 403 + log; ADMIN ruta mapeada PASA, ruta sin mapeo 403 (deny-by-default aplica antes del bypass, a propósito — fuerza a cerrar huecos en QA).
- Caché: key `permisos.rol_{id}` se crea al primer read, `Cache::forget` la borra, el siguiente read la re-cachea.
- `php artisan route:list` corre limpio (255 rutas); alias `permiso` resuelto en el router.

**Notas / Desviaciones del spec**:
- **Helper extra `esUsuarioAdministrador($user)`**: el spec dice que `tienePermiso`/`Gate::before` se apoyan en `User::isAdmin()`. En la base actual la capa de compatibilidad de `User` (Módulo 2 / `rol()`+accessor `role`) NO está desplegada todavía, por lo que `User::isAdmin()` (que compara `$this->role`, columna ya dropeada) devuelve false para todos. Para que el bypass del Administrador funcione HOY y siga funcionando cuando Módulo 2 aterrice, el bypass se resuelve de forma robusta: primero intenta `User::isAdmin()` y, si no resuelve, cae a comparar el nombre del rol relacionado por `role_id` contra `'Administrador'` (cacheado en `rol_nombre_{id}`). Cuando Módulo 2 arregle `isAdmin()`, este helper lo respeta sin cambios.
- **`*.check-*` (validadores AJAX de unicidad) NO van en `comunes`**: el task los listaba como candidatos pero dejaba el criterio abierto. Se gobiernan con el permiso `gestionar` de su propio módulo (revelarían existencia de email/documento/código a usuarios sin acceso al módulo). Documentado en `config/modulos.php`.
- **Acciones solo-admin añadidas a módulos operativos**: `pedidos.gestionar`, `cotizaciones.gestionar`, `proveedores.gestionar` mapean la escritura que hoy vive en el grupo `role:Administrador`. No están en `PERMISOS_SUPERVISOR` (el Supervisor no las recibe), así que la paridad se mantiene; el admin entra por `Gate::before`.
- **Nota de entorno (no afecta el entregable)**: el worktree se había creado desde una base (`b225dbf`) que NO contenía TASK-035 ni FEAT-004; se rebasó la rama a `3f98bfc` (TASK-035, que sí incluye FEAT-004 y los modelos `Rol`/`PermisoRol`) antes de implementar. `routes/web.php` NO se modificó (es alcance de TASK-038).
