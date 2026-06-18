# TASK-048: PDF del detalle se ve y comporta igual que "Exportar PDF" del índice

**Feature**: FEAT-006 — correcciones-ux-detalles
**Spec**: `sdd/specs/correcciones-ux-detalles.spec.md`
**Status**: done
**Priority**: medium
**Esfuerzo estimado**: S (< 2h)
**Depends-on**: TASK-044, TASK-046
**Assigned-to**: vanessa

---

## Contexto

Cambio de requerimiento del solicitante: el botón PDF de las ventanas detalle debe **verse y comportarse igual** que el botón "Exportar PDF" que está afuera, en la barra de la DataTable al lado de "Agregar". Esto **supersede la decisión visual de TASK-044** (que lo había unificado a `btn-sm btn-outline-danger` con descarga directa del registro).

Antes: el botón del footer del detalle descargaba el PDF de ESE registro (`/<modulo>/{id}/pdf`).
Ahora: es idéntico al de afuera (`btn btn-danger`, ícono `ri-file-pdf-line align-bottom`, etiqueta "Exportar PDF") y abre el **mismo modal de exportación con filtros** (`#pdfExportModal`).

---

## Scope

- En los 3 detalles (Cotizaciones, Pedidos, Compras), reemplazar el `<a>` de PDF del footer por un `<button>` idéntico al del índice: `btn btn-danger`, `data-bs-toggle="modal" data-bs-target="#pdfExportModal"`, ícono+etiqueta iguales.
- Eliminar el JS que seteaba el `href` del botón (ya no aplica).

**NO está en alcance**:
- El botón "Exportar PDF" del índice (es la referencia, no se toca).
- El dropdown por fila "Ver / Descargar PDF" (sigue descargando el registro individual).
- La generación del PDF ni el contenido del modal de exportación.

---

## Archivos modificados

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/cotizaciones/modals.blade.php` | MODIFY | Botón footer `#viewModal` → `btn-danger` que abre `#pdfExportModal` |
| `resources/views/admin/cotizaciones/scripts/main.blade.php` | MODIFY | Eliminado `$('#view-pdf-btn').attr('href', …)` |
| `resources/views/admin/pedidos/modals.blade.php` | MODIFY | Botón footer `#viewModal` → `btn-danger` que abre `#pdfExportModal` |
| `resources/views/admin/pedidos/scripts/listado.blade.php` | MODIFY | Eliminado `$('#view-ped-pdf-btn').attr('href', …)` |
| `resources/views/admin/compras/modals/view.blade.php` | MODIFY | Botón footer `#viewCompraModal` → `btn-danger` que abre `#pdfExportModal` |
| `resources/views/admin/compras/scripts/main.blade.php` | MODIFY | Eliminado `$('#cv-pdf-btn').attr('href', …)` |

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

### NO existe — no referenciar
- ~~ids `#view-pdf-btn`, `#view-ped-pdf-btn`, `#cv-pdf-btn`~~ — eliminados; el botón ya no necesita id (abre el modal vía data-bs-target).

---

## Criterios de aceptación

- [x] Los 3 botones PDF del detalle se ven igual al "Exportar PDF" del índice (btn-danger sólido, mismo ícono y etiqueta).
- [x] Al hacer clic abren `#pdfExportModal` (mismo comportamiento que el de afuera).
- [x] El detalle se oculta y revive correctamente al cerrar el export (fix global de modales anidados).
- [x] Sin referencias JS huérfanas a los ids viejos.

---

## QA manual

1. `/cotizaciones` → "Ver" → footer: botón "Exportar PDF" rojo idéntico al de afuera → clic abre el modal de exportación con filtros.
2. Cerrar el modal de exportación → vuelve al detalle intacto.
3. Repetir en `/pedidos` y `/compras`.
4. Comparar visualmente el botón del detalle con el de la barra (al lado de Agregar) → iguales.

---

## Nota de Completitud

**Completado por**: vanessa (con Claude Code)
**Fecha**: 2026-06-18
**Commits**: *(este commit)*
**Notas**: Supersede la decisión visual de TASK-044 (outline + descarga directa). Ahora los 3 botones del detalle replican exactamente el botón índice y abren el modal de exportación con filtros, apoyándose en el fix global de modales anidados. El dropdown por fila sigue descargando el registro individual.
**Desviaciones del spec**: Cambio de requerimiento posterior al shipped; documentado como task nueva.
