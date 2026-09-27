# TASK-054: Corregir 500 del PDF de Pedidos en líneas con producto dinámico

**Feature**: FEAT-006 — correcciones-ux-detalles
**Spec**: `sdd/specs/correcciones-ux-detalles.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: S (< 2h)
**Depends-on**: TASK-053
**Assigned-to**: vanessa

---

## Contexto

Bug **preexistente** detectado al cablear el botón "Descargar PDF" del detalle de Pedidos (TASK-053). La plantilla del PDF (`pedidos/factura.blade.php`) accedía a `$detalle->producto->nombre` sin null-safety. Con la feature de **variantes dinámicas** (`detalle_pedido.producto_id` es nullable, TASK-020), las líneas dinámicas tienen `producto = null`, así que el PDF reventaba con:

```
Attempt to read property "nombre" on null
  ↳ resources/views/admin/pedidos/factura.blade.php:165
  ↳ PedidoController::pedidoPdf → PDF::loadView (línea 349)
```

En la BD había 7 `detalle_pedido` con `producto_id` NULL (p. ej. pedido #1, línea tipo "Delantal"). El PDF de Cotizaciones no fallaba porque ya usaba el fallback `?? '-'`.

---

## Scope

- Hacer null-safe el nombre del producto en `factura.blade.php`, con el **mismo fallback que la vista web** (`scripts/listado.blade.php:364`): producto → tipo de producto → "Producto".
- Eager-load de `productos.tipoProducto` en `PedidoController::pedidoPdf` para evitar N+1.

**NO está en alcance**:
- Cambiar el cálculo financiero del PDF ni el diseño de la factura.
- El PDF de Cotizaciones (ya era null-safe).

---

## Archivos modificados

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/pedidos/factura.blade.php` | MODIFY | Línea 165: `$detalle->producto?->nombre ?? $detalle->tipoProducto?->nombre ?? 'Producto'` |
| `app/Http/Controllers/PedidoController.php` | MODIFY | `pedidoPdf` (línea 340): añadir `'productos.tipoProducto'` al `->load()` |

---

## Codebase Contract (Anti-Alucinación)

> Verificado en `enmanuel` (2026-06-18).

```text
# app/Http/Controllers/PedidoController.php
  public function pedidoPdf(Pedido $pedido) .......... :337
  $pedido->load([... 'productos.producto', ...]) ..... :340  ← añadir 'productos.tipoProducto'
  PDF::loadView('admin.pedidos.factura', [...]) ...... :349

# app/Models/DetallePedido.php
  producto():    belongsTo(Producto::class) ....... :59   (NULLABLE → puede ser null en líneas dinámicas)
  tipoProducto(): belongsTo(TipoProducto::class) .. :64
  columnas snapshot: tela_snapshot, atributos_snapshot, sku_snapshot, tipo_producto_id

# resources/views/admin/pedidos/factura.blade.php
  {{ $detalle->producto->nombre }} ................. :165  ← inseguro (causa del 500)
  {{ $detalle->color?->nombre ?? '-' }} ............ :173  (ya null-safe, referencia de patrón)

# Patrón web equivalente (referencia)
  resources/views/admin/pedidos/scripts/listado.blade.php:364
    prod.nombre_completo || prod.nombre || (prod.tipo_producto ? prod.tipo_producto.nombre : '') || 'Producto'
```

### NO existe — no referenciar
- ~~accessor `nombre_producto` en DetallePedido~~ — no existe; el nombre se resuelve por relación `producto`/`tipoProducto`.

---

## Criterios de aceptación

- [x] El PDF de un pedido con línea dinámica (producto null) ya no lanza 500.
- [x] La línea dinámica muestra el nombre del tipo de producto (p. ej. "Delantal").
- [x] Los pedidos con producto normal siguen mostrando el nombre del producto.

---

## QA manual

1. `/pedidos` → "Ver" un pedido con producto dinámico (p. ej. #1) → "Descargar PDF" → abre sin error.
2. La línea dinámica muestra el tipo de producto, no un guion ni un crash.
3. Pedido con producto normal → nombre del producto correcto.

> Verificado adicionalmente renderizando `admin.pedidos.factura` para el pedido #1 en tinker → 15 KB sin excepción.

---

## Nota de Completitud

**Completado por**: vanessa (con Claude Code)
**Fecha**: 2026-06-18
**Commits**: 3ba1bd6
**Notas**: Fix de un bug preexistente (no introducido por FEAT-006) detectado al exponer la ruta PDF en el detalle de pedidos. Mismo fallback que la vista web. Verificado por render en tinker del pedido #1.
**Desviaciones del spec**: Task extra de bugfix; no estaba en el spec original.
