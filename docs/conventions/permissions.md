# Permisos, roles y autorización (registry + matriz)

> Cómo funciona la seguridad por roles y permisos del sistema (FEAT-005) y cómo
> agregar un módulo o acción nueva. Patrón gemelo de [`system-config.md`](system-config.md):
> **definición en código, valor en BD**.

## Arquitectura

| Pieza | Dónde | Qué guarda |
|---|---|---|
| Registry | `config/modulos.php` | DEFINICIÓN: módulos, sus acciones y el mapeo `nombre-de-ruta → acción`. Más la lista `comunes` (rutas abiertas a todo autenticado) |
| Roles | tabla `rol` (`nombre` UNIQUE, `descripcion`, `es_sistema`, softDeletes) | Roles administrables. `Administrador` y `Supervisor` se siembran con `es_sistema=1` |
| Permisos | tabla `permiso_rol` (`rol_id`, `permiso`, UNIQUE) | Filas otorgadas (`'compras.procesar'`...). Sin fila = denegado. El Administrador NO consulta esta tabla |
| Usuario→rol | `user.role_id` (FK a `rol`) | 1 rol por usuario. Accessor `$user->role` ⇒ `rol->nombre` (compatibilidad de lecturas) |
| Helper | `tienePermiso('modulo.accion')` en `app/Support/helpers.php` | ¿El usuario actual tiene el permiso? Admin ⇒ siempre `true` |
| Middleware | `permiso` (`app/Http/Middleware/CheckPermiso.php`) | Resuelve el permiso requerido DESDE el nombre de la ruta vía el registry. Deny-by-default |
| Bypass | `Gate::before` en `AuthServiceProvider` | `isAdmin()` ⇒ `true` (anti-lockout); cualquier otro ⇒ `null` (sigue la cadena) |
| Panel | `/configuracion/seguridad` (solo Administrador) | tab Roles (CRUD) + tab Permisos (matriz módulo × acción) |

```
Petición a una ruta autenticada
   └─ middleware 'permiso'  (aplicado a TODO el grupo auth de web.php)
        ├─ ruta en 'comunes'      ─► pasa
        ├─ ruta mapeada al registry ─► exige tienePermiso('modulo.accion')
        │        └─ Cache "permisos.rol_{id}" ─► tabla permiso_rol
        │        └─ Gate::before: isAdmin() ⇒ true (bypass, no toca BD)
        └─ ruta SIN mapeo         ─► 403 + Log::warning (denegar > permitir)
```

## Reglas de oro

1. **Autoriza SIEMPRE con `tienePermiso('modulo.accion')`** en vistas y código — nunca
   con `isAdmin()`/`hasRole()` quemados para gatear módulos. (`isAdmin()` sigue válido
   solo para lo que es genuinamente solo-admin, como el panel de configuración.)
2. **El registry es la única fuente del mapeo ruta→permiso.** El middleware `permiso`
   está aplicado al grupo `auth` completo en `routes/web.php`; las rutas NO llevan
   `role:`/`can:` individuales. Para cambiar quién entra a dónde, se toca el registry
   o la matriz — no `web.php`.
3. **Deny-by-default.** Una ruta autenticada que no esté en `comunes` ni mapeada por
   ningún módulo ⇒ 403 + `Log::warning`. Mejor descubrir un hueco en QA que dejar una
   puerta abierta. Al añadir rutas nuevas, mapéalas en el registry.
4. **Una key de caché por ROL** (`permisos.rol_{id}`), nunca por usuario. Toda escritura
   de la matriz / edición / borrado de un rol hace `Cache::forget("permisos.rol_{id}")`
   (lo hace `SeguridadController`). Si tocas `permiso_rol` por otra vía (seeder, tinker),
   flushea tú mismo.
5. **`ver` es prerrequisito del módulo.** No se puede otorgar `gestionar`/`procesar`/etc.
   sin `ver`. La matriz lo fuerza en UI y `guardarMatriz` lo re-asegura en servidor.
6. **El Administrador es intocable.** No aparece editable en la matriz (acceso total por
   `Gate::before`); `Administrador` y `Supervisor` (`es_sistema=1`) no se renombran ni
   eliminan. Sumado a la protección "último admin activo" (UserController), es imposible
   quedarse sin acceso.

## Cómo agregar un módulo o acción nuevo

1. Añade/edita la entrada en `config/modulos.php`:

```php
'compras' => [
    'nombre'   => 'Compras',
    'acciones' => [
        'ver'       => 'Ver listado y detalle',
        'gestionar' => 'Crear y editar borradores',
        'procesar'  => 'Procesar (afecta inventario)',
        'anular'    => 'Anular',
    ],
    // mapeo nombre-de-ruta → acción (patrón pipe). Lo consume el middleware 'permiso'.
    'rutas' => [
        'compras.index|compras.data|compras.show' => 'ver',
        'compras.store|compras.update'            => 'gestionar',
        'compras.procesar'                        => 'procesar',
        'compras.anular'                          => 'anular',
    ],
],
```

2. **Listo.** La matriz del panel de seguridad lo renderiza solo (una columna por acción)
   y el middleware ya protege las rutas. No se toca ni vista ni `SeguridadController`.
3. En las vistas, condiciona la UI con `@if(tienePermiso('compras.procesar'))`.
4. Si la ruta debe ser accesible a TODO usuario autenticado (autocompletes, dashboard),
   agrégala a la lista `comunes` en vez de a un módulo.

### Granularidad (convención del proyecto)
- **Maestros**: acciones gruesas — `ver` / `gestionar` (`gestionar` = crear+editar+eliminar).
- **Transaccionales**: acciones finas — `procesar` / `anular` / `clonar` / `avance` / `cancelar` / `convertir` / `pdf`.
- **PDF de maestros compartidos**: va bajo `ver` (quien ve el listado puede exportarlo).
  Los maestros solo-admin (users/clientes/empleados) sí tienen `pdf` como acción aparte.

## El panel de seguridad NO se gobierna por la matriz

`/configuracion/seguridad` y sus endpoints viven en un grupo de rutas **aparte**, SIN el
middleware `permiso`, protegido por el gate **`acceso-seguridad`** (definido en
`AuthServiceProvider`, devuelve `true` solo para admin) vía `can:acceso-seguridad`.

El módulo "seguridad" **no existe** en `config/modulos.php` a propósito: si estuviera,
aparecería en la matriz y un admin podría otorgar el panel a otro rol → **escalada de
privilegios**. Manteniéndolo fuera del registry, el acceso al panel es estructuralmente
solo del Administrador y no es delegable.

## Gotchas

- **`permiso` corre ANTES del bypass admin.** Si una ruta autenticada no está mapeada,
  el middleware aborta 403 *incluso para el admin* (el deny-by-default precede a
  `tienePermiso`). Por eso las rutas que deben evadir la matriz (panel de seguridad) van
  en su propio grupo sin `permiso`, no "confiando" en el bypass.
- **Caché por rol, no por usuario.** Cambiar la matriz de un rol afecta a TODOS sus
  usuarios en su siguiente request, sin re-login. No agregues una caché por usuario.
- **El accessor `role` es null-safe** (`$this->rol?->nombre`): un usuario con rol
  soft-deleted no debe tirar 500. No "simplifiques" quitando el `?`.
- **`rol.nombre` es UNIQUE + softDeletes**: re-crear un rol con el nombre de uno borrado
  puede chocar. La validación usa `Rule::unique('rol','nombre')->whereNull('deleted_at')`.
- **El helper nunca tira 500 por sí solo**: si las tablas no existen o la BD está caída,
  deniega (salvo admin). No le agregues lecturas a BD fuera de ese guard.
- **`tienePermiso` es global** (`app/Support/helpers.php`, bloque `files` de
  `composer.json`). Si "no existe", corre `composer dump-autoload`.

## Qué NO es esto

- **No** son permisos por registro/fila ("este vendedor solo ve sus cotizaciones") ni por
  campo — solo módulo + acción.
- **No** es multi-rol por usuario — se mantiene 1 rol por usuario.
- **No** reemplaza los parámetros configurables (eso es FEAT-004 / [`system-config.md`](system-config.md)).
