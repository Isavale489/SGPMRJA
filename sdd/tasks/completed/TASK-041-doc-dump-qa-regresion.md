# TASK-041: Doc de convenciones, dump SQL actualizado y QA de regresión final

**Feature**: FEAT-005 — seguridad-roles-permisos
**Spec**: `sdd/specs/seguridad-roles-permisos.spec.md`
**Status**: completed
**Priority**: medium
**Esfuerzo estimado**: M (2-4h)
**Depends-on**: TASK-035, TASK-036, TASK-037, TASK-038, TASK-039, TASK-040
**Assigned-to**: Emmanuel

---

## Contexto

Cierre de FEAT-005. Cubre los criterios de aceptación del spec (§5) que no pertenecen a
ningún módulo individual: el doc de convenciones del patrón registry + matriz, el dump SQL
actualizado con las tablas nuevas y `user` sin ENUM, y el barrido de QA de regresión
completo (golden path + edge cases del §4).

Implementa los criterios de §5: doc `permissions.md`, dump actualizado, paridad verificada.

---

## Scope

- Crear `docs/conventions/permissions.md`: documentar el patrón completo — registry `config/modulos.php` (forma de una entrada, `comunes`), helper `tienePermiso()`, middleware `permiso` (deny-by-default + log), `Gate::before` admin, caché por rol y su flush, y cómo agregar un módulo/acción nuevo (una entrada en el registry → aparece en la matriz sin tocar la vista). Enlazar desde `docs/conventions/README.md`.
- Actualizar `database/sistema_atlantico.sql`: regenerar/aplicar el delta para que incluya las tablas `rol` y `permiso_rol`, `user` con `role_id` (sin ENUM `role`), los 2 roles sistema sembrados y el seed de paridad del Supervisor. (Política de dump del proyecto: ver memoria/CLAUDE.md — el usuario Santi gestiona la subida del dump; coordinar.)
- Ejecutar el QA de regresión completo del spec §4 (golden path pasos 1-9 + edge cases) y dejar registro en la Nota de Completitud.
- Verificar el barrido `php artisan route:list` contra el registry: ninguna ruta autenticada sin mapeo (checklist del §7).

**NO está en alcance**:
- Cambios de código de los módulos 1-6 (ya entregados por TASK-035..040).
- Nuevas features.

---

## Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `docs/conventions/permissions.md` | CREATE | patrón registry + matriz + autorización |
| `docs/conventions/README.md` | MODIFY | enlace al doc nuevo |
| `database/sistema_atlantico.sql` | UPDATE | tablas nuevas + `user` sin ENUM + seeds |

---

## Codebase Contract (Anti-Alucinación)

### Entregables previos a documentar (de TASK-035..039)
```
config/modulos.php                         (TASK-037) — registry
app/Support/helpers.php :: tienePermiso()  (TASK-037)
app/Http/Middleware/CheckPermiso.php       (TASK-037) — alias 'permiso'
app/Providers/AuthServiceProvider.php      (TASK-037) — Gate::before
app/Models/Rol.php, tablas rol/permiso_rol (TASK-035)
app/Http/Controllers/SeguridadController.php + views (TASK-039)
```

### Doc de referencia análogo (FEAT-004)
```
docs/conventions/system-config.md  — estructura/estilo a seguir para permissions.md
docs/conventions/README.md         — índice donde enlazar
```

### Política de dump (CLAUDE.md + memoria)
```
- El sistema corre sobre MySQL 8; el dump debe quedar en formato MySQL nativo.
- Cliente en C:\xampp\mysql\bin\. PowerShell: usar --result-file (NO '>').
- Memoria feedback_dump_mysql: Santi gestiona la subida del dump. COORDINAR antes de regenerar/commitear.
```

### Criterios de aceptación del spec a cerrar aquí (§5)
```
[ ] Doc nuevo docs/conventions/permissions.md con el patrón registry + matriz
[ ] Dump database/sistema_atlantico.sql actualizado
[ ] Paridad Administrador/Supervisor verificada (QA paso 9)
[ ] grep where('role' limpio  (verificación final transversal)
[ ] Los dos grupos role: eliminados; middleware permiso cubre las rutas
```

### NO existe — no referenciar
- ~~`docs/conventions/permissions.md`~~ — se crea aquí

---

## Notas de implementación

### Dump SQL
Seguir la política del proyecto (CLAUDE.md §Dump SQL). **Coordinar con Santi** antes de
regenerar/commitear el dump — según la memoria, él gestiona esa subida. No re-dumpear a
ciegas desde la MariaDB local (arrastra header MariaDB, `current_timestamp()`, anchos
`bigint(20)`); preferir delta de esquema sobre el dump de referencia MySQL.

### QA de regresión
Es la red de seguridad de toda la feature. Ejecutar los 9 pasos del golden path y los edge
cases del §4 (rol sin permisos, ruta sin mapeo, último admin, búsqueda de usuarios, PDF,
dos sesiones, `migrate:fresh` vs dump). Documentar resultados.

---

## Criterios de aceptación

- [ ] `docs/conventions/permissions.md` creado y enlazado en README
- [ ] Dump actualizado (coordinado con Santi) con tablas nuevas, `user` sin ENUM, seeds
- [ ] `grep -rn "where('role'" app/` limpio
- [ ] `php artisan route:list` sin rutas autenticadas sin mapeo en el registry
- [ ] QA de regresión §4 ejecutado y documentado (golden path + edge cases)
- [ ] Paridad Supervisor/Administrador confirmada
- [ ] `migrate:fresh` y dump dejan roles sembrados consistentes

---

## QA manual

1. Ejecutar el golden path completo del spec §4 (pasos 1-9).
2. Ejecutar los edge cases del §4.
3. `php artisan migrate:fresh` y, por separado, importar el dump → comparar estado de `rol`/`permiso_rol`.
4. Leer `permissions.md` y validar que un dev nuevo podría añadir un módulo siguiendo solo el doc.

---

## Instrucciones para el ejecutor

1. Lee el spec completo (§4 QA, §5 criterios, §7).
2. Confirma TASK-035..040 en `completed/`.
3. **Coordina con Santi** la actualización del dump antes de commitearlo.
4. `Status: in-progress`, `Assigned-to: <tu-nombre>`.
5. Rama: `git checkout -b feat/TASK-041-doc-dump-qa`.
6. Ejecuta QA, documenta, mueve a `completed/`, rellena Nota.
7. PR final de la feature contra `enmanuel`.

---

## Nota de Completitud

**Completado por**: Emmanuel
**Fecha**: 2026-06-18
**Commits**: doc `88be3e3`, dump `a949e2e`; mergeado a `enmanuel` vía PR #21 (merge `dd2a9f1`).
**Notas**:
- `docs/conventions/permissions.md` creado + enlazado en `docs/conventions/README.md`: patrón registry + matriz + autorización (helper `tienePermiso`, middleware `permiso` deny-by-default, `Gate::before`, caché por rol y flush, gate `acceso-seguridad` del panel, granularidad, cómo agregar módulo/acción).
- Dump `database/sistema_atlantico.sql` actualizado por Santi: incluye `rol` (2 roles sistema), `permiso_rol` (34 filas de paridad Supervisor) y `user.role_id` (sin ENUM `role`).
- **QA automático ✅**: `grep where('role'` limpio (0), grupos `role:` retirados de web.php (solo en comentarios), 0 huecos de rutas con `permiso`, 6 rutas `seguridad.*` con `can:acceso-seguridad`.
- **QA manual de regresión ✅ PASÓ** (confirmado por Santi 2026-06-18): golden path §4 + edge cases. Runbook en `sdd/qa/FEAT-005-qa-regresion.md`.
- Paridad Administrador/Supervisor confirmada (en navegador desde TASK-038/039 y en este QA).

**Desviaciones del spec**:
1. **Dump con sabor MariaDB** (no MySQL nativo): se exportó vía phpMyAdmin desde la MariaDB local de Santi (`bigint(20)`, header phpMyAdmin) en vez del MySQL 8 nativo que pide CLAUDE.md. Decisión pragmática de Santi; el contenido es correcto e importa en MySQL 8. Aclaración registrada en memoria [[feedback_dump_mysql]].
2. **Runbook de QA como artefacto** (`sdd/qa/FEAT-005-qa-regresion.md`): no estaba en el scope original, pero formaliza el QA manual del §4 para que lo ejecute el encargado.
