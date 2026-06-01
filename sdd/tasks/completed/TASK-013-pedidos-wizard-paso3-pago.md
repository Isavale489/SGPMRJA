# TASK-013: Paso 3 del wizard de pedidos (Pago) — NUEVO

**Feature**: FEAT-002 — pedidos-wizard
**Spec**: `sdd/specs/pedidos-wizard.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: L (4–8h)
**Depends-on**: TASK-012
**Assigned-to**: santiago

---

## Contexto

Implementar el paso 3 (Pago). Es el paso **nuevo** del wizard de pedidos — no existe en cotización. Maneja total readonly (del paso 2), abono, restante calculado, métodos de pago y campos condicionales por método.

Sección del spec: `## 3 → Módulo 5`. Paso 3 detallado en `## 2 → Paso 3`.

---

## Scope

- Implementar HTML del paso 3 en `pedidos/modals.blade.php`:
  - Total del pedido (readonly, viene del paso 2)
  - Abono (input numérico)
  - Restante (readonly, = total − abono)
  - Chips de métodos de pago: Efectivo / Transferencia / Pago Móvil
  - Sección condicional por método (banco + referencia para transferencia y pago móvil)
- Implementar lógica JS en `pedidos/scripts/main.blade.php` (sección paso 3):
  - Sincronizar total desde `PedidoWizard.total`
  - Calcular restante en vivo al cambiar abono
  - Mostrar/ocultar campos condicionales según método
  - Cargar select de bancos (desde tabla `banco`)
- Validaciones cross-field:
  - `0 ≤ abono ≤ total`
  - si `abono > 0` → método de pago obligatorio
  - si método ∈ {transferencia, pago_movil} → banco + referencia obligatorios

**NO está en alcance**:
- Submit final (TASK-014) — aquí solo se recolecta el estado de pago en memoria
- Paso 4 Resumen

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/pedidos/modals.blade.php` | MODIFY | HTML del paso 3 (pago) |
| `resources/views/admin/pedidos/scripts/main.blade.php` | MODIFY | Lógica paso 3 + validaciones |
| `app/Http/Controllers/PedidoController.php` | READ-ONLY | Verificar que `index()`/`create()` pasa `$bancos` a la vista; si no, añadirlo |

---

## Codebase Contract (Anti-Alucinación)

### Modelo de pago verificado

```php
// app/Models/PagoPedido.php
class PagoPedido extends Model {
    protected $table = 'pago_pedido';
    protected $fillable = ['pedido_id', 'metodo', 'monto', 'banco_id', 'referencia'];
    const METODOS = ['efectivo', 'transferencia', 'pago_movil'];
    public function banco(): BelongsTo;  // tabla `banco`
}

// Schema pago_pedido:
//   metodo     enum('efectivo','transferencia','pago_movil')
//   monto      decimal(10,2) default 0
//   banco_id   FK nullable → banco
//   referencia varchar(255) nullable
```

### Modelo banco verificado

```php
// app/Models/Banco.php
class Banco extends Model {
    protected $table = 'banco';
    // verificar columnas con: grep fillable app/Models/Banco.php
}
```

### Métodos de pago (hardcoded — confirmado en spec)

Los 3 métodos son fijos: `['efectivo', 'transferencia', 'pago_movil']`. NO vienen de una tabla de catálogo. Usar `PagoPedido::METODOS` o hardcodear los 3 chips.

### Patrón de referencia (modal viejo de pedidos)

`resources/views/admin/pedidos/index.blade.php` líneas ~634-700 — el bloque "Pago y Saldo" del modal viejo. Tiene la lógica de:
- total / abono / restante
- métodos de pago como chips/botones
- campos condicionales de transferencia y pago móvil

**Reusar la lógica de cálculo, NO los estilos inline** (deuda CSS — ver `memory/project_pedidos_css_debt.md`).

### Validaciones existentes de referencia

`memory/project_validaciones_js.md` — "campos condicionales de pago en Pedidos" ya estaba documentado. Cross-field: abono vs total.

### Convenciones a respetar

- `docs/conventions/js-validations.md` — patrón blur + validación al avanzar
- Bancos en select: cargar vía Blade (`@foreach($bancos ...)`) o endpoint; preferir Blade si el controller ya los pasa

### NO existe — no referenciar

- ~~tabla `metodo_pago`~~ — NO existe; los métodos son enum hardcoded
- ~~`PagoPedido::scopeActivos()`~~ — no existe
- ~~campo `pedido.efectivo_pagado`~~ — columnas flat viejas; el modelo normalizado usa `pago_pedido`. NO escribir en las columnas flat
- ~~`pago_pedido.fecha_pago`~~ — no existe esa columna; solo `created_at`

---

## Notas de implementación

### Patrón a seguir

```js
PedidoWizard.pago = {
    abono: 0,
    metodo: null,        // 'efectivo' | 'transferencia' | 'pago_movil'
    banco_id: null,
    referencia: null,
};
PedidoWizard.recalcularRestante() {
    const restante = this.total - this.pago.abono;
    $('#ped-restante').val(restante.toFixed(2));
}
PedidoWizard.validarPaso3() {
    if (this.pago.abono < 0 || this.pago.abono > this.total) return false;
    if (this.pago.abono > 0 && !this.pago.metodo) return false;
    if (['transferencia','pago_movil'].includes(this.pago.metodo)
        && (!this.pago.banco_id || !this.pago.referencia)) return false;
    return true;
}
```

### Restricciones clave

- Total readonly siempre — solo refleja `PedidoWizard.total`
- Si el usuario vuelve al paso 2 y cambia productos, al re-entrar al paso 3 el total debe re-sincronizarse
- Restante puede ser 0 (pago completo) — válido
- Abono 0 con método null — válido (pedido sin abono inicial)

### Referencias en el código

- `resources/views/admin/pedidos/index.blade.php:634-700` — bloque pago viejo (lógica)
- `app/Models/PagoPedido.php` — modelo de pago

---

## Criterios de aceptación

- [ ] Total readonly refleja el del paso 2
- [ ] Cambiar abono → restante recalcula en vivo
- [ ] Seleccionar Transferencia/Pago Móvil → aparecen banco + referencia
- [ ] Seleccionar Efectivo → campos condicionales ocultos
- [ ] Validación: abono > total bloquea
- [ ] Validación: abono > 0 sin método bloquea
- [ ] Validación: transferencia sin banco/referencia bloquea
- [ ] Select de bancos poblado desde tabla `banco`
- [ ] Light + dark mode

---

## QA manual

1. Wizard → pasos 1, 2 completos → entrar paso 3
2. Total readonly = total del paso 2
3. Ingresar abono parcial → restante recalcula
4. Seleccionar Efectivo → sin campos extra → continuar OK
5. Seleccionar Transferencia → banco + referencia aparecen → llenar → continuar OK
6. Abono > total → bloquea con mensaje
7. Abono > 0 sin método → bloquea
8. Volver a paso 2, agregar producto, volver a paso 3 → total actualizado
9. Dark mode → repetir

---

## Instrucciones para el ejecutor

1. Lee spec + verifica TASK-012 `completed`
2. Verifica `Banco` fillable + que el controller pasa `$bancos`
3. Header: `Status: in-progress`
4. Rama `feat/pedidos-wizard`
5. Implementa
6. QA manual (6 casos de validación)
7. Mueve a `completed/`, llena Nota
8. NO mergear aún

---

## Nota de Completitud

**Completado por**: santiago
**Fecha**: 2026-05-26
**Commits**: ver rama `feat/pedidos-wizard`
**Notas**: CSS `.ped-metodo-chip` / `.ped-metodo-chips` + dark mode. Paso 3 HTML en `modals.blade.php`: cards Total/Abono/Restante (reutilizando `.pago-kpi-box`), chips de método, bloques condicionales Transferencia/Pago Móvil (reutilizando `.metodo-form-block`). Paso 3 IIFE en `scripts/main.blade.php`: `pedSincronizarTotalPago()` expuesto y hookeado en `showStep(3)`, `pedRecalcularRestante()` en tiempo real, toggle chips, show/hide condicionales, reset al abrir, `window.pedPagoState` con getters para TASK-014. `validateStep(3)` en scaffold: abono ≤ total, método requerido si abono > 0, banco+ref requeridos si transferencia/pago_movil.

**Desviaciones del spec**: ninguna.
