# TASK-020: `producto_id` nullable + `tipo_producto_id` en detalle_cotizacion/detalle_pedido

**Feature**: FEAT-003 — variantes-dinamicas
**Spec**: `sdd/specs/variantes-dinamicas.spec.md`
**Status**: pending
**Priority**: high
**Esfuerzo estimado**: S
**Depends-on**: none
**Assigned-to**: unassigned

---

## Contexto
Permite que una línea de cotización/pedido se autodescriba por snapshots sin requerir un `Producto`. Implementa la parte de datos del Módulo 4. La línea ya tiene `tela_snapshot`/`atributos_snapshot`; falta soltar la obligatoriedad de `producto_id` y guardar el tipo directo.

## Scope
- Migración: en `detalle_cotizacion` y `detalle_pedido`, `producto_id` → **nullable**; añadir `tipo_producto_id` (nullable, FK a `tipo_producto`, `nullOnDelete`).
- Añadir `tipo_producto_id` al `$fillable` de `DetalleCotizacion` y `DetallePedido`; relación `tipoProducto(): BelongsTo`.

**NO está en alcance**: lógica de guardado en controllers (TASK-023/025), órdenes (TASK-026).

## Archivos a crear / modificar
| Archivo | Acción | Descripción |
|---|---|---|
| `database/migrations/<fecha>_detalle_nullable_producto_add_tipo.php` | CREATE | `change()` + columna |
| `app/Models/DetalleCotizacion.php` | MODIFY | fillable + `tipoProducto()` |
| `app/Models/DetallePedido.php` | MODIFY | fillable + `tipoProducto()` |

## Codebase Contract (Anti-Alucinación)
### Firmas existentes
```php
// app/Models/DetalleCotizacion.php:16 — fillable: cotizacion_id, producto_id, tela_snapshot, atributos_snapshot,
//   cantidad, descripcion, lleva_bordado, color_id, talla_id, precio_unitario
//   casts: tela_snapshot=>array, atributos_snapshot=>array
//   columnas BD detalle_cotizacion: id, cotizacion_id, producto_id, tela_snapshot, atributos_snapshot,
//     cantidad, descripcion, lleva_bordado, color_id, talla_id, precio_unitario, created_at, updated_at
// app/Models/DetallePedido.php — análogo (tiene tela_snapshot/atributos_snapshot, ver migración
//   2026_05_07_100008_add_snapshots_to_detalle_pedido.php)
```
### Notas
- `change()` requiere `doctrine/dbal`. Si no está instalado, alternativa: SQL crudo en la migración o instalar dbal. Verificar antes.
- Patrón FK nullable: `2026_05_27_220125_op_redesign_orden_produccion_empleado_detalle.php` usa `nullOnDelete()`.
### NO existe — no referenciar
- ~~`detalle_cotizacion.tipo_producto_id`~~ / ~~`detalle_pedido.tipo_producto_id`~~ — se crean en ESTA task.

## Criterios de aceptación
- [ ] `php artisan migrate` limpio; `producto_id` acepta NULL en ambas tablas; existe `tipo_producto_id`.
- [ ] `DetalleCotizacion::create([... 'producto_id'=>null, 'tipo_producto_id'=>x ...])` funciona.
- [ ] Líneas legacy (con producto_id) intactas.

## QA manual
1. `php artisan migrate`
2. Tinker: crear un detalle con `producto_id=null, tipo_producto_id=<id>` → persiste; `->tipoProducto` resuelve.

## Nota de Completitud
*(Llenar al terminar)*
