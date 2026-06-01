---
name: Rediseño UI flujo recuperación de contraseña
description: Detalle del rediseño visual de las 5 vistas del flujo recovery y el modal admin de reset
type: project
originSessionId: b5259b25-855d-4d1a-bfcc-0b3207fb0e5a
---
# Rediseño UI flujo recuperación de contraseña (2026-04-28)

## guest.blade.php — título dinámico
- Agregado `@props(['title' => 'Bienvenido de nuevo', 'icon' => 'bx-user-circle'])`
- Cada vista recovery pasa su propio título e ícono al card header
- Login no cambia (usa los defaults)

## Vistas rediseñadas

### method.blade.php
- Title: "Recuperar contraseña" / icon: `bx-lock-open-alt`
- Dos **option cards** (`.recovery-option-card`) con borde-izquierda de color:
  - Email: navy `#1e3c72`, ícono cuadrado `recovery-option-icon--navy`
  - Preguntas: emerald `#10b981`, ícono cuadrado `recovery-option-icon--emerald`
- Flecha `›` que se desplaza al hover
- Textos principales en `.recovery-option-strong` (color adaptativo light/dark)
- Textos de conectividad en `<strong>` sin clase (heredan gris del descriptor)

### email.blade.php
- Title: "Buscar cuenta" / icon: `bx-search-alt-2`
- Campo email en `.recovery-field-block` con header (ícono circular navy + label)
- Error genérico del controller se muestra como `.recovery-info-notice` (amber, borde-izquierda)
  — NO como `invalid-feedback` rojo dentro del input-group
- El campo email NO se pone rojo al error (mensaje es informativo, no alarmante)

### answers.blade.php
- Title: "Verificar identidad" / icon: `bx-shield-quarter`
- Hint compacto en una sola línea horizontal:
  - Fila 1: ícono `bx-info-circle` + "Escribe las respuestas que registraste en tu perfil."
  - Fila 2 (`.recovery-hint-pills`): label "El sistema ignora →" + 3 píldoras (Mayúsculas, Tildes, Espacios extra)
  - Píldoras alineadas con `padding-left: 0` (ancho completo)
- Error de respuestas incorrectas: `.recovery-error-notice` (rojo, borde-izquierda)

### reset.blade.php
- Title: "Nueva contraseña" / icon: `bx-lock-alt`
- Badge "Identidad verificada" emerald al tope (`.recovery-verified-badge`)
- Dos campos de contraseña en `.recovery-q-block` individuales
- Ícono de candado en círculo navy (`.recovery-q-num`) en lugar de número
- Show/hide password con `btn-show-pass[data-target]` (patrón guest layout)

### locked.blade.php
- Title: "Acceso bloqueado" / icon: `bx-lock`
- Ícono 72px con animación `lockPulse`:
  - Soft lock: amber `#d97706`, reloj `bx-time-five`
  - Hard lock: rojo `#dc3545`, candado `bx-lock`
- Card informativa `.recovery-lock-card--soft/--hard` con borde-izquierda de color

## Modal reset password — /admin/users (commit 4da7a49)

Convertido de SweetAlert2 a Bootstrap modal con clase `atlantico-modal`.

**Por qué se abandonó Swal:** SweetAlert2 v11.14.1 no tiene `.swal2-header` como
wrapper del título — el título es hijo directo del popup. Intentar aplicar gradiente
al header vía CSS fue problemático. La solución correcta es usar el patrón estándar
del proyecto (Bootstrap modal `atlantico-modal`).

**Estructura del modal:**
- ID: `resetPasswordModal`
- Header: gradiente navy vía `atlantico-modal` (estándar del proyecto)
- Tarjeta usuario: nombre + email con borde-izquierda navy (pobladp via `data-name`/`data-email`)
- `generateButtons()` ahora recibe `userName` y `userEmail` y los pasa como `data-` al botón
- 2 campos de contraseña con show/hide independientes (`#rp-toggle-pass`, `#rp-toggle-pass-confirm`)
- Validación inline en `#rp-error` (mínimo 8 chars + coincidencia)
- Botón "Resetear": gradiente navy inline `linear-gradient(135deg,#1e3c72,#2a5298)`
- Spinner en botón durante AJAX
- Al éxito: cierra modal + Swal toast de confirmación
- Al error AJAX: muestra mensaje en `#rp-error` (no cierra el modal)

**CSS limpio:** Se eliminaron `.swal-header-navy` y `.swal-navy-header` de custom.css
(intentos fallidos de header Swal que quedaron en la sesión anterior).
