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

## Endpoints compartidos con módulos Blade

Si un módulo aún en Blade (jQuery) usa el mismo endpoint, el controller responde según quién llama:

```php
return $request->header('X-Inertia')
    ? back()->with('success', $mensaje)          // página Inertia
    : response()->json(['success' => $mensaje] + $datos);  // $.ajax de un módulo Blade
```

La validación del FormRequest ya distingue sola los dos casos: redirect con errores para Inertia y 422 JSON para jQuery. Un test fija el contrato JSON del cliente viejo (ver `ProveedorFlujoTest::test_el_alta_rapida_desde_compras_recibe_json_con_el_proveedor`).

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

**Resultado medido:** el módulo pasó de 1.956 líneas (Blade + jQuery + controller) a 1.195 (−39 %). Se agregaron 374 líneas de piezas reutilizables y 430 de tests; antes el módulo no tenía ninguno. El piloto encontró y corrigió 2 bugs: el RIF duplicado daba 500, y editar un proveedor natural duplicaba el apellido.
