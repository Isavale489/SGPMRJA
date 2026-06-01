# Arquitectura de Base de Datos

> Estado actual del esquema tras la auditoría completa de BD. Convenciones, normalizaciones y patrones a respetar en migraciones futuras.

## Convenciones de esquema

### Nombres de tablas
- **Singular** (`pedido`, `cotizacion`, `orden_produccion`, `detalle_pedido`).
- Snake_case.
- Sin prefijos del tipo `tbl_` o `t_`.

### Foreign keys
- Sufijo `_id` por convención Laravel: `pedido_id`, `empleado_id`, `persona_id`.
- Acción default: `restrict` (NO `cascade`) salvo casos justificados como `user` o pivots de catálogo.
- Índices compuestos donde el query lo amerita.

### SoftDeletes
Aplicado en:
- `user`
- Catálogos maestros: `banco`, `color`, `talla`, `tipo_producto`, `logo`, `bordado_ubicacion`
- Catálogos transaccionales: `empleado`, `cliente`, `proveedor`, `insumo`, `producto`, `cotizacion`, `pedido`, `orden_produccion`
- Cualquier entidad con UNIQUE constraint que necesite "borrado lógico" sin perder histórico

### Ledgers inmutables
- `movimiento_insumo` — registro de entradas y salidas de stock, nunca se edita.
- `pago_pedido` — pagos asociados a un pedido (se sincroniza con delete+recreate vía `PedidoService::syncPagos()`).
- `recovery_attempt` — bitácora de intentos de recuperación de contraseña.

## Tabla `persona` unificada

La tabla `persona` es el **núcleo de identidad** del sistema. Cliente, Empleado y Proveedor son **roles** sobre una persona, no entidades independientes con datos duplicados.

```
persona (id, documento_identidad UNIQUE, nombre, apellido, ...)
   ├── cliente (persona_id FK)
   ├── empleado (persona_id FK)
   └── proveedor (persona_id FK)
```

**Implicación**: cualquier formulario que pida datos de una persona debe usar el patrón [`persona-unified-search.md`](persona-unified-search.md) en vez de duplicar registros.

Proveedores jurídicos también usan `persona` (mismo patrón que Clientes). Los campos directos legacy en `proveedor` fueron eliminados.

## Pagos normalizados

Tabla `pago_pedido`:
```
id, pedido_id (FK), metodo (ENUM: efectivo|transferencia|pago_movil),
monto, banco_id (FK nullable), referencia (string nullable), timestamps
```

- Modelo: `App\Models\PagoPedido`.
- Relación: `Pedido->pagos()`.
- Sincronización: `PedidoService::syncPagos()` con patrón delete+recreate (no UPDATE incremental).
- `pedido.abono` se auto-calcula vía `Pedido::recalcularAbono()` y NO se escribe a mano.

**NO añadir** columnas planas tipo `pedido.monto_efectivo` o `pedido.banco_transferencia` — todas las dimensiones de pago viven en `pago_pedido`.

## Logos en bordados

`detalle_*_bordado` (cotizacion + pedido) tiene `logo_id` FK (no texto libre):
- Reemplaza al viejo `nombre_logo` (eliminado).
- `nombre_logo_aplicado` se preserva como **snapshot histórico** — congela el nombre del logo al momento del bordado, para que documentos antiguos no se afecten si el catálogo cambia.
- Accessor `getNombreLogoAttribute()` computa dinámicamente desde la relación.
- Frontend usa `data-logo-id` + `seleccionarLogo(logoId, logoName)`.

## Variantes de producto

Ver [`product-variants.md`](product-variants.md) para arquitectura detallada de `producto`, `tipo_producto_atributo`, `producto_atributo_valor`, snapshots de cotización/pedido, y reglas de SoftDelete + UNIQUE.

## Migraciones — convenciones

- Toda nueva migración debe tener `down()` reversible.
- Cambios destructivos (drop column, drop table) requieren backup previo.
- Para renombrar columnas con datos en producción: migración de copia → backfill → drop, no `renameColumn` directo si la columna tiene FK.
- ENUMs: definir en la migración, no en el modelo.

## Dumps de referencia

Los dumps de la BD post-auditoría viven en `database/`. Antes de cambios estructurales grandes, generar dump nuevo.
