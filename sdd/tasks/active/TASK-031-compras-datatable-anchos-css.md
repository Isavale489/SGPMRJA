# TASK-031: Mover anchos de DataTable de Compras de JS a custom.css

**Feature**: FEAT-COMPRAS-UX-01 — Refactor UX/UI Módulo de Compras
**Spec**: `sdd/specs/refactor-compras-ui.spec.md`
**Status**: pending
**Priority**: medium
**Esfuerzo estimado**: S (< 2h)
**Depends-on**: none
**Assigned-to**: unassigned

---

## Contexto

La DataTable de Compras (`#compras-table`) define los anchos de columna directamente en la inicialización JS (`width: '25%'`, etc.). El estándar del sistema (`AGENTS.md` § Estándares visuales y la convención de todos los demás módulos) exige que los anchos vivan en `custom.css` como `#compras-table th:nth-child(N) { width: ... }`, con `autoWidth: false` mantenido en JS pero sin `width` por columna.

Esta task es independiente y no comparte archivos con TASK-030 ni TASK-032. Puede tomarse en paralelo.

Ref. spec § Módulo 2.

---

## Scope

- En `scripts/main.blade.php`: **eliminar** el atributo `width` de cada definición de columna en `#compras-table`.
- En `public/assets/css/custom.css`: **añadir** un bloque `#compras-table th:nth-child(N)` para las 7 columnas, siguiendo exactamente el patrón de los otros módulos (ej. `#ordenes-table`, `#pedidos-table`).
- Verificar que `#compras-table` tiene o hereda `table-layout: fixed` (normalmente lo da `.table-operativa`). Si la tabla no tiene esa clase, añadirla al `<table>` en `modals/create.blade.php` (o en `index.blade.php` donde viva la tabla).

**NO está en alcance**:
- Cambios al JS de la DataTable más allá de eliminar `width`
- Cambios en las columnas, renders o filtros
- Validaciones del wizard → TASK-030

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/compras/scripts/main.blade.php` | MODIFY | Eliminar `width: 'X%'` de cada columna; mantener `autoWidth: false` |
| `public/assets/css/custom.css` | MODIFY | Añadir bloque `#compras-table th:nth-child(N)` (7 columnas) |

---

## Codebase Contract (Anti-Alucinación)

> **CRÍTICO**: Esta sección contiene referencias VERIFICADAS del código real.

### Estado actual en `scripts/main.blade.php` (verificado)
```js
window.comprasTable = $('#compras-table').DataTable({
    processing: true,
    serverSide: true,
    autoWidth: false,      // ← MANTENER
    columns: [
        { data: 'id',               name: 'id',               width: '5%'  },  // ← ELIMINAR width
        { data: 'proveedor_nombre', name: 'proveedor_nombre', width: '25%' },  // ← ELIMINAR width
        { data: 'numero_factura',   name: 'numero_factura',   width: '12%' },  // ← ELIMINAR width
        { data: 'fecha_formateada', name: 'fecha_compra',     width: '12%' },  // ← ELIMINAR width
        { data: 'total',            name: 'total',            width: '14%', className: 'text-end' }, // ← ELIMINAR width
        { data: 'estado_badge',     name: 'estado',           width: '10%', orderable: false, searchable: false }, // ← ELIMINAR width
        { data: 'actions',          name: 'actions',          width: '16%', orderable: false, searchable: false }, // ← ELIMINAR width
    ]
});
// Nota: las columnas suman 5+25+12+12+14+10+16 = 94%; revisar si el total debe llegar a 100%
// antes de commitear. Ajustar proporcionalmente si es necesario.
```

### Patrón CSS a seguir (verificado en `custom.css` — otros módulos)
```css
/* ============================================================
   MÓDULO COMPRAS — Anchos de columna
   ID  · Proveedor · Nro. Factura · Fecha · Total · Estado · Acciones
   ============================================================ */
#compras-table th:nth-child(1) { width: 5%;  text-align: center; }  /* ID */
#compras-table th:nth-child(2) { width: 25%; }                      /* Proveedor */
#compras-table th:nth-child(3) { width: 12%; }                      /* Nro. Factura */
#compras-table th:nth-child(4) { width: 12%; }                      /* Fecha */
#compras-table th:nth-child(5) { width: 14%; text-align: right;  }  /* Total */
#compras-table th:nth-child(6) { width: 10%; text-align: center; }  /* Estado */
#compras-table th:nth-child(7) { width: 16%; text-align: center; }  /* Acciones */

#compras-table td:last-child    { overflow: visible; text-overflow: clip; text-align: center; }
```

### Dónde insertar en custom.css
Buscar el bloque `MÓDULO COMPRAS` existente (alrededor de la línea donde están los demás módulos de anchos de columna, junto a `#cotizaciones-table`, `#pedidos-table`, `#ordenes-table`). Si no existe ese bloque, insertarlo justo antes de la sección `MÓDULO ÓRDENES DE PRODUCCIÓN`.

### Clase .table-operativa (verificado)
```css
/* custom.css ~línea 1076 */
.table-operativa {
    width: 100% !important;
    /* ... incluye table-layout: fixed ... */
}
```
Verificar que `#compras-table` tenga la clase `table-operativa` en el HTML. Si no, añadirla.

### Convenciones a respetar
- `AGENTS.md` § Estándares visuales — anchos en CSS, no en JS
- Patrón verificado en: `#cotizaciones-table` (~línea 1207), `#pedidos-table` (~línea 4234), `#ordenes-table` (~línea 5471)

### NO existe — no referenciar
- ~~`public/css/custom.css`~~ — el archivo es `public/assets/css/custom.css`
- ~~`table-layout: fixed` en el JS de DataTable~~ — va en CSS, no en JS
- ~~`.table-operativa` en `custom.css` como nueva clase~~ — ya existe, solo verificar que la tabla la tenga

---

## Notas de implementación

1. Abrir `index.blade.php` de compras para confirmar el ID real de la tabla (`#compras-table`) y sus clases actuales.
2. Si la tabla no tiene `table-operativa`, añadirla en el HTML de `index.blade.php`.
3. Eliminar `width: '...'` de las 7 columnas en `scripts/main.blade.php` (mantener todos los demás atributos: `data`, `name`, `className`, `orderable`, `searchable`, `render`).
4. En `custom.css`, insertar el bloque de anchos en la sección de anchos de columna por módulo.
5. Los anchos deben sumar 100% (o cerca). Verificar en el navegador.

---

## Criterios de aceptación

- [ ] No existen atributos `width: '...'` en la definición de columnas de `#compras-table` en `scripts/main.blade.php`
- [ ] `custom.css` contiene el bloque `#compras-table th:nth-child(N)` con 7 reglas
- [ ] La tabla se renderiza correctamente a distintos anchos de ventana
- [ ] Las proporciones de columnas son visualmente consistentes con el resto del sistema
- [ ] PR contra `enmanuel` con descripción enlazando esta task

---

## QA manual

1. Ir a Compras (listado).
2. Verificar que las columnas tienen el ancho correcto: ID pequeño, Proveedor ancho, Acciones al final.
3. Redimensionar la ventana al 50% del ancho → la tabla debe mantener proporciones (no colapsar).
4. Abrir DevTools → inspeccionar `<th>` → confirmar que el ancho viene del CSS, no de estilos inline JS.
5. Probar dark mode.

---

## Instrucciones para el ejecutor

Cuando tomes esta task:

1. **Lee el spec** completo en `sdd/specs/refactor-compras-ui.spec.md`.
2. **Verifica dependencias** — ninguna; puede tomarse en paralelo con TASK-030.
3. **Verifica el Codebase Contract** antes de codificar:
   - Lee `resources/views/admin/compras/scripts/main.blade.php` líneas ~29-75 para confirmar el estado actual
   - Busca `#compras-table` en `custom.css` para confirmar que no hay reglas previas
4. **Actualiza el header**: `Status: in-progress`, `Assigned-to: <tu-nombre>`.
5. **Crea rama**: `git checkout -b feat/TASK-031-compras-datatable-css` desde `enmanuel`.
6. **Implementa** dentro del scope.
7. **Verifica** los criterios de aceptación y el QA manual.
8. **Mueve este archivo** a `sdd/tasks/completed/TASK-031-compras-datatable-anchos-css.md`.
9. **PR** contra `enmanuel`.

---

## Nota de Completitud

*(Llenar al terminar)*

**Completado por**: —
**Fecha**: —
**Commits**: —
**Notas**: —
**Desviaciones del spec**: ninguna | —
