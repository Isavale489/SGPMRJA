# TASK-029: Crear tela inline desde el selector de variante (cotización)

**Feature**: FEAT-003 — variantes-dinamicas
**Spec**: `sdd/specs/variantes-dinamicas.spec.md`
**Status**: done
**Priority**: high
**Esfuerzo estimado**: M
**Depends-on**: TASK-022, TASK-024
**Assigned-to**: emmanuel

---

## Contexto
Requerimiento adicional (profesora): desde el selector de variante de la cotización, poder
**crear una tela nueva sin salir** — o sea, dar de alta un Insumo `tipo='Tela'` inline y que quede
disponible para el tipo en ese momento. Mismo patrón que "crear color" / "crear proveedor" inline.

## Scope
- Endpoint `POST /tipo-productos/{tipoProducto}/telas` (`TipoProductoController@storeTela`): crea el
  Insumo (tipo='Tela', réplica de las reglas del alta de insumo) y lo asigna al tipo
  (`tipo_producto_tela`). Devuelve la tela.
- Mini-modal `#crearTelaRapidaModal` (réplica del form de Insumo del maestro, tipo fijo "Tela"):
  nombre, código, unidad, is_inventoriable + stock mín/actual/máx (toggle), costo, estado.
- Botón "Nueva tela" en `#vs-tela-section` (gated Admin/Supervisor); al guardar, la tela se agrega a
  `vsState.tipo.telas`, se re-renderiza y queda **auto-seleccionada**.

## Codebase Contract
- `InsumoController@store` — reglas replicadas (nombre/codigo/unidad/costo/stock/is_inventoriable/estado).
- `TipoProducto::telas()` (TASK-019) — `syncWithoutDetaching`.
- `vsState`, `vsRenderTelas()`, `vsResolverVariante()` en `cotizaciones/scripts/main.blade.php`.
- Insumo es tela vía `tipo='Tela'` (NO tabla `tela`).

## Nota de Completitud
**Completado por**: emmanuel · **Fecha**: 2026-06-03
**Notas**: Endpoint + mini-modal + botón implementados. Gated a Administrador/Supervisor (igual que
crear color). QA backend (rollback): crea Insumo tipo Tela (LINP, costo 42.5, stock), lo asigna a
Franela (telas: Jersey, Microfibra, Lino Premium) y devuelve la tela.
**⚠️ REQUIERE QA en navegador**: abrir selector → "Nueva tela" → completar → guardar → la tela
aparece seleccionada y se puede configurar/cotizar.
**Permiso**: gateado a Admin/Supervisor. Si la profesora quiere que *cualquier* vendedor cree telas,
quitar el `@if(Auth::user()->hasRole(...))` del botón/modal y ajustar el grupo de la ruta.
