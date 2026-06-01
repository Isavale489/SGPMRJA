# Convenciones del Proyecto SGPMRJA

> Reglas, patrones y arquitecturas a respetar en todo el código del proyecto. Esta carpeta es la **fuente de verdad** del equipo — cualquier dev o agente IA debe leer las convenciones relevantes antes de codificar.

## Cómo se usa

- Cada `.md` cubre un tema autocontenido.
- Los specs SDD (`sdd/specs/`) y tasks (`sdd/tasks/`) **enlazan** a estos docs en su sección "Codebase Contract" en vez de duplicar contenido.
- Si descubres una convención nueva durante un feature SDD que vale la pena compartir, añade un doc aquí en la misma PR.
- Si una convención cambia, actualizar el doc y mencionarlo en el commit.

## Índice de convenciones

### Visual y UI

| Doc | Cuándo leerlo |
|---|---|
| [`modal-system.md`](modal-system.md) | Cualquier modal nuevo o modificación de uno existente |
| [`nested-modals.md`](nested-modals.md) | Cuando vayas a abrir un modal dentro de otro |
| [`wizard-pattern.md`](wizard-pattern.md) | Formulario complejo multi-paso (stepper `.wiz-*`) |
| [`sidebar-colors.md`](sidebar-colors.md) | Añadir ítem al sidebar o nueva sección |
| [`ux-search-filters.md`](ux-search-filters.md) | Implementar búsqueda + filtros en una vista listado |

### Frontend / JS

| Doc | Cuándo leerlo |
|---|---|
| [`js-validations.md`](js-validations.md) | Cualquier formulario con validación cliente |

### Backend / BD

| Doc | Cuándo leerlo |
|---|---|
| [`db-architecture.md`](db-architecture.md) | Antes de crear migraciones o modificar esquema |
| [`business-flows.md`](business-flows.md) | Antes de diseñar transacciones nuevas |
| [`softdeletes-unique.md`](softdeletes-unique.md) | Generadores secuenciales en modelos con SoftDeletes |
| [`column-naming.md`](column-naming.md) | Toda columna nueva |

### Productos y SKU

| Doc | Cuándo leerlo |
|---|---|
| [`product-variants.md`](product-variants.md) | Tocar el catálogo de productos, variantes o atributos |
| [`sku-format.md`](sku-format.md) | Generar códigos de producto o cualquier código secuencial |
| [`code-immutability.md`](code-immutability.md) | Tocar `insumo.codigo`, `atributo.codigo`, `atributo_valor.codigo`, `tipo_producto.prefijo` |

### Patrones reusables

| Doc | Cuándo leerlo |
|---|---|
| [`persona-unified-search.md`](persona-unified-search.md) | Cualquier form que necesite seleccionar una persona del sistema |
| [`pdf-generation.md`](pdf-generation.md) | Generar PDFs |

### Auth y seguridad

| Doc | Cuándo leerlo |
|---|---|
| [`password-recovery.md`](password-recovery.md) | Tocar el flujo de auth o recuperación de contraseña |

### Lecciones aprendidas

| Doc | Cuándo leerlo |
|---|---|
| [`anti-patterns.md`](anti-patterns.md) | Antes de implementar fullscreen persistente u otros patrones similares |

### Deuda técnica conocida

| Doc | Cuándo leerlo |
|---|---|
| [`pedidos-css-debt.md`](pedidos-css-debt.md) | Si tomas el spec de limpieza CSS de Pedidos |

## Historia del proyecto

Para contexto histórico (sprints completados, refactors mergeados, decisiones cerradas), ver `docs/history/`. No es de lectura obligatoria — está como bitácora.

## Cómo añadir una convención nueva

1. Crear `docs/conventions/<tema-kebab-case>.md`.
2. Estructura sugerida:
   - **TL;DR** en una frase si la convención es corta.
   - **Por qué existe** (el razonamiento, no solo la regla).
   - **Cómo aplicar** (pasos concretos o ejemplos).
   - **Cuándo NO aplica** (excepciones legítimas).
3. Añadir el enlace en la tabla correspondiente de este README.
4. Si afecta a múltiples convenciones, cross-linkear con `[[nombre]]` o markdown links.
5. Commitear en la PR del feature que la introduce, o en una PR separada de "documentación de convenciones".
