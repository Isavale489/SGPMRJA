# Patrón Wizard Multi-paso (`.wiz-*`)

> Patrón unificado para formularios complejos divididos en pasos secuenciales con stepper visual, validación por paso y navegación adelante/atrás. Las clases `.wiz-*` y su CSS son **compartidos** entre todos los wizards del proyecto (cotizaciones y pedidos hoy). No dupliques estructura ni estilos: reutiliza las clases.

## Cuándo usar este patrón

- Formularios largos que se benefician de dividirse en etapas (cliente → productos → pago → resumen).
- Cuando hay validación incremental: no dejar avanzar al paso siguiente hasta que el actual esté completo.
- Cuando el mismo modal sirve para **crear**, **editar** y/o **completar desde otra entidad** (con campos protegidos según el modo).

Para un formulario simple de uno o dos campos, **no** uses wizard: un modal `atlantico-modal` normal (ver [`modal-system.md`](modal-system.md)) es suficiente.

## Arquitectura de archivos

El módulo se parte en archivos delgados (mismo patrón que cotizaciones y pedidos):

```
admin/<modulo>/index.blade.php            ← ~150-200 líneas: breadcrumb + card (DataTable + filtros) + @includes
admin/<modulo>/modals.blade.php           ← el wizard (#showModal) + viewModal + modales auxiliares
admin/<modulo>/scripts/listado.blade.php  ← DataTable init, filtros, handlers ver/editar/eliminar
admin/<modulo>/scripts/main.blade.php     ← lógica del wizard (navegación + cada paso)
```

`index.blade.php` solo orquesta:

```blade
@section('content')
    {{-- breadcrumb + card con DataTable + filtros unificados --}}
    @include('admin.<modulo>.modals')
@endsection

@push('scripts')
    {{-- script tags CDN --}}
    @include('admin.<modulo>.scripts.listado')
    @include('admin.<modulo>.scripts.main')
@endpush
```

## Estructura HTML del stepper

```
.modal.atlantico-modal.atlantico-modal--op.wiz-modal#showModal
  └── .wiz-stepper-wrapper
        └── .wiz-stepper[role="tablist"]
              ├── button.wiz-step-marker.is-active[data-step="1"]
              │     ├── span.wiz-step-dot           ← número del paso
              │     └── span.wiz-step-label         ← nombre del paso
              ├── span.wiz-step-line > span.wiz-step-line-fill[data-line="1"]
              ├── button.wiz-step-marker[data-step="2"] ...
              └── ... (un marker por paso, una línea entre cada par)
  └── form#<prefijo>Form
        ├── .wiz-wizard-body
        │     ├── section.wiz-step-content.is-active#<pref>-wiz-step-1[data-step="1"]
        │     │     ├── .wiz-step-header (.wiz-step-tag + .wiz-step-title + .wiz-step-desc)
        │     │     └── {{-- campos del paso 1 --}}
        │     ├── section.wiz-step-content#<pref>-wiz-step-2[data-step="2"] hidden
        │     └── ... (una section por paso; las inactivas con `hidden`)
        └── .wiz-wizard-footer
              ├── .wiz-wizard-footer-info (.wiz-wizard-step-info — contador "Paso N de M")
              └── .wiz-wizard-footer-actions
                    ├── button#btn-<pref>-prev   (.wiz-wizard-btn-prev)
                    ├── button#btn-<pref>-next   (.wiz-wizard-btn-next)
                    └── button#<pref>-wiz-add-btn (.wiz-wizard-btn-submit) — solo visible en el último paso
```

Convención de IDs por paso: `#<prefijo>-wiz-step-N` para la `section` y `data-step="N"` tanto en la section como en el `.wiz-step-marker`. El marker usa `aria-controls` apuntando a la section (accesibilidad).

## Clases CSS (en `public/assets/css/custom.css`)

| Clase | Propósito |
|---|---|
| `.wiz-modal` | Marca el modal como wizard (alto fijo, body scrollable) |
| `.wiz-stepper-wrapper` / `.wiz-stepper` | Contenedor del stepper visual superior |
| `.wiz-step-marker` | Botón de paso; `.is-active` = paso actual, `.is-complete` = ya superado |
| `.wiz-step-dot` / `.wiz-step-label` | Número y etiqueta dentro del marker |
| `.wiz-step-line` / `.wiz-step-line-fill` | Línea de progreso entre pasos (fill al 100% cuando el paso quedó atrás) |
| `.wiz-step-content` | Una por paso; `.is-active` + atributo `hidden` controlan visibilidad |
| `.wiz-step-header` / `.wiz-step-tag` / `.wiz-step-title` / `.wiz-step-desc` | Encabezado de cada paso |
| `.wiz-wizard-body` / `.wiz-wizard-footer` / `.wiz-wizard-footer-actions` | Layout body + footer fijo |
| `.wiz-wizard-btn-prev` / `-next` / `-submit` | Botones de navegación del footer |
| `.wiz-stepper-side` (`--left` / `--right`) | Dos columnas laterales de igual ancho que centran el stepper; la izquierda aloja el chip de cliente |
| `.wiz-client-banner` (+ `-label` / `-avatar` / `-main` / `-name` / `-sub` / `-doc`) | Chip-píldora ("Para: <cliente>") que mantiene visible **a quién** es la cotización/pedido en todos los pasos |

### Chip de cliente persistente (`.wiz-client-banner`)

Vive **dentro de `.wiz-stepper-wrapper`**, en el gutter libre a la izquierda del stepper. El wrapper usa un layout de 3 columnas (`.wiz-stepper-side--left` con el chip + `.wiz-stepper` + `.wiz-stepper-side--right` vacío); las dos columnas laterales son `flex: 1 1 0` → el stepper queda perfectamente centrado aunque el chip aparezca o no. En `≤991px` el chip baja a una fila centrada bajo el stepper.

Se alimenta de la **misma fuente** que la card del paso 1: la función `<pref>MostrarTarjetaCliente(persona)` rellena tanto la card grande como los `#<pref>-banner-*`. Reglas de visibilidad (en `showStep` + helper `<pref>ActualizarBannerCliente`):

- **Oculto en el paso 1** (ahí ya está la card grande de selección) y cuando no hay cliente.
- **Visible en pasos 2+** si hay cliente seleccionado.
- Se oculta al limpiar el cliente (`<pref>ResetearCliente`).

Avatar (iniciales + color por hash), nombre completo (sin truncar, envuelve si es largo) y documento se reusan de la card; el badge de rol se omite a propósito (ya está en la card del paso 1). IDs: `#cot-banner-*` / `#ped-banner-*`.

**Chip "Creada/Creado por" (gutter derecho).** Simétrico al de cliente, en `.wiz-stepper-side--right`. Muestra el usuario logueado (`Auth::user()->name` + `->avatar_url` como imagen) renderizado server-side. Visibilidad: **todos los pasos, en crear y editar**. En **crear** muestra al usuario logueado (`window.<pref>CreadorDefault`, render server-side); en **editar** se hidrata con el creador real (`data.creador.{name,avatar_url}` que devuelve el `show()`), y `showStep` solo restablece al usuario logueado cuando `!isEditMode()` (para no arrastrar el creador de una edición previa al abrir en modo crear).

Para que el creador sea fiable, `user_id` se setea con `Auth::id()` **solo al crear** y NO se sobrescribe en `actualizar()` (queda fijo como creador original). Los `show()` de Cotizacion/Pedido eager-cargan `user:id,name,avatar` y exponen un objeto `creador`. IDs `#cot-creador-banner` / `#ped-creador-banner`. Reusa `.wiz-client-banner` + `.wiz-client-banner-avatar--img`.

Todas tienen dark mode completo en el mismo archivo. Las clases son **agnósticas del dominio** — reutilízalas tal cual. Para contenido específico del módulo usa clases propias con prefijo del módulo (p. ej. `.cot-kpi`, `.ped-product-card`).

## Patrón JS de navegación

El scaffold vive en una IIFE al inicio de `scripts/main.blade.php`. Esqueleto (de pedidos):

```js
(function () {
    'use strict';
    var TOTAL_STEPS = 4;
    var currentStep = 1;

    function isEditMode() { return !!$('#ped-wiz-id-field').val(); }

    function showStep(n) {
        n = Math.max(1, Math.min(TOTAL_STEPS, n));
        currentStep = n;
        // 1. mostrar la section activa, ocultar el resto (is-active + hidden)
        // 2. markers: is-active (==n), is-complete (<n), aria-selected
        // 3. líneas de progreso: width 100% si line < n
        // 4. contador "Paso N de M"
        // 5. visibilidad de botones del footer (prev si n>1; en el último paso oculta next y muestra submit)
        // 6. hooks por paso (p. ej. sincronizar total al entrar al paso de pago, render resumen en el último)
    }

    function validateStep(n) {
        // valida SOLO los campos del paso n; muestra Swal.fire warning y return false si algo falla
        // return true si el paso está OK
    }

    function nextStep() { if (currentStep < TOTAL_STEPS && validateStep(currentStep)) showStep(currentStep + 1); }
    function prevStep() { if (currentStep > 1) showStep(currentStep - 1); }

    $('#btn-ped-next').on('click', nextStep);
    $('#btn-ped-prev').on('click', prevStep);

    // Click en marker: retroceso libre, avance valida cada paso intermedio
    $(document).on('click', '.wiz-step-marker[data-step]', function () {
        var target = parseInt(this.dataset.step, 10);
        if (target < currentStep) { showStep(target); return; }
        for (var s = currentStep; s < target; s++) if (!validateStep(s)) return;
        showStep(target);
    });

    // Lifecycle: arrancar en paso 1 al abrir
    var $wizModal = $('#showModal');
    $wizModal.on('show.bs.modal', function () { showStep(1); });
    $wizModal.on('hidden.bs.modal', function () { currentStep = 1; });

    // API global para que otros scripts abran/naveguen el wizard
    window.pedWizard = { show: showStep, next: nextStep, prev: prevStep };
})();
```

### Reglas clave

- **Navegación hacia atrás siempre libre; hacia adelante valida** cada paso intermedio.
- **Reset de modo crear en `show.bs.modal`**, *gated* por `!isEditMode()` (no resetear si se abrió en edición). Cada paso registra su propio handler `show.bs.modal` para limpiar su sección.
- **`window.<pref>Wizard`** expone `show/next/prev` para que `listado` u otros scripts abran el wizard programáticamente (p. ej. `window.pedAbrirEnEdit(id)` hidrata y luego `wizard.show(1)`).
- **Modos**: crear (limpio), editar (campos protegidos + submit PUT), completar-desde-X (hidrata pasos y abre en el paso relevante con banner "Datos heredados"). El modo se detecta por un hidden field de id (`#<pref>-wiz-id-field`) y/o un flag de cotización.
- **Hooks por paso** dentro de `showStep` para recálculos (totales, resumen) al entrar a un paso.

## Implementaciones de referencia

| Módulo | Pasos | Archivos |
|---|---|---|
| Cotizaciones | Cliente · Productos · Resumen (3) | `admin/cotizaciones/{index, modals}.blade.php`, `scripts/main.blade.php` |
| Pedidos | Cliente · Productos · Pago · Resumen (4) | `admin/pedidos/{index, modals}.blade.php`, `scripts/{listado, cotizacion_selection, main}.blade.php` |

Ambos comparten las clases `.wiz-*` y su CSS. Si evolucionas el scaffold (CSS o JS base), verifica que **ambos** sigan funcionando.

## Relacionados

- [`modal-system.md`](modal-system.md) — el wizard usa el shell `atlantico-modal atlantico-modal--op`
- [`nested-modals.md`](nested-modals.md) — modales auxiliares (agregar producto, crear cliente) se abren sobre el wizard
- [`js-validations.md`](js-validations.md) — patrón de validación por campo (`novalidate` + blur + Swal)
- [`ux-search-filters.md`](ux-search-filters.md) — filtros del listado que convive con el wizard
