# TASK-011: Paso 1 del wizard de pedidos (Cliente y datos)

**Feature**: FEAT-002 — pedidos-wizard
**Spec**: `sdd/specs/pedidos-wizard.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: M (2–4h)
**Depends-on**: TASK-010
**Assigned-to**: santiago

---

## Contexto

Implementar el paso 1 del wizard (Cliente y datos del pedido): buscador unificado de personas, crear cliente inline, fechas, prioridad. Portar la lógica del paso 1 del wizard de cotizaciones adaptando nombres de campos al dominio pedido.

Sección del spec: `## 3. Desglose por módulos` → Módulo 3. Paso 1 detallado en `## 2 → Paso 1`.

---

## Scope

- Crear `resources/views/admin/pedidos/scripts/main.blade.php` (NUEVO) con el scaffold del wizard JS y la lógica del paso 1
- Implementar dentro del paso 1 (reemplazar placeholder de TASK-010):
  - Buscador de personas por documento (autocomplete contra `personas.search`)
  - Botón "Crear cliente nuevo" inline cuando no existe
  - Fechas: `fecha_pedido` (auto = hoy) + `fecha_entrega_estimada`
  - Prioridad: chips Normal / Alta / Urgente (mismo patrón visual que cotización)
- Implementar la navegación base del wizard (`irAPaso(n)`, validación de paso antes de avanzar, render del stepper)
- Validación del paso 1: cliente seleccionado + fecha_pedido obligatorios antes de continuar

**NO está en alcance**:
- Productos (TASK-012), Pago (TASK-013), Resumen (TASK-014)
- Modo "completar desde cotización" (TASK-015) ni "edit" (TASK-016)

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/pedidos/scripts/main.blade.php` | CREATE | Scaffold wizard JS + lógica paso 1 |
| `resources/views/admin/pedidos/modals.blade.php` | MODIFY | Reemplazar placeholder paso 1 con campos reales |

---

## Codebase Contract (Anti-Alucinación)

### Rutas verificadas

```
GET  /personas-search           personas.search   → PersonaController@search   (routes/web.php:80)
POST /clientes/from-persona/{id} (verificar nombre exacto con grep antes de usar)
```

### Patrón de referencia (cotización paso 1)

- `resources/views/admin/cotizaciones/scripts/main.blade.php` — buscar la sección del paso 1 (autocomplete + crear cliente). Reusar la lógica de:
  - autocomplete con debounce contra `personas.search`
  - render de resultados en lista
  - hidratación de campos cliente al seleccionar
  - chips de prioridad (`data-prioridad`)

### Mapeo de campos cotización → pedido

```
fecha_emision   → fecha_pedido            (input date, default hoy)
fecha_validez   → fecha_entrega_estimada  (input date, opcional)
(prioridad)     → prioridad               (idéntico: Normal/Alta/Urgente)
cliente_id      → cliente_id              (hidden, idéntico)
```

### IDs del HTML (definidos en TASK-010)

- `#ped-wiz-step-1` — sección del paso 1
- Campos: usar prefijo `ped-` (ej. `#ped-cliente-nombre`, `#ped-ci-rif-number`, `#ped-fecha-pedido`, `#ped-fecha-entrega`, `#ped-prioridad`, `#ped-cliente-id`)

### Convenciones a respetar

- `memory/reference_persona_unified_search.md` — patrón del buscador unificado
- `docs/conventions/js-validations.md` — validación blur + antes de avanzar de paso
- `memory/feedback_softdeletes_unique_constraint.md` — si se crea cliente, el backend ya maneja `codigo_empleado` con `withTrashed()`

### NO existe — no referenciar

- ~~`clientes-search`~~ — el endpoint es `personas-search` (busca cliente+empleado+proveedor)
- ~~`PedidoController@buscarCliente`~~ — no existe; la búsqueda usa `PersonaController@search`
- ~~validación con jQuery Validate plugin~~ — el proyecto usa validación manual vanilla

---

## Notas de implementación

### Patrón a seguir

El scaffold del wizard JS debe exponer:

```js
const PedidoWizard = {
    pasoActual: 1,
    totalPasos: 4,
    irAPaso(n) { /* render stepper + show/hide sections */ },
    validarPaso(n) { /* return bool */ },
    siguiente() { if (this.validarPaso(this.pasoActual)) this.irAPaso(this.pasoActual + 1); },
    anterior() { this.irAPaso(this.pasoActual - 1); },
};
```

Copiar la mecánica del stepper de cotización (rellenar líneas `.wiz-step-line-fill`, marcar `.is-active` / `.is-complete`).

### Restricciones clave

- Validación del paso 1 antes de avanzar: `cliente_id` no vacío + `fecha_pedido` no vacío
- `fecha_pedido` default = hoy (`new Date().toISOString().slice(0,10)`)
- Autocomplete con debounce 300ms
- Chips de prioridad: click marca `is-selected`, set valor en hidden/select

### Referencias en el código

- `resources/views/admin/cotizaciones/scripts/main.blade.php` — lógica del paso 1 a portar
- `resources/views/admin/pedidos/index.blade.php:2300-2400` — lógica vieja de autocomplete cliente (referencia de cómo se hacía antes en pedidos)

---

## Criterios de aceptación

- [ ] Wizard abre en paso 1 con campos reales (no placeholder)
- [ ] Buscar documento existente → autocomplete muestra resultados → seleccionar hidrata campos
- [ ] "Crear cliente nuevo" funciona inline
- [ ] No se puede avanzar al paso 2 sin cliente + fecha_pedido
- [ ] Chips de prioridad funcionan
- [ ] Navegación stepper (continuar/anterior) renderiza correctamente
- [ ] Light + dark mode correctos

---

## QA manual

1. `/pedidos` → "Agregar Pedido" → wizard abre en paso 1
2. Tipear documento de cliente existente → autocomplete aparece → seleccionar
3. Verificar campos cliente se rellenan (nombre, email, teléfono)
4. Dejar fecha_pedido (default hoy), setear entrega, elegir prioridad Alta
5. Click "Continuar" → avanza a paso 2 (placeholder de TASK-012 visible)
6. Click "Anterior" → vuelve a paso 1 con datos preservados
7. Limpiar cliente → "Continuar" → bloquea con mensaje de validación
8. Probar "Crear cliente nuevo" → flujo inline
9. Dark mode → repetir

---

## Instrucciones para el ejecutor

1. Lee spec + verifica TASK-010 `completed`
2. Verifica contrato: `grep personas-search routes/web.php`
3. Header: `Status: in-progress`
4. Rama `feat/pedidos-wizard`
5. Implementa
6. QA manual
7. Mueve a `completed/`, llena Nota
8. NO mergear aún

---

## Nota de Completitud

**Completado por**: santiago (Claude)
**Fecha**: 2026-05-26
**Commits**: feat(pedidos): implementar paso 1 del wizard (cliente, fechas, prioridad)
**Notas**:
- Creado `resources/views/admin/pedidos/scripts/main.blade.php` con wizard scaffold (TOTAL_STEPS=4, showStep, validateStep, nextStep, prevStep, window.pedWizard API)
- Lógica paso 1: autocomplete personas-search con debounce 300ms, tarjeta cliente visual (avatar+roles), chips prioridad (Normal/Alta/Urgente), chips estado (solo edit), fecha_pedido default=hoy
- Modal `#modalAddCliente` incluido en `modals.blade.php` con handlers en `scripts/main.blade.php` (pedToggleClienteFields, save con POST /clientes, callback a pedAplicarPersonaAPedido)
- `index.blade.php` actualizado con @include modals, municipios-venezuela.js y @include scripts/main
- 24/24 assertions HTML + 30/30 assertions JS — Blade cache clean

**Desviaciones del spec**:
- El modal `#modalAddCliente` usa los mismos IDs que cotizaciones (sin sufijo `-ped`). No hay conflicto porque son páginas distintas. Se optó por reutilizar ids estándar para evitar duplicar la lógica del formulario.
- La tarjeta cliente en pedidos omite el bloque de estadísticas (cotizaciones_count) que cotizaciones sí muestra; no está en el mapeo de campos del spec de pedidos.
