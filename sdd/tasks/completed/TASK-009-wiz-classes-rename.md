# TASK-009: Generalizar clases del wizard (.cot-* → .wiz-*)

**Feature**: FEAT-002 — pedidos-wizard
**Spec**: `sdd/specs/pedidos-wizard.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: M (2–4h)
**Depends-on**: none
**Assigned-to**: santiago

---

## Contexto

Primer paso del wizard de pedidos. El wizard de cotizaciones usa el prefijo `.cot-*` para sus clases CSS y selectores JS, lo que ata el patrón a un solo módulo. Para que el wizard de pedidos pueda reusar 100% del CSS y la lógica visual sin duplicar código, generalizamos a `.wiz-*` (wizard-agnóstico).

Sección del spec: `## 3. Desglose por módulos` → Módulo 1.

---

## Scope

- Renombrar todas las clases `.cot-step-*`, `.cot-wizard-*`, `.cot-step-content`, `.cot-step-marker`, `.cot-stepper`, `.cot-stepper-wrapper`, `.cot-step-dot`, `.cot-step-label`, `.cot-step-line`, `.cot-step-line-fill`, `.cot-step-tag`, `.cot-step-title`, `.cot-step-desc`, `.cot-step-header` → `.wiz-*` equivalente
- Renombrar la sección CSS "COT WIZARD" → "WIZARD UNIFICADO" en `public/assets/css/custom.css`
- Actualizar `cotizaciones/modals.blade.php` (39 ocurrencias) con las nuevas clases
- Actualizar `cotizaciones/scripts/main.blade.php` (6 ocurrencias) con los nuevos selectores
- Verificar que el wizard de cotizaciones sigue funcionando idéntico (test manual)

**NO está en alcance**:
- Crear el wizard de pedidos (TASK-010+)
- Modificar lógica funcional del wizard de cotizaciones (solo rename)
- Renombrar IDs (`#cot-step-1` etc.) — los IDs se mantienen porque pertenecen al modal de cotización; el wizard de pedidos usará `#ped-step-N` o `#wiz-step-N` con namespace propio en sus IDs

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `public/assets/css/custom.css` | MODIFY | Rename 42 ocurrencias `.cot-*` → `.wiz-*` + título de sección |
| `resources/views/admin/cotizaciones/modals.blade.php` | MODIFY | Rename 39 ocurrencias en clases HTML |
| `resources/views/admin/cotizaciones/scripts/main.blade.php` | MODIFY | Rename 6 ocurrencias en selectores jQuery |

---

## Codebase Contract (Anti-Alucinación)

### Estado actual verificado

- **CSS**: `public/assets/css/custom.css` líneas 1311+ contiene la sección `COT WIZARD` con 42 ocurrencias de clases `.cot-*`. Primer selector verificado: `.cot-stepper-wrapper` en línea 1311.
- **HTML**: `resources/views/admin/cotizaciones/modals.blade.php` tiene 39 usos de clases `cot-*`. Modal raíz tiene clase `cot-wizard-modal` en línea 212.
- **JS**: `resources/views/admin/cotizaciones/scripts/main.blade.php` tiene 6 selectores con `cot-*`.

### Mapping de rename (completo)

```
.cot-stepper-wrapper       → .wiz-stepper-wrapper
.cot-stepper               → .wiz-stepper
.cot-step-marker           → .wiz-step-marker
.cot-step-dot              → .wiz-step-dot
.cot-step-label            → .wiz-step-label
.cot-step-line             → .wiz-step-line
.cot-step-line-fill        → .wiz-step-line-fill
.cot-step-content          → .wiz-step-content
.cot-step-header           → .wiz-step-header
.cot-step-title            → .wiz-step-title
.cot-step-desc             → .wiz-step-desc
.cot-step-tag              → .wiz-step-tag
.cot-wizard-modal          → .wiz-wizard-modal  (o simplemente .wiz-modal)
```

Estados modificadores (`.is-active`, `.is-complete`) se preservan tal cual.

### Convenciones a respetar

- Rename mecánico con `sed`/Edit replace_all — no reordenar reglas CSS
- Mantener orden de propiedades dentro de cada bloque
- NO cambiar valores de colores, paddings, transiciones

### NO existe — no referenciar

- ~~`.wiz-modal-header`~~ — no inventar nuevas clases en esta task; solo rename 1:1
- ~~`.ped-step-*`~~ — no se usan; el namespace compartido es `.wiz-*`

---

## Notas de implementación

### Patrón a seguir

Usar `Edit` con `replace_all: true` para cada clase listada en el mapping, una por una. Hacer rename progresivo verificando con `grep` que las cuentas bajan a 0.

```bash
# Verificación post-rename:
grep -c "cot-step\|cot-wizard\|cot-stepper" public/assets/css/custom.css \
   resources/views/admin/cotizaciones/modals.blade.php \
   resources/views/admin/cotizaciones/scripts/main.blade.php
# Esperado: 0 0 0
```

### Restricciones clave

- **No commitear hasta verificar visualmente** que el wizard de cotizaciones funciona idéntico
- El rename no debe tocar:
  - IDs (`id="cot-step-1"` se queda — son parte del modal de cotización, único en su contexto). Decisión: aceptar la inconsistencia menor (clase wiz pero id cot) por simplicidad. Refactor de IDs en task futura si molesta.
  - El nombre del modal `id="showModal"` — sigue siendo el id genérico
  - Comentarios del código que mencionan "cotización" semánticamente

### Referencias en el código

- `public/assets/css/custom.css:1311` — inicio de la sección CSS del wizard

---

## Criterios de aceptación

- [ ] `grep -c "cot-step\|cot-wizard\|cot-stepper"` devuelve 0 en los 3 archivos
- [ ] `grep -c "wiz-step\|wiz-wizard\|wiz-stepper"` muestra las mismas cuentas que tenía antes (42, 39, 6)
- [ ] Wizard de cotizaciones abre y funciona idéntico (los 3 pasos navegables, validaciones, submit)
- [ ] Light + dark mode visualmente idénticos
- [ ] Sin commits con regresión: probar localmente antes de PR

---

## QA manual

1. `php artisan serve` y abrir `/cotizaciones`
2. Click "Agregar Cotización" → modal abre con stepper visible
3. Verificar que stepper muestra 3 dots conectados con líneas
4. Click en dot 2 (sin completar paso 1) → no debe avanzar (validación)
5. Completar paso 1 → click "Continuar" → stepper avanza, línea se rellena
6. Repetir hasta paso 3 → "Guardar Cotización"
7. Toggle dark mode → repetir flujo → todo idéntico
8. Hover sobre dots inactivos → cambia color como antes
9. Si algo no luce idéntico, revertir y diagnosticar antes de seguir

---

## Instrucciones para el ejecutor

Cuando tomes esta task:

1. Lee el spec completo: `sdd/specs/pedidos-wizard.spec.md`
2. Sin dependencias previas
3. Verifica el contrato — sigue siendo válido si `grep` da los mismos counts
4. Actualiza header: `Status: in-progress`
5. Crea rama: `git checkout -b feat/pedidos-wizard` (rama compartida del feature)
6. Implementa el rename mecánico
7. Verifica con QA manual completo en cotizaciones
8. Mueve este archivo a `sdd/tasks/completed/`
9. Llena la Nota de Completitud
10. **NO mergear a `enmanuel` aún** — espera a que las 10 tasks del feature estén completas y se mergea todo junto como una sola PR

---

## Nota de Completitud

**Completado por**: santiago
**Fecha**: 2026-05-26
**Commits**: feat(task-009): rename .cot-* → .wiz-* (WIZARD UNIFICADO)
**Notas**: Rename mecánico ejecutado en los 3 archivos. CSS: 42/42 clases renombradas (0 residual). modals.blade.php: 37/39 clases renombradas; 4 IDs (cot-step-1/2/3/current) preservados per spec. scripts/main.blade.php: 3 class selectors renombrados; 2 ID selectors (#cot-step-1, #cot-step-current) preservados.

**Desviaciones del spec**: El criterio "grep -c → 0 0 0" no se alcanza en modals (4) y scripts (2) porque los IDs se mantienen intencionalmente según la restricción explícita del spec ("Decisión: aceptar la inconsistencia menor"). CSS sí es 0.
