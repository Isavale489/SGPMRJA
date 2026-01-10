# **Sistema de Control de Producción para Fábrica de Ropa**

## **Objetivo General**
Gestionar la producción de prendas de vestir (polos, poleras, etc.), desde la recepción de materia prima hasta la producción final, asegurando eficiencia en costos y control de stock.

---

## **Fases de Desarrollo**

### **Fase 1: Módulo Básico (MVP)**
**Objetivo:** Tener un sistema funcional que registre la producción, insumos y órdenes de fabricación.

#### **Requerimientos**
1. **Gestión de Productos**  
   - Registrar productos (polo, polera, etc.).  
   - Definir características (modelo, color, talla, material, etc.).  
   - Listar, editar y eliminar productos.  

2. **Gestión de Insumos**  
   - Registrar insumos (telas, hilos, botones, cierres, etc.).  
   - Controlar stock de insumos.  
   - Listar, editar y eliminar insumos.  

3. **Órdenes de Producción**  
   - Crear una orden de producción con detalles de prenda, cantidad y fecha.  
   - Asignar insumos a la orden de producción.  
   - Listar órdenes en proceso y terminadas.  

4. **Registro de Producción**  
   - Registrar la cantidad de productos terminados por día.
   - Asignar operarios responsables.  
   - Calcular insumos consumidos.  

5. **Reportes Básicos**  
   - Producción diaria.  
   - Insumos consumidos.  
   - Costos estimados por producción.  

---

### **Fase 2: Control de Costos y Eficiencia**
**Objetivo:** Mejorar la toma de decisiones con información detallada sobre costos y eficiencia.

#### **Requerimientos**
6. **Cálculo de Costos**  
   - Definir el costo por unidad de insumo.  
   - Calcular el costo total por prenda.  
   - Mostrar costos totales por orden de producción.  

7. **Gestión de Proveedores**  
   - Registrar proveedores de telas y materiales.  
   - Controlar compras y costos de insumos.  
   - Relacionar insumos con proveedores.  

8. **Alertas de Stock Bajo**  
   - Notificar cuando un insumo tenga stock bajo.  
   - Generar sugerencias de compra.  

9. **Registro de Defectos y Desperdicios**  
   - Registrar productos defectuosos.  
   - Controlar insumos desperdiciados.  
   - Calcular porcentaje de defectos en la producción.

10. **Control de Productividad por Operario**  
   - Registrar prendas producidas por trabajador.  
   - Medir eficiencia por operario.
   - Generar reportes de productividad.

---

### **Fase 3: Automatización y Expansión**
**Objetivo:** Optimizar la producción mediante automatización y análisis avanzado.

#### **Requerimientos**
11. **Órdenes de Producción Automáticas**  
   - Generar órdenes en base a demanda y stock disponible.  
   - Predecir necesidades de insumos.  

12. **Gestión de Turnos y Horarios**  
   - Control de horarios de operarios.  
   - Registro de asistencia y horas trabajadas.  

13. **Integración con Ventas y Pedidos**  
   - Relacionar producción con pedidos de clientes.  
   - Control de despachos y entregas.  

14. **Trazabilidad de Productos**  
   - Seguimiento desde la materia prima hasta el producto final.  
   - Registro de lotes de producción.  

---

## **Esquema de Base de Datos**

### **Tablas Principales**

#### **1. `productos`** (Para registrar polos, poleras, etc.)
```sql
CREATE TABLE productos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100),
    descripcion TEXT,
    modelo VARCHAR(50),
    color VARCHAR(50),
    talla VARCHAR(10),
    material VARCHAR(50),
    precio DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### **2. `insumos`** (Telas, hilos, botones, etc.)
```sql
CREATE TABLE insumos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100),
    tipo VARCHAR(50),
    unidad_medida VARCHAR(20),
    costo DECIMAL(10,2),
    stock_actual INT,
    stock_minimo INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### **3. `ordenes_produccion`** (Ordenes de producción de polos/poleras)
```sql
CREATE TABLE ordenes_produccion (
    id INT PRIMARY KEY AUTO_INCREMENT,
    producto_id INT,
    cantidad INT,
    fecha_inicio DATE,
    fecha_fin DATE NULL,
    estado ENUM('Pendiente', 'En Proceso', 'Finalizado') DEFAULT 'Pendiente',
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### **4. `detalle_orden_insumos`** (Insumos usados en cada orden)
```sql
CREATE TABLE detalle_orden_insumos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    orden_id INT,
    insumo_id INT,
    cantidad_utilizada DECIMAL(10,2),
    FOREIGN KEY (orden_id) REFERENCES ordenes_produccion(id) ON DELETE CASCADE,
    FOREIGN KEY (insumo_id) REFERENCES insumos(id) ON DELETE CASCADE
);
```

#### **5. `produccion_diaria`** (Registro de producción por operario)
```sql
CREATE TABLE produccion_diaria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    orden_id INT,
    operario_id INT,
    cantidad_producida INT,
    fecha DATE,
    FOREIGN KEY (orden_id) REFERENCES ordenes_produccion(id) ON DELETE CASCADE,
    FOREIGN KEY (operario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
```

---

## **Próximos Pasos**
- Diseñar las pantallas del sistema (UI/UX).
- Definir la arquitectura en Laravel.
- Construir el MVP con los módulos iniciales.

---

**🚀 Listo para desarrollar este sistema!**

