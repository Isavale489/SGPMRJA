# QA de regresión — FEAT-005: Seguridad (roles + permisos)

> Checklist de validación manual en navegador. Cubre el golden path y edge cases del
> spec `sdd/specs/seguridad-roles-permisos.spec.md` (§4).
> Patrón: **definición en código (registry) + valor en BD (matriz por rol)**.

| Campo | Valor |
|---|---|
| Feature | FEAT-005 — roles dinámicos + permisos por módulo |
| Responsable QA | _____________________ |
| Fecha | _____________________ |
| Entorno / rama | `feat/TASK-041-doc-dump-qa` (o `enmanuel` tras merge) |
| Build / commit | _____________________ |

**Marca cada paso:** ✅ OK · ❌ Falla (anota qué viste) · ⏭️ N/A

---

## Preparación

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| 0.1 | Iniciar sesión como **Administrador** | Acceso normal | ☐ |
| 0.2 | Menú del header (arriba derecha) → **"Configuración de seguridad"** | El ítem existe y abre la página con pestañas **Roles** y **Permisos** | ☐ |
| 0.3 | Verificar acceso alterno: página **Configuración del sistema** → nav lateral "Otras configuraciones" → **Configuración de seguridad** | El enlace existe y lleva a la misma página | ☐ |

---

## Test A — Crear un rol nuevo

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| A.1 | Pestaña **Roles** → botón **"Nuevo rol"** | Abre el modal "Nuevo rol" | ☐ |
| A.2 | Nombre: `Vendedor`, descripción: `Prueba QA` → **Guardar** | Toast verde; aparece la fila "Vendedor" (0 usuarios, sin badge "Sistema", con botones editar/eliminar) | ☐ |
| A.3 | Intentar guardar un rol con nombre vacío | Error inline "El nombre del rol es obligatorio" | ☐ |
| A.4 | Intentar crear otro rol llamado `Vendedor` | Error de nombre duplicado | ☐ |

---

## Test B — Asignar permisos parciales

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| B.1 | Pestaña **Permisos** → select → **Vendedor** | Se muestra la matriz módulo × acción | ☐ |
| B.2 | Fila **Cotizaciones** → marcar `Gestionar` | Se marca automáticamente también `Ver` (prerrequisito) | ☐ |
| B.3 | Fila **Cotizaciones** → desmarcar `Ver` | Se desmarcan todas las acciones del módulo | ☐ |
| B.4 | Volver a marcar `Ver` + `Gestionar` → **Guardar permisos** | Toast verde | ☐ |

---

## Test C — Crear usuario con el rol nuevo

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| C.1 | Ir a **Usuarios** (`/users`) → crear usuario `vendedor@test.com`, **rol = Vendedor**, con contraseña conocida | El rol "Vendedor" aparece en el select de roles; usuario creado | ☐ |

---

## Test D — Entrar como el rol parcial (núcleo del QA)

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| D.1 | Cerrar sesión y entrar como `vendedor@test.com` | Login OK, va al Dashboard | ☐ |
| D.2 | Revisar el sidebar | Muestra **solo "Cotizaciones"** (no Pedidos, Compras, Maestros, Órdenes, Reportes) | ☐ |
| D.3 | Entrar a Cotizaciones | Ve el listado; como tiene `gestionar`, ve "Agregar Cotización" y acciones editar/eliminar | ☐ |
| D.4 | Escribir en la barra del navegador `/compras` directo | **403** (sin `compras.ver`) | ☐ |
| D.5 | Escribir `/configuracion/seguridad` directo | **403** | ☐ |

---

## Test E — Flush de caché (cambio sin re-login)

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| E.1 | Sin cerrar la sesión del vendedor, en otra ventana como **Admin**: Seguridad → Permisos → Vendedor → **desmarcar `Gestionar`** (dejar solo `Ver`) → Guardar | Toast verde | ☐ |
| E.2 | Volver a la sesión del vendedor y recargar el listado de Cotizaciones | Ya **no** aparecen botones crear/editar/eliminar | ☐ |
| E.3 | (Opcional) Forzar un `POST` de edición de cotización como vendedor | **403** | ☐ |

---

## Test F — Reglas de borrado de roles

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| F.1 | Como Admin, Seguridad → Roles → intentar eliminar **Vendedor** (con 1 usuario) | Botón eliminar **deshabilitado** (tooltip "Tiene usuarios asignados") | ☐ |
| F.2 | En Usuarios, reasignar `vendedor@test.com` a otro rol (o eliminarlo) | Usuario reasignado | ☐ |
| F.3 | Volver a Roles → eliminar **Vendedor** | Confirmación SweetAlert → toast verde → la fila desaparece | ☐ |

---

## Test G — Roles de sistema intocables

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| G.1 | Roles → **Administrador** | Badge "Sistema"; sin botones de acción (no editable/eliminable) | ☐ |
| G.2 | Roles → **Supervisor** | Badge "Sistema"; sin editar/eliminar | ☐ |
| G.3 | Permisos → abrir el select de rol | **Administrador NO aparece** (acceso total); **Supervisor SÍ** aparece (editable en matriz) | ☐ |

---

## Test H — Paridad Supervisor

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| H.1 | Entrar como un usuario **Supervisor** | Ve exactamente lo de siempre: Pedidos, Cotizaciones, Órdenes, Compras, Reportes y maestros (muestreo en 2-3 módulos) | ☐ |
| H.2 | Confirmar exportación PDF en Productos / Insumos / Proveedores | El Supervisor puede exportar el PDF (paridad preservada) | ☐ |
| H.3 | `/configuracion/seguridad` directo | **403** | ☐ |

---

## Edge cases

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| X.1 | Crear rol "VacioQA" sin marcar permisos, asignarlo a un usuario y entrar | Login OK sin error 500; ve **solo el Dashboard**; sidebar sin secciones rotas | ☐ |
| X.2 | Buscar "Admin" en el listado de Usuarios | El filtro por rol sigue funcionando (búsqueda migrada a `role_id`) | ☐ |
| X.3 | Abrir el PDF de Usuarios | Muestra el nombre del rol correctamente (vía accessor) | ☐ |

---

## Dark mode

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| DM.1 | Activar modo oscuro y revisar Seguridad | Matriz, chips de acción marcados, tabs y badge "Sistema" con buen contraste | ☐ |
| DM.2 | Modal "Nuevo rol" en dark mode | Hereda estándar `atlantico-modal`, legible | ☐ |

---

## Resultado global

- [ ] **APROBADO** — todos los pasos ✅ (las fallas N/A documentadas)
- [ ] **APROBADO CON OBSERVACIONES** — fallas menores no bloqueantes (listar abajo)
- [ ] **RECHAZADO** — fallas bloqueantes (listar abajo)

**Observaciones / fallas encontradas:**

```
(anota aquí: # de paso, qué esperabas, qué viste, captura si aplica)
```

**Firma QA:** _____________________  **Fecha:** ___________

---

> Notas para el equipo:
> - El QA automático (cobertura de rutas, `grep where('role'`, grupos `role:` retirados,
>   panel gateado por `can:acceso-seguridad`) ya pasó — ver Nota de Completitud de TASK-041.
> - El dump `database/sistema_atlantico.sql` lo actualiza Santi por separado.
