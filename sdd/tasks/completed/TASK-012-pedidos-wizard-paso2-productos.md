# TASK-012: Paso 2 del wizard de pedidos (Productos + importar desde cotización)

**Feature**: FEAT-002 — pedidos-wizard
**Spec**: `sdd/specs/pedidos-wizard.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: L (4–8h)
**Depends-on**: TASK-011
**Assigned-to**: santiago

---

## Contexto

Implementar el paso 2 (Productos): grilla de productos del pedido con agregar/editar/eliminar y total en vivo, más el banner "Importar desde cotización" que abre el modal de selección existente y hidrata el paso.

Sección del spec: `## 3 → Módulo 4`. Paso 2 detallado en `## 2 → Paso 2`.

---

## Scope

- Implementar en `pedidos/scripts/main.blade.php` (sección paso 2):
  - Estado en memoria de productos del pedido (array JS)
  - Agregar producto (buscar producto, cantidad, talla, color, precio unitario)
  - Editar / eliminar producto individual
  - Cálculo de total en vivo (suma de subtotales)
  - Render de tarjetas de producto
- Banner "¿Importar productos desde una cotización?" que abre `modal_seleccionar_cotizacion`
- Al confirmar selección de cotización → hidratar productos del paso 2 con badge "Heredado de #NN"
- Validación del paso 2: al menos 1 producto antes de continuar a paso 3

**NO está en alcance**:
- Paso 3 (Pago), Paso 4 (Resumen)
- Lógica completa del modo "completar desde cotización" que abre directo en paso 3 (TASK-015) — aquí solo el botón de importar manual desde el paso 2

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/pedidos/scripts/main.blade.php` | MODIFY | Lógica del paso 2 (productos) |
| `resources/views/admin/pedidos/modals.blade.php` | MODIFY | Reemplazar placeholder paso 2 + banner importar |
| `resources/views/admin/pedidos/scripts/cotizacion_selection.blade.php` | MODIFY | Ajustar callback de selección para hidratar paso 2 |

---

## Codebase Contract (Anti-Alucinación)

### Rutas verificadas

```
GET /pedidos/cotizaciones-disponibles   pedidos.cotizacionesDisponibles  → PedidoController@getCotizacionesDisponibles  (routes/web.php:139)
GET /cotizaciones/{id}/datos-para-pedido cotizaciones.datosParaPedido     → CotizacionController@getDatosParaPedido      (routes/web.php:153)
```

### Componentes existentes

- **Modal seleccionar cotización**: `resources/views/admin/pedidos/modals/seleccionar_cotizacion.blade.php`
- **Su JS**: `resources/views/admin/pedidos/scripts/cotizacion_selection.blade.php` — contiene la lógica de listar cotizaciones disponibles y seleccionar una. **Mantener intacto salvo el callback** que recibe la cotización elegida.
- **Grilla de productos de referencia**: `resources/views/admin/cotizaciones/scripts/main.blade.php` paso 2 — lógica de agregar/editar/eliminar productos y totales a portar.

### Patrón de referencia (cotización paso 2)

Reusar de cotización:
- estructura del array de productos en memoria
- render de tarjetas (`renderProductoCard`)
- cálculo de total
- búsqueda de producto (autocomplete o select)

Adaptar nombres a pedido si difieren.

### Endpoint datos-para-pedido

`cotizaciones.datosParaPedido` devuelve los productos de la cotización listos para pre-llenar. Verificar el shape exacto del JSON con:
```bash
grep -A30 "function getDatosParaPedido" app/Http/Controllers/CotizacionController.php
```

### Convenciones a respetar

- `memory/reference_modales_anidados.md` — el modal de selección se abre encima del wizard; fix global ya aplicado
- DataTable interno (si aplica): `dt-transactional`
- `docs/conventions/js-validations.md`

### NO existe — no referenciar

- ~~`PedidoController@importarCotizacion`~~ — no existe; se usa `datosParaPedido` (lectura) y el guardado real es en el submit final (TASK-014)
- ~~tabla intermedia para productos en draft~~ — el estado vive en JS hasta el submit

---

## Notas de implementación

### Patrón a seguir

```js
PedidoWizard.productos = [];  // [{producto_id, nombre, cantidad, talla, color, precio_unitario, subtotal, heredado_cotizacion_id}]
PedidoWizard.agregarProducto(data) { ... recalcularTotal() }
PedidoWizard.eliminarProducto(idx) { ... }
PedidoWizard.recalcularTotal() { this.total = sum(subtotales); render(); }
PedidoWizard.hidratarDesdeCotizacion(cotizacionId) {
    // fetch datosParaPedido → push productos con heredado_cotizacion_id = cotizacionId
}
```

### Restricciones clave

- Productos heredados muestran badge visual "Heredado de #NN" pero siguen siendo editables/eliminables
- Total se propaga al paso 3 (Pago) — exponer `PedidoWizard.total`
- Validación: `productos.length > 0` antes de avanzar
- El modal de selección al cerrarse debe restaurar el wizard (patrón modal anidado global)

### Referencias en el código

- `resources/views/admin/cotizaciones/scripts/main.blade.php` — paso 2 productos
- `resources/views/admin/pedidos/index.blade.php` — `renderProductoCard` viejo (referencia, tiene deuda CSS inline; NO copiar los inline styles)

---

## Criterios de aceptación

- [ ] Agregar producto manual → tarjeta aparece, total se actualiza
- [ ] Editar producto → recalcula
- [ ] Eliminar producto → recalcula
- [ ] Banner "Importar desde cotización" abre el modal de selección
- [ ] Seleccionar cotización → productos hidratados con badge "Heredado de #NN"
- [ ] No se avanza a paso 3 sin al menos 1 producto
- [ ] Modal anidado cierra y restaura el wizard intacto
- [ ] Light + dark mode

---

## QA manual

1. Wizard → completar paso 1 → entrar paso 2
2. Agregar 2 productos manuales → total suma correcto
3. Editar cantidad de uno → total recalcula
4. Eliminar uno → total recalcula
5. Click "Importar desde cotización" → modal abre encima
6. Seleccionar una cotización → modal cierra → productos hidratados con badge
7. Editar un producto heredado → funciona
8. Vaciar productos → "Continuar" bloquea
9. Dark mode → repetir

---

## Instrucciones para el ejecutor

1. Lee spec + verifica TASK-011 `completed`
2. Verifica shape de `datosParaPedido` con grep
3. Header: `Status: in-progress`
4. Rama `feat/pedidos-wizard`
5. Implementa
6. QA manual
7. Mueve a `completed/`, llena Nota
8. NO mergear aún

---

## Nota de Completitud

**Completado por**: santiago
**Fecha**: 2026-05-26
**Commits**: ver rama `feat/pedidos-wizard`
**Notas**: Implementación completa. CSS en `custom.css` (`.ped-import-cot-banner`, `.ped-prod-card`, `.ped-prod-icon`, `.ped-prod-meta`, `.ped-prod-subtotal`, `.ped-inherited-badge`, dark mode). Paso 2 en `modals.blade.php` con banner, KPI bar (reutilizando `cot-kpi-grid`), empty state (reutilizando `cot-empty-state`), lista dinámica y modal `#ped-agregar-producto-modal`. Paso 2 IIFE en `scripts/main.blade.php` con estado `pedProdItems[]`, CRUD completo, autocomplete contra `window.products`, hydratación desde cotización via `window.pedHidratarDesde()`. `validateStep(2)` actualizado en scaffold. `cotizacion_selection.blade.php` actualizado con check `window.pedWizardImportMode` para redirigir a hidratación en lugar de conversión atómica.

**Desviaciones del spec**: ninguna.
