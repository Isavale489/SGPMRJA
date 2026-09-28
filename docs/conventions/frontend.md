# Frontend — Plataforma Inertia + React + TypeScript

> Cómo se construye y se migra una página a la plataforma nueva. Aplica a toda página Inertia.
> Contexto y decisión: [`docs/auditoria_migracion_frontend.html`](../auditoria_migracion_frontend.html) (paso 2).
> Referencia viva: **`/plataforma/componentes`** (solo Administrador).

## Convivencia con Blade (mientras dure la migración)

| | Páginas Blade (legado) | Páginas Inertia (nuevas) |
|---|---|---|
| Plantilla raíz | `admin/layouts/app.blade.php` | `resources/views/inertia.blade.php` |
| CSS | Bootstrap/Velzon + `public/assets/css/custom.css` | `resources/css/plataforma.css` (Tailwind v4) |
| JS | jQuery + IIFEs en `@push('scripts')` | React 19 + TypeScript en `resources/js/` |
| Menú | `admin/layouts/sidebar.blade.php` | `config/navegacion.php` (filtrado por permisos en el servidor) |

**Reglas de convivencia**
- Una página Inertia **nunca** carga Bootstrap, jQuery ni `custom.css`, y una Blade nunca carga `plataforma.css`. El reset de Tailwind (preflight) y Bootstrap se pisan. Hay un test que lo verifica (`PlataformaInertiaTest`).
- Un módulo nuevo o migrado se agrega en **los dos** menús (`sidebar.blade.php` y `config/navegacion.php`). En `navegacion.php`, `'inertia' => true` **solo** cuando su página ya es Inertia. Un `<Link>` hacia una página Blade haría que Inertia mostrara HTML como error.
- El tema claro/oscuro se comparte: la clave `localStorage['sgpmrja-theme']` es la misma en ambos layouts.

## Estructura

```
resources/js/
  inertia.tsx              entrada (createInertiaApp)
  pages/<Modulo>/<Vista>.tsx   una página por componente Inertia (Inertia::render('Modulo/Vista'))
  layouts/app-layout.tsx   sidebar + barra superior + título + avisos
  components/ui/           primitivas de shadcn/ui (código nuestro; se puede editar)
  components/app/          componentes de dominio (Monto, EstadoBadge, Campo, TasaBcv, ConfirmarPeligro…)
  hooks/                   use-permisos, use-tema
  lib/                     utils (cn), formato (Bs, $, fechas)
  types/                   DatosCompartidos (props de HandleInertiaRequests)
```

## Reglas

1. **La lógica de negocio no se mueve.** Validación en FormRequests, transacciones y reglas en `app/Services`. React solo dibuja y envía.
2. **Permisos: el servidor decide.** `usePermisos().puede('compras.anular')` solo oculta botones. La autorización real la hace `CheckPermiso`. El menú llega ya filtrado.
3. **Formularios con `useForm` de Inertia.** Los errores vienen de Laravel (`form.errors.campo`) y se muestran con `<Campo error={…}>`. **No** se duplica la validación en el cliente.
   - Si el control es **compuesto** (prefijo + número) o un **`<Select>`**, `Campo` recibe una función: `{(control) => <SelectTrigger {...control}>…}`. Así la etiqueta nombra el control real; de lo contrario, un lector de pantalla no sabe qué es.
   - Un formulario en diálogo se **monta de nuevo en cada apertura** (`key`), porque `useForm` toma sus valores al montarse. `setDefaults()` + `reset()` en el mismo ciclo no sirve.
   - Cambios sin guardar: `useGuardCambios(form.isDirty)` y `confirmarDescarte(form.isDirty)` en el `onOpenChange` del diálogo.
4. **Montos:** siempre `<Monto usd={…} />`, que muestra el equivalente en Bs y la tasa con su fecha. Si el documento congeló una tasa (p. ej. la cotización), se pasa `tasa={…}`.
5. **Fechas:** `formatoFecha('2026-09-27')` y `hoyLocalIso()` de `lib/formato`. **Nunca** `new Date('AAAA-MM-DD')` ni `toISOString()`, que son UTC y en Venezuela (UTC−4) corren la fecha un día.
6. **Estados:** `<EstadoBadge estado="Aprobada" />`. Un estado nuevo se agrega en `estado-badge.tsx`, no con clases sueltas.
7. **Colores y movimiento solo con tokens** (`bg-primary`, `text-muted-foreground`, `duration-rapido`, `ease-salida`…) definidos en `plataforma.css`. Nada de hex sueltos ni duraciones de más de 320 ms.
8. **Capas flotantes (diálogos, menús, selects, tooltips) con los componentes de `components/ui`.** Se renderizan en un portal, así que ningún `overflow` las recorta. No reimplementar parches como `modal-hidden-temp` o Popper con `strategy: 'fixed'`.
9. **Íconos:** `lucide-react`. Los del menú se registran en `components/app/icono.tsx`; un test verifica que cada ícono de `navegacion.php` exista.

## Comandos

```bash
npm run dev          # Vite con recarga en caliente
npm run typecheck    # TypeScript estricto (la CI lo exige)
npm run build        # genera public/build — SE COMMITEA (el despliegue no compila)
npm run ui:add -- popover   # agregar componentes shadcn (usar este script, no el CLI directo)
npm run test:e2e     # smoke Playwright (BD propia sistema_atlantico_e2e)
```

- **`public/build` se versiona.** Después de tocar `resources/js` o `resources/css`, hay que correr `npm run build` y commitear el resultado. La CI falla si el build del repo no coincide con el código.
- **`npm run ui:add`** existe porque el CLI de shadcn, en este repo, importa `cn` desde el paquete npm `"cn"` (ajeno) y agrega `next-themes`. El script corrige ambos. Revisa el diff igual.

## Listados: tabla con datos del servidor

- `useFiltrosUrl(url, filtros, ['listado', 'filtros'])`: los filtros viven en la URL (se puede recargar, compartir o volver atrás) y cada cambio recarga **solo** esas props, con 300 ms de espera para el texto.
- `<TablaServidor pagina={…} columnas={…} />` dibuja el `paginate()` de Laravel con su `<Paginacion>`. El filtrado, el orden y la paginación se resuelven en el servidor.
- El controller arma cada fila con **todo** lo que usan "Ver" y "Editar", así el diálogo no necesita otra petición.
- **Contrato de las filas:** la interfaz TS de la fila (p. ej. `ProveedorFila` en `pages/Proveedores/tipos.ts`) se compara en un test con las claves que manda el servidor (`hasAll` sin `etc()`). Si difieren, la CI falla. Ver `ProveedorPaginaTest`.

## Catálogos (patrón estándar)

Un catálogo con listado, historial y formulario pequeño **no se escribe desde cero**:

- `<PaginaCatalogo>` (`components/app/pagina-catalogo.tsx`) da el título, historial, búsqueda en la URL, tabla paginada y acciones por fila (Editar, Inhabilitar con confirmación, Restaurar). El módulo aporta `columnas`, `filtrosExtra` opcionales y `formulario`.
- `<DialogoFormulario>` es la estructura del formulario, con Cancelar/Guardar y el aviso de cambios sin guardar. Se monta con `key={p.apertura}`.
- Referencia: `pages/Departamentos` (1 campo), `pages/Cargos` (select + filtro extra), `pages/Colores` (selector de color + sugerencias).
- **Confirmaciones que salen de un menú ⋮:** el ítem del menú solo guarda qué registro se confirma. `<ConfirmarPeligro abierto onCerrar>` va **fuera** del menú. Si vive dentro, el menú queda abierto al confirmar y deja la página inaccesible.

## Patrones de la fase 3 (Atributos, Clientes, Insumos, Empleados, Usuarios)

- **Maestro-detalle** (`pages/Atributos`): el registro elegido va en la URL (`?atributo=ID`) y elegir otro hace `router.get(url, {atributo}, { only: ['seleccionado', 'valores'], preserveState: true })`. En el servidor, las props del detalle son closures (`'valores' => fn () => …`) para que la recarga parcial solo calcule lo pedido. En móvil se muestra un panel a la vez.
- **Recarga parcial + aviso flash:** si una mutación responde con `back()->with('success')` y la visita usa `only`, el flash **no** se envía y queda pendiente en la sesión: aparece en la *siguiente* página. O se incluye `'flash'` en `only` (alta rápida de Empleados) o el endpoint no pone flash para Inertia (reordenar valores de Atributos).
- **Alta rápida de un catálogo desde otro formulario** (`AltaRapida` en `pages/Empleados/formulario-empleado.tsx`): `router.post(urlDelCatalogo, datos, { only: [catalogo, 'flash'], preserveState: true })` y en `onSuccess` se elige el nuevo por su nombre en `page.props`. El diálogo no se remonta, así que lo escrito se conserva.
- **Persona compartida con otro rol** (Clientes, Empleados): al salir del documento, el `check-documento` del módulo devuelve `other_role` y los datos; se ofrece «Usar sus datos», se bloquean nombre y correo (el service no los cambia al reutilizar la persona) y no se deja guardar hasta decidir. En edición, la fila trae `otros_roles` para avisar que el cambio también se ve allá.
- **Archivos con PUT** (foto de Usuarios): PHP no lee multipart en PUT. Se envía `form.post(url, { forceFormData: true })` con `_method: 'put'` (vía `form.transform`).
- **Piezas nuevas:** `ExportarPdf` (lista de filtros por reporte, cada módulo con sus parámetros), `Dato` (ficha de Ver), `ui/switch` (booleanos como `is_inventoriable`).
- **Gotcha de rutas:** una ruta `x/{id}` (show) declarada antes que `x/check-nombre` la captura y responde 404. Al migrar, `show` suele sobrar (la fila trae todo); si queda, declara las rutas fijas primero.

## Endpoints compartidos con módulos Blade

Si un módulo aún en Blade (jQuery) usa el mismo endpoint, el controller usa el trait `App\Http\Controllers\Concerns\RespondeSegunCliente`:

```php
return $this->responder($request, 'Cargo creado.', ['success' => true, 'message' => 'Cargo creado.', 'cargo' => $cargo]);
// Inertia → back()->with('success', …) · jQuery → ese JSON tal cual
return $this->rechazar($request, 'No se puede inhabilitar: tiene empleados.');
// Inertia → back()->with('error', …) · jQuery → 422 {success:false, message}
```

- **`$request->ajax()` NO distingue:** Inertia también manda `X-Requested-With`. Si un `index()` devuelve JSON para un cliente jQuery, la condición es `$request->ajax() && ! $this->esInertia($request)`. Si no se hace así, las recargas parciales de Inertia reciben JSON y la página se rompe. Ver `DepartamentoController::index` y su test.
- La validación del FormRequest ya distingue sola los dos casos: redirect con errores para Inertia y 422 JSON (con `message` = primer error) para jQuery.
- Un test de caracterización fija el contrato JSON del cliente viejo antes de migrar (ver `tests/Feature/Flujos/CatalogosSimplesFlujoTest.php`).
- En tests que simulan una petición Inertia, manda también `X-Inertia-Version` (la de `HandleInertiaRequests::version()`). Si falta, Inertia responde 409 y pide recargar, que es lo correcto.

## Migrar un módulo (checklist)

1. Controller: `return Inertia::render('Modulo/Index', [...props])` en vez de `view()`. Las mutaciones responden `redirect()->back()->with('success', …)`, no JSON. El JSON queda para búsquedas y autocompletado.
2. Validación en un FormRequest (si era inline, se mueve).
3. Página en `resources/js/pages/Modulo/` usando `AppLayout`.
4. `config/navegacion.php`: `'inertia' => true` en su enlace.
5. Tests: los de `tests/Feature/Flujos` deben seguir en verde sin cambios, porque verifican la BD y no el JSON. Agrega un `assertInertia` para las props.
6. `npm run typecheck && npm run build`, E2E en verde, commit incluyendo `public/build`.

## Decisiones del piloto (Proveedores, sep-2026)

Herramientas evaluadas y **no** adoptadas. Se revisan si cambian las condiciones:

| Herramienta | Por qué no (todavía) |
|---|---|
| TanStack Table | La v9 (estable desde 2026) rehízo la API. Como el filtrado, el orden y la paginación son del servidor, no hay lógica de cliente que la justifique. Revisar si hace falta selección de filas o columnas configurables. |
| spatie/laravel-data + typescript-transformer | Para Laravel 13 no hay combinación estable: laravel-data soporta el transformer v2, que choca con sus propias dependencias, y todavía no soporta la v3. Lo reemplaza el test de contrato de las filas. |
| Laravel Wayfinder (rutas tipadas) | Sigue en v0.x (beta). Mientras tanto, cada página recibe sus URLs como prop (`urls`). |

**Resultado medido (Proveedores):** el módulo pasó de 1.956 líneas (Blade + jQuery + controller) a 1.195 (−39 %). Se agregaron 374 líneas de piezas reutilizables y 430 de tests; antes el módulo no tenía ninguno. El piloto encontró y corrigió 2 bugs: el RIF duplicado daba 500, y editar un proveedor natural duplicaba el apellido.

**Catálogos simples (Departamentos, Cargos, Colores):** 1.824 → 666 líneas (−63 %) con `PaginaCatalogo` + `DialogoFormulario`. El costo por módulo baja a medida que se acumulan las piezas.

**Fase 3 (sep-2026):** Atributos 965 → 752 (−22 %), Clientes 1.840 → 987 (−46 %), Insumos 1.799 → 953 (−47 %), Empleados 1.938 → 1.119 (−42 %), Usuarios 1.454 → 763 (−48 %). Los tests de caracterización encontraron 10 bugs de producción (cliente gubernamental imposible, direcciones duplicadas al reutilizar personas, apellido duplicado al editar empleados, insumos de Movimientos creados como no inventariables, `check-nombre` de insumos siempre 404, el último administrador podía perder su rol, entre otros); cada PR los detalla.
