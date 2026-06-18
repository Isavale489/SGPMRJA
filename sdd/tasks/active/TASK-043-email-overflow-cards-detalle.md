# TASK-043: Corregir overflow del email en cards de detalle

**Feature**: FEAT-006 — correcciones-ux-detalles
**Spec**: `sdd/specs/correcciones-ux-detalles.spec.md`
**Status**: in-progress
**Priority**: medium
**Esfuerzo estimado**: S (< 2h)
**Depends-on**: none
**Assigned-to**: vanessa

---

## Contexto

Implementa el **Módulo 2** del spec. El campo de **correo electrónico** desborda su contenedor cuando es muy largo, tanto en el detalle "Ver" (`#view-cliente-email`) como en la card de cliente del wizard de edición (`.cot-cliente-contact-item`). Es un defecto puramente de CSS: los contenedores flex no permiten quiebre de texto.

---

## Scope

- Añadir manejo de quiebre de texto (`overflow-wrap: anywhere` / `word-break: break-word`) al valor del email en el detalle (`#view-cliente-email`) y al item de contacto del wizard de edición (`.cot-cliente-contact-item`).
- Añadir `min-width: 0` al contenedor flex padre cuando sea necesario para que el quiebre surta efecto (un flex item no encoge por debajo de su contenido sin `min-width:0`).
- Todo el CSS va en `public/assets/css/custom.css`.

**NO está en alcance**:
- Cambiar el markup de los emails (salvo, si fuese imprescindible, añadir una clase utilitaria) — preferir selectores existentes.
- Tocar la lógica JS ni el backend.
- Otros módulos del spec (campos dinámicos, PDF, separación de secciones).

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `public/assets/css/custom.css` | MODIFY | Reglas de quiebre para email en detalle y en card de edición |
| `resources/views/admin/cotizaciones/modals.blade.php` | MODIFY *(solo si imprescindible)* | Añadir clase utilitaria al span `#view-cliente-email` (línea 92) si el selector por id no basta |

---

## Codebase Contract (Anti-Alucinación)

> Verificado por lectura directa en `enmanuel` (2026-06-18).

### Elementos y CSS actuales
```text
# resources/views/admin/cotizaciones/modals.blade.php
  #view-cliente-email .... :92   <span class="fw-semibold fs-13" id="view-cliente-email">
                                  está dentro de un div.col-12.d-flex.align-items-start (:87)

# public/assets/css/custom.css  (NO tienen word-break/overflow → permiten overflow)
  .cot-cliente-contact-row  ... :2533   (display:flex; flex-wrap:wrap; gap:6px 14px; font-size:.8rem)
  .cot-cliente-contact-item ... :2540   (display:inline-flex; align-items:center; gap:5px)
```
El email del wizard de edición se renderiza en `#cot-cliente-email-display` dentro de un `.cot-cliente-contact-item` (`modals.blade.php` ≈ :428-431).

### Convenciones a respetar
- `AGENTS.md` § Estándares visuales — CSS personalizado SOLO en `custom.css`, dark mode sin `!important`.

### NO existe — no referenciar
- ~~clase utilitaria de "truncate/wrap" propia del proyecto~~ — no asumir; usar reglas estándar CSS o crear una clase pequeña local.

---

## Notas de implementación

### Patrón a seguir
```css
/* email en detalle Ver */
#view-cliente-email {
    overflow-wrap: anywhere;
    word-break: break-word;
}
/* permitir que el flex item encoja y quiebre */
.cot-cliente-contact-item { min-width: 0; }
#cot-cliente-email-display { overflow-wrap: anywhere; word-break: break-word; }
```
- Verificar también que el `div` contenedor del email en el detalle (`col-12 d-flex`) no impida el quiebre; si hace falta, aplicar `min-width:0` al hijo que contiene el `<span>`.
- Probar con un email real largo (p. ej. `nombre.muy.largo.apellido@dominio-corporativo-extenso.com.ve`).

### Restricciones clave
- Sin estilos inline; sin `!important` salvo necesidad real documentada.

---

## Criterios de aceptación

- [ ] Email largo en detalle "Ver" quiebra dentro del card; sin scroll horizontal ni layout roto.
- [ ] Email largo en card de cliente del wizard de edición quiebra correctamente.
- [ ] Email vacío sigue mostrando "N/A"/guion sin artefactos.
- [ ] Dark mode sin regresiones.
- [ ] PR contra `enmanuel` enlazando esta task.

---

## QA manual

1. Login admin → `/cotizaciones`.
2. "Ver" una cotización cuyo cliente tenga email largo → el email quiebra dentro del card.
3. Abrir el wizard de **edición**, seleccionar un cliente con email largo → el email en la card de cliente quiebra.
4. Reducir el ancho de la ventana (responsive) → no hay desbordamiento.
5. Dark mode: revisar contraste/legibilidad.

---

## Instrucciones para el ejecutor

1. Lee el spec.
2. Verifica el Codebase Contract con `read` (las líneas pudieron moverse).
3. Header: `Status: in-progress`, `Assigned-to: <tu-nombre>`.
4. Rama: `git checkout -b feat/TASK-043-email-overflow` desde `enmanuel`.
5. Implementa solo CSS (y clase utilitaria si imprescindible).
6. Verifica criterios + QA.
7. Mueve a `sdd/tasks/completed/` y rellena la Nota de Completitud.
8. PR contra `enmanuel`.

---

## Nota de Completitud

*(Llenar al terminar)*

**Completado por**:
**Fecha**:
**Commits**:
**Notas**:
**Desviaciones del spec**: ninguna
