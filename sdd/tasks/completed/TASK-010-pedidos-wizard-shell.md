# TASK-010: Shell HTML del wizard de pedidos (4 pasos)

**Feature**: FEAT-002 — pedidos-wizard
**Spec**: `sdd/specs/pedidos-wizard.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: M (2–4h)
**Depends-on**: TASK-009
**Assigned-to**: santiago

---

## Contexto

Crear el archivo `pedidos/modals.blade.php` con la estructura HTML del wizard de 4 pasos: Cliente · Productos · Pago · Resumen. Esta task **solo crea el HTML** (stepper + secciones vacías placeholder). La lógica JS y los campos de cada paso se llenan en tasks siguientes.

Sección del spec: `## 3. Desglose por módulos` → Módulo 2.

---

## Scope

- Crear `resources/views/admin/pedidos/modals.blade.php` con:
  - Modal raíz `<div class="modal fade atlantico-modal atlantico-modal--op wiz-modal" id="showModal">`
  - Header con título dinámico ("Nuevo Pedido" / "Completar Pedido #NN" / "Editar Pedido #NN") y botón cerrar
  - Stepper horizontal con 4 dots conectados por líneas (clases `.wiz-*` ya renombradas en TASK-009)
  - 4 `<section class="wiz-step-content" id="ped-wiz-step-N" data-step="N" hidden>` con `wiz-step-header` (tag + título + descripción), placeholder visible para implementar luego
  - Footer con paginador "Paso X de 4" + botones `Anterior`, `Continuar`, `Guardar` (solo activo en paso 4)
- Incluir también en `modals.blade.php` el `viewModal` actual (read-only) y `modal_seleccionar_cotizacion` extraídos del `index.blade.php` para tener todos los modales en un solo archivo

**NO está en alcance**:
- Lógica JS de navegación entre pasos (TASK-011 y siguientes)
- Implementación de campos de cada paso (TASK-011, TASK-012, TASK-013, TASK-014)
- Refactor de `index.blade.php` para incluir este archivo (TASK-017)

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/pedidos/modals.blade.php` | CREATE | Wizard HTML + viewModal + modal seleccionar cotización |

---

## Codebase Contract (Anti-Alucinación)

### Estado actual verificado

- **Plantilla base**: `resources/views/admin/cotizaciones/modals.blade.php` ya tiene el patrón del wizard. Líneas 212-810 contienen el modal `showModal` con stepper.
- **Modales auxiliares en pedidos**: `resources/views/admin/pedidos/index.blade.php` tiene 3 modales:
  - `id="productosModal"` (línea 38) — modal de selección de productos
  - `id="viewModal"` (línea 224) — read-only ver detalles
  - `id="showModal"` (línea 511) — el modal grande que vamos a reemplazar
- **Modal seleccionar cotización**: existe en `resources/views/admin/pedidos/modals/seleccionar_cotizacion.blade.php` (NO confundir con el directorio `modals/`)

### Imports / convenciones

- Modal raíz: `class="modal fade atlantico-modal atlantico-modal--op wiz-modal"` — clases ya existen en custom.css
- Modal size: `class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-sm-down"` (mobile fullscreen)
- Stepper: usar exactamente las clases `.wiz-step-marker`, `.wiz-step-dot`, `.wiz-step-label`, `.wiz-step-line`, `.wiz-step-line-fill` renombradas en TASK-009
- IDs específicos de pedidos: usar prefijo `ped-wiz-` (ej. `id="ped-wiz-step-1"`) para no chocar con cotización si ambos modales coexisten en el DOM

### Convenciones a respetar

- `docs/conventions/modal-system.md` — clase `atlantico-modal--op` para transaccionales
- `AGENTS.md` § Estándares visuales — cards `card border-0 shadow-sm`, header `bg-light-subtle`
- `memory/reference_modales_anidados.md` — fix global ya aplicado, NO reimplementar

### Estructura objetivo del paso (referencia)

```html
<section class="wiz-step-content is-active" id="ped-wiz-step-1" data-step="1">
    <div class="wiz-step-header">
        <span class="wiz-step-tag">Paso 1 de 4</span>
        <h4 class="wiz-step-title">Cliente y datos del pedido</h4>
        <p class="wiz-step-desc">Busca el cliente y define las fechas y prioridad del pedido.</p>
    </div>
    <div class="row g-3">
        <!-- Implementar en TASK-011 -->
        <div class="text-center text-muted py-5">
            <i class="ri-tools-line fs-1"></i>
            <p class="mt-2 mb-0">Placeholder paso 1 — se implementa en TASK-011</p>
        </div>
    </div>
</section>
```

### NO existe — no referenciar

- ~~`pedidos-wizard.blade.php`~~ — no usar; el archivo correcto es `modals.blade.php`
- ~~`wiz-step-1.blade.php` componente Blade~~ — el spec decidió NO crear componente compartido; HTML inline
- ~~`<x-wizard>` Blade component~~ — no se usan Blade components en este proyecto

---

## Notas de implementación

### Patrón a seguir

Copiar la estructura del wizard de `cotizaciones/modals.blade.php:212-810` como base, adaptar:

- Título del modal → dinámico vía JS (id `modalTitle`)
- 3 pasos → 4 pasos (añadir paso 3 "Pago" entre Productos y Resumen)
- Texto del stepper: "Cliente", "Productos", "Pago", "Resumen"
- Etiqueta de paso: "Paso 1 de 4" etc.
- IDs: prefijo `ped-wiz-` para todo en pedidos
- Mantener placeholders visibles dentro de cada `<section>` que serán reemplazados en tasks siguientes

### Restricciones clave

- HTML semánticamente correcto (`role="tablist"`, `aria-selected`, `aria-controls` en stepper)
- Footer con clases `modal-footer d-flex justify-content-between align-items-center`
- Botón "Anterior" disabled en paso 1; "Continuar" visible en pasos 1-3; "Guardar" visible solo en paso 4 (puede ser el mismo botón con texto dinámico)
- Botones con tipos correctos: `type="button"` para nav, `type="submit"` para guardar (dentro de un `<form id="pedidoForm">`)

### Referencias en el código

- `resources/views/admin/cotizaciones/modals.blade.php:212-810` — plantilla del wizard a copiar/adaptar
- `resources/views/admin/pedidos/modals/seleccionar_cotizacion.blade.php` — modal a incluir o mantener separado (decisión: incluirlo como `@include` desde modals.blade.php para consolidar)

---

## Criterios de aceptación

- [ ] Archivo `pedidos/modals.blade.php` creado y válido sintácticamente (`php artisan view:clear` sin errores)
- [ ] Modal raíz tiene clase `wiz-modal` y abre con `data-bs-target="#showModal"` (sin JS adicional)
- [ ] Stepper visible con 4 dots y 3 líneas, dot 1 marcado como `is-active`
- [ ] 4 secciones presentes con placeholder visible
- [ ] Footer con paginador y botones placeholder
- [ ] Light + dark mode visualmente correctos
- [ ] `viewModal` y `modal_seleccionar_cotizacion` incluidos también en este archivo

---

## QA manual

1. Incluir temporalmente `@include('admin.pedidos.modals')` en `pedidos/index.blade.php` (NO commitear este include — TASK-017 lo hace formalmente)
2. Recargar `/pedidos` → abrir consola, ejecutar `$('#showModal').modal('show')`
3. Verificar:
   - Stepper visible y bonito
   - 4 dots con label correcto
   - Paso 1 activo (línea 1→2 sin rellenar aún)
   - Placeholder visible dentro del paso 1
4. Click dot 2 (sin lógica JS aún, solo debe verse hover funcional)
5. Toggle dark mode → verificar contrastes del stepper y modal
6. Mobile (Chrome devtools → iPhone) → modal-fullscreen activa correctamente

---

## Instrucciones para el ejecutor

1. Lee spec + TASK-009 (debe estar `completed` antes de empezar)
2. Verifica que `grep "wiz-step-marker" public/assets/css/custom.css` devuelve > 0
3. Actualiza header: `Status: in-progress`
4. Trabaja en rama `feat/pedidos-wizard` (creada en TASK-009)
5. Crea el archivo
6. QA manual visual
7. Mueve a `sdd/tasks/completed/`
8. Llena Nota de Completitud
9. NO mergear aún — feature completo va en una PR

---

## Nota de Completitud

**Completado por**: santiago
**Fecha**: 2026-05-26
**Commits**: feat(task-010): crear pedidos/modals.blade.php — shell wizard 4 pasos
**Notas**: 41 checks estructurales PASS vía PHP render. Stepper 4 dots + 3 líneas, secciones ped-wiz-step-1..4, viewModal inline, @include seleccionar_cotizacion. No duplicate IDs. CSS dark mode ya cubierto por TASK-009.

**Desviaciones del spec**: ninguna.
