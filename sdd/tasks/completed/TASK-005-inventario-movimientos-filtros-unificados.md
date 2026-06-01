# TASK-005: Filtros unificados en Inventario Movimientos

**Feature**: FEAT-001 — unified-filters-rollout
**Spec**: `sdd/specs/unified-filters-rollout.spec.md`
**Status**: done
**Priority**: medium
**Esfuerzo estimado**: M (2-4h)
**Depends-on**: none
**Assigned-to**: santiago

---

## Contexto

Migrar el listado de Movimientos de Inventario al patron unificado de busqueda + filtros (spec seccion 3, Modulo 5).

---

## Scope

- Reemplazar el header con `search-box` por la estructura `advanced-filters-wrapper navy-theme` y `navy-filter-header`.
- Agregar filtros: Tipo (Entrada/Salida), Insumo y rango de fecha (desde/hasta).
- Cablear JS estandar: `updateFilterBadge()`, debounce 300ms, `clear-filters` y toggle del collapse.
- Enviar filtros en el DataTable y aplicar `where` condicionales en `MovimientoInsumoController::getMovimientos()`.
- Preservar botones Registrar Movimiento, Alertas y Reporte.

**NO esta en alcance**:
- Cambiar columnas o estructura del DataTable.
- Introducir CSS nuevo o estilos inline.

---

## Archivos a crear / modificar

| Archivo | Accion | Descripcion |
|---|---|---|
| `resources/views/admin/inventario/movimientos/index.blade.php` | MODIFY | Header unificado + filtros |
| `resources/views/admin/inventario/movimientos/scripts/main.blade.php` | MODIFY | Handlers de filtros + badge + debounce |
| `app/Http/Controllers/MovimientoInsumoController.php` | MODIFY | Filtros server-side en `getMovimientos()` |

---

## Codebase Contract (Anti-Alucinacion)

### Imports verificados
```php
use App\Models\MovimientoInsumo;    // app/Models/MovimientoInsumo.php
use App\Models\Insumo;              // app/Models/Insumo.php
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
```

### Firmas existentes a usar
```php
// app/Http/Controllers/MovimientoInsumoController.php
public function index()
public function getMovimientos()

// app/Models/MovimientoInsumo.php
class MovimientoInsumo extends Model
```

### Vistas / JS de referencia
- `resources/views/admin/inventario/movimientos/index.blade.php` (header con search-box actual)
- `resources/views/admin/inventario/movimientos/scripts/main.blade.php` (DataTable `#movimientos-table` y busqueda)
- `resources/views/admin/clientes/index.blade.php` (markup canonico `advanced-filters-wrapper navy-theme`)

### Convenciones a respetar (ver `docs/conventions/`)
- `docs/conventions/ux-search-filters.md`
- `AGENTS.md` § Estndares visuales

### NO existe — no referenciar
- ~~clase CSS `.operativa-filter-*`~~ — no existe

---

## Notas de implementacion

- El filtro de Insumo debe usar la lista ya cargada en `index()`.
- Para fechas, filtrar por `movimiento_insumo.created_at` con `whereBetween`.

---

## Criterios de aceptacion

- [ ] Header con `navy-filter-header` visible y panel colapsable funcionando
- [ ] Filtros Tipo/Insumo/Fecha aplican server-side
- [ ] Badge de filtros activos muestra el conteo correcto
- [ ] Boton Limpiar restablece filtros, busqueda y tabla

---

## QA manual

1. Ir a `/inventario/movimientos` y verificar el nuevo header unificado.
2. Buscar texto y ver que filtra con debounce.
3. Abrir filtros, seleccionar Tipo/Insumo/Fecha, confirmar recarga.
4. Click en Limpiar filtros.

---

## Instrucciones para el ejecutor

1. Leer el spec completo.
2. Verificar dependencias.
3. Actualizar el header `Status` y `Assigned-to`.
4. Crear rama `feat/TASK-005-inventario-movimientos-filtros-unificados`.
5. Implementar dentro del scope y completar QA.
6. Mover a `sdd/tasks/completed/` y llenar Nota de Completitud.

---

## Nota de Completitud

**Completado por**: santiago
**Fecha**: 2026-05-23
**Commits**: f7c97f0
**Notas**: Header unificado con filtros tipo/insumo/fecha, debounce, clear y badge; DataTable envia filtros y controller aplica condiciones.

**Desviaciones del spec**: ninguna
