# TASK-034: Migrar consumidores de IVA al helper + doc del patrón

**Feature**: FEAT-004 — Panel de Configuración del Sistema (base)
**Spec**: `sdd/specs/panel-configuracion.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: M (2-4h)
**Depends-on**: TASK-030
**Assigned-to**: emmanuel

---

## Contexto

Implementa el **Módulo 5** del spec: conectar el sistema real al panel. Mientras
los consumidores lean `config('impuestos.iva')`, el panel no tiene efecto. Esta
task los migra al helper `parametro()` y verifica la garantía central de la
feature: **el snapshot de las compras previas no se altera**. Cierra con la
documentación del patrón para el equipo.

Paralelizable con TASK-031/032: solo depende del helper (TASK-030).

---

## Scope

- Migrar los **3 consumidores verificados** de `config('impuestos.iva', 16)` a `parametro('impuestos.iva')`:
  1. `app/Services/CompraService.php` → método `tasaIva()`
  2. `resources/views/admin/compras/index.blade.php` → `window.IVA_TASA`
  3. `resources/views/admin/compras/modals/create.blade.php` → texto informativo del IVA
- Verificar con grep que NO quedan otros lectores de `config('impuestos`.
- QA del snapshot (golden path §4 pasos 4-5 del spec): cambiar IVA por panel/tinker NO altera compras ya procesadas.
- Crear `docs/conventions/system-config.md` documentando el patrón (registry + override + helper + cómo agregar un parámetro nuevo) y añadirlo al índice `docs/conventions/README.md`.

**NO está en alcance**:
- Migrar `config/pedidos.php`, `Cotizacion::DIAS_VIGENCIA` ni textos T&C — fase 2 (no-goal del spec)
- Eliminar `config/impuestos.php` — se conserva como fuente del default
- La UI del panel (TASK-032)

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `app/Services/CompraService.php` | MODIFY | `tasaIva()` lee del helper |
| `resources/views/admin/compras/index.blade.php` | MODIFY | `window.IVA_TASA` lee del helper |
| `resources/views/admin/compras/modals/create.blade.php` | MODIFY | Texto del IVA lee del helper |
| `docs/conventions/system-config.md` | CREATE | Doc del patrón registry + override |
| `docs/conventions/README.md` | MODIFY | Entrada en el índice |

---

## Codebase Contract (Anti-Alucinación)

### Consumidores exactos a migrar (verificados 2026-06-12)
```php
// app/Services/CompraService.php:258-264 — ÚNICO punto del service; las líneas pueden correrse, ubicar por firma
/** Tasa de IVA general vigente (%). Centralizada en config/impuestos.php. */
private function tasaIva(): float
{
    return (float) config('impuestos.iva', 16);
}
// (actualizar también el PHPDoc: la fuente pasa a ser el panel/registry)

// resources/views/admin/compras/index.blade.php:199
window.IVA_TASA = @json((float) config('impuestos.iva', 16));

// resources/views/admin/compras/modals/create.blade.php:393
... El IVA ({{ (float) config('impuestos.iva', 16) }}%) aplica solo a las líneas gravables.
```

### Garantía que NO se puede romper (verificada)
```
- compra.iva_porcentaje: snapshot por compra (migración 2026_06_10_000003), se fija al PROCESAR.
- CompraService usa tasaIva() para calcular totales de compras NUEVAS; las procesadas
  leen su propio iva_porcentaje. Esta task NO toca esa lógica — solo la fuente de la tasa viva.
```

### Convenciones a respetar
- `docs/conventions/README.md` — índice; seguir el formato de los 18 docs existentes
- En docs visibles al equipo, citar `docs/conventions/<tema>.md` (regla SDD del equipo)

### NO existe — no referenciar
- ~~otros consumidores de `config('impuestos`~~ — grep 2026-06-12 encontró SOLO los 3 listados; re-verificar al ejecutar
- ~~`docs/conventions/system-config.md`~~ — se crea aquí
- ~~uso de `parametro()` en JS~~ — el helper es PHP; el JS recibe el valor inyectado por Blade (como hoy `window.IVA_TASA`)

---

## Notas de implementación

### Restricciones clave
- El comportamiento ANTES de tocar el panel debe ser idéntico (helper sin fila en BD = mismo `16` de siempre) — esta task se puede desplegar sin el panel y nada cambia.
- El doc `system-config.md` debe incluir la receta "cómo agregar un parámetro nuevo" (entrada en registry → aparece en panel → consumir con `parametro()`), el patrón snapshot y el gotcha del `autoload.files`.

---

## Criterios de aceptación

- [ ] `grep -rn "config('impuestos" app/ resources/` → cero resultados (solo `config/impuestos.php` define el default)
- [ ] Compra nueva procesada con IVA cambiado (vía tinker o panel) usa la tasa nueva y la snapshotea
- [ ] Compra procesada ANTES del cambio conserva su `iva_porcentaje` en detalle, comprobante y PDF (QA §4 paso 5 del spec)
- [ ] `window.IVA_TASA` en el wizard de compras refleja el valor del helper
- [ ] `docs/conventions/system-config.md` creado + indexado en README

---

## QA manual

1. Sin fila en BD: flujo de compras completo idéntico a hoy (IVA 16%).
2. Insertar override IVA=8 (tinker + flush, o panel si TASK-032 ya está): crear y procesar compra con línea gravable → totales al 8%, `compra.iva_porcentaje = 8.00`.
3. Volver a 16 → la compra del paso 2 sigue mostrando 8% en show/comprobante/PDF.
4. Abrir wizard de compras → resumen calcula con la tasa vigente del helper.

---

## Instrucciones para el ejecutor

1. **Lee el spec** y **verifica que TASK-030 está en `tasks/completed/`**.
2. **Re-verifica el Codebase Contract** (grep los 3 consumidores; pudieron moverse de línea).
3. **Actualiza el header**: `Status: in-progress`.
4. **Implementa**; **mueve a `tasks/completed/`** con Nota de Completitud.

---

## Nota de Completitud

**Completado por**: emmanuel (+ Claude Code)
**Fecha**: 2026-06-12
**Commits**: `00a5e6e` (rama `feat/panel-configuracion`)
**Notas**: 3 consumidores migrados (re-verificados por grep antes y después:
quedó cero `config('impuestos` fuera del propio config file). QA de la garantía
central en tinker: con override IVA=8, `CompraService::tasaIva()` (vía
reflection) devuelve 8.0 mientras los `iva_porcentaje` de las compras
existentes quedan idénticos (16.00); comprobante y controller leen del snapshot
(verificado por grep de `$compra->iva_porcentaje`). Override de prueba
eliminado al final (tabla limpia). Doc `docs/conventions/system-config.md`
creado (arquitectura, reglas de oro, receta de parámetro nuevo, gotchas) e
indexado en README. QA de flujo compra completa en navegador queda dentro del
QA general del feature (Emmanuel).

**Desviaciones del spec**: ninguna.
