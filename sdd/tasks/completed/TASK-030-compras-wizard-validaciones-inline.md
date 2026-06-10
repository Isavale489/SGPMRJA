# TASK-030: Refactor validateStep() — reemplazar Swal por marcarInvalido en wizard de Compras

**Feature**: FEAT-COMPRAS-UX-01 — Refactor UX/UI Módulo de Compras
**Spec**: `sdd/specs/refactor-compras-ui.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: M (2-4h)
**Depends-on**: none
**Assigned-to**: Claude Code

---

## Contexto

El wizard de Nueva Compra tiene una función `validateStep(n)` que actualmente muestra `Swal.fire` genéricos cuando el usuario deja un campo vacío y avanza de paso. El estándar del sistema (documentado en `docs/conventions/js-validations.md`) exige usar `marcarInvalido()` para pintar los campos en rojo con mensaje inline, y añadir handlers `blur` para feedback en tiempo real.

Ref. spec § Módulo 1.

---

## Scope

- En `validateStep(1)`: reemplazar los dos `Swal.fire` por `marcarInvalido($('#c-proveedor'), '...')` y `marcarInvalido($('#c-fecha'), '...')`. Usar `$('#c-proveedor').trigger('focus')` o su equivalente Select2 en el primer error.
- En `validateStep(2)`:
  - "Sin ítems" → mostrar un elemento `#c-items-error-msg` (ver Notas) — SweetAlert permitido aquí por convención (campo oculto / contenedor, no un input).
  - "Insumo duplicado" / "Datos incompletos" → identificar la primera fila con el problema y llamar `marcarInvalido()` sobre la celda-campo con error (`c-insumo`, `c-cantidad`, `c-costo`).
- Añadir handler `select2:close` en `#c-proveedor` para feedback inmediato (Select2 oculta el nativo).
- Añadir handler `blur` en `#c-fecha` para feedback inmediato.
- Llamar `marcarValido($('#c-proveedor'))` / `marcarValido($('#c-fecha'))` cuando el campo ya tiene valor y se activa el evento.

**NO está en alcance**:
- Validaciones de modales anidados (`cirForm`, `cprForm`) → TASK-032
- Anchos de DataTable → TASK-031
- Lógica backend / `CompraService` / `CompraController`

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/compras/scripts/create.blade.php` | MODIFY | Reemplazar Swal en `validateStep`, añadir blur/select2:close handlers |

---

## Codebase Contract (Anti-Alucinación)

> **CRÍTICO**: Esta sección contiene referencias VERIFICADAS del código real.
> El implementador DEBE usar estos imports, IDs y firmas EXACTOS.

### Helpers globales (verificados en `app.blade.php`)
```js
marcarInvalido($campo, mensaje)   // añade is-invalid + invalid-feedback al campo
marcarValido($campo)              // limpia is-invalid
limpiarValidacion($campo)         // resetea estado (útil en reset del form)
```

### IDs verificados en `modals/create.blade.php`
```
#c-proveedor        → input oculto que guarda el ID del proveedor seleccionado
                      (el Select2 visible es el autocomplete, no este campo)
#c-fecha            → <input type="date"> en el paso 1
#c-items-tbody      → <tbody> de la tabla de ítems (paso 2)
.c-insumo           → <select> de insumo por fila
.c-cantidad         → <input type="number"> de cantidad por fila
.c-costo            → <input type="number"> de costo por fila
```

### Función a modificar (líneas ~293-347 de `scripts/create.blade.php`)
```js
function validateStep(n) {
    if (n === 1) {
        if (!$('#c-proveedor').val()) {
            Swal.fire({ title: 'Campo requerido', text: 'Seleccione un proveedor.', ... });
            // → REEMPLAZAR por: marcarInvalido($('#c-proveedor'), 'Seleccione un proveedor.');
        }
        if (!$('#c-fecha').val()) {
            Swal.fire({ title: 'Campo requerido', text: 'Ingrese la fecha de compra.', ... });
            // → REEMPLAZAR por: marcarInvalido($('#c-fecha'), 'Ingrese la fecha de compra.');
        }
    }
    // Swal "Sin ítems" (contenedor) → PUEDE quedarse como Swal (convención hidden-field)
    // Swal "Insumo duplicado" / "Datos incompletos" → marcarInvalido en la primera fila problemática
}
```

### Patrón blur/select2:close (de `docs/conventions/js-validations.md`)
```js
// Select2 — NO usar blur nativo
$('#c-proveedor').on('select2:close', function () {
    if (!$(this).val()) marcarInvalido($(this), 'Seleccione un proveedor.');
    else marcarValido($(this));
});

// Input nativo
$('#c-fecha').on('blur', function () {
    if (!$(this).val()) marcarInvalido($(this), 'Ingrese la fecha de compra.');
    else marcarValido($(this));
});
```

### Convenciones a respetar
- `docs/conventions/js-validations.md` § "Campos hidden con error vía SweetAlert" — contenedores / inputs ocultos SÍ pueden usar Swal
- `docs/conventions/js-validations.md` § "Select2 — usar select2:close en vez de blur"

### NO existe — no referenciar
- ~~`resources/views/admin/compras/create.blade.php`~~ — el modal está en `resources/views/admin/compras/modals/create.blade.php`
- ~~`#c-proveedor-select2`~~ — no existe; el campo real es `#c-proveedor` (hidden)
- ~~Nuevos endpoints backend~~ — no crear
- ~~`novalidate` en los forms~~ — **ya está presente** en `compraForm`, `cirForm` y `cprForm`; no añadir de nuevo

---

## Notas de implementación

### Focus en primer error
```js
// Cuando el primer error es el proveedor:
marcarInvalido($('#c-proveedor'), 'Seleccione un proveedor.');
// Focus al contenedor visible del Select2 (no al hidden):
$('#c-proveedor').parent().find('.select2-selection').trigger('focus');
return false;
```

### Restricciones clave
- Respetar el orden actual de validaciones dentro de `validateStep` — solo reemplazar los Swal, no reestructurar la función
- Al añadir `marcarValido` en el blur, verificar que el campo efectivamente tenga valor antes de limpiar (no limpiar si sigue vacío)
- Para las filas de ítems con error, marcar solo la primera fila con problema para no inundar la UI

---

## Criterios de aceptación

- [ ] `validateStep(1)` no llama `Swal.fire` para errores de campo vacío
- [ ] Al intentar avanzar con proveedor vacío → el campo se pinta rojo con mensaje inline
- [ ] Al intentar avanzar con fecha vacía → el campo se pinta rojo con mensaje inline
- [ ] Al llenar el campo y mover el foco (blur / select2:close) → el rojo desaparece
- [ ] "Sin ítems" y "Insumo duplicado" muestran feedback coherente (Swal o inline según convención)
- [ ] Sin `Swal.fire` de "Campo requerido" o "Datos incompletos" al avanzar pasos
- [ ] Dark mode no rompe estilos de error
- [ ] PR contra `enmanuel` con descripción enlazando esta task

---

## QA manual

1. Abrir Compras → Nueva Compra.
2. En Paso 1, dejar proveedor vacío y presionar "Continuar" → debe aparecer borde rojo + texto bajo el campo, SIN popup de SweetAlert.
3. Dejar fecha vacía y presionar "Continuar" → idem.
4. Dejar proveedor seleccionado, quitar el foco → rojo desaparece.
5. En Paso 2, sin ítems → click "Continuar" → verificar comportamiento definido (Swal o banner inline).
6. En Paso 2, añadir fila con insumo sin seleccionar → "Continuar" → primera fila se pinta roja.
7. Probar en dark mode (toggle de tema).

---

## Instrucciones para el ejecutor

Cuando tomes esta task:

1. **Lee el spec** completo en `sdd/specs/refactor-compras-ui.spec.md`.
2. **Verifica dependencias** — esta task no tiene dependencias; puede tomarse directamente.
3. **Verifica el Codebase Contract** antes de codificar:
   - Confirma que `marcarInvalido` está definida en `app.blade.php`
   - Confirma IDs de campos leyendo `resources/views/admin/compras/modals/create.blade.php`
4. **Actualiza el header**: `Status: in-progress`, `Assigned-to: <tu-nombre>`.
5. **Crea rama**: `git checkout -b feat/TASK-030-compras-validaciones` desde `enmanuel`.
6. **Implementa** dentro del scope. Si descubres trabajo extra, créalo como task nueva.
7. **Verifica** los criterios de aceptación y el QA manual.
8. **Mueve este archivo** a `sdd/tasks/completed/TASK-030-compras-wizard-validaciones-inline.md`.
9. **PR** contra `enmanuel`.

---

## Nota de Completitud

*(Llenar al terminar)*

**Completado por**: Claude Code
**Fecha**: 2026-06-10
**Commits**: —
**Notas**: collectItems refactorizado para trackear la primera fila con error y llamar marcarInvalido en el campo específico (insumo/cantidad/costo). Swal conservado solo para "Sin ítems" (tabla vacía). Blur handler en #c-fecha y select2:close en #c-proveedor añadidos.
**Desviaciones del spec**: ninguna
