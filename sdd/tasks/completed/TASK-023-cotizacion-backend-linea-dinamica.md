# TASK-023: Cotización — guardar línea dinámica sin `producto_id`

**Feature**: FEAT-003 — variantes-dinamicas
**Spec**: `sdd/specs/variantes-dinamicas.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: M
**Depends-on**: TASK-020, TASK-021
**Assigned-to**: emmanuel

---

## Contexto
Backend de cotización: aceptar y persistir líneas configuradas dinámicamente (tipo+tela+atributos) sin `producto_id`. Parte de cotización del Módulo 4.

## Scope
- En `CotizacionController` store y update: `productos.*.producto_id` deja de ser `required` → `nullable|exists:producto,id`; aceptar `productos.*.tipo_producto_id` (required cuando no hay producto_id) + tela + atributo_valor_ids.
- Al crear cada `DetalleCotizacion`: si viene producto_id (legacy/compat) usar el flujo actual; si no, guardar `tipo_producto_id`, snapshots y SKU vía `ProductoService::buildSnapshotsDesdeTipo()` (TASK-021), `precio_unitario` calculado.
- Mantener snapshots **inmutables** (no recalcular en update de líneas existentes).

**NO está en alcance**: JS del selector (TASK-024), pedido (TASK-025), órdenes (TASK-026).

## Archivos a crear / modificar
| Archivo | Acción | Descripción |
|---|---|---|
| `app/Http/Controllers/CotizacionController.php` | MODIFY | validación + creación de detalle dinámico |

## Codebase Contract (Anti-Alucinación)
### Firmas existentes
```php
// app/Http/Controllers/CotizacionController.php
//   :166 y :272 'productos.*.producto_id' => 'required|exists:producto,id'  ← cambiar a nullable
//   :192/:300 mensajes producto_id.required/exists
// app/Services/ProductoService::buildSnapshotsDesdeTipo(TipoProducto,?Insumo,array): array  (TASK-021)
// app/Models/DetalleCotizacion — fillable incl. tipo_producto_id (TASK-020), tela_snapshot, atributos_snapshot,
//   color_id, talla_id, precio_unitario, cantidad, lleva_bordado
```
### Convenciones
- `docs/conventions/product-variants.md` §2 (NO descontar stock en cotización) §3 (snapshots inmutables).
- Reglas de serialización: `lleva_bordado`/`es_personalizada` como entero 1/0 (ver memoria pedidos-wizard / commit 960f3c9).
### NO existe — no referenciar
- ~~`buildSnapshotsDesdeTipo`~~ debe existir (TASK-021) antes de tomar esta task.

## Criterios de aceptación
- [ ] Guardar cotización con una línea sin `producto_id` (solo tipo+tela+atributos) → persiste con snapshots + SKU + `producto_id` NULL.
- [ ] Cotización legacy (con producto_id) sigue guardando igual.
- [ ] NO se generan `MovimientoInsumo` (stock intacto).
- [ ] Editar la cotización no altera snapshots de líneas ya guardadas.

## QA manual
1. Crear cotización con franela dinámica (tela Algodón + manga corta) → guardar → revisar `detalle_cotizacion`: producto_id NULL, tipo_producto_id + snapshots + sku presentes.
2. Verificar stock sin cambios.

## Nota de Completitud

**Completado por**: emmanuel
**Fecha**: 2026-06-03
**Commits**: (en rama `feat/variantes-dinamicas`)
**Notas**:
- `CotizacionController` store + update: `producto_id` ahora `nullable|required_without:tipo_producto_id`;
  nuevos campos `tipo_producto_id` (required_without producto_id), `insumo_tela_id`, `atributo_valor_ids[]`.
  Mensajes `required_without` agregados. Verificado: required_without valida **por fila**.
- `CotizacionService`: nuevo helper `resolverVarianteLinea($item)` que resuelve legacy
  (`producto_id` → `buildSnapshotsParaDetalle`) o dinámico (`tipo_producto_id`+tela+atributos →
  `buildSnapshotsDesdeTipo`). `calcularTotal` y `crearDetalles` lo usan; el detalle guarda
  `tipo_producto_id` + snapshots. Imports `TipoProducto`/`Insumo` agregados.
- QA (rollback, con Auth::login): cotización dinámica → detalle con `producto_id=NULL`,
  `tipo_producto_id` correcto, snapshots correctos, total 174 (=58×3). **MovimientoInsumo 37→37**
  (no descuenta stock, regla intacta).

**Desviaciones del spec**:
1. La persistencia del SKU en el snapshot (decisión §8) NO se implementó: no existe columna para el
   SKU y embeberlo en `atributos_snapshot` rompería su forma (los PDFs la iteran). El SKU es
   **recomputable** desde tipo+tela+atributos (todo guardado). Si se quiere un SKU congelado, es un
   follow-up de 1 columna (`sku_snapshot`) — pendiente de decisión de Emmanuel.
2. Telas se integró sin endpoints separados (ver TASK-022).
