# TASK-045: Separación visual de secciones en el detalle de Cotizaciones

**Feature**: FEAT-006 — correcciones-ux-detalles
**Spec**: `sdd/specs/correcciones-ux-detalles.spec.md`
**Status**: pending
**Priority**: medium
**Esfuerzo estimado**: M (2-4h)
**Depends-on**: TASK-042
**Assigned-to**: unassigned

---

## Contexto

Implementa el **Módulo 4** del spec. El detalle "Ver" de Cotizaciones (`#viewModal`) usa cards genéricas `bg-soft-primary` con poca separación visual entre secciones. Se busca la sensación limpia de "Detalles del Insumo": secciones claramente separadas y coherentes.

**Importante**: el detalle de Cotizaciones es un **wizard de pasos**, no el detalle de página única de los módulos maestros. **NO** se migra literalmente el hero `cli-view-*`; se mejora la separación/jerarquía visual de las secciones dentro del wizard, manteniendo el stepper y el paradigma actual.

**Depende de TASK-042** porque ambas tocan el Paso 1 (card de cliente) de `cotizaciones/modals.blade.php`. Tomar TASK-042 primero (o la misma persona hace ambas) para evitar conflicto.

---

## Scope

- Mejorar la separación visual entre las secciones del detalle (Paso 1: "Información del Cliente" / "Datos de la Cotización"; y donde aplique en Productos/Resumen): encabezados de sección claros, espaciado y bordes/tarjetas coherentes.
- Mantener el patrón de dato `emp-icon-box emp-icon-box--navy` + label `text-muted fs-12` + valor `fw-semibold fs-13` ya usado.
- Respetar la decisión de TASK-042 (campos dinámicos jurídico/natural): el bloque "Apellido" puede estar oculto (`#view-apellido-wrap.d-none`) sin romper la grilla.
- CSS en `public/assets/css/custom.css`; reusar/extender clases existentes en vez de duplicar.

**NO está en alcance**:
- Migrar el hero `cli-view-hero` de los módulos maestros al wizard.
- Unificar los 5 wizards de Gestión Operativa (footer/botón, hero persistente, tamaño de modal) — decisión separada y mayor.
- Cambiar la lógica JS de poblado (más allá de lo necesario por reordenamiento de markup).
- El botón PDF (TASK-044) ni el overflow de email (TASK-043).

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/cotizaciones/modals.blade.php` | MODIFY | `#viewModal`: jerarquía/separación de secciones |
| `public/assets/css/custom.css` | MODIFY | Estilos de tarjetas/encabezados de sección del detalle |

---

## Codebase Contract (Anti-Alucinación)

> Verificado por lectura directa en `enmanuel` (2026-06-18).

### Estructura actual del detalle (modals.blade.php, #viewModal)
```text
#viewModal (modal "Ver", wizard read-only 3 pasos) ........ :1-235
  Paso 1 "Cliente" ......................................... :43-140
    Card "Información del Cliente"  (card border-0 shadow-sm /
       card-header bg-soft-primary) .......... :50-97
    Card "Datos de la Cotización" ............ :98-138
  Paso 2 "Productos" ....................................... :142-165
  Paso 3 "Resumen" ......................................... :167-210
  Footer con #view-pdf-btn ................................. :216-218
```

### Patrón de referencia "hero + cards" (NO copiar el hero; referencia de estilo de secciones)
```text
# resources/views/admin/insumos/index.blade.php:199-294  → markup de hero + cards
# public/assets/css/custom.css:8689-8898  → .cli-view-hero, .cli-view-card,
#   .cli-view-card-header, .cli-view-card-body, .cli-view-sections (+ dark mode)
```

### Patrón de dato dentro de card (mantener)
```html
<div class="... d-flex align-items-start">
  <div class="emp-icon-box emp-icon-box--navy rounded-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
    <i class="ri-... emp-icon--navy"></i>
  </div>
  <div><small class="text-muted d-block fs-12">Label</small>
  <span class="fw-semibold fs-13" id="...">-</span></div>
</div>
```

### Convenciones a respetar
- `docs/conventions/wizard-pattern.md` — NO romper el stepper `.wiz-*` ni `wiz-step-content`/`data-step`.
- `AGENTS.md` § Estándares visuales — CSS en `custom.css`, dark mode sin `!important`.
- `docs/conventions/modal-system.md` — modal `atlantico-modal--op`.

### NO existe — no referenciar
- ~~`docs/conventions/detail-view-hero-cards.md`~~ — no existe.
- ~~hero persistente en el wizard de cotizaciones~~ — no existe (solo Compras tiene uno propio); no es objetivo de esta task.

---

## Notas de implementación

### Patrón a seguir
- Reforzar la separación con: encabezados de sección consistentes (icono + título uppercase como `cli-view-card-header`), bordes/sombra sutiles y espaciado uniforme entre cards.
- Si se introduce una clase nueva, reusar la estética de `.cli-view-card*` adaptada al ancho del wizard, sin duplicar bloques completos de CSS.
- Mantener responsividad (`col-md-6` etc.) y que la ocultación de `#view-apellido-wrap` no deje huecos raros.
- Usar **Edit** sobre el blade, nunca Write.

### Restricciones clave
- No alterar `data-step`, IDs de campos (`#view-cliente-*`, etc.) ni el stepper.
- Sin estilos inline; dark mode probado.

---

## Criterios de aceptación

- [ ] Las secciones del detalle se ven claramente separadas y coherentes (sensación "Detalles del Insumo").
- [ ] Se conserva el stepper, los `data-step` y todos los IDs de campos.
- [ ] Compatible con TASK-042: con cliente jurídico (Apellido oculto) la grilla no queda con huecos.
- [ ] Sin estilos inline; CSS en `custom.css`; dark mode sin regresiones.
- [ ] PR contra `enmanuel` enlazando esta task.

---

## QA manual

1. Login admin → `/cotizaciones` → "Ver".
2. Paso 1: "Información del Cliente" y "Datos de la Cotización" visualmente separadas y prolijas.
3. Recorrer Pasos 2 (Productos) y 3 (Resumen): consistencia visual.
4. Cliente jurídico (Apellido oculto): sin huecos ni desalineación.
5. Responsive (ventana angosta) y dark mode: sin regresiones.

---

## Instrucciones para el ejecutor

1. Lee el spec.
2. **Confirma que TASK-042 está en `completed/`** (o impleméntala primero); ambas tocan el Paso 1.
3. Verifica el Codebase Contract con `read` (líneas pueden haberse movido tras TASK-042).
4. Header: `Status: in-progress`, `Assigned-to: <tu-nombre>`.
5. Rama: `git checkout -b feat/TASK-045-detalle-separacion` desde `enmanuel` (con TASK-042 ya integrada).
6. Implementa con **Edit**.
7. Verifica criterios + QA.
8. Mueve a `sdd/tasks/completed/` y rellena la Nota de Completitud.
9. PR contra `enmanuel`.

---

## Nota de Completitud

*(Llenar al terminar)*

**Completado por**:
**Fecha**:
**Commits**:
**Notas**:
**Desviaciones del spec**: ninguna
