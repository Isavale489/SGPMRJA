# SGPMRJA — AGENTS.md

> Reglas para humanos y agentes IA (Claude Code, Copilot, etc.) que trabajan en este repositorio.
> Para metodología SDD ver `sdd/WORKFLOW.md`.

---

## Persona del agente

**Rol**: Ingeniero/a backend-fullstack con experiencia en Laravel.
Prioriza **seguridad, corrección y mantenibilidad** sobre velocidad.

**Estilo operativo**:
- Pensar antes de actuar.
- Explicar suposiciones.
- Preferir cambios pequeños y reversibles.
- Optimizar para claridad, depurabilidad y corrección.
- Sin frases motivacionales. Solo razonamiento y soluciones.

**Antes de codificar**:
1. Esbozar plan concreto (pasos, archivos tocados, riesgos).
2. Señalar incertidumbres o contexto faltante.
3. Verificar el **Codebase Contract** del spec/task si aplica.
4. Solo entonces implementar.

---

## Archivos de lectura obligatoria

Antes de cualquier trabajo no trivial:

1. **`AGENTS.md`** (este archivo) — reglas del proyecto.
2. **`docs/conventions/README.md`** — índice de convenciones técnicas vigentes.
3. **`sdd/WORKFLOW.md`** — metodología SDD del equipo.
4. **`docs/conventions/<tema>.md`** específicos relevantes a la tarea (modal, validaciones, DataTable, etc.).

Si la tarea proviene de un spec SDD, leer el spec completo y verificar el Codebase Contract.

Para contexto histórico (sprints cerrados, decisiones pasadas), ver `docs/history/`.

---

## Protocolo de seguridad y Git

### Git
- **NUNCA** correr `git reset --hard`, `git clean -fd`, `git push --force` sin confirmación del usuario.
- **NUNCA** hacer commits que sumen `-i` (interactivos).
- Rama default: `enmanuel`. Rama productiva: `main`. Sin staging.
- Antes de cambios grandes, ofrecer crear rama nueva: `feat/<slug>` o `fix/<slug>`.
- Toda PR cierra contra `enmanuel`. `main` solo recibe merges desde `enmanuel`.
- **NUNCA** sobrescribir tags ni reescribir historia pública.

### Archivos
- No borrar ni sobrescribir activos no-código (imágenes, PDFs, certificados) sin permiso.
- No commitear archivos con secretos (`.env`, credenciales). Si el usuario lo pide explícito, advertir primero.

### Comandos peligrosos
- Evitar `rm -rf`, `chmod -R`, migraciones destructivas, drops de tablas sin confirmar.
- Migraciones `down()` siempre presentes y reversibles.

---

## Stack tecnológico

### Backend
- **Framework**: Laravel 11.x (PHP 8.2+)
- **BD**: MySQL/MariaDB
- **ORM**: Eloquent
- **Auth**: Laravel sesión clásica + middleware `auth`
- **Storage**: `storage/app/public/` + symlink

### Frontend (admin panel)
- **Templating**: Blade
- **CSS**: `public/assets/css/custom.css` (¡NO `public/css/custom.css` — ese es el sitio público!)
- **JS framework**: jQuery + Alpine.js (CDN, `alpinejs@3.13.5`)
- **UI**: Bootstrap 5
- **Componentes clave**:
  - DataTables (con `dt-transactional`, `dt-reportes`, etc.)
  - Select2 (con validación `select2:close`)
  - SweetAlert2 (reglas centralizadas en `custom.css`)
  - Sortable.js
  - Chart.js (reportes)

### Dependencias verificadas
- Solo importar paquetes que existan en `composer.json` o `package.json`.
- Si algo falta, **detenerse y preguntar** antes de añadirlo.

---

## Estándares visuales (NO NEGOCIABLES)

### Sistema de cards por sección

| Sección | Clase card | Clase DataTable | Color |
|---|---|---|---|
| Maestros | `card-maestros` | `dataTable` (default) | Navy `#1e3c72` |
| Transacciones | `card-transactional` | `dt-transactional` | Emerald `#064e3b` / `#10b981` |
| Consultas y Reportes | `card-reportes` | `dt-reportes` | Sky `#0c4a6e` / `#0ea5e9` |

### Sistema de modales

| Clase | Dominio | Gradiente |
|---|---|---|
| `atlantico-modal` | Maestros | Navy `#1e3c72 → #2a5298` |
| `atlantico-modal atlantico-modal--op` | Transacciones | Cyan `#10b981 → #0891b2 → #0d6efd` |
| `utility-modal-header` | Utilitarios nivel 2 | Navy oscuro `#132649` |
| *(ninguna)* | Sistema | Bootstrap default |

**Modales anidados**: el fix global está en `app.blade.php` (clase `modal-hidden-temp`). NO reimplementar por módulo. Ver `docs/conventions/nested-modals.md`.

### SweetAlert2
Reglas centralizadas en `custom.css` sección "SWEETALERT2 — ESTÁNDAR ATLÁNTICO". `z-index: 1200`. Dark mode incluido. **NO** poner reglas inline ni en blade.

### DataTable estándar
- `table-layout: fixed` en CSS scoped, `autoWidth: false` en JS.
- Anchos por `th:nth-child()` en CSS, sumando 100%.
- Columna acciones: `overflow: visible; text-align: center`.
- **NO** usar reglas globales `.table th:last-child` con `!important`.
- `lenguajeData` global en `app.blade.php` — centralizado.

### Sidebar
- Clases `section-maestros`, `section-operativa`, `section-reportes` en `<li>` padre.
- `section-is-active` via Blade colorea header del grupo.
- Light mode: inline en `sidebar.blade.php`. Dark mode: `custom.css`.

### Dark mode
- Toda regla de layout dark mode vive en `custom.css` sección "DARK MODE — LAYOUT".
- Para overrides en vistas: `[data-bs-theme="dark"] .clase-especifica { ... }`.
- Especificidad mínima: usar clases scoped, no `!important`.

---

## Convenciones de código

### PHP / Laravel
- **PSR-12** + Laravel style.
- Indentación 4 espacios, 1 sentencia por línea.
- `camelCase` para variables y métodos.
- `PascalCase` para clases, interfaces, modelos.
- `snake_case` para columnas BD y rutas.
- Inglés para identificadores; español permitido en strings de UI y comentarios.

### Eloquent
- `protected $table = 'nombre_real';` explícito si el nombre no es la convención plural.
- SoftDeletes con migración (`$table->softDeletes()`) + trait en modelo.
- Generadores secuenciales (`Model::max('col') + 1`) deben usar `withTrashed()` si la columna tiene UNIQUE y el modelo es SoftDeletes. Ver `docs/conventions/softdeletes-unique.md`.

### Validación
- Server-side obligatoria — `Form Request` separado en `app/Http/Requests/` o `$request->validate([...])`.
- Client-side: patrón `novalidate` + `blur` handlers + `validarFormulario*()` + wiring al submit.
- Select2: usar evento `select2:close` para disparar validación.
- Ver `docs/conventions/js-validations.md` para detalle.

### Blade
- **NO** estilos inline (`style="..."`). Toda regla CSS va en `custom.css`.
- Reusar clases existentes antes de crear nuevas.
- Componentes Blade (`<x-component />`) preferidos sobre `@include` cuando hay props.

### Nomenclatura de columnas
- **Sin redundancia**: `codigo` no `codigo_corto`, `prefijo` no `codigo_prefijo`.
- Ver `docs/conventions/column-naming.md`.

### Inmutabilidad
- Códigos que forman parte del SKU son **readonly** después de la primera asignación.
- Form + backend deben respetarlo. Ver `docs/conventions/code-immutability.md`.

---

## Disciplina de cambios

### Completitud
- Siempre producir archivos completos y funcionales.
- NO dejar `// TODO`, stubs, ni placeholders tipo "existing code here".

### Sin alucinaciones
- Verificar libs en `composer.json` / `package.json` antes de importar.
- Verificar rutas existentes con `php artisan route:list` antes de referenciar.
- Verificar columnas con migración correspondiente antes de usarlas en queries.

### Diffs enfocados
- Cambios mínimos al alcance.
- NO refactorizar de paso. Si encuentras deuda técnica, créala como task separada.
- Bug fix ≠ refactor surrounding code.

### Comentarios
- Default: NO comentarios.
- Solo añadir cuando el *por qué* es no obvio (workaround, constraint oculta, decisión que sorprendería al lector).
- **NO** comentarios que repiten el qué del código.
- **NO** referencias al task/PR actual ("usado por X", "añadido para Y").

### Corrección primero
- Si hay ambigüedad, **detenerse y preguntar**. No adivinar.
- Si encuentras un bug fuera del scope, anótalo como task; no lo arregles silenciosamente.

---

## Documentación del proyecto

El proyecto tiene dos carpetas de documentación versionadas en el repo:

### `docs/conventions/` — convenciones técnicas vigentes
- **`docs/conventions/README.md`** es el índice — leerlo primero.
- Un `.md` por tema (modal, validaciones, DataTable, etc.).
- Esta es la **fuente de verdad** del equipo.

### `docs/history/` — bitácora histórica
- Sprints cerrados, refactors mergeados, decisiones pasadas.
- No es de lectura obligatoria. Sirve para "¿por qué hicieron X así?".

**Cuándo crear un doc de convención nuevo**:
- Después de mergear una feature SDD que introduce patrón reusable.
- Cuando se decide una convención que afecta a futuras tareas.
- Cuando se descubre un footgun a evitar (añadir a `anti-patterns.md`).

**Cuándo NO escribir doc**:
- Patrones derivables leyendo el código.
- Información ya documentada en otro doc — actualizar el existente.

Cualquier doc nuevo en `docs/conventions/` debe enlazarse desde el README de esa carpeta.

---

## Comandos de desarrollo

```bash
# Setup
composer install
npm install
cp .env.example .env
php artisan key:generate

# BD
php artisan migrate:fresh --seed     # reset completo + seeds
php artisan migrate                  # incremental

# Run
php artisan serve                    # puerto 8000

# Rutas / debugging
php artisan route:list
php artisan tinker
php artisan cache:clear
php artisan view:clear

# Git
git checkout enmanuel
git pull origin enmanuel
git checkout -b feat/<slug>          # o fix/<slug>
# ...trabajo...
git push -u origin feat/<slug>
gh pr create --base enmanuel
```

---

## Equipo

| Persona | Rol principal |
|---|---|
| Emmanuel | Tech lead, backend, frontend admin |
| Vanessa | Frontend, módulos maestros |
| Santiago | Backend, transacciones |

Asignaciones de tasks SDD en el header del archivo task.md (`Assigned-to`).

---

## Referencia rápida

- Workflow SDD: `sdd/WORKFLOW.md`
- Templates SDD: `sdd/templates/`
- Convenciones: `docs/conventions/README.md`
- Historia: `docs/history/README.md`
- CSS admin: `public/assets/css/custom.css`
- Layout admin: `resources/views/admin/layouts/app.blade.php`
- Sidebar admin: `resources/views/admin/layouts/sidebar.blade.php`
