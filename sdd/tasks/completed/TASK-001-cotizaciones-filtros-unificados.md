# TASK-001: Filtros unificados en Cotizaciones

**Feature**: FEAT-001 — unified-filters-rollout
**Spec**: `sdd/specs/unified-filters-rollout.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: L (4-8h)
**Depends-on**: none
**Assigned-to**: santiago

---

## Contexto

Migrar el listado de Cotizaciones al patron unificado de busqueda + filtros (spec seccion 3, Modulo 1).

---

## Scope

- Reemplazar el header con `search-box` por la estructura `advanced-filters-wrapper navy-theme` y `navy-filter-header`.
- Agregar filtros avanzados: Estado (select) y Cliente (autocomplete persona-search).
- Cablear JS estandar: `updateFilterBadge()`, debounce de busqueda 300ms, `clear-filters` y toggle del collapse.
- Enviar filtros en el DataTable y aplicar `where` condicionales en `CotizacionController::getCotizaciones()` usando `$request->filled(...)`.
- Preservar botones Agregar y Exportar PDF sin mover su logica.

**NO esta en alcance**:
- Cambiar columnas o estructura del DataTable.
- Introducir CSS nuevo o estilos inline.
- Agregar librerias o endpoints nuevos.

---

## Archivos a crear / modificar

| Archivo | Accion | Descripcion |
|---|---|---|
| `resources/views/admin/cotizaciones/index.blade.php` | MODIFY | Header unificado y filtros en el listado |
| `resources/views/admin/cotizaciones/scripts/main.blade.php` | MODIFY | Handlers de filtros + badge + debounce |
| `app/Http/Controllers/CotizacionController.php` | MODIFY | Filtros server-side en `getCotizaciones()` |

---

## Codebase Contract (Anti-Alucinacion)

### Imports verificados
```php
use App\Models\Cotizacion;          // app/Models/Cotizacion.php
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
```

### Firmas existentes a usar
```php
// app/Http/Controllers/CotizacionController.php
public function index()
public function getCotizaciones()

// app/Models/Cotizacion.php
public static function actualizarCotizacionesVencidas()
```

### Vistas / JS de referencia
- `resources/views/admin/cotizaciones/index.blade.php` (header con search-box actual)
- `resources/views/admin/cotizaciones/scripts/main.blade.php` (DataTable `#cotizaciones-table`, busqueda `#custom-search-input`)
- `resources/views/admin/clientes/index.blade.php` (markup canonico `advanced-filters-wrapper navy-theme`)

### Convenciones a respetar (ver `docs/conventions/`)
- `docs/conventions/ux-search-filters.md` — patron canonico de filtros
- `docs/conventions/persona-unified-search.md` — autocomplete persona-search
- `AGENTS.md` § Estndares visuales (card, DataTable, dark mode)

### NO existe — no referenciar
- ~~`Cotizacion::scopeConFiltros()`~~ — no existe
- ~~clase CSS `.operativa-filter-*`~~ — no existe
- ~~ruta `cotizaciones.filters`~~ — no existe (usar `cotizaciones.data`)

---

## Notas de implementacion

- Para Cliente, usar el endpoint `/personas-search` y el helper documentado en `persona-unified-search.md`.
- Mantener la busqueda actual basada en DataTable, solo cambiar el input y handlers.

---

## Criterios de aceptacion

- [ ] Header con `navy-filter-header` visible y panel colapsable funcionando
- [ ] Filtros Estado y Cliente aplican server-side (interseccion con busqueda)
- [ ] Badge de filtros activos muestra el conteo correcto
- [ ] Boton Limpiar restablece selects, busqueda y tabla
- [ ] Sin estilos inline nuevos en Blade

---

## QA manual

1. Ir a `/cotizaciones` y verificar el nuevo header unificado.
2. Buscar texto y ver que filtra con debounce.
3. Abrir filtros, seleccionar Estado y Cliente, confirmar recarga.
4. Click en Limpiar filtros y confirmar reset.
5. Verificar light + dark mode.

---

## Instrucciones para el ejecutor

1. Leer el spec completo.
2. Verificar dependencias.
3. Actualizar el header `Status` y `Assigned-to`.
4. Crear rama `feat/TASK-001-cotizaciones-filtros-unificados`.
5. Implementar dentro del scope y completar QA.
6. Mover a `sdd/tasks/completed/` y llenar Nota de Completitud.

---

## Nota de Completitud

**Completado por**: Santiago + equipo (TL)
**Fecha**: 2026-04 → 2026-05 (rollout); reconciliado 2026-05-26
**Commits**: `1f7dd15` (rollout operativa), `8436a1a` + `922d5f2` (ajustes filtros cotizaciones), PR #13 y #14
**Notas**: Filtros aplicados en `cotizaciones/index.blade.php` con `advanced-filters-wrapper` + `navy-filter-header`. Filtros reales: Estado, Fecha, Orden. Verificado en código durante reconciliación SDD (grep `advanced-filters-wrapper` = 1). Reconciliado a `done` por discrepancia entre archivos de task y realidad de git (la implementación se mergeó sin cerrar la task).

**Desviaciones del spec**: el spec planteaba "Estado + Cliente"; la implementación usa Estado + Fecha + Orden (el filtro de cliente se descartó por redundancia con la búsqueda — ver PR #14 "remove redundant client input").
