---
type: feature
base_branch: enmanuel
---

# Feature Specification: Variantes dinámicas por tipo de producto

**Feature ID**: FEAT-003
**Fecha**: 2026-06-03
**Autor**: Emmanuel Arroyo
**Status**: draft
**Versión objetivo**: por definir

---

## 1. Motivación y requisitos de negocio

### Planteamiento del problema

Hoy cada combinación vendible (tipo + tela + atributos) **debe existir como una fila `producto`**.
El endpoint `productos-resolver-variante` exige un `Producto` que matchee la combinación exacta;
si no existe, responde *"No existe una variante con esa combinación. Crea primero el producto en /productos."*

Consecuencia: para ofrecer una "Franela" en 5 telas × 2 mangas × 5 cuellos haría falta registrar
decenas de filas `producto`, ensuciando la DataTable de Productos con combinaciones que en realidad
son **configurables al vuelo** en el momento de cotizar.

Cita del usuario (Emmanuel):
> "no quiero tener 10 registros de producto de tipo franela en mi datatable cuando esas variantes
> yo las debería poder configurar dinámicamente en el paso de productos de la cotización… franela
> manga larga con tela X cuello X, o franela manga corta con tela X, y así."

### Objetivos

- El **catálogo pasa a ser el Tipo de Producto**: una sola entrada "Franela" en la DataTable.
- Las **telas válidas se definen por tipo** (nueva relación `tipo_producto_tela`), gestionadas desde el editor del Tipo — sin crear filas `producto`.
- En el paso de productos de la cotización, la variante (tela + atributos) se **configura dinámicamente**; el precio se calcula en vivo (`tipo.precio_confeccion + tela.costo_unitario`).
- La línea de cotización/pedido se **autodescribe por snapshots** (`tela_snapshot`, `atributos_snapshot`) sin requerir `producto_id`.
- **Órdenes de Producción** fabrica desde tipo + tela + atributos (snapshot), no desde `producto_id`.
- Deprecar el `Producto`-por-combinación como unidad de catálogo.

### Fuera de alcance (No-Goals)

- **NO** se crea una tabla `tela` separada — la tela sigue siendo `Insumo::where('tipo','Tela')` (ver `docs/conventions/product-variants.md` §1).
- **NO** se toca la regla de stock: cotización/pedido **no** descuenta inventario; solo producción lo hace (ver `product-variants.md` §2).
- **NO** se rompe la inmutabilidad de snapshots: una vez creado el detalle, sus snapshots no cambian (ver `product-variants.md` §3).
- **NO** se eliminan los productos legacy existentes (9 filas) — se mantienen funcionando durante la transición.
- **NO** se agrega de vuelta `producto.modelo`.

---

## 2. Diseño arquitectónico

### Resumen

Se invierte la unidad de catálogo: de `Producto` (combinación concreta) a `TipoProducto`
(plantilla con telas + atributos permitidos + precio de confección). La combinación concreta
deja de persistirse como fila `producto`; se resuelve y valora **en vivo** y se congela como
**snapshot** en la línea del documento (cotización/pedido). Producción consume el snapshot.

El modelo ya soporta el 70%: existen `tela_snapshot`/`atributos_snapshot` en los detalles,
`ProductoService::sugerirPrecio()` y los atributos por tipo (`tipo_producto_atributo`). Faltan:
telas por tipo, resolución sin Producto, `producto_id` nullable + `tipo_producto_id` en la línea,
y la adaptación de Órdenes de Producción.

### Diagrama de componentes

```
Tipo de Producto (catálogo)
  ├── tipo_producto_atributo  → atributos permitidos (manga, cuello)   [EXISTE]
  ├── tipo_producto_tela      → telas permitidas                       [NUEVO]
  └── precio_confeccion                                                [EXISTE]
            │
            ▼
Cotización · paso productos (Blade/JS)
  ├── selector de variante: telas del tipo + atributos del tipo        [MODIFICA]
  ├── precio en vivo: sugerirPrecio(tipo, tela)                        [EXISTE]
  └── resolverVariante: calcula (no exige Producto)                    [MODIFICA]
            │
            ▼
detalle_cotizacion / detalle_pedido
  ├── tipo_producto_id        [NUEVO]   ├── producto_id (nullable)     [MODIFICA]
  ├── tela_snapshot (JSON)    [EXISTE]  └── atributos_snapshot (JSON)  [EXISTE]
            │
            ▼
Orden de Producción
  └── fabrica desde tipo+tela+atributos del snapshot                   [MODIFICA, punto crítico]
```

### Puntos de integración

| Componente existente | Tipo | Notas |
|---|---|---|
| `App\Models\TipoProducto` | extiende | nueva relación `telas(): BelongsToMany` vía `tipo_producto_tela` |
| `App\Http\Controllers\ProductoController::resolverVariante` | modifica | calcula precio/SKU en vivo sin exigir `Producto` |
| `App\Http\Controllers\CotizacionController` (store/update) | modifica | `producto_id` deja de ser `required`; persistir `tipo_producto_id` + snapshots construidos desde tipo+tela+valores |
| `App\Http\Controllers\PedidoController` (store/update) | modifica | idem cotización |
| `App\Services\ProductoService` | extiende | variante de `buildSnapshotsParaDetalle()` que arma snapshots desde tipo+tela+valores (sin `Producto`) |
| `App\Models\DetalleCotizacion` / `DetallePedido` | extiende | fillable `tipo_producto_id`; `producto_id` nullable |
| `App\Models\OrdenProduccion` | modifica | resolver qué fabricar desde la línea (snapshot) en vez de `producto.*` |
| `resources/views/admin/cotizaciones/scripts/main.blade.php` | modifica | `telasDelTipo()` lee de `tipo_producto_tela`; selector no depende de productos existentes |
| `resources/views/admin/productos/index.blade.php` | modifica | DataTable muestra **tipos**; editor de Tipo gana multi-select de telas |
| `routes/web.php` | añade | endpoints para telas-por-tipo |

### Modelos de datos

```php
// NUEVA: telas permitidas por tipo de producto
Schema::create('tipo_producto_tela', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('tipo_producto_id');
    $table->unsignedBigInteger('insumo_id'); // insumo con tipo='Tela'
    $table->timestamps();

    $table->foreign('tipo_producto_id')->references('id')->on('tipo_producto')->cascadeOnDelete();
    $table->foreign('insumo_id')->references('id')->on('insumo')->cascadeOnDelete();
    $table->unique(['tipo_producto_id', 'insumo_id'], 'tipo_producto_tela_unique');
});

// MODIFICA: la línea se autodescribe; producto_id deja de ser obligatorio
Schema::table('detalle_cotizacion', function (Blueprint $table) {
    $table->unsignedBigInteger('producto_id')->nullable()->change();
    $table->unsignedBigInteger('tipo_producto_id')->nullable()->after('producto_id');
    $table->foreign('tipo_producto_id')->references('id')->on('tipo_producto')->nullOnDelete();
});
// idem detalle_pedido

// REVISAR (pregunta abierta): orden_produccion.producto_id → nullable + tipo_producto_id,
// o la orden se deriva de la línea de pedido.
```

> **Decisión pendiente (ver §8):** cómo identifica la Orden de Producción qué fabricar.
> Opción 1: `orden_produccion` apunta a `detalle_pedido_id` y lee su snapshot.
> Opción 2: `orden_produccion.producto_id` nullable + `tipo_producto_id` + snapshots propios.

### Rutas nuevas

| Verbo | URI | Acción | Nombre |
|---|---|---|---|
| GET | /tipos-producto/{tipo}/telas | telas asignadas (JSON) | tipos-producto.telas.index |
| PUT | /tipos-producto/{tipo}/telas | sincronizar telas del tipo | tipos-producto.telas.sync |

> `productos-resolver-variante` y `productos-sugerir-precio` ya existen; se **modifican**, no se agregan.

### UI / Vistas

- DataTable de Productos: pasa a listar **Tipos de Producto** (agrupación), card `card-maestros`.
- Editor de Tipo de Producto: multi-select de telas permitidas (Select2 o checklist) + atributos (ya existe).
- Selector de variante en cotización: chips de tela desde `tipo_producto_tela`; sin "Crea primero el producto".
- Modal: `atlantico-modal` (maestros) en editor de tipo — ver `docs/conventions/modal-system.md`.

---

## 3. Desglose por módulos

### Módulo 1: Relación telas-por-tipo (datos)
- **Path**: `database/migrations/<fecha>_create_tipo_producto_tela_table.php`, `app/Models/TipoProducto.php`
- **Responsabilidad**: pivot `tipo_producto_tela` + relación `TipoProducto::telas()`
- **Depende de**: `Insumo` (scope `telas()`)

### Módulo 2: Gestión de telas desde el editor de Tipo
- **Path**: `app/Http/Controllers/TipoProductoController.php`, `resources/views/admin/productos/index.blade.php` (modal Tipos)
- **Responsabilidad**: asignar/desasignar telas a un tipo (endpoints `telas.index`/`telas.sync` + UI)
- **Depende de**: Módulo 1

### Módulo 3: Resolución/valoración dinámica de variante
- **Path**: `app/Http/Controllers/ProductoController::resolverVariante`, `app/Services/ProductoService`
- **Responsabilidad**: dada (tipo, tela, valores), devolver SKU calculado + precio (`sugerirPrecio`) + snapshots, **sin exigir `Producto`**
- **Depende de**: Módulo 1; `ProductoService::generarCodigo/sugerirPrecio/buildAtributosSnapshot`

### Módulo 4: Persistencia de línea sin producto_id
- **Path**: migraciones `detalle_cotizacion`/`detalle_pedido`, modelos `DetalleCotizacion`/`DetallePedido`, `CotizacionController`, `PedidoController`
- **Responsabilidad**: `producto_id` nullable, `tipo_producto_id` nuevo; validación deja de exigir `producto_id`; guardar snapshots construidos desde tipo+tela+valores
- **Depende de**: Módulo 3

### Módulo 5: Órdenes de Producción desde snapshot (punto crítico)
- **Path**: `app/Models/OrdenProduccion.php`, `app/Http/Controllers/OrdenProduccionController.php`, posible migración
- **Responsabilidad**: identificar qué fabricar desde tipo+tela+atributos de la línea, no desde `producto_id`
- **Depende de**: Módulo 4 + decisión §8

### Módulo 6: DataTable de Productos = Tipos
- **Path**: `resources/views/admin/productos/index.blade.php`, `ProductoController`
- **Responsabilidad**: el listado principal muestra tipos (no combinaciones); los productos legacy quedan accesibles pero no se generan nuevos por combinación
- **Depende de**: Módulos 1–4

### Módulo 7: Documentación de convenciones
- **Path**: `docs/conventions/product-variants.md`, `docs/conventions/sku-format.md`
- **Responsabilidad**: actualizar la convención (el catálogo ahora es el Tipo; SKU es calculado/no persistido por combinación)
- **Depende de**: Módulos 1–6

---

## 4. Test / QA Specification

### QA manual (golden path)
1. Editor de Tipo "Franela" → asignar telas {Jersey, Algodón} → guardar.
2. Cotización → paso productos → "Franela" → el selector ofrece Jersey y Algodón (sin que existan productos por combinación).
3. Elegir Algodón + manga corta + cuello redondo → precio se calcula en vivo → Configurar → agregar al carrito.
4. Guardar cotización → la línea persiste `tipo_producto_id` + snapshots, `producto_id` NULL.
5. Convertir a pedido → snapshots intactos.
6. Generar Orden de Producción desde ese pedido → fabrica la combinación correcta (tela/atributos) leyendo el snapshot.
7. DataTable de Productos: "Franela" aparece **una sola vez**.

### Edge cases a verificar
- Tipo con `requiere_tela=true` sin telas asignadas → el selector debe avisar, no permitir resolver.
- Cotización/pedido **legacy** (con `producto_id` y snapshots viejos) sigue mostrándose y editándose.
- PDF de cotización/pedido lee del snapshot (no del catálogo vivo) — sin regresiones.
- Precio en vivo coincide con `tipo.precio_confeccion + tela.costo_unitario`.
- Inmutabilidad: editar otra línea no altera snapshots ya guardados.

### Dark mode
- Editor de Tipo (multi-select telas), selector de variante y DataTable de tipos en modo oscuro.

---

## 5. Criterios de aceptación

- [ ] Migración corre limpia en BD fresca: `php artisan migrate:fresh --seed`
- [ ] Se puede asignar/quitar telas a un tipo desde su editor; persiste en `tipo_producto_tela`
- [ ] El selector de variante en cotización ofrece las telas del tipo **sin** requerir filas `producto`
- [ ] `resolverVariante` devuelve SKU + precio calculados sin exigir `Producto`
- [ ] Cotización y pedido guardan línea con `tipo_producto_id` + snapshots y `producto_id` NULL
- [ ] Orden de Producción fabrica la combinación correcta desde el snapshot (QA §4 paso 6)
- [ ] Cotizaciones/pedidos **legacy** siguen funcionando (sin backfill urgente)
- [ ] Cotización/pedido **no** descuenta stock (regla intacta)
- [ ] Snapshots inmutables tras crear el detalle
- [ ] DataTable de Productos muestra tipos (una "Franela")
- [ ] `docs/conventions/product-variants.md` actualizado al nuevo modelo
- [ ] PR mergeada a `enmanuel`

---

## 6. Codebase Contract

> **CRÍTICO — anclaje anti-alucinación.** Verificado por `read`/`grep` el 2026-06-03.

### Imports verificados
```php
use App\Models\Producto;            // app/Models/Producto.php:1
use App\Models\TipoProducto;        // app/Models/TipoProducto.php:1
use App\Models\Insumo;              // app/Models/Insumo.php (scope telas())
use App\Models\DetalleCotizacion;   // app/Models/DetalleCotizacion.php:1
use App\Models\DetallePedido;       // app/Models/DetallePedido.php:1
use App\Models\OrdenProduccion;     // app/Models/OrdenProduccion.php:1
use App\Services\ProductoService;   // app/Services/ProductoService.php:1
```

### Firmas existentes a usar
```php
// app/Models/Producto.php:15  — fillable: tipo_producto_id, insumo_tela_id (+ atributos, precio_base, codigo, imagen, estado)
//   tipoProducto(): BelongsTo (:40) · tela(): BelongsTo(Insumo,'insumo_tela_id') (:45)
//   atributoValores(): BelongsToMany (:50) · ordenesProduccion(): HasMany (:87)
//   getNombreCompletoAttribute(): string (:61)

// app/Models/TipoProducto.php:15 — fillable: nombre, prefijo, descripcion, precio_confeccion, requiere_tela, consumo_tela_por_unidad
//   productos(): HasMany (:30) · atributos(): BelongsToMany 'tipo_producto_atributo' (:39)
//   insumosDefault(): BelongsToMany 'tipo_producto_insumo' (cantidad_estimada) (:52)
//   casts: requiere_tela=>boolean, consumo_tela_por_unidad=>decimal:2, precio_confeccion=>decimal:2

// app/Services/ProductoService.php
//   generarCodigo(TipoProducto $tipo, ?Insumo $tela, array $valoresOrdenados): string   (:28)
//   sugerirPrecio(TipoProducto $tipo, ?Insumo $tela): float                              (:62)
//   ordenarValoresParaTipo(TipoProducto $tipo, array $valoresIds)                        (:77)
//   buildAtributosSnapshot($valoresOrdenados): array                                     (:100)
//   previsualizarCodigo(TipoProducto $tipo, ?Insumo $tela, array $valoresIds): string    (:194)
//   buildSnapshotsParaDetalle(Producto $producto): array                                 (:207)  ← hoy requiere Producto

// app/Http/Controllers/ProductoController.php
//   resolverVariante(Request): JSON — hoy busca Producto match y si no, found=false
//   sugerirPrecio(Request): JSON — ya calcula precio_sugerido vía ProductoService
//   index(): $telasDisponibles = Insumo::telas() (:22)

// app/Models/DetalleCotizacion.php:16 — fillable: cotizacion_id, producto_id, tela_snapshot, atributos_snapshot, ...
//   casts: tela_snapshot=>array, atributos_snapshot=>array
//   columnas BD: id, cotizacion_id, producto_id, tela_snapshot, atributos_snapshot, cantidad,
//                descripcion, lleva_bordado, color_id, talla_id, precio_unitario, timestamps

// app/Models/OrdenProduccion.php:18 — fillable incluye producto_id; producto(): BelongsTo(Producto) (:40)

// routes/web.php
//   :195 GET productos-sugerir-precio   → ProductoController@sugerirPrecio
//   :197 GET productos-resolver-variante → ProductoController@resolverVariante

// app/Http/Controllers/CotizacionController.php
//   :166 y :272 validación 'productos.*.producto_id' => 'required|exists:producto,id'  ← cambiar a nullable
```

### JS relevante (cotización)
```
resources/views/admin/cotizaciones/scripts/main.blade.php
  :3896 telasDelTipo(productos) — hoy deriva telas de productos existentes  ← cambiar a telas del tipo
  :3974 $.getJSON(productos.resolver-variante) — resuelve combinación
  vsState (selector de variante), renderColorGrid/cfgState (configurador)
```

### Convenciones a respetar (ver `docs/conventions/`)
- `docs/conventions/product-variants.md` — tela=insumo, snapshots inmutables, cotización no toca stock (**este doc se ACTUALIZA en esta feature**)
- `docs/conventions/sku-format.md` — fórmula del SKU (ahora calculado, no persistido por combinación)
- `docs/conventions/db-architecture.md` — convenciones de migraciones/FK
- `docs/conventions/softdeletes-unique.md` — generadores secuenciales con `withTrashed()`
- `docs/conventions/code-immutability.md` — códigos readonly que forman el SKU
- `docs/conventions/business-flows.md` — flujo Cotización→Pedido→Orden

### NO existe — no referenciar
- ~~tabla `tela`~~ — la tela es `Insumo` con `tipo='Tela'` (NO crear tabla)
- ~~relación `tipo_producto_tela`~~ — se crea en esta feature (Módulo 1)
- ~~`detalle_cotizacion.tipo_producto_id`~~ — se agrega en esta feature (Módulo 4)
- ~~`ProductoService::buildSnapshotsDesdeTipo(...)`~~ — se crea en esta feature (Módulo 3)
- ~~`producto.modelo`~~ — eliminado, NO reintroducir

---

## 7. Notas de implementación y restricciones

### Patrones a seguir
- Snapshots se llenan al **crear** el detalle y son inmutables (mismo patrón actual).
- Cotización/pedido **referencial**: jamás `MovimientoInsumo` ni decremento de stock.
- SKU calculado con `generarCodigo()` para mostrar; **no** se persiste una fila `producto` por combinación.
- Migraciones con FK + `nullOnDelete`/`cascadeOnDelete` según corresponda; índice único en el pivot.

### Riesgos conocidos
| Riesgo | Mitigación |
|---|---|
| Órdenes de Producción acopladas a `producto_id` | resolver decisión §8 antes de implementar Módulo 5; mantener compat legacy |
| Documentos legacy con `producto_id` no nulo | `producto_id` nullable (no se borra); lectura tolerante a ambos casos |
| Reportes/PDF que leen `producto->nombre_completo` | leer del snapshot cuando `producto_id` es NULL |
| Pérdida de trazabilidad del SKU | persistir el SKU calculado en el snapshot del detalle |

### Dependencias externas
| Paquete | Versión | Razón |
|---|---|---|
| — | — | sin dependencias nuevas |

---

## 8. Preguntas abiertas

- [ ] **(CRÍTICA) ¿Cómo identifica la Orden de Producción qué fabricar sin `producto_id`?**
      Opción 1: `orden_produccion` referencia `detalle_pedido_id` y lee su snapshot.
      Opción 2: `orden_produccion.producto_id` nullable + `tipo_producto_id` + snapshots propios.
      *Owner: Emmanuel*
- [ ] ¿El SKU calculado se persiste en el snapshot del detalle para trazabilidad/PDF? (recomendado: sí). *Owner: Emmanuel*
- [ ] ¿Se hace backfill de los 9 productos legacy hacia el nuevo modelo, o conviven indefinidamente? *Owner: Emmanuel*
- [ ] ¿La DataTable de Productos elimina del todo la vista de combinaciones o deja un "ver SKUs" secundario? *Owner: Emmanuel*
- [ ] ¿`insumosDefault` (`tipo_producto_insumo`, BOM de la orden) debe incluir la tela elegida automáticamente al producir? *Owner: Emmanuel*

---

## Historial de revisiones

| Versión | Fecha | Autor | Cambio |
|---|---|---|---|
| 0.1 | 2026-06-03 | Emmanuel Arroyo | Borrador inicial (refactor completo — camino A) |
