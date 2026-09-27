# TASK-032: Validaciones inline en modales anidados (Insumo Rápido y Proveedor Rápido)

**Feature**: FEAT-COMPRAS-UX-01 — Refactor UX/UI Módulo de Compras
**Spec**: `sdd/specs/refactor-compras-ui.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: M (2-4h)
**Depends-on**: TASK-030
**Assigned-to**: Claude Code

---

## Contexto

Los modales anidados `#crearInsumoRapidoModal` (`cirForm`) y `#crearProveedorRapidoModal` (`cprForm`) actualmente manejan sus errores de dos maneras inconsistentes:
- `cirForm` submit: usa `.addClass('is-invalid')` / `.removeClass('is-invalid')` directamente, saltándose los helpers globales `marcarInvalido`/`marcarValido`.
- `cprForm` submit: muestra `Swal.fire` de error cuando el backend rechaza, pero no valida campos requeridos visualmente antes del submit.

La tarea es unificar ambos modales con el patrón estándar de `docs/conventions/js-validations.md`: blur handlers, `marcarInvalido`/`marcarValido`, y validación previa al submit.

Depende de TASK-030 para evitar conflictos en el mismo archivo `scripts/create.blade.php`.

Ref. spec § Módulo 3.

---

## Scope

### cirForm (`#crearInsumoRapidoModal`)
- Añadir handlers `blur` para campos requeridos: `#cir-nombre-field`, `#cir-tipo-field`, `#cir-unidad-field`, `#cir-costo-field`.
- En el submit handler (`#cir-submit-btn`), reemplazar el bloque `.addClass('is-invalid')` / `.removeClass('is-invalid')` por `marcarInvalido()`/`marcarValido()` + `limpiarValidacion()` en el reset del modal.
- Crear función `validarCirForm()` que devuelve boolean y usa `marcarInvalido`.

### cprForm (`#crearProveedorRapidoModal`)
- Añadir handlers `blur` para campos requeridos **según el tipo activo** (jurídico / natural):
  - Jurídico: `#cpr-razon-social-field`, `#cpr-telefono-jur-number-field`
  - Natural: `#cpr-nombre-field`, `#cpr-apellido-field`, `#cpr-telefono-nat-number-field`
  - Ambos: `#cpr-tipo-proveedor-field` (select nativo — usar `change` en vez de `blur`)
- En el submit handler, añadir validación pre-AJAX con `marcarInvalido` antes de llamar al backend.
- Al resetear el modal (`hidden.bs.modal`), llamar `limpiarValidacion()` en todos los campos.

**NO está en alcance**:
- Validaciones del wizard principal → TASK-030 (prerequisito)
- Anchos de DataTable → TASK-031
- Cambios al backend (endpoints, controllers)
- Cambiar los `Swal.fire` de éxito/error de respuesta del servidor → esos son notificaciones post-submit, no validaciones de campo. Quedan como están.

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/compras/scripts/create.blade.php` | MODIFY | Añadir blur handlers + reemplazar is-invalid directo en cirForm y cprForm |

---

## Codebase Contract (Anti-Alucinación)

> **CRÍTICO**: Esta sección contiene referencias VERIFICADAS del código real.

### Helpers globales (verificados en `app.blade.php`)
```js
marcarInvalido($campo, mensaje)   // añade is-invalid + invalid-feedback
marcarValido($campo)              // limpia is-invalid
limpiarValidacion($campo)         // resetea estado (para usar en hidden.bs.modal)
```

### IDs verificados en `modals/create.blade.php` — cirForm
```
#crearInsumoRapidoModal   → ID del modal contenedor
#cirForm                  → <form novalidate> del modal
#cir-submit-btn           → botón de submit
#cir-nombre-field         → <input type="text"> — requerido
#cir-codigo-field         → <input type="text"> — opcional
#cir-tipo-field           → <select> nativo — requerido
#cir-unidad-field         → <select> nativo — requerido
#cir-costo-field          → <input type="number"> — requerido (> 0)
```

### IDs verificados en `modals/create.blade.php` — cprForm (condicionales)
```
#crearProveedorRapidoModal       → ID del modal contenedor
#cprForm                         → <form novalidate> del modal
#cpr-submit-btn                  → botón de submit
#cpr-tipo-proveedor-field        → <select> (x-forms.select) — siempre requerido
/* Jurídico */
#cpr-rif-number-field            → <input> — requerido si tipo=jurídico
#cpr-razon-social-field          → <input> — requerido si tipo=jurídico
#cpr-telefono-jur-number-field   → <input> — requerido si tipo=jurídico
/* Natural */
#cpr-nombre-field                → <input> — requerido si tipo=natural
#cpr-apellido-field              → <input> — requerido si tipo=natural
#cpr-documento-identidad-field   → <input> — requerido si tipo=natural
#cpr-telefono-nat-number-field   → <input> — requerido si tipo=natural
```

### Estado actual del submit de cirForm (líneas ~756-835 de `scripts/create.blade.php`)
```js
// ANTES (a reemplazar):
$('#cir-nombre-field, #cir-codigo-field, ...').removeClass('is-invalid');
// DESPUÉS (nuevo patrón):
limpiarValidacion($('#cir-nombre-field'));
// ... etc.
```

### Función de validación a crear (patrón de `js-validations.md`)
```js
function validarCirForm() {
    let valido = true;
    if (!$('#cir-nombre-field').val().trim()) {
        marcarInvalido($('#cir-nombre-field'), 'El nombre del insumo es requerido.'); valido = false;
    } else { marcarValido($('#cir-nombre-field')); }
    if (!$('#cir-tipo-field').val()) {
        marcarInvalido($('#cir-tipo-field'), 'Seleccione el tipo de insumo.'); valido = false;
    } else { marcarValido($('#cir-tipo-field')); }
    // ... idem para unidad y costo
    return valido;
}
```

### Convenciones a respetar
- `docs/conventions/js-validations.md` — patrón blur + submit, select nativo usa `change`/`blur` (no `select2:close`)
- Los `Swal.fire` de respuesta de servidor (éxito/error HTTP) NO se tocan — son notificaciones, no validaciones de campo

### NO existe — no referenciar
- ~~`resources/views/admin/compras/create.blade.php`~~ — el archivo real es `resources/views/admin/compras/modals/create.blade.php`
- ~~`select2:close` en `#cir-tipo-field`~~ — es select nativo, NO Select2; usar `change` o `blur`
- ~~`select2:close` en `#cpr-tipo-proveedor-field`~~ — verificar si es Select2 antes de asumir; si no, usar `change`
- ~~Validación en el componente Blade `<x-forms.select>`~~ — el componente solo renderiza HTML; el JS va en `scripts/create.blade.php`

---

## Notas de implementación

### Limpiar validaciones al cerrar el modal
```js
$('#crearInsumoRapidoModal').on('hidden.bs.modal', function () {
    $('#cirForm')[0].reset();
    $('#cir-nombre-field, #cir-tipo-field, #cir-unidad-field, #cir-costo-field').each(function () {
        limpiarValidacion($(this));
    });
});
```

### cprForm — validación condicional por tipo
El tipo de proveedor activo se lee con `$('#cpr-tipo-proveedor-field').val()`. Validar solo los campos visibles del tipo activo.

```js
function validarCprForm() {
    let valido = true;
    const tipo = $('#cpr-tipo-proveedor-field').val();
    if (!tipo) { marcarInvalido($('#cpr-tipo-proveedor-field'), 'Seleccione el tipo de proveedor.'); valido = false; }
    if (tipo === 'juridico') {
        if (!$('#cpr-razon-social-field').val().trim()) {
            marcarInvalido($('#cpr-razon-social-field'), 'La razón social es requerida.'); valido = false;
        }
        // ... etc.
    } else if (tipo === 'natural') {
        // ... validar nombre, apellido, etc.
    }
    return valido;
}
```

### Restricciones clave
- El orden de validación en el submit handler debe ser: primero `validarCirForm()` / `validarCprForm()`, y solo si devuelve `true`, proceder con el AJAX
- No eliminar el `is-invalid` / `invalid-feedback` que Bootstrap añade automáticamente a través de `marcarInvalido` — verificar que el CSS del sistema tiene definidos estos estilos

---

## Criterios de aceptación

- [ ] En `crearInsumoRapidoModal`: submit sin nombre → campo se pinta rojo con mensaje inline (sin Swal)
- [ ] En `crearInsumoRapidoModal`: submit sin tipo/unidad/costo → misma experiencia
- [ ] Al llenar el campo y mover el foco → el error desaparece
- [ ] Al cerrar el modal → todos los errores visuales se limpian
- [ ] En `crearProveedorRapidoModal`: campos requeridos del tipo activo se validan inline
- [ ] Los `Swal.fire` de respuesta del servidor (éxito, error HTTP) no se modifican
- [ ] PR contra `enmanuel` con descripción enlazando esta task

---

## QA manual

1. Ir a Compras → Nueva Compra → Paso 1 → click "Nuevo Proveedor".
2. Dejar todos los campos vacíos → click "Guardar y seleccionar" → verificar errores inline rojos SIN popup Swal de validación.
3. Seleccionar tipo "Jurídico", completar solo el nombre → intentar guardar → validar campos restantes pintados.
4. Cerrar el modal → volver a abrir → confirmar que no quedan errores residuales.
5. Repetir pasos 2-4 para "Nuevo Insumo" (`crearInsumoRapidoModal`).
6. Completar todos los campos de insumo → guardar → debe funcionar correctamente.
7. Probar en dark mode.

---

## Instrucciones para el ejecutor

Cuando tomes esta task:

1. **Lee el spec** completo en `sdd/specs/refactor-compras-ui.spec.md`.
2. **Verifica dependencias** — TASK-030 debe estar en `completed/` antes de empezar (misma rama/archivo).
3. **Verifica el Codebase Contract** antes de codificar:
   - Lee `resources/views/admin/compras/modals/create.blade.php` sección `crearInsumoRapidoModal` para confirmar IDs
   - Lee `resources/views/admin/compras/scripts/create.blade.php` líneas ~750-835 para el submit handler actual de cirForm
   - Confirma si `#cpr-tipo-proveedor-field` es Select2 o nativo (afecta el evento a usar)
4. **Actualiza el header**: `Status: in-progress`, `Assigned-to: <tu-nombre>`.
5. **Crea rama**: `git checkout -b feat/TASK-032-compras-modales-validaciones` desde `enmanuel` (o desde la rama de TASK-030 si no está mergeada aún).
6. **Implementa** dentro del scope.
7. **Verifica** los criterios de aceptación y el QA manual.
8. **Mueve este archivo** a `sdd/tasks/completed/TASK-032-compras-modales-anidados-validaciones.md`.
9. **PR** contra `enmanuel`.

---

## Nota de Completitud

*(Llenar al terminar)*

**Completado por**: Claude Code
**Fecha**: 2026-06-10
**Commits**: —
**Notas**: cirForm — reemplazado el bloque flag/is-invalid por validarCirForm() + blur/change handlers + hidden.bs.modal cleanup. cprForm — añadido validarCprForm() con lógica condicional por tipo (jurídico/natural), blur handlers que guardan el tipo activo antes de validar, y hidden.bs.modal cleanup. Tres edits quirúrgicos al mismo archivo scripts/create.blade.php.
**Desviaciones del spec**: ninguna
