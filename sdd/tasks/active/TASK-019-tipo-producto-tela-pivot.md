# TASK-019: Pivot `tipo_producto_tela` + relación `TipoProducto::telas()`

**Feature**: FEAT-003 — variantes-dinamicas
**Spec**: `sdd/specs/variantes-dinamicas.spec.md`
**Status**: pending
**Priority**: high
**Esfuerzo estimado**: S
**Depends-on**: none
**Assigned-to**: unassigned

---

## Contexto
Base de datos del refactor. Define **qué telas puede usar cada tipo de producto** (hoy se deduce de productos existentes). Implementa el Módulo 1 del spec. Sin esto, el selector de variante no tiene fuente de telas por tipo.

## Scope
- Crear migración del pivot `tipo_producto_tela` (tipo_producto_id, insumo_id, timestamps, único compuesto, FKs cascadeOnDelete).
- Añadir relación `TipoProducto::telas(): BelongsToMany` → `Insumo` vía `tipo_producto_tela`.

**NO está en alcance**: UI de asignación (TASK-022), uso en cotización (TASK-024), nullable producto_id (TASK-020).

## Archivos a crear / modificar
| Archivo | Acción | Descripción |
|---|---|---|
| `database/migrations/<fecha>_create_tipo_producto_tela_table.php` | CREATE | Pivot |
| `app/Models/TipoProducto.php` | MODIFY | Relación `telas()` |

## Codebase Contract (Anti-Alucinación)
### Firmas existentes
```php
// app/Models/TipoProducto.php:15 — fillable: nombre, prefijo, descripcion, precio_confeccion, requiere_tela, consumo_tela_por_unidad
//   atributos(): BelongsToMany 'tipo_producto_atributo' (:39)  ← copiar este patrón para telas()
//   insumosDefault(): BelongsToMany 'tipo_producto_insumo' (:52)
// app/Models/Insumo.php — scope telas() = where tipo='Tela'
// Tabla insumo PK 'id'; tabla tipo_producto PK 'id'
```
### Patrón de pivot a copiar
```php
// database/migrations/2026_05_28_191504_create_tipo_producto_insumo_table.php — MISMA estructura
// (id, FKs, unique compuesto). Replicar quitando 'cantidad_estimada'.
```
### NO existe — no referenciar
- ~~tabla `tela`~~ — la tela es `Insumo` con `tipo='Tela'`. NO crear tabla separada (ver `docs/conventions/product-variants.md` §1).
- ~~`TipoProducto::telas()`~~ — se crea en ESTA task.

## Criterios de aceptación
- [ ] `php artisan migrate` corre limpio; existe tabla `tipo_producto_tela` con único `(tipo_producto_id, insumo_id)`.
- [ ] `TipoProducto::find(x)->telas` devuelve insumos tipo Tela asignados.
- [ ] `$tipo->telas()->sync([...])` funciona.

## QA manual
1. `php artisan migrate`
2. Tinker: `\$t = TipoProducto::first(); \$t->telas()->sync([id_tela]); \$t->telas;` → devuelve la tela.

## Nota de Completitud
*(Llenar al terminar)*
