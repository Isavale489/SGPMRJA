# TASK-008: Actualizar doc ux-search-filters

**Feature**: FEAT-001 — unified-filters-rollout
**Spec**: `sdd/specs/unified-filters-rollout.spec.md`
**Status**: done
**Priority**: low
**Esfuerzo estimado**: S (< 2h)
**Depends-on**: TASK-001, TASK-002, TASK-003, TASK-004, TASK-005, TASK-006, TASK-007
**Assigned-to**: emmanuel

---

## Contexto

El spec requiere actualizar la tabla "Estado por modulo" en `docs/conventions/ux-search-filters.md` una vez que los 7 modulos esten migrados.

---

## Scope

- Actualizar la tabla "Estado por modulo" para incluir Cotizaciones, Pedidos, Ordenes, Produccion Diaria, Inventario Movimientos, Inventario Alertas y Users.
- Mover esos modulos fuera de la seccion "Modulos donde NO aplica".
- Mantener el formato del doc y sus ejemplos sin cambios de estilo.

**NO esta en alcance**:
- Cambiar el patron base o agregar nuevas clases CSS.

---

## Archivos a crear / modificar

| Archivo | Accion | Descripcion |
|---|---|---|
| `docs/conventions/ux-search-filters.md` | MODIFY | Actualizar estado por modulo |

---

## Codebase Contract (Anti-Alucinacion)

### Referencias verificadas
- `docs/conventions/ux-search-filters.md` (tabla "Estado por modulo" y seccion "Modulos donde NO aplica")

---

## Criterios de aceptacion

- [ ] Tabla "Estado por modulo" refleja los 7 modulos migrados
- [ ] La lista "Modulos donde NO aplica" ya no incluye esos modulos
- [ ] El doc mantiene el formato actual

---

## QA manual

1. Abrir `docs/conventions/ux-search-filters.md` y verificar el contenido.

---

## Instrucciones para el ejecutor

1. Leer el spec completo.
2. Verificar dependencias.
3. Actualizar el header `Status` y `Assigned-to`.
4. Crear rama `feat/TASK-008-doc-ux-search-filters-estado`.
5. Implementar dentro del scope y completar QA.
6. Mover a `sdd/tasks/completed/` y llenar Nota de Completitud.

---

## Nota de Completitud

**Completado por**: Emmanuel + Claude
**Fecha**: 2026-05-26
**Commits**: (reconciliación SDD — este commit)
**Notas**: Actualizada la sección "Estado por módulo" de `docs/conventions/ux-search-filters.md`. Antes listaba solo Clientes/Proveedores/Productos y declaraba que los operativos "NO aplica el patrón" — ya falso tras el rollout FEAT-001. Ahora la tabla refleja los 3 grupos (maestros navy, operativos emerald, sistema) con los filtros reales verificados por grep de cada `index.blade.php`. Añadida nota de la variante `emerald-theme` introducida en `480b4bd`.

**Desviaciones del spec**: ninguna.
