---
description: Sugerir próximas tasks SDD desbloqueadas para tomar
---

# /sdd-next — Sugerir próximas tasks desbloqueadas

Identifica tasks que están listas para ser tomadas — sin dependencias pendientes, sin asignar (o asignadas al usuario), priorizadas.

## Uso
```
/sdd-next [--for <nombre>]
```

Sin argumentos: muestra las top 3-5 tasks desbloqueadas en todo el proyecto.
Con `--for <nombre>`: muestra tasks ya asignadas a esa persona (en curso o pendientes).

## Algoritmo

### 1. Cargar grafo de tasks

- Recorrer `sdd/tasks/active/*.md` y `sdd/tasks/completed/*.md`.
- Para cada task extraer del header: ID, status, priority, depends_on, assigned_to, feature.

### 2. Filtrar candidatos

Una task es **tomable** si:
- `Status: pending`
- `Assigned-to: unassigned` (o coincide con `--for <nombre>`)
- **Todas las dependencias** en `Depends-on` están en `sdd/tasks/completed/` con `Status: done`

### 3. Priorizar

Ordenar candidatos por:
1. **Prioridad** (high > medium > low)
2. **Esfuerzo** (S antes que XL — para entregar quick wins primero)
3. **Feature aprobado** primero (specs en `approved` antes que `draft`)
4. **Antigüedad** (TASK-NNN más bajos primero, para limpiar backlog viejo)

### 4. Output

```
🎯 Tasks listas para tomar

1. TASK-007 garantias-migracion
   Feature: FEAT-002 garantias [approved]
   Priority: high   Effort: S   Depends: none
   Archivo: sdd/tasks/active/TASK-007-garantias-migracion.md

2. TASK-008 control-calidad-pdf-reporte
   Feature: FEAT-001 control-calidad [approved]
   Priority: medium Effort: M   Depends: TASK-004 (done ✅)
   Archivo: sdd/tasks/active/TASK-008-control-calidad-pdf-reporte.md

3. TASK-010 reporte-mensual-grafico
   Feature: FEAT-003 reporte-ventas-mensual [approved]
   Priority: medium Effort: M   Depends: none
   Archivo: sdd/tasks/active/TASK-010-reporte-mensual-grafico.md

Para tomar una task:
  1. git checkout enmanuel && git pull
  2. git checkout -b feat/TASK-007-garantias-migracion
  3. Editar el header del archivo task:
     Status: in-progress
     Assigned-to: <tu-nombre>
  4. Implementar siguiendo el Codebase Contract.

💡 Tip: si una task de alta prioridad está bloqueada esperando otra task sin asignar,
   considera tomar primero la bloqueante para desbloquear al equipo.
```

### 5. Variante `--for <nombre>`

Muestra:
- Tasks asignadas a esa persona y aún pendientes/en-curso.
- Tasks bloqueadas por sus dependencias.
- Sugerencias de qué tomar siguiente si esa persona no tiene asignaciones.

```
👤 Tasks de emmanuel

En curso:
  🔄 TASK-005 calidad-vista-index   (priority: high, effort: M)

Pendientes asignadas:
  ⏳ TASK-009 calidad-export-excel  (priority: medium, blocked by TASK-005)

Sin tasks pendientes desbloqueadas. Sugerencias del backlog general:
  → TASK-007 garantias-migracion (high, S, sin asignar)
```

### 6. Si no hay candidatos

```
ℹ️  No hay tasks desbloqueadas en este momento.

Razones posibles:
  - Todas las tasks pending tienen dependencias sin completar
  - Todas están asignadas
  - El backlog está vacío

Acción sugerida:
  - /sdd-status para ver el estado completo
  - Crear nuevo spec: /sdd-spec <slug>
```

## Reglas
- Una task **bloqueada** (con dependencias `pending` o `in-progress`) NUNCA debe sugerirse.
- Tasks de specs en `draft` se ignoran (el spec no está validado todavía).
- Si hay tasks sin metadata válida, reportarlas aparte para que alguien las corrija.
