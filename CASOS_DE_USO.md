# 📋 Casos de Uso del Sistema de Gestión para Pedidos
## Manufacturas R.J. Atlántico C.A

**Grupo Textil - Sección 536**  
**PNF Informática - UPTP "JJ Montilla"**

---

## 👥 Actores del Sistema

### Actor Principal
**Cliente Externo**: Persona o empresa que solicita productos textiles (no tiene acceso directo al sistema)

### Actores Internos (Usuarios del Sistema)

1. **👤 Administrador**
   - Control total del sistema
   - Gestión de usuarios y configuraciones
   - Acceso a todos los módulos y reportes

2. **👨‍💼 Supervisor**
   - Gestión de producción
   - Creación de órdenes de producción
   - Supervisión de operarios
   - Gestión de pedidos y cotizaciones
   - **Gestión de inventario de insumos**
   - **Control de entradas y salidas de inventario**
   - **Generación de alertas de stock**

3. **👷 Operario**
   - Registro de producción diaria
   - Consulta de órdenes asignadas
   - Actualización de avances

---

## 📊 Diagrama de Casos de Uso General

```mermaid
graph TB
    subgraph Actores
        Admin[👤 Administrador]
        Supervisor[👨‍💼 Supervisor]
        Operario[👷 Operario]
        Cliente[Cliente Externo]
    end
    
    subgraph Sistema["Sistema de Gestión para Pedidos"]
        CU1[Gestionar Usuarios]
        CU2[Gestionar Clientes]
        CU3[Gestionar Productos]
        CU4[Gestionar Proveedores]
        CU5[Gestionar Insumos]
        CU6[Crear Cotización]
        CU7[Crear Pedido]
        CU8[Crear Orden Producción]
        CU9[Registrar Producción Diaria]
        CU10[Gestionar Inventario]
        CU11[Generar Reportes]
    end
    
    Cliente -.solicita.-> CU6
    Supervisor --> CU6
    Supervisor --> CU7
    Supervisor --> CU8
    Supervisor --> CU5
    Supervisor --> CU10
    Operario --> CU9
    Admin --> CU1
    Admin --> CU2
    Admin --> CU3
    Admin --> CU4
    Admin --> CU5
    Admin --> CU10
    Admin --> CU11
```

---

## 🎯 CASOS DE USO DETALLADOS

---

## 1️⃣ MÓDULO DE USUARIOS

### CU-001: Registrar Usuario

**Actor Principal**: Administrador

**Precondiciones**:
- El administrador ha iniciado sesión
- Tiene permisos de administrador

**Flujo Principal**:
1. El administrador accede al módulo de usuarios
2. Selecciona la opción "Crear Nuevo Usuario"
3. El sistema muestra el formulario de registro
4. El administrador ingresa:
   - Nombre completo
   - Correo electrónico
   - Contraseña
   - Rol (Administrador, Supervisor, Operario)
   - Foto/Avatar (opcional)
   - Estado (Activo/Inactivo)
5. El sistema valida que el correo no esté registrado
6. El sistema encripta la contraseña
7. El sistema guarda el usuario
8. El sistema muestra mensaje de confirmación

**Flujos Alternativos**:
- **3a. Correo ya existe**: El sistema muestra error y solicita otro correo
- **3b. Contraseña débil**: El sistema solicita contraseña más segura (mínimo 8 caracteres)

**Postcondiciones**:
- Usuario creado en la base de datos
- Usuario puede iniciar sesión

---

### CU-002: Modificar Usuario

**Actor Principal**: Administrador

**Precondiciones**:
- El administrador ha iniciado sesión
- Existe al menos un usuario en el sistema

**Flujo Principal**:
1. El administrador accede al listado de usuarios
2. Busca y selecciona el usuario a modificar
3. Hace clic en "Editar"
4. El sistema muestra el formulario pre-llenado
5. El administrador modifica los datos necesarios
6. El sistema valida los cambios
7. El sistema actualiza la información
8. El sistema muestra mensaje de confirmación

**Flujos Alternativos**:
- **5a. Intenta eliminar el único administrador**: El sistema muestra error
- **5b. Cambio de correo a uno existente**: El sistema muestra error

**Postcondiciones**:
- Información del usuario actualizada

---

### CU-003: Eliminar Usuario

**Actor Principal**: Administrador

**Precondiciones**:
- El administrador ha iniciado sesión
- Existe el usuario a eliminar
- No es el único administrador del sistema

**Flujo Principal**:
1. El administrador accede al listado de usuarios
2. Selecciona el usuario a eliminar
3. Hace clic en "Eliminar"
4. El sistema solicita confirmación
5. El administrador confirma
6. El sistema realiza eliminación lógica (soft delete)
7. El sistema muestra mensaje de confirmación

**Flujos Alternativos**:
- **4a. Usuario cancela**: No se elimina nada
- **5a. Es el único administrador**: El sistema rechaza la eliminación

**Postcondiciones**:
- Usuario marcado como eliminado
- Usuario no puede iniciar sesión
- Registro permanece en BD para auditoría

---

## 2️⃣ MÓDULO DE CLIENTES

### CU-004: Registrar Cliente

**Actor Principal**: Administrador, Supervisor

**Precondiciones**:
- Usuario ha iniciado sesión
- Tiene permisos de administrador o supervisor

**Flujo Principal**:
1. Usuario accede al módulo de clientes
2. Selecciona "Crear Nuevo Cliente"
3. El sistema muestra formulario de registro
4. Usuario ingresa:
   - Nombre completo
   - CI/RIF
   - Teléfono
   - Correo electrónico
   - Dirección
5. El sistema valida que CI/RIF no esté duplicado
6. El sistema valida formato de correo y teléfono
7. El sistema guarda el cliente
8. El sistema muestra mensaje de confirmación

**Flujos Alternativos**:
- **5a. CI/RIF duplicado**: El sistema muestra error y datos del cliente existente
- **6a. Formato inválido**: El sistema solicita corrección

**Postcondiciones**:
- Cliente registrado en el sistema
- Cliente disponible para pedidos y cotizaciones

---

### CU-005: Buscar Cliente (AJAX)

**Actor Principal**: Administrador, Supervisor

**Precondiciones**:
- Usuario está creando un pedido o cotización

**Flujo Principal**:
1. Usuario comienza a escribir nombre o CI/RIF del cliente
2. El sistema busca en tiempo real sin recargar página
3. El sistema muestra resultados mientras se escribe
4. Usuario selecciona cliente de la lista
5. El sistema auto-completa los datos del cliente en el formulario

**Flujos Alternativos**:
- **3a. No encuentra resultados**: Usuario puede crear nuevo cliente

**Postcondiciones**:
- Datos del cliente cargados en el formulario

---

### CU-006: Generar Reporte de Clientes PDF

**Actor Principal**: Administrador, Supervisor

**Precondiciones**:
- Existen clientes registrados

**Flujo Principal**:
1. Usuario accede al módulo de clientes
2. Hace clic en "Exportar PDF"
3. El sistema genera PDF con:
   - Logo de la empresa
   - Fecha y hora actual
   - Listado de todos los clientes activos
   - Total de clientes
4. El sistema descarga el PDF

**Postcondiciones**:
- PDF generado y descargado

---

## 3️⃣ MÓDULO DE PRODUCTOS

### CU-007: Registrar Producto

**Actor Principal**: Administrador

**Precondiciones**:
- Usuario administrador ha iniciado sesión

**Flujo Principal**:
1. Administrador accede al módulo de productos
2. Selecciona "Crear Nuevo Producto"
3. El sistema muestra formulario
4. Administrador ingresa:
   - Nombre del producto
   - Descripción
   - Precio unitario
   - Tallas disponibles (XS, S, M, L, XL, XXL, XXXL)
   - Colores disponibles
5. El sistema valida que precio sea mayor a 0
6. El sistema valida que nombre sea único
7. El sistema guarda el producto
8. El sistema muestra confirmación

**Flujos Alternativos**:
- **6a. Nombre duplicado**: Sistema muestra error
- **5a. Precio inválido**: Sistema solicita corrección

**Postcondiciones**:
- Producto disponible para cotizaciones y pedidos

---

## 4️⃣ MÓDULO DE INSUMOS

### CU-008: Registrar Insumo

**Actor Principal**: Administrador, Supervisor

**Precondiciones**:
- Existen proveedores registrados

**Flujo Principal**:
1. Usuario accede al módulo de insumos
2. Selecciona "Crear Nuevo Insumo"
3. El sistema muestra formulario
4. Usuario ingresa:
   - Nombre del insumo
   - Tipo (Tela, Hilo, Botón, Cierre, etc.)
   - Unidad de medida
   - Costo unitario
   - Stock actual
   - Stock mínimo
   - Proveedor
5. El sistema valida valores numéricos positivos
6. El sistema guarda el insumo
7. El sistema muestra confirmación

**Postcondiciones**:
- Insumo disponible para órdenes de producción
- Sistema comienza a monitorear stock

---

### CU-009: Registrar Movimiento de Inventario

**Actor Principal**: Supervisor

**Precondiciones**:
- Existen insumos registrados

**Flujo Principal**:
1. Supervisor accede a "Movimientos de Inventario"
2. Selecciona "Registrar Movimiento"
3. El sistema muestra formulario
4. Supervisor ingresa:
   - Insumo afectado
   - Tipo de movimiento (Entrada/Salida/Ajuste)
   - Cantidad
   - Motivo/Descripción
5. El sistema calcula nuevo stock actual
6. Si es Entrada: stock_actual = stock_actual + cantidad
7. Si es Salida: stock_actual = stock_actual - cantidad
8. El sistema actualiza el insumo
9. El sistema registra el movimiento con fecha y usuario
10. Si stock_actual ≤ stock_minimo: Sistema genera alerta

**Flujos Alternativos**:
- **7a. Salida excede stock**: Sistema muestra advertencia
- **10a. Stock bajo**: Sistema muestra alerta en dashboard

**Postcondiciones**:
- Stock actualizado
- Movimiento registrado con trazabilidad
- Alerta generada si corresponde

---

## 5️⃣ MÓDULO DE COTIZACIONES

### CU-010: Crear Cotización

**Actor Principal**: Supervisor

**Precondiciones**:
- Existen clientes y productos registrados

**Flujo Principal**:
1. Supervisor accede a "Cotizaciones"
2. Selecciona "Crear Nueva Cotización"
3. El sistema muestra formulario
4. Supervisor busca y selecciona cliente (AJAX)
5. Supervisor agrega productos:
   - Selecciona producto
   - Especifica cantidad
   - Define talla y color
   - Precio se carga automáticamente
6. El sistema calcula subtotal por producto
7. El sistema calcula total de la cotización
8. Supervisor puede agregar notas
9. El sistema guarda la cotización con estado "Pendiente"
10. El sistema muestra confirmación

**Postcondiciones**:
- Cotización creada
- Disponible para conversión a pedido

---

### CU-011: Convertir Cotización a Pedido

**Actor Principal**: Supervisor

**Precondiciones**:
- Cotización existe con estado "Aprobada"

**Flujo Principal**:
1. Supervisor accede a cotizaciones aprobadas
2. Selecciona una cotización
3. Hace clic en "Convertir a Pedido"
4. El sistema crea un pedido con:
   - Todos los datos del cliente
   - Todos los productos de la cotización
   - Estado "Pendiente"
   - Fecha de pedido actual
5. El sistema solicita información de pago
6. Supervisor ingresa datos de pago inicial
7. El sistema guarda el pedido
8. El sistema muestra confirmación

**Postcondiciones**:
- Pedido creado desde cotización
- Pedido disponible para producción

---

## 6️⃣ MÓDULO DE PEDIDOS

### CU-012: Crear Pedido

**Actor Principal**: Supervisor

**Precondiciones**:
- Existen clientes y productos registrados

**Flujo Principal**:
1. Supervisor accede a "Pedidos"
2. Selecciona "Crear Nuevo Pedido"
3. El sistema muestra formulario
4. Supervisor selecciona cliente (búsqueda AJAX)
5. Supervisor agrega productos con detalles:
   - Producto
   - Cantidad
   - Talla
   - Color
   - Descripción personalizada
   - Logo (si aplica)
6. El sistema calcula total
7. Supervisor ingresa:
   - Fecha de entrega estimada
   - Prioridad (Alta/Media/Baja)
   - Estado inicial
8. Supervisor registra pago/abono:
   - Método (Efectivo/Transferencia/Pago Móvil)
   - Monto
   - Referencias (si aplica)
   - Banco (si aplica)
9. El sistema guarda el pedido
10. El sistema genera PDF del pedido

**Flujos Alternativos**:
- **8a. Pago completo**: Estado cambia a "En Proceso"
- **8b. Abono parcial**: Estado queda "Pendiente"

**Postcondiciones**:
- Pedido registrado
- PDF generado
- Disponible para crear orden de producción

---

### CU-013: Registrar Pago de Pedido

**Actor Principal**: Supervisor

**Precondiciones**:
- Pedido existe con saldo pendiente

**Flujo Principal**:
1. Supervisor accede al pedido
2. Selecciona "Registrar Pago"
3. El sistema muestra saldo pendiente
4. Supervisor ingresa:
   - Monto del pago
   - Método de pago
   - Referencia (si aplica)
   - Banco (si aplica)
5. El sistema actualiza montos pagados
6. El sistema calcula nuevo saldo
7. Si saldo = 0: Sistema actualiza estado a "Pagado"
8. El sistema guarda el registro
9. El sistema actualiza el PDF

**Postcondiciones**:
- Pago registrado
- Estado del pedido actualizado si corresponde

---

### CU-014: Generar Reporte PDF de Pedido

**Actor Principal**: Supervisor, Administrador

**Precondiciones**:
- Pedido existe

**Flujo Principal**:
1. Usuario accede al detalle del pedido
2. Hace clic en "Generar PDF"
3. El sistema genera PDF con:
   - Datos de la empresa
   - Datos del cliente
   - Detalle de productos
   - Totales y pagos
   - Estado actual
4. El sistema descarga el PDF

**Postcondiciones**:
- PDF generado y descargado

---

## 7️⃣ MÓDULO DE ÓRDENES DE PRODUCCIÓN

### CU-015: Crear Orden de Producción

**Actor Principal**: Supervisor

**Precondiciones**:
- Existe un pedido aprobado
- Existen insumos suficientes

**Flujo Principal**:
1. Supervisor accede a "Órdenes de Producción"
2. Selecciona "Crear Nueva Orden"
3. El sistema muestra formulario
4. Supervisor selecciona:
   - Pedido asociado
   - Producto a fabricar
   - Cantidad solicitada
5. Supervisor ingresa:
   - Fecha de inicio
   - Fecha de fin estimada
   - Costo estimado
   - Logo personalizado (si aplica)
   - Notas
6. El sistema crea la orden con estado "Pendiente"
7. El sistema registra quién la creó (created_by)
8. El sistema muestra confirmación

**Postcondiciones**:
- Orden de producción creada
- Disponible para asignar insumos
- Disponible para asignar a operarios

---

### CU-016: Asignar Insumos a Orden de Producción

**Actor Principal**: Supervisor

**Precondiciones**:
- Orden de producción existe
- Existen insumos disponibles

**Flujo Principal**:
1. Supervisor accede a la orden de producción
2. Selecciona "Asignar Insumos"
3. El sistema muestra lista de insumos disponibles
4. Supervisor selecciona insumo
5. Supervisor ingresa cantidad estimada necesaria
6. El sistema verifica stock disponible
7. El sistema registra la asignación
8. Supervisor puede agregar más insumos
9. El sistema guarda todas las asignaciones

**Flujos Alternativos**:
- **6a. Stock insuficiente**: Sistema muestra advertencia
- **6b. Stock crítico**: Sistema sugiere reabastecimiento

**Postcondiciones**:
- Insumos asignados a la orden
- Stock se descuenta al iniciar producción

---

### CU-017: Iniciar Orden de Producción

**Actor Principal**: Supervisor

**Precondiciones**:
- Orden existe con estado "Pendiente"
- Insumos están asignados

**Flujo Principal**:
1. Supervisor accede a la orden
2. Verifica que todo esté listo
3. Hace clic en "Iniciar Producción"
4. El sistema confirma acción
5. El sistema cambia estado a "En Proceso"
6. El sistema descuenta insumos del inventario
7. El sistema registra fecha de inicio real
8. El sistema genera movimientos de salida de insumos
9. El sistema muestra confirmación

**Postcondiciones**:
- Orden en proceso
- Insumos descontados del inventario
- Disponible para registro de producción diaria

---

## 8️⃣ MÓDULO DE PRODUCCIÓN DIARIA

### CU-018: Registrar Producción Diaria

**Actor Principal**: Operario

**Precondiciones**:
- Operario ha iniciado sesión
- Existe orden en proceso

**Flujo Principal**:
1. Operario accede a "Producción Diaria"
2. Selecciona "Registrar Producción"
3. El sistema muestra órdenes asignadas
4. Operario selecciona la orden en la que trabajó
5. Operario ingresa:
   - Fecha de producción (hoy por defecto)
   - Cantidad producida
   - Observaciones
6. El sistema suma cantidad a total producido de la orden
7. El sistema guarda el registro
8. El sistema verifica si se completó la cantidad solicitada
9. Si cantidad_producida >= cantidad_solicitada:
   - Sistema sugiere finalizar la orden

**Flujos Alternativos**:
- **9a. Orden completada**: Supervisor puede finalizar la orden

**Postcondiciones**:
- Producción registrada
- Progreso de la orden actualizado
- Registro con fecha y operario guardado

---

### CU-019: Consultar Producción por Operario

**Actor Principal**: Supervisor, Administrador

**Precondiciones**:
- Existen registros de producción

**Flujo Principal**:
1. Usuario accede a "Reportes de Producción"
2. Selecciona "Por Operario"
3. El sistema muestra lista de operarios
4. Usuario selecciona un operario
5. Usuario define rango de fechas
6. El sistema muestra:
   - Total producido
   - Órdenes en las que trabajó
   - Promedio diario
   - Gráficos de rendimiento
7. Usuario puede exportar a PDF

**Postcondiciones**:
- Reporte visualizado
- PDF generado si se solicitó

---

## 9️⃣ MÓDULO DE REPORTES

### CU-020: Generar Reporte de Producción

**Actor Principal**: Supervisor, Administrador

**Precondiciones**:
- Existen órdenes de producción finalizadas

**Flujo Principal**:
1. Usuario accede a "Reportes"
2. Selecciona "Reporte de Producción"
3. Usuario define:
   - Rango de fechas
   - Productos (todos o específicos)
4. El sistema recopila datos
5. El sistema genera reporte con:
   - Total producido por producto
   - Órdenes completadas
   - Tendencias
   - Gráficos
6. El sistema muestra el reporte
7. Usuario puede exportar a PDF

**Postcondiciones**:
- Reporte generado y visualizado

---

### CU-021: Generar Reporte de Eficiencia

**Actor Principal**: Supervisor, Administrador

**Precondiciones**:
- Existen registros de producción diaria

**Flujo Principal**:
1. Usuario accede a "Reportes"
2. Selecciona "Reporte de Eficiencia"
3. Usuario define rango de fechas
4. El sistema calcula:
   - Eficiencia por operario
   - Cumplimiento de órdenes en tiempo
   - Tiempos promedio de producción
   - Comparativas
5. El sistema genera gráficos
6. El sistema muestra el reporte
7. Usuario puede exportar a PDF

**Postcondiciones**:
- Reporte de eficiencia generado

---

### CU-022: Generar Reporte de Inventario

**Actor Principal**: Supervisor, Administrador

**Precondiciones**:
- Existen insumos registrados

**Flujo Principal**:
1. Usuario accede a "Reportes"
2. Selecciona "Reporte de Inventario"
3. El sistema recopila:
   - Stock actual de cada insumo
   - Insumos con stock bajo
   - Movimientos recientes
   - Proyecciones de reabastecimiento
4. El sistema genera reporte con:
   - Tabla de insumos
   - Estado de cada uno
   - Alertas
   - Recomendaciones
5. El sistema muestra el reporte
6. Usuario puede exportar a PDF

**Postcondiciones**:
- Reporte de inventario generado
- Alertas identificadas

---

## 🔟 MÓDULO DE DASHBOARD

### CU-023: Visualizar Dashboard

**Actor Principal**: Todos los usuarios autenticados

**Precondiciones**:
- Usuario ha iniciado sesión

**Flujo Principal**:
1. Usuario accede al sistema
2. El sistema carga el dashboard
3. El sistema muestra según rol:
   - **Administrador**: Todos los KPIs, todos los gráficos
   - **Supervisor**: KPIs de producción, gráficos de órdenes, KPIs de inventario, alertas de stock
   - **Operario**: Sus órdenes asignadas, su producción
4. El sistema actualiza datos en tiempo real
5. Usuario puede interactuar con gráficos

**Postcondiciones**:
- Dashboard visualizado con datos actualizados

---

### CU-024: Ver Alertas de Stock Bajo

**Actor Principal**: Supervisor, Administrador

**Precondiciones**:
- Existen insumos con stock_actual ≤ stock_minimo

**Flujo Principal**:
1. Usuario accede al dashboard
2. El sistema muestra badge de alertas
3. Usuario hace clic en alertas
4. El sistema muestra lista de insumos críticos:
   - Nombre del insumo
   - Stock actual
   - Stock mínimo
   - Cantidad faltante
   - Proveedor
5. Usuario puede ir directamente a gestionar el insumo

**Postcondiciones**:
- Alertas visualizadas
- Usuario informado de situación crítica

---

## 📊 RESUMEN DE CASOS DE USO POR MÓDULO

| Módulo | Cantidad de CU | Actores Principales |
|--------|----------------|---------------------|
| **Usuarios** | 3 | Administrador |
| **Clientes** | 3 | Administrador, Supervisor |
| **Productos** | 1 | Administrador |
| **Proveedores** | 1 | Administrador |
| **Insumos** | 2 | Administrador, Supervisor |
| **Cotizaciones** | 2 | Supervisor |
| **Pedidos** | 3 | Supervisor |
| **Órdenes Producción** | 3 | Supervisor |
| **Producción Diaria** | 2 | Operario, Supervisor |
| **Reportes** | 3 | Administrador, Supervisor |
| **Dashboard** | 2 | Todos |
| **TOTAL** | **25** | **3 tipos de actores** |

---

## 🎯 MATRIZ DE ACTORES vs CASOS DE USO

| Caso de Uso | Admin | Supervisor | Operario |
|-------------|-------|------------|----------|
| CU-001 a CU-003: Gestión Usuarios | ✅ | ❌ | ❌ |
| CU-004 a CU-006: Gestión Clientes | ✅ | ✅ | ❌ |
| CU-007: Gestión Productos | ✅ | ❌ | ❌ |
| CU-008 a CU-009: Gestión Insumos | ✅ | ✅ | ❌ |
| CU-010 a CU-011: Cotizaciones | ✅ | ✅ | ❌ |
| CU-012 a CU-014: Pedidos | ✅ | ✅ | ❌ |
| CU-015 a CU-017: Órdenes Producción | ✅ | ✅ | ❌ |
| CU-018 a CU-019: Producción Diaria | ✅ | ✅ | ✅ |
| CU-020 a CU-022: Reportes | ✅ | ✅ | ❌ |
| CU-023 a CU-024: Dashboard | ✅ | ✅ | ✅ |

---

**Documento generado para:**  
Sistema de Gestión para Pedidos - Manufacturas R.J. Atlántico C.A  
Grupo Textil - Sección 536  
PNF Informática - UPTP "JJ Montilla"  
**Fecha:** Noviembre 2025
