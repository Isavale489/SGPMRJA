---
description: Descomponer un spec aprobado en tasks SDD atómicas
---

# /sdd-task — Descomponer un Spec en Tasks SDD

Descomponer una Especificación de Feature aprobada en tasks atómicas, independientes y asignables.

## Uso
```
/sdd-task sdd/specs/<feature-slug>.spec.md
```

## Reglas
- Solo descomponer specs con `Status: approved`.
- Cada task debe ser **independientemente implementable y verificable**.
- Revisar tasks existentes en `sdd/tasks/active/` y `sdd/tasks/completed/` para evitar duplicados.
- **NO escribir código** de implementación — las tasks son planes, no código.
- Debe correr en rama `enmanuel` (o la rama del feature antes de crear sub-ramas por task).
- **Commitear tasks y spec** a la rama antes de que los devs comiencen.

## Pasos

### 1. Verificar rama
Confirmar rama actual:
```bash
git branch --show-current
```
Si no es `enmanuel` o `feat/<slug-spec>`, avisar:
```
⚠️  /sdd-task debería correr en enmanuel o en la rama del feature.
   Rama actual: <rama>
   Cambia primero: git checkout enmanuel && git pull origin enmanuel
```

### 2. Leer el spec
Leer el archivo del spec provisto.
- Si `Status` no es `approved`, **advertir** y pedir confirmación para continuar.
- Extraer: Feature ID, título, secciones "Diseño arquitectónico", "Desglose por módulos", "Criterios de aceptación".

### 3. Planificar descomposición

Analizar el spec e identificar tasks atómicas:
- **Una task por módulo, clase, o entregable distinto** (ver sección 3 del spec).
- **Ordenar tasks por dependencia** — migración antes de modelo, modelo antes de controller, controller antes de vista.
- **Tamaño objetivo**: 1–4 horas por task.

**Análisis de paralelismo:**
- Identificar tasks que NO comparten archivos con otras.
- Esas pueden tomarse en paralelo por Vanessa, Santiago y tú.
- Tasks que extienden código de una task previa son secuenciales (default).
- Documentar en `parallelism_notes` la razón.

**CRÍTICO — Codebase Contract por task (Anti-Alucinación):**

Para CADA task, poblar su sección `## Codebase Contract`:

1. **Extraer del spec sección 6**: copiar imports verificados, firmas y entradas "NO existe" relevantes a ESTA task.
2. **Re-verificar**: `read` o `grep` cada archivo referenciado para confirmar que las firmas siguen vigentes. El código pudo cambiar entre spec y task.
3. **Añadir referencias específicas**: si la task toca archivos no cubiertos por el contrato del spec, leer esos archivos y añadir sus firmas.
4. **Scope preciso**: solo incluir imports/firmas que la task realmente necesita. Una task de migración no necesita firmas de vistas.
5. **Sección "NO existe"**: lista cosas que un implementador podría asumir existen pero no — esta es la medida anti-alucinación más fuerte.
6. **Enlazar convenciones**: si el patrón está documentado en `docs/conventions/`, enlazar en vez de duplicar.

**Quality bar**: una task sin Codebase Contract poblada está **incompleta**. El ejecutor (humano junior o Claude Code) **alucinará** si no le damos anclas explícitas verificadas.

### 4. Generar tasks

1. Verificar que `sdd/tasks/active/` existe.
2. Leer template `sdd/templates/task.md`.
3. Calcular siguiente TASK-NNN:
   - Listar tasks: `ls sdd/tasks/active/ sdd/tasks/completed/`
   - Buscar el `TASK-NNN` máximo
   - Incrementar; si no hay ninguno, empezar en `TASK-001`
4. Para cada task identificada, crear `sdd/tasks/active/TASK-<NNN>-<slug>.md` usando el template.

**Convenciones de campos:**
- `Feature`: ID del spec (ej. `FEAT-014 — control-calidad`)
- `Status`: `pending`
- `Priority`: `high` | `medium` | `low`
- `Esfuerzo estimado`: S | M | L | XL
- `Depends-on`: lista de TASK-IDs o `none`
- `Assigned-to`: `unassigned` inicialmente; los devs editan al tomarla

### 5. Commitear tasks

```bash
git add sdd/tasks/active/TASK-*.md
git commit -m "sdd: añadir <N> tasks para FEAT-<ID> — <feature-slug>"
```

### 6. Output

```
✅ <N> tasks generadas para FEAT-<ID> — <feature-slug>

   Tasks creadas:
   - TASK-<NNN> <título>  [Priority: high]  [Effort: M]  [Depends: none]
   - TASK-<NNN> <título>  [Priority: high]  [Effort: M]  [Depends: TASK-<X>]
   - TASK-<NNN> <título>  [Priority: medium] [Effort: S] [Depends: TASK-<X>]
   - ...

   Paralelizables (pueden tomarse en simultáneo):
   - TASK-<X>, TASK-<Y>

   Secuenciales (tras sus dependencias):
   - TASK-<Z>

Siguiente paso:
  1. Equipo revisa tasks en sdd/tasks/active/
  2. Cada dev se asigna una task editando el header `Assigned-to`
  3. /sdd-status para ver progreso
  4. /sdd-next para sugerencias de qué tomar
```

## Reglas para la descomposición

1. **Una task = un commit lógico** (idealmente 1 PR pequeña).
2. **Tests/QA por task** — cada task lleva su propia sección de QA manual.
3. **Migraciones antes de modelos** — siempre.
4. **Controllers antes de vistas** — pero la vista puede tomarse en paralelo si la firma del controller está clara en el spec.
5. **Sidebar y rutas al final** — para que el ítem nuevo no aparezca antes de tener pantalla funcional.
6. **NO** crear tasks de "limpieza" o "refactor" mezcladas con la feature — eso va en tasks separadas etiquetadas como tal.

## Referencia
- Template: `sdd/templates/task.md`
- Tasks activas: `sdd/tasks/active/`
- Tasks completadas: `sdd/tasks/completed/`
- Metodología: `sdd/WORKFLOW.md`
