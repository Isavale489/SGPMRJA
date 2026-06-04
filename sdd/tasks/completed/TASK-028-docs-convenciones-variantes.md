# TASK-028: Actualizar convenciones al nuevo modelo de variantes

**Feature**: FEAT-003 — variantes-dinamicas
**Spec**: `sdd/specs/variantes-dinamicas.spec.md`
**Status**: done
**Priority**: medium
**Esfuerzo estimado**: S
**Depends-on**: TASK-019, TASK-020, TASK-021, TASK-022, TASK-023, TASK-024, TASK-025, TASK-026, TASK-027
**Assigned-to**: emmanuel

---

## Contexto
Tras el refactor, las convenciones canónicas deben reflejar que **el catálogo es el Tipo de Producto** y que las combinaciones son dinámicas (no una fila `producto` por combinación). Implementa el Módulo 7. Se hace al final, cuando el comportamiento ya está implementado.

## Scope
- Actualizar `docs/conventions/product-variants.md`:
  - Catálogo = Tipo; `tipo_producto_tela` define telas permitidas.
  - La línea se autodescribe por snapshots + `tipo_producto_id`; `producto_id` nullable/legacy.
  - Orden de Producción fabrica desde el snapshot del `detalle_pedido` (Opción 1).
  - SKU calculado y persistido en el snapshot; no hay fila `producto` por combinación.
- Actualizar `docs/conventions/sku-format.md` si cambia cómo/ cuándo se persiste el SKU.
- Nota de migración legacy (conviven sin backfill).

**NO está en alcance**: cambios de código (van en TASK-019..027).

## Archivos a crear / modificar
| Archivo | Acción | Descripción |
|---|---|---|
| `docs/conventions/product-variants.md` | MODIFY | nuevo modelo |
| `docs/conventions/sku-format.md` | MODIFY | persistencia del SKU en snapshot |
| `docs/conventions/README.md` | MODIFY (si aplica) | índice |

## Codebase Contract (Anti-Alucinación)
### Referencias
```
docs/conventions/product-variants.md — documento ACTUAL (describe el modelo viejo a reemplazar)
docs/conventions/sku-format.md — fórmula SKU
docs/conventions/README.md — índice de convenciones
```
### NO existe — no referenciar
- No documentar comportamiento que no haya quedado realmente implementado en TASK-019..027 (verificar contra el código final).

## Criterios de aceptación
- [ ] `product-variants.md` describe el modelo nuevo sin contradicciones con el código final.
- [ ] `sku-format.md` refleja la persistencia del SKU en el snapshot.
- [ ] Sin referencias muertas al "Producto por combinación" como unidad de catálogo.

## QA manual
1. Leer ambos docs contra el código mergeado; verificar que no contradigan el comportamiento real.

## Nota de Completitud

**Completado por**: emmanuel
**Fecha**: 2026-06-03
**Commits**: (en rama `feat/variantes-dinamicas`)
**Notas**:
- `docs/conventions/product-variants.md`: modelo de datos actualizado (tipo_producto como unidad de
  catálogo, `tipo_producto_tela`, `producto_id` nullable + `tipo_producto_id` + `sku_snapshot` en
  detalles, orden con producto_id nullable). Nueva sección **"FEAT-003 — Variantes dinámicas"** con
  las 7 decisiones clave, reglas que se mantienen y la limitación conocida (edición dinámica).
  Actualizadas las secciones de snapshots y del wizard.
- `docs/conventions/sku-format.md`: `resolver-variante` documentado con `dynamic:true/false`; nueva
  sección "SKU en variantes dinámicas" (calculado + congelado en `sku_snapshot` + recomputable; nota
  del secuencial referencial).

**Desviaciones del spec**: ninguna. Docs alineadas con el código mergeado en TASK-019..027.
