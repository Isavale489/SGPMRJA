# TASK-039: Página "Configuración de seguridad" (tabs Roles + Permisos)

**Feature**: FEAT-005 — seguridad-roles-permisos
**Spec**: `sdd/specs/seguridad-roles-permisos.spec.md`
**Status**: in-progress
**Priority**: high
**Esfuerzo estimado**: XL (> 8h)
**Depends-on**: TASK-035, TASK-037
**Assigned-to**: Emmanuel

---

## Contexto

Módulo 5 del spec (§3). La pantalla que usa el admin: tab **Roles** (CRUD con modal
`atlantico-modal`) y tab **Permisos** (selector de rol → matriz de checkboxes
módulo × acción, guardado AJAX). Accesible desde el dropdown "Configuración" del header
(mismo hub que FEAT-004), solo Administrador.

Implementa §2 punto 7, "Rutas nuevas", "UI / Vistas" y §3 Módulo 5.

---

## Scope

- Crear `app/Http/Controllers/SeguridadController.php` con:
  - `index` → vista con tabs (carga roles + el registry de módulos para la matriz).
  - `storeRol`, `updateRol`, `destroyRol` (bloquea `es_sistema` y roles con usuarios asignados).
  - `getPermisos($rol)` → JSON con los permisos otorgados del rol (para pintar la matriz).
  - `guardarMatriz($rol)` → `sync` de `permiso_rol` + `Cache::forget("permisos.rol_{id}")`.
- Registrar las 6 rutas nuevas en `routes/web.php`, protegidas con `isAdmin()`/`can:` directo (**NO** con el middleware `permiso` — el panel de seguridad es siempre y solo del Administrador, §2). Ubicarlas bajo el bloque solo-admin existente (prefijo `configuracion/seguridad`).
- Crear vistas en `resources/views/admin/seguridad/` (index + partials + scripts):
  - Header navy estándar.
  - **Tab Roles**: tabla (nombre, descripción, nº usuarios, badge "Sistema") + modal `atlantico-modal` con `#id-field` (AtlanticoGuard aplica solo). Eliminar deshabilitado para `es_sistema` y roles con usuarios.
  - **Tab Permisos**: select de rol (excluye Administrador, mostrado aparte como "acceso total") → matriz módulo × acción con checkboxes; fila de módulo con "marcar todos"; `ver` es prerrequisito (desmarcarlo desmarca las demás del módulo); guardar por rol vía AJAX + SweetAlert.
  - CSS en `public/assets/css/custom.css`; scripts en `@push('scripts')` como IIFE.
  - Español neutro (tuteo). Dark mode con contraste correcto.
- Añadir el ítem "Configuración de seguridad" (icono `mdi mdi-shield-lock-outline`) en el dropdown "Configuración" del header (`header.blade.php:337`, dentro del `@if isAdmin()` existente, junto al ítem de FEAT-004).

**NO está en alcance**:
- El registry/middleware/helper (TASK-037) — se consumen aquí.
- Migración de las demás vistas a permisos (TASK-038).
- El esquema de tablas (TASK-035).

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `app/Http/Controllers/SeguridadController.php` | CREATE | CRUD roles + matriz permisos (JSON + sync) |
| `routes/web.php` | MODIFY | 6 rutas `seguridad.*` bajo bloque solo-admin |
| `resources/views/admin/seguridad/index.blade.php` | CREATE | tabs Roles + Permisos |
| `resources/views/admin/seguridad/partials/*.blade.php` | CREATE | matriz + modal de rol |
| `resources/views/admin/seguridad/scripts/main.blade.php` | CREATE | IIFE: CRUD AJAX + matriz |
| `resources/views/admin/layouts/header.blade.php` | MODIFY | ítem "Configuración de seguridad" |
| `public/assets/css/custom.css` | MODIFY | estilos de la matriz/tabs/badges |

---

## Codebase Contract (Anti-Alucinación)

### Rutas nuevas (del spec §2 "Rutas nuevas")
```
GET    /configuracion/seguridad             seguridad.index
POST   /configuracion/seguridad/roles       seguridad.roles.store
PUT    /configuracion/seguridad/roles/{rol} seguridad.roles.update
DELETE /configuracion/seguridad/roles/{rol} seguridad.roles.destroy   (bloquea es_sistema y roles con usuarios)
GET    /configuracion/seguridad/permisos/{rol}  seguridad.permisos.get   (JSON)
PUT    /configuracion/seguridad/permisos/{rol}  seguridad.permisos.update (sync + flush caché)
```

### routes/web.php — bloque solo-admin donde anclar (verificado 2026-06-15)
```php
:63 Route::middleware('role:Administrador')->group(function () {
:65   Route::get('configuracion', [ConfiguracionController::class,'index'])->name('configuracion.index');   // FEAT-004
:66   Route::put('configuracion/{modulo}', ...)->name('configuracion.update');
:67   Route::delete('configuracion/{modulo}/{clave}', ...)->name('configuracion.reset');
      // ← añadir aquí el sub-bloque configuracion/seguridad/*
});
// (si TASK-038 ya fundió los grupos role:, anclar igual dentro de un check isAdmin())
```

### header.blade.php (verificado vía spec §6 — re-confirmar con grep)
```
:337 @if isAdmin()   // dropdown "Configuración": usuarios / sistema(FEAT-004) / [+ seguridad aquí]
```

### De TASK-035 (dependencia)
```php
// app/Models/Rol.php : $table='rol', SoftDeletes, $fillable=['nombre','descripcion','es_sistema'],
//   relación usuarios() y permisos()
// tabla permiso_rol(rol_id, permiso, unique[rol_id,permiso])
```

### De TASK-037 (dependencia)
```php
// config/modulos.php  → fuente de la matriz (módulos + acciones)
// convención de caché: Cache::forget("permisos.rol_{id}") al guardar la matriz / editar rol
```

### Convenciones a respetar (ver `docs/conventions/`)
- `docs/conventions/modal-system.md` — modal de rol `atlantico-modal` + `#id-field`
- `docs/conventions/js-validations.md` — validación blur + submit
- `docs/conventions/softdeletes-unique.md` — `rol.nombre` UNIQUE + softDeletes
- AtlanticoGuard global (CLAUDE.md): el modal con `#id-field` activa el guard solo
- DataTable server-side si la tabla de roles lo amerita (o tabla simple si son pocos)

### NO existe — no referenciar
- ~~`App\Http\Controllers\SeguridadController`~~ — se crea aquí
- ~~vistas `resources/views/admin/seguridad/`~~ — se crean aquí
- ~~rutas `seguridad.*`~~ — se registran aquí

---

## Notas de implementación

### destroyRol — reglas (§2, §4)
- `es_sistema = 1` ⇒ bloqueado (no eliminar/renombrar Administrador ni Supervisor).
- Rol con ≥1 usuario asignado ⇒ bloqueado con mensaje claro; reasignar primero.
- Al eliminar un rol válido, borrar (`delete`) sus filas en `permiso_rol`.

### guardarMatriz
`sync` de los permisos del rol + `Cache::forget("permisos.rol_{id}")` para que el cambio
aplique sin re-login (QA paso 6 del spec). Administrador NO es editable en la matriz.

### Restricciones clave
- Panel protegido SOLO por `isAdmin()` (no por `permiso`).
- Validación server-side; `rol.nombre` único (cuidado con softDeletes).
- Sin estilos inline; CSS en custom.css. Dark mode probado.

---

## Criterios de aceptación

- [ ] Tab Roles: crear/editar/eliminar rol; eliminar bloqueado para `es_sistema` y roles con usuarios
- [ ] Rol nuevo aparece en el select del modal de Usuarios (integra con TASK-036)
- [ ] Tab Permisos: matriz se renderiza desde `config/modulos.php` SIN tocar la vista al añadir un módulo
- [ ] Guardar matriz hace `sync` + flush caché (cambio aplica sin re-login)
- [ ] Administrador NO editable en la matriz (mostrado como "acceso total")
- [ ] Acceso solo Administrador; Supervisor ⇒ 403 en `/configuracion/seguridad`
- [ ] Ítem en dropdown del header; dark mode OK; tuteo

---

## QA manual

1. Login admin → header → "Configuración de seguridad".
2. Crear rol "Vendedor" → aparece en select de Usuarios.
3. Tab Permisos: a Vendedor marcar `cotizaciones.ver` + `cotizaciones.gestionar` → guardar.
4. Quitar `cotizaciones.gestionar` → siguiente request del usuario Vendedor ya no edita (caché flusheada).
5. Eliminar rol con usuario asignado → bloqueado; reasignar → se elimina.
6. Administrador no aparece editable; Supervisor sí editable pero no renombrable/eliminable.
7. Login Supervisor → `/configuracion/seguridad` ⇒ 403.
8. Dark mode: matriz, tabs, badges "Sistema" con contraste correcto.

---

## Instrucciones para el ejecutor

1. Lee el spec (§2 punto 7 + Rutas/UI, §3 Módulo 5, §4 QA).
2. Confirma TASK-035 y TASK-037 en `completed/`.
3. Verifica el Codebase Contract con `grep`/`read` (líneas de header/web.php cambian fácil).
4. `Status: in-progress`, `Assigned-to: <tu-nombre>`.
5. Rama: `git checkout -b feat/TASK-039-pagina-seguridad`.
6. Implementa, QA, mueve a `completed/`, rellena Nota.
7. PR.

> Coordinación: esta task y TASK-038 editan `routes/web.php` y `header.blade.php`.
> Coordinar el orden de merge para evitar conflictos.

---

## Nota de Completitud

**Completado por**: Emmanuel
**Fecha**: 2026-06-17
**Commits**: (rama `feat/TASK-039-pagina-seguridad`)
**Notas**:
- `SeguridadController`: index + CRUD roles (storeRol/updateRol/destroyRol) + matriz (getPermisos/guardarMatriz con `sync` reemplazante + `Cache::forget("permisos.rol_{id}")`). Reglas: es_sistema no editable/eliminable, rol con usuarios no eliminable, Administrador no editable en matriz, `ver` forzado como prerrequisito server-side, solo claves válidas del registry.
- 6 rutas `seguridad.*` registradas. Vistas en `admin/seguridad/` (index + 3 partials + scripts IIFE). Ítem en dropdown del header + enlace en el nav de config. CSS en custom.css (dark mode incluido). Validado en navegador por Santi.

**Desviaciones del spec**:
1. **Layout integrado al shell de Configuración** (decisión de Santi): en vez de una página standalone, la página de seguridad reusa el shell de dos columnas de FEAT-004. Se extrajo el nav-pills a un partial compartido `configuracion/partials/nav.blade.php` (modo `tabs` en la página de config, modo `links` en seguridad/otras). La página de config se refactorizó para usarlo (sin cambio funcional).
2. **Gate `acceso-seguridad` + grupo de rutas fuera de `permiso`** (no contemplado en el contract porque se escribió pre-TASK-038): como TASK-038 aplicó `permiso` a TODO el grupo `auth` y este hace deny-by-default (403) ANTES del bypass admin, las rutas de seguridad van en un grupo aparte SIN `permiso`, protegidas por `can:acceso-seguridad` (gate nuevo en AuthServiceProvider, solo-admin). Se mantiene FUERA de `config/modulos.php` a propósito: el acceso al panel NO es otorgable desde la matriz (anti-escalada). Cumple el requisito del spec "panel solo-admin, no por `permiso`".
