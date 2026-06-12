# Catálogo de Productos — Variantes y Atributos

> Arquitectura del catálogo. Decisiones de diseño que NO son derivables del código.
> **Actualizado en FEAT-003 (variantes dinámicas)** — ver la sección dedicada al final.

## Modelo de datos

```
tipo_producto  ← UNIDAD DE CATÁLOGO (prefijo, precio_confeccion, requiere_tela, consumo_tela_por_unidad)
  ├── pivot: tipo_producto_atributo → atributo (con orden)
  ├── pivot: tipo_producto_tela     → insumo (tipo='Tela')   [FEAT-003: telas permitidas]
  └── pivot: tipo_producto_insumo   → insumo (BOM, cantidad_estimada)

atributo
  └── atributo_valor (codigo único por atributo)

producto  ← SKU concreto (legacy / explícito). Ya NO se crea uno por combinación.
  ├── tipo_producto_id      → tipo_producto
  ├── insumo_tela_id        → insumo (tipo='Tela')
  ├── codigo (SKU)           → PREFIJO-TELA-VALORES-NNN
  └── pivot: producto_atributo_valor → atributo_valor

detalle_cotizacion / detalle_pedido  ← la línea se autodescribe (FEAT-003)
  ├── producto_id           → producto    (NULLABLE — null en variantes dinámicas)
  ├── tipo_producto_id      → tipo_producto  [FEAT-003]
  ├── tela_snapshot         → JSON inmutable
  ├── atributos_snapshot    → JSON inmutable
  └── sku_snapshot          → string (SKU congelado)  [FEAT-003]

orden_produccion
  ├── detalle_pedido_id     → detalle_pedido (fuente de qué fabricar)
  └── producto_id           → producto  (NULLABLE — lee del snapshot si es dinámico)  [FEAT-003]
```

## Decisiones que NO son obvias mirando el código

### 1. Tela = `insumo` filtrado por `tipo='Tela'` (no tabla separada)

**Por qué**: crear una tabla `tela` aparte habría duplicado catálogos. El insumo ya tenía `costo_unitario`, `unidad_medida` y `stock`; solo faltaba `codigo` para participar del SKU.

**Cómo aplicar**: si alguien sugiere crear una tabla `tela` separada, **no hacerlo**. Usar `Insumo::where('tipo','Tela')` o el scope `Insumo::telas()`.

### 2. Cotización es referencial, NO descuenta stock

**Por qué**: una cotización rechazada no debe dejar inventario fantasma reservado. Solo la orden de producción descuenta `insumo.stock_actual` vía `MovimientoInsumo`.

**Cómo aplicar**: cualquier feature nuevo que toque cotización/pedido **NO** debe llamar a `MovimientoInsumo` ni decrementar stock. Solo el módulo de producción lo hace.

### 3. Snapshots inmutables en detalles

**Por qué**: si el catálogo cambia entre cotizar y producir, los documentos viejos deben mostrar exactamente lo que el cliente firmó (mismo patrón que `nombre_logo_aplicado` en bordados).

**Cómo aplicar**:
- Las columnas `tela_snapshot`, `atributos_snapshot` y `sku_snapshot` se llenan al **crear** el detalle:
  desde un Producto vía `ProductoService::buildSnapshotsParaDetalle()` (legacy), o desde tipo+tela+atributos
  vía `ProductoService::buildSnapshotsDesdeTipo()` (dinámico, FEAT-003).
- **NO se actualizan** en updates posteriores.
- Los PDFs leen del snapshot, no del catálogo vivo.

### 4. `producto.modelo` fue eliminado

**Por qué**: era texto libre. Reemplazado por SKU determinístico + accessor `nombre_completo` que arma "Tipo Tela ValoresAtributos" desde la relación + `atributos_snapshot`.

**Cómo aplicar**: **NO agregar de vuelta** `producto.modelo`. Si necesitan un alias comercial, discutirlo antes de añadir cualquier campo nuevo (probablemente sería un campo opcional, no devolver el viejo).

## Generación de SKU

Ver [`sku-format.md`](sku-format.md) para fórmula y reglas.

Implementación: `app/Services/ProductoService::generarCodigo()` con `withTrashed()` y loop defensivo (ver [`softdeletes-unique.md`](softdeletes-unique.md)).

## Inmutabilidad de códigos

Los códigos que forman parte del SKU (`insumo.codigo`, `atributo.codigo`, `atributo_valor.codigo`, `tipo_producto.prefijo`) son **readonly** después de la primera asignación. Ver [`code-immutability.md`](code-immutability.md).

## Wizard de cotizaciones

Funcionalidad no trivial que vive en `resources/views/admin/cotizaciones/scripts/main.blade.php`:

### Botón "Cambiar variante" en el configurador
1. Cierra el configurador.
2. Abre el selector de variante con preselección + `editContexto`.
3. Al confirmar, reabre el configurador conservando color/tallas/precio.
4. El patcher de save usa `productoIdOriginal` para:
   - Borrar las cards del SKU viejo.
   - Migrar los bordados del `groupKey` viejo al nuevo (ver `cotGroupBordadosState`).

### Selector de variante (paso 2 del wizard)
- `vsAbrir(tipoId, opts)` con preselección y `editContexto`.
- Las telas ofrecidas salen de `tipo.telas` (`tipo_producto_tela`), NO de productos existentes.
- `GET /productos-resolver-variante` resuelve la combinación: si existe un Producto devuelve
  `dynamic:false` (legacy); si no, **calcula la variante** (`dynamic:true`) con SKU/precio/snapshots
  sin exigir un Producto. Ver sección FEAT-003.

## Productos legacy (pre-refactor)

Los productos preexistentes (con o sin `insumo_tela_id`) **siguen funcionando** sin backfill. Las
líneas de documentos viejos referencian `producto_id`; las nuevas variantes dinámicas usan
`tipo_producto_id` + snapshots (ver FEAT-003). La lectura tolera ambos casos.

## FEAT-003 — Variantes dinámicas (el catálogo es el Tipo)

**Cambio de modelo (2026-06-03):** la unidad de catálogo es **`tipo_producto`**, no el Producto.
Ya **NO se crea una fila `producto` por combinación** (tipo+tela+atributos). Las combinaciones se
configuran al vuelo al cotizar y se congelan como snapshots en la línea.

### Decisiones clave
1. **Telas por tipo**: `tipo_producto_tela` define qué telas (`Insumo` tipo Tela) puede usar cada
   tipo. Se gestiona desde el editor del Tipo ("Gestionar Tipos" en `/productos`). El selector de
   variante de la cotización ofrece SOLO estas telas.
2. **Resolución sin Producto**: `ProductoService::buildSnapshotsDesdeTipo(tipo, ?tela, valoresIds)`
   arma `tela_snapshot` + `atributos_snapshot` + `sku` (`generarCodigo`) + `precio_sugerido`
   (`sugerirPrecio`) **sin tocar la BD**. `resolverVariante` lo usa cuando no hay Producto match.
3. **Línea autodescriptiva**: `detalle_cotizacion`/`detalle_pedido` tienen `producto_id` **nullable**
   + `tipo_producto_id` + `sku_snapshot`. En `CotizacionService`/`PedidoService` el helper
   `resolverVarianteLinea($item)` resuelve legacy (por `producto_id`) o dinámico (por
   `tipo_producto_id`). La conversión cotización→pedido copia `tipo_producto_id` + `sku_snapshot`.
4. **Orden de producción** (`producto_id` nullable): fabrica leyendo el snapshot del
   `detalle_pedido` (relación `detallePedido`). `OrdenProduccion::nombre_producto` arma el nombre
   legible (legacy o desde snapshot). La **tela se consume** vía los insumos de la orden (prefill en
   `pedidosDisponibles` con `consumo_tela_por_unidad × unidades`), igual que para productos legacy.
5. **SKU congelado y recomputable**: se guarda en `sku_snapshot` para trazabilidad estable, y además
   es recomputable desde tipo+tela+atributos. No se persiste dentro de los snapshots JSON (rompería
   su forma; los PDFs los iteran).
6. **Frontend (cotización)**: enfoque "producto virtual" — al confirmar una variante dinámica se
   inyecta un producto sintético `{id:'vN', _dynamic, _variante}` en `products` para que el pipeline
   (configurador, carrito, tabla) lo trate por id; en el submit, `addProductItem` emite
   `tipo_producto_id`/`insumo_tela_id`/`atributo_valor_ids[]` en vez de `producto_id`.
7. **Página `/productos`**: muestra los **SKUs individuales** (que ya no se inflan). El catálogo por
   tipos vive en el manager "Gestionar Tipos".

### Reglas que se mantienen
- Tela sigue siendo `Insumo` tipo Tela (NO tabla `tela`).
- Cotización/pedido NO descuentan stock (solo producción, vía insumos de la orden).
- Snapshots inmutables tras crear el detalle.
- `producto.modelo` sigue eliminado.

### Limitación conocida
- **Edición** de una cotización/pedido con líneas dinámicas: el loader de edición aún no reconstruye
  el producto virtual desde los snapshots guardados (crear/cotizar sí funciona). Follow-up pendiente.

## Documento técnico para profesor

`tareas/refactor_variantes_atributos.html` — explica el refactor con diagramas y casos de uso. Sirve también como referencia rápida para devs nuevos.
