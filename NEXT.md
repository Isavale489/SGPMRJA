# NEXT — Continuación de FEAT-005 (seguridad: roles dinámicos + permisos por módulo)

> Prompt para retomar el trabajo pendiente. Última actualización: 2026-06-15.
> Spec: `sdd/specs/seguridad-roles-permisos.spec.md`

## Estado actual

**Fase 1 MERGEADA a `dev`** (merge `a5f613e`):

| Task | Estado | Qué hizo |
|---|---|---|
| TASK-035 | ✅ | Tablas `rol`/`permiso_rol`, `user.role` ENUM → `role_id` con backfill, roles de sistema + paridad Supervisor (34 permisos) |
| TASK-036 | ✅ | Capa de compatibilidad en `User` (accessor `role`, `isAdmin/hasRole` vía relación) |
| TASK-037 | ✅ | Registry `config/modulos.php` + helper `tienePermiso()` + middleware `permiso` (registrado pero **dormido**) + `Gate::before` bypass admin |
| TASK-040 | ✅ | Limpieza de código muerto (roles fantasma Secretaria/Administrativa) |
| TASK-038 | ⬜ | Aplicar middleware `permiso` + UI por permisos |
| TASK-039 | ⬜ | Página "Configuración de seguridad" (Roles + Permisos) |
| TASK-041 | ⬜ | Doc + dump SQL + QA de regresión |

La fase 1 es **no-breaking**: el middleware `permiso` está registrado pero no aplicado; el sistema sigue gateando por los grupos `role:` vía la capa de compatibilidad.

## Prompt para retomar

```
Continuar con FEAT-005 (configuración de seguridad: roles dinámicos + permisos por módulo).

Contexto: ya está MERGEADO a dev la fase 1 (TASK-035/036/037/040): tablas rol/permiso_rol,
user.role ENUM → role_id con backfill, capa de compatibilidad en User (accessor role,
isAdmin/hasRole vía relación), registry config/modulos.php + helper tienePermiso() + middleware
'permiso' (registrado pero DORMIDO) + Gate::before bypass admin, y limpieza de roles fantasma.
Spec: sdd/specs/seguridad-roles-permisos.spec.md. Memoria: project_feat005_seguridad.md.

Antes de empezar:
1. git checkout dev && git pull, luego composer dump-autoload && php artisan migrate && php artisan config:clear
2. Crea rama de trabajo desde dev (p. ej. feat/seguridad-roles-permisos-fase2).

Falta implementar, en este orden:
- TASK-038 (sdd/tasks/active/): fundir los grupos role: de routes/web.php en el grupo auth con el
  middleware 'permiso', y migrar los gates de UI quemados (sidebar por *.ver, botones por permiso).
  CRÍTICO: QA de paridad — login Supervisor debe conservar EXACTAMENTE su acceso actual.
  Las 34 claves de PERMISOS_SUPERVISOR (migración 2026_06_15_000003) deben seguir cubiertas por config/modulos.php.
- TASK-039 (sdd/tasks/active/): página "Configuración de seguridad" (SeguridadController + vistas
  admin/seguridad/) con tab Roles (CRUD) y tab Permisos (matriz módulo×acción, sync + flush caché
  permisos.rol_{id}), ítem en el dropdown del header. Protegida solo por isAdmin(), NO por 'permiso'.
- TASK-041 (sdd/tasks/active/): doc docs/conventions/permissions.md, dump SQL actualizado
  (COORDINAR con Santi antes de commitear el dump), y QA de regresión completo del spec §4.

OJO (coordinación): TASK-038 y TASK-039 tocan ambos routes/web.php y header.blade.php → hazlas
secuenciales (038 primero, luego 039) o coordina el merge para evitar conflictos.
Lee cada task file completo (traen Codebase Contract verificado) y respeta su scope.

Pendiente menor de limpieza (no es parte del scope, solo si sobra tiempo): borrar los huérfanos
app/Mail/NuevaSolicitudCredito.php y app/Notifications/NuevaSolicitudCredito.php (importan el
modelo inexistente SolicitudCredito; inofensivos pero muertos).

Empieza por TASK-038.
```

## Puntos de riesgo a vigilar

- **Paridad del Supervisor**: tras aplicar `permiso` (TASK-038), el Supervisor debe conservar exactamente su acceso actual. Las 34 claves de `PERMISOS_SUPERVISOR` (en la migración `2026_06_15_000003`) son el contrato: `config/modulos.php` debe reflejarlas (verificado al cierre de fase 1: 34/34, 0 brechas).
- **Conflicto de archivos**: TASK-038 y TASK-039 editan ambos `routes/web.php` y `header.blade.php` → secuenciales.
- **Dump SQL**: coordinar con Santi antes de regenerar/commitear (preferencia registrada del proyecto).
- **Post-merge**: cualquier `git pull` de dev requiere `composer dump-autoload && php artisan migrate && php artisan config:clear`.
