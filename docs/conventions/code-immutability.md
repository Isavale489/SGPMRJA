# Códigos del SKU son Inmutables

> Una vez asignado un código a una entidad que aparece dentro del SKU del producto, **no se puede modificar**. Protege los SKUs históricos contra renombres del catálogo.

## Columnas afectadas

Las siguientes columnas son **readonly** después de la primera asignación:
- `insumo.codigo` (forma parte del SKU vía `CAM-OXF-L-MAO-001`)
- `atributo.codigo` (no participa directamente pero referencia a sus valores)
- `atributo_valor.codigo` (forma parte del SKU)
- `tipo_producto.prefijo` (forma parte del SKU)

## Por qué

Si renombras "Oxford" a "Premium" después de crear 50 productos con `CAM-OXF-...-001`, los SKUs viejos quedan inconsistentes con el catálogo vivo. Etiquetas físicas, registros impresos, cotizaciones antiguas y contabilidad referencian SKUs que dejarían de mapear al catálogo. La inmutabilidad es la defensa.

## Cómo se implementa

### Frontend
Si el código ya está asignado (no vacío), el campo aparece `readonly` al editar.

### Backend
El controller respeta la inmutabilidad aunque el HTML sea manipulado:

```php
if (empty($entidad->codigo) && $request->filled('codigo')) {
    $data['codigo'] = strtoupper(trim($request->codigo));
}
// Si ya tiene código, $data['codigo'] no se setea → no se sobreescribe
```

### Validación
- Regex: `/^[A-Z0-9]+$/`
- Longitud: 2 – 8 caracteres
- Único en la tabla

### Excepción válida
Si el código es `NULL` (entidad pre-refactor sin código asignado), permitir asignarlo **una vez**. A partir de esa primera asignación, queda fijo.

## Si el profesor o usuario pide cambiar un código existente

1. **Explicarle el riesgo** — rompe SKUs históricos.
2. Si insiste, la solución correcta es:
   - Crear una nueva entidad con el código nuevo.
   - Marcar la vieja como inactiva (SoftDelete o `estado=false`).
   - Editar productos uno por uno para apuntar a la nueva (regenera SKU).
   - Documentos históricos (cotizaciones/pedidos) leen del snapshot inmutable, no se afectan.

**NO permitir cambio directo del código en una entidad ya en uso.**

## Snapshots inmutables (relacionado)

Detalles de cotización y pedido guardan `tela_snapshot` y `atributos_snapshot` para que los documentos viejos rendericen exactamente lo que el cliente firmó, aun si el catálogo cambia. Ver [`product-variants.md`](product-variants.md) para detalle.
