# FEAT-001: Rollout del Patrón de Filtros Unificados

Migrar los 7 módulos pendientes al patrón `navy-filter-header` documentado en `docs/conventions/ux-search-filters.md`. Cada módulo reemplaza su `search-box` viejo en `card-header` por la estructura `advanced-filters-wrapper.navy-theme` con filtros colapsables, badge contador, búsqueda con debounce 300ms, y filtros server-side vía `ajax.reload()`.

## Investigación completada

He leído y verificado:
- [AGENTS.md](file:///c:/Users/Santi/Project/SGPMRJA/AGENTS.md) — reglas del equipo
- [sdd/WORKFLOW.md](file:///c:/Users/Santi/Project/SGPMRJA/sdd/WORKFLOW.md) — metodología SDD
- [Spec completo FEAT-001](file:///c:/Users/Santi/Project/SGPMRJA/sdd/specs/unified-filters-rollout.spec.md) — diseño aprobado
- [ux-search-filters.md](file:///c:/Users/Santi/Project/SGPMRJA/docs/conventions/ux-search-filters.md) — patrón canónico
- [js-validations.md](file:///c:/Users/Santi/Project/SGPMRJA/docs/conventions/js-validations.md) — validaciones JS
- Vista de referencia: [clientes/index.blade.php](file:///c:/Users/Santi/Project/SGPMRJA/resources/views/admin/clientes/index.blade.php) (patrón ya aplicado)
- Los 7 módulos a migrar (vistas, controllers, scripts)

## Orden de ejecución (de menor a mayor riesgo)

| # | Módulo | Task | Complejidad | Filtros |
|---|--------|------|-------------|---------|
| 1 | Inv. Alertas | TASK-001 | S (< 2h) | Solo visual, sin filtros nuevos |
| 2 | Users | TASK-002 | S (< 2h) | Rol + Estado (server-side) |
| 3 | Órdenes | TASK-003 | M (2-4h) | Estado + Departamento (server-side) |
| 4 | Producción Diaria | TASK-004 | M (2-4h) | Empleado + Fecha desde/hasta (server-side) |
| 5 | Inv. Movimientos | TASK-005 | M (2-4h) | Tipo + Insumo + Fecha (server-side) |
| 6 | Pedidos | TASK-006 | M (2-4h) | Estado + Cliente (server-side) |
| 7 | Cotizaciones | TASK-007 | M (2-4h) | Estado + Cliente (server-side) |

> [!IMPORTANT]
> El spec indica empezar por el módulo más simple para validar el approach. Sigo esa recomendación: Alertas → Users → Órdenes → Producción → Movimientos → Pedidos → Cotizaciones.

## Proposed Changes

### Por cada módulo (patrón repetitivo)

1. **Vista `index.blade.php`**: Reemplazar `card-header > search-box` por `advanced-filters-wrapper.navy-theme` con:
   - `navy-filter-header.is-collapsed` + `navy-header-search` + `navy-filter-btn`
   - `collapse#filters-collapse-body` con selects `navy-filter-select`
   - Botón `btn-clear-filters`
   - Preservar botones existentes (Agregar, PDF, Historial, etc.)

2. **Script JS** (`scripts/main.blade.php` o inline): Añadir:
   - `updateFilterBadge()` función
   - Collapse listeners (`show.bs.collapse` / `hidden.bs.collapse`)
   - Debounce 300ms en `#custom-search-input`
   - `.navy-filter-select` change → `table.ajax.reload()` + `updateFilterBadge()`
   - `#btn-clear-filters` click → reset + reload

3. **Controller** (solo módulos con filtros server-side): Añadir `$request->filled('filter_*')` → `$query->where(...)` antes del `DataTables::of($query)`.

---

### Archivos específicos por task

#### TASK-001: Inventario Alertas (solo visual)
- [MODIFY] [index.blade.php](file:///c:/Users/Santi/Project/SGPMRJA/resources/views/admin/inventario/alertas/index.blade.php)

#### TASK-002: Users
- [MODIFY] [index.blade.php](file:///c:/Users/Santi/Project/SGPMRJA/resources/views/admin/users/index.blade.php)
- [MODIFY] [UserController.php](file:///c:/Users/Santi/Project/SGPMRJA/app/Http/Controllers/UserController.php)

#### TASK-003: Órdenes de Producción
- [MODIFY] [index.blade.php](file:///c:/Users/Santi/Project/SGPMRJA/resources/views/admin/ordenes/index.blade.php)
- [MODIFY] [OrdenProduccionController.php](file:///c:/Users/Santi/Project/SGPMRJA/app/Http/Controllers/OrdenProduccionController.php)
- [MODIFY] scripts/main.blade.php (de órdenes)

#### TASK-004: Producción Diaria
- [MODIFY] [index.blade.php](file:///c:/Users/Santi/Project/SGPMRJA/resources/views/admin/produccion/diaria/index.blade.php)
- [MODIFY] [ProduccionDiariaController.php](file:///c:/Users/Santi/Project/SGPMRJA/app/Http/Controllers/ProduccionDiariaController.php)
- [MODIFY] scripts/main.blade.php (de producción diaria)

#### TASK-005: Inventario Movimientos
- [MODIFY] [index.blade.php](file:///c:/Users/Santi/Project/SGPMRJA/resources/views/admin/inventario/movimientos/index.blade.php)
- [MODIFY] [MovimientoInsumoController.php](file:///c:/Users/Santi/Project/SGPMRJA/app/Http/Controllers/MovimientoInsumoController.php)
- [MODIFY] scripts/main.blade.php (de movimientos)

#### TASK-006: Pedidos
- [MODIFY] [index.blade.php](file:///c:/Users/Santi/Project/SGPMRJA/resources/views/admin/pedidos/index.blade.php)
- [MODIFY] [PedidoController.php](file:///c:/Users/Santi/Project/SGPMRJA/app/Http/Controllers/PedidoController.php)
- [MODIFY] scripts (de pedidos)

#### TASK-007: Cotizaciones
- [MODIFY] [index.blade.php](file:///c:/Users/Santi/Project/SGPMRJA/resources/views/admin/cotizaciones/index.blade.php)
- [MODIFY] [CotizacionController.php](file:///c:/Users/Santi/Project/SGPMRJA/app/Http/Controllers/CotizacionController.php)
- [MODIFY] scripts/main.blade.php (de cotizaciones)

---

### Task final: Actualizar documentación
- [MODIFY] [ux-search-filters.md](file:///c:/Users/Santi/Project/SGPMRJA/docs/conventions/ux-search-filters.md) — tabla "Estado por módulo" actualizada

## Restricciones clave (del spec)

- **NO** introducir librerías nuevas
- **NO** cambiar columnas de DataTable ni su lógica de ordenamiento
- **NO** tocar `inventario/reporte/` (sección Reportes, paleta sky)
- **NO** estilos inline nuevos en Blade (todo en `custom.css` existente)
- Cards usan `card-transactional` (sección operativa), Users usa `card-maestros`
- Usar `navy-filter-*` existente, no inventar clases nuevas
- Filtros de fecha: 2 `<input type="date">` simples, NO flatpickr

## Verification Plan

### QA por módulo
1. Cargar listado → header colapsado visible
2. Búsqueda con debounce 300ms funciona
3. Expandir/colapsar filtros con animación
4. Filtros server-side recargan DataTable
5. Badge contador muestra filtros activos
6. "Limpiar filtros" resetea todo
7. Botones existentes siguen funcionando
8. Dark mode sin regresiones

### Comando de verificación
```bash
php artisan route:list
```
(verificar que no se rompen rutas existentes)
