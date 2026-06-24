# TASK-043: `ControlCalidadService` — inspección + reproceso

**Feature**: FEAT-006 — control-calidad
**Spec**: `sdd/specs/control-calidad.spec.md`
**Status**: pending
**Priority**: high
**Esfuerzo estimado**: M
**Depends-on**: TASK-042
**Assigned-to**: unassigned

---

## Contexto
Módulo 2 del spec: el corazón de la lógica. Registra la inspección y, si hay
defectuosas, dispara el **reproceso** reusando la máquina de estados existente
(`OrdenProduccion::recalcularEstadoDesdeSubordenes()`).

## Scope
- Crear `App\Services\ControlCalidadService::inspeccionar(OrdenProduccion $orden, array $data, int $inspectorId): ControlCalidad`.
- En transacción con `lockForUpdate()` sobre la orden:
  1. Re-chequear que la orden está en `Finalizado` (si no → ValidationException).
  2. Crear `ControlCalidad` (cantidades, resultado, observaciones, inspector, fecha=now).
  3. `orden.cantidad_defectuosa += cantidad_rechazada` (acumula histórico, decisión #3).
  4. Si `cantidad_rechazada > 0` → **reproceso**: `orden.cantidad_producida -= cantidad_rechazada`; guardar; llamar `recalcularEstadoDesdeSubordenes()` (bajará a `En Proceso`).
- Resolver el **riesgo de avance tras reproceso**: investigar `OrdenProduccionController::registrarAvance` y `SubOrdenProduccion`; si el avance se bloquea con subórdenes `Finalizado`, dejar la orden en estado que permita re-producir (documentar la mecánica elegida en el PR).

**NO está en alcance**: HTTP/validación de request (TASK-044), UI (TASK-045).
**NO** crear `MovimientoInsumo` ni tocar `Insumo.stock_actual` (invariante business-flows).

## Archivos a crear / modificar
| Archivo | Acción | Descripción |
|---|---|---|
| `app/Services/ControlCalidadService.php` | CREATE | `inspeccionar()` + helpers de reproceso |

## Codebase Contract (Anti-Alucinación)

### Imports verificados
```php
use App\Models\ControlCalidad;     // creado en TASK-042
use App\Models\OrdenProduccion;    // app/Models/OrdenProduccion.php:9
use Illuminate\Support\Facades\DB;
```

### Firmas existentes a usar
```php
// app/Models/OrdenProduccion.php
protected $table = 'orden_produccion';
// columnas: cantidad_solicitada, cantidad_producida, cantidad_defectuosa, estado (string)
public function recalcularEstadoDesdeSubordenes(): void;  // :151
//   'Finalizado' exige (subórdenes todas 'Finalizado') Y (cantidad_producida >= cantidad_solicitada).
//   Si cantidad_producida baja por debajo de solicitada → recalcula a 'En Proceso'.
public function subordenes(): HasMany;   // :141 (SubOrdenProduccion)
```

### Convenciones a respetar
- `docs/conventions/business-flows.md` — **stock baja SOLO en producción** (DetalleOrdenInsumo). Calidad NO toca stock.
- Patrón de transacción + `lockForUpdate()` como en `CompraService::procesar` / `OrdenProduccionController::registrarAvance` (verificar firma real antes de copiar).

### NO existe — no referenciar
- ~~estado `'En Calidad'`/`'Aprobado'`~~ — NO existe; no agregar al enum.
- ~~método de "marcar aprobada por calidad" en OrdenProduccion~~ — el estado de calidad se DERIVA de `control_calidad` (decisión #2), no se persiste en la orden.

## Notas de implementación
- Normalizar/validar coherencia mínima en el service como defensa (aunque el Request valide): `aprobada + rechazada == inspeccionada`, `inspeccionada <= cantidad_producida`.
- `resultado` lo decide el caller/Request (`aprobado`|`rechazado`|`observado`); el service confía pero verifica `rechazado ⇔ rechazada>0`.
- **Investigar primero** `registrarAvance` y `SubOrdenProduccion` para no romper el reproceso; si hace falta reabrir una sububorden, hacerlo aquí de forma explícita y documentarlo.

## Criterios de aceptación
- [ ] `inspeccionar()` crea el registro y actualiza `cantidad_defectuosa` (acumulado).
- [ ] Rechazo → orden vuelve a `En Proceso` y admite re-producción hasta completar.
- [ ] Aprobado/observado → orden permanece `Finalizado` (sin tocar stock).
- [ ] Inspección sobre orden no `Finalizado` → ValidationException.
- [ ] Cero `MovimientoInsumo` generados por la inspección.

## QA manual (tinker, con rollback)
1. Orden `Finalizado` → `inspeccionar` con rechazadas=0 → sigue `Finalizado`, defectuosa sin cambio.
2. Orden `Finalizado` (producida=solicitada) → `inspeccionar` rechazadas=N>0 → `cantidad_producida -= N`, `cantidad_defectuosa += N`, estado `En Proceso`.
3. Verificar `MovimientoInsumo::count()` no cambió.
