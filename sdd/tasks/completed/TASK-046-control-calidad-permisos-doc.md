# TASK-046: Permisos del módulo `calidad` + actualizar business-flows

**Feature**: FEAT-006 — control-calidad
**Spec**: `sdd/specs/control-calidad.spec.md`
**Status**: done
**Priority**: medium
**Esfuerzo estimado**: S
**Depends-on**: TASK-044
**Assigned-to**: unassigned

---

## Contexto
Módulo 5 del spec + cierre documental. La inspección es responsabilidad del
**Supervisor** (carril del diagrama de actividad); hay que registrarlo en el
registry de permisos (FEAT-005) y dejar de marcar Calidad como "pendiente".

> Paralelizable con TASK-045 (no comparten archivos): una toca vistas, esta
> toca `config/modulos.php` + docs.

## Scope
- Registrar el módulo `calidad` en `config/modulos.php` con acciones
  `ver` / `inspeccionar` y mapeo de rutas (`calidad.index|calidad.data|calidad.detalle` → `ver`; `calidad.inspeccionar` → `inspeccionar`).
- Asegurar que el rol **Supervisor** obtenga `calidad.ver` + `calidad.inspeccionar`
  (vía la matriz/seed de roles de FEAT-005).
- Actualizar `docs/conventions/business-flows.md`: añadir el bloque "Control de
  Calidad" al flujo maestro (tras `ORDEN → Finalizado`) y quitarlo de "placeholder
  pendiente".

**NO está en alcance**: lógica/UI (otras tasks).

## Archivos a crear / modificar
| Archivo | Acción | Descripción |
|---|---|---|
| `config/modulos.php` | MODIFY | Módulo `calidad` (acciones + rutas) |
| `docs/conventions/business-flows.md` | MODIFY | Documentar Control de Calidad en el flujo |
| (seed/matriz roles FEAT-005) | MODIFY | Asignar permisos `calidad.*` al Supervisor |

## Codebase Contract (Anti-Alucinación)

### Hechos verificados
```php
// config/modulos.php — forma de un módulo (:60+):
// 'modulo' => [
//   'nombre'   => 'Etiqueta humana',
//   'acciones' => ['ver' => '...', 'gestionar' => '...'],
//   'rutas'    => ['ruta.a|ruta.b' => 'accion'],
// ];
// clave de permiso = 'modulo.accion'; el middleware aborta 403 si falta el permiso.
```
Bloque a añadir (ajustar wording):
```php
'calidad' => [
    'nombre'   => 'Control de Calidad',
    'acciones' => [
        'ver'         => 'Ver inspecciones y órdenes pendientes de calidad',
        'inspeccionar'=> 'Registrar inspección de calidad de una orden',
    ],
    'rutas' => [
        'calidad.index|calidad.data|calidad.detalle' => 'ver',
        'calidad.inspeccionar'                       => 'inspeccionar',
    ],
],
```

### NO existe — verificar antes
- Confirmar **cómo FEAT-005 asigna permisos a un rol** (tabla `permiso_rol` / seed / matriz UI) leyendo `sdd/specs/seguridad-roles-permisos.spec.md` y el seeder real antes de tocar. NO inventar el mecanismo de asignación.
- Confirmar el **nombre exacto del rol Supervisor** en la tabla `rol` (no asumir el slug).

### Convenciones a respetar
- `docs/conventions/business-flows.md` (estructura del documento).
- `sdd/specs/seguridad-roles-permisos.spec.md` (FEAT-005) para el registry.

## Criterios de aceptación
- [ ] Módulo `calidad` aparece en `config/modulos.php` con sus rutas mapeadas.
- [ ] Un usuario con rol Supervisor accede a `/calidad` y puede inspeccionar; uno sin permiso recibe 403.
- [ ] `business-flows.md` documenta Control de Calidad (ya no "pendiente").

## QA manual
1. Login Supervisor → `/calidad` carga (200).
2. Login con rol sin permiso `calidad.*` → 403 al entrar a `/calidad`.
3. Revisar `business-flows.md` actualizado.
