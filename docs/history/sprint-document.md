---
name: Sprint Task Assignment — Estado v3 post auditoría ramas Santiago
description: Documento de tareas del sprint actualizado 2026-03-22. 16 tareas (Vanessa 9 + Santiago 7). S-01/S-02/S-03 mergeadas con correcciones.
type: project
---

Documento oficial: `tareas/asignacion_tareas_sprint.html` (v2, 20/03/2026)

**Why:** Actualizado tras completar auditoría DB. Tareas redefinidas para enfocarse solo en Backend (MVC) y Frontend (Blade/JS) — cero migraciones.

**How to apply:** Exportar HTML a PDF para distribuir al equipo. Si se modifica, mantener el mismo diseño visual.

---

## Resumen actual: 16 tareas (antes 19)

### Vanessa — 9 tareas (Frontend & UI)
| ID | Título | Complejidad | Estado |
|---|---|---|---|
| V-01 | Simplificación Paginación DataTables (eliminar 10 overrides locales) | Baja | Pendiente |
| V-02 | Session Timeout (solo cambiar .env a 30 min) | Baja | Pendiente |
| V-03 | Refinamiento Paleta Colores Dashboard | Baja | Pendiente |
| V-04 | Fix Badge Jurídico Dark Mode | Baja | Pendiente |
| V-05 | Imagen Opcional en Edición (quitar `required` del input file) | Baja | Pendiente |
| V-07 | Cambiar Select de Estado a Switch Toggle en Productos | Baja | Pendiente |
| V-08 | Reubicar Botón Agregar Producto en Cotizaciones | Baja | Pendiente |
| V-09 | Fix Visual Ficha Clientes (espera S-01) | Baja | Desbloqueada |
| V-10 | Persistencia Fullscreen con localStorage | Media | Pendiente |

### Santiago — 7 tareas (Backend & Lógica)
| ID | Título | Complejidad | Estado |
|---|---|---|---|
| S-01 | Estándar Geográfico (labels Ciudad→Municipio) — PREREQUISITO | Media | Mergeada (corregida por TL) |
| S-02 | Eliminar Botón Agregar Cliente en Pedidos | Baja | Mergeada (corregida por TL) |
| S-03 | Fix Encoding UTF-8 SweetAlert en Pedidos | Baja | Mergeada (corregida por TL) |
| S-05 | Proveedores: Estado/Municipio en form jurídico (solo frontend+service) | Baja | Mergeada (2026-03-27) |
| S-06 | SoftDeletes UI: Inhabilitar + Historial (DB ya lista) | Media | Mergeada (2026-03-27, observación: catálogos maestros no cubiertos) |
| S-07 | Filtros por Columna en Clientes | Alta | Mergeada (2026-03-27, conflictos resueltos por TL) |
| S-09 | Estándar Reportes PDF (6 reportes por migrar al estándar canónico) | Alta | Pendiente |

### Auditoría de ramas Santiago (2026-03-22)
- S-01: incompleta — solo cubría Clientes, faltaban Empleados y Pedidos. Completada por TL.
- S-02: funcional pero con JS huérfano (~85 líneas código muerto). Limpiado por TL.
- S-03: corrigió 5 strings pero quedaban ~12 más con encoding roto. Completada por TL.
- Fix adicional: eliminado bloque `currentProductItemIndex` huérfano + `confirmButtonClass` deprecado (3 ocurrencias).

### Tareas eliminadas (completadas por Tech Lead)
- **V-06** — Badges Activo/Inactivo en Productos (ya implementados)
- **S-04** — Fix Modal Edición Empleados (ya funciona correctamente)
- **S-08** — Pagos Múltiples (UI + backend + DB 100% operativo)

### Dependencias
- S-01 completada → V-09 y S-05 desbloqueadas
- S-02 → S-03 (ambas completadas)
