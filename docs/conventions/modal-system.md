# Sistema de Modales

> Convención visual del proyecto para todos los modales del panel admin.

## Clases disponibles

### `.atlantico-modal` — base institucional (Maestros, Navy)
- Header: `linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)`
- Título blanco, btn-close invertido, opacity 0.85
- Footer: `#f8f9fa` light / `#1f2229` dark
- Border-radius: 0.75rem, shadow institucional

### `.atlantico-modal--op` — modificador para Transacciones (Cyan)
- Header: `linear-gradient(135deg, #10b981 0%, #0891b2 50%, #0d6efd 100%)`
- Se usa **junto a** `.atlantico-modal`:
  ```html
  <div class="modal fade atlantico-modal atlantico-modal--op">
  ```

### `.utility-modal-header` — modales utilitarios nivel 2
- Aplicada en el `div.modal-header` (no en el wrapper)
- Background: `#132649` (navy oscuro fijo)
- Usar para modales de búsqueda/selección anidados

## Mapeo módulo → clase

| Tipo de módulo | Clase |
|---|---|
| Maestros (Clientes, Empleados, Insumos, Productos, Proveedores, Usuarios) | `atlantico-modal` |
| Inventario (Movimientos, Alertas) | `atlantico-modal` |
| Transacciones (Cotizaciones, Pedidos, Órdenes, Producción Diaria) | `atlantico-modal atlantico-modal--op` |
| Catálogos de selección anidados (logo, color, talla, ubicación, productos) | `utility-modal-header` o inline `#132649` |
| Notificaciones del sistema | Bootstrap default |

## Dark mode

Para superar la regla global `[data-bs-theme="dark"] .modal-header` que vive en `app.blade.php` (carga después de `custom.css` y tiene especificidad 0,2,0), las reglas dark del sistema usan **3 clases de especificidad (0,3,0)**:

```css
[data-bs-theme="dark"] .atlantico-modal .modal-header { ... }       /* navy */
[data-bs-theme="dark"] .atlantico-modal--op .modal-header { ... }   /* cyan */
[data-bs-theme="dark"] .atlantico-modal .modal-footer { ... }       /* #1f2229 */
```

**Regla**: cualquier override dark mode futuro para modales debe seguir este patrón de especificidad para no ser aplastado por la regla global.

## Modales anidados

El proyecto tiene un fix global para modales apilados (Bootstrap 5 no los soporta nativamente). Ver [`nested-modals.md`](nested-modals.md) — **NO reimplementar** por módulo.
