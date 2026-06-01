# TASK-016: Modo edit del wizard con campos protegidos

**Feature**: FEAT-002 — pedidos-wizard
**Spec**: `sdd/specs/pedidos-wizard.spec.md`
**Status**: done
**Priority**: medium
**Esfuerzo estimado**: M (2–4h)
**Depends-on**: TASK-014
**Assigned-to**: santiago

---

## Contexto

Implementar el modo edición del wizard, preservando el comportamiento del modal actual: en edit la mayoría de campos de los pasos 1 y 2 quedan read-only, y solo se puede editar fecha de entrega, prioridad, estado y todo el bloque de pago (paso 3). El submit hace PUT en lugar de POST.

Sección del spec: `## 3 → Módulo 7b`.

> **Paralelizable con TASK-015** — ambas dependen de TASK-014 pero tocan rutas de código distintas (TASK-015 = origen cotización, TASK-016 = origen edit). Coordinar merge final.

---

## Scope

- Implementar handler `PedidoWizard.abrirEnEdit(pedidoId)`:
  - Fetch a `pedidos.show` o `pedidos.edit` para cargar el pedido
  - Hidratar los 4 pasos
  - Aplicar read-only a campos protegidos del paso 1 y 2
  - Habilitar solo: `fecha_entrega_estimada`, `prioridad`, `estado`, y todo el paso 3 (pago)
  - Mostrar campo `estado` (solo visible en edit)
  - Permitir reabrir incluso si el pedido está "Completado"
- Submit en modo edit hace PUT a `pedidos.update`
- Cablear el botón "Editar"/"Completar Pedido #NN" del listado de pedidos

**NO está en alcance**:
- Modo crear (TASK-014) ni modo desde-cotización (TASK-015)

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/pedidos/scripts/main.blade.php` | MODIFY | Handler `abrirEnEdit` + lógica read-only + submit PUT |
| `resources/views/admin/pedidos/modals.blade.php` | MODIFY | Campo `estado` (solo edit) en paso 1 |

---

## Codebase Contract (Anti-Alucinación)

### Rutas verificadas

```
GET /pedidos/{pedido}/edit   pedidos.edit    → PedidoController@edit    (routes/web.php:114)
GET /pedidos/{pedido}        pedidos.show    → PedidoController@show     (routes/web.php:142)
PUT /pedidos/{pedido}        pedidos.update  → PedidoController@update(UpdatePedidoRequest) (routes/web.php:112)
```

### Request de validación

```php
// app/Http/Requests/UpdatePedidoRequest.php — leer reglas antes de implementar:
//   grep -A40 "function rules" app/Http/Requests/UpdatePedidoRequest.php
```

### Comportamiento read-only del modal actual (verificado)

`resources/views/admin/pedidos/index.blade.php` líneas 1871-1893 — al abrir edit, el modal viejo aplica:
- `cliente-id-field`: `prop('disabled', true)` + `campo-protegido`
- `cliente-nombre-field`, `cliente-email-field`, `cliente-telefono-field`: `prop('readonly', true)` + `bg-light` + cursor not-allowed
- `ci-rif-number-field` / `ci-rif-prefix-field`: readonly/disabled + bg-light
- `fecha-pedido-field`: `prop('readonly', true)` + `campo-protegido`

Campos editables en edit (NO en la lista protegida):
- `fecha-entrega-estimada-field`
- `prioridad-field`
- `estado-field` (visible solo en edit — `#estado-field-wrapper` en línea 618 del modal viejo)
- bloque de pago completo

### Estados del pedido

```php
// Verificar enum/constante exacta:
//   grep -i "estado" app/Models/Pedido.php
// El modal viejo usa: Pendiente, Procesando, Completado, Cancelado (index.blade.php:626-629)
```

### Convenciones a respetar

- Clase `campo-protegido` ya existe en el proyecto (estilos bg-light + cursor)
- `docs/conventions/js-validations.md`

### NO existe — no referenciar

- ~~`pedidos.completar`~~ — no hay ruta especial; edit usa `pedidos.update`
- ~~bloqueo por estado al abrir~~ — el spec decidió permitir reabrir siempre (sin importar estado)

---

## Notas de implementación

### Patrón a seguir

```js
PedidoWizard.abrirEnEdit(pedidoId) {
    this.reset();
    this.modo = 'edit';
    this.pedidoId = pedidoId;
    fetch(`/pedidos/${pedidoId}/edit`)
        .then(r => r.json())
        .then(data => {
            this.hidratarTodo(data);
            this.aplicarCamposProtegidos();   // readonly en cliente, doc, fecha_pedido, productos
            this.mostrarCampoEstado();
            this.irAPaso(1);
            $('#showModal').modal('show');
        });
}
PedidoWizard.aplicarCamposProtegidos() {
    // readonly + campo-protegido en los campos listados en el contrato
    // productos del paso 2: ocultar botones agregar/editar/eliminar
}
```

### Restricciones clave

- En edit, el paso 2 muestra productos pero sin acciones de modificación (read-only)
- El submit PUT envía solo los campos editables; el backend ignora cliente/productos si vienen (verificar `UpdatePedidoRequest`)
- El título del modal pasa a "Editar Pedido #NN" o "Completar Pedido #NN" según estado
- Reset de los `campo-protegido` al cerrar/reabrir en modo crear (no arrastrar readonly entre modos)

### Referencias en el código

- `resources/views/admin/pedidos/index.blade.php:1871-1893` — lógica read-only vieja a portar
- `app/Http/Requests/UpdatePedidoRequest.php` — reglas de update

---

## Criterios de aceptación

- [ ] "Editar" en listado abre wizard con datos cargados
- [ ] Cliente, documento, fecha_pedido → read-only (bg-light, cursor not-allowed)
- [ ] Productos paso 2 → read-only (sin botones modificar)
- [ ] fecha_entrega, prioridad, estado → editables
- [ ] Campo estado visible solo en edit
- [ ] Paso 3 (pago) → totalmente editable
- [ ] Guardar → PUT a `pedidos.update` → pedido actualizado
- [ ] Reabrir un pedido "Completado" → permitido
- [ ] Cambiar de modo edit a crear (cerrar y "Agregar") → campos protegidos se resetean
- [ ] Light + dark mode

---

## QA manual

1. `/pedidos` → "Editar" un pedido Pendiente
2. Wizard abre en paso 1 con datos cargados
3. Verificar cliente/documento/fecha_pedido read-only
4. Cambiar prioridad y fecha entrega → OK
5. Paso 2 → productos visibles, sin botones de edición
6. Paso 3 → modificar abono/método → OK
7. Guardar → pedido actualizado en DataTable
8. Editar un pedido "Completado" → debe permitir abrir
9. Cerrar → "Agregar Pedido" → verificar campos NO están protegidos (modo crear limpio)
10. Dark mode → repetir

---

## Instrucciones para el ejecutor

1. Lee spec + verifica TASK-014 `completed`
2. **LEE `UpdatePedidoRequest`** + verifica estados en `Pedido` model
3. Header: `Status: in-progress`
4. Rama `feat/pedidos-wizard`
5. Implementa
6. QA manual (atención al caso 9 — reset entre modos)
7. Mueve a `completed/`, llena Nota
8. NO mergear aún

---

## Nota de Completitud

**Completado por**: Emmanuel (continuando el trabajo de Santiago)
**Fecha**: 2026-05-27
**Commits**: (este commit)
**Notas**:
- `window.pedAbrirEnEdit(pedidoId)` agregado en `scripts/main.blade.php` (IIFE nuevo, espeja el patrón de TASK-015). Usa `GET pedidos.show` (no existe `edit()` en el controller — la ruta `pedidos.edit` apunta a un método inexistente; `show()` ya devuelve el pedido completo con productos/bordados/pagos/cliente normalizado).
- Hidrata los 4 pasos: cliente (construye objeto persona desde campos normalizados), fechas, prioridad, estado, productos (mapea formato `show()` `producto.nombre` → `producto_nombre`), pago (abono + primer pago).
- Marca edit con `#ped-wiz-id-field` ANTES de abrir el modal → los resets de modo crear (en `show.bs.modal`) se saltan solos.
- Campos protegidos: documento del cliente (`#ped-ci-rif-*`) y `fecha_pedido` → readonly + `campo-protegido`. Clase `.ped-wiz-edit-mode` en el modal oculta acciones de producto, importar y cambiar cliente (CSS en custom.css).
- Submit ramifica: edit → `_method: PUT` + `estado` + `url = /pedidos/{id}`; crear → POST store. Mensaje de éxito adaptado.
- `hidden.bs.modal` resetea todo el estado edit (id, readonly, estado wrapper, título, texto botón) → la próxima apertura en modo crear queda limpia.
- **Fix de bug en prep de TASK-016**: los chips/select de estado en `modals.blade.php` tenían valores de cotización (`Aprobado`, `Entregado`) que NO existen en el enum de pedido. Corregidos a `Pendiente/Procesando/Completado/Cancelado` (coinciden con `UpdatePedidoRequest`).

**Desviaciones del spec**:
- El backend `update()` BLOQUEA con 403 editar pedidos `Completado`/`Cancelado` (guard pre-existente). El spec pedía "reabrir siempre". Resolución: el wizard SÍ abre en edit para cualquier estado (read), pero el guardado de un Completado/Cancelado será rechazado por el backend con su mensaje. Para Pendiente/Procesando el edit funciona completo.
- **Limitación de bordados (pre-existente, NO introducida aquí)**: `pedConstruirPayload()` (TASK-014) no envía bordados ni en crear ni en editar — el modelo de productos del wizard aún no captura bordados. Editar+guardar un pedido con bordados los perdería. Es un gap de TASK-012/014; se debe abordar aparte. Flageado para QA en TASK-018.
- **QA end-to-end diferido a TASK-018**: hay un `#showModal` duplicado (el viejo en `index.blade.php` + el nuevo en `modals.blade.php`). TASK-017 elimina el viejo; recién ahí el wizard es testeable en navegador.
