# 🧵 Sistema de Gestión para Pedidos en Manufacturas R.J. Atlántico C.A.

> **Sistema web integral para la gestión textil, desarrollado con Laravel 10.**

Este proyecto es una solución tecnológica desarrollada por el **Grupo Textil de la Sección 636 del PNF en Informática de la UPTP "JJ Montilla"**, como parte del Proyecto Socio-Tecnológico III. Su objetivo es automatizar y optimizar los procesos operativos, administrativos y de producción de la empresa **Manufacturas R.J. Atlántico C.A.**

---

## 📘 Descripción General

El sistema permite la administración eficiente de todo el ciclo de vida de la producción textil, desde la gestión de clientes y cotizaciones hasta la producción, las compras y el control de existencias de insumos. Implementando una arquitectura **MVC (Modelo-Vista-Controlador)** con una capa de servicios, garantiza un código organizado, escalable y mantenible.

### 🌟 Características Principales

#### 🗂️ Gestión General (Maestros)
*   **Clientes y Proveedores**: Registro con historial; una misma persona es reutilizable como cliente, empleado o proveedor, y el tipo (Natural / Jurídico / Gubernamental) se deriva del documento.
*   **Productos**: Catálogo por **Tipo de Producto** con variantes dinámicas (tela + atributos con valores) que se eligen al cotizar, generando un SKU determinístico.
*   **Insumos**: Catálogo de materiales (telas, hilos, etc.) con tipos gestionables y control de existencias (Mínimo / Actual / Máximo).
*   **Recursos Humanos**: Empleados con sus catálogos de **Departamentos** y **Cargos**.
*   **Catálogos**: Colores, Atributos y Valores, y Tipos de Insumo, todos administrables.

#### ⚙️ Gestión Operativa (Transacciones)
*   **Cotizaciones**: Asistente (wizard) de 3 pasos, con servicio de bordado y precios en USD con su equivalente en bolívares (tasa BCV). Exportables a PDF.
*   **Pedidos**: Asistente de 4 pasos; un pedido nace de una cotización aprobada, con pago multi-método y abono mínimo configurable.
*   **Órdenes de Producción**: Asistente que escala de 1 a N órdenes; una orden por línea, con asignación a varios empleados y división de cantidades.
*   **Compras**: Registro de compras a proveedores con ciclo *borrador → recibida → anular → clonar*, en bolívares e IVA por línea; al recibirse genera la entrada de existencias.
*   **Movimiento de Insumos**: Entradas y salidas de existencias con panel de existencias y alertas automáticas de stock bajo.
*   **Control de Calidad** y **Garantías**: *Módulos planificados para una próxima etapa.*

#### 📊 Consultas y Reportes
*   **Dashboard Interactivo**: KPIs operativos en tiempo real y tendencia mensual de producción.
*   **Reportes PDF**: Informes de producción, eficiencia, insumos y empleados, con filtros previos a la exportación.

#### 🔐 Configuración y Seguridad
*   **Panel de Configuración**: Parámetros del sistema editables sin tocar código (impuestos/IVA, abono mínimo, vigencia de cotización, días de entrega).
*   **Roles y Permisos**: Control de acceso (RBAC) con autorización en tiempo de ejecución sobre rutas e interfaz.
*   **Recuperación de cuenta**: Multi-método (correo electrónico, preguntas de seguridad y reset por administrador) con bloqueo escalado.

---

## 🛠️ Tecnologías Utilizadas

El sistema está construido sobre un stack moderno y robusto:

### Backend
*   **Laravel 10**: Framework PHP principal.
*   **PHP 8.1+**: Lenguaje de servidor.
*   **MySQL**: Base de datos relacional.
*   **Composer**: Gestión de dependencias PHP.

### Frontend
*   **Blade**: Motor de plantillas.
*   **Velzon Admin Template**: Interfaz de usuario profesional (basada en Bootstrap/Tailwind).
*   **Tailwind CSS**: Estilos modernos y responsivos.
*   **Vite**: Empaquetador de assets de alto rendimiento.
*   **JavaScript**: Interactividad y gráficos (ApexCharts, Chart.js).

---

## 👥 Roles y Permisos

El sistema cuenta con un control de acceso basado en roles (RBAC) con **permisos configurables**: los roles son dinámicos y cada uno define qué módulos y acciones puede usar. Roles base incluidos:

1.  **Administrador**: Control total del sistema, gestión de usuarios y configuraciones globales.
2.  **Supervisor**: Gestión operativa (existencias, producción y reportes) según los permisos asignados.

> Se pueden crear roles adicionales y ajustar sus permisos desde el módulo de Seguridad.

---

## 🚀 Instalación y Puesta en Marcha

Sigue estos pasos para desplegar el proyecto en un entorno local:

### Requisitos Previos
*   **XAMPP** (o cualquier servidor web con Apache y MySQL).
*   **Composer** instalado.
*   **Node.js** y **NPM** instalados.
*   **Git** (opcional).

### Pasos de Instalación

1.  **Clonar/Descargar el repositorio**:
    Copia los archivos del proyecto en tu directorio de servidor web (ej. `C:\xampp\htdocs\sistema-atlantico`).

2.  **Instalar dependencias de PHP**:
    ```bash
    composer install
    ```

3.  **Instalar dependencias de JavaScript**:
    ```bash
    npm install
    ```

4.  **Configurar el entorno**:
    *   Duplica el archivo `.env.example` y renómbralo a `.env`.
    *   Configura las credenciales de base de datos en el archivo `.env`:
        ```env
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=sistema_atlantico
        DB_USERNAME=root
        DB_PASSWORD=
        ```

5.  **Generar clave de aplicación**:
    ```bash
    php artisan key:generate
    ```

6.  **Base de Datos**:
    *   Crea una base de datos vacía llamada `sistema_atlantico` en tu gestor MySQL (phpMyAdmin, etc.).
    *   Ejecuta las migraciones y seeders:
        ```bash
        php artisan migrate --seed
        ```
    *   *Alternativa (recomendada)*: Importa el dump incluido en `database/sistema_atlantico.sql` para una base de datos pre-cargada.

7.  **Ejecutar el proyecto**:
    En una terminal:
    ```bash
    php artisan serve
    ```
    En otra terminal (para los estilos y scripts):
    ```bash
    npm run dev
    ```

8.  **Acceder**:
    Abre tu navegador en `http://127.0.0.1:8000`.

---

## 👨‍💻 Equipo de Desarrollo

**PNF Informática - UPTP "JJ Montilla" (Sección 636)**

*   **Emmanuel Arroyo** - *Desarrollador*
*   **Santiago Mendoza** - *Desarrollador*
*   **Johiner Orellana** - *Analista*
*   **Luis Rodriguez** - *Analista*
*   **Vanessa Lopez** - *Desarrolladora*
*   **Isabella Colmenarez** - *Analista*
*   **Alejandro Adam** - *Analista*

**Asesor Académico**: Juan Esteller
**Comunidad**: Manufacturas R.J. Atlántico C.A. (Acarigua, Edo. Portuguesa)

---

## 📄 Licencia

Este proyecto se alinea con el Plan de Desarrollo Económico de la Nación (Motor N.º 13 de Telecomunicaciones e Informática).

Licenciado bajo **Creative Commons Atribución – No Comercial – Compartir Igual 4.0 Internacional**.
Consulta los términos en: [creativecommons.org](https://creativecommons.org/licenses/by-nc-sa/4.0/deed.es)

---

## 📚 Documentación del Proyecto

Para evitar duplicidad y versiones inconsistentes:

- La documentación **oficial y vigente** está en `docs/`.
- `Documentacion/` se conserva como **legado/histórico**.
- Punto de entrada recomendado: `docs/README.md`.