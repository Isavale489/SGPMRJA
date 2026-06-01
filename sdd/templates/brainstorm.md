---
type: feature        # feature | fix
base_branch: enmanuel
---

# Brainstorm: <Título>

**Fecha**: YYYY-MM-DD
**Autor**: <nombre>
**Status**: exploration | accepted | rejected
**Opción recomendada**: <Letra>

---

## Planteamiento del problema

<!-- ¿Qué problema estamos resolviendo? ¿A quién afecta?
     ¿Por qué ahora? Específica el dolor o la oportunidad. -->

## Restricciones y requisitos

<!-- Restricciones duras que cualquier solución debe cumplir.
     Ej: timeline de entrega al profesor, compatibilidad con módulos existentes,
     no romper el sistema modal estándar, no introducir dependencias nuevas. -->

- Restricción 1
- Restricción 2

---

## Opciones exploradas

### Opción A: <Nombre>

<!-- Describe el enfoque. Foco en QUÉ hace, no CÓMO codificarlo. -->

✅ **Pros:**
- Beneficio 1
- Beneficio 2

❌ **Contras:**
- Inconveniente 1

📊 **Esfuerzo:** Bajo | Medio | Alto

📦 **Librerías / paquetes:**
| Paquete | Propósito | Notas |
|---|---|---|
| `nombre` | qué hace | versión, madurez |

🔗 **Código existente a reusar:**
- `app/Http/Controllers/Admin/PedidoController.php` — patrón controller
- `docs/conventions/modal-system.md` — sistema modal estándar

---

### Opción B: <Nombre>

✅ **Pros:**
- Beneficio 1

❌ **Contras:**
- Inconveniente 1

📊 **Esfuerzo:** Bajo | Medio | Alto

📦 **Librerías / paquetes:**
| Paquete | Propósito | Notas |
|---|---|---|

🔗 **Código existente a reusar:**
- ...

---

### Opción C: <Nombre>

✅ **Pros:**
- Beneficio 1

❌ **Contras:**
- Inconveniente 1

📊 **Esfuerzo:** Bajo | Medio | Alto

---

## Recomendación

**Opción <X>** se recomienda porque:

<!-- Explica el razonamiento. Referencia los tradeoffs de arriba.
     Sé honesto sobre qué estás sacrificando. -->

---

## Descripción de la feature

<!-- Explicación detallada de la feature usando la opción recomendada.
     Debe ser suficiente para alimentar directamente al spec.
     Cubrir: comportamiento de usuario, comportamiento interno, edge cases, errores. -->

### Comportamiento visible al usuario
<!-- ¿Qué ve o experimenta el usuario final (admin del sistema)? -->

### Comportamiento interno
<!-- ¿Cómo funciona a alto nivel? Sin código — describir flujo y responsabilidades. -->

### Edge cases y manejo de errores
<!-- ¿Qué pasa cuando algo falla? Condiciones de frontera. -->

---

## Capacidades

### Nuevas capacidades
<!-- Capacidades que se introducen.
     Usar identificadores kebab-case (ej: control-calidad, reporte-garantias).
     Cada capacidad aceptada → un spec en sdd/specs/<name>.spec.md -->
- `<nombre>`: <descripción corta>

### Capacidades modificadas
<!-- Capacidades existentes cuyos requisitos cambian. -->
- ...

---

## Impacto e integración

<!-- ¿Qué componentes existentes se ven afectados?
     ¿Hay breaking changes? ¿Dependencias nuevas? ¿Cambios de despliegue?
     Considerar: rutas, modelos, migraciones, vistas, sidebar, memorias. -->

| Componente afectado | Tipo de impacto | Notas |
|---|---|---|
| `app/Models/Pedido` | extiende (nueva relación) | ... |
| Sidebar | añade ítem | sección Transacciones |
| BD | nueva tabla | requiere migración |

---

## Contexto de código

<!-- CRÍTICO: Esta sección preserva referencias VERIFICADAS al código
     para que sobrevivan el pipeline brainstorm → spec → task
     y prevenir alucinaciones durante la implementación. -->

### Código provisto por el usuario / profesor
<!-- Pegar fragmentos que el profesor o el equipo haya provisto. Identificar fuente. -->

```php
// Source: profesor (correo del YYYY-MM-DD)
// <pegar>
```

### Referencias verificadas en el código
<!-- Firmas, imports y atributos REALES descubiertos durante la investigación.
     Cada entrada DEBE incluir ruta y línea. -->

#### Clases y firmas
```php
// app/Models/Pedido.php:NN
class Pedido extends Model {
    use HasFactory, SoftDeletes;
    public function empleado(): BelongsTo { ... }
}
```

#### Imports verificados
```php
use App\Models\Pedido;            // app/Models/Pedido.php
use App\Models\Empleado;          // app/Models/Empleado.php
```

### Convenciones relevantes
<!-- Lista los docs de docs/conventions/ que orientan esta feature. -->
- `docs/conventions/modal-system.md`
- `docs/conventions/js-validations.md`

### NO existe — anti-alucinación
<!-- Cosas que podrían parecer existentes pero NO. -->
- ~~`App\Services\NombreInventado`~~ — no existe
- ~~tabla `nombre_imaginario`~~ — no en el esquema

---

## Evaluación de paralelismo

<!-- ¿La feature se puede dividir en tasks independientes que tres devs puedan tomar en paralelo? -->

- **Paralelismo interno**: <!-- ¿Las tasks comparten archivos? ¿Pueden ir en paralelo? -->
- **Independencia entre features**: <!-- ¿Choca con otra feature activa? ¿Archivos compartidos? -->
- **Aislamiento recomendado**: <!-- per-spec (una rama por feature) | per-task (rama por task) -->
- **Razón**: <!-- breve explicación -->

---

## Preguntas abiertas

<!-- Todo lo no resuelto. Convención (consumida por /sdd-spec):
       [ ] pregunta abierta — *Owner: nombre*
       [x] pregunta resuelta — *Owner: nombre*: <respuesta>
     Al resolver, cambia [ ] por [x] y añade la respuesta tras el `:`. -->

- [ ] Pregunta 1 — *Owner: nombre*
- [ ] Pregunta 2 — *Owner: nombre*
