# Estándares de Botones para el Sistema Atlántico

## Componente de Botones de Reportes

Se ha creado un componente estandarizado para mantener consistencia en todos los botones de reportes del sistema.

### Ubicación del Componente
```
resources/views/components/report-buttons.blade.php
```

### Tipos de Botones Disponibles

#### 1. Botón PDF (Rojo)
```blade
<x-report-buttons 
    type="pdf" 
    :route="route('modulo.reporte.pdf')" 
    text="Reporte PDF" />
```
- **Clase CSS**: `btn btn-danger`
- **Icono**: `ri-file-pdf-line`
- **Color**: Rojo
- **Uso**: Para exportar reportes en formato PDF

#### 2. Botón Excel (Verde)
```blade
<x-report-buttons 
    type="excel" 
    :route="route('modulo.reporte.excel')" 
    text="Exportar Excel" />
```
- **Clase CSS**: `btn btn-success`
- **Icono**: `ri-file-excel-line`
- **Color**: Verde
- **Uso**: Para exportar reportes en formato Excel

#### 3. Botón Reporte General (Azul)
```blade
<x-report-buttons 
    type="general" 
    :route="route('modulo.reporte')" 
    text="Ver Reporte"
    target="_self" />
```
- **Clase CSS**: `btn btn-info`
- **Icono**: `ri-file-list-3-line`
- **Color**: Azul
- **Uso**: Para acceder a reportes generales o páginas de reportes

#### 4. Botón Volver (Gris)
```blade
<x-report-buttons 
    type="back" 
    :route="route('modulo.index')" 
    target="_self" />
```
- **Clase CSS**: `btn btn-secondary`
- **Icono**: `ri-arrow-go-back-line`
- **Color**: Gris
- **Uso**: Para regresar a la página anterior

#### 5. Botón Imprimir (Amarillo)
```blade
<x-report-buttons 
    type="print" 
    :route="route('modulo.print')" 
    text="Imprimir Reporte" />
```
- **Clase CSS**: `btn btn-warning`
- **Icono**: `ri-printer-line`
- **Color**: Amarillo
- **Uso**: Para imprimir reportes directamente

### Parámetros del Componente

| Parámetro | Tipo | Descripción | Valor por Defecto |
|-----------|------|-------------|-------------------|
| `type` | string | Tipo de botón (pdf, excel, general, back, print) | 'pdf' |
| `route` | string | Ruta del enlace | '#' |
| `text` | string | Texto personalizado del botón | Según el tipo |
| `icon` | string | Icono personalizado | Según el tipo |
| `target` | string | Target del enlace (_blank, _self) | '_blank' |
| `class` | string | Clases CSS personalizadas | Según el tipo |

### Estándares de Otros Botones

#### Botones de Acción Principal
- **Agregar/Crear**: `btn btn-success` con icono `ri-add-line`
- **Editar**: `btn btn-purple` o `btn btn-warning` con icono `ri-pencil-line`
- **Eliminar**: `btn btn-danger` con icono `ri-delete-bin-line`
- **Ver/Detalles**: `btn btn-info` con icono `ri-eye-line`

#### Botones de Modal
- **Guardar/Agregar**: `btn btn-success`
- **Actualizar**: `btn btn-success`
- **Cancelar/Cerrar**: `btn btn-light`

#### Botones de Alerta
- **Alertas de Stock**: `btn btn-warning` con icono `ri-alert-line`
- **Notificaciones**: `btn btn-info` con icono `ri-notification-line`

### Implementación en Módulos

#### Módulos Actualizados:
- ✅ Pedidos
- ✅ Inventario/Movimientos
- ✅ Inventario/Historial

#### Módulos Pendientes:
- ⏳ Clientes
- ⏳ Productos
- ⏳ Proveedores
- ⏳ Insumos
- ⏳ Órdenes
- ⏳ Producción
- ⏳ Usuarios
- ⏳ Reportes Generales

### Ejemplo de Uso Completo

```blade
<div class="flex-shrink-0">
    <!-- Botón principal de acción -->
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal">
        <i class="ri-add-line align-bottom me-1"></i> Agregar Registro
    </button>
    
    <!-- Botones de reportes estandarizados -->
    <x-report-buttons 
        type="pdf" 
        :route="route('modulo.reporte.pdf')" 
        text="Reporte PDF" />
        
    <x-report-buttons 
        type="excel" 
        :route="route('modulo.reporte.excel')" 
        text="Exportar Excel" />
        
    <x-report-buttons 
        type="general" 
        :route="route('modulo.reporte')" 
        text="Ver Reportes"
        target="_self" />
</div>
```

### Beneficios del Estándar

1. **Consistencia Visual**: Todos los botones de reportes tienen el mismo aspecto
2. **Mantenibilidad**: Cambios globales desde un solo componente
3. **Facilidad de Uso**: Implementación simple y rápida
4. **Accesibilidad**: Iconos y colores consistentes mejoran la UX
5. **Escalabilidad**: Fácil agregar nuevos tipos de botones

### Notas de Migración

Al actualizar módulos existentes:
1. Reemplazar botones de reportes individuales con el componente
2. Verificar que las rutas funcionen correctamente
3. Ajustar el atributo `target` según sea necesario
4. Mantener la funcionalidad existente intacta