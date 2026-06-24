---
type: feature
base_branch: enmanuel
---

# Feature Specification: Control de Calidad (Inspección de Órdenes de Producción)

**Feature ID**: FEAT-006
**Fecha**: 2026-06-25
**Autor**: Emmanuel Arroyo
**Status**: approved
**Versión objetivo**: cierre del ciclo de transacciones

---

## 1. Motivación y requisitos de negocio

> ¿Por qué existe esta feature? ¿Qué problema resuelve?

### Planteamiento del problema
El módulo **Control de Calidad** existe en el sidebar como placeholder muerto
(`href="#"` + `{{-- TODO: Crear ruta y controlador --}}`) y figura como
*"pendiente"* en `docs/conventions/business-flows.md`. Es el **eslabón faltante
del ciclo de transacciones**: hoy una Orden de Producción llega a `Finalizado`
y no hay paso formal que verifique que lo producido es conforme antes de
entregarlo al cliente.

El **Diagrama de Actividad del Software** (`docs/diagrama_actividad_software.html`)
ya lo modela explícitamente: en el carril **Supervisor** hay un nodo
*"Registrar control de calidad"* seguido de la decisión **"¿Producto conforme?"**:
- **SÍ** → (Admin) *"Registrar entrega y cobro de saldo"*.
- **NO** → **reproceso** → vuelve a *"Avanzar producción"*.

### Objetivos
- Permitir al **Supervisor** registrar una **inspección de calidad por Orden de
  Producción** una vez que la orden está en `Finalizado`.
- Capturar `cantidad_inspeccionada`, `cantidad_aprobada` (conformes),
  `cantidad_rechazada` (defectuosas) y motivo/observaciones; alimentar
  `orden_produccion.cantidad_defectuosa` (columna ya existente).
- Implementar la decisión **"¿Producto conforme?"**: si hay defectuosas →
  **reproceso** (la orden vuelve a producción para rehacer las N unidades);
  si todo conforme → la orden queda **aprobada por calidad**.
- Servir de **compuerta**: el pedido no se da por listo para entrega hasta que
  todas sus órdenes pasen calidad.
- Cablear el módulo `/calidad` real (ruta, controlador, vista, sidebar) e
  integrarlo con el sistema de permisos por rol (FEAT-005).

### Fuera de alcance (No-Goals)
- **Registrar entrega y cobro de saldo** (el paso Admin posterior del diagrama)
  — es un flujo aparte; aquí solo se deja la **compuerta** que lo habilita.
  Ver Pregunta abierta #1.
- **Garantías / reclamos post-venta** — placeholder separado, futuro FEAT.
- **Catálogo de tipos de defecto** — por ahora el motivo es texto libre
  (decisión del dueño del producto). Posible mejora futura.
- **No toca stock**: la inspección NO crea `MovimientoInsumo` ni decrementa
  `Insumo.stock_actual` (invariante de `business-flows.md`). El consumo extra
  por reproceso ocurre por la vía normal de producción (`DetalleOrdenInsumo`).

---

## 2. Diseño arquitectónico

### Resumen
Nueva entidad `ControlCalidad` (1:N respecto a `OrdenProduccion`): cada registro
es una inspección de una orden finalizada. El `ControlCalidadController` lista
las órdenes finalizadas **pendientes de inspección** + el historial de
inspecciones, y un modal registra el veredicto. Un `ControlCalidadService`
encapsula la transacción: crear la inspección, actualizar
`orden.cantidad_defectuosa`, y —si hay rechazadas— disparar el **reproceso**
(reducir `cantidad_producida`, que recalcula el estado de la orden a
`En Proceso`).

### Diagrama de componentes
```
Vista (admin/calidad/index) ──→ ControlCalidadController ──→ ControlCalidadService ──→ ControlCalidad (modelo)
        │ DataTable server-side            │                          │                       │
        │ modal de inspección              └──→ StoreControlCalidadRequest   └──→ OrdenProduccion (estado/cantidades)
        │                                                                          └──→ migración control_calidad
        └──→ sidebar (enlace real /calidad)
```

### Puntos de integración
| Componente existente | Tipo de integración | Notas |
|---|---|---|
| `App\Models\OrdenProduccion` | extiende | añade relación `controlesCalidad(): HasMany`; scopes `finalizadas`/`pendientesCalidad` |
| `OrdenProduccion::recalcularEstadoDesdeSubordenes()` | reusa | al bajar `cantidad_producida` por reproceso, recalcula a `En Proceso` (def. en `app/Models/OrdenProduccion.php:151`) |
| `orden_produccion.cantidad_defectuosa` | escribe | columna ya existente (no migración) |
| `App\Models\Pedido` | lee | compuerta: pedido "listo" solo si todas sus órdenes están aprobadas por calidad |
| `resources/views/admin/layouts/sidebar.blade.php:576` | modifica | reemplazar placeholder `href="#"` por ruta real `calidad.index` |
| `routes/web.php` (grupo admin) | añade | rutas `calidad.*` (patrón "específicas antes del resource", como ordenes) |
| `config/modulos.php` | añade | módulo `calidad` con acciones `ver`/`inspeccionar` y mapeo de rutas (rol Supervisor) |

### Modelos de datos
```php
// Nueva tabla / modelo
Schema::create('control_calidad', function (Blueprint $table) {
    $table->id();
    $table->foreignId('orden_produccion_id')->constrained('orden_produccion')->cascadeOnDelete();
    $table->foreignId('inspector_id')->constrained('user');           // Supervisor que inspecciona (User logueado)
    $table->unsignedInteger('cantidad_inspeccionada');
    $table->unsignedInteger('cantidad_aprobada');                     // conformes
    $table->unsignedInteger('cantidad_rechazada');                    // defectuosas
    $table->enum('resultado', ['aprobado', 'rechazado', 'observado']);// conforme / no conforme / conforme con notas
    $table->text('observaciones')->nullable();                        // motivo del defecto (oblig. si rechazado/observado)
    $table->timestamp('fecha_inspeccion');
    $table->softDeletes();
    $table->timestamps();
});
```
Invariantes de datos:
- `cantidad_aprobada + cantidad_rechazada == cantidad_inspeccionada`.
- `cantidad_inspeccionada <= orden.cantidad_producida`.
- `resultado = 'rechazado'` ⇔ `cantidad_rechazada > 0`.
- `observaciones` obligatorio si `resultado != 'aprobado'`.

### Rutas nuevas
| Verbo | URI | Acción | Nombre |
|---|---|---|---|
| GET | /calidad | index | calidad.index |
| GET | /calidad-data | getOrdenesCalidad (DataTable) | calidad.data |
| GET | /calidad/{orden}/detalle | detalle JSON (orden + historial) | calidad.detalle |
| POST | /calidad/{orden}/inspeccionar | store (registrar inspección) | calidad.inspeccionar |

### UI / Vistas
- Card: `card-transactional` (sección Transacciones) — ver `AGENTS.md` § Estándares visuales.
- Modal: `atlantico-modal atlantico-modal--op` (transaccional) — ver `docs/conventions/modal-system.md`.
- DataTable server-side `dt-transactional` (convención: server-side + método `getX()` en el controller).
- Sidebar: ítem ya existe (carril Operativa); solo enlazar a `calidad.index` y marcar activo con `request()->is('calidad*')`.

---

## 3. Desglose por módulos

> Cada módulo se convertirá en al menos una TASK en Fase 2.

### Módulo 1: Capa de datos
- **Path**: `database/migrations/xxxx_create_control_calidad_table.php` + `app/Models/ControlCalidad.php`
- **Responsabilidad**: tabla + modelo `ControlCalidad` (relaciones `ordenProduccion()`, `inspector()`); relación inversa `OrdenProduccion::controlesCalidad()`.
- **Depende de**: `orden_produccion` (existe), `user` (existe).

### Módulo 2: Service (lógica de inspección + reproceso)
- **Path**: `app/Services/ControlCalidadService.php`
- **Responsabilidad**: `inspeccionar(OrdenProduccion $orden, array $data)` en transacción: crea `ControlCalidad`; `orden.cantidad_defectuosa += rechazadas`; si `rechazadas > 0` → reproceso (`orden.cantidad_producida -= rechazadas` → `recalcularEstadoDesdeSubordenes()` lo lleva a `En Proceso`). NO toca stock.
- **Depende de**: Módulo 1, `OrdenProduccion`.

### Módulo 3: Controller + Request
- **Path**: `app/Http/Controllers/ControlCalidadController.php`, `app/Http/Requests/StoreControlCalidadRequest.php`
- **Responsabilidad**: `index` (vista), `getOrdenesCalidad` (DataTable de órdenes finalizadas pendientes + historial), `detalle` (JSON), `inspeccionar` (valida + delega al service). Inspector = `auth()->user()`.
- **Depende de**: Módulos 1-2.

### Módulo 4: Vista + sidebar
- **Path**: `resources/views/admin/calidad/index.blade.php` (+ modal), `sidebar.blade.php`
- **Responsabilidad**: listado DataTable + modal de inspección (cantidades + motivo, validación JS); enlazar sidebar.
- **Depende de**: Módulo 3 (rutas registradas).

### Módulo 5: Permisos
- **Path**: `config/modulos.php` (+ seed/asignación del rol Supervisor si aplica)
- **Responsabilidad**: registrar módulo `calidad` (acciones `ver`/`inspeccionar`), mapear rutas; el Supervisor obtiene `calidad.inspeccionar`.
- **Depende de**: FEAT-005 (registry de permisos por rol).

---

## 4. Test / QA Specification

### QA manual (golden path)
1. Login como **Supervisor** → navegar a `/calidad`.
2. El listado muestra **órdenes en `Finalizado` sin inspección aprobada** (pendientes de calidad).
3. Click "Inspeccionar" en una orden → modal con datos de la orden (producto, pedido, cantidad producida) prellenados.
4. Caso conforme: `inspeccionada = producida`, `aprobadas = inspeccionada`, `rechazadas = 0` → resultado `aprobado` → submit → la orden desaparece de "pendientes" y queda **aprobada por calidad**.
5. Caso rechazo: `rechazadas = N > 0` + motivo → resultado `rechazado` → submit → `cantidad_defectuosa += N`, `cantidad_producida -= N`, la orden vuelve a **`En Proceso`** (reproceso) y reaparece en producción.
6. Tras reproceso: producción registra avance hasta completar → orden re-`Finalizado` → reaparece en calidad → re-inspección.
7. Verificar que el **stock NO cambió** por la inspección (sin `MovimientoInsumo` nuevo).

### Edge cases a verificar
- `aprobadas + rechazadas != inspeccionada` → 422.
- `inspeccionada > cantidad_producida` → 422.
- `rechazadas > 0` sin observaciones → 422 (motivo obligatorio).
- Orden no `Finalizado` → no inspeccionable (no aparece / 422).
- Reproceso cuando las **subórdenes ya están `Finalizado`**: confirmar que la orden acepta nuevo avance tras volver a `En Proceso` (ver Riesgo).
- Doble inspección simultánea de la misma orden (lock / re-chequeo de estado).

### Dark mode
- Modal, DataTable y **badges de resultado** (aprobado/rechazado/observado) con contraste correcto; sin estilos inline.

---

## 5. Criterios de aceptación

> Esta feature está completa cuando TODO lo siguiente es verdadero:

- [ ] Migración corre limpia en BD fresca: `php artisan migrate:fresh --seed`
- [ ] Controller pasa QA manual (sección 4)
- [ ] Vista respeta estándares visuales: card transactional, modal `--op`, DataTable server-side, validaciones JS
- [ ] Sidebar enlazado a `calidad.index` (placeholder eliminado) — ver `docs/conventions/sidebar-colors.md`
- [ ] Reproceso revierte estado a `En Proceso` y permite re-producir + re-inspeccionar
- [ ] La inspección NO genera `MovimientoInsumo` ni toca `Insumo.stock_actual`
- [ ] Permiso `calidad.*` integrado y asignado al rol Supervisor (FEAT-005)
- [ ] Dark mode funcional sin estilos inline
- [ ] `business-flows.md` actualizado: Control de Calidad deja de ser "pendiente"
- [ ] PR mergeada a `enmanuel`

---

## 6. Codebase Contract

> **CRÍTICO — anclaje anti-alucinación.** Solo referenciar lo verificado aquí.

### Imports verificados
```php
use App\Http\Controllers\Controller;                 // app/Http/Controllers/Controller.php
use App\Models\OrdenProduccion;                       // app/Models/OrdenProduccion.php:9
use App\Models\Pedido;                                // app/Models/Pedido.php
use App\Models\User;                                  // app/Models/User.php (inspector via created_by-style)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;                      // ya usado en OrdenProduccionController
```

### Firmas existentes a usar
```php
// app/Models/OrdenProduccion.php
class OrdenProduccion extends Model {
    use HasFactory, SoftDeletes;
    protected $table = 'orden_produccion';
    // columnas vivas (verificadas en BD): pedido_id, detalle_pedido_id, producto_id,
    //   empleado_id, cantidad_solicitada, cantidad_producida, cantidad_defectuosa,
    //   fecha_inicio, fecha_fin_estimada, fecha_fin_real, estado, notas,
    //   motivo_cancelacion, created_by, timestamps, deleted_at
    // estado: STRING (no enum DB) — valores: 'Pendiente' | 'En Proceso' | 'Finalizado' | 'Cancelado'
    public function pedido(): BelongsTo;              // :123
    public function detallePedido(): BelongsTo;       // :58 (FK detalle_pedido_id)
    public function producto(): BelongsTo;            // :41
    public function creadoPor(): BelongsTo;           // :118 (User, created_by)
    public function subordenes(): HasMany;            // :141 (SubOrdenProduccion)
    public function getNombreProductoAttribute(): string; // :70 (nombre legible, dinámico/legacy)
    public function getProgresoAttribute(): float;    // :109 (producida/solicitada)
    public function recalcularEstadoDesdeSubordenes(): void; // :151 — 'Finalizado' exige
        // todas las subórdenes 'Finalizado' Y cantidad_producida >= cantidad_solicitada;
        // si baja cantidad_producida → recalcula a 'En Proceso'
}

// routes/web.php — patrón de ordenes (rutas específicas ANTES del resource), :262-271
Route::get('ordenes-data', [OrdenProduccionController::class, 'getOrdenes'])->name('ordenes.data');
Route::post('ordenes/{orden}/avance', [OrdenProduccionController::class, 'registrarAvance'])->name('ordenes.avance');

// config/modulos.php — forma de un módulo de permisos (:60+)
// 'modulo' => ['nombre' => '...', 'acciones' => ['ver'=>'...','gestionar'=>'...'],
//              'rutas' => ['ruta.a|ruta.b' => 'accion']]
// clave de permiso = 'modulo.accion'; middleware aborta 403 si falta.

// resources/views/admin/layouts/sidebar.blade.php:576-581 — placeholder actual:
//   {{-- TODO: Crear ruta y controlador para Control de Calidad --}}
//   <a href="#" class="nav-link {{ request()->is('calidad*') ? 'active' : '' }}">
//       <i class="ri-shield-check-line me-1"></i> Control de Calidad
```

### Convenciones a respetar (ver `docs/conventions/`)
- `docs/conventions/business-flows.md` — flujo maestro + **invariantes** (stock SOLO en producción; no tocar `MovimientoInsumo` desde calidad).
- `docs/conventions/modal-system.md` — `atlantico-modal--op` para modales transaccionales.
- `docs/conventions/js-validations.md` — validación blur + submit.
- `docs/conventions/sidebar-colors.md` — el ítem va en el grupo Operativa.
- `AGENTS.md` § DataTable estándar — server-side, `autoWidth: false`.

### NO existe — no referenciar
- ~~`App\Models\ControlCalidad`~~ — se crea en esta feature (Módulo 1).
- ~~`App\Services\ControlCalidadService`~~ — se crea (Módulo 2).
- ~~`App\Http\Controllers\ControlCalidadController`~~ — se crea (Módulo 3).
- ~~ruta `calidad.*`~~ — se registra (Módulo 3).
- ~~tabla `produccion_diaria`~~ — **ELIMINADA** (el diccionario de datos la lista pero ya no existe); el avance vive en `orden_produccion` + `sub_orden_produccion`.
- ~~estado de orden tipo "En Calidad"/"Aprobado"~~ — NO se agrega al enum de estado; el estado de calidad se deriva de los registros `control_calidad` (ver Pregunta abierta #2).

---

## 7. Notas de implementación y restricciones

### Patrones a seguir
- Controller con `index` + `getOrdenesCalidad` (DataTable server-side) + `detalle` + `inspeccionar`.
- Validación en `StoreControlCalidadRequest` (cantidades coherentes + motivo condicional).
- Transacción en el Service con `lockForUpdate()` sobre la orden (evita doble inspección / carrera con producción).
- SoftDeletes en migración + modelo.
- Inspector = `auth()->id()` (el Supervisor logueado).

### Riesgos conocidos
| Riesgo | Mitigación |
|---|---|
| Reproceso vs. máquina de estados por subórdenes: bajar `cantidad_producida` recalcula a `En Proceso`, pero las subórdenes siguen `Finalizado` → ¿el endpoint de avance acepta nuevo avance? | Verificar `OrdenProduccionController::registrarAvance` y, si bloquea, reabrir/crear sububorden o permitir avance mientras `cantidad_producida < cantidad_solicitada`. **Detallar en la TASK del Service.** |
| Doble inspección concurrente de la misma orden | `lockForUpdate()` + re-chequear `estado === 'Finalizado'` dentro de la transacción |
| `cantidad_defectuosa` en reprocesos sucesivos | **Resuelto (#3): acumula histórico** — cada rechazo hace `+= rechazadas`. El listado de "pendientes de calidad" se basa en el estado/última inspección, no en esta columna |

### Dependencias externas
| Paquete | Versión | Razón |
|---|---|---|
| — | — | sin dependencias nuevas |

---

## 8. Preguntas abiertas

> Resolver antes de mergear. Marcar con [x] al cerrar y dejar la respuesta.

- [x] **#1 — ¿"Registrar entrega y cobro de saldo" entra en este FEAT-006 o es un FEAT aparte?** → **FEAT aparte.** FEAT-006 solo deja la **compuerta** (el pedido no se da por "listo para entrega" si tiene órdenes sin aprobar calidad). La pantalla de entrega/cobro de saldo será un FEAT futuro.
- [x] **#2 — ¿Cómo se marca que una orden ya está "aprobada por calidad"?** → **Opción A:** se **deriva de los registros `control_calidad`** (no se agrega columna ni estado a `orden_produccion`). Una orden está "aprobada por calidad" si tiene una inspección con resultado `aprobado`/`observado` y no quedó reproceso pendiente.
- [x] **#3 — ¿`cantidad_defectuosa` acumula histórico o solo el ciclo vigente?** → **Acumula histórico:** cada rechazo suma a `orden.cantidad_defectuosa` (refleja el total de defectuosas detectadas en todos los ciclos de reproceso de esa orden).
- [x] **#4 — Inspector: ¿`User` o `Empleado`?** → **`User`** (el usuario logueado, consistente con `created_by`). El actor "Supervisor" del diagrama se modela vía rol/permiso, no como FK a `empleado`.

---

## Historial de revisiones

| Versión | Fecha | Autor | Cambio |
|---|---|---|---|
| 0.1 | 2026-06-25 | Emmanuel Arroyo | Borrador inicial — derivado del diagrama de actividad + business-flows + decisiones del dueño del producto |
| 0.2 | 2026-06-25 | Emmanuel Arroyo | Resueltas las 4 preguntas abiertas (#1 entrega=FEAT aparte, #2 derivar de control_calidad, #3 defectuosa acumula, #4 inspector=User). Status → approved |
