---
type: feature        # feature | fix
base_branch: enmanuel
---

# Feature Specification: <Nombre de la feature>

**Feature ID**: FEAT-<NNN>
**Fecha**: YYYY-MM-DD
**Autor**: <nombre>
**Status**: draft | review | approved | shipped
**Versión objetivo**: <sprint o release>

---

## 1. Motivación y requisitos de negocio

> ¿Por qué existe esta feature? ¿Qué problema resuelve?

### Planteamiento del problema
<!-- Describe el dolor o capacidad faltante. Si viene del profesor, citarlo. -->

### Objetivos
- Objetivo 1
- Objetivo 2

### Fuera de alcance (No-Goals)
- Cosa N que NO se hará en esta feature

---

## 2. Diseño arquitectónico

### Resumen
<!-- Descripción de alto nivel de la solución -->

### Diagrama de componentes
```
Vista (Blade) ──→ Controller ──→ Service ──→ Modelo Eloquent
                       │                          │
                       └──→ Request (validation)  └──→ Migración DB
```

### Puntos de integración
| Componente existente | Tipo de integración | Notas |
|---|---|---|
| `App\Models\Pedido` | extiende | añade relación `controlCalidad()` |
| `resources/views/admin/pedidos/index.blade.php` | modifica | nuevo botón "Calidad" |
| `routes/web.php` | añade | grupo `admin.calidad.*` |

### Modelos de datos
```php
// Nueva tabla / modelo
Schema::create('control_calidad', function (Blueprint $table) {
    $table->id();
    $table->foreignId('pedido_id')->constrained('pedido');
    $table->enum('resultado', ['aprobado', 'rechazado', 'observado']);
    $table->text('observaciones')->nullable();
    $table->foreignId('empleado_id')->constrained('empleado');
    $table->timestamp('fecha_revision');
    $table->softDeletes();
    $table->timestamps();
});
```

### Rutas nuevas
| Verbo | URI | Acción | Nombre |
|---|---|---|---|
| GET | /admin/calidad | index | admin.calidad.index |
| POST | /admin/calidad | store | admin.calidad.store |
| GET | /admin/calidad/{id}/edit | edit | admin.calidad.edit |

### UI / Vistas
- Tipo de card: `card-transactional` (sección Transacciones) o `card-maestros` / `card-reportes` según corresponda — ver `AGENTS.md` § Estándares visuales
- Tipo de modal: `atlantico-modal atlantico-modal--op` (transaccional) — ver `docs/conventions/modal-system.md`
- DataTable: clase `dt-transactional`, anchos por `nth-child`, `lenguajeData` global

---

## 3. Desglose por módulos

> Cada módulo se convertirá en al menos una TASK en Fase 2.

### Módulo 1: <Nombre>
- **Path**: `app/Http/Controllers/Admin/ControlCalidadController.php`
- **Responsabilidad**: CRUD del control de calidad por pedido
- **Depende de**: existing `PedidoController`, modelo `Empleado`

### Módulo 2: <Nombre>
- **Path**: `resources/views/admin/calidad/index.blade.php`
- **Responsabilidad**: listado con DataTable + modal de registro
- **Depende de**: Módulo 1 (rutas registradas)

### Módulo N
- **Path**: ...

---

## 4. Test / QA Specification

### QA manual (golden path)
1. Login como admin → navegar a `/admin/calidad`
2. Verificar listado vacío si no hay registros
3. Click "Nuevo control" → modal abre con datos de pedido prellenados
4. Llenar form → submit → verificar registro en DataTable
5. Editar → cambiar resultado → verificar persistencia
6. Soft delete → verificar que desaparece del listado

### Edge cases a verificar
- Pedido sin empleado asignado
- Resultado "rechazado" debe abrir campo observaciones obligatorio
- Validación JS al cerrar Select2 (patrón `select2:close` — ver `docs/conventions/js-validations.md`)

### Dark mode
- Verificar contrastes en modal, DataTable, badges de resultado
- Usar `[data-bs-theme="dark"]` selectors si se necesita override

---

## 5. Criterios de aceptación

> Esta feature está completa cuando TODO lo siguiente es verdadero:

- [ ] Migración corre limpia en BD fresca: `php artisan migrate:fresh --seed`
- [ ] Controller pasa QA manual (sección 4)
- [ ] Vista respeta estándares visuales: card, modal, DataTable, validaciones JS
- [ ] Sidebar actualizado con el ítem nuevo si aplica (ver `docs/conventions/sidebar-colors.md`)
- [ ] Dark mode funcional sin estilos inline
- [ ] PR mergeada a `enmanuel`
- [ ] Doc nuevo en `docs/conventions/` si introduce patrón reusable

---

## 6. Codebase Contract

> **CRÍTICO — anclaje anti-alucinación.**
> Esta sección es la única fuente de verdad sobre qué existe en el código.
> Los implementadores (humanos o Claude Code) NO deben referenciar imports,
> rutas, métodos o tablas que no estén listados aquí sin verificarlos primero
> con `grep` o `read`.

### Imports verificados
```php
use App\Http\Controllers\Controller;                    // app/Http/Controllers/Controller.php
use App\Models\Pedido;                                  // app/Models/Pedido.php:1
use App\Models\Empleado;                                // app/Models/Empleado.php:1
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
```

### Firmas existentes a usar
```php
// app/Models/Pedido.php:NN
class Pedido extends Model {
    use HasFactory, SoftDeletes;
    protected $table = 'pedido';
    // relación: empleado(): BelongsTo
    // relación: detalles(): HasMany
}

// routes/web.php:NN — grupo admin con middleware auth
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(...);
```

### Convenciones a respetar (ver `docs/conventions/`)
- `docs/conventions/modal-system.md` — usar `atlantico-modal--op` para modales transaccionales
- `docs/conventions/js-validations.md` — patrón de validación blur + submit
- `docs/conventions/nested-modals.md` — fix global ya aplicado, NO reimplementar
- `AGENTS.md` § DataTable estándar — `table-layout: fixed`, `autoWidth: false`

### NO existe — no referenciar
- ~~`App\Services\ControlCalidadService`~~ — se crea en esta feature (TASK-???)
- ~~ruta `admin.calidad.*`~~ — se registra en TASK-???
- ~~columna `pedido.calidad_id`~~ — la FK va en la nueva tabla, no en `pedido`

---

## 7. Notas de implementación y restricciones

### Patrones a seguir
- Controller con métodos REST estándar (`index`, `create`, `store`, `edit`, `update`, `destroy`)
- Validación en `Form Request` separado (`app/Http/Requests/`)
- Modal `atlantico-modal--op` con header gradiente cyan
- DataTable con `dt-transactional`, anchos por `nth-child`, sin reglas globales `!important`
- SoftDeletes en migración + en el modelo

### Riesgos conocidos
| Riesgo | Mitigación |
|---|---|
| Conflicto z-index modal/SweetAlert | usar reglas centralizadas en `custom.css` |
| Validación condicional rota en `select2:close` | seguir patrón documentado en memoria |

### Dependencias externas
| Paquete | Versión | Razón |
|---|---|---|
| — | — | sin dependencias nuevas |

---

## 8. Preguntas abiertas

> Resolver antes de mergear. Marcar con [x] al cerrar y dejar la respuesta.

- [ ] ¿El control de calidad es por pedido o por detalle_pedido? — *Owner: profesor*
- [ ] ¿Necesita reportes PDF? — *Owner: emmanuel*

---

## Historial de revisiones

| Versión | Fecha | Autor | Cambio |
|---|---|---|---|
| 0.1 | YYYY-MM-DD | <nombre> | Borrador inicial |
