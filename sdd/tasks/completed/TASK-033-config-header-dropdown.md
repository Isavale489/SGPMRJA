# TASK-033: Ítem "Configuración del sistema" en el dropdown del header

**Feature**: FEAT-004 — Panel de Configuración del Sistema (base)
**Spec**: `sdd/specs/panel-configuracion.spec.md`
**Status**: done
**Priority**: medium
**Esfuerzo estimado**: S (< 2h)
**Depends-on**: TASK-031, TASK-032
**Assigned-to**: emmanuel

---

## Contexto

Implementa el **Módulo 4** del spec: el punto de acceso al panel. Va al final
(regla SDD: accesos de navegación después de tener pantalla funcional) para que
el ítem no apunte a una página a medias. Decisión 2026-06-12: el acceso vive en
el dropdown "Configuración" del header — el sidebar NO se toca.

---

## Scope

- Añadir `dropdown-item` **"Configuración del sistema"** en el dropdown del perfil del header, dentro del bloque `@if (Auth::user()->isAdmin())` existente, debajo de "Configuración de usuarios".
- Enlace: `route('configuracion.index')`. Icono: `mdi mdi-cog-outline` con `text-primary` (mismo estilo de los ítems hermanos).

**NO está en alcance**:
- Tocar el sidebar (decisión explícita del spec)
- Reordenar o renombrar los ítems existentes del dropdown
- El ítem "Configuración de seguridad" (es de FEAT-005)

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `resources/views/admin/layouts/header.blade.php` | MODIFY | Un `dropdown-item` nuevo en el bloque isAdmin (líneas ~337-342) |

---

## Codebase Contract (Anti-Alucinación)

### Markup real a imitar (verificado)
```blade
{{-- header.blade.php:337-342 — el ítem nuevo va DENTRO de este mismo @if, después de este <a> --}}
@if (Auth::user()->isAdmin())
    <a class="dropdown-item d-flex align-items-center" href="{{ url('users') }}">
        <i class="mdi mdi-account-group-outline fs-16 align-middle me-2 text-primary"></i>
        <span class="align-middle">Configuración de usuarios</span>
    </a>
@endif
```

### Datos verificados
```
- El botón del dropdown ya se titula "Configuración" (header.blade.php:300)
- Ítems existentes: "Configuración de perfil" (:313, route('profile.edit')) y
  "Configuración de usuarios" (:338, url('users'))
- app/Models/User.php:101 → isAdmin() { return $this->role === 'Administrador'; }
- Hay bloque comentado del template Velzon (líneas 317-336) — NO descomentar ni borrar en esta task
```

### NO existe — no referenciar
- ~~ruta `configuracion.index`~~ hasta que TASK-031 esté completada — verificar con `php artisan route:list | grep configuracion` antes de empezar
- ~~sección "Administración" en el sidebar~~ — descartada en revisión 0.2 del spec

---

## Criterios de aceptación

- [ ] Admin: el dropdown muestra Perfil / Usuarios / **Sistema** / Cerrar sesión, estilos idénticos
- [ ] Supervisor: el ítem NO se renderiza (mismo comportamiento que "Configuración de usuarios")
- [ ] Click navega a `/configuracion`
- [ ] Dark mode: el ítem hereda los estilos del `profile-dropdown-menu` sin ajustes extra

---

## QA manual

1. Login admin → abrir dropdown del header → verificar orden e iconografía.
2. Click → `/configuracion` carga.
3. Login Supervisor → el ítem no aparece.
4. Toggle dark mode con el dropdown abierto.

---

## Instrucciones para el ejecutor

1. **Verifica que TASK-031 y TASK-032 están en `tasks/completed/`**.
2. **Verifica el Codebase Contract** (las líneas del header pueden haberse corrido).
3. **Actualiza el header de esta task**: `Status: in-progress`.
4. **Implementa**; **mueve a `tasks/completed/`** con Nota de Completitud.

---

## Nota de Completitud

**Completado por**: emmanuel (+ Claude Code)
**Fecha**: 2026-06-12
**Commits**: `3a672d1` (rama `feat/panel-configuracion`)
**Notas**: dropdown-item agregado dentro del `@if isAdmin()` existente, debajo de
"Configuración de usuarios", con el mismo markup e icono `mdi mdi-cog-outline`.
QA por render del header en tinker: como Administrador el ítem aparece con el
href correcto; como Supervisor no se renderiza (y conserva "Configuración de
perfil"). QA visual en navegador (dark mode) pendiente junto al del panel.

**Desviaciones del spec**: ninguna.
