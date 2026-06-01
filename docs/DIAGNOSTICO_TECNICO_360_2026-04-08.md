# Diagnóstico Técnico 360° — SGPMRJA

**Fecha:** 2026-04-08  
**Rol de auditoría:** Lead Software Architect & Principal QA Engineer  
**Objetivo:** Evaluar integralmente el sistema antes del punto de control con tutores, con foco en integridad transaccional, seguridad, rendimiento, UX de errores y consistencia arquitectónica.

---

## 1) Alcance y método

Se auditó el estado actual del código fuente y estructura de datos en:

- Rutas web/API y middlewares de acceso
- Controladores clave de negocio (Pedidos, Cotizaciones, Inventario, Órdenes, Producción)
- Servicios de dominio con lógica transaccional
- Modelos y políticas de Soft Delete
- Migraciones recientes de integridad referencial
- Manejo de errores en backend y feedback en frontend (SweetAlert/AJAX)

### Módulos revisados (principalmente)

- `routes/web.php`, `routes/api.php`
- `app/Http/Controllers/PedidoController.php`
- `app/Http/Controllers/CotizacionController.php`
- `app/Http/Controllers/MovimientoInsumoController.php`
- `app/Http/Controllers/OrdenProduccionController.php`
- `app/Http/Controllers/DetalleOrdenInsumoController.php`
- `app/Http/Controllers/InsumoController.php`
- `app/Http/Controllers/ProduccionDiariaController.php`
- `app/Services/PedidoService.php`
- `app/Services/CotizacionService.php`
- `app/Http/Kernel.php`, `app/Http/Middleware/CheckRole.php`, `app/Http/Middleware/CheckAdminRole.php`
- `app/Exceptions/Handler.php`
- `database/migrations/2026_03_19_*.php`
- `database/schema/mysql-schema.sql` (tomado como referencia histórica, no siempre alineada al estado más reciente de migraciones)

---

## 2) Resumen Ejecutivo

El sistema presenta una base funcional sólida para demo: hay buena separación por módulos, uso extendido de SoftDeletes, validaciones de entrada razonables y flujos críticos encapsulados en servicios con transacciones.

Sin embargo, persisten **riesgos operativos de alto impacto** en inventario y producción (atomicidad incompleta y control insuficiente de stock), junto con inconsistencias arquitectónicas y de UX de error que pueden exponerse frente a tutores en escenarios de concurrencia o datos límite.

**Conclusión general:**
- Estado actual: **Aprobable con riesgo medio-alto en operaciones críticas.**
- Para una demo robusta: resolver primero atomicidad/consistencia de inventario y estandarización de errores.

---

## 3) Hallazgos por eje de auditoría

## 3.1 Integridad de flujos (transacciones y consistencia)

### Fortalezas detectadas

- Flujo de creación/actualización de pedidos encapsulado con `DB::transaction(...)` en `PedidoService`.
- Conversión cotización→pedido con bloqueo pesimista (`lockForUpdate`) y validación anti-duplicado dentro de la transacción en `CotizacionService`.
- Endurecimiento de FKs (CASCADE→RESTRICT) en migraciones recientes para preservar trazabilidad histórica (`2026_03_19_200003_cr02_cr03_me03_cascade_to_restrict.php`).

### Riesgos críticos

1. **Transacción con salida temprana sin rollback explícito en inventario**  
   En `MovimientoInsumoController::store`, se inicia `DB::beginTransaction()` y, ante stock insuficiente en salidas, se retorna respuesta 422 dentro del `try` sin rollback explícito previo.

2. **Operaciones multi-tabla sin transacción en órdenes de producción**  
   `OrdenProduccionController::store` y `update` crean/actualizan orden y luego manipulan pivotes de insumos (`attach/sync`) sin envolver todo en una transacción única.

3. **Actualización de consumo de insumos con atomicidad parcial**  
   En `DetalleOrdenInsumoController::update`, el detalle se actualiza fuera de la transacción y luego se ajusta stock/movimiento dentro de transacción. Esto abre una ventana de inconsistencia si falla una parte.

4. **Riesgo de stock negativo por falta de validación final concurrente**  
   El descuento de stock en `DetalleOrdenInsumoController::update` no valida explícitamente que el resultado no sea negativo en el momento de persistencia.

### Caso solicitado: “¿qué pasa si inhabilito/eliminar un insumo usado en una orden?”

- `InsumoController::destroy` aplica soft delete sin regla de negocio previa.
- A nivel BD, migraciones recientes endurecen FKs históricas (`movimiento_insumo.insumo_id` y `detalle_orden_insumo.insumo_id` en RESTRICT), lo cual protege borrado físico.
- Riesgo funcional: en relaciones Eloquent que no usan `withTrashed`, un insumo inhabilitado puede dejar de mostrarse en consultas históricas y degradar trazabilidad visual.

**Impacto:** histórico no necesariamente se rompe en BD, pero sí puede “desaparecer” en UI/consultas de negocio.

---

## 3.2 Seguridad y validación

### Fortalezas detectadas

- Rutas críticas dentro de grupo `auth` + `role` con segmentación Admin/Supervisor.
- Uso de FormRequests en entidades sensibles (`StorePedidoRequest`, `UpdatePedidoRequest`, etc.).
- Modelos con `fillable` definidos (protección base contra mass assignment indiscriminado).

### Riesgos críticos

1. **Inconsistencia de middleware legacy de admin**  
   `CheckAdminRole` valida rol `'Administrativa'` mientras el sistema maneja `'Administrador'/'Supervisor'`. Aunque parece no ser el middleware principal en rutas actuales, es deuda técnica peligrosa.

2. **Validaciones duplicadas e inline en varios controladores**  
   Cotizaciones, insumos, órdenes y producción dependen de validaciones embebidas; esto incrementa riesgo de divergencia funcional entre store/update.

3. **Sanitización de inputs no estandarizada**  
   Existe validación de formato, pero no una capa homogénea de normalización textual/encoding para entradas libres (riesgo de inconsistencias de caracteres especiales y comportamientos no uniformes).

4. **Mensajería de error no uniforme para frontend AJAX**  
   Unos endpoints responden `error`, otros `message`, y el frontend mezcla ambos contratos; ante edge cases, puede mostrarse mensaje vacío o genérico.

---

## 3.3 Eficiencia (N+1 y rendimiento)

### Fortalezas detectadas

- Buen uso de eager loading en varias pantallas de alta carga (clientes, pedidos, órdenes, inventario).
- DataTables server-side en módulos principales.

### Riesgos y oportunidades

1. **N+1 residual por accessors encadenados**  
   En listados donde se consume información derivada de `cliente/persona/telefonos` sin eager loading profundo completo, pueden dispararse consultas por fila.

2. **Lookups repetidos en loops de servicios**  
   En cálculo/creación de detalles de pedido/cotización, se consulta `Producto::find(...)` por ítem en cada iteración; en cargas altas, esto escala mal.

3. **Consultas con `whereRaw/DB::raw` correctas funcionalmente, pero con oportunidad de refactor semántico**  
   No hay evidencia de inyección en los casos revisados (uso parametrizado en los críticos), pero hay margen para mejorar legibilidad y mantenibilidad.

---

## 3.4 UX y feedback del sistema

### Fortalezas detectadas

- Uso extendido de SweetAlert para éxito/error en CRUD.
- En muchos módulos se capturan errores AJAX y se intentan mostrar mensajes de backend.

### Riesgos críticos

1. **Manejo global de excepciones mínimo**  
   `app/Exceptions/Handler.php` no incorpora estrategia consistente para respuestas JSON en errores inesperados, lo que puede derivar en 500 HTML para flujos AJAX.

2. **Contratos de error heterogéneos backend/frontend**  
   Inconsistencia entre `error`, `message`, y estructuras de validación (`errors`) dificulta feedback uniforme.

3. **Posibles estados “congelados” de UI en fallos no previstos**  
   En scripts extensos con múltiples requests, si una rama no captura todos los formatos de error, la UX queda degradada.

---

## 3.5 Consistencia arquitectónica

### Fortalezas detectadas

- Patrón de “Inhabilitar/Historial” ya presente en varios módulos mediante SoftDeletes.
- Evolución reciente guiada por auditoría (migraciones con prefijos CR/ME/BA y foco en integridad).

### Inconsistencias clave

1. **Patrón inhabilitar/restaurar no estandarizado entre módulos**  
   `TipoProducto` tiene flujo más completo (historial + restore), pero no todos los catálogos equivalentes muestran mismo comportamiento.

2. **Código legado/deuda técnica coexistiendo con estándar nuevo**  
   Ejemplo: middleware `CheckAdminRole` con rol antiguo, trazas de nombres históricos y contratos de respuesta heterogéneos.

3. **Desalineación potencial entre `database/schema/mysql-schema.sql` y migraciones recientes**  
   Para decisiones de integridad, se debe confiar en migraciones activas + estado real de BD del entorno, no solo en dump de schema.

---

## 4) Entregable solicitado

## ✅ Puntos Fuertes (lo ya sólido)

- Segmentación de rutas y control de acceso por rol bien planteado.
- Uso de transacciones y locking en flujos core de pedido/cotización.
- SoftDeletes ampliamente aplicado en dominio principal.
- Esfuerzo reciente de hardening de FKs para proteger historial.
- Buen uso de eager loading en parte importante de listados críticos.

## ⚠️ Riesgos Críticos (lo que puede fallar en demo)

- Atomicidad incompleta en inventario/producción.
- Posible inconsistencia de stock en condiciones límite.
- Inhabilitación de insumos usados sin política uniforme de visualización histórica.
- Errores no controlados globalmente para consumo AJAX.
- Inconsistencia de contratos de error y middleware legacy con rol desactualizado.
- N+1 residual en algunos listados/accessors.

## 🛠️ Mejoras Sugeridas (deuda técnica y pulido)

### Prioridad P0 (antes de la demo)

1. Transaccionar completamente operaciones multi-tabla en:
   - Movimiento de inventario
   - Orden de producción (store/update)
   - Detalle de consumo de insumos por orden

2. Definir política formal para insumos referenciados:
   - Bloquear inhabilitación si tiene historial activo, o
   - Permitir inhabilitación pero garantizar lectura histórica con relaciones `withTrashed` donde aplique.

3. Estandarizar payload de error JSON (`message`, `errors`, `code`) y centralizar fallback para excepciones no controladas.

### Prioridad P1 (inmediatamente después)

4. Reducir N+1 residual:
   - Eager loading profundo en listados con accessors de persona/telefonía.
   - Evitar `find` por iteración en servicios de cálculo de detalle.

5. Migrar validaciones inline repetidas a FormRequests en módulos faltantes.

6. Unificar patrón de Inhabilitar/Restaurar entre catálogos para coherencia UX/operativa.

### Prioridad P2 (cierre técnico)

7. Retirar o alinear middleware/código legado no usado (`CheckAdminRole` y otros artefactos incompatibles).
8. Documentar formalmente contrato de errores y convención de estados/enum para evitar regresiones.

---

## 5) Riesgo consolidado y criterio de salida

## Semáforo actual

- **Integridad transaccional:** Amarillo/Rojo (según flujo)
- **Seguridad de acceso:** Verde/Amarillo
- **Rendimiento (N+1):** Amarillo
- **UX de fallos:** Amarillo/Rojo
- **Consistencia arquitectónica:** Amarillo

## Criterio mínimo recomendado para “listo para demo”

- Todas las operaciones multi-tabla críticas protegidas por transacción completa.
- Contrato de errores JSON unificado y validado en frontend de módulos core.
- Política explícita y consistente para inhabilitar entidades referenciadas (insumos/productos/clientes/proveedores).

---

## 6) Observaciones finales para Tech Lead

- La base funcional está madura y demuestra evolución técnica consciente.
- Los riesgos detectados son corregibles en ventana corta si se priorizan por impacto en demo.
- Recomendación de ejecución: atacar primero atomicidad + UX de errores, luego rendimiento residual y estandarización arquitectónica.

---

**Fin del diagnóstico técnico 360°.**
