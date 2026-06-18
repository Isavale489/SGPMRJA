# TASK-038: Migración de rutas a middleware `permiso` + UI dirigida por permisos

**Feature**: FEAT-005 — seguridad-roles-permisos
**Spec**: `sdd/specs/seguridad-roles-permisos.spec.md`
**Status**: in-progress
**Priority**: high
**Esfuerzo estimado**: L (4-8h)
**Depends-on**: TASK-036, TASK-037
**Assigned-to**: Emmanuel

---

## Contexto

Módulo 4 del spec (§3). Conecta la autorización runtime (TASK-037) con las rutas reales
y la UI: funde los dos grupos `role:` de `web.php` en el grupo autenticado con el
middleware `permiso`, reemplaza el gate global del sidebar (`hasRole([...])`) por
visibilidad por módulo (`*.ver`), y los botones de acción quemados con `isAdmin()`/
`hasRole()` por `tienePermiso('<modulo>.<accion>')`.

QA de regresión crítico: **el Supervisor debe conservar exactamente su acceso actual**
(paridad, criterio de aceptación del spec).

Implementa §2 puntos 5 y 6, §3 Módulo 4.

---

## Scope

- En `routes/web.php`: aplicar el middleware `permiso` al grupo autenticado y **fundir** los dos grupos `role:Administrador` (`:63`) y `role:Administrador,Supervisor` (`:148`) en el grupo `auth` (`:48`). El permiso lo resuelve el middleware desde el nombre de ruta vía el registry — las rutas casi no cambian, solo se quita el wrapper `Route::middleware('role:...')->group(...)`.
  - **Transición segura (§7)**: primero dejar `permiso` conviviendo con los grupos `role:` (doble check inofensivo), validar paridad, y SOLO al final retirar los grupos `role:`. Decidir con el equipo si esta task entrega ya retirados o en convivencia.
- En `resources/views/admin/layouts/sidebar.blade.php:314`: reemplazar el `@if hasRole(['Administrador','Supervisor'])` que envuelve TODO el sidebar por visibilidad por módulo: cada ítem se muestra si `tienePermiso('<modulo>.ver')`.
- Migrar los gates de UI quemados (ver Contract) de `isAdmin()`/`hasRole()` a `tienePermiso('<modulo>.<accion>')`:
  - `pedidos/index.blade.php:45`, `pedidos/scripts/listado.blade.php:93` (var JS `isAdmin`),
  - `cotizaciones/index.blade.php:41`, `cotizaciones/modals.blade.php:899,1024,1466,1532`.

**NO está en alcance**:
- El dropdown del header "Configuración de seguridad" (TASK-039 — junto con la página).
- El gate `@if isAdmin()` del header dropdown (`header.blade.php:337`) que agrupa usuarios/sistema/seguridad → ese se mantiene `isAdmin()` (el panel de config es solo-admin, §2). NO migrarlo a `tienePermiso`.
- Crear el registry/middleware/helper (TASK-037).
- Código muerto de roles fantasma (TASK-040).

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `routes/web.php` | MODIFY | fundir grupos `role:` en el grupo auth con middleware `permiso` |
| `resources/views/admin/layouts/sidebar.blade.php` | MODIFY | visibilidad por módulo (`*.ver`) en vez del gate global |
| `resources/views/admin/pedidos/index.blade.php` | MODIFY | `isAdmin()` → `tienePermiso('pedidos.*')` |
| `resources/views/admin/pedidos/scripts/listado.blade.php` | MODIFY | var JS `isAdmin` → permiso(s) específicos |
| `resources/views/admin/cotizaciones/index.blade.php` | MODIFY | `isAdmin()` → `tienePermiso('cotizaciones.*')` |
| `resources/views/admin/cotizaciones/modals.blade.php` | MODIFY | 4 gates `hasRole([...])` → permisos |

---

## Codebase Contract (Anti-Alucinación)

### routes/web.php (verificado 2026-06-15 — OJO: líneas corridas respecto al spec)
```php
:48  Route::middleware(['auth','throttle:60,1','active.user','recovery.questions.required'])->group(function () {
:51    Route::get('/dashboard', ...)        // común
:54    Route::get('/profile', ...)          // común
:63    Route::middleware('role:Administrador')->group(function () { ... });            // ~solo-admin
:148   Route::middleware('role:Administrador,Supervisor')->group(function () { ... }); // ~operativa
:312 });
// NOTA: el spec §6 cita :62 y :142; tras FEAT-004 son :63 y :148. Verificar con grep "role:" antes de tocar.
```

### Gates de UI quemados (verificado vía spec §6 — re-confirmar líneas con grep antes de editar)
```
resources/views/admin/layouts/sidebar.blade.php:314   @if hasRole(['Administrador','Supervisor'])   ← gate global del sidebar
resources/views/admin/layouts/header.blade.php:337    @if isAdmin()  ← dropdown config (NO migrar, queda isAdmin)
resources/views/admin/pedidos/index.blade.php:45      @if isAdmin()
resources/views/admin/pedidos/scripts/listado.blade.php:93   var isAdmin = {{ ... }}  (JS)
resources/views/admin/cotizaciones/index.blade.php:41 @if isAdmin()
resources/views/admin/cotizaciones/modals.blade.php:899,1024,1466,1532   @if hasRole([...])
```

### De TASK-037 (dependencia)
```php
// helper tienePermiso('modulo.accion'): bool  (en app/Support/helpers.php)
// middleware alias 'permiso' (Kernel)
// config/modulos.php con el mapeo nombre-de-ruta → permiso
```

### De TASK-036 (dependencia)
```php
// hasRole()/isAdmin() siguen funcionando (firma intacta) durante la transición
```

### Convenciones
- §7 spec: orden de transición (convivencia → QA paridad → retirar `role:`)
- §5 criterio: los dos grupos `role:` eliminados; `permiso` cubre las ~180 rutas

### NO existe — no referenciar
- ~~grupos `role:` en :62/:142~~ — están en :63/:148 (corridos por FEAT-004)

---

## Notas de implementación

### Sidebar por módulo
Cada sección/ítem del sidebar se condiciona con `@if(tienePermiso('<modulo>.ver'))`.
Un rol sin ningún `*.ver` ve solo el dashboard (sin sidebar roto, §4 edge case).

### Botones por acción
Ej. en cotizaciones, un botón "convertir a pedido" → `@if(tienePermiso('cotizaciones.gestionar'))`
(o la acción que corresponda según el registry de TASK-037). Mapear cada botón a su acción real.

### Restricciones clave
- NO tocar el `@if isAdmin()` del header dropdown de configuración.
- QA de paridad obligatorio antes de retirar los grupos `role:`.

---

## Criterios de aceptación

- [ ] Los dos grupos `role:` de `web.php` eliminados (o en convivencia documentada); middleware `permiso` cubre las rutas autenticadas
- [ ] Sidebar muestra solo módulos con `*.ver`
- [ ] Botones de acción condicionados por su permiso
- [ ] **Paridad Supervisor**: login Supervisor conserva exactamente su acceso actual (QA paso 9 del spec)
- [ ] Rol sin permisos ve solo el dashboard, sin 500 ni sidebar roto
- [ ] `GET /compras` directo con rol sin `compras.ver` ⇒ 403

---

## QA manual

1. Login Supervisor → muestreo: pedidos, cotizaciones, órdenes, compras, reportes accesibles igual que antes.
2. Login Administrador → acceso total intacto (Gate::before).
3. Crear rol parcial (solo `cotizaciones.ver`) y usuario con ese rol → sidebar solo Cotizaciones; `/compras` ⇒ 403.
4. Rol sin permisos → solo dashboard, sin errores.
5. `php artisan route:list` contra el registry como checklist (ninguna ruta autenticada sin mapeo).

---

## Instrucciones para el ejecutor

1. Lee el spec (§2 puntos 5-6, §3 Módulo 4, §7).
2. Confirma TASK-036 y TASK-037 en `completed/`.
3. **Re-confirma las líneas** de web.php y de los blades con `grep` (cambian fácil).
4. `Status: in-progress`, `Assigned-to: <tu-nombre>`.
5. Rama: `git checkout -b feat/TASK-038-rutas-ui-permisos`.
6. Implementa, QA paridad, mueve a `completed/`, rellena Nota.
7. PR.

---

## Nota de Completitud

**Completado por**: Emmanuel
**Fecha**: 2026-06-17
**Commits**: (rama `feat/TASK-038-rutas-ui-permisos`)
**Notas**:
- `routes/web.php`: fundidos los dos grupos `role:` en el grupo `auth` con middleware `permiso` (retirados, NO en convivencia). Verificado con script ad-hoc sobre `Route::getRoutes()`: **0 huecos** (toda ruta autenticada resuelve a un permiso o está en `comunes`); 147 rutas permitidas al Supervisor, 75 denegadas — las 75 eran exactamente las del antiguo grupo solo-admin. **Paridad Supervisor confirmada a nivel de rutas** (y validada en navegador por Santi/Emmanuel).
- Sidebar: gate global → visibilidad por módulo (`tienePermiso('<modulo>.ver')`), con secciones/sub-dropdowns ocultos si no tienen hijos visibles. Placeholders TODO (Calidad/Garantías/Reportes Generales) conservados.
- Botones migrados a `tienePermiso`: pedidos (index + listado JS), cotizaciones (index + 4 gates de modals: telas→`tipo-productos.gestionar`, colores→`colores.gestionar`).

**Desviaciones del spec**:
1. **Brecha de paridad PDF (fix incluido)**: `productos/insumos/proveedores.reporte.pdf` exigían `*.pdf`, acción que el Supervisor NO tiene en sus 34 claves → habría perdido la exportación. Resuelto en `config/modulos.php` mapeando esos `reporte.pdf` a `*.ver` (decisión de Santi: sin tocar DB/dump, mantiene las 34 claves, consistente con `movimiento-insumo.reporte.pdf`). Se eliminó la acción `pdf` muerta en esos 3 módulos.
2. **Archivos fuera del Contract migrados también** (por coherencia de UI para roles dinámicos, decisión de Santi): `proveedores/index.blade.php` (:57, :721 → `proveedores.gestionar`, neutro para roles actuales) y `cotizaciones/scripts/main.blade.php` (:479/:520 → separados en `cotizaciones.convertir` para cambio-de-estado/convertir/reactivar y `cotizaciones.gestionar` para editar/eliminar). **Cambio visible**: el Supervisor ahora ve en cotizaciones los botones de cambio de estado/convertir/reactivar (alineado con la ruta `updateEstado`=`convertir` que ya tenía). Si el negocio lo quiere solo-admin, mover `updateEstado` a `gestionar` en el registry.
3. `header.blade.php:337` se mantiene `isAdmin()` (excluido por el spec; el panel de config es solo-admin).
