# Nomenclatura de Columnas — Sin Redundancia

> Regla del proyecto: nombres de columnas **lo más simples posible**. La tabla ya da el contexto.

## La regla

- ✅ `codigo` (no `codigo_corto`)
- ✅ `prefijo` (no `codigo_prefijo`)
- ✅ `descripcion` (no `descripcion_larga`)

**Por qué**: queremos un Diagrama Entidad-Relación limpio y profesional. Los prefijos/sufijos redundantes (`_corto`, `codigo_`, etc.) repiten contexto que la tabla ya da. La columna `codigo` de la tabla `insumo` no necesita llamarse `codigo_insumo`.

## Cómo aplicar

### Toda columna nueva
Usar el nombre más simple posible. Si hay ambigüedad genuina, añadir una columna `descripcion` separada en vez de inflar el nombre.

### Columnas existentes con nombres redundantes
**NO renombrar en producción activa** — rompe código vivo. Esperar momento de limpieza programada con migración de rename específica (como se hizo con `tipo_producto.codigo_prefijo` → `tipo_producto.prefijo` en el refactor de variantes).

### Excepciones válidas
**Foreign keys** llevan sufijo `_id` por convención Laravel:
- `tipo_producto_id` ✅
- `insumo_tela_id` ✅
- `persona_id` ✅

Eso NO es redundancia — es la convención del framework y debe respetarse.

## Aplicado en el proyecto

Tras el refactor de variantes:
- `insumo.codigo` (no `codigo_corto`)
- `atributo.codigo`, `atributo_valor.codigo` (no `codigo_corto`)
- `tipo_producto.prefijo` (renombrado de `codigo_prefijo`)
- `producto.codigo` (siempre fue así — funciona como SKU)
