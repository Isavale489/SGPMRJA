# /sdd-spec — Crear una Especificación de Feature

Crea una nueva especificación SDD para SGPMRJA usando la metodología documentada en `sdd/WORKFLOW.md`.

## Uso
```
/sdd-spec <feature-slug> [-- notas libres sobre motivación / scope]
```

Ejemplos:
- `/sdd-spec control-calidad`
- `/sdd-spec garantias -- el profesor pidió poder registrar reclamos post-venta`

## Reglas
- **Siempre** usar el template `sdd/templates/spec.md`.
- **NO escribir código** de implementación en el spec — es un documento de diseño.
- **Feature IDs únicos** — revisar specs existentes antes de asignar.
- Si existe un `<slug>.brainstorm.md` o `<slug>.md` en `sdd/proposals/`, usarlo como input.
- **Commitear el spec** a la rama actual antes de crear tasks.
- Idioma: **español** en el contenido (motivación, descripciones); identificadores en inglés.

## Pasos

### 1. Parsear input
- `<feature-slug>`: kebab-case. Si falta, preguntar.
- Notas libres tras `--`: usar como semilla del "Planteamiento del problema".

### 2. Buscar exploración previa
Revisar `sdd/proposals/`:
- `<slug>.brainstorm.md` → análisis de opciones; usar la "Opción recomendada".
- `<slug>.proposal.md` → discusión; usar Motivación + Scope.

Si existe, **prellenar el spec con esa información** y minimizar preguntas al usuario.

### 3. Investigar el código y construir Codebase Contract

**Antes de escribir el spec:**
- Leer specs existentes en `sdd/specs/` para reusar patrones.
- Identificar componentes relacionados:
  - Controllers en `app/Http/Controllers/Admin/`
  - Modelos en `app/Models/`
  - Vistas en `resources/views/admin/`
  - Rutas en `routes/web.php`
- Notar qué se reusa vs qué se crea.

**CRÍTICO — Construcción del Codebase Contract** (sección 6 del template):

Esta sección **previene alucinaciones** durante la implementación. Hacer:

1. **Si hay brainstorm previo**: copiar su sección "Contexto de código" al spec. Re-verificar referencias (el código pudo haber cambiado).
2. **Para cada clase/módulo referenciado**: `read` el archivo real y registrar firmas exactas, métodos, atributos, con ruta y línea.
3. **Verificar imports**: confirmar que `use App\X\Y;` resuelve realmente. No asumir.
4. **Registrar lo que NO existe**: si buscaste algo plausible y no está, anotarlo en "NO existe". Esta es la medida anti-alucinación más efectiva.
5. **Enlazar convenciones relevantes**: en vez de duplicar contenido, apuntar a archivos en `docs/conventions/`:
   - `docs/conventions/modal-system.md`
   - `docs/conventions/js-validations.md`
   - `docs/conventions/nested-modals.md`
   - `docs/conventions/db-architecture.md`
   - `docs/conventions/README.md` para el índice completo
6. **Incluir código provisto por el usuario**: si el profesor o equipo dio fragmentos, preservarlos como referencias verificadas.

### 4. Crear el spec
1. Leer el template `sdd/templates/spec.md`.
2. Calcular siguiente Feature ID:
   - Listar specs existentes: `ls sdd/specs/*.spec.md`
   - Buscar el `FEAT-NNN` máximo dentro de los archivos
   - Incrementar; si no hay ninguno, empezar en `FEAT-001`
3. Crear `sdd/specs/<feature-slug>.spec.md` rellenando:
   - Feature ID, fecha (hoy), autor (usuario actual)
   - Respuestas del usuario o de la exploración previa
   - Patrones arquitectónicos descubiertos
4. Status inicial: `draft`.

### 5. Confirmar con el usuario
Antes de commitear, mostrar al usuario:
- Resumen del spec generado
- Secciones que necesitan input adicional (marcadas como `<TBD>` o con preguntas)
- Lista de memorias enlazadas en Codebase Contract

Esperar confirmación o ajustes.

### 6. Commitear el spec

```bash
git add sdd/specs/<feature-slug>.spec.md
git commit -m "sdd: añadir spec FEAT-<ID> — <feature-slug>"
```

### 7. Output

```
✅ Spec creado: sdd/specs/<feature-slug>.spec.md

   Feature ID: FEAT-<ID>
   Status: draft
   Convenciones enlazadas: <lista>
   Preguntas abiertas: <count>

Siguiente paso:
  1. Revisar el spec — verificar Criterios de Aceptación y Diseño Arquitectónico.
  2. Resolver preguntas abiertas con el equipo / profesor.
  3. Cambiar status a `approved` cuando esté listo.
  4. Ejecutar: /sdd-task sdd/specs/<feature-slug>.spec.md
```

## Referencia
- Template: `sdd/templates/spec.md`
- Specs existentes: `sdd/specs/`
- Metodología: `sdd/WORKFLOW.md`
