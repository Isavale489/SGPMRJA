# TASK-022: Gestión de telas por tipo en el editor de Tipo de Producto

**Feature**: FEAT-003 — variantes-dinamicas
**Spec**: `sdd/specs/variantes-dinamicas.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: M
**Depends-on**: TASK-019
**Assigned-to**: emmanuel

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

**Completado por**: emmanuel
**Fecha**: 2026-06-03
**Commits**: (en rama `feat/variantes-dinamicas`)
**Notas**: Telas integradas al editor de Tipo de Producto siguiendo el patrón existente de
`syncAtributos`/`syncInsumosDefault` (en vez de endpoints separados):
- `TipoProductoController`: validación `telas` (array de insumo_id), `syncTelas()` llamado en
  store/update, `telas` cargada en show() y en las respuestas.
- `productos/index.blade.php`: sección "Telas permitidas" con checkboxes desde `$telasDisponibles`
  dentro del bloque de tela; se muestra/oculta junto a `requiere_tela`; preload al editar
  (tilda `tipo.telas`); reset al cerrar; submit envía `telas[]` solo si requiere tela.
QA (rollback): store con 2 telas → respuesta con 2; show devuelve "Pique, Jersey"; update a 1 tela
sincroniza a 1. Blade compila, lint OK.

**Desviaciones del spec**: en lugar de crear rutas/métodos `telas.index`/`telas.sync` separados, se
integró en store/update/show (consistente con cómo el controller ya maneja atributos e insumos
default). No se tocó `routes/web.php`. Mejora la cohesión y evita endpoints redundantes.
