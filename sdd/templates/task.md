# TASK-<NNN>: <Título>

**Feature**: FEAT-<NNN> — <Título de la feature>
**Spec**: `sdd/specs/<feature-slug>.spec.md`
**Status**: pending | in-progress | done
**Priority**: high | medium | low
**Esfuerzo estimado**: S (< 2h) | M (2-4h) | L (4-8h) | XL (> 8h)
**Depends-on**: TASK-<X>, TASK-<Y>   *(o "none")*
**Assigned-to**: emmanuel | vanessa | santiago | unassigned

---

## Contexto

> Por qué existe esta task. Cómo encaja en la feature global.
> Referenciar la sección del spec que implementa.

---

## Scope

> Exactamente qué debe hacer esta task. Nada más.
> Usar lenguaje imperativo: "Implementar X", "Añadir Y", "Refactorizar Z".

- Implementar ...
- Añadir ...
- Crear migración para ...

**NO está en alcance**:
- Cosa A (pertenece a TASK-???)
- Cosa B (pertenece a otra feature)

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `app/Http/Controllers/Admin/ControlCalidadController.php` | CREATE | Controller REST |
| `app/Models/ControlCalidad.php` | CREATE | Modelo Eloquent con SoftDeletes |
| `database/migrations/YYYY_MM_DD_HHMMSS_create_control_calidad_table.php` | CREATE | Migración tabla |
| `routes/web.php` | MODIFY | Añadir grupo `admin.calidad.*` |
| `resources/views/admin/sidebar.blade.php` | MODIFY | Item en sección Transacciones |

---

## Codebase Contract (Anti-Alucinación)

> **CRÍTICO**: Esta sección contiene referencias VERIFICADAS del código real.
> El implementador (humano o Claude Code) DEBE usar estos imports, clases y firmas EXACTOS.
> **NO** inventar, adivinar ni asumir nada que no esté listado.
> Si necesitas algo que no está aquí, VERIFICA con `grep` o `read` antes.

### Imports verificados
```php
use App\Http\Controllers\Controller;
use App\Models\Pedido;            // app/Models/Pedido.php
use App\Models\Empleado;          // app/Models/Empleado.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
```

### Firmas existentes a usar
```php
// app/Models/Pedido.php
class Pedido extends Model {
    use HasFactory, SoftDeletes;
    protected $table = 'pedido';
    public function empleado(): BelongsTo { ... }
}

// routes/web.php — patrón a seguir
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('pedidos', PedidoController::class);
});
```

### Convenciones a respetar (ver `docs/conventions/`)
- `AGENTS.md` § DataTable estándar
- `docs/conventions/modal-system.md`
- `docs/conventions/js-validations.md`
- `docs/conventions/softdeletes-unique.md` si la task genera códigos secuenciales

### NO existe — no referenciar
- ~~`App\Services\ControlCalidadService`~~ — no existe
- ~~ruta `admin.calidad.show`~~ — no se registra (no hay vista show)

---

## Notas de implementación

> Guía técnica para el ejecutor.

### Patrón a seguir
```php
// Copia este esqueleto de PedidoController (app/Http/Controllers/Admin/PedidoController.php)
class ControlCalidadController extends Controller
{
    public function index(Request $request) {
        // ...
    }
    public function store(Request $request) {
        $request->validate([...]);
        // ...
    }
}
```

### Restricciones clave
- Usar transacciones DB cuando se afecten múltiples tablas
- Validación server-side obligatoria (no confiar solo en JS)
- Modal con clase `atlantico-modal atlantico-modal--op`
- DataTable con clase `dt-transactional`, `lenguajeData` global
- Sidebar item con clase `section-operativa`

### Referencias en el código
- `app/Http/Controllers/Admin/PedidoController.php` — patrón controller REST
- `resources/views/admin/pedidos/index.blade.php` — patrón vista listado
- `resources/views/admin/layouts/sidebar.blade.php` — patrón sidebar

---

## Criterios de aceptación

- [ ] Implementación completa según el scope
- [ ] Migración corre limpia: `php artisan migrate:fresh --seed`
- [ ] Rutas listadas en `php artisan route:list | grep calidad`
- [ ] QA manual (sección siguiente) pasa
- [ ] Sin estilos inline en Blade (todo en `custom.css`)
- [ ] Dark mode probado
- [ ] PR contra `enmanuel` con descripción enlazando esta task

---

## QA manual

> Pasos para validar la task antes de marcar `done`.

1. `php artisan migrate:fresh --seed`
2. Login admin → ir a `/admin/calidad`
3. Verificar [comportamiento esperado]
4. Probar edge case A
5. Probar dark mode
6. ...

---

## Instrucciones para el ejecutor

Cuando tomes esta task:

1. **Lee el spec** completo en el path indicado arriba.
2. **Verifica dependencias** — confirma que las tasks en `Depends-on` están en `tasks/completed/`.
3. **Verifica el Codebase Contract** antes de codificar:
   - Confirma que cada import existe (`grep` en `app/`)
   - Confirma que cada firma de clase/método sigue vigente
   - Si algo cambió, actualiza el contrato PRIMERO y luego implementa
   - **NUNCA** uses un import, atributo o método que no esté en el contrato sin verificarlo
4. **Actualiza el header**: `Status: in-progress`, `Assigned-to: <tu-nombre>`.
5. **Crea rama**: `git checkout -b feat/TASK-<NNN>-<slug>` desde `enmanuel`.
6. **Implementa** dentro del scope. Si descubres trabajo extra, créalo como task nueva.
7. **Verifica** los criterios de aceptación y el QA manual.
8. **Mueve este archivo** a `sdd/tasks/completed/TASK-<NNN>-<slug>.md`.
9. **Rellena la Nota de Completitud** abajo.
10. **PR** contra `enmanuel`.

---

## Nota de Completitud

*(Llenar al terminar)*

**Completado por**: <nombre>
**Fecha**: YYYY-MM-DD
**Commits**: <SHA1>, <SHA2>
**Notas**: Qué se implementó, desviaciones del scope, problemas encontrados.

**Desviaciones del spec**: ninguna | <describir>
