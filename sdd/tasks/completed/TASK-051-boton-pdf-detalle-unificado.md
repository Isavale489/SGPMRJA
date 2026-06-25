# TASK-051: Unificar el botón PDF de las ventanas detalle (Cotizaciones + Compras)

**Feature**: FEAT-006 — correcciones-ux-detalles
**Spec**: `sdd/specs/correcciones-ux-detalles.spec.md`
**Status**: done
**Priority**: medium
**Esfuerzo estimado**: S (< 2h)
**Depends-on**: none
**Assigned-to**: vanessa

---

## Contexto

Implementa el **Módulo 3** del spec. El botón de PDF dentro de las ventanas **detalle** (footer del wizard) está descoordinado: Cotizaciones usa `btn-sm btn-warning` (amarillo) y Compras `btn-success btn-sm` (verde). Resultado: "se ve feo". Se unifican a **un único estilo coherente**.

---

## Scope

- Definir **un estilo único** para el botón PDF del footer de las ventanas detalle y aplicarlo a:
  - Cotizaciones: `#view-pdf-btn` (`modals.blade.php:216-218`).
  - Compras: `#cv-pdf-btn` (`compras/modals/view.blade.php:238-240`).
- Mantener el mismo ícono (`ri-file-pdf-line`) y una etiqueta consistente (p. ej. "PDF" o "Descargar PDF" — elegir una y usarla en ambos).
- Estilo recomendado: `btn btn-sm` con apariencia neutra/outline legible sobre el footer claro del wizard (evitar el amarillo `btn-warning`). Definir clase/estilo en `custom.css` si se requiere afinar.

**NO está en alcance**:
- El botón **"Exportar PDF" del índice** (`cotizaciones/index.blade.php:47`, `btn-danger`, abre `#pdfExportModal`) — se mantiene; es otra acción (reporte filtrado).
- Cambiar las rutas o la generación del PDF (`cotizacionPdf` / `compraPdf`).
- Otros módulos del spec.

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/cotizaciones/modals.blade.php` | MODIFY | `#view-pdf-btn` (línea 216-218): cambiar clases/etiqueta |
| `resources/views/admin/compras/modals/view.blade.php` | MODIFY | `#cv-pdf-btn` (línea 238-240): cambiar clases/etiqueta |
| `public/assets/css/custom.css` | MODIFY *(opcional)* | Clase del botón PDF de detalle si se afina el estilo |

---

## Codebase Contract (Anti-Alucinación)

> Verificado por lectura directa en `enmanuel` (2026-06-18).

### Botones actuales (ambos en `.wiz-wizard-footer-info`)
```html
<!-- resources/views/admin/cotizaciones/modals.blade.php:216-218 -->
<a href="#" id="view-pdf-btn" class="btn btn-sm btn-warning" target="_blank">
    <i class="ri-file-pdf-line me-1"></i>PDF
</a>

<!-- resources/views/admin/compras/modals/view.blade.php:238-240 -->
<a id="cv-pdf-btn" href="#" target="_blank" class="btn btn-success btn-sm">
    <i class="ri-file-pdf-2-line me-1"></i>Descargar PDF
</a>
```

### Poblado del href (NO tocar la lógica)
```js
// cotizaciones/scripts/main.blade.php:2806
$('#view-pdf-btn').attr('href', '/cotizaciones/' + id + '/pdf');
// compras/scripts/main.blade.php:196
$('#cv-pdf-btn').attr('href', '/compras/' + d.id + '/pdf');
```

### Convenciones a respetar
- `AGENTS.md` § Estándares visuales — botones coherentes con el dominio; CSS en `custom.css`, dark mode sin `!important`.
- `docs/conventions/wizard-pattern.md` — footer del wizard `.wiz-wizard-footer-info`.

### NO existe — no referenciar
- ~~una clase de botón PDF compartida ya definida~~ — no existe; si se crea, definirla en `custom.css`.

---

## Notas de implementación

### Patrón a seguir
- Conservar el `id`, `href="#"`, `target="_blank"` y el span del ícono; cambiar **solo** las clases de estilo y unificar la etiqueta.
- Mantener `btn-sm` para no romper la altura del footer del wizard.
- Verificar legibilidad en dark mode del footer.
- Usar **Edit** sobre los blade, nunca Write.

### Restricciones clave
- No alterar el JS que setea el `href`.
- Etiqueta e ícono idénticos en ambos módulos tras el cambio.

---

## Criterios de aceptación

- [ ] El botón PDF del detalle de Cotizaciones y el de Compras tienen **el mismo estilo, ícono y etiqueta**.
- [ ] Ya no se usa `btn-warning` (amarillo) en el botón PDF del detalle.
- [ ] Ambos botones siguen abriendo el PDF correcto (`/cotizaciones/{id}/pdf`, `/compras/{id}/pdf`).
- [ ] El botón "Exportar PDF" del índice queda intacto.
- [ ] Dark mode legible.
- [ ] PR contra `enmanuel` enlazando esta task.

---

## QA manual

1. Login admin → `/cotizaciones` → "Ver" → footer: el botón PDF luce con el estilo unificado; clic → abre el PDF de la cotización.
2. `/compras` → "Ver" → footer: el botón PDF luce **idéntico** al de cotizaciones; clic → abre el PDF de la compra.
3. `/cotizaciones` → botón "Exportar PDF" (índice) sin cambios y funcional.
4. Dark mode: botón legible en ambos detalles.

---

## Instrucciones para el ejecutor

1. Lee el spec.
2. Verifica el Codebase Contract con `read` (Cotizaciones comparte archivo con TASK-049/045 → confirma líneas; la zona del footer es distinta del Paso 1).
3. Header: `Status: in-progress`, `Assigned-to: <tu-nombre>`.
4. Rama: `git checkout -b feat/TASK-051-pdf-detalle-unificado` desde `enmanuel`.
5. Implementa con **Edit**.
6. Verifica criterios + QA.
7. Mueve a `sdd/tasks/completed/` y rellena la Nota de Completitud.
8. PR contra `enmanuel`.

---

## Nota de Completitud

*(Llenar al terminar)*

**Completado por**: vanessa (con Claude Code)
**Fecha**: 2026-06-18
**Commits**: 834cfd0
**Notas**: Ambos botones de detalle (cotizaciones `#view-pdf-btn`, compras `#cv-pdf-btn`) unificados a `btn-sm btn-outline-danger`, ícono `ri-file-pdf-line`, etiqueta "Descargar PDF". IDs/href/target intactos; el JS no cambió. El "Exportar PDF" del índice quedó intacto.
**Desviaciones del spec**: ninguna
