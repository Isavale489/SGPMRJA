---
name: Conflicto silencioso merge Santiago — proveedores/index.blade.php
description: Merge de Santiago (2026-05-04) sobreescribió el header unificado de búsqueda en Proveedores sin conflicto marcado
type: project
originSessionId: 7085129f-42f5-4324-82ca-3ea221a6538c
---
# Conflicto silencioso — `resources/views/admin/proveedores/index.blade.php`

## Qué pasó
El merge de Santiago `79b47fa` (2026-05-04) integró su rama `fix/santiago/auditoria-tutores`
en `enmanuel`. Git no marcó conflicto, pero la versión de Santiago ganó sobre la tuya en la
sección del header de filtros de Proveedores.

## Qué se perdió
Tu commit `d614bd6` (2026-04-30) había aplicado el **patrón de búsqueda unificada**
(`navy-filter-header` + `navy-header-search` + `#custom-search-input`) a Proveedores,
igual al que tiene Clientes.

La versión actual (HEAD) tiene el patrón viejo de Santiago:
```html
<button class="navy-filter-toggle collapsed" ...>
    <div class="navy-filter-title">Filtros</div>
</button>
```

Falta el patrón correcto:
```html
<div class="navy-filter-header is-collapsed">
    <div class="navy-header-search">
        <i class="ri-search-line"></i>
        <input type="text" id="custom-search-input" class="navy-search-input" placeholder="Buscar proveedor..." autocomplete="off">
    </div>
    <div class="navy-header-divider"></div>
    <button class="navy-filter-btn collapsed" ...>Filtros + badge</button>
</div>
```

## Referencia
- Patrón correcto: `resources/views/admin/clientes/index.blade.php` líneas ~74-100
- Commit original tuyo: `d614bd6`
- Commit que lo sobreescribió: `79b47fa` (merge de Santiago)

**Why:** Santiago basó su rama en un commit anterior a tu UX unification. Al mergear,
Git eligió su estructura para esa sección del archivo.

**How to apply:** Restaurar el `navy-filter-header` con search input en proveedores,
copiando el patrón de clientes. Pendiente de hacer cuando se retome.
