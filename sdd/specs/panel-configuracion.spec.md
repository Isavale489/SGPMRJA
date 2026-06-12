---
type: feature        # feature | fix
base_branch: enmanuel
---

# Feature Specification: Panel de Configuración del Sistema (base)

**Feature ID**: FEAT-004
**Fecha**: 2026-06-12
**Autor**: Emmanuel
**Status**: approved
**Versión objetivo**: Sprint Final

---

## 1. Motivación y requisitos de negocio

> ¿Por qué existe esta feature? ¿Qué problema resuelve?

### Planteamiento del problema

Los parámetros de negocio del sistema están dispersos y cambiarlos requiere tocar
código o el `.env` y redesplegar:

| Parámetro | Dónde vive hoy |
|---|---|
| Tasa de IVA general (16%) | `config/impuestos.php` + env `IVA_TASA` |
| Abono mínimo de pedido (50%) | `config/pedidos.php` + env `PEDIDO_ABONO_MINIMO_PORCENTAJE` |
| Vigencia de cotización (15 días) | constante `Cotizacion::DIAS_VIGENCIA` (hardcodeada) |
| Días hábiles de entrega (30) | hardcodeado en la lógica de formalización |

El equipo quiere un **panel de configuración** donde el administrador ajuste estos
parámetros sin intervención técnica. Esta feature construye **la base** del panel
(infraestructura + UI) y migra **un solo grupo de punta a punta: Impuestos (IVA)**,
que sirve de caso de prueba porque ya tiene patrón de snapshot
(`compra.iva_porcentaje`) y pocos consumidores.

### Objetivos

- Infraestructura reutilizable: registry de parámetros en código + valores override en BD + helper `parametro()` con caché.
- Página `/configuracion` dirigida por el registry: agregar un parámetro nuevo = una entrada en `config/parametros.php`, sin tocar vista ni controller.
- Acceso desde el **dropdown "Configuración" del header** (que ya agrupa "Configuración de perfil" y "Configuración de usuarios"): ítem nuevo **"Configuración del sistema"**, visible solo para Administrador.
- Grupo **Impuestos** (tasa de IVA) configurable desde el panel, con los 3 consumidores actuales migrados al helper.
- Garantizar que cambiar un parámetro **no reescribe historia**: las transacciones conservan sus snapshots (patrón ya existente).

### Fuera de alcance (No-Goals)

- Migrar el resto de parámetros (abono mínimo, vigencia de cotización, días hábiles, textos T&C/FAQ) — fase 2, sobre esta misma base.
- Tema del sistema (dark/claro) configurable — futuro; además es **preferencia por usuario**, no parámetro global, así que será otro sabor de configuración.
- Historial completo de cambios (`configuracion_historial`) — la auditoría de fase 1 es `updated_by_id` + `updated_at`.
- La tasa BCV — es dato operativo diario con su propia tabla `tasa_cambio`, no configuración.

---

## 2. Diseño arquitectónico

### Resumen

Separar **definición** de **valor**:

1. **Registry en PHP** (`config/parametros.php`): catálogo de parámetros — clave,
   etiqueta, descripción, módulo, tipo de dato, reglas de validación y default.
   La metadata vive en código (versionada, validación en un solo lugar).
2. **Tabla `configuracion`**: solo guarda los valores que el admin cambió
   (`clave` UNIQUE → `valor` como texto, casteado según el tipo del registry).
3. **Helper global `parametro(string $clave)`** con cadena de fallback:
   **valor en BD → default del registry** (que puede leer env). Caché con
   `Cache::rememberForever('parametros')` + flush al guardar. Mientras un
   parámetro no esté en BD, el sistema se comporta exactamente igual que hoy
   → rollout incremental sin riesgo.
4. **Panel UI** `/configuracion`: página (no modal) con nav-pills vertical por
   módulo + un `<form>` por módulo. El Blade itera el registry; un partial
   renderiza cada campo según su tipo.

### Diagrama de componentes

```
config/parametros.php (registry: metadata + defaults)
        │
        ├──→ helper parametro(clave) ──→ Cache ──→ tabla configuracion (overrides)
        │         ▲                                        ▲
        │         │ lee                                    │ escribe + flush caché
        │   CompraService / vistas                 ConfiguracionController@update
        │
        └──→ ConfiguracionController@index ──→ index.blade.php (itera registry)
                                                    └── partials/campo.blade.php (@switch tipo)
```

### Puntos de integración

| Componente existente | Tipo de integración | Notas |
|---|---|---|
| `routes/web.php` grupo `role:Administrador` (línea ~62) | añade | rutas `configuracion.*` dentro del grupo existente |
| `resources/views/admin/layouts/header.blade.php` (dropdown perfil, líneas 306-343) | modifica | ítem "Configuración del sistema" dentro del `@if (Auth::user()->isAdmin())` existente, junto a "Configuración de usuarios" |
| `app/Services/CompraService.php` → `tasaIva()` (línea 262) | modifica | `config('impuestos.iva', 16)` → `parametro('impuestos.iva')` |
| `resources/views/admin/compras/index.blade.php:199` | modifica | `window.IVA_TASA = @json(...)` → helper |
| `resources/views/admin/compras/modals/create.blade.php:393` | modifica | texto informativo del IVA → helper |
| `config/impuestos.php` | conserva | queda como fuente del **default** del registry (no se borra) |
| `composer.json` | modifica | registrar `app/Support/helpers.php` en `autoload.files` + `composer dump-autoload` |

### Modelos de datos

```php
// database/migrations/2026_06_12_000001_create_configuracion_table.php
Schema::create('configuracion', function (Blueprint $table) {
    $table->id();
    $table->string('clave')->unique();          // ej: 'impuestos.iva'
    $table->string('valor');                    // texto; se castea según tipo del registry
    $table->foreignId('updated_by_id')->nullable()->constrained('user');
    $table->timestamps();
});
// Sin softDeletes: "restablecer al default" = DELETE de la fila (vuelve al fallback).
// Sin seeder de valores: la tabla nace vacía; los defaults viven en el registry.
```

```php
// config/parametros.php — forma de cada entrada del registry
'impuestos.iva' => [
    'modulo'      => 'Impuestos',
    'nombre'      => 'Tasa de IVA general (%)',
    'descripcion' => 'Aplicada a las líneas gravables de compras. Las compras ya registradas conservan su snapshot (compra.iva_porcentaje).',
    'tipo'        => 'decimal',                 // decimal | entero | booleano | texto
    'reglas'      => 'required|numeric|min:0|max:100',
    'default'     => null,                      // null ⇒ fallback a config('impuestos.iva')
    'config_key'  => 'impuestos.iva',           // puente al config file legacy
],
```

### Rutas nuevas

| Verbo | URI | Acción | Nombre |
|---|---|---|---|
| GET | /configuracion | index | configuracion.index |
| PUT | /configuracion/{modulo} | update | configuracion.update |
| DELETE | /configuracion/{modulo}/{clave} | restablecer | configuracion.reset |

Las tres dentro del grupo `Route::middleware('role:Administrador')` existente.
`{modulo}` y `{clave}` se validan contra el registry (404 si no existen).

### UI / Vistas

- **Página completa**, no modal — la configuración no es CRUD de filas. Header estándar navy.
- Layout: **nav-pills vertical a la izquierda** (un pill por módulo del registry) + card de contenido a la derecha con el form del módulo activo.
- Un `<form>` por módulo con su botón Guardar → `PUT` vía AJAX + SweetAlert de éxito (ya global en el layout).
- `partials/campo.blade.php`: `@switch($parametro['tipo'])` → input numérico con min/max, switch Bootstrap, input/textarea. Debajo, la `descripcion` como help-text y badge **"Por defecto: X"** cuando el valor viene del fallback (sin fila en BD).
- Botón **"Restablecer"** por parámetro → `DELETE` de la fila → vuelve al default.
- Validación JS estándar (blur + submit) según `docs/conventions/js-validations.md`.
- Acceso: ítem "Configuración del sistema" (icono `mdi mdi-cog-outline`, mismo estilo de los ítems existentes) en el dropdown del header, dentro del bloque `isAdmin()`. NO se toca el sidebar.
- **El panel es el hub de configuración** (decisión 2026-06-12, rev 1.1): bajo los pills de parámetros va la sección "Otras configuraciones" con enlaces a **Configuración de usuarios** (`/users`) y **Configuración de perfil** (`/profile/edit`). En consecuencia, el ítem "Configuración de usuarios" SALE del dropdown del header (queda: Perfil — visible para todos porque el Supervisor no entra al panel — y Sistema, solo admin).

---

## 3. Desglose por módulos

> Cada módulo se convertirá en al menos una TASK en Fase 2.

### Módulo 1: Infraestructura (migración + modelo + registry + helper)
- **Path**: `database/migrations/2026_06_12_000001_create_configuracion_table.php`, `app/Models/Configuracion.php`, `config/parametros.php`, `app/Support/helpers.php`, `composer.json`
- **Responsabilidad**: tabla de overrides, modelo Eloquent (`protected $table = 'configuracion'`), registry con la entrada `impuestos.iva`, helper `parametro()` con caché y fallback, registro del helper en composer autoload
- **Depende de**: nada

### Módulo 2: Controller + rutas + Form Request
- **Path**: `app/Http/Controllers/ConfiguracionController.php`, `routes/web.php`
- **Responsabilidad**: `index` (registry agrupado por módulo + valores BD superpuestos), `update` (valida con las `reglas` del registry, upsert + `updated_by_id`, flush caché), `reset` (delete fila + flush caché)
- **Depende de**: Módulo 1

### Módulo 3: Vista del panel
- **Path**: `resources/views/admin/configuracion/index.blade.php`, `partials/campo.blade.php`, `scripts/main.blade.php`
- **Responsabilidad**: página con nav-pills por módulo, render dirigido por registry, submit AJAX con validación blur, badge de default, botón restablecer
- **Depende de**: Módulo 2 (rutas registradas)

### Módulo 4: Header — ítem en el dropdown Configuración
- **Path**: `resources/views/admin/layouts/header.blade.php` (líneas 337-342)
- **Responsabilidad**: agregar `dropdown-item` "Configuración del sistema" → `route('configuracion.index')` dentro del `@if (Auth::user()->isAdmin())` que ya contiene "Configuración de usuarios"; mismo markup (`d-flex align-items-center`, icono `text-primary`)
- **Depende de**: Módulo 2 (ruta nombrada)

### Módulo 5: Migrar consumidores de IVA al helper
- **Path**: `app/Services/CompraService.php:262`, `resources/views/admin/compras/index.blade.php:199`, `resources/views/admin/compras/modals/create.blade.php:393`
- **Responsabilidad**: reemplazar `config('impuestos.iva', 16)` por `parametro('impuestos.iva')` en los 3 puntos; verificar que el snapshot `compra.iva_porcentaje` sigue intacto
- **Depende de**: Módulo 1

---

## 4. Test / QA Specification

### QA manual (golden path)
1. Login como **Administrador** → dropdown "Configuración" del header muestra: Configuración de perfil · Configuración de usuarios · **Configuración del sistema**.
2. `/configuracion` abre con el pill "Impuestos"; el campo IVA muestra `16` con badge "Por defecto".
3. Cambiar IVA a `8` → Guardar → SweetAlert de éxito; recargar → muestra `8` sin badge de default.
4. Crear una compra borrador con línea gravable → procesar → el IVA aplicado es 8% y `compra.iva_porcentaje = 8.00`.
5. Volver IVA a `16` desde el panel → la compra del paso 4 **sigue mostrando 8%** (snapshot intacto) en detalle, comprobante y PDF.
6. Botón "Restablecer" → el campo vuelve a `16` con badge "Por defecto" (fila eliminada de `configuracion`).
7. Login como **Supervisor** → el ítem "Configuración del sistema" NO aparece en el dropdown (igual que hoy "Configuración de usuarios"); `GET /configuracion` directo → 403.

### Edge cases a verificar
- Valor fuera de rango (`-1`, `150`, texto) → rechazado por validación backend (reglas del registry) y por la validación JS en blur.
- `PUT /configuracion/{modulo}` con una clave que no pertenece a ese módulo → 422/404.
- Caché: tras guardar, el siguiente request lee el valor nuevo (flush correcto); con dos workers/procesos no quedan valores viejos servidos.
- Tabla `configuracion` vacía (instalación fresca) → el panel y el helper funcionan solo con defaults.
- `window.IVA_TASA` en compras refleja el valor del panel al abrir el wizard.

### Dark mode
- Nav-pills, cards, badges de default y help-text con contraste correcto.
- El ítem nuevo del dropdown hereda los estilos existentes del `profile-dropdown-menu` (verificar en dark).

---

## 5. Criterios de aceptación

> Esta feature está completa cuando TODO lo siguiente es verdadero:

- [ ] Migración corre limpia en BD fresca y sobre BD existente (`php artisan migrate`)
- [ ] Helper `parametro()` resuelve BD → default del registry, con caché y flush al guardar
- [ ] Panel `/configuracion` pasa el QA manual (sección 4), incluido el 403 a no-administradores
- [ ] Los 3 consumidores de IVA leen del helper; el snapshot `compra.iva_porcentaje` se preserva (paso 5 del QA)
- [ ] Agregar un parámetro de prueba al registry lo hace aparecer en el panel **sin tocar vista ni controller** (verificación del patrón)
- [ ] Ítem "Configuración del sistema" en el dropdown del header, visible solo para Administrador
- [ ] Vista respeta estándares: header navy, validaciones JS, español neutro (tuteo), sin estilos inline
- [ ] Dump `database/sistema_atlantico.sql` actualizado con la tabla nueva
- [ ] PR mergeada a `enmanuel`
- [ ] Doc nuevo `docs/conventions/system-config.md` (el patrón registry+override es reusable)

---

## 6. Codebase Contract

> **CRÍTICO — anclaje anti-alucinación.**
> Esta sección es la única fuente de verdad sobre qué existe en el código.
> Los implementadores (humanos o Claude Code) NO deben referenciar imports,
> rutas, métodos o tablas que no estén listados aquí sin verificarlos primero
> con `grep` o `read`.

### Imports verificados
```php
use App\Http\Controllers\Controller;        // app/Http/Controllers/Controller.php
use App\Models\User;                        // app/Models/User.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;       // ya usado en app/Providers/AppServiceProvider.php:55
use Illuminate\Support\Facades\Auth;
```

### Firmas existentes a usar
```php
// app/Models/User.php:101
public function isAdmin()                   // return $this->role === 'Administrador';

// app/Models/User.php:113
public function hasRole($roles)             // acepta string o array de nombres de rol

// routes/web.php:47 — grupo autenticado global
Route::middleware(['auth', 'throttle:60,1', 'active.user', 'recovery.questions.required'])->group(...)

// routes/web.php:62 — grupo SOLO ADMINISTRADOR (aquí van las rutas nuevas)
Route::middleware('role:Administrador')->group(...)

// app/Services/CompraService.php:258-264 — consumidor a migrar
/** Tasa de IVA general vigente (%). Centralizada en config/impuestos.php. */
private function tasaIva(): float
{
    return (float) config('impuestos.iva', 16);
}

// config/impuestos.php — default actual
'iva' => env('IVA_TASA', 16),

// resources/views/admin/compras/index.blade.php:199
window.IVA_TASA = @json((float) config('impuestos.iva', 16));

// resources/views/admin/compras/modals/create.blade.php:393 — texto con la tasa interpolada
```

### Datos verificados del entorno
- Tablas en **singular** (`compra`, `cotizacion`, `user`, `tasa_cambio`) → la nueva se llama `configuracion`.
- La FK a usuarios apunta a la tabla **`user`** (singular) — ver migración `2026_06_03_000001_add_auditoria_anulacion_to_compra_table` (`anulado_por_id`).
- Dropdown del header (`header.blade.php:306-352`, `#profile-dropdown-menu`): el botón ya se titula "Configuración" y contiene "Configuración de perfil" (`route('profile.edit')`, línea 313) y "Configuración de usuarios" (`url('users')`, líneas 337-342, dentro de `@if (Auth::user()->isAdmin())`). El ítem nuevo va en ese mismo bloque `@if`.
- El sidebar NO se toca en esta feature (decisión 2026-06-12: el acceso vive en el dropdown del header).
- `composer.json` **no tiene** bloque `autoload.files` — hay que crearlo para registrar el helper, y correr `composer dump-autoload`.
- Snapshot existente: `compra.iva_porcentaje` (migración `2026_06_10_000003`) se fija al **procesar** la compra — esta feature no lo toca.
- Nomenclatura de columnas sin redundancia (`clave`, `valor` — no `config_clave`): regla del equipo.

### Convenciones a respetar (ver `docs/conventions/`)
- `docs/conventions/js-validations.md` — validación blur + submit en el form del panel
- `docs/conventions/column-naming.md` — nombres de columna sin prefijos redundantes
- `AGENTS.md` § Estándares visuales — header navy, sin estilos inline, dark mode
- Español neutro (tuteo) en todos los textos de UI — pedido explícito del equipo

### NO existe — no referenciar
- ~~`app/Helpers/` ni `app/Support/`~~ — el directorio `app/Support/` se crea en esta feature (Módulo 1)
- ~~helper global `bsEquivalente()`~~ — no hay helpers PHP globales registrados; no copiar ese patrón porque no existe como función PHP
- ~~`App\Models\Configuracion`~~ — se crea en Módulo 1
- ~~ruta `configuracion.*`~~ — se registra en Módulo 2
- ~~tabla `settings` / `parametros`~~ — no existe ninguna tabla de configuración previa
- ~~paquete spatie/laravel-settings~~ — descartado; el registry propio es suficiente y se integra mejor con las convenciones del equipo

---

## 7. Notas de implementación y restricciones

### Patrones a seguir
- Helper con caché: `Cache::rememberForever('parametros', fn() => Configuracion::pluck('valor','clave'))` + `Cache::forget('parametros')` en `update`/`reset`. Una sola entrada de caché para todo el mapa (no una por clave).
- Casteo según `tipo` del registry al leer (`decimal` → float, `entero` → int, `booleano` → bool, `texto` → string).
- Validación del `update` construida dinámicamente desde las `reglas` del registry — NO duplicar reglas en el Form Request.
- El panel muestra junto al campo IVA la advertencia de que las compras previas conservan su snapshot (texto ya redactado en la `descripcion` del registry).
- Guardar el grupo Impuestos pasa por un **SweetAlert de confirmación** (decisión 2026-06-12) antes del `PUT`; "Cancelar" no envía nada.
- `config/impuestos.php` y `config/pedidos.php` NO se eliminan: son la fuente de los defaults (`config_key` en el registry) y el fallback si la tabla está vacía.

### Riesgos conocidos
| Riesgo | Mitigación |
|---|---|
| Helper llamado antes de correr la migración (deploy a medias) | `try/catch` o `Schema::hasTable` cacheado en el helper: si la tabla no existe, devolver el default del registry |
| Caché stale entre procesos (artisan serve + scheduler) | una sola key de caché + `Cache::forget` en cada escritura; QA explícito (edge case) |
| AtlanticoGuard no cubre esta página (vigila modales con `#id-field`) | aceptado en fase 1; dirty-check propio si el equipo lo pide después |
| `config:cache` en producción y env-defaults | los defaults se leen vía `config()` (no `env()` directo) — compatible |

### Dependencias externas
| Paquete | Versión | Razón |
|---|---|---|
| — | — | sin dependencias nuevas |

---

## 8. Preguntas abiertas

> Resolver antes de mergear. Marcar con [x] al cerrar y dejar la respuesta.

- [x] ¿Dónde vive el acceso al panel? — **Resuelto 2026-06-12 (Emmanuel): en el dropdown "Configuración" del header, junto a Perfil y Usuarios. El sidebar no se toca.**
- [x] ¿Qué pasa con el ítem "Usuarios"? — **Resuelto: ya vive en el dropdown del header (`header.blade.php:337-342`); no se mueve nada.**
- [x] ¿El panel es solo-Administrador, o el Supervisor lo ve en solo lectura? — **Resuelto 2026-06-12 (Emmanuel): solo Administrador en fase 1 (consistente con "Configuración de usuarios").**
- [x] ¿El cambio de IVA requiere confirmación extra por su impacto en compras nuevas? — **Resuelto 2026-06-12 (Emmanuel): sí — al guardar el grupo Impuestos, SweetAlert de confirmación recordando que las compras previas conservan su snapshot.**

---

## Historial de revisiones

| Versión | Fecha | Autor | Cambio |
|---|---|---|---|
| 0.1 | 2026-06-12 | Emmanuel | Borrador inicial — base del panel + grupo Impuestos |
| 0.2 | 2026-06-12 | Emmanuel | Acceso movido del sidebar al dropdown "Configuración" del header (junto a Perfil y Usuarios) |
| 1.0 | 2026-06-12 | Emmanuel | Preguntas abiertas resueltas (solo-admin, confirm IVA) — status `approved` |
| 1.1 | 2026-06-12 | Emmanuel | El panel pasa a ser el hub: accesos a Usuarios y Perfil dentro de /configuracion; "Configuración de usuarios" sale del dropdown (Perfil se queda por el Supervisor) |
