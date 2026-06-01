---
type: feature
base_branch: enmanuel
---

# Feature Specification: Wizard de Pedidos (Cliente · Productos · Pago · Resumen)

**Feature ID**: FEAT-002
**Fecha**: 2026-05-26
**Autor**: Emmanuel
**Status**: approved
**Assigned-to**: Santiago
**Versión objetivo**: Sprint actual

---

## 1. Motivación y requisitos de negocio

### Planteamiento del problema

La cotización fue refactorizada a un **wizard moderno de 3 pasos** (Cliente · Productos · Resumen) que elevó significativamente la UX de la operación inicial del flujo comercial. Sin embargo, **el pedido — que es la transacción que sigue a la cotización — todavía usa la UI antigua**: un modal único con toda la información mostrada de golpe, mezclando datos del cliente, productos, fechas, pagos y métodos de pago en una sola pantalla densa.

Esto rompe la consistencia UX del flujo operativo: el usuario crea/edita una cotización con la nueva interfaz, la convierte a pedido, y al completar el pedido se encuentra con la interfaz vieja. La incongruencia es especialmente visible cuando se hace clic en "Completar Pedido" desde el listado: el modal carga estados parciales (datos pre-llenados desde la cotización) sin guía visual de qué pasos faltan ni jerarquía clara de información.

### Objetivos

1. Convertir el modal de creación/edición de pedido en un wizard de **4 pasos**: Cliente · Productos · Pago · Resumen.
2. Reusar al máximo el patrón visual y los componentes (CSS, JS helpers) del wizard de cotizaciones para mantener consistencia.
3. Integrar el flujo "**Desde Cotización**" de forma elegante dentro del nuevo wizard (no como botón externo desconectado).
4. Soportar 3 modos del wizard: **crear nuevo**, **completar desde cotización** (datos pre-llenados, paso 1+2 ya cubiertos), **editar pedido existente**.
5. Mantener intactos los endpoints y la lógica de backend — el refactor es 100% frontend/Blade.

### Fuera de alcance (No-Goals)

- **NO** se modifica el modelo `Pedido` ni sus relaciones (`cliente`, `productos`, `pagos`, `cotizacion`).
- **NO** se altera el esquema de BD: la tabla `pedido`, `pago_pedido` y `detalle_pedido` se quedan como están.
- **NO** se cambia la lógica de `PedidoController::store/update` más allá de adaptarse al payload del wizard (que enviará los mismos campos).
- **NO** se reescribe `factura.blade.php` ni `reporte_pdf.blade.php` — son artefactos separados.
- **NO** se aborda la deuda CSS de Pedidos (138 `style=""` inline en `pedidos/index.blade.php` — ver memoria `project_pedidos_css_debt.md`); se hará en un spec aparte.
- **NO** se cambia el modal de "Seleccionar Cotización" (`pedidos/modals/seleccionar_cotizacion.blade.php`) — el wizard lo abre desde el paso 2 cuando aplica.
- **NO** se modifica el wizard de cotizaciones; solo se reusa.

---

## 2. Diseño arquitectónico

### Resumen

Replicar la estructura de archivos del módulo de cotizaciones (`index.blade.php` delgado + `modals.blade.php` con el wizard + `scripts/main.blade.php` con la lógica) y adaptarla a pedidos. Las clases CSS del wizard (`cot-step-*`, `cot-wizard-*`) se **generalizan a `wiz-*`** y se aplican a ambos módulos, evitando duplicación.

### Diagrama de componentes

```
resources/views/admin/pedidos/
├── index.blade.php                  ← refactor 2709 → ~200 líneas
├── modals.blade.php                 ← NUEVO: wizard + viewModal + showModal
├── scripts/
│   ├── main.blade.php               ← NUEVO: lógica del wizard
│   └── cotizacion_selection.blade.php  ← existente, se preserva
└── modals/
    └── seleccionar_cotizacion.blade.php ← existente, se preserva

public/assets/css/custom.css
└── sección "WIZARD UNIFICADO" (rename .cot-* → .wiz-*)

resources/views/admin/cotizaciones/
├── modals.blade.php                 ← cambiar .cot-* → .wiz-*
└── scripts/main.blade.php           ← cambiar selectores .cot-* → .wiz-*
```

### Estructura del wizard de pedidos

```
┌─────────────────────────────────────────────────────────────┐
│  Nuevo Pedido (o "Completar Pedido #NN", o "Editar Pedido") │
├─────────────────────────────────────────────────────────────┤
│  ① Cliente ── ② Productos ── ③ Pago ── ④ Resumen             │
│   ●━━━━━━━━━━━━━━○━━━━━━━━━━━━━○━━━━━━━━━━○                  │
├─────────────────────────────────────────────────────────────┤
│  [Contenido del paso activo]                                │
├─────────────────────────────────────────────────────────────┤
│  Paso 1 de 4                       [← Anterior]  [Continuar →] │
└─────────────────────────────────────────────────────────────┘
```

### Contenido de cada paso

#### Paso 1: Cliente y datos del pedido
- Buscador unificado de personas (mismo componente que cotizaciones — `personas-search` endpoint)
- Botón "Crear cliente nuevo" inline si no existe
- Fechas: emisión (auto, hoy) + entrega estimada
- Prioridad: chips Normal / Alta / Urgente (mismo patrón visual de cotización)
- **Modo "completar desde cotización"**: campos pre-llenados, marca visual "Datos heredados de la cotización #NN"

#### Paso 2: Productos
- Reusa el componente de productos del wizard de cotizaciones
- **Nuevo banner arriba**: "¿Importar productos desde una cotización?" → abre el modal `seleccionar_cotizacion` existente
- Al importar, los productos se cargan en el paso 2 y se muestra badge "Heredado de cotización #NN" por producto
- Soporta edición/eliminación individual antes de pasar al paso 3

#### Paso 3: Pago (NUEVO — no existe en cotización)
- Total del pedido: campo readonly (calculado del paso 2)
- Abono: input numérico
- Restante: campo readonly (calculado: total − abono)
- Validaciones: abono ≤ total, abono ≥ 0
- Métodos de pago: chips selectores **Efectivo / Transferencia / Pago Móvil** (hardcoded — viven en `App\Models\PagoPedido::METODOS` y como `enum` en la columna `pago_pedido.metodo`)
- Cuando se selecciona método ≠ Efectivo, se expande sección con campos condicionales:
  - **Transferencia**: `banco_id` (select cargado desde tabla `banco`), `referencia`
  - **Pago Móvil**: `banco_id` (select cargado desde tabla `banco`), `referencia`
  - Nota: cada pago va en su propia fila de `pago_pedido` (no se mezclan en columnas); el wizard puede registrar 1 o más pagos
- Validación cross-field: si abono > 0, método de pago obligatorio
- En **modo edit**: este paso es el principal — la mayoría de campos de pasos 1 y 2 están bloqueados, edit existe para registrar/ajustar pagos

#### Paso 4: Resumen
- Tarjeta resumen del cliente (read-only)
- Tabla compacta de productos con total
- Bloque de pago (abono, restante, método)
- Botón final "Guardar Pedido" / "Actualizar Pedido" / "Completar Pedido"

### Puntos de integración

| Componente existente | Tipo de integración | Notas |
|---|---|---|
| `cotizaciones/modals.blade.php` | rename clases | `.cot-*` → `.wiz-*` (compartido) |
| `cotizaciones/scripts/main.blade.php` | rename selectores | actualizar JS para `.wiz-*` |
| `custom.css` § "COT WIZARD" | rename + reubicar | sección "WIZARD UNIFICADO" |
| `pedidos/index.blade.php` | refactor mayor | extraer modales a `modals.blade.php` |
| `PedidoController::store/update` | revisar payload | el wizard envía los mismos campos JSON |
| `PedidoController::getCotizacionesDisponibles` | reusar | abierto desde el paso 2 |
| `personas-search` endpoint | reusar | paso 1 (autocomplete) |
| `cotizaciones.datosParaPedido` endpoint | reusar | hidrata paso 1+2 al importar |
| `modals/seleccionar_cotizacion.blade.php` | reusar | abierto desde el paso 2 |

### Modelo de datos

No hay cambios. Solo referencia para el implementador.

```php
// app/Models/Pedido.php
protected $fillable = [
    'cotizacion_id',   // null si no viene de cotización
    'cliente_id',
    'fecha_pedido',
    'fecha_entrega_estimada',
    'estado',
    'total',
    'abono',
    'prioridad',
    'user_id',
];

// Relaciones existentes
public function cliente()      { return $this->belongsTo(Cliente::class); }
public function cotizacion()   { return $this->belongsTo(Cotizacion::class); }
public function productos()    { return $this->hasMany(DetallePedido::class); }
public function pagos()        { return $this->hasMany(PagoPedido::class); }
```

```php
// app/Models/PagoPedido.php — métodos hardcoded
class PagoPedido extends Model {
    protected $table = 'pago_pedido';
    protected $fillable = ['pedido_id', 'metodo', 'monto', 'banco_id', 'referencia'];
    const METODOS = ['efectivo', 'transferencia', 'pago_movil'];
    public function banco(): BelongsTo  // tabla `banco`
}

// Schema: enum('metodo', ['efectivo','transferencia','pago_movil'])
//         banco_id nullable → tabla `banco`
//         referencia varchar(255) nullable
```

### Rutas

No hay rutas nuevas. Las existentes se reusan:

```php
// routes/web.php — todas existen ya
GET    pedidos                              pedidos.index
POST   pedidos                              pedidos.store
GET    pedidos/{id}/edit                    pedidos.edit
PUT    pedidos/{id}                         pedidos.update
GET    pedidos/cotizaciones-disponibles     pedidos.cotizacionesDisponibles
GET    cotizaciones/{id}/datos-para-pedido  cotizaciones.datosParaPedido
GET    personas-search                       (paso 1)
```

### UI / Vistas

- Modal raíz: clase `atlantico-modal atlantico-modal--op wiz-modal` (transaccional, gradiente cyan)
- Header del wizard: stepper horizontal con 4 puntos conectados por líneas progresivas
- Body: `<section>` por paso con `hidden` cuando no es el activo, animación de transición suave
- Footer: paginador "Paso X de 4" + botones Anterior/Continuar/Guardar
- Cards internas: `card border-0 shadow-sm` con header `bg-light-subtle`
- DataTable de productos (paso 2): clase `dt-transactional`
- Validaciones: patrón blur + `validarFormularioPedido*()` + wiring al submit (ver `memory/project_validaciones_js.md`)

---

## 3. Desglose por módulos

> Cada módulo se convertirá en una TASK independiente en Fase 2. Algunas tienen dependencias (módulo 1 debe ir primero).

### Módulo 1: Generalizar clases del wizard (cot → wiz)
- **Path**: `public/assets/css/custom.css` + `cotizaciones/modals.blade.php` + `cotizaciones/scripts/main.blade.php`
- **Responsabilidad**: renombrar todas las clases `.cot-step-*`, `.cot-wizard-*`, `.cot-step-content`, etc. a `.wiz-*`. Mover el bloque CSS a una sección renombrada "WIZARD UNIFICADO". JS de cotizaciones actualizado consecuentemente.
- **Depende de**: ninguno
- **Riesgo**: medio — toca código en producción de cotizaciones; debe verificarse que el wizard de cotizaciones sigue funcionando idéntico

### Módulo 2: Crear shell del wizard de pedidos
- **Path**: `resources/views/admin/pedidos/modals.blade.php` (NUEVO) + refactor de `pedidos/index.blade.php` (extraer modales)
- **Responsabilidad**: crear el archivo `modals.blade.php` con la estructura del wizard de 4 pasos (HTML + stepper). Modal raíz `wiz-modal` con header gradiente cyan ("Nuevo Pedido" / "Completar Pedido #NN" / "Editar Pedido"). Sin lógica JS aún — solo HTML.
- **Depende de**: Módulo 1 (necesita clases `.wiz-*` ya renombradas)
- **Riesgo**: bajo

### Módulo 3: Paso 1 (Cliente) funcional
- **Path**: `resources/views/admin/pedidos/scripts/main.blade.php` (NUEVO, secciones paso 1)
- **Responsabilidad**: portar el JS del paso 1 de cotizaciones (autocomplete persona-search, crear cliente inline, fechas, prioridad). Adaptar nombre de campos: `fecha_pedido` en lugar de `fecha_emision`, `fecha_entrega_estimada` en lugar de `fecha_validez`. El componente "Crear cliente nuevo" se reusa idéntico.
- **Depende de**: Módulo 2
- **Riesgo**: bajo — copia directa con renombre de campos

### Módulo 4: Paso 2 (Productos) funcional + "Importar desde cotización"
- **Path**: `pedidos/scripts/main.blade.php` (sección paso 2)
- **Responsabilidad**: portar la grilla de productos del wizard de cotizaciones (búsqueda, agregar, editar, eliminar, totales). Añadir banner "Importar desde cotización" que abre el modal `seleccionar_cotizacion.blade.php` existente y al confirmar hidrata `wiz-paso-2`. Productos importados muestran badge "Heredado de #NN".
- **Depende de**: Módulo 3 (validación de paso 1 antes de entrar a paso 2)
- **Riesgo**: medio — la integración con `seleccionar_cotizacion` requiere coordinar JS de dos archivos

### Módulo 5: Paso 3 (Pago) — NUEVO
- **Path**: `pedidos/scripts/main.blade.php` (sección paso 3) + `pedidos/modals.blade.php` (HTML paso 3)
- **Responsabilidad**: implementar el paso de pago — total readonly, abono input, restante calculado, métodos de pago chips, campos condicionales por método. Validaciones cross-field (abono ≤ total, método obligatorio si abono > 0).
- **Depende de**: Módulo 4 (necesita el total calculado del paso 2)
- **Riesgo**: medio — paso nuevo sin precedente, validaciones complejas

### Módulo 6: Paso 4 (Resumen) + submit final
- **Path**: `pedidos/scripts/main.blade.php` (sección paso 4)
- **Responsabilidad**: render del resumen consolidado (cliente + productos + pago). Botón final que recolecta `formData` de los 4 pasos y hace POST a `pedidos.store` (crear) o PUT a `pedidos.update` (editar). Toast de éxito + cierre del modal + `dataTable.ajax.reload()`.
- **Depende de**: Módulo 5
- **Riesgo**: medio — manejo del payload + edge cases (crear vs editar vs completar desde cotización)

### Módulo 7: Modo "completar desde cotización" en el wizard
- **Path**: `pedidos/scripts/main.blade.php` (handler `abrirWizardDesdeCotizacion(cotizacionId)`)
- **Responsabilidad**: cuando se entra al wizard desde el listado de cotizaciones con la acción "Convertir a pedido", se hidratan pasos 1 y 2 desde `cotizaciones.datosParaPedido` y se abre directamente en paso 3. Marca visual "Datos heredados de cotización #NN" en pasos 1 y 2 (banner amber claro).
- **Depende de**: Módulo 6
- **Riesgo**: bajo — endpoint ya existe, solo wiring

### Módulo 7b: Modo edit — campos protegidos (preservar comportamiento actual)
- **Path**: `pedidos/scripts/main.blade.php` (handler `abrirWizardEnEdit(pedidoId)`)
- **Responsabilidad**: replicar el comportamiento de read-only del modal actual. Al abrir un pedido existente:
  - **Paso 1 (Cliente)**: `cliente_id`, `cliente_nombre`, `cliente_email`, `cliente_telefono`, `ci_rif`, `ci_rif_prefix`, `fecha_pedido` → todos `readonly` + clase `campo-protegido` (bg gris, cursor not-allowed)
  - **Paso 1 — Editables**: `fecha_entrega_estimada`, `prioridad`, `estado` (este último solo visible en edit)
  - **Paso 2 (Productos)**: lista visible pero sin botones de agregar/editar/eliminar (read-only). Total readonly
  - **Paso 3 (Pago)**: 100% editable — es el motivo principal por el que existe el edit
  - **Paso 4 (Resumen)**: read-only consolidado
  - Submit envía PUT a `pedidos.update` con solo los campos editables (el backend ignora cliente/productos en update si vienen)
  - Se permite reabrir el wizard incluso si el pedido está "Completado" (no se bloquea por estado)
- **Depende de**: Módulo 6
- **Riesgo**: medio — la lógica de "qué se bloquea por paso" debe quedar centralizada y bien documentada para no perderse al evolucionar el wizard

### Módulo 8: Refactor `pedidos/index.blade.php`
- **Path**: `pedidos/index.blade.php`
- **Responsabilidad**: bajar de 2709 a ~200 líneas. Solo queda el wrapper del DataTable + breadcrumb + botones de acción. Modales y scripts viven en `modals.blade.php` y `scripts/main.blade.php`, incluidos vía `@include`.
- **Depende de**: Módulos 2–7 completos (el archivo nuevo debe funcionar antes de desmontar el viejo)
- **Riesgo**: alto — es donde más se puede romper algo si se omite un fragmento (botones, handlers de DataTable, modales auxiliares)

### Módulo 9: QA + ajustes
- **Path**: navegador + posibles fixes en módulos previos
- **Responsabilidad**: ejecutar la sección 4 (QA manual) completa. Reportar y corregir cualquier regresión.
- **Depende de**: Módulos 1–8 completos
- **Riesgo**: variable

---

## 4. Test / QA Specification

### QA manual (golden path)

**Modo crear pedido nuevo (desde cero):**
1. Ir a `/pedidos` → click "Agregar Pedido"
2. Wizard abre en paso 1, stepper marca paso 1 activo
3. Buscar cliente por documento (existente) → autocomplete muestra resultados → seleccionar
4. Campos de cliente se rellenan automáticamente
5. Setear fecha entrega + prioridad
6. Click "Continuar" → paso 2 activo
7. Agregar 2 productos manuales con cantidades y unitarios
8. Total se actualiza en vivo
9. Click "Continuar" → paso 3 activo
10. Total readonly, ingresar abono parcial
11. Restante se calcula automáticamente
12. Seleccionar método "Transferencia" → campos condicionales aparecen
13. Llenar campos de transferencia
14. Click "Continuar" → paso 4 (resumen)
15. Verificar que toda la info se muestra correctamente
16. Click "Guardar Pedido" → toast verde "Pedido creado #NN" → modal cierra → DataTable se recarga → nuevo pedido visible

**Modo completar desde cotización:**
1. En `/cotizaciones` → click "Convertir a Pedido" en una cotización aprobada
2. Wizard abre directamente en **paso 3** (saltea 1 y 2 hidratados)
3. Banner amber arriba: "Datos heredados de cotización #NN"
4. Verificar que cliente y productos están bien
5. Ingresar pago → continuar → resumen → guardar
6. Pedido creado con `cotizacion_id` referencia correcta

**Modo editar pedido existente:**
1. En `/pedidos` → click "Editar" en un pedido pendiente
2. Wizard abre en paso 1 con datos pre-cargados
3. Navegar libremente entre pasos (sin validación de orden, ya están todos válidos)
4. Modificar cualquier campo → continuar → guardar
5. Pedido actualizado en DataTable

**Importación de cotización desde el paso 2:**
1. Modo crear nuevo → completar paso 1 → entrar a paso 2
2. Click banner "Importar desde cotización"
3. Modal `seleccionar_cotizacion` abre encima del wizard (modales anidados — patrón global ya funciona)
4. Seleccionar cotización → modal cierra → productos del paso 2 se hidratan con badge "Heredado de #NN"
5. Productos editables/eliminables como cualquier otro
6. Continuar normalmente

### Edge cases a verificar

- **Cancelar wizard a media**: click X o "Cancelar" → confirmación si hay cambios → modal cierra sin persistir
- **Validaciones por paso**: no se puede pasar al paso N+1 sin completar campos requeridos del paso N
- **Abono = 0**: método de pago opcional, restante = total
- **Abono = total**: paga completo, estado del pedido podría ser "Completado" (decisión del backend)
- **Sin productos en paso 2**: no se puede continuar a paso 3 (validación)
- **Reabrir wizard en modo edit**: todos los pasos pre-llenados, primer paso activo
- **Cambio de cliente en edit**: si el pedido tenía cliente A y se cambia a B, debe actualizarse `cliente_id` en backend
- **Modal anidado (seleccionar cotización dentro del wizard)**: el patrón global `modal-hidden-temp` debe funcionar; verificar que al cerrar el modal hijo, el wizard reaparece intacto

### Dark mode

- Stepper, líneas progresivas, dots — todos los acentos cyan
- Cards internos con `bg-light-subtle` en dark mode pasan a tono oscuro
- Chips de prioridad y métodos de pago con contraste adecuado
- Validaciones invalid border-color visible en dark
- Banner amber "Datos heredados" — tono dark mode acorde

### Responsive

- Wizard `modal-xl` debería caber en pantallas ≥1200px sin scroll horizontal
- En tablet (~768–1199px), el stepper sigue horizontal; labels pueden achicarse a iniciales
- En mobile (<768px), considerar si el wizard usa modal-fullscreen-sm-down (decisión: **sí**)

---

## 5. Criterios de aceptación

> La feature está completa cuando TODO lo siguiente es verdadero:

- [ ] Wizard de 4 pasos funcional en los 3 modos: crear nuevo, completar desde cotización, editar
- [ ] Clases CSS renombradas: `.cot-*` → `.wiz-*` en custom.css, blade y JS — wizard de cotizaciones sigue funcionando idéntico
- [ ] `pedidos/index.blade.php` baja de 2709 a ~200 líneas
- [ ] `pedidos/modals.blade.php` creado con el wizard + viewModal + showModal
- [ ] `pedidos/scripts/main.blade.php` creado con la lógica completa
- [ ] Cada paso valida antes de permitir avanzar al siguiente
- [ ] Importación "Desde cotización" desde el paso 2 funciona (modal anidado)
- [ ] Paso 3 (Pago) tiene validaciones cross-field correctas (abono ≤ total, método obligatorio si abono > 0)
- [ ] Submit final hace POST/PUT correcto, DataTable se recarga, toast de éxito
- [ ] QA manual sección 4 pasa completa en light + dark mode
- [ ] Mobile (<768px) usa modal-fullscreen-sm-down
- [ ] Sin estilos inline nuevos en los Blade (toda regla en `custom.css`)
- [ ] **Una sola PR** que cubre los 10 módulos (1–9 + 7b), mergeada a `enmanuel`
- [ ] Modo edit preserva campos protegidos del modal actual (cliente, documento, fecha pedido, productos read-only)
- [ ] Doc `docs/conventions/wizard-pattern.md` creado (renombre desde la sección de cotización en `custom.css` y patrón replicable para futuros módulos)

---

## 6. Codebase Contract

> **CRÍTICO — anclaje anti-alucinación.** Esta sección es la única fuente de verdad sobre qué existe en el código.

### Imports verificados

```php
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePedidoRequest;
use App\Http\Requests\UpdatePedidoRequest;
use App\Models\Pedido;                   // app/Models/Pedido.php
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\DetallePedido;
use App\Models\PagoPedido;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
```

### Firmas existentes a usar

```php
// app/Http/Controllers/PedidoController.php
public function store(StorePedidoRequest $request)        // línea 137
public function update(UpdatePedidoRequest $request, $id) // línea 170
public function show($id)                                  // línea 148
public function getCotizacionesDisponibles()              // línea 108
public function getPedidos(Request $request)               // línea 45

// app/Models/Pedido.php
class Pedido extends Model {
    use HasFactory, SoftDeletes;
    protected $table = 'pedido';
    protected $fillable = ['cotizacion_id', 'cliente_id', 'fecha_pedido',
                           'fecha_entrega_estimada', 'estado', 'total',
                           'user_id', 'abono', 'prioridad'];
    public function cliente(): BelongsTo
    public function cotizacion(): BelongsTo
    public function productos(): HasMany    // DetallePedido
    public function pagos(): HasMany        // PagoPedido
    public function recalcularAbono(): void
}
```

### Rutas existentes a usar

```
POST   /pedidos                                pedidos.store
PUT    /pedidos/{pedido}                       pedidos.update
GET    /pedidos/{pedido}/edit                  pedidos.edit
GET    /pedidos-data                           pedidos.data
GET    /pedidos/cotizaciones-disponibles       pedidos.cotizacionesDisponibles
GET    /cotizaciones/{id}/datos-para-pedido    cotizaciones.datosParaPedido
POST   /cotizaciones/{id}/convertir-a-pedido   cotizaciones.convertirAPedido
GET    /personas-search                         (autocomplete paso 1)
POST   /clientes/from-persona/{id}              (crear cliente desde persona)
```

### Convenciones a respetar (ver `docs/conventions/`)

- `docs/conventions/modal-system.md` — modal raíz `atlantico-modal atlantico-modal--op`
- `docs/conventions/nested-modals.md` — fix global ya aplicado; modal "seleccionar cotización" dentro del wizard funciona automáticamente
- `docs/conventions/js-validations.md` — patrón blur + `validarFormulario*()` + wiring al submit; `select2:close` si hay Select2
- `docs/conventions/ux-search-filters.md` — no aplica al modal pero sí al listado de pedidos (ya migrado en FEAT-001)
- `memory/reference_persona_unified_search.md` — buscador de personas del paso 1
- `AGENTS.md` § Estándares visuales — DataTable, cards, badges

### Vista canónica de referencia (cotización wizard)

```
resources/views/admin/cotizaciones/modals.blade.php:212-810   ← wizard HTML
resources/views/admin/cotizaciones/scripts/main.blade.php     ← wizard JS (5277 líneas)
public/assets/css/custom.css § "COT WIZARD"                    ← estilos
```

### NO existe — no referenciar

- ~~`wiz-step-component.blade.php`~~ — no crear componente Blade compartido; el wizard de pedidos usa su propia copia del HTML por simplicidad
- ~~`PedidoService`~~ — no se crea; la lógica vive en el controller
- ~~ruta `pedidos.wizard.*`~~ — no se necesitan rutas nuevas
- ~~tabla `pedido_wizard_state`~~ — no se persiste estado del wizard; vive en JS
- ~~clase `.ped-step-*`~~ — no inventar; reusar `.wiz-*` ya generalizado
- ~~Vue/React/Alpine para el wizard~~ — sigue siendo jQuery vanilla como cotizaciones

---

## 7. Notas de implementación y restricciones

### Patrones a seguir

- **Empezar por Módulo 1** (rename `.cot-*` → `.wiz-*`) antes de tocar pedidos. Verificar que cotizaciones sigue funcionando.
- **Crear `pedidos/modals.blade.php` desde cero** copiando `cotizaciones/modals.blade.php` como base y adaptando.
- **Crear `pedidos/scripts/main.blade.php` desde cero** copiando `cotizaciones/scripts/main.blade.php` como base y adaptando — pero el paso 3 (pago) es nuevo, no hay referencia.
- **Reutilizar handlers** de validación y eventos del wizard de cotización donde sea idéntico (chips de prioridad, autocomplete cliente, totales de productos).
- **Mantener `pedidos/index.blade.php` funcional** hasta que el wizard nuevo esté completo — desmontar el modal viejo solo en el Módulo 8.
- **Refactorizar `index.blade.php` con `@include`s** al final, no al principio.
- **NO commitear** un wizard parcialmente roto a `enmanuel`. Trabajar en rama `feat/pedidos-wizard` y mergear solo cuando todo funcione.

### Riesgos conocidos

| Riesgo | Mitigación |
|---|---|
| Rename `.cot-*` → `.wiz-*` rompe el wizard de cotizaciones | Hacer el rename como Módulo 1 aislado, probar cotizaciones antes de seguir |
| Modal anidado (seleccionar cotización dentro del wizard) — z-index o backdrop | El patrón global `modal-hidden-temp` ya está implementado (memoria `reference_modales_anidados`); verificar pero NO reimplementar |
| Validaciones de pago cross-field complejas | Aislar en función `validarPagoPedidoPaso3()`; probar 6 casos: abono 0, abono parcial, abono total, abono > total, sin método, con método |
| `index.blade.php` tiene 138 estilos inline | Fuera de alcance del spec; los inlines se preservan tal cual al extraer al `modals.blade.php`. Se abordan en spec aparte |
| `seleccionar_cotizacion.blade.php` tiene su propio JS (`cotizacion_selection.blade.php`) | Mantener intacto; solo cambiar el callback que recibe la cotización seleccionada para hidratar el paso 2 |

### Dependencias externas

| Paquete | Versión | Razón |
|---|---|---|
| — | — | sin dependencias nuevas |

---

## 8. Preguntas abiertas — RESUELTAS

- [x] **Rename `.cot-*` → `.wiz-*` afecta a ambos módulos en la misma PR, o solo a cotizaciones (Módulo 1) primero y luego pedidos?** — *Owner: Emmanuel*: **misma PR**. El rename va dentro del PR del wizard de pedidos (Módulo 1). El primer commit del flujo es el rename + verificación de que cotizaciones sigue funcionando.
- [x] **El paso 3 (Pago) — métodos de pago: ¿son siempre los mismos 3 (Efectivo, Transferencia, Pago Móvil) o vienen de BD?** — *Owner: Emmanuel + investigación Claude*: **hardcoded**. Verificado: viven como `const METODOS = ['efectivo', 'transferencia', 'pago_movil']` en `app/Models/PagoPedido.php` y como `enum('metodo', [...])` en la columna `pago_pedido.metodo`. Lo que sí es tabla normalizada es `banco_id` (tabla `banco`) usado para transferencia y pago móvil.
- [x] **¿Mostrar el wizard también para `show` (ver detalles)?** — *Owner: Emmanuel*: **NO**. El wizard solo cubre create/edit. `viewModal` (read-only) queda intacto como está hoy.
- [x] **¿Botones Anterior/Continuar abajo o arriba?** — *Owner: Emmanuel*: **abajo** (footer del modal), consistente con el wizard de cotización.
- [x] **Modo edit: ¿se permite reabrir el wizard si el pedido ya está completo?** — *Owner: Emmanuel + investigación Claude*: **sí, siempre**. Se preserva el comportamiento del modal actual: en edit los campos del paso 1 y paso 2 quedan read-only (cliente, documento, fecha pedido, productos), solo se pueden editar `fecha_entrega_estimada`, `prioridad`, `estado` y todo el bloque de pago del paso 3. Documentado en detalle como **Módulo 7b** en sección 3.
- [x] **Asignación del spec** — *Owner: Emmanuel*: **Emmanuel** toma la feature. Una sola PR, rama `feat/pedidos-wizard`.

---

## Historial de revisiones

| Versión | Fecha | Autor | Cambio |
|---|---|---|---|
| 0.1 | 2026-05-26 | Emmanuel + Claude | Borrador inicial: wizard 4 pasos (Cliente · Productos · Pago · Resumen), 9 módulos, rename `.cot-*` → `.wiz-*` |
| 1.0 | 2026-05-26 | Emmanuel | Resueltas las 6 preguntas abiertas. Métodos de pago confirmados como enum hardcoded + tabla `banco`. Agregado Módulo 7b (modo edit con campos protegidos). Spec asignado a Emmanuel. Status → approved. |
