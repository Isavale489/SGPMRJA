# Flujos de Transacciones

> Mapa de los módulos transaccionales del sistema, sus máquinas de estado y cómo se conectan. **Leer antes de diseñar transacciones nuevas** para no romper las reglas de negocio existentes.

## Módulos transaccionales (7)

### 1. Cotización
- **Estados**: `Pendiente → Aprobada → Convertida` | `Pendiente/Aprobada → Vencida` | `→ Cancelada`
- Vencimiento automático al cargar la tabla si `fecha_validez` pasó.
- Precio = precio_base + recargos de bordado.
- Solo `Aprobada` puede convertirse a Pedido (con pessimistic locking).

### 2. Pedido
- **Estados**: `Pendiente → Cancelado` | `Pendiente → Completado` (vía Orden Producción)
- Puede crearse desde Cotización Aprobada o manualmente.
- Soporta pagos múltiples (tabla `pago_pedido`); `pedido.abono` = suma calculada.
- Bloqueado para editar/eliminar si está `Completado` o `Cancelado`.

### 3. Orden de Producción
- **Estados**: `Pendiente → En Proceso → Finalizado` | `→ Cancelado`
- `En Proceso` se activa con el primer registro de Producción Diaria.
- `Finalizado` se activa automáticamente cuando `cantidad_producida >= cantidad_solicitada`.
- Solo eliminable en `Pendiente`.
- Requiere insumos estimados al crear.

### 4. Producción Diaria
- Sin estados propios — avanza el acumulado de la Orden.
- Solo empleados del departamento Producción.
- No acepta cantidad mayor a unidades restantes.
- Editar/eliminar revierte el acumulado correctamente.

### 5. Control de Insumos (`DetalleOrdenInsumo`)
- **Aquí es donde se descuenta el stock.**
- `cantidad_utilizada` no puede superar `cantidad_estimada`.
- Cada delta de `cantidad_utilizada` → baja `Insumo.stock_actual` + crea `MovimientoInsumo` tipo Salida.
- No eliminable si `cantidad_utilizada > 0`.

### 6. Movimiento de Inventario
- **Ledger inmutable**: `Entrada` (manual/compra) y `Salida` (automática desde DetalleOrdenInsumo).
- Fuente única de verdad del stock.
- Guarda `stock_antes`, `stock_después`, motivo, usuario, timestamp.
- Módulo Alertas lista insumos donde `stock_actual <= stock_minimo`.

### 7. Pagos (`PagoPedido`)
- Sin estados — ledger acumulativo por pedido.
- Métodos: `efectivo`, `transferencia`, `pago_movil`.
- Sincronización completa en cada edición del pedido (patrón delete+recreate vía `PedidoService::syncPagos()`).
- No afecta stock ni producción.

## Flujo maestro

```
COTIZACIÓN (Aprobada)
    │ convertirAPedido()
    ▼
PEDIDO ──── PAGOS (abono acumulado)
    │
    │ crear OrdenProduccion
    ▼
ORDEN PRODUCCIÓN ──── INSUMOS ESTIMADOS
    │                       │ actualizar cantidad_utilizada
    │                       ▼
    │                 MOVIMIENTO INSUMO (stock baja)
    │
    │ registrar día a día
    ▼
PRODUCCIÓN DIARIA
    │ acumula hasta cantidad_solicitada
    ▼
ORDEN → Finalizado
```

## Reglas invariantes

1. **Cotización es referencial**: NO descuenta stock. Si la cotización se rechaza, no debe quedar inventario fantasma reservado.
2. **Stock baja SOLO en Producción**: específicamente en `DetalleOrdenInsumo` al actualizar `cantidad_utilizada`. Cualquier feature nuevo que toque cotización/pedido NO debe llamar a `MovimientoInsumo` ni decrementar stock.
3. **Pagos son sincronización completa**: editar un pedido borra y recrea sus pagos. No hay "actualización parcial" — toda la lista se reescribe.
4. **Producción Diaria es aditiva**: cada registro suma al acumulado de la orden, no lo reemplaza.
5. **Movimiento de Inventario es inmutable**: una vez creado un movimiento, no se edita. Para corregir, crear otro movimiento compensatorio.

## Módulos placeholder (sin implementar)

- **Control de Calidad** — pendiente.
- **Garantías** — pendiente.

Ambos están en el sidebar como placeholders pero sin implementación. Son candidatos prioritarios para próximos sprints SDD.

## Cómo aplicar este documento

Antes de diseñar un módulo transaccional nuevo (o modificar uno existente):
1. Identificar a qué bloque del flujo maestro pertenece.
2. Verificar que las reglas invariantes se respeten.
3. Si el módulo toca stock, debe ir vía `MovimientoInsumo` — no escribir `Insumo.stock_actual` directamente.
4. Si el módulo tiene máquina de estado, documentarla en el spec (sección 2 del template SDD).
