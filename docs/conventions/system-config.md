# Configuración del Sistema (registry + overrides)

> Cómo funcionan los parámetros configurables del panel `/configuracion` (FEAT-004)
> y cómo agregar uno nuevo. Patrón: **definición en código, valor en BD**.

## Arquitectura

| Pieza | Dónde | Qué guarda |
|---|---|---|
| Registry | `config/parametros.php` | DEFINICIÓN de cada parámetro: módulo, nombre, descripción, tipo, reglas de validación, default |
| Overrides | tabla `configuracion` (`clave` UNIQUE, `valor`, `updated_by_id`) | SOLO los valores que el admin cambió desde el panel. Sin fila = default |
| Helper | `parametro('clave')` en `app/Support/helpers.php` | Valor efectivo: override en BD → `default` del registry → `config(config_key)` legacy/.env |
| Panel | `/configuracion` (solo Administrador) | UI dirigida por el registry: un nav-pill por módulo, campos según `tipo` |

```
parametro('impuestos.iva')
   └─ Cache 'parametros' (mapa completo clave→valor, rememberForever)
        └─ tabla configuracion ──si no hay fila──► default del registry ──► config legacy
```

## Reglas de oro

1. **Leer SIEMPRE con `parametro('clave')`** — nunca del modelo `Configuracion` ni
   de `config('parametros...')` directo. El helper resuelve fallback, caché y casteo.
2. **Una sola key de caché** (`parametros`). Toda escritura (update/reset del
   controller) hace `Cache::forget('parametros')`. Si escribes overrides por otra
   vía (seeder, tinker), flushea tú mismo.
3. **Los valores históricos van en snapshot, no en el parámetro.** Cambiar un
   parámetro afecta solo operaciones NUEVAS. Si una transacción depende del valor
   vigente al momento de crearse, persiste su propio snapshot (precedente:
   `compra.iva_porcentaje`). NUNCA recalcular registros viejos con el valor actual.
4. **Los config files legacy no se eliminan** (`config/impuestos.php`,
   `config/pedidos.php`): son la fuente del default vía `config_key` y mantienen
   el puente con el `.env`.
5. **Clave inexistente = excepción.** `parametro('typo.clave')` lanza
   `InvalidArgumentException` a propósito: un typo debe tronar en desarrollo, no
   devolver `null` mudo.

## Cómo agregar un parámetro nuevo

1. Añade la entrada en `config/parametros.php`:

```php
'pedidos.abono_minimo' => [
    'modulo'      => 'Pedidos',                       // agrupa en el panel (pill nuevo si no existe)
    'nombre'      => 'Abono mínimo (%)',
    'descripcion' => 'Porcentaje mínimo abonado para formalizar un pedido.',
    'tipo'        => 'decimal',                       // decimal | entero | booleano | texto
    'reglas'      => 'required|numeric|min:0|max:100',// validación server (min/max también alimentan el input)
    'default'     => null,                            // null ⇒ tomar de config_key
    'config_key'  => 'pedidos.abono_minimo_porcentaje',

    // Opcionales (UI):
    // 'confirmar_guardado'   => true,
    // 'mensaje_confirmacion' => '...texto del SweetAlert previo al guardado...',
],
```

2. **Listo.** El panel lo renderiza solo (campo según `tipo`, badge "Por defecto",
   botón Restablecer, validación) — no se toca ni vista ni controller ni JS.
3. Migra los consumidores: `config('x.y')` → `parametro('clave.nueva')`.
4. Si el valor afecta documentos/transacciones históricas, evalúa snapshot (regla 3).

## Gotchas

- **`autoload.files`**: el helper vive en `app/Support/helpers.php`, registrado en
  el bloque `"files"` de `composer.json` (creado en FEAT-004). Si el helper "no
  existe", corre `composer dump-autoload`.
- **Claves con punto y el validador**: en `ConfiguracionController@update` las
  reglas se registran con la clave ESCAPADA (`impuestos\.iva`) pero las etiquetas
  (customAttributes) con la clave sin escapar. No "simplificar" eso.
- **Payload del panel**: `PUT /configuracion/{modulo}` espera
  `{"valores": {"clave.con.punto": "valor"}}`; los errores 422 vienen keyed por
  clave sin escapar.
- **El helper nunca tira 500 por sí solo**: si la tabla no existe o la BD está
  caída, resuelve con defaults (guard interno). No le agregues lecturas directas
  a BD por fuera de ese guard.
- Los **booleanos** se guardan como `'1'`/`'0'` (la columna `valor` es varchar);
  el casteo a `bool` lo hace el helper según `tipo`.

## Qué NO es esto

- **No** es para datos operativos que cambian solos (ej. tasa BCV → tabla
  `tasa_cambio`).
- **No** es para preferencias por usuario (ej. tema dark/claro) — eso será otro
  mecanismo cuando toque.
- **No** reemplaza permisos/roles (eso es FEAT-005).
