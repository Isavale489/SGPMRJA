---
type: feature
base_branch: enmanuel
---

# Feature Specification: Rollout del Patrón de Filtros Unificados

**Feature ID**: FEAT-001
**Fecha**: 2026-05-20
**Autor**: Emmanuel
**Status**: shipped
**Assigned-to**: Santiago
**Versión objetivo**: Sprint actual

---

## 1. Motivación y requisitos de negocio

### Planteamiento del problema

El proyecto tiene establecido el patrón visual unificado de **búsqueda + filtros colapsables** (clases `navy-filter-header`, `advanced-filters-wrapper`, etc.) — documentado en `docs/conventions/ux-search-filters.md`. Está aplicado en **7 módulos** (clientes, proveedores, productos, empleados, insumos, departamentos, cargos), pero **7 módulos siguen con la barra de búsqueda simple vieja** y deben migrarse:

| Módulo | Sección |
|---|---|
| `cotizaciones/` | Gestión Operativa |
| `pedidos/` | Gestión Operativa |
| `ordenes/` | Gestión Operativa |
| `produccion/diaria/` | Gestión Operativa |
| `inventario/movimientos/` | Gestión Operativa |
| `inventario/alertas/` | Gestión Operativa |
| `users/` | Sistema |

`inventario/reporte/` queda **fuera de alcance** — pertenece a la sección Reportes (paleta sky `card-reportes`), no operativa.

Esto rompe la consistencia visual del sistema y deja a los módulos operativos sin filtros avanzados que ayudarían al usuario a navegar volúmenes crecientes de datos (cotizaciones, pedidos, órdenes).

### Objetivos

1. Migrar los 7 módulos pendientes al patrón `navy-filter-header` unificado.
2. Añadir filtros relevantes a cada módulo según su dominio (estados, fechas, empleados, tipos, etc.).
3. Mantener consistencia con el patrón ya validado en módulos maestros.
4. Botón "Historial" donde aplique (módulos con SoftDeletes activo).

### Fuera de alcance (No-Goals)

- **NO** rediseñar los DataTables ni cambiar columnas.
- **NO** modificar el patrón base ya establecido en `custom.css` (solo aplicar las clases existentes).
- **NO** tocar `inventario/reporte/` — es sección Reportes (paleta sky), no operativa.
- **NO** introducir librerías nuevas — todo se hace con lo ya disponible (jQuery + DataTables + Bootstrap 5).
- **NO** cambiar la lógica de los controllers más allá de aceptar filtros adicionales en `$request`.

---

## 2. Diseño arquitectónico

### Resumen

Aplicar el patrón estándar documentado en `docs/conventions/ux-search-filters.md` a cada módulo operativo. Cada módulo recibe:

1. **HTML**: reemplazar el `card-header` actual (con `search-box`) por la estructura `advanced-filters-wrapper.navy-theme` + `navy-filter-header` + cuerpo colapsable.
2. **JS**: añadir handlers de filtros + `updateFilterBadge()` + `clear-filters`.
3. **Controller** (si filtro es server-side): aceptar nuevos parámetros `filter_*` vía `$request->filled(...)`.

### Diagrama de la migración por módulo

```
ANTES                                  DESPUÉS
─────────────────────────             ─────────────────────────────────────
card-header                            advanced-filters-wrapper.navy-theme
  └── search-box                        └── navy-filter-header.is-collapsed
        └── input#custom-search-input         ├── navy-header-search
                                              │     └── input#custom-search-input
                                              ├── navy-header-divider
                                              └── navy-filter-btn
                                                    └── navy-filter-badge#active-filter-count
                                       └── collapse#filters-collapse-body
                                              └── navy-filter-body
                                                    ├── selects.navy-filter-select
                                                    └── btn#btn-clear-filters
                                       (botón historial si aplica, fuera del wrapper)
```

### Puntos de integración

| Componente existente | Tipo de integración | Notas |
|---|---|---|
| `custom.css` § "FILTROS NAVY" | reusar | clases ya existen, no añadir nuevas |
| `app.blade.php` § `lenguajeData` | reusar | DataTable language ya centralizado |
| `CotizacionController::getCotizaciones()` | modificar | añadir `where` condicionales |
| `PedidoController::getPedidos()` | modificar | añadir `where` condicionales |
| `OrdenProduccionController::getOrdenes()` | modificar | añadir `where` condicionales |
| `ProduccionDiariaController` | modificar | añadir `where` condicionales |
| `MovimientoInventarioController` | modificar | añadir `where` condicionales |
| `InventarioAlertaController` | revisar | quizás solo visual |
| `UserController` | modificar | añadir `where` condicionales (`role`, `estado`) |

### Filtros confirmados por módulo

| Módulo | Filtros | Mecanismo |
|---|---|---|
| Cotizaciones | Estado (Pendiente/Aprobada/Vencida/Convertida/Cancelada) + Cliente (autocomplete persona-search) | Server-side |
| Pedidos | Estado (Pendiente/Completado/Cancelado) + Cliente | Server-side |
| Órdenes | Estado (Pendiente/En Proceso/Finalizado/Cancelado) + Departamento | Server-side |
| Producción Diaria | Empleado + Rango de fecha (2 inputs `<input type="date">` desde/hasta) | Server-side |
| Inventario Movimientos | Tipo (Entrada/Salida) + Insumo + Rango de fecha | Server-side |
| Inventario Alertas | (sin filtros adicionales — vista ya filtra `stock_actual <= stock_minimo`) | Solo visual |
| Users | Rol (Administrador/Supervisor/Usuario) + Estado (Activo/Inactivo) | Server-side |

### UI / Vistas

- Tipo de card: `card-transactional` (sección Transacciones) — ver `AGENTS.md` § Estándares visuales
- Tipo de modal: `atlantico-modal atlantico-modal--op` (transaccional) — ver `docs/conventions/modal-system.md`
- DataTable: clase `dt-transactional`, anchos por `nth-child`, `lenguajeData` global
- Botones existentes (Agregar / Exportar PDF / Historial) se preservan; se mueven al header del wrapper o quedan a la derecha del card-title como hoy.

---

## 3. Desglose por módulos

> Cada módulo se convertirá en una TASK independiente en Fase 2. Las tasks son paralelizables entre sí (cada una toca archivos distintos).

### Módulo 1: Cotizaciones
- **Path**: `resources/views/admin/cotizaciones/index.blade.php` + `scripts/main.blade.php` + `CotizacionController.php`
- **Responsabilidad**: aplicar el patrón visual + filtros de estado y cliente
- **Depende de**: ninguno (es la referencia operativa principal)
- **Riesgo**: medio — módulo grande (wizard, mucho JS)

### Módulo 2: Pedidos
- **Path**: `resources/views/admin/pedidos/index.blade.php` + scripts + `PedidoController.php`
- **Responsabilidad**: patrón visual + filtros de estado y método de pago
- **Depende de**: ninguno
- **Riesgo**: medio — módulo con mucho JS (deuda CSS también, pero NO se aborda aquí)

### Módulo 3: Órdenes de Producción
- **Path**: `resources/views/admin/ordenes/index.blade.php` + scripts + `OrdenProduccionController.php`
- **Responsabilidad**: patrón visual + filtros de estado y departamento
- **Depende de**: ninguno
- **Riesgo**: bajo — módulo más simple

### Módulo 4: Producción Diaria
- **Path**: `resources/views/admin/produccion/diaria/index.blade.php` + scripts + `ProduccionDiariaController.php`
- **Responsabilidad**: patrón visual + filtros de empleado y fecha
- **Depende de**: ninguno
- **Riesgo**: bajo

### Módulo 5: Inventario Movimientos
- **Path**: `resources/views/admin/inventario/movimientos/index.blade.php` + scripts + `MovimientoInventarioController.php`
- **Responsabilidad**: patrón visual + filtros de tipo (Entrada/Salida), insumo y fecha
- **Depende de**: ninguno
- **Riesgo**: bajo

### Módulo 6: Inventario Alertas
- **Path**: `resources/views/admin/inventario/alertas/index.blade.php`
- **Responsabilidad**: patrón visual (sin filtros adicionales — la vista ya está filtrada por stock bajo)
- **Depende de**: ninguno
- **Riesgo**: bajo — más rápida; solo cambio visual del header

### Módulo 7: Users
- **Path**: `resources/views/admin/users/index.blade.php` + scripts + `UserController.php`
- **Responsabilidad**: patrón visual + filtros de Rol y Estado
- **Depende de**: ninguno
- **Riesgo**: bajo — módulo pequeño con pocos campos

---

## 4. Test / QA Specification

### QA manual por módulo (golden path)

Para cada módulo migrado, validar:

1. Cargar el listado → ver el nuevo header colapsado (búsqueda visible, botón "Filtros" con flecha).
2. Tipear en la búsqueda → DataTable filtra con debounce 300ms.
3. Click "Filtros" → panel se expande con animación, header se desredondea (no más `is-collapsed`).
4. Seleccionar un filtro → DataTable recarga (server-side via `ajax.reload()`).
5. Verificar que el **badge contador** muestra el número de filtros activos.
6. Click "Limpiar filtros" → todos los selects vuelven a default, badge desaparece, tabla recarga sin filtros.
7. Click "Filtros" otra vez → panel se cierra, header vuelve a `is-collapsed`.
8. Si el módulo tiene **botón Historial** → verificar que sigue funcionando y mantiene su estilo (`btn-historial-ver` / `btn-historial-volver`).

### Edge cases a verificar

- **Búsqueda + filtro combinados**: aplicar texto Y un select → resultado debe ser intersección.
- **Sin resultados**: con filtros activos que devuelven 0 → DataTable muestra "Sin datos disponibles" (mensaje del `lenguajeData` global).
- **Filtros + paginación**: cambiar filtro debe volver a página 1.
- **Validación JS al cerrar Select2**: si el módulo usa Select2 dentro de modales, mantener patrón `select2:close` — ver `docs/conventions/js-validations.md`.

### Dark mode

- Verificar contrastes del `navy-filter-header`, badge contador, selects y botón "Limpiar".
- Las reglas dark mode ya existen en `custom.css` — solo verificar que no hay overrides locales que rompan la consistencia.

### Responsive

- En viewport < 768px, el header debe seguir siendo legible. Si el ancho no alcanza, los botones (Agregar, Exportar, Historial) pueden envolver a una segunda línea.

---

## 5. Criterios de aceptación

> La feature está completa cuando TODO lo siguiente es verdadero:

- [ ] 7 módulos migrados con el patrón `navy-filter-header` (Cotizaciones, Pedidos, Órdenes, Producción Diaria, Inventario Movimientos, Inventario Alertas, Users).
- [ ] Cada módulo pasa QA manual (sección 4) en light + dark mode.
- [ ] Filtros server-side funcionan: cambiar select → `ajax.reload()` → datos filtrados.
- [ ] Badge contador muestra correctamente filtros activos.
- [ ] Botón "Limpiar filtros" resetea todo.
- [ ] Búsqueda con debounce 300ms en todos los módulos.
- [ ] Botón Historial preservado donde aplicaba antes.
- [ ] Sin estilos inline nuevos en los Blade (toda regla en `custom.css`).
- [ ] DataTable mantiene `lenguajeData` global y `dt-transactional`.
- [ ] **Una sola PR** que cubre los 7 módulos, mergeada a `enmanuel`.
- [ ] `docs/conventions/ux-search-filters.md` actualizado: tabla "Estado por módulo" refleja la nueva realidad.

---

## 6. Codebase Contract

> **CRÍTICO — anclaje anti-alucinación.** Esta sección es la única fuente de verdad sobre qué existe en el código.

### Convenciones a respetar (ver `docs/conventions/`)

- `docs/conventions/ux-search-filters.md` — **patrón canónico** a aplicar (estructura HTML, clases, JS).
- `docs/conventions/js-validations.md` — patrón `select2:close` si hay Select2 en modales.
- `AGENTS.md` § Estándares visuales — DataTable, cards, dark mode.

### Imports verificados

```php
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;       // ya usado en todos los controllers afectados
```

### Firmas existentes a usar

**Vista canónica de referencia (clientes):**
```
resources/views/admin/clientes/index.blade.php:80-110
  → div.advanced-filters-wrapper.navy-theme#advanced-filters
  → div.navy-filter-header.is-collapsed
  → div.collapse#filters-collapse-body
```

**Controller canónico de referencia (productos, server-side):**
```php
// app/Http/Controllers/Admin/ProductoController.php — método getProductos()
$query = Producto::query()->with([...]);
if ($request->filled('filter_tipo_producto_id')) {
    $query->where('tipo_producto_id', $request->filter_tipo_producto_id);
}
return DataTables::of($query)->make(true);
```

**Estados existentes a usar en selects:**
```php
// Cotizacion::ESTADOS (verificar exacto en app/Models/Cotizacion.php)
['Pendiente', 'Aprobada', 'Vencida', 'Convertida', 'Cancelada']

// Pedido::ESTADOS
['Pendiente', 'Completado', 'Cancelado']

// OrdenProduccion::ESTADOS
['Pendiente', 'En Proceso', 'Finalizado', 'Cancelado']
```

### NO existe — no referenciar

- ~~`Cotizacion::scopeConFiltros()`~~ — no existe; se aplican los `where` directamente en el controller.
- ~~clase CSS `.operativa-filter-*`~~ — no inventar; usar `.navy-filter-*` existente.
- ~~middleware `'filter.validation'`~~ — no se necesita; la validación es trivial.
- ~~ruta `cotizaciones.filters`~~ — el endpoint sigue siendo el mismo (`cotizaciones.data` o equivalente).

### Ejemplo de markup a generar (extraído de clientes)

```html
<div class="advanced-filters-wrapper navy-theme" id="advanced-filters">
    <div class="navy-filter-header is-collapsed">
        <div class="navy-header-search">
            <i class="ri-search-line"></i>
            <input type="text" class="navy-search-input" id="custom-search-input"
                   placeholder="Buscar cotización...">
        </div>
        <div class="navy-header-divider"></div>
        <button class="navy-filter-btn collapsed" type="button"
                data-bs-toggle="collapse" data-bs-target="#filters-collapse-body">
            <i class="ri-filter-3-line"></i>
            <span>Filtros</span>
            <span class="navy-filter-badge d-none" id="active-filter-count"></span>
            <i class="ri-arrow-down-s-line navy-filter-chevron"></i>
        </button>
    </div>
    <div class="collapse" id="filters-collapse-body">
        <div class="navy-filter-body">
            <select class="form-select navy-filter-select" id="filter-estado">
                <option value="">Todos los estados</option>
                <option value="Pendiente">Pendiente</option>
                <option value="Aprobada">Aprobada</option>
                <!-- ... -->
            </select>
            <button class="btn btn-link" id="btn-clear-filters">Limpiar filtros</button>
        </div>
    </div>
</div>
```

---

## 7. Notas de implementación y restricciones

### Patrones a seguir

- **Empezar por el módulo más simple** (Inventario Alertas, solo visual; o Users, también pequeño) para validar el approach antes de tocar los grandes.
- **Una sola PR final** que cubra los 7 módulos — facilita ver el patrón aplicado consistentemente en todo el sistema antes de mergear. Se puede ir commiteando módulo por módulo en la misma rama feat/.
- **Botones existentes (Agregar / Exportar / Historial)** se preservan. Quedan a la derecha del `advanced-filters-wrapper` o dentro del card-header arriba, según mejor encaje visual.
- **Servidor primero, cliente después**: añadir lógica en el controller, luego cablear el JS.
- **NO romper el ordenamiento ni paginación de DataTable** existentes.

### Riesgos conocidos

| Riesgo | Mitigación |
|---|---|
| El JS de Cotizaciones es grande (wizard) — añadir handlers podría chocar con código existente | Aislar los handlers en una función separada `initFiltrosCotizaciones()` llamada después de `initDataTable()` |
| Filtro por fecha (Producción Diaria, Movimientos) requiere 2 selects (desde/hasta) o un date-range — más complejo | Usar 2 `<input type="date">` simples con server-side `whereBetween` — no introducir flatpickr ni similar |
| Botón "Limpiar filtros" debe resetear Select2 si el filtro usa Select2 | Usar `.val(null).trigger('change')` para Select2; para selects nativos basta con `.val('')` |
| Cambio de URL al cambiar filtro (botón atrás del browser) — no preservar estado | Aceptable para sprint 1; si se pide, agregar `history.pushState` en spec futuro |

### Dependencias externas

| Paquete | Versión | Razón |
|---|---|---|
| — | — | Sin dependencias nuevas. Todo se hace con jQuery + Bootstrap 5 + DataTables ya instalados. |

---

## 8. Preguntas abiertas — RESUELTAS

- [x] **Inventario Reporte (`inventario/reporte/`)** — ¿se incluye? — *Owner: Emmanuel*: NO. Pertenece a sección Reportes (paleta sky), no operativa. Queda fuera del spec.
- [x] **Users (`users/`)** — ¿se incluye? — *Owner: Emmanuel*: SÍ, incluir como Módulo 7. Filtros: Rol (Administrador/Supervisor/Usuario) + Estado (Activo/Inactivo).
- [x] **Filtros confirmados por módulo** — *Owner: Emmanuel*: la propuesta inicial queda aprobada con estos detalles:
  - Cotizaciones: Estado + Cliente (autocomplete persona-search).
  - Pedidos: Estado + Cliente (método de pago descartado — poco discriminante).
  - Órdenes: Estado + Departamento.
  - Producción Diaria: Empleado + rango de fecha (2 inputs `<input type="date">` desde/hasta, NO selector de mes).
  - Inv. Movimientos: Tipo + Insumo + rango de fecha.
  - Inv. Alertas: solo migración visual, sin filtros nuevos.
  - Users: Rol + Estado.
- [x] **Asignación del spec** — *Owner: Emmanuel*: spec completo asignado a **Santiago**. Regla del equipo: 1 spec = 1 persona = 1 PR (ver `feedback-spec-assignment-model` en memoria).
- [x] **Una PR grande o N pequeñas?** — *Owner: Emmanuel*: **una PR grande** que cubre los 7 módulos. Permite revisar el patrón aplicado consistentemente en todo el sistema antes de mergear.

---

## Historial de revisiones

| Versión | Fecha | Autor | Cambio |
|---|---|---|---|
| 0.1 | 2026-05-20 | Emmanuel + Claude | Borrador inicial |
| 1.0 | 2026-05-20 | Emmanuel | Resueltas 5 preguntas abiertas, añadido Módulo 7 (Users), spec asignado a Santiago, estrategia 1 PR. Status → approved. |
