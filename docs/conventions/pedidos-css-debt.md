# Pedidos — Deuda Técnica CSS Inline

> 138 atributos `style=""` en `resources/views/admin/pedidos/index.blade.php` pendientes de migrar a `custom.css`. Listados por categoría para que sea fácil tomarlo como task SDD.

## Archivo excluido

`pedidos/factura.blade.php` — estilos inline **requeridos por el renderer PDF**. NO migrar este archivo. Ver [`pdf-generation.md`](pdf-generation.md).

## Categorías de la deuda

### 1. HTML estático — viewModal (~30 atributos)
Mismo patrón que Empleados. Ya existen clases disponibles, solo aplicarlas:
- Card headers: `inv-card-header-navy`, `inv-card-header-teal`
- Iconos circulares: `emp-icon-box emp-icon-box--navy/green/teal` + `emp-icon--navy/green/teal`

### 2. `display: none` jQuery-controlados (~6 atributos)
**NO migrar a `d-none`** — jQuery `.show()/.hide()` no puede superar `!important`. Dejar como están:
- `#pago-efectivo-container`, `#pago-transferencia-container`, `#pago-pago_movil-container`
- `#edit-btn` (`style="display: none;"`) — igual que en Empleados
- `#estado-field-wrapper` (`style="display:none;"`)

### 3. JS template literals — el bloque más grande (~80 atributos)
Las funciones `renderProductoCard()` y `renderViewCard()` (líneas ~1348 y ~1975 de `index.blade.php`) generan HTML como strings JS con estilos embebidos. Son las más difíciles.

**Patrones repetidos a extraer:**

| Inline actual | Clase propuesta |
|---|---|
| `border-left: 4px solid #00d9a5 !important;` | `.pedido-product-card` |
| `width:45px;height:45px;background:#1e3c72;` | `.pedido-product-avatar` |
| `background:#00d9a5;font-size:0.9rem;` | (clase en badge) |
| `width:28px;height:28px;background:rgba(...)` | reusar `.emp-icon-box--*` |
| `font-size:0.85rem;` | `.pedido-card-text` o Bootstrap `fs-6` |
| `font-size:0.7rem;` | Bootstrap `fs-7` / `.pedido-label-sm` |
| `background:rgba(30,60,114,0.08);` | `.pedido-logos-block` |
| `background:rgba(30,60,114,0.05);` | `.pedido-desc-block` |
| `background:rgba(46,204,113,0.08);` | `.pedido-insumos-block` |
| `border-bottom:1px dashed rgba(30,60,114,0.2);` | `.pedido-bordado-row` |
| `font-size:0.84rem;color:#1e3c72;` | `.pedido-logo-label` |

### 4. Panel total con gradiente (~8 atributos, líneas ~410-430)
Header del panel de resumen monetario:

| Inline actual | Clase propuesta |
|---|---|
| `background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);` | `.pedido-total-panel` |
| `width:38px;height:38px;background:rgba(255,255,255,0.15);` | `.pedido-total-icon` |
| `font-size:0.7rem;letter-spacing:0.06em;text-transform:uppercase;` | `.pedido-total-label` |
| `font-size:1.8rem;line-height:1;` | `.pedido-total-amount` |

### 5. Otros elementos especiales (~14 atributos)
- Persona card / aviso compartido: mismo patrón que Empleados → reusar `.emp-persona-card`, `.emp-shared-notice`.
- `ci_rif_prefix` select: `style="max-width:70px;"` → `.ci-rif-select` (como `.tipo-doc-select`).
- Inputs de búsqueda con bordes navy: `style="border-color:#1e3c72;"` → `.input-navy`.
- Modal footer: `style="background:#f8f9fa;"` → Bootstrap `bg-light`.
- Scrollable areas: `style="max-height:350px/490px/500px;overflow-y:auto;"` → clases utilitarias.

## Orden recomendado de implementación

1. **Primero**: HTML estático del viewModal — fácil, las clases ya existen.
2. **Luego**: elementos especiales (persona card, ci-rif-select, modal footer) — bajo riesgo.
3. **Al final**: JS template literals — mayor riesgo. Hacer con cuidado y probar en browser cada cambio.

## Por qué este módulo es más laborioso que Empleados o Historial

Pedidos tiene ~60% de sus inline styles dentro de strings JS de renderizado dinámico, lo que hace el refactor más laborioso que módulos cuyo CSS inline está mayormente en HTML estático.

## Candidato a spec SDD

Esta deuda es lo suficientemente grande para convertirse en un **spec SDD propio**:
- Feature: `pedidos-css-cleanup`.
- Múltiples tasks (una por categoría de las de arriba).
- Cada task verificable visualmente comparando antes/después en browser.
