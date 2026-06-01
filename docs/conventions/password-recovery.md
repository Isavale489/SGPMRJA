# Sistema de Recuperación de Contraseña

> Arquitectura del sistema multi-método (email + preguntas offline + reset admin). Leer antes de tocar el flujo de autenticación.

## Requisito de origen

El sistema debe poder recuperar contraseñas **sin internet ni electricidad**. Por eso el flujo tiene tres caminos paralelos: email (Breeze), preguntas de seguridad (offline), y reset por admin (fallback físico).

## Esquema de base de datos

### Tabla `user_recovery_question`
```
id, user_id (FK→user, cascade), pregunta_id (tinyint),
respuesta (string 255 bcrypt), orden (1-3), timestamps
UNIQUE(user_id, orden), UNIQUE(user_id, pregunta_id)
```

### Tabla `recovery_attempt` (bitácora)
```
id, user_id (FK nullable), email, ip, user_agent (500),
tipo enum('email','preguntas'),
resultado enum('exito','fallo','bloqueado'),
created_at (sin updated_at — ledger inmutable)
Índices: user_id, email, created_at
```

### Columnas en tabla `user`
- `recovery_locked_until` (timestamp nullable) — bloqueo temporal
- `recovery_failed_attempts` (tinyint, default 0) — contador
- `recovery_must_reset_questions` (boolean) — fuerza reconfiguración
- `password_reset_by_admin` (boolean) — fuerza cambio de contraseña en login

## Catálogo de preguntas

Definido en `config/recovery_questions.php` (no en tabla — son datos triviales, evita migraciones para cambios).

- 10 preguntas predefinidas.
- Cada usuario configura **3 obligatorias**, todas distintas, con respuestas distintas entre sí.

## Parámetros de seguridad

```php
// config/recovery_questions.php
'max_attempts_soft_lock' => 5,    // 5 fallos
'soft_lock_minutes'      => 15,   // bloqueo 15 min
'max_attempts_hard_lock' => 10,   // 10 fallos = bloqueo total
'reset_token_ttl'        => 300,  // 5 min entre validar respuestas y reset
```

## Normalización de respuestas

`RecoveryQuestionController::normalizeAnswer()` aplica:
1. Normalización Unicode NFC (tildes precompuestas vs combinadas).
2. Trim al inicio/final.
3. Colapso de espacios internos múltiples (`preg_replace('/\s+/u', ' ', ...)`).
4. Lowercase (mb_strtolower).

Comparación: case-insensitive vía `Hash::check` con el valor normalizado.

## Middleware: `EnsureRecoveryQuestionsConfigured`

Alias: `recovery.questions.required`. Aplicado al grupo `auth` en `routes/web.php`.

**Cadena de prioridades:**
1. Si `password_reset_by_admin = true` → fuerza `/force-password-change`.
2. Si `recovery_must_reset_questions = true` o `!hasRecoveryQuestionsConfigured()` → fuerza `/profile`.
3. Caso normal → `next()`.

**Whitelist de rutas permitidas en cada estado:**
- Force password change: `auth.force-password-change.show/process`, `logout`.
- Force questions: `profile.edit`, `profile.update`, `profile.recovery-questions.update`, `logout`.

## Flujo completo de recuperación

1. Login → enlace "¿Olvidaste tu contraseña?" → `/recovery/method`.
2. Selección método: **email** (Breeze) o **preguntas** (offline).
3. Email → `/recovery/email` → si existe + tiene preguntas → mensaje genérico.
4. Mostrar las 3 preguntas → `/recovery/questions`.
5. Usuario responde → si las 3 OK → token de sesión (5 min TTL) + reset contador.
6. Si falla → incrementar `recovery_failed_attempts`, log fallo.
7. 5 fallos → `recovery_locked_until = now() + 15min`.
8. 10 fallos → bloqueo total (requiere desbloqueo admin).
9. Si éxito → `/recovery/reset/{token}` → nueva contraseña.
10. Al guardar: `Hash::make`, regenera `remember_token` (cierra sesiones), marca `recovery_must_reset_questions=true`, redirect login.

## Reset por admin (fallback físico)

Cuando un usuario no tiene preguntas configuradas y se va la luz:

1. Admin va físicamente con el usuario → `/admin/users` → botón llave 🔑.
2. SweetAlert pide contraseña temporal (mínimo 8 chars).
3. POST `/users/{id}/reset-password`:
   - `password` hasheada.
   - `remember_token = null` (cierra sesiones existentes).
   - `password_reset_by_admin = true`.
   - `recovery_must_reset_questions = true`.
   - Limpia bloqueos (`recovery_failed_attempts=0`, `recovery_locked_until=null`).
4. **El admin NO puede resetear su propia contraseña** (validación explícita).
5. Usuario va al sistema con la temporal → login OK.
6. Middleware detecta `password_reset_by_admin=true` → redirige a `/force-password-change`.
7. Usuario cambia contraseña (validación `current_password` + `different`).
8. Middleware detecta sin preguntas → redirige a `/profile`.
9. Configura preguntas → finalmente puede usar el sistema.

## Edición granular de preguntas

`ProfileController::updateRecoveryQuestions` acepta:
- `cambios[N][editing]` = "0" o "1".
- `cambios[N][pregunta_id]` (siempre).
- `cambios[N][respuesta]` (solo si editing=1).
- `current_password` (requerido si está configurado y hay edición).

**Configuración inicial**: los 3 bloques deben tener `editing=1`.
**Edición sin cambios**: no actualiza nada, redirect con `warning_recovery_no_changes`.
**Edición con cambios**: requiere contraseña actual + actualiza solo bloques editados.

Vista usa componente Alpine `recoveryBlock(startInEdit)`:
- View mode: pregunta + `●●●●●●●●` + botón Cambiar.
- Edit mode: select + input + botón "Cancelar este cambio".
- `cancel()` resetea select a `data.originalValue` (attr `data-original-value`), limpia input, dispara `change` event para refrescar selects de otros bloques.

## Anti-autofill agresivo

Los navegadores ignoran `autocomplete="off"`. Solución combinada:

1. `<form autocomplete="off">`.
2. **Decoys ocultos** al inicio del form que absorben el autofill:
   ```html
   <input type="text" name="autofill_decoy_user" autocomplete="username" hidden-style>
   <input type="password" name="autofill_decoy_pass" autocomplete="current-password" hidden-style>
   ```
3. Inputs reales con `autocomplete="new-password"` (truco que sí respetan).
4. En respuestas: `autocorrect="off"`, `autocapitalize="off"`, `spellcheck="false"` para evitar que el corrector cambie la respuesta.

## Banner post-intento en dashboard

`HomeController::getRecoveryAlert()` busca último intento de las últimas 24h del usuario logueado y muestra banner una sola vez por sesión (`Session::put('recovery_alert_shown', true)`).

## Archivos relevantes

### Modelos
- `app/Models/User.php` — relaciones, casts, helpers (`hasRecoveryQuestionsConfigured`, `isRecoveryLocked`, `isRecoveryHardLocked`).
- `app/Models/UserRecoveryQuestion.php` — accessor `pregunta_texto` lee del catálogo.
- `app/Models/RecoveryAttempt.php` — sin `updated_at`.

### Controllers
- `app/Http/Controllers/Auth/RecoveryQuestionController.php` — flujo completo.
- `app/Http/Controllers/Auth/ForcePasswordChangeController.php` — cambio forzoso post-reset.
- `app/Http/Controllers/ProfileController.php` — `updateRecoveryQuestions` con edición granular.
- `app/Http/Controllers/UserController.php` — `unlockRecovery` + `resetPassword` admin.
- `app/Http/Controllers/HomeController.php` — `getRecoveryAlert` para banner.

### Middleware
- `app/Http/Middleware/EnsureRecoveryQuestionsConfigured.php` (alias `recovery.questions.required`).

### Config
- `config/recovery_questions.php`.

### Vistas
- `resources/views/auth/recovery/{method,email,answers,reset,locked}.blade.php`
- `resources/views/auth/force-password-change.blade.php`
- `resources/views/profile/edit.blade.php`
- `resources/views/profile/partials/update-recovery-questions-form.blade.php`

## Dependencia crítica: Alpine.js

El admin layout `resources/views/admin/layouts/app.blade.php` carga Alpine vía CDN:
```html
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
```

`resources/js/app.js` SÍ importa Alpine, pero el admin layout **no usa `@vite`**. El CDN es la solución de menor impacto. Si Alpine deja de cargar, los `x-data`, `x-show`, `x-init`, `@click` del perfil dejan de funcionar.
