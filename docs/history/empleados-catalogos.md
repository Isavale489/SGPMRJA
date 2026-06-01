---
name: Empleados — catálogos Departamento y Cargo
description: Normalización de cargo/departamento en Empleados + CRUD maestros (commit 9eae7e7, 2026-04-21)
type: project
originSessionId: 5d5b3520-8427-4183-bbc1-cc95d8e90989
---
# Catálogos Departamento y Cargo en Empleados

**Commit:** `9eae7e7` (2026-04-21) — rama `enmanuel`, pusheado a `origin/enmanuel`.

**Why:** Los campos `cargo` y `departamento` en `empleado` eran varchar libres. Generaban inconsistencias ("Produccion" vs "Producción"), impedían validar reglas de negocio (costurera en Administración), y no permitían renombrar globalmente.

**How to apply:**
- Para editar/eliminar catálogos de cargo/departamento: usar los botones "Departamentos" / "Cargos" en el header de `/empleados` (siguen patrón TipoProducto, manejados por modal + DataTable con historial/restaurar).
- Para validar cargo: siempre verificar que `cargo.departamento_id == empleado.departamento_id`.
- Para crear empleados vía API/seed: usar IDs, no nombres. El campo ya es FK.

## Modelo relacional
```
departamento (1) ──< cargo (N)  ──< empleado (N)
```
- `departamento`: id, nombre UNIQUE, activo, softDeletes
- `cargo`: id, nombre, departamento_id FK (restrictOnDelete), activo, softDeletes, UNIQUE(nombre, departamento_id)
- `empleado`: agregado `departamento_id` y `cargo_id` FKs, eliminadas columnas varchar `cargo` y `departamento`

## Endpoints (todos bajo middleware `role:Administrador`)
| Método | Ruta | Controlador |
|---|---|---|
| GET | `departamentos` | `DepartamentoController@index` (con `?historial=true`) |
| POST | `departamentos` | `DepartamentoController@store` |
| PUT | `departamentos/{id}` | `@update` |
| DELETE | `departamentos/{id}` | `@destroy` (valida cargos y empleados) |
| PATCH | `departamentos/{id}/restore` | `@restore` |
| GET | `departamentos-check-nombre` | `@checkNombre` |
| *(idem)* | `cargos/*` | `CargoController` (con `?departamento_id=` como filtro) |
| GET | `empleados-get-cargos?departamento_id=N` | `EmpleadoController@getCargos` (cascading) |

## UI — selects en cascada
- Departamento: select + botón `+` → modal rápido
- Cargo: deshabilitado hasta elegir departamento, carga por AJAX `empleados-get-cargos`
- Botón `+` de cargo solo habilitado con departamento seleccionado → vincula automáticamente
- Los botones `+` usan `departamentos.store` y `cargos.store` (no hay ya `empleados.store-departamento/cargo`)

## Reglas de integridad
- No inhabilitar departamento con cargos o empleados asociados
- No inhabilitar cargo con empleados asociados
- Nombre único por tabla (case-insensitive vía `whereRaw LOWER(nombre) = ?`)

## Migración de datos
La migración `2026_04_21_000003` es reversible (down() restaura columnas varchar desde FKs). Al ejecutar: 2 deptos + 5 cargos creados automáticamente desde datos existentes.

## Reporte de implementación
`tareas/implementacion_empleados_departamento_cargo.html` + `.pdf` (242K) — generado con Chrome headless.
