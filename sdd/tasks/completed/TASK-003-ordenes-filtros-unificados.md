# TASK-003: Filtros unificados en Ordenes de Produccion

**Feature**: FEAT-001 — unified-filters-rollout
**Spec**: `sdd/specs/unified-filters-rollout.spec.md`
**Status**: done
**Priority**: medium
**Esfuerzo estimado**: M (2-4h)
**Depends-on**: none
**Assigned-to**: santiago

---

## Contexto

Migrar el listado de Ordenes de Produccion al patron unificado de busqueda + filtros (spec seccion 3, Modulo 3). Ajuste aprobado por el owner: el filtro de Departamento se descarta por no existir en el esquema actual.

---

## Scope

- Reemplazar el header con `search-box` por la estructura `advanced-filters-wrapper navy-theme` y `navy-filter-header`.
- Agregar filtros: Estado, Fecha desde, Fecha hasta y Orden (Recientes / Mayor progreso / Menor progreso).
- Cablear JS estandar: `updateFilterBadge()`, debounce 300ms, `clear-filters` y toggle del collapse.
- Enviar filtros en el DataTable y aplicar `where` condicionales en `OrdenProduccionController::getOrdenes()`.
- Mantener botones existentes (Nueva Orden) y la tabla actual.

**NO esta en alcance**:
- Agregar filtro por Departamento o cambios de BD.
- Cambiar columnas o estructura del DataTable.
- Introducir CSS nuevo o estilos inline.

---

## Archivos a crear / modificar

| Archivo | Accion | Descripcion |
|---|---|---|
| `resources/views/admin/ordenes/index.blade.php` | MODIFY | Header unificado + filtros |
| `resources/views/admin/ordenes/scripts/main.blade.php` | MODIFY | Handlers de filtros + badge + debounce |
| `app/Http/Controllers/OrdenProduccionController.php` | MODIFY | Filtros server-side en `getOrdenes()` |

---

## Codebase Contract (Anti-Alucinacion)

### Imports verificados
```php
use App\Models\OrdenProduccion;     // app/Models/OrdenProduccion.php
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
```

### Firmas existentes a usar
```php
// app/Http/Controllers/OrdenProduccionController.php
public function index()
public function getOrdenes()

// app/Models/OrdenProduccion.php
class OrdenProduccion extends Model
```

### Vistas / JS de referencia
- `resources/views/admin/ordenes/index.blade.php` (header con search-box actual)
- `resources/views/admin/ordenes/scripts/main.blade.php` (DataTable `#ordenes-table` y busqueda)
- `resources/views/admin/clientes/index.blade.php` (markup canonico `advanced-filters-wrapper navy-theme`)

### Convenciones a respetar (ver `docs/conventions/`)
- `docs/conventions/ux-search-filters.md`
- `AGENTS.md` § Estndares visuales

### NO existe — no referenciar
- ~~filtro Departamento~~ — descartado por no existir en el esquema
- ~~clase CSS `.operativa-filter-*`~~ — no existe

---

## Notas de implementacion

- Filtro por fecha debe usar `fecha_fin_estimada` (rango desde/hasta).
- Ordenar por progreso puede ser calculado en query (ratio) o en memoria, sin romper el ordenamiento base.

---

## Criterios de aceptacion

- [ ] Header con `navy-filter-header` visible y panel colapsable funcionando
- [ ] Filtros Estado/Fecha/Orden aplican server-side
- [ ] Badge de filtros activos muestra el conteo correcto
- [ ] Boton Limpiar restablece filtros, busqueda y tabla

---

## QA manual

1. Ir a `/ordenes` y verificar el nuevo header unificado.
2. Buscar texto y ver que filtra con debounce.
3. Abrir filtros, seleccionar Estado y rango de fechas, confirmar recarga.
4. Cambiar Orden y verificar que reordena.
5. Click en Limpiar filtros.

---

## Instrucciones para el ejecutor

1. Leer el spec completo.
2. Verificar dependencias.
3. Actualizar el header `Status` y `Assigned-to`.
4. Crear rama `feat/TASK-003-ordenes-filtros-unificados`.
5. Implementar dentro del scope y completar QA.
6. Mover a `sdd/tasks/completed/` y llenar Nota de Completitud.

---

## Nota de Completitud

**Completado por**: Santiago + equipo (TL)
**Fecha**: 2026-04 → 2026-05 (rollout); reconciliado 2026-05-26
**Commits**: `1f7dd15` (rollout operativa, tocó `ordenes/index.blade.php` +67 y `ordenes/scripts/main.blade.php` +72 líneas), PR #13
**Notas**: Filtros aplicados en `ordenes/index.blade.php` con `advanced-filters-wrapper` + `navy-filter-header`. Filtros reales: Estado, Fecha desde, Fecha hasta, Orden. Verificado en código durante reconciliación SDD. Reconciliado a `done` por discrepancia entre task y git.

**Desviaciones del spec**: el spec planteaba "Estado + Departamento"; la implementación usa Estado + rango de fecha (desde/hasta) + Orden.
