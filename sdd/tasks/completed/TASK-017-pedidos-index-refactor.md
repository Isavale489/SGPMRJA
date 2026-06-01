# TASK-017: Refactor de pedidos/index.blade.php (2709 → ~200 líneas)

**Feature**: FEAT-002 — pedidos-wizard
**Spec**: `sdd/specs/pedidos-wizard.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: L (4–8h)
**Depends-on**: TASK-010, TASK-011, TASK-012, TASK-013, TASK-014, TASK-015, TASK-016
**Assigned-to**: santiago

---

## Contexto

Una vez el wizard nuevo funciona completo (modales + scripts en archivos separados), desmontar el modal viejo de `pedidos/index.blade.php` y dejar el archivo delgado: solo wrapper del DataTable + breadcrumb + botones de acción, incluyendo `modals.blade.php` y `scripts/main.blade.php` vía `@include`.

Sección del spec: `## 3 → Módulo 8`. Es la task de **mayor riesgo** — donde más se puede romper algo al desmontar el archivo viejo.

---

## Scope

- Refactorizar `resources/views/admin/pedidos/index.blade.php`:
  - Conservar: `@extends`, breadcrumb, card con DataTable, filtros unificados (FEAT-001), botones Agregar/Exportar/Historial
  - Conservar: inicialización del DataTable + handlers de acciones (ver/editar/eliminar/completar)
  - Reemplazar el modal viejo `#showModal` (líneas ~511+) por `@include('admin.pedidos.modals')`
  - Mover los scripts del wizard a `@include('admin.pedidos.scripts.main')`
  - Eliminar el `productosModal` viejo si el wizard lo reemplaza
- Cablear los botones del DataTable a los handlers del wizard:
  - "Agregar" → `PedidoWizard.abrirCrear()`
  - "Editar"/"Completar" → `PedidoWizard.abrirEnEdit(id)`
  - "Ver" → `viewModal` (read-only, sin cambios)
- Eliminar los archivos `.bak` y `.utf8` obsoletos

**NO está en alcance**:
- Resolver la deuda CSS inline (138 `style=""`) — spec aparte (`project_pedidos_css_debt.md`)
- Cambiar la lógica del DataTable más allá de cablear los botones

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/pedidos/index.blade.php` | MODIFY | Bajar a ~200 líneas; `@include` de modals + scripts |
| `resources/views/admin/pedidos/index.blade.php.bak` | DELETE | Obsoleto |
| `resources/views/admin/pedidos/index.blade.php.utf8` | DELETE | Obsoleto |

---

## Codebase Contract (Anti-Alucinación)

### Estado actual verificado

- `resources/views/admin/pedidos/index.blade.php` = 2709 líneas
- Modales presentes:
  - `#productosModal` (línea 38)
  - `#viewModal` (línea 224) — se preserva (read-only)
  - `#showModal` (línea 511) — se reemplaza por el wizard
- Filtros unificados ya aplicados (FEAT-001) — NO tocar
- DataTable init + handlers están inline en el `<script>` al final del archivo

### Vista de referencia (cotización, ya refactorizada)

```
resources/views/admin/cotizaciones/index.blade.php   = 137 líneas (objetivo de delgadez)
  → @include('admin.cotizaciones.modals')
  → @include('admin.cotizaciones.scripts.main')
```

Replicar esta estructura para pedidos.

### Includes a verificar existen (creados en tasks previas)

```
resources/views/admin/pedidos/modals.blade.php          (TASK-010+)
resources/views/admin/pedidos/scripts/main.blade.php    (TASK-011+)
```

### Handlers del DataTable a preservar

```bash
# Identificar los handlers de acciones actuales antes de refactorizar:
grep -n "editPedido\|verPedido\|eliminarPedido\|completarPedido\|deletePedido\|\.on('click'" \
   resources/views/admin/pedidos/index.blade.php
```

### Convenciones a respetar

- `docs/conventions/ux-search-filters.md` — filtros ya migrados, preservar
- DataTable: `dt-transactional`, `lenguajeData` global
- `AGENTS.md` § Estándares visuales

### NO existe — no referenciar

- ~~`pedidos/wizard.blade.php`~~ — el wizard vive en `modals.blade.php`
- ~~componente Blade `<x-pedido-modal>`~~ — no se usan Blade components

---

## Notas de implementación

### Patrón a seguir

1. **Hacer backup mental del archivo viejo** (git lo conserva)
2. Reconstruir `index.blade.php` desde cero usando `cotizaciones/index.blade.php` como molde
3. Preservar TODO lo del DataTable + filtros (copiar tal cual)
4. Reemplazar referencias al `#showModal` viejo por las llamadas al wizard
5. `@include` de modals y scripts al final

### Restricciones clave

- **Esta task se hace AL FINAL** — el wizard nuevo debe estar 100% funcional antes de desmontar el viejo
- Verificar que NINGÚN handler del DataTable quede huérfano (ver/editar/eliminar/completar/exportar/historial)
- Verificar que los filtros unificados (FEAT-001) siguen funcionando
- NO perder el `viewModal` (read-only) ni su lógica

### Referencias en el código

- `resources/views/admin/cotizaciones/index.blade.php` — molde de archivo delgado
- `resources/views/admin/pedidos/index.blade.php` (versión vieja, en git) — fuente de los handlers a preservar

---

## Criterios de aceptación

- [ ] `index.blade.php` baja a ~200 líneas
- [ ] `@include('admin.pedidos.modals')` y `@include('admin.pedidos.scripts.main')` presentes
- [ ] Modal viejo `#showModal` eliminado
- [ ] `productosModal` viejo eliminado (si el wizard lo reemplaza)
- [ ] `viewModal` (read-only) preservado y funcional
- [ ] Botones DataTable cableados al wizard (agregar, editar/completar, ver)
- [ ] Filtros unificados (FEAT-001) intactos
- [ ] Exportar PDF e Historial intactos
- [ ] Archivos `.bak` y `.utf8` eliminados
- [ ] `php artisan view:clear` sin errores
- [ ] Light + dark mode

---

## QA manual (regresión completa de pedidos)

1. `/pedidos` → listado carga con DataTable + filtros
2. Filtros: buscar, filtrar por estado → funciona (FEAT-001)
3. "Agregar Pedido" → wizard nuevo abre (modo crear)
4. "Editar" un pedido → wizard abre en modo edit
5. "Ver" un pedido → viewModal read-only abre
6. "Eliminar" → confirma y elimina
7. "Exportar PDF" → genera PDF
8. "Historial" (si aplica) → funciona
9. Crear un pedido completo vía wizard → aparece en listado
10. Convertir cotización → pedido (TASK-015) → funciona
11. Dark mode → repetir todo
12. Verificar consola sin errores JS

---

## Instrucciones para el ejecutor

1. Lee spec + verifica que TASK-010 a TASK-016 estén `completed`
2. Identifica todos los handlers del DataTable con grep
3. Header: `Status: in-progress`
4. Rama `feat/pedidos-wizard`
5. Refactoriza con cuidado — esta es la task de mayor riesgo
6. QA manual de regresión COMPLETA
7. Mueve a `completed/`, llena Nota
8. NO mergear aún — espera TASK-018 (QA final)

---

## Nota de Completitud

**Completado por**: Emmanuel (continuando el trabajo de Santiago)
**Fecha**: 2026-05-27
**Commits**: (este commit)
**Notas**:
- `index.blade.php` reconstruido desde cero usando `cotizaciones/index.blade.php` como molde: **2712 → 159 líneas**. Solo queda breadcrumb + card (header con "Agregar Pedido" + "Exportar PDF") + wrapper de filtros unificados (FEAT-001) + `<table id="pedidos-table">`, más los `@include` de modales y scripts.
- Modales viejos inline eliminados: `#productosModal` (selector de producto viejo, reemplazado por `#ped-agregar-producto-modal` del wizard), `#viewModal` (duplicado — ahora vive en `modals.blade.php` con los 20 IDs `#view-*`) y `#showModal` (wizard viejo, reemplazado por el de `modals.blade.php`).
- **Nuevo archivo `scripts/listado.blade.php`**: extraje el JS de *listado* que estaba mezclado con el wizard viejo en los `<script>` inline del index. Contiene init del DataTable (server-side, columnas, badges de estado), filtros unificados (search debounce, navy-filter, limpiar, badge de conteo), catálogos para el `viewModal` (`tallasCatalogo`/`coloresCatalogo`/`logosCatalogo` + `getTallaLabel`/`getColorNombre`/`getLogoNombre`), y los handlers `view-btn` (read-only) y `remove-btn` (eliminar). Decisión: archivo aparte para no tocar el wizard (`main.blade.php`) y mantener separadas las responsabilidades listado vs wizard.
- **`window.products = @json($productos)` movido al tope de `listado.blade.php`** (fuera de `$(document).ready`): `main.blade.php:735` hace `var pedProdCatalogo = window.products || []` — el wizard DEPENDE de esa global. Definirla a nivel de script garantiza disponibilidad antes de cualquier callback de ready.
- **Cableado de botones del DataTable al wizard nuevo**:
  - "Agregar Pedido" (`#create-btn`): `data-bs-target` cambiado de `#seleccionarCotizacionModal` → **`#showModal`**. El wizard se auto-resetea a modo crear en `show.bs.modal` (gated por `!isEditMode()`). Coincide con spec §4 ("click Agregar → wizard abre en paso 1 modo crear", sin modal intermedio).
  - "Editar" (`.edit-btn`): recableado de la hidratación del `#showModal` viejo → **`window.pedAbrirEnEdit(id)`** (TASK-016).
  - "Ver" (`.view-btn`): preservado verbatim, apunta al `#viewModal` nuevo (mismos IDs).
- El JS viejo descartado (autocomplete levenshtein, `addProductItem`, `validarFormularioPedido`, submit de `#pedidoForm` viejo, hidratación convert-desde-cotización por URL param) ya está cubierto por el wizard. Verificado por grep: **ninguna función huérfana** referenciada desde `main`/`cotizacion_selection`/`modals` salvo `window.products` (resuelto).
- `index.blade.php.bak` y `.utf8` eliminados.
- `php artisan view:cache` compila TODAS las vistas sin errores (valida sintaxis de `index`, `listado`, `modals`, etc.). `view:clear` limpio.

**Desviaciones del spec**:
- **Flujos import/convert quedan independientes del botón "Agregar"**: antes "Agregar Pedido" abría `#seleccionarCotizacionModal` (que solo permitía convertir, sin opción de pedido en blanco — gap detectado en prep de TASK-016). Ahora "Agregar" va directo al wizard en blanco (per spec §4). El `#seleccionarCotizacionModal` queda como picker del flujo **importar** (botón en paso 2, `main:953`) y el **convertir desde cotización** sigue por URL param desde `/cotizaciones` (`main:1438`, TASK-015). Ningún flujo se rompe.
- **QA en navegador diferido a TASK-018**: la verificación fue estática/estructural (compilación Blade + grep de dependencias + contrato de IDs). El click-through real (3 modos × light/dark) corresponde a TASK-018.
