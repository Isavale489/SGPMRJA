# TASK-042: Campos dinámicos jurídico/natural en detalle de Cotizaciones

**Feature**: FEAT-006 — correcciones-ux-detalles
**Spec**: `sdd/specs/correcciones-ux-detalles.spec.md`
**Status**: pending
**Priority**: high
**Esfuerzo estimado**: S (< 2h)
**Depends-on**: none
**Assigned-to**: unassigned

---

## Contexto

Implementa el **Módulo 1** del spec. Tras migrar el detalle de Cotizaciones al wizard `#viewModal`, los clientes **jurídicos** muestran el campo "Apellido" como **"N/A"** y no usan la **Razón Social** como nombre. El backend (`CotizacionController::show`) **ya expone** `tipo_documento` y `razon_social` en el JSON; el defecto es 100% front-end en el handler que puebla el modal "Ver".

---

## Scope

- En el handler `.view-btn` de `cotizaciones/scripts/main.blade.php`, detectar si el cliente es **jurídico** por prefijo de documento (`tipo_documento ∈ {'J-','G-'}`).
- Si **jurídico**: poblar `#view-cliente-nombre` con `data.cliente.razon_social` (fallback a `nombre` si viene vacía) y **ocultar** el bloque de "Apellido" (no mostrar "N/A").
- Si **natural** (`V-`, `E-`): conservar el comportamiento actual (Nombre + Apellido).
- En `cotizaciones/modals.blade.php`, envolver el `col-6` de "Apellido" (Paso 1) en un contenedor con `id` (`#view-apellido-wrap`) para poder ocultarlo vía `d-none` desde el JS, sin borrarlo del DOM.
- Mantener intactas las ramas existentes de **cliente eliminado** (badge "Eliminado" + `text-muted`) y **cliente no encontrado**.

**NO está en alcance**:
- Cambios en backend / `CotizacionController` (el payload ya trae los datos).
- Separación visual de secciones / rediseño de las cards (eso es TASK-045).
- El botón PDF (TASK-044) ni el overflow de email (TASK-043).
- Replicar esto en Pedidos/Órdenes (verificado: no tienen el bug).

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/cotizaciones/scripts/main.blade.php` | MODIFY | Handler `.view-btn` (≈ líneas 2745-2767): lógica jurídico/natural |
| `resources/views/admin/cotizaciones/modals.blade.php` | MODIFY | Paso 1: envolver bloque "Apellido" (líneas 66-72) en `#view-apellido-wrap` |

---

## Codebase Contract (Anti-Alucinación)

> Verificado por lectura directa en `enmanuel` (2026-06-18).

### Endpoint / payload verificado
```php
// app/Http/Controllers/CotizacionController.php:235  public function show($id)
//   $response['cliente'] = [
//     'id','nombre','apellido','email','telefono','documento',
//     'tipo_documento' => optional($cliente->persona)->tipo_documento,  // :256  ('V-','J-','E-','G-')
//     'razon_social'   => optional($cliente->persona)->razon_social,    // :257
//     'direccion','ciudad','eliminado' (bool)
//   ];
// → tipo_documento y razon_social YA llegan al front. NO tocar el backend.
```

### Markup actual (modals.blade.php, #viewModal Paso 1)
```text
#view-cliente-nombre ...... :64   (col-6)
Bloque "Apellido" ......... :66-72  (div.col-6 ... #view-cliente-apellido :71)  ← envolver en #view-apellido-wrap
#view-ci-rif (documento) .. :78
#view-cliente-telefono .... :85
#view-cliente-email ....... :92
```

### Handler actual (scripts/main.blade.php)
```js
// $('#cotizaciones-table').on('click', '.view-btn', ...)  :2745
var nombreHtml = data.cliente.nombre || 'N/A';            // :2753  ← debe usar razon_social si jurídico
// ... badge 'Eliminado' si data.cliente.eliminado         // :2754-2756
function vm(v){ return muted ? '<span class="text-muted">'+(v||'')+'</span>' : (v||'N/A'); } // :2759
$('#view-cliente-apellido').html(vm(data.cliente.apellido)); // :2760  ← ocultar bloque si jurídico
// rama else (cliente no encontrado) ...................... :2764-2766
```

### Patrón de detección jurídico YA usado en el mismo archivo (reusar criterio)
```js
var isJuridico = p.tipo_documento === 'J-' || p.tipo_documento === 'G-';   // main.blade.php:3068
var nombreMostrar = isJuridico && persona.razon_social ? persona.razon_social : ...; // :3029, :3111
```

### Convenciones a respetar
- `docs/conventions/wizard-pattern.md` — wizard `.wiz-*` de cotizaciones.
- Convención del proyecto "Documento primero" en bloques de identificación — respetar el orden actual de campos.

### NO existe — no referenciar
- ~~accessor "esJuridico" en `Cliente`~~ — no existe; detectar por prefijo en JS.
- ~~campo `razon_social` en modelo `Cliente`~~ — vive en `Persona`; ya viene en el JSON de `show()`.

---

## Notas de implementación

### Patrón a seguir
```js
// dentro del success del handler .view-btn, rama if (data.cliente) {
var esJuridico = ['J-','G-'].includes(String(data.cliente.tipo_documento || '').toUpperCase());
var nombreBase = esJuridico
    ? (data.cliente.razon_social || data.cliente.nombre || 'N/A')
    : (data.cliente.nombre || 'N/A');
// ... conservar el badge 'Eliminado' concatenado a nombreBase ...
$('#view-cliente-nombre').html(nombreHtml);
$('#view-apellido-wrap').toggleClass('d-none', esJuridico);   // ocultar Apellido en jurídicos
if (!esJuridico) { $('#view-cliente-apellido').html(vm(data.cliente.apellido)); }
```
- Edición de Blade: usar **Edit**, nunca Write, sobre estos archivos (memoria del proyecto).
- Al envolver el bloque Apellido, conservar todas sus clases (`col-6 d-flex align-items-start`, icono `emp-icon-box`).

### Restricciones clave
- Sin estilos inline; sin cambios de CSS en esta task.
- No romper la rama de cliente eliminado/no encontrado.

---

## Criterios de aceptación

- [ ] Cliente **natural** (V-/E-): detalle muestra Nombre + Apellido, sin "N/A" indebido.
- [ ] Cliente **jurídico** (J-/G-): detalle muestra **Razón Social** como nombre y **NO** muestra el bloque "Apellido".
- [ ] Cliente jurídico **sin** razón social: fallback a nombre/documento, nunca "N/A" en Apellido.
- [ ] Cliente **eliminado**: conserva badge "Eliminado" + atenuado.
- [ ] `data.cliente` null: conserva "Cliente no encontrado".
- [ ] PR contra `enmanuel` enlazando esta task.

---

## QA manual

1. Login admin → `/cotizaciones`.
2. "Ver" en cotización de cliente **natural** → Paso 1: Nombre y Apellido poblados.
3. "Ver" en cotización de cliente **jurídico** (J-/G-) → Paso 1: Razón Social como nombre, sin campo "Apellido".
4. (Si hay dato) cliente jurídico sin razón social → no aparece "N/A" en Apellido.
5. "Ver" cotización con cliente **eliminado** → badge "Eliminado" presente.
6. Dark mode: revisar contraste del Paso 1.

---

## Instrucciones para el ejecutor

1. Lee el spec completo.
2. Verifica el Codebase Contract con `grep`/`read` antes de codear (las líneas pudieron moverse).
3. Header: `Status: in-progress`, `Assigned-to: <tu-nombre>`.
4. Rama: `git checkout -b feat/TASK-042-cotiz-detalle-juridico` desde `enmanuel` (o trabaja en `fix/correcciones-ux-detalles`).
5. Implementa dentro del scope; usa **Edit** en los blade.
6. Verifica criterios + QA.
7. Mueve este archivo a `sdd/tasks/completed/` y rellena la Nota de Completitud.
8. PR contra `enmanuel`.

---

## Nota de Completitud

*(Llenar al terminar)*

**Completado por**:
**Fecha**:
**Commits**:
**Notas**:
**Desviaciones del spec**: ninguna
