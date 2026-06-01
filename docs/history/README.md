# Historia del Proyecto

> Bitácora histórica del proyecto: sprints, refactors, decisiones cerradas. **No es de lectura obligatoria** — está como referencia para entender el contexto detrás del estado actual del código.

Los archivos aquí son **snapshots en el tiempo** copiados desde la memoria personal del lead developer. Conservan su voz original (no fueron reescritos para audiencia de equipo). Si alguno contiene una convención que sigue vigente, esa convención debe vivir en `docs/conventions/` — no aquí.

## Índice

| Archivo | Fecha aprox. | Tema |
|---|---|---|
| [`sprint-document.md`](sprint-document.md) | 2026-03 | Sprint v3 — planificación inicial |
| [`empleados-catalogos.md`](empleados-catalogos.md) | 2026-04-21 | Refactor empleados: Departamento + Cargo como FKs |
| [`correcciones-profesor.md`](correcciones-profesor.md) | 2026-04-30 | Correcciones del profesor (sub-grupo RRHH, modal proveedores, búsqueda unificada) |
| [`merge-conflict-proveedores.md`](merge-conflict-proveedores.md) | 2026-05-04 | Resolución de conflicto en módulo Proveedores |
| [`cotizaciones-wizard-progress.md`](cotizaciones-wizard-progress.md) | 2026-05-03 | Progreso refactor a wizard de 3 pasos |
| [`cotizaciones-wizard-debt.md`](cotizaciones-wizard-debt.md) | 2026-05-03 | Deuda técnica del wizard |
| [`recovery-ui.md`](recovery-ui.md) | 2026-04-28 | Rediseño UI del flujo de recuperación de contraseña |
| [`deuda-columna-departamento-legacy.md`](deuda-columna-departamento-legacy.md) | 2026-05-07 | Footgun de la columna `empleado.departamento` (varchar legacy) |

## Cuándo consultar esta carpeta

- "¿Por qué hicieron X así en este módulo?"
- "¿Cuándo se decidió Y?"
- "¿Qué hubo en el sprint Z?"

Para reglas vigentes y convenciones activas, ir a [`docs/conventions/`](../conventions/).
