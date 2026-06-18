# TASK-046: Añadir botón "Descargar PDF" al detalle de Pedidos

**Feature**: FEAT-006 — correcciones-ux-detalles
**Spec**: `sdd/specs/correcciones-ux-detalles.spec.md`
**Status**: done
**Priority**: medium
**Esfuerzo estimado**: S (< 2h)
**Depends-on**: TASK-044
**Assigned-to**: vanessa

---

## Contexto

Mejora de consistencia surgida durante la implementación de FEAT-006. Tras unificar el botón PDF del detalle en Cotizaciones y Compras (TASK-044), el detalle de **Pedidos** quedó como el único sin botón PDF en el footer del wizard, pese a que la ruta `pedidos/{pedido}/pdf` ya existe. Se añade el mismo botón para que las 3 ventanas detalle (Cotizaciones, Pedidos, Compras) sean idénticas.

---

## Scope

- Añadir al footer del `#viewModal` de pedidos (slot `wiz-wizard-footer-info`, que estaba vacío) un botón "Descargar PDF" con el **mismo estilo** que Cotizaciones/Compras (`btn-sm btn-outline-danger`, ícono `ri-file-pdf-line`).
- Cablear su `href` a `/pedidos/{id}/pdf` en el handler `.view-btn`.

**NO está en alcance**:
- El "Exportar PDF" del índice ni el dropdown por fila (ya existen).
- Cambios en `PedidoController` o en la generación del PDF.

---

## Archivos modificados

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/pedidos/modals.blade.php` | MODIFY | Botón `#view-ped-pdf-btn` en `wiz-wizard-footer-info` (≈ línea 808) |
| `resources/views/admin/pedidos/scripts/listado.blade.php` | MODIFY | Handler `.view-btn`: `attr('href', '/pedidos/'+id+'/pdf')` |

---

## Codebase Contract (Anti-Alucinación)

> Verificado en `enmanuel` (2026-06-18).

```text
# resources/views/admin/pedidos/modals.blade.php
  #viewModal ............................... :588
  footer wiz-wizard-footer ................. :807
  wiz-wizard-footer-info (estaba VACÍO) .... :808  ← aquí va el botón

# resources/views/admin/pedidos/scripts/listado.blade.php
  $('#pedidos-table').on('click', '.view-btn', ...) ... :411
  var id = $(this).data('id') ......................... :412

# Ruta verificada (php artisan route:list)
  GET pedidos/{pedido}/pdf → pedidos.pdf › PedidoController@pedidoPdf
```

### NO existe — no referenciar
- ~~botón PDF previo en el footer del detalle de pedidos~~ — no existía (el slot estaba vacío).

---

## Criterios de aceptación

- [x] El detalle de Pedidos muestra "Descargar PDF" idéntico al de Cotizaciones/Compras.
- [x] El botón abre `/pedidos/{id}/pdf` del pedido correcto.
- [x] Sin estilos inline; dark mode coherente (mismo botón que ya se valida en TASK-044).

---

## QA manual

1. `/pedidos` → "Ver" un pedido → footer: botón "Descargar PDF" outline rojo.
2. Clic → abre el PDF de ESE pedido en pestaña nueva.
3. Comparar con Cotizaciones y Compras → se ven iguales.

---

## Nota de Completitud

**Completado por**: vanessa (con Claude Code)
**Fecha**: 2026-06-18
**Commits**: *(este commit de cierre)*
**Notas**: Botón añadido al slot vacío del footer; href cableado tras leer el id en el handler `.view-btn`. Ruta `pedidos/{pedido}/pdf` ya existía.
**Desviaciones del spec**: Task extra (no estaba en el spec original); mejora de consistencia aprobada por el solicitante.
