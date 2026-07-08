# CLAUDE.md — Contexto de proyecto para Claude Code

> Leído automáticamente por Claude Code al iniciar sesión.
> Última actualización: 2026-07-08 (QoL cotizaciones/dashboard + reversión cotización + dropdown banco · commit `6a4a5cc`) · Rama activa: `enmanuel`

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

## Trabajo realizado en sesión 2026-07-07/08

### 1. Paquete QoL — Cotizaciones y Dashboard

| Commit | Cambio |
|---|---|
| `8177e91` | **Logo/bordado opcional**: `logo_id` de bordados pasa de `required` a `nullable` en store/update de `CotizacionController`; el configurador ya no bloquea al aplicar sin logo (estado "Sin logo asignado" en vez de "Falta logo"). El servicio ya persistía nullable |
| `5e56df3` | **Días de validez dinámicos**: el cálculo ya usaba `Cotizacion::diasVigencia()` (parámetro `cotizaciones.dias_vigencia`); el único hardcode restante era el mensaje "Nueva validez: 15 días" de `reactivar`, ahora interpolado |
| `0c5d049` | **Widget Productos del dashboard**: contaba la tabla `producto` (solo variantes materializadas, 0 filas) → ahora cuenta `tipo_producto` (deleted_at IS NULL), que es lo que lista el módulo `/productos` ("Catálogo de Productos (Tipos)") |
| `948f5c1` | **Fecha emisión/validez en hora local**: `new Date().toISOString()` devuelve UTC; en Venezuela (UTC−4) crear en la tarde/noche corría la emisión al día siguiente. Nuevo helper `cotFechaLocalISO()` en `cotizaciones/scripts/main.blade.php`, usado en default de emisión, `cotSeedValidez` y chips de validez rápida |

### 2. Dropdown de Banco (wizard Pedido, paso Pago) — clipping por footer

**Problema**: el menú de banco (realzado por AtlanticoSelect) abría hacia abajo
y el footer opaco del modal + el `overflow-y:auto` del `wiz-wizard-body` tapaban
las opciones (peor con una sola fila de pago).

**Solución final** (commits `9f28dbd` → `6a4a5cc`, en `pedAgregarFila` de
`pedidos/scripts/main.blade.php`): `.afs-wrap` con clase `dropup` + Dropdown de
Bootstrap instanciado con `popperConfig` → `strategy:'fixed'` (el menú flota
libre, escapa el clipping de ancestros) y modificador `flip` deshabilitado
(siempre abre hacia arriba). **Gotchas aprendidos**:
- `data-bs-display="static"` NO sirve: desactiva Popper y el menú vuelve al
  flujo → lo recorta el overflow del contenedor.
- Con `strategy:'fixed'` los anchos porcentuales (`w-100`, `min-width:100%` de
  `.afs-menu`) se resuelven contra el **viewport** → menú a pantalla completa y
  desfasado. Hay que **quitar la clase `w-100`** (es `!important`) y fijar el
  ancho del toggle en px en cada `show.bs.dropdown` (+ `min-width:0`).

Patrón reutilizable si otro módulo sufre el mismo clipping con AtlanticoSelect.

### 3. Reversión de estado de Cotización al eliminar su Pedido

**Problema**: al eliminar un pedido creado desde una cotización, esta quedaba
atascada en estado `Convertida` y ya no se podía volver a convertir
(`Cotizacion::puedeConvertirse()` exige `Aprobada`). Segundo bloqueo: el índice
único **plano** `pedido_cotizacion_id_unique` (migración `2026_02_19_200000…`)
no ignora filas soft-deleted, así que la fila borrada seguía ocupando el
`cotizacion_id` y re-convertir fallaba por *duplicate key*.

**Solución** (commit `f3922f9`):

| Archivo | Cambio |
|---|---|
| `app/Services/PedidoService.php` | Nuevo `eliminar(Pedido $pedido)`: en transacción revierte cotización `Convertida → Aprobada` (con `lockForUpdate()`), **desliga** `cotizacion_id` del pedido (`NULL` libera el índice único; MySQL admite múltiples NULL) y hace el soft delete |
| `app/Http/Controllers/PedidoController.php` | `destroy()` conserva sus guards (Completado/Cancelado, `tieneProduccionActiva`) y delega en `pedidoService->eliminar()` en vez del `$pedido->delete()` directo |

**Reglas / decisiones**:
- Solo aplica a **eliminación**. `cancelar()` NO revierte: el pedido sigue
  existiendo (estado `Cancelado`), la cotización permanece `Convertida` a propósito.
- Sin migración (el desligue por `NULL` resuelve el índice único) ni cambios de
  frontend (el botón "Convertir a pedido" reaparece solo, es data-driven).
- La conversión sigue por `CotizacionService::convertirAPedido` (re-chequea
  vigencia); `yaFueConvertida()` = `pedido()->exists()` ya excluye trashed.

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
- `2025_12_15_164400_create_tasa_cambio_table` — tabla `tasa_cambio` (tasa BCV USD→VES por fecha; `UNIQUE(moneda, fecha_bcv)`)
- `2026_06_10_000004_add_moneda_bs_to_compra_tables` — **compras en bolívares**: columna `tasa_cambio` (`decimal(12,4)`) en `compra` + `costo_unitario_bs` (`decimal(14,2)`) en `compra_detalle`; incluye backfill de filas previas con la tasa BCV vigente a la fecha de cada compra

---

## Dump SQL — `database/sistema_atlantico.sql`

> **ESTÁNDAR DEL EQUIPO (2026-06-25):** todos corren **MySQL 8 local** (igual que producción) y exportan/importan **solo con los scripts** `database/export-db.*` / `database/import-db.*`. **Prohibido** subir dumps de phpMyAdmin o del `mysqldump` de XAMPP/MariaDB.

### Por qué este estándar
El equipo usaba motores distintos (MariaDB en XAMPP vs MySQL 8 en Ubuntu/producción), y phpMyAdmin generaba dumps incompatibles que fallaban al importar en MySQL 8. Causas concretas detectadas:
- phpMyAdmin **difería `PRIMARY KEY` + `AUTO_INCREMENT`** a bloques `ALTER TABLE` al final → si la importación se corta, las tablas quedan **sin autoincrement** (`Field 'id' doesn't have a default value`).
- No desactivaba `FOREIGN_KEY_CHECKS`.
- Sintaxis MariaDB: `current_timestamp()` (con paréntesis) y anchos `bigint(20)`, que MySQL 8 estricto rechaza o deprecia.

Con todos en MySQL 8 + `mysqldump`, los dumps salen idénticos y siempre importan limpio.

### Exportar la BD al repo (cualquier SO)
```bash
# Windows
powershell -ExecutionPolicy Bypass -File database\export-db.ps1
# Linux / Mac
bash database/export-db.sh
```
Genera `database/sistema_atlantico.sql` en formato MySQL 8 nativo (lee config del `.env`). Luego `git add database/sistema_atlantico.sql && git commit`.

### Importar la BD desde el repo (cualquier SO)
```bash
git pull
# Windows
powershell -ExecutionPolicy Bypass -File database\import-db.ps1
# Linux / Mac
bash database/import-db.sh
```
Crea la BD (nombre tomado del `.env`, default `sistema_atlantico`) e importa el dump. El `.sql` es **agnóstico al nombre de BD** (no trae `CREATE DATABASE`/`USE`), por eso no importa cómo se llame la BD en cada máquina.

### Características del dump generado por los scripts
- Header MySQL nativo, **sin** `CREATE DATABASE`/`USE` ni `GTID_PURGED`.
- `PRIMARY KEY` + `AUTO_INCREMENT` **dentro** del `CREATE TABLE`; `FOREIGN_KEY_CHECKS=0`; charset `utf8mb4`.
- Flags: `--single-transaction --no-tablespaces --set-gtid-purged=OFF --add-drop-table --result-file=…`.
- **PowerShell**: nunca usar `>` (genera UTF-16/BOM y rompe el archivo) — los scripts usan `--result-file`.

### Setup de MySQL 8 local (una vez por máquina)
1. Instalar **MySQL Server 8.0** y agregar su carpeta `bin` al PATH (`mysqldump`/`mysql` accesibles).
2. Ajustar el `.env` (`DB_PORT`, `DB_USERNAME`, `DB_PASSWORD`) a la instancia MySQL 8.
3. (Alternativa sin importar dump) `php artisan migrate --seed` reconstruye esquema + data de catálogo desde migraciones y seeders.

---

## Ramas activas

| Rama | Propósito |
|---|---|
| `main` | Producción / base estable |
| `enmanuel` | Rama de trabajo principal de Emmanuel (contiene todo lo reciente) |
| `feat/compras-ajustes` | Feature de compras (mergeada a `enmanuel`) |
