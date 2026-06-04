# TASK-025: Pedido — guardar línea dinámica sin `producto_id` + conversión desde cotización

**Feature**: FEAT-003 — variantes-dinamicas
**Spec**: `sdd/specs/variantes-dinamicas.spec.md`
**Status**: pending
**Priority**: high
**Esfuerzo estimado**: M
**Depends-on**: TASK-020, TASK-021
**Assigned-to**: unassigned

---

## Contexto
Equivalente a TASK-023 pero para Pedidos. Un pedido nace de una cotización aprobada (regla de negocio confirmada), así que la conversión debe **copiar los snapshots + tipo_producto_id** de la línea de cotización tal cual (inmutables).

## Scope
- En `PedidoController` store/update: `producto_id` deja de ser `required` → `nullable`; aceptar `tipo_producto_id` + snapshots.
- En la conversión cotización→pedido: copiar `tipo_producto_id`, `tela_snapshot`, `atributos_snapshot`, SKU y `precio_unitario` de cada `DetalleCotizacion` al `DetallePedido` (sin recalcular).
- Mantener inmutabilidad de snapshots.

**NO está en alcance**: cotización (TASK-023), órdenes (TASK-026).

## Archivos a crear / modificar
| Archivo | Acción | Descripción |
|---|---|---|
| `app/Http/Controllers/PedidoController.php` | MODIFY | validación nullable + creación/conversión de detalle |
| `app/Services/PedidoService.php` (si existe) | MODIFY | copia de snapshots en conversión |

## Codebase Contract (Anti-Alucinación)
### Verificar antes de codificar
```php
// grep producto_id required en PedidoController (mismo patrón que CotizacionController:166)
// app/Models/DetallePedido — fillable incl. tipo_producto_id (TASK-020), tela_snapshot, atributos_snapshot
// Regla negocio: un pedido SOLO nace de cotización aprobada (memoria pedidos-wizard, commit 278e039)
// Serialización lleva_bordado/es_personalizada como entero 1/0 (commit 960f3c9)
// ¿Existe PedidoService? verificar: ls app/Services/PedidoService.php
```
### Convenciones
- `docs/conventions/product-variants.md` §3 (snapshots inmutables) §2 (no stock en pedido).
- `docs/conventions/business-flows.md`.
### NO existe — no referenciar
- No asumir métodos de PedidoService sin verificarlos con `grep`/`read`.

## Criterios de aceptación
- [ ] Convertir una cotización con línea dinámica → el pedido conserva tipo_producto_id + snapshots + sku idénticos, producto_id NULL.
- [ ] Pedido legacy (con producto_id) sigue funcionando.
- [ ] Pedido NO descuenta stock.
- [ ] Snapshots no se recalculan.

## QA manual
1. Cotización dinámica aprobada → convertir a pedido → revisar `detalle_pedido`: snapshots/sku copiados, producto_id NULL.

## Nota de Completitud
*(Llenar al terminar)*
