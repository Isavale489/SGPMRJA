# Documentación oficial de SGPMRJA

Este directorio (`docs/`) es la **fuente única de verdad** para documentación técnica y funcional del proyecto.

## Política de documentación

- **Canónico**: todo documento vigente debe vivir en `docs/`.
- **Legado**: `Documentacion/` se conserva solo como histórico y respaldo.
- **Formato oficial**: usar `Markdown (.md)` como formato editable principal.
- **HTML/PDF**: se consideran artefactos de publicación/exportación, no fuente maestra.

## Índice maestro

### Arquitectura y análisis
- `SGPMRJA_ANALISIS_TECNICO_2026-02-17.md`: diagnóstico técnico del sistema (rutas, modelos, BD, mejoras).
- `ARQUITECTURA_SISTEMA.md`: vista consolidada de arquitectura de aplicación y datos.

### Análisis funcional
- `CASOS_DE_USO.md`: casos de uso y actores del sistema.
- `REQUERIMIENTOS_FUNCIONALES.md`: requerimientos funcionales consolidados.
- `DICCIONARIO_DATOS.md`: diccionario de datos regenerable desde `database/schema/mysql-schema.sql` para evitar deriva documental.

### Estándares de UI
- `BUTTON_STANDARDS.md`: guía de botones.
- `BUTTON_STYLE_STANDARDS.md`: detalles de estilo de botones.

### Implementación
- `IMPLEMENTATION_SUMMARY.md`: resumen de cambios aplicados.
- `DOCUMENTATION_MIGRATION_MATRIX.md`: matriz para consolidar documentos heredados.

### Historial y auditoría
- `CHANGELOG_COTIZACIONES.md`: historial de cambios del módulo de cotizaciones.
- `AUDITORIA_SGPMRJA.md`: auditoría técnica integral del sistema.

### Convenciones técnicas vigentes (SDD)
- [`conventions/README.md`](conventions/README.md): índice de convenciones técnicas (modal-system, validaciones JS, sidebar-colors, sku-format, etc.) usadas por specs/tasks SDD.

### Bitácora histórica (SDD)
- [`history/README.md`](history/README.md): snapshots de sprints y refactors cerrados.

## Convención para nuevos documentos

Nombrado sugerido:

- `AREA_TEMA.md` para guías estables (ej: `ARQUITECTURA_DATOS.md`).
- `AREA_ANALISIS_YYYY-MM-DD.md` para diagnósticos fechados.
- `CHANGELOG_<MODULO>.md` para historial por módulo.

## Migración progresiva desde Documentacion/

Mientras se normaliza, cualquier documento útil en `Documentacion/` debe:

1. Copiarse o consolidarse en un `.md` canónico dentro de `docs/`.
2. Marcarse en `Documentacion/README.md` como **Legado** o **Migrado**.
3. Evitar crear nuevos `.html` como fuente de edición.
