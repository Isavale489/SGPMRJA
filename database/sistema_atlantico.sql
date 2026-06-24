-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3308
-- Tiempo de generación: 18-06-2026 a las 05:18:38
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
(5, 'Capucha', 'CPC', NULL, '2026-05-07 17:15:29', '2026-05-07 17:15:29'),
(6, 'Modelo de Delantal', 'MDLNT', 'Para el producto delantal', '2026-06-06 23:28:07', '2026-06-07 16:47:46'),
(7, 'Modelo de Gorra', 'MG', 'Distintos modelos de gorras', '2026-06-07 16:46:24', '2026-06-07 16:46:24'),
(8, 'Modelo de Franela', 'MFR', 'Distintos modelos que existen para el tipo de producto franela', '2026-06-10 04:01:17', '2026-06-10 04:01:17'),
(9, 'Modelo de Pantalon', 'MDP', 'Modelos que puede tener un pantalon', '2026-06-10 04:03:28', '2026-06-10 04:03:28'),
(10, 'Modelo de Camisa', 'MDC', 'Modelos para las camisas', '2026-06-10 04:04:53', '2026-06-10 04:04:53'),
(11, 'Modelo de Chemise', 'MCH', 'Modelos de Chemise', '2026-06-10 04:09:08', '2026-06-10 04:09:08'),
(12, 'Cuello de Camisa', 'CLLC', 'Cuellos que pueda una camisa', '2026-06-10 04:11:29', '2026-06-10 04:11:29'),
(13, 'Cuello de Chemise', 'CDC', 'Cuellos que puede tener una chemise', '2026-06-10 04:16:31', '2026-06-10 04:16:31');

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
(17, 3, 'Columbia II', 'CLM2', 5, '2026-05-27 02:51:36', '2026-05-27 02:51:36'),
(18, 6, '2 bolsillos', '2BL', 1, '2026-06-06 23:28:32', '2026-06-06 23:28:46'),
(19, 6, '1 Bolsillo', '1BL', 2, '2026-06-06 23:29:11', '2026-06-06 23:29:11'),
(20, 7, 'Gorra Clasica', 'GC', 1, '2026-06-07 16:46:43', '2026-06-07 16:46:43'),
(21, 7, 'Nano Cap', 'NNOCAP', 2, '2026-06-07 16:48:19', '2026-06-07 16:48:19'),
(22, 8, 'Cuello en V', 'CV', 1, '2026-06-10 04:01:57', '2026-06-10 04:01:57'),
(23, 8, 'Cuello Redondo', 'CUR', 2, '2026-06-10 04:02:26', '2026-06-10 04:02:26'),
(24, 9, 'Rigido', 'RIG', 1, '2026-06-10 04:03:43', '2026-06-10 04:03:43'),
(25, 9, 'Stretch', 'STR', 2, '2026-06-10 04:03:58', '2026-06-10 04:03:58'),
(26, 10, 'Clasica', 'CCL', 1, '2026-06-10 04:05:33', '2026-06-10 04:05:33'),
(27, 10, 'Columbia I', 'CCL1', 2, '2026-06-10 04:05:49', '2026-06-10 04:05:49'),
(28, 10, 'Columbia II', 'CCL2', 3, '2026-06-10 04:06:14', '2026-06-10 04:06:23'),
(29, 11, 'Clasica', 'CHCL', 1, '2026-06-10 04:09:28', '2026-06-10 04:09:28'),
(30, 12, 'Con tapabotones', 'CT', 1, '2026-06-10 04:11:45', '2026-06-10 04:11:45'),
(31, 12, 'Sin tapabotones', 'ST', 2, '2026-06-10 04:12:13', '2026-06-10 04:12:13'),
(32, 13, 'Clasico', 'CLC', 1, '2026-06-10 04:16:45', '2026-06-10 04:16:45'),
(33, 13, 'Mao', 'CMAO', 2, '2026-06-10 04:16:59', '2026-06-10 04:16:59');

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
(4, 'Limpieza', 4, 1, '2026-04-21 20:16:52', '2026-05-29 20:06:02', NULL),
(5, 'Supervisor 2', 1, 1, '2026-04-21 20:16:52', '2026-04-21 20:32:49', '2026-04-21 20:32:49'),
(6, 'Gerente', 2, 1, '2026-04-21 20:33:35', '2026-04-21 20:33:35', NULL),
(7, 'Supervisor de almacen', 3, 1, '2026-04-29 00:11:01', '2026-04-29 00:11:01', NULL),
(8, 'Aseador', 4, 1, '2026-04-29 00:12:43', '2026-05-29 20:06:12', '2026-05-29 20:06:12'),
(9, 'Costurera', 1, 1, '2026-05-29 20:05:05', '2026-05-29 20:05:05', NULL),
(10, 'Bordador', 1, 1, '2026-05-30 19:46:32', '2026-05-30 19:46:32', NULL);

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
(8, 12, '2025-12-10 20:29:40', '2026-05-29 20:48:46', NULL, 'natural', 1),
(9, 15, '2026-01-17 16:51:52', '2026-01-17 16:51:52', NULL, 'natural', 1),
(10, 16, '2026-01-17 17:11:09', '2026-01-17 17:11:09', NULL, 'natural', 1),
(11, 17, '2026-01-17 22:05:23', '2026-01-17 22:10:55', '2026-01-17 22:10:55', 'natural', 1),
(12, 18, '2026-01-17 22:31:33', '2026-01-17 22:31:33', NULL, 'natural', 1),
(13, 19, '2026-01-18 03:49:00', '2026-01-18 03:54:29', '2026-01-18 03:54:29', 'natural', 1),
(14, 20, '2026-01-18 03:56:57', '2026-01-18 03:56:57', NULL, 'natural', 1),
(15, 29, '2026-01-19 00:25:34', '2026-06-04 18:11:05', '2026-06-04 18:11:05', 'natural', 1),
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
(38, 61, '2026-04-29 00:22:53', '2026-04-29 00:22:53', NULL, 'natural', 1),
(39, 25, '2026-04-30 20:32:50', '2026-04-30 20:32:50', NULL, 'natural', 1),
(40, 4, '2026-04-30 20:34:42', '2026-04-30 20:34:42', NULL, 'natural', 1),
(41, 65, '2026-04-30 20:36:55', '2026-04-30 20:36:55', NULL, 'natural', 1),
(42, 13, '2026-05-28 03:49:39', '2026-05-28 03:49:39', NULL, 'natural', 1),
(43, 70, '2026-06-06 14:22:07', '2026-06-06 14:24:01', NULL, 'natural', 1),
(44, 71, '2026-06-06 15:47:42', '2026-06-06 15:47:42', NULL, 'natural', 1);

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
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `iva` decimal(10,2) NOT NULL DEFAULT 0.00,
  `iva_porcentaje` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tasa_cambio` decimal(12,4) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `observaciones` text DEFAULT NULL,
  `estado` enum('borrador','recibida','anulada') NOT NULL DEFAULT 'recibida',
  `clonada` tinyint(1) NOT NULL DEFAULT 0,
  `anulado_por_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fecha_anulacion` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `compra`
--

INSERT INTO `compra` (`id`, `proveedor_id`, `user_id`, `numero_factura`, `fecha_compra`, `subtotal`, `iva`, `iva_porcentaje`, `tasa_cambio`, `total`, `observaciones`, `estado`, `clonada`, `anulado_por_id`, `fecha_anulacion`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, '0001', '2026-06-09', 9500.00, 1520.00, 16.00, 567.6828, 11020.00, NULL, 'recibida', 0, NULL, NULL, '2026-06-10 01:41:22', '2026-06-10 01:41:45', NULL);

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
  `costo_unitario_bs` decimal(14,2) DEFAULT NULL,
  `aplica_iva` tinyint(1) NOT NULL DEFAULT 1,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `compra_detalle`
--

INSERT INTO `compra_detalle` (`id`, `compra_id`, `insumo_id`, `cantidad`, `costo_unitario`, `costo_unitario_bs`, `aplica_iva`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 500.00, 1.00, 567.68, 1, 500.00, '2026-06-10 01:41:22', '2026-06-10 01:41:22'),
(2, 1, 8, 500.00, 18.00, 10218.29, 1, 9000.00, '2026-06-10 01:41:22', '2026-06-10 01:41:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `clave` varchar(191) NOT NULL,
  `valor` varchar(191) NOT NULL,
  `updated_by_id` bigint(20) UNSIGNED DEFAULT NULL,
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
(1, 23, '2026-06-06', '2026-06-22', 'Convertida', 352.00, 563.2892, 'Para el director de compañia de tecnologia', NULL, 7, 'Normal', '2026-06-07 03:23:23', '2026-06-07 06:02:13', NULL),
(2, 14, '2026-06-07', '2026-06-22', 'Convertida', 70.00, 563.2892, 'Para encargada de grupo musical', NULL, 7, 'Normal', '2026-06-07 17:22:52', '2026-06-07 17:24:05', NULL),
(3, 42, '2026-06-07', '2026-06-22', 'Convertida', 62.00, 563.2892, 'Para evento', NULL, 7, 'Normal', '2026-06-07 17:25:28', '2026-06-07 17:26:16', NULL),
(4, 9, '2026-06-09', '2026-06-24', 'Convertida', 264.00, 567.6828, NULL, NULL, 1, 'Normal', '2026-06-09 19:54:20', '2026-06-09 20:55:33', NULL);

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
(2, 'Administracion', 1, '2026-04-21 20:16:52', '2026-04-21 20:16:52', NULL),
(3, 'Logistica', 1, '2026-04-29 00:10:36', '2026-04-29 00:10:36', NULL),
(4, 'Mantenimiento', 1, '2026-04-29 00:12:02', '2026-04-29 00:12:02', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_cotizacion`
--

CREATE TABLE `detalle_cotizacion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cotizacion_id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo_producto_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tela_snapshot` longtext DEFAULT NULL,
  `atributos_snapshot` longtext DEFAULT NULL,
  `sku_snapshot` varchar(100) DEFAULT NULL,
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

INSERT INTO `detalle_cotizacion` (`id`, `cotizacion_id`, `producto_id`, `tipo_producto_id`, `tela_snapshot`, `atributos_snapshot`, `sku_snapshot`, `cantidad`, `descripcion`, `lleva_bordado`, `color_id`, `talla_id`, `precio_unitario`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 10, '{\"id\":10,\"nombre\":\"Gabardina \\/ Dril\",\"codigo\":\"GBD\",\"costo_unitario\":22,\"unidad_medida\":\"Metro\",\"snapshot_at\":\"2026-06-06\"}', '{\"Modelo Delantal\":\"2 bolsillos\"}', 'DLNT-GBD-2BL-001', 11, NULL, 1, 11, 11, 32.00, '2026-06-07 03:23:23', '2026-06-07 03:23:23'),
(2, 2, NULL, 1, '{\"id\":3,\"nombre\":\"Pique\",\"codigo\":\"PIQ\",\"costo_unitario\":50,\"unidad_medida\":\"Kg\",\"snapshot_at\":\"2026-06-07\"}', '{\"Manga\":\"Corta\",\"Cuello\":\"Cl\\u00e1sico\",\"Corte\":\"Clasico\"}', 'CHM-PIQ-C-CLA-CL-001', 2, NULL, 0, 2, 11, 14.00, '2026-06-07 17:22:52', '2026-06-07 17:22:52'),
(3, 2, NULL, 2, '{\"id\":5,\"nombre\":\"Jersey\",\"codigo\":\"AJR\",\"costo_unitario\":3,\"unidad_medida\":\"Kg\",\"snapshot_at\":\"2026-06-07\"}', '{\"Manga\":\"Corta\",\"Cuello\":\"Redondo\"}', 'FRN-AJR-C-R-001', 2, NULL, 0, 2, 11, 11.00, '2026-06-07 17:22:52', '2026-06-07 17:22:52'),
(4, 2, NULL, 9, NULL, '{\"Modelo de Gorra\":\"Gorra Clasica\"}', 'GO-GC-001', 2, NULL, 0, 2, 1, 10.00, '2026-06-07 17:22:52', '2026-06-07 17:22:52'),
(5, 3, NULL, 1, '{\"id\":3,\"nombre\":\"Pique\",\"codigo\":\"PIQ\",\"costo_unitario\":50,\"unidad_medida\":\"Kg\",\"snapshot_at\":\"2026-06-07\"}', '{\"Manga\":\"Corta\",\"Cuello\":\"Redondo\",\"Corte\":\"Clasico\"}', 'CHM-PIQ-C-R-CL-001', 1, NULL, 0, 6, 11, 62.00, '2026-06-07 17:25:28', '2026-06-07 17:25:28'),
(6, 4, NULL, 3, '{\"id\":8,\"nombre\":\"Oxford\",\"codigo\":\"OXF\",\"costo_unitario\":18,\"unidad_medida\":\"Metro\",\"snapshot_at\":\"2026-06-09\"}', '{\"Manga\":\"Corta\",\"Cuello\":\"Con Tapa Botones\",\"Corte\":\"Columbia I\"}', 'CAM-OXF-C-CTB-CLM1-001', 4, NULL, 0, 10, 11, 33.00, '2026-06-09 19:54:20', '2026-06-09 19:54:20'),
(7, 4, NULL, 3, '{\"id\":8,\"nombre\":\"Oxford\",\"codigo\":\"OXF\",\"costo_unitario\":18,\"unidad_medida\":\"Metro\",\"snapshot_at\":\"2026-06-09\"}', '{\"Manga\":\"Corta\",\"Cuello\":\"Con Tapa Botones\",\"Corte\":\"Columbia I\"}', 'CAM-OXF-C-CTB-CLM1-001', 4, NULL, 0, 10, 12, 33.00, '2026-06-09 19:54:20', '2026-06-09 19:54:20');

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
(1, 1, 1, 5, 'Frontal Izquierdo', 'Alcaldia Municipal', 0, 1, 3.00, 0, '2026-06-07 03:23:23', '2026-06-07 03:23:23');

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
(1, 1, 10, 16.50, 0.00, '2026-06-07 15:54:52', '2026-06-07 15:54:52'),
(2, 1, 14, 11.00, 0.00, '2026-06-07 15:54:52', '2026-06-07 15:54:52'),
(3, 1, 16, 11.00, 0.00, '2026-06-07 15:54:52', '2026-06-07 15:54:52'),
(4, 1, 17, 1100.00, 0.00, '2026-06-07 15:54:52', '2026-06-07 15:54:52'),
(5, 2, 3, 4.00, 0.00, '2026-06-07 17:55:58', '2026-06-07 17:55:58'),
(6, 3, 5, 4.00, 0.00, '2026-06-07 17:55:58', '2026-06-07 17:55:58'),
(8, 4, 3, 3.00, 0.00, '2026-06-07 18:02:53', '2026-06-07 18:02:53'),
(17, 12, 8, 8.00, 8.00, '2026-06-10 02:06:03', '2026-06-10 02:06:03'),
(18, 12, 2, 32.00, 32.00, '2026-06-10 02:06:03', '2026-06-10 02:06:03'),
(19, 13, 8, 8.00, 8.00, '2026-06-10 02:06:03', '2026-06-10 02:06:03'),
(20, 13, 2, 32.00, 32.00, '2026-06-10 02:06:03', '2026-06-10 02:06:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pedido_id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo_producto_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tela_snapshot` longtext DEFAULT NULL,
  `atributos_snapshot` longtext DEFAULT NULL,
  `sku_snapshot` varchar(100) DEFAULT NULL,
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

INSERT INTO `detalle_pedido` (`id`, `pedido_id`, `producto_id`, `tipo_producto_id`, `tela_snapshot`, `atributos_snapshot`, `sku_snapshot`, `cantidad`, `descripcion`, `lleva_bordado`, `color_id`, `talla_id`, `precio_unitario`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 10, '{\"id\":10,\"nombre\":\"Gabardina \\/ Dril\",\"codigo\":\"GBD\",\"costo_unitario\":22,\"unidad_medida\":\"Metro\",\"snapshot_at\":\"2026-06-07\"}', '{\"Modelo Delantal\":\"2 bolsillos\"}', 'DLNT-GBD-2BL-001', 11, NULL, 1, 11, 11, 32.00, '2026-06-07 06:02:13', '2026-06-07 06:02:13'),
(2, 2, NULL, 1, '{\"id\":3,\"nombre\":\"Pique\",\"codigo\":\"PIQ\",\"costo_unitario\":50,\"unidad_medida\":\"Kg\",\"snapshot_at\":\"2026-06-07\"}', '{\"Manga\":\"Corta\",\"Cuello\":\"Cl\\u00e1sico\",\"Corte\":\"Clasico\"}', 'CHM-PIQ-C-CLA-CL-001', 2, NULL, 0, 2, 11, 14.00, '2026-06-07 17:24:05', '2026-06-07 17:24:05'),
(3, 2, NULL, 2, '{\"id\":5,\"nombre\":\"Jersey\",\"codigo\":\"AJR\",\"costo_unitario\":3,\"unidad_medida\":\"Kg\",\"snapshot_at\":\"2026-06-07\"}', '{\"Manga\":\"Corta\",\"Cuello\":\"Redondo\"}', 'FRN-AJR-C-R-001', 2, NULL, 0, 2, 11, 11.00, '2026-06-07 17:24:05', '2026-06-07 17:24:05'),
(4, 2, NULL, 9, NULL, '{\"Modelo de Gorra\":\"Gorra Clasica\"}', 'GO-GC-001', 2, NULL, 0, 2, 1, 10.00, '2026-06-07 17:24:05', '2026-06-07 17:24:05'),
(5, 3, NULL, 1, '{\"id\":3,\"nombre\":\"Pique\",\"codigo\":\"PIQ\",\"costo_unitario\":50,\"unidad_medida\":\"Kg\",\"snapshot_at\":\"2026-06-07\"}', '{\"Manga\":\"Corta\",\"Cuello\":\"Redondo\",\"Corte\":\"Clasico\"}', 'CHM-PIQ-C-R-CL-001', 1, NULL, 0, 6, 11, 62.00, '2026-06-07 17:26:16', '2026-06-07 17:26:16'),
(6, 6, NULL, 3, '{\"id\":8,\"nombre\":\"Oxford\",\"codigo\":\"OXF\",\"costo_unitario\":18,\"unidad_medida\":\"Metro\",\"snapshot_at\":\"2026-06-09\"}', '{\"Manga\":\"Corta\",\"Cuello\":\"Con Tapa Botones\",\"Corte\":\"Columbia I\"}', 'CAM-OXF-C-CTB-CLM1-001', 4, NULL, 0, 10, 11, 33.00, '2026-06-09 20:55:33', '2026-06-09 20:55:33'),
(7, 6, NULL, 3, '{\"id\":8,\"nombre\":\"Oxford\",\"codigo\":\"OXF\",\"costo_unitario\":18,\"unidad_medida\":\"Metro\",\"snapshot_at\":\"2026-06-09\"}', '{\"Manga\":\"Corta\",\"Cuello\":\"Con Tapa Botones\",\"Corte\":\"Columbia I\"}', 'CAM-OXF-C-CTB-CLM1-001', 4, NULL, 0, 10, 12, 33.00, '2026-06-09 20:55:33', '2026-06-09 20:55:33');

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
(1, 1, 1, 5, 'Frontal Izquierdo', 'Alcaldia Municipal', 0, 1, 3.00, 0, '2026-06-07 06:02:13', '2026-06-07 06:02:13');

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado`
--

CREATE TABLE `estado` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estado`
--

INSERT INTO `estado` (`id`, `nombre`) VALUES
(1, 'Amazonas'),
(2, 'Anzoátegui'),
(3, 'Apure'),
(4, 'Aragua'),
(5, 'Barinas'),
(6, 'Bolívar'),
(7, 'Carabobo'),
(8, 'Cojedes'),
(9, 'Delta Amacuro'),
(10, 'Distrito Capital'),
(11, 'Falcón'),
(12, 'Guárico'),
(13, 'La Guaira'),
(14, 'Lara'),
(15, 'Mérida'),
(16, 'Miranda'),
(17, 'Monagas'),
(18, 'Nueva Esparta'),
(19, 'Portuguesa'),
(20, 'Sucre'),
(21, 'Táchira'),
(22, 'Trujillo'),
(23, 'Yaracuy'),
(24, 'Zulia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `municipio`
--

CREATE TABLE `municipio` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `estado_id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `municipio`
--

INSERT INTO `municipio` (`id`, `estado_id`, `nombre`) VALUES
(1, 1, 'Alto Orinoco'),
(2, 1, 'Atabapo'),
(3, 1, 'Atures'),
(4, 1, 'Autana'),
(5, 1, 'Manapiare'),
(6, 1, 'Maroa'),
(7, 1, 'Río Negro'),
(8, 2, 'Anaco'),
(9, 2, 'Aragua'),
(10, 2, 'Bolívar'),
(11, 2, 'Bruzual'),
(12, 2, 'Cajigal'),
(13, 2, 'Carvajal'),
(14, 2, 'Diego Bautista Urbaneja'),
(15, 2, 'Freites'),
(16, 2, 'Guanipa'),
(17, 2, 'Guanta'),
(18, 2, 'Independencia'),
(19, 2, 'Libertad'),
(20, 2, 'McGregor'),
(21, 2, 'Miranda'),
(22, 2, 'Monagas'),
(23, 2, 'Peñalver'),
(24, 2, 'Píritu'),
(25, 2, 'San José de Guanipa'),
(26, 2, 'San Juan de Capistrano'),
(27, 2, 'Santa Ana'),
(28, 2, 'Simón Rodríguez'),
(29, 2, 'Sotillo'),
(30, 3, 'Achaguas'),
(31, 3, 'Biruaca'),
(32, 3, 'Muñoz'),
(33, 3, 'Páez'),
(34, 3, 'Pedro Camejo'),
(35, 3, 'Rómulo Gallegos'),
(36, 3, 'San Fernando'),
(37, 4, 'Bolívar'),
(38, 4, 'Camatagua'),
(39, 4, 'Francisco Linares Alcántara'),
(40, 4, 'Girardot'),
(41, 4, 'José Ángel Lamas'),
(42, 4, 'José Félix Ribas'),
(43, 4, 'José Rafael Revenga'),
(44, 4, 'Libertador'),
(45, 4, 'Mario Briceño Iragorry'),
(46, 4, 'Ocumare de la Costa de Oro'),
(47, 4, 'San Casimiro'),
(48, 4, 'San Sebastián'),
(49, 4, 'Santiago Mariño'),
(50, 4, 'Santos Michelena'),
(51, 4, 'Sucre'),
(52, 4, 'Tovar'),
(53, 4, 'Urdaneta'),
(54, 4, 'Zamora'),
(55, 5, 'Alberto Arvelo Torrealba'),
(56, 5, 'Andrés Eloy Blanco'),
(57, 5, 'Antonio José de Sucre'),
(58, 5, 'Arismendi'),
(59, 5, 'Barinas'),
(60, 5, 'Bolívar'),
(61, 5, 'Cruz Paredes'),
(62, 5, 'Ezequiel Zamora'),
(63, 5, 'Obispos'),
(64, 5, 'Pedraza'),
(65, 5, 'Rojas'),
(66, 5, 'Sosa'),
(67, 6, 'Caroní'),
(68, 6, 'Cedeño'),
(69, 6, 'El Callao'),
(70, 6, 'Gran Sabana'),
(71, 6, 'Heres'),
(72, 6, 'Piar'),
(73, 6, 'Angostura del Orinoco'),
(74, 6, 'Roscio'),
(75, 6, 'Sifontes'),
(76, 6, 'Sucre'),
(77, 6, 'Padre Pedro Chien'),
(78, 7, 'Bejuma'),
(79, 7, 'Carlos Arvelo'),
(80, 7, 'Diego Ibarra'),
(81, 7, 'Guacara'),
(82, 7, 'Juan José Mora'),
(83, 7, 'Libertador'),
(84, 7, 'Los Guayos'),
(85, 7, 'Miranda'),
(86, 7, 'Montalbán'),
(87, 7, 'Naguanagua'),
(88, 7, 'Puerto Cabello'),
(89, 7, 'San Diego'),
(90, 7, 'San Joaquín'),
(91, 7, 'Valencia'),
(92, 8, 'Anzoátegui'),
(93, 8, 'Ezequiel Zamora'),
(94, 8, 'Falcón'),
(95, 8, 'Girardot'),
(96, 8, 'Lima Blanco'),
(97, 8, 'Pao de San Juan Bautista'),
(98, 8, 'Ricaurte'),
(99, 8, 'Rómulo Gallegos'),
(100, 8, 'Tinaco'),
(101, 9, 'Antonio Díaz'),
(102, 9, 'Casacoima'),
(103, 9, 'Pedernales'),
(104, 9, 'Tucupita'),
(105, 10, 'Libertador'),
(106, 11, 'Acosta'),
(107, 11, 'Bolívar'),
(108, 11, 'Buchivacoa'),
(109, 11, 'Cacique Manaure'),
(110, 11, 'Carirubana'),
(111, 11, 'Colina'),
(112, 11, 'Dabajuro'),
(113, 11, 'Democracia'),
(114, 11, 'Falcón'),
(115, 11, 'Federación'),
(116, 11, 'Jacura'),
(117, 11, 'Los Taques'),
(118, 11, 'Mauroa'),
(119, 11, 'Miranda'),
(120, 11, 'Monseñor Iturriza'),
(121, 11, 'Palmasola'),
(122, 11, 'Petit'),
(123, 11, 'Píritu'),
(124, 11, 'San Francisco'),
(125, 11, 'Silva'),
(126, 11, 'Sucre'),
(127, 11, 'Tocópero'),
(128, 11, 'Unión'),
(129, 11, 'Urumaco'),
(130, 11, 'Zamora'),
(131, 12, 'Camaguán'),
(132, 12, 'Chaguaramas'),
(133, 12, 'El Socorro'),
(134, 12, 'Infante'),
(135, 12, 'Las Mercedes'),
(136, 12, 'Leonardo Infante'),
(137, 12, 'Mellado'),
(138, 12, 'Miranda'),
(139, 12, 'Monagas'),
(140, 12, 'Ortíz'),
(141, 12, 'Ribas'),
(142, 12, 'Roscio'),
(143, 12, 'San Gerónimo de Guayabal'),
(144, 12, 'San José de Guaribe'),
(145, 12, 'Santa María de Ipire'),
(146, 12, 'Zaraza'),
(147, 13, 'Vargas'),
(148, 14, 'Andrés Eloy Blanco'),
(149, 14, 'Crespo'),
(150, 14, 'Iribarren'),
(151, 14, 'Jiménez'),
(152, 14, 'Morán'),
(153, 14, 'Palavecino'),
(154, 14, 'Simón Planas'),
(155, 14, 'Torres'),
(156, 14, 'Urdaneta'),
(157, 15, 'Alberto Adriani'),
(158, 15, 'Andrés Bello'),
(159, 15, 'Antonio Pinto Salinas'),
(160, 15, 'Aricagua'),
(161, 15, 'Arzobispo Chacón'),
(162, 15, 'Campo Elías'),
(163, 15, 'Caracciolo Parra Olmedo'),
(164, 15, 'Cardenal Quintero'),
(165, 15, 'Guaraque'),
(166, 15, 'Julio César Salas'),
(167, 15, 'Justo Briceño'),
(168, 15, 'Libertador'),
(169, 15, 'Miranda'),
(170, 15, 'Obispo Ramos de Lora'),
(171, 15, 'Padre Noguera'),
(172, 15, 'Pueblo Llano'),
(173, 15, 'Rangel'),
(174, 15, 'Rivas Dávila'),
(175, 15, 'Santos Marquina'),
(176, 15, 'Sucre'),
(177, 15, 'Tovar'),
(178, 15, 'Tulio Febres Cordero'),
(179, 15, 'Zea'),
(180, 16, 'Acevedo'),
(181, 16, 'Andrés Bello'),
(182, 16, 'Baruta'),
(183, 16, 'Brión'),
(184, 16, 'Buroz'),
(185, 16, 'Carrizal'),
(186, 16, 'Chacao'),
(187, 16, 'Cristóbal Rojas'),
(188, 16, 'El Hatillo'),
(189, 16, 'Guaicaipuro'),
(190, 16, 'Independencia'),
(191, 16, 'Lander'),
(192, 16, 'Los Salias'),
(193, 16, 'Páez'),
(194, 16, 'Paz Castillo'),
(195, 16, 'Pedro Gual'),
(196, 16, 'Plaza'),
(197, 16, 'Simón Bolívar'),
(198, 16, 'Sucre'),
(199, 16, 'Urdaneta'),
(200, 16, 'Zamora'),
(201, 17, 'Acosta'),
(202, 17, 'Aguasay'),
(203, 17, 'Bolívar'),
(204, 17, 'Caripe'),
(205, 17, 'Cedeño'),
(206, 17, 'Ezequiel Zamora'),
(207, 17, 'Libertador'),
(208, 17, 'Maturín'),
(209, 17, 'Piar'),
(210, 17, 'Punceres'),
(211, 17, 'Santa Bárbara'),
(212, 17, 'Sotillo'),
(213, 17, 'Uracoa'),
(214, 18, 'Antolín del Campo'),
(215, 18, 'Arismendi'),
(216, 18, 'Díaz'),
(217, 18, 'García'),
(218, 18, 'Gómez'),
(219, 18, 'Maneiro'),
(220, 18, 'Marcano'),
(221, 18, 'Mariño'),
(222, 18, 'Península de Macanao'),
(223, 18, 'Tubores'),
(224, 18, 'Villalba'),
(225, 19, 'Agua Blanca'),
(226, 19, 'Araure'),
(227, 19, 'Esteller'),
(228, 19, 'Guanare'),
(229, 19, 'Guanarito'),
(230, 19, 'Monseñor José Vicente de Unda'),
(231, 19, 'Ospino'),
(232, 19, 'Páez'),
(233, 19, 'Papelón'),
(234, 19, 'San Genaro de Boconoíto'),
(235, 19, 'San Rafael de Onoto'),
(236, 19, 'Santa Rosalía'),
(237, 19, 'Sucre'),
(238, 19, 'Turén'),
(239, 20, 'Andrés Eloy Blanco'),
(240, 20, 'Andrés Mata'),
(241, 20, 'Arismendi'),
(242, 20, 'Benítez'),
(243, 20, 'Bermúdez'),
(244, 20, 'Bolívar'),
(245, 20, 'Cajigal'),
(246, 20, 'Cruz Salmerón Acosta'),
(247, 20, 'Libertador'),
(248, 20, 'Mariño'),
(249, 20, 'Mejía'),
(250, 20, 'Montes'),
(251, 20, 'Ribero'),
(252, 20, 'Sucre'),
(253, 20, 'Valdez'),
(254, 21, 'Andrés Bello'),
(255, 21, 'Antonio Rómulo Costa'),
(256, 21, 'Ayacucho'),
(257, 21, 'Bolívar'),
(258, 21, 'Cárdenas'),
(259, 21, 'Córdoba'),
(260, 21, 'Fernández Feo'),
(261, 21, 'Francisco de Miranda'),
(262, 21, 'García de Hevia'),
(263, 21, 'Guásimos'),
(264, 21, 'Independencia'),
(265, 21, 'Jáuregui'),
(266, 21, 'José María Vargas'),
(267, 21, 'Junín'),
(268, 21, 'Libertad'),
(269, 21, 'Libertador'),
(270, 21, 'Lobatera'),
(271, 21, 'Michelena'),
(272, 21, 'Panamericano'),
(273, 21, 'Pedro María Ureña'),
(274, 21, 'Rafael Urdaneta'),
(275, 21, 'Samuel Darío Maldonado'),
(276, 21, 'San Cristóbal'),
(277, 21, 'Seboruco'),
(278, 21, 'Simón Rodríguez'),
(279, 21, 'Sucre'),
(280, 21, 'Torbes'),
(281, 21, 'Uribante'),
(282, 21, 'San Judas Tadeo'),
(283, 22, 'Andrés Bello'),
(284, 22, 'Boconó'),
(285, 22, 'Bolívar'),
(286, 22, 'Candelaria'),
(287, 22, 'Carache'),
(288, 22, 'Escuque'),
(289, 22, 'José Felipe Márquez Cañizales'),
(290, 22, 'Juan Vicente Campo Elías'),
(291, 22, 'La Ceiba'),
(292, 22, 'Miranda'),
(293, 22, 'Monte Carmelo'),
(294, 22, 'Motatán'),
(295, 22, 'Pampán'),
(296, 22, 'Pampanito'),
(297, 22, 'Rafael Rangel'),
(298, 22, 'San Rafael de Carvajal'),
(299, 22, 'Sucre'),
(300, 22, 'Trujillo'),
(301, 22, 'Urdaneta'),
(302, 22, 'Valera'),
(303, 23, 'Arístides Bastidas'),
(304, 23, 'Bolívar'),
(305, 23, 'Bruzual'),
(306, 23, 'Cocorote'),
(307, 23, 'Independencia'),
(308, 23, 'José Antonio Páez'),
(309, 23, 'La Trinidad'),
(310, 23, 'Manuel Monge'),
(311, 23, 'Nirgua'),
(312, 23, 'Peña'),
(313, 23, 'San Felipe'),
(314, 23, 'Sucre'),
(315, 23, 'Urachiche'),
(316, 23, 'Veroes'),
(317, 24, 'Almirante Padilla'),
(318, 24, 'Baralt'),
(319, 24, 'Cabimas'),
(320, 24, 'Catatumbo'),
(321, 24, 'Colón'),
(322, 24, 'Francisco Javier Pulgar'),
(323, 24, 'Jesús Enrique Lossada'),
(324, 24, 'Jesús María Semprún'),
(325, 24, 'La Cañada de Urdaneta'),
(326, 24, 'Lagunillas'),
(327, 24, 'Machiques de Perijá'),
(328, 24, 'Mara'),
(329, 24, 'Maracaibo'),
(330, 24, 'Miranda'),
(331, 24, 'Páez'),
(332, 24, 'Rosario de Perijá'),
(333, 24, 'San Francisco'),
(334, 24, 'Santa Rita'),
(335, 24, 'Simón Bolívar'),
(336, 24, 'Sucre'),
(337, 24, 'Valmore Rodríguez');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccion`
--

CREATE TABLE `direccion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `persona_id` bigint(20) UNSIGNED NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `estado_id` bigint(20) UNSIGNED DEFAULT NULL,
  `municipio_id` bigint(20) UNSIGNED DEFAULT NULL,
  `es_principal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `direccion`
--

INSERT INTO `direccion` (`id`, `persona_id`, `direccion`, `estado_id`, `municipio_id`, `es_principal`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'La Goajira', NULL, NULL, 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(2, 3, 'Washington DC', NULL, NULL, 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(3, 4, 'Urbanización Fundación MendozaAvenida 7, Calle Principal', NULL, NULL, 1, '2025-12-10 18:42:20', '2026-05-29 20:05:28', NULL),
(4, 6, 'Wall Street', NULL, NULL, 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(5, 7, 'Sillycon Valley', NULL, NULL, 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(6, 8, 'Dayton', NULL, NULL, 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(7, 9, 'Headington Hill Hall, Reino Unido', NULL, NULL, 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(8, 10, 'San Tomas Expressway', NULL, NULL, 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(9, 11, 'Denver', NULL, NULL, 1, '2025-12-10 18:42:20', '2025-12-10 18:42:20', NULL),
(10, 12, 'Crescent Park, 11 Avenue Palo Alto', 19, 226, 1, '2025-12-10 20:29:40', '2026-01-18 23:37:09', NULL),
(11, 14, 'Avenue 10', NULL, NULL, 1, '2025-12-10 21:09:58', '2025-12-10 21:09:58', NULL),
(12, 13, 'villas', NULL, NULL, 1, '2025-12-10 21:17:57', '2026-06-03 04:12:10', NULL),
(13, 15, 'Urb prados del sol', NULL, NULL, 1, '2026-01-17 16:51:52', '2026-01-17 16:51:52', NULL),
(14, 16, 'prados del sol', NULL, NULL, 1, '2026-01-17 17:11:09', '2026-01-17 17:11:09', NULL),
(15, 18, 'Urb prados del sol', NULL, NULL, 1, '2026-01-17 22:31:33', '2026-01-17 22:31:33', NULL),
(17, 20, 'Urb. Los Cortijos', 19, 232, 1, '2026-01-18 03:56:57', '2026-01-18 03:56:57', NULL),
(19, 22, 'Avenida los Pescadores calle 5', 19, 226, 1, '2026-01-18 20:23:50', '2026-01-18 20:23:50', NULL),
(20, 23, 'Urb. Los Pinos, Calle 3, Casa 15, Acarigua', 19, 232, 1, '2026-01-18 20:37:01', '2026-01-18 20:37:01', NULL),
(21, 24, 'Av. Principal, Edif. Sol, Apto 4B, Araure', 19, 226, 1, '2026-01-18 20:38:40', '2026-01-18 20:38:40', NULL),
(22, 25, 'carlossilva@gmail.com', 19, 228, 1, '2026-01-18 20:41:05', '2026-01-18 20:41:05', NULL),
(23, 26, 'Urb. El Recreo, Calle 10, Casa 8, Barinas', 5, 59, 1, '2026-01-18 20:43:53', '2026-01-18 20:43:53', NULL),
(24, 27, 'Urb. Los Samanes, Calle 5, Casa 12, Sector Centro', 19, 231, 1, '2026-01-18 20:57:49', '2026-01-18 20:57:49', NULL),
(25, 28, 'Calle 8, Casa 22, Sector La Barinesa, Acarigua', 19, 232, 1, '2026-01-18 21:52:28', '2026-01-18 21:52:28', NULL),
(26, 29, 'Urb prados del sol', 19, 226, 1, '2026-01-19 00:25:34', '2026-01-19 00:25:34', NULL),
(27, 30, 'agua clar', 19, 226, 1, '2026-01-19 03:56:04', '2026-01-19 03:56:04', NULL),
(28, 31, 'agua', 19, 226, 1, '2026-01-19 04:01:50', '2026-01-19 04:01:50', NULL),
(29, 32, 'Urb prados del sol', 19, 226, 1, '2026-01-19 04:05:33', '2026-01-19 04:05:33', NULL),
(30, 33, 'Urb prados del sol', 19, 226, 1, '2026-01-19 04:17:44', '2026-01-19 04:17:44', NULL),
(31, 34, 'Urb prados del sol', 19, 227, 1, '2026-01-19 04:26:34', '2026-01-19 04:26:34', NULL),
(32, 35, 'Urb prados del sol', 19, 226, 1, '2026-01-19 16:49:16', '2026-01-19 16:49:16', NULL),
(33, 36, 'Urb. villas del pilar', 19, 226, 1, '2026-01-20 01:29:08', '2026-01-20 01:29:08', NULL),
(34, 37, 'Fundacion Mendoza', 19, 232, 1, '2026-01-20 21:23:34', '2026-01-20 21:23:34', NULL),
(35, 38, 'Urbanizacion Bosques de Camoruco, Av 7 Calle Principal', 19, 232, 1, '2026-02-22 17:56:13', '2026-02-22 17:56:13', NULL),
(36, 39, 'Calle 15 con Av. Bolívar, Casa #12', 19, NULL, 1, '2026-02-26 19:10:25', '2026-02-26 19:10:25', NULL),
(37, 40, 'Urb. Las Acacias, Calle 3, Casa #8', 19, 226, 1, '2026-02-26 19:10:25', '2026-02-26 19:10:25', NULL),
(38, 42, 'Zona Industrial II, Galpón 5', 7, 91, 1, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(39, 43, 'Av. Principal de Páez, Edificio Don Luis, Piso 2', 19, NULL, 1, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(40, 44, 'Calle 30 entre Av. 27 y 28, Local 4', 19, 235, 1, '2026-02-26 19:11:43', '2026-02-26 20:21:46', NULL),
(41, 45, 'Urb. Villa del Pilar, Calle 10, Casa 25', 19, 226, 1, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(42, 46, 'Barrio Sucre, Calle Principal, Casa S/N', 5, 59, 1, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(43, 47, 'Centro Empresarial La Paz, Oficina 302', 16, 199, 1, '2026-02-26 19:11:43', '2026-02-26 20:23:03', NULL),
(44, 48, 'Urb. Prados del Sol, Calle 5, Casa 14-B', 19, 226, 1, '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(45, 49, 'Sector Los Cortijos, Vereda 3, Casa 7', 19, 232, 1, '2026-02-26 19:11:44', '2026-02-26 19:11:44', NULL),
(46, 50, 'Avenida, Calle, Casa', 19, 232, 1, '2026-02-26 19:46:04', '2026-02-26 19:46:04', NULL),
(47, 51, 'Diagonal Urbanizacion Altos de la Galera', 19, 232, 1, '2026-03-05 17:17:09', '2026-03-05 17:17:09', NULL),
(48, 53, 'Av. Industrial 123, Venezuela', NULL, NULL, 1, '2026-03-19 18:21:05', '2026-03-19 18:21:05', NULL),
(49, 54, 'Torre Jalisco, Las Mercedes', NULL, NULL, 1, '2026-03-19 18:21:05', '2026-03-19 18:21:05', NULL),
(50, 55, 'Zona Industrial II, Galpón 15, Acarigua', NULL, NULL, 1, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(51, 56, 'Av. Bolívar, Local 23, Araure', NULL, NULL, 1, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(52, 57, 'Calle 5, CC Los Llanos, Valencia', NULL, NULL, 1, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(53, 58, 'Av. Libertador, Edif. Comercial, Piso 2, Barquisimeto', NULL, NULL, 1, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(54, 59, 'Calle Principal, Galpón 8, Mérida', NULL, NULL, 1, '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(55, 60, 'La goajira', 19, 228, 1, '2026-04-14 21:26:32', '2026-04-14 21:26:32', NULL),
(56, 61, 'Roca del llano', 19, 232, 1, '2026-04-29 00:22:53', '2026-04-29 00:22:53', NULL),
(57, 62, 'Prados del Sol', 19, 232, 1, '2026-04-29 00:25:48', '2026-04-29 00:25:48', NULL),
(60, 65, 'Urb San Jose', NULL, NULL, 1, '2026-04-30 20:25:42', '2026-04-30 20:25:42', NULL),
(61, 66, 'Zona Sur', NULL, NULL, 1, '2026-05-30 19:47:16', '2026-05-30 19:47:16', NULL),
(62, 67, 'Urb San Jose', NULL, NULL, 1, '2026-05-30 19:50:52', '2026-05-30 19:50:52', NULL),
(63, 68, 'Urb  San Jose II', NULL, NULL, 1, '2026-05-30 19:55:30', '2026-05-30 19:55:30', NULL),
(64, 69, 'Urb San Jose', NULL, NULL, 1, '2026-05-30 20:00:52', '2026-05-30 20:00:52', NULL),
(65, 70, 'Edif Gina, Araure', 19, 226, 1, '2026-06-06 14:22:07', '2026-06-06 14:22:07', NULL),
(66, 71, 'Urb Durigua 3', 19, 232, 1, '2026-06-06 15:47:42', '2026-06-06 15:47:42', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado`
--

CREATE TABLE `empleado` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `persona_id` bigint(20) UNSIGNED NOT NULL,
  `codigo_empleado` varchar(50) NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` enum('M','F','Otro') DEFAULT NULL,
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

INSERT INTO `empleado` (`id`, `persona_id`, `codigo_empleado`, `fecha_ingreso`, `fecha_nacimiento`, `genero`, `cargo_id`, `departamento_id`, `salario`, `estado`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'EMP-001', '2025-12-04', '2003-04-08', 'M', 1, 1, NULL, 1, '2025-12-04 19:45:19', '2026-01-17 05:25:17', '2026-01-17 05:25:17'),
(2, 3, 'EMP-002', '2025-04-07', '1980-04-04', 'M', 2, 2, 897000.00, 1, '2025-12-04 20:19:19', '2026-01-16 21:58:15', '2026-01-16 21:58:15'),
(3, 4, 'EMP-003', '2025-09-29', '2005-03-31', 'M', 9, 1, NULL, 1, '2025-12-05 20:14:04', '2026-05-29 20:28:22', NULL),
(4, 13, 'EMP-004', '2025-05-08', '2005-01-11', 'M', 9, 1, NULL, 1, '2025-12-10 20:57:36', '2026-06-06 19:41:37', '2026-06-06 19:41:37'),
(5, 14, 'EMP-005', '2025-12-10', '2004-03-10', 'M', 5, 1, NULL, 1, '2025-12-10 21:09:58', '2026-01-16 21:58:10', '2026-01-16 21:58:10'),
(8, 65, 'EMP-006', '2026-04-29', '2002-01-30', 'M', 3, 1, NULL, 1, '2026-04-30 20:25:42', '2026-06-06 19:41:52', NULL),
(9, 66, 'EMP-007', '2021-12-10', '1978-01-29', 'M', 10, 1, NULL, 1, '2026-05-30 19:47:16', '2026-05-30 19:47:16', NULL),
(10, 67, 'EMP-008', '2020-01-15', '1974-01-05', 'F', 9, 1, NULL, 1, '2026-05-30 19:50:52', '2026-05-30 19:50:52', NULL),
(11, 68, 'EMP-009', '2024-03-14', '1975-11-30', 'F', 9, 1, NULL, 1, '2026-05-30 19:55:30', '2026-05-30 19:55:30', NULL),
(12, 69, 'EMP-010', '2018-01-15', '1969-04-30', 'F', 9, 1, NULL, 1, '2026-05-30 20:00:52', '2026-05-30 20:00:52', NULL);

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
  `tipo` varchar(50) NOT NULL,
  `unidad_medida` varchar(20) NOT NULL,
  `is_inventoriable` tinyint(1) NOT NULL DEFAULT 1,
  `costo_unitario` decimal(10,2) NOT NULL,
  `aplica_iva` tinyint(1) NOT NULL DEFAULT 1,
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

INSERT INTO `insumo` (`id`, `nombre`, `codigo`, `tipo`, `unidad_medida`, `is_inventoriable`, `costo_unitario`, `aplica_iva`, `stock_actual`, `stock_minimo`, `stock_maximo`, `estado`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'Botón Nacar 18mm', NULL, 'Boton', 'Unidad', 1, 1.00, 1, 546.00, 1000.00, 0.00, 1, '2025-12-04 18:58:28', '2026-06-11 03:22:26', NULL),
(3, 'Pique', 'PIQ', 'Tela', 'Kg', 1, 50.00, 1, 110.00, 5.00, 0.00, 1, '2025-12-11 00:39:02', '2026-06-11 03:22:26', NULL),
(5, 'Jersey', 'AJR', 'Tela', 'Kg', 1, 3.00, 1, 110.00, 10.00, 0.00, 1, '2026-01-20 20:36:23', '2026-06-11 03:22:26', NULL),
(7, 'Dacron', 'DAC', 'Tela', 'Metro', 1, 12.00, 1, 110.00, 0.00, 0.00, 1, '2026-05-07 17:15:29', '2026-06-11 03:22:26', NULL),
(8, 'Oxford', 'OXF', 'Tela', 'Metro', 1, 18.00, 1, 594.00, 0.00, 0.00, 1, '2026-05-07 17:15:29', '2026-06-11 03:22:26', NULL),
(9, 'Microfibra', 'MFB', 'Tela', 'Metro', 1, 14.00, 1, 110.00, 0.00, 0.00, 1, '2026-05-07 17:15:29', '2026-06-11 03:22:26', NULL),
(10, 'Gabardina / Dril', 'GBD', 'Tela', 'Metro', 1, 22.00, 1, 110.00, 0.00, 0.00, 1, '2026-05-07 17:15:29', '2026-06-11 03:22:26', NULL),
(14, 'Etiqueta Atlantico', 'EATL', 'Etiqueta', 'Unidad', 1, 1.00, 1, 610.00, 100.00, 5000.00, 1, '2026-06-07 01:07:14', '2026-06-11 03:22:26', NULL),
(16, 'Cinta para delantal', 'CTDLN', 'Cinta', 'Unidad', 1, 1.00, 1, 210.00, 10.00, 500.00, 1, '2026-06-07 01:55:10', '2026-06-11 03:22:26', NULL),
(17, 'Hilo poliéster', 'HL', 'Hilo', 'Metro', 1, 3.00, 1, 160.00, 10.00, 100.00, 1, '2026-06-07 02:01:57', '2026-06-11 03:22:26', NULL);

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
(106, '2026_05_07_100009_modelo_nullable_in_producto', 55),
(107, '2026_05_07_100010_rename_codigo_prefijo_to_prefijo_in_tipo_producto', 56),
(108, '2026_05_07_100011_drop_modelo_from_producto', 56),
(109, '2026_05_27_220125_op_redesign_orden_produccion_empleado_detalle', 57),
(110, '2026_05_27_222657_op_drop_produccion_diaria_add_defectuosa', 58),
(111, '2026_05_28_004004_add_is_inventoriable_to_insumo_table', 59),
(112, '2026_05_28_185713_drop_proveedor_id_from_insumo', 60),
(113, '2026_05_28_191504_create_tipo_producto_insumo_table', 61),
(114, '2026_05_28_194636_add_consumo_tela_por_unidad_to_tipo_producto', 62),
(115, '2026_05_29_110403_drop_costo_estimado_from_orden_produccion', 63),
(116, '2026_05_31_000000_add_stock_maximo_to_insumo_table', 64),
(117, '2026_06_01_142640_add_tasa_y_condiciones_to_cotizacion_table', 65),
(118, '2026_06_02_000001_create_compra_table', 66),
(119, '2026_06_02_000002_create_compra_detalle_table', 66),
(120, '2026_06_03_000001_add_auditoria_anulacion_to_compra_table', 67),
(122, '2026_06_03_000002_add_clonada_to_compra_table', 68),
(123, '2026_06_03_220000_create_tipo_producto_tela_table', 69),
(124, '2026_06_03_220100_detalle_nullable_producto_add_tipo', 69),
(125, '2026_06_03_220200_add_sku_snapshot_to_detalles', 69),
(126, '2026_06_03_220300_orden_produccion_nullable_producto', 69),
(127, '2026_06_06_000001_add_imagen_to_tipo_producto_table', 70),
(128, '2026_06_06_000002_create_tipo_insumo_and_alter_insumo_tipo', 71),
(129, '2026_06_07_000001_add_requiere_produccion_to_tipo_producto', 72),
(130, '2026_06_07_000001_drop_tipo_pago_y_vencimiento_from_compra_table', 73),
(131, '2026_06_07_000002_create_sub_orden_produccion_tables', 74),
(132, '2026_06_08_000001_add_motivo_cancelacion_to_orden_produccion_table', 75),
(133, '2026_06_08_000002_add_fecha_formalizacion_to_pedido_table', 75),
(134, '2026_06_09_000001_create_orden_produccion_empleado_table', 76),
(135, '2026_06_10_000001_add_aplica_iva_to_insumo_table', 77),
(136, '2026_06_10_000002_add_aplica_iva_to_compra_detalle_table', 77),
(137, '2026_06_10_000003_add_iva_porcentaje_to_compra_table', 77),
(138, '2026_06_12_000001_create_configuracion_table', 78),
(139, '2026_06_10_000004_add_moneda_bs_to_compra_tables', 79),
(144, '2026_06_15_000001_create_rol_table', 80),
(145, '2026_06_15_000002_create_permiso_rol_table', 80),
(146, '2026_06_15_000003_migrate_user_role_enum_to_role_id', 80),
(147, '2026_06_23_000001_drop_apellido_from_persona_table', 81),
(148, '2026_06_23_000002_move_fecha_nacimiento_genero_to_empleado', 82),
(149, '2026_06_24_000001_create_estado_municipio_and_refactor_direccion', 83),
(150, '2026_06_24_000002_drop_tipo_from_direccion_table', 84);

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
(5, 2, 'Entrada', 500.00, 0.00, 500.00, 'Compra #1 — Fact: 0001', 1, '2026-06-10 01:41:45', '2026-06-10 01:41:45'),
(6, 8, 'Entrada', 500.00, 0.00, 500.00, 'Compra #1 — Fact: 0001', 1, '2026-06-10 01:41:45', '2026-06-10 01:41:45'),
(7, 8, 'Salida', 8.00, 500.00, 492.00, 'Consumo de producción — OP #12', 1, '2026-06-10 02:06:03', '2026-06-10 02:06:03'),
(8, 2, 'Salida', 32.00, 500.00, 468.00, 'Consumo de producción — OP #12', 1, '2026-06-10 02:06:03', '2026-06-10 02:06:03'),
(9, 8, 'Salida', 8.00, 492.00, 484.00, 'Consumo de producción — OP #13', 1, '2026-06-10 02:06:03', '2026-06-10 02:06:03'),
(10, 2, 'Salida', 32.00, 468.00, 436.00, 'Consumo de producción — OP #13', 1, '2026-06-10 02:06:03', '2026-06-10 02:06:03'),
(21, 2, 'Entrada', 100.00, 436.00, 536.00, 'llenado general', 1, '2026-06-11 03:21:43', '2026-06-11 03:21:43'),
(22, 3, 'Entrada', 100.00, 0.00, 100.00, 'llenado general', 1, '2026-06-11 03:21:43', '2026-06-11 03:21:43'),
(23, 5, 'Entrada', 100.00, 0.00, 100.00, 'llenado general', 1, '2026-06-11 03:21:43', '2026-06-11 03:21:43'),
(24, 7, 'Entrada', 100.00, 0.00, 100.00, 'llenado general', 1, '2026-06-11 03:21:43', '2026-06-11 03:21:43'),
(25, 8, 'Entrada', 100.00, 484.00, 584.00, 'llenado general', 1, '2026-06-11 03:21:43', '2026-06-11 03:21:43'),
(26, 9, 'Entrada', 100.00, 0.00, 100.00, 'llenado general', 1, '2026-06-11 03:21:43', '2026-06-11 03:21:43'),
(27, 10, 'Entrada', 100.00, 0.00, 100.00, 'llenado general', 1, '2026-06-11 03:21:43', '2026-06-11 03:21:43'),
(28, 14, 'Entrada', 100.00, 500.00, 600.00, 'llenado general', 1, '2026-06-11 03:21:43', '2026-06-11 03:21:43'),
(29, 16, 'Entrada', 100.00, 100.00, 200.00, 'llenado general', 1, '2026-06-11 03:21:43', '2026-06-11 03:21:43'),
(30, 17, 'Entrada', 100.00, 50.00, 150.00, 'llenado general', 1, '2026-06-11 03:21:43', '2026-06-11 03:21:43'),
(31, 2, 'Entrada', 10.00, 536.00, 546.00, 'llenado general', 1, '2026-06-11 03:22:26', '2026-06-11 03:22:26'),
(32, 3, 'Entrada', 10.00, 100.00, 110.00, 'llenado general', 1, '2026-06-11 03:22:26', '2026-06-11 03:22:26'),
(33, 5, 'Entrada', 10.00, 100.00, 110.00, 'llenado general', 1, '2026-06-11 03:22:26', '2026-06-11 03:22:26'),
(34, 7, 'Entrada', 10.00, 100.00, 110.00, 'llenado general', 1, '2026-06-11 03:22:26', '2026-06-11 03:22:26'),
(35, 8, 'Entrada', 10.00, 584.00, 594.00, 'llenado general', 1, '2026-06-11 03:22:26', '2026-06-11 03:22:26'),
(36, 9, 'Entrada', 10.00, 100.00, 110.00, 'llenado general', 1, '2026-06-11 03:22:26', '2026-06-11 03:22:26'),
(37, 10, 'Entrada', 10.00, 100.00, 110.00, 'llenado general', 1, '2026-06-11 03:22:26', '2026-06-11 03:22:26'),
(38, 14, 'Entrada', 10.00, 600.00, 610.00, 'llenado general', 1, '2026-06-11 03:22:26', '2026-06-11 03:22:26'),
(39, 16, 'Entrada', 10.00, 200.00, 210.00, 'llenado general', 1, '2026-06-11 03:22:26', '2026-06-11 03:22:26'),
(40, 17, 'Entrada', 10.00, 150.00, 160.00, 'llenado general', 1, '2026-06-11 03:22:26', '2026-06-11 03:22:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_produccion`
--

CREATE TABLE `orden_produccion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pedido_id` bigint(20) UNSIGNED DEFAULT NULL,
  `detalle_pedido_id` bigint(20) UNSIGNED DEFAULT NULL,
  `producto_id` bigint(20) UNSIGNED DEFAULT NULL,
  `empleado_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cantidad_solicitada` int(11) NOT NULL,
  `cantidad_producida` int(11) NOT NULL DEFAULT 0,
  `cantidad_defectuosa` int(11) NOT NULL DEFAULT 0,
  `fecha_inicio` date NOT NULL,
  `fecha_fin_estimada` date NOT NULL,
  `fecha_fin_real` date DEFAULT NULL,
  `estado` enum('Pendiente','En Proceso','Finalizado','Cancelado') NOT NULL DEFAULT 'Pendiente',
  `notas` text DEFAULT NULL,
  `motivo_cancelacion` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `orden_produccion`
--

INSERT INTO `orden_produccion` (`id`, `pedido_id`, `detalle_pedido_id`, `producto_id`, `empleado_id`, `cantidad_solicitada`, `cantidad_producida`, `cantidad_defectuosa`, `fecha_inicio`, `fecha_fin_estimada`, `fecha_fin_real`, `estado`, `notas`, `motivo_cancelacion`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, NULL, 10, 11, 0, 0, '2026-06-08', '2026-06-15', NULL, 'Pendiente', 'notificar a departamente administrativo cuando finalice la produccion de este producto', NULL, 7, '2026-06-07 15:54:52', '2026-06-07 15:54:52', NULL),
(2, 2, 2, NULL, 12, 2, 0, 0, '2026-06-07', '2026-06-20', NULL, 'Pendiente', NULL, NULL, 7, '2026-06-07 17:55:58', '2026-06-07 17:55:58', NULL),
(3, 2, 3, NULL, 11, 2, 0, 0, '2026-06-07', '2026-06-20', NULL, 'Pendiente', NULL, NULL, 7, '2026-06-07 17:55:58', '2026-06-07 17:55:58', NULL),
(4, 3, 5, NULL, 3, 1, 0, 0, '2026-06-07', '2026-06-21', NULL, 'Pendiente', 'notas', NULL, 7, '2026-06-07 17:57:45', '2026-06-07 18:02:53', NULL),
(12, 6, 6, NULL, 10, 4, 2, 0, '2026-06-10', '2026-06-22', NULL, 'En Proceso', NULL, NULL, 1, '2026-06-10 02:06:03', '2026-06-10 02:06:23', NULL),
(13, 6, 7, NULL, 10, 4, 2, 0, '2026-06-10', '2026-06-22', NULL, 'En Proceso', NULL, NULL, 1, '2026-06-10 02:06:03', '2026-06-10 02:06:31', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_produccion_empleado`
--

CREATE TABLE `orden_produccion_empleado` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `orden_produccion_id` bigint(20) UNSIGNED NOT NULL,
  `empleado_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `orden_produccion_empleado`
--

INSERT INTO `orden_produccion_empleado` (`id`, `orden_produccion_id`, `empleado_id`, `created_at`, `updated_at`) VALUES
(8, 12, 10, '2026-06-10 02:06:03', '2026-06-10 02:06:03'),
(9, 13, 10, '2026-06-10 02:06:03', '2026-06-10 02:06:03');

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
(1, 1, 'efectivo', 100.00, NULL, NULL, '2026-06-07 06:02:12', '2026-06-07 06:02:12'),
(2, 2, 'efectivo', 40.00, NULL, NULL, '2026-06-07 17:24:05', '2026-06-07 17:24:05'),
(3, 3, 'efectivo', 30.00, NULL, NULL, '2026-06-07 17:26:16', '2026-06-07 17:26:16'),
(10, 6, 'efectivo', 132.00, NULL, NULL, '2026-06-09 20:55:33', '2026-06-09 20:55:33');

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
('emman6321@gmail.com', '$2y$12$0MvH089R4gFOIbZ/eXd/LuVwsGg5U.Nmyelh7/rsAs6hHaTkJkPn.', '2026-06-06 15:33:07'),
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
  `fecha_formalizacion` date DEFAULT NULL,
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

INSERT INTO `pedido` (`id`, `cotizacion_id`, `cliente_id`, `fecha_pedido`, `fecha_entrega_estimada`, `fecha_formalizacion`, `estado`, `prioridad`, `total`, `abono`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 23, '2026-06-07', '2026-06-22', NULL, 'Pendiente', 'Normal', 352.00, 100.00, 7, '2026-06-07 06:02:12', '2026-06-07 06:02:12', NULL),
(2, 2, 14, '2026-06-07', '2026-06-22', NULL, 'Pendiente', 'Normal', 70.00, 40.00, 7, '2026-06-07 17:24:05', '2026-06-07 17:24:05', NULL),
(3, 3, 42, '2026-06-07', '2026-06-22', NULL, 'Pendiente', 'Normal', 62.00, 30.00, 7, '2026-06-07 17:26:16', '2026-06-07 17:26:16', NULL),
(6, 4, 9, '2026-06-09', '2026-06-24', '2026-06-09', 'Procesando', 'Normal', 264.00, 132.00, 1, '2026-06-09 20:55:33', '2026-06-10 02:06:23', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permiso_rol`
--

CREATE TABLE `permiso_rol` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rol_id` bigint(20) UNSIGNED NOT NULL,
  `permiso` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permiso_rol`
--

INSERT INTO `permiso_rol` (`id`, `rol_id`, `permiso`, `created_at`, `updated_at`) VALUES
(1, 2, 'pedidos.ver', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(2, 2, 'pedidos.pdf', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(3, 2, 'cotizaciones.ver', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(4, 2, 'cotizaciones.pdf', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(5, 2, 'cotizaciones.convertir', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(6, 2, 'proveedores.ver', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(7, 2, 'productos.ver', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(8, 2, 'productos.gestionar', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(9, 2, 'tipo-productos.ver', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(10, 2, 'tipo-productos.gestionar', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(11, 2, 'atributos.ver', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(12, 2, 'atributos.gestionar', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(13, 2, 'colores.ver', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(14, 2, 'colores.gestionar', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(15, 2, 'insumos.ver', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(16, 2, 'insumos.gestionar', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(17, 2, 'tipo-insumos.ver', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(18, 2, 'tipo-insumos.gestionar', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(19, 2, 'logos.ver', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(20, 2, 'tallas.ver', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(21, 2, 'ordenes.ver', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(22, 2, 'ordenes.gestionar', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(23, 2, 'ordenes.avance', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(24, 2, 'ordenes.cancelar', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(25, 2, 'ordenes.pdf', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(26, 2, 'compras.ver', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(27, 2, 'compras.gestionar', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(28, 2, 'compras.procesar', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(29, 2, 'compras.anular', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(30, 2, 'compras.clonar', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(31, 2, 'compras.pdf', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(32, 2, 'movimiento-insumo.ver', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(33, 2, 'movimiento-insumo.gestionar', '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(34, 2, 'reportes.ver', '2026-06-16 00:50:12', '2026-06-16 00:50:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `persona`
--

CREATE TABLE `persona` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `documento_identidad` varchar(50) NOT NULL,
  `tipo_documento` enum('V-','E-','J-','G-') NOT NULL DEFAULT 'V-',
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `persona`
--

INSERT INTO `persona` (`id`, `nombre`, `documento_identidad`, `tipo_documento`, `email`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Administrador Sistema', '00000001', 'V-', 'admin@gmail.com', '2025-12-04 18:58:27', '2025-12-04 18:58:27', NULL),
(2, 'El Supervisor', '00000002', 'V-', NULL, '2025-12-04 18:58:28', '2025-12-08 14:30:58', NULL),
(3, 'James David Vance', '8889292', 'G-', 'jdvance@gmail.com', '2025-12-04 20:19:19', '2025-12-04 20:19:19', NULL),
(4, 'Jose Luis Rodriguez', '4567899', 'V-', 'isavale10@gmail.com', '2025-12-05 20:14:04', '2025-12-05 20:14:04', NULL),
(6, 'Peter Thiel', '8769044', 'E-', 'pltrinvest@gmail.com', '2025-12-08 19:55:18', '2025-12-08 20:23:37', NULL),
(7, 'Larry Ellison', '545683666', 'E-', 'oraclecorporation@gmail.com', '2025-12-08 19:55:18', '2025-12-08 20:23:29', NULL),
(8, 'Leslie Herbert Wexner', '6233455', 'E-', 'vsecret@gmail.com', '2025-12-08 20:04:57', '2025-12-08 20:23:20', NULL),
(9, 'Robert Maxwell', '987489', 'E-', 'maxwellcorporation@gmail.com', '2025-12-08 20:19:32', '2025-12-08 20:22:30', NULL),
(10, 'Jose Juan', '7499586', 'E-', 'nvidiaceo@gmail.com', '2025-12-09 18:54:35', '2025-12-09 18:56:47', NULL),
(11, 'Alexander Caedmon Karp', '89320234', 'E-', 'alexkpr@gmail.com', '2025-12-10 18:09:48', '2025-12-10 18:09:48', NULL),
(12, 'Mark Zuckerberg', '18728555', 'V-', 'facebook@gmail.com', '2025-12-10 20:29:40', '2025-12-10 20:31:20', NULL),
(13, 'Santiago Abad Mendoza', '30822318', 'V-', 'santitron@gmail.com', '2025-12-10 20:57:36', '2026-06-03 04:12:10', NULL),
(14, 'Mark Cuban', '6786543', 'E-', 'markcu@gmail.com', '2025-12-10 21:09:58', '2025-12-10 21:09:58', NULL),
(15, 'Vanessa diaz', '30966655', 'V-', 'vanessalopez090551@gmail.com', '2026-01-17 16:51:52', '2026-01-17 16:51:52', NULL),
(16, 'valeria diaz', '32152373', 'V-', 'valediaz@gmail.com', '2026-01-17 17:11:09', '2026-01-17 17:11:09', NULL),
(17, 'cvane', '30966654', 'V-', NULL, '2026-01-17 22:05:23', '2026-01-17 22:05:23', NULL),
(18, 'alalalallaa kneoucnewuivcw', '30966659', 'V-', 'alalallala@email.com', '2026-01-17 22:31:33', '2026-01-17 22:31:33', NULL),
(19, 'Cleymar Mendoza', '30966271', 'V-', 'cley@gmail.com', '2026-01-18 03:49:00', '2026-01-18 03:49:00', NULL),
(20, 'Cleymar Mendoza', '30966275', 'V-', 'cleymar@gmail.com', '2026-01-18 03:56:57', '2026-01-18 03:56:57', NULL),
(22, 'Victor Mendoza', '12344093', 'V-', 'victorm@gmail.com', '2026-01-18 20:23:50', '2026-01-18 20:23:50', NULL),
(23, 'Luis Alberto Mendoza García', '15789234', 'V-', 'luismendoza@gmail.com', '2026-01-18 20:37:01', '2026-01-18 20:37:01', NULL),
(24, 'Rosa María Hernández López', '18234567', 'V-', 'rosahdez@hotmail.com', '2026-01-18 20:38:40', '2026-01-18 20:38:40', NULL),
(25, 'Carlos Eduardo Silva Martínez', '84567890', 'E-', 'carlossilva@gmail.com', '2026-01-18 20:41:05', '2026-01-18 20:41:05', NULL),
(26, 'Angela Patricia Vargas Rojas', '12456789', 'V-', 'angelavargas@gmail.com', '2026-01-18 20:43:53', '2026-01-18 20:43:53', NULL),
(27, 'María Fernanda Gutiérrez Méndez', '16823456', 'V-', 'mariagutierrez@gmail.com', '2026-01-18 20:57:49', '2026-01-18 20:57:49', NULL),
(28, 'Pedro Antonio Briceño Rivas', '19876543', 'V-', 'pedrobriceno@gmail.com', '2026-01-18 21:52:28', '2026-01-18 21:52:28', NULL),
(29, 'Angely Canelon', '37782737', 'V-', 'loca123@gmail.com', '2026-01-19 00:25:34', '2026-01-19 00:25:34', NULL),
(30, 'Alejandro Abreu', '31558506', 'V-', 'ale@gmail.com', '2026-01-19 03:56:04', '2026-01-19 03:56:04', NULL),
(31, 'alejandro abreu', '31558507', 'V-', 'ale2@gmail.com', '2026-01-19 04:01:50', '2026-01-19 04:01:50', NULL),
(32, 'alejandro diaz', '31558508', 'V-', 'vd6955291@gmail.com', '2026-01-19 04:05:33', '2026-01-19 04:05:33', NULL),
(33, 'josefina lopez', '31558509', 'V-', 'vd695529221@gmail.com', '2026-01-19 04:17:44', '2026-01-19 04:17:44', NULL),
(34, 'angel Canelon', '37782735', 'V-', 'loca1233@gmail.com', '2026-01-19 04:26:34', '2026-01-19 04:26:34', NULL),
(35, 'abby chuela', '31558599', 'V-', 'abbychuela@gmail.com', '2026-01-19 16:49:16', '2026-01-19 16:49:16', NULL),
(36, 'Yohan Mendoza', '15692128', 'V-', 'yohansito@gmail.com', '2026-01-20 01:29:08', '2026-01-25 03:45:08', NULL),
(37, 'Emmanuel Arroyo', '30922671', 'V-', 'emman6321@gmail.com', '2026-01-20 21:23:34', '2026-01-20 21:23:34', NULL),
(38, 'Inversiones Full Color CA', '30666777', 'J-', 'fullcolor10@gmail.com', '2026-02-22 17:56:13', '2026-02-22 17:56:13', NULL),
(39, 'Carlos Ramírez', '18456321', 'V-', 'carlos.ramirez@gmail.com', '2026-02-26 19:10:25', '2026-02-26 19:10:25', NULL),
(40, 'María González', '20134567', 'V-', 'maria.gonzalez@hotmail.com', '2026-02-26 19:10:25', '2026-02-26 19:10:25', NULL),
(42, 'Inversiones Textilera del Centro', '41234567', 'J-', 'admintextileria@gmail.com', '2026-02-26 19:11:43', '2026-02-26 20:22:20', NULL),
(43, 'Luis Hernández', '15678234', 'V-', 'luis.hernandez@outlook.com', '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(44, 'Confecciones El Llano CA', '40987654', 'J-', 'ventas@gmail.com', '2026-02-26 19:11:43', '2026-02-26 20:21:46', NULL),
(45, 'Ana Martínez', '22345678', 'V-', 'ana.martinez@gmail.com', '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(46, 'José Pérez', '17890456', 'V-', 'jose.perez@yahoo.com', '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(47, 'Uniformes Profesionales VE CA', '42567890', 'J-', 'contactounipro@gmail.com', '2026-02-26 19:11:43', '2026-02-26 20:23:03', NULL),
(48, 'Rosa Castillo', '19567890', 'V-', 'rosa.castillo@gmail.com', '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(49, 'Pedro Morales', '16789012', 'V-', 'pedro.morales@gmail.com', '2026-02-26 19:11:43', '2026-02-26 19:11:43', NULL),
(50, 'Gregorio Rodriguez', '10729713', 'V-', 'gregorio10@gmail.com', '2026-02-26 19:46:04', '2026-02-26 19:46:04', NULL),
(51, 'Asoproductos de Portuguesa CA', '13232455', 'J-', 'asoproductos@gmail.com', '2026-03-05 17:17:09', '2026-03-05 17:17:09', NULL),
(53, 'Textiles Caracas Vzla', '1231321', 'J-', 'ventas@textilesvenezuela.com', '2026-03-19 18:21:05', '2026-03-19 18:21:05', NULL),
(54, 'Insumos Textiles C.C.S', '11112222', 'J-', 'ventas@insumostextiles.com', '2026-03-19 18:21:05', '2026-03-19 18:21:05', NULL),
(55, 'Telas y Bordados del Centro CA', '401234567', 'J-', 'ventas@telasbordados.com', '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(56, 'Hilos Industriales Portuguesa SA', '312456789', 'J-', 'info@hilosindustriales.com', '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(57, 'Insumos Textiles Venezuela CA', '201987654', 'G-', 'compras@insumostextiles.com.ve', '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(58, 'Botones y Accesorios Lara CA', '502345671', 'J-', 'ventas@botonesaccesorios.com', '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(59, 'Distribuidora de Telas Los Andes', '415678903', 'J-', 'contacto@telaslosandes.com', '2026-03-19 18:21:06', '2026-03-19 18:21:06', NULL),
(60, 'Sofia Mendoza', '29347954', 'V-', 'sofi@gmail.com', '2026-04-14 21:26:32', '2026-04-14 21:26:32', NULL),
(61, 'Maria Mendoza', '12345678', 'V-', 'mari10@gmail.com', '2026-04-29 00:22:53', '2026-04-29 00:22:53', NULL),
(62, 'Jhoanir Torres', '11111111', 'V-', 'jhoanir10@gmail.com', '2026-04-29 00:25:48', '2026-04-29 00:25:48', NULL),
(65, 'Julian Ramirez', '5233421', 'V-', 'Julian19@gmail.com', '2026-04-30 20:25:42', '2026-04-30 20:25:42', NULL),
(66, 'Kleiver Alexander Colmenarez', '14879947', 'V-', 'kleiverc@gmail.com', '2026-05-30 19:47:16', '2026-05-30 19:47:16', NULL),
(67, 'Yaneth Coromoto Dsantis Salcedo', '11544468', 'V-', 'Yanethds@gmail.com', '2026-05-30 19:50:52', '2026-05-30 19:50:52', NULL),
(68, 'Yanet Yubisai Mendoza', '14346453', 'V-', 'Yanethyubisai@gmail.com', '2026-05-30 19:55:30', '2026-05-30 19:55:30', NULL),
(69, 'Carmen Sofia Rodriguez Perez', '10644939', 'V-', 'Sofiar@gmail.com', '2026-05-30 20:00:52', '2026-05-30 20:00:52', NULL),
(70, 'johiner orellana', '31492161', 'V-', 'johiner@gmail.com', '2026-06-06 14:22:07', '2026-06-06 14:22:07', NULL),
(71, 'Alejandro Adam', '31056872', 'V-', 'jhonalejandroadam@gmail.com', '2026-06-06 15:47:42', '2026-06-06 15:47:42', NULL);

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
-- Estructura de tabla para la tabla `produccion_diaria`
--

CREATE TABLE `produccion_diaria` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `orden_id` bigint(20) UNSIGNED NOT NULL,
  `fecha_produccion` date DEFAULT NULL,
  `empleado_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad_producida` int(11) NOT NULL,
  `cantidad_defectuosa` int(11) NOT NULL DEFAULT 0,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
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
  `atributos_snapshot` longtext DEFAULT NULL,
  `imagen` varchar(191) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 'juridico', 53, 'Juan Pérez', '0412-5231234', 1, '2025-12-04 18:58:28', '2026-05-29 21:00:11', NULL),
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
(13, 'natural', 27, NULL, NULL, 1, '2026-01-18 20:57:49', '2026-05-29 20:13:27', '2026-05-29 20:13:27'),
(14, 'natural', 28, NULL, NULL, 1, '2026-01-18 21:52:28', '2026-05-29 20:10:20', '2026-05-29 20:10:20'),
(15, 'natural', 62, NULL, NULL, 1, '2026-04-29 00:25:48', '2026-05-29 20:10:07', '2026-05-29 20:10:07');

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
(1, NULL, 'admin@gmail.com', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'preguntas', 'fallo', '2026-04-26 21:37:32'),
(2, 1, 'admin@gmail.com', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'preguntas', 'fallo', '2026-04-27 02:14:16'),
(3, 1, 'admin@gmail.com', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'preguntas', 'exito', '2026-04-27 02:14:32'),
(4, NULL, 'emman6321@gmail.com', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'preguntas', 'fallo', '2026-04-28 13:17:40'),
(5, NULL, 'emman6321@gmail.com', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'preguntas', 'fallo', '2026-04-28 13:19:35'),
(6, NULL, 'emman6321@gmail.com', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'preguntas', 'fallo', '2026-04-28 13:19:42'),
(7, NULL, 'emman6321@gmail.com', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'preguntas', 'fallo', '2026-04-28 13:20:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(191) NOT NULL,
  `descripcion` varchar(191) DEFAULT NULL,
  `es_sistema` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id`, `nombre`, `descripcion`, `es_sistema`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', NULL, 1, NULL, '2026-06-16 00:50:12', '2026-06-16 00:50:12'),
(2, 'Supervisor', NULL, 1, NULL, '2026-06-16 00:50:12', '2026-06-16 00:50:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sub_orden_empleado`
--

CREATE TABLE `sub_orden_empleado` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sub_orden_produccion_id` bigint(20) UNSIGNED NOT NULL,
  `empleado_id` bigint(20) UNSIGNED NOT NULL,
  `rol` varchar(80) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sub_orden_produccion`
--

CREATE TABLE `sub_orden_produccion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `orden_produccion_id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `cantidad_asignada` int(10) UNSIGNED DEFAULT NULL,
  `estado` enum('Pendiente','En Proceso','Finalizado','Cancelado') NOT NULL DEFAULT 'Pendiente',
  `notas` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(42, 'USD', 485.2251, '2026-04-28', 'BCV (DolarAPI)', '2026-04-28 12:59:20', '2026-04-28 12:59:20'),
(43, 'USD', 487.1192, '2026-04-30', 'BCV (DolarAPI)', '2026-04-30 14:19:08', '2026-04-30 14:19:08'),
(44, 'USD', 490.0442, '2026-05-05', 'BCV (DolarAPI)', '2026-05-05 15:09:27', '2026-05-05 15:09:27'),
(45, 'USD', 496.8301, '2026-05-07', 'BCV (DolarAPI)', '2026-05-07 14:18:30', '2026-05-07 14:18:30'),
(46, 'USD', 504.9146, '2026-05-12', 'BCV (DolarAPI)', '2026-05-12 23:34:48', '2026-05-12 23:34:48'),
(47, 'USD', 510.7873, '2026-05-14', 'BCV (DolarAPI)', '2026-05-14 14:27:58', '2026-05-14 14:27:58'),
(48, 'USD', 520.9142, '2026-05-20', 'BCV (DolarAPI)', '2026-05-21 01:20:31', '2026-05-21 01:20:31'),
(49, 'USD', 535.3853, '2026-05-26', 'BCV (DolarAPI)', '2026-05-26 23:18:33', '2026-05-26 23:18:33'),
(50, 'USD', 540.0431, '2026-05-27', 'BCV (DolarAPI)', '2026-05-27 05:01:16', '2026-05-27 05:01:16'),
(51, 'USD', 544.5794, '2026-05-28', 'BCV (DolarAPI)', '2026-05-28 04:13:27', '2026-05-28 04:13:27'),
(52, 'USD', 549.3716, '2026-05-29', 'BCV (DolarAPI)', '2026-05-29 14:28:13', '2026-05-29 14:28:13'),
(53, 'USD', 554.4258, '2026-06-01', 'BCV (DolarAPI)', '2026-06-01 17:11:23', '2026-06-01 17:11:23'),
(54, 'USD', 557.9741, '2026-06-02', 'BCV (DolarAPI)', '2026-06-02 15:13:27', '2026-06-02 15:13:27'),
(55, 'USD', 558.6436, '2026-06-03', 'BCV (DolarAPI)', '2026-06-03 05:20:10', '2026-06-03 05:20:10'),
(56, 'USD', 560.3753, '2026-06-04', 'BCV (DolarAPI)', '2026-06-04 04:09:40', '2026-06-04 04:09:40'),
(57, 'USD', 563.2892, '2026-06-05', 'BCV (DolarAPI)', '2026-06-05 17:50:40', '2026-06-05 17:50:40'),
(58, 'USD', 567.6828, '2026-06-09', 'BCV (DolarAPI)', '2026-06-09 14:34:17', '2026-06-09 14:34:17'),
(59, 'USD', 572.6784, '2026-06-10', 'BCV (DolarAPI)', '2026-06-11 02:17:24', '2026-06-11 02:17:24'),
(60, 'USD', 577.5461, '2026-06-11', 'BCV (DolarAPI)', '2026-06-11 04:48:02', '2026-06-11 04:48:02'),
(61, 'USD', 587.4059, '2026-06-15', 'BCV (DolarAPI)', '2026-06-16 02:23:16', '2026-06-16 02:23:16'),
(62, 'USD', 596.7824, '2026-06-17', 'BCV (DolarAPI)', '2026-06-18 01:25:03', '2026-06-18 01:25:03');

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
(56, 60, '0422-7234564', 'movil', 1, '2026-04-14 21:26:32', '2026-04-14 21:26:32', NULL),
(57, 61, '0424-3452345', 'movil', 1, '2026-04-29 00:22:53', '2026-04-29 00:22:53', NULL),
(58, 62, '0424-3456272', 'movil', 1, '2026-04-29 00:25:48', '2026-04-29 00:25:48', NULL),
(61, 65, '0424-3456865', 'movil', 1, '2026-04-30 20:25:42', '2026-04-30 20:25:42', NULL),
(62, 66, '0412-0567198', 'movil', 1, '2026-05-30 19:47:16', '2026-05-30 19:47:16', NULL),
(63, 67, '0424-5396144', 'movil', 1, '2026-05-30 19:50:52', '2026-05-30 19:50:52', NULL),
(64, 68, '0422-6464295', 'movil', 1, '2026-05-30 19:55:30', '2026-05-30 19:55:30', NULL),
(65, 69, '0424-5348922', 'movil', 1, '2026-05-30 20:00:52', '2026-05-30 20:00:52', NULL),
(66, 70, '0424-5909133', 'movil', 1, '2026-06-06 14:22:07', '2026-06-06 14:22:07', NULL),
(67, 71, '0412-2923040', 'movil', 1, '2026-06-06 15:47:42', '2026-06-06 15:47:42', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_insumo`
--

CREATE TABLE `tipo_insumo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipo_insumo`
--

INSERT INTO `tipo_insumo` (`id`, `nombre`, `activo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Tela', 1, '2026-06-07 01:27:06', '2026-06-07 01:27:06', NULL),
(2, 'Hilo', 1, '2026-06-07 01:27:06', '2026-06-07 01:27:06', NULL),
(3, 'Boton', 1, '2026-06-07 01:27:06', '2026-06-07 01:27:06', NULL),
(4, 'Cierre', 1, '2026-06-07 01:27:06', '2026-06-07 01:27:06', NULL),
(5, 'Etiqueta', 1, '2026-06-07 01:27:06', '2026-06-07 01:27:06', NULL),
(8, 'Cinta', 1, '2026-06-07 01:48:20', '2026-06-07 01:48:20', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_producto`
--

CREATE TABLE `tipo_producto` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `prefijo` varchar(5) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(191) DEFAULT NULL,
  `precio_confeccion` decimal(10,2) NOT NULL DEFAULT 0.00,
  `requiere_tela` tinyint(1) NOT NULL DEFAULT 1,
  `requiere_produccion` tinyint(1) NOT NULL DEFAULT 1,
  `consumo_tela_por_unidad` decimal(8,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipo_producto`
--

INSERT INTO `tipo_producto` (`id`, `nombre`, `prefijo`, `descripcion`, `imagen`, `precio_confeccion`, `requiere_tela`, `requiere_produccion`, `consumo_tela_por_unidad`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Chemise', 'CHM', 'Camisas tipo polo con cuello', 'productoimg/tipos/6a259a4c388c5.jpg', 12.00, 1, 1, 0.00, '2025-12-16 17:48:48', '2026-06-07 16:20:28', NULL),
(2, 'Franela', 'FRN', 'Franelas cuello redondo o V', 'productoimg/tipos/6a259afddafc4.jpg', 8.00, 1, 1, 0.00, '2025-12-16 17:48:48', '2026-06-07 16:23:25', NULL),
(3, 'Camisa', 'CAM', 'Camisas formales', 'productoimg/tipos/6a2596791397d.jpg', 15.00, 1, 1, 2.00, '2025-12-16 17:48:48', '2026-06-07 16:04:09', NULL),
(4, 'Pantalón', 'PNT', 'Pantalones de trabajo o formales', 'productoimg/tipos/6a259be89184c.jpg', 18.00, 0, 1, 0.00, '2025-12-16 17:48:48', '2026-06-07 16:27:20', NULL),
(5, 'Chaqueta', 'CHQ', 'Chaquetas industriales o formales', 'productoimg/tipos/6a2599d8769af.jpg', 25.00, 1, 1, 0.00, '2025-12-16 17:48:48', '2026-06-07 16:18:32', NULL),
(6, 'Overol', 'OVR', 'Overoles y monos de trabajo', 'productoimg/tipos/6a259b83d8470.jpg', 0.00, 1, 1, 0.00, '2025-12-16 17:48:48', '2026-06-07 16:25:39', NULL),
(7, 'Chemise Escolar', 'ESC', 'Prendas para uniformes escolares', 'productoimg/tipos/6a259a90459fe.jpg', 0.00, 1, 1, 0.00, '2025-12-16 17:48:48', '2026-06-07 16:21:36', NULL),
(8, 'Accesorio', 'ACC', 'delantales, chalecos, etc.', 'productoimg/tipos/6a2598f47744f.jpg', 0.00, 0, 1, 0.00, '2025-12-16 17:48:48', '2026-06-07 16:29:29', '2026-06-07 16:29:29'),
(9, 'Gorra', 'GO', 'Clasica', 'productoimg/tipos/6a259869d6d86.jpg', 10.00, 0, 0, 0.00, '2026-05-03 21:27:03', '2026-06-07 17:09:23', NULL),
(10, 'Delantal', 'DLNT', 'Para carniceros, caleteros, chefs, entre otros', 'productoimg/tipos/6a2596ebb12cf.jpg', 7.00, 1, 1, 1.50, '2026-06-07 02:02:23', '2026-06-07 16:06:03', NULL);

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

--
-- Volcado de datos para la tabla `tipo_producto_atributo`
--

INSERT INTO `tipo_producto_atributo` (`id`, `tipo_producto_id`, `atributo_id`, `es_obligatorio`, `orden`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 1, 1, '2026-05-07 17:15:29', '2026-06-10 04:08:04'),
(3, 2, 1, 1, 1, '2026-05-07 17:15:29', '2026-06-07 16:23:25'),
(5, 1, 1, 1, 1, '2026-05-07 17:15:29', '2026-06-07 16:20:28'),
(8, 5, 4, 1, 1, '2026-05-07 17:15:29', '2026-06-07 16:18:32'),
(9, 5, 5, 1, 2, '2026-05-07 17:15:29', '2026-06-07 16:18:32'),
(12, 10, 6, 1, 1, '2026-06-07 02:02:23', '2026-06-07 16:06:03'),
(13, 9, 7, 1, 1, '2026-06-07 16:46:24', '2026-06-07 17:09:23'),
(14, 2, 8, 1, 0, '2026-06-10 04:01:17', '2026-06-10 04:01:17'),
(15, 4, 9, 1, 0, '2026-06-10 04:03:28', '2026-06-10 04:03:28'),
(16, 3, 10, 1, 1, '2026-06-10 04:04:53', '2026-06-10 04:08:04'),
(17, 1, 11, 1, 0, '2026-06-10 04:09:09', '2026-06-10 04:09:09'),
(18, 3, 12, 1, 0, '2026-06-10 04:13:11', '2026-06-10 04:13:11'),
(19, 1, 13, 1, 0, '2026-06-10 04:16:31', '2026-06-10 04:16:31');

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

--
-- Volcado de datos para la tabla `tipo_producto_insumo`
--

INSERT INTO `tipo_producto_insumo` (`id`, `tipo_producto_id`, `insumo_id`, `cantidad_estimada`, `created_at`, `updated_at`) VALUES
(7, 3, 2, 8.00, '2026-05-28 23:43:33', '2026-06-10 04:08:04'),
(9, 10, 16, 1.00, '2026-06-07 02:02:23', '2026-06-07 16:06:03'),
(10, 10, 14, 1.00, '2026-06-07 02:02:23', '2026-06-07 16:06:03'),
(11, 10, 17, 100.00, '2026-06-07 02:06:03', '2026-06-07 16:06:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_producto_tela`
--

CREATE TABLE `tipo_producto_tela` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tipo_producto_id` bigint(20) UNSIGNED NOT NULL,
  `insumo_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipo_producto_tela`
--

INSERT INTO `tipo_producto_tela` (`id`, `tipo_producto_id`, `insumo_id`, `created_at`, `updated_at`) VALUES
(1, 2, 5, '2026-06-05 01:15:25', '2026-06-05 01:15:25'),
(2, 3, 8, '2026-06-06 21:43:25', '2026-06-06 21:43:25'),
(3, 5, 10, '2026-06-06 22:00:52', '2026-06-06 22:00:52'),
(4, 1, 3, '2026-06-06 22:01:10', '2026-06-06 22:01:10'),
(5, 6, 10, '2026-06-06 22:01:52', '2026-06-06 22:01:52'),
(6, 7, 3, '2026-06-06 22:03:08', '2026-06-06 22:03:08'),
(7, 3, 7, '2026-06-06 22:03:24', '2026-06-06 22:03:24'),
(8, 10, 10, '2026-06-07 02:02:23', '2026-06-07 02:02:23'),
(9, 5, 5, '2026-06-07 16:15:34', '2026-06-07 16:15:34'),
(10, 1, 5, '2026-06-07 16:19:38', '2026-06-07 16:19:38'),
(11, 7, 5, '2026-06-07 16:21:36', '2026-06-07 16:21:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

CREATE TABLE `user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `persona_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
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

INSERT INTO `user` (`id`, `role_id`, `persona_id`, `name`, `email`, `email_verified_at`, `password`, `avatar`, `estado`, `recovery_locked_until`, `recovery_failed_attempts`, `recovery_must_reset_questions`, `password_reset_by_admin`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Emmanuel Jesus', 'admin@gmail.com', NULL, '$2y$12$wtIIuUZn/BiKnjOswxoVheWAG.p4yg.RLfxdwcmNkVQS4Vytj6r5u', 'avatars/69a05e0f7203f.png', 1, NULL, 0, 0, 0, 'D5fcihSlvsFxWuYw8npIOFbblfkM3fF5zHAWedFcp5oabVqrHb6L5sYYhMcE', '2025-12-04 18:58:27', '2026-06-03 23:10:29', NULL),
(2, 2, 2, 'Supervisor', 'supervisor@gmail.com', NULL, '$2y$12$WZ9jnte4F/DkVPbh64iBKOt91FLUDEDRzmYtJYvc6.iwwLhn3wef6', NULL, 1, NULL, 0, 0, 0, NULL, '2025-12-04 18:58:28', '2026-05-29 21:46:51', NULL),
(5, 1, NULL, 'Vanessa Diaz', 'vanessalopez090551@gmail.com', NULL, '$2y$12$d0G/88tSU7qJKgmtlYP.ZO95ss9hgFYK8lZF6N9tsRQAsmlansFHC', 'avatars/69a060ce9e755.png', 1, NULL, 0, 0, 0, 'Egbv2pBcVPvjVa8jdqGym2kvHdikOyAWaF03sHTyIAY1ZE28cT8ARZkfl6GR', '2026-01-15 00:05:33', '2026-05-29 21:49:26', NULL),
(7, 1, NULL, 'Jesus Rodriguez', 'emman6321@gmail.com', NULL, '$2y$12$b9CFjlKZfvn41Ap747aBLOrvO7JJncegQIKYLyfrS8jTeguVdvdhG', 'avatars/69a8a14e140ef.jpg', 1, NULL, 0, 0, 0, 'Bubfbn9FUx1e1mV3c7A7SvlQA22Zu9nKaYYO9TopvfzSFu0hAM4pIQmhWcAr', '2026-03-04 21:17:02', '2026-05-29 21:50:19', NULL),
(8, 1, NULL, 'Francis', 'francis@gmail.com', NULL, '$2y$12$iBPxm0QiVidpnp4gjx1vgeyZ3gEFE0hP8AUbvMt60gN69/CwrQ/ze', NULL, 1, NULL, 0, 0, 0, NULL, '2026-03-19 15:17:38', '2026-03-19 15:17:38', NULL);

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
(3, 1, 8, '$2y$12$wOWMCJxPaUsgkGiWnC4fLuPhn89hEbfmuni7VYUWxpMTG9PDPrZVm', 3, '2026-04-27 00:01:15', '2026-04-27 00:01:15'),
(4, 7, 1, '$2y$12$5rct3LK.u.IjLHDOUZmY9.oI8uwSBDHdZ3GxwNbcUTpobloaffbom', 1, '2026-05-30 20:21:58', '2026-06-11 13:42:35'),
(5, 7, 10, '$2y$12$O3bJAo/PgG.i1C2fQSzvsOUu09b.IETROEMkCYk6gGcy97Dto9pIW', 2, '2026-05-30 20:21:58', '2026-06-11 13:42:35'),
(6, 7, 6, '$2y$12$IpNClXJizVDqTmAA/v8eyOtX7/spQLPZip6amH2adzpzMfMRhBWoS', 3, '2026-05-30 20:21:58', '2026-06-11 13:42:35');

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
-- Indices de la tabla `compra`
--
ALTER TABLE `compra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compra_proveedor_id_foreign` (`proveedor_id`),
  ADD KEY `compra_user_id_foreign` (`user_id`),
  ADD KEY `compra_anulado_por_id_foreign` (`anulado_por_id`);

--
-- Indices de la tabla `compra_detalle`
--
ALTER TABLE `compra_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compra_detalle_compra_id_foreign` (`compra_id`),
  ADD KEY `compra_detalle_insumo_id_foreign` (`insumo_id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `configuracion_clave_unique` (`clave`),
  ADD KEY `configuracion_updated_by_id_foreign` (`updated_by_id`);

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
  ADD KEY `detalle_cotizacion_talla_id_foreign` (`talla_id`),
  ADD KEY `detalle_cotizacion_tipo_producto_id_foreign` (`tipo_producto_id`);

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
  ADD KEY `detalle_pedido_talla_id_foreign` (`talla_id`),
  ADD KEY `detalle_pedido_tipo_producto_id_foreign` (`tipo_producto_id`);

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
-- Indices de la tabla `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `estado_nombre_unique` (`nombre`);

--
-- Indices de la tabla `municipio`
--
ALTER TABLE `municipio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `municipio_estado_id_nombre_unique` (`estado_id`,`nombre`);

--
-- Indices de la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `direccion_persona_id_index` (`persona_id`),
  ADD KEY `direccion_estado_id_foreign` (`estado_id`),
  ADD KEY `direccion_municipio_id_foreign` (`municipio_id`);

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
-- Indices de la tabla `orden_produccion_empleado`
--
ALTER TABLE `orden_produccion_empleado`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `op_emp_unique` (`orden_produccion_id`,`empleado_id`),
  ADD KEY `orden_produccion_empleado_empleado_id_foreign` (`empleado_id`);

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
-- Indices de la tabla `permiso_rol`
--
ALTER TABLE `permiso_rol`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permiso_rol_rol_id_permiso_unique` (`rol_id`,`permiso`);

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
-- Indices de la tabla `produccion_diaria`
--
ALTER TABLE `produccion_diaria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produccion_diaria_orden_id_foreign` (`orden_id`),
  ADD KEY `produccion_diaria_empleado_id_index` (`empleado_id`),
  ADD KEY `idx_prod_diaria_orden_fecha` (`orden_id`,`fecha_produccion`);

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
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rol_nombre_unique` (`nombre`);

--
-- Indices de la tabla `sub_orden_empleado`
--
ALTER TABLE `sub_orden_empleado`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sub_orden_empleado_unico` (`sub_orden_produccion_id`,`empleado_id`),
  ADD KEY `sub_orden_empleado_empleado_id_foreign` (`empleado_id`);

--
-- Indices de la tabla `sub_orden_produccion`
--
ALTER TABLE `sub_orden_produccion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sub_orden_produccion_orden_produccion_id_foreign` (`orden_produccion_id`);

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
-- Indices de la tabla `tipo_insumo`
--
ALTER TABLE `tipo_insumo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tipo_insumo_nombre_unique` (`nombre`);

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
-- Indices de la tabla `tipo_producto_tela`
--
ALTER TABLE `tipo_producto_tela`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tipo_producto_tela_unique` (`tipo_producto_id`,`insumo_id`),
  ADD KEY `tipo_producto_tela_insumo_id_foreign` (`insumo_id`);

--
-- Indices de la tabla `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `user_persona_id_unique` (`persona_id`),
  ADD KEY `user_role_id_foreign` (`role_id`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `atributo_valor`
--
ALTER TABLE `atributo_valor`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `color`
--
ALTER TABLE `color`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `compra`
--
ALTER TABLE `compra`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `compra_detalle`
--
ALTER TABLE `compra_detalle`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cotizacion`
--
ALTER TABLE `cotizacion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `departamento`
--
ALTER TABLE `departamento`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `detalle_cotizacion_bordado`
--
ALTER TABLE `detalle_cotizacion_bordado`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detalle_orden_insumo`
--
ALTER TABLE `detalle_orden_insumo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido_bordado`
--
ALTER TABLE `detalle_pedido_bordado`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido_insumo`
--
ALTER TABLE `detalle_pedido_insumo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estado`
--
ALTER TABLE `estado`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `municipio`
--
ALTER TABLE `municipio`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=338;

--
-- AUTO_INCREMENT de la tabla `direccion`
--
ALTER TABLE `direccion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT de la tabla `empleado`
--
ALTER TABLE `empleado`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `insumo`
--
ALTER TABLE `insumo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `logo`
--
ALTER TABLE `logo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT de la tabla `movimiento_insumo`
--
ALTER TABLE `movimiento_insumo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `orden_produccion`
--
ALTER TABLE `orden_produccion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `orden_produccion_empleado`
--
ALTER TABLE `orden_produccion_empleado`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `pago_pedido`
--
ALTER TABLE `pago_pedido`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `permiso_rol`
--
ALTER TABLE `permiso_rol`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `persona`
--
ALTER TABLE `persona`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `produccion_diaria`
--
ALTER TABLE `produccion_diaria`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `producto_atributo_valor`
--
ALTER TABLE `producto_atributo_valor`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `recovery_attempt`
--
ALTER TABLE `recovery_attempt`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `sub_orden_empleado`
--
ALTER TABLE `sub_orden_empleado`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `sub_orden_produccion`
--
ALTER TABLE `sub_orden_produccion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `talla`
--
ALTER TABLE `talla`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `tasa_cambio`
--
ALTER TABLE `tasa_cambio`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de la tabla `telefono`
--
ALTER TABLE `telefono`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT de la tabla `tipo_insumo`
--
ALTER TABLE `tipo_insumo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `tipo_producto`
--
ALTER TABLE `tipo_producto`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `tipo_producto_atributo`
--
ALTER TABLE `tipo_producto_atributo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `tipo_producto_insumo`
--
ALTER TABLE `tipo_producto_insumo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `tipo_producto_tela`
--
ALTER TABLE `tipo_producto_tela`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `user`
--
ALTER TABLE `user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `user_recovery_question`
--
ALTER TABLE `user_recovery_question`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
-- Filtros para la tabla `compra`
--
ALTER TABLE `compra`
  ADD CONSTRAINT `compra_anulado_por_id_foreign` FOREIGN KEY (`anulado_por_id`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `compra_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedor` (`id`),
  ADD CONSTRAINT `compra_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Filtros para la tabla `compra_detalle`
--
ALTER TABLE `compra_detalle`
  ADD CONSTRAINT `compra_detalle_compra_id_foreign` FOREIGN KEY (`compra_id`) REFERENCES `compra` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `compra_detalle_insumo_id_foreign` FOREIGN KEY (`insumo_id`) REFERENCES `insumo` (`id`);

--
-- Filtros para la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD CONSTRAINT `configuracion_updated_by_id_foreign` FOREIGN KEY (`updated_by_id`) REFERENCES `user` (`id`);

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
  ADD CONSTRAINT `detalle_cotizacion_tipo_producto_id_foreign` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipo_producto` (`id`) ON DELETE SET NULL,
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
  ADD CONSTRAINT `detalle_pedido_tipo_producto_id_foreign` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipo_producto` (`id`) ON DELETE SET NULL,
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
-- Filtros para la tabla `municipio`
--
ALTER TABLE `municipio`
  ADD CONSTRAINT `municipio_estado_id_foreign` FOREIGN KEY (`estado_id`) REFERENCES `estado` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD CONSTRAINT `direccion_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `direccion_estado_id_foreign` FOREIGN KEY (`estado_id`) REFERENCES `estado` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `direccion_municipio_id_foreign` FOREIGN KEY (`municipio_id`) REFERENCES `municipio` (`id`) ON DELETE SET NULL;

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
-- Filtros para la tabla `orden_produccion_empleado`
--
ALTER TABLE `orden_produccion_empleado`
  ADD CONSTRAINT `orden_produccion_empleado_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleado` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orden_produccion_empleado_orden_produccion_id_foreign` FOREIGN KEY (`orden_produccion_id`) REFERENCES `orden_produccion` (`id`) ON DELETE CASCADE;

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
-- Filtros para la tabla `permiso_rol`
--
ALTER TABLE `permiso_rol`
  ADD CONSTRAINT `permiso_rol_rol_id_foreign` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id`);

--
-- Filtros para la tabla `produccion_diaria`
--
ALTER TABLE `produccion_diaria`
  ADD CONSTRAINT `produccion_diaria_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleado` (`id`),
  ADD CONSTRAINT `produccion_diaria_orden_id_foreign` FOREIGN KEY (`orden_id`) REFERENCES `orden_produccion` (`id`) ON DELETE CASCADE;

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
-- Filtros para la tabla `sub_orden_empleado`
--
ALTER TABLE `sub_orden_empleado`
  ADD CONSTRAINT `sub_orden_empleado_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleado` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sub_orden_empleado_sub_orden_produccion_id_foreign` FOREIGN KEY (`sub_orden_produccion_id`) REFERENCES `sub_orden_produccion` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sub_orden_produccion`
--
ALTER TABLE `sub_orden_produccion`
  ADD CONSTRAINT `sub_orden_produccion_orden_produccion_id_foreign` FOREIGN KEY (`orden_produccion_id`) REFERENCES `orden_produccion` (`id`) ON DELETE CASCADE;

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
-- Filtros para la tabla `tipo_producto_tela`
--
ALTER TABLE `tipo_producto_tela`
  ADD CONSTRAINT `tipo_producto_tela_insumo_id_foreign` FOREIGN KEY (`insumo_id`) REFERENCES `insumo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tipo_producto_tela_tipo_producto_id_foreign` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipo_producto` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`),
  ADD CONSTRAINT `user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `rol` (`id`);

--
-- Filtros para la tabla `user_recovery_question`
--
ALTER TABLE `user_recovery_question`
  ADD CONSTRAINT `user_recovery_question_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
