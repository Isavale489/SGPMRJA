# TASK-015: Modo "completar desde cotización" en el wizard

**Feature**: FEAT-002 — pedidos-wizard
**Spec**: `sdd/specs/pedidos-wizard.spec.md`
**Status**: done
**Priority**: medium
**Esfuerzo estimado**: M (2–4h)
**Depends-on**: TASK-014
**Assigned-to**: santiago

---

## Contexto

Cuando el usuario convierte una cotización en pedido (acción "Convertir a Pedido" en el listado de cotizaciones), el wizard debe abrir con pasos 1 y 2 hidratados desde la cotización y posicionarse directamente en el paso 3 (Pago), con marca visual de "Datos heredados".

Sección del spec: `## 3 → Módulo 7`.

---

## Scope

- Implementar handler `PedidoWizard.abrirDesdeCotizacion(cotizacionId)`:
  - Fetch a `cotizaciones.datosParaPedido`
  - Hidratar paso 1 (cliente, fechas, prioridad) y paso 2 (productos)
  - Marcar `cotizacion_id` en el estado
  - Posicionar el wizard en paso 3 (Pago)
  - Banner amber "Datos heredados de cotización #NN" visible en pasos 1 y 2
- Cablear el botón "Convertir a Pedido" del listado de cotizaciones para invocar este handler
- El submit final (TASK-014) ya incluye `cotizacion_id` en el payload → verificar que se persiste

**NO está en alcance**:
- Modo edit (TASK-016)
- Cambiar el endpoint `datosParaPedido` (ya existe)

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/pedidos/scripts/main.blade.php` | MODIFY | Handler `abrirDesdeCotizacion` |
| `resources/views/admin/pedidos/modals.blade.php` | MODIFY | Banner "Datos heredados" en pasos 1 y 2 |
| `resources/views/admin/cotizaciones/index.blade.php` o `scripts/main.blade.php` | MODIFY | Cablear botón "Convertir a Pedido" |

---

## Codebase Contract (Anti-Alucinación)

### Rutas verificadas

```
GET  /cotizaciones/{id}/datos-para-pedido    cotizaciones.datosParaPedido   → CotizacionController@getDatosParaPedido    (routes/web.php:153)
POST /cotizaciones/{id}/convertir-a-pedido   cotizaciones.convertirAPedido  → CotizacionController@convertirAPedido      (routes/web.php:154)
```

**Decisión de diseño**: el wizard usa `datosParaPedido` (lectura) para hidratar y luego hace el `pedidos.store` normal con `cotizacion_id`. El endpoint `convertirAPedido` (POST directo) queda como flujo alternativo legacy — verificar si se sigue usando o se deja la conversión 100% vía wizard. *Confirmar con Emmanuel si se deprecia `convertirAPedido`.*

### Shape de datosParaPedido

```bash
# Verificar antes de implementar:
grep -A30 "function getDatosParaPedido" app/Http/Controllers/CotizacionController.php
```

### Patrón de referencia

- El botón actual "Convertir a Pedido" en cotizaciones — buscar con:
  ```bash
  grep -n "convertir\|Convertir\|datosParaPedido" resources/views/admin/cotizaciones/scripts/main.blade.php
  ```

### Convenciones a respetar

- Banner amber: usar paleta amber consistente con `btn-historial-ver` (`memory` — amber/slate)
- `docs/conventions/js-validations.md`

### NO existe — no referenciar

- ~~`pedidos.desdeCotizacion`~~ — no hay ruta nueva; se usa `datosParaPedido` + `store`
- ~~estado `convirtiendo`~~ — usar `PedidoWizard.cotizacionId` para marcar origen

---

## Notas de implementación

### Patrón a seguir

```js
PedidoWizard.abrirDesdeCotizacion(cotizacionId) {
    this.reset();
    this.cotizacionId = cotizacionId;
    fetch(`/cotizaciones/${cotizacionId}/datos-para-pedido`)
        .then(r => r.json())
        .then(data => {
            this.hidratarCliente(data.cliente);
            this.hidratarProductos(data.productos);
            this.mostrarBannerHeredado(cotizacionId);
            this.irAPaso(3);  // saltar directo a Pago
            $('#showModal').modal('show');
        });
}
```

### Restricciones clave

- Los pasos 1 y 2 quedan navegables hacia atrás (el usuario puede revisar/ajustar)
- El banner solo aparece cuando `cotizacionId != null`
- El título del modal pasa a "Completar Pedido (desde Cotización #NN)"
- Si el usuario edita productos heredados, sigue siendo válido (el pedido puede diferir de la cotización)

### Referencias en el código

- `app/Http/Controllers/CotizacionController.php@getDatosParaPedido` — shape del JSON

---

## Criterios de aceptación

- [ ] "Convertir a Pedido" en cotizaciones abre el wizard hidratado en paso 3
- [ ] Cliente y productos correctos (heredados de la cotización)
- [ ] Banner amber "Datos heredados de cotización #NN" en pasos 1 y 2
- [ ] Guardar → pedido creado con `cotizacion_id` correcto
- [ ] Navegación hacia atrás funciona (revisar pasos 1 y 2)
- [ ] Light + dark mode

---

## QA manual

1. `/cotizaciones` → cotización aprobada → "Convertir a Pedido"
2. Wizard abre directo en paso 3, banner amber visible
3. Volver a paso 1 → cliente correcto, banner visible
4. Volver a paso 2 → productos heredados con badge
5. Volver a paso 3 → ingresar pago → guardar
6. Verificar en BD que `pedido.cotizacion_id` = id correcto
7. Dark mode → repetir

---

## Instrucciones para el ejecutor

1. Lee spec + verifica TASK-014 `completed`
2. Verifica shape de `datosParaPedido`
3. Confirma con Emmanuel si se deprecia `convertirAPedido`
4. Header: `Status: in-progress`
5. Rama `feat/pedidos-wizard`
6. Implementa
7. QA manual
8. Mueve a `completed/`, llena Nota
9. NO mergear aún

---

## Nota de Completitud

**Completado por**: santiago
**Fecha**: 2026-05-27
**Commits**: feat(pedidos): TASK-015 modo completar desde cotización
**Notas**: Implementado patrón `pedPendingHydration` — los datos se almacenan antes de que `show.bs.modal` dispare los resets, y se aplican en `shown.bs.modal` después de que los resets terminan. IIFE aislado en `scripts/main.blade.php`. Banner amber en pasos 1 y 2. Query param `?convertir={id}` detectado en carga de página. `pedHidratarProductosDesde` agregado al IIFE de paso 2 como API limpia.

**Desviaciones del spec**:
- Flujo de "Agregar Pedido" desde pedidos/index.blade.php (que abría el modal `seleccionarCotizacionModal` directamente) ahora también usa `pedAbrirDesdeCotizacion` en lugar del POST atómico.
- El endpoint `convertirAPedido` (POST) ya no se usa desde ningún flujo de UI — queda como API interna pero sin botón que lo llame.
