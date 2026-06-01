---
name: Deuda — columna empleado.departamento legacy puede romper otros controladores
description: La auditoría DB previa eliminó empleado.departamento varchar y la reemplazó por departamento_id FK. Hay riesgo de que controladores no migrados aún usen el nombre viejo y tiren error 1054.
type: project
originSessionId: 2a4061cc-06f9-461b-bdcd-f08727ba96ce
---
## Contexto

En la auditoría DB del proyecto (commit `406a19d`, marzo 2026) se normalizó:
- `empleado.departamento` (varchar) → eliminado
- `empleado.departamento_id` (FK → `departamento` table) → nuevo
- Equivalente para `empleado.cargo` → `cargo_id`

El equipo migró la mayoría de los queries pero quedaron 2 controladores rotos detectados durante el refactor de variantes (mayo 2026):
- `OrdenProduccionController::index` — fix en commit `afdc523`
- `ProduccionDiariaController::index` — fix en commit `cbfccc1`

Ambos usaban `where('departamento', 'Produccion')` contra una columna que ya no existe → SQL error 1054 → HTTP 500.

## How to apply

**Si aparece error 1054 con columna 'departamento' o 'cargo'**: el fix es:
```php
// ANTES (roto):
->where('departamento', 'Produccion')

// DESPUÉS:
->whereHas('departamento', fn($q) => $q->whereRaw("LOWER(nombre) LIKE 'producc%'"))
```

El `LIKE 'producc%'` es defensivo: matchea tanto "Produccion" como "Producción" con tilde.

**Acceso a la relación es seguro**: `$empleado->departamento->nombre` y `$empleado->cargo->nombre` siguen funcionando (relación Eloquent), solo falla `where()` directo contra la columna eliminada.

## Búsqueda preventiva

Si aparece el bug en otro lugar:
```bash
grep -rn "where.*['\"]departamento['\"]\|where.*['\"]cargo['\"]" app/
```
Los falsos positivos a ignorar: accesos a la relación (`$emp->departamento->nombre`), referencias a `departamento_id` o `cargo_id`.

**Why:** El bug es heredado, no del refactor de variantes, pero quedó documentado porque puede aparecer en cualquier query histórica que toque empleados. La memoria preserva el patrón de fix para que no haya que re-investigar.
