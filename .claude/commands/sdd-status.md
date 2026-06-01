# /sdd-status — Resumen del estado SDD del proyecto

Muestra un resumen del estado actual de specs y tasks SDD.

## Uso
```
/sdd-status [--feature <feature-slug>]
```

Sin argumentos: muestra el panorama completo del proyecto.
Con `--feature <slug>`: muestra solo el detalle de esa feature.

## Pasos

### 1. Listar specs
- Recorrer `sdd/specs/*.spec.md`
- Para cada uno, leer el header y extraer:
  - `Feature ID`
  - `Status` (draft / review / approved / shipped)
  - Título
  - Fecha

### 2. Listar tasks
- Recorrer `sdd/tasks/active/*.md` y `sdd/tasks/completed/*.md`
- Para cada uno, leer el header y extraer:
  - `TASK-NNN`
  - Feature al que pertenece
  - `Status` (pending / in-progress / done)
  - `Priority`
  - `Esfuerzo estimado`
  - `Depends-on`
  - `Assigned-to`

### 3. Agrupar y mostrar

Formato de salida:

```
📋 SGPMRJA — Estado SDD

┌─ Specs ──────────────────────────────────────────────────────────┐
│ FEAT-001 control-calidad        [approved]  6 tasks  4 done       │
│ FEAT-002 garantias              [draft]     —                     │
│ FEAT-003 reporte-ventas-mensual [shipped]   3 tasks  3 done       │
└──────────────────────────────────────────────────────────────────┘

┌─ Tasks activas ──────────────────────────────────────────────────┐
│ TASK-005 calidad-controller     pending  [emmanuel]  Depends: 003 │
│ TASK-006 calidad-vista-index    pending  [vanessa]   Depends: 005 │
│ TASK-007 garantias-migracion    pending  [unassigned]            │
└──────────────────────────────────────────────────────────────────┘

┌─ En curso ───────────────────────────────────────────────────────┐
│ TASK-004 calidad-modelo         in-progress  [santiago]           │
└──────────────────────────────────────────────────────────────────┘

📊 Resumen
   Specs:    3 (1 draft, 1 approved, 1 shipped)
   Tasks:    10 total — 6 done, 1 in-progress, 3 pending
   Por dev:  emmanuel: 1 | vanessa: 1 | santiago: 1 | unassigned: 1
   Bloqueadas: TASK-006 (espera TASK-005)
   Listas para tomar: TASK-007 (sin dependencias, sin asignar)

💡 Sugerencia: /sdd-next para ver qué tomar siguiente.
```

### 4. Con `--feature <slug>`

Muestra el detalle de un solo spec:

```
📄 FEAT-001 — control-calidad

Status: approved
Autor: emmanuel
Fecha: 2026-05-20
Spec: sdd/specs/control-calidad.spec.md

Tasks (6 total — 4 done, 1 in-progress, 1 pending):

  ✅ TASK-001 calidad-migracion        done       [santiago]
  ✅ TASK-002 calidad-modelo           done       [santiago]
  ✅ TASK-003 calidad-rutas            done       [emmanuel]
  ✅ TASK-004 calidad-controller       done       [emmanuel]
  🔄 TASK-005 calidad-vista-index      in-progress [vanessa]
  ⏳ TASK-006 calidad-vista-modal      pending    [unassigned]  Depends: TASK-005

Próxima tomable: ninguna (TASK-006 bloqueada por TASK-005).
```

## Notas
- Los conteos y agrupaciones se calculan parseando el frontmatter/encabezado markdown de cada archivo.
- Si encuentras un archivo malformado (sin `Status:` o `Feature ID:`), reportarlo separado bajo `⚠️ Archivos con metadata faltante`.
