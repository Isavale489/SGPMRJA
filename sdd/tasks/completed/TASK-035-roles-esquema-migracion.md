# TASK-035: Esquema y migración de roles (tabla `rol`, `permiso_rol`, ENUM → `role_id`)

**Feature**: FEAT-005 — seguridad-roles-permisos
**Spec**: `sdd/specs/seguridad-roles-permisos.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: L (4-8h)
**Depends-on**: none
**Assigned-to**: claude (Santiago)

---

## Contexto

Base de toda la feature (Módulo 1 del spec, §3). Hoy `user.role` es un ENUM de 2
valores (`Administrador`, `Supervisor`) definido por la migración
`2025_12_04_134221_update_user_role_enum`. Esta task crea las tablas `rol` y
`permiso_rol`, migra el ENUM a una FK `role_id` con backfill, y siembra los 2
roles de sistema + el seed de paridad de permisos del Supervisor (que debe nacer
con acceso equivalente al que tiene hoy vía el grupo `role:Administrador,Supervisor`).

Implementa §2 "Modelos de datos" y §3 Módulo 1.

---

## Scope

- Crear migración `create_rol_table`: `id`, `nombre` UNIQUE, `descripcion` nullable, `es_sistema` boolean default false, `softDeletes()`, `timestamps()`.
- Crear migración `create_permiso_rol_table`: `id`, `rol_id` FK→`rol`, `permiso` string, `unique(['rol_id','permiso'])`, `timestamps()`.
- Crear migración `migrate_user_role_enum_to_role_id` que, EN ORDEN, dentro de una transacción donde aplique:
  1. Siembra filas `Administrador` y `Supervisor` en `rol` con `es_sistema = 1`.
  2. Añade `user.role_id` (FK nullable a `rol`).
  3. Backfill: `role_id` = id del rol cuyo `nombre` = valor actual del ENUM `role`.
  4. Marca `role_id` NOT NULL.
  5. Dropea la columna `role` (ENUM).
  6. Seed de paridad: inserta en `permiso_rol` todos los permisos del Supervisor equivalentes a su acceso actual (los módulos/acciones del grupo `role:Administrador,Supervisor` de `web.php:148`). **Administrador NO recibe filas** (bypass por `Gate::before`).
- Crear modelo `app/Models/Rol.php`: `$table = 'rol'`, `SoftDeletes`, `$fillable = ['nombre','descripcion','es_sistema']`, cast `es_sistema => boolean`, relación `usuarios()` (`hasMany(User::class, 'role_id')`) y `permisos()` (`hasMany` a `permiso_rol` o método que devuelva el array de `permiso`).
- `down()` de cada migración debe revertir limpio (recrear ENUM, dropear columnas/tablas).

**NO está en alcance**:
- Accessor `role` ni `hasRole()`/`isAdmin()` en User (TASK-036).
- Registry `config/modulos.php` ni helper `tienePermiso()` (TASK-037) — el seed de paridad lista los permisos **a mano** o leyendo el registry SI ya existe; no depende de él.
- Cualquier vista, ruta o controller.

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `database/migrations/2026_06_15_000001_create_rol_table.php` | CREATE | Tabla `rol` |
| `database/migrations/2026_06_15_000002_create_permiso_rol_table.php` | CREATE | Tabla `permiso_rol` |
| `database/migrations/2026_06_15_000003_migrate_user_role_enum_to_role_id.php` | CREATE | Backfill ENUM→FK + seed paridad + drop ENUM |
| `app/Models/Rol.php` | CREATE | Modelo Eloquent con SoftDeletes |

---

## Codebase Contract (Anti-Alucinación)

### Estado actual verificado (2026-06-15)
```php
// BD: user.role — ENUM('Administrador','Supervisor') NOT NULL
//   definido por migración 2025_12_04_134221_update_user_role_enum
// app/Models/User.php
:15  use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
:17  protected $table = 'user';
:24  $fillable = ['name','persona_id','avatar','role','email','password','estado', ...]
:101 isAdmin()      => $this->role === 'Administrador'
:106 isSupervisor() => $this->role === 'Supervisor'
:113 hasRole($roles) // in_array si array, === si string
```

### Acceso actual del Supervisor (fuente del seed de paridad — `routes/web.php:148-311`)
> El grupo `role:Administrador,Supervisor` da al Supervisor acceso de LECTURA + CRUD compartido a:
> pedidos (lectura), cotizaciones (lectura + conversión/reactivar), proveedores (lectura),
> logos, colores (CRUD), tallas, productos (resource), tipo-productos, atributos + valores,
> insumos (resource), tipo-insumos, órdenes (resource + subórdenes + avance + cancelar),
> ordenes/insumos, compras (index/store/update/data/tasa/pdf/procesar/anular/clonar/destroy),
> movimiento-insumo (todo), notificaciones, reportes (producción/eficiencia/insumos/empleados).
> El seed de paridad del Supervisor debe cubrir EXACTAMENTE estos módulos/acciones.
> NOTA: el grupo `role:Administrador` (`web.php:63-143`) es solo-admin → NO va al Supervisor.

### Convenciones a respetar (ver `docs/conventions/`)
- `docs/conventions/column-naming.md` — `nombre`, `descripcion`, `permiso` (sin prefijos)
- `docs/conventions/softdeletes-unique.md` — `rol.nombre` UNIQUE + softDeletes (cuidado al re-crear nombres borrados)
- Tablas en singular: `rol`, `permiso_rol`. FK a usuarios apunta a tabla `user`.

### NO existe — no referenciar
- ~~tabla `rol` / `permiso_rol`~~ — se crean aquí
- ~~`App\Models\Rol`~~ — se crea aquí
- ~~spatie/laravel-permission~~ — descartado en el spec (§1 No-Goals)
- ~~`Gate::before` / helper `tienePermiso()`~~ — TASK-037, no acoplar el seed a ellos

---

## Notas de implementación

### Orden crítico de la migración del ENUM
El drop de la columna `role` debe ser el ÚLTIMO paso y solo después de que `role_id`
esté backfilleado y NOT NULL. Si el backfill falla (algún `role` que no mapea a un
`rol.nombre`), abortar antes de dropear.

### Seed de paridad
Construir el array de permisos del Supervisor desde la lista del Contract. Si TASK-037
ya creó `config/modulos.php`, se puede derivar del registry para no duplicar; si no,
hardcodear la lista en la migración (es un snapshot histórico, está bien que sea explícita).

### Restricciones clave
- Transacción DB en la migración de datos.
- `es_sistema = 1` solo en Administrador y Supervisor.
- `php artisan migrate:fresh` y `migrate` sobre BD existente deben dejar el mismo estado de roles.

---

## Criterios de aceptación

- [ ] `php artisan migrate` sobre BD existente: usuarios conservan su rol (backfill correcto), `user.role` ya no existe, `user.role_id` NOT NULL
- [ ] `php artisan migrate:fresh` deja `rol` con Administrador + Supervisor (`es_sistema=1`)
- [ ] `permiso_rol` tiene las filas de paridad del Supervisor; Administrador con 0 filas
- [ ] `down()` revierte limpio (recrea ENUM)
- [ ] `App\Models\Rol` instanciable con relaciones `usuarios()`/`permisos()`
- [ ] Sin tocar vistas/controllers/rutas

---

## QA manual

1. Backup de la BD de desarrollo.
2. `php artisan migrate` → `SELECT id,name,role_id FROM user;` todos con `role_id` no nulo y correcto.
3. `SELECT * FROM rol;` → 2 filas sistema.
4. `SELECT permiso FROM permiso_rol pr JOIN rol r ON r.id=pr.rol_id WHERE r.nombre='Supervisor';` → cubre los módulos del Contract.
5. `php artisan migrate:rollback` → ENUM `role` restaurado, tablas dropeadas.
6. `php artisan migrate:fresh` → estado consistente desde cero.

---

## Instrucciones para el ejecutor

1. Lee el spec completo (§2 Modelos de datos, §3 Módulo 1, §7).
2. Verifica el Codebase Contract con `grep`/`read` antes de codificar.
3. `Status: in-progress`, `Assigned-to: <tu-nombre>`.
4. Rama: `git checkout -b feat/TASK-035-roles-esquema` desde `feat/seguridad-roles-permisos`.
5. Implementa dentro del scope.
6. Verifica criterios + QA.
7. Mueve a `sdd/tasks/completed/`.
8. Rellena la Nota de Completitud.
9. PR contra `feat/seguridad-roles-permisos` (o `enmanuel` según acuerden).

---

## Nota de Completitud

**Completado por**: Claude (sesión Santiago)
**Fecha**: 2026-06-15
**Commits**: *(en rama `feat/TASK-035-roles-esquema`)*

**Notas**:
- 3 migraciones (`2026_06_15_000001/2/3`) + modelos `App\Models\Rol` y `App\Models\PermisoRol`.
- `rol` (nombre UNIQUE, descripcion, es_sistema, softDeletes) y `permiso_rol` (rol_id FK, permiso, unique[rol_id,permiso]).
- Migración 3: siembra Administrador/Supervisor (`es_sistema=1`, idempotente vía `updateOrInsert`), añade `role_id` FK nullable, backfill por nombre, **salvaguarda que aborta si quedan huérfanos** antes de dropear, `role_id` NOT NULL, drop del ENUM `role`, y seed de paridad del Supervisor.
- **Seed de paridad: 34 permisos** derivados del grupo `role:Administrador,Supervisor` de `web.php`. Definidos como constante `PERMISOS_SUPERVISOR` y documentados como **CONTRATO DE VOCABULARIO** que `config/modulos.php` (TASK-037) debe reflejar. Administrador con 0 filas (bypass por Gate::before).
- QA verificado: migrate sobre BD existente (5 usuarios backfilleados: 4 Admin + 1 Supervisor), rollback completo (ENUM restaurado como tipo `enum` real + tablas dropeadas), re-migrate, y modelo `Rol` instanciable con `usuarios()`/`permisos()`/`permisosArray()`.

**Desviaciones del spec**:
- El spec citaba la migración `2025_12_04_134221_update_user_role_enum` como origen del ENUM; **no existe ese archivo** (el ENUM ya venía en el dump/esquema base). No afecta el resultado: la migración detecta y migra el ENUM real (`enum('Administrador','Supervisor') NOT NULL`).
- `down()` recrea el ENUM con SQL crudo (`ALTER TABLE ... MODIFY ... ENUM(...)`) porque el schema builder de Laravel degrada `enum→varchar` en MariaDB al usar `change()`. Resultado idéntico en MySQL 8 y MariaDB.
- Se añadió un modelo `PermisoRol` (no exigido explícitamente) para soportar la relación `Rol::permisos()`; el spec dejaba abierta la forma ("hasMany o método que devuelva el array").
