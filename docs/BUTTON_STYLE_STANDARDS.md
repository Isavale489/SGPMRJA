# Estándares de Estilos para Botones de Reportes

## Estándares Aplicados

### 🎨 Colores y Estilos Estandarizados

| Tipo de Botón | Clase CSS | Color | Icono | Ejemplo de Uso |
|---------------|-----------|-------|-------|----------------|
| **PDF/Exportar** | `btn btn-danger` | 🔴 Rojo | `ri-file-pdf-line` | Exportar reportes PDF |
| **Reportes Generales** | `btn btn-info` | 🔵 Azul | `ri-file-list-3-line` | Ver reportes generales |
| **Volver/Regresar** | `btn btn-secondary` | ⚫ Gris | `ri-arrow-go-back-line` | Navegación hacia atrás |
| **Alertas** | `btn btn-warning` | 🟡 Amarillo | `ri-alert-line` | Alertas y notificaciones |

### 📋 Módulos Estandarizados

#### ✅ Pedidos
```html
<a href="{{ route('pedidos.reporte.pdf') }}" target="_blank" class="btn btn-danger ms-2">
    <i class="ri-file-pdf-line align-bottom me-1"></i> Exportar PDF
</a>
```

#### ✅ Inventario - Movimientos
```html
<a href="{{ route('existencia.alertas') }}" class="btn btn-warning ms-2">
    <i class="ri-alert-line align-bottom me-1"></i> Alertas de Stock
</a>
<a href="{{ route('existencia.reporte') }}" class="btn btn-danger ms-2">
    <i class="ri-file-list-3-line align-bottom me-1"></i> Reporte de Existencia
</a>
```

#### ✅ Inventario - Historial
```html
<a href="{{ route('existencia.reporte') }}" class="btn btn-secondary ms-2">
    <i class="ri-arrow-go-back-line align-bottom me-1"></i> Volver
</a>
```

### 🔧 Reglas de Implementación

1. **Consistencia**: Todos los botones del mismo tipo deben usar la misma clase CSS
2. **Iconos**: Usar iconos de Remix Icon (ri-*) consistentes
3. **Espaciado**: Usar `ms-2` para separación entre botones
4. **Alineación**: Usar `align-bottom me-1` en iconos para alineación correcta

### 📝 Beneficios Obtenidos

- ✅ **Consistencia Visual**: Todos los botones de reportes tienen colores uniformes
- ✅ **Identificación Rápida**: Los usuarios pueden identificar fácilmente el tipo de acción
- ✅ **Mantenibilidad**: Estándar claro para futuros desarrollos
- ✅ **Profesionalismo**: Interfaz más pulida y coherente

### 🎯 Estado Actual

**Módulos Actualizados:**
- ✅ Pedidos
- ✅ Inventario/Movimientos  
- ✅ Inventario/Historial

**Próximos módulos a revisar:**
- ⏳ Productos
- ⏳ Clientes
- ⏳ Proveedores
- ⏳ Insumos
- ⏳ Órdenes
- ⏳ Producción
- ⏳ Usuarios
- ⏳ Reportes Generales

### 📖 Guía de Aplicación

Para aplicar estos estándares en nuevos módulos o actualizar existentes:

1. **Identificar el tipo de botón** según su función
2. **Aplicar la clase CSS correspondiente** del estándar
3. **Usar el icono apropiado** de la tabla de referencia
4. **Mantener el espaciado consistente** con `ms-2`
5. **Verificar la funcionalidad** después del cambio

Este estándar asegura una experiencia de usuario consistente y profesional en todo el sistema.