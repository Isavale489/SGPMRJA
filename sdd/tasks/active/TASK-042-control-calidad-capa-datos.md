# TASK-042: Capa de datos — tabla y modelo `control_calidad`

**Feature**: FEAT-006 — control-calidad
**Spec**: `sdd/specs/control-calidad.spec.md`
**Status**: pending
**Priority**: high
**Esfuerzo estimado**: S
**Depends-on**: none
**Assigned-to**: unassigned

---

## Contexto
Base del módulo Control de Calidad (Módulo 1 del spec). Sin tabla/modelo no hay
nada que construir encima. Una inspección es 1:N respecto a `OrdenProduccion`.

## Scope
- Crear migración `control_calidad` (ver "Modelos de datos" del spec, sección 2).
- Crear modelo `App\Models\ControlCalidad` con `SoftDeletes`, fillable, casts y
  relaciones `ordenProduccion()` (belongsTo) e `inspector()` (belongsTo User).
- Añadir relación inversa `OrdenProduccion::controlesCalidad(): HasMany`.

**NO está en alcance**: lógica de inspección/reproceso (TASK-043), controller
(TASK-044), vista (TASK-045). NO agregar columnas ni estados a `orden_produccion`
(decisión #2: el estado de calidad se DERIVA de `control_calidad`).

## Archivos a crear / modificar
| Archivo | Acción | Descripción |
|---|---|---|
| `database/migrations/2026_06_25_000001_create_control_calidad_table.php` | CREATE | Tabla según spec |
| `app/Models/ControlCalidad.php` | CREATE | Modelo + SoftDeletes + relaciones |
| `app/Models/OrdenProduccion.php` | MODIFY | Añadir `controlesCalidad(): HasMany` |

## Codebase Contract (Anti-Alucinación)

### Imports verificados
```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\OrdenProduccion;   // app/Models/OrdenProduccion.php:9
use App\Models\User;              // app/Models/User.php
```

### Firmas / hechos verificados
```php
// Tabla destino de la FK: 'orden_produccion' (OrdenProduccion::$table = 'orden_produccion')
// Tabla usuarios: 'user' (singular) — confirmar con \Schema; User model existe.
// orden_produccion.estado es STRING: 'Pendiente'|'En Proceso'|'Finalizado'|'Cancelado'
```
Migración (forma exacta, ver spec §2):
```php
Schema::create('control_calidad', function (Blueprint $table) {
    $table->id();
    $table->foreignId('orden_produccion_id')->constrained('orden_produccion')->cascadeOnDelete();
    $table->foreignId('inspector_id')->constrained('user');
    $table->unsignedInteger('cantidad_inspeccionada');
    $table->unsignedInteger('cantidad_aprobada');
    $table->unsignedInteger('cantidad_rechazada');
    $table->enum('resultado', ['aprobado', 'rechazado', 'observado']);
    $table->text('observaciones')->nullable();
    $table->timestamp('fecha_inspeccion');
    $table->softDeletes();
    $table->timestamps();
});
```

### NO existe — no referenciar
- ~~`App\Models\ControlCalidad`~~ — se crea aquí.
- ~~tabla `produccion_diaria`~~ — ELIMINADA; no referenciar.
- ~~columna `orden_produccion.calidad_*`~~ — NO se agrega.

## Notas de implementación
- **OJO nombre de tabla `user`**: confirmar con `\Schema::hasTable('user')` antes
  de la FK `constrained('user')`. Si la convención del proyecto difiere, ajustar.
- Casts: cantidades → `integer`, `fecha_inspeccion` → `datetime`.

## Criterios de aceptación
- [ ] `php artisan migrate` corre limpio; `control_calidad` existe con sus FKs.
- [ ] `ControlCalidad` y `OrdenProduccion::controlesCalidad()` resuelven en tinker.
- [ ] Sin cambios en el enum/columnas de `orden_produccion` salvo la relación Eloquent.

## QA manual
1. `php artisan migrate`
2. tinker: `OrdenProduccion::first()->controlesCalidad()->count()` no lanza error.
3. tinker: crear un `ControlCalidad` de prueba (rollback) y leer `->ordenProduccion`, `->inspector`.
