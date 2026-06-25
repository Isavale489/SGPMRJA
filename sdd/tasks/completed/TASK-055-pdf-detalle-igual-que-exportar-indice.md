# TASK-055: PDF del detalle se VE igual que "Exportar PDF" del índice (descarga ESE registro)

**Feature**: FEAT-006 — correcciones-ux-detalles
**Spec**: `sdd/specs/correcciones-ux-detalles.spec.md`
**Status**: done
**Priority**: medium
**Esfuerzo estimado**: S (< 2h)
**Depends-on**: TASK-051, TASK-053
**Assigned-to**: vanessa

---

## Contexto

Cambio de requerimiento del solicitante: el botón PDF de las ventanas detalle debe **verse igual** que el botón "Exportar PDF" que está afuera, en la barra de la DataTable al lado de "Agregar". **Solo el look** — supersede la decisión visual de TASK-051 (`btn-sm btn-outline-danger`).

> **Aclaración del solicitante (corrige una primera implementación):** el comportamiento NO debe ser igual. El de afuera es un reporte **general** (modal `#pdfExportModal` con filtros del listado); el del detalle es de **ese registro específico** (descarga `/<modulo>/{id}/pdf`). Solo se iguala la apariencia.

Resultado final:
- **Look**: `btn btn-danger` sólido, ícono `ri-file-pdf-line align-bottom`, etiqueta "Exportar PDF" — idéntico al de afuera.
- **Comportamiento**: `<a target="_blank">` cuyo `href` se setea por JS a `/<modulo>/{id}/pdf` → descarga el PDF de ESE registro.

---

## Scope

- En los 3 detalles (Cotizaciones, Pedidos, Compras), darle al botón PDF del footer el **mismo look** que el del índice: `btn btn-danger` sólido, ícono `ri-file-pdf-line align-bottom`, etiqueta "Exportar PDF".
- Mantener el comportamiento **por registro**: `<a target="_blank">` con `href` seteado por JS a `/<modulo>/{id}/pdf`.

**NO está en alcance**:
- El botón "Exportar PDF" del índice (es la referencia, no se toca).
- Que el detalle abra el modal de exportación general (descartado tras aclaración: el detalle es específico).
- La generación del PDF.

---

## Archivos modificados

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/cotizaciones/modals.blade.php` | MODIFY | `#view-pdf-btn`: `<a btn-danger target=_blank>` "Exportar PDF" (descarga el registro) |
| `resources/views/admin/cotizaciones/scripts/main.blade.php` | MODIFY | `$('#view-pdf-btn').attr('href', '/cotizaciones/'+id+'/pdf')` |
| `resources/views/admin/pedidos/modals.blade.php` | MODIFY | `#view-ped-pdf-btn`: `<a btn-danger target=_blank>` "Exportar PDF" |
| `resources/views/admin/pedidos/scripts/listado.blade.php` | MODIFY | `$('#view-ped-pdf-btn').attr('href', '/pedidos/'+id+'/pdf')` |
| `resources/views/admin/compras/modals/view.blade.php` | MODIFY | `#cv-pdf-btn`: `<a btn-danger target=_blank>` "Exportar PDF" |
| `resources/views/admin/compras/scripts/main.blade.php` | MODIFY | `$('#cv-pdf-btn').attr('href', '/compras/'+d.id+'/pdf')` |

---

## Codebase Contract (Anti-Alucinación)

> Verificado en `enmanuel` (2026-06-18).

```text
# Botón de referencia (índice, al lado de "Agregar") — idéntico en los 3 módulos
  cotizaciones/index.blade.php:47   btn btn-danger ms-2 → data-bs-target="#pdfExportModal"
  pedidos/index.blade.php:52        btn btn-danger      → data-bs-target="#pdfExportModal"
  compras/index.blade.php:55,61     btn btn-danger      → data-bs-target="#pdfExportModal"
  Ícono+texto: <i class="ri-file-pdf-line align-bottom me-1"></i> Exportar PDF

# Modal de exportación con filtros — mismo id en los 3, presente en cada index
  cotizaciones/index.blade.php:131  #pdfExportModal (atlantico-modal)
  pedidos/index.blade.php:139       #pdfExportModal (atlantico-modal)
  compras/index.blade.php:145       #pdfExportModal (atlantico-modal--op)  + @include modals.view (:142)

# Modales anidados: fix global ya aplicado (app.blade.php) — ver docs/conventions/nested-modals.md
#   Al abrir #pdfExportModal sobre el detalle, el detalle se oculta (modal-hidden-temp)
#   y revive al cerrar el export. NO reimplementar por módulo.
```

### IDs (vigentes — los usa el JS para setear el href por registro)
- `#view-pdf-btn` (cotizaciones), `#view-ped-pdf-btn` (pedidos), `#cv-pdf-btn` (compras).

---

## Criterios de aceptación

- [x] Los 3 botones PDF del detalle se ven igual al "Exportar PDF" del índice (btn-danger sólido, mismo ícono y etiqueta).
- [x] Al hacer clic descargan/abren el PDF de ESE registro (`/<modulo>/{id}/pdf`), NO el modal de exportación general.
- [x] El comportamiento del botón "Exportar PDF" del índice (reporte general con filtros) queda intacto.

---

## QA manual

1. `/cotizaciones` → "Ver" → footer: botón "Exportar PDF" rojo idéntico al de afuera → clic abre el PDF de ESA cotización.
2. Repetir en `/pedidos` y `/compras` (PDF del pedido/compra específico).
3. Comparar visualmente el botón del detalle con el de la barra (al lado de Agregar) → se ven iguales.
4. El "Exportar PDF" de la barra sigue abriendo el modal de reporte general.

---

## Nota de Completitud

**Completado por**: vanessa (con Claude Code)
**Fecha**: 2026-06-18
**Commits**: de0d618 (1ª versión: abría el modal general), <este commit> (corrección: descarga el registro)
**Notas**: Primero se implementó abriendo `#pdfExportModal` (interpretando "comportarse igual"). El solicitante aclaró que el detalle debe ser específico: se corrigió a `<a>` con href por registro, conservando el look sólido rojo del botón de afuera. Supersede la decisión visual de TASK-051 (outline). El dropdown por fila sigue descargando el registro individual.
**Desviaciones del spec**: Cambio de requerimiento posterior al shipped; documentado como task nueva.
