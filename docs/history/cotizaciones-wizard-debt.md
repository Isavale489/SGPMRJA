---
name: Cotizaciones wizard — deuda técnica bordados
description: El módulo Cotizaciones (wizard 3 pasos) mantiene un productos-container oculto como buffer porque el modal legacy de bordados (#ubicacionCatalogoModal) está acoplado a currentBordadoCard. Es la próxima refactorización lógica si se vuelve a tocar el módulo.
type: project
originSessionId: cdb81f0b-d893-4752-bd60-b9f59c507686
---
Tras el refactor del módulo Cotizaciones a wizard 3 pasos (rama
`feat/cotizaciones-wizard`, commits `b42de3a` → `7d8af28`), quedó una
deuda técnica intencional sobre el flujo de bordados.

## Arquitectura actual

- **UI nueva** (wizard, catálogo, configurador, tabla agrupada en paso 2)
  escribe al `#productos-container` oculto (`display: none !important`).
- Las cards legacy del container son la **fuente de verdad** para el
  FormData del submit (`<input name="productos[X][...]">`).
- El modal legacy de bordados `#ubicacionCatalogoModal` opera sobre
  `currentBordadoCard` (referencia jQuery a una `.product-item` oculta).
- La acción ✂ Bordado de cada fila de la tabla setea `currentBordadoCard`
  a la 1ra card del bloque, abre el modal, y al cerrar replica los
  bordados a las demás cards del bloque vía
  `window.__cotBordadoReplicateGroup`.

## Por qué se dejó así

El modal de bordados tiene ~400 líneas de lógica (ubicaciones estándar,
ubicaciones personalizadas, logos, cantidades, validaciones, recálculos
en vivo). Reescribirlo desde cero estaba fuera del alcance del sprint
del wizard.

## Refactor pendiente para eliminar la deuda

1. Refactor de `#ubicacionCatalogoModal` para operar sobre un parámetro
   `bordadosArray` arbitrario (no sobre `currentBordadoCard.data('bordados')`).
2. Almacenar bordados en el estado JS de cada item del `cotCart` / grupo.
3. Al submit, serializar el estado JS a FormData manualmente (en vez de
   depender de los inputs hidden de las cards).
4. Eliminar `#productos-container`, la función `addProductItem` y los
   handlers delegados sobre el container.

## Cómo aplicar

- **Si vas a tocar bordados** del módulo Cotizaciones: prioridad
  eliminar esta deuda antes de agregar features.
- **Si vas a tocar otra parte** del módulo: no necesitas refactorizar
  bordados; respeta la convención actual y agrega listeners delegados
  en `$(document)`, no en `$('#productos-container')`.

**Why:** Documentación explícita para que cualquier futura modificación
sepa que el flujo de bordados está intencionalmente amarrado a las cards
ocultas, y entienda cuál es el camino correcto para resolverlo si se
vuelve a tocar.
