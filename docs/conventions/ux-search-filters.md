# Búsqueda + Filtros Unificados

> Patrón visual unificado para módulos maestros: barra de búsqueda integrada en el header de filtros, panel colapsable de filtros avanzados, badge con contador de filtros activos.

## Estructura HTML

```
.advanced-filters-wrapper.navy-theme
  └── .navy-filter-header.is-collapsed         ← siempre visible
        ├── .navy-header-search                 ← fondo blanco, flex-grow
        │     ├── <i class="ri-search-line">
        │     └── input.navy-search-input       ← id="custom-search-input"
        ├── .navy-header-divider
        └── button.navy-filter-btn.collapsed    ← toggle collapse
              ├── <i ri-filter-3-line>
              ├── <span>Filtros</span>
              ├── span.navy-filter-badge#active-filter-count
              └── <i.navy-filter-chevron>
  └── .collapse#filters-collapse-body
        └── .navy-filter-body
              └── [selects navy-filter-select] + btn#btn-clear-filters
```

## Clases CSS (en `custom.css`)

| Clase | Propósito |
|---|---|
| `.navy-filter-header` | Flex container del header unificado |
| `.navy-filter-header.is-collapsed` | Bordes redondeados cuando cerrado |
| `.navy-header-search` | Zona izquierda (bg blanco, hover `#f5f7fb`) |
| `.navy-search-input` | Input transparente sin bordes |
| `.navy-header-divider` | Línea vertical separadora |
| `.navy-filter-btn` | Botón toggle (reemplaza el viejo `navy-filter-toggle`) |
| `.navy-filter-badge` | Pill contador de filtros activos |
| `.btn-historial` + `.btn-historial-ver` / `.btn-historial-volver` | Botones pill historial (amber / navy) |

Todas tienen dark mode completo en el mismo archivo.

## Patrón JS estándar

```js
function updateFilterBadge() {
    // cuenta selects no-default
    let count = 0;
    $('.navy-filter-select').each(function () {
        if ($(this).val() && $(this).val() !== '') count++;
    });
    $('#active-filter-count').text(count).toggle(count > 0);
}

$('#filters-collapse-body')
    .on('show.bs.collapse',  () => $('.navy-filter-header').removeClass('is-collapsed'))
    .on('hidden.bs.collapse', () => $('.navy-filter-header').addClass('is-collapsed'));

// Búsqueda: debounce 300ms → table.search(val).draw()
$('#custom-search-input').on('input', debounce(function () {
    table.search($(this).val()).draw();
}, 300));

// Filtros: ajax.reload() (server-side) o column(N).search().draw() (client-side)
$('.navy-filter-select').on('change', function () {
    table.ajax.reload();   // o table.column(N).search($(this).val()).draw();
    updateFilterBadge();
});

// Clear: resetea inputs + tabla + badge
$('#btn-clear-filters').on('click', function () {
    $('.navy-filter-select').val('').trigger('change');
    $('#custom-search-input').val('');
    table.search('').draw();
    updateFilterBadge();
});
```

## Estado por módulo

> Rollout completado (FEAT-001, 2026-05). El patrón está aplicado en **todos los módulos maestros y operativos**.

### Maestros (paleta navy `navy-theme`)

| Módulo | Filtros | Mecanismo | Botón Historial |
|---|---|---|---|
| Clientes | Tipo, Estatus, Estado (3 selects) | Server-side (`ajax.reload()`) | ✅ |
| Proveedores | Tipo Proveedor (1 select) | Client-side (`column(2).search()`) | ✅ |
| Productos | Tipo Producto (1 select, dinámico) | Server-side (`ajax.reload()`) | ✅ |
| Empleados | Departamento, Cargo | Server-side | ✅ |
| Insumos | Tipo | Server-side | ✅ |
| Departamentos / Cargos | búsqueda simple | Client-side | ✅ |

### Operativos (paleta emerald `emerald-theme` — ver `480b4bd`)

| Módulo | Filtros | Mecanismo | Botón Historial |
|---|---|---|---|
| Cotizaciones | Estado, Fecha, Orden | Server-side | — |
| Pedidos | Estado, Fecha entrega, Orden | Server-side | — |
| Órdenes de Producción | Estado, Fecha desde/hasta, Orden | Server-side | — |
| Producción Diaria | Empleado, Fecha desde/hasta | Server-side | — |
| Inventario Movimientos | Tipo, Insumo, Fecha desde/hasta | Server-side | — |
| Inventario Alertas | (solo visual — vista ya filtrada por stock bajo) | — | — |

### Sistema

| Módulo | Filtros | Mecanismo |
|---|---|---|
| Usuarios | Rol, Estado | Server-side |

> **Nota de paleta**: los módulos operativos usan la variante `emerald-theme` del wrapper (no `navy-theme`) para mantener consistencia con el estándar visual de la sección Gestión Operativa. Las clases estructurales `navy-filter-*` se mantienen como base; solo el color cambia según el tema del wrapper padre.

## Patrón server-side: ejemplo Productos

`ProductoController::getProductos()` usa Eloquent query builder (NO `->get()` directo). Esto permite encadenar filtros condicionales:

```php
$query = Producto::query()->with(['tipoProducto', 'insumoTela']);

if ($request->filled('filter_tipo_producto_id')) {
    $query->where('tipo_producto_id', $request->filter_tipo_producto_id);
}

return DataTables::of($query)->make(true);
```

**Regla**: para añadir un filtro nuevo server-side, añadir `$query->where(...)` antes del `DataTables::of($query)`. No usar Collection (`->get()`) si se necesitan filtros dinámicos.

## Cómo aplicar el patrón a un módulo nuevo

1. Reemplazar el search-box clásico del card-header por la estructura HTML de arriba.
2. Usar las clases CSS existentes — no inventar nuevas.
3. Decidir mecanismo de filtro:
   - Pocos registros, datos en cliente → `column(N).search()`
   - Muchos registros, datos en servidor → `ajax.reload()` + lógica `where` en el controller
4. Implementar `updateFilterBadge()` y los handlers de collapse.
5. Añadir botón historial si el módulo tiene tabla de soft-deleted.
