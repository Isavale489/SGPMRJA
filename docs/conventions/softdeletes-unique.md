# SoftDeletes + UNIQUE Constraint

> Cuando un modelo usa `SoftDeletes` Y tiene una columna con `UNIQUE` constraint que se genera secuencialmente, los generadores deben usar `withTrashed()` o colisionarán con registros borrados.

## La regla

Cualquier `Model::max('campo_unique')` en un servicio que use SoftDeletes → cambiar a `Model::withTrashed()->max('campo_unique')`.

## Por qué

Eloquent's default scope excluye registros con `deleted_at != NULL` de queries normales (incluyendo `max()`). Pero las constraints `UNIQUE` de la base de datos **NO** los excluyen — siguen reservando el valor.

**Resultado**: el generador calcula un número "siguiente" basándose solo en registros visibles, pero al insertar choca con un soft-deleted y la migración falla con error 1062 (Duplicate entry).

## Patrón correcto

```php
// ❌ ANTES — colisiona con soft-deleted
$ultimoCodigo = Empleado::max('codigo_empleado');
$numero = $ultimoCodigo ? ((int) substr($ultimoCodigo, 4) + 1) : 1;
$codigoEmpleado = 'EMP-' . str_pad($numero, 3, '0', STR_PAD_LEFT);

// ✅ DESPUÉS — incluye trashed + loop defensivo
$ultimoCodigo = Empleado::withTrashed()->max('codigo_empleado');
$numero = $ultimoCodigo ? ((int) substr($ultimoCodigo, 4) + 1) : 1;
do {
    $codigoEmpleado = 'EMP-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
    $existe = Empleado::withTrashed()->where('codigo_empleado', $codigoEmpleado)->exists();
    $numero++;
} while ($existe);
```

El **loop defensivo** protege contra race conditions y restauraciones manuales.

## Aplica a

- `codigo_empleado` (Empleado)
- `codigo` de productos (`Producto`, vía `ProductoService::generarCodigo()`)
- Cualquier código secuencial futuro

## Audit recomendado antes de cada sprint

```bash
grep -rn "::max('" app/Services app/Http/Controllers
```

Por cada hit, verificar:
1. ¿La columna tiene `UNIQUE` en alguna migración?
2. ¿El modelo usa el trait `SoftDeletes`?

Si ambas respuestas son sí → la query necesita `withTrashed()` + loop defensivo.

## Caso real documentado

`EmpleadoService::crear()` generaba duplicados de `EMP-005` cuando había empleados soft-deleted con ese código. Fix aplicado siguiendo el patrón de arriba.
