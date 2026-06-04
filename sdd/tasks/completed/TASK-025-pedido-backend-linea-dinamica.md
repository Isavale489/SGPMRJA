# TASK-025: Pedido — guardar línea dinámica sin `producto_id` + conversión desde cotización

**Feature**: FEAT-003 — variantes-dinamicas
**Spec**: `sdd/specs/variantes-dinamicas.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: M
**Depends-on**: TASK-020, TASK-021
**Assigned-to**: emmanuel

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

**Completado por**: emmanuel
**Fecha**: 2026-06-03
**Commits**: (en rama `feat/variantes-dinamicas`)
**Notas**:
- `StorePedidoRequest` + `UpdatePedidoRequest`: `producto_id` →
  `nullable|required_without:tipo_producto_id`; nuevos `tipo_producto_id`, `insumo_tela_id`,
  `atributo_valor_ids[]`.
- `PedidoService`: helper `resolverVarianteLinea()` (idéntico al de cotización) usado por
  `calcularTotal` y `crearDetalles`; el detalle guarda `tipo_producto_id` + snapshots + `sku_snapshot`.
  Imports `TipoProducto`/`Insumo`.
- La **conversión cotización→pedido** (`CotizacionService::convertirAPedido`) ya copiaba
  `tipo_producto_id` + `sku_snapshot` (hecho en el addendum de TASK-023).
- QA (rollback): pedido directo dinámico → total 232 (=58×4), `producto_id` NULL, `tipo_producto_id`,
  `sku_snapshot` `FRN-PIQ-C-CLA-001`; **MovimientoInsumo 37→37** (no descuenta stock).

**Desviaciones del spec**: ninguna. El frontend del wizard de pedidos hereda el mismo enfoque que
cotización (TASK-024); su QA en navegador queda para el QA final junto con edición dinámica.
