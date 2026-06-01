# TASK-007: Filtros unificados en Users

**Feature**: FEAT-001 — unified-filters-rollout
**Spec**: `sdd/specs/unified-filters-rollout.spec.md`
**Status**: done
**Priority**: medium
**Esfuerzo estimado**: M (2-4h)
**Depends-on**: none
**Assigned-to**: santiago

---

## Contexto

Migrar el listado de Users al patron unificado de busqueda + filtros (spec seccion 3, Modulo 7).

---

## Scope

- Reemplazar el header con `search-box` por la estructura `advanced-filters-wrapper navy-theme` y `navy-filter-header`.
- Agregar filtros: Rol (Administrador/Supervisor/Usuario) y Estado (Activo/Inactivo).
- Cablear JS estandar: `updateFilterBadge()`, debounce 300ms, `clear-filters` y toggle del collapse.
- Enviar filtros en el DataTable y aplicar `where` condicionales en `UserController::getUsers()`.

**NO esta en alcance**:
- Cambiar columnas o estructura del DataTable.
- Introducir CSS nuevo o estilos inline.

---

## Archivos a crear / modificar

| Archivo | Accion | Descripcion |
|---|---|---|
| `resources/views/admin/users/index.blade.php` | MODIFY | Header unificado + filtros + JS de filtros |
| `app/Http/Controllers/UserController.php` | MODIFY | Filtros server-side en `getUsers()` |

---

## Codebase Contract (Anti-Alucinacion)

### Imports verificados
```php
use App\Models\User;                // app/Models/User.php
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
```

### Firmas existentes a usar
```php
// app/Http/Controllers/UserController.php
public function index()
public function getUsers()

// app/Models/User.php
class User extends Authenticatable
```

### Vistas / JS de referencia
- `resources/views/admin/users/index.blade.php` (DataTable `#users-table` y busqueda)
- `resources/views/admin/clientes/index.blade.php` (markup canonico `advanced-filters-wrapper navy-theme`)

### Convenciones a respetar (ver `docs/conventions/`)
- `docs/conventions/ux-search-filters.md`
- `docs/conventions/js-validations.md` (no romper validaciones existentes)
- `AGENTS.md` § Estndares visuales

### NO existe — no referenciar
- ~~clase CSS `.operativa-filter-*`~~ — no existe

---

## Notas de implementacion

- Usar `User::query()` para permitir `where` condicionales antes de `DataTables::of(...)`.
- Mantener el DataTable sin `serverSide` si no se cambia el modo; solo filtrar en el query actual.

---

## Criterios de aceptacion

- [ ] Header con `navy-filter-header` visible y panel colapsable funcionando
- [ ] Filtros Rol/Estado aplican server-side
- [ ] Badge de filtros activos muestra el conteo correcto
- [ ] Boton Limpiar restablece filtros, busqueda y tabla

---

## QA manual

1. Ir a `/users` y verificar el nuevo header unificado.
2. Buscar texto y ver que filtra con debounce.
3. Abrir filtros, seleccionar Rol y Estado, confirmar recarga.
4. Click en Limpiar filtros.

---

## Instrucciones para el ejecutor

1. Leer el spec completo.
2. Verificar dependencias.
3. Actualizar el header `Status` y `Assigned-to`.
4. Crear rama `feat/TASK-007-users-filtros-unificados`.
5. Implementar dentro del scope y completar QA.
6. Mover a `sdd/tasks/completed/` y llenar Nota de Completitud.

---

## Nota de Completitud

**Completado por**: santiago
**Fecha**: 2026-05-23
**Commits**: 9fe30dd
**Notas**: Header unificado con filtros de rol/estado, debounce de busqueda y clear; DataTable server-side con filtros en query.

**Desviaciones del spec**: ninguna
