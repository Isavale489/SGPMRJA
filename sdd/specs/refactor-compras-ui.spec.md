---
type: fix
base_branch: enmanuel
---

# Feature Specification: Refactor UX/UI Módulo de Compras

**Feature ID**: FEAT-COMPRAS-UX-01
**Fecha**: 2026-06-10
**Autor**: Antigravity
**Status**: approved
**Versión objetivo**: Sprint actual

---

## 1. Motivación y requisitos de negocio

### Planteamiento del problema
Durante la auditoría de calidad (QA) del módulo de Compras, se detectaron desviaciones respecto a los estándares visuales y de UX documentados en `AGENTS.md` y la carpeta `docs/conventions/`. Estas inconsistencias incluyen validaciones client-side que no usan el patrón estándar, anchos de DataTable definidos en JS en lugar de CSS, y falta de validación visual en los modales de creación rápida (Proveedor/Insumo).

### Objetivos
- Alinear el Wizard de Nueva Compra (`create.blade.php`) con el estándar de validación de `js-validations.md`.
- Mover la configuración de anchos de columnas de la DataTable de Compras (`main.blade.php`) hacia `custom.css`, siguiendo la regla de `th:nth-child()`.
- Implementar validación nativa y correcta en los modales anidados de creación rápida.

### Fuera de alcance (No-Goals)
- No se modificará la lógica de transacciones del backend (`CompraService`, `CompraController`), ya que funciona perfectamente.
- No se modificará el comportamiento del buscador global con debounce, solo se dejará tal cual.

---

## 2. Diseño arquitectónico

Al ser un refactor puramente de Frontend/UI, no hay cambios en Modelos, Migraciones o Controladores. 

### Puntos de integración
| Componente existente | Tipo de integración | Notas |
|---|---|---|
| `resources/views/admin/compras/scripts/create.blade.php` | modifica | Implementar `blur` y `marcarInvalido()` |
| `resources/views/admin/compras/create.blade.php` | modifica | Añadir `novalidate` a los tags `<form>` |
| `resources/views/admin/compras/scripts/main.blade.php` | modifica | Eliminar `width` de la definición JS del DataTable |
| `public/assets/css/custom.css` | modifica | Añadir selectores `th:nth-child()` para la DataTable de compras |

---

## 3. Desglose por módulos

### Módulo 1: Refactor de Validaciones del Wizard de Compras
- **Path**: `resources/views/admin/compras/scripts/create.blade.php`
- **Responsabilidad**: Reemplazar los `Swal.fire` genéricos en `validateStep()` por el patrón global `marcarInvalido` y `marcarValido`. Añadir eventos `blur` y `select2:close` para feedback en tiempo real.

### Módulo 2: Refactor DataTable de Compras
- **Path**: `resources/views/admin/compras/scripts/main.blade.php` y `public/assets/css/custom.css`
- **Responsabilidad**: Eliminar la inyección de estilos (widths) desde JS en DataTables. Trasladar estos anchos al CSS centralizado y verificar que la tabla de transacciones de compras tenga la clase pertinente.

### Módulo 3: Validación en Modales Anidados
- **Path**: `resources/views/admin/compras/scripts/create.blade.php`
- **Responsabilidad**: Aplicar validaciones visuales con `marcarInvalido` en `crearProveedorRapidoModal` y `crearInsumoRapidoModal`. Cambiar el `<select>` nativo de estado en el modal rápido por el componente blade `<x-forms.select>` (si aplica) o adaptarlo al estándar.

---

## 4. Test / QA Specification

### QA manual (golden path)
1. Navegar a Compras -> Nueva Compra.
2. Dejar campos vacíos y avanzar de paso: los campos deben pintarse de rojo con el texto debajo, en lugar de un pop-up genérico de SweetAlert.
3. Al llenar un campo y quitar el foco (blur), el rojo debe desaparecer.
4. En el listado de compras, redimensionar la ventana y verificar que el ancho de la tabla se mantenga según la proporción definida en CSS.
5. Abrir "Nuevo Proveedor" desde el wizard, dejar en blanco y guardar: deben aparecer errores inline rojos, no un SweetAlert.

---

## 5. Criterios de aceptación

- [ ] `resources/views/admin/compras/create.blade.php` tiene `<form novalidate>`.
- [ ] `validateStep()` en `scripts/create.blade.php` usa `marcarInvalido()` en lugar de `Swal.fire`.
- [ ] No existen referencias a `width: '...'` en la inicialización de DataTables en `scripts/main.blade.php`.
- [ ] `custom.css` contiene las reglas para `table-layout: fixed` y anchos de `th` para las columnas de compras.
- [ ] PR mergeada a `enmanuel`.

---

## 6. Codebase Contract

> **CRÍTICO — anclaje anti-alucinación.**
> Los implementadores (humanos o Claude Code) NO deben referenciar imports,
> rutas, métodos o tablas que no estén listados aquí.

### Convenciones a respetar (ver `docs/conventions/`)
- `docs/conventions/js-validations.md` — patrón de validación blur + submit, `select2:close`.
- `AGENTS.md` § Estándares visuales — `table-layout: fixed`, anchos en CSS.

### Archivos permitidos para editar
- `resources/views/admin/compras/create.blade.php`
- `resources/views/admin/compras/scripts/create.blade.php`
- `resources/views/admin/compras/scripts/main.blade.php`
- `public/assets/css/custom.css`

### NO existe — no referenciar
- ~~`public/css/custom.css`~~ — El archivo CSS del panel admin es `public/assets/css/custom.css`.
- ~~Nuevos endpoints backend~~ — La API y backend ya funcionan correctamente, no deben modificarse.

---

## 7. Notas de implementación y restricciones

- Al remover los Swal de `validateStep`, asegúrate de que el foco se dirija al primer elemento con error para mejorar la accesibilidad (usar `focus()`).
- Para los validadores de Select2 en el Wizard, utiliza el evento `.on('select2:close', function () { ... })` como dicta la convención, dado que Select2 oculta el `<select>` original y el evento `blur` nativo no siempre se dispara.
