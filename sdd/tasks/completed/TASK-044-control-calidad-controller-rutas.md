# TASK-044: Controller + Request + rutas de Control de Calidad

**Feature**: FEAT-006 — control-calidad
**Spec**: `sdd/specs/control-calidad.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: M
**Depends-on**: TASK-042, TASK-043
**Assigned-to**: unassigned

---

## Contexto
Módulo 3 del spec: expone la lógica vía HTTP. Lista órdenes finalizadas
pendientes de calidad + historial, y registra inspecciones.

## Scope
- Crear `App\Http\Controllers\ControlCalidadController` con:
  - `index()` → vista `admin.calidad.index`.
  - `getOrdenesCalidad(Request)` → **DataTable server-side**: órdenes en `Finalizado` **sin** inspección aprobada/observada que las cubra (pendientes de calidad). Incluir columnas: nombre producto (`nombre_producto`), pedido, cantidad_producida, fecha_fin_real, acciones.
  - `detalle(OrdenProduccion $orden)` → JSON con datos de la orden + historial de inspecciones (`controlesCalidad`).
  - `inspeccionar(StoreControlCalidadRequest, OrdenProduccion $orden)` → delega a `ControlCalidadService::inspeccionar(..., auth()->id())`; responde JSON.
- Crear `App\Http\Requests\StoreControlCalidadRequest` (reglas + mensajes ES).
- Registrar rutas en `routes/web.php` (patrón "específicas antes del resource", como ordenes).

**NO está en alcance**: Blade/JS (TASK-045), permisos (TASK-046).

## Archivos a crear / modificar
| Archivo | Acción | Descripción |
|---|---|---|
| `app/Http/Controllers/ControlCalidadController.php` | CREATE | index/getOrdenesCalidad/detalle/inspeccionar |
| `app/Http/Requests/StoreControlCalidadRequest.php` | CREATE | Validación inspección |
| `routes/web.php` | MODIFY | Rutas `calidad.*` |

## Codebase Contract (Anti-Alucinación)

### Imports verificados
```php
use App\Http\Controllers\Controller;          // app/Http/Controllers/Controller.php
use App\Models\OrdenProduccion;               // app/Models/OrdenProduccion.php:9
use App\Services\ControlCalidadService;       // creado en TASK-043
use App\Http\Requests\StoreControlCalidadRequest; // creado en esta task
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;              // usado en OrdenProduccionController
```

### Firmas existentes a usar
```php
// app/Models/OrdenProduccion.php
public function getNombreProductoAttribute(): string;   // :70 (nombre legible)
public function pedido(): BelongsTo;                    // :123
public function controlesCalidad(): HasMany;            // añadido en TASK-042
// estado string: 'Finalizado' filtra pendientes de calidad.

// routes/web.php — patrón ordenes (rutas específicas ANTES del resource), :262-271:
Route::get('ordenes-data', [OrdenProduccionController::class, 'getOrdenes'])->name('ordenes.data');
Route::post('ordenes/{orden}/avance', [OrdenProduccionController::class, 'registrarAvance'])->name('ordenes.avance');
// Nombres SIN prefijo 'admin.' — usar 'calidad.index','calidad.data','calidad.detalle','calidad.inspeccionar'.
```
Rutas a registrar:
```php
Route::get('calidad', [ControlCalidadController::class, 'index'])->name('calidad.index');
Route::get('calidad-data', [ControlCalidadController::class, 'getOrdenesCalidad'])->name('calidad.data');
Route::get('calidad/{orden}/detalle', [ControlCalidadController::class, 'detalle'])->name('calidad.detalle');
Route::post('calidad/{orden}/inspeccionar', [ControlCalidadController::class, 'inspeccionar'])->name('calidad.inspeccionar');
```

### Reglas del Request
```php
'cantidad_inspeccionada' => 'required|integer|min:1',
'cantidad_aprobada'      => 'required|integer|min:0',
'cantidad_rechazada'     => 'required|integer|min:0',
'resultado'              => 'required|in:aprobado,rechazado,observado',
'observaciones'          => 'required_unless:resultado,aprobado|nullable|string|max:1000',
// + regla custom: aprobada+rechazada == inspeccionada; inspeccionada <= orden.cantidad_producida
```

### NO existe — no referenciar
- ~~`app/Http/Controllers/Admin/`~~ — el proyecto NO usa subnamespace `Admin`; va en `app/Http/Controllers/`.
- ~~prefijo de ruta `admin.`~~ — los nombres son `calidad.*` a secas.

## Convenciones a respetar
- DataTable **server-side** + método `getX()` (convención del proyecto).
- `docs/conventions/js-validations.md` para los mensajes/validación.

## Criterios de aceptación
- [ ] `php artisan route:list | grep calidad` lista las 4 rutas.
- [ ] `getOrdenesCalidad` devuelve solo órdenes `Finalizado` pendientes de calidad.
- [ ] `inspeccionar` valida coherencia y delega al service; responde JSON OK/422.
- [ ] Request rechaza cantidades incoherentes y motivo faltante en rechazo.

## QA manual
1. `route:list | grep calidad` → 4 rutas.
2. GET `/calidad-data` → JSON DataTable (órdenes finalizadas pendientes).
3. POST `/calidad/{orden}/inspeccionar` con payload válido → 200 + registro creado.
4. Payload incoherente (`aprobada+rechazada != inspeccionada`) → 422.
