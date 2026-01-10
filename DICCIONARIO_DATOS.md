# DICCIONARIO DE DATOS

**Sistema de Gestión para Pedidos - Manufacturas R.J. Atlántico C.A**  
**Base de Datos: atlantico_db**

---

## 1. TABLAS MAESTRAS

### 1.1. Tabla: banco

Almacena el catálogo de bancos para métodos de pago electrónicos.

| Campo | Tipo de Dato | Longitud | Restricciones | Descripción |
|-------|-------------|----------|---------------|-------------|
| id | BIGINT | - | PK, AI, Unsigned | Identificador único del banco. |
| nombre | VARCHAR | 191 | Not Null, Unique | Nombre del banco. |
| created_at | TIMESTAMP | - | Nullable | Fecha y hora de creación del registro. |
| updated_at | TIMESTAMP | - | Nullable | Fecha y hora de última actualización. |

### 1.2. Tabla: proveedor

Almacena la información de los proveedores de insumos.

| Campo | Tipo de Dato | Longitud | Restricciones | Descripción |
|-------|-------------|----------|---------------|-------------|
| id | BIGINT | - | PK, AI, Unsigned | Identificador único del proveedor. |
| razon_social | VARCHAR | 100 | Not Null | Razón social del proveedor. |
| rif | VARCHAR | 15 | Not Null, Unique | RIF del proveedor. |
| estado | TINYINT | 1 | Not Null, Default: 1 | Estado del proveedor (1: Activo, 0: Inactivo). |
| created_at | TIMESTAMP | - | Nullable | Fecha y hora de creación del registro. |
| updated_at | TIMESTAMP | - | Nullable | Fecha y hora de última actualización. |
| deleted_at | TIMESTAMP | - | Nullable | Fecha y hora de eliminación lógica (soft delete). |

### 1.3. Tabla: persona

Tabla que almacena los datos generales de todas las personas (clientes, usuarios).

| Campo | Tipo de Dato | Longitud | Restricciones | Descripción |
|-------|-------------|----------|---------------|-------------|
| id | BIGINT | - | PK, AI, Unsigned | Identificador único de la persona. |
| nombre | VARCHAR | 191 | Not Null | Nombre de la persona. |
| apellido | VARCHAR | 191 | Not Null | Apellido de la persona. |
| documento_identidad | VARCHAR | 191 | Not Null, Unique | Número de documento de identidad. |
| email | VARCHAR | 191 | Not Null, Unique | Correo electrónico de la persona. |
| direccion | TEXT | - | Nullable | Dirección física de la persona. |
| ciudad | VARCHAR | 191 | Nullable | Ciudad de residencia. |
| sexo | VARCHAR | 191 | Nullable | Sexo de la persona. |
| proveedores_id | BIGINT | - | Not Null, Unsigned, FK | ID del proveedor asociado. |
| created_at | TIMESTAMP | - | Nullable | Fecha y hora de creación del registro. |
| updated_at | TIMESTAMP | - | Nullable | Fecha y hora de última actualización. |
| deleted_at | TIMESTAMP | - | Nullable | Fecha y hora de eliminación lógica (soft delete). |

### 1.4. Tabla: cliente

Almacena información específica de clientes, vinculada a una persona.

| Campo | Tipo de Dato | Longitud | Restricciones | Descripción |
|-------|-------------|----------|---------------|-------------|
| id | BIGINT | - | PK, AI, Unsigned | Identificador único del cliente. |
| persona_id | BIGINT | - | Nullable, Unsigned, FK | ID de la persona asociada. |
| tipo_cliente | ENUM | - | Not Null | Tipo de cliente (natural, juridico). |
| estado | TINYINT | - | Not Null, Default: 1 | Estado del cliente (1: Activo, 0: Inactivo). |
| created_at | TIMESTAMP | - | Nullable | Fecha y hora de creación del registro. |
| updated_at | TIMESTAMP | - | Nullable | Fecha y hora de última actualización. |
| deleted_at | TIMESTAMP | - | Nullable | Fecha y hora de eliminación lógica (soft delete). |

### 1.5. Tabla: telefono

Almacena los números de teléfono de las personas.

| Campo | Tipo de Dato | Longitud | Restricciones | Descripción |
|-------|-------------|----------|---------------|-------------|
| id | BIGINT | - | PK, AI, Unsigned | Identificador único del teléfono. |
| persona_id | BIGINT | - | Not Null, Unsigned, FK | ID de la persona propietaria. |
| numero | VARCHAR | 20 | Not Null | Número de teléfono. |
| tipo | ENUM | - | Not Null, Default: 'movil' | Tipo de teléfono (movil, trabajo, casa, otro). |
| created_at | TIMESTAMP | - | Nullable | Fecha y hora de creación del registro. |
| updated_at | TIMESTAMP | - | Nullable | Fecha y hora de última actualización. |

### 1.6. Tabla: user

Almacena las credenciales de acceso al sistema vinculadas a personas.

| Campo | Tipo de Dato | Longitud | Restricciones | Descripción |
|-------|-------------|----------|---------------|-------------|
| id | BIGINT | - | PK, AI, Unsigned | Identificador único del usuario. |
| persona_id | BIGINT | - | Nullable, Unsigned, FK | ID de la persona asociada. |
| role | ENUM | - | Not Null | Rol del usuario (Administrador, Supervisor, Operario, Almacenero). |
| email_verified_at | TIMESTAMP | - | Nullable | Fecha y hora de verificación del email. |
| password | VARCHAR | 255 | Not Null | Contraseña encriptada del usuario. |
| avatar | TEXT | - | Nullable | Ruta de la imagen de perfil del usuario. |
| estado | TINYINT | 1 | Not Null, Default: 1 | Estado del usuario (1: Activo, 0: Inactivo). |
| remember_token | VARCHAR | 100 | Nullable | Token para función "Recordarme". |
| created_at | TIMESTAMP | - | Nullable | Fecha y hora de creación del registro. |
| updated_at | TIMESTAMP | - | Nullable | Fecha y hora de última actualización. |

### 1.7. Tabla: producto

Almacena el catálogo de productos textiles que ofrece la empresa.

| Campo | Tipo de Dato | Longitud | Restricciones | Descripción |
|-------|-------------|----------|---------------|-------------|
| id | BIGINT | - | PK, AI, Unsigned | Identificador único del producto. |
| nombre | VARCHAR | 100 | Not Null | Nombre del producto. |
| descripcion | TEXT | - | Nullable | Descripción detallada del producto. |
| modelo | VARCHAR | 50 | Not Null | Modelo del producto. |
| precio_base | DECIMAL | 10,2 | Not Null | Precio base del producto. |
| imagen | VARCHAR | 255 | Nullable | Ruta de la imagen del producto. |
| estado | TINYINT | 1 | Not Null, Default: 1 | Estado del producto (1: Activo, 0: Inactivo). |
| created_at | TIMESTAMP | - | Nullable | Fecha y hora de creación del registro. |
| updated_at | TIMESTAMP | - | Nullable | Fecha y hora de última actualización. |
| deleted_at | TIMESTAMP | - | Nullable | Fecha y hora de eliminación lógica (soft delete). |

### 1.8. Tabla: insumo

Almacena el inventario de insumos necesarios para la producción.

| Campo | Tipo de Dato | Longitud | Restricciones | Descripción |
|-------|-------------|----------|---------------|-------------|
| id | BIGINT | - | PK, AI, Unsigned | Identificador único del insumo. |
| nombre | VARCHAR | 100 | Not Null | Nombre del insumo. |
| tipo | ENUM | - | Not Null | Tipo de insumo (Tela, Hilo, Botón, Cierre, Etiqueta, Otro). |
| unidad_medida | VARCHAR | 20 | Not Null | Unidad de medida (metros, kilos, unidades). |
| costo_unitario | DECIMAL | 10,2 | Not Null | Costo por unidad del insumo. |
| stock_actual | DECIMAL | 10,2 | Not Null | Cantidad disponible en inventario. |
| stock_minimo | DECIMAL | 10,2 | Not Null | Cantidad mínima antes de generar alerta. |
| proveedor_id | BIGINT | - | Nullable, Unsigned, FK | ID del proveedor principal del insumo. |
| estado | TINYINT | 1 | Not Null, Default: 1 | Estado del insumo (1: Activo, 0: Inactivo). |
| created_at | TIMESTAMP | - | Nullable | Fecha y hora de creación del registro. |
| updated_at | TIMESTAMP | - | Nullable | Fecha y hora de última actualización. |
| deleted_at | TIMESTAMP | - | Nullable | Fecha y hora de eliminación lógica (soft delete). |

---

## 2. TABLAS DE COTIZACIONES

### 2.1. Tabla: cotizacion

Almacena las cotizaciones generadas para clientes.

| Campo | Tipo de Dato | Longitud | Restricciones | Descripción |
|-------|-------------|----------|---------------|-------------|
| id | BIGINT | - | PK, AI, Unsigned | Identificador único de la cotización. |
| cliente_id | BIGINT | - | Not Null, Unsigned, FK | ID del cliente asociado. |
| cliente_nombre | VARCHAR | 191 | Not Null | Nombre del cliente (desnormalizado). |
| cliente_email | VARCHAR | 191 | Nullable | Email del cliente (desnormalizado). |
| cliente_telefono | VARCHAR | 191 | Nullable | Teléfono del cliente (desnormalizado). |
| ci_rif | VARCHAR | 191 | Nullable | CI o RIF del cliente. |
| fecha_cotizacion | DATE | - | Not Null | Fecha de emisión de la cotización. |
| fecha_validez | DATE | - | Nullable | Fecha de validez de la cotización. |
| estado | VARCHAR | 191 | Not Null, Default: 'Pendiente' | Estado (Pendiente, Aprobada, Rechazada). |
| total | DECIMAL | 10,2 | Not Null, Default: 0.00 | Monto total de la cotización. |
| created_at | TIMESTAMP | - | Nullable | Fecha y hora de creación del registro. |
| updated_at | TIMESTAMP | - | Nullable | Fecha y hora de última actualización. |
| deleted_at | TIMESTAMP | - | Nullable | Fecha y hora de eliminación lógica (soft delete). |

### 2.2. Tabla: detalle_cotizacion

Almacena los productos incluidos en cada cotización.

| Campo | Tipo de Dato | Longitud | Restricciones | Descripción |
|-------|-------------|----------|---------------|-------------|
| id | BIGINT | - | PK, AI, Unsigned | Identificador único del detalle. |
| cotizacion_id | BIGINT | - | Not Null, Unsigned, FK | ID de la cotización padre. |
| producto_id | BIGINT | - | Not Null, Unsigned, FK | ID del producto cotizado. |
| cantidad | INT | - | Not Null | Cantidad de unidades del producto. |
| descripcion | TEXT | - | Nullable | Descripción o especificaciones. |
| lleva_bordado | TINYINT | 1 | Not Null, Default: 0 | Indica si lleva bordado (1: Sí, 0: No). |
| nombre_logo | VARCHAR | 191 | Nullable | Nombre o texto del logo a bordar. |
| ubicacion_logo | VARCHAR | 255 | Nullable | Ubicación del logo en la prenda. |
| cantidad_logo | INT | - | Nullable | Cantidad de logos a bordar. |
| talla | VARCHAR | 191 | Nullable | Talla específica solicitada. |
| precio_unitario | DECIMAL | 10,2 | Not Null, Default: 0.00 | Precio unitario del producto. |
| created_at | TIMESTAMP | - | Nullable | Fecha y hora de creación del registro. |
| updated_at | TIMESTAMP | - | Nullable | Fecha y hora de última actualización. |

---

## 3. TABLAS DE PEDIDOS

### 3.1. Tabla: pedido

Almacena los pedidos confirmados de los clientes.

| Campo | Tipo de Dato | Longitud | Restricciones | Descripción |
|-------|-------------|----------|---------------|-------------|
| id | BIGINT | - | PK, AI, Unsigned | Identificador único del pedido. |
| cliente_id | BIGINT | - | Not Null, Unsigned, FK | ID del cliente que realiza el pedido. |
| cliente_nombre | VARCHAR | 255 | Not Null | Nombre del cliente (desnormalizado). |
| cliente_email | VARCHAR | 255 | Nullable | Email del cliente (desnormalizado). |
| cliente_telefono | VARCHAR | 255 | Nullable | Teléfono del cliente (desnormalizado). |
| ci_rif | VARCHAR | 255 | Nullable | CI o RIF del cliente. |
| fecha_pedido | DATE | - | Not Null | Fecha de creación del pedido. |
| fecha_entrega_estimada | DATE | - | Nullable | Fecha estimada de entrega. |
| estado | VARCHAR | 255 | Not Null, Default: 'Pendiente' | Estado (Pendiente, Procesando, Completado, Cancelado). |
| prioridad | VARCHAR | 191 | Not Null, Default: 'Normal' | Prioridad (Normal, Alta, Urgente). |
| total | DECIMAL | 10,2 | Not Null, Default: 0.00 | Monto total del pedido. |
| abono | DECIMAL | 10,2 | Not Null, Default: 0.00 | Monto abonado por el cliente. |
| efectivo_pagado | TINYINT | 1 | Not Null, Default: 0 | Indica si se pagó con efectivo (1: Sí, 0: No). |
| transferencia_pagado | TINYINT | 1 | Not Null, Default: 0 | Indica si se pagó con transferencia (1: Sí, 0: No). |
| pago_movil_pagado | TINYINT | 1 | Not Null, Default: 0 | Indica si se pagó con pago móvil (1: Sí, 0: No). |
| referencia_transferencia | VARCHAR | 191 | Nullable | Número de referencia de transferencia. |
| referencia_pago_movil | VARCHAR | 191 | Nullable | Número de referencia de pago móvil. |
| banco_id | BIGINT | - | Nullable, Unsigned, FK | ID del banco usado para pago electrónico. |
| user_id | BIGINT | - | Not Null, Unsigned, FK | ID del usuario que creó el pedido. |
| created_at | TIMESTAMP | - | Nullable | Fecha y hora de creación del registro. |
| updated_at | TIMESTAMP | - | Nullable | Fecha y hora de última actualización. |
| deleted_at | TIMESTAMP | - | Nullable | Fecha y hora de eliminación lógica (soft delete). |

### 3.2. Tabla: detalle_pedido

Almacena los productos incluidos en cada pedido con sus especificaciones.

| Campo | Tipo de Dato | Longitud | Restricciones | Descripción |
|-------|-------------|----------|---------------|-------------|
| id | BIGINT | - | PK, AI, Unsigned | Identificador único del detalle. |
| pedido_id | BIGINT | - | Not Null, Unsigned, FK | ID del pedido padre. |
| producto_id | BIGINT | - | Not Null, Unsigned, FK | ID del producto solicitado. |
| cantidad | INT | - | Not Null | Cantidad de unidades del producto. |
| descripcion | TEXT | - | Nullable | Descripción o especificaciones adicionales. |
| lleva_bordado | TINYINT | 1 | Not Null, Default: 0 | Indica si lleva bordado (1: Sí, 0: No). |
| nombre_logo | VARCHAR | 255 | Nullable | Nombre o texto del logo a bordar. |
| ubicacion_logo | VARCHAR | 255 | Nullable | Ubicación del logo en la prenda. |
| cantidad_logo | INT | - | Nullable | Cantidad de logos a bordar. |
| color | VARCHAR | 50 | Nullable | Color específico solicitado. |
| talla | VARCHAR | 50 | Nullable | Talla específica solicitada. |
| precio_unitario | DECIMAL | 10,2 | Not Null | Precio unitario del producto. |
| created_at | TIMESTAMP | - | Nullable | Fecha y hora de creación del registro. |
| updated_at | TIMESTAMP | - | Nullable | Fecha y hora de última actualización. |

### 3.3. Tabla: detalle_pedido_insumo

Tabla intermedia que almacena los insumos estimados por cada detalle de pedido.

| Campo | Tipo de Dato | Longitud | Restricciones | Descripción |
|-------|-------------|----------|---------------|-------------|
| id | BIGINT | - | PK, AI, Unsigned | Identificador único del registro. |
| detalle_pedido_id | BIGINT | - | Not Null, Unsigned, FK | ID del detalle de pedido. |
| insumo_id | BIGINT | - | Not Null, Unsigned, FK | ID del insumo. |
| cantidad_estimada | DECIMAL | 8,2 | Not Null | Cantidad estimada del insumo. |
| created_at | TIMESTAMP | - | Nullable | Fecha y hora de creación del registro. |
| updated_at | TIMESTAMP | - | Nullable | Fecha y hora de última actualización. |

---

## 4. TABLAS DE PRODUCCIÓN

### 4.1. Tabla: orden_produccion

Almacena las órdenes de producción para fabricar productos.

| Campo | Tipo de Dato | Longitud | Restricciones | Descripción |
|-------|-------------|----------|---------------|-------------|
| id | BIGINT | - | PK, AI, Unsigned | Identificador único de la orden de producción. |
| pedido_id | BIGINT | - | Nullable, Unsigned, FK | ID del pedido origen. |
| producto_id | BIGINT | - | Not Null, Unsigned, FK | ID del producto a fabricar. |
| cantidad_solicitada | INT | - | Not Null | Cantidad total a producir. |
| cantidad_producida | INT | - | Not Null, Default: 0 | Cantidad ya producida. |
| fecha_inicio | DATE | - | Not Null | Fecha de inicio de producción. |
| fecha_fin_estimada | DATE | - | Not Null | Fecha estimada de finalización. |
| estado | ENUM | - | Not Null, Default: 'Pendiente' | Estado (Pendiente, En Proceso, Finalizado, Cancelado). |
| costo_estimado | DECIMAL | 12,2 | Not Null, Default: 0.00 | Costo estimado de producción. |
| logo | TEXT | - | Nullable | Ruta del archivo de imagen del logo. |
| notas | TEXT | - | Nullable | Notas u observaciones de producción. |
| created_by | BIGINT | - | Not Null, Unsigned, FK | ID del usuario que creó la orden. |
| created_at | TIMESTAMP | - | Nullable | Fecha y hora de creación del registro. |
| updated_at | TIMESTAMP | - | Nullable | Fecha y hora de última actualización. |
| deleted_at | TIMESTAMP | - | Nullable | Fecha y hora de eliminación lógica (soft delete). |

### 4.2. Tabla: detalle_orden_insumo

Almacena los insumos asignados a cada orden de producción.

| Campo | Tipo de Dato | Longitud | Restricciones | Descripción |
|-------|-------------|----------|---------------|-------------|
| id | BIGINT | - | PK, AI, Unsigned | Identificador único del detalle. |
| orden_produccion_id | BIGINT | - | Not Null, Unsigned, FK | ID de la orden de producción. |
| insumo_id | BIGINT | - | Not Null, Unsigned, FK | ID del insumo asignado. |
| cantidad_estimada | DECIMAL | 10,2 | Not Null | Cantidad estimada a utilizar. |
| cantidad_utilizada | DECIMAL | 10,2 | Not Null, Default: 0.00 | Cantidad realmente utilizada. |
| created_at | TIMESTAMP | - | Nullable | Fecha y hora de creación del registro. |
| updated_at | TIMESTAMP | - | Nullable | Fecha y hora de última actualización. |

### 4.3. Tabla: produccion_diaria

Almacena los registros diarios de producción por operario.

| Campo | Tipo de Dato | Longitud | Restricciones | Descripción |
|-------|-------------|----------|---------------|-------------|
| id | BIGINT | - | PK, AI, Unsigned | Identificador único del registro de producción. |
| orden_id | BIGINT | - | Not Null, Unsigned, FK | ID de la orden de producción. |
| operario_id | BIGINT | - | Not Null, Unsigned, FK | ID del operario (usuario). |
| cantidad_producida | INT | - | Not Null | Cantidad de unidades producidas. |
| cantidad_defectuosa | INT | - | Not Null, Default: 0 | Cantidad de unidades defectuosas. |
| observaciones | TEXT | - | Nullable | Observaciones o incidencias del día. |
| created_at | TIMESTAMP | - | Nullable | Fecha y hora de creación del registro. |
| updated_at | TIMESTAMP | - | Nullable | Fecha y hora de última actualización. |
| deleted_at | TIMESTAMP | - | Nullable | Fecha y hora de eliminación lógica (soft delete). |

---

## 5. TABLAS DE INVENTARIO

### 5.1. Tabla: movimiento_insumo

Almacena el historial de movimientos de inventario de insumos.

| Campo | Tipo de Dato | Longitud | Restricciones | Descripción |
|-------|-------------|----------|---------------|-------------|
| id | BIGINT | - | PK, Unsigned | Identificador único del movimiento. |
| insumo_id | BIGINT | - | Not Null, Unsigned, FK | ID del insumo afectado. |
| tipo_movimiento | ENUM | - | Not Null | Tipo de movimiento (Entrada, Salida). |
| cantidad | DECIMAL | 10,2 | Not Null | Cantidad del movimiento. |
| stock_anterior | DECIMAL | 10,2 | Not Null | Stock antes del movimiento. |
| stock_nuevo | DECIMAL | 10,2 | Not Null | Stock después del movimiento. |
| motivo | TEXT | - | Nullable | Motivo o descripción del movimiento. |
| created_by | BIGINT | - | Not Null, Unsigned, FK | ID del usuario que registró el movimiento. |
| created_at | TIMESTAMP | - | Nullable | Fecha y hora de creación del registro. |
| updated_at | TIMESTAMP | - | Nullable | Fecha y hora de última actualización. |

---

## RESUMEN DE TABLAS

| # | Tabla | Categoría | Total de Campos |
|---|-------|-----------|-----------------|
| 1 | banco | Maestros | 4 |
| 2 | proveedor | Maestros | 7 |
| 3 | persona | Maestros | 12 |
| 4 | cliente | Maestros | 7 |
| 5 | telefono | Maestros | 6 |
| 6 | user | Autenticación | 10 |
| 7 | producto | Productos | 10 |
| 8 | insumo | Insumos | 12 |
| 9 | cotizacion | Cotizaciones | 13 |
| 10 | detalle_cotizacion | Cotizaciones | 13 |
| 11 | pedido | Pedidos | 21 |
| 12 | detalle_pedido | Pedidos | 14 |
| 13 | detalle_pedido_insumo | Pedidos | 6 |
| 14 | orden_produccion | Producción | 15 |
| 15 | detalle_orden_insumo | Producción | 7 |
| 16 | produccion_diaria | Producción | 9 |
| 17 | movimiento_insumo | Inventario | 10 |

**Total de tablas**: 17  
**Total de campos**: 176

---

## RELACIONES PRINCIPALES (FOREIGN KEYS)

### **Maestros:**
- `persona.proveedores_id` → `proveedor.id` (ON DELETE NO ACTION)
- `cliente.persona_id` → `persona.id` (ON DELETE CASCADE)
- `telefono.persona_id` → `persona.id` (ON DELETE CASCADE)
- `user.persona_id` → `persona.id` (ON DELETE CASCADE)
- `insumo.proveedor_id` → `proveedor.id` (ON DELETE SET NULL)

### **Cotizaciones:**
- `cotizacion.cliente_id` → `cliente.id` (ON DELETE NO ACTION)
- `detalle_cotizacion.cotizacion_id` → `cotizacion.id` (ON DELETE CASCADE)
- `detalle_cotizacion.producto_id` → `producto.id` (ON DELETE CASCADE)

### **Pedidos:**
- `pedido.cliente_id` → `cliente.id` (ON DELETE NO ACTION)
- `pedido.user_id` → `user.id` (ON DELETE CASCADE)
- `pedido.banco_id` → `banco.id` (ON DELETE SET NULL)
- `detalle_pedido.pedido_id` → `pedido.id` (ON DELETE CASCADE)
- `detalle_pedido.producto_id` → `producto.id` (ON DELETE CASCADE)
- `detalle_pedido_insumo.detalle_pedido_id` → `detalle_pedido.id` (ON DELETE CASCADE)
- `detalle_pedido_insumo.insumo_id` → `insumo.id` (ON DELETE CASCADE)

### **Producción:**
- `orden_produccion.pedido_id` → `pedido.id` (ON DELETE SET NULL)
- `orden_produccion.producto_id` → `producto.id` (ON DELETE CASCADE)
- `orden_produccion.created_by` → `user.id`
- `detalle_orden_insumo.orden_produccion_id` → `orden_produccion.id` (ON DELETE CASCADE)
- `detalle_orden_insumo.insumo_id` → `insumo.id` (ON DELETE CASCADE)
- `produccion_diaria.orden_id` → `orden_produccion.id` (ON DELETE CASCADE)
- `produccion_diaria.operario_id` → `user.id` (ON DELETE CASCADE)

### **Inventario:**
- `movimiento_insumo.insumo_id` → `insumo.id` (ON DELETE CASCADE)
- `movimiento_insumo.created_by` → `user.id`

---

## ÍNDICES ÚNICOS

- `banco.nombre` → UNIQUE
- `proveedor.rif` → UNIQUE
- `persona.documento_identidad` → UNIQUE
- `persona.email` → UNIQUE
- `detalle_pedido_insumo(detalle_pedido_id, insumo_id)` → UNIQUE (composite)

---

**Sistema de Gestión para Pedidos - Manufacturas R.J. Atlántico C.A**  
**Grupo Textil - Sección 536**  
**PNF Informática - UPTP "JJ Montilla"**  
**Fecha**: Diciembre 2025
