# TASK-030: Infraestructura de configuración — migración, modelo, registry y helper

**Feature**: FEAT-004 — Panel de Configuración del Sistema (base)
**Spec**: `sdd/specs/panel-configuracion.spec.md`
**Status**: pending
**Priority**: high
**Esfuerzo estimado**: M (2-4h)
**Depends-on**: none
**Assigned-to**: emmanuel

---

## Contexto

Implementa el **Módulo 1** del spec: la base sobre la que se monta todo el panel.
Separa definición (registry en código) de valor (tabla de overrides) y expone el
helper `parametro()` que el resto del sistema consumirá. Sin esta task no hay
nada que mostrar ni guardar.

---

## Scope

- Crear migración `create_configuracion_table` (ver esquema en spec §2).
- Crear modelo `app/Models/Configuracion.php` (`protected $table = 'configuracion'`, fillable `clave`, `valor`, `updated_by_id`; SIN SoftDeletes).
- Crear registry `config/parametros.php` con la única entrada de fase 1: `impuestos.iva` (forma exacta en spec §2 "Modelos de datos").
- Crear `app/Support/helpers.php` con el helper global `parametro(string $clave)`:
  - fallback: valor en BD → `config($entrada['config_key'])` → `default` del registry
  - casteo según `tipo` del registry
  - caché única `Cache::rememberForever('parametros', ...)` con el mapa clave→valor
  - guard si la tabla no existe aún (deploy a medias): devolver el default sin tocar BD
- Registrar el helper en `composer.json` → bloque `autoload.files` (NO existe hoy, crearlo) + `composer dump-autoload`.
- Actualizar dump `database/sistema_atlantico.sql` con la tabla nueva (workflow en memoria del equipo / `docs/conventions/`).

**NO está en alcance**:
- Controller, rutas y validación (TASK-031)
- Vista del panel (TASK-032)
- Migrar los consumidores de IVA (TASK-034)

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `database/migrations/2026_06_12_000001_create_configuracion_table.php` | CREATE | Tabla de overrides |
| `app/Models/Configuracion.php` | CREATE | Modelo Eloquent simple |
| `config/parametros.php` | CREATE | Registry con `impuestos.iva` |
| `app/Support/helpers.php` | CREATE | Helper `parametro()` |
| `composer.json` | MODIFY | Bloque `autoload.files` nuevo |
| `database/sistema_atlantico.sql` | MODIFY | Dump con la tabla nueva |

---

## Codebase Contract (Anti-Alucinación)

### Imports verificados
```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;    // patrón en app/Providers/AppServiceProvider.php:55
use Illuminate\Database\Eloquent\Model;
```

### Firmas y datos existentes
```php
// config/impuestos.php — fuente del default; NO se elimina
'iva' => env('IVA_TASA', 16),

// composer.json:27-33 — autoload actual: SOLO psr-4, no hay key "files"
"autoload": { "psr-4": { "App\\": "app/", ... } }

// FK a usuarios: tabla `user` (singular) — precedente real:
// migración 2026_06_03_000001_add_auditoria_anulacion_to_compra_table → constrained('user')
```

### Convenciones a respetar
- Tablas en **singular** → `configuracion`
- `docs/conventions/column-naming.md` — `clave`, `valor` (sin prefijos redundantes)
- Dump MySQL 8 nativo, collation `utf8mb4_unicode_ci` — ver workflow de dumps del equipo (memoria `reference-db-dump-workflow` / CLAUDE.md § Dump SQL)

### NO existe — no referenciar
- ~~`app/Support/` ni `app/Helpers/`~~ — el directorio se crea aquí
- ~~`App\Models\Configuracion`~~ — se crea aquí
- ~~tabla `settings` / `parametros`~~ — no hay tabla de configuración previa
- ~~helper PHP `bsEquivalente()`~~ — no hay helpers globales registrados; no hay patrón previo que copiar
- ~~seeder de valores~~ — la tabla nace VACÍA por diseño (defaults viven en el registry)

---

## Notas de implementación

### Restricciones clave
- El helper lee defaults vía `config()` (nunca `env()` directo) — compatible con `config:cache`.
- Una sola key de caché para todo el mapa (`parametros`), NO una por clave.
- `Schema::hasTable('configuracion')` como guard del helper debe a su vez tolerar BD caída (try/catch) — el helper jamás puede tirar 500 por sí solo.
- La migración debe ser idempotente-amigable (precedente: `6492a8a fix(migrations): hacer idempotentes las migraciones de compra`).

---

## Criterios de aceptación

- [ ] `php artisan migrate` limpio sobre BD existente y fresca
- [ ] `parametro('impuestos.iva')` devuelve `16.0` (float) con tabla vacía
- [ ] Insertar fila `impuestos.iva = 8` por tinker → devuelve `8.0`; borrarla → vuelve `16.0` (tras `Cache::forget('parametros')`)
- [ ] `composer dump-autoload` sin errores; helper disponible en tinker y en Blade
- [ ] Dump actualizado e importable en BD temporal
- [ ] Clave inexistente → excepción clara o `null` documentado (decidir y documentar en el PHPDoc del helper)

---

## QA manual

1. `php artisan migrate` → verificar tabla `configuracion` con UNIQUE en `clave` y FK a `user`.
2. `php artisan tinker`: `parametro('impuestos.iva')` → `16.0`.
3. `Configuracion::create(['clave'=>'impuestos.iva','valor'=>'8'])` + `Cache::forget('parametros')` → helper devuelve `8.0`.
4. Borrar la fila + flush → `16.0` de nuevo.
5. Con `IVA_TASA=12` en `.env` (y sin fila en BD) → `12.0`. Restaurar `.env`.

---

## Instrucciones para el ejecutor

1. **Lee el spec** completo (`sdd/specs/panel-configuracion.spec.md`).
2. **Verifica el Codebase Contract** antes de codificar (grep/read cada referencia).
3. **Actualiza el header**: `Status: in-progress`.
4. **Implementa** dentro del scope. Trabajo extra → task nueva.
5. **Verifica** criterios de aceptación y QA manual.
6. **Mueve este archivo** a `sdd/tasks/completed/` y rellena la Nota de Completitud.

---

## Nota de Completitud

*(Llenar al terminar)*

**Completado por**:
**Fecha**:
**Commits**:
**Notas**:

**Desviaciones del spec**:
