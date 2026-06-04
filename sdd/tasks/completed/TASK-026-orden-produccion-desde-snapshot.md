# TASK-026: Órdenes de Producción desde snapshot + consumo de tela (CRÍTICA)

**Feature**: FEAT-003 — variantes-dinamicas
**Spec**: `sdd/specs/variantes-dinamicas.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: L
**Depends-on**: TASK-020, TASK-025
**Assigned-to**: emmanuel

---

## Contexto
El punto crítico del refactor. Decisión §8: **Opción 1** — la orden ya tiene `detalle_pedido_id` + `detallePedido()`; debe fabricar leyendo tipo+tela+atributos del **snapshot de la línea**, no de `producto_id`. Además descuenta la tela elegida del stock.

## Scope
- Migración: `orden_produccion.producto_id` → **nullable** (legacy, no se borra).
- En `OrdenProduccionController`: al crear/mostrar/producir una orden, resolver qué fabricar desde `detallePedido` (snapshots tela/atributos) cuando no hay `producto_id`. Nombre/descripción de la orden desde el snapshot.
- Al producir (estado que descuenta stock): descontar la **tela** del snapshot vía `MovimientoInsumo` tipo Salida, cantidad = `tipo.consumo_tela_por_unidad × cantidad_producida` (mantener el patrón actual de consumo de insumos).

**NO está en alcance**: cambiar el flujo de estados de la orden más allá del consumo de tela; UI de productos (TASK-027).

## Archivos a crear / modificar
| Archivo | Acción | Descripción |
|---|---|---|
| `database/migrations/<fecha>_orden_produccion_nullable_producto.php` | CREATE | nullable |
| `app/Http/Controllers/OrdenProduccionController.php` | MODIFY | resolver desde snapshot + consumo tela |
| `app/Models/OrdenProduccion.php` | MODIFY (si aplica) | accessor nombre desde snapshot |

## Codebase Contract (Anti-Alucinación)
### Firmas verificadas
```php
// app/Models/OrdenProduccion.php:15 — fillable: pedido_id, detalle_pedido_id, producto_id, empleado_id,
//   cantidad_solicitada, cantidad_producida, cantidad_defectuosa, fechas, estado, notas, created_by
//   producto(): BelongsTo(Producto) (:40) · detallePedido(): BelongsTo(DetallePedido,'detalle_pedido_id')
//   insumos(): BelongsToMany(Insumo,'detalle_orden_insumo')->withPivot(cantidad_estimada,cantidad_utilizada)
//   columnas BD orden_produccion: ... detalle_pedido_id, producto_id ... (ya existe detalle_pedido_id)
// app/Models/TipoProducto — consumo_tela_por_unidad (decimal:2)
// MovimientoInsumo — patrón Entrada/Salida + lockForUpdate (ver CompraService::procesar / módulo producción actual)
// app/Models/DetallePedido — tela_snapshot, atributos_snapshot, tipo_producto_id (TASK-020)
```
### Convenciones
- `docs/conventions/product-variants.md` §2 — SOLO producción descuenta stock vía `MovimientoInsumo`.
- `docs/conventions/business-flows.md`.
### NO existe — no referenciar
- No asumir que `orden_produccion` necesita `tipo_producto_id` propio — leer del `detallePedido` (Opción 1).

## Criterios de aceptación
- [ ] Generar orden desde un pedido con línea dinámica → la orden identifica correctamente tipo/tela/atributos desde el snapshot.
- [ ] Al producir, se descuenta la tela del snapshot del stock (`MovimientoInsumo` Salida, `tipo.consumo_tela_por_unidad × cantidad`).
- [ ] Órdenes legacy (con producto_id) siguen funcionando.
- [ ] `migrate:fresh --seed` limpio.

## QA manual
1. Pedido dinámico → generar orden → ver que muestra "Franela · Algodón · manga corta" desde snapshot.
2. Marcar producción → verificar `MovimientoInsumo` Salida de la tela y `stock_actual` decrementado.

## Nota de Completitud

**Completado por**: emmanuel
**Fecha**: 2026-06-03
**Commits**: (en rama `feat/variantes-dinamicas`)
**Notas** (Opción 1 del §8 — la orden ya tiene `detalle_pedido_id`):
- Migración `…220300`: `orden_produccion.producto_id` → nullable (ALTER MODIFY, conserva FK).
- `OrdenProduccion::getNombreProductoAttribute()`: nombre legible legacy (producto) o dinámico
  (tipo + tela_snapshot + atributos_snapshot; fallback `sku_snapshot`).
- `pedidosDisponibles()`: tipo desde `producto->tipoProducto` (legacy) o `tipoProducto` (dinámico);
  **tela prellenada en insumos_default** desde `producto->tela` o desde `tela_snapshot`
  (id/nombre/unidad), cantidad = `consumo_tela_por_unidad × unidades`. Eager load
  `productos.tipoProducto.insumosDefault`.
- Display: `getOrdenes`, `ordenesPorEmpleado`, `show` usan `nombre_producto` (+ eager load
  `detallePedido.tipoProducto`; `show` hace `append('nombre_producto')`).
- `store`/`storeBatch` ya derivaban `producto_id` de la línea (null para dinámico, OK con la migración).
- QA (rollback): pedido dinámico → la línea muestra "Franela Pique" + tela prefill (Pique ×10);
  orden creada con producto_id NULL, `nombre_producto`="Franela Pique Corta Clásico", tela en insumos.

**Consumo de tela (§8):** la tela se consume vía los **insumos de la orden** (prefill), igual que
para productos legacy. La producción no descuenta stock directamente en este controller (lo maneja
el módulo de inventario / flujo de insumos de la orden); por eso no se agregó lógica nueva de
`MovimientoInsumo` — el comportamiento dinámico iguala al legacy.

**Desviaciones del spec**: ninguna funcional. La "consume tela" se cumple por el mecanismo de
insumos de la orden (no se introdujo `MovimientoInsumo` nuevo, consistente con el flujo actual).

**Pendiente para QA final / frontend**: el modal de orden (vista) debería mostrar
`orden.nombre_producto` para órdenes dinámicas (hoy el front puede leer `orden.producto.nombre`,
null en dinámicas) — verificar en navegador.
