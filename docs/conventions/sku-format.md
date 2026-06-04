# Formato y Generación del SKU

> Cómo se construyen los códigos de Producto (`producto.codigo`) tras el refactor de variantes. Si en el futuro hay que generar códigos similares para otros catálogos, usar este patrón.

## Fórmula

```
PREFIJO_TIPO - CODIGO_TELA - VALORES_ATRIBUTOS - SECUENCIAL
```

Ejemplos vivos:
- `CAM-OXF-L-MAO-001` → Camisa Oxford Manga Larga Cuello Mao (primera)
- `FRN-AJR-C-V-001` → Franela Algodón Jersey Manga Corta Cuello V
- `PNT-GBD-STR-001` → Pantalón Gabardina Stretch (sin segundo atributo)

## Implementación canónica

`app/Services/ProductoService.php::generarCodigo(TipoProducto $tipo, ?Insumo $tela, array $valoresOrdenados): string`

### Reglas

1. **Orden de atributos**: dictado por `tipo_producto_atributo.orden` (definido por el admin al asociar atributos al tipo). Usar `ordenarValoresParaTipo()` antes de generar.
2. **`withTrashed()`** al contar productos existentes para no colisionar con SoftDeletes — ver [`softdeletes-unique.md`](softdeletes-unique.md).
3. **Loop defensivo** hasta 100 intentos para tolerar race conditions:
   ```php
   do {
       $count = Producto::withTrashed()->where('codigo', 'LIKE', $base.'-%')->count();
       $candidato = $base.'-'.str_pad($count + 1 + $intentos, 3, '0', STR_PAD_LEFT);
       $intentos++;
   } while ($intentos < 100 && Producto::withTrashed()->where('codigo', $candidato)->exists());
   ```
4. **Códigos cortos inmutables** una vez creados — ver [`code-immutability.md`](code-immutability.md).

## Endpoints relacionados

- `GET /productos-preview-codigo` → devuelve el próximo SKU para una combinación tipo + tela + valores (usado por el form de productos para mostrar el SKU en vivo antes de guardar).
- `GET /productos-sugerir-precio` → devuelve `tela.costo_unitario + tipo.precio_confeccion`.
- `GET /productos-resolver-variante` → resuelve una combinación tipo+tela+valores. Si existe un
  Producto, devuelve `dynamic:false` con su `id` (legacy). Si no, **calcula la variante**
  (`dynamic:true`) con SKU + precio + snapshots **sin crear un Producto** (FEAT-003). Usado por el
  wizard de cotizaciones/pedidos.

## SKU en variantes dinámicas (FEAT-003)

Tras FEAT-003 la mayoría de las ventas NO crean una fila `producto` (el catálogo es el Tipo). El SKU:

- Se **calcula** con el mismo `generarCodigo()` desde tipo + tela + valores (`buildSnapshotsDesdeTipo`).
- Se **congela** en `detalle_cotizacion.sku_snapshot` / `detalle_pedido.sku_snapshot` al crear la
  línea, para trazabilidad estable en PDFs/órdenes aunque cambien los códigos del catálogo.
- Sigue siendo **recomputable** desde tipo+tela+atributos (todo guardado en la línea).
- El secuencial `NNN` se cuenta sobre la tabla `producto`; en variantes dinámicas (sin filas nuevas)
  suele quedar en `-001`. Es un SKU **referencial** de la línea, no un id único de catálogo.

## Cómo aplicar el patrón a futuros catálogos

Si en el futuro hay que generar códigos para otra entidad (lotes, órdenes con prefijo, etc.):

- **Reusar este patrón** en lugar de inventar uno nuevo.
- Definir el orden de los segmentos como configuración (no hardcoded).
- Usar `withTrashed()` siempre que la columna tenga `UNIQUE` y el modelo `SoftDeletes`.
- Si hay variantes, exponer un endpoint de "preview" (como `productos-preview-codigo`) para que el form muestre el SKU en vivo antes de guardar.
- Implementar el loop defensivo — las races no son hipotéticas, pasan.
