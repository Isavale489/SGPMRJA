# TASK-014: Paso 4 del wizard (Resumen) + submit final

**Feature**: FEAT-002 — pedidos-wizard
**Spec**: `sdd/specs/pedidos-wizard.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: M (2–4h)
**Depends-on**: TASK-013
**Assigned-to**: santiago

---

## Contexto

Implementar el paso 4 (Resumen) que consolida cliente + productos + pago en una vista de revisión, y el submit final que hace POST/PUT al backend, recarga el DataTable y muestra toast de éxito.

Sección del spec: `## 3 → Módulo 6`. Paso 4 detallado en `## 2 → Paso 4`.

---

## Scope

- Implementar HTML del paso 4 en `pedidos/modals.blade.php`:
  - Tarjeta resumen del cliente (read-only)
  - Tabla compacta de productos con total
  - Bloque de pago (abono, restante, método)
- Implementar lógica JS en `pedidos/scripts/main.blade.php`:
  - Render del resumen al entrar al paso 4 (lee estado de PedidoWizard)
  - Botón final "Guardar Pedido" (crear) — recolecta todo el estado y hace POST a `pedidos.store`
  - Toast de éxito + cierre del modal + `dataTable.ajax.reload()`
  - Manejo de errores de validación del backend (mostrar en el paso correspondiente)

**NO está en alcance**:
- Modo "completar desde cotización" (TASK-015)
- Modo edit / update (TASK-016) — esta task implementa SOLO el flujo de creación (POST)

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/pedidos/modals.blade.php` | MODIFY | HTML del paso 4 (resumen) |
| `resources/views/admin/pedidos/scripts/main.blade.php` | MODIFY | Render resumen + submit POST |

---

## Codebase Contract (Anti-Alucinación)

### Rutas verificadas

```
POST /pedidos   pedidos.store   → PedidoController@store(StorePedidoRequest)   (routes/web.php:110)
```

### Request de validación

```php
// app/Http/Requests/StorePedidoRequest.php — verificar reglas con:
//   grep -A40 "function rules" app/Http/Requests/StorePedidoRequest.php
// El payload del wizard debe matchear estas reglas.
```

**IMPORTANTE**: Antes de implementar el submit, leer `StorePedidoRequest` para conocer el shape exacto esperado (nombres de campos, productos como array, pagos como array, etc.).

### Estructura del payload (a confirmar con StorePedidoRequest)

```js
{
    cliente_id,
    cotizacion_id,          // null si no viene de cotización
    fecha_pedido,
    fecha_entrega_estimada,
    prioridad,
    total,
    abono,
    productos: [ {producto_id, cantidad, talla, color, precio_unitario, ...} ],
    pagos: [ {metodo, monto, banco_id, referencia} ],   // confirmar nombre en el Request
}
```

### Patrón de referencia

- `resources/views/admin/cotizaciones/scripts/main.blade.php` — submit del wizard de cotización (estructura del fetch/ajax, manejo de toast, reload del DataTable)
- `resources/views/admin/pedidos/index.blade.php` — submit viejo del pedido (referencia del payload que `store` espera)

### Convenciones a respetar

- Toast: SweetAlert2 centralizado (`memory` — reglas en custom.css)
- CSRF: incluir token (`@csrf` en el form o header `X-CSRF-TOKEN`)
- `dataTable.ajax.reload(null, false)` para no perder la página actual

### NO existe — no referenciar

- ~~`pedidos.guardarWizard`~~ — no hay ruta especial; se usa `pedidos.store` existente
- ~~`PedidoController@storeFromWizard`~~ — no existe; el `store` actual debe aceptar el payload

---

## Notas de implementación

### Patrón a seguir

```js
PedidoWizard.construirPayload() {
    return {
        cliente_id: this.cliente.id,
        cotizacion_id: this.cotizacionId || null,
        fecha_pedido: $('#ped-fecha-pedido').val(),
        fecha_entrega_estimada: $('#ped-fecha-entrega').val(),
        prioridad: $('#ped-prioridad').val(),
        total: this.total,
        abono: this.pago.abono,
        productos: this.productos,
        pagos: this.pago.metodo ? [this.pago] : [],
    };
}
PedidoWizard.guardar() {
    // validar los 4 pasos; POST; toast; cerrar; reload
}
```

### Restricciones clave

- Validar los 4 pasos antes de enviar (defensa final)
- Si el backend devuelve 422, mapear errores al paso correspondiente y navegar a él
- Deshabilitar botón "Guardar" durante el request (evitar doble submit)
- Tras éxito: cerrar modal, reset del wizard, reload DataTable, toast verde

### Referencias en el código

- `app/Http/Requests/StorePedidoRequest.php` — reglas de validación (LEER PRIMERO)
- `resources/views/admin/cotizaciones/scripts/main.blade.php` — submit de referencia

---

## Criterios de aceptación

- [ ] Paso 4 muestra resumen correcto de cliente + productos + pago
- [ ] "Guardar Pedido" hace POST con payload correcto
- [ ] Éxito → toast verde + modal cierra + DataTable recarga + pedido visible
- [ ] Error 422 → errores mapeados al paso correcto, navega a él
- [ ] Botón se deshabilita durante el request (no doble submit)
- [ ] Light + dark mode

---

## QA manual

1. Wizard completo (pasos 1-3) → entrar paso 4
2. Verificar resumen: cliente correcto, productos con total, pago con método
3. "Guardar Pedido" → toast verde → modal cierra → nuevo pedido en DataTable
4. Repetir creando un pedido sin abono (abono 0, sin método)
5. Forzar error (ej. quitar fecha en backend) → verificar mapeo de error
6. Doble click rápido en Guardar → solo 1 pedido creado
7. Dark mode → repetir

---

## Instrucciones para el ejecutor

1. Lee spec + verifica TASK-013 `completed`
2. **LEE `StorePedidoRequest` antes de codificar el payload**
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
**Notas**: Paso 4 HTML en `modals.blade.php` con 4 cards (cliente, datos, productos en tabla, pago) populados por JS. Hook `pedRenderResumen()` inyectado en `showStep(4)`. IIFE paso 4 en `scripts/main.blade.php`: `pedRenderResumen()` expuesto globalmente, `pedConstruirPayload()` construye payload sin `precio_unitario` (stripped por `validated()`), `pedMapErrorsToStep()` mapea errores 422 al paso correcto, submit POST a `pedidos.store` con disable/enable del botón, toast éxito, cierre del modal, `$('#pedidos-table').DataTable().ajax.reload(null, false)`.

**Desviaciones del spec**: `precio_unitario` no incluido en el payload porque `StorePedidoRequest` no lo valida y `validated()` lo stripearía — el servidor usa `precio_base` del catálogo. El total en el resumen es estimado (calculado en el cliente con los precios ingresados en el wizard).
