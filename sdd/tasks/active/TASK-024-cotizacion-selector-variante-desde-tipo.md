# TASK-024: Cotización — selector de variante lee telas del tipo (JS)

**Feature**: FEAT-003 — variantes-dinamicas
**Spec**: `sdd/specs/variantes-dinamicas.spec.md`
**Status**: pending
**Priority**: high
**Esfuerzo estimado**: M
**Depends-on**: TASK-021
**Assigned-to**: unassigned

---

## Contexto
Frontend del paso de productos: el selector de variante debe ofrecer las telas del **tipo** (`tipo_producto_tela`) y resolver la combinación dinámicamente, sin el mensaje "Crea primero el producto". Parte UI del Módulo 3.

## Scope
- En `cotizaciones/scripts/main.blade.php`: `telasDelTipo()` deja de derivar telas de productos existentes y usa las telas del tipo (expuestas por el backend: incluir las telas del tipo en el payload de productos del catálogo, o un endpoint).
- Ajustar el flujo `resolver-variante`: con la respuesta calculada (TASK-021), tomar sku/precio/snapshots y permitir "Configurar" aunque no exista Producto.
- Quitar el manejo de `found:false → "Crea primero el producto"`.

**NO está en alcance**: persistencia (TASK-023), pedido (TASK-025).

## Archivos a crear / modificar
| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/cotizaciones/scripts/main.blade.php` | MODIFY | `telasDelTipo`, flujo resolver-variante |
| `app/Http/Controllers/CotizacionController.php` (o ProductoController) | MODIFY (mínimo) | exponer telas del tipo al front si hace falta |

## Codebase Contract (Anti-Alucinación)
### Referencias JS verificadas
```
resources/views/admin/cotizaciones/scripts/main.blade.php
  :3896 telasDelTipo(productos) — hoy: byId desde p.tela de productos existentes  ← cambiar
  :3974 $.getJSON("{{ route('productos.resolver-variante') }}", {tipo_producto_id, insumo_tela_id, atributo_valor_ids})
  vsState (selector variante), :3963 requiereTela = vsState.tipo.requiere_tela
  cfgState / renderColorGrid (configurador)
```
### Convenciones
- `docs/conventions/product-variants.md` — wizard de cotizaciones (selector + "Cambiar variante").
- `docs/conventions/wizard-pattern.md`.
### NO existe — no referenciar
- El endpoint `resolver-variante` ya no debe devolver `found:false` para combinaciones nuevas (depende de TASK-021).

## Criterios de aceptación
- [ ] Al abrir el selector de un tipo, las telas mostradas son las de `tipo_producto_tela` (no las de productos existentes).
- [ ] Elegir una combinación sin Producto preexistente → "Configurar" funciona (precio/sku desde el cálculo).
- [ ] No aparece "Crea primero el producto en /productos".
- [ ] Tipo con `requiere_tela` sin telas asignadas → avisa, no deja resolver.

## QA manual
1. Asignar telas a "Franela" (TASK-022). 2. Cotización → seleccionar Franela → ver telas del tipo → elegir combinación nueva → Configurar → agregar al carrito.

## Nota de Completitud
*(Llenar al terminar)*
