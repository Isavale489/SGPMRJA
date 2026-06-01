# Colores del Sidebar por Sección

> El sidebar refleja el color estándar de cada sección (Maestros / Operativa / Reportes) tanto en el ítem activo como en el header del grupo cuando un subítem está activo.

## Estructura Blade

Cada `<li>` padre del sidebar lleva dos clases:
- Clase fija de sección: `section-maestros`, `section-operativa`, o `section-reportes`.
- Clase dinámica PHP: `section-is-active` cuando la ruta actual pertenece a esa sección.

```html
<li class="nav-item section-maestros {{ request()->is('clientes*', 'productos*', 'proveedores*', 'insumos*', 'empleados*') ? 'section-is-active' : '' }}">

<li class="nav-item section-operativa {{ request()->is('cotizaciones*', 'pedidos*', 'ordenes*', 'calidad*', 'inventario*', 'garantias*') ? 'section-is-active' : '' }}">

<li class="nav-item section-reportes {{ request()->is('reportes*') ? 'section-is-active' : '' }}">
```

## CSS Light mode (inline en `sidebar.blade.php`)

| Sección | Texto activo | Border-left | Background |
|---|---|---|---|
| Maestros | Navy `#1e3c72` | (mismo) | `rgba(30,60,114,0.12)` |
| Operativa | Emerald `#059669` | `#10b981` | `rgba(16,185,129,0.12)` |
| Reportes | Sky `#0369a1` | `#0ea5e9` | `rgba(14,165,233,0.10)` |

La clase combinada `.section-***.section-is-active > .menu-link` colorea el header del grupo cuando un subítem está activo.

## CSS Dark mode (en `custom.css` — sección DARK MODE LAYOUT)

| Sección | Texto activo | Background |
|---|---|---|
| Maestros | `#4e7ac7` | `rgba(30,60,114,0.28)` |
| Operativa | `#10b981` | `rgba(16,185,129,0.22)` |
| Reportes | `#38bdf8` | `rgba(14,165,233,0.20)` |

El fallback genérico `[data-bs-theme="dark"] .nav-link.active` (azul Bootstrap) sigue activo para el Dashboard.

## Consistencia con el resto del sistema

Los colores del sidebar son los mismos que usan:
- Cards: `card-maestros`, `card-transactional`, `card-reportes`
- DataTables: clases `dt-transactional`, `dt-reportes`
- Modales: `atlantico-modal` (navy) y `atlantico-modal--op` (cyan)

## Cómo añadir una sección nueva

1. Definir el slug semántico: `section-<nombre>`.
2. Añadirla al `<li>` padre correspondiente en `sidebar.blade.php`.
3. Crear las reglas CSS light mode dentro del mismo bloque que las existentes en `sidebar.blade.php`.
4. Añadir las reglas dark mode al final de la sección "DARK MODE — LAYOUT" en `custom.css`.
5. Mantener consistencia de paleta con el card y DataTable de esa misma sección.
