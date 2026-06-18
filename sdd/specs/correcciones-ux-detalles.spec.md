---
type: fix
base_branch: enmanuel
---

# Feature Specification: Correcciones UI/UX en vistas de detalle (Cotizaciones)

**Feature ID**: FEAT-006
**Fecha**: 2026-06-18
**Autor**: Vane2105
**Status**: shipped
**Versión objetivo**: Sprint actual

---

## 1. Motivación y requisitos de negocio

> ¿Por qué existe esta feature? ¿Qué problema resuelve?

### Planteamiento del problema

Tras migrar el detalle de Cotizaciones al patrón de **wizard** (`#viewModal` en `modals.blade.php`), se introdujeron y/o quedaron sin resolver cuatro defectos de UI/UX en las vistas de detalle ("Ver"). Citando al solicitante:

1. **Campos dinámicos según tipo de cliente** — En las vistas de detalle (Cotizaciones), la interfaz debe detectar de forma reactiva si el cliente es **jurídico**. Si lo es, **no** debe mostrar campos vacíos como "Apellido" (que hoy sale como **"N/A"**), sino mostrar únicamente la **Razón Social**. *(Se rompió al cambiar al componente wizard.)*
2. **Desbordamiento de texto (overflow)** — El campo de **correo electrónico** se desborda de su contenedor cuando el correo es muy largo.
3. **Botón de exportar PDF** — Mejorar visualmente y/o reubicar el botón de PDF en la vista de Cotizaciones, ya que "se ve feo ahí".
4. **Separación visual** — Mantener/aplicar la estructura de separar visualmente las secciones dentro de las tarjetas de detalle (como en el ejemplo de "Detalles del Insumo", patrón **hero + cards**).

### Objetivos

- Que el detalle "Ver" de Cotizaciones muestre **Razón Social** y **oculte "Apellido"** cuando el cliente es jurídico (prefijo de documento `J-` o `G-`); para naturales (`V-`, `E-`), conservar Nombre + Apellido.
- Eliminar el desbordamiento del email (y, por consistencia, otros campos largos) con manejo CSS de quiebre de texto.
- Unificar el botón de exportar PDF **dentro de las ventanas detalle** (Cotizaciones + Compras) a un único estilo coherente.
- Separar visualmente las secciones dentro del detalle de Cotizaciones (tarjetas coherentes, sensación "Detalles del Insumo"), respetando que es un wizard de pasos.

### Fuera de alcance (No-Goals)

- **No** se cambia el backend de Cotizaciones: el endpoint `GET /cotizaciones/{id}` ya expone `tipo_documento` y `razon_social` (ver Codebase Contract). Las correcciones de campos dinámicos son **front-end**.
- **No** se modifica el wizard de **creación/edición** (`#showModal`) más allá del overflow de email; su card de cliente ya usa `razon_social` correctamente.
- **No** se rediseña el PDF generado (`cotizacionPdf` / `reportePdf`), solo el botón que lo dispara.
- **No** se toca la lógica de negocio de estados de cotización ni los filtros del listado.
- **No** se crean migraciones, modelos ni rutas nuevas.

---

## 2. Diseño arquitectónico

### Resumen

Cambios localizados en la capa de presentación de Cotizaciones (Blade + JS de vista + CSS). No hay cambios de controller, modelo, rutas ni BD. El payload del detalle ya trae los campos necesarios para la lógica jurídico/natural; la reactividad se resuelve en el handler JS del botón "Ver".

### Diagrama de componentes

```
GET /cotizaciones/{id}  ──→ CotizacionController::show()  ──→ JSON { cliente: {nombre, apellido,
   (sin cambios)                  (sin cambios)                     email, documento, tipo_documento,
                                                                    razon_social, eliminado, ... } }
        │
        ▼
scripts/main.blade.php  (handler .view-btn)  ── decide jurídico/natural, puebla #viewModal
        │
        ▼
modals.blade.php (#viewModal)  +  custom.css (.cli-view-*, overflow email)
```

### Puntos de integración

| Componente existente | Tipo de integración | Notas |
|---|---|---|
| `resources/views/admin/cotizaciones/scripts/main.blade.php` | modifica | Handler `.view-btn` (≈ línea 2745-2767): lógica jurídico/natural reactiva |
| `resources/views/admin/cotizaciones/modals.blade.php` | modifica | `#viewModal` Paso 1: markup del cliente (hero+cards), wrapper Apellido ocultable, botón PDF |
| `resources/views/admin/compras/modals/view.blade.php` | modifica | Botón PDF del detalle `#cv-pdf-btn` (línea 238-239) — unificar estilo con Cotizaciones |
| `public/assets/css/custom.css` | modifica | Overflow de email; estilo de tarjetas de sección del detalle |

### Modelos de datos

Sin cambios. No hay migraciones.

### Rutas nuevas

Ninguna. Se reutilizan las existentes (ver Codebase Contract).

### UI / Vistas

- Patrón de detalle: **hero + cards** (`.cli-view-hero`, `.cli-view-card`, `.cli-view-sections`) — ya definido en `public/assets/css/custom.css:8689-8898` y aplicado en 5 módulos maestros (commit `ac9e556`/`9127b0d`).
- Iconos de dato: patrón `emp-icon-box emp-icon-box--navy` + label `text-muted fs-12` + valor `fw-semibold fs-13` (ya usado en `#viewModal`).
- Dark mode: overrides ya existentes para `.cli-view-*`; verificar contraste de los campos nuevos.

---

## 3. Desglose por módulos

> Cada módulo se convertirá en al menos una TASK en Fase 2.

### Módulo 1: Campos dinámicos jurídico/natural en detalle de Cotizaciones
- **Path**: `resources/views/admin/cotizaciones/scripts/main.blade.php` (handler `.view-btn`) + `resources/views/admin/cotizaciones/modals.blade.php` (`#viewModal` Paso 1)
- **Responsabilidad**: Detectar jurídico por `tipo_documento ∈ {J-, G-}`. Si jurídico → `#view-cliente-nombre` = `razon_social`, **ocultar** el bloque "Apellido" (no mostrar "N/A"). Si natural → comportamiento actual (Nombre + Apellido). Envolver el `col` de Apellido en un contenedor con `id` para poder ocultarlo (`d-none`).
- **Depende de**: payload existente de `show()` (ya trae `tipo_documento`, `razon_social`).

### Módulo 2: Overflow de email (y campos largos) en cards de detalle
- **Path**: `public/assets/css/custom.css`
- **Responsabilidad**: Aplicar `overflow-wrap:anywhere` / `word-break:break-word` (+ `min-width:0` en el flex item) a `#view-cliente-email` y al item de contacto del card de edición (`.cot-cliente-contact-item`), para que correos largos quiebren en vez de desbordar.
- **Depende de**: nada.

### Módulo 3: Botón de exportar PDF en ventanas detalle — estilo unificado
- **Path**: `resources/views/admin/cotizaciones/modals.blade.php` (`#view-pdf-btn`, línea 216-218) + `resources/views/admin/compras/modals/view.blade.php` (`#cv-pdf-btn`, línea 238-239)
- **Responsabilidad**: Unificar el botón PDF **dentro de las ventanas detalle (footer del wizard)** a **un único estilo coherente**. Hoy Cotizaciones usa `btn-sm btn-warning` (amarillo) y Compras `btn-success btn-sm` (verde) → descoordinado y "se ve feo". Definir un estilo común (recomendado: `btn-sm` con outline/neutro legible en el footer, mismo ícono `ri-file-pdf-line` y misma etiqueta).
- **Fuera de este módulo**: el botón "Exportar PDF" del **índice** (`index.blade.php:47`, `btn-danger`, abre `#pdfExportModal`) se mantiene sin cambios — es otra acción (reporte filtrado), no la del detalle.
- **Depende de**: nada. *(Decisión §8 cerrada: alcance = botón PDF del detalle en Cotizaciones + Compras.)*

### Módulo 4: Separación visual de secciones dentro del detalle de Cotizaciones
- **Path**: `resources/views/admin/cotizaciones/modals.blade.php` (`#viewModal`)
- **Responsabilidad**: Mejorar la **separación visual entre secciones** del detalle (Cliente / Datos de la cotización / Productos / Resumen) con tarjetas coherentes y limpias, replicando la sensación de "Detalles del Insumo". **NO** es una migración literal del hero `cli-view-*`: el detalle de Cotizaciones es un **wizard de pasos**, paradigma distinto al detalle de página única de los módulos maestros. Se busca consistencia visual de secciones, no clonar el hero.
- **Depende de**: Módulo 1 (toca el mismo markup del cliente, Paso 1) → **secuenciar: Módulo 1 antes que Módulo 4, o hacerlos en una sola task** para evitar conflicto.
- **Fuera de este módulo**: la **unificación de los 5 wizards de Gestión Operativa** (footer/botón, hero persistente, tamaño de modal) es una decisión separada y mayor ya diagnosticada aparte — NO se aborda aquí.

---

## 4. Test / QA Specification

### QA manual (golden path)

1. Login como admin → `/cotizaciones` → click "Ver" en una cotización de **cliente natural (V-)** → Paso 1 muestra **Nombre + Apellido** poblados, sin "N/A" indebido.
2. Click "Ver" en una cotización de **cliente jurídico (J- o G-)** → Paso 1 muestra **Razón Social** en el campo principal y **NO** muestra el campo "Apellido" (ni "N/A").
3. Cotización con **email largo** (p. ej. `nombre.muy.largo.apellido@dominio-corporativo-extenso.com.ve`) → el email **quiebra dentro del card**, sin desbordar el contenedor ni romper el layout.
4. Botón "Exportar PDF" en la vista → se ve **coherente** con el resto de la UI; sigue abriendo el modal de exportación / generando el PDF correctamente.
5. Detalle "Ver" → las secciones (Cliente / Datos de la cotización / Productos / Resumen) se ven **separadas visualmente** como en "Detalles del Insumo".

### Edge cases a verificar

- Cliente jurídico **sin** `razon_social` cargada → fallback razonable (mostrar `nombre` o documento, nunca "N/A" crudo en Apellido).
- Cliente **eliminado** (soft-deleted): conservar el badge "Eliminado" y el atenuado `text-muted` actual junto con la lógica jurídico/natural.
- Cliente no encontrado (`data.cliente` null): conservar el mensaje "Cliente no encontrado".
- Email vacío → mostrar "N/A" (o guion) sin romper estilos de quiebre.

### Dark mode

- Verificar contraste del hero, cards y campos del cliente en `[data-bs-theme="dark"]` (las reglas dark de `.cli-view-*` ya existen; confirmar que los elementos nuevos las heredan).
- Botón PDF legible en dark mode.

---

## 5. Criterios de aceptación

> Esta feature está completa cuando TODO lo siguiente es verdadero:

- [ ] Detalle "Ver" de cliente **jurídico** muestra Razón Social y oculta "Apellido" (sin "N/A"); **natural** conserva Nombre + Apellido.
- [ ] Email largo quiebra dentro del card en detalle (y en card de edición); no hay desbordamiento horizontal.
- [ ] Botón "Exportar PDF" reubicado/restilizado y coherente con la UI; sigue funcionando.
- [ ] Secciones del detalle separadas visualmente con patrón `cli-view-*` (hero + cards).
- [ ] Dark mode funcional sin estilos inline; CSS nuevo vive en `public/assets/css/custom.css`.
- [ ] QA manual (§4) pasa, incluyendo edge cases.
- [ ] PR mergeada a `enmanuel`.

---

## 6. Codebase Contract

> **CRÍTICO — anclaje anti-alucinación.** Verificado por lectura directa del código en `enmanuel` (2026-06-18).

### Imports / endpoints verificados

```php
// app/Http/Controllers/CotizacionController.php:235  — public function show($id)
//   Carga 'cliente' => withTrashed()->with('persona')
//   Devuelve JSON con $response['cliente'] = [
//     'id', 'nombre', 'apellido', 'email', 'telefono', 'documento',
//     'tipo_documento'  => optional($cliente->persona)->tipo_documento,  // :256  ('V-','J-','E-','G-')
//     'razon_social'    => optional($cliente->persona)->razon_social,    // :257
//     'direccion', 'ciudad', 'eliminado' (bool)
//   ];
// → El payload del detalle YA trae tipo_documento y razon_social. NO hace falta tocar el backend.

// routes/web.php
//   GET  /cotizaciones/{cotizacion}     → CotizacionController::show
//   GET  /cotizaciones/{cotizacion}/pdf → CotizacionController::cotizacionPdf   (:173)
//   GET  /cotizaciones/reporte/pdf      → CotizacionController::reportePdf      (:170)
```

### Firmas / estructuras existentes a usar

```text
# resources/views/admin/cotizaciones/modals.blade.php
  #viewModal  (modal "Ver", wizard read-only 3 pasos) ........ líneas 1-235
    Paso 1 "Cliente" .......................................... líneas 43-140
      #view-cliente-nombre ........... :64   (col-6)
      #view-cliente-apellido ......... :71   (col-6)  ← bloque a envolver/ocultar para jurídicos
      #view-ci-rif (documento) ....... :78
      #view-cliente-telefono ......... :85
      #view-cliente-email ............ :92   (col-12, .fw-semibold .fs-13)  ← overflow
    Botón PDF detalle  #view-pdf-btn .. líneas 216-218  (.btn .btn-sm .btn-warning, footer modal)

# resources/views/admin/cotizaciones/scripts/main.blade.php
  Handler $('#cotizaciones-table').on('click', '.view-btn', ...) ... línea 2745
    nombreHtml = data.cliente.nombre || 'N/A' ..................... :2753  ← NO usa razon_social (BUG)
    $('#view-cliente-apellido').html(vm(data.cliente.apellido)) ... :2760  ← muestra 'N/A' en jurídicos (BUG)
    $('#view-cliente-email').html(vm(data.cliente.email)) ......... :2761
    $('#view-pdf-btn').attr('href', '/cotizaciones/'+id+'/pdf') ... :2806
  Patrón jurídico YA usado en otras partes del mismo archivo (referencia de estilo):
    const isJuridico = p.tipo_documento === 'J-' || p.tipo_documento === 'G-'; ... :3068
    nombreMostrar = isJuridico && razon_social ? razon_social : ... ............... :3029, :3111, :3641

# resources/views/admin/cotizaciones/index.blade.php
  Botón "Exportar PDF" reporte (abre #pdfExportModal) ... líneas 47-49  (.btn .btn-danger .ms-2)  ← NO se toca

# resources/views/admin/compras/modals/view.blade.php
  Botón PDF del detalle  #cv-pdf-btn ............. líneas 238-239  (.btn .btn-success .btn-sm)  ← unificar con cotizaciones
  (href poblado en compras/scripts/main.blade.php:196)

# public/assets/css/custom.css
  .cot-cliente-contact-row / .cot-cliente-contact-item ........ líneas 2533-2546
     (flex, SIN text-overflow/word-break → permite overflow)
  Patrón detalle hero + cards (.cli-view-hero, .cli-view-card,
     .cli-view-card-header/body, .cli-view-sections, dark mode) .. líneas 8689-8898
     Referencia de uso en markup: resources/views/admin/insumos/index.blade.php:199-294
```

### Convenciones a respetar (ver `docs/conventions/`)
- `docs/conventions/README.md` — índice de convenciones.
- `docs/conventions/wizard-pattern.md` — arquitectura `.wiz-*` del wizard de cotizaciones.
- `docs/conventions/modal-system.md` — clases de modales.
- `AGENTS.md` § Estándares visuales — cards por sección, dark mode en `custom.css` sin `!important`.
- Patrón "Ver hero + cards": **no tiene doc formal aún** (solo código + MEMORY.md). Considerar documentarlo si esta feature lo consolida (ver §8).
- Convención del proyecto: el **Documento va primero** en bloques de identificación (memoria `feedback_documento_primero`).

### NO existe — no referenciar
- ~~Campo `tipo_persona` / `razon_social` en el modelo `Cliente`~~ — viven en `Persona` (`tipo_documento`, `razon_social`, `nombre`, `apellido`). `Cliente` los expone vía accessors / el controller los inyecta en el JSON.
- ~~Accessor "es jurídico" en `Cliente`~~ — no existe; la detección se hace en JS por prefijo de `tipo_documento` (`J-`/`G-`), como ya se hace en el resto de `main.blade.php`.
- ~~Doc `docs/conventions/detail-view-hero-cards.md`~~ — aún no existe.

---

## 7. Notas de implementación y restricciones

### Patrones a seguir
- Detección jurídico en JS: `var esJuridico = ['J-','G-'].includes(String(data.cliente.tipo_documento||'').toUpperCase());` — reutilizar el criterio ya presente en el archivo (`:3068`).
- Para ocultar "Apellido" en jurídicos: envolver el `col-6` de Apellido (`modals.blade.php:66-72`) en un contenedor con `id` (p. ej. `#view-apellido-wrap`) y togglear `d-none` desde el handler; **no** borrarlo del DOM.
- Overflow: en el flex item, además de `overflow-wrap:anywhere`, añadir `min-width:0` al padre flex para que el quiebre surta efecto.
- CSS solo en `public/assets/css/custom.css`; sin estilos inline; dark mode sin `!important`.
- Reusar `.cli-view-*` existentes; **no** duplicar definiciones de CSS.

### Riesgos conocidos
| Riesgo | Mitigación |
|---|---|
| Módulo 1 y Módulo 4 editan el mismo markup del cliente | Secuenciar (1 antes de 4) o fusionarlos en una sola task |
| Regresión en cliente eliminado / no encontrado | Mantener ramas `eliminado` y `else` del handler actual |
| Jurídico sin `razon_social` | Fallback a `nombre`/documento, nunca "N/A" en Apellido |

### Dependencias externas
| Paquete | Versión | Razón |
|---|---|---|
| — | — | sin dependencias nuevas |

---

## 8. Preguntas abiertas

> Resueltas con el solicitante (2026-06-18).

- [x] **Botón PDF**: el "feo" es el de **dentro de las ventanas detalle**. Decisión: **unificar a un solo estilo** el botón PDF del detalle de **Cotizaciones** (`btn-warning`) y **Compras** (`btn-success`). El `Exportar PDF` del índice (`btn-danger`) queda igual. *(Ver Módulo 3.)*
- [x] **Alcance hero+cards**: el detalle de Cotizaciones es un **wizard**, no aplica el hero literal de los maestros. Decisión: **separación visual coherente de secciones** dentro del detalle de Cotizaciones (no migración del hero). Unificación de los 5 wizards = decisión separada, fuera de scope. *(Ver Módulo 4.)*
- [x] **Replicar campos dinámicos en otros detalles**: **No**. Verificado que **Pedidos** renderiza nombre combinado (`cliente_nombre_completo`, `scripts/listado.blade.php:427`) sin campo "Apellido" separado → no tiene el bug; **Órdenes** no muestra nombre/apellido de cliente así. La corrección se limita a **Cotizaciones**.
- [ ] ¿Formalizar `docs/conventions/detail-view-hero-cards.md`? — diferido; no es parte de esta feature (el patrón maestro ya está estable; los wizards van por la unificación separada). *Owner: equipo.*

---

## Historial de revisiones

| Versión | Fecha | Autor | Cambio |
|---|---|---|---|
| 0.1 | 2026-06-18 | Vane2105 | Borrador inicial (4 correcciones UI/UX de detalle en Cotizaciones) |
| 0.2 | 2026-06-18 | Vane2105 | Resueltas preguntas abiertas (PDF detalle cotiz+compras; campos dinámicos solo cotizaciones; separación de secciones sin migrar hero). Status → approved |
| 1.0 | 2026-06-18 | Vane2105 | Implementadas TASK-042..045 + TASK-046 (botón PDF en detalle de Pedidos, mejora de consistencia). Status → shipped |
| 1.1 | 2026-06-18 | Vane2105 | TASK-047: fix del 500 en el PDF de Pedidos con producto dinámico (bug preexistente detectado vía TASK-046) |
| 1.2 | 2026-06-18 | Vane2105 | TASK-048: el PDF del detalle se ve y comporta igual que "Exportar PDF" del índice (abre #pdfExportModal); supersede la decisión visual de TASK-044 |
