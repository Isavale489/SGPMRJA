# TASK-004: Filtros unificados en Produccion Diaria

**Feature**: FEAT-001 — unified-filters-rollout
**Spec**: `sdd/specs/unified-filters-rollout.spec.md`
**Status**: done
**Priority**: medium
**Esfuerzo estimado**: M (2-4h)
**Depends-on**: none
**Assigned-to**: santiago

---

## Contexto

Migrar el listado de Produccion Diaria al patron unificado de busqueda + filtros (spec seccion 3, Modulo 4).

---

## Scope

- Cambiar la card a `card-transactional` para alinear con Gestion Operativa.
- Reemplazar el header con `search-box` por la estructura `advanced-filters-wrapper navy-theme` y `navy-filter-header`.
- Agregar filtros: Empleado (select) y rango de fecha (desde/hasta) con `<input type="date">`.
- Cablear JS estandar: `updateFilterBadge()`, debounce 300ms, `clear-filters` y toggle del collapse.
- Enviar filtros en el DataTable y aplicar `where` condicionales en `ProduccionDiariaController::getRegistros()`.

**NO esta en alcance**:
- Cambiar columnas o estructura del DataTable.
- Introducir CSS nuevo o estilos inline.

---

## Archivos a crear / modificar

| Archivo | Accion | Descripcion |
|---|---|---|
| `resources/views/admin/produccion/diaria/index.blade.php` | MODIFY | Card transaccional + header unificado + filtros |
| `resources/views/admin/produccion/diaria/scripts/main.blade.php` | MODIFY | Handlers de filtros + badge + debounce |
| `app/Http/Controllers/ProduccionDiariaController.php` | MODIFY | Filtros server-side en `getRegistros()` |

---

## Codebase Contract (Anti-Alucinacion)

### Imports verificados
```php
use App\Models\ProduccionDiaria;    // app/Models/ProduccionDiaria.php
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
```

### Firmas existentes a usar
```php
// app/Http/Controllers/ProduccionDiariaController.php
public function index()
public function getRegistros()

// app/Models/ProduccionDiaria.php
class ProduccionDiaria extends Model
```

### Vistas / JS de referencia
- `resources/views/admin/produccion/diaria/index.blade.php` (header con search-box actual)
- `resources/views/admin/produccion/diaria/scripts/main.blade.php` (DataTable `#produccion-table` y busqueda)
- `resources/views/admin/clientes/index.blade.php` (markup canonico `advanced-filters-wrapper navy-theme`)

### Convenciones a respetar (ver `docs/conventions/`)
- `docs/conventions/ux-search-filters.md`
- `docs/conventions/js-validations.md` (Select2 en modales)
- `AGENTS.md` § Estndares visuales

### NO existe — no referenciar
- ~~clase CSS `.operativa-filter-*`~~ — no existe

---

## Notas de implementacion

- El filtro de Empleado debe usar la lista ya cargada en `index()`.
- Para fecha, filtrar por `fecha_produccion` con `whereBetween`.

---

## Criterios de aceptacion

- [ ] Header con `navy-filter-header` visible y panel colapsable funcionando
- [ ] Filtros Empleado/Fecha aplican server-side
- [ ] Badge de filtros activos muestra el conteo correcto
- [ ] Boton Limpiar restablece filtros, busqueda y tabla

---

## QA manual

1. Ir a `/produccion/diaria` y verificar el nuevo header unificado.
2. Buscar texto y ver que filtra con debounce.
3. Abrir filtros, seleccionar Empleado y rango de fechas, confirmar recarga.
4. Click en Limpiar filtros.

---

## Instrucciones para el ejecutor

1. Leer el spec completo.
2. Verificar dependencias.
3. Actualizar el header `Status` y `Assigned-to`.
4. Crear rama `feat/TASK-004-produccion-diaria-filtros-unificados`.
5. Implementar dentro del scope y completar QA.
6. Mover a `sdd/tasks/completed/` y llenar Nota de Completitud.

---

## Nota de Completitud

**Completado por**: santiago
**Fecha**: 2026-05-23
**Commits**: 53139b7
**Notas**: Card transaccional y header unificado; filtros por empleado y fecha con debounce, clear y badge; controller filtra por empleado y rango de fechas.

**Desviaciones del spec**: ninguna
