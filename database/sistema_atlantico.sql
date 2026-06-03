-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3308
-- Tiempo de generación: 03-06-2026 a las 03:37:57
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_atlantico`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atributo`
--

CREATE TABLE `atributo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `codigo` varchar(8) NOT NULL,
  `descripcion` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `atributo`
--

INSERT INTO `atributo` (`id`, `nombre`, `codigo`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'Manga', 'MNG', NULL, '2026-05-07 17:15:29', '2026-05-07 17:15:29'),
(2, 'Cuello', 'CLL', NULL, '2026-05-07 17:15:29', '2026-05-07 17:15:29'),
(3, 'Corte', 'CRT', NULL, '2026-05-07 17:15:29', '2026-05-07 17:15:29'),
(4, 'Cierre', 'CRR', NULL, '2026-05-07 17:15:29', '2026-05-07 17:15:29'),
(5, 'Capucha', 'CPC', NULL, '2026-05-07 17:15:29', '2026-05-07 17:15:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atributo_valor`
--

CREATE TABLE `atributo_valor` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `atributo_id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `codigo` varchar(8) NOT NULL,
  `orden` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `atributo_valor`
--

INSERT INTO `atributo_valor` (`id`, `atributo_id`, `nombre`, `codigo`, `orden`, `created_at`, `updated_at`) VALUES
(1, 1, 'Larga', 'L', 1, '2026-05-07 17:15:29', '2026-05-07 17:15:29'),
(2, 1, 'Corta', 'C', 2, '2026-05-07 17:15:29', '2026-05-07 17:15:29'),
(3, 2, 'Clásico', 'CLA', 1, '2026-05-07 17:15:29', '2026-05-07 17:15:29'),
(4, 2, 'Mao', 'MAO', 2, '2026-05-07 17:15:29', '2026-05-07 17:15:29'),
(5, 2, 'Con Tapa Botones', 'CTB', 3, '2026-05-07 17:15:29', '2026-05-07 17:15:29'),
(6, 2, 'Redondo', 'R', 4, '2026-05-07 17:15:29', '2026-05-07 17:15:29'),
(7, 2, 'V', 'V', 5, '2026-05-07 17:15:29', '2026-05-07 17:15:29'),
(8, 3, 'Rígido', 'RIG', 1, '2026-05-07 17:15:29', '2026-05-07 17:15:29'),
(9, 3, 'Stretch', 'STR', 1, '2026-05-07 17:15:29', '2026-05-27 02:50:38'),
(10, 4, 'Cremallera', 'CRE', 1, '2026-05-07 17:15:29', '2026-05-07 17:15:29'),
(11, 4, 'Botones', 'BOT', 2, '2026-05-07 17:15:29', '2026-05-07 17:15:29'),
(12, 4, 'Cerrado', 'CER', 3, '2026-05-07 17:15:29', '2026-05-07 17:15:29'),
(13, 5, 'Con capucha', 'CCH', 1, '2026-05-07 17:15:29', '2026-05-07 17:15:29'),
(14, 5, 'Sin capucha', 'SCH', 2, '2026-05-07 17:15:29', '2026-05-07 17:15:29'),
(15, 3, 'Clasico', 'CL', 3, '2026-05-27 02:50:25', '2026-05-27 02:50:25'),
(16, 3, 'Columbia I', 'CLM1', 4, '2026-05-27 02:51:17', '2026-05-27 02:51:17'),
(17, 3, 'Columbia II', 'CLM2', 5, '2026-05-27 02:51:36', '2026-05-27 02:51:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `banco`
--

CREATE TABLE `banco` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `banco`
--

INSERT INTO `banco` (`id`, `nombre`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Banco de Venezuela', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(2, 'Banco Mercantil', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(3, 'Banco Provincial', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(4, 'Banesco', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(5, 'Bancaribe', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(6, 'BOD', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(7, 'Banco Caroní', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(8, 'Banco Plaza', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(9, 'BFC Banco Fondo Común', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(10, '100% Banco', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(11, 'DelSur', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(12, 'Banco del Tesoro', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(13, 'Bancrecer', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(14, 'Banco Activo', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(15, 'Bancamiga', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(16, 'Banplus', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(17, 'Banco Bicentenario', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(18, 'BNC Nacional de Crédito', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(19, 'Zelle', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(20, 'PayPal', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(21, 'Binance', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL),
(22, 'Efectivo', '2026-01-19 19:27:19', '2026-01-19 19:27:19', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bordado_ubicacion`
--

CREATE TABLE `bordado_ubicacion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(191) NOT NULL,
  `grupo` varchar(191) DEFAULT NULL,
  `precio_base` decimal(10,2) NOT NULL DEFAULT 0.00,
  `orden` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `bordado_ubicacion`
--

INSERT INTO `bordado_ubicacion` (`id`, `nombre`, `grupo`, `precio_base`, `orden`, `activo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Frontal Izquierdo', 'Frontal', 3.00, 10, 1, '2026-02-24 02:48:28', '2026-02-24 02:48:28', NULL),
(2, 'Frontal Derecho', 'Frontal', 3.00, 20, 1, '2026-02-24 02:48:28', '2026-02-24 02:48:28', NULL),
(3, 'Manga Izquierda', 'Mangas', 3.00, 30, 1, '2026-02-24 02:48:28', '2026-02-24 02:48:28', NULL),
(4, 'Manga Derecha', 'Mangas', 3.00, 40, 1, '2026-02-24 02:48:28', '2026-02-24 02:48:28', NULL),
(5, 'Espaldar', 'Espalda', 5.00, 50, 1, '2026-02-24 02:48:28', '2026-02-24 02:48:28', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargo`
--

CREATE TABLE `cargo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `departamento_id` bigint(20) UNSIGNED NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cargo`
--

INSERT INTO `cargo` (`id`, `nombre`, `departamento_id`, `activo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Supervisor de Producción', 1, 1, '2026-04-21 20:16:52', '2026-04-21 20:16:52', NULL),
(2, 'Supervisor', 2, 1, '2026-04-21 20:16:52', '2026-04-21 20:32:45', '2026-04-21 20:32:45'),
(3, 'Cortador', 1, 1, '2026-04-21 20:16:52', '2026-04-21 20:16:52', NULL),
(4, 'Limpieza', 1, 1, '2026-04-21 20:16:52', '2026-04-21 20:16:52', NULL),
(5, 'Supervisor 2', 1, 1, '2026-04-21 20:16:52', '2026-04-21 20:32:49', '2026-04-21 20:32:49'),
(6, 'Gerente', 2, 1, '2026-04-21 20:33:35', '2026-04-21 20:33:35', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `persona_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `tipo_cliente` enum('natural','juridico') NOT NULL DEFAULT 'natural',
  `estatus` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`id`, `persona_id`, `created_at`, `updated_at`, `deleted_at`, `tipo_cliente`, `estatus`) VALUES
(1, 3, '2025-12-04 19:37:44', '2026-01-16 21:57:40', '2026-01-16 21:57:40', 'natural', 1),
(2, 6, '2025-12-05 19:22:15', '2026-01-16 21:57:58', '2026-01-16 21:57:58', 'natural', 1),
(3, 7, '2025-12-08 19:23:05', '2026-01-16 21:57:47', '2026-01-16 21:57:47', 'natural', 1),
(4, 8, '2025-12-08 20:04:57', '2025-12-09 18:51:56', '2025-12-09 18:51:56', 'natural', 1),
(5, 9, '2025-12-08 20:19:32', '2025-12-09 18:16:57', '2025-12-09 18:16:57', 'natural', 1),
(6, 10, '2025-12-09 18:54:35', '2025-12-09 18:57:12', '2025-12-09 18:57:12', 'natural', 1),
(7, 11, '2025-12-10 18:09:48', '2026-01-16 21:57:54', '2026-01-16 21:57:54', 'natural', 1),
(8, 12, '2025-12-10 20:29:40', '2026-01-17 22:34:46', NULL, 'natural', 1),
(9, 15, '2026-01-17 16:51:52', '2026-01-17 16:51:52', NULL, 'natural', 1),
(10, 16, '2026-01-17 17:11:09', '2026-01-17 17:11:09', NULL, 'natural', 1),
(11, 17, '2026-01-17 22:05:23', '2026-01-17 22:10:55', '2026-01-17 22:10:55', 'natural', 1),
(12, 18, '2026-01-17 22:31:33', '2026-01-17 22:31:33', NULL, 'natural', 1),
(13, 19, '2026-01-18 03:49:00', '2026-01-18 03:54:29', '2026-01-18 03:54:29', 'natural', 1),
(14, 20, '2026-01-18 03:56:57', '2026-01-18 03:56:57', NULL, 'natural', 1),
(15, 29, '2026-01-19 00:25:34', '2026-01-19 00:25:34', NULL, 'natural', 1),
(16, 30, '2026-01-19 03:56:04', '2026-01-19 04:20:07', '2026-01-19 04:20:07', 'natural', 1),
(17, 31, '2026-01-19 04:01:50', '2026-01-19 04:20:11', '2026-01-19 04:20:11', 'natural', 1),
(18, 32, '2026-01-19 04:05:33', '2026-01-19 04:20:04', '2026-01-19 04:20:04', 'natural', 1),
(19, 33, '2026-01-19 04:17:44', '2026-01-19 04:20:00', '2026-01-19 04:20:00', 'natural', 1),
(20, 34, '2026-01-19 04:26:34', '2026-01-19 04:26:34', NULL, 'natural', 1),
(21, 35, '2026-01-19 16:49:16', '2026-01-19 16:49:16', NULL, 'natural', 1),
(22, 36, '2026-01-20 01:29:08', '2026-01-20 01:29:08', NULL, 'natural', 1),
(23, 37, '2026-01-20 21:23:34', '2026-01-20 21:23:34', NULL, 'natural', 1),
(24, 38, '2026-02-22 17:56:13', '2026-02-26 20:20:19', '2026-02-26 20:20:19', 'juridico', 1),
(25, 39, '2026-02-26 19:10:25', '2026-02-26 19:10:25', NULL, 'natural', 1),
(26, 40, '2026-02-26 19:10:25', '2026-02-26 19:10:25', NULL, 'natural', 1),
(27, 42, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL, 'juridico', 1),
(28, 43, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL, 'natural', 1),
(29, 44, '2026-02-26 19:11:43', '2026-02-26 20:27:30', '2026-02-26 20:27:30', 'juridico', 1),
(30, 45, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL, 'natural', 1),
(31, 46, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL, 'natural', 1),
(32, 47, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL, 'juridico', 1),
(33, 48, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL, 'natural', 1),
(34, 49, '2026-02-26 19:11:44', '2026-02-26 19:11:44', NULL, 'natural', 1),
(35, 50, '2026-02-26 19:46:04', '2026-02-26 19:46:04', NULL, 'natural', 1),
(36, 51, '2026-03-05 17:17:09', '2026-03-05 17:17:09', NULL, 'juridico', 1),
(37, 60, '2026-04-14 21:26:32', '2026-04-14 21:26:32', NULL, 'natural', 1),
(38, 13, '2026-05-27 04:11:40', '2026-05-27 04:11:40', NULL, 'natural', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `color`
--

CREATE TABLE `color` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(191) NOT NULL COMMENT 'Nombre comercial del color (Ej: Azul Marino)',
  `hex_referencial` varchar(7) NOT NULL COMMENT 'Color HEX referencial para el swatch (#1B3A5C)',
  `grupo` varchar(191) DEFAULT NULL COMMENT 'Agrupación visual: Básicos, Pasteles, Oscuros, etc.',
  `activo` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Permite desactivar colores sin borrarlos',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `color`
--

INSERT INTO `color` (`id`, `nombre`, `hex_referencial`, `grupo`, `activo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Blanco', '#FFFFFF', 'Básicos', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(2, 'Negro', '#1C1C1C', 'Básicos', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(3, 'Gris Claro', '#C0C0C0', 'Básicos', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(4, 'Gris Oscuro', '#5A5A5A', 'Básicos', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(5, 'Gris Jaspeado', '#9E9E9E', 'Básicos', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(6, 'Azul Marino', '#1B3A5C', 'Azules', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(7, 'Azul Royal', '#2E5DA8', 'Azules', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(8, 'Azul Cielo', '#87CEEB', 'Azules', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(9, 'Azul Turquesa', '#40B5AD', 'Azules', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(10, 'Azul Petróleo', '#1A5276', 'Azules', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(11, 'Rojo', '#C0392B', 'Rojos y Cálidos', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(12, 'Rojo Vino', '#722F37', 'Rojos y Cálidos', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(13, 'Naranja', '#E67E22', 'Rojos y Cálidos', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(14, 'Coral', '#E8735A', 'Rojos y Cálidos', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(15, 'Amarillo', '#F1C40F', 'Rojos y Cálidos', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(16, 'Verde Oscuro', '#1E5631', 'Verdes', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(17, 'Verde Oliva', '#6B8E23', 'Verdes', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(18, 'Verde Menta', '#98D8C8', 'Verdes', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(19, 'Verde Botella', '#0B5345', 'Verdes', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(20, 'Rosa Pastel', '#F5B7C1', 'Pasteles', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(21, 'Celeste', '#AED6F1', 'Pasteles', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(22, 'Lila', '#C39BD3', 'Pasteles', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(23, 'Melocotón', '#F5CBA7', 'Pasteles', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(24, 'Lavanda', '#D7BDE2', 'Pasteles', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(25, 'Beige', '#F5DEB3', 'Tierra y Neutros', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(26, 'Caqui', '#C3B091', 'Tierra y Neutros', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(27, 'Marrón', '#6E3B23', 'Tierra y Neutros', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(28, 'Café', '#4E342E', 'Tierra y Neutros', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(29, 'Crema', '#FFFDD0', 'Tierra y Neutros', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(30, 'Fucsia', '#C71585', 'Especiales', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(31, 'Morado', '#6C3483', 'Especiales', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL),
(32, 'Dorado', '#D4A017', 'Especiales', 1, '2026-02-23 18:14:33', '2026-02-23 18:14:33', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compra`
--

CREATE TABLE `compra` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `proveedor_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `numero_factura` varchar(30) DEFAULT NULL,
  `fecha_compra` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `tipo_pago` enum('contado','credito') NOT NULL DEFAULT 'contado',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `iva` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `observaciones` text DEFAULT NULL,
  `estado` enum('borrador','recibida','anulada') NOT NULL DEFAULT 'recibida',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compra_detalle`
--

CREATE TABLE `compra_detalle` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `compra_id` bigint(20) UNSIGNED NOT NULL,
  `insumo_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `costo_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizacion`
--

CREATE TABLE `cotizacion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cliente_id` bigint(20) UNSIGNED NOT NULL,
  `fecha_cotizacion` date NOT NULL,
  `fecha_validez` date DEFAULT NULL,
  `estado` enum('Pendiente','Aprobada','Cancelada','Convertida','Vencida') NOT NULL DEFAULT 'Pendiente',
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tasa_cambio_valor` decimal(10,4) DEFAULT NULL COMMENT 'Tasa BCV USD→VES vigente al momento de crear/actualizar la cotización',
  `notas` text DEFAULT NULL,
  `condiciones_terminos` text DEFAULT NULL COMMENT 'Cláusulas legales y condiciones de la cotización',
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `prioridad` enum('Normal','Alta','Urgente') NOT NULL DEFAULT 'Normal',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cotizacion`
--

INSERT INTO `cotizacion` (`id`, `cliente_id`, `fecha_cotizacion`, `fecha_validez`, `estado`, `total`, `tasa_cambio_valor`, `notas`, `condiciones_terminos`, `user_id`, `prioridad`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, '2025-11-11', '2025-12-31', 'Aprobada', 59.80, NULL, NULL, NULL, 1, 'Normal', '2025-12-08 18:51:54', '2026-01-17 16:56:05', '2026-01-17 16:56:05'),
(2, 3, '2025-11-10', '2026-01-01', 'Aprobada', 599.00, NULL, NULL, NULL, 1, 'Normal', '2025-12-08 19:24:18', '2026-01-17 16:56:10', '2026-01-17 16:56:10'),
(3, 5, '2025-10-09', '2025-12-24', 'Aprobada', 599.00, NULL, NULL, NULL, 1, 'Normal', '2025-12-08 20:21:31', '2026-01-17 16:56:16', '2026-01-17 16:56:16'),
(4, 6, '2025-11-13', '2026-01-30', 'Aprobada', 2396.00, NULL, NULL, NULL, 1, 'Normal', '2025-12-09 18:56:23', '2026-01-17 16:48:18', '2026-01-17 16:48:18'),
(5, 7, '2025-11-06', '2025-12-28', 'Pendiente', 897.00, NULL, NULL, NULL, 1, 'Normal', '2025-12-10 18:11:52', '2026-01-17 16:48:14', '2026-01-17 16:48:14'),
(6, 7, '2025-12-10', '2025-12-31', 'Aprobada', 419.00, NULL, NULL, NULL, 1, 'Normal', '2025-12-11 00:29:48', '2026-01-16 22:02:54', '2026-01-16 22:02:54'),
(7, 1, '2025-12-19', '2025-12-30', 'Convertida', 34.00, NULL, NULL, NULL, 1, 'Normal', '2025-12-19 20:15:18', '2026-01-16 22:02:37', '2026-01-16 22:02:37'),
(8, 9, '2026-01-17', '2026-01-24', 'Cancelada', 119.60, NULL, NULL, NULL, 5, 'Normal', '2026-01-17 16:53:23', '2026-01-17 22:48:34', NULL),
(9, 12, '2026-01-19', '2026-01-22', 'Vencida', 119.60, NULL, NULL, NULL, 1, 'Normal', '2026-01-17 17:11:33', '2026-01-25 02:13:47', NULL),
(10, 8, '2026-01-17', '2026-01-22', 'Aprobada', 9956.70, NULL, NULL, NULL, 1, 'Normal', '2026-01-17 22:35:19', '2026-01-19 04:36:58', '2026-01-19 04:36:58'),
(11, 14, '2026-01-18', '2026-01-28', 'Convertida', 388.70, NULL, NULL, NULL, 1, 'Normal', '2026-01-18 22:11:33', '2026-01-19 03:42:07', NULL),
(12, 9, '2026-01-19', '2026-01-20', 'Vencida', 50.00, NULL, NULL, NULL, 5, 'Normal', '2026-01-18 23:44:02', '2026-01-22 03:14:17', NULL),
(13, 15, '2026-01-15', '2026-01-23', 'Convertida', 25.00, NULL, NULL, NULL, 1, 'Normal', '2026-01-19 00:26:43', '2026-01-19 19:24:34', NULL),
(14, 9, '2026-01-19', '2026-01-22', 'Convertida', 1317.80, NULL, NULL, NULL, 5, 'Normal', '2026-01-19 04:25:53', '2026-01-19 19:13:45', NULL),
(15, 20, '2026-01-19', '2026-01-22', 'Vencida', 1317.80, NULL, NULL, NULL, 1, 'Normal', '2026-01-19 04:26:49', '2026-01-25 02:13:47', NULL),
(16, 9, '2026-01-19', '2026-01-22', 'Convertida', 374.00, NULL, NULL, NULL, 1, 'Normal', '2026-01-19 05:10:13', '2026-01-20 21:34:18', NULL),
(17, 15, '2026-01-19', '2026-01-23', 'Convertida', 2963.40, NULL, NULL, NULL, 1, 'Normal', '2026-01-19 05:15:58', '2026-01-19 20:16:20', NULL),
(18, 21, '2026-01-19', '2026-01-21', 'Convertida', 627.00, NULL, NULL, NULL, 1, 'Normal', '2026-01-19 16:49:36', '2026-01-19 19:51:33', NULL),
(19, 9, '2026-01-19', '2026-01-23', 'Convertida', 986.70, NULL, NULL, NULL, 1, 'Normal', '2026-01-19 16:51:55', '2026-01-19 19:32:56', NULL),
(20, 22, '2026-01-19', '2026-01-30', 'Convertida', 29.90, NULL, NULL, NULL, 1, 'Normal', '2026-01-20 01:35:43', '2026-01-20 21:21:33', NULL),
(21, 23, '2026-01-20', '2026-01-31', 'Convertida', 51.00, NULL, NULL, NULL, 1, 'Normal', '2026-01-20 21:25:00', '2026-01-20 21:25:39', NULL),
(22, 23, '2026-01-19', '2026-01-28', 'Convertida', 358.80, NULL, NULL, NULL, 1, 'Normal', '2026-01-20 21:31:53', '2026-01-20 21:32:04', NULL),
(23, 23, '2026-01-21', '2026-01-31', 'Convertida', 190.00, NULL, NULL, NULL, 1, 'Normal', '2026-01-20 21:39:00', '2026-01-20 21:39:09', NULL),
(24, 14, '2026-01-20', '2026-01-30', 'Convertida', 29.90, NULL, NULL, NULL, 1, 'Normal', '2026-01-20 21:45:35', '2026-01-20 21:45:49', NULL),
(25, 23, '2026-01-20', '2026-01-31', 'Convertida', 29.90, NULL, NULL, NULL, 1, 'Normal', '2026-01-20 21:51:41', '2026-01-20 21:52:03', NULL),
(26, 23, '2026-01-20', '2026-01-29', 'Convertida', 59.80, NULL, NULL, NULL, 1, 'Normal', '2026-01-20 21:56:09', '2026-01-20 21:56:29', NULL),
(27, 23, '2026-01-20', '2026-02-03', 'Convertida', 29.90, NULL, NULL, NULL, 1, 'Normal', '2026-01-20 22:01:32', '2026-01-20 22:01:40', NULL),
(28, 23, '2026-01-19', '2026-01-28', 'Convertida', 358.80, NULL, NULL, NULL, 1, 'Normal', '2026-01-20 22:21:26', '2026-01-20 22:21:37', NULL),
(29, 23, '2026-02-19', '2026-02-27', 'Convertida', 17.00, NULL, NULL, NULL, 1, 'Normal', '2026-02-19 20:02:04', '2026-02-19 20:07:49', NULL),
(30, 23, '2026-02-19', '2026-02-24', 'Convertida', 204.00, NULL, NULL, NULL, 1, 'Normal', '2026-02-19 20:53:01', '2026-02-19 20:53:28', NULL),
(31, 23, '2026-02-12', '2026-02-28', 'Convertida', 204.00, NULL, NULL, NULL, 1, 'Normal', '2026-02-20 00:04:01', '2026-02-20 00:04:19', NULL),
(32, 23, '2026-02-19', '2026-04-25', 'Convertida', 156.00, NULL, NULL, NULL, 1, 'Normal', '2026-02-20 00:28:01', '2026-02-20 00:28:11', NULL),
(33, 23, '2026-02-19', '2026-02-23', 'Convertida', 578.00, NULL, NULL, NULL, 1, 'Normal', '2026-02-20 00:32:09', '2026-02-20 00:32:25', NULL),
(34, 23, '2026-02-26', '2026-02-28', 'Convertida', 51.00, NULL, NULL, NULL, 1, 'Normal', '2026-02-20 00:43:30', '2026-02-20 00:43:39', NULL),
(35, 23, '2026-02-19', '2026-05-21', 'Convertida', 85.00, NULL, NULL, NULL, 1, 'Normal', '2026-02-20 00:52:07', '2026-02-20 00:52:16', NULL),
(36, 23, '2026-02-19', '2026-09-17', 'Convertida', 170.00, NULL, NULL, NULL, 1, 'Normal', '2026-02-20 01:01:13', '2026-02-20 01:01:25', NULL),
(37, 23, '2026-02-19', '2026-02-20', 'Convertida', 340.00, NULL, NULL, NULL, 1, 'Normal', '2026-02-20 01:04:12', '2026-02-20 01:04:25', NULL),
(38, 9, '2026-02-22', '2026-02-27', 'Convertida', 204.00, NULL, 'Condiciones....', NULL, 1, 'Normal', '2026-02-22 17:57:57', '2026-02-22 20:52:23', NULL),
(39, 9, '2026-02-23', '2026-02-26', 'Convertida', 170.00, NULL, 'Sakura Fest tiene que pagar rapido', NULL, 1, 'Normal', '2026-02-23 18:44:06', '2026-02-23 20:00:14', NULL),
(40, 23, '2026-02-23', '2026-02-27', 'Convertida', 170.00, NULL, NULL, NULL, 1, 'Normal', '2026-02-23 21:30:18', '2026-02-24 20:05:58', NULL),
(41, 23, '2026-02-24', '2026-02-28', 'Convertida', 22.00, NULL, 'Que quede todo bello', NULL, 1, 'Normal', '2026-02-23 21:46:23', '2026-02-24 19:41:36', NULL),
(42, 23, '2026-02-23', '2026-02-24', 'Convertida', 170.00, NULL, 'Que quede muy bien', NULL, 1, 'Normal', '2026-02-23 22:16:34', '2026-02-24 16:36:47', NULL),
(43, 8, '2026-02-23', '2026-03-05', 'Convertida', 94.50, NULL, 'QA E2E bordados update', NULL, 1, 'Normal', '2026-02-24 02:54:17', '2026-02-24 02:57:23', '2026-02-24 02:57:23'),
(44, 8, '2026-02-23', '2026-03-05', 'Convertida', 94.50, NULL, 'QA E2E bordados script update', NULL, 1, 'Normal', '2026-02-24 02:58:25', '2026-02-24 02:58:25', '2026-02-24 02:58:25'),
(45, 8, '2026-02-23', '2026-03-05', 'Convertida', 94.50, NULL, 'QA E2E bordados script update', NULL, 1, 'Normal', '2026-02-24 03:26:39', '2026-02-24 03:26:40', '2026-02-24 03:26:40'),
(46, 9, '2026-02-24', '2026-04-29', 'Convertida', 253.00, NULL, 'Se requiere un abono del 50% del monto total para empezar a producir', NULL, 1, 'Normal', '2026-02-24 14:41:49', '2026-02-24 15:51:39', NULL),
(47, 8, '2026-02-24', '2026-03-06', 'Convertida', 94.50, NULL, 'QA E2E bordados script update', NULL, 1, 'Normal', '2026-02-24 16:31:27', '2026-02-24 16:31:28', '2026-02-24 16:31:28'),
(48, 23, '2026-02-24', '2026-02-26', 'Convertida', 260.00, NULL, 'hacer bien', NULL, 1, 'Normal', '2026-02-24 17:25:57', '2026-02-24 17:26:11', NULL),
(49, 28, '2026-02-26', '2026-02-28', 'Convertida', 470.00, NULL, 'Fabricar con estandares excelentes', NULL, 1, 'Normal', '2026-02-26 20:32:49', '2026-02-26 20:33:31', NULL),
(50, 28, '2026-02-26', '2026-02-28', 'Convertida', 34.00, NULL, 'condiciones', NULL, 1, 'Normal', '2026-02-26 20:36:45', '2026-02-26 20:36:54', NULL),
(51, 28, '2026-02-26', '2026-02-28', 'Convertida', 17.00, NULL, 'hola', NULL, 1, 'Normal', '2026-02-26 20:39:59', '2026-02-26 20:40:21', NULL),
(52, 28, '2026-02-26', '2026-02-27', 'Convertida', 51.00, NULL, NULL, NULL, 1, 'Normal', '2026-02-26 20:42:22', '2026-02-26 20:42:31', NULL),
(53, 23, '2026-02-26', '2026-02-27', 'Convertida', 170.00, NULL, NULL, NULL, 1, 'Normal', '2026-02-26 20:48:10', '2026-02-26 20:49:46', NULL),
(54, 23, '2026-02-11', '2026-02-27', 'Convertida', 17.00, NULL, NULL, NULL, 1, 'Normal', '2026-02-26 20:50:24', '2026-02-26 20:50:33', NULL),
(55, 23, '2026-02-26', '2026-02-27', 'Convertida', 16.00, NULL, NULL, NULL, 1, 'Normal', '2026-02-26 20:51:52', '2026-02-26 20:54:22', NULL),
(56, 23, '2026-02-26', '2026-02-27', 'Convertida', 16.00, NULL, NULL, NULL, 1, 'Normal', '2026-02-26 21:01:29', '2026-02-26 21:01:40', NULL),
(57, 36, '2026-03-05', '2026-03-06', 'Convertida', 190.00, NULL, 'Maiz', NULL, 7, 'Normal', '2026-03-05 17:30:11', '2026-03-05 17:32:34', NULL),
(58, 36, '2026-03-05', '2026-03-28', 'Convertida', 200.00, NULL, 'hola', NULL, 7, 'Normal', '2026-03-05 17:47:35', '2026-03-05 17:47:46', NULL),
(59, 36, '2026-03-05', '2026-03-14', 'Convertida', 110.00, NULL, 'Pronto', NULL, 7, 'Normal', '2026-03-05 20:03:32', '2026-03-05 20:13:13', NULL),
(60, 36, '2026-03-05', '2026-06-17', 'Pendiente', 80.00, 557.9741, 'lol', NULL, 7, 'Normal', '2026-03-05 20:52:23', '2026-06-03 01:36:08', NULL),
(61, 23, '2026-03-10', '2026-03-26', 'Convertida', 160.00, NULL, 'Precio razonable', NULL, 7, 'Normal', '2026-03-10 20:53:07', '2026-03-10 20:54:39', NULL),
(62, 23, '2026-03-15', '2026-03-18', 'Convertida', 16.00, NULL, 'w', NULL, 7, 'Normal', '2026-03-15 14:51:30', '2026-03-18 14:07:18', NULL),
(63, 8, '2026-03-19', '2026-03-29', 'Convertida', 94.50, NULL, 'QA E2E bordados script update', NULL, 1, 'Normal', '2026-03-19 21:35:32', '2026-03-19 21:35:33', '2026-03-19 21:35:33'),
(64, 8, '2026-03-20', '2026-03-30', 'Convertida', 94.50, NULL, 'QA E2E bordados script update', NULL, 1, 'Normal', '2026-03-20 13:17:33', '2026-03-20 13:17:34', '2026-03-20 13:17:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamento`
--

CREATE TABLE `departamento` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `departamento`
--

INSERT INTO `departamento` (`id`, `nombre`, `activo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Produccion', 1, '2026-04-21 20:16:52', '2026-04-21 20:16:52', NULL),
(2, 'Administracion', 1, '2026-04-21 20:16:52', '2026-04-21 20:16:52', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_cotizacion`
--

CREATE TABLE `detalle_cotizacion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cotizacion_id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` bigint(20) UNSIGNED NOT NULL,
  `tela_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tela_snapshot`)),
  `atributos_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`atributos_snapshot`)),
  `cantidad` int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `lleva_bordado` tinyint(1) NOT NULL DEFAULT 0,
  `color_id` bigint(20) UNSIGNED DEFAULT NULL,
  `talla_id` bigint(20) UNSIGNED DEFAULT NULL,
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_cotizacion`
--

INSERT INTO `detalle_cotizacion` (`id`, `cotizacion_id`, `producto_id`, `tela_snapshot`, `atributos_snapshot`, `cantidad`, `descripcion`, `lleva_bordado`, `color_id`, `talla_id`, `precio_unitario`, `created_at`, `updated_at`) VALUES
(3, 1, 1, NULL, NULL, 2, 'Promo Utah Valley University', 1, NULL, 11, 29.90, '2025-12-08 19:11:06', '2025-12-08 19:11:06'),
(4, 2, 2, NULL, NULL, 10, 'Camisa para Oracle', 1, NULL, 12, 59.90, '2025-12-08 19:24:18', '2025-12-08 19:24:18'),
(5, 3, 2, NULL, NULL, 10, 'Camisas Corte Clasico', 1, NULL, 14, 59.90, '2025-12-08 20:21:31', '2025-12-08 20:21:31'),
(6, 4, 2, NULL, NULL, 40, 'Camisas Corte Columbia para Empresa de GPU\'S', 1, NULL, 12, 59.90, '2025-12-09 18:56:23', '2025-12-09 18:56:23'),
(8, 5, 1, NULL, NULL, 30, 'Cotizacion para empresa de IA', 1, NULL, 11, 29.90, '2025-12-10 23:45:22', '2025-12-10 23:45:22'),
(9, 6, 1, NULL, NULL, 6, 'Chemise Clasica para empresa de IA', 1, NULL, 4, 29.90, '2025-12-11 00:29:48', '2025-12-11 00:29:48'),
(10, 6, 2, NULL, NULL, 4, 'Camisa para empresa', 1, NULL, 13, 59.90, '2025-12-11 00:29:48', '2025-12-11 00:29:48'),
(11, 7, 6, NULL, NULL, 2, NULL, 1, NULL, 12, 17.00, '2025-12-19 20:15:18', '2025-12-19 20:15:18'),
(21, 8, 1, NULL, NULL, 4, 'null', 1, NULL, 10, 29.90, '2026-01-17 22:48:34', '2026-01-17 22:48:34'),
(30, 11, 1, NULL, NULL, 13, 'Chemises Unicolor', 1, NULL, 12, 29.90, '2026-01-19 02:32:34', '2026-01-19 02:32:34'),
(31, 10, 1, NULL, NULL, 333, 'null', 0, NULL, 1, 29.90, '2026-01-19 02:34:08', '2026-01-19 02:34:08'),
(34, 12, 5, NULL, NULL, 2, 'null', 1, NULL, 10, 25.00, '2026-01-19 04:34:46', '2026-01-19 04:34:46'),
(36, 15, 2, NULL, NULL, 22, 'null', 0, NULL, 3, 59.90, '2026-01-19 04:37:45', '2026-01-19 04:37:45'),
(37, 16, 6, NULL, NULL, 22, 'weqew', 1, NULL, 10, 17.00, '2026-01-19 05:10:13', '2026-01-19 05:10:13'),
(40, 14, 2, NULL, NULL, 22, 'null', 0, NULL, 3, 59.90, '2026-01-19 05:24:59', '2026-01-19 05:24:59'),
(44, 9, 1, NULL, NULL, 4, 'null', 0, NULL, 2, 29.90, '2026-01-19 18:53:19', '2026-01-19 18:53:19'),
(45, 13, 5, NULL, NULL, 1, 'null', 0, NULL, 10, 25.00, '2026-01-19 18:53:47', '2026-01-19 18:53:47'),
(46, 19, 1, NULL, NULL, 33, 'null', 0, NULL, 2, 29.90, '2026-01-19 19:31:03', '2026-01-19 19:31:03'),
(47, 18, 4, NULL, NULL, 33, 'null', 0, NULL, 2, 19.00, '2026-01-19 19:51:06', '2026-01-19 19:51:06'),
(50, 17, 1, NULL, NULL, 33, 'scs', 0, NULL, 1, 29.90, '2026-01-19 20:15:52', '2026-01-19 20:15:52'),
(51, 17, 2, NULL, NULL, 33, 'null', 0, NULL, 12, 59.90, '2026-01-19 20:15:52', '2026-01-19 20:15:52'),
(54, 20, 1, NULL, NULL, 1, 'faltan medidas', 1, NULL, 13, 29.90, '2026-01-20 01:41:41', '2026-01-20 01:41:41'),
(55, 21, 6, NULL, NULL, 3, 'Chemises para tienda de servicio tecnico', 1, NULL, 12, 17.00, '2026-01-20 21:25:00', '2026-01-20 21:25:00'),
(56, 22, 1, NULL, NULL, 12, 'Dotacion', 1, NULL, 7, 29.90, '2026-01-20 21:31:53', '2026-01-20 21:31:53'),
(57, 23, 4, NULL, NULL, 10, '10', 1, NULL, 1, 19.00, '2026-01-20 21:39:00', '2026-01-20 21:39:00'),
(58, 24, 1, NULL, NULL, 1, 'Bera', 1, NULL, 12, 29.90, '2026-01-20 21:45:35', '2026-01-20 21:45:35'),
(59, 25, 1, NULL, NULL, 1, 'urgente', 1, NULL, 11, 29.90, '2026-01-20 21:51:41', '2026-01-20 21:51:41'),
(60, 26, 1, NULL, NULL, 2, 'urgente', 1, NULL, 12, 29.90, '2026-01-20 21:56:09', '2026-01-20 21:56:09'),
(61, 27, 1, NULL, NULL, 1, 'si', 0, NULL, 12, 29.90, '2026-01-20 22:01:32', '2026-01-20 22:01:32'),
(62, 28, 1, NULL, NULL, 12, 'pedido urgente', 0, NULL, 11, 29.90, '2026-01-20 22:21:26', '2026-01-20 22:21:26'),
(63, 29, 6, NULL, NULL, 1, 'chemise normal', 1, NULL, 11, 17.00, '2026-02-19 20:02:04', '2026-02-19 20:02:04'),
(64, 30, 6, NULL, NULL, 12, 'chemise calidad', 1, NULL, 10, 17.00, '2026-02-19 20:53:01', '2026-02-19 20:53:01'),
(65, 31, 6, NULL, NULL, 12, 'restaurante', 1, NULL, 8, 17.00, '2026-02-20 00:04:01', '2026-02-20 00:04:01'),
(66, 32, 7, NULL, NULL, 13, 'lolita', 1, NULL, 10, 12.00, '2026-02-20 00:28:01', '2026-02-20 00:28:01'),
(67, 33, 6, NULL, NULL, 34, 'banda', 1, NULL, 11, 17.00, '2026-02-20 00:32:09', '2026-02-20 00:32:09'),
(68, 34, 6, NULL, NULL, 3, 'loli', 1, NULL, 12, 17.00, '2026-02-20 00:43:30', '2026-02-20 00:43:30'),
(69, 35, 6, NULL, NULL, 5, 'psuv', 1, NULL, 12, 17.00, '2026-02-20 00:52:07', '2026-02-20 00:52:07'),
(70, 36, 6, NULL, NULL, 10, 'mandarinas', 1, NULL, 12, 17.00, '2026-02-20 01:01:13', '2026-02-20 01:01:13'),
(71, 37, 6, NULL, NULL, 20, 'nube', 1, NULL, 10, 17.00, '2026-02-20 01:04:12', '2026-02-20 01:04:12'),
(72, 38, 6, NULL, NULL, 12, 'Para restaurante', 0, NULL, 12, 17.00, '2026-02-22 17:57:57', '2026-02-22 17:57:57'),
(73, 39, 6, NULL, NULL, 10, 'Chemises de evento', 1, NULL, 12, 17.00, '2026-02-23 18:44:06', '2026-02-23 18:44:06'),
(74, 40, 6, NULL, NULL, 10, 'hola', 1, NULL, 5, 17.00, '2026-02-23 21:30:18', '2026-02-23 21:30:18'),
(76, 42, 6, NULL, NULL, 10, 'Producto de calidad para restaurant', 1, NULL, 12, 17.00, '2026-02-23 22:16:34', '2026-02-23 22:16:34'),
(78, 43, 5, NULL, NULL, 3, 'QA producto update', 1, NULL, NULL, 31.50, '2026-02-24 02:54:17', '2026-02-24 02:54:17'),
(80, 44, 5, NULL, NULL, 3, 'QA producto update', 1, NULL, NULL, 31.50, '2026-02-24 02:58:25', '2026-02-24 02:58:25'),
(82, 45, 5, NULL, NULL, 3, 'QA producto update', 1, NULL, NULL, 31.50, '2026-02-24 03:26:39', '2026-02-24 03:26:39'),
(83, 46, 6, NULL, NULL, 10, 'Chemises para la Alcaldia de Paez', 1, 6, 12, 22.00, '2026-02-24 14:41:49', '2026-02-24 14:41:49'),
(84, 46, 5, NULL, NULL, 1, 'Camisas para empresa agro', 1, 8, 11, 33.00, '2026-02-24 14:41:49', '2026-02-24 14:41:49'),
(86, 47, 5, NULL, NULL, 3, 'QA producto update', 1, NULL, NULL, 31.50, '2026-02-24 16:31:27', '2026-02-24 16:31:27'),
(87, 48, 6, NULL, NULL, 13, 'notas', 1, 7, 12, 20.00, '2026-02-24 17:25:57', '2026-02-24 17:25:57'),
(88, 41, 6, NULL, NULL, 1, 'normal', 1, NULL, 12, 22.00, '2026-02-24 19:32:00', '2026-02-24 19:32:00'),
(89, 49, 6, NULL, NULL, 10, 'Observaciones', 1, 2, 12, 47.00, '2026-02-26 20:32:49', '2026-02-26 20:32:49'),
(90, 50, 6, NULL, NULL, 2, 'hola', 0, 1, 12, 17.00, '2026-02-26 20:36:45', '2026-02-26 20:36:45'),
(91, 51, 6, NULL, NULL, 1, NULL, 0, 7, 14, 17.00, '2026-02-26 20:39:59', '2026-02-26 20:39:59'),
(92, 52, 6, NULL, NULL, 3, NULL, 0, 10, 13, 17.00, '2026-02-26 20:42:22', '2026-02-26 20:42:22'),
(93, 53, 6, NULL, NULL, 10, NULL, 0, 7, 11, 17.00, '2026-02-26 20:48:10', '2026-02-26 20:48:10'),
(94, 54, 6, NULL, NULL, 1, NULL, 0, 9, 10, 17.00, '2026-02-26 20:50:24', '2026-02-26 20:50:24'),
(95, 55, 8, NULL, NULL, 1, NULL, 0, 7, 12, 16.00, '2026-02-26 20:51:52', '2026-02-26 20:51:52'),
(96, 56, 8, NULL, NULL, 1, NULL, 0, 1, 14, 16.00, '2026-02-26 21:01:29', '2026-02-26 21:01:29'),
(98, 57, 8, NULL, NULL, 10, 'Buena calidad', 1, 6, 11, 19.00, '2026-03-05 17:30:23', '2026-03-05 17:30:23'),
(99, 58, 6, NULL, NULL, 10, 'hola', 1, 7, 12, 20.00, '2026-03-05 17:47:35', '2026-03-05 17:47:35'),
(100, 59, 6, NULL, NULL, 5, 'caraotas', 1, 2, 14, 22.00, '2026-03-05 20:03:32', '2026-03-05 20:03:32'),
(101, 60, 8, NULL, NULL, 5, 'lol', 0, 7, 13, 16.00, '2026-03-05 20:52:23', '2026-03-05 20:52:23'),
(102, 61, 8, NULL, NULL, 10, 'Notas', 1, 6, 11, 16.00, '2026-03-10 20:53:07', '2026-03-10 20:53:07'),
(103, 62, 8, NULL, NULL, 1, NULL, 0, 9, 13, 16.00, '2026-03-15 14:51:30', '2026-03-15 14:51:30'),
(105, 63, 6, NULL, NULL, 3, 'QA producto update', 1, NULL, NULL, 31.50, '2026-03-19 21:35:32', '2026-03-19 21:35:32'),
(107, 64, 6, NULL, NULL, 3, 'QA producto update', 1, NULL, NULL, 31.50, '2026-03-20 13:17:33', '2026-03-20 13:17:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_cotizacion_bordado`
--

CREATE TABLE `detalle_cotizacion_bordado` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `detalle_cotizacion_id` bigint(20) UNSIGNED NOT NULL,
  `ubicacion_bordado_id` bigint(20) UNSIGNED DEFAULT NULL,
  `logo_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nombre_aplicado` varchar(191) NOT NULL,
  `nombre_logo_aplicado` varchar(120) DEFAULT NULL,
  `es_personalizada` tinyint(1) NOT NULL DEFAULT 0,
  `cantidad` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `precio_aplicado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `orden` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_cotizacion_bordado`
--

INSERT INTO `detalle_cotizacion_bordado` (`id`, `detalle_cotizacion_id`, `ubicacion_bordado_id`, `logo_id`, `nombre_aplicado`, `nombre_logo_aplicado`, `es_personalizada`, `cantidad`, `precio_aplicado`, `orden`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, NULL, 'Frontal Izquierdo', 'Turning Point USA', 1, 1, 3.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(2, 4, NULL, NULL, 'Frontal Izquierdo', 'Oracle', 1, 1, 3.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(3, 5, NULL, NULL, 'Frontal Izquierdo', 'maxcell corporation logo', 1, 1, 3.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(4, 6, NULL, NULL, 'Frontal Izquierdo', 'nvidia logo', 1, 1, 3.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(5, 8, NULL, NULL, 'Frontal Izquierdo', 'Palantir Logo', 1, 1, 3.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(6, 9, NULL, NULL, 'Frontal Izquierdo', 'Logo de IA', 1, 1, 3.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(7, 10, NULL, NULL, 'Frontal Derecho', 'Logo de Camisa', 1, 1, 3.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(8, 11, NULL, NULL, 'Frontal Izquierdo', 'Logo UPTP', 1, 1, 3.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(9, 21, NULL, NULL, 'Frontal Izquierdo', 'Logo UPTP', 1, 1, 3.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(10, 30, NULL, NULL, 'Frontal Izquierdo', 'Lacoste', 1, 1, 3.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(11, 34, NULL, NULL, 'Frontal Izquierdo', 'uptp', 1, 1, 3.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(12, 37, NULL, NULL, 'izquierda', 'uptp', 1, 1, 0.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(13, 54, NULL, 4, 'Frontal izquierdo', 'Paica', 1, 1, 3.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(14, 55, NULL, NULL, 'Frontal Izquierdo', 'Arzatek', 1, 1, 3.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(15, 56, NULL, NULL, 'Frontal Izquierdo', 'TROC', 1, 1, 3.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(16, 57, NULL, 4, 'Pierna Izquierda', 'Paica', 1, 1, 0.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(17, 58, NULL, NULL, 'Frontal Izquierdo', 'Bera', 1, 1, 3.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(18, 59, NULL, NULL, 'Frontal Izquierdo', 'Arzatek', 1, 1, 3.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(19, 60, NULL, NULL, 'Frontal Izquierdo', 'Arzatek', 1, 1, 3.00, 0, '2026-02-24 02:48:13', '2026-02-24 02:48:13'),
(20, 63, NULL, NULL, 'frontal izquierdo', 'Bera logo', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(21, 64, NULL, NULL, 'Frontal Izquierdo', 'Forum', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(22, 65, NULL, NULL, 'Frontal Izquierdo', 'china grill', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(23, 66, NULL, NULL, 'Frontal Izquierdo', 'pollos marielis', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(24, 67, NULL, NULL, 'Frontal Izquierdo', 'rock', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(25, 68, NULL, NULL, 'Frontal Izquierdo', 'rosas', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(26, 69, NULL, NULL, 'Frontal Izquierdo', 'gallo', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(27, 70, NULL, NULL, 'Frontal Izquierdo', 'fruta', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(28, 71, NULL, NULL, 'Frontal Izquierdo', 'algodon', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(29, 73, NULL, 18, 'Frontal Izquierdo', 'Supermercado Garzon', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(30, 74, NULL, 5, 'Frontal Izquierdo', 'Alcaldia Municipal', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(32, 76, NULL, 16, 'Frontal Izquierdo', 'Panaderia La Espiga', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(36, 78, 2, NULL, 'Frontal Derecho', 'Logo QA2', 0, 1, 2.50, 0, '2026-02-24 02:54:17', '2026-02-24 02:54:17'),
(37, 78, 5, NULL, 'Espaldar', 'Logo QA2', 0, 2, 4.50, 1, '2026-02-24 02:54:17', '2026-02-24 02:54:17'),
(41, 80, 2, NULL, 'Frontal Derecho', 'Logo QA2', 0, 1, 2.50, 0, '2026-02-24 02:58:25', '2026-02-24 02:58:25'),
(42, 80, 5, NULL, 'Espaldar', 'Logo QA2', 0, 2, 4.50, 1, '2026-02-24 02:58:25', '2026-02-24 02:58:25'),
(46, 82, 2, NULL, 'Frontal Derecho', 'Logo QA A2', 0, 1, 2.50, 0, '2026-02-24 03:26:39', '2026-02-24 03:26:39'),
(47, 82, 5, NULL, 'Espaldar', 'Logo QA B2', 0, 2, 4.50, 1, '2026-02-24 03:26:39', '2026-02-24 03:26:39'),
(48, 83, 5, 5, 'Espaldar', 'Alcaldia Municipal', 0, 1, 5.00, 0, '2026-02-24 14:41:49', '2026-02-24 14:41:49'),
(49, 84, 5, 14, 'Espaldar', 'Inversiones Polar', 0, 1, 5.00, 0, '2026-02-24 14:41:49', '2026-02-24 14:41:49'),
(50, 84, 1, 2, 'Frontal Izquierdo', 'Asoportuguesa Corp', 0, 1, 3.00, 1, '2026-02-24 14:41:49', '2026-02-24 14:41:49'),
(54, 86, 2, NULL, 'Frontal Derecho', 'Logo QA A2', 0, 1, 2.50, 0, '2026-02-24 16:31:27', '2026-02-24 16:31:27'),
(55, 86, 5, NULL, 'Espaldar', 'Logo QA B2', 0, 2, 4.50, 1, '2026-02-24 16:31:27', '2026-02-24 16:31:27'),
(56, 87, 1, 5, 'Frontal Izquierdo', 'Alcaldia Municipal', 0, 1, 3.00, 0, '2026-02-24 17:25:57', '2026-02-24 17:25:57'),
(57, 88, 5, 1, 'Espaldar', 'Colegio Angel de la Guarda', 0, 1, 5.00, 0, '2026-02-24 19:32:00', '2026-02-24 19:32:00'),
(58, 88, NULL, 6, 'Frontal Izquierdo', 'Banco Provincial S.A', 1, 1, 3.00, 1, '2026-02-24 19:32:00', '2026-02-24 19:32:00'),
(59, 89, 1, 1, 'Frontal Izquierdo', 'Colegio Angel de la Guarda', 0, 10, 3.00, 0, '2026-02-26 20:32:49', '2026-02-26 20:32:49'),
(61, 98, 1, 2, 'Frontal Izquierdo', 'Asoportuguesa Corp', 0, 1, 3.00, 0, '2026-03-05 17:30:23', '2026-03-05 17:30:23'),
(62, 99, 2, 2, 'Frontal Derecho', 'Asoportuguesa Corp', 0, 1, 3.00, 0, '2026-03-05 17:47:35', '2026-03-05 17:47:35'),
(63, 100, 5, 2, 'Espaldar', 'Asoportuguesa Corp', 0, 1, 5.00, 0, '2026-03-05 20:03:32', '2026-03-05 20:03:32'),
(64, 102, 1, 2, 'Frontal Izquierdo', 'Asoportuguesa Corp', 0, 1, 2.00, 0, '2026-03-10 20:53:07', '2026-03-10 20:53:07'),
(65, 102, 2, 7, 'Frontal Derecho', 'Coca-Cola Classic', 0, 1, 2.00, 1, '2026-03-10 20:53:07', '2026-03-10 20:53:07'),
(69, 105, 2, 1, 'Frontal Derecho', 'Colegio Angel de la Guarda', 0, 1, 2.50, 0, '2026-03-19 21:35:32', '2026-03-19 21:35:32'),
(70, 105, 5, 2, 'Espaldar', 'Asoportuguesa Corp', 0, 2, 4.50, 1, '2026-03-19 21:35:32', '2026-03-19 21:35:32'),
(74, 107, 2, 1, 'Frontal Derecho', 'Colegio Angel de la Guarda', 0, 1, 2.50, 0, '2026-03-20 13:17:33', '2026-03-20 13:17:33'),
(75, 107, 5, 2, 'Espaldar', 'Asoportuguesa Corp', 0, 2, 4.50, 1, '2026-03-20 13:17:33', '2026-03-20 13:17:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_orden_insumo`
--

CREATE TABLE `detalle_orden_insumo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `orden_produccion_id` bigint(20) UNSIGNED NOT NULL,
  `insumo_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad_estimada` decimal(10,2) NOT NULL,
  `cantidad_utilizada` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_orden_insumo`
--

INSERT INTO `detalle_orden_insumo` (`id`, `orden_produccion_id`, `insumo_id`, `cantidad_estimada`, `cantidad_utilizada`, `created_at`, `updated_at`) VALUES
(3, 4, 1, 2.00, 0.00, '2025-12-18 18:00:17', '2025-12-18 18:00:17'),
(4, 5, 1, 2.00, 0.00, '2025-12-19 18:44:18', '2025-12-19 18:44:18'),
(6, 6, 1, 2.00, 0.00, '2026-01-16 18:10:26', '2026-01-16 18:10:26'),
(9, 7, 2, 4.00, 0.00, '2026-01-17 17:35:23', '2026-01-17 17:35:23'),
(10, 8, 5, 1.00, 0.00, '2026-03-06 18:38:41', '2026-03-06 18:38:41'),
(11, 9, 5, 50.00, 0.00, '2026-03-06 20:28:00', '2026-03-06 20:28:00'),
(15, 10, 3, 8.00, 0.00, '2026-03-06 21:04:25', '2026-03-06 21:04:25'),
(16, 11, 5, 10.00, 0.00, '2026-03-18 14:10:20', '2026-03-18 14:10:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pedido_id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` bigint(20) UNSIGNED NOT NULL,
  `tela_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tela_snapshot`)),
  `atributos_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`atributos_snapshot`)),
  `cantidad` int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `lleva_bordado` tinyint(1) NOT NULL DEFAULT 0,
  `color_id` bigint(20) UNSIGNED DEFAULT NULL,
  `talla_id` bigint(20) UNSIGNED DEFAULT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id`, `pedido_id`, `producto_id`, `tela_snapshot`, `atributos_snapshot`, `cantidad`, `descripcion`, `lleva_bordado`, `color_id`, `talla_id`, `precio_unitario`, `created_at`, `updated_at`) VALUES
(3, 3, 5, NULL, NULL, 1, 'con tapa botones', 1, 1, 13, 25.00, '2025-12-18 17:45:32', '2025-12-18 17:45:32'),
(7, 4, 6, NULL, NULL, 2, NULL, 0, 1, 4, 17.00, '2026-01-16 18:00:46', '2026-01-16 18:00:46'),
(8, 2, 5, NULL, NULL, 2, 'manga corta', 1, 1, 12, 25.00, '2026-01-16 18:07:36', '2026-01-16 18:07:36'),
(9, 1, 1, NULL, NULL, 1, 'Chemises clasicas para empresa de Inteligencia Artificial', 1, NULL, 13, 29.90, '2026-01-16 18:07:49', '2026-01-16 18:07:49'),
(12, 6, 1, NULL, NULL, 4, 'null', 0, 1, 2, 29.90, '2026-01-17 22:34:12', '2026-01-17 22:34:12'),
(13, 5, 1, NULL, NULL, 4, 'null', 1, 1, 10, 29.90, '2026-01-17 22:34:28', '2026-01-17 22:34:28'),
(15, 7, 1, NULL, NULL, 4, 'null', 0, 1, 2, 29.90, '2026-01-17 23:23:25', '2026-01-17 23:23:25'),
(16, 8, 1, NULL, NULL, 13, 'Chemises Unicolor', 1, 1, 12, 29.90, '2026-01-19 03:37:08', '2026-01-19 03:37:08'),
(17, 9, 1, NULL, NULL, 13, 'Chemises Unicolor', 1, 1, 12, 29.90, '2026-01-19 03:42:07', '2026-01-19 03:42:07'),
(18, 10, 2, NULL, NULL, 22, 'null', 0, 1, 3, 59.90, '2026-01-19 04:36:20', '2026-01-19 04:36:20'),
(19, 11, 2, NULL, NULL, 22, 'null', 0, NULL, 3, 59.90, '2026-01-19 19:13:45', '2026-01-19 19:13:45'),
(20, 12, 5, NULL, NULL, 1, 'null', 0, NULL, 10, 25.00, '2026-01-19 19:24:34', '2026-01-19 19:24:34'),
(21, 13, 1, NULL, NULL, 33, 'null', 0, NULL, 2, 29.90, '2026-01-19 19:32:56', '2026-01-19 19:32:56'),
(22, 14, 4, NULL, NULL, 33, 'null', 0, NULL, 2, 19.00, '2026-01-19 19:51:33', '2026-01-19 19:51:33'),
(23, 15, 1, NULL, NULL, 33, 'scs', 0, NULL, 1, 29.90, '2026-01-19 20:16:20', '2026-01-19 20:16:20'),
(24, 15, 2, NULL, NULL, 33, 'null', 0, NULL, 12, 59.90, '2026-01-19 20:16:20', '2026-01-19 20:16:20'),
(25, 16, 1, NULL, NULL, 1, 'faltan medidas', 1, NULL, 13, 29.90, '2026-01-20 21:21:33', '2026-01-20 21:21:33'),
(26, 17, 6, NULL, NULL, 3, 'Chemises para tienda de servicio tecnico', 1, NULL, 12, 17.00, '2026-01-20 21:25:39', '2026-01-20 21:25:39'),
(27, 18, 1, NULL, NULL, 12, 'Dotacion', 1, NULL, 7, 29.90, '2026-01-20 21:32:04', '2026-01-20 21:32:04'),
(28, 19, 6, NULL, NULL, 22, 'weqew', 1, NULL, 10, 17.00, '2026-01-20 21:34:18', '2026-01-20 21:34:18'),
(29, 20, 4, NULL, NULL, 10, '10', 1, NULL, 1, 19.00, '2026-01-20 21:39:09', '2026-01-20 21:39:09'),
(30, 21, 1, NULL, NULL, 1, 'Bera', 1, NULL, 12, 29.90, '2026-01-20 21:45:49', '2026-01-20 21:45:49'),
(31, 22, 1, NULL, NULL, 1, 'urgente', 1, NULL, 11, 29.90, '2026-01-20 21:52:03', '2026-01-20 21:52:03'),
(32, 23, 1, NULL, NULL, 2, 'urgente', 1, NULL, 12, 29.90, '2026-01-20 21:56:29', '2026-01-20 21:56:29'),
(33, 24, 1, NULL, NULL, 1, 'si', 0, NULL, 12, 29.90, '2026-01-20 22:01:40', '2026-01-20 22:01:40'),
(35, 25, 1, NULL, NULL, 12, 'pedido urgente', 0, NULL, 11, 29.90, '2026-01-20 22:22:03', '2026-01-20 22:22:03'),
(36, 26, 6, NULL, NULL, 1, 'chemise normal', 1, NULL, 11, 17.00, '2026-02-19 20:07:49', '2026-02-19 20:07:49'),
(37, 27, 6, NULL, NULL, 12, 'chemise calidad', 1, NULL, 10, 17.00, '2026-02-19 20:53:28', '2026-02-19 20:53:28'),
(38, 28, 6, NULL, NULL, 12, 'restaurante', 1, NULL, 8, 17.00, '2026-02-20 00:04:19', '2026-02-20 00:04:19'),
(39, 29, 7, NULL, NULL, 13, 'lolita', 1, NULL, 10, 12.00, '2026-02-20 00:28:11', '2026-02-20 00:28:11'),
(41, 30, 6, NULL, NULL, 34, 'banda', 1, NULL, 11, 17.00, '2026-02-20 00:42:11', '2026-02-20 00:42:11'),
(42, 31, 6, NULL, NULL, 3, 'loli', 1, NULL, 12, 17.00, '2026-02-20 00:43:39', '2026-02-20 00:43:39'),
(43, 32, 6, NULL, NULL, 5, 'psuv', 1, NULL, 12, 17.00, '2026-02-20 00:52:16', '2026-02-20 00:52:16'),
(44, 33, 6, NULL, NULL, 10, 'mandarinas', 1, NULL, 12, 17.00, '2026-02-20 01:01:25', '2026-02-20 01:01:25'),
(45, 34, 6, NULL, NULL, 20, 'nube', 1, NULL, 10, 17.00, '2026-02-20 01:04:25', '2026-02-20 01:04:25'),
(46, 35, 6, NULL, NULL, 12, 'Para restaurante', 0, NULL, 12, 17.00, '2026-02-22 20:52:23', '2026-02-22 20:52:23'),
(47, 36, 6, NULL, NULL, 10, 'Chemises de evento', 1, NULL, 12, 17.00, '2026-02-23 20:00:14', '2026-02-23 20:00:14'),
(48, 37, 5, NULL, NULL, 3, 'QA producto update', 1, NULL, NULL, 31.50, '2026-02-24 02:54:17', '2026-02-24 02:54:17'),
(49, 38, 5, NULL, NULL, 3, 'QA producto update', 1, NULL, NULL, 31.50, '2026-02-24 02:58:25', '2026-02-24 02:58:25'),
(50, 39, 5, NULL, NULL, 3, 'QA producto update', 1, NULL, NULL, 31.50, '2026-02-24 03:26:39', '2026-02-24 03:26:39'),
(51, 40, 6, NULL, NULL, 10, 'Chemises para la Alcaldia de Paez', 1, 6, 12, 22.00, '2026-02-24 15:51:39', '2026-02-24 15:51:39'),
(52, 40, 5, NULL, NULL, 1, 'Camisas para empresa agro', 1, 8, 11, 33.00, '2026-02-24 15:51:39', '2026-02-24 15:51:39'),
(53, 41, 5, NULL, NULL, 3, 'QA producto update', 1, NULL, NULL, 31.50, '2026-02-24 16:31:27', '2026-02-24 16:31:27'),
(55, 42, 6, NULL, NULL, 10, 'Producto de calidad para restaurant', 1, NULL, 12, 20.00, '2026-02-24 16:37:21', '2026-02-24 16:37:21'),
(58, 43, 6, NULL, NULL, 13, 'notas', 1, 7, 12, 20.00, '2026-02-24 17:58:15', '2026-02-24 17:58:15'),
(60, 44, 6, NULL, NULL, 1, 'normal', 1, NULL, 12, 25.00, '2026-02-24 19:42:24', '2026-02-24 19:42:24'),
(61, 45, 6, NULL, NULL, 10, 'hola', 1, NULL, 5, 17.00, '2026-02-24 20:05:58', '2026-02-24 20:05:58'),
(64, 46, 6, NULL, NULL, 10, 'Observaciones', 1, 2, 12, 47.00, '2026-02-26 20:35:51', '2026-02-26 20:35:51'),
(65, 47, 6, NULL, NULL, 2, 'hola', 0, 1, 12, 17.00, '2026-02-26 20:36:54', '2026-02-26 20:36:54'),
(66, 48, 6, NULL, NULL, 1, NULL, 0, 7, 14, 17.00, '2026-02-26 20:40:21', '2026-02-26 20:40:21'),
(67, 49, 6, NULL, NULL, 3, NULL, 0, 10, 13, 17.00, '2026-02-26 20:42:31', '2026-02-26 20:42:31'),
(68, 50, 6, NULL, NULL, 10, NULL, 0, 7, 11, 17.00, '2026-02-26 20:49:46', '2026-02-26 20:49:46'),
(69, 51, 6, NULL, NULL, 1, NULL, 0, 9, 10, 17.00, '2026-02-26 20:50:33', '2026-02-26 20:50:33'),
(70, 52, 8, NULL, NULL, 1, NULL, 0, 7, 12, 16.00, '2026-02-26 20:54:22', '2026-02-26 20:54:22'),
(71, 53, 8, NULL, NULL, 1, NULL, 0, 1, 14, 16.00, '2026-02-26 21:01:40', '2026-02-26 21:01:40'),
(73, 54, 8, NULL, NULL, 10, 'Buena calidad', 1, 6, 11, 19.00, '2026-03-05 17:33:27', '2026-03-05 17:33:27'),
(75, 55, 6, NULL, NULL, 10, 'hola', 1, 7, 12, 20.00, '2026-03-05 17:48:24', '2026-03-05 17:48:24'),
(77, 56, 6, NULL, NULL, 5, 'caraotas', 1, 2, 14, 22.00, '2026-03-05 20:14:11', '2026-03-05 20:14:11'),
(79, 57, 8, NULL, NULL, 10, 'Notas', 1, 6, 11, 20.00, '2026-03-10 20:57:35', '2026-03-10 20:57:35'),
(82, 59, 6, NULL, NULL, 3, 'QA producto update', 1, NULL, NULL, 31.50, '2026-03-19 21:35:32', '2026-03-19 21:35:32'),
(83, 60, 6, NULL, NULL, 3, 'QA producto update', 1, NULL, NULL, 31.50, '2026-03-20 13:17:33', '2026-03-20 13:17:33'),
(87, 58, 8, NULL, NULL, 1, 'null', 0, 9, 13, 16.00, '2026-03-23 03:12:13', '2026-03-23 03:12:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido_bordado`
--

CREATE TABLE `detalle_pedido_bordado` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `detalle_pedido_id` bigint(20) UNSIGNED NOT NULL,
  `ubicacion_bordado_id` bigint(20) UNSIGNED DEFAULT NULL,
  `logo_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nombre_aplicado` varchar(191) NOT NULL,
  `nombre_logo_aplicado` varchar(120) DEFAULT NULL,
  `es_personalizada` tinyint(1) NOT NULL DEFAULT 0,
  `cantidad` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `precio_aplicado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `orden` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_pedido_bordado`
--

INSERT INTO `detalle_pedido_bordado` (`id`, `detalle_pedido_id`, `ubicacion_bordado_id`, `logo_id`, `nombre_aplicado`, `nombre_logo_aplicado`, `es_personalizada`, `cantidad`, `precio_aplicado`, `orden`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, 3, 'Frontal Izquierdo', 'Los Caminos', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(2, 8, NULL, NULL, 'Frontal Izquierdo', 'Palantir Logo', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(3, 9, NULL, NULL, 'Frontal Izquierdo', 'Palantir Logo', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(4, 13, NULL, NULL, 'Frontal Izquierdo', 'Logo UPTP', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(5, 16, NULL, NULL, 'Frontal Izquierdo', 'Lacoste', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(6, 17, NULL, NULL, 'Frontal Izquierdo', 'Lacoste', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(7, 25, NULL, 4, 'Frontal izquierdo', 'Paica', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(8, 26, NULL, NULL, 'Frontal Izquierdo', 'Arzatek', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(9, 27, NULL, NULL, 'Frontal Izquierdo', 'TROC', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(10, 28, NULL, NULL, 'izquierda', 'uptp', 1, 1, 0.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(11, 29, NULL, 4, 'Pierna Izquierda', 'Paica', 1, 1, 0.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(12, 30, NULL, NULL, 'Frontal Izquierdo', 'Bera', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(13, 31, NULL, NULL, 'Frontal Izquierdo', 'Arzatek', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(14, 32, NULL, NULL, 'Frontal Izquierdo', 'Arzatek', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(15, 36, NULL, NULL, 'frontal izquierdo', 'Bera logo', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(16, 37, NULL, NULL, 'Frontal Izquierdo', 'Forum', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(17, 38, NULL, NULL, 'Frontal Izquierdo', 'china grill', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(18, 39, NULL, NULL, 'Frontal Izquierdo', 'pollos marielis', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(19, 42, NULL, NULL, 'Frontal Izquierdo', 'rosas', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(20, 43, NULL, NULL, 'Frontal Izquierdo', 'gallo', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(21, 44, NULL, NULL, 'Frontal Izquierdo', 'fruta', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(22, 45, NULL, NULL, 'Frontal Izquierdo', 'algodon', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(23, 47, NULL, 18, 'Frontal Izquierdo', 'Supermercado Garzon', 1, 1, 3.00, 0, '2026-02-24 02:48:14', '2026-02-24 02:48:14'),
(24, 48, 2, NULL, 'Frontal Derecho', 'Logo QA2', 0, 1, 2.50, 0, '2026-02-24 02:54:17', '2026-02-24 02:54:17'),
(25, 48, 5, NULL, 'Espaldar', 'Logo QA2', 0, 2, 4.50, 1, '2026-02-24 02:54:17', '2026-02-24 02:54:17'),
(26, 49, 2, NULL, 'Frontal Derecho', 'Logo QA2', 0, 1, 2.50, 0, '2026-02-24 02:58:25', '2026-02-24 02:58:25'),
(27, 49, 5, NULL, 'Espaldar', 'Logo QA2', 0, 2, 4.50, 1, '2026-02-24 02:58:25', '2026-02-24 02:58:25'),
(28, 50, 2, NULL, 'Frontal Derecho', 'Logo QA A2', 0, 1, 2.50, 0, '2026-02-24 03:26:39', '2026-02-24 03:26:39'),
(29, 50, 5, NULL, 'Espaldar', 'Logo QA B2', 0, 2, 4.50, 1, '2026-02-24 03:26:39', '2026-02-24 03:26:39'),
(30, 51, 5, 5, 'Espaldar', 'Alcaldia Municipal', 0, 1, 5.00, 0, '2026-02-24 15:51:39', '2026-02-24 15:51:39'),
(31, 52, 5, 14, 'Espaldar', 'Inversiones Polar', 0, 1, 5.00, 0, '2026-02-24 15:51:39', '2026-02-24 15:51:39'),
(32, 52, 1, 2, 'Frontal Izquierdo', 'Asoportuguesa Corp', 0, 1, 3.00, 1, '2026-02-24 15:51:39', '2026-02-24 15:51:39'),
(33, 53, 2, NULL, 'Frontal Derecho', 'Logo QA A2', 0, 1, 2.50, 0, '2026-02-24 16:31:27', '2026-02-24 16:31:27'),
(34, 53, 5, NULL, 'Espaldar', 'Logo QA B2', 0, 2, 4.50, 1, '2026-02-24 16:31:27', '2026-02-24 16:31:27'),
(36, 55, NULL, 16, 'Frontal Izquierdo', 'Panaderia La Espiga', 1, 1, 3.00, 0, '2026-02-24 16:37:21', '2026-02-24 16:37:21'),
(39, 58, 1, 5, 'Frontal Izquierdo', 'Alcaldia Municipal', 0, 1, 3.00, 0, '2026-02-24 17:58:15', '2026-02-24 17:58:15'),
(42, 60, 5, 1, 'Espaldar', 'Colegio Angel de la Guarda', 0, 1, 5.00, 0, '2026-02-24 19:42:24', '2026-02-24 19:42:24'),
(43, 60, NULL, 6, 'Frontal Izquierdo', 'Banco Provincial S.A', 1, 1, 3.00, 1, '2026-02-24 19:42:24', '2026-02-24 19:42:24'),
(44, 61, NULL, 5, 'Frontal Izquierdo', 'Alcaldia Municipal', 1, 1, 3.00, 0, '2026-02-24 20:05:58', '2026-02-24 20:05:58'),
(47, 64, 1, 1, 'Frontal Izquierdo', 'Colegio Angel de la Guarda', 0, 10, 3.00, 0, '2026-02-26 20:35:51', '2026-02-26 20:35:51'),
(49, 73, 1, 2, 'Frontal Izquierdo', 'Asoportuguesa Corp', 0, 1, 3.00, 0, '2026-03-05 17:33:27', '2026-03-05 17:33:27'),
(51, 75, 2, 2, 'Frontal Derecho', 'Asoportuguesa Corp', 0, 1, 3.00, 0, '2026-03-05 17:48:24', '2026-03-05 17:48:24'),
(53, 77, 5, 2, 'Espaldar', 'Asoportuguesa Corp', 0, 1, 5.00, 0, '2026-03-05 20:14:11', '2026-03-05 20:14:11'),
(56, 79, 1, 2, 'Frontal Izquierdo', 'Asoportuguesa Corp', 0, 1, 2.00, 0, '2026-03-10 20:57:35', '2026-03-10 20:57:35'),
(57, 79, 2, 7, 'Frontal Derecho', 'Coca-Cola Classic', 0, 1, 2.00, 1, '2026-03-10 20:57:35', '2026-03-10 20:57:35'),
(58, 82, 2, 1, 'Frontal Derecho', 'Colegio Angel de la Guarda', 0, 1, 2.50, 0, '2026-03-19 21:35:32', '2026-03-19 21:35:32'),
(59, 82, 5, 2, 'Espaldar', 'Asoportuguesa Corp', 0, 2, 4.50, 1, '2026-03-19 21:35:32', '2026-03-19 21:35:32'),
(60, 83, 2, 1, 'Frontal Derecho', 'Colegio Angel de la Guarda', 0, 1, 2.50, 0, '2026-03-20 13:17:33', '2026-03-20 13:17:33'),
(61, 83, 5, 2, 'Espaldar', 'Asoportuguesa Corp', 0, 2, 4.50, 1, '2026-03-20 13:17:33', '2026-03-20 13:17:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido_insumo`
--

CREATE TABLE `detalle_pedido_insumo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `detalle_pedido_id` bigint(20) UNSIGNED NOT NULL,
  `insumo_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad_estimada` decimal(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_pedido_insumo`
--

INSERT INTO `detalle_pedido_insumo` (`id`, `detalle_pedido_id`, `insumo_id`, `cantidad_estimada`, `created_at`, `updated_at`) VALUES
(3, 3, 1, 2.00, NULL, NULL),
(7, 7, 2, 4.00, NULL, NULL),
(8, 7, 1, 4.00, NULL, NULL),
(9, 8, 1, 4.00, NULL, NULL),
(10, 9, 1, 5.00, NULL, NULL),
(13, 12, 2, 4.00, NULL, NULL),
(14, 13, 2, 1.00, NULL, NULL),
(16, 15, 2, 4.00, NULL, NULL),
(17, 16, 2, 2.00, NULL, NULL),
(18, 17, 2, 2.00, NULL, NULL),
(19, 18, 2, 2.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccion`
--

CREATE TABLE `direccion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `persona_id` bigint(20) UNSIGNED NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL COMMENT 'Estado/Territorio geográfico',
  `tipo` enum('casa','trabajo','envio') NOT NULL DEFAULT 'casa',
  `es_principal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `direccion`
--

INSERT INTO `direccion` (`id`, `persona_id`, `direccion`, `ciudad`, `estado`, `tipo`, `es_principal`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'La Goajira', 'acarigua', NULL, 'casa', 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(2, 3, 'Washington DC', 'Washington', NULL, 'casa', 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(3, 4, 'Urbanización Fundación Mendoza\r\nAvenida 7, Calle Principal', 'Acarigua, Municipio Paez', NULL, 'casa', 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(4, 6, 'Wall Street', 'New York NY', NULL, 'casa', 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(5, 7, 'Sillycon Valley', 'California', NULL, 'casa', 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(6, 8, 'Dayton', 'Ohio', NULL, 'casa', 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(7, 9, 'Headington Hill Hall, Reino Unido', 'Oxford', NULL, 'casa', 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(8, 10, 'San Tomas Expressway', 'Santa Clara, CA', NULL, 'casa', 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(9, 11, 'Denver', 'Colorado', NULL, 'casa', 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(10, 12, 'Crescent Park, 11 Avenue Palo Alto', 'Araure', 'Portuguesa', 'casa', 1, '2025-12-10 20:29:40', '2026-01-18 23:37:09', NULL),
(11, 14, 'Avenue 10', 'Dallas', NULL, 'casa', 1, '2025-12-10 21:09:58', '2025-12-10 21:09:58', NULL),
(12, 13, 'villas', 'acarigua', NULL, 'casa', 1, '2025-12-10 21:17:57', '2025-12-10 21:17:57', NULL),
(13, 15, 'Urb prados del sol', 'Araure', NULL, 'casa', 1, '2026-01-17 16:51:52', '2026-01-17 16:51:52', NULL),
(14, 16, 'prados del sol', 'araure', NULL, 'casa', 1, '2026-01-17 17:11:09', '2026-01-17 17:11:09', NULL),
(15, 18, 'Urb prados del sol', 'Araure', NULL, 'casa', 1, '2026-01-17 22:31:33', '2026-01-17 22:31:33', NULL),
(17, 20, 'Urb. Los Cortijos', 'Páez', 'Portuguesa', 'casa', 1, '2026-01-18 03:56:57', '2026-01-18 03:56:57', NULL),
(19, 22, 'Avenida los Pescadores calle 5', 'Araure', 'Portuguesa', 'trabajo', 1, '2026-01-18 20:23:50', '2026-01-18 20:23:50', NULL),
(20, 23, 'Urb. Los Pinos, Calle 3, Casa 15, Acarigua', 'Páez', 'Portuguesa', 'trabajo', 1, '2026-01-18 20:37:01', '2026-01-18 20:37:01', NULL),
(21, 24, 'Av. Principal, Edif. Sol, Apto 4B, Araure', 'Araure', 'Portuguesa', 'trabajo', 1, '2026-01-18 20:38:40', '2026-01-18 20:38:40', NULL),
(22, 25, 'carlossilva@gmail.com', 'Guanare', 'Portuguesa', 'trabajo', 1, '2026-01-18 20:41:05', '2026-01-18 20:41:05', NULL),
(23, 26, 'Urb. El Recreo, Calle 10, Casa 8, Barinas', 'Barinas', 'Barinas', 'trabajo', 1, '2026-01-18 20:43:53', '2026-01-18 20:43:53', NULL),
(24, 27, 'Urb. Los Samanes, Calle 5, Casa 12, Sector Centro', 'Ospino', 'Portuguesa', 'trabajo', 1, '2026-01-18 20:57:49', '2026-01-18 20:57:49', NULL),
(25, 28, 'Calle 8, Casa 22, Sector La Barinesa, Acarigua', 'Páez', 'Portuguesa', 'trabajo', 1, '2026-01-18 21:52:28', '2026-01-18 21:52:28', NULL),
(26, 29, 'Urb prados del sol', 'Araure', 'Portuguesa', 'casa', 1, '2026-01-19 00:25:34', '2026-01-19 00:25:34', NULL),
(27, 30, 'agua clar', 'Araure', 'Portuguesa', 'casa', 1, '2026-01-19 03:56:04', '2026-01-19 03:56:04', NULL),
(28, 31, 'agua', 'Araure', 'Portuguesa', 'casa', 1, '2026-01-19 04:01:50', '2026-01-19 04:01:50', NULL),
(29, 32, 'Urb prados del sol', 'Araure', 'Portuguesa', 'casa', 1, '2026-01-19 04:05:33', '2026-01-19 04:05:33', NULL),
(30, 33, 'Urb prados del sol', 'Araure', 'Portuguesa', 'casa', 1, '2026-01-19 04:17:44', '2026-01-19 04:17:44', NULL),
(31, 34, 'Urb prados del sol', 'Esteller', 'Portuguesa', 'casa', 1, '2026-01-19 04:26:34', '2026-01-19 04:26:34', NULL),
(32, 35, 'Urb prados del sol', 'Araure', 'Portuguesa', 'casa', 1, '2026-01-19 16:49:16', '2026-01-19 16:49:16', NULL),
(33, 36, 'Urb. villas del pilar', 'Araure', 'Portuguesa', 'casa', 1, '2026-01-20 01:29:08', '2026-01-20 01:29:08', NULL),
(34, 37, 'Fundacion Mendoza', 'Páez', 'Portuguesa', 'casa', 1, '2026-01-20 21:23:34', '2026-01-20 21:23:34', NULL),
(35, 38, 'Urbanizacion Bosques de Camoruco, Av 7 Calle Principal', 'Páez', 'Portuguesa', 'casa', 1, '2026-02-22 17:56:13', '2026-02-22 17:56:13', NULL),
(36, 39, 'Calle 15 con Av. Bolívar, Casa #12', 'Acarigua', 'Portuguesa', 'casa', 1, '2026-02-26 19:10:25', '2026-02-26 19:10:25', NULL),
(37, 40, 'Urb. Las Acacias, Calle 3, Casa #8', 'Araure', 'Portuguesa', 'casa', 1, '2026-02-26 19:10:25', '2026-02-26 19:10:25', NULL),
(38, 42, 'Zona Industrial II, Galpón 5', 'Valencia', 'Carabobo', 'casa', 1, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(39, 43, 'Av. Principal de Páez, Edificio Don Luis, Piso 2', 'Acarigua', 'Portuguesa', 'casa', 1, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(40, 44, 'Calle 30 entre Av. 27 y 28, Local 4', 'San Rafael de Onoto', 'Portuguesa', 'casa', 1, '2026-02-26 19:11:43', '2026-02-26 20:21:46', NULL),
(41, 45, 'Urb. Villa del Pilar, Calle 10, Casa 25', 'Araure', 'Portuguesa', 'casa', 1, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(42, 46, 'Barrio Sucre, Calle Principal, Casa S/N', 'Barinas', 'Barinas', 'casa', 1, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(43, 47, 'Centro Empresarial La Paz, Oficina 302', 'Urdaneta', 'Miranda', 'casa', 1, '2026-02-26 19:11:43', '2026-02-26 20:23:03', NULL),
(44, 48, 'Urb. Prados del Sol, Calle 5, Casa 14-B', 'Araure', 'Portuguesa', 'casa', 1, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(45, 49, 'Sector Los Cortijos, Vereda 3, Casa 7', 'Páez', 'Portuguesa', 'casa', 1, '2026-02-26 19:11:44', '2026-02-26 19:11:44', NULL),
(46, 50, 'Avenida, Calle, Casa', 'Páez', 'Portuguesa', 'casa', 1, '2026-02-26 19:46:04', '2026-02-26 19:46:04', NULL),
(47, 51, 'Diagonal Urbanizacion Altos de la Galera', 'Páez', 'Portuguesa', 'casa', 1, '2026-03-05 17:17:09', '2026-03-05 17:17:09', NULL),
(48, 53, 'Av. Industrial 123, Venezuela', NULL, NULL, 'trabajo', 1, '2026-03-19 18:21:05', '2026-03-19 18:21:05', NULL),
(49, 54, 'Torre Jalisco, Las Mercedes', NULL, NULL, 'trabajo', 1, '2026-03-19 18:21:05', '2026-03-19 18:21:05', NULL),
(50, 55, 'Zona Industrial II, Galpón 15, Acarigua', NULL, NULL, 'trabajo', 1, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(51, 56, 'Av. Bolívar, Local 23, Araure', NULL, NULL, 'trabajo', 1, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(52, 57, 'Calle 5, CC Los Llanos, Valencia', NULL, NULL, 'trabajo', 1, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(53, 58, 'Av. Libertador, Edif. Comercial, Piso 2, Barquisimeto', NULL, NULL, 'trabajo', 1, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(54, 59, 'Calle Principal, Galpón 8, Mérida', NULL, NULL, 'trabajo', 1, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(55, 60, 'La goajira', 'Guanare', 'Portuguesa', 'casa', 1, '2026-04-14 21:26:32', '2026-04-14 21:26:32', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado`
--

CREATE TABLE `empleado` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `persona_id` bigint(20) UNSIGNED NOT NULL,
  `codigo_empleado` varchar(50) NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `cargo_id` bigint(20) UNSIGNED DEFAULT NULL,
  `departamento_id` bigint(20) UNSIGNED DEFAULT NULL,
  `salario` decimal(10,2) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empleado`
--

INSERT INTO `empleado` (`id`, `persona_id`, `codigo_empleado`, `fecha_ingreso`, `cargo_id`, `departamento_id`, `salario`, `estado`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'EMP-001', '2025-12-04', 1, 1, NULL, 1, '2025-12-04 19:45:19', '2026-01-17 05:25:17', '2026-01-17 05:25:17'),
(2, 3, 'EMP-002', '2025-04-07', 2, 2, 897000.00, 1, '2025-12-04 20:19:19', '2026-01-16 21:58:15', '2026-01-16 21:58:15'),
(3, 4, 'EMP-003', '2025-09-29', 3, 1, NULL, 1, '2025-12-05 20:14:04', '2025-12-05 20:14:04', NULL),
(4, 13, 'EMP-004', '2025-05-08', 4, 1, NULL, 1, '2025-12-10 20:57:36', '2025-12-10 20:57:36', NULL),
(5, 14, 'EMP-005', '2025-12-10', 5, 1, NULL, 1, '2025-12-10 21:09:58', '2026-01-16 21:58:10', '2026-01-16 21:58:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `insumo`
--

CREATE TABLE `insumo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `codigo` varchar(8) DEFAULT NULL,
  `tipo` enum('Tela','Hilo','Boton','Cierre','Etiqueta','Otro') NOT NULL,
  `unidad_medida` varchar(20) NOT NULL,
  `is_inventoriable` tinyint(1) NOT NULL DEFAULT 1,
  `costo_unitario` decimal(10,2) NOT NULL,
  `stock_actual` decimal(10,2) NOT NULL,
  `stock_minimo` decimal(10,2) NOT NULL,
  `stock_maximo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `insumo`
--

INSERT INTO `insumo` (`id`, `nombre`, `codigo`, `tipo`, `unidad_medida`, `is_inventoriable`, `costo_unitario`, `stock_actual`, `stock_minimo`, `stock_maximo`, `estado`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Tela Algodón Pima', NULL, 'Tela', 'Metro', 1, 15.50, 985.00, 200.00, 0.00, 1, '2025-12-04 18:58:28', '2026-01-17 17:35:36', '2026-01-17 17:35:36'),
(2, 'Botón Nacar 18mm', NULL, 'Boton', 'Unidad', 1, 0.50, 4981.00, 1000.00, 0.00, 1, '2025-12-04 18:58:28', '2026-01-19 04:36:20', NULL),
(3, 'Pique', NULL, 'Tela', 'Kg', 1, 50.00, 0.00, 5.00, 0.00, 1, '2025-12-11 00:39:02', '2026-02-27 14:58:33', NULL),
(4, 'microdurazno', NULL, 'Hilo', '43', 1, 29.00, 40.00, 39.00, 0.00, 1, '2026-01-14 23:55:14', '2026-01-17 05:19:57', '2026-01-17 05:19:57'),
(5, 'Jersey', NULL, 'Tela', 'Kg', 1, 3.00, 100.00, 10.00, 0.00, 1, '2026-01-20 20:36:23', '2026-02-27 14:58:40', NULL),
(6, 'goma', NULL, 'Otro', 'Metro', 1, 1.00, 10.00, 5.00, 0.00, 1, '2026-02-16 22:19:21', '2026-02-17 05:55:40', '2026-02-17 05:55:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logo`
--

CREATE TABLE `logo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL COMMENT 'Nombre limpio del logo (sin extensión)',
  `original_filename` varchar(191) NOT NULL COMMENT 'Nombre completo del archivo .emb en MEGA',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `logo`
--

INSERT INTO `logo` (`id`, `name`, `original_filename`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Colegio Angel de la Guarda', 'Colegio Angel de la Guarda.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(2, 'Asoportuguesa Corp', 'Asoportuguesa Corp.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(3, 'Los Caminos Hacienda', 'Los Caminos Hacienda.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(4, 'PAICA Alimentos', 'PAICA Alimentos.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(5, 'Alcaldia Municipal', 'Alcaldia Municipal.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(6, 'Banco Provincial S.A', 'Banco Provincial S.A.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(7, 'Coca-Cola Classic', 'Coca-Cola Classic.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(8, 'Distribuidora El Faro', 'Distribuidora El Faro.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(9, 'Escuela de Futbol', 'Escuela de Futbol.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(10, 'Farmacia Express', 'Farmacia Express.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(11, 'Gimnasio Iron Body', 'Gimnasio Iron Body.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(12, 'Hotel Kristoff', 'Hotel Kristoff.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(13, 'Iglesia San Juan', 'Iglesia San Juan.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(14, 'Inversiones Polar', 'Inversiones Polar.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(15, 'Logistica Global', 'Logistica Global.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(16, 'Panaderia La Espiga', 'Panaderia La Espiga.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(17, 'Restaurante El Meson', 'Restaurante El Meson.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(18, 'Supermercado Garzon', 'Supermercado Garzon.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(19, 'Transporte Rapido', 'Transporte Rapido.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL),
(20, 'Universidad Central', 'Universidad Central.emb', '2026-02-22 18:55:09', '2026-02-22 18:55:09', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2025_03_01_000000_create_sistema_produccion_tables', 1),
(6, '2025_06_14_091624_create_pedidos_table', 1),
(7, '2025_06_14_091726_create_detalle_pedidos_table', 1),
(8, '2025_06_14_094205_add_fecha_entrega_estimada_to_pedidos_table', 1),
(9, '2025_06_14_100214_add_rif_to_pedidos_table', 1),
(10, '2025_06_14_102229_remove_unique_rif_from_pedidos_table', 1),
(11, '2025_06_14_103232_rename_rif_to_ci_rif_in_pedidos_table', 1),
(12, '2025_06_14_112859_add_description_and_logo_fields_to_detalle_pedidos_table', 1),
(13, '2025_06_14_114729_add_talla_and_color_to_detalle_pedidos_table', 1),
(14, '2025_06_14_115649_update_talla_enum_in_detalle_pedidos_table', 1),
(15, '2025_06_14_123551_force_update_talla_enum_in_detalle_pedidos_table', 1),
(16, '2025_06_14_210039_create_detalle_pedido_insumo_table', 1),
(17, '2025_06_15_191252_create_bancos_table', 1),
(18, '2025_06_15_191339_add_payment_fields_to_pedidos_table', 1),
(19, '2025_06_19_143226_create_clientes_table', 1),
(20, '2025_06_19_143359_add_cliente_id_to_pedidos_table', 1),
(21, '2025_06_20_000001_create_cotizaciones_table', 1),
(22, '2025_06_20_000002_create_detalle_cotizaciones_table', 1),
(23, '2025_06_21_112333_add_deleted_at_to_clientes_table', 1),
(24, '2025_06_26_221106_remove_prioridad_column_from_cotizaciones_table', 1),
(25, '2025_12_04_134221_update_user_role_enum', 1),
(26, '2025_12_04_150028_rename_all_tables_to_singular_final', 2),
(27, '2025_12_04_153326_add_missing_columns_to_cliente_table', 3),
(28, '2025_12_04_154406_create_persona_table', 4),
(29, '2025_12_04_154408_create_empleado_table', 4),
(30, '2025_12_04_154409_add_persona_id_to_user_table', 4),
(31, '2025_12_04_154448_migrate_users_to_persona', 4),
(32, '2025_12_04_154449_create_empleados_from_supervisores', 4),
(33, '2025_12_05_165423_rename_ruc_to_rif_in_proveedor_table', 5),
(34, '2025_12_08_151400_add_cliente_id_to_cotizacion', 6),
(36, '2025_12_08_153400_normalize_cotizacion_remove_redundant_cliente_fields', 7),
(37, '2025_12_08_154900_normalize_cliente_with_persona', 8),
(38, '2025_12_09_170406_remove_payment_columns_from_cotizacion_table', 9),
(39, '2025_12_10_143835_create_telefono_table', 10),
(40, '2025_12_10_144011_create_direccion_table', 10),
(41, '2025_12_10_144137_migrate_telefono_direccion_data_from_persona', 10),
(42, '2025_12_10_164505_remove_telefono_direccion_ciudad_from_persona_table', 11),
(43, '2025_12_10_173653_add_cliente_id_to_pedido_table', 12),
(44, '2025_12_10_194225_remove_legacy_cliente_columns_from_pedido_table', 13),
(45, '2025_12_15_150500_make_color_nullable_in_producto_table', 14),
(46, '2025_12_15_155200_make_material_talla_nullable_in_producto_table', 15),
(47, '2025_12_15_160400_drop_material_talla_from_producto_table', 16),
(48, '2025_12_15_164400_create_tasa_cambio_table', 17),
(49, '2025_12_16_134300_create_tipo_producto_table', 18),
(50, '2025_12_16_134400_add_tipo_and_codigo_to_producto_table', 18),
(51, '2025_12_18_134800_add_pedido_id_and_logo_to_orden_produccion', 19),
(52, '2025_12_19_152000_add_cotizacion_id_to_pedido', 20),
(53, '2026_01_17_225337_rename_estado_to_estatus_and_add_estado_territorial', 21),
(54, '2026_01_18_160000_add_tipo_proveedor_and_persona_id_to_proveedor', 22),
(55, '2026_01_18_162000_make_proveedor_fields_nullable', 23),
(56, '2026_01_19_145036_add_separate_bank_fields_to_pedidos_table', 24),
(57, '2026_02_19_200000_add_unique_index_to_pedido_cotizacion_id', 25),
(58, '2026_02_22_125718_add_notas_to_cotizacion_table', 26),
(59, '2026_02_22_143623_create_logos_table', 27),
(60, '2026_02_23_140000_create_colores_table', 28),
(61, '2026_02_23_170000_create_tallas_table', 29),
(62, '2026_02_23_201000_create_bordado_ubicaciones_table', 30),
(63, '2026_02_23_201100_create_detalle_cotizacion_bordados_table', 30),
(64, '2026_02_23_201200_create_detalle_pedido_bordados_table', 30),
(65, '2026_02_23_201300_migrate_legacy_bordado_fields_and_drop_columns', 30),
(66, '2026_02_23_230000_add_nombre_logo_aplicado_to_detalle_bordados_tables', 31),
(67, '2026_03_06_155710_rename_operario_id_to_empleado_id_in_produccion_diaria', 32),
(68, '2026_03_19_000001_cr03_enum_estado_prioridad_pedido_cotizacion', 33),
(69, '2026_03_19_000002_cr01_color_talla_fk_detalle_cotizacion_pedido', 34),
(70, '2026_03_19_000003_me01_indices_faltantes', 35),
(71, '2026_03_19_000004_me04_fecha_produccion_produccion_diaria', 36),
(72, '2026_03_19_000005_me06_fecha_fin_real_orden_produccion', 37),
(73, '2026_03_19_000006_batch_me05_me03_ba01_to_ba06', 38),
(74, '2026_03_19_000007_cr06_created_by_on_delete_restrict', 39),
(75, '2026_03_19_000008_ba03_rename_estado_persona_to_estado_geografico', 40),
(76, '2026_03_19_000009_cr05_logo_id_fk_orden_produccion', 41),
(77, '2026_03_19_000010_cr02_normalizar_proveedores_juridicos', 42),
(78, '2026_03_19_200001_cr07_cr01_softdeletes_user_restrict_fks', 43),
(79, '2026_03_19_200002_cr06_cr05_fks_banco_pedido_unique_persona', 43),
(80, '2026_03_19_200003_cr02_cr03_me03_cascade_to_restrict', 43),
(81, '2026_03_19_200004_me01_me02_me05_ba03_unify_persona_fks', 43),
(82, '2026_03_19_200005_me04_ba01_composite_indexes', 43),
(83, '2026_03_19_200006_ba05_enum_cancelada_cotizacion', 44),
(84, '2026_03_19_200007_me06_rename_plural_tables_to_singular', 45),
(85, '2026_03_19_200008_ba02_softdeletes_catalogos_maestros', 46),
(86, '2026_03_19_200009_ba04_create_pago_pedido_migrate_data', 47),
(87, '2026_03_19_200010_ba04_drop_flat_payment_columns_from_pedido', 48),
(88, '2026_03_19_200011_me07_logo_id_fk_bordados_drop_nombre_logo', 49),
(89, '2026_04_14_140742_normalize_unidad_medida_in_insumos', 50),
(90, '2026_04_21_000001_create_departamento_table', 51),
(91, '2026_04_21_000002_create_cargo_table', 51),
(92, '2026_04_21_000003_normalize_departamento_cargo_in_empleado', 51),
(93, '2026_04_26_000001_create_user_recovery_question_table', 52),
(94, '2026_04_26_000002_create_recovery_attempt_table', 52),
(95, '2026_04_26_000003_add_recovery_columns_to_user_table', 52),
(96, '2026_04_26_000004_add_password_reset_flag_to_user_table', 53),
(97, '2026_05_07_100000_create_atributo_table', 54),
(98, '2026_05_07_100001_create_atributo_valor_table', 54),
(99, '2026_05_07_100002_create_tipo_producto_atributo_table', 54),
(100, '2026_05_07_100003_create_producto_atributo_valor_table', 54),
(101, '2026_05_07_100004_add_codigo_to_insumo', 54),
(102, '2026_05_07_100005_add_precio_confeccion_and_requiere_tela_to_tipo_producto', 54),
(103, '2026_05_07_100006_add_insumo_tela_and_atributos_to_producto', 54),
(104, '2026_05_07_100007_add_snapshots_to_detalle_cotizacion', 54),
(105, '2026_05_07_100008_add_snapshots_to_detalle_pedido', 54),
(106, '2026_05_07_100009_modelo_nullable_in_producto', 54),
(107, '2026_05_07_100010_rename_codigo_prefijo_to_prefijo_in_tipo_producto', 54),
(108, '2026_05_07_100011_drop_modelo_from_producto', 54),
(109, '2026_05_28_004004_add_is_inventoriable_to_insumo_table', 55),
(110, '2026_06_01_142640_add_tasa_y_condiciones_to_cotizacion_table', 56),
(111, '2026_05_27_220125_op_redesign_orden_produccion_empleado_detalle', 57),
(112, '2026_05_27_222657_op_drop_produccion_diaria_add_defectuosa', 57),
(113, '2026_05_28_185713_drop_proveedor_id_from_insumo', 57),
(114, '2026_05_28_191504_create_tipo_producto_insumo_table', 57),
(115, '2026_05_28_194636_add_consumo_tela_por_unidad_to_tipo_producto', 57),
(116, '2026_05_29_110403_drop_costo_estimado_from_orden_produccion', 57),
(117, '2026_05_31_000000_add_stock_maximo_to_insumo_table', 57),
(118, '2026_06_02_000001_create_compra_table', 58),
(119, '2026_06_02_000002_create_compra_detalle_table', 58);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_insumo`
--

CREATE TABLE `movimiento_insumo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `insumo_id` bigint(20) UNSIGNED NOT NULL,
  `tipo_movimiento` enum('Entrada','Salida') NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `stock_anterior` decimal(10,2) NOT NULL,
  `stock_nuevo` decimal(10,2) NOT NULL,
  `motivo` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `movimiento_insumo`
--

INSERT INTO `movimiento_insumo` (`id`, `insumo_id`, `tipo_movimiento`, `cantidad`, `stock_anterior`, `stock_nuevo`, `motivo`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Entrada', 1000.00, 0.00, 1000.00, 'Inventario inicial', 2, '2025-12-04 18:58:28', '2025-12-04 18:58:28'),
(2, 2, 'Entrada', 5000.00, 0.00, 5000.00, 'Compra inicial', 2, '2025-12-04 18:58:28', '2025-12-04 18:58:28'),
(3, 1, 'Salida', 5.00, 1000.00, 995.00, 'Consumo por Pedido #1 - Producto: Polo Clásico', 1, '2025-12-10 23:24:45', '2025-12-10 23:24:45'),
(4, 3, 'Salida', 20.00, 50.00, 30.00, 'Vendidos a una costurera', 1, '2025-12-11 00:40:50', '2025-12-11 00:40:50'),
(5, 1, 'Salida', 4.00, 995.00, 991.00, 'Consumo por Pedido #2 - Producto: Camisa Oxford Clasica', 1, '2025-12-16 20:47:53', '2025-12-16 20:47:53'),
(6, 1, 'Salida', 2.00, 991.00, 989.00, 'Consumo por Pedido #3 - Producto: Camisa Oxford Clasica', 1, '2025-12-18 17:45:32', '2025-12-18 17:45:32'),
(7, 1, 'Salida', 4.00, 989.00, 985.00, 'Consumo por Pedido #4 - Producto: Chemise Cuello Mao', 1, '2025-12-19 20:28:18', '2025-12-19 20:28:18'),
(8, 1, 'Entrada', 4.00, 985.00, 989.00, 'Reversión por actualización de Pedido #4 - Producto: Chemise Cuello Mao', 5, '2026-01-16 17:28:16', '2026-01-16 17:28:16'),
(9, 1, 'Salida', 4.00, 989.00, 985.00, 'Consumo por actualización de Pedido #4 - Producto: Chemise Cuello Mao', 5, '2026-01-16 17:28:16', '2026-01-16 17:28:16'),
(10, 1, 'Entrada', 4.00, 985.00, 989.00, 'Reversión por actualización de Pedido #4 - Producto: Chemise Cuello Mao', 5, '2026-01-16 17:59:27', '2026-01-16 17:59:27'),
(11, 2, 'Salida', 4.00, 5000.00, 4996.00, 'Consumo por actualización de Pedido #4 - Producto: CE-001', 5, '2026-01-16 17:59:27', '2026-01-16 17:59:27'),
(12, 2, 'Entrada', 4.00, 4996.00, 5000.00, 'Reversión por actualización de Pedido #4 - Producto: CE-001', 5, '2026-01-16 18:00:46', '2026-01-16 18:00:46'),
(13, 2, 'Salida', 4.00, 5000.00, 4996.00, 'Consumo por actualización de Pedido #4 - Producto: Chemise Cuello Mao', 5, '2026-01-16 18:00:46', '2026-01-16 18:00:46'),
(14, 1, 'Salida', 4.00, 989.00, 985.00, 'Consumo por actualización de Pedido #4 - Producto: Chemise Cuello Mao', 5, '2026-01-16 18:00:46', '2026-01-16 18:00:46'),
(15, 1, 'Entrada', 4.00, 985.00, 989.00, 'Reversión por actualización de Pedido #2 - Producto: Camisa Oxford Clasica', 5, '2026-01-16 18:07:36', '2026-01-16 18:07:36'),
(16, 1, 'Salida', 4.00, 989.00, 985.00, 'Consumo por actualización de Pedido #2 - Producto: Camisa Oxford Clasica', 5, '2026-01-16 18:07:36', '2026-01-16 18:07:36'),
(17, 1, 'Entrada', 5.00, 985.00, 990.00, 'Reversión por actualización de Pedido #1 - Producto: Chemise Clasica', 5, '2026-01-16 18:07:49', '2026-01-16 18:07:49'),
(18, 1, 'Salida', 5.00, 990.00, 985.00, 'Consumo por actualización de Pedido #1 - Producto: Chemise Clasica', 5, '2026-01-16 18:07:49', '2026-01-16 18:07:49'),
(19, 2, 'Salida', 1.00, 4996.00, 4995.00, 'Consumo por Pedido #5 - Producto: Chemise Clasica', 5, '2026-01-17 16:59:42', '2026-01-17 16:59:42'),
(20, 2, 'Salida', 4.00, 4995.00, 4991.00, 'Consumo por Pedido #6 - Producto: Chemise Clasica', 5, '2026-01-17 22:33:53', '2026-01-17 22:33:53'),
(21, 2, 'Entrada', 4.00, 4991.00, 4995.00, 'Reversión por actualización de Pedido #6 - Producto: Chemise Clasica', 5, '2026-01-17 22:34:12', '2026-01-17 22:34:12'),
(22, 2, 'Salida', 4.00, 4995.00, 4991.00, 'Consumo por actualización de Pedido #6 - Producto: Chemise Clasica', 5, '2026-01-17 22:34:12', '2026-01-17 22:34:12'),
(23, 2, 'Entrada', 1.00, 4991.00, 4992.00, 'Reversión por actualización de Pedido #5 - Producto: Chemise Clasica', 5, '2026-01-17 22:34:28', '2026-01-17 22:34:28'),
(24, 2, 'Salida', 1.00, 4992.00, 4991.00, 'Consumo por actualización de Pedido #5 - Producto: Chemise Clasica', 5, '2026-01-17 22:34:28', '2026-01-17 22:34:28'),
(25, 2, 'Salida', 4.00, 4991.00, 4987.00, 'Consumo por Pedido #7 - Producto: Chemise Clasica', 5, '2026-01-17 22:36:10', '2026-01-17 22:36:10'),
(26, 2, 'Entrada', 4.00, 4987.00, 4991.00, 'Reversión por actualización de Pedido #7 - Producto: Chemise Clasica', 5, '2026-01-17 23:23:25', '2026-01-17 23:23:25'),
(27, 2, 'Salida', 4.00, 4991.00, 4987.00, 'Consumo por actualización de Pedido #7 - Producto: Chemise Clasica', 5, '2026-01-17 23:23:25', '2026-01-17 23:23:25'),
(28, 3, 'Salida', 2.00, 30.00, 28.00, 'chemise', 5, '2026-01-18 23:59:30', '2026-01-18 23:59:30'),
(29, 3, 'Salida', 28.00, 28.00, 0.00, 'chemises', 5, '2026-01-19 00:01:47', '2026-01-19 00:01:47'),
(30, 2, 'Salida', 2.00, 4987.00, 4985.00, 'Consumo por Pedido #8 - Producto: Chemise Clasica', 5, '2026-01-19 03:37:08', '2026-01-19 03:37:08'),
(31, 2, 'Salida', 2.00, 4985.00, 4983.00, 'Consumo por Pedido #9 - Producto: Chemise Clasica', 5, '2026-01-19 03:42:07', '2026-01-19 03:42:07'),
(32, 2, 'Salida', 2.00, 4983.00, 4981.00, 'Consumo por Pedido #10 - Producto: CE-001', 5, '2026-01-19 04:36:20', '2026-01-19 04:36:20'),
(33, 5, 'Entrada', 10.00, 100.00, 110.00, 'pues llegaron 10 mas', 1, '2026-01-20 20:36:39', '2026-01-20 20:36:39'),
(34, 5, 'Salida', 10.00, 110.00, 100.00, 'Se gastaron', 1, '2026-01-20 20:38:10', '2026-01-20 20:38:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_produccion`
--

CREATE TABLE `orden_produccion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pedido_id` bigint(20) UNSIGNED DEFAULT NULL,
  `detalle_pedido_id` bigint(20) UNSIGNED DEFAULT NULL,
  `producto_id` bigint(20) UNSIGNED NOT NULL,
  `empleado_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cantidad_solicitada` int(11) NOT NULL,
  `cantidad_producida` int(11) NOT NULL DEFAULT 0,
  `cantidad_defectuosa` int(11) NOT NULL DEFAULT 0,
  `fecha_inicio` date NOT NULL,
  `fecha_fin_estimada` date NOT NULL,
  `fecha_fin_real` date DEFAULT NULL,
  `estado` enum('Pendiente','En Proceso','Finalizado','Cancelado') NOT NULL DEFAULT 'Pendiente',
  `notas` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `orden_produccion`
--

INSERT INTO `orden_produccion` (`id`, `pedido_id`, `detalle_pedido_id`, `producto_id`, `empleado_id`, `cantidad_solicitada`, `cantidad_producida`, `cantidad_defectuosa`, `fecha_inicio`, `fecha_fin_estimada`, `fecha_fin_real`, `estado`, `notas`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, 3, NULL, 5, NULL, 1, 0, 0, '2025-12-18', '2026-01-28', NULL, 'Pendiente', 'urgenteeeee', 1, '2025-12-18 17:51:44', '2025-12-19 18:43:30', '2025-12-19 18:43:30'),
(4, 3, NULL, 5, NULL, 1, 0, 0, '2025-12-18', '2026-01-28', NULL, 'Pendiente', 'urgente', 1, '2025-12-18 18:00:17', '2025-12-19 18:43:21', '2025-12-19 18:43:21'),
(5, 3, NULL, 5, NULL, 1, 0, 0, '2025-12-19', '2026-01-28', NULL, 'Pendiente', 'urgente', 1, '2025-12-19 18:44:18', '2026-01-17 16:56:43', '2026-01-17 16:56:43'),
(6, 3, NULL, 5, NULL, 1, 0, 0, '2026-01-16', '2026-01-28', NULL, 'Finalizado', NULL, 5, '2026-01-16 18:10:08', '2026-01-16 18:10:26', NULL),
(7, 5, NULL, 1, NULL, 4, 0, 0, '2026-01-17', '2026-01-20', NULL, 'Finalizado', NULL, 5, '2026-01-17 17:01:08', '2026-01-17 17:35:23', NULL),
(8, 56, NULL, 6, NULL, 5, 6, 0, '2026-03-06', '2026-05-20', NULL, 'Finalizado', NULL, 7, '2026-03-06 18:38:41', '2026-03-06 20:22:51', NULL),
(9, 56, NULL, 6, NULL, 5, 5, 0, '2026-03-06', '2026-05-20', NULL, 'Finalizado', 'hacerlo bien', 7, '2026-03-06 20:28:00', '2026-03-06 20:28:27', NULL),
(10, 54, NULL, 8, NULL, 10, 10, 0, '2026-03-06', '2026-03-08', NULL, 'En Proceso', 'hola', 7, '2026-03-06 20:43:01', '2026-03-06 21:04:25', NULL),
(11, 58, NULL, 8, NULL, 1, 1, 0, '2026-03-18', '2026-03-19', NULL, 'Finalizado', NULL, 7, '2026-03-18 14:10:20', '2026-03-18 14:10:39', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago_pedido`
--

CREATE TABLE `pago_pedido` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pedido_id` bigint(20) UNSIGNED NOT NULL,
  `metodo` enum('efectivo','transferencia','pago_movil') NOT NULL,
  `monto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `banco_id` bigint(20) UNSIGNED DEFAULT NULL,
  `referencia` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pago_pedido`
--

INSERT INTO `pago_pedido` (`id`, `pedido_id`, `metodo`, `monto`, `banco_id`, `referencia`, `created_at`, `updated_at`) VALUES
(1, 5, 'efectivo', 11.00, NULL, NULL, '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(2, 6, 'efectivo', 7.00, NULL, NULL, '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(3, 8, 'efectivo', 22.00, NULL, NULL, '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(4, 10, 'efectivo', 33.00, NULL, NULL, '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(5, 12, 'efectivo', 20.00, NULL, NULL, '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(6, 13, 'efectivo', 900.00, NULL, NULL, '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(7, 14, 'efectivo', 44.00, NULL, NULL, '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(8, 15, 'efectivo', 300.00, NULL, NULL, '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(9, 30, 'efectivo', 40.00, NULL, NULL, '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(10, 42, 'efectivo', 20.00, NULL, NULL, '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(11, 43, 'pago_movil', 10.00, 15, '56465', '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(12, 43, 'efectivo', 0.00, NULL, NULL, '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(13, 44, 'transferencia', 20.00, 13, '4534538423', '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(14, 44, 'pago_movil', 0.00, 16, '4231325523', '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(15, 44, 'efectivo', 0.00, NULL, NULL, '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(16, 54, 'efectivo', 10.00, NULL, NULL, '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(17, 55, 'efectivo', 100.00, NULL, NULL, '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(18, 56, 'transferencia', 100.00, 2, '43242342', '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(19, 57, 'transferencia', 20.00, 2, '4543534636', '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(20, 57, 'pago_movil', 0.00, 14, '12379847239', '2026-03-19 21:04:11', '2026-03-19 21:04:11'),
(22, 9, 'efectivo', 22.00, NULL, NULL, '2026-03-19 21:05:00', '2026-03-19 21:05:00'),
(23, 11, 'efectivo', 33.00, NULL, NULL, '2026-03-19 21:05:00', '2026-03-19 21:05:00'),
(24, 46, 'efectivo', 300.00, NULL, NULL, '2026-03-19 21:05:00', '2026-03-19 21:05:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `password_resets`
--

INSERT INTO `password_resets` (`email`, `token`, `created_at`) VALUES
('admin@gmail.com', '$2y$12$fMwLn7TO4DrrtysDHd1SgOJbk4HCA.h71MW6tNNbPnd1TMpUIvXZe', '2026-04-14 23:38:32'),
('vanessalopez090551@gmail.com', '$2y$12$y7iiZTcAZq9wHPrufhpSK./ZStflchwZvYdOigU4/YZJlzXQWr6C2', '2026-01-15 00:32:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cotizacion_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cliente_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fecha_pedido` date NOT NULL,
  `fecha_entrega_estimada` date DEFAULT NULL,
  `estado` enum('Pendiente','Procesando','Completado','Cancelado') NOT NULL DEFAULT 'Pendiente',
  `prioridad` enum('Normal','Alta','Urgente') NOT NULL DEFAULT 'Normal',
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `abono` decimal(10,2) NOT NULL DEFAULT 0.00,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`id`, `cotizacion_id`, `cliente_id`, `fecha_pedido`, `fecha_entrega_estimada`, `estado`, `prioridad`, `total`, `abono`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, 7, '2025-12-10', '2026-01-07', 'Procesando', 'Normal', 29.90, 0.00, 1, '2025-12-10 23:24:45', '2026-01-17 16:58:05', '2026-01-17 16:58:05'),
(2, NULL, 7, '2025-12-16', '2025-12-31', 'Cancelado', 'Normal', 50.00, 0.00, 1, '2025-12-16 20:47:53', '2026-01-17 16:58:01', '2026-01-17 16:58:01'),
(3, NULL, 3, '2025-12-18', '2026-01-30', 'Pendiente', 'Urgente', 25.00, 0.00, 1, '2025-12-18 17:45:32', '2026-01-17 16:57:54', '2026-01-17 16:57:54'),
(4, 7, 1, '2025-12-20', '2026-01-16', 'Completado', 'Urgente', 34.00, 4.00, 1, '2025-12-19 20:28:18', '2026-01-17 16:57:58', '2026-01-17 16:57:58'),
(5, 8, 9, '2026-01-17', '2026-01-22', 'Cancelado', 'Normal', 119.60, 11.00, 5, '2026-01-17 16:59:42', '2026-01-17 22:34:28', NULL),
(6, 9, 12, '2026-01-24', '2026-01-31', 'Completado', 'Normal', 119.60, 7.00, 5, '2026-01-17 22:33:53', '2026-01-17 22:34:12', NULL),
(7, 10, 8, '2026-01-24', '2026-01-31', 'Procesando', 'Normal', 119.60, 0.00, 5, '2026-01-17 22:36:10', '2026-01-17 23:23:25', NULL),
(8, NULL, 14, '2026-01-18', '2026-01-20', 'Pendiente', 'Normal', 388.70, 22.00, 5, '2026-01-19 03:37:08', '2026-01-19 03:37:08', NULL),
(9, 11, 14, '2026-01-18', '2026-01-20', 'Pendiente', 'Normal', 388.70, 22.00, 5, '2026-01-19 03:42:07', '2026-01-19 03:42:07', NULL),
(10, 15, 20, '2026-01-18', '2026-01-20', 'Pendiente', 'Normal', 1317.80, 33.00, 5, '2026-01-19 04:36:20', '2026-01-19 04:36:20', NULL),
(11, 14, 9, '2026-01-19', '2026-01-21', 'Pendiente', 'Alta', 1317.80, 33.00, 1, '2026-01-19 19:13:45', '2026-01-19 19:13:45', NULL),
(12, 13, 15, '2026-01-19', '2026-01-23', 'Pendiente', 'Normal', 25.00, 20.00, 1, '2026-01-19 19:24:34', '2026-01-19 19:24:34', NULL),
(13, 19, 9, '2026-01-19', '2026-01-23', 'Pendiente', 'Normal', 986.70, 900.00, 1, '2026-01-19 19:32:56', '2026-01-19 19:32:56', NULL),
(14, 18, 21, '2026-01-19', '2026-01-21', 'Pendiente', 'Normal', 627.00, 44.00, 1, '2026-01-19 19:51:33', '2026-01-19 19:51:33', NULL),
(15, 17, 15, '2026-01-19', '2026-01-23', 'Pendiente', 'Normal', 2963.40, 300.00, 1, '2026-01-19 20:16:20', '2026-01-19 20:16:20', NULL),
(16, 20, 22, '2026-01-20', '2026-01-27', 'Pendiente', 'Normal', 29.90, 0.00, 1, '2026-01-20 21:21:33', '2026-01-20 21:21:33', NULL),
(17, 21, 23, '2026-01-20', '2026-01-27', 'Pendiente', 'Normal', 51.00, 0.00, 1, '2026-01-20 21:25:39', '2026-01-20 21:25:39', NULL),
(18, 22, 23, '2026-01-20', '2026-01-27', 'Pendiente', 'Normal', 358.80, 0.00, 1, '2026-01-20 21:32:04', '2026-01-20 21:32:04', NULL),
(19, 16, 9, '2026-01-20', '2026-01-27', 'Pendiente', 'Normal', 374.00, 0.00, 1, '2026-01-20 21:34:18', '2026-01-20 21:34:18', NULL),
(20, 23, 23, '2026-01-20', '2026-01-27', 'Pendiente', 'Normal', 190.00, 0.00, 1, '2026-01-20 21:39:09', '2026-01-20 21:39:09', NULL),
(21, 24, 14, '2026-01-20', '2026-01-27', 'Pendiente', 'Normal', 29.90, 0.00, 1, '2026-01-20 21:45:49', '2026-01-20 21:45:49', NULL),
(22, 25, 23, '2026-01-20', '2026-01-27', 'Pendiente', 'Normal', 29.90, 0.00, 1, '2026-01-20 21:52:03', '2026-01-20 21:52:03', NULL),
(23, 26, 23, '2026-01-20', '2026-01-27', 'Pendiente', 'Normal', 59.80, 0.00, 1, '2026-01-20 21:56:29', '2026-01-20 21:56:29', NULL),
(24, 27, 23, '2026-01-20', '2026-01-27', 'Pendiente', 'Normal', 29.90, 0.00, 1, '2026-01-20 22:01:40', '2026-01-20 22:01:40', NULL),
(25, 28, 23, '2026-01-20', '2026-01-27', 'Pendiente', 'Normal', 358.80, 0.00, 1, '2026-01-20 22:21:37', '2026-01-20 22:22:03', NULL),
(26, 29, 23, '2026-02-19', '2026-02-26', 'Pendiente', 'Normal', 17.00, 0.00, 1, '2026-02-19 20:07:49', '2026-02-19 20:07:49', NULL),
(27, 30, 23, '2026-02-19', NULL, 'Pendiente', 'Normal', 204.00, 0.00, 1, '2026-02-19 20:53:28', '2026-02-19 20:53:28', NULL),
(28, 31, 23, '2026-02-19', NULL, 'Pendiente', 'Normal', 204.00, 0.00, 1, '2026-02-20 00:04:19', '2026-02-20 00:04:19', NULL),
(29, 32, 23, '2026-02-19', NULL, 'Pendiente', 'Normal', 156.00, 0.00, 1, '2026-02-20 00:28:11', '2026-02-20 00:28:11', NULL),
(30, 33, 23, '2026-02-19', '2026-02-28', 'Pendiente', 'Alta', 578.00, 40.00, 1, '2026-02-20 00:32:25', '2026-02-20 00:42:11', NULL),
(31, 34, 23, '2026-02-19', NULL, 'Pendiente', 'Normal', 51.00, 0.00, 1, '2026-02-20 00:43:39', '2026-02-20 00:43:39', NULL),
(32, 35, 23, '2026-02-19', NULL, 'Pendiente', 'Normal', 85.00, 0.00, 1, '2026-02-20 00:52:16', '2026-02-20 00:52:16', NULL),
(33, 36, 23, '2026-02-19', NULL, 'Pendiente', 'Normal', 170.00, 0.00, 1, '2026-02-20 01:01:25', '2026-02-20 01:01:25', NULL),
(34, 37, 23, '2026-02-19', NULL, 'Pendiente', 'Normal', 340.00, 0.00, 1, '2026-02-20 01:04:25', '2026-02-20 01:04:25', NULL),
(35, 38, 9, '2026-02-22', NULL, 'Pendiente', 'Normal', 204.00, 0.00, 1, '2026-02-22 20:52:23', '2026-02-22 20:52:23', NULL),
(36, 39, 9, '2026-02-23', NULL, 'Pendiente', 'Normal', 170.00, 0.00, 1, '2026-02-23 20:00:14', '2026-02-23 20:00:14', NULL),
(37, 43, 8, '2026-02-23', NULL, 'Pendiente', 'Normal', 94.50, 0.00, 1, '2026-02-24 02:54:17', '2026-02-24 02:57:23', '2026-02-24 02:57:23'),
(38, 44, 8, '2026-02-23', NULL, 'Pendiente', 'Normal', 94.50, 0.00, 1, '2026-02-24 02:58:25', '2026-02-24 02:58:25', '2026-02-24 02:58:25'),
(39, 45, 8, '2026-02-23', NULL, 'Pendiente', 'Normal', 94.50, 0.00, 1, '2026-02-24 03:26:39', '2026-02-24 03:26:40', '2026-02-24 03:26:40'),
(40, 46, 9, '2026-02-24', NULL, 'Pendiente', 'Normal', 253.00, 0.00, 1, '2026-02-24 15:51:39', '2026-02-24 15:51:39', NULL),
(41, 47, 8, '2026-02-24', NULL, 'Pendiente', 'Normal', 94.50, 0.00, 1, '2026-02-24 16:31:27', '2026-02-24 16:31:28', '2026-02-24 16:31:28'),
(42, 42, 23, '2026-02-24', '2026-04-25', 'Pendiente', 'Normal', 200.00, 20.00, 1, '2026-02-24 16:36:47', '2026-02-24 16:37:21', NULL),
(43, 48, 23, '2026-02-24', '2026-03-28', 'Pendiente', 'Normal', 260.00, 10.00, 1, '2026-02-24 17:26:11', '2026-02-24 17:58:15', NULL),
(44, 41, 23, '2026-02-24', '2026-02-27', 'Pendiente', 'Normal', 25.00, 20.00, 1, '2026-02-24 19:41:36', '2026-02-24 19:42:24', NULL),
(45, 40, 23, '2026-02-24', NULL, 'Pendiente', 'Normal', 170.00, 0.00, 1, '2026-02-24 20:05:58', '2026-02-24 20:05:58', NULL),
(46, 49, 28, '2026-02-26', '2026-03-29', 'Pendiente', 'Normal', 470.00, 300.00, 1, '2026-02-26 20:33:31', '2026-02-26 20:35:51', NULL),
(47, 50, 28, '2026-02-26', NULL, 'Pendiente', 'Normal', 34.00, 0.00, 1, '2026-02-26 20:36:54', '2026-02-26 20:36:54', NULL),
(48, 51, 28, '2026-02-26', NULL, 'Pendiente', 'Normal', 17.00, 0.00, 1, '2026-02-26 20:40:21', '2026-02-26 20:40:21', NULL),
(49, 52, 28, '2026-02-26', NULL, 'Pendiente', 'Normal', 51.00, 0.00, 1, '2026-02-26 20:42:31', '2026-02-26 20:42:31', NULL),
(50, 53, 23, '2026-02-26', NULL, 'Pendiente', 'Normal', 170.00, 0.00, 1, '2026-02-26 20:49:46', '2026-02-26 20:49:46', NULL),
(51, 54, 23, '2026-02-26', NULL, 'Pendiente', 'Normal', 17.00, 0.00, 1, '2026-02-26 20:50:33', '2026-02-26 20:50:33', NULL),
(52, 55, 23, '2026-02-26', NULL, 'Pendiente', 'Normal', 16.00, 0.00, 1, '2026-02-26 20:54:22', '2026-02-26 20:54:22', NULL),
(53, 56, 23, '2026-02-26', NULL, 'Pendiente', 'Normal', 16.00, 0.00, 1, '2026-02-26 21:01:40', '2026-02-26 21:01:40', NULL),
(54, 57, 36, '2026-03-05', '2026-03-10', 'Pendiente', 'Alta', 190.00, 10.00, 7, '2026-03-05 17:32:34', '2026-03-05 17:33:27', NULL),
(55, 58, 36, '2026-03-05', '2026-03-19', 'Pendiente', 'Alta', 200.00, 100.00, 7, '2026-03-05 17:47:46', '2026-03-05 17:48:24', NULL),
(56, 59, 36, '2026-03-05', '2026-05-22', 'Pendiente', 'Normal', 110.00, 100.00, 7, '2026-03-05 20:13:13', '2026-03-05 20:14:11', NULL),
(57, 61, 23, '2026-03-10', '2026-03-31', 'Pendiente', 'Alta', 200.00, 20.00, 7, '2026-03-10 20:54:39', '2026-03-10 20:57:35', NULL),
(58, 62, 23, '2026-03-18', '2026-03-20', 'Pendiente', 'Normal', 16.00, 0.00, 7, '2026-03-18 14:07:18', '2026-03-23 03:12:12', NULL),
(59, 63, 8, '2026-03-19', NULL, 'Pendiente', 'Normal', 94.50, 0.00, 1, '2026-03-19 21:35:32', '2026-03-19 21:35:33', '2026-03-19 21:35:33'),
(60, 64, 8, '2026-03-20', NULL, 'Pendiente', 'Normal', 94.50, 0.00, 1, '2026-03-20 13:17:33', '2026-03-20 13:17:34', '2026-03-20 13:17:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `persona`
--

CREATE TABLE `persona` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `documento_identidad` varchar(50) NOT NULL,
  `tipo_documento` enum('V-','E-','J-','G-') NOT NULL DEFAULT 'V-',
  `email` varchar(255) DEFAULT NULL,
  `estado_geografico` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` enum('M','F','Otro') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `persona`
--

INSERT INTO `persona` (`id`, `nombre`, `apellido`, `documento_identidad`, `tipo_documento`, `email`, `estado_geografico`, `fecha_nacimiento`, `genero`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Administrador', 'Sistema', '00000001', 'V-', 'admin@gmail.com', NULL, NULL, NULL, '2025-12-04 18:58:27', '2025-12-04 18:58:27', NULL),
(2, 'El', 'Supervisor', '00000002', 'V-', NULL, 'Portuguesa', '2003-04-08', 'M', '2025-12-04 18:58:28', '2025-12-08 14:30:58', NULL),
(3, 'James David', 'Vance', '8889292', 'G-', 'jdvance@gmail.com', 'Washington DC', '1980-04-04', 'M', '2025-12-04 20:19:19', '2025-12-04 20:19:19', NULL),
(4, 'Jose Luis', 'Rodriguez', '4567899', 'V-', 'isavale10@gmail.com', 'Portuguesa', '2005-03-31', 'M', '2025-12-05 20:14:04', '2025-12-05 20:14:04', NULL),
(6, 'Peter', 'Thiel', '8769044', 'E-', 'pltrinvest@gmail.com', NULL, NULL, NULL, '2025-12-08 19:55:18', '2025-12-08 20:23:37', NULL),
(7, 'Larry', 'Ellison', '545683666', 'E-', 'oraclecorporation@gmail.com', NULL, NULL, NULL, '2025-12-08 19:55:18', '2025-12-08 20:23:29', NULL),
(8, 'Leslie Herbert', 'Wexner', '6233455', 'E-', 'vsecret@gmail.com', NULL, NULL, NULL, '2025-12-08 20:04:57', '2025-12-08 20:23:20', NULL),
(9, 'Robert', 'Maxwell', '987489', 'E-', 'maxwellcorporation@gmail.com', NULL, NULL, NULL, '2025-12-08 20:19:32', '2025-12-08 20:22:30', NULL),
(10, 'Jose', 'Juan', '7499586', 'E-', 'nvidiaceo@gmail.com', NULL, NULL, NULL, '2025-12-09 18:54:35', '2025-12-09 18:56:47', NULL),
(11, 'Alexander Caedmon', 'Karp', '89320234', 'E-', 'alexkpr@gmail.com', NULL, NULL, NULL, '2025-12-10 18:09:48', '2025-12-10 18:09:48', NULL),
(12, 'Mark', 'Zuckerberg', '18728555', 'V-', 'facebook@gmail.com', NULL, NULL, NULL, '2025-12-10 20:29:40', '2025-12-10 20:31:20', NULL),
(13, 'Santiago Joseito de la Concepcion', 'Mendoza', '30822318', 'V-', 'santitron@gmail.com', 'Portuguesa', '2005-01-11', 'M', '2025-12-10 20:57:36', '2026-01-25 03:55:31', NULL),
(14, 'Mark', 'Cuban', '6786543', 'E-', 'markcu@gmail.com', 'Texas', '2004-03-10', 'M', '2025-12-10 21:09:58', '2025-12-10 21:09:58', NULL),
(15, 'Vanessa', 'diaz', '30966655', 'V-', 'vanessalopez090551@gmail.com', NULL, NULL, NULL, '2026-01-17 16:51:52', '2026-01-17 16:51:52', NULL),
(16, 'valeria', 'diaz', '32152373', 'V-', 'valediaz@gmail.com', NULL, NULL, NULL, '2026-01-17 17:11:09', '2026-01-17 17:11:09', NULL),
(17, 'cvane', '', '30966654', 'V-', NULL, NULL, NULL, NULL, '2026-01-17 22:05:23', '2026-01-17 22:05:23', NULL),
(18, 'alalalallaa', 'kneoucnewuivcw', '30966659', 'V-', 'alalallala@email.com', NULL, NULL, NULL, '2026-01-17 22:31:33', '2026-01-17 22:31:33', NULL),
(19, 'Cleymar', 'Mendoza', '30966271', 'V-', 'cley@gmail.com', NULL, NULL, NULL, '2026-01-18 03:49:00', '2026-01-18 03:49:00', NULL),
(20, 'Cleymar', 'Mendoza', '30966275', 'V-', 'cleymar@gmail.com', NULL, NULL, NULL, '2026-01-18 03:56:57', '2026-01-18 03:56:57', NULL),
(22, 'Victor', 'Mendoza', '12344093', 'V-', 'victorm@gmail.com', NULL, NULL, NULL, '2026-01-18 20:23:50', '2026-01-18 20:23:50', NULL),
(23, 'Luis Alberto', 'Mendoza García', '15789234', 'V-', 'luismendoza@gmail.com', NULL, NULL, NULL, '2026-01-18 20:37:01', '2026-01-18 20:37:01', NULL),
(24, 'Rosa María', 'Hernández López', '18234567', 'V-', 'rosahdez@hotmail.com', NULL, NULL, NULL, '2026-01-18 20:38:40', '2026-01-18 20:38:40', NULL),
(25, 'Carlos Eduardo', 'Silva Martínez', '84567890', 'E-', 'carlossilva@gmail.com', NULL, NULL, NULL, '2026-01-18 20:41:05', '2026-01-18 20:41:05', NULL),
(26, 'Angela Patricia', 'Vargas Rojas', '12456789', 'V-', 'angelavargas@gmail.com', NULL, NULL, NULL, '2026-01-18 20:43:53', '2026-01-18 20:43:53', NULL),
(27, 'María Fernanda', 'Gutiérrez Méndez', '16823456', 'V-', 'mariagutierrez@gmail.com', NULL, NULL, NULL, '2026-01-18 20:57:49', '2026-01-18 20:57:49', NULL),
(28, 'Pedro Antonio', 'Briceño Rivas', '19876543', 'V-', 'pedrobriceno@gmail.com', NULL, NULL, NULL, '2026-01-18 21:52:28', '2026-01-18 21:52:28', NULL),
(29, 'Angely', 'Canelon', '37782737', 'V-', 'loca123@gmail.com', NULL, NULL, NULL, '2026-01-19 00:25:34', '2026-01-19 00:25:34', NULL),
(30, 'Alejandro', 'Abreu', '31558506', 'V-', 'ale@gmail.com', NULL, NULL, NULL, '2026-01-19 03:56:04', '2026-01-19 03:56:04', NULL),
(31, 'alejandro', 'abreu', '31558507', 'V-', 'ale2@gmail.com', NULL, NULL, NULL, '2026-01-19 04:01:50', '2026-01-19 04:01:50', NULL),
(32, 'alejandro', 'diaz', '31558508', 'V-', 'vd6955291@gmail.com', NULL, NULL, NULL, '2026-01-19 04:05:33', '2026-01-19 04:05:33', NULL),
(33, 'josefina', 'lopez', '31558509', 'V-', 'vd695529221@gmail.com', NULL, NULL, NULL, '2026-01-19 04:17:44', '2026-01-19 04:17:44', NULL),
(34, 'angel', 'Canelon', '37782735', 'V-', 'loca1233@gmail.com', NULL, NULL, NULL, '2026-01-19 04:26:34', '2026-01-19 04:26:34', NULL),
(35, 'abby', 'chuela', '31558599', 'V-', 'abbychuela@gmail.com', NULL, NULL, NULL, '2026-01-19 16:49:16', '2026-01-19 16:49:16', NULL),
(36, 'Yohan', 'Mendoza', '15692128', 'V-', 'yohansito@gmail.com', NULL, NULL, NULL, '2026-01-20 01:29:08', '2026-01-25 03:45:08', NULL),
(37, 'Emmanuel', 'Arroyo', '30922671', 'V-', 'emman6321@gmail.com', NULL, NULL, NULL, '2026-01-20 21:23:34', '2026-01-20 21:23:34', NULL),
(38, 'Inversiones Full Color CA', '', '30666777', 'J-', 'fullcolor10@gmail.com', NULL, NULL, NULL, '2026-02-22 17:56:13', '2026-02-22 17:56:13', NULL),
(39, 'Carlos', 'Ramírez', '18456321', 'V-', 'carlos.ramirez@gmail.com', NULL, NULL, NULL, '2026-02-26 19:10:25', '2026-02-26 19:10:25', NULL),
(40, 'María', 'González', '20134567', 'V-', 'maria.gonzalez@hotmail.com', NULL, NULL, NULL, '2026-02-26 19:10:25', '2026-02-26 19:10:25', NULL),
(42, 'Inversiones Textilera del Centro', '', '41234567', 'J-', 'admintextileria@gmail.com', NULL, NULL, NULL, '2026-02-26 19:11:43', '2026-02-26 20:22:20', NULL),
(43, 'Luis', 'Hernández', '15678234', 'V-', 'luis.hernandez@outlook.com', NULL, NULL, NULL, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(44, 'Confecciones El Llano CA', '', '40987654', 'J-', 'ventas@gmail.com', NULL, NULL, NULL, '2026-02-26 19:11:43', '2026-02-26 20:21:46', NULL),
(45, 'Ana', 'Martínez', '22345678', 'V-', 'ana.martinez@gmail.com', NULL, NULL, NULL, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(46, 'José', 'Pérez', '17890456', 'V-', 'jose.perez@yahoo.com', NULL, NULL, NULL, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(47, 'Uniformes Profesionales VE CA', '', '42567890', 'J-', 'contactounipro@gmail.com', NULL, NULL, NULL, '2026-02-26 19:11:43', '2026-02-26 20:23:03', NULL),
(48, 'Rosa', 'Castillo', '19567890', 'V-', 'rosa.castillo@gmail.com', NULL, NULL, NULL, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(49, 'Pedro', 'Morales', '16789012', 'V-', 'pedro.morales@gmail.com', NULL, NULL, NULL, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(50, 'Gregorio', 'Rodriguez', '10729713', 'V-', 'gregorio10@gmail.com', NULL, NULL, NULL, '2026-02-26 19:46:04', '2026-02-26 19:46:04', NULL),
(51, 'Asoproductos de Portuguesa CA', '', '13232455', 'J-', 'asoproductos@gmail.com', NULL, NULL, NULL, '2026-03-05 17:17:09', '2026-03-05 17:17:09', NULL),
(53, 'Textiles Caracas Vzla', '', '1231321', 'J-', 'ventas@textilesvenezuela.com', NULL, NULL, NULL, '2026-03-19 18:21:05', '2026-03-19 18:21:05', NULL),
(54, 'Insumos Textiles C.C.S', '', '11112222', 'J-', 'ventas@insumostextiles.com', NULL, NULL, NULL, '2026-03-19 18:21:05', '2026-03-19 18:21:05', NULL),
(55, 'Telas y Bordados del Centro CA', '', '401234567', 'J-', 'ventas@telasbordados.com', NULL, NULL, NULL, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(56, 'Hilos Industriales Portuguesa SA', '', '312456789', 'J-', 'info@hilosindustriales.com', NULL, NULL, NULL, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(57, 'Insumos Textiles Venezuela CA', '', '201987654', 'G-', 'compras@insumostextiles.com.ve', NULL, NULL, NULL, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(58, 'Botones y Accesorios Lara CA', '', '502345671', 'J-', 'ventas@botonesaccesorios.com', NULL, NULL, NULL, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(59, 'Distribuidora de Telas Los Andes', '', '415678903', 'J-', 'contacto@telaslosandes.com', NULL, NULL, NULL, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(60, 'Sofia', 'Mendoza', '29347954', 'V-', 'sofi@gmail.com', NULL, NULL, NULL, '2026-04-14 21:26:32', '2026-04-14 21:26:32', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(191) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tipo_producto_id` bigint(20) UNSIGNED DEFAULT NULL,
  `insumo_tela_id` bigint(20) UNSIGNED DEFAULT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_base` decimal(10,2) NOT NULL,
  `atributos_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`atributos_snapshot`)),
  `imagen` varchar(191) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id`, `tipo_producto_id`, `insumo_tela_id`, `codigo`, `descripcion`, `precio_base`, `atributos_snapshot`, `imagen`, `estado`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'CHM-001', 'Polo de algodón pima con cuello redondo', 29.90, NULL, 'productoimg/imagenes/69406d911e834.jpg', 1, '2025-12-04 18:58:28', '2026-01-20 22:30:55', '2026-01-20 22:30:55'),
(2, NULL, NULL, NULL, 'Camisa manga larga para oficina', 59.90, NULL, NULL, 1, '2025-12-04 18:58:28', '2026-01-20 22:31:08', '2026-01-20 22:31:08'),
(3, NULL, NULL, NULL, 'Corte Columbia I', 19.00, NULL, 'productoimg/imagenes/6940679f046be.png', 1, '2025-12-15 19:55:11', '2026-01-20 22:31:04', '2026-01-20 22:31:04'),
(4, NULL, NULL, NULL, 'Pantalon de Seguridad', 19.00, NULL, 'productoimg/imagenes/69406ba3c92c3.jpg', 1, '2025-12-15 20:12:19', '2026-01-20 22:31:01', '2026-01-20 22:31:01'),
(5, 3, NULL, 'CAM-001', NULL, 25.00, NULL, NULL, 1, '2025-12-16 18:30:44', '2026-02-26 19:44:29', '2026-02-26 19:44:29'),
(6, 1, NULL, 'CHM-002', 'chemise cuello chino', 17.00, NULL, 'productoimg/imagenes/6941a6dcf11a1.jpg', 1, '2025-12-16 18:37:16', '2025-12-16 18:37:16', NULL),
(7, 2, NULL, 'FRN-CEV-001', NULL, 12.00, NULL, NULL, 1, '2025-12-16 18:57:27', '2026-02-26 19:42:05', '2026-02-26 19:42:05'),
(8, 2, NULL, 'FRN-CUR-001', 'Franela basica', 16.00, NULL, 'productoimg/imagenes/69a0a285243b5.jpg', 1, '2026-02-26 19:44:05', '2026-02-26 19:44:05', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_atributo_valor`
--

CREATE TABLE `producto_atributo_valor` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` bigint(20) UNSIGNED NOT NULL,
  `atributo_valor_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tipo_proveedor` enum('natural','juridico') NOT NULL DEFAULT 'juridico',
  `persona_id` bigint(20) UNSIGNED DEFAULT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono_contacto` varchar(20) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`id`, `tipo_proveedor`, `persona_id`, `contacto`, `telefono_contacto`, `estado`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'juridico', 53, 'Juan Pérez', '0412-5231234', 1, '2025-12-04 18:58:28', '2025-12-05 20:55:48', NULL),
(2, 'juridico', 54, 'María García', '0424890457', 1, '2025-12-04 18:58:28', '2025-12-04 18:58:28', NULL),
(3, 'natural', 22, NULL, NULL, 1, '2026-01-18 20:23:50', '2026-01-18 20:23:50', NULL),
(4, 'juridico', 55, 'María González', '0412-5678901', 1, '2026-01-18 20:33:31', '2026-01-18 20:33:31', NULL),
(5, 'juridico', 56, 'José Rodríguez', '0414-3456789', 1, '2026-01-18 20:34:28', '2026-01-18 20:34:28', NULL),
(6, 'juridico', 57, 'Carmen Pérez', '0424-9876543', 1, '2026-01-18 20:35:48', '2026-01-18 20:35:48', NULL),
(7, 'natural', 23, NULL, NULL, 1, '2026-01-18 20:37:01', '2026-01-18 20:37:01', NULL),
(8, 'natural', 24, NULL, NULL, 1, '2026-01-18 20:38:40', '2026-01-18 20:38:40', NULL),
(9, 'natural', 25, NULL, NULL, 1, '2026-01-18 20:41:05', '2026-01-18 20:41:05', NULL),
(10, 'juridico', 58, 'Fernando Castillo', '0416-7890123', 1, '2026-01-18 20:42:44', '2026-01-18 20:42:44', NULL),
(11, 'natural', 26, NULL, NULL, 1, '2026-01-18 20:43:53', '2026-01-18 20:43:53', NULL),
(12, 'juridico', 59, 'Ana Beatriz Ramos', '0426-5432109', 1, '2026-01-18 20:45:00', '2026-01-18 20:45:00', NULL),
(13, 'natural', 27, NULL, NULL, 1, '2026-01-18 20:57:49', '2026-01-18 20:57:49', NULL),
(14, 'natural', 28, NULL, NULL, 1, '2026-01-18 21:52:28', '2026-01-18 21:52:28', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recovery_attempt`
--

CREATE TABLE `recovery_attempt` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `email` varchar(191) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `tipo` enum('email','preguntas') NOT NULL,
  `resultado` enum('exito','fallo','bloqueado') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `recovery_attempt`
--

INSERT INTO `recovery_attempt` (`id`, `user_id`, `email`, `ip`, `user_agent`, `tipo`, `resultado`, `created_at`) VALUES
(1, NULL, 'admin@gmail.com', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'preguntas', 'fallo', '2026-04-26 21:37:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `talla`
--

CREATE TABLE `talla` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(191) NOT NULL COMMENT 'Valor interno de talla (Ej: XS, M, Talla Unica)',
  `etiqueta` varchar(191) DEFAULT NULL COMMENT 'Etiqueta visual para UI (Ej: Única)',
  `grupo` varchar(191) DEFAULT NULL COMMENT 'Agrupación visual: Única, Numéricas, Letras',
  `orden` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Orden de despliegue en UI',
  `activo` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Permite desactivar tallas sin borrarlas',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `talla`
--

INSERT INTO `talla` (`id`, `nombre`, `etiqueta`, `grupo`, `orden`, `activo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Talla Unica', 'Única', 'Única', 10, 1, '2026-02-23 21:43:06', '2026-02-23 21:43:06', NULL),
(2, '2', '2', 'Numéricas', 20, 1, '2026-02-23 21:43:06', '2026-02-23 21:43:06', NULL),
(3, '4', '4', 'Numéricas', 21, 1, '2026-02-23 21:43:06', '2026-02-23 21:43:06', NULL),
(4, '6', '6', 'Numéricas', 22, 1, '2026-02-23 21:43:06', '2026-02-23 21:43:06', NULL),
(5, '8', '8', 'Numéricas', 23, 1, '2026-02-23 21:43:06', '2026-02-23 21:43:06', NULL),
(6, '10', '10', 'Numéricas', 24, 1, '2026-02-23 21:43:06', '2026-02-23 21:43:06', NULL),
(7, '12', '12', 'Numéricas', 25, 1, '2026-02-23 21:43:06', '2026-02-23 21:43:06', NULL),
(8, '14', '14', 'Numéricas', 26, 1, '2026-02-23 21:43:06', '2026-02-23 21:43:06', NULL),
(9, '16', '16', 'Numéricas', 27, 1, '2026-02-23 21:43:06', '2026-02-23 21:43:06', NULL),
(10, 'XS', 'XS', 'Letras', 40, 1, '2026-02-23 21:43:06', '2026-02-23 21:43:06', NULL),
(11, 'S', 'S', 'Letras', 41, 1, '2026-02-23 21:43:06', '2026-02-23 21:43:06', NULL),
(12, 'M', 'M', 'Letras', 42, 1, '2026-02-23 21:43:06', '2026-02-23 21:43:06', NULL),
(13, 'L', 'L', 'Letras', 43, 1, '2026-02-23 21:43:06', '2026-02-23 21:43:06', NULL),
(14, 'XL', 'XL', 'Letras', 44, 1, '2026-02-23 21:43:06', '2026-02-23 21:43:06', NULL),
(15, 'XXL', 'XXL', 'Letras', 45, 1, '2026-02-23 21:43:06', '2026-02-23 21:43:06', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tasa_cambio`
--

CREATE TABLE `tasa_cambio` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `moneda` varchar(10) NOT NULL,
  `valor` decimal(12,4) NOT NULL,
  `fecha_bcv` date NOT NULL,
  `fuente` varchar(191) NOT NULL DEFAULT 'BCV',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tasa_cambio`
--

INSERT INTO `tasa_cambio` (`id`, `moneda`, `valor`, `fecha_bcv`, `fuente`, `created_at`, `updated_at`) VALUES
(1, 'USD', 270.7900, '2025-12-15', 'BCV (DolarAPI)', '2025-12-15 20:47:22', '2025-12-15 20:47:22'),
(2, 'USD', 273.5900, '2025-12-16', 'BCV (DolarAPI)', '2025-12-16 15:36:07', '2025-12-16 15:36:07'),
(3, 'USD', 279.5600, '2025-12-18', 'BCV (DolarAPI)', '2025-12-18 13:34:20', '2025-12-18 13:34:20'),
(4, 'USD', 282.5100, '2025-12-19', 'BCV (DolarAPI)', '2025-12-19 18:34:37', '2025-12-19 18:34:37'),
(5, 'USD', 336.4600, '2026-01-14', 'BCV (DolarAPI)', '2026-01-14 23:32:37', '2026-01-14 23:32:37'),
(6, 'USD', 339.1500, '2026-01-15', 'BCV (DolarAPI)', '2026-01-15 17:04:21', '2026-01-15 17:04:21'),
(7, 'USD', 341.7400, '2026-01-16', 'BCV (DolarAPI)', '2026-01-16 17:18:16', '2026-01-16 17:18:16'),
(8, 'USD', 344.5100, '2026-01-17', 'BCV (DolarAPI)', '2026-01-17 04:00:35', '2026-01-17 04:00:35'),
(9, 'USD', 344.5100, '2026-01-18', 'BCV (DolarAPI)', '2026-01-18 14:29:14', '2026-01-18 14:29:14'),
(10, 'USD', 344.5100, '2026-01-19', 'BCV (DolarAPI)', '2026-01-19 16:28:54', '2026-01-19 16:28:54'),
(11, 'USD', 344.5100, '2026-01-20', 'BCV (DolarAPI)', '2026-01-20 19:43:50', '2026-01-20 19:43:50'),
(12, 'USD', 352.7063, '2026-01-23', 'BCV (DolarAPI)', '2026-01-23 18:42:46', '2026-01-23 18:42:46'),
(13, 'USD', 355.5528, '2026-01-24', 'BCV (DolarAPI)', '2026-01-25 02:37:20', '2026-01-25 02:37:20'),
(14, 'USD', 382.6318, '2026-02-09', 'BCV (DolarAPI)', '2026-02-09 16:11:39', '2026-02-09 16:11:39'),
(15, 'USD', 396.3674, '2026-02-14', 'BCV (DolarAPI)', '2026-02-14 20:01:56', '2026-02-14 20:01:56'),
(16, 'USD', 396.3674, '2026-02-16', 'BCV (DolarAPI)', '2026-02-16 14:41:31', '2026-02-16 14:41:31'),
(17, 'USD', 396.3674, '2026-02-17', 'BCV (DolarAPI)', '2026-02-17 18:27:16', '2026-02-17 18:27:16'),
(18, 'USD', 398.7456, '2026-02-19', 'BCV (DolarAPI)', '2026-02-19 19:56:33', '2026-02-19 19:56:33'),
(19, 'USD', 402.3343, '2026-02-20', 'BCV (DolarAPI)', '2026-02-20 13:09:37', '2026-02-20 13:09:37'),
(20, 'USD', 405.3518, '2026-02-22', 'BCV (DolarAPI)', '2026-02-22 15:13:41', '2026-02-22 15:13:41'),
(21, 'USD', 405.3518, '2026-02-23', 'BCV (DolarAPI)', '2026-02-23 17:36:41', '2026-02-23 17:36:41'),
(22, 'USD', 407.3786, '2026-02-24', 'BCV (DolarAPI)', '2026-02-24 14:34:03', '2026-02-24 14:34:03'),
(23, 'USD', 414.0455, '2026-02-26', 'BCV (DolarAPI)', '2026-02-26 14:10:28', '2026-02-26 14:10:28'),
(24, 'USD', 417.3579, '2026-02-27', 'BCV (DolarAPI)', '2026-02-27 13:59:09', '2026-02-27 13:59:09'),
(25, 'USD', 425.6741, '2026-03-04', 'BCV (DolarAPI)', '2026-03-04 18:24:59', '2026-03-04 18:24:59'),
(26, 'USD', 427.9302, '2026-03-05', 'BCV (DolarAPI)', '2026-03-05 14:38:25', '2026-03-05 14:38:25'),
(27, 'USD', 431.0113, '2026-03-06', 'BCV (DolarAPI)', '2026-03-06 14:11:54', '2026-03-06 14:11:54'),
(28, 'USD', 436.2419, '2026-03-10', 'BCV (DolarAPI)', '2026-03-10 20:47:31', '2026-03-10 20:47:31'),
(29, 'USD', 440.9657, '2026-03-12', 'BCV (DolarAPI)', '2026-03-12 23:28:11', '2026-03-12 23:28:11'),
(30, 'USD', 446.8048, '2026-03-15', 'BCV (DolarAPI)', '2026-03-15 13:48:19', '2026-03-15 13:48:19'),
(31, 'USD', 446.8048, '2026-03-16', 'BCV (DolarAPI)', '2026-03-17 17:26:17', '2026-03-17 17:26:17'),
(32, 'USD', 451.5072, '2026-03-18', 'BCV (DolarAPI)', '2026-03-18 14:03:09', '2026-03-18 14:03:09'),
(33, 'USD', 455.2547, '2026-03-20', 'BCV (DolarAPI)', '2026-03-20 13:17:33', '2026-03-20 13:17:33'),
(34, 'USD', 457.0757, '2026-03-23', 'BCV (DolarAPI)', '2026-03-23 15:54:57', '2026-03-23 15:54:57'),
(35, 'USD', 462.6687, '2026-03-25', 'BCV (DolarAPI)', '2026-03-25 18:43:19', '2026-03-25 18:43:19'),
(36, 'USD', 475.9583, '2026-04-09', 'BCV (DolarAPI)', '2026-04-09 18:29:02', '2026-04-09 18:29:02'),
(37, 'USD', 477.1488, '2026-04-13', 'BCV (DolarAPI)', '2026-04-13 21:42:31', '2026-04-13 21:42:31'),
(38, 'USD', 477.6259, '2026-04-14', 'BCV (DolarAPI)', '2026-04-14 12:48:51', '2026-04-14 12:48:51'),
(39, 'USD', 481.6989, '2026-04-21', 'BCV (DolarAPI)', '2026-04-21 19:48:19', '2026-04-21 19:48:19'),
(40, 'USD', 482.7586, '2026-04-22', 'BCV (DolarAPI)', '2026-04-22 18:27:21', '2026-04-22 18:27:21'),
(41, 'USD', 483.8695, '2026-04-24', 'BCV (DolarAPI)', '2026-04-26 20:05:11', '2026-04-26 20:05:11'),
(42, 'USD', 510.7873, '2026-05-14', 'BCV (DolarAPI)', '2026-05-15 02:36:26', '2026-05-15 02:36:26'),
(43, 'USD', 526.8694, '2026-05-22', 'BCV (DolarAPI)', '2026-05-23 15:34:22', '2026-05-23 15:34:22'),
(44, 'USD', 530.5047, '2026-05-25', 'BCV (DolarAPI)', '2026-05-26 02:46:34', '2026-05-26 02:46:34'),
(45, 'USD', 535.3853, '2026-05-26', 'BCV (DolarAPI)', '2026-05-27 02:50:35', '2026-05-27 02:50:35'),
(46, 'USD', 540.0431, '2026-05-27', 'BCV (DolarAPI)', '2026-05-27 04:11:01', '2026-05-27 04:11:01'),
(47, 'USD', 544.5794, '2026-05-28', 'BCV (DolarAPI)', '2026-05-28 11:20:58', '2026-05-28 11:20:58'),
(48, 'USD', 557.9741, '2026-06-02', 'BCV (DolarAPI)', '2026-06-03 01:04:53', '2026-06-03 01:04:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `telefono`
--

CREATE TABLE `telefono` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `persona_id` bigint(20) UNSIGNED NOT NULL,
  `numero` varchar(20) NOT NULL,
  `tipo` enum('movil','casa','trabajo') NOT NULL DEFAULT 'movil',
  `es_principal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `telefono`
--

INSERT INTO `telefono` (`id`, `persona_id`, `numero`, `tipo`, `es_principal`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, '0426-3412567', 'movil', 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(2, 3, '0412-3453314', 'movil', 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(3, 4, '0426-1135645', 'movil', 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(4, 6, '0412555777', 'movil', 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(5, 7, '0424-869334', 'movil', 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(6, 8, '0422-344859', 'movil', 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(7, 9, '0424-898099', 'movil', 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(8, 10, '0422-778456', 'movil', 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(9, 11, '0414-5548982', 'movil', 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(10, 12, '0412-4436668', 'movil', 1, '2025-12-10 20:29:40', '2026-01-17 03:27:27', NULL),
(11, 14, '0412-3556789', 'movil', 1, '2025-12-10 21:09:58', '2025-12-10 21:09:58', NULL),
(12, 13, '0412-4435673', 'movil', 1, '2025-12-10 21:17:57', '2025-12-10 21:17:57', NULL),
(13, 15, '0412-9288102', 'movil', 1, '2026-01-17 16:51:52', '2026-01-17 16:51:52', NULL),
(14, 16, '0414-5684402', 'movil', 1, '2026-01-17 17:11:09', '2026-01-17 17:11:09', NULL),
(15, 17, '0424-3637623', 'movil', 1, '2026-01-17 22:05:23', '2026-01-17 22:05:23', NULL),
(16, 18, '0414-5684402', 'movil', 1, '2026-01-17 22:31:33', '2026-01-17 22:31:33', NULL),
(17, 19, '0424-1595466', 'movil', 1, '2026-01-18 03:49:00', '2026-01-18 03:49:00', NULL),
(18, 20, '0424-1595466', 'movil', 1, '2026-01-18 03:56:57', '2026-01-18 03:56:57', NULL),
(20, 22, '0412-5238473', 'movil', 1, '2026-01-18 20:23:50', '2026-01-18 20:23:50', NULL),
(21, 23, '0424-5671234', 'movil', 1, '2026-01-18 20:37:01', '2026-01-18 20:37:01', NULL),
(22, 24, '0412-8904567', 'movil', 1, '2026-01-18 20:38:40', '2026-01-18 20:38:40', NULL),
(23, 25, '0414-2345678', 'movil', 1, '2026-01-18 20:41:05', '2026-01-18 20:41:05', NULL),
(24, 26, '0426-3456789', 'movil', 1, '2026-01-18 20:43:53', '2026-01-18 20:43:53', NULL),
(25, 27, '0424-5678901', 'movil', 1, '2026-01-18 20:57:49', '2026-01-18 20:57:49', NULL),
(26, 28, '0424-8901234', 'movil', 1, '2026-01-18 21:52:28', '2026-01-18 21:52:28', NULL),
(27, 29, '0422-2222222', 'movil', 1, '2026-01-19 00:25:34', '2026-01-19 00:25:34', NULL),
(28, 30, '0424-5345463', 'movil', 1, '2026-01-19 03:56:04', '2026-01-19 03:56:04', NULL),
(29, 31, '0424-3442434', 'movil', 1, '2026-01-19 04:01:50', '2026-01-19 04:01:50', NULL),
(30, 32, '0424-5684402', 'movil', 1, '2026-01-19 04:05:33', '2026-01-19 04:05:33', NULL),
(31, 33, '0424-5684402', 'movil', 1, '2026-01-19 04:17:44', '2026-01-19 04:17:44', NULL),
(32, 34, '0424-2222222', 'movil', 1, '2026-01-19 04:26:34', '2026-01-19 04:26:34', NULL),
(33, 35, '0424-4523142', 'movil', 1, '2026-01-19 16:49:16', '2026-01-19 16:49:16', NULL),
(34, 36, '0412-9020671', 'movil', 1, '2026-01-20 01:29:08', '2026-01-20 01:29:08', NULL),
(35, 37, '0412-5235773', 'movil', 1, '2026-01-20 21:23:34', '2026-01-20 21:23:34', NULL),
(36, 38, '0412-3456775', 'movil', 1, '2026-02-22 17:56:13', '2026-02-22 17:56:13', NULL),
(37, 39, '0414-7821345', 'movil', 1, '2026-02-26 19:10:25', '2026-02-26 19:10:25', NULL),
(38, 40, '0424-5567890', 'movil', 1, '2026-02-26 19:10:25', '2026-02-26 19:10:25', NULL),
(39, 42, '0422-2514789', 'movil', 1, '2026-02-26 19:11:43', '2026-02-26 20:22:20', NULL),
(40, 43, '0412-8903456', 'movil', 1, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(41, 44, '0412-6618900', 'movil', 1, '2026-02-26 19:11:43', '2026-02-26 20:21:46', NULL),
(42, 45, '0416-4567123', 'movil', 1, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(43, 46, '0424-3345678', 'movil', 1, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(44, 47, '0422-5551234', 'movil', 1, '2026-02-26 19:11:43', '2026-02-26 20:23:03', NULL),
(45, 48, '0414-9012345', 'movil', 1, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(46, 49, '0412-6781234', 'movil', 1, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(47, 50, '0412-5773592', 'movil', 1, '2026-02-26 19:46:04', '2026-02-26 19:46:04', NULL),
(48, 51, '0424-5049283', 'movil', 1, '2026-03-05 17:17:09', '2026-03-05 17:17:09', NULL),
(49, 53, '0412-555666', 'trabajo', 1, '2026-03-19 18:21:05', '2026-03-19 18:21:05', NULL),
(50, 54, '01-3214567', 'trabajo', 1, '2026-03-19 18:21:05', '2026-03-19 18:21:05', NULL),
(51, 55, '0255-6234567', 'trabajo', 1, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(52, 56, '0255-6789012', 'trabajo', 1, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(53, 57, '0241-8345678', 'trabajo', 1, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(54, 58, '0251-7891234', 'trabajo', 1, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(55, 59, '0274-4567890', 'trabajo', 1, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(56, 60, '0422-7234564', 'movil', 1, '2026-04-14 21:26:32', '2026-04-14 21:26:32', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_producto`
--

CREATE TABLE `tipo_producto` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `prefijo` varchar(5) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_confeccion` decimal(10,2) NOT NULL DEFAULT 0.00,
  `requiere_tela` tinyint(1) NOT NULL DEFAULT 1,
  `consumo_tela_por_unidad` decimal(8,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipo_producto`
--

INSERT INTO `tipo_producto` (`id`, `nombre`, `prefijo`, `descripcion`, `precio_confeccion`, `requiere_tela`, `consumo_tela_por_unidad`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Chemise', 'CHM', 'Camisas tipo polo con cuello', 0.00, 1, 0.00, '2025-12-16 17:48:48', '2025-12-16 18:37:16', NULL),
(2, 'Franela', 'FRN', 'Franelas cuello redondo o V', 0.00, 1, 0.00, '2025-12-16 17:48:48', '2025-12-16 17:48:48', NULL),
(3, 'Camisa', 'CAM', 'Camisas formales manga larga/corta', 0.00, 1, 0.00, '2025-12-16 17:48:48', '2025-12-16 18:30:44', NULL),
(4, 'Pantalón', 'PNT', 'Pantalones de trabajo o formales', 0.00, 1, 0.00, '2025-12-16 17:48:48', '2025-12-16 17:48:48', NULL),
(5, 'Chaqueta', 'CHQ', 'Chaquetas industriales o formales', 0.00, 1, 0.00, '2025-12-16 17:48:48', '2025-12-16 17:48:48', NULL),
(6, 'Overol', 'OVR', 'Overoles y monos de trabajo', 0.00, 1, 0.00, '2025-12-16 17:48:48', '2025-12-16 17:48:48', NULL),
(7, 'Uniforme Escolar', 'ESC', 'Prendas para uniformes escolares', 0.00, 1, 0.00, '2025-12-16 17:48:48', '2025-12-16 17:48:48', NULL),
(8, 'Accesorio', 'ACC', 'Gorras, delantales, chalecos, etc.', 0.00, 1, 0.00, '2025-12-16 17:48:48', '2025-12-16 17:48:48', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_producto_atributo`
--

CREATE TABLE `tipo_producto_atributo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tipo_producto_id` bigint(20) UNSIGNED NOT NULL,
  `atributo_id` bigint(20) UNSIGNED NOT NULL,
  `es_obligatorio` tinyint(1) NOT NULL DEFAULT 1,
  `orden` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_producto_insumo`
--

CREATE TABLE `tipo_producto_insumo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tipo_producto_id` bigint(20) UNSIGNED NOT NULL,
  `insumo_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad_estimada` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

CREATE TABLE `user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `persona_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `role` enum('Administrador','Supervisor') NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `recovery_locked_until` timestamp NULL DEFAULT NULL,
  `recovery_failed_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `recovery_must_reset_questions` tinyint(1) NOT NULL DEFAULT 0,
  `password_reset_by_admin` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user`
--

INSERT INTO `user` (`id`, `persona_id`, `name`, `email`, `role`, `email_verified_at`, `password`, `avatar`, `estado`, `recovery_locked_until`, `recovery_failed_attempts`, `recovery_must_reset_questions`, `password_reset_by_admin`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Emmanuel Jesus', 'admin@gmail.com', 'Administrador', NULL, '$2y$12$eq5H9CQvR.2WeRggx.y92Ot93ftz0Ml0rrPRGhmxGMsfEwq0/zahm', 'avatars/69a05e0f7203f.png', 1, NULL, 0, 0, 0, '9XLDTYGF28zbfGsMVmKu816YhoB9eml9Mi8locRKGmmj76dOlnyK16y2apDZ', '2025-12-04 18:58:27', '2026-02-26 14:51:59', NULL),
(2, 2, 'Supervisor', 'supervisor@gmail.com', 'Supervisor', NULL, '$2y$12$WZ9jnte4F/DkVPbh64iBKOt91FLUDEDRzmYtJYvc6.iwwLhn3wef6', NULL, 1, NULL, 0, 0, 0, NULL, '2025-12-04 18:58:28', '2025-12-04 18:58:28', NULL),
(5, NULL, 'Vanessa Diaz', 'vanessalopez090551@gmail.com', 'Administrador', NULL, '$2y$12$d0G/88tSU7qJKgmtlYP.ZO95ss9hgFYK8lZF6N9tsRQAsmlansFHC', 'avatars/69a060ce9e755.png', 1, NULL, 0, 0, 0, 'Egbv2pBcVPvjVa8jdqGym2kvHdikOyAWaF03sHTyIAY1ZE28cT8ARZkfl6GR', '2026-01-15 00:05:33', '2026-02-26 15:03:42', NULL),
(7, NULL, 'Jesus Rodriguez', 'emman6321@gmail.com', 'Administrador', NULL, '$2y$12$b9CFjlKZfvn41Ap747aBLOrvO7JJncegQIKYLyfrS8jTeguVdvdhG', 'avatars/69a8a14e140ef.jpg', 1, NULL, 0, 0, 0, 'PO03W1NRVXXG9mIhw7Blk9P6Er4MNd9wUh3i1ki7KFtpKfGqdz9tn5I5EvcV', '2026-03-04 21:17:02', '2026-03-05 14:39:50', NULL),
(8, NULL, 'Francis', 'francis@gmail.com', 'Administrador', NULL, '$2y$12$iBPxm0QiVidpnp4gjx1vgeyZ3gEFE0hP8AUbvMt60gN69/CwrQ/ze', NULL, 1, NULL, 0, 0, 0, NULL, '2026-03-19 15:17:38', '2026-03-19 15:17:38', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_recovery_question`
--

CREATE TABLE `user_recovery_question` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `pregunta_id` tinyint(3) UNSIGNED NOT NULL,
  `respuesta` varchar(255) NOT NULL,
  `orden` tinyint(3) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_recovery_question`
--

INSERT INTO `user_recovery_question` (`id`, `user_id`, `pregunta_id`, `respuesta`, `orden`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '$2y$12$O.578XXRW1QdIHrZS9Qi9uWz.DCrMwApRih1WNxBLlKOoCTu3p0Cm', 1, '2026-04-27 00:01:14', '2026-04-27 00:01:14'),
(2, 1, 10, '$2y$12$wCFiXmfG0Lud6r3FM0tItecUYLeK0eHVUlhnJQk0zfh6GwXkRVvtq', 2, '2026-04-27 00:01:15', '2026-04-27 00:01:15'),
(3, 1, 8, '$2y$12$wOWMCJxPaUsgkGiWnC4fLuPhn89hEbfmuni7VYUWxpMTG9PDPrZVm', 3, '2026-04-27 00:01:15', '2026-04-27 00:01:15');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `atributo`
--
ALTER TABLE `atributo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `atributo_nombre_unique` (`nombre`),
  ADD UNIQUE KEY `atributo_codigo_unique` (`codigo`);

--
-- Indices de la tabla `atributo_valor`
--
ALTER TABLE `atributo_valor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `atributo_valor_atributo_id_codigo_unique` (`atributo_id`,`codigo`),
  ADD UNIQUE KEY `atributo_valor_atributo_id_nombre_unique` (`atributo_id`,`nombre`);

--
-- Indices de la tabla `banco`
--
ALTER TABLE `banco`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bancos_nombre_unique` (`nombre`);

--
-- Indices de la tabla `bordado_ubicacion`
--
ALTER TABLE `bordado_ubicacion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bordado_ubicaciones_nombre_unique` (`nombre`);

--
-- Indices de la tabla `cargo`
--
ALTER TABLE `cargo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cargo_nombre_departamento_id_unique` (`nombre`,`departamento_id`),
  ADD KEY `cargo_departamento_id_foreign` (`departamento_id`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_persona_id_foreign` (`persona_id`);

--
-- Indices de la tabla `color`
--
ALTER TABLE `color`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `colores_nombre_unique` (`nombre`);

--
-- Indices de la tabla `cotizacion`
--
ALTER TABLE `cotizacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cotizaciones_user_id_foreign` (`user_id`),
  ADD KEY `cotizacion_cliente_id_foreign` (`cliente_id`),
  ADD KEY `idx_cotizacion_estado` (`estado`),
  ADD KEY `idx_cotizacion_cliente_estado` (`cliente_id`,`estado`);

--
-- Indices de la tabla `departamento`
--
ALTER TABLE `departamento`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departamento_nombre_unique` (`nombre`);

--
-- Indices de la tabla `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detalle_cotizaciones_cotizacion_id_foreign` (`cotizacion_id`),
  ADD KEY `detalle_cotizaciones_producto_id_foreign` (`producto_id`),
  ADD KEY `detalle_cotizacion_color_id_foreign` (`color_id`),
  ADD KEY `detalle_cotizacion_talla_id_foreign` (`talla_id`);

--
-- Indices de la tabla `detalle_cotizacion_bordado`
--
ALTER TABLE `detalle_cotizacion_bordado`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_det_cot_bordado_detalle` (`detalle_cotizacion_id`),
  ADD KEY `idx_det_cot_bordado_ubicacion` (`ubicacion_bordado_id`),
  ADD KEY `detalle_cotizacion_bordado_logo_id_foreign` (`logo_id`);

--
-- Indices de la tabla `detalle_orden_insumo`
--
ALTER TABLE `detalle_orden_insumo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detalle_orden_insumos_orden_produccion_id_foreign` (`orden_produccion_id`),
  ADD KEY `detalle_orden_insumos_insumo_id_foreign` (`insumo_id`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detalle_pedidos_pedido_id_foreign` (`pedido_id`),
  ADD KEY `detalle_pedidos_producto_id_foreign` (`producto_id`),
  ADD KEY `detalle_pedido_color_id_foreign` (`color_id`),
  ADD KEY `detalle_pedido_talla_id_foreign` (`talla_id`);

--
-- Indices de la tabla `detalle_pedido_bordado`
--
ALTER TABLE `detalle_pedido_bordado`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_det_ped_bordado_detalle` (`detalle_pedido_id`),
  ADD KEY `idx_det_ped_bordado_ubicacion` (`ubicacion_bordado_id`),
  ADD KEY `detalle_pedido_bordado_logo_id_foreign` (`logo_id`);

--
-- Indices de la tabla `detalle_pedido_insumo`
--
ALTER TABLE `detalle_pedido_insumo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `detalle_pedido_insumo_detalle_pedido_id_insumo_id_unique` (`detalle_pedido_id`,`insumo_id`),
  ADD KEY `detalle_pedido_insumo_insumo_id_foreign` (`insumo_id`);

--
-- Indices de la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `direccion_persona_id_index` (`persona_id`);

--
-- Indices de la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `empleado_persona_id_unique` (`persona_id`),
  ADD UNIQUE KEY `empleado_codigo_empleado_unique` (`codigo_empleado`),
  ADD KEY `empleado_departamento_id_foreign` (`departamento_id`),
  ADD KEY `empleado_cargo_id_foreign` (`cargo_id`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `insumo`
--
ALTER TABLE `insumo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `insumo_codigo_unique` (`codigo`),
  ADD KEY `idx_insumo_stock` (`stock_actual`);

--
-- Indices de la tabla `logo`
--
ALTER TABLE `logo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `logos_name_unique` (`name`),
  ADD UNIQUE KEY `logos_original_filename_unique` (`original_filename`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `movimiento_insumo`
--
ALTER TABLE `movimiento_insumo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movimientos_insumos_insumo_id_foreign` (`insumo_id`),
  ADD KEY `movimientos_insumos_created_by_foreign` (`created_by`),
  ADD KEY `idx_mov_created_at` (`created_at`),
  ADD KEY `idx_mov_insumo_created` (`insumo_id`,`created_at`);

--
-- Indices de la tabla `orden_produccion`
--
ALTER TABLE `orden_produccion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ordenes_produccion_producto_id_foreign` (`producto_id`),
  ADD KEY `ordenes_produccion_created_by_foreign` (`created_by`),
  ADD KEY `orden_produccion_pedido_id_foreign` (`pedido_id`),
  ADD KEY `idx_orden_estado` (`estado`),
  ADD KEY `idx_orden_fecha_fin` (`fecha_fin_estimada`),
  ADD KEY `idx_orden_estado_fecha_fin` (`estado`,`fecha_fin_estimada`),
  ADD KEY `orden_produccion_detalle_pedido_id_foreign` (`detalle_pedido_id`),
  ADD KEY `orden_produccion_empleado_id_foreign` (`empleado_id`);

--
-- Indices de la tabla `pago_pedido`
--
ALTER TABLE `pago_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pago_pedido_banco_id_foreign` (`banco_id`),
  ADD KEY `idx_pago_pedido_metodo` (`pedido_id`,`metodo`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pedido_cotizacion_id_unique` (`cotizacion_id`),
  ADD KEY `pedidos_user_id_foreign` (`user_id`),
  ADD KEY `pedido_cliente_id_foreign` (`cliente_id`),
  ADD KEY `pedido_cotizacion_id_foreign` (`cotizacion_id`),
  ADD KEY `idx_pedido_estado` (`estado`),
  ADD KEY `idx_pedido_fecha` (`fecha_pedido`),
  ADD KEY `idx_pedido_cliente_estado` (`cliente_id`,`estado`);

--
-- Indices de la tabla `persona`
--
ALTER TABLE `persona`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `persona_tipo_doc_documento_unique` (`tipo_documento`,`documento_identidad`),
  ADD UNIQUE KEY `persona_email_unique` (`email`);

--
-- Indices de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `producto_codigo_unique` (`codigo`),
  ADD KEY `producto_tipo_producto_id_foreign` (`tipo_producto_id`),
  ADD KEY `producto_insumo_tela_id_foreign` (`insumo_tela_id`);

--
-- Indices de la tabla `producto_atributo_valor`
--
ALTER TABLE `producto_atributo_valor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `producto_atributo_valor_producto_id_atributo_valor_id_unique` (`producto_id`,`atributo_valor_id`),
  ADD KEY `producto_atributo_valor_atributo_valor_id_foreign` (`atributo_valor_id`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proveedor_persona_id_foreign` (`persona_id`);

--
-- Indices de la tabla `recovery_attempt`
--
ALTER TABLE `recovery_attempt`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recovery_attempt_user_id_index` (`user_id`),
  ADD KEY `recovery_attempt_email_index` (`email`),
  ADD KEY `recovery_attempt_created_at_index` (`created_at`);

--
-- Indices de la tabla `talla`
--
ALTER TABLE `talla`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tallas_nombre_unique` (`nombre`);

--
-- Indices de la tabla `tasa_cambio`
--
ALTER TABLE `tasa_cambio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tasa_cambio_moneda_fecha_bcv_unique` (`moneda`,`fecha_bcv`),
  ADD KEY `idx_tasa_fecha` (`fecha_bcv`);

--
-- Indices de la tabla `telefono`
--
ALTER TABLE `telefono`
  ADD PRIMARY KEY (`id`),
  ADD KEY `telefono_persona_id_index` (`persona_id`);

--
-- Indices de la tabla `tipo_producto`
--
ALTER TABLE `tipo_producto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tipo_producto_codigo_prefijo_unique` (`prefijo`);

--
-- Indices de la tabla `tipo_producto_atributo`
--
ALTER TABLE `tipo_producto_atributo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tipo_producto_atributo_tipo_producto_id_atributo_id_unique` (`tipo_producto_id`,`atributo_id`),
  ADD KEY `tipo_producto_atributo_atributo_id_foreign` (`atributo_id`);

--
-- Indices de la tabla `tipo_producto_insumo`
--
ALTER TABLE `tipo_producto_insumo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tipo_producto_insumo_unique` (`tipo_producto_id`,`insumo_id`),
  ADD KEY `tipo_producto_insumo_insumo_id_foreign` (`insumo_id`);

--
-- Indices de la tabla `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `user_persona_id_unique` (`persona_id`);

--
-- Indices de la tabla `user_recovery_question`
--
ALTER TABLE `user_recovery_question`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_recovery_question_user_id_orden_unique` (`user_id`,`orden`),
  ADD UNIQUE KEY `user_recovery_question_user_id_pregunta_id_unique` (`user_id`,`pregunta_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `atributo`
--
ALTER TABLE `atributo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `atributo_valor`
--
ALTER TABLE `atributo_valor`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `banco`
--
ALTER TABLE `banco`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `bordado_ubicacion`
--
ALTER TABLE `bordado_ubicacion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `cargo`
--
ALTER TABLE `cargo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `color`
--
ALTER TABLE `color`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `cotizacion`
--
ALTER TABLE `cotizacion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT de la tabla `departamento`
--
ALTER TABLE `departamento`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT de la tabla `detalle_cotizacion_bordado`
--
ALTER TABLE `detalle_cotizacion_bordado`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT de la tabla `detalle_orden_insumo`
--
ALTER TABLE `detalle_orden_insumo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido_bordado`
--
ALTER TABLE `detalle_pedido_bordado`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido_insumo`
--
ALTER TABLE `detalle_pedido_insumo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `direccion`
--
ALTER TABLE `direccion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de la tabla `empleado`
--
ALTER TABLE `empleado`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `insumo`
--
ALTER TABLE `insumo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `logo`
--
ALTER TABLE `logo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT de la tabla `movimiento_insumo`
--
ALTER TABLE `movimiento_insumo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `orden_produccion`
--
ALTER TABLE `orden_produccion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `pago_pedido`
--
ALTER TABLE `pago_pedido`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de la tabla `persona`
--
ALTER TABLE `persona`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `producto_atributo_valor`
--
ALTER TABLE `producto_atributo_valor`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `recovery_attempt`
--
ALTER TABLE `recovery_attempt`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `talla`
--
ALTER TABLE `talla`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `tasa_cambio`
--
ALTER TABLE `tasa_cambio`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `telefono`
--
ALTER TABLE `telefono`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `tipo_producto`
--
ALTER TABLE `tipo_producto`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `tipo_producto_atributo`
--
ALTER TABLE `tipo_producto_atributo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_producto_insumo`
--
ALTER TABLE `tipo_producto_insumo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `user`
--
ALTER TABLE `user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `user_recovery_question`
--
ALTER TABLE `user_recovery_question`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `atributo_valor`
--
ALTER TABLE `atributo_valor`
  ADD CONSTRAINT `atributo_valor_atributo_id_foreign` FOREIGN KEY (`atributo_id`) REFERENCES `atributo` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cargo`
--
ALTER TABLE `cargo`
  ADD CONSTRAINT `cargo_departamento_id_foreign` FOREIGN KEY (`departamento_id`) REFERENCES `departamento` (`id`);

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `cliente_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`);

--
-- Filtros para la tabla `cotizacion`
--
ALTER TABLE `cotizacion`
  ADD CONSTRAINT `cotizacion_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`),
  ADD CONSTRAINT `cotizacion_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Filtros para la tabla `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  ADD CONSTRAINT `detalle_cotizacion_color_id_foreign` FOREIGN KEY (`color_id`) REFERENCES `color` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `detalle_cotizacion_talla_id_foreign` FOREIGN KEY (`talla_id`) REFERENCES `talla` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `detalle_cotizaciones_cotizacion_id_foreign` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizacion` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_cotizaciones_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`);

--
-- Filtros para la tabla `detalle_cotizacion_bordado`
--
ALTER TABLE `detalle_cotizacion_bordado`
  ADD CONSTRAINT `detalle_cotizacion_bordado_detalle_cotizacion_id_foreign` FOREIGN KEY (`detalle_cotizacion_id`) REFERENCES `detalle_cotizacion` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_cotizacion_bordado_logo_id_foreign` FOREIGN KEY (`logo_id`) REFERENCES `logo` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `detalle_cotizacion_bordado_ubicacion_bordado_id_foreign` FOREIGN KEY (`ubicacion_bordado_id`) REFERENCES `bordado_ubicacion` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `detalle_orden_insumo`
--
ALTER TABLE `detalle_orden_insumo`
  ADD CONSTRAINT `detalle_orden_insumos_insumo_id_foreign` FOREIGN KEY (`insumo_id`) REFERENCES `insumo` (`id`),
  ADD CONSTRAINT `detalle_orden_insumos_orden_produccion_id_foreign` FOREIGN KEY (`orden_produccion_id`) REFERENCES `orden_produccion` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_color_id_foreign` FOREIGN KEY (`color_id`) REFERENCES `color` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `detalle_pedido_talla_id_foreign` FOREIGN KEY (`talla_id`) REFERENCES `talla` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `detalle_pedidos_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_pedidos_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`);

--
-- Filtros para la tabla `detalle_pedido_bordado`
--
ALTER TABLE `detalle_pedido_bordado`
  ADD CONSTRAINT `detalle_pedido_bordado_detalle_pedido_id_foreign` FOREIGN KEY (`detalle_pedido_id`) REFERENCES `detalle_pedido` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_pedido_bordado_logo_id_foreign` FOREIGN KEY (`logo_id`) REFERENCES `logo` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `detalle_pedido_bordado_ubicacion_bordado_id_foreign` FOREIGN KEY (`ubicacion_bordado_id`) REFERENCES `bordado_ubicacion` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `detalle_pedido_insumo`
--
ALTER TABLE `detalle_pedido_insumo`
  ADD CONSTRAINT `detalle_pedido_insumo_detalle_pedido_id_foreign` FOREIGN KEY (`detalle_pedido_id`) REFERENCES `detalle_pedido` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_pedido_insumo_insumo_id_foreign` FOREIGN KEY (`insumo_id`) REFERENCES `insumo` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD CONSTRAINT `direccion_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD CONSTRAINT `empleado_cargo_id_foreign` FOREIGN KEY (`cargo_id`) REFERENCES `cargo` (`id`),
  ADD CONSTRAINT `empleado_departamento_id_foreign` FOREIGN KEY (`departamento_id`) REFERENCES `departamento` (`id`),
  ADD CONSTRAINT `empleado_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`);

--
-- Filtros para la tabla `movimiento_insumo`
--
ALTER TABLE `movimiento_insumo`
  ADD CONSTRAINT `movimientos_insumos_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `movimientos_insumos_insumo_id_foreign` FOREIGN KEY (`insumo_id`) REFERENCES `insumo` (`id`);

--
-- Filtros para la tabla `orden_produccion`
--
ALTER TABLE `orden_produccion`
  ADD CONSTRAINT `orden_produccion_detalle_pedido_id_foreign` FOREIGN KEY (`detalle_pedido_id`) REFERENCES `detalle_pedido` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orden_produccion_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleado` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orden_produccion_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ordenes_produccion_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `ordenes_produccion_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pago_pedido`
--
ALTER TABLE `pago_pedido`
  ADD CONSTRAINT `pago_pedido_banco_id_foreign` FOREIGN KEY (`banco_id`) REFERENCES `banco` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pago_pedido_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `pedido_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pedido_cotizacion_id_foreign` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizacion` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pedido_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_insumo_tela_id_foreign` FOREIGN KEY (`insumo_tela_id`) REFERENCES `insumo` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `producto_tipo_producto_id_foreign` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipo_producto` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `producto_atributo_valor`
--
ALTER TABLE `producto_atributo_valor`
  ADD CONSTRAINT `producto_atributo_valor_atributo_valor_id_foreign` FOREIGN KEY (`atributo_valor_id`) REFERENCES `atributo_valor` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `producto_atributo_valor_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD CONSTRAINT `proveedor_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`);

--
-- Filtros para la tabla `recovery_attempt`
--
ALTER TABLE `recovery_attempt`
  ADD CONSTRAINT `recovery_attempt_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `telefono`
--
ALTER TABLE `telefono`
  ADD CONSTRAINT `telefono_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tipo_producto_atributo`
--
ALTER TABLE `tipo_producto_atributo`
  ADD CONSTRAINT `tipo_producto_atributo_atributo_id_foreign` FOREIGN KEY (`atributo_id`) REFERENCES `atributo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tipo_producto_atributo_tipo_producto_id_foreign` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipo_producto` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tipo_producto_insumo`
--
ALTER TABLE `tipo_producto_insumo`
  ADD CONSTRAINT `tipo_producto_insumo_insumo_id_foreign` FOREIGN KEY (`insumo_id`) REFERENCES `insumo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tipo_producto_insumo_tipo_producto_id_foreign` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipo_producto` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`);

--
-- Filtros para la tabla `user_recovery_question`
--
ALTER TABLE `user_recovery_question`
  ADD CONSTRAINT `user_recovery_question_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
