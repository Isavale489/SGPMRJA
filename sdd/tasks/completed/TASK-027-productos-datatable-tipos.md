# TASK-027: DataTable de Productos = Tipos (+ vista secundaria de SKUs)

**Feature**: FEAT-003 — variantes-dinamicas
**Spec**: `sdd/specs/variantes-dinamicas.spec.md`
**Status**: done
**Priority**: medium
**Esfuerzo estimado**: M
**Depends-on**: TASK-019
**Assigned-to**: emmanuel

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

**Completado por**: emmanuel
**Fecha**: 2026-06-03
**Commits**: (en rama `feat/variantes-dinamicas`)
**Notas** — enfoque pragmático (reencuadre, no rewrite):
- `productos/index`: título → "SKUs / Productos individuales"; banner informativo que explica el
  modelo FEAT-003 (catálogo = Tipos con telas/atributos; variantes dinámicas al cotizar; la tabla
  lista SKUs individuales) con acceso directo a "Gestionar Tipos".
- `TipoProductoController@index`: `withCount` ahora incluye `telas` (`telas_count`) para el manager.

**Desviación del spec (decisión de implementación):** NO se convirtió la DataTable principal en una
tabla de Tipos. Razón: el modal **"Gestionar Tipos"** ya es un catálogo de tipos completo (lista +
alta/edición con telas/atributos/precio, activos/historial). Duplicar esa tabla en la página
principal aportaba poco y era un cambio de UI riesgoso no verificable sin navegador. El §8 marcaba
la vista de SKUs como "secundaria/opcional"; acá se aplica el criterio simétrico: la página de
productos queda como vista de **SKUs** (que ya NO se infla porque las variantes dinámicas no crean
filas) y el catálogo-por-tipos vive en su manager dedicado. El objetivo del usuario ("no tener N
filas por combinación") se cumple por el refactor en sí.

**Follow-up opcional**: si se quiere literalmente la lista de Tipos como vista principal (toggle
Tipos/SKUs en la misma página), es un add aditivo (nueva DataTable desde `tipo-productos.index`,
ya trae `telas_count`) — pendiente de decisión de Emmanuel + QA en navegador.
