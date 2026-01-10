> Sistema web para la gestión textil desarrollado como proyecto académico con Laravel.

# 🧵 Sistema de Gestión para Pedidos en Manufacturas R.J. Atlántico C.A

Este sistema fue desarrollado por el **Grupo Textil de la Sección 536 del PNF en Informática de la UPTP "JJ Montilla"** como parte del **Proyecto Socio-Tecnológico II**.

## 📘 Descripción General

El sistema de gestión para la empresa **Textil Manufacturas R.J. Atlántico C.A** es una solución empresarial integral desarrollada con **Laravel 10** y **MySQL**, diseñada para automatizar y optimizar los procesos claves de la industria textil.

Además, el sistema sigue la **arquitectura MVC (Modelo - Vista - Controlador)**, lo que facilita el mantenimiento, escalabilidad y separación de responsabilidades del código.

## ⚙️ Funcionalidades Principales

- 📦 Gestión de **existencia de insumos**
- 🧾 Administración de **proveedores**
- 🏭 Registro de **órdenes de producción**
- 📈 Seguimiento de **producción diaria**
- ✅ Control de **calidad textil**
- 📊 Dashboard interactivo con:
  - Niveles de stock
  - Órdenes en proceso
  - Producción total
  - Alertas de existencia
- 📄 Reportes sobre:
  - Eficiencia de producción
  - Consumo de insumos
  - Rendimiento de operarios

---

## 🧰 Tecnologías Utilizadas

### 🔙 Backend
- **Laravel 10** – Framework PHP moderno
- **PHP** – Lenguaje de programación
- **Composer** – Gestor de dependencias para PHP
- **Git Bash** – Terminal utilizada para levantar el servidor con `php artisan serve`

### 🗃️ Base de Datos
- **MySQL** – Sistema de gestión de base de datos relacional

### 🎨 Frontend
- **Velzon Admin Template** – Template premium para dashboards (v4.3.0, de Themesbrand)
- **Blade** – Motor de plantillas de Laravel
- **Tailwind CSS** – Framework CSS moderno y utilitario
- **Vite** – Empaquetador de recursos modernos (JS/CSS)
- **JavaScript** – Lenguaje base del frontend
- **npm / Node.js** – Gestor de paquetes y entorno de ejecución para JS

### 📊 Visualización de Datos
- **Velzon incluye integraciones con ApexCharts, Chart.js, etc.**

### ✅ Pruebas
- **PHPUnit** – Framework de testing para PHP


## 🚀 Instalación y Puesta en Marcha

Para instalar o configurar el sistema en un servidor local, los requisitos básicos a tener son los siguientes:

Xampp, es un paquete de software gratuito y de código abierto que facilita la creación de un entorno de desarrollo local.
Editor de código, hay montón de programas. Puede ser Visual Studio Code, Sublime Text, Atom, etc.
Los pasos a seguir para instalar el sistema de manera correcta:

Descargar e instalar XAMPP.
Abrir el programa XAMPP.
Iniciar los servicios Apache y MySQL.
Extraer o descomprimir el sistema o proyecto.
Copiar la carpeta del sistema extraído y pegar en la carpeta htdocs de XAMPP.
Abrir phpMyAdmin desde XAMPP dando clic en Admin de Mysql.
Crear la base de datos con el nombre atlantico_db
Importar el archivo SQL, la cual se encuentra dentro de la carpeta database del sistema con el nombre atlantico_db.sql
Para abrir el sistema, en la barra de direcciones de Google escribe lo siguiente http://127.0.0.1:8000/


## 🧪 Estructura del Proyecto

El sistema está desarrollado bajo el **paradigma MVC**:

- **Modelo** – Estructura y consultas de base de datos
Comunidad Beneficiada: Manufacturas R.J. Atlántico

PNF Informática - UPTP "JJ Montilla"

Fecha de Socialización: Junio de 2025, en la ciudad de 
Acarigua, Edo Portuguesa, Venezuela. Lapso Académico: 2025-II


## 📄 Licencia

El desarrollo de este PST se alinea con el Plan de Desarrollo Económico de la Nación, específicamente con el Motor N.º 13: Telecomunicaciones e Informática, contribuyendo al logro de su propósito de desarrollar aplicaciones, programas, software y brindar asesorías tecnológicas.

Este proyecto está licenciado bajo la Creative Commons Atribución – No Comercial – Compartir Igual 4.0 Internacional.
Consulta los términos en https://creativecommons.org/licenses/by-nc-sa/4.0/deed.es