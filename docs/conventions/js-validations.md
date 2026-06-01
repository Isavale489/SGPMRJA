# Validaciones JS (Client-Side)

> Patrón estándar de validación en formularios del panel admin.

## Filosofía

La validación JS **complementa** la validación server-side de Laravel — no la reemplaza. Server-side es la fuente de verdad de seguridad; client-side mejora la UX dando feedback inmediato.

## Patrón estándar

### 1. `novalidate` en todos los `<form>`
Evita que el browser intercepte el submit antes que el JS.

```html
<form id="miForm" novalidate>
```

### 2. Helpers globales en `app.blade.php`

```js
marcarInvalido($campo, mensaje)   // pinta error + muestra mensaje
marcarValido($campo)              // limpia error
limpiarValidacion($campo)         // resetea estado
```

### 3. Handlers `blur` para feedback inmediato

```js
$('#mi-campo').on('blur', function () {
    if (!$(this).val()) {
        marcarInvalido($(this), 'Campo requerido');
    } else {
        marcarValido($(this));
    }
});
```

### 4. Función `validarFormulario*()` que valida todo al submit

```js
function validarFormularioMiModulo() {
    let valido = true;
    if (!$('#campo1').val()) { marcarInvalido($('#campo1'), '...'); valido = false; }
    if (!$('#campo2').val()) { marcarInvalido($('#campo2'), '...'); valido = false; }
    return valido;
}

$('#miForm').on('submit', function (e) {
    if (!validarFormularioMiModulo()) {
        e.preventDefault();
        return false;
    }
});
```

## Módulos donde ya está aplicado

| Módulo | Implementado |
|---|---|
| Usuarios | ✅ |
| Clientes | ✅ |
| Empleados | ✅ |
| Proveedores | ✅ |
| Insumos | ✅ |
| Productos | ✅ |
| Órdenes de Producción | ✅ |
| Cotizaciones | ✅ |
| Pedidos | ✅ |

## Reglas especiales

### Select2 — usar `select2:close` en vez de `blur`
Select2 oculta el `<select>` nativo, así que `blur` no dispara:
```js
$('.insumo-select').on('select2:close', function () { ... });
```

### Validaciones numéricas estrictas
- `costo_estimado`: `val <= 0` (no `val < 0`) — el 0 también es inválido.
- Cantidades: usar `val <= 0` cuando el 0 lógicamente no aplica.

### Campos hidden con error vía SweetAlert
Cuando el "campo" inválido es un `<input type="hidden">` (ej. `producto-id-input` en filas dinámicas de cotización), mostrar el error con SweetAlert en vez de pintar el campo:
```js
Swal.fire({icon: 'warning', title: 'Cada fila debe tener un producto asignado'});
```

### Cross-field validations

| Módulo | Regla |
|---|---|
| Insumos | `stock_minimo ≤ stock_actual` (re-trigger al cambiar `stock_actual`) |
| Órdenes | `fecha_fin ≥ fecha_inicio` |
| Cotizaciones | `fecha_validez ≥ fecha_cotizacion` |
| Pedidos | `fecha_entrega ≥ fecha_pedido`; `abono ≤ total` |

### Validaciones condicionales (Pedidos — métodos de pago)
- Si checkbox de método activo → monto > 0.
- Si método es `transferencia` o `pago_movil` → banco + referencia obligatorios.
- `.pago-monto-input` blur: extrae método del id (`pago-{metodo}-monto`).

### Productos — sanitización en tiempo real
- `#tipo-prefijo-field`: solo letras, auto-uppercase.
- Guard de edit mode antes del AJAX de validación de unicidad.
- `#tipoForm` tiene su propia `validarFormularioTipo()` separada de `validarFormularioProducto()`.

## Cómo aplicar el patrón a un módulo nuevo

1. Añadir `novalidate` al `<form>`.
2. Para cada campo requerido: añadir handler `blur` que use `marcarInvalido/marcarValido`.
3. Crear `validarFormularioMiModulo()` que devuelve boolean.
4. Cablear el submit del form: `if (!validarFormularioMiModulo()) return false;`.
5. Si hay Select2, usar `select2:close` en vez de `blur`.
6. Si hay relaciones entre campos, añadir re-trigger del campo dependiente cuando cambia el otro.
