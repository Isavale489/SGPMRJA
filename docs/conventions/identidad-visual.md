# Identidad visual por sección

> El panel tiene una identidad de color por grupo de módulos, heredada del panel Blade:
> **navy** para Gestión General, **esmeralda** para Gestión Operativa y **sky** para Consultas y Reportes.
> Propuesta visual con maqueta interactiva: [`docs/propuesta_identidad_visual.html`](../propuesta_identidad_visual.html).

## Cómo funciona

```
config/secciones.php            módulo → sección (fuente única)
        │
HandleInertiaRequests           prop compartida `seccion` = sección de la ruta actual (seccionDeRuta())
        │
AppLayout (useLayoutEffect)     <html data-seccion="operativa">
        │
plataforma.css                  [data-seccion] redefine --seccion*, --primary y --ring
        │
componentes                     bg-seccion-fondo, text-seccion, bg-primary… sin saber de secciones
```

- **La sección va en `<html>`**, no en el layout: los diálogos, menús y tooltips que Radix monta fuera del árbol también la heredan.
- **Rutas sin sección** (dashboard, perfil y demás rutas `comunes`): no hay `data-seccion` y se usan los valores de marca, que son navy.
- **Cualquier elemento puede pintarse con otra sección** poniendo su propio `data-seccion`. Así se hace en cada grupo del menú, en las tarjetas de la matriz de Seguridad y en los grupos del hub de reportes. `data-seccion="marca"` fija el azul de marca en lo que no es de ninguna sección (el enlace «Inicio» del menú).

## Secciones

| Clave | Sección | Módulos | Acento claro / oscuro | Fondo oscuro (cabecera de tabla) |
|---|---|---|---|---|
| `maestros` | Gestión General | clientes, empleados, proveedores, productos, insumos y catálogos | `#1e3c72` / `#7d9be0` | `#1e3c72` |
| `operativa` | Gestión Operativa | cotizaciones, pedidos, órdenes, calidad, compras, movimientos | `#047857` / `#34d399` | `#064e3b` (oscuro `#065f46`) |
| `reportes` | Consultas y Reportes | reportes | `#0369a1` / `#38bdf8` | `#0c4a6e` (oscuro `#0369a1`) |
| `admin` | Administración | configuración, usuarios | igual que Maestros | igual que Maestros |

Los colores que llevan texto blanco encima cumplen contraste AA (≥ 4.5:1). Por eso el esmeralda de Operativa es `#047857` y no el `#10b981` del Blade, que queda como acento.

## Tokens

| Token | Uso | Utilidad Tailwind |
|---|---|---|
| `--seccion` | Texto e íconos del grupo | `text-seccion` |
| `--seccion-acento` | Bordes, rieles, tintes de fondo | `border-seccion-acento`, `bg-seccion-acento/12` |
| `--seccion-fondo` | Superficies oscuras con texto blanco | `bg-seccion-fondo` |
| `--seccion-degradado` | Encabezado de diálogos, ícono del título | `bg-seccion-degradado` |
| `--primary`, `--ring` | Botón principal, enlaces, casillas, foco (siguen a la sección) | `bg-primary`, `text-primary`, `accent-primary`… |
| `--topbar` | Barra superior navy | `bg-topbar` |
| `--marca` | Azul fijo que no cambia con la sección (series de gráficos) | `text-marca`, `bg-marca` |
| `--info`, `--especial` | Estados «En proceso» y «Convertida» | `text-info`, `bg-especial/12` |

## Dónde se ve (sin tocar las páginas)

| Pieza | Qué lleva | Dónde está |
|---|---|---|
| Barra superior | Navy sólido, isla oscura (`class="dark"`) con píldoras de vidrio (`data-pildora`) | `layouts/app-layout.tsx` |
| Título de página | Ícono en degradado (el del enlace del menú) + ceja con el nombre de la sección | `layouts/app-layout.tsx` |
| Menú | Ícono de cada sección en su color; el enlace activo con tinte y riel izquierdo | `components/app/sidebar.tsx` |
| Tablas de listado | Cabecera en `--seccion-fondo`, texto blanco, borde de acento; filas rayadas | `CabeceraSeccion` y `CuerpoRayado` (`components/app/tabla-seccion.tsx`); ya las usa `TablaServidor` |
| Diálogos | Todo `DialogHeader` que abre un `DialogContent` se vuelve franja en degradado. Un `EstadoBadge` dentro de la franja pasa a fondo blanco con el color del estado para fondo claro (`data-tono`) | CSS en `plataforma.css` (selectores `data-slot`) |
| Botones, enlaces, foco, pasos del asistente | `--primary` y `--ring` de la sección | tokens |
| Barra de filtros de un listado | Tinte en degradado y riel izquierdo de la sección; controles en color de tarjeta | `BarraFiltros` (`components/app/barra-filtros.tsx`) |
| Menú ⋮ de acciones | Ícono en cajita teñida con el color de la acción (`tono` en `DropdownMenuItem`) | `data-tono` en `plataforma.css` |
| Cliente y creador en asistentes | Píldora con avatar o iniciales en degradado, rótulo y nombre con documento | `ChipPersona` (`components/app/chip-persona.tsx`) |
| Detalle de Compra | Vista de documento: membrete, datos con línea punteada, barra de totales con «Total a pagar» destacado | `pages/Compras/detalle-compra.tsx` |
| Detalle de Orden | Hero: foto del producto (o ícono en degradado), nombre, variante y avance | `pages/Ordenes/detalle-orden.tsx` |

## Reglas

1. **Un módulo nuevo va en `config/secciones.php`.** `SeccionesTest` falla si queda sin sección o en dos.
2. **Una sección nueva del menú declara `'seccion'`** en `config/navegacion.php`. `SeccionesTest` verifica que coincida con la sección de cada uno de sus enlaces.
3. **Los estados nunca usan `primary`.** `primary` cambia con la sección, y un «En Proceso» no puede verse verde en Operativa. Usa `warning`, `info`, `success`, `destructive` y `especial` (ver `EstadoBadge`).
4. **Gráficos:** `usePaleta()`, `colorEstado()` y `colorEficiencia()` de `components/app/grafico.tsx` leen los mismos tokens que los badges. Nada de hex en las opciones de AG Charts. Solo se leen tokens que no cambian con la sección, porque el gráfico se arma antes de que el layout la aplique.
5. **Las tablas de detalle** (dentro de diálogos o formularios) llevan la cabecera clara normal. `CabeceraSeccion` es solo para listados.
6. **Dentro de la franja de un diálogo**, lo que no sea título, descripción o `EstadoBadge` va en blanco (`text-white`, `bg-white/20`): los tintes de `primary` o de estado no se leen sobre el degradado.
7. **Las confirmaciones** (`AlertDialog`: `ConfirmarPeligro`, `confirmar()`) no llevan franja: su foco es la pregunta.
8. **Toda acción de un menú ⋮ lleva `tono`:** `editar` (verde), `peligro` (rojo: eliminar), `aviso` (ámbar: anular, cancelar, inhabilitar), `restaurar` (celeste: restaurar, reactivar, habilitar), `documento` (gris: PDF) o `principal` (color de la sección: convertir, aprobar, procesar). Es un color por acción, no por sección: «Editar» es verde en todo el sistema.
9. **Nada de clases de paleta de Tailwind** para identidad (`text-emerald-600`, `bg-sky-500/10`…). Usa `data-seccion` + los tokens.

## Verificación

- `tests/Feature/SeccionesTest.php` comprueba la coherencia de `config/secciones.php`, `config/navegacion.php` y `config/reportes.php`, y la prop `seccion` de cada página.
- `tests/e2e/identidad.spec.ts` comprueba el color real que pinta el navegador: cabecera de tabla, botón principal, menú, franja del diálogo, modo oscuro y navegación sin recarga.
