# Modales anidados (Bootstrap 5)

> **TL;DR:** Bootstrap 5 no soporta modales apilados. El fix global ya está aplicado en `app.blade.php`. **NO reimplementar** por módulo.

## El problema

Bootstrap 5 no soporta modales apilados nativamente (confirmado en su documentación oficial). Al abrir un modal hijo sobre un padre y cerrar el hijo con X o Cancelar, **el padre también se cerraba** porque Bootstrap remueve `modal-open` del body de forma incondicional.

## La solución (ya implementada globalmente)

**Ubicación:** `resources/views/admin/layouts/app.blade.php` — después del handler `show.bs.modal` de limpieza de validaciones.

Usa la clase utilitaria `modal-hidden-temp` (definida en `custom.css`, aplica `opacity: 0` + `pointer-events: none`). Al abrir un modal sobre otro:

1. `show.bs.modal` detecta modales ya abiertos y les aplica `modal-hidden-temp`.
2. Guarda referencia al padre en `$.data('parentModals')` del hijo.
3. Al cerrar el hijo (`hidden.bs.modal`): quita la clase del padre y re-añade `modal-open` al body.

La instancia del modal padre **nunca se cierra** — queda invisible pero viva en Bootstrap.

## Qué NO hacer

- **NO** agregar listeners individuales de `modal-hidden-temp` en módulos nuevos — el fix global ya los cubre.
- **NO** hackear z-index para modales anidados — el fix usa ocultación, no apilamiento.
- **NO** copiar el patrón antiguo de Inventario (`movimientos/scripts/main.blade.php` líneas ~237-244) — esos listeners son redundantes con el fix global; se pueden eliminar en refactor futuro.

## Módulos protegidos automáticamente

- **Empleados**: `showModal → addDepartamentoModal / addCargoModal / gestionDepartamentosModal → formDepartamentoModal`
- **Productos**: `showModal → addTipoModal`
- **Inventario**: `createModal → modalAddInsumo`
- **Cualquier futuro módulo** con modales anidados — protegido sin código adicional.

## Cómo verificar que sigue funcionando

Abrir un modal padre, abrir un sub-modal con el botón "+", dar Cancelar. El padre debe quedar intacto con todos los datos que tenía antes.
