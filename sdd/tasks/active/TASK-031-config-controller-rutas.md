# TASK-031: ConfiguracionController + rutas (index / update / reset)

**Feature**: FEAT-004 — Panel de Configuración del Sistema (base)
**Spec**: `sdd/specs/panel-configuracion.spec.md`
**Status**: pending
**Priority**: high
**Esfuerzo estimado**: M (2-4h)
**Depends-on**: TASK-030
**Assigned-to**: emmanuel

---

## Contexto

Implementa el **Módulo 2** del spec: el backend HTTP del panel. Expone el registry
agrupado por módulo a la vista, y persiste/restablece overrides con validación
dinámica tomada del propio registry.

---

## Scope

- Crear `app/Http/Controllers/ConfiguracionController.php` con:
  - `index()` — registry de `config('parametros')` agrupado por `modulo`, con valor efectivo superpuesto (override BD o default) y flag `es_default` por parámetro → vista `admin.configuracion.index`
  - `update(Request $request, string $modulo)` — valida SOLO las claves que pertenecen a ese módulo, con las `reglas` del registry (construidas dinámicamente, NO duplicadas); upsert en `configuracion` con `updated_by_id = Auth::id()`; `Cache::forget('parametros')`; respuesta JSON
  - `reset(string $modulo, string $clave)` — verifica que la clave existe en el registry y pertenece al módulo; DELETE de la fila; flush caché; respuesta JSON con el valor default resultante
- Registrar 3 rutas dentro del grupo `role:Administrador` existente:
  `GET /configuracion` · `PUT /configuracion/{modulo}` · `DELETE /configuracion/{modulo}/{clave}` (nombres: `configuracion.index|update|reset`)
- `{modulo}`/`{clave}` inexistentes en el registry → 404.

**NO está en alcance**:
- La vista Blade (TASK-032) — `index()` puede retornar la vista aunque aún no exista al desarrollar; coordinar con TASK-032
- Ítem del dropdown del header (TASK-033)
- Form Request dedicado — la validación se construye desde el registry en el controller (decisión del spec §7)

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `app/Http/Controllers/ConfiguracionController.php` | CREATE | index / update / reset |
| `routes/web.php` | MODIFY | 3 rutas en el grupo `role:Administrador` (línea ~62) |

---

## Codebase Contract (Anti-Alucinación)

### Imports verificados
```php
use App\Http\Controllers\Controller;     // clase base real del proyecto
use App\Models\Configuracion;            // creado por TASK-030
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
```

### Firmas y datos existentes
```php
// routes/web.php:47 — grupo autenticado global (envuelve todo)
Route::middleware(['auth', 'throttle:60,1', 'active.user', 'recovery.questions.required'])->group(...)

// routes/web.php:62 — grupo SOLO ADMINISTRADOR: AQUÍ van las 3 rutas nuevas
Route::middleware('role:Administrador')->group(function () { ... });

// Los controllers del proyecto viven en app/Http/Controllers/ (raíz)
// — NO en app/Http/Controllers/Admin/ (el template de task usa Admin/ como EJEMPLO genérico;
//   este proyecto no tiene esa subcarpeta: ver CompraController.php, UserController.php en la raíz)
```

### Convenciones a respetar
- Respuestas AJAX JSON con la forma usada por los módulos existentes (ver `CompraController::procesar` como referencia de éxito/error)
- Mensajes de error en español neutro (tuteo)

### NO existe — no referenciar
- ~~`App\Http\Requests\ConfiguracionRequest`~~ — NO se crea; validación dinámica desde el registry
- ~~`App\Services\ConfiguracionService`~~ — innecesario; la lógica cabe en el controller (3 métodos cortos)
- ~~middleware `permiso`~~ — es de FEAT-005, aquí se usa el grupo `role:Administrador` existente
- ~~vista `admin/configuracion/index.blade.php`~~ — la crea TASK-032

---

## Notas de implementación

### Restricciones clave
- `update` debe rechazar claves enviadas que NO estén en el registry o NO pertenezcan al `{modulo}` (422) — nunca persistir claves arbitrarias.
- El valor se guarda como string (`valor` es varchar); el casteo es responsabilidad del helper al leer.
- Upsert: `Configuracion::updateOrCreate(['clave' => $clave], ['valor' => $valor, 'updated_by_id' => Auth::id()])`.
- Si el valor enviado es idéntico al default, igual se persiste (decisión simple; "restablecer" es la vía explícita para volver al default).
- Ojo al ORDEN de rutas en web.php: `GET /configuracion` no colisiona con nada, pero verificar con `php artisan route:list | grep configuracion`.

---

## Criterios de aceptación

- [ ] `php artisan route:list | grep configuracion` muestra las 3 rutas dentro del middleware correcto
- [ ] `PUT /configuracion/impuestos` con `{"impuestos.iva": 8}` → 200, fila creada, caché flusheada
- [ ] `PUT` con valor inválido (`-1`, `150`, `"abc"`) → 422 con mensaje de las reglas del registry
- [ ] `PUT /configuracion/noexiste` → 404; clave ajena al módulo → 422
- [ ] `DELETE /configuracion/impuestos/impuestos.iva` → fila eliminada, respuesta incluye el default
- [ ] Como Supervisor: las 3 rutas → 403

---

## QA manual

1. Login como admin; probar las 3 rutas (con la vista de TASK-032, o vía curl/fetch con CSRF si se prueba antes).
2. Verificar en BD: `updated_by_id` = id del admin logueado.
3. `php artisan tinker`: `parametro('impuestos.iva')` refleja el cambio inmediatamente (caché flusheada).
4. Login como Supervisor → 403 en `GET /configuracion`.

---

## Instrucciones para el ejecutor

1. **Lee el spec** completo y **verifica que TASK-030 está en `tasks/completed/`**.
2. **Verifica el Codebase Contract** (grep/read cada referencia).
3. **Actualiza el header**: `Status: in-progress`.
4. **Implementa** dentro del scope.
5. **Verifica** criterios y QA; **mueve a `tasks/completed/`** con Nota de Completitud.

---

## Nota de Completitud

*(Llenar al terminar)*

**Completado por**:
**Fecha**:
**Commits**:
**Notas**:

**Desviaciones del spec**:
