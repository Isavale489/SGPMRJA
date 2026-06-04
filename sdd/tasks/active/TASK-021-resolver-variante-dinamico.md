# TASK-021: Resolución dinámica de variante (`ProductoService` + `resolverVariante`)

**Feature**: FEAT-003 — variantes-dinamicas
**Spec**: `sdd/specs/variantes-dinamicas.spec.md`
**Status**: pending
**Priority**: high
**Esfuerzo estimado**: M
**Depends-on**: TASK-019
**Assigned-to**: unassigned

---

## Contexto
Núcleo del refactor: resolver una combinación (tipo + tela + atributos) **sin exigir un `Producto`**. Hoy `resolverVariante` busca un Producto match y si no existe responde "Crea primero el producto". Implementa el Módulo 3.

## Scope
- Añadir `ProductoService::buildSnapshotsDesdeTipo(TipoProducto $tipo, ?Insumo $tela, array $valoresIds): array` → devuelve `tela_snapshot` + `atributos_snapshot` + `sku` (calculado con `generarCodigo()`) + `precio_sugerido` (`sugerirPrecio()`), sin tocar BD.
- Modificar `ProductoController::resolverVariante`: si existe Producto match, seguir devolviéndolo (compat); si **no**, devolver `found: true` con la variante **calculada** (sku, precio, snapshots, tipo_producto_id, tela_id, valores) en vez de `found: false`.
- Validar que el tipo tenga la tela dentro de `tipo_producto_tela` (TASK-019) si `requiere_tela`.

**NO está en alcance**: persistir la línea (TASK-023/025), JS del selector (TASK-024).

## Archivos a crear / modificar
| Archivo | Acción | Descripción |
|---|---|---|
| `app/Services/ProductoService.php` | MODIFY | `buildSnapshotsDesdeTipo()` |
| `app/Http/Controllers/ProductoController.php` | MODIFY | `resolverVariante` calcula sin exigir Producto |

## Codebase Contract (Anti-Alucinación)
### Firmas existentes a reusar
```php
// app/Services/ProductoService.php
//   generarCodigo(TipoProducto $tipo, ?Insumo $tela, array $valoresOrdenados): string   (:28)
//   sugerirPrecio(TipoProducto $tipo, ?Insumo $tela): float                              (:62)
//   ordenarValoresParaTipo(TipoProducto $tipo, array $valoresIds)                        (:77)
//   buildAtributosSnapshot($valoresOrdenados): array                                     (:100)
//   buildSnapshotsParaDetalle(Producto $producto): array  (:207) ← referencia de forma del snapshot
// app/Http/Controllers/ProductoController.php
//   resolverVariante(Request): valida tipo_producto_id|required, insumo_tela_id|nullable,
//     atributo_valor_ids.*; hoy: Producto::where(...)->first(match) o found=false
// app/Models/TipoProducto.php — requiere_tela (bool), telas() (TASK-019)
```
### Convenciones
- `docs/conventions/sku-format.md` — fórmula SKU.
- `docs/conventions/product-variants.md` §3 — forma de los snapshots (inmutables).
### NO existe — no referenciar
- ~~`ProductoService::buildSnapshotsDesdeTipo()`~~ — se crea en ESTA task.
- ~~tabla `tela`~~ — usar `Insumo::find()` (tipo Tela).

## Criterios de aceptación
- [ ] `resolverVariante` con una combinación inexistente como Producto devuelve `found:true` + sku + precio + snapshots calculados.
- [ ] Con combinación que SÍ existe como Producto, sigue devolviendo el producto (sin regresión).
- [ ] `buildSnapshotsDesdeTipo` no escribe en BD y su salida tiene la misma forma que `buildSnapshotsParaDetalle`.

## QA manual
1. Tinker/HTTP: `GET productos-resolver-variante?tipo_producto_id=<franela>&insumo_tela_id=<algodon>&atributo_valor_ids[]=<manga>` con una combinación que NO existe como producto → respuesta con sku + precio_sugerido + snapshots.

## Nota de Completitud
*(Llenar al terminar)*
