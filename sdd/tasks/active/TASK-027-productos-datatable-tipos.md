# TASK-027: DataTable de Productos = Tipos (+ vista secundaria de SKUs)

**Feature**: FEAT-003 — variantes-dinamicas
**Spec**: `sdd/specs/variantes-dinamicas.spec.md`
**Status**: pending
**Priority**: medium
**Esfuerzo estimado**: M
**Depends-on**: TASK-019
**Assigned-to**: unassigned

---

## Contexto
El objetivo visible del usuario: que la DataTable principal de Productos muestre **tipos** (una "Franela"), no una fila por combinación. Implementa el Módulo 6. Decisión §8: solo tipos en la principal; legacy/SKUs en vista secundaria opcional.

## Scope
- Vista principal de `/productos`: listar **Tipos de Producto** (con sus telas/atributos/precio_confeccion), no combinaciones.
- Vista secundaria opcional "SKUs generados / productos legacy" para auditar los `producto` existentes (los 9 legacy + cualquiera materializado).
- Ajustar filtros y acciones acorde (editar tipo, gestionar telas/atributos).

**NO está en alcance**: lógica de variante (TASK-021/024), órdenes (TASK-026).

## Archivos a crear / modificar
| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/productos/index.blade.php` | MODIFY | DataTable de tipos + acceso a SKUs |
| `app/Http/Controllers/ProductoController.php` | MODIFY | data source de tipos / vista SKUs |
| `routes/web.php` | MODIFY (si aplica) | ruta vista SKUs secundaria |

## Codebase Contract (Anti-Alucinación)
### Firmas verificadas
```php
// app/Http/Controllers/ProductoController.php
//   index(): $tiposProducto, $telasDisponibles=Insumo::telas(), $insumosDisponibles, $historial (:22-32)
//   getProductos()/getX DataTable server-side (verificar método exacto con grep)
// app/Models/TipoProducto — productos() HasMany, telas() (TASK-019), atributos()
// resources/views/admin/productos/index.blade.php — DataTable actual + modal Tipos (:447)
```
### Convenciones
- `AGENTS.md` § Estándares visuales — card `card-maestros`, DataTable estándar.
- `docs/conventions/ux-search-filters.md`.
### NO existe — no referenciar
- ~~vista "SKUs generados"~~ — se crea en ESTA task (si se decide implementarla).

## Criterios de aceptación
- [ ] La vista principal de Productos muestra una fila por **tipo** (una "Franela").
- [ ] Los productos legacy siguen accesibles (vista secundaria) sin romperse.
- [ ] Filtros/acciones coherentes con el nuevo listado.
- [ ] Dark mode OK.

## QA manual
1. /productos → ver lista de tipos (una Franela). 2. Abrir vista secundaria de SKUs → ver los legacy.

## Nota de Completitud
*(Llenar al terminar)*
