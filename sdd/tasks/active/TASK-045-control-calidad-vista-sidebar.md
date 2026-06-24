# TASK-045: Vista `/calidad` (DataTable + modal de inspección) + sidebar

**Feature**: FEAT-006 — control-calidad
**Spec**: `sdd/specs/control-calidad.spec.md`
**Status**: pending
**Priority**: high
**Esfuerzo estimado**: L
**Depends-on**: TASK-044
**Assigned-to**: unassigned

---

## Contexto
Módulo 4 del spec: la pantalla del Supervisor. Lista las órdenes finalizadas
pendientes de inspección y permite registrar el veredicto. Cablea el placeholder
muerto del sidebar.

## Scope
- Crear `resources/views/admin/calidad/index.blade.php`: card transactional +
  DataTable server-side (`calidad.data`) + modal de inspección.
- Modal de inspección (`atlantico-modal atlantico-modal--op`): muestra datos de
  la orden (producto, pedido, cantidad producida); campos cantidad_inspeccionada,
  cantidad_aprobada, cantidad_rechazada (autocalcular rechazada = inspeccionada −
  aprobada), `resultado` (derivar: rechazada>0 → rechazado), observaciones
  (obligatorio si no aprobado). Validación JS blur+submit; POST a `calidad.inspeccionar`.
- Historial de inspecciones de la orden (vía `calidad.detalle`).
- Enlazar el sidebar: reemplazar el `href="#"` placeholder por `route('calidad.index')`.

**NO está en alcance**: permisos (TASK-046), lógica backend (ya en TASK-043/044).

## Archivos a crear / modificar
| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/calidad/index.blade.php` | CREATE | Listado + modal inspección |
| `resources/views/admin/layouts/sidebar.blade.php` | MODIFY | Enlace real a `calidad.index` |
| `public/assets/css/custom.css` | MODIFY (si hace falta) | Badges de resultado, sin estilos inline |

## Codebase Contract (Anti-Alucinación)

### Hechos verificados
```blade
{{-- resources/views/admin/layouts/sidebar.blade.php:575-581 — placeholder ACTUAL --}}
<li class="nav-item">
    {{-- TODO: Crear ruta y controlador para Control de Calidad --}}
    <a href="#" class="nav-link {{ request()->is('calidad*') ? 'active' : '' }}">
        <i class="ri-shield-check-line me-1"></i> Control de Calidad
    </a>
</li>
{{-- → cambiar href="#" por {{ route('calidad.index') }} --}}
```
Rutas disponibles (de TASK-044): `calidad.index`, `calidad.data`, `calidad.detalle`, `calidad.inspeccionar`.

### Convenciones a respetar (ver `docs/conventions/`)
- `docs/conventions/modal-system.md` — `atlantico-modal atlantico-modal--op` (transaccional, header cyan), `data-bs-backdrop="static"`.
- `docs/conventions/js-validations.md` — validación blur + submit, IIFE en `@push('scripts')`.
- `reference_ui_standards` — DataTable server-side `dt-transactional`, card por sección.
- AtlanticoGuard global (cambios sin guardar) — usar `#id-field` si aplica al modal de edición.
- Reusar el partial/JS de municipios o repeater solo si aplica (no es el caso aquí).

### NO existe — no referenciar
- ~~`resources/views/admin/sidebar.blade.php`~~ — la ruta real es `resources/views/admin/layouts/sidebar.blade.php`.
- ~~CSS en `public/css/`~~ — el admin usa `public/assets/css/custom.css`.

## Notas de implementación
- Sin estilos inline (todo en `custom.css`).
- DataTable: server-side, `autoWidth:false`, `lenguajeData` global.
- Badges de resultado: aprobado (verde), rechazado (rojo), observado (ámbar).

## Criterios de aceptación
- [ ] `/calidad` carga; DataTable lista órdenes finalizadas pendientes.
- [ ] Modal de inspección registra y refresca la tabla; la orden inspeccionada sale de "pendientes" (o reaparece en producción si fue rechazo).
- [ ] Sidebar enlaza a `calidad.index` (placeholder eliminado) y marca activo.
- [ ] Dark mode OK (modal, tabla, badges) sin estilos inline.

## QA manual
1. Login Supervisor → sidebar "Control de Calidad" navega a `/calidad`.
2. Inspeccionar conforme → sale de la lista.
3. Inspeccionar con rechazadas → vuelve a producción (verificar en módulo Órdenes).
4. Validaciones JS: rechazadas>0 sin motivo bloquea el submit.
5. Dark mode.
