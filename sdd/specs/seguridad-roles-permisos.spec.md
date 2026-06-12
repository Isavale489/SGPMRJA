---
type: feature        # feature | fix
base_branch: enmanuel
---

# Feature Specification: Configuración de Seguridad — Roles dinámicos y permisos por módulo

**Feature ID**: FEAT-005
**Fecha**: 2026-06-12
**Autor**: Emmanuel
**Status**: approved
**Versión objetivo**: Sprint Final

---

## 1. Motivación y requisitos de negocio

> ¿Por qué existe esta feature? ¿Qué problema resuelve?

### Planteamiento del problema

Pedido de Emmanuel (2026-06-12): *"me gustaría tener para el admin que pueda
configurar quiénes entran a cada módulo (por sus roles) y qué parte del módulo
pueden entrar — una configuración de seguridad donde el admin pueda elegir
quiénes se meten"*. Decisión de alcance: **roles dinámicos** (el admin crea
roles nuevos), no solo configurar los 2 actuales.

Hoy la seguridad es rígida:

- `user.role` es un **ENUM de 2 valores** (`Administrador`, `Supervisor`) — no se pueden crear roles como Vendedor o Almacenista.
- El acceso está **hardcodeado en `routes/web.php`**: grupo `role:Administrador` (~58 rutas de escritura/CRUD) y grupo `role:Administrador,Supervisor` (~123 rutas operativas).
- Sidebar, header y botones de las vistas consultan `hasRole()`/`isAdmin()` con valores quemados. Un rol nuevo hoy **no vería ningún módulo** (el sidebar entero está dentro de `@if hasRole(['Administrador','Supervisor'])`).

### Objetivos

- **Roles administrables**: tabla `rol` con CRUD para el admin (crear Vendedor, Almacenista, etc.) y asignación de rol a cada usuario desde el módulo Usuarios.
- **Matriz de permisos rol × módulo × acción** configurable: qué módulos ve cada rol y qué acciones puede ejecutar dentro (ej. en Compras: ver / gestionar / procesar / anular / clonar / PDF).
- **Catálogo de módulos y acciones en código** (registry `config/modulos.php`), misma filosofía que FEAT-004: agregar un módulo/acción nueva = una entrada en el registry, la matriz lo renderiza sola.
- **Anti-bloqueo**: el rol Administrador siempre tiene acceso total, no es editable ni eliminable — nadie puede dejarse a sí mismo fuera del panel de seguridad.
- **Paridad al desplegar**: el rol Supervisor nace con permisos equivalentes a su acceso actual; ningún usuario pierde acceso por la migración.
- Página **"Configuración de seguridad"** accesible desde el dropdown "Configuración" del header (el mismo hub de FEAT-004), solo Administrador.

### Fuera de alcance (No-Goals)

- Múltiples roles por usuario — se mantiene **1 rol por usuario** (como hoy).
- Permisos por registro/fila (ej. "este vendedor solo ve sus cotizaciones") — solo módulo + acción.
- Permisos por campo o por columna de tabla.
- Paquete spatie/laravel-permission — descartado: el modelo de 1 rol por usuario + registry propio es más simple y consistente con FEAT-004.
- El panel de parámetros (FEAT-004) — feature hermana e independiente; comparten el hub del dropdown pero no hay dependencia de código.

---

## 2. Diseño arquitectónico

### Resumen

1. **Tabla `rol`**: roles administrables. `Administrador` y `Supervisor` se siembran como roles de sistema (`es_sistema = 1`): no se renombran ni eliminan; Administrador además no aparece editable en la matriz.
2. **`user.role_id`** (FK a `rol`) reemplaza el ENUM `user.role`, con backfill. **Capa de compatibilidad** en el modelo `User`: accessor `role` que devuelve `rol->nombre` + `hasRole()`/`isAdmin()` reimplementados sobre la relación con **firma intacta** → las ~20 lecturas existentes en vistas y controllers siguen funcionando sin tocarse (las queries SQL `where('role', ...)` sí se migran — listadas en el Contract).
3. **Registry `config/modulos.php`**: catálogo de módulos con sus acciones y el mapeo **nombre de ruta → permiso** (ej. `compras.procesar` → permiso `compras.procesar`; `compras.index|data|show` → `compras.ver`).
4. **Tabla `permiso_rol`**: filas (rol_id, permiso) otorgadas. Sin fila = denegado. Administrador no consulta la tabla (bypass).
5. **Autorización en runtime**:
   - `Gate::before` → si `isAdmin()`, todo permitido (anti-lockout estructural).
   - Helper `tienePermiso('compras.procesar')` con caché por rol (flush al guardar la matriz).
   - Middleware nuevo `permiso` que resuelve el permiso requerido **desde el nombre de la ruta actual** vía el registry — se aplica al grupo autenticado completo y reemplaza a los dos grupos `role:` (las rutas casi no se tocan; el mapeo vive en el registry).
6. **UI dirigida por permisos**: sidebar muestra solo los módulos con `*.ver`; botones de acción condicionados por su permiso (reemplaza los `isAdmin()` quemados listados en el Contract).
7. **Página de seguridad**: tab **Roles** (CRUD con modal `atlantico-modal`) + tab **Permisos** (selector de rol → matriz de checkboxes módulo × acción, guardado AJAX).

### Diagrama de componentes

```
config/modulos.php (registry: módulos, acciones, mapeo ruta→permiso)
        │
        ├──→ middleware 'permiso' ──→ tienePermiso() ──→ Cache (por rol) ──→ permiso_rol
        │          ▲                        ▲                                    ▲
        │   grupo auth (web.php)     sidebar / vistas              SeguridadController@guardarMatriz
        │                                                                        │
        │                            Gate::before: isAdmin() ⇒ true (bypass)  ───┘
        │
        └──→ SeguridadController@index ──→ seguridad/index.blade.php
                                              ├── tab Roles (CRUD rol)
                                              └── tab Permisos (matriz checkboxes)

user ──role_id──→ rol ──(softDeletes, es_sistema)
         └─ accessor $user->role ⇒ rol->nombre   (compatibilidad total de lecturas)
```

### Puntos de integración

| Componente existente | Tipo de integración | Notas |
|---|---|---|
| `app/Models/User.php` | modifica | relación `rol()`, accessor `role`, `hasRole()`/`isAdmin()`/`isSupervisor()` sobre la relación (misma firma) |
| `app/Http/Middleware/CheckRole.php` | conserva→retira | convive durante la transición; al final los grupos `role:` se eliminan de web.php |
| `app/Http/Kernel.php:68` | añade | alias `'permiso'` para el middleware nuevo |
| `routes/web.php` (grupos :62 y :142) | modifica | los dos grupos `role:` se funden en el grupo auth con middleware `permiso`; rutas de seguridad nuevas |
| `app/Http/Controllers/UserController.php` | modifica | queries `where('role',...)` → `role_id`/`whereHas` (4 puntos), validaciones, protección último admin se conserva |
| `app/Http/Requests/StoreUserRequest.php:21` / `UpdateUserRequest.php:22` | modifica | `in:Administrador,Supervisor` → `exists:rol,id` |
| `resources/views/admin/users/` (modal + filtros) | modifica | select de rol poblado desde tabla `rol` |
| `resources/views/admin/layouts/sidebar.blade.php:314` | modifica | el gate global `hasRole([...])` se reemplaza por visibilidad por módulo (`*.ver`) |
| `resources/views/admin/layouts/header.blade.php:337-343` | modifica | ítem "Configuración de seguridad" en el dropdown (junto al de FEAT-004) |
| Vistas con `isAdmin()` quemado (ver Contract) | modifica | pasan a `tienePermiso('<modulo>.<accion>')` |
| `database/sistema_atlantico.sql` | actualiza | dump con tablas nuevas y `user` sin ENUM |

### Modelos de datos

```php
// 1) Tabla de roles
Schema::create('rol', function (Blueprint $table) {
    $table->id();
    $table->string('nombre')->unique();          // 'Administrador', 'Supervisor', 'Vendedor'...
    $table->string('descripcion')->nullable();
    $table->boolean('es_sistema')->default(false); // sembrados: no renombrar/eliminar
    $table->softDeletes();
    $table->timestamps();
});

// 2) Permisos otorgados (sin fila = denegado; Administrador no consulta esta tabla)
Schema::create('permiso_rol', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rol_id')->constrained('rol');
    $table->string('permiso');                   // 'compras.procesar', 'cotizaciones.ver'...
    $table->unique(['rol_id', 'permiso']);
    $table->timestamps();
});

// 3) user: ENUM → FK (en la misma migración, con backfill)
//    a. crear filas Administrador/Supervisor en rol (es_sistema = 1)
//    b. add role_id FK nullable → backfill desde el ENUM → NOT NULL
//    c. drop column role (ENUM)
//    d. seed de paridad: permisos del Supervisor = su acceso actual (grupo compartido)
```

```php
// config/modulos.php — forma de cada entrada del registry
'compras' => [
    'nombre'   => 'Compras',
    'acciones' => [
        'ver'       => 'Ver listado y detalle',
        'gestionar' => 'Crear y editar borradores',
        'procesar'  => 'Procesar (afecta inventario)',
        'anular'    => 'Anular',
        'clonar'    => 'Clonar',
        'pdf'       => 'Exportar PDF',
    ],
    // mapeo nombre-de-ruta → acción (patrones; lo consume el middleware 'permiso')
    'rutas' => [
        'compras.index|compras.data|compras.show|compras.detalle' => 'ver',
        'compras.store|compras.update|compras.editar-datos'       => 'gestionar',
        'compras.procesar' => 'procesar',
        'compras.anular'   => 'anular',
        'compras.clonar'   => 'clonar',
        'compras.pdf|compras.reporte.pdf' => 'pdf',
    ],
],
```

### Rutas nuevas

| Verbo | URI | Acción | Nombre |
|---|---|---|---|
| GET | /configuracion/seguridad | index (tabs Roles + Permisos) | seguridad.index |
| POST | /configuracion/seguridad/roles | storeRol | seguridad.roles.store |
| PUT | /configuracion/seguridad/roles/{rol} | updateRol | seguridad.roles.update |
| DELETE | /configuracion/seguridad/roles/{rol} | destroyRol (bloquea es_sistema y roles con usuarios) | seguridad.roles.destroy |
| GET | /configuracion/seguridad/permisos/{rol} | getPermisos (JSON para la matriz) | seguridad.permisos.get |
| PUT | /configuracion/seguridad/permisos/{rol} | guardarMatriz (sync + flush caché) | seguridad.permisos.update |

Protegidas con `can:` / check `isAdmin()` directo (NO con el middleware `permiso` — el panel de seguridad es siempre y solo del Administrador, fuera de la matriz).

### UI / Vistas

- **Página completa** `resources/views/admin/seguridad/` (index + partials + scripts), header navy estándar.
- **Tab Roles**: tabla simple (nombre, descripción, nº usuarios, badge "Sistema") + modal `atlantico-modal` con `#id-field` (AtlanticoGuard aplica solo). Eliminar deshabilitado para `es_sistema` y para roles con usuarios asignados.
- **Tab Permisos**: select de rol (excluye Administrador, mostrado aparte como "acceso total") → matriz módulo × acción con checkboxes; fila de módulo con "marcar todos"; guardar por rol vía AJAX + SweetAlert. La acción `ver` es prerrequisito visual de las demás (si se desmarca, se desmarcan todas las del módulo).
- Español neutro (tuteo) en todos los textos.
- Acceso: dropdown "Configuración" del header → "Configuración de seguridad" (icono `mdi mdi-shield-lock-outline`), dentro del `@if isAdmin()` existente.

---

## 3. Desglose por módulos

> Cada módulo se convertirá en al menos una TASK en Fase 2.

### Módulo 1: Esquema y migración de roles
- **Path**: migraciones `create_rol_table`, `create_permiso_rol_table`, `migrate_user_role_enum_to_role_id`; `app/Models/Rol.php`
- **Responsabilidad**: tablas nuevas, backfill del ENUM, seed de paridad del Supervisor, drop del ENUM
- **Depende de**: nada

### Módulo 2: Capa de compatibilidad en User + consumidores SQL
- **Path**: `app/Models/User.php`, `app/Http/Controllers/UserController.php`, `app/Http/Requests/{Store,Update}UserRequest.php`, vistas de users (select de rol)
- **Responsabilidad**: relación `rol()` + accessor `role` + `hasRole`/`isAdmin`/`isSupervisor` sobre relación; migrar las 4 queries `where('role',...)`; validación `exists:rol,id`; conservar protección "último admin activo" (UserController:145)
- **Depende de**: Módulo 1

### Módulo 3: Registry + autorización runtime
- **Path**: `config/modulos.php`, helper `tienePermiso()` (en `app/Support/helpers.php`, creado por FEAT-004 o aquí), middleware `app/Http/Middleware/CheckPermiso.php`, `app/Http/Kernel.php`, `app/Providers/AuthServiceProvider.php` (`Gate::before`)
- **Responsabilidad**: catálogo completo de módulos/acciones/rutas, caché por rol con flush, middleware que resuelve permiso por nombre de ruta
- **Depende de**: Módulo 1

### Módulo 4: Migración de rutas y UI por permisos
- **Path**: `routes/web.php`, `sidebar.blade.php`, vistas con `isAdmin()`/`hasRole()` quemados (lista en Contract)
- **Responsabilidad**: fundir los grupos `role:` en el grupo auth con middleware `permiso`; sidebar por `*.ver`; botones por acción. QA de regresión: Supervisor conserva exactamente su acceso actual
- **Depende de**: Módulo 3

### Módulo 5: Página Configuración de seguridad
- **Path**: `app/Http/Controllers/SeguridadController.php`, `resources/views/admin/seguridad/`, `routes/web.php`, `header.blade.php`
- **Responsabilidad**: tabs Roles (CRUD) + Permisos (matriz), endpoints JSON, ítem del dropdown
- **Depende de**: Módulos 1 y 3

### Módulo 6 (limpieza): código muerto de roles fantasma
- **Path**: `app/Http/Middleware/CheckAdminRole.php`, `app/Traits/NotificaSecretarias.php`, `app/Jobs/EnviarNotificacionSolicitud.php`
- **Responsabilidad**: eliminar/neutralizar referencias a roles inexistentes ('Administrativa', 'Secretaria') — romperían con el nuevo esquema o quedarían como queries muertas
- **Depende de**: Módulo 2

---

## 4. Test / QA Specification

### QA manual (golden path)
1. Migrar sobre BD existente → usuarios conservan su rol (Administrador/Supervisor) y **todo su acceso actual** (paridad Supervisor).
2. Login Administrador → dropdown header → "Configuración de seguridad".
3. Tab Roles: crear rol "Vendedor" → aparece en el select del modal de Usuarios.
4. Tab Permisos: a Vendedor marcar solo `cotizaciones.ver` y `cotizaciones.gestionar` → guardar.
5. Crear usuario con rol Vendedor → login: el sidebar muestra **solo Cotizaciones**; puede crear/editar cotizaciones; `GET /compras` directo → 403; los botones de acciones que no tiene no se renderizan.
6. Quitar `cotizaciones.gestionar` a Vendedor → sin re-login, el siguiente request ya no permite editar (caché flusheada) y la UI deja de mostrar los botones.
7. Intentar eliminar el rol Vendedor con un usuario asignado → bloqueado con mensaje claro. Reasignar el usuario → ahora sí se elimina.
8. Verificar que Administrador NO aparece editable en la matriz y que `rol` Administrador/Supervisor no se pueden renombrar/eliminar.
9. Login Supervisor → mismo acceso que antes del deploy (muestreo: pedidos, cotizaciones, órdenes, compras, reportes) y SIN acceso a /configuracion/seguridad (403).

### Edge cases a verificar
- Usuario cuyo rol pierde TODOS los permisos → puede loguearse y ve solo el dashboard (sin 500 ni sidebar roto).
- Ruta que no está mapeada en el registry → el middleware deniega por defecto (denegar > permitir) y lo registra en log para detectarlo en QA.
- Protección "último administrador activo" (UserController:145) sigue funcionando con el nuevo esquema.
- `where('role', 'like', ...)` de la búsqueda de usuarios (UserController:46) migrada — buscar "Admin" en /users sigue filtrando.
- PDF de usuarios (`users/reporte_pdf.blade.php:59`) muestra el nombre del rol vía accessor.
- Dos sesiones simultáneas: cambiar la matriz en una → la otra pierde el acceso en su siguiente request.
- `php artisan migrate:fresh` + dump: ambos caminos dejan roles sembrados consistentes.

### Dark mode
- Matriz de permisos (checkboxes, headers de módulo), tabs y badges "Sistema" con contraste correcto.
- Modal de rol hereda estándares `atlantico-modal`.

---

## 5. Criterios de aceptación

> Esta feature está completa cuando TODO lo siguiente es verdadero:

- [ ] Migración corre limpia en BD fresca Y sobre BD existente con usuarios reales (backfill verificado)
- [ ] **Paridad**: tras el deploy, Administrador y Supervisor tienen exactamente el acceso que tenían antes (QA paso 9)
- [ ] Rol nuevo con permisos parciales pasa el golden path completo (pasos 3-7)
- [ ] Imposible quedarse sin acceso: `Gate::before` admin + roles sistema protegidos + último-admin-activo intacto
- [ ] Agregar un módulo/acción al registry lo hace aparecer en la matriz **sin tocar la vista** (verificación del patrón)
- [ ] Los dos grupos `role:` de web.php eliminados; middleware `permiso` cubre las ~180 rutas autenticadas
- [ ] Cero referencias rotas a `user.role` como columna (grep `where('role'` limpio)
- [ ] Código muerto de roles fantasma eliminado (Módulo 6)
- [ ] Dump `database/sistema_atlantico.sql` actualizado
- [ ] PR mergeada a `enmanuel`
- [ ] Doc nuevo `docs/conventions/permissions.md` con el patrón registry + matriz

---

## 6. Codebase Contract

> **CRÍTICO — anclaje anti-alucinación.**
> Esta sección es la única fuente de verdad sobre qué existe en el código.
> Los implementadores (humanos o Claude Code) NO deben referenciar imports,
> rutas, métodos o tablas que no estén listados aquí sin verificarlos primero
> con `grep` o `read`.

### Estado actual del esquema de roles (verificado 2026-06-12)
```php
// BD: user.role — dump línea 1524
`role` enum('Administrador','Supervisor') NOT NULL
// definido por la migración 2025_12_04_134221_update_user_role_enum

// app/Models/User.php
:28   'role' en $fillable
:101  public function isAdmin()        { return $this->role === 'Administrador'; }
:108  public function isSupervisor()   { return $this->role === 'Supervisor'; }
:113  public function hasRole($roles)  // in_array si array, === si string

// app/Http/Middleware/CheckRole.php — alias 'role' en app/Http/Kernel.php:68
public function handle(Request $request, Closure $next, ...$roles): Response
// usa $request->user()->hasRole($roles); aborta 403

// routes/web.php
:47   grupo global: middleware(['auth','throttle:60,1','active.user','recovery.questions.required'])
:62   grupo 'role:Administrador'              → ~58 rutas (CRUDs de escritura, users, etc.)
:142  grupo 'role:Administrador,Supervisor'   → ~123 rutas (operativa: pedidos, cotizaciones, órdenes, compras, reportes)
```

### Consumidores de rol a migrar (queries SQL — ROMPEN al dropear el ENUM)
```php
app/Http/Controllers/UserController.php:24   ->where('role', $request->input('filter_role'))
app/Http/Controllers/UserController.php:46   ->orWhere('role', 'like', "{$keyword}%")
app/Http/Controllers/UserController.php:146  User::where('role','Administrador')->where('estado',1)->count()  // protección último admin
app/Http/Controllers/UserController.php:195  ->where('role', $request->role)                                  // filtro PDF
```

### Lecturas compatibles vía accessor (NO se tocan si existe `getRoleAttribute()`)
```php
app/Http/Controllers/UserController.php:80,113   $user->role = $request->role;   // ← este SÍ cambia (escritura → role_id)
app/Http/Controllers/UserController.php:182      'role' => $user->role,
resources/views/admin/layouts/header.blade.php:310    {{ Auth::user()->role }}
resources/views/profile/edit.blade.php:534            $user->role ?? '—'
resources/views/admin/users/reporte_pdf.blade.php:59  {{ $user->role }}
```

### Gates de UI quemados a migrar a permisos
```
resources/views/admin/layouts/sidebar.blade.php:314      @if hasRole(['Administrador','Supervisor'])  ← gate de TODO el sidebar
resources/views/admin/layouts/header.blade.php:337       @if isAdmin()  (dropdown: usuarios / sistema / seguridad)
resources/views/admin/pedidos/index.blade.php:45         @if isAdmin()
resources/views/admin/pedidos/scripts/listado.blade.php:93   var isAdmin = {{ ... }}  (JS — condiciona botones editar/cancelar)
resources/views/admin/cotizaciones/index.blade.php:41    @if isAdmin()
resources/views/admin/cotizaciones/modals.blade.php:899,1024,1466,1532   @if hasRole([...])
```

### Validaciones del rol en Requests
```php
app/Http/Requests/StoreUserRequest.php:21   'role' => 'required|in:Administrador,Supervisor',
app/Http/Requests/UpdateUserRequest.php:22  'role' => 'required|in:Administrador,Supervisor',
```

### Código MUERTO detectado (roles fantasma — limpiar en Módulo 6)
```php
app/Http/Middleware/CheckAdminRole.php:20        Auth::user()->role === 'Administrativa'   // rol inexistente
app/Traits/NotificaSecretarias.php:17            User::where('role','Secretaria')          // rol inexistente
app/Jobs/EnviarNotificacionSolicitud.php:44      User::where('role','Secretaria')          // rol inexistente
```

### Convenciones a respetar (ver `docs/conventions/`)
- `docs/conventions/modal-system.md` — modal de rol con `atlantico-modal` + `#id-field`
- `docs/conventions/js-validations.md` — validación blur + submit
- `docs/conventions/column-naming.md` — `nombre`, `descripcion`, `permiso` (sin prefijos redundantes)
- `docs/conventions/softdeletes-unique.md` — `rol.nombre` UNIQUE + softDeletes ⇒ cuidado con re-crear nombres
- Tablas en singular (`rol`, `permiso_rol`); FK a usuarios apunta a tabla `user`
- Español neutro (tuteo) en textos de UI

### NO existe — no referenciar
- ~~tabla `rol` / `permiso_rol` / `role` / `permission`~~ — se crean en esta feature
- ~~`App\Models\Rol`~~ — se crea en Módulo 1
- ~~`Gate::define` / `Gate::before` en `AuthServiceProvider`~~ — hoy no hay gates definidos; se introducen aquí
- ~~middleware alias `permiso`~~ — se registra en Módulo 3
- ~~spatie/laravel-permission~~ — descartado explícitamente
- ~~relación `User::rol()`~~ — hoy `role` es columna string, no relación

---

## 7. Notas de implementación y restricciones

### Patrones a seguir
- **Denegar por defecto**: ruta autenticada sin mapeo en el registry ⇒ 403 + log (mejor descubrir un hueco en QA que dejar una puerta abierta).
- **Caché por rol**: key `permisos.rol_{id}` con el array de permisos; `Cache::forget` al guardar la matriz o editar/eliminar el rol. NO cachear por usuario.
- **`Gate::before`** devuelve `true` solo para `isAdmin()`; nunca `false` (deja seguir la cadena).
- El accessor `role` debe hacer eager-load seguro (`$this->rol?->nombre`) — usuarios con rol soft-deleted no deben tirar 500.
- Excepciones del middleware `permiso`: dashboard, perfil, logout y endpoints transversales (`personas-search`, `empleados-get-cargos`, etc.) se declaran en una lista `comunes` del registry, accesibles para todo usuario autenticado.
- Orden de migración de la transición: primero middleware `permiso` conviviendo con `role:` (doble check inofensivo), QA de paridad, y al final retirar los grupos `role:`.

### Riesgos conocidos
| Riesgo | Mitigación |
|---|---|
| Lockout del admin | `Gate::before` + roles sistema inmutables + protección último-admin-activo existente |
| Pérdida de acceso del Supervisor al desplegar | seed de paridad generado DESDE los grupos `role:` actuales + QA paso 9 explícito |
| Ruta olvidada sin mapeo (180+ rutas) | denegar por defecto + log; barrido con `php artisan route:list` contra el registry como checklist de QA |
| Drop del ENUM rompe queries no detectadas | grep `where('role'` en criterios de aceptación; el accessor cubre las lecturas |
| Caché stale entre procesos | una key por rol + flush en cada escritura; QA paso 6 |
| `permiso_rol` huérfanos al soft-deletar rol | eliminar rol exige 0 usuarios; las filas de permisos se borran (`delete`) al eliminar |

### Dependencias externas
| Paquete | Versión | Razón |
|---|---|---|
| — | — | sin dependencias nuevas |

### Relación con FEAT-004
Comparten el hub del dropdown "Configuración" del header y la filosofía
registry-en-código. **No hay dependencia dura**: pueden implementarse en
cualquier orden. Si FEAT-004 se implementa primero, el helper `tienePermiso()`
se suma al `app/Support/helpers.php` que esa feature crea (y viceversa).

---

## 8. Preguntas abiertas

> Resolver antes de mergear. Marcar con [x] al cerrar y dejar la respuesta.

- [x] ¿Roles dinámicos o solo los 2 actuales? — **Resuelto 2026-06-12 (Emmanuel): roles dinámicos, el admin crea roles y los asigna.**
- [x] Granularidad inicial de acciones — **Resuelto 2026-06-12 (Emmanuel): finas en transaccionales (procesar/anular/clonar/formalizar/PDF...), gruesas (ver/gestionar) en maestros.**
- [x] ¿Reportes módulo único o uno por reporte? — **Resuelto 2026-06-12 (Emmanuel): módulo único con permiso `reportes.ver`; subdividir después si hace falta.**
- [x] ¿El Supervisor es editable en la matriz? — **Resuelto 2026-06-12 (Emmanuel): editable — solo Administrador es intocable. `es_sistema` en Supervisor solo impide renombrar/eliminar, NO bloquea su fila en la matriz.**
- [x] ¿Roles a sembrar? — **Resuelto 2026-06-12 (Emmanuel): solo Administrador y Supervisor. Los demás los crea el admin desde el panel cuando los necesite (no se siembran ejemplos).**

---

## Historial de revisiones

| Versión | Fecha | Autor | Cambio |
|---|---|---|---|
| 0.1 | 2026-06-12 | Emmanuel | Borrador inicial — roles dinámicos + matriz de permisos por módulo/acción |
| 1.0 | 2026-06-12 | Emmanuel | Preguntas resueltas (granularidad mixta, reportes único, Supervisor editable, seed solo 2 roles) — status `approved` |
