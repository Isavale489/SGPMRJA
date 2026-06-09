# CLAUDE.md — Contexto de proyecto para Claude Code

> Leído automáticamente por Claude Code al iniciar sesión.
> Última actualización: 2026-06-03 (dump SQL limpio · commit `caa618b`) · Rama activa: `enmanuel`

---

## Stack y arranque

- **Framework**: Laravel 11 + Blade + jQuery + Bootstrap 5
- **BD**: MariaDB 10.4 (puerto 3308, usuario `root`, sin contraseña, DB `sistema_atlantico`)
- **Servidor local**: `php artisan serve` (o Laragon/XAMPP)
- **Assets**: archivos estáticos en `public/assets/` (sin Vite en el admin)
- **Layout admin**: `resources/views/admin/layouts/app.blade.php`

---

## Convenciones clave (leer antes de tocar código)

| Tema | Regla |
|---|---|
| CSS | Todo CSS personalizado va en `public/assets/css/custom.css` |
| JS | IIFEs por módulo; scripts en `@push('scripts')` al final de cada vista |
| Modales | Clase `atlantico-modal` obligatoria; `data-bs-backdrop="static"` |
| IDs de form | Campo oculto `#id-field` para el ID del registro (convención universal) |
| DataTables | Siempre server-side; método `getX()` en el controller |
| Modelos | Soft deletes en la mayoría; `estado` como ENUM en lugar de booleano |

---

## Trabajo realizado en sesión 2026-06-03

### 1. AtlanticoGuard — Guard de cambios sin guardar (GLOBAL)

**Archivo**: `resources/views/admin/layouts/app.blade.php` (antes de `@stack('scripts')`)

Guard IIFE global que detecta cambios sin guardar en **todos** los modales de edición. Funciona automáticamente en cualquier `.atlantico-modal` con `<form>` adentro.

**Comportamiento**:
- Solo activa en modo edición (cuando `#id-field` tiene valor)
- Al intentar cerrar con cambios → SweetAlert 3 botones: **Guardar / Descartar / Seguir editando**
- "Guardar" llama `$form.trigger('submit')` o el botón declarado en `data-guard-save-btn`
- Flag `isDirty` activado por eventos `input`/`change` del usuario

**Atributos HTML opcionales en el modal**:
```html
data-guard-id-field="nombre-del-campo-id"   <!-- default: id-field -->
data-guard-save-btn="id-del-boton-guardar"  <!-- para módulos con e.preventDefault() en submit -->
```

**Módulos con atributos no estándar**:
| Módulo | `data-guard-id-field` | `data-guard-save-btn` |
|---|---|---|
| pedidos | `ped-wiz-id-field` | `ped-wiz-edit-btn` |
| departamentos | `form-depto-id` | — |
| cargos | `form-cargo-id` | — |
| atributos (atributoModal) | `atr-id` | — |
| atributos (valorModal) | `val-id` | — |

**SweetAlert2** movido al layout global (ya no se carga por módulo).

---

### 2. Cotizaciones — Chips de estado eliminados del modal de edición

El modal de edición de cotizaciones tenía chips interactivos para cambiar el estado (Pendiente / Aprobada / Cancelada). Se eliminaron porque el estado ya se gestiona mediante acciones dedicadas en la tabla (Reactivar, Anular, etc.).

**Cambios**:
- `resources/views/admin/cotizaciones/modals.blade.php`: card de chips eliminada, `<select id="estado-field" class="d-none">` conservado (solo lectura, se envía con el form)
- `resources/views/admin/cotizaciones/scripts/main.blade.php`: eliminados handlers de chips, referencias a `#estado-field-wrapper`

---

### 3. Módulo Compras — Flujo Borrador/Procesar/Anular/Clonar

**Rama**: `feat/compras-ajustes` (mergeada a `enmanuel`)

#### Flujo de estados
```
[NUEVA COMPRA] → borrador → [PROCESAR] → recibida → [ANULAR] → anulada
                                                                     ↓
                                                              [CLONAR] → borrador (nuevo)
```

#### Reglas de negocio
- **borrador**: editable libremente, **no genera movimientos de inventario**
- **procesar** (`PATCH /compras/{id}/procesar`): cambia a `recibida` + itera detalles + genera `MovimientoInsumo` tipo `Entrada` + actualiza `stock_actual` con `lockForUpdate()` en transacción
- **anular** (`PATCH /compras/{id}/anular`): solo para `recibidas`, revierte stock con `MovimientoInsumo` tipo `Salida`
- **clonar** (`POST /compras/{id}/clonar`): solo para `anuladas`, replica cabecera + detalles como nuevo `borrador` (fecha hoy, sin número de factura)

#### Archivos modificados
| Archivo | Cambio |
|---|---|
| `app/Services/CompraService.php` | Métodos: `registrar()` (borrador), `actualizar()`, `procesar()`, `anular()`, `clonar()` |
| `app/Http/Controllers/CompraController.php` | Métodos: `store`, `update`, `procesar`, `anular`, `clonar`, `getParaEditar`, `getCompras` (acciones condicionales por estado) |
| `routes/web.php` | Rutas nuevas: `PUT update`, `PATCH procesar`, `POST clonar`, `GET editar-datos` |
| `resources/views/admin/compras/index.blade.php` | Botones reordenados: Nueva Compra primero, PDF segundo |
| `resources/views/admin/compras/modals/create.blade.php` | Campo `#c-edit-id` y `id="compraModalTitle"` para modo edición |

#### Rutas de compras (completas)
```
GET    /compras                    → index
POST   /compras                    → store (crea borrador)
PUT    /compras/{compra}           → update (edita borrador)
GET    /compras/data               → getCompras (DataTable)
GET    /compras/reporte/pdf        → reportePdf
GET    /compras/{compra}/editar-datos → getParaEditar (JSON para modal)
GET    /compras/{compra}/pdf       → compraPdf
GET    /compras/{compra}           → show
PATCH  /compras/{compra}/procesar  → procesar
PATCH  /compras/{compra}/anular    → anular
POST   /compras/{compra}/clonar    → clonar
```

#### Pendiente (Task #4)
- Handlers JS en `main.blade.php` para los botones: `procesar-btn`, `anular-btn`, `clonar-btn`, `editar-btn`
- El submit handler del wizard necesita detectar `#c-edit-id` para usar `PUT` en vez de `POST`
- `resetModal()` debe limpiar `#c-edit-id` y restaurar el título

---

## Migraciones pendientes de ejecutar en otros entornos

Al clonar o cambiar de equipo, ejecutar:
```bash
php artisan migrate
```

Migraciones relevantes recientes:
- `2026_06_01_142640_add_tasa_y_condiciones_to_cotizacion_table` — columnas `tasa_cambio_valor` y `condiciones_terminos` en `cotizacion`
- `2026_06_02_000001_create_compra_table` — tabla `compra`
- `2026_06_02_000002_create_compra_detalle_table` — tabla `compra_detalle`
- `2026_06_03_000001_add_auditoria_anulacion_to_compra_table` — columnas `anulado_por_id` (FK a `user`) y `fecha_anulacion` en `compra`
- `2026_06_03_000002_add_clonada_to_compra_table` — columna `clonada` (tinyint) en `compra`

---

## Dump SQL — `database/sistema_atlantico.sql`

> El sistema corre sobre **MySQL 8** (no MariaDB; el local es solo el motor de desarrollo). El dump debe quedar siempre en formato MySQL nativo.

**Estado al 2026-06-03** (commit `caa618b` en `enmanuel`): dump limpio y funcional, listo para importar directamente en cualquier gestor de DB.

- Header MySQL nativo (`MySQL dump 10.13 Distrib 8.0.46`), DB `sistema_atlantico5`
- 43 tablas, datos sin basura de testeo
- `compra`: 2 filas reales (ambas `recibida`); incluye las 3 columnas nuevas (`clonada`, `anulado_por_id`, `fecha_anulacion`) + FK `compra_anulado_por_id_foreign`
- Migraciones registradas hasta la `121` (batch 68)

**Para regenerar el dump** (cuidando compatibilidad MySQL):
- Preferir partir del dump de referencia MySQL nativo + aplicar solo el delta de esquema, en vez de re-dumpear desde la MariaDB local (arrastra header "MariaDB dump", `current_timestamp()`, anchos `bigint(20)` y data de prueba).
- Cliente/dump en `C:\xampp\mysql\bin\`. **Bash tool**: la redirección `<`/`>` funciona bien. **PowerShell**: NO usar `>` (genera UTF-16/BOM y rompe el archivo) — usar `--result-file`.
- Validar siempre importando en una BD temporal antes de commitear, y hacer `DROP` al terminar.

---

## Ramas activas

| Rama | Propósito |
|---|---|
| `main` | Producción / base estable |
| `enmanuel` | Rama de trabajo principal de Emmanuel (contiene todo lo reciente) |
| `feat/compras-ajustes` | Feature de compras (mergeada a `enmanuel`) |
