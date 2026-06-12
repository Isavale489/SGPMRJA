# TASK-024: Cotización — selector de variante lee telas del tipo (JS)

**Feature**: FEAT-003 — variantes-dinamicas
**Spec**: `sdd/specs/variantes-dinamicas.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: M
**Depends-on**: TASK-021
**Assigned-to**: emmanuel

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

**Completado por**: emmanuel
**Fecha**: 2026-06-03
**Commits**: (en rama `feat/variantes-dinamicas`)
**Notas** — enfoque "producto virtual" (todo el pipeline selector→configurador→carrito→submit
está cableado a `producto_id` numérico; en vez de reescribirlo, se inyecta un producto sintético):
- `telasDelTipo()` ahora lee `vsState.tipo.telas` (de `tipo_producto_tela`), no de productos existentes.
- `vsResolverVariante()` maneja `dynamic:true`: muestra SKU/precio calculados y habilita confirmar
  guardando el descriptor `variante` en `#vs-confirm`.
- `cotRegistrarProductoVirtual(resp)`: crea un producto virtual `{id:'vN', _dynamic:true,
  _variante:{tipo_producto_id, insumo_tela_id, atributo_valor_ids}, codigo:sku, precio_base, tela,
  tipo_producto, atributo_valores}` y lo empuja a `products` (dedupe por SKU). Lo demás lo trata por id.
- `#vs-confirm`: si es dinámico, registra el virtual y usa su id sintético como `pid`.
- `addProductItem()`: rama dinámica — display readonly + `producto-id-input` (hidden, id sintético,
  sin name) + hidden `tipo_producto_id`/`insumo_tela_id`/`atributo_valor_ids[]` para el submit
  (el `producto_id` NO se envía). Legacy filtra virtuales del dropdown. Escape local `escAttr`
  (escapeForHtml vive en otro IIFE, no estaba en scope).
- Guard: los virtuales no aparecen en el catálogo de familias.

Blade compila; lint del backend ya verde en tasks previas.

**LIMITACIONES / FOLLOW-UPS** (no en alcance de esta task):
1. **Edición de cotización con líneas dinámicas**: al reabrir una cotización guardada, las líneas
   dinámicas (producto_id NULL) NO se reconstruyen como producto virtual desde sus snapshots.
   Hay que reconstruir el virtual en el loader de edición (loadDetallesIntoForm, ~línea 2432).
   Crear como task aparte si se necesita editar dinámicas.
2. **Catálogo = productos existentes**: las "familias" se arman desde productos (cada tipo actual
   tiene ≥1, así que son alcanzables). Tipos sin ningún producto no mostrarían card → lo resuelve
   TASK-027 (catálogo por tipos).

**⚠️ REQUIERE QA EN NAVEGADOR**: no se pudo probar JS desde la sesión. Verificar: abrir cotización
→ familia (ej. Franela) → telas del tipo aparecen → elegir combinación nueva → "Configurar" →
color/tallas → agregar al carrito → confirmar → guardar → revisar en BD que la línea quedó con
`tipo_producto_id` + snapshots + `sku_snapshot`, `producto_id` NULL. Y que el flujo legacy
(productos reales) sigue intacto.

**Desviaciones del spec**: enfoque producto-virtual (no reescritura del pipeline); edición dinámica
diferida (limitación #1).
