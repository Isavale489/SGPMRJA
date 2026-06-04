# TASK-022: Gestión de telas por tipo en el editor de Tipo de Producto

**Feature**: FEAT-003 — variantes-dinamicas
**Spec**: `sdd/specs/variantes-dinamicas.spec.md`
**Status**: pending
**Priority**: high
**Esfuerzo estimado**: M
**Depends-on**: TASK-019
**Assigned-to**: unassigned

---

## Contexto
Donde el usuario "le registra más telas a un tipo de producto" — cómodamente, sin crear filas `producto`. Implementa el Módulo 2: endpoints + UI multi-select de telas en el modal "Gestionar Tipos de Producto".

## Scope
- Endpoints en `TipoProductoController`: `telasIndex(TipoProducto)` (JSON ids asignados) y `telasSync(TipoProducto)` (recibe array de insumo_id tela → `telas()->sync()`).
- Rutas `tipos-producto.telas.index` (GET) y `tipos-producto.telas.sync` (PUT).
- UI en el modal de Tipos de Producto (en `productos/index.blade.php`): multi-select / checklist de telas disponibles (`Insumo::telas()`), precargado al editar, guardado junto al tipo.

**NO está en alcance**: usar las telas en cotización (TASK-024).

## Archivos a crear / modificar
| Archivo | Acción | Descripción |
|---|---|---|
| `app/Http/Controllers/TipoProductoController.php` | MODIFY | `telasIndex`, `telasSync` |
| `routes/web.php` | MODIFY | 2 rutas telas |
| `resources/views/admin/productos/index.blade.php` | MODIFY | multi-select telas en modal Tipos + JS |

## Codebase Contract (Anti-Alucinación)
### Firmas existentes
```php
// app/Models/TipoProducto.php — telas() BelongsToMany (TASK-019)
// app/Http/Controllers/ProductoController.php:22 — $telasDisponibles = Insumo::telas()->get([...]) ← misma fuente
// resources/views/admin/productos/index.blade.php:447 — "Modal para gestionar Tipos de Producto"
//   :1395 funciones JS "Tipos de Producto"; :1556 validaciones AJAX onblur Tipos
// Route model binding {tipoProducto}/{tipo} → TipoProducto
```
### Convenciones
- `docs/conventions/modal-system.md` — modal `atlantico-modal` (maestros).
- `docs/conventions/js-validations.md`.
### NO existe — no referenciar
- ~~rutas `tipos-producto.telas.*`~~ — se registran en ESTA task.

## Criterios de aceptación
- [ ] Al editar un Tipo, se ven precargadas sus telas asignadas.
- [ ] Asignar/quitar telas y guardar → persiste en `tipo_producto_tela`.
- [ ] `requiere_tela=false` → la sección de telas puede ocultarse/ser opcional.
- [ ] Dark mode OK.

## QA manual
1. /productos → Gestionar Tipos → editar "Franela" → marcar {Jersey, Algodón} → guardar.
2. Reabrir → siguen marcadas. Verificar fila en `tipo_producto_tela`.

## Nota de Completitud
*(Llenar al terminar)*
