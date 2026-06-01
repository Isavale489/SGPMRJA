# Catálogo de Productos — Variantes y Atributos

> Arquitectura del catálogo tras el refactor a variantes/atributos. Decisiones de diseño que NO son derivables del código.

## Modelo de datos

```
producto
  ├── tipo_producto_id      → tipo_producto (prefijo, precio_confeccion)
  ├── insumo_tela_id        → insumo (filtrado por tipo='Tela')
  ├── codigo (SKU)           → PREFIJO-TELA-VALORES-NNN
  └── pivot: producto_atributo_valor → atributo_valor

tipo_producto
  └── pivot: tipo_producto_atributo → atributo (con orden)

atributo
  └── atributo_valor (codigo único por atributo)

detalle_cotizacion / detalle_pedido
  ├── producto_id           → producto
  ├── tela_snapshot         → JSON inmutable
  └── atributos_snapshot    → JSON inmutable
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
- Las columnas `tela_snapshot` y `atributos_snapshot` se llenan al **crear** el detalle vía `ProductoService::buildSnapshotsParaDetalle()`.
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
- El endpoint `GET /productos-resolver-variante` resuelve la combinación contra el catálogo.

## Productos legacy (pre-refactor)

13 productos preexistentes quedaron con `insumo_tela_id=NULL` y `atributos_snapshot=NULL`. **Siguen funcionando**. Cuando el admin los re-edite, quedan integrados al nuevo modelo. **No hace falta backfill urgente.**

## Documento técnico para profesor

`tareas/refactor_variantes_atributos.html` — explica el refactor con diagramas y casos de uso. Sirve también como referencia rápida para devs nuevos.
