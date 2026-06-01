# Búsqueda Unificada de Personas

> Patrón reusable para seleccionar a una persona (cliente, empleado o proveedor) desde cualquier formulario sin duplicar registros en la tabla `persona`.

## Por qué existe

La tabla `persona` unifica la identidad de Cliente, Empleado y Proveedor (todos tienen `persona_id`). Antes, cada autocomplete buscaba solo en su tabla específica → si alguien ya estaba registrado como empleado y quería comprar como cliente, se le pedía registrarlo otra vez → datos duplicados.

Este patrón resuelve eso: una sola búsqueda contra `persona`, devuelve roles, y si falta el rol que se necesita, lo crea de forma idempotente.

## Endpoints

### `GET /personas-search?q=<documento>`

Controller: `PersonaController@search`. Busca por `documento_identidad` LIKE en la tabla `persona`. Mínimo 6 caracteres. Limit 10.

Solo retorna personas con **al menos un rol activo**:
- Cliente con `estatus = 1` y no soft-deleted
- Empleado no soft-deleted
- Proveedor con `estado = 1` y no soft-deleted

**Respuesta:**
```json
[
  {
    "persona_id": 12,
    "cliente_id": 8,                  // null si no es cliente
    "tipo_cliente": "natural",        // null si no es cliente
    "documento": "V-18728555",
    "tipo_documento": "V-",
    "documento_num": "18728555",
    "nombre": "Mark",
    "apellido": "Zuckerberg",
    "razon_social": null,             // solo proveedor jurídico
    "email": "...",
    "telefono": "0412-...",
    "direccion": "...",
    "estado": "Portuguesa",
    "ciudad": "Acarigua",
    "roles": ["cliente", "empleado"]  // o ["proveedor_natural"], ["proveedor_juridico"], etc.
  }
]
```

### `POST /clientes/from-persona/{persona}`

Controller: `ClienteController@createFromPersona`. **Idempotente**:
- Si ya existe cliente activo vinculado → lo devuelve (`reused: true`).
- Si existe cliente inactivo (`estatus=0` o trashed) → lo reactiva (`reused: true`).
- Si no existe → crea uno nuevo (`reused: false`).

**Tipo cliente detectado por prefijo del documento:**

| Prefijo | tipo_cliente |
|---|---|
| `V-`, `E-` | `natural` |
| `J-` | `juridico` |
| `G-` | `gubernamental` |

**Respuesta:**
```json
{ "success": true, "message": "...", "cliente_id": 40, "reused": false }
```

## Implementación frontend (referencia)

Existe en `resources/views/admin/cotizaciones/scripts/main.blade.php` (líneas ~2595-2780). Patrón clave:

```js
const ROLE_BADGES = {
    cliente:            { label: 'Cliente',   cls: 'bg-success-subtle text-success' },
    empleado:           { label: 'Empleado',  cls: 'bg-info-subtle text-info' },
    proveedor_natural:  { label: 'Proveedor', cls: 'bg-warning-subtle text-warning' },
    proveedor_juridico: { label: 'Proveedor', cls: 'bg-warning-subtle text-warning' },
};
```

**Flujo al seleccionar:**
1. Si `persona.cliente_id` existe → aplicar al form directamente.
2. Si NO → SweetAlert "¿Crear cliente con los datos existentes?" → confirma → `POST /clientes/from-persona/{id}` → aplicar al form.

**Helper reutilizable** en cotizaciones: `aplicarPersonaACotizacion(persona, clienteId)` — escribe documento, layout dinámico (juridico vs natural), email, teléfono. Si se reusa en otro módulo (Pedidos), copiar el helper y adaptar los IDs de campos.

## Modelo `Persona` — relaciones requeridas

```php
public function cliente()   { return $this->hasOne(Cliente::class); }
public function empleado()  { return $this->hasOne(Empleado::class); }
public function proveedor() { return $this->hasOne(Proveedor::class); }
public function user()      { return $this->hasOne(User::class); }
```

`User` no tiene documento en este sistema, por eso queda fuera de la búsqueda.

## Cómo aplicar este patrón en otros módulos

Ejemplo para Pedidos:

1. **No duplicar el endpoint** — usar el mismo `/personas-search` y `/clientes/from-persona/{id}`.
2. Adaptar el JS del autocomplete del módulo:
   - Cambiar el endpoint AJAX a `/personas-search`.
   - Renderizar resultados con `ROLE_BADGES`.
   - En el handler de selección, distinguir si `persona.cliente_id` existe vs `null`.
3. Crear un helper `aplicarPersona*()` específico del formulario (los IDs cambian, la lógica es la misma).

## Limitaciones conocidas

- El endpoint NO filtra por rol esperado. El componente que consume decide qué hacer con `cliente_id`.
- Si una persona tiene `cliente_id` pero el cliente fue inhabilitado, el endpoint **NO** la incluirá (filtra `estatus=1`). Esto es correcto para autocomplete de venta.
- No hay paginación — limit 10 es suficiente para búsqueda por documento (que es muy específica).
