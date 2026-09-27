# TASK-032: Vista del panel /configuracion — nav-pills, campos por tipo y guardado AJAX

**Feature**: FEAT-004 — Panel de Configuración del Sistema (base)
**Spec**: `sdd/specs/panel-configuracion.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: L (4-8h)
**Depends-on**: TASK-031
**Assigned-to**: emmanuel

---

## Contexto

Implementa el **Módulo 3** del spec: la página del panel, **dirigida por el
registry** — la vista no conoce los parámetros, los itera. Es la pieza que hace
escalable la base: un parámetro nuevo en `config/parametros.php` debe aparecer
solo, sin tocar esta vista.

---

## Scope

- Crear `resources/views/admin/configuracion/index.blade.php`:
  - header de página estándar (navy) + layout de 2 columnas: **nav-pills vertical** a la izquierda (un pill por módulo del registry) y card de contenido a la derecha
  - un `<form>` por módulo con botón Guardar propio
- Crear `resources/views/admin/configuracion/partials/campo.blade.php`:
  - `@switch($parametro['tipo'])`: `decimal`/`entero` → input numérico con min/max derivados de `reglas`; `booleano` → switch Bootstrap; `texto` → input/textarea
  - `descripcion` del registry como help-text bajo el campo
  - badge **"Por defecto: X"** visible cuando `es_default` es true
  - botón **"Restablecer"** por parámetro (visible solo cuando hay override)
- Crear `resources/views/admin/configuracion/scripts/main.blade.php` (IIFE en `@push('scripts')`):
  - validación blur + submit según convención
  - submit AJAX `PUT configuracion.update` del módulo activo → SweetAlert éxito / errores 422 en línea
  - **confirmación SweetAlert antes de guardar el grupo Impuestos** (decisión 2026-06-12): recordar que las compras previas conservan su snapshot; "Cancelar" no envía nada
  - handler de "Restablecer" → confirm → `DELETE configuracion.reset` → repintar campo con el default + badge
- CSS necesario en `public/assets/css/custom.css` (NUNCA inline), con dark mode.

**NO está en alcance**:
- Backend (TASK-031)
- Ítem del dropdown del header (TASK-033)
- Dirty-check de cambios sin guardar (descartado en fase 1 por el spec §7 — AtlanticoGuard no aplica a páginas)

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/configuracion/index.blade.php` | CREATE | Página del panel |
| `resources/views/admin/configuracion/partials/campo.blade.php` | CREATE | Render de un parámetro por tipo |
| `resources/views/admin/configuracion/scripts/main.blade.php` | CREATE | IIFE: validación + AJAX + confirms |
| `public/assets/css/custom.css` | MODIFY | Estilos del panel (light + dark) |

---

## Codebase Contract (Anti-Alucinación)

### Datos verificados
```
- Layout admin: resources/views/admin/layouts/app.blade.php (extender como los demás módulos)
- Scripts por módulo: IIFE dentro de @push('scripts') al final de la vista (convención universal)
- SweetAlert2: YA cargado globalmente en el layout — NO añadir <script> propio
- CSS personalizado: SOLO public/assets/css/custom.css (NO public/css/)
- Datos que entrega el controller (TASK-031 index()): registry agrupado por módulo,
  con valor efectivo y flag es_default por parámetro — confirmar la forma exacta
  leyendo ConfiguracionController@index antes de maquetar
```

### Convenciones a respetar
- `docs/conventions/js-validations.md` — patrón blur + submit con `novalidate`
- `docs/conventions/ux-search-filters.md` / vistas existentes — header navy de página (referencia real: `resources/views/admin/compras/index.blade.php`)
- Español neutro (tuteo) — NUNCA voseo
- Sin estilos inline; dark mode con `[data-bs-theme="dark"]` en custom.css

### NO existe — no referenciar
- ~~AtlanticoGuard en esta página~~ — vigila modales con `#id-field`; aquí NO aplica y NO se reimplementa
- ~~DataTable~~ — esta página no tiene tabla; no cargar dt scripts
- ~~modal alguno~~ — todo es edición en página; no usar `atlantico-modal`
- ~~`window.IVA_TASA`~~ — es de la vista de compras; no leerlo aquí

---

## Notas de implementación

### Restricciones clave
- La vista itera `$modulos` del controller: CERO nombres de parámetros hardcodeados (criterio de aceptación del spec: un parámetro nuevo en el registry aparece sin tocar la vista).
- La confirmación extra de Impuestos NO se hardcodea por módulo en el JS si se puede evitar: sugerencia — flag `confirmar_guardado` + `mensaje_confirmacion` opcionales en el registry, que el JS lee de un `data-` attribute del form (mantiene el patrón registry-driven). Si se complica, hardcodear el caso Impuestos es aceptable en fase 1 (documentarlo).
- El repintado tras "Restablecer" usa el valor default que devuelve el endpoint (TASK-031), no recalcula en cliente.
- Nav-pills: módulo activo persistido en el hash de la URL (`#impuestos`) para que recargar no pierda la pestaña.

---

## Criterios de aceptación

- [ ] `/configuracion` renderiza el pill Impuestos con el campo IVA, help-text y badge "Por defecto: 16"
- [ ] Guardar IVA=8 → confirm SweetAlert → éxito → badge desaparece; recargar → persiste
- [ ] Valor inválido → error en línea (blur) y 422 manejado sin SweetAlert de éxito
- [ ] Restablecer → confirm → campo vuelve a 16 con badge
- [ ] **Prueba del patrón**: añadir un parámetro dummy al registry → aparece en el panel sin tocar Blade/JS (quitarlo después)
- [ ] Dark mode completo; cero estilos inline; español neutro

---

## QA manual

1. Login admin → `/configuracion` → golden path completo del spec §4 (pasos 2, 3 y 6).
2. Probar validación: `-1`, `150`, vacío, texto.
3. Cancelar el confirm de Impuestos → verificar que NO hubo request (network tab).
4. Hash de URL: guardar, recargar con `#impuestos` → pill correcto activo.
5. Toggle dark mode y revisar pills, badges, help-text, botones.

---

## Instrucciones para el ejecutor

1. **Lee el spec** completo y **verifica que TASK-031 está en `tasks/completed/`**.
2. **Verifica el Codebase Contract**; en particular, lee `ConfiguracionController@index` real antes de maquetar.
3. **Actualiza el header**: `Status: in-progress`.
4. **Implementa**; trabajo extra → task nueva.
5. **Verifica** criterios y QA; **mueve a `tasks/completed/`** con Nota de Completitud.

---

## Nota de Completitud

**Completado por**: emmanuel (+ Claude Code)
**Fecha**: 2026-06-12
**Commits**: `1c988fe` (rama `feat/panel-configuracion`)
**Notas**: Implementado completo. La confirmación del guardado quedó
registry-driven (campos opcionales `confirmar_guardado` + `mensaje_confirmacion`
en la entrada del registry → `data-confirmar` en el form), NO hardcodeada por
módulo. QA server-side por render en tinker: página completa OK (pill, form,
input con min/max derivados de reglas, badge default, reset oculto sin override,
help-text, update-url). **Prueba del patrón VERIFICADA**: módulo dummy "Pruebas"
con parámetro booleano inyectado al registry en runtime → pill + form + switch
checked + badge "Por defecto: Sí" aparecieron sin tocar Blade/JS. Hardening
encontrado en el QA: entrada del registry sin `config_key` rompía helper y
partial (`config(null)` devuelve el Repository) — blindado en ambos.
**PENDIENTE: QA en navegador por Emmanuel** (clicks, SweetAlerts, dark mode
visual, hash de URL) — el flujo AJAX completo se prueba junto a TASK-033.

**Desviaciones del spec**: ninguna.
