# 📋 Requerimientos Funcionales del Sistema de Gestión para Pedidos
## Manufacturas R.J. Atlántico C.A

**Grupo Textil - Sección 536**  
**PNF Informática - UPTP "JJ Montilla"**

---

## 1️⃣ MAESTRO DE USUARIOS

### RF-USU-001: Registrar Usuario
El sistema debe permitir al Administrador registrar nuevos usuarios en el sistema, ingresando los siguientes datos:
- Nombre completo
- Correo electrónico
- Contraseña
- Rol (Administrador, Supervisor, Operario, Almacenero)
- Estado (Activo/Inactivo)
- Avatar - foto (opcional)

**Validaciones:**
- El correo electrónico debe ser único
- El correo debe tener formato válido
- La contraseña debe tener mínimo 8 caracteres
- Todos los campos son obligatorios excepto el avatar

### RF-USU-002: Consultar Usuarios
El sistema debe permitir visualizar un listado de todos los usuarios registrados con la siguiente información:
- Nombre
- Correo electrónico
- Rol asignado
- Estado (Activo/Inactivo)
- Avatar - foto

**Características:**
- Tabla con paginación
- Búsqueda en tiempo real
- Filtrado por rol
- Ordenamiento por columnas

### RF-USU-003: Modificar Usuario
El sistema debe permitir al Administrador modificar la información de usuarios existentes:
- Actualizar nombre
- Cambiar correo electrónico
- Modificar rol
- Cambiar estado (Activo/Inactivo)
- Actualizar avatar - foto

**Restricciones:**
- No se puede modificar el usuario actualmente en sesión si es el único Administrador
- El correo debe seguir siendo único

### RF-USU-004: Eliminar Usuario
El sistema debe permitir al Administrador eliminar usuarios del sistema mediante eliminación lógica (soft delete).

**Restricciones:**
- No se puede eliminar el usuario actual
- No se puede eliminar si es el único Administrador
- La eliminación es reversible

### RF-USU-005: Activar/Desactivar Usuario
El sistema debe permitir cambiar el estado de un usuario entre Activo e Inactivo.

**Efecto:**
- Usuario Inactivo no puede iniciar sesión
- Se mantiene el registro en el sistema

### RF-USU-006: Buscar Usuario
El sistema debe permitir buscar usuarios por:
- Nombre
- Correo electrónico
- Rol

**Características:**
- Búsqueda en tiempo real (AJAX)
- Resultados mientras se escribe

### RF-USU-007: Control de Acceso por Rol
El sistema debe validar y restringir el acceso a funcionalidades según el rol del usuario:
- **Administrador**: Acceso total
- **Supervisor**: Gestión de producción y pedidos
- **Operario**: Registro de producción diaria
- **Almacenero**: Gestión de inventario

---

## 2️⃣ MAESTRO DE CLIENTES

### RF-CLI-001: Registrar Cliente
El sistema debe permitir registrar nuevos clientes ingresando:
- Nombre completo
- CI/RIF
- Teléfono
- Correo electrónico
- Dirección

**Validaciones:**
- Nombre es obligatorio
- CI/RIF debe ser único
- Formato válido de correo electrónico
- Teléfono con formato válido

### RF-CLI-002: Consultar Clientes
El sistema debe permitir visualizar un listado de todos los clientes con:
- Nombre
- CI/RIF
- Teléfono
- Correo electrónico
- Dirección
- Estado

**Características:**
- DataTable con paginación del lado del servidor
- Búsqueda en tiempo real
- Ordenamiento por columnas
- Mostrar/ocultar columnas

### RF-CLI-003: Modificar Cliente
El sistema debe permitir modificar la información de clientes existentes.

**Validaciones:**
- Si se cambia el CI/RIF, debe seguir siendo único
- Formato de correo válido
- Todos los campos obligatorios deben estar llenos

### RF-CLI-004: Eliminar Cliente
El sistema debe permitir eliminar clientes mediante soft delete (eliminación lógica).

**Restricciones:**
- No se puede eliminar si tiene pedidos o cotizaciones activas
- La eliminación es reversible

### RF-CLI-005: Activar/Desactivar Cliente
El sistema debe permitir cambiar el estado del cliente (Activo/Inactivo).

**Efecto:**
- Cliente Inactivo no puede crear nuevos pedidos/cotizaciones
- Se mantiene histórico de transacciones

### RF-CLI-006: Buscar Cliente
El sistema debe permitir búsqueda AJAX de clientes para:
- Creación rápida de pedidos
- Creación de cotizaciones
- Búsqueda por: nombre, CI/RIF, teléfono

**Características:**
- Autocompletado
- Resultados mientras se escribe
- Sin recargar la página

### RF-CLI-007: Exportar Reporte de Clientes
El sistema debe permitir generar un reporte en PDF con el listado completo de clientes.

**Contenido del reporte:**
- Logo de la empresa
- Fecha y hora de generación
- Listado de clientes con todos sus datos
- Total de clientes registrados

---

## 3️⃣ MAESTRO DE PRODUCTOS

### RF-PRO-001: Registrar Producto
El sistema debe permitir registrar productos textiles con:
- Nombre del producto
- Descripción
- Precio unitario
- Tallas disponibles (XS, S, M, L, XL, XXL, XXXL)
- Colores disponibles
- Estado (Activo/Inactivo)

**Validaciones:**
- Nombre es obligatorio y único
- Precio debe ser mayor a 0
- Descripción obligatoria

### RF-PRO-002: Consultar Productos
El sistema debe mostrar catálogo de productos con:
- Nombre
- Descripción
- Precio
- Tallas disponibles
- Estado

**Características:**
- DataTable con paginación
- Búsqueda en tiempo real
- Ordenamiento por precio, nombre
- Filtro por estado

### RF-PRO-003: Modificar Producto
El sistema debe permitir actualizar información de productos:
- Cambiar precio
- Actualizar descripción
- Modificar tallas disponibles
- Cambiar estado

**Validaciones:**
- Precio debe ser mayor a 0
- Nombre debe seguir siendo único

### RF-PRO-004: Eliminar Producto
El sistema debe permitir eliminar productos mediante soft delete.

**Restricciones:**
- No se puede eliminar si está en pedidos activos
- No se puede eliminar si está en órdenes de producción en proceso

### RF-PRO-005: Activar/Desactivar Producto
El sistema debe permitir cambiar estado del producto.

**Efecto:**
- Productos inactivos no se muestran en nuevos pedidos/cotizaciones
- Se mantienen en pedidos históricos

### RF-PRO-006: Buscar Producto
El sistema debe permitir buscar productos por:
- Nombre
- Rango de precio
- Talla

### RF-PRO-007: Exportar Catálogo de Productos
El sistema debe generar reporte PDF del catálogo con:
- Listado completo de productos
- Precios actualizados
- Tallas disponibles
- Total de productos activos

---

## 4️⃣ MAESTRO DE INSUMOS

### RF-INS-001: Registrar Insumo
El sistema debe permitir registrar insumos (materias primas) con:
- Nombre del insumo
- Tipo (Tela, Hilo, Botón, Cierre, Elástico, etc.)
- Unidad de medida (metros, kilogramos, unidades, etc.)
- Costo unitario
- Stock actual
- Stock mínimo
- Proveedor
- Estado (Activo/Inactivo)

**Validaciones:**
- Nombre obligatorio
- Costo unitario mayor a 0
- Stock mínimo mayor a 0
- Stock actual >= 0
- Proveedor debe existir en el sistema

### RF-INS-002: Consultar Insumos
El sistema debe mostrar inventario de insumos con:
- Nombre
- Tipo
- Stock actual
- Stock mínimo
- Costo unitario
- Proveedor
- Estado
- Indicador visual si stock está bajo (stock actual ≤ stock mínimo)

**Características:**
- DataTable con paginación
- Búsqueda por nombre, tipo
- Ordenamiento por stock
- Filtro por estado de stock (normal, bajo, crítico)
- Resaltado de insumos con stock bajo en color rojo/amarillo

### RF-INS-003: Modificar Insumo
El sistema debe permitir actualizar información de insumos:
- Cambiar costo unitario
- Modificar stock mínimo
- Actualizar proveedor
- Cambiar estado

**Validaciones:**
- Costo unitario mayor a 0
- Stock mínimo mayor a 0
- El stock actual NO se modifica aquí (solo con movimientos de inventario)

### RF-INS-004: Eliminar Insumo
El sistema debe permitir eliminar insumos mediante soft delete.

**Restricciones:**
- No se puede eliminar si tiene movimientos de inventario
- No se puede eliminar si está asignado a órdenes de producción activas

### RF-INS-005: Activar/Desactivar Insumo
El sistema debe cambiar estado del insumo.

**Efecto:**
- Insumos inactivos no se pueden asignar a nuevas órdenes
- Se mantienen en órdenes históricas

### RF-INS-006: Buscar Insumo
El sistema debe permitir buscar por:
- Nombre
- Tipo
- Proveedor
- Estado de stock

### RF-INS-007: Alertas de Stock Bajo
El sistema debe mostrar automáticamente:
- Notificación en el dashboard cuando hay insumos con stock_actual ≤ stock_mínimo
- Listado de insumos con stock crítico
- Cantidad faltante para llegar al stock mínimo

**Visualización:**
- Badge de alerta en menú principal
- Contador de insumos críticos
- Sección específica en dashboard

---

## 5️⃣ MAESTRO DE PROVEEDORES

### RF-PRV-001: Registrar Proveedor
El sistema debe permitir registrar proveedores con:
- Nombre de la empresa
- RIF
- Teléfono
- Correo electrónico
- Dirección
- Estado (Activo/Inactivo)

**Validaciones:**
- Nombre obligatorio
- RIF único y obligatorio
- Formato válido de correo
- Teléfono con formato válido

### RF-PRV-002: Consultar Proveedores
El sistema debe mostrar listado de proveedores con:
- Nombre
- RIF
- Teléfono
- Correo
- Dirección
- Estado
- Cantidad de insumos que suministra

**Características:**
- DataTable con paginación
- Búsqueda en tiempo real
- Ordenamiento por columnas
- Filtro por estado

### RF-PRV-003: Modificar Proveedor
El sistema debe permitir actualizar información de proveedores.

**Validaciones:**
- RIF debe seguir siendo único si se cambia
- Correo con formato válido
- Campos obligatorios llenos

### RF-PRV-004: Eliminar Proveedor
El sistema debe eliminar proveedores mediante soft delete.

**Restricciones:**
- No se puede eliminar si tiene insumos activos asociados
- Debe reasignar insumos a otro proveedor antes de eliminar

### RF-PRV-005: Activar/Desactivar Proveedor
El sistema debe cambiar estado del proveedor.

**Efecto:**
- Proveedores inactivos no se pueden asignar a nuevos insumos
- Se mantienen en insumos existentes

### RF-PRV-006: Buscar Proveedor
El sistema debe buscar por:
- Nombre
- RIF
- Tipo de insumos que suministra

### RF-PRV-007: Ver Insumos por Proveedor
El sistema debe mostrar listado de todos los insumos que suministra un proveedor específico.

**Información:**
- Nombre del insumo
- Stock actual
- Costo unitario
- Última compra

---

## 6️⃣ REQUERIMIENTOS GENERALES DE TODOS LOS MAESTROS

### RF-GEN-001: Autenticación
El sistema debe requerir autenticación para acceder a cualquier funcionalidad de gestión de maestros.

### RF-GEN-002: Auditoría
El sistema debe registrar automáticamente:
- Fecha y hora de creación (created_at)
- Fecha y hora de última modificación (updated_at)
- Usuario que realizó la acción

### RF-GEN-003: Validación de Datos
El sistema debe validar todos los datos antes de guardar:
- Campos obligatorios llenos
- Formato correcto de datos (email, teléfono, etc.)
- Unicidad donde corresponda
- Rangos válidos (precios > 0, stocks >= 0)

### RF-GEN-004: Mensajes de Confirmación
El sistema debe mostrar mensajes de confirmación/error después de cada operación:
- Éxito al crear/modificar/eliminar
- Error con descripción clara del problema
- Confirmación antes de eliminar

### RF-GEN-005: Responsive Design
El sistema debe ser responsive y funcionar correctamente en:
- Computadoras de escritorio
- Tablets
- Dispositivos móviles

### RF-GEN-006: Manejo de Errores
El sistema debe manejar errores de forma amigable:
- Mensajes claros para el usuario
- Sin mostrar errores técnicos
- Log de errores para el administrador

### RF-GEN-007: Performance
El sistema debe:
- Cargar listados en menos de 3 segundos
- Usar paginación del lado del servidor para grandes volúmenes
- Implementar caché donde sea necesario

---

## 📊 RESUMEN DE REQUERIMIENTOS POR MAESTRO

| Maestro | Total RFs | CRUD | Búsqueda | Reportes | Validaciones | Otros |
|---------|-----------|------|----------|----------|--------------|-------|
| **Usuarios** | 7 | ✅ | ✅ | - | ✅ | Control de roles |
| **Clientes** | 7 | ✅ | ✅ | ✅ PDF | ✅ | Búsqueda AJAX |
| **Productos** | 7 | ✅ | ✅ | ✅ PDF | ✅ | Catálogo |
| **Insumos** | 7 | ✅ | ✅ | - | ✅ | Alertas stock |
| **Proveedores** | 7 | ✅ | ✅ | - | ✅ | Ver insumos |
| **Generales** | 7 | - | - | - | ✅ | Auditoría, seguridad |
| **TOTAL** | **42** | | | | | |

---

## 🎯 NOTAS IMPORTANTES

1. **Eliminación Lógica (Soft Delete)**: Todos los maestros usan soft delete para mantener integridad referencial y permitir auditoría.

2. **DataTables**: Todos los listados usan Yajra DataTables con paginación del lado del servidor para mejor performance.

3. **Búsqueda AJAX**: Clientes tiene búsqueda AJAX específica para agilizar creación de pedidos.

4. **Reportes PDF**: Solo Clientes y Productos tienen reportes PDF por ser los más consultados.

5. **Alertas**: Solo Insumos tiene sistema de alertas por ser crítico para la producción.

6. **Validaciones**: Todas las validaciones se implementan tanto en frontend (JavaScript) como en backend (Laravel).

---

**Documento generado para:**  
Sistema de Gestión para Pedidos - Manufacturas R.J. Atlántico C.A  
Grupo Textil - Sección 536  
PNF Informática - UPTP "JJ Montilla"  
**Fecha:** Noviembre 2025
