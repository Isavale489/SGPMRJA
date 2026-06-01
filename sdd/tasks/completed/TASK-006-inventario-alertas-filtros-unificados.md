# TASK-006: Filtros unificados en Inventario Alertas

**Feature**: FEAT-001 — unified-filters-rollout
**Spec**: `sdd/specs/unified-filters-rollout.spec.md`
**Status**: done
**Priority**: low
**Esfuerzo estimado**: S (< 2h)
**Depends-on**: none
**Assigned-to**: santiago

---

## Contexto

Migrar el listado de Alertas de Inventario al patron unificado de busqueda + filtros (spec seccion 3, Modulo 6). Este modulo no requiere filtros adicionales (solo visual + busqueda).

---

## Scope

- Cambiar la card a `card-transactional` para alinear con Gestion Operativa.
- Reemplazar el header con `search-box` por la estructura `advanced-filters-wrapper navy-theme` y `navy-filter-header`.
- Mantener solo la busqueda y el boton Filtros (sin selects adicionales).
- Cablear JS estandar: debounce 300ms, `updateFilterBadge()` (0 filtros), `clear-filters` y toggle del collapse.
- Preservar el boton Volver y el resto del contenido.

**NO esta en alcance**:
- Agregar filtros nuevos.
- Cambiar columnas o estructura del DataTable.
- Introducir CSS nuevo o estilos inline.

---

## Archivos a crear / modificar

| Archivo | Accion | Descripcion |
|---|---|---|
| `resources/views/admin/inventario/alertas/index.blade.php` | MODIFY | Card transaccional + header unificado + busqueda |

---

## Codebase Contract (Anti-Alucinacion)

### Firmas existentes a usar
```php
// app/Http/Controllers/MovimientoInsumoController.php
public function alertasStock()
```

### Vistas / JS de referencia
- `resources/views/admin/inventario/alertas/index.blade.php` (header con search-box actual + script inline)
- `resources/views/admin/clientes/index.blade.php` (markup canonico `advanced-filters-wrapper navy-theme`)

### Convenciones a respetar (ver `docs/conventions/`)
- `docs/conventions/ux-search-filters.md`
- `AGENTS.md` § Estndares visuales

### NO existe — no referenciar
- ~~clase CSS `.operativa-filter-*`~~ — no existe

---

## Notas de implementacion

- La vista usa script inline; adaptar el handler del buscador para usar debounce y `clear-filters`.
- El badge debe permanecer oculto (0 filtros).

---

## Criterios de aceptacion

- [ ] Header con `navy-filter-header` visible y panel colapsable funcionando
- [ ] Busqueda funciona con debounce
- [ ] Boton Limpiar limpia busqueda y tabla

---

## QA manual

1. Ir a `/inventario/alertas` y verificar el nuevo header unificado.
2. Buscar texto y ver que filtra con debounce.
3. Click en Limpiar filtros.

---

## Instrucciones para el ejecutor

1. Leer el spec completo.
2. Verificar dependencias.
3. Actualizar el header `Status` y `Assigned-to`.
4. Crear rama `feat/TASK-006-inventario-alertas-filtros-unificados`.
5. Implementar dentro del scope y completar QA.
6. Mover a `sdd/tasks/completed/` y llenar Nota de Completitud.

---

## Nota de Completitud

**Completado por**: santiago
**Fecha**: 2026-05-23
**Commits**: c80fa46
**Notas**: Migrado el header a filtros unificados, debounce de busqueda y boton Limpiar; card y tabla con clases transaccionales.

**Desviaciones del spec**: ninguna
