# SGPMRJA — Spec-Driven Development (SDD)

## Visión general

Esta metodología convierte las **especificaciones en la única fuente de verdad** del proyecto. Antes de codificar un módulo nuevo o un refactor grande, escribimos un *spec* y lo descomponemos en *tasks* atómicas que cualquier miembro del equipo (Emmanuel, Vanessa, Santiago) o Claude Code puede tomar de forma independiente.

**Cuándo SÍ usar SDD:**
- Módulos nuevos (ej. Control de Calidad, Garantías).
- Refactors grandes (ej. el de variantes/atributos del catálogo de productos).
- Features que tocan más de 3 archivos o requieren cambios de BD.

**Cuándo NO usar SDD (ir directo al código):**
- Correcciones del profesor de una sola pantalla.
- Bugs aislados.
- Cambios de CSS, copy o textos.
- Renombres y limpieza de deuda técnica.

---

## Ciclo SDD

```
[Idea]
   │
   ├─ /sdd-proposal <slug>   → sdd/proposals/<slug>.brainstorm.md
   │                            (explora opciones, sin código)
   │
   ├─ /sdd-spec <slug>       → sdd/specs/<slug>.spec.md
   │                            (diseño detallado, contrato de código)
   │
   ├─ /sdd-task <spec>       → sdd/tasks/active/TASK-NNN-<slug>.md
   │                            (tareas atómicas, asignables)
   │
   └─ Ejecutor (humano o Claude Code) implementa la task
                              → mueve archivo a sdd/tasks/completed/
                              → PR a `enmanuel`
```

---

## Ramas Git

| Rama | Rol |
|---|---|
| `main` | Entregable al profesor. Solo merges desde `enmanuel`. |
| `enmanuel` | Rama de integración del equipo. Default para PRs. |
| `feat/<slug>` | Rama por feature/spec. Sale de `enmanuel`, vuelve a `enmanuel`. |
| `fix/<slug>` | Rama por bug o corrección puntual del profesor. |

**Regla:** ninguna rama de trabajo sale directamente de `main`. Toda PR cierra contra `enmanuel`. Cuando el profesor revise, se mergea `enmanuel → main`.

---

## Fase 0 — Proposal / Brainstorm (opcional)

Útil cuando la idea aún no está clara. Se exploran opciones A/B/C, se elige una, y eso alimenta el spec.

```
/sdd-proposal control-calidad
```
Genera `sdd/proposals/control-calidad.brainstorm.md` con secciones: problema, opciones, recomendación, impacto.

---

## Fase 1 — Spec

Cuando ya sabemos qué construir:

```
/sdd-spec control-calidad
```

Crea `sdd/specs/control-calidad.spec.md` con:
1. **Motivación** y objetivos
2. **Diseño arquitectónico** (controladores, vistas, modelos)
3. **Módulos** (un módulo = una task futura)
4. **Test/QA Specification** (cómo se prueba manualmente)
5. **Criterios de aceptación**
6. **Codebase Contract** ← *crítico, anti-alucinación*
7. **Notas de implementación**
8. **Preguntas abiertas**

El spec se versiona en `sdd/specs/`. Se hace commit antes de generar tasks.

### Codebase Contract — la sección más importante

Lista los **imports, clases, rutas y métodos del proyecto que se van a usar**, verificados con `grep`/`read`. También lista lo que **NO existe** (anti-alucinación). Esto previene que un dev junior o Claude Code invente código.

Ejemplo:
```
### Existen — verificados
- app/Http/Controllers/Admin/PedidoController.php:42 → método store(Request $request): RedirectResponse
- resources/views/admin/pedidos/index.blade.php → vista listado
- modelo App\Models\Pedido (HasFactory, SoftDeletes en migración 2024_03_...)

### No existen — no referenciar
- ~~App\Services\PedidoCalidadService~~ — no existe aún (se crea en esta feature)
- ~~ruta admin.pedidos.calidad~~ — no registrada en routes/web.php
```

Para SGPMRJA, los **contratos pueden enlazar a las convenciones del repo** en vez de duplicar contenido:
- Sistema modal → `docs/conventions/modal-system.md`
- Validaciones → `docs/conventions/js-validations.md`
- DataTable estándar → `AGENTS.md` § Estándares visuales
- Patrón búsqueda de persona → `docs/conventions/persona-unified-search.md`
- Índice completo → `docs/conventions/README.md`

---

## Fase 2 — Tasks

```
/sdd-task sdd/specs/control-calidad.spec.md
```

Descompone el spec en tasks atómicas en `sdd/tasks/active/`. Cada task:
- Es **independiente** (se puede implementar sola).
- Toma **1–4 horas** de trabajo.
- Tiene **criterios de aceptación** y **QA manual**.
- Declara `Depends-on` si requiere que otra task termine primero.

Convención de nombres: `TASK-NNN-<slug>.md` con NNN incremental global.

Asignación: en el encabezado del archivo, `Assigned-to: emmanuel | vanessa | santiago | unassigned`.

---

## Fase 3 — Ejecución

Cualquier miembro del equipo (o Claude Code) puede tomar una task. Pasos:

1. Verificar que `Depends-on` esté en `tasks/completed/`.
2. Actualizar el encabezado: `Status: in-progress`, `Assigned-to: <tu-nombre>`.
3. Crear rama: `git checkout -b feat/TASK-NNN-<slug>` (sale de `enmanuel`).
4. Implementar **solo el scope** de la task. Si descubres trabajo extra, anótalo como nueva task.
5. Cumplir criterios de aceptación y QA manual.
6. Mover el archivo a `sdd/tasks/completed/` y rellenar la **Nota de Completitud**.
7. PR contra `enmanuel`. En el PR enlazar la task: `Cierra sdd/tasks/completed/TASK-NNN-<slug>.md`.
8. Revisar entre miembros del equipo antes de mergear.

---

## Fase 4 — Validación

Antes de cerrar la feature completa (todas las tasks de un spec):

- Pasar el spec a `Status: approved` → cambiar a `Status: shipped` con fecha.
- Crear o actualizar doc en `docs/conventions/` cuando aplique (patrones nuevos, decisiones no obvias).
- Si la feature ya quedó cerrada y es de tamaño relevante, opcionalmente añadir snapshot a `docs/history/`.

---

## Reglas de calidad para ejecutores

1. **Respetar el scope** — si encuentras algo fuera, anótalo como nueva task; no lo metas en este PR.
2. **Seguir patrones existentes** — revisar `docs/conventions/` y el código antes de inventar abstracciones.
3. **Codebase Contract es ley** — no inventes imports, rutas o métodos. Si necesitas algo no listado, verifícalo y actualiza el contrato antes de codificar.
4. **Commits pequeños** — una task = uno o pocos commits coherentes.
5. **PR descriptivo** — enlazar task, resumir cambios, listar QA pasado.

---

## Comandos disponibles

| Comando | Propósito |
|---|---|
| `/sdd-spec <slug>` | Crear spec formal (Fase 1). |
| `/sdd-task <spec>` | Descomponer spec en tasks (Fase 2). |
| `/sdd-status` | Resumir estado de tasks (pendientes / en curso / completas). |
| `/sdd-next` | Sugerir próximas tasks desbloqueadas para tomar. |

Los comandos están definidos en **dos lugares** para soportar ambas herramientas del equipo:
- **Claude Code** (Emmanuel): `.claude/commands/sdd-*.md`
- **Antigravity** (Vanessa, Santiago): `.agent/workflows/sdd-*.md`

Las definiciones son funcionalmente idénticas — al editar una, mantener la otra sincronizada.

---

## Estructura de carpetas

```
sdd/
├── WORKFLOW.md          ← este archivo
├── templates/
│   ├── brainstorm.md    ← plantilla Fase 0
│   ├── proposal.md      ← plantilla proposal corta
│   ├── spec.md          ← plantilla Fase 1
│   └── task.md          ← plantilla Fase 2
├── proposals/           ← brainstorms y proposals activos
├── specs/               ← specs aprobados / en review
└── tasks/
    ├── active/          ← tasks pendientes o en curso
    └── completed/       ← tasks terminadas (auditoría histórica)
```

---

## Convenciones de IDs

- **FEAT-NNN**: identificador único de feature/spec. Incremental global, NNN = 001, 002, ...
- **TASK-NNN**: identificador único de task. Incremental global, NNN = 001, 002, ...
- Antes de asignar un ID nuevo, revisar el máximo existente en `sdd/specs/` o `sdd/tasks/`.
