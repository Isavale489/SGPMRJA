---
name: Cotizaciones wizard — progreso y plan de continuidad
description: Estado completo del refactor del módulo Cotizaciones a wizard 3 pasos. Lista de commits, decisiones tomadas, deuda técnica y próximos pasos para retomar mañana.
type: project
originSessionId: cdb81f0b-d893-4752-bd60-b9f59c507686
---
## Rama y estado

- **Rama de trabajo:** `feat/cotizaciones-wizard` (pusheada al remoto)
- **Base:** `main`
- **Commits acumulados:** 17 (todos pusheados, working tree limpio al cierre)
- **Último commit:** `f572a66` — modal Agregar Cliente réplica del estándar /clientes
- **No mergeada en `enmanuel`** todavía — esperar validación del flujo completo en navegador.

## Lo que está terminado

### Wizard 3 pasos (Cliente · Productos · Resumen)
- Stepper visual con barra de progreso, validación entre pasos, footer con
  Anterior/Cerrar/Continuar/Submit que cambia según paso.
- Submit final dispara las rutas existentes (POST /cotizaciones, PUT /cotizaciones/{id}).
- Modo crear y modo editar funcionando.

### Step 1 — Cliente
- Buscador de documento con icono de lupa.
- Empty state con CTA "Crear cliente nuevo" prominente.
- Skeleton loading durante AJAX.
- **Tarjeta visual del cliente seleccionado:** avatar con iniciales coloreadas por hash,
  nombre prominente, roles como pills (Cliente / Empleado / Proveedor), documento,
  teléfono, email, mini-stats (cotizaciones previas + última fecha), botón "Cambiar".
- **Selector de prioridad como 3 chips** (Normal/Alta/Urgente con dot de color).
- **Selector de estado como 3 chips** (Pendiente/Aprobada/Cancelada) — solo en edición.
- **Atajos de validez:** chips Hoy/+15/+30/+60 días.
- Backend: `PersonaController::search` retorna `cotizaciones_count` + `cotizaciones_last_date`.
- Modal "Agregar Cliente" anidado: réplica visual exacta del modal de `/clientes`
  (5 secciones temáticas, opción Gubernamental, conmutación natural ↔ jurídico).

### Step 2 — Productos
- Modal Catálogo (anidado): sidebar de filtros (búsqueda, tipos, precio, sort) +
  grilla de cards de producto + carrito lateral.
- Modal Configurador (anidado sobre catálogo): ficha del producto + selector de
  color (chips por categoría) + matrix de tallas + **sección 3 Precio editable** con
  reset al precio base.
- Carrito → líneas: `cotCartConfirmar` expande cada item del carrito en N llamadas
  a `addProductItem` (una por talla con qty>0).
- **Tabla profesional ERP** (estilo SAP/Odoo/NetSuite) reemplazó las cards verticales
  legacy. Columnas: # · Producto · Color · Tallas · Unid · Precio U · Subtotal · Acc.
- Tabla con `max-height: min(380px, 45vh)`, header sticky, scrollbar estilizado.
- Acciones por fila: ✏ Editar (reabre configurador con datos), ✂ Bordado (modal legacy),
  🗑 Eliminar bloque.
- Toggle "Vista detallada por línea" eliminado — el flujo es único: catálogo + configurador.

### Step 3 — Resumen
- Resumen visual con desglose Subtotal / IVA 16% / Total.
- Tabla agrupada de líneas (1 fila por bloque, no por talla) con bordado pill cuando aplica.
- Textarea de notas/condiciones.

### Otros
- IDs legacy preservados como hidden inputs para que el JS existente no se rompa.
- `productos-container` queda permanentemente oculto (`display:none !important`) como
  fuente de verdad para FormData del submit.

## Decisiones de diseño tomadas

1. **Una talla por línea + matrix UI**: el configurador genera N líneas internas
   (una por talla con qty>0) compartiendo color y bordados. Sin cambio de schema.
2. **Bordados por bloque, no por línea**: la acción ✂ del bloque escribe en la 1ra
   card del bloque y replica al resto al cerrar el modal de bordados.
3. **Tabla profesional ≫ cards en grid**: la industria B2B usa tablas para líneas
   de cotización (alineación, densidad, comparabilidad). El profesor pidió "grilla"
   y se interpretó como tabla profesional, no como grid de cards.
4. **Catálogo + Configurador es el único punto de entrada** para agregar productos
   (no más "Agregar manual"). El "precio negociado" se cubre dentro del configurador.
5. **Modal Agregar Cliente debe ser idéntico al de `/clientes`** — consistencia
   total con el módulo padre.

## Deuda técnica documentada

Ver `memory/project_cotizaciones_wizard_debt.md` — el flujo de bordados aún pasa
por las cards legacy ocultas porque `#ubicacionCatalogoModal` está acoplado a
`currentBordadoCard`. Eliminar ese acoplamiento es el próximo refactor lógico
si se vuelve a tocar el módulo.

## Lo que queda pendiente (para mañana o más adelante)

### Validación end-to-end en navegador
- [ ] Crear cotización completa de cero (cliente nuevo, varios productos con
  bordados, distintos estados de prioridad).
- [ ] Editar cotización existente y verificar que los datos se cargan en la
  tabla agrupada.
- [ ] Convertir cotización aprobada a pedido y verificar que la conversión
  sigue funcionando.
- [ ] Probar dark mode en todos los modales (wizard, catálogo, configurador,
  agregar cliente).
- [ ] Probar responsive: el catálogo ya tiene media queries, validar en mobile.

### Posible refactor "limpio" de bordados (ver memoria de deuda)
- Mover bordados a estado JS del cart/grupo.
- Refactor de `#ubicacionCatalogoModal` para operar sobre `bordadosArray` arbitrario.
- Eliminar `#productos-container` y `addProductItem`.

### Merge a `enmanuel`
Cuando el usuario valide el flujo completo en navegador, mergear `feat/cotizaciones-wizard`
en `enmanuel` con `--no-ff`.

## Cómo retomar mañana

1. `git checkout feat/cotizaciones-wizard`
2. `git pull origin feat/cotizaciones-wizard`
3. Refresca el navegador con `Ctrl+F5` y empezá testing end-to-end (sección anterior).
4. Anotar bugs encontrados y corregir uno por uno con commits descriptivos.
5. Cuando todo esté validado: PR a `main` o merge directo a `enmanuel` según preferencia.

**Why:** Snapshot ejecutivo del refactor. Si la próxima sesión es nueva, este archivo
es suficiente para ponerse al día sin re-explorar el código.
