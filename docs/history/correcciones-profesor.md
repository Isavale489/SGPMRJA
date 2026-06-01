---
name: Correcciones del profesor — Sesión 2026-04-30
description: Estado y avances de las correcciones solicitadas por el profesor tras la revisión del sistema. Incluye 3 correcciones completadas y commits de referencia.
type: project
originSessionId: 97bc1c0e-ea40-4ebb-8e18-f8110ae556eb
---
# Correcciones del profesor — Estado

Sesión iniciada **2026-04-30**. Todas las correcciones se trabajaron en rama `enmanuel` y se mergearon directamente (sin PRs separadas — son fixes incrementales).

## ✅ Corrección 1 — Submenú de RRHH en sidebar (commit `9b9f378`)

**Problema:** Departamento y Cargo solo eran accesibles vía botones modales dentro de `/empleados`. No tenían entrada propia en el sidebar.

**Solución:**
- Vistas dedicadas creadas: `admin/departamentos/index.blade.php` y `admin/cargos/index.blade.php` (catálogo maestro completo: card-maestros + DataTable + modales atlantico-modal CRUD + toggle historial + restaurar)
- `DepartamentoController::index()` y `CargoController::index()` modificados: detectan AJAX vs navegador (mantienen compat con mini-modales en `/empleados`)
- Sidebar: sub-grupo "Recursos Humanos" como chip con gradiente navy `#1e3c72→#2a5298` (mismo del proyecto) que solo se "enciende" cuando estás en `empleados*`, `departamentos*` o `cargos*`. Inactivo es neutro/gris
- Departamentos y Cargos como sub-maestros con líneas guía estilo file-tree (clase `menu-subitem-child`)
- En `/empleados`: botones del header "Departamentos" / "Cargos" convertidos a enlaces sólidos cyan-800/amber-800 hacia los nuevos catálogos. Modales de gestión completa eliminados (~330 líneas JS removidas). Mini-modales `addDepartamentoModal`/`addCargoModal` SE MANTIENEN (creación rápida desde form de empleado)

**Decisiones de diseño tomadas:**
- NO usar dropdowns anidados (sub-submenús): Bootstrap5+Velzon no los soporta nativamente, y suman clics. Estándar 2024-2026 es máx 2 niveles.
- Estilo "Recursos Humanos" activo: píldora con gradiente navy (mismo del atlantico-modal), texto blanco, sin border-left — se diferencia a propósito del patrón "barra lateral + fondo translúcido" de items normales.
- Líneas guía vertical NO se resaltan cuando el sub-item está activo (queda sólo el ítem en navy, sin "ruido" extra).

## ✅ Corrección 2 — Reorganización modal de Proveedores (commit `d614bd6`)

**Problema:** Modal de "Agregar Proveedor" no seguía el estándar de Clientes:
- Documento de Identidad estaba dentro de "Contacto" en natural y dentro de "Datos Empresariales" en jurídico
- Switch "Estatus" estaba arriba en "Identificación" en lugar de al final
- Jurídico no tenía sección "Contacto" propia (Tel+Email mezclados con datos empresariales)
- Estado/Municipio embebidos en "Datos Empresariales" en lugar de sección "Ubicación" propia

**Solución (estructura final):**
- **Identificación**: Documento (RIF jurídico / Cédula natural) PRIMERO + Tipo de Proveedor — wrappers con clases `js-tipo-juridico` / `js-tipo-natural` que togglean por JS según tipo
- **Datos Empresariales** (jurídico): solo Razón Social + Dirección (mitad/mitad)
- **Datos Personales** (natural): solo Nombre + Apellido + Dirección
- **Contacto** (ambos): Teléfono + Email
- **Contacto Secundario** (solo jurídico): Persona + Teléfono Contacto
- **Ubicación** (ambos): Estado + Municipio — JUSTO ANTES de Estatus
- **Estatus** (al final del modal-body, igual que Clientes)

**JS `toggleCampos()` extendido:** ahora considera selectores `'#campos-juridico, .js-tipo-juridico'` y `'#campos-natural, .js-tipo-natural'` para que el toggle de visibilidad y la lógica required/data-required apliquen tanto a los bloques grandes como a los wrappers en Identificación.

## ✅ Corrección 3 — Búsqueda unificada de personas en Cotizaciones (commit `1d02d7e`)

**Problema:** En `/cotizaciones` → "Agregar Cotización" → campo "Documento de identidad", solo buscaba en tabla `cliente`. Si la persona ya estaba registrada como empleado o proveedor, decía "no existe" → duplicaba registros.

**Solución:** Ver `memory/reference_persona_unified_search.md` para detalle COMPLETO del patrón reusable. Resumen:
- `PersonaController@search` en `GET /personas-search` busca en tabla `persona` unificada, devuelve roles activos (cliente/empleado/proveedor_natural/proveedor_juridico)
- `ClienteController@createFromPersona` en `POST /clientes/from-persona/{id}` crea cliente reutilizando persona, idempotente
- JS de cotizaciones: badges por rol (Cliente verde, Empleado cyan, Proveedor amber) + SweetAlert de confirmación cuando la persona NO es cliente

**Decisiones tomadas:**
- Solo activos (excluir soft-deleted)
- Tipo cliente detectado por prefijo: V/E → natural, J → juridico, G → gubernamental
- Endpoint viejo `/clientes-search` no se tocó — sigue intacto

## 🔧 Bugs incidentales arreglados

1. **EmpleadoService — códigos EMP-XXX duplicados** (commit `1d02d7e`)
   Ver `memory/feedback_softdeletes_unique_constraint.md` para regla general.
   `EmpleadoService::crear()` usaba `Empleado::max('codigo_empleado')` para calcular el siguiente código, pero `max()` ignoraba soft-deleted mientras la UNIQUE constraint sí los considera. Fix: `withTrashed()` en max + loop defensivo.

2. **Modelo Persona — relación proveedor faltante** (commit `1d02d7e`)
   `Persona` solo tenía `cliente()` y `empleado()`. Faltaba `proveedor() => hasOne(Proveedor::class)`. Sin esto, el autocomplete unificado fallaba con `RelationNotFoundException`.

3. **ClienteController — JsonResponse sin importar** (commit `1d02d7e`)
   El método `createFromPersona(int $personaId): JsonResponse` declaraba el tipo pero faltaba `use Illuminate\Http\JsonResponse;` → PHP interpretó como `App\Http\Controllers\JsonResponse` → 500 al retornar.

## 📋 Próximas correcciones pendientes (sin definir aún)

El profesor sigue dando correcciones una por una. Cuando llegue la siguiente, esta lista se actualizará.

## Archivos clave modificados en la sesión

- `app/Http/Controllers/PersonaController.php` (NUEVO)
- `app/Http/Controllers/ClienteController.php` (createFromPersona)
- `app/Http/Controllers/DepartamentoController.php` + `CargoController.php` (AJAX/vista toggle)
- `app/Models/Persona.php` (relación proveedor)
- `app/Services/EmpleadoService.php` (withTrashed fix)
- `resources/views/admin/departamentos/index.blade.php` (NUEVO)
- `resources/views/admin/cargos/index.blade.php` (NUEVO)
- `resources/views/admin/empleados/index.blade.php` (eliminados modales gestión)
- `resources/views/admin/layouts/sidebar.blade.php` (sub-grupo RRHH)
- `resources/views/admin/proveedores/index.blade.php` (reorganización modal)
- `resources/views/admin/cotizaciones/scripts/main.blade.php` (autocomplete unificado)
- `routes/web.php` (rutas nuevas)
