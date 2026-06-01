# TASK-002: Filtros unificados en Pedidos

**Feature**: FEAT-001 — unified-filters-rollout
**Spec**: `sdd/specs/unified-filters-rollout.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: L (4-8h)
**Depends-on**: none
**Assigned-to**: santiago

---

## Contexto

Migrar el listado de Pedidos al patron unificado de busqueda + filtros (spec seccion 3, Modulo 2).

---

## Scope

- Reemplazar el header con `search-box` por la estructura `advanced-filters-wrapper navy-theme` y `navy-filter-header`.
- Agregar filtros: Estado (Pendiente/Completado/Cancelado) y Cliente (autocomplete persona-search).
- Cablear JS estandar: `updateFilterBadge()`, debounce 300ms, `clear-filters` y toggle del collapse.
- Enviar filtros en el DataTable y aplicar `where` condicionales en `PedidoController::getPedidos()`.
- Preservar botones Agregar y Exportar PDF.

**NO esta en alcance**:
- Cambiar columnas o estructura del DataTable.
- Introducir CSS nuevo o estilos inline.
- Agregar librerias o endpoints nuevos.

---

## Archivos a crear / modificar

| Archivo | Accion | Descripcion |
|---|---|---|
| `resources/views/admin/pedidos/index.blade.php` | MODIFY | Header unificado + filtros + JS de filtros |
| `app/Http/Controllers/PedidoController.php` | MODIFY | Filtros server-side en `getPedidos()` |

---

## Codebase Contract (Anti-Alucinacion)

### Imports verificados
```php
use App\Models\Pedido;              // app/Models/Pedido.php
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
```

### Firmas existentes a usar
```php
// app/Http/Controllers/PedidoController.php
public function index()
public function getPedidos()

// app/Models/Pedido.php
class Pedido extends Model
```

### Vistas / JS de referencia
- `resources/views/admin/pedidos/index.blade.php` (DataTable `#pedidos-table` y handlers actuales)
- `resources/views/admin/clientes/index.blade.php` (markup canonico `advanced-filters-wrapper navy-theme`)

### Convenciones a respetar (ver `docs/conventions/`)
- `docs/conventions/ux-search-filters.md`
- `docs/conventions/persona-unified-search.md`
- `AGENTS.md` § Estndares visuales

### NO existe — no referenciar
- ~~`Pedido::scopeConFiltros()`~~ — no existe
- ~~clase CSS `.operativa-filter-*`~~ — no existe
- ~~ruta `pedidos.filters`~~ — no existe (usar `pedidos.data`)

---

## Notas de implementacion

- Usar `/personas-search` para el filtro de Cliente, siguiendo `persona-unified-search.md`.
- Mantener la busqueda basada en DataTable, solo cambiar el input y handlers.

---

## Criterios de aceptacion

- [ ] Header con `navy-filter-header` visible y panel colapsable funcionando
- [ ] Filtros Estado y Cliente aplican server-side
- [ ] Badge de filtros activos muestra el conteo correcto
- [ ] Boton Limpiar restablece selects, busqueda y tabla
- [ ] Sin estilos inline nuevos en Blade

---

## QA manual

1. Ir a `/pedidos` y verificar el nuevo header unificado.
2. Buscar texto y ver que filtra con debounce.
3. Abrir filtros, seleccionar Estado y Cliente, confirmar recarga.
4. Click en Limpiar filtros y confirmar reset.
5. Verificar light + dark mode.

---

## Instrucciones para el ejecutor

1. Leer el spec completo.
2. Verificar dependencias.
3. Actualizar el header `Status` y `Assigned-to`.
4. Crear rama `feat/TASK-002-pedidos-filtros-unificados`.
5. Implementar dentro del scope y completar QA.
6. Mover a `sdd/tasks/completed/` y llenar Nota de Completitud.

---

## Nota de Completitud

**Completado por**: Santiago + equipo (TL)
**Fecha**: 2026-04 → 2026-05 (rollout); reconciliado 2026-05-26
**Commits**: `1f7dd15` (rollout operativa, tocó `pedidos/index.blade.php` +129 líneas), PR #13
**Notas**: Filtros aplicados en `pedidos/index.blade.php` con `advanced-filters-wrapper` + `navy-filter-header`. Filtros reales: Estado, Fecha de entrega, Orden. Verificado en código durante reconciliación SDD. Reconciliado a `done` por discrepancia entre task y git.

**Desviaciones del spec**: el spec planteaba "Estado + Cliente"; la implementación usa Estado + Fecha entrega + Orden (mismo criterio que cotizaciones — cliente descartado por redundancia con búsqueda).
