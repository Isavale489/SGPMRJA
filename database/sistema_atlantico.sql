-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: sistema_atlantico5
-- ------------------------------------------------------
-- Server version	8.0.46-0ubuntu0.24.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `atributo`
--

DROP TABLE IF EXISTS `atributo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atributo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `atributo_nombre_unique` (`nombre`),
  UNIQUE KEY `atributo_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atributo`
--

/*!40000 ALTER TABLE `atributo` DISABLE KEYS */;
INSERT INTO `atributo` VALUES (1,'Manga','MNG',NULL,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(2,'Cuello','CLL',NULL,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(3,'Corte','CRT',NULL,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(4,'Cierre','CRR',NULL,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(5,'Capucha','CPC',NULL,'2026-05-07 17:15:29','2026-05-07 17:15:29');
/*!40000 ALTER TABLE `atributo` ENABLE KEYS */;

--
-- Table structure for table `atributo_valor`
--

DROP TABLE IF EXISTS `atributo_valor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atributo_valor` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `atributo_id` bigint unsigned NOT NULL,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `atributo_valor_atributo_id_codigo_unique` (`atributo_id`,`codigo`),
  UNIQUE KEY `atributo_valor_atributo_id_nombre_unique` (`atributo_id`,`nombre`),
  CONSTRAINT `atributo_valor_atributo_id_foreign` FOREIGN KEY (`atributo_id`) REFERENCES `atributo` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atributo_valor`
--

/*!40000 ALTER TABLE `atributo_valor` DISABLE KEYS */;
INSERT INTO `atributo_valor` VALUES (1,1,'Larga','L',1,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(2,1,'Corta','C',2,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(3,2,'Clásico','CLA',1,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(4,2,'Mao','MAO',2,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(5,2,'Con Tapa Botones','CTB',3,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(6,2,'Redondo','R',4,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(7,2,'V','V',5,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(8,3,'Rígido','RIG',1,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(9,3,'Stretch','STR',1,'2026-05-07 17:15:29','2026-05-27 02:50:38'),(10,4,'Cremallera','CRE',1,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(11,4,'Botones','BOT',2,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(12,4,'Cerrado','CER',3,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(13,5,'Con capucha','CCH',1,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(14,5,'Sin capucha','SCH',2,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(15,3,'Clasico','CL',3,'2026-05-27 02:50:25','2026-05-27 02:50:25'),(16,3,'Columbia I','CLM1',4,'2026-05-27 02:51:17','2026-05-27 02:51:17'),(17,3,'Columbia II','CLM2',5,'2026-05-27 02:51:36','2026-05-27 02:51:36');
/*!40000 ALTER TABLE `atributo_valor` ENABLE KEYS */;

--
-- Table structure for table `banco`
--

DROP TABLE IF EXISTS `banco`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banco` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bancos_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banco`
--

/*!40000 ALTER TABLE `banco` DISABLE KEYS */;
INSERT INTO `banco` VALUES (1,'Banco de Venezuela','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(2,'Banco Mercantil','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(3,'Banco Provincial','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(4,'Banesco','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(5,'Bancaribe','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(6,'BOD','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(7,'Banco Caroní','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(8,'Banco Plaza','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(9,'BFC Banco Fondo Común','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(10,'100% Banco','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(11,'DelSur','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(12,'Banco del Tesoro','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(13,'Bancrecer','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(14,'Banco Activo','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(15,'Bancamiga','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(16,'Banplus','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(17,'Banco Bicentenario','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(18,'BNC Nacional de Crédito','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(19,'Zelle','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(20,'PayPal','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(21,'Binance','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL),(22,'Efectivo','2026-01-19 19:27:19','2026-01-19 19:27:19',NULL);
/*!40000 ALTER TABLE `banco` ENABLE KEYS */;

--
-- Table structure for table `bordado_ubicacion`
--

DROP TABLE IF EXISTS `bordado_ubicacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bordado_ubicacion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grupo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio_base` decimal(10,2) NOT NULL DEFAULT '0.00',
  `orden` int unsigned NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bordado_ubicaciones_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bordado_ubicacion`
--

/*!40000 ALTER TABLE `bordado_ubicacion` DISABLE KEYS */;
INSERT INTO `bordado_ubicacion` VALUES (1,'Frontal Izquierdo','Frontal',3.00,10,1,'2026-02-24 02:48:28','2026-02-24 02:48:28',NULL),(2,'Frontal Derecho','Frontal',3.00,20,1,'2026-02-24 02:48:28','2026-02-24 02:48:28',NULL),(3,'Manga Izquierda','Mangas',3.00,30,1,'2026-02-24 02:48:28','2026-02-24 02:48:28',NULL),(4,'Manga Derecha','Mangas',3.00,40,1,'2026-02-24 02:48:28','2026-02-24 02:48:28',NULL),(5,'Espaldar','Espalda',5.00,50,1,'2026-02-24 02:48:28','2026-02-24 02:48:28',NULL);
/*!40000 ALTER TABLE `bordado_ubicacion` ENABLE KEYS */;

--
-- Table structure for table `cargo`
--

DROP TABLE IF EXISTS `cargo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cargo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `departamento_id` bigint unsigned NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cargo_nombre_departamento_id_unique` (`nombre`,`departamento_id`),
  KEY `cargo_departamento_id_foreign` (`departamento_id`),
  CONSTRAINT `cargo_departamento_id_foreign` FOREIGN KEY (`departamento_id`) REFERENCES `departamento` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cargo`
--

/*!40000 ALTER TABLE `cargo` DISABLE KEYS */;
INSERT INTO `cargo` VALUES (1,'Supervisor de Producción',1,1,'2026-04-21 20:16:52','2026-04-21 20:16:52',NULL),(2,'Supervisor',2,1,'2026-04-21 20:16:52','2026-04-21 20:32:45','2026-04-21 20:32:45'),(3,'Cortador',1,1,'2026-04-21 20:16:52','2026-04-21 20:16:52',NULL),(4,'Limpieza',4,1,'2026-04-21 20:16:52','2026-05-29 20:06:02',NULL),(5,'Supervisor 2',1,1,'2026-04-21 20:16:52','2026-04-21 20:32:49','2026-04-21 20:32:49'),(6,'Gerente',2,1,'2026-04-21 20:33:35','2026-04-21 20:33:35',NULL),(7,'Supervisor de almacen',3,1,'2026-04-29 00:11:01','2026-04-29 00:11:01',NULL),(8,'Aseador',4,1,'2026-04-29 00:12:43','2026-05-29 20:06:12','2026-05-29 20:06:12'),(9,'Costurera',1,1,'2026-05-29 20:05:05','2026-05-29 20:05:05',NULL),(10,'Bordador',1,1,'2026-05-30 19:46:32','2026-05-30 19:46:32',NULL);
/*!40000 ALTER TABLE `cargo` ENABLE KEYS */;

--
-- Table structure for table `cliente`
--

DROP TABLE IF EXISTS `cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cliente` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `persona_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `tipo_cliente` enum('natural','juridico') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'natural',
  `estatus` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `cliente_persona_id_foreign` (`persona_id`),
  CONSTRAINT `cliente_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cliente`
--

/*!40000 ALTER TABLE `cliente` DISABLE KEYS */;
INSERT INTO `cliente` VALUES (1,3,'2025-12-04 19:37:44','2026-01-16 21:57:40','2026-01-16 21:57:40','natural',1),(2,6,'2025-12-05 19:22:15','2026-01-16 21:57:58','2026-01-16 21:57:58','natural',1),(3,7,'2025-12-08 19:23:05','2026-01-16 21:57:47','2026-01-16 21:57:47','natural',1),(4,8,'2025-12-08 20:04:57','2025-12-09 18:51:56','2025-12-09 18:51:56','natural',1),(5,9,'2025-12-08 20:19:32','2025-12-09 18:16:57','2025-12-09 18:16:57','natural',1),(6,10,'2025-12-09 18:54:35','2025-12-09 18:57:12','2025-12-09 18:57:12','natural',1),(7,11,'2025-12-10 18:09:48','2026-01-16 21:57:54','2026-01-16 21:57:54','natural',1),(8,12,'2025-12-10 20:29:40','2026-05-29 20:48:46',NULL,'natural',1),(9,15,'2026-01-17 16:51:52','2026-01-17 16:51:52',NULL,'natural',1),(10,16,'2026-01-17 17:11:09','2026-01-17 17:11:09',NULL,'natural',1),(11,17,'2026-01-17 22:05:23','2026-01-17 22:10:55','2026-01-17 22:10:55','natural',1),(12,18,'2026-01-17 22:31:33','2026-01-17 22:31:33',NULL,'natural',1),(13,19,'2026-01-18 03:49:00','2026-01-18 03:54:29','2026-01-18 03:54:29','natural',1),(14,20,'2026-01-18 03:56:57','2026-01-18 03:56:57',NULL,'natural',1),(15,29,'2026-01-19 00:25:34','2026-01-19 00:25:34',NULL,'natural',1),(16,30,'2026-01-19 03:56:04','2026-01-19 04:20:07','2026-01-19 04:20:07','natural',1),(17,31,'2026-01-19 04:01:50','2026-01-19 04:20:11','2026-01-19 04:20:11','natural',1),(18,32,'2026-01-19 04:05:33','2026-01-19 04:20:04','2026-01-19 04:20:04','natural',1),(19,33,'2026-01-19 04:17:44','2026-01-19 04:20:00','2026-01-19 04:20:00','natural',1),(20,34,'2026-01-19 04:26:34','2026-01-19 04:26:34',NULL,'natural',1),(21,35,'2026-01-19 16:49:16','2026-01-19 16:49:16',NULL,'natural',1),(22,36,'2026-01-20 01:29:08','2026-01-20 01:29:08',NULL,'natural',1),(23,37,'2026-01-20 21:23:34','2026-01-20 21:23:34',NULL,'natural',1),(24,38,'2026-02-22 17:56:13','2026-02-26 20:20:19','2026-02-26 20:20:19','juridico',1),(25,39,'2026-02-26 19:10:25','2026-02-26 19:10:25',NULL,'natural',1),(26,40,'2026-02-26 19:10:25','2026-02-26 19:10:25',NULL,'natural',1),(27,42,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL,'juridico',1),(28,43,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL,'natural',1),(29,44,'2026-02-26 19:11:43','2026-02-26 20:27:30','2026-02-26 20:27:30','juridico',1),(30,45,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL,'natural',1),(31,46,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL,'natural',1),(32,47,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL,'juridico',1),(33,48,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL,'natural',1),(34,49,'2026-02-26 19:11:44','2026-02-26 19:11:44',NULL,'natural',1),(35,50,'2026-02-26 19:46:04','2026-02-26 19:46:04',NULL,'natural',1),(36,51,'2026-03-05 17:17:09','2026-03-05 17:17:09',NULL,'juridico',1),(37,60,'2026-04-14 21:26:32','2026-04-14 21:26:32',NULL,'natural',1),(38,61,'2026-04-29 00:22:53','2026-04-29 00:22:53',NULL,'natural',1),(39,25,'2026-04-30 20:32:50','2026-04-30 20:32:50',NULL,'natural',1),(40,4,'2026-04-30 20:34:42','2026-04-30 20:34:42',NULL,'natural',1),(41,65,'2026-04-30 20:36:55','2026-04-30 20:36:55',NULL,'natural',1),(42,13,'2026-05-28 03:49:39','2026-05-28 03:49:39',NULL,'natural',1);
/*!40000 ALTER TABLE `cliente` ENABLE KEYS */;

--
-- Table structure for table `color`
--

DROP TABLE IF EXISTS `color`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `color` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre comercial del color (Ej: Azul Marino)',
  `hex_referencial` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Color HEX referencial para el swatch (#1B3A5C)',
  `grupo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Agrupación visual: Básicos, Pasteles, Oscuros, etc.',
  `activo` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Permite desactivar colores sin borrarlos',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `colores_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `color`
--

/*!40000 ALTER TABLE `color` DISABLE KEYS */;
INSERT INTO `color` VALUES (1,'Blanco','#FFFFFF','Básicos',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(2,'Negro','#1C1C1C','Básicos',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(3,'Gris Claro','#C0C0C0','Básicos',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(4,'Gris Oscuro','#5A5A5A','Básicos',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(5,'Gris Jaspeado','#9E9E9E','Básicos',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(6,'Azul Marino','#1B3A5C','Azules',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(7,'Azul Royal','#2E5DA8','Azules',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(8,'Azul Cielo','#87CEEB','Azules',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(9,'Azul Turquesa','#40B5AD','Azules',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(10,'Azul Petróleo','#1A5276','Azules',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(11,'Rojo','#C0392B','Rojos y Cálidos',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(12,'Rojo Vino','#722F37','Rojos y Cálidos',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(13,'Naranja','#E67E22','Rojos y Cálidos',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(14,'Coral','#E8735A','Rojos y Cálidos',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(15,'Amarillo','#F1C40F','Rojos y Cálidos',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(16,'Verde Oscuro','#1E5631','Verdes',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(17,'Verde Oliva','#6B8E23','Verdes',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(18,'Verde Menta','#98D8C8','Verdes',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(19,'Verde Botella','#0B5345','Verdes',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(20,'Rosa Pastel','#F5B7C1','Pasteles',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(21,'Celeste','#AED6F1','Pasteles',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(22,'Lila','#C39BD3','Pasteles',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(23,'Melocotón','#F5CBA7','Pasteles',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(24,'Lavanda','#D7BDE2','Pasteles',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(25,'Beige','#F5DEB3','Tierra y Neutros',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(26,'Caqui','#C3B091','Tierra y Neutros',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(27,'Marrón','#6E3B23','Tierra y Neutros',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(28,'Café','#4E342E','Tierra y Neutros',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(29,'Crema','#FFFDD0','Tierra y Neutros',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(30,'Fucsia','#C71585','Especiales',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(31,'Morado','#6C3483','Especiales',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(32,'Dorado','#D4A017','Especiales',1,'2026-02-23 18:14:33','2026-02-23 18:14:33',NULL),(34,'verde benetton','#008860','Verdes',1,'2026-06-03 23:17:46','2026-06-03 23:17:46',NULL);
/*!40000 ALTER TABLE `color` ENABLE KEYS */;

--
-- Table structure for table `compra`
--

DROP TABLE IF EXISTS `compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compra` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proveedor_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `numero_factura` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_compra` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `tipo_pago` enum('contado','credito') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'contado',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `iva` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `estado` enum('borrador','recibida','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'recibida',
  `clonada` tinyint(1) NOT NULL DEFAULT '0',
  `anulado_por_id` bigint unsigned DEFAULT NULL,
  `fecha_anulacion` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `compra_proveedor_id_foreign` (`proveedor_id`),
  KEY `compra_user_id_foreign` (`user_id`),
  KEY `compra_anulado_por_id_foreign` (`anulado_por_id`),
  CONSTRAINT `compra_anulado_por_id_foreign` FOREIGN KEY (`anulado_por_id`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `compra_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedor` (`id`),
  CONSTRAINT `compra_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compra`
--

/*!40000 ALTER TABLE `compra` DISABLE KEYS */;
INSERT INTO `compra` VALUES (1,2,7,NULL,'2026-06-02',NULL,'contado',30.00,4.80,34.80,NULL,'recibida',0,NULL,NULL,'2026-06-02 20:18:51','2026-06-02 20:18:51',NULL),(2,2,7,'0001','2026-06-02',NULL,'contado',210.00,33.60,243.60,'Pago Insumos','recibida',0,NULL,NULL,'2026-06-02 20:48:34','2026-06-02 20:48:34',NULL);
/*!40000 ALTER TABLE `compra` ENABLE KEYS */;

--
-- Table structure for table `compra_detalle`
--

DROP TABLE IF EXISTS `compra_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compra_detalle` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `compra_id` bigint unsigned NOT NULL,
  `insumo_id` bigint unsigned NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `costo_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `compra_detalle_compra_id_foreign` (`compra_id`),
  KEY `compra_detalle_insumo_id_foreign` (`insumo_id`),
  CONSTRAINT `compra_detalle_compra_id_foreign` FOREIGN KEY (`compra_id`) REFERENCES `compra` (`id`) ON DELETE CASCADE,
  CONSTRAINT `compra_detalle_insumo_id_foreign` FOREIGN KEY (`insumo_id`) REFERENCES `insumo` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compra_detalle`
--

/*!40000 ALTER TABLE `compra_detalle` DISABLE KEYS */;
INSERT INTO `compra_detalle` VALUES (1,1,5,10.00,3.00,30.00,'2026-06-02 20:18:51','2026-06-02 20:18:51'),(2,2,5,10.00,3.00,30.00,'2026-06-02 20:48:34','2026-06-02 20:48:34'),(3,2,8,10.00,18.00,180.00,'2026-06-02 20:48:34','2026-06-02 20:48:34');
/*!40000 ALTER TABLE `compra_detalle` ENABLE KEYS */;

--
-- Table structure for table `cotizacion`
--

DROP TABLE IF EXISTS `cotizacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cotizacion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint unsigned NOT NULL,
  `fecha_cotizacion` date NOT NULL,
  `fecha_validez` date DEFAULT NULL,
  `estado` enum('Pendiente','Aprobada','Cancelada','Convertida','Vencida') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tasa_cambio_valor` decimal(10,4) DEFAULT NULL COMMENT 'Tasa BCV USD→VES vigente al momento de crear/actualizar la cotización',
  `notas` text COLLATE utf8mb4_unicode_ci,
  `condiciones_terminos` text COLLATE utf8mb4_unicode_ci COMMENT 'Cláusulas legales y condiciones de la cotización',
  `user_id` bigint unsigned NOT NULL,
  `prioridad` enum('Normal','Alta','Urgente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Normal',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cotizaciones_user_id_foreign` (`user_id`),
  KEY `cotizacion_cliente_id_foreign` (`cliente_id`),
  KEY `idx_cotizacion_estado` (`estado`),
  KEY `idx_cotizacion_cliente_estado` (`cliente_id`,`estado`),
  CONSTRAINT `cotizacion_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`),
  CONSTRAINT `cotizacion_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cotizacion`
--

/*!40000 ALTER TABLE `cotizacion` DISABLE KEYS */;
INSERT INTO `cotizacion` VALUES (1,2,'2025-11-11','2025-12-31','Aprobada',59.80,NULL,NULL,NULL,1,'Normal','2025-12-08 18:51:54','2026-01-17 16:56:05','2026-01-17 16:56:05'),(2,3,'2025-11-10','2026-01-01','Aprobada',599.00,NULL,NULL,NULL,1,'Normal','2025-12-08 19:24:18','2026-01-17 16:56:10','2026-01-17 16:56:10'),(3,5,'2025-10-09','2025-12-24','Aprobada',599.00,NULL,NULL,NULL,1,'Normal','2025-12-08 20:21:31','2026-01-17 16:56:16','2026-01-17 16:56:16'),(4,6,'2025-11-13','2026-01-30','Aprobada',2396.00,NULL,NULL,NULL,1,'Normal','2025-12-09 18:56:23','2026-01-17 16:48:18','2026-01-17 16:48:18'),(5,7,'2025-11-06','2025-12-28','Pendiente',897.00,NULL,NULL,NULL,1,'Normal','2025-12-10 18:11:52','2026-01-17 16:48:14','2026-01-17 16:48:14'),(6,7,'2025-12-10','2025-12-31','Aprobada',419.00,NULL,NULL,NULL,1,'Normal','2025-12-11 00:29:48','2026-01-16 22:02:54','2026-01-16 22:02:54'),(7,1,'2025-12-19','2025-12-30','Convertida',34.00,NULL,NULL,NULL,1,'Normal','2025-12-19 20:15:18','2026-01-16 22:02:37','2026-01-16 22:02:37'),(8,9,'2026-01-17','2026-01-24','Cancelada',119.60,NULL,NULL,NULL,5,'Normal','2026-01-17 16:53:23','2026-01-17 22:48:34',NULL),(9,12,'2026-01-19','2026-01-22','Vencida',119.60,554.4258,NULL,NULL,1,'Normal','2026-01-17 17:11:33','2026-06-01 19:10:27',NULL),(10,8,'2026-01-17','2026-01-22','Aprobada',9956.70,NULL,NULL,NULL,1,'Normal','2026-01-17 22:35:19','2026-01-19 04:36:58','2026-01-19 04:36:58'),(11,14,'2026-01-18','2026-01-28','Convertida',388.70,NULL,NULL,NULL,1,'Normal','2026-01-18 22:11:33','2026-01-19 03:42:07',NULL),(12,9,'2026-01-19','2026-01-20','Vencida',50.00,NULL,NULL,NULL,5,'Normal','2026-01-18 23:44:02','2026-01-22 03:14:17',NULL),(13,15,'2026-01-15','2026-01-23','Convertida',25.00,NULL,NULL,NULL,1,'Normal','2026-01-19 00:26:43','2026-01-19 19:24:34',NULL),(14,9,'2026-01-19','2026-01-22','Convertida',1317.80,NULL,NULL,NULL,5,'Normal','2026-01-19 04:25:53','2026-01-19 19:13:45',NULL),(15,20,'2026-01-19','2026-01-22','Vencida',1317.80,NULL,NULL,NULL,1,'Normal','2026-01-19 04:26:49','2026-01-25 02:13:47',NULL),(16,9,'2026-01-19','2026-01-22','Convertida',374.00,NULL,NULL,NULL,1,'Normal','2026-01-19 05:10:13','2026-01-20 21:34:18',NULL),(17,15,'2026-01-19','2026-01-23','Convertida',2963.40,NULL,NULL,NULL,1,'Normal','2026-01-19 05:15:58','2026-01-19 20:16:20',NULL),(18,21,'2026-01-19','2026-01-21','Convertida',627.00,NULL,NULL,NULL,1,'Normal','2026-01-19 16:49:36','2026-01-19 19:51:33',NULL),(19,9,'2026-01-19','2026-01-23','Convertida',986.70,NULL,NULL,NULL,1,'Normal','2026-01-19 16:51:55','2026-01-19 19:32:56',NULL),(20,22,'2026-01-19','2026-01-30','Convertida',29.90,NULL,NULL,NULL,1,'Normal','2026-01-20 01:35:43','2026-01-20 21:21:33',NULL),(21,23,'2026-01-20','2026-01-31','Convertida',51.00,NULL,NULL,NULL,1,'Normal','2026-01-20 21:25:00','2026-01-20 21:25:39',NULL),(22,23,'2026-01-19','2026-01-28','Convertida',358.80,NULL,NULL,NULL,1,'Normal','2026-01-20 21:31:53','2026-01-20 21:32:04',NULL),(23,23,'2026-01-21','2026-01-31','Convertida',190.00,NULL,NULL,NULL,1,'Normal','2026-01-20 21:39:00','2026-01-20 21:39:09',NULL),(24,14,'2026-01-20','2026-01-30','Convertida',29.90,NULL,NULL,NULL,1,'Normal','2026-01-20 21:45:35','2026-01-20 21:45:49',NULL),(25,23,'2026-01-20','2026-01-31','Convertida',29.90,NULL,NULL,NULL,1,'Normal','2026-01-20 21:51:41','2026-01-20 21:52:03',NULL),(26,23,'2026-01-20','2026-01-29','Convertida',59.80,NULL,NULL,NULL,1,'Normal','2026-01-20 21:56:09','2026-01-20 21:56:29',NULL),(27,23,'2026-01-20','2026-02-03','Convertida',29.90,NULL,NULL,NULL,1,'Normal','2026-01-20 22:01:32','2026-01-20 22:01:40',NULL),(28,23,'2026-01-19','2026-01-28','Convertida',358.80,NULL,NULL,NULL,1,'Normal','2026-01-20 22:21:26','2026-01-20 22:21:37',NULL),(29,23,'2026-02-19','2026-02-27','Convertida',17.00,NULL,NULL,NULL,1,'Normal','2026-02-19 20:02:04','2026-02-19 20:07:49',NULL),(30,23,'2026-02-19','2026-02-24','Convertida',204.00,NULL,NULL,NULL,1,'Normal','2026-02-19 20:53:01','2026-02-19 20:53:28',NULL),(31,23,'2026-02-12','2026-02-28','Convertida',204.00,NULL,NULL,NULL,1,'Normal','2026-02-20 00:04:01','2026-02-20 00:04:19',NULL),(32,23,'2026-02-19','2026-04-25','Convertida',156.00,NULL,NULL,NULL,1,'Normal','2026-02-20 00:28:01','2026-02-20 00:28:11',NULL),(33,23,'2026-02-19','2026-02-23','Convertida',578.00,NULL,NULL,NULL,1,'Normal','2026-02-20 00:32:09','2026-02-20 00:32:25',NULL),(34,23,'2026-02-26','2026-02-28','Convertida',51.00,NULL,NULL,NULL,1,'Normal','2026-02-20 00:43:30','2026-02-20 00:43:39',NULL),(35,23,'2026-02-19','2026-05-21','Convertida',85.00,NULL,NULL,NULL,1,'Normal','2026-02-20 00:52:07','2026-02-20 00:52:16',NULL),(36,23,'2026-02-19','2026-09-17','Convertida',170.00,NULL,NULL,NULL,1,'Normal','2026-02-20 01:01:13','2026-02-20 01:01:25',NULL),(37,23,'2026-02-19','2026-02-20','Convertida',340.00,NULL,NULL,NULL,1,'Normal','2026-02-20 01:04:12','2026-02-20 01:04:25',NULL),(38,9,'2026-02-22','2026-02-27','Convertida',204.00,NULL,'Condiciones....',NULL,1,'Normal','2026-02-22 17:57:57','2026-02-22 20:52:23',NULL),(39,9,'2026-02-23','2026-02-26','Convertida',170.00,NULL,'Sakura Fest tiene que pagar rapido',NULL,1,'Normal','2026-02-23 18:44:06','2026-02-23 20:00:14',NULL),(40,23,'2026-02-23','2026-02-27','Convertida',170.00,NULL,NULL,NULL,1,'Normal','2026-02-23 21:30:18','2026-02-24 20:05:58',NULL),(41,23,'2026-02-24','2026-02-28','Convertida',22.00,NULL,'Que quede todo bello',NULL,1,'Normal','2026-02-23 21:46:23','2026-02-24 19:41:36',NULL),(42,23,'2026-02-23','2026-02-24','Convertida',170.00,NULL,'Que quede muy bien',NULL,1,'Normal','2026-02-23 22:16:34','2026-02-24 16:36:47',NULL),(43,8,'2026-02-23','2026-03-05','Convertida',94.50,NULL,'QA E2E bordados update',NULL,1,'Normal','2026-02-24 02:54:17','2026-02-24 02:57:23','2026-02-24 02:57:23'),(44,8,'2026-02-23','2026-03-05','Convertida',94.50,NULL,'QA E2E bordados script update',NULL,1,'Normal','2026-02-24 02:58:25','2026-02-24 02:58:25','2026-02-24 02:58:25'),(45,8,'2026-02-23','2026-03-05','Convertida',94.50,NULL,'QA E2E bordados script update',NULL,1,'Normal','2026-02-24 03:26:39','2026-02-24 03:26:40','2026-02-24 03:26:40'),(46,9,'2026-02-24','2026-04-29','Convertida',253.00,NULL,'Se requiere un abono del 50% del monto total para empezar a producir',NULL,1,'Normal','2026-02-24 14:41:49','2026-02-24 15:51:39',NULL),(47,8,'2026-02-24','2026-03-06','Convertida',94.50,NULL,'QA E2E bordados script update',NULL,1,'Normal','2026-02-24 16:31:27','2026-02-24 16:31:28','2026-02-24 16:31:28'),(48,23,'2026-02-24','2026-02-26','Convertida',260.00,NULL,'hacer bien',NULL,1,'Normal','2026-02-24 17:25:57','2026-02-24 17:26:11',NULL),(49,28,'2026-02-26','2026-02-28','Convertida',470.00,NULL,'Fabricar con estandares excelentes',NULL,1,'Normal','2026-02-26 20:32:49','2026-02-26 20:33:31',NULL),(50,28,'2026-02-26','2026-02-28','Convertida',34.00,NULL,'condiciones',NULL,1,'Normal','2026-02-26 20:36:45','2026-02-26 20:36:54',NULL),(51,28,'2026-02-26','2026-02-28','Convertida',17.00,NULL,'hola',NULL,1,'Normal','2026-02-26 20:39:59','2026-02-26 20:40:21',NULL),(52,28,'2026-02-26','2026-02-27','Convertida',51.00,NULL,NULL,NULL,1,'Normal','2026-02-26 20:42:22','2026-02-26 20:42:31',NULL),(53,23,'2026-02-26','2026-02-27','Convertida',170.00,NULL,NULL,NULL,1,'Normal','2026-02-26 20:48:10','2026-02-26 20:49:46',NULL),(54,23,'2026-02-11','2026-02-27','Convertida',17.00,NULL,NULL,NULL,1,'Normal','2026-02-26 20:50:24','2026-02-26 20:50:33',NULL),(55,23,'2026-02-26','2026-02-27','Convertida',16.00,NULL,NULL,NULL,1,'Normal','2026-02-26 20:51:52','2026-02-26 20:54:22',NULL),(56,23,'2026-02-26','2026-02-27','Convertida',16.00,NULL,NULL,NULL,1,'Normal','2026-02-26 21:01:29','2026-02-26 21:01:40',NULL),(57,36,'2026-03-05','2026-03-06','Convertida',190.00,NULL,'Maiz',NULL,7,'Normal','2026-03-05 17:30:11','2026-03-05 17:32:34',NULL),(58,36,'2026-03-05','2026-03-28','Convertida',200.00,NULL,'hola',NULL,7,'Normal','2026-03-05 17:47:35','2026-03-05 17:47:46',NULL),(59,36,'2026-03-05','2026-03-14','Convertida',110.00,NULL,'Pronto',NULL,7,'Normal','2026-03-05 20:03:32','2026-03-05 20:13:13',NULL),(60,36,'2026-03-05','2026-06-17','Pendiente',80.00,557.9741,'lol',NULL,7,'Normal','2026-03-05 20:52:23','2026-06-03 02:42:18',NULL),(61,23,'2026-03-10','2026-03-26','Convertida',160.00,NULL,'Precio razonable',NULL,7,'Normal','2026-03-10 20:53:07','2026-03-10 20:54:39',NULL),(62,23,'2026-03-15','2026-03-18','Convertida',16.00,NULL,'w',NULL,7,'Normal','2026-03-15 14:51:30','2026-03-18 14:07:18',NULL),(63,8,'2026-03-19','2026-03-29','Convertida',94.50,NULL,'QA E2E bordados script update',NULL,1,'Normal','2026-03-19 21:35:32','2026-03-19 21:35:33','2026-03-19 21:35:33'),(64,8,'2026-03-20','2026-03-30','Convertida',94.50,NULL,'QA E2E bordados script update',NULL,1,'Normal','2026-03-20 13:17:33','2026-03-20 13:17:34','2026-03-20 13:17:34'),(65,23,'2026-05-03','2026-05-29','Convertida',17.00,NULL,NULL,NULL,1,'Normal','2026-05-03 16:46:06','2026-05-26 23:51:07',NULL),(66,23,'2026-05-07','2026-05-22','Convertida',33.00,NULL,'entregar bonito',NULL,1,'Normal','2026-05-07 18:38:20','2026-05-07 18:38:53',NULL),(67,23,'2026-05-14','2026-05-29','Convertida',348.00,NULL,NULL,NULL,1,'Normal','2026-05-14 23:53:04','2026-05-15 00:25:57',NULL),(68,23,'2026-05-26','2026-06-11','Convertida',16.00,NULL,NULL,NULL,1,'Normal','2026-05-27 01:26:57','2026-05-27 01:29:00',NULL),(69,23,'2026-05-27','2026-06-11','Convertida',16.00,NULL,NULL,NULL,1,'Normal','2026-05-27 05:01:07','2026-05-28 01:06:26',NULL),(70,23,'2026-05-13','2026-06-12','Aprobada',32.00,NULL,NULL,NULL,1,'Normal','2026-05-28 00:56:55','2026-05-28 00:56:59',NULL),(71,42,'2026-05-30','2026-06-13','Convertida',64.00,NULL,NULL,NULL,1,'Normal','2026-05-29 14:57:19','2026-05-29 14:58:08',NULL),(72,23,'2026-05-29','2026-06-13','Convertida',1126.00,NULL,NULL,NULL,1,'Normal','2026-05-29 23:07:27','2026-05-29 23:14:13',NULL),(73,22,'2026-05-30','2026-06-14','Pendiente',180.00,NULL,NULL,NULL,7,'Normal','2026-05-30 20:23:32','2026-05-30 20:23:32',NULL),(74,14,'2026-06-01','2026-06-16','Convertida',17.00,554.4258,NULL,NULL,1,'Normal','2026-06-01 18:50:22','2026-06-01 18:52:08',NULL);
/*!40000 ALTER TABLE `cotizacion` ENABLE KEYS */;

--
-- Table structure for table `departamento`
--

DROP TABLE IF EXISTS `departamento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departamento` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departamento_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departamento`
--

/*!40000 ALTER TABLE `departamento` DISABLE KEYS */;
INSERT INTO `departamento` VALUES (1,'Produccion',1,'2026-04-21 20:16:52','2026-04-21 20:16:52',NULL),(2,'Administracion',1,'2026-04-21 20:16:52','2026-04-21 20:16:52',NULL),(3,'Logistica',1,'2026-04-29 00:10:36','2026-04-29 00:10:36',NULL),(4,'Mantenimiento',1,'2026-04-29 00:12:02','2026-04-29 00:12:02',NULL);
/*!40000 ALTER TABLE `departamento` ENABLE KEYS */;

--
-- Table structure for table `detalle_cotizacion`
--

DROP TABLE IF EXISTS `detalle_cotizacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_cotizacion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cotizacion_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `tela_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `atributos_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `cantidad` int NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `lleva_bordado` tinyint(1) NOT NULL DEFAULT '0',
  `color_id` bigint unsigned DEFAULT NULL,
  `talla_id` bigint unsigned DEFAULT NULL,
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detalle_cotizaciones_cotizacion_id_foreign` (`cotizacion_id`),
  KEY `detalle_cotizaciones_producto_id_foreign` (`producto_id`),
  KEY `detalle_cotizacion_color_id_foreign` (`color_id`),
  KEY `detalle_cotizacion_talla_id_foreign` (`talla_id`),
  CONSTRAINT `detalle_cotizacion_color_id_foreign` FOREIGN KEY (`color_id`) REFERENCES `color` (`id`) ON DELETE SET NULL,
  CONSTRAINT `detalle_cotizacion_talla_id_foreign` FOREIGN KEY (`talla_id`) REFERENCES `talla` (`id`) ON DELETE SET NULL,
  CONSTRAINT `detalle_cotizaciones_cotizacion_id_foreign` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizacion` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_cotizaciones_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`),
  CONSTRAINT `detalle_cotizacion_chk_1` CHECK (json_valid(`tela_snapshot`)),
  CONSTRAINT `detalle_cotizacion_chk_2` CHECK (json_valid(`atributos_snapshot`))
) ENGINE=InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_cotizacion`
--

/*!40000 ALTER TABLE `detalle_cotizacion` DISABLE KEYS */;
INSERT INTO `detalle_cotizacion` VALUES (3,1,1,NULL,NULL,2,'Promo Utah Valley University',1,NULL,11,29.90,'2025-12-08 19:11:06','2025-12-08 19:11:06'),(4,2,2,NULL,NULL,10,'Camisa para Oracle',1,NULL,12,59.90,'2025-12-08 19:24:18','2025-12-08 19:24:18'),(5,3,2,NULL,NULL,10,'Camisas Corte Clasico',1,NULL,14,59.90,'2025-12-08 20:21:31','2025-12-08 20:21:31'),(6,4,2,NULL,NULL,40,'Camisas Corte Columbia para Empresa de GPU\'S',1,NULL,12,59.90,'2025-12-09 18:56:23','2025-12-09 18:56:23'),(8,5,1,NULL,NULL,30,'Cotizacion para empresa de IA',1,NULL,11,29.90,'2025-12-10 23:45:22','2025-12-10 23:45:22'),(9,6,1,NULL,NULL,6,'Chemise Clasica para empresa de IA',1,NULL,4,29.90,'2025-12-11 00:29:48','2025-12-11 00:29:48'),(10,6,2,NULL,NULL,4,'Camisa para empresa',1,NULL,13,59.90,'2025-12-11 00:29:48','2025-12-11 00:29:48'),(11,7,6,NULL,NULL,2,NULL,1,NULL,12,17.00,'2025-12-19 20:15:18','2025-12-19 20:15:18'),(21,8,1,NULL,NULL,4,'null',1,NULL,10,29.90,'2026-01-17 22:48:34','2026-01-17 22:48:34'),(30,11,1,NULL,NULL,13,'Chemises Unicolor',1,NULL,12,29.90,'2026-01-19 02:32:34','2026-01-19 02:32:34'),(31,10,1,NULL,NULL,333,'null',0,NULL,1,29.90,'2026-01-19 02:34:08','2026-01-19 02:34:08'),(34,12,5,NULL,NULL,2,'null',1,NULL,10,25.00,'2026-01-19 04:34:46','2026-01-19 04:34:46'),(36,15,2,NULL,NULL,22,'null',0,NULL,3,59.90,'2026-01-19 04:37:45','2026-01-19 04:37:45'),(37,16,6,NULL,NULL,22,'weqew',1,NULL,10,17.00,'2026-01-19 05:10:13','2026-01-19 05:10:13'),(40,14,2,NULL,NULL,22,'null',0,NULL,3,59.90,'2026-01-19 05:24:59','2026-01-19 05:24:59'),(44,9,1,NULL,NULL,4,'null',0,NULL,2,29.90,'2026-01-19 18:53:19','2026-01-19 18:53:19'),(45,13,5,NULL,NULL,1,'null',0,NULL,10,25.00,'2026-01-19 18:53:47','2026-01-19 18:53:47'),(46,19,1,NULL,NULL,33,'null',0,NULL,2,29.90,'2026-01-19 19:31:03','2026-01-19 19:31:03'),(47,18,4,NULL,NULL,33,'null',0,NULL,2,19.00,'2026-01-19 19:51:06','2026-01-19 19:51:06'),(50,17,1,NULL,NULL,33,'scs',0,NULL,1,29.90,'2026-01-19 20:15:52','2026-01-19 20:15:52'),(51,17,2,NULL,NULL,33,'null',0,NULL,12,59.90,'2026-01-19 20:15:52','2026-01-19 20:15:52'),(54,20,1,NULL,NULL,1,'faltan medidas',1,NULL,13,29.90,'2026-01-20 01:41:41','2026-01-20 01:41:41'),(55,21,6,NULL,NULL,3,'Chemises para tienda de servicio tecnico',1,NULL,12,17.00,'2026-01-20 21:25:00','2026-01-20 21:25:00'),(56,22,1,NULL,NULL,12,'Dotacion',1,NULL,7,29.90,'2026-01-20 21:31:53','2026-01-20 21:31:53'),(57,23,4,NULL,NULL,10,'10',1,NULL,1,19.00,'2026-01-20 21:39:00','2026-01-20 21:39:00'),(58,24,1,NULL,NULL,1,'Bera',1,NULL,12,29.90,'2026-01-20 21:45:35','2026-01-20 21:45:35'),(59,25,1,NULL,NULL,1,'urgente',1,NULL,11,29.90,'2026-01-20 21:51:41','2026-01-20 21:51:41'),(60,26,1,NULL,NULL,2,'urgente',1,NULL,12,29.90,'2026-01-20 21:56:09','2026-01-20 21:56:09'),(61,27,1,NULL,NULL,1,'si',0,NULL,12,29.90,'2026-01-20 22:01:32','2026-01-20 22:01:32'),(62,28,1,NULL,NULL,12,'pedido urgente',0,NULL,11,29.90,'2026-01-20 22:21:26','2026-01-20 22:21:26'),(63,29,6,NULL,NULL,1,'chemise normal',1,NULL,11,17.00,'2026-02-19 20:02:04','2026-02-19 20:02:04'),(64,30,6,NULL,NULL,12,'chemise calidad',1,NULL,10,17.00,'2026-02-19 20:53:01','2026-02-19 20:53:01'),(65,31,6,NULL,NULL,12,'restaurante',1,NULL,8,17.00,'2026-02-20 00:04:01','2026-02-20 00:04:01'),(66,32,7,NULL,NULL,13,'lolita',1,NULL,10,12.00,'2026-02-20 00:28:01','2026-02-20 00:28:01'),(67,33,6,NULL,NULL,34,'banda',1,NULL,11,17.00,'2026-02-20 00:32:09','2026-02-20 00:32:09'),(68,34,6,NULL,NULL,3,'loli',1,NULL,12,17.00,'2026-02-20 00:43:30','2026-02-20 00:43:30'),(69,35,6,NULL,NULL,5,'psuv',1,NULL,12,17.00,'2026-02-20 00:52:07','2026-02-20 00:52:07'),(70,36,6,NULL,NULL,10,'mandarinas',1,NULL,12,17.00,'2026-02-20 01:01:13','2026-02-20 01:01:13'),(71,37,6,NULL,NULL,20,'nube',1,NULL,10,17.00,'2026-02-20 01:04:12','2026-02-20 01:04:12'),(72,38,6,NULL,NULL,12,'Para restaurante',0,NULL,12,17.00,'2026-02-22 17:57:57','2026-02-22 17:57:57'),(73,39,6,NULL,NULL,10,'Chemises de evento',1,NULL,12,17.00,'2026-02-23 18:44:06','2026-02-23 18:44:06'),(74,40,6,NULL,NULL,10,'hola',1,NULL,5,17.00,'2026-02-23 21:30:18','2026-02-23 21:30:18'),(76,42,6,NULL,NULL,10,'Producto de calidad para restaurant',1,NULL,12,17.00,'2026-02-23 22:16:34','2026-02-23 22:16:34'),(78,43,5,NULL,NULL,3,'QA producto update',1,NULL,NULL,31.50,'2026-02-24 02:54:17','2026-02-24 02:54:17'),(80,44,5,NULL,NULL,3,'QA producto update',1,NULL,NULL,31.50,'2026-02-24 02:58:25','2026-02-24 02:58:25'),(82,45,5,NULL,NULL,3,'QA producto update',1,NULL,NULL,31.50,'2026-02-24 03:26:39','2026-02-24 03:26:39'),(83,46,6,NULL,NULL,10,'Chemises para la Alcaldia de Paez',1,6,12,22.00,'2026-02-24 14:41:49','2026-02-24 14:41:49'),(84,46,5,NULL,NULL,1,'Camisas para empresa agro',1,8,11,33.00,'2026-02-24 14:41:49','2026-02-24 14:41:49'),(86,47,5,NULL,NULL,3,'QA producto update',1,NULL,NULL,31.50,'2026-02-24 16:31:27','2026-02-24 16:31:27'),(87,48,6,NULL,NULL,13,'notas',1,7,12,20.00,'2026-02-24 17:25:57','2026-02-24 17:25:57'),(88,41,6,NULL,NULL,1,'normal',1,NULL,12,22.00,'2026-02-24 19:32:00','2026-02-24 19:32:00'),(89,49,6,NULL,NULL,10,'Observaciones',1,2,12,47.00,'2026-02-26 20:32:49','2026-02-26 20:32:49'),(90,50,6,NULL,NULL,2,'hola',0,1,12,17.00,'2026-02-26 20:36:45','2026-02-26 20:36:45'),(91,51,6,NULL,NULL,1,NULL,0,7,14,17.00,'2026-02-26 20:39:59','2026-02-26 20:39:59'),(92,52,6,NULL,NULL,3,NULL,0,10,13,17.00,'2026-02-26 20:42:22','2026-02-26 20:42:22'),(93,53,6,NULL,NULL,10,NULL,0,7,11,17.00,'2026-02-26 20:48:10','2026-02-26 20:48:10'),(94,54,6,NULL,NULL,1,NULL,0,9,10,17.00,'2026-02-26 20:50:24','2026-02-26 20:50:24'),(95,55,8,NULL,NULL,1,NULL,0,7,12,16.00,'2026-02-26 20:51:52','2026-02-26 20:51:52'),(96,56,8,NULL,NULL,1,NULL,0,1,14,16.00,'2026-02-26 21:01:29','2026-02-26 21:01:29'),(98,57,8,NULL,NULL,10,'Buena calidad',1,6,11,19.00,'2026-03-05 17:30:23','2026-03-05 17:30:23'),(99,58,6,NULL,NULL,10,'hola',1,7,12,20.00,'2026-03-05 17:47:35','2026-03-05 17:47:35'),(100,59,6,NULL,NULL,5,'caraotas',1,2,14,22.00,'2026-03-05 20:03:32','2026-03-05 20:03:32'),(101,60,8,NULL,NULL,5,'lol',0,7,13,16.00,'2026-03-05 20:52:23','2026-03-05 20:52:23'),(102,61,8,NULL,NULL,10,'Notas',1,6,11,16.00,'2026-03-10 20:53:07','2026-03-10 20:53:07'),(103,62,8,NULL,NULL,1,NULL,0,9,13,16.00,'2026-03-15 14:51:30','2026-03-15 14:51:30'),(105,63,6,NULL,NULL,3,'QA producto update',1,NULL,NULL,31.50,'2026-03-19 21:35:32','2026-03-19 21:35:32'),(107,64,6,NULL,NULL,3,'QA producto update',1,NULL,NULL,31.50,'2026-03-20 13:17:33','2026-03-20 13:17:33'),(108,65,6,NULL,NULL,1,NULL,0,7,4,17.00,'2026-05-03 16:46:06','2026-05-03 16:46:06'),(109,66,15,'{\"id\": 8, \"codigo\": \"OXF\", \"nombre\": \"Oxford\", \"snapshot_at\": \"2026-05-07\", \"unidad_medida\": \"Metro\", \"costo_unitario\": 18}','{\"Manga\": \"Larga\", \"Cuello\": \"Con Tapa Botones\"}',1,NULL,0,1,12,33.00,'2026-05-07 18:38:20','2026-05-07 18:38:20'),(110,67,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-14\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',1,NULL,1,6,12,116.00,'2026-05-14 23:53:04','2026-05-14 23:53:04'),(111,67,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-14\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',2,NULL,1,6,11,116.00,'2026-05-14 23:53:04','2026-05-14 23:53:04'),(113,68,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-26\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',1,'null',0,6,12,16.00,'2026-05-27 01:28:49','2026-05-27 01:28:49'),(114,69,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-27\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',1,NULL,0,11,13,16.00,'2026-05-27 05:01:07','2026-05-27 05:01:07'),(115,70,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-27\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',1,NULL,0,10,13,16.00,'2026-05-28 00:56:55','2026-05-28 00:56:55'),(116,70,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-27\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',1,NULL,0,6,13,16.00,'2026-05-28 00:56:55','2026-05-28 00:56:55'),(117,71,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-29\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',1,NULL,0,6,12,16.00,'2026-05-29 14:57:19','2026-05-29 14:57:19'),(118,71,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-29\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',1,NULL,0,18,11,16.00,'2026-05-29 14:57:19','2026-05-29 14:57:19'),(119,71,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-29\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',1,NULL,0,16,14,16.00,'2026-05-29 14:57:19','2026-05-29 14:57:19'),(120,71,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-29\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',1,NULL,0,20,14,16.00,'2026-05-29 14:57:19','2026-05-29 14:57:19'),(121,72,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-29\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',3,NULL,1,6,11,316.00,'2026-05-29 23:07:27','2026-05-29 23:07:27'),(122,72,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-29\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',1,NULL,0,2,12,16.00,'2026-05-29 23:07:27','2026-05-29 23:07:27'),(123,72,13,'{\"id\": 10, \"codigo\": \"GBD\", \"nombre\": \"Gabardina / Dril\", \"snapshot_at\": \"2026-05-29\", \"unidad_medida\": \"Metro\", \"costo_unitario\": 22}','{\"Corte\": \"Rígido\"}',9,NULL,0,2,1,18.00,'2026-05-29 23:07:27','2026-05-29 23:07:27'),(124,73,13,'{\"id\": 10, \"codigo\": \"GBD\", \"nombre\": \"Gabardina / Dril\", \"snapshot_at\": \"2026-05-30\", \"unidad_medida\": \"Metro\", \"costo_unitario\": 22}','{\"Corte\": \"Rígido\"}',10,NULL,0,2,1,18.00,'2026-05-30 20:23:32','2026-05-30 20:23:32'),(125,74,12,'{\"id\":3,\"nombre\":\"Pique\",\"codigo\":\"PIQ\",\"costo_unitario\":50,\"unidad_medida\":\"Kg\",\"snapshot_at\":\"2026-06-01\"}','{\"Corte\":\"Clasico\",\"Manga\":\"Corta\",\"Cuello\":\"Cl\\u00e1sico\"}',1,NULL,1,6,12,17.00,'2026-06-01 18:50:22','2026-06-01 18:50:22');
/*!40000 ALTER TABLE `detalle_cotizacion` ENABLE KEYS */;

--
-- Table structure for table `detalle_cotizacion_bordado`
--

DROP TABLE IF EXISTS `detalle_cotizacion_bordado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_cotizacion_bordado` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `detalle_cotizacion_id` bigint unsigned NOT NULL,
  `ubicacion_bordado_id` bigint unsigned DEFAULT NULL,
  `logo_id` bigint unsigned DEFAULT NULL,
  `nombre_aplicado` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_logo_aplicado` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `es_personalizada` tinyint(1) NOT NULL DEFAULT '0',
  `cantidad` int unsigned NOT NULL DEFAULT '1',
  `precio_aplicado` decimal(10,2) NOT NULL DEFAULT '0.00',
  `orden` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_det_cot_bordado_detalle` (`detalle_cotizacion_id`),
  KEY `idx_det_cot_bordado_ubicacion` (`ubicacion_bordado_id`),
  KEY `detalle_cotizacion_bordado_logo_id_foreign` (`logo_id`),
  CONSTRAINT `detalle_cotizacion_bordado_detalle_cotizacion_id_foreign` FOREIGN KEY (`detalle_cotizacion_id`) REFERENCES `detalle_cotizacion` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_cotizacion_bordado_logo_id_foreign` FOREIGN KEY (`logo_id`) REFERENCES `logo` (`id`) ON DELETE SET NULL,
  CONSTRAINT `detalle_cotizacion_bordado_ubicacion_bordado_id_foreign` FOREIGN KEY (`ubicacion_bordado_id`) REFERENCES `bordado_ubicacion` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_cotizacion_bordado`
--

/*!40000 ALTER TABLE `detalle_cotizacion_bordado` DISABLE KEYS */;
INSERT INTO `detalle_cotizacion_bordado` VALUES (1,3,NULL,NULL,'Frontal Izquierdo','Turning Point USA',1,1,3.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(2,4,NULL,NULL,'Frontal Izquierdo','Oracle',1,1,3.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(3,5,NULL,NULL,'Frontal Izquierdo','maxcell corporation logo',1,1,3.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(4,6,NULL,NULL,'Frontal Izquierdo','nvidia logo',1,1,3.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(5,8,NULL,NULL,'Frontal Izquierdo','Palantir Logo',1,1,3.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(6,9,NULL,NULL,'Frontal Izquierdo','Logo de IA',1,1,3.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(7,10,NULL,NULL,'Frontal Derecho','Logo de Camisa',1,1,3.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(8,11,NULL,NULL,'Frontal Izquierdo','Logo UPTP',1,1,3.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(9,21,NULL,NULL,'Frontal Izquierdo','Logo UPTP',1,1,3.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(10,30,NULL,NULL,'Frontal Izquierdo','Lacoste',1,1,3.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(11,34,NULL,NULL,'Frontal Izquierdo','uptp',1,1,3.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(12,37,NULL,NULL,'izquierda','uptp',1,1,0.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(13,54,NULL,4,'Frontal izquierdo','Paica',1,1,3.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(14,55,NULL,NULL,'Frontal Izquierdo','Arzatek',1,1,3.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(15,56,NULL,NULL,'Frontal Izquierdo','TROC',1,1,3.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(16,57,NULL,4,'Pierna Izquierda','Paica',1,1,0.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(17,58,NULL,NULL,'Frontal Izquierdo','Bera',1,1,3.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(18,59,NULL,NULL,'Frontal Izquierdo','Arzatek',1,1,3.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(19,60,NULL,NULL,'Frontal Izquierdo','Arzatek',1,1,3.00,0,'2026-02-24 02:48:13','2026-02-24 02:48:13'),(20,63,NULL,NULL,'frontal izquierdo','Bera logo',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(21,64,NULL,NULL,'Frontal Izquierdo','Forum',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(22,65,NULL,NULL,'Frontal Izquierdo','china grill',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(23,66,NULL,NULL,'Frontal Izquierdo','pollos marielis',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(24,67,NULL,NULL,'Frontal Izquierdo','rock',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(25,68,NULL,NULL,'Frontal Izquierdo','rosas',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(26,69,NULL,NULL,'Frontal Izquierdo','gallo',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(27,70,NULL,NULL,'Frontal Izquierdo','fruta',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(28,71,NULL,NULL,'Frontal Izquierdo','algodon',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(29,73,NULL,18,'Frontal Izquierdo','Supermercado Garzon',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(30,74,NULL,5,'Frontal Izquierdo','Alcaldia Municipal',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(32,76,NULL,16,'Frontal Izquierdo','Panaderia La Espiga',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(36,78,2,NULL,'Frontal Derecho','Logo QA2',0,1,2.50,0,'2026-02-24 02:54:17','2026-02-24 02:54:17'),(37,78,5,NULL,'Espaldar','Logo QA2',0,2,4.50,1,'2026-02-24 02:54:17','2026-02-24 02:54:17'),(41,80,2,NULL,'Frontal Derecho','Logo QA2',0,1,2.50,0,'2026-02-24 02:58:25','2026-02-24 02:58:25'),(42,80,5,NULL,'Espaldar','Logo QA2',0,2,4.50,1,'2026-02-24 02:58:25','2026-02-24 02:58:25'),(46,82,2,NULL,'Frontal Derecho','Logo QA A2',0,1,2.50,0,'2026-02-24 03:26:39','2026-02-24 03:26:39'),(47,82,5,NULL,'Espaldar','Logo QA B2',0,2,4.50,1,'2026-02-24 03:26:39','2026-02-24 03:26:39'),(48,83,5,5,'Espaldar','Alcaldia Municipal',0,1,5.00,0,'2026-02-24 14:41:49','2026-02-24 14:41:49'),(49,84,5,14,'Espaldar','Inversiones Polar',0,1,5.00,0,'2026-02-24 14:41:49','2026-02-24 14:41:49'),(50,84,1,2,'Frontal Izquierdo','Asoportuguesa Corp',0,1,3.00,1,'2026-02-24 14:41:49','2026-02-24 14:41:49'),(54,86,2,NULL,'Frontal Derecho','Logo QA A2',0,1,2.50,0,'2026-02-24 16:31:27','2026-02-24 16:31:27'),(55,86,5,NULL,'Espaldar','Logo QA B2',0,2,4.50,1,'2026-02-24 16:31:27','2026-02-24 16:31:27'),(56,87,1,5,'Frontal Izquierdo','Alcaldia Municipal',0,1,3.00,0,'2026-02-24 17:25:57','2026-02-24 17:25:57'),(57,88,5,1,'Espaldar','Colegio Angel de la Guarda',0,1,5.00,0,'2026-02-24 19:32:00','2026-02-24 19:32:00'),(58,88,NULL,6,'Frontal Izquierdo','Banco Provincial S.A',1,1,3.00,1,'2026-02-24 19:32:00','2026-02-24 19:32:00'),(59,89,1,1,'Frontal Izquierdo','Colegio Angel de la Guarda',0,10,3.00,0,'2026-02-26 20:32:49','2026-02-26 20:32:49'),(61,98,1,2,'Frontal Izquierdo','Asoportuguesa Corp',0,1,3.00,0,'2026-03-05 17:30:23','2026-03-05 17:30:23'),(62,99,2,2,'Frontal Derecho','Asoportuguesa Corp',0,1,3.00,0,'2026-03-05 17:47:35','2026-03-05 17:47:35'),(63,100,5,2,'Espaldar','Asoportuguesa Corp',0,1,5.00,0,'2026-03-05 20:03:32','2026-03-05 20:03:32'),(64,102,1,2,'Frontal Izquierdo','Asoportuguesa Corp',0,1,2.00,0,'2026-03-10 20:53:07','2026-03-10 20:53:07'),(65,102,2,7,'Frontal Derecho','Coca-Cola Classic',0,1,2.00,1,'2026-03-10 20:53:07','2026-03-10 20:53:07'),(69,105,2,1,'Frontal Derecho','Colegio Angel de la Guarda',0,1,2.50,0,'2026-03-19 21:35:32','2026-03-19 21:35:32'),(70,105,5,2,'Espaldar','Asoportuguesa Corp',0,2,4.50,1,'2026-03-19 21:35:32','2026-03-19 21:35:32'),(74,107,2,1,'Frontal Derecho','Colegio Angel de la Guarda',0,1,2.50,0,'2026-03-20 13:17:33','2026-03-20 13:17:33'),(75,107,5,2,'Espaldar','Asoportuguesa Corp',0,2,4.50,1,'2026-03-20 13:17:33','2026-03-20 13:17:33'),(76,110,1,1,'Frontal Izquierdo','Colegio Angel de la Guarda',0,100,1.00,0,'2026-05-14 23:53:04','2026-05-14 23:53:04'),(77,111,1,1,'Frontal Izquierdo','Colegio Angel de la Guarda',0,100,1.00,0,'2026-05-14 23:53:04','2026-05-14 23:53:04'),(78,121,1,7,'Frontal Izquierdo','Coca-Cola Classic',0,100,3.00,0,'2026-05-29 23:07:27','2026-05-29 23:07:27'),(79,125,1,2,'Frontal Izquierdo','Asoportuguesa Corp',0,1,3.00,0,'2026-06-01 18:50:22','2026-06-01 18:50:22');
/*!40000 ALTER TABLE `detalle_cotizacion_bordado` ENABLE KEYS */;

--
-- Table structure for table `detalle_orden_insumo`
--

DROP TABLE IF EXISTS `detalle_orden_insumo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_orden_insumo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `orden_produccion_id` bigint unsigned NOT NULL,
  `insumo_id` bigint unsigned NOT NULL,
  `cantidad_estimada` decimal(10,2) NOT NULL,
  `cantidad_utilizada` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detalle_orden_insumos_orden_produccion_id_foreign` (`orden_produccion_id`),
  KEY `detalle_orden_insumos_insumo_id_foreign` (`insumo_id`),
  CONSTRAINT `detalle_orden_insumos_insumo_id_foreign` FOREIGN KEY (`insumo_id`) REFERENCES `insumo` (`id`),
  CONSTRAINT `detalle_orden_insumos_orden_produccion_id_foreign` FOREIGN KEY (`orden_produccion_id`) REFERENCES `orden_produccion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_orden_insumo`
--

/*!40000 ALTER TABLE `detalle_orden_insumo` DISABLE KEYS */;
INSERT INTO `detalle_orden_insumo` VALUES (3,4,1,2.00,0.00,'2025-12-18 18:00:17','2025-12-18 18:00:17'),(4,5,1,2.00,0.00,'2025-12-19 18:44:18','2025-12-19 18:44:18'),(6,6,1,2.00,0.00,'2026-01-16 18:10:26','2026-01-16 18:10:26'),(9,7,2,4.00,0.00,'2026-01-17 17:35:23','2026-01-17 17:35:23'),(10,8,5,1.00,0.00,'2026-03-06 18:38:41','2026-03-06 18:38:41'),(11,9,5,50.00,0.00,'2026-03-06 20:28:00','2026-03-06 20:28:00'),(15,10,3,8.00,0.00,'2026-03-06 21:04:25','2026-03-06 21:04:25'),(16,11,5,10.00,0.00,'2026-03-18 14:10:20','2026-03-18 14:10:20'),(18,13,5,10.00,0.00,'2026-05-28 02:19:23','2026-05-28 02:19:23'),(20,15,3,10.00,0.00,'2026-05-28 03:01:04','2026-05-28 03:01:04'),(21,15,5,10.00,0.00,'2026-05-28 03:01:04','2026-05-28 03:01:04'),(22,15,7,10.00,0.00,'2026-05-28 03:01:04','2026-05-28 03:01:04'),(23,15,8,10.00,0.00,'2026-05-28 03:01:04','2026-05-28 03:01:04'),(24,15,10,10.00,0.00,'2026-05-28 03:01:04','2026-05-28 03:01:04'),(25,15,9,10.00,0.00,'2026-05-28 03:01:04','2026-05-28 03:01:04'),(26,14,2,2.00,0.00,'2026-05-28 03:01:34','2026-05-28 03:01:34'),(31,20,5,2.00,0.00,'2026-05-29 23:28:13','2026-05-29 23:28:13'),(32,21,5,10.00,0.00,'2026-05-30 21:35:26','2026-05-30 21:35:26');
/*!40000 ALTER TABLE `detalle_orden_insumo` ENABLE KEYS */;

--
-- Table structure for table `detalle_pedido`
--

DROP TABLE IF EXISTS `detalle_pedido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_pedido` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `tela_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `atributos_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `cantidad` int NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `lleva_bordado` tinyint(1) NOT NULL DEFAULT '0',
  `color_id` bigint unsigned DEFAULT NULL,
  `talla_id` bigint unsigned DEFAULT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detalle_pedidos_pedido_id_foreign` (`pedido_id`),
  KEY `detalle_pedidos_producto_id_foreign` (`producto_id`),
  KEY `detalle_pedido_color_id_foreign` (`color_id`),
  KEY `detalle_pedido_talla_id_foreign` (`talla_id`),
  CONSTRAINT `detalle_pedido_color_id_foreign` FOREIGN KEY (`color_id`) REFERENCES `color` (`id`) ON DELETE SET NULL,
  CONSTRAINT `detalle_pedido_talla_id_foreign` FOREIGN KEY (`talla_id`) REFERENCES `talla` (`id`) ON DELETE SET NULL,
  CONSTRAINT `detalle_pedidos_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_pedidos_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`),
  CONSTRAINT `detalle_pedido_chk_1` CHECK (json_valid(`tela_snapshot`)),
  CONSTRAINT `detalle_pedido_chk_2` CHECK (json_valid(`atributos_snapshot`))
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_pedido`
--

/*!40000 ALTER TABLE `detalle_pedido` DISABLE KEYS */;
INSERT INTO `detalle_pedido` VALUES (3,3,5,NULL,NULL,1,'con tapa botones',1,1,13,25.00,'2025-12-18 17:45:32','2025-12-18 17:45:32'),(7,4,6,NULL,NULL,2,NULL,0,1,4,17.00,'2026-01-16 18:00:46','2026-01-16 18:00:46'),(8,2,5,NULL,NULL,2,'manga corta',1,1,12,25.00,'2026-01-16 18:07:36','2026-01-16 18:07:36'),(9,1,1,NULL,NULL,1,'Chemises clasicas para empresa de Inteligencia Artificial',1,NULL,13,29.90,'2026-01-16 18:07:49','2026-01-16 18:07:49'),(12,6,1,NULL,NULL,4,'null',0,1,2,29.90,'2026-01-17 22:34:12','2026-01-17 22:34:12'),(13,5,1,NULL,NULL,4,'null',1,1,10,29.90,'2026-01-17 22:34:28','2026-01-17 22:34:28'),(15,7,1,NULL,NULL,4,'null',0,1,2,29.90,'2026-01-17 23:23:25','2026-01-17 23:23:25'),(16,8,1,NULL,NULL,13,'Chemises Unicolor',1,1,12,29.90,'2026-01-19 03:37:08','2026-01-19 03:37:08'),(17,9,1,NULL,NULL,13,'Chemises Unicolor',1,1,12,29.90,'2026-01-19 03:42:07','2026-01-19 03:42:07'),(18,10,2,NULL,NULL,22,'null',0,1,3,59.90,'2026-01-19 04:36:20','2026-01-19 04:36:20'),(19,11,2,NULL,NULL,22,'null',0,NULL,3,59.90,'2026-01-19 19:13:45','2026-01-19 19:13:45'),(20,12,5,NULL,NULL,1,'null',0,NULL,10,25.00,'2026-01-19 19:24:34','2026-01-19 19:24:34'),(21,13,1,NULL,NULL,33,'null',0,NULL,2,29.90,'2026-01-19 19:32:56','2026-01-19 19:32:56'),(22,14,4,NULL,NULL,33,'null',0,NULL,2,19.00,'2026-01-19 19:51:33','2026-01-19 19:51:33'),(23,15,1,NULL,NULL,33,'scs',0,NULL,1,29.90,'2026-01-19 20:16:20','2026-01-19 20:16:20'),(24,15,2,NULL,NULL,33,'null',0,NULL,12,59.90,'2026-01-19 20:16:20','2026-01-19 20:16:20'),(25,16,1,NULL,NULL,1,'faltan medidas',1,NULL,13,29.90,'2026-01-20 21:21:33','2026-01-20 21:21:33'),(26,17,6,NULL,NULL,3,'Chemises para tienda de servicio tecnico',1,NULL,12,17.00,'2026-01-20 21:25:39','2026-01-20 21:25:39'),(27,18,1,NULL,NULL,12,'Dotacion',1,NULL,7,29.90,'2026-01-20 21:32:04','2026-01-20 21:32:04'),(28,19,6,NULL,NULL,22,'weqew',1,NULL,10,17.00,'2026-01-20 21:34:18','2026-01-20 21:34:18'),(29,20,4,NULL,NULL,10,'10',1,NULL,1,19.00,'2026-01-20 21:39:09','2026-01-20 21:39:09'),(30,21,1,NULL,NULL,1,'Bera',1,NULL,12,29.90,'2026-01-20 21:45:49','2026-01-20 21:45:49'),(31,22,1,NULL,NULL,1,'urgente',1,NULL,11,29.90,'2026-01-20 21:52:03','2026-01-20 21:52:03'),(32,23,1,NULL,NULL,2,'urgente',1,NULL,12,29.90,'2026-01-20 21:56:29','2026-01-20 21:56:29'),(33,24,1,NULL,NULL,1,'si',0,NULL,12,29.90,'2026-01-20 22:01:40','2026-01-20 22:01:40'),(35,25,1,NULL,NULL,12,'pedido urgente',0,NULL,11,29.90,'2026-01-20 22:22:03','2026-01-20 22:22:03'),(36,26,6,NULL,NULL,1,'chemise normal',1,NULL,11,17.00,'2026-02-19 20:07:49','2026-02-19 20:07:49'),(37,27,6,NULL,NULL,12,'chemise calidad',1,NULL,10,17.00,'2026-02-19 20:53:28','2026-02-19 20:53:28'),(38,28,6,NULL,NULL,12,'restaurante',1,NULL,8,17.00,'2026-02-20 00:04:19','2026-02-20 00:04:19'),(39,29,7,NULL,NULL,13,'lolita',1,NULL,10,12.00,'2026-02-20 00:28:11','2026-02-20 00:28:11'),(41,30,6,NULL,NULL,34,'banda',1,NULL,11,17.00,'2026-02-20 00:42:11','2026-02-20 00:42:11'),(42,31,6,NULL,NULL,3,'loli',1,NULL,12,17.00,'2026-02-20 00:43:39','2026-02-20 00:43:39'),(43,32,6,NULL,NULL,5,'psuv',1,NULL,12,17.00,'2026-02-20 00:52:16','2026-02-20 00:52:16'),(44,33,6,NULL,NULL,10,'mandarinas',1,NULL,12,17.00,'2026-02-20 01:01:25','2026-02-20 01:01:25'),(45,34,6,NULL,NULL,20,'nube',1,NULL,10,17.00,'2026-02-20 01:04:25','2026-02-20 01:04:25'),(46,35,6,NULL,NULL,12,'Para restaurante',0,NULL,12,17.00,'2026-02-22 20:52:23','2026-02-22 20:52:23'),(47,36,6,NULL,NULL,10,'Chemises de evento',1,NULL,12,17.00,'2026-02-23 20:00:14','2026-02-23 20:00:14'),(48,37,5,NULL,NULL,3,'QA producto update',1,NULL,NULL,31.50,'2026-02-24 02:54:17','2026-02-24 02:54:17'),(49,38,5,NULL,NULL,3,'QA producto update',1,NULL,NULL,31.50,'2026-02-24 02:58:25','2026-02-24 02:58:25'),(50,39,5,NULL,NULL,3,'QA producto update',1,NULL,NULL,31.50,'2026-02-24 03:26:39','2026-02-24 03:26:39'),(51,40,6,NULL,NULL,10,'Chemises para la Alcaldia de Paez',1,6,12,22.00,'2026-02-24 15:51:39','2026-02-24 15:51:39'),(52,40,5,NULL,NULL,1,'Camisas para empresa agro',1,8,11,33.00,'2026-02-24 15:51:39','2026-02-24 15:51:39'),(53,41,5,NULL,NULL,3,'QA producto update',1,NULL,NULL,31.50,'2026-02-24 16:31:27','2026-02-24 16:31:27'),(55,42,6,NULL,NULL,10,'Producto de calidad para restaurant',1,NULL,12,20.00,'2026-02-24 16:37:21','2026-02-24 16:37:21'),(58,43,6,NULL,NULL,13,'notas',1,7,12,20.00,'2026-02-24 17:58:15','2026-02-24 17:58:15'),(60,44,6,NULL,NULL,1,'normal',1,NULL,12,25.00,'2026-02-24 19:42:24','2026-02-24 19:42:24'),(61,45,6,NULL,NULL,10,'hola',1,NULL,5,17.00,'2026-02-24 20:05:58','2026-02-24 20:05:58'),(64,46,6,NULL,NULL,10,'Observaciones',1,2,12,47.00,'2026-02-26 20:35:51','2026-02-26 20:35:51'),(65,47,6,NULL,NULL,2,'hola',0,1,12,17.00,'2026-02-26 20:36:54','2026-02-26 20:36:54'),(66,48,6,NULL,NULL,1,NULL,0,7,14,17.00,'2026-02-26 20:40:21','2026-02-26 20:40:21'),(67,49,6,NULL,NULL,3,NULL,0,10,13,17.00,'2026-02-26 20:42:31','2026-02-26 20:42:31'),(68,50,6,NULL,NULL,10,NULL,0,7,11,17.00,'2026-02-26 20:49:46','2026-02-26 20:49:46'),(69,51,6,NULL,NULL,1,NULL,0,9,10,17.00,'2026-02-26 20:50:33','2026-02-26 20:50:33'),(70,52,8,NULL,NULL,1,NULL,0,7,12,16.00,'2026-02-26 20:54:22','2026-02-26 20:54:22'),(71,53,8,NULL,NULL,1,NULL,0,1,14,16.00,'2026-02-26 21:01:40','2026-02-26 21:01:40'),(73,54,8,NULL,NULL,10,'Buena calidad',1,6,11,19.00,'2026-03-05 17:33:27','2026-03-05 17:33:27'),(75,55,6,NULL,NULL,10,'hola',1,7,12,20.00,'2026-03-05 17:48:24','2026-03-05 17:48:24'),(77,56,6,NULL,NULL,5,'caraotas',1,2,14,22.00,'2026-03-05 20:14:11','2026-03-05 20:14:11'),(79,57,8,NULL,NULL,10,'Notas',1,6,11,20.00,'2026-03-10 20:57:35','2026-03-10 20:57:35'),(82,59,6,NULL,NULL,3,'QA producto update',1,NULL,NULL,31.50,'2026-03-19 21:35:32','2026-03-19 21:35:32'),(83,60,6,NULL,NULL,3,'QA producto update',1,NULL,NULL,31.50,'2026-03-20 13:17:33','2026-03-20 13:17:33'),(87,58,8,NULL,NULL,1,'null',0,9,13,16.00,'2026-03-23 03:12:13','2026-03-23 03:12:13'),(89,61,15,'{\"id\": 8, \"codigo\": \"OXF\", \"nombre\": \"Oxford\", \"snapshot_at\": \"2026-05-07\", \"unidad_medida\": \"Metro\", \"costo_unitario\": 18}','{\"Manga\": \"Larga\", \"Cuello\": \"Con Tapa Botones\"}',1,NULL,0,1,12,33.00,'2026-05-07 18:39:36','2026-05-07 18:39:36'),(90,62,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-14\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',1,NULL,1,6,12,116.00,'2026-05-15 00:25:57','2026-05-15 00:25:57'),(91,62,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-14\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',2,NULL,1,6,11,116.00,'2026-05-15 00:25:57','2026-05-15 00:25:57'),(92,63,6,NULL,NULL,1,NULL,0,7,4,17.00,'2026-05-26 23:51:07','2026-05-26 23:51:07'),(93,64,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-26\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',1,'null',0,6,12,16.00,'2026-05-27 01:29:00','2026-05-27 01:29:00'),(94,65,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-27\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',1,NULL,0,11,13,16.00,'2026-05-28 01:06:26','2026-05-28 01:06:26'),(95,66,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-29\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',1,NULL,0,6,12,16.00,'2026-05-29 14:58:08','2026-05-29 14:58:08'),(96,66,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-29\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',1,NULL,0,18,11,16.00,'2026-05-29 14:58:08','2026-05-29 14:58:08'),(97,66,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-29\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',1,NULL,0,16,14,16.00,'2026-05-29 14:58:08','2026-05-29 14:58:08'),(98,66,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-29\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',1,NULL,0,20,14,16.00,'2026-05-29 14:58:08','2026-05-29 14:58:08'),(99,67,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-29\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',3,NULL,1,6,11,316.00,'2026-05-29 23:14:12','2026-05-29 23:14:12'),(100,67,8,'{\"id\": 5, \"codigo\": \"AJR\", \"nombre\": \"Jersey\", \"snapshot_at\": \"2026-05-29\", \"unidad_medida\": \"Kg\", \"costo_unitario\": 3}','{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}',1,NULL,0,2,12,16.00,'2026-05-29 23:14:13','2026-05-29 23:14:13'),(101,67,13,'{\"id\": 10, \"codigo\": \"GBD\", \"nombre\": \"Gabardina / Dril\", \"snapshot_at\": \"2026-05-29\", \"unidad_medida\": \"Metro\", \"costo_unitario\": 22}','{\"Corte\": \"Rígido\"}',9,NULL,0,2,1,18.00,'2026-05-29 23:14:13','2026-05-29 23:14:13'),(102,68,12,'{\"id\":3,\"nombre\":\"Pique\",\"codigo\":\"PIQ\",\"costo_unitario\":50,\"unidad_medida\":\"Kg\",\"snapshot_at\":\"2026-06-01\"}','{\"Corte\":\"Clasico\",\"Manga\":\"Corta\",\"Cuello\":\"Cl\\u00e1sico\"}',1,NULL,1,6,12,17.00,'2026-06-01 18:52:08','2026-06-01 18:52:08');
/*!40000 ALTER TABLE `detalle_pedido` ENABLE KEYS */;

--
-- Table structure for table `detalle_pedido_bordado`
--

DROP TABLE IF EXISTS `detalle_pedido_bordado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_pedido_bordado` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `detalle_pedido_id` bigint unsigned NOT NULL,
  `ubicacion_bordado_id` bigint unsigned DEFAULT NULL,
  `logo_id` bigint unsigned DEFAULT NULL,
  `nombre_aplicado` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_logo_aplicado` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `es_personalizada` tinyint(1) NOT NULL DEFAULT '0',
  `cantidad` int unsigned NOT NULL DEFAULT '1',
  `precio_aplicado` decimal(10,2) NOT NULL DEFAULT '0.00',
  `orden` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_det_ped_bordado_detalle` (`detalle_pedido_id`),
  KEY `idx_det_ped_bordado_ubicacion` (`ubicacion_bordado_id`),
  KEY `detalle_pedido_bordado_logo_id_foreign` (`logo_id`),
  CONSTRAINT `detalle_pedido_bordado_detalle_pedido_id_foreign` FOREIGN KEY (`detalle_pedido_id`) REFERENCES `detalle_pedido` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_pedido_bordado_logo_id_foreign` FOREIGN KEY (`logo_id`) REFERENCES `logo` (`id`) ON DELETE SET NULL,
  CONSTRAINT `detalle_pedido_bordado_ubicacion_bordado_id_foreign` FOREIGN KEY (`ubicacion_bordado_id`) REFERENCES `bordado_ubicacion` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_pedido_bordado`
--

/*!40000 ALTER TABLE `detalle_pedido_bordado` DISABLE KEYS */;
INSERT INTO `detalle_pedido_bordado` VALUES (1,3,NULL,3,'Frontal Izquierdo','Los Caminos',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(2,8,NULL,NULL,'Frontal Izquierdo','Palantir Logo',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(3,9,NULL,NULL,'Frontal Izquierdo','Palantir Logo',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(4,13,NULL,NULL,'Frontal Izquierdo','Logo UPTP',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(5,16,NULL,NULL,'Frontal Izquierdo','Lacoste',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(6,17,NULL,NULL,'Frontal Izquierdo','Lacoste',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(7,25,NULL,4,'Frontal izquierdo','Paica',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(8,26,NULL,NULL,'Frontal Izquierdo','Arzatek',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(9,27,NULL,NULL,'Frontal Izquierdo','TROC',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(10,28,NULL,NULL,'izquierda','uptp',1,1,0.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(11,29,NULL,4,'Pierna Izquierda','Paica',1,1,0.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(12,30,NULL,NULL,'Frontal Izquierdo','Bera',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(13,31,NULL,NULL,'Frontal Izquierdo','Arzatek',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(14,32,NULL,NULL,'Frontal Izquierdo','Arzatek',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(15,36,NULL,NULL,'frontal izquierdo','Bera logo',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(16,37,NULL,NULL,'Frontal Izquierdo','Forum',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(17,38,NULL,NULL,'Frontal Izquierdo','china grill',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(18,39,NULL,NULL,'Frontal Izquierdo','pollos marielis',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(19,42,NULL,NULL,'Frontal Izquierdo','rosas',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(20,43,NULL,NULL,'Frontal Izquierdo','gallo',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(21,44,NULL,NULL,'Frontal Izquierdo','fruta',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(22,45,NULL,NULL,'Frontal Izquierdo','algodon',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(23,47,NULL,18,'Frontal Izquierdo','Supermercado Garzon',1,1,3.00,0,'2026-02-24 02:48:14','2026-02-24 02:48:14'),(24,48,2,NULL,'Frontal Derecho','Logo QA2',0,1,2.50,0,'2026-02-24 02:54:17','2026-02-24 02:54:17'),(25,48,5,NULL,'Espaldar','Logo QA2',0,2,4.50,1,'2026-02-24 02:54:17','2026-02-24 02:54:17'),(26,49,2,NULL,'Frontal Derecho','Logo QA2',0,1,2.50,0,'2026-02-24 02:58:25','2026-02-24 02:58:25'),(27,49,5,NULL,'Espaldar','Logo QA2',0,2,4.50,1,'2026-02-24 02:58:25','2026-02-24 02:58:25'),(28,50,2,NULL,'Frontal Derecho','Logo QA A2',0,1,2.50,0,'2026-02-24 03:26:39','2026-02-24 03:26:39'),(29,50,5,NULL,'Espaldar','Logo QA B2',0,2,4.50,1,'2026-02-24 03:26:39','2026-02-24 03:26:39'),(30,51,5,5,'Espaldar','Alcaldia Municipal',0,1,5.00,0,'2026-02-24 15:51:39','2026-02-24 15:51:39'),(31,52,5,14,'Espaldar','Inversiones Polar',0,1,5.00,0,'2026-02-24 15:51:39','2026-02-24 15:51:39'),(32,52,1,2,'Frontal Izquierdo','Asoportuguesa Corp',0,1,3.00,1,'2026-02-24 15:51:39','2026-02-24 15:51:39'),(33,53,2,NULL,'Frontal Derecho','Logo QA A2',0,1,2.50,0,'2026-02-24 16:31:27','2026-02-24 16:31:27'),(34,53,5,NULL,'Espaldar','Logo QA B2',0,2,4.50,1,'2026-02-24 16:31:27','2026-02-24 16:31:27'),(36,55,NULL,16,'Frontal Izquierdo','Panaderia La Espiga',1,1,3.00,0,'2026-02-24 16:37:21','2026-02-24 16:37:21'),(39,58,1,5,'Frontal Izquierdo','Alcaldia Municipal',0,1,3.00,0,'2026-02-24 17:58:15','2026-02-24 17:58:15'),(42,60,5,1,'Espaldar','Colegio Angel de la Guarda',0,1,5.00,0,'2026-02-24 19:42:24','2026-02-24 19:42:24'),(43,60,NULL,6,'Frontal Izquierdo','Banco Provincial S.A',1,1,3.00,1,'2026-02-24 19:42:24','2026-02-24 19:42:24'),(44,61,NULL,5,'Frontal Izquierdo','Alcaldia Municipal',1,1,3.00,0,'2026-02-24 20:05:58','2026-02-24 20:05:58'),(47,64,1,1,'Frontal Izquierdo','Colegio Angel de la Guarda',0,10,3.00,0,'2026-02-26 20:35:51','2026-02-26 20:35:51'),(49,73,1,2,'Frontal Izquierdo','Asoportuguesa Corp',0,1,3.00,0,'2026-03-05 17:33:27','2026-03-05 17:33:27'),(51,75,2,2,'Frontal Derecho','Asoportuguesa Corp',0,1,3.00,0,'2026-03-05 17:48:24','2026-03-05 17:48:24'),(53,77,5,2,'Espaldar','Asoportuguesa Corp',0,1,5.00,0,'2026-03-05 20:14:11','2026-03-05 20:14:11'),(56,79,1,2,'Frontal Izquierdo','Asoportuguesa Corp',0,1,2.00,0,'2026-03-10 20:57:35','2026-03-10 20:57:35'),(57,79,2,7,'Frontal Derecho','Coca-Cola Classic',0,1,2.00,1,'2026-03-10 20:57:35','2026-03-10 20:57:35'),(58,82,2,1,'Frontal Derecho','Colegio Angel de la Guarda',0,1,2.50,0,'2026-03-19 21:35:32','2026-03-19 21:35:32'),(59,82,5,2,'Espaldar','Asoportuguesa Corp',0,2,4.50,1,'2026-03-19 21:35:32','2026-03-19 21:35:32'),(60,83,2,1,'Frontal Derecho','Colegio Angel de la Guarda',0,1,2.50,0,'2026-03-20 13:17:33','2026-03-20 13:17:33'),(61,83,5,2,'Espaldar','Asoportuguesa Corp',0,2,4.50,1,'2026-03-20 13:17:33','2026-03-20 13:17:33'),(62,90,1,1,'Frontal Izquierdo','Colegio Angel de la Guarda',0,100,1.00,0,'2026-05-15 00:25:57','2026-05-15 00:25:57'),(63,91,1,1,'Frontal Izquierdo','Colegio Angel de la Guarda',0,100,1.00,0,'2026-05-15 00:25:57','2026-05-15 00:25:57'),(64,99,1,7,'Frontal Izquierdo','Coca-Cola Classic',0,100,3.00,0,'2026-05-29 23:14:12','2026-05-29 23:14:12'),(65,102,1,2,'Frontal Izquierdo','Asoportuguesa Corp',0,1,3.00,0,'2026-06-01 18:52:08','2026-06-01 18:52:08');
/*!40000 ALTER TABLE `detalle_pedido_bordado` ENABLE KEYS */;

--
-- Table structure for table `detalle_pedido_insumo`
--

DROP TABLE IF EXISTS `detalle_pedido_insumo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_pedido_insumo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `detalle_pedido_id` bigint unsigned NOT NULL,
  `insumo_id` bigint unsigned NOT NULL,
  `cantidad_estimada` decimal(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `detalle_pedido_insumo_detalle_pedido_id_insumo_id_unique` (`detalle_pedido_id`,`insumo_id`),
  KEY `detalle_pedido_insumo_insumo_id_foreign` (`insumo_id`),
  CONSTRAINT `detalle_pedido_insumo_detalle_pedido_id_foreign` FOREIGN KEY (`detalle_pedido_id`) REFERENCES `detalle_pedido` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_pedido_insumo_insumo_id_foreign` FOREIGN KEY (`insumo_id`) REFERENCES `insumo` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_pedido_insumo`
--

/*!40000 ALTER TABLE `detalle_pedido_insumo` DISABLE KEYS */;
INSERT INTO `detalle_pedido_insumo` VALUES (3,3,1,2.00,NULL,NULL),(7,7,2,4.00,NULL,NULL),(8,7,1,4.00,NULL,NULL),(9,8,1,4.00,NULL,NULL),(10,9,1,5.00,NULL,NULL),(13,12,2,4.00,NULL,NULL),(14,13,2,1.00,NULL,NULL),(16,15,2,4.00,NULL,NULL),(17,16,2,2.00,NULL,NULL),(18,17,2,2.00,NULL,NULL),(19,18,2,2.00,NULL,NULL);
/*!40000 ALTER TABLE `detalle_pedido_insumo` ENABLE KEYS */;

--
-- Table structure for table `direccion`
--

DROP TABLE IF EXISTS `direccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `direccion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `persona_id` bigint unsigned NOT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ciudad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Estado/Territorio geográfico',
  `tipo` enum('casa','trabajo','envio') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'casa',
  `es_principal` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `direccion_persona_id_index` (`persona_id`),
  CONSTRAINT `direccion_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `direccion`
--

/*!40000 ALTER TABLE `direccion` DISABLE KEYS */;
INSERT INTO `direccion` VALUES (1,2,'La Goajira','acarigua',NULL,'casa',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(2,3,'Washington DC','Washington',NULL,'casa',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(3,4,'Urbanización Fundación MendozaAvenida 7, Calle Principal',NULL,NULL,'casa',1,'2025-12-10 18:42:20','2026-05-29 20:05:28',NULL),(4,6,'Wall Street','New York NY',NULL,'casa',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(5,7,'Sillycon Valley','California',NULL,'casa',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(6,8,'Dayton','Ohio',NULL,'casa',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(7,9,'Headington Hill Hall, Reino Unido','Oxford',NULL,'casa',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(8,10,'San Tomas Expressway','Santa Clara, CA',NULL,'casa',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(9,11,'Denver','Colorado',NULL,'casa',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(10,12,'Crescent Park, 11 Avenue Palo Alto','Araure','Portuguesa','casa',1,'2025-12-10 20:29:40','2026-01-18 23:37:09',NULL),(11,14,'Avenue 10','Dallas',NULL,'casa',1,'2025-12-10 21:09:58','2025-12-10 21:09:58',NULL),(12,13,'villas','Páez',NULL,'casa',1,'2025-12-10 21:17:57','2026-05-29 19:52:22',NULL),(13,15,'Urb prados del sol','Araure',NULL,'casa',1,'2026-01-17 16:51:52','2026-01-17 16:51:52',NULL),(14,16,'prados del sol','araure',NULL,'casa',1,'2026-01-17 17:11:09','2026-01-17 17:11:09',NULL),(15,18,'Urb prados del sol','Araure',NULL,'casa',1,'2026-01-17 22:31:33','2026-01-17 22:31:33',NULL),(17,20,'Urb. Los Cortijos','Páez','Portuguesa','casa',1,'2026-01-18 03:56:57','2026-01-18 03:56:57',NULL),(19,22,'Avenida los Pescadores calle 5','Araure','Portuguesa','trabajo',1,'2026-01-18 20:23:50','2026-01-18 20:23:50',NULL),(20,23,'Urb. Los Pinos, Calle 3, Casa 15, Acarigua','Páez','Portuguesa','trabajo',1,'2026-01-18 20:37:01','2026-01-18 20:37:01',NULL),(21,24,'Av. Principal, Edif. Sol, Apto 4B, Araure','Araure','Portuguesa','trabajo',1,'2026-01-18 20:38:40','2026-01-18 20:38:40',NULL),(22,25,'carlossilva@gmail.com','Guanare','Portuguesa','trabajo',1,'2026-01-18 20:41:05','2026-01-18 20:41:05',NULL),(23,26,'Urb. El Recreo, Calle 10, Casa 8, Barinas','Barinas','Barinas','trabajo',1,'2026-01-18 20:43:53','2026-01-18 20:43:53',NULL),(24,27,'Urb. Los Samanes, Calle 5, Casa 12, Sector Centro','Ospino','Portuguesa','trabajo',1,'2026-01-18 20:57:49','2026-01-18 20:57:49',NULL),(25,28,'Calle 8, Casa 22, Sector La Barinesa, Acarigua','Páez','Portuguesa','trabajo',1,'2026-01-18 21:52:28','2026-01-18 21:52:28',NULL),(26,29,'Urb prados del sol','Araure','Portuguesa','casa',1,'2026-01-19 00:25:34','2026-01-19 00:25:34',NULL),(27,30,'agua clar','Araure','Portuguesa','casa',1,'2026-01-19 03:56:04','2026-01-19 03:56:04',NULL),(28,31,'agua','Araure','Portuguesa','casa',1,'2026-01-19 04:01:50','2026-01-19 04:01:50',NULL),(29,32,'Urb prados del sol','Araure','Portuguesa','casa',1,'2026-01-19 04:05:33','2026-01-19 04:05:33',NULL),(30,33,'Urb prados del sol','Araure','Portuguesa','casa',1,'2026-01-19 04:17:44','2026-01-19 04:17:44',NULL),(31,34,'Urb prados del sol','Esteller','Portuguesa','casa',1,'2026-01-19 04:26:34','2026-01-19 04:26:34',NULL),(32,35,'Urb prados del sol','Araure','Portuguesa','casa',1,'2026-01-19 16:49:16','2026-01-19 16:49:16',NULL),(33,36,'Urb. villas del pilar','Araure','Portuguesa','casa',1,'2026-01-20 01:29:08','2026-01-20 01:29:08',NULL),(34,37,'Fundacion Mendoza','Páez','Portuguesa','casa',1,'2026-01-20 21:23:34','2026-01-20 21:23:34',NULL),(35,38,'Urbanizacion Bosques de Camoruco, Av 7 Calle Principal','Páez','Portuguesa','casa',1,'2026-02-22 17:56:13','2026-02-22 17:56:13',NULL),(36,39,'Calle 15 con Av. Bolívar, Casa #12','Acarigua','Portuguesa','casa',1,'2026-02-26 19:10:25','2026-02-26 19:10:25',NULL),(37,40,'Urb. Las Acacias, Calle 3, Casa #8','Araure','Portuguesa','casa',1,'2026-02-26 19:10:25','2026-02-26 19:10:25',NULL),(38,42,'Zona Industrial II, Galpón 5','Valencia','Carabobo','casa',1,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL),(39,43,'Av. Principal de Páez, Edificio Don Luis, Piso 2','Acarigua','Portuguesa','casa',1,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL),(40,44,'Calle 30 entre Av. 27 y 28, Local 4','San Rafael de Onoto','Portuguesa','casa',1,'2026-02-26 19:11:43','2026-02-26 20:21:46',NULL),(41,45,'Urb. Villa del Pilar, Calle 10, Casa 25','Araure','Portuguesa','casa',1,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL),(42,46,'Barrio Sucre, Calle Principal, Casa S/N','Barinas','Barinas','casa',1,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL),(43,47,'Centro Empresarial La Paz, Oficina 302','Urdaneta','Miranda','casa',1,'2026-02-26 19:11:43','2026-02-26 20:23:03',NULL),(44,48,'Urb. Prados del Sol, Calle 5, Casa 14-B','Araure','Portuguesa','casa',1,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL),(45,49,'Sector Los Cortijos, Vereda 3, Casa 7','Páez','Portuguesa','casa',1,'2026-02-26 19:11:44','2026-02-26 19:11:44',NULL),(46,50,'Avenida, Calle, Casa','Páez','Portuguesa','casa',1,'2026-02-26 19:46:04','2026-02-26 19:46:04',NULL),(47,51,'Diagonal Urbanizacion Altos de la Galera','Páez','Portuguesa','casa',1,'2026-03-05 17:17:09','2026-03-05 17:17:09',NULL),(48,53,'Av. Industrial 123, Venezuela',NULL,NULL,'trabajo',1,'2026-03-19 18:21:05','2026-03-19 18:21:05',NULL),(49,54,'Torre Jalisco, Las Mercedes',NULL,NULL,'trabajo',1,'2026-03-19 18:21:05','2026-03-19 18:21:05',NULL),(50,55,'Zona Industrial II, Galpón 15, Acarigua',NULL,NULL,'trabajo',1,'2026-03-19 18:21:06','2026-03-19 18:21:06',NULL),(51,56,'Av. Bolívar, Local 23, Araure',NULL,NULL,'trabajo',1,'2026-03-19 18:21:06','2026-03-19 18:21:06',NULL),(52,57,'Calle 5, CC Los Llanos, Valencia',NULL,NULL,'trabajo',1,'2026-03-19 18:21:06','2026-03-19 18:21:06',NULL),(53,58,'Av. Libertador, Edif. Comercial, Piso 2, Barquisimeto',NULL,NULL,'trabajo',1,'2026-03-19 18:21:06','2026-03-19 18:21:06',NULL),(54,59,'Calle Principal, Galpón 8, Mérida',NULL,NULL,'trabajo',1,'2026-03-19 18:21:06','2026-03-19 18:21:06',NULL),(55,60,'La goajira','Guanare','Portuguesa','casa',1,'2026-04-14 21:26:32','2026-04-14 21:26:32',NULL),(56,61,'Roca del llano','Páez','Portuguesa','casa',1,'2026-04-29 00:22:53','2026-04-29 00:22:53',NULL),(57,62,'Prados del Sol','Páez','Portuguesa','trabajo',1,'2026-04-29 00:25:48','2026-04-29 00:25:48',NULL),(60,65,'Urb San Jose','Páez',NULL,'casa',1,'2026-04-30 20:25:42','2026-04-30 20:25:42',NULL),(61,66,'Zona Sur','Páez',NULL,'casa',1,'2026-05-30 19:47:16','2026-05-30 19:47:16',NULL),(62,67,'Urb San Jose','Araure',NULL,'casa',1,'2026-05-30 19:50:52','2026-05-30 19:50:52',NULL),(63,68,'Urb  San Jose II','Araure',NULL,'casa',1,'2026-05-30 19:55:30','2026-05-30 19:55:30',NULL),(64,69,'Urb San Jose','Páez',NULL,'casa',1,'2026-05-30 20:00:52','2026-05-30 20:00:52',NULL);
/*!40000 ALTER TABLE `direccion` ENABLE KEYS */;

--
-- Table structure for table `empleado`
--

DROP TABLE IF EXISTS `empleado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleado` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `persona_id` bigint unsigned NOT NULL,
  `codigo_empleado` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `cargo_id` bigint unsigned DEFAULT NULL,
  `departamento_id` bigint unsigned DEFAULT NULL,
  `salario` decimal(10,2) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `empleado_persona_id_unique` (`persona_id`),
  UNIQUE KEY `empleado_codigo_empleado_unique` (`codigo_empleado`),
  KEY `empleado_departamento_id_foreign` (`departamento_id`),
  KEY `empleado_cargo_id_foreign` (`cargo_id`),
  CONSTRAINT `empleado_cargo_id_foreign` FOREIGN KEY (`cargo_id`) REFERENCES `cargo` (`id`),
  CONSTRAINT `empleado_departamento_id_foreign` FOREIGN KEY (`departamento_id`) REFERENCES `departamento` (`id`),
  CONSTRAINT `empleado_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empleado`
--

/*!40000 ALTER TABLE `empleado` DISABLE KEYS */;
INSERT INTO `empleado` VALUES (1,2,'EMP-001','2025-12-04',1,1,NULL,1,'2025-12-04 19:45:19','2026-01-17 05:25:17','2026-01-17 05:25:17'),(2,3,'EMP-002','2025-04-07',2,2,897000.00,1,'2025-12-04 20:19:19','2026-01-16 21:58:15','2026-01-16 21:58:15'),(3,4,'EMP-003','2025-09-29',9,1,NULL,1,'2025-12-05 20:14:04','2026-05-29 20:28:22',NULL),(4,13,'EMP-004','2025-05-08',9,1,NULL,1,'2025-12-10 20:57:36','2026-05-29 20:05:19',NULL),(5,14,'EMP-005','2025-12-10',5,1,NULL,1,'2025-12-10 21:09:58','2026-01-16 21:58:10','2026-01-16 21:58:10'),(8,65,'EMP-006','2026-04-29',3,1,NULL,1,'2026-04-30 20:25:42','2026-05-29 20:37:26','2026-05-29 20:37:26'),(9,66,'EMP-007','2021-12-10',10,1,NULL,1,'2026-05-30 19:47:16','2026-05-30 19:47:16',NULL),(10,67,'EMP-008','2020-01-15',9,1,NULL,1,'2026-05-30 19:50:52','2026-05-30 19:50:52',NULL),(11,68,'EMP-009','2024-03-14',9,1,NULL,1,'2026-05-30 19:55:30','2026-05-30 19:55:30',NULL),(12,69,'EMP-010','2018-01-15',9,1,NULL,1,'2026-05-30 20:00:52','2026-05-30 20:00:52',NULL);
/*!40000 ALTER TABLE `empleado` ENABLE KEYS */;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;

--
-- Table structure for table `insumo`
--

DROP TABLE IF EXISTS `insumo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `insumo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` enum('Tela','Hilo','Boton','Cierre','Etiqueta','Otro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `unidad_medida` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_inventoriable` tinyint(1) NOT NULL DEFAULT '1',
  `costo_unitario` decimal(10,2) NOT NULL,
  `stock_actual` decimal(10,2) NOT NULL,
  `stock_minimo` decimal(10,2) NOT NULL,
  `stock_maximo` decimal(10,2) NOT NULL DEFAULT '0.00',
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `insumo_codigo_unique` (`codigo`),
  KEY `idx_insumo_stock` (`stock_actual`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `insumo`
--

/*!40000 ALTER TABLE `insumo` DISABLE KEYS */;
INSERT INTO `insumo` VALUES (1,'Tela Algodón Pima',NULL,'Tela','Metro',1,15.50,985.00,200.00,0.00,1,'2025-12-04 18:58:28','2026-01-17 17:35:36','2026-01-17 17:35:36'),(2,'Botón Nacar 18mm',NULL,'Boton','Unidad',1,0.50,4981.00,1000.00,0.00,1,'2025-12-04 18:58:28','2026-01-19 04:36:20',NULL),(3,'Pique','PIQ','Tela','Kg',1,50.00,0.00,5.00,0.00,1,'2025-12-11 00:39:02','2026-05-07 17:15:29',NULL),(4,'microdurazno',NULL,'Hilo','43',1,29.00,40.00,39.00,0.00,1,'2026-01-14 23:55:14','2026-01-17 05:19:57','2026-01-17 05:19:57'),(5,'Jersey','AJR','Tela','Kg',1,3.00,120.00,10.00,0.00,1,'2026-01-20 20:36:23','2026-06-02 20:48:34',NULL),(6,'goma',NULL,'Otro','Metro',1,1.00,10.00,5.00,0.00,1,'2026-02-16 22:19:21','2026-02-17 05:55:40','2026-02-17 05:55:40'),(7,'Dacron','DAC','Tela','Metro',1,12.00,0.00,0.00,0.00,1,'2026-05-07 17:15:29','2026-05-07 17:15:29',NULL),(8,'Oxford','OXF','Tela','Metro',1,18.00,10.00,0.00,0.00,1,'2026-05-07 17:15:29','2026-06-02 20:48:34',NULL),(9,'Microfibra','MFB','Tela','Metro',1,14.00,0.00,0.00,0.00,1,'2026-05-07 17:15:29','2026-05-07 17:15:29',NULL),(10,'Gabardina / Dril','GBD','Tela','Metro',1,22.00,0.00,0.00,0.00,1,'2026-05-07 17:15:29','2026-05-07 17:15:29',NULL);
/*!40000 ALTER TABLE `insumo` ENABLE KEYS */;

--
-- Table structure for table `logo`
--

DROP TABLE IF EXISTS `logo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre limpio del logo (sin extensión)',
  `original_filename` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre completo del archivo .emb en MEGA',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `logos_name_unique` (`name`),
  UNIQUE KEY `logos_original_filename_unique` (`original_filename`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logo`
--

/*!40000 ALTER TABLE `logo` DISABLE KEYS */;
INSERT INTO `logo` VALUES (1,'Colegio Angel de la Guarda','Colegio Angel de la Guarda.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(2,'Asoportuguesa Corp','Asoportuguesa Corp.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(3,'Los Caminos Hacienda','Los Caminos Hacienda.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(4,'PAICA Alimentos','PAICA Alimentos.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(5,'Alcaldia Municipal','Alcaldia Municipal.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(6,'Banco Provincial S.A','Banco Provincial S.A.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(7,'Coca-Cola Classic','Coca-Cola Classic.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(8,'Distribuidora El Faro','Distribuidora El Faro.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(9,'Escuela de Futbol','Escuela de Futbol.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(10,'Farmacia Express','Farmacia Express.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(11,'Gimnasio Iron Body','Gimnasio Iron Body.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(12,'Hotel Kristoff','Hotel Kristoff.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(13,'Iglesia San Juan','Iglesia San Juan.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(14,'Inversiones Polar','Inversiones Polar.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(15,'Logistica Global','Logistica Global.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(16,'Panaderia La Espiga','Panaderia La Espiga.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(17,'Restaurante El Meson','Restaurante El Meson.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(18,'Supermercado Garzon','Supermercado Garzon.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(19,'Transporte Rapido','Transporte Rapido.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL),(20,'Universidad Central','Universidad Central.emb','2026-02-22 18:55:09','2026-02-22 18:55:09',NULL);
/*!40000 ALTER TABLE `logo` ENABLE KEYS */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2025_03_01_000000_create_sistema_produccion_tables',1),(6,'2025_06_14_091624_create_pedidos_table',1),(7,'2025_06_14_091726_create_detalle_pedidos_table',1),(8,'2025_06_14_094205_add_fecha_entrega_estimada_to_pedidos_table',1),(9,'2025_06_14_100214_add_rif_to_pedidos_table',1),(10,'2025_06_14_102229_remove_unique_rif_from_pedidos_table',1),(11,'2025_06_14_103232_rename_rif_to_ci_rif_in_pedidos_table',1),(12,'2025_06_14_112859_add_description_and_logo_fields_to_detalle_pedidos_table',1),(13,'2025_06_14_114729_add_talla_and_color_to_detalle_pedidos_table',1),(14,'2025_06_14_115649_update_talla_enum_in_detalle_pedidos_table',1),(15,'2025_06_14_123551_force_update_talla_enum_in_detalle_pedidos_table',1),(16,'2025_06_14_210039_create_detalle_pedido_insumo_table',1),(17,'2025_06_15_191252_create_bancos_table',1),(18,'2025_06_15_191339_add_payment_fields_to_pedidos_table',1),(19,'2025_06_19_143226_create_clientes_table',1),(20,'2025_06_19_143359_add_cliente_id_to_pedidos_table',1),(21,'2025_06_20_000001_create_cotizaciones_table',1),(22,'2025_06_20_000002_create_detalle_cotizaciones_table',1),(23,'2025_06_21_112333_add_deleted_at_to_clientes_table',1),(24,'2025_06_26_221106_remove_prioridad_column_from_cotizaciones_table',1),(25,'2025_12_04_134221_update_user_role_enum',1),(26,'2025_12_04_150028_rename_all_tables_to_singular_final',2),(27,'2025_12_04_153326_add_missing_columns_to_cliente_table',3),(28,'2025_12_04_154406_create_persona_table',4),(29,'2025_12_04_154408_create_empleado_table',4),(30,'2025_12_04_154409_add_persona_id_to_user_table',4),(31,'2025_12_04_154448_migrate_users_to_persona',4),(32,'2025_12_04_154449_create_empleados_from_supervisores',4),(33,'2025_12_05_165423_rename_ruc_to_rif_in_proveedor_table',5),(34,'2025_12_08_151400_add_cliente_id_to_cotizacion',6),(36,'2025_12_08_153400_normalize_cotizacion_remove_redundant_cliente_fields',7),(37,'2025_12_08_154900_normalize_cliente_with_persona',8),(38,'2025_12_09_170406_remove_payment_columns_from_cotizacion_table',9),(39,'2025_12_10_143835_create_telefono_table',10),(40,'2025_12_10_144011_create_direccion_table',10),(41,'2025_12_10_144137_migrate_telefono_direccion_data_from_persona',10),(42,'2025_12_10_164505_remove_telefono_direccion_ciudad_from_persona_table',11),(43,'2025_12_10_173653_add_cliente_id_to_pedido_table',12),(44,'2025_12_10_194225_remove_legacy_cliente_columns_from_pedido_table',13),(45,'2025_12_15_150500_make_color_nullable_in_producto_table',14),(46,'2025_12_15_155200_make_material_talla_nullable_in_producto_table',15),(47,'2025_12_15_160400_drop_material_talla_from_producto_table',16),(48,'2025_12_15_164400_create_tasa_cambio_table',17),(49,'2025_12_16_134300_create_tipo_producto_table',18),(50,'2025_12_16_134400_add_tipo_and_codigo_to_producto_table',18),(51,'2025_12_18_134800_add_pedido_id_and_logo_to_orden_produccion',19),(52,'2025_12_19_152000_add_cotizacion_id_to_pedido',20),(53,'2026_01_17_225337_rename_estado_to_estatus_and_add_estado_territorial',21),(54,'2026_01_18_160000_add_tipo_proveedor_and_persona_id_to_proveedor',22),(55,'2026_01_18_162000_make_proveedor_fields_nullable',23),(56,'2026_01_19_145036_add_separate_bank_fields_to_pedidos_table',24),(57,'2026_02_19_200000_add_unique_index_to_pedido_cotizacion_id',25),(58,'2026_02_22_125718_add_notas_to_cotizacion_table',26),(59,'2026_02_22_143623_create_logos_table',27),(60,'2026_02_23_140000_create_colores_table',28),(61,'2026_02_23_170000_create_tallas_table',29),(62,'2026_02_23_201000_create_bordado_ubicaciones_table',30),(63,'2026_02_23_201100_create_detalle_cotizacion_bordados_table',30),(64,'2026_02_23_201200_create_detalle_pedido_bordados_table',30),(65,'2026_02_23_201300_migrate_legacy_bordado_fields_and_drop_columns',30),(66,'2026_02_23_230000_add_nombre_logo_aplicado_to_detalle_bordados_tables',31),(67,'2026_03_06_155710_rename_operario_id_to_empleado_id_in_produccion_diaria',32),(68,'2026_03_19_000001_cr03_enum_estado_prioridad_pedido_cotizacion',33),(69,'2026_03_19_000002_cr01_color_talla_fk_detalle_cotizacion_pedido',34),(70,'2026_03_19_000003_me01_indices_faltantes',35),(71,'2026_03_19_000004_me04_fecha_produccion_produccion_diaria',36),(72,'2026_03_19_000005_me06_fecha_fin_real_orden_produccion',37),(73,'2026_03_19_000006_batch_me05_me03_ba01_to_ba06',38),(74,'2026_03_19_000007_cr06_created_by_on_delete_restrict',39),(75,'2026_03_19_000008_ba03_rename_estado_persona_to_estado_geografico',40),(76,'2026_03_19_000009_cr05_logo_id_fk_orden_produccion',41),(77,'2026_03_19_000010_cr02_normalizar_proveedores_juridicos',42),(78,'2026_03_19_200001_cr07_cr01_softdeletes_user_restrict_fks',43),(79,'2026_03_19_200002_cr06_cr05_fks_banco_pedido_unique_persona',43),(80,'2026_03_19_200003_cr02_cr03_me03_cascade_to_restrict',43),(81,'2026_03_19_200004_me01_me02_me05_ba03_unify_persona_fks',43),(82,'2026_03_19_200005_me04_ba01_composite_indexes',43),(83,'2026_03_19_200006_ba05_enum_cancelada_cotizacion',44),(84,'2026_03_19_200007_me06_rename_plural_tables_to_singular',45),(85,'2026_03_19_200008_ba02_softdeletes_catalogos_maestros',46),(86,'2026_03_19_200009_ba04_create_pago_pedido_migrate_data',47),(87,'2026_03_19_200010_ba04_drop_flat_payment_columns_from_pedido',48),(88,'2026_03_19_200011_me07_logo_id_fk_bordados_drop_nombre_logo',49),(89,'2026_04_14_140742_normalize_unidad_medida_in_insumos',50),(90,'2026_04_21_000001_create_departamento_table',51),(91,'2026_04_21_000002_create_cargo_table',51),(92,'2026_04_21_000003_normalize_departamento_cargo_in_empleado',51),(93,'2026_04_26_000001_create_user_recovery_question_table',52),(94,'2026_04_26_000002_create_recovery_attempt_table',52),(95,'2026_04_26_000003_add_recovery_columns_to_user_table',52),(96,'2026_04_26_000004_add_password_reset_flag_to_user_table',53),(97,'2026_05_07_100000_create_atributo_table',54),(98,'2026_05_07_100001_create_atributo_valor_table',54),(99,'2026_05_07_100002_create_tipo_producto_atributo_table',54),(100,'2026_05_07_100003_create_producto_atributo_valor_table',54),(101,'2026_05_07_100004_add_codigo_to_insumo',54),(102,'2026_05_07_100005_add_precio_confeccion_and_requiere_tela_to_tipo_producto',54),(103,'2026_05_07_100006_add_insumo_tela_and_atributos_to_producto',54),(104,'2026_05_07_100007_add_snapshots_to_detalle_cotizacion',54),(105,'2026_05_07_100008_add_snapshots_to_detalle_pedido',54),(106,'2026_05_07_100009_modelo_nullable_in_producto',55),(107,'2026_05_07_100010_rename_codigo_prefijo_to_prefijo_in_tipo_producto',56),(108,'2026_05_07_100011_drop_modelo_from_producto',56),(109,'2026_05_27_220125_op_redesign_orden_produccion_empleado_detalle',57),(110,'2026_05_27_222657_op_drop_produccion_diaria_add_defectuosa',58),(111,'2026_05_28_004004_add_is_inventoriable_to_insumo_table',59),(112,'2026_05_28_185713_drop_proveedor_id_from_insumo',60),(113,'2026_05_28_191504_create_tipo_producto_insumo_table',61),(114,'2026_05_28_194636_add_consumo_tela_por_unidad_to_tipo_producto',62),(115,'2026_05_29_110403_drop_costo_estimado_from_orden_produccion',63),(116,'2026_05_31_000000_add_stock_maximo_to_insumo_table',64),(117,'2026_06_01_142640_add_tasa_y_condiciones_to_cotizacion_table',65),(118,'2026_06_02_000001_create_compra_table',66),(119,'2026_06_02_000002_create_compra_detalle_table',66),(120,'2026_06_03_000001_add_auditoria_anulacion_to_compra_table',67),(121,'2026_06_03_000002_add_clonada_to_compra_table',68);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;

--
-- Table structure for table `movimiento_insumo`
--

DROP TABLE IF EXISTS `movimiento_insumo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimiento_insumo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `insumo_id` bigint unsigned NOT NULL,
  `tipo_movimiento` enum('Entrada','Salida') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `stock_anterior` decimal(10,2) NOT NULL,
  `stock_nuevo` decimal(10,2) NOT NULL,
  `motivo` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `movimientos_insumos_insumo_id_foreign` (`insumo_id`),
  KEY `movimientos_insumos_created_by_foreign` (`created_by`),
  KEY `idx_mov_created_at` (`created_at`),
  KEY `idx_mov_insumo_created` (`insumo_id`,`created_at`),
  CONSTRAINT `movimientos_insumos_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`),
  CONSTRAINT `movimientos_insumos_insumo_id_foreign` FOREIGN KEY (`insumo_id`) REFERENCES `insumo` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimiento_insumo`
--

/*!40000 ALTER TABLE `movimiento_insumo` DISABLE KEYS */;
INSERT INTO `movimiento_insumo` VALUES (1,1,'Entrada',1000.00,0.00,1000.00,'Inventario inicial',2,'2025-12-04 18:58:28','2025-12-04 18:58:28'),(2,2,'Entrada',5000.00,0.00,5000.00,'Compra inicial',2,'2025-12-04 18:58:28','2025-12-04 18:58:28'),(3,1,'Salida',5.00,1000.00,995.00,'Consumo por Pedido #1 - Producto: Polo Clásico',1,'2025-12-10 23:24:45','2025-12-10 23:24:45'),(4,3,'Salida',20.00,50.00,30.00,'Vendidos a una costurera',1,'2025-12-11 00:40:50','2025-12-11 00:40:50'),(5,1,'Salida',4.00,995.00,991.00,'Consumo por Pedido #2 - Producto: Camisa Oxford Clasica',1,'2025-12-16 20:47:53','2025-12-16 20:47:53'),(6,1,'Salida',2.00,991.00,989.00,'Consumo por Pedido #3 - Producto: Camisa Oxford Clasica',1,'2025-12-18 17:45:32','2025-12-18 17:45:32'),(7,1,'Salida',4.00,989.00,985.00,'Consumo por Pedido #4 - Producto: Chemise Cuello Mao',1,'2025-12-19 20:28:18','2025-12-19 20:28:18'),(8,1,'Entrada',4.00,985.00,989.00,'Reversión por actualización de Pedido #4 - Producto: Chemise Cuello Mao',5,'2026-01-16 17:28:16','2026-01-16 17:28:16'),(9,1,'Salida',4.00,989.00,985.00,'Consumo por actualización de Pedido #4 - Producto: Chemise Cuello Mao',5,'2026-01-16 17:28:16','2026-01-16 17:28:16'),(10,1,'Entrada',4.00,985.00,989.00,'Reversión por actualización de Pedido #4 - Producto: Chemise Cuello Mao',5,'2026-01-16 17:59:27','2026-01-16 17:59:27'),(11,2,'Salida',4.00,5000.00,4996.00,'Consumo por actualización de Pedido #4 - Producto: CE-001',5,'2026-01-16 17:59:27','2026-01-16 17:59:27'),(12,2,'Entrada',4.00,4996.00,5000.00,'Reversión por actualización de Pedido #4 - Producto: CE-001',5,'2026-01-16 18:00:46','2026-01-16 18:00:46'),(13,2,'Salida',4.00,5000.00,4996.00,'Consumo por actualización de Pedido #4 - Producto: Chemise Cuello Mao',5,'2026-01-16 18:00:46','2026-01-16 18:00:46'),(14,1,'Salida',4.00,989.00,985.00,'Consumo por actualización de Pedido #4 - Producto: Chemise Cuello Mao',5,'2026-01-16 18:00:46','2026-01-16 18:00:46'),(15,1,'Entrada',4.00,985.00,989.00,'Reversión por actualización de Pedido #2 - Producto: Camisa Oxford Clasica',5,'2026-01-16 18:07:36','2026-01-16 18:07:36'),(16,1,'Salida',4.00,989.00,985.00,'Consumo por actualización de Pedido #2 - Producto: Camisa Oxford Clasica',5,'2026-01-16 18:07:36','2026-01-16 18:07:36'),(17,1,'Entrada',5.00,985.00,990.00,'Reversión por actualización de Pedido #1 - Producto: Chemise Clasica',5,'2026-01-16 18:07:49','2026-01-16 18:07:49'),(18,1,'Salida',5.00,990.00,985.00,'Consumo por actualización de Pedido #1 - Producto: Chemise Clasica',5,'2026-01-16 18:07:49','2026-01-16 18:07:49'),(19,2,'Salida',1.00,4996.00,4995.00,'Consumo por Pedido #5 - Producto: Chemise Clasica',5,'2026-01-17 16:59:42','2026-01-17 16:59:42'),(20,2,'Salida',4.00,4995.00,4991.00,'Consumo por Pedido #6 - Producto: Chemise Clasica',5,'2026-01-17 22:33:53','2026-01-17 22:33:53'),(21,2,'Entrada',4.00,4991.00,4995.00,'Reversión por actualización de Pedido #6 - Producto: Chemise Clasica',5,'2026-01-17 22:34:12','2026-01-17 22:34:12'),(22,2,'Salida',4.00,4995.00,4991.00,'Consumo por actualización de Pedido #6 - Producto: Chemise Clasica',5,'2026-01-17 22:34:12','2026-01-17 22:34:12'),(23,2,'Entrada',1.00,4991.00,4992.00,'Reversión por actualización de Pedido #5 - Producto: Chemise Clasica',5,'2026-01-17 22:34:28','2026-01-17 22:34:28'),(24,2,'Salida',1.00,4992.00,4991.00,'Consumo por actualización de Pedido #5 - Producto: Chemise Clasica',5,'2026-01-17 22:34:28','2026-01-17 22:34:28'),(25,2,'Salida',4.00,4991.00,4987.00,'Consumo por Pedido #7 - Producto: Chemise Clasica',5,'2026-01-17 22:36:10','2026-01-17 22:36:10'),(26,2,'Entrada',4.00,4987.00,4991.00,'Reversión por actualización de Pedido #7 - Producto: Chemise Clasica',5,'2026-01-17 23:23:25','2026-01-17 23:23:25'),(27,2,'Salida',4.00,4991.00,4987.00,'Consumo por actualización de Pedido #7 - Producto: Chemise Clasica',5,'2026-01-17 23:23:25','2026-01-17 23:23:25'),(28,3,'Salida',2.00,30.00,28.00,'chemise',5,'2026-01-18 23:59:30','2026-01-18 23:59:30'),(29,3,'Salida',28.00,28.00,0.00,'chemises',5,'2026-01-19 00:01:47','2026-01-19 00:01:47'),(30,2,'Salida',2.00,4987.00,4985.00,'Consumo por Pedido #8 - Producto: Chemise Clasica',5,'2026-01-19 03:37:08','2026-01-19 03:37:08'),(31,2,'Salida',2.00,4985.00,4983.00,'Consumo por Pedido #9 - Producto: Chemise Clasica',5,'2026-01-19 03:42:07','2026-01-19 03:42:07'),(32,2,'Salida',2.00,4983.00,4981.00,'Consumo por Pedido #10 - Producto: CE-001',5,'2026-01-19 04:36:20','2026-01-19 04:36:20'),(33,5,'Entrada',10.00,100.00,110.00,'pues llegaron 10 mas',1,'2026-01-20 20:36:39','2026-01-20 20:36:39'),(34,5,'Salida',10.00,110.00,100.00,'Se gastaron',1,'2026-01-20 20:38:10','2026-01-20 20:38:10'),(36,5,'Entrada',10.00,100.00,110.00,'Compra #1 — Fact: S/N',7,'2026-06-02 20:18:51','2026-06-02 20:18:51'),(37,5,'Entrada',10.00,110.00,120.00,'Compra #2 — Fact: 0001',7,'2026-06-02 20:48:34','2026-06-02 20:48:34'),(38,8,'Entrada',10.00,0.00,10.00,'Compra #2 — Fact: 0001',7,'2026-06-02 20:48:34','2026-06-02 20:48:34');
/*!40000 ALTER TABLE `movimiento_insumo` ENABLE KEYS */;

--
-- Table structure for table `orden_produccion`
--

DROP TABLE IF EXISTS `orden_produccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orden_produccion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint unsigned DEFAULT NULL,
  `detalle_pedido_id` bigint unsigned DEFAULT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `empleado_id` bigint unsigned DEFAULT NULL,
  `cantidad_solicitada` int NOT NULL,
  `cantidad_producida` int NOT NULL DEFAULT '0',
  `cantidad_defectuosa` int NOT NULL DEFAULT '0',
  `fecha_inicio` date NOT NULL,
  `fecha_fin_estimada` date NOT NULL,
  `fecha_fin_real` date DEFAULT NULL,
  `estado` enum('Pendiente','En Proceso','Finalizado','Cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `notas` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ordenes_produccion_producto_id_foreign` (`producto_id`),
  KEY `ordenes_produccion_created_by_foreign` (`created_by`),
  KEY `orden_produccion_pedido_id_foreign` (`pedido_id`),
  KEY `idx_orden_estado` (`estado`),
  KEY `idx_orden_fecha_fin` (`fecha_fin_estimada`),
  KEY `idx_orden_estado_fecha_fin` (`estado`,`fecha_fin_estimada`),
  KEY `orden_produccion_detalle_pedido_id_foreign` (`detalle_pedido_id`),
  KEY `orden_produccion_empleado_id_foreign` (`empleado_id`),
  CONSTRAINT `orden_produccion_detalle_pedido_id_foreign` FOREIGN KEY (`detalle_pedido_id`) REFERENCES `detalle_pedido` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orden_produccion_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleado` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orden_produccion_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordenes_produccion_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`),
  CONSTRAINT `ordenes_produccion_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orden_produccion`
--

/*!40000 ALTER TABLE `orden_produccion` DISABLE KEYS */;
INSERT INTO `orden_produccion` VALUES (3,3,NULL,5,NULL,1,0,0,'2025-12-18','2026-01-28',NULL,'Pendiente','urgenteeeee',1,'2025-12-18 17:51:44','2025-12-19 18:43:30','2025-12-19 18:43:30'),(4,3,NULL,5,NULL,1,0,0,'2025-12-18','2026-01-28',NULL,'Pendiente','urgente',1,'2025-12-18 18:00:17','2025-12-19 18:43:21','2025-12-19 18:43:21'),(5,3,NULL,5,NULL,1,0,0,'2025-12-19','2026-01-28',NULL,'Pendiente','urgente',1,'2025-12-19 18:44:18','2026-01-17 16:56:43','2026-01-17 16:56:43'),(6,3,NULL,5,NULL,1,0,0,'2026-01-16','2026-01-28',NULL,'Finalizado',NULL,5,'2026-01-16 18:10:08','2026-01-16 18:10:26',NULL),(7,5,NULL,1,NULL,4,0,0,'2026-01-17','2026-01-20',NULL,'Finalizado',NULL,5,'2026-01-17 17:01:08','2026-01-17 17:35:23',NULL),(8,56,NULL,6,NULL,5,6,0,'2026-03-06','2026-05-20',NULL,'Finalizado',NULL,7,'2026-03-06 18:38:41','2026-03-06 20:22:51',NULL),(9,56,NULL,6,NULL,5,5,0,'2026-03-06','2026-05-20',NULL,'Finalizado','hacerlo bien',7,'2026-03-06 20:28:00','2026-03-06 20:28:27',NULL),(10,54,NULL,8,NULL,10,10,0,'2026-03-06','2026-03-08',NULL,'En Proceso','hola',7,'2026-03-06 20:43:01','2026-03-06 21:04:25',NULL),(11,58,NULL,8,NULL,1,1,0,'2026-03-18','2026-03-19',NULL,'Finalizado',NULL,7,'2026-03-18 14:10:20','2026-03-18 14:10:39',NULL),(13,62,90,8,4,1,1,0,'2026-05-28','2026-05-30','2026-05-29','Finalizado',NULL,1,'2026-05-28 02:19:23','2026-05-29 23:30:39',NULL),(14,7,15,1,3,4,1,0,'2026-05-27','2026-06-01',NULL,'Pendiente',NULL,1,'2026-05-28 02:34:11','2026-05-28 03:01:39','2026-05-28 03:01:39'),(15,65,94,8,3,1,1,0,'2026-05-28','2026-05-29','2026-05-27','Finalizado',NULL,1,'2026-05-28 03:01:04','2026-05-28 03:02:16',NULL),(20,67,100,8,4,1,1,0,'2026-05-29','2026-06-26','2026-05-29','Finalizado','Producir con urgencia',1,'2026-05-29 23:28:13','2026-05-29 23:28:42',NULL),(21,67,99,8,4,3,0,0,'2026-05-30','2026-06-26',NULL,'Pendiente',NULL,1,'2026-05-30 21:35:26','2026-05-30 21:35:26',NULL);
/*!40000 ALTER TABLE `orden_produccion` ENABLE KEYS */;

--
-- Table structure for table `pago_pedido`
--

DROP TABLE IF EXISTS `pago_pedido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pago_pedido` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint unsigned NOT NULL,
  `metodo` enum('efectivo','transferencia','pago_movil') COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `banco_id` bigint unsigned DEFAULT NULL,
  `referencia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pago_pedido_banco_id_foreign` (`banco_id`),
  KEY `idx_pago_pedido_metodo` (`pedido_id`,`metodo`),
  CONSTRAINT `pago_pedido_banco_id_foreign` FOREIGN KEY (`banco_id`) REFERENCES `banco` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pago_pedido_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pago_pedido`
--

/*!40000 ALTER TABLE `pago_pedido` DISABLE KEYS */;
INSERT INTO `pago_pedido` VALUES (1,5,'efectivo',11.00,NULL,NULL,'2026-03-19 21:04:11','2026-03-19 21:04:11'),(2,6,'efectivo',7.00,NULL,NULL,'2026-03-19 21:04:11','2026-03-19 21:04:11'),(3,8,'efectivo',22.00,NULL,NULL,'2026-03-19 21:04:11','2026-03-19 21:04:11'),(4,10,'efectivo',33.00,NULL,NULL,'2026-03-19 21:04:11','2026-03-19 21:04:11'),(5,12,'efectivo',20.00,NULL,NULL,'2026-03-19 21:04:11','2026-03-19 21:04:11'),(6,13,'efectivo',900.00,NULL,NULL,'2026-03-19 21:04:11','2026-03-19 21:04:11'),(7,14,'efectivo',44.00,NULL,NULL,'2026-03-19 21:04:11','2026-03-19 21:04:11'),(8,15,'efectivo',300.00,NULL,NULL,'2026-03-19 21:04:11','2026-03-19 21:04:11'),(9,30,'efectivo',40.00,NULL,NULL,'2026-03-19 21:04:11','2026-03-19 21:04:11'),(10,42,'efectivo',20.00,NULL,NULL,'2026-03-19 21:04:11','2026-03-19 21:04:11'),(11,43,'pago_movil',10.00,15,'56465','2026-03-19 21:04:11','2026-03-19 21:04:11'),(12,43,'efectivo',0.00,NULL,NULL,'2026-03-19 21:04:11','2026-03-19 21:04:11'),(13,44,'transferencia',20.00,13,'4534538423','2026-03-19 21:04:11','2026-03-19 21:04:11'),(14,44,'pago_movil',0.00,16,'4231325523','2026-03-19 21:04:11','2026-03-19 21:04:11'),(15,44,'efectivo',0.00,NULL,NULL,'2026-03-19 21:04:11','2026-03-19 21:04:11'),(16,54,'efectivo',10.00,NULL,NULL,'2026-03-19 21:04:11','2026-03-19 21:04:11'),(17,55,'efectivo',100.00,NULL,NULL,'2026-03-19 21:04:11','2026-03-19 21:04:11'),(18,56,'transferencia',100.00,2,'43242342','2026-03-19 21:04:11','2026-03-19 21:04:11'),(19,57,'transferencia',20.00,2,'4543534636','2026-03-19 21:04:11','2026-03-19 21:04:11'),(20,57,'pago_movil',0.00,14,'12379847239','2026-03-19 21:04:11','2026-03-19 21:04:11'),(22,9,'efectivo',22.00,NULL,NULL,'2026-03-19 21:05:00','2026-03-19 21:05:00'),(23,11,'efectivo',33.00,NULL,NULL,'2026-03-19 21:05:00','2026-03-19 21:05:00'),(24,46,'efectivo',300.00,NULL,NULL,'2026-03-19 21:05:00','2026-03-19 21:05:00'),(26,61,'efectivo',10.00,NULL,NULL,'2026-05-07 18:39:36','2026-05-07 18:39:36'),(27,65,'efectivo',10.00,NULL,NULL,'2026-05-28 01:06:26','2026-05-28 01:06:26'),(28,66,'efectivo',20.00,NULL,NULL,'2026-05-29 14:58:08','2026-05-29 14:58:08'),(29,67,'efectivo',10.00,NULL,NULL,'2026-05-29 23:14:12','2026-05-29 23:14:12'),(30,67,'transferencia',500.00,15,'8127984723984','2026-05-29 23:14:12','2026-05-29 23:14:12'),(31,67,'pago_movil',500.00,2,'37463764','2026-05-29 23:14:12','2026-05-29 23:14:12'),(32,68,'efectivo',17.00,NULL,NULL,'2026-06-01 18:52:08','2026-06-01 18:52:08');
/*!40000 ALTER TABLE `pago_pedido` ENABLE KEYS */;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES ('admin@gmail.com','$2y$12$fMwLn7TO4DrrtysDHd1SgOJbk4HCA.h71MW6tNNbPnd1TMpUIvXZe','2026-04-14 23:38:32'),('emman6321@gmail.com','$2y$12$W5jzsw712xxv0eHn6bG4BO4OSjb.CLieSugeA4pUlD9H75rnD11za','2026-05-30 20:20:03'),('vanessalopez090551@gmail.com','$2y$12$y7iiZTcAZq9wHPrufhpSK./ZStflchwZvYdOigU4/YZJlzXQWr6C2','2026-01-15 00:32:57');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;

--
-- Table structure for table `pedido`
--

DROP TABLE IF EXISTS `pedido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedido` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cotizacion_id` bigint unsigned DEFAULT NULL,
  `cliente_id` bigint unsigned DEFAULT NULL,
  `fecha_pedido` date NOT NULL,
  `fecha_entrega_estimada` date DEFAULT NULL,
  `estado` enum('Pendiente','Procesando','Completado','Cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `prioridad` enum('Normal','Alta','Urgente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Normal',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `abono` decimal(10,2) NOT NULL DEFAULT '0.00',
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pedido_cotizacion_id_unique` (`cotizacion_id`),
  KEY `pedidos_user_id_foreign` (`user_id`),
  KEY `pedido_cliente_id_foreign` (`cliente_id`),
  KEY `pedido_cotizacion_id_foreign` (`cotizacion_id`),
  KEY `idx_pedido_estado` (`estado`),
  KEY `idx_pedido_fecha` (`fecha_pedido`),
  KEY `idx_pedido_cliente_estado` (`cliente_id`,`estado`),
  CONSTRAINT `pedido_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pedido_cotizacion_id_foreign` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizacion` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pedido_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedido`
--

/*!40000 ALTER TABLE `pedido` DISABLE KEYS */;
INSERT INTO `pedido` VALUES (1,NULL,7,'2025-12-10','2026-01-07','Procesando','Normal',29.90,0.00,1,'2025-12-10 23:24:45','2026-01-17 16:58:05','2026-01-17 16:58:05'),(2,NULL,7,'2025-12-16','2025-12-31','Cancelado','Normal',50.00,0.00,1,'2025-12-16 20:47:53','2026-01-17 16:58:01','2026-01-17 16:58:01'),(3,NULL,3,'2025-12-18','2026-01-30','Pendiente','Urgente',25.00,0.00,1,'2025-12-18 17:45:32','2026-01-17 16:57:54','2026-01-17 16:57:54'),(4,7,1,'2025-12-20','2026-01-16','Completado','Urgente',34.00,4.00,1,'2025-12-19 20:28:18','2026-01-17 16:57:58','2026-01-17 16:57:58'),(5,8,9,'2026-01-17','2026-01-22','Cancelado','Normal',119.60,11.00,5,'2026-01-17 16:59:42','2026-01-17 22:34:28',NULL),(6,9,12,'2026-01-24','2026-01-31','Completado','Normal',119.60,7.00,5,'2026-01-17 22:33:53','2026-01-17 22:34:12',NULL),(7,10,8,'2026-01-24','2026-01-31','Procesando','Normal',119.60,0.00,5,'2026-01-17 22:36:10','2026-01-17 23:23:25',NULL),(8,NULL,14,'2026-01-18','2026-01-20','Pendiente','Normal',388.70,22.00,5,'2026-01-19 03:37:08','2026-01-19 03:37:08',NULL),(9,11,14,'2026-01-18','2026-01-20','Pendiente','Normal',388.70,22.00,5,'2026-01-19 03:42:07','2026-01-19 03:42:07',NULL),(10,15,20,'2026-01-18','2026-01-20','Pendiente','Normal',1317.80,33.00,5,'2026-01-19 04:36:20','2026-01-19 04:36:20',NULL),(11,14,9,'2026-01-19','2026-01-21','Pendiente','Alta',1317.80,33.00,1,'2026-01-19 19:13:45','2026-01-19 19:13:45',NULL),(12,13,15,'2026-01-19','2026-01-23','Pendiente','Normal',25.00,20.00,1,'2026-01-19 19:24:34','2026-01-19 19:24:34',NULL),(13,19,9,'2026-01-19','2026-01-23','Pendiente','Normal',986.70,900.00,1,'2026-01-19 19:32:56','2026-01-19 19:32:56',NULL),(14,18,21,'2026-01-19','2026-01-21','Pendiente','Normal',627.00,44.00,1,'2026-01-19 19:51:33','2026-01-19 19:51:33',NULL),(15,17,15,'2026-01-19','2026-01-23','Pendiente','Normal',2963.40,300.00,1,'2026-01-19 20:16:20','2026-01-19 20:16:20',NULL),(16,20,22,'2026-01-20','2026-01-27','Pendiente','Normal',29.90,0.00,1,'2026-01-20 21:21:33','2026-01-20 21:21:33',NULL),(17,21,23,'2026-01-20','2026-01-27','Pendiente','Normal',51.00,0.00,1,'2026-01-20 21:25:39','2026-01-20 21:25:39',NULL),(18,22,23,'2026-01-20','2026-01-27','Pendiente','Normal',358.80,0.00,1,'2026-01-20 21:32:04','2026-01-20 21:32:04',NULL),(19,16,9,'2026-01-20','2026-01-27','Pendiente','Normal',374.00,0.00,1,'2026-01-20 21:34:18','2026-01-20 21:34:18',NULL),(20,23,23,'2026-01-20','2026-01-27','Pendiente','Normal',190.00,0.00,1,'2026-01-20 21:39:09','2026-01-20 21:39:09',NULL),(21,24,14,'2026-01-20','2026-01-27','Pendiente','Normal',29.90,0.00,1,'2026-01-20 21:45:49','2026-01-20 21:45:49',NULL),(22,25,23,'2026-01-20','2026-01-27','Pendiente','Normal',29.90,0.00,1,'2026-01-20 21:52:03','2026-01-20 21:52:03',NULL),(23,26,23,'2026-01-20','2026-01-27','Pendiente','Normal',59.80,0.00,1,'2026-01-20 21:56:29','2026-01-20 21:56:29',NULL),(24,27,23,'2026-01-20','2026-01-27','Pendiente','Normal',29.90,0.00,1,'2026-01-20 22:01:40','2026-01-20 22:01:40',NULL),(25,28,23,'2026-01-20','2026-01-27','Pendiente','Normal',358.80,0.00,1,'2026-01-20 22:21:37','2026-01-20 22:22:03',NULL),(26,29,23,'2026-02-19','2026-02-26','Pendiente','Normal',17.00,0.00,1,'2026-02-19 20:07:49','2026-02-19 20:07:49',NULL),(27,30,23,'2026-02-19',NULL,'Pendiente','Normal',204.00,0.00,1,'2026-02-19 20:53:28','2026-02-19 20:53:28',NULL),(28,31,23,'2026-02-19',NULL,'Pendiente','Normal',204.00,0.00,1,'2026-02-20 00:04:19','2026-02-20 00:04:19',NULL),(29,32,23,'2026-02-19',NULL,'Pendiente','Normal',156.00,0.00,1,'2026-02-20 00:28:11','2026-02-20 00:28:11',NULL),(30,33,23,'2026-02-19','2026-02-28','Pendiente','Alta',578.00,40.00,1,'2026-02-20 00:32:25','2026-02-20 00:42:11',NULL),(31,34,23,'2026-02-19',NULL,'Pendiente','Normal',51.00,0.00,1,'2026-02-20 00:43:39','2026-02-20 00:43:39',NULL),(32,35,23,'2026-02-19',NULL,'Pendiente','Normal',85.00,0.00,1,'2026-02-20 00:52:16','2026-02-20 00:52:16',NULL),(33,36,23,'2026-02-19',NULL,'Pendiente','Normal',170.00,0.00,1,'2026-02-20 01:01:25','2026-02-20 01:01:25',NULL),(34,37,23,'2026-02-19',NULL,'Pendiente','Normal',340.00,0.00,1,'2026-02-20 01:04:25','2026-02-20 01:04:25',NULL),(35,38,9,'2026-02-22',NULL,'Pendiente','Normal',204.00,0.00,1,'2026-02-22 20:52:23','2026-02-22 20:52:23',NULL),(36,39,9,'2026-02-23',NULL,'Pendiente','Normal',170.00,0.00,1,'2026-02-23 20:00:14','2026-02-23 20:00:14',NULL),(37,43,8,'2026-02-23',NULL,'Pendiente','Normal',94.50,0.00,1,'2026-02-24 02:54:17','2026-02-24 02:57:23','2026-02-24 02:57:23'),(38,44,8,'2026-02-23',NULL,'Pendiente','Normal',94.50,0.00,1,'2026-02-24 02:58:25','2026-02-24 02:58:25','2026-02-24 02:58:25'),(39,45,8,'2026-02-23',NULL,'Pendiente','Normal',94.50,0.00,1,'2026-02-24 03:26:39','2026-02-24 03:26:40','2026-02-24 03:26:40'),(40,46,9,'2026-02-24',NULL,'Pendiente','Normal',253.00,0.00,1,'2026-02-24 15:51:39','2026-02-24 15:51:39',NULL),(41,47,8,'2026-02-24',NULL,'Pendiente','Normal',94.50,0.00,1,'2026-02-24 16:31:27','2026-02-24 16:31:28','2026-02-24 16:31:28'),(42,42,23,'2026-02-24','2026-04-25','Pendiente','Normal',200.00,20.00,1,'2026-02-24 16:36:47','2026-02-24 16:37:21',NULL),(43,48,23,'2026-02-24','2026-03-28','Pendiente','Normal',260.00,10.00,1,'2026-02-24 17:26:11','2026-02-24 17:58:15',NULL),(44,41,23,'2026-02-24','2026-02-27','Pendiente','Normal',25.00,20.00,1,'2026-02-24 19:41:36','2026-02-24 19:42:24',NULL),(45,40,23,'2026-02-24',NULL,'Pendiente','Normal',170.00,0.00,1,'2026-02-24 20:05:58','2026-02-24 20:05:58',NULL),(46,49,28,'2026-02-26','2026-03-29','Pendiente','Normal',470.00,300.00,1,'2026-02-26 20:33:31','2026-02-26 20:35:51',NULL),(47,50,28,'2026-02-26',NULL,'Pendiente','Normal',34.00,0.00,1,'2026-02-26 20:36:54','2026-02-26 20:36:54',NULL),(48,51,28,'2026-02-26',NULL,'Pendiente','Normal',17.00,0.00,1,'2026-02-26 20:40:21','2026-02-26 20:40:21',NULL),(49,52,28,'2026-02-26',NULL,'Pendiente','Normal',51.00,0.00,1,'2026-02-26 20:42:31','2026-02-26 20:42:31',NULL),(50,53,23,'2026-02-26',NULL,'Pendiente','Normal',170.00,0.00,1,'2026-02-26 20:49:46','2026-02-26 20:49:46',NULL),(51,54,23,'2026-02-26',NULL,'Pendiente','Normal',17.00,0.00,1,'2026-02-26 20:50:33','2026-02-26 20:50:33',NULL),(52,55,23,'2026-02-26',NULL,'Pendiente','Normal',16.00,0.00,1,'2026-02-26 20:54:22','2026-02-26 20:54:22',NULL),(53,56,23,'2026-02-26',NULL,'Pendiente','Normal',16.00,0.00,1,'2026-02-26 21:01:40','2026-02-26 21:01:40',NULL),(54,57,36,'2026-03-05','2026-03-10','Pendiente','Alta',190.00,10.00,7,'2026-03-05 17:32:34','2026-03-05 17:33:27',NULL),(55,58,36,'2026-03-05','2026-03-19','Pendiente','Alta',200.00,100.00,7,'2026-03-05 17:47:46','2026-03-05 17:48:24',NULL),(56,59,36,'2026-03-05','2026-05-22','Pendiente','Normal',110.00,100.00,7,'2026-03-05 20:13:13','2026-03-05 20:14:11',NULL),(57,61,23,'2026-03-10','2026-03-31','Pendiente','Alta',200.00,20.00,7,'2026-03-10 20:54:39','2026-03-10 20:57:35',NULL),(58,62,23,'2026-03-18','2026-03-20','Pendiente','Normal',16.00,0.00,7,'2026-03-18 14:07:18','2026-03-23 03:12:12',NULL),(59,63,8,'2026-03-19',NULL,'Pendiente','Normal',94.50,0.00,1,'2026-03-19 21:35:32','2026-03-19 21:35:33','2026-03-19 21:35:33'),(60,64,8,'2026-03-20',NULL,'Pendiente','Normal',94.50,0.00,1,'2026-03-20 13:17:33','2026-03-20 13:17:34','2026-03-20 13:17:34'),(61,66,23,'2026-05-07','2026-05-16','Pendiente','Normal',33.00,10.00,1,'2026-05-07 18:38:53','2026-05-07 18:39:36',NULL),(62,67,23,'2026-05-14',NULL,'Pendiente','Normal',348.00,0.00,1,'2026-05-15 00:25:57','2026-05-15 00:25:57',NULL),(63,65,23,'2026-05-26',NULL,'Pendiente','Normal',17.00,0.00,1,'2026-05-26 23:51:07','2026-05-26 23:51:07',NULL),(64,68,23,'2026-05-26',NULL,'Pendiente','Normal',16.00,0.00,1,'2026-05-27 01:29:00','2026-05-27 01:29:00',NULL),(65,69,23,'2026-05-28','2026-05-28','Pendiente','Alta',16.00,10.00,1,'2026-05-28 01:06:26','2026-05-28 01:06:26',NULL),(66,71,42,'2026-05-29','2026-06-13','Pendiente','Normal',64.00,20.00,1,'2026-05-29 14:58:08','2026-05-29 14:58:08',NULL),(67,72,23,'2026-05-29','2026-06-28','Procesando','Normal',1126.00,1010.00,1,'2026-05-29 23:14:12','2026-05-30 21:35:26',NULL),(68,74,14,'2026-06-01','2026-06-16','Pendiente','Normal',17.00,17.00,1,'2026-06-01 18:52:07','2026-06-01 18:52:08',NULL);
/*!40000 ALTER TABLE `pedido` ENABLE KEYS */;

--
-- Table structure for table `persona`
--

DROP TABLE IF EXISTS `persona`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `persona` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento_identidad` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_documento` enum('V-','E-','J-','G-') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'V-',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_geografico` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` enum('M','F','Otro') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `persona_tipo_doc_documento_unique` (`tipo_documento`,`documento_identidad`),
  UNIQUE KEY `persona_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `persona`
--

/*!40000 ALTER TABLE `persona` DISABLE KEYS */;
INSERT INTO `persona` VALUES (1,'Administrador','Sistema','00000001','V-','admin@gmail.com',NULL,NULL,NULL,'2025-12-04 18:58:27','2025-12-04 18:58:27',NULL),(2,'El','Supervisor','00000002','V-',NULL,'Portuguesa','2003-04-08','M','2025-12-04 18:58:28','2025-12-08 14:30:58',NULL),(3,'James David','Vance','8889292','G-','jdvance@gmail.com','Washington DC','1980-04-04','M','2025-12-04 20:19:19','2025-12-04 20:19:19',NULL),(4,'Jose Luis','Rodriguez','4567899','V-','isavale10@gmail.com','Portuguesa','2005-03-31','M','2025-12-05 20:14:04','2025-12-05 20:14:04',NULL),(6,'Peter','Thiel','8769044','E-','pltrinvest@gmail.com',NULL,NULL,NULL,'2025-12-08 19:55:18','2025-12-08 20:23:37',NULL),(7,'Larry','Ellison','545683666','E-','oraclecorporation@gmail.com',NULL,NULL,NULL,'2025-12-08 19:55:18','2025-12-08 20:23:29',NULL),(8,'Leslie Herbert','Wexner','6233455','E-','vsecret@gmail.com',NULL,NULL,NULL,'2025-12-08 20:04:57','2025-12-08 20:23:20',NULL),(9,'Robert','Maxwell','987489','E-','maxwellcorporation@gmail.com',NULL,NULL,NULL,'2025-12-08 20:19:32','2025-12-08 20:22:30',NULL),(10,'Jose','Juan','7499586','E-','nvidiaceo@gmail.com',NULL,NULL,NULL,'2025-12-09 18:54:35','2025-12-09 18:56:47',NULL),(11,'Alexander Caedmon','Karp','89320234','E-','alexkpr@gmail.com',NULL,NULL,NULL,'2025-12-10 18:09:48','2025-12-10 18:09:48',NULL),(12,'Mark','Zuckerberg','18728555','V-','facebook@gmail.com',NULL,NULL,NULL,'2025-12-10 20:29:40','2025-12-10 20:31:20',NULL),(13,'Santiago','Mendoza','30822318','V-','santitron@gmail.com','Portuguesa','2005-01-11','M','2025-12-10 20:57:36','2026-05-29 19:52:36',NULL),(14,'Mark','Cuban','6786543','E-','markcu@gmail.com','Texas','2004-03-10','M','2025-12-10 21:09:58','2025-12-10 21:09:58',NULL),(15,'Vanessa','diaz','30966655','V-','vanessalopez090551@gmail.com',NULL,NULL,NULL,'2026-01-17 16:51:52','2026-01-17 16:51:52',NULL),(16,'valeria','diaz','32152373','V-','valediaz@gmail.com',NULL,NULL,NULL,'2026-01-17 17:11:09','2026-01-17 17:11:09',NULL),(17,'cvane','','30966654','V-',NULL,NULL,NULL,NULL,'2026-01-17 22:05:23','2026-01-17 22:05:23',NULL),(18,'alalalallaa','kneoucnewuivcw','30966659','V-','alalallala@email.com',NULL,NULL,NULL,'2026-01-17 22:31:33','2026-01-17 22:31:33',NULL),(19,'Cleymar','Mendoza','30966271','V-','cley@gmail.com',NULL,NULL,NULL,'2026-01-18 03:49:00','2026-01-18 03:49:00',NULL),(20,'Cleymar','Mendoza','30966275','V-','cleymar@gmail.com',NULL,NULL,NULL,'2026-01-18 03:56:57','2026-01-18 03:56:57',NULL),(22,'Victor','Mendoza','12344093','V-','victorm@gmail.com',NULL,NULL,NULL,'2026-01-18 20:23:50','2026-01-18 20:23:50',NULL),(23,'Luis Alberto','Mendoza García','15789234','V-','luismendoza@gmail.com',NULL,NULL,NULL,'2026-01-18 20:37:01','2026-01-18 20:37:01',NULL),(24,'Rosa María','Hernández López','18234567','V-','rosahdez@hotmail.com',NULL,NULL,NULL,'2026-01-18 20:38:40','2026-01-18 20:38:40',NULL),(25,'Carlos Eduardo','Silva Martínez','84567890','E-','carlossilva@gmail.com',NULL,NULL,NULL,'2026-01-18 20:41:05','2026-01-18 20:41:05',NULL),(26,'Angela Patricia','Vargas Rojas','12456789','V-','angelavargas@gmail.com',NULL,NULL,NULL,'2026-01-18 20:43:53','2026-01-18 20:43:53',NULL),(27,'María Fernanda','Gutiérrez Méndez','16823456','V-','mariagutierrez@gmail.com',NULL,NULL,NULL,'2026-01-18 20:57:49','2026-01-18 20:57:49',NULL),(28,'Pedro Antonio','Briceño Rivas','19876543','V-','pedrobriceno@gmail.com',NULL,NULL,NULL,'2026-01-18 21:52:28','2026-01-18 21:52:28',NULL),(29,'Angely','Canelon','37782737','V-','loca123@gmail.com',NULL,NULL,NULL,'2026-01-19 00:25:34','2026-01-19 00:25:34',NULL),(30,'Alejandro','Abreu','31558506','V-','ale@gmail.com',NULL,NULL,NULL,'2026-01-19 03:56:04','2026-01-19 03:56:04',NULL),(31,'alejandro','abreu','31558507','V-','ale2@gmail.com',NULL,NULL,NULL,'2026-01-19 04:01:50','2026-01-19 04:01:50',NULL),(32,'alejandro','diaz','31558508','V-','vd6955291@gmail.com',NULL,NULL,NULL,'2026-01-19 04:05:33','2026-01-19 04:05:33',NULL),(33,'josefina','lopez','31558509','V-','vd695529221@gmail.com',NULL,NULL,NULL,'2026-01-19 04:17:44','2026-01-19 04:17:44',NULL),(34,'angel','Canelon','37782735','V-','loca1233@gmail.com',NULL,NULL,NULL,'2026-01-19 04:26:34','2026-01-19 04:26:34',NULL),(35,'abby','chuela','31558599','V-','abbychuela@gmail.com',NULL,NULL,NULL,'2026-01-19 16:49:16','2026-01-19 16:49:16',NULL),(36,'Yohan','Mendoza','15692128','V-','yohansito@gmail.com',NULL,NULL,NULL,'2026-01-20 01:29:08','2026-01-25 03:45:08',NULL),(37,'Emmanuel','Arroyo','30922671','V-','emman6321@gmail.com',NULL,NULL,NULL,'2026-01-20 21:23:34','2026-01-20 21:23:34',NULL),(38,'Inversiones Full Color CA','','30666777','J-','fullcolor10@gmail.com',NULL,NULL,NULL,'2026-02-22 17:56:13','2026-02-22 17:56:13',NULL),(39,'Carlos','Ramírez','18456321','V-','carlos.ramirez@gmail.com',NULL,NULL,NULL,'2026-02-26 19:10:25','2026-02-26 19:10:25',NULL),(40,'María','González','20134567','V-','maria.gonzalez@hotmail.com',NULL,NULL,NULL,'2026-02-26 19:10:25','2026-02-26 19:10:25',NULL),(42,'Inversiones Textilera del Centro','','41234567','J-','admintextileria@gmail.com',NULL,NULL,NULL,'2026-02-26 19:11:43','2026-02-26 20:22:20',NULL),(43,'Luis','Hernández','15678234','V-','luis.hernandez@outlook.com',NULL,NULL,NULL,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL),(44,'Confecciones El Llano CA','','40987654','J-','ventas@gmail.com',NULL,NULL,NULL,'2026-02-26 19:11:43','2026-02-26 20:21:46',NULL),(45,'Ana','Martínez','22345678','V-','ana.martinez@gmail.com',NULL,NULL,NULL,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL),(46,'José','Pérez','17890456','V-','jose.perez@yahoo.com',NULL,NULL,NULL,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL),(47,'Uniformes Profesionales VE CA','','42567890','J-','contactounipro@gmail.com',NULL,NULL,NULL,'2026-02-26 19:11:43','2026-02-26 20:23:03',NULL),(48,'Rosa','Castillo','19567890','V-','rosa.castillo@gmail.com',NULL,NULL,NULL,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL),(49,'Pedro','Morales','16789012','V-','pedro.morales@gmail.com',NULL,NULL,NULL,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL),(50,'Gregorio','Rodriguez','10729713','V-','gregorio10@gmail.com',NULL,NULL,NULL,'2026-02-26 19:46:04','2026-02-26 19:46:04',NULL),(51,'Asoproductos de Portuguesa CA','','13232455','J-','asoproductos@gmail.com',NULL,NULL,NULL,'2026-03-05 17:17:09','2026-03-05 17:17:09',NULL),(53,'Textiles Caracas Vzla','','1231321','J-','ventas@textilesvenezuela.com',NULL,NULL,NULL,'2026-03-19 18:21:05','2026-03-19 18:21:05',NULL),(54,'Insumos Textiles C.C.S','','11112222','J-','ventas@insumostextiles.com',NULL,NULL,NULL,'2026-03-19 18:21:05','2026-03-19 18:21:05',NULL),(55,'Telas y Bordados del Centro CA','','401234567','J-','ventas@telasbordados.com',NULL,NULL,NULL,'2026-03-19 18:21:06','2026-03-19 18:21:06',NULL),(56,'Hilos Industriales Portuguesa SA','','312456789','J-','info@hilosindustriales.com',NULL,NULL,NULL,'2026-03-19 18:21:06','2026-03-19 18:21:06',NULL),(57,'Insumos Textiles Venezuela CA','','201987654','G-','compras@insumostextiles.com.ve',NULL,NULL,NULL,'2026-03-19 18:21:06','2026-03-19 18:21:06',NULL),(58,'Botones y Accesorios Lara CA','','502345671','J-','ventas@botonesaccesorios.com',NULL,NULL,NULL,'2026-03-19 18:21:06','2026-03-19 18:21:06',NULL),(59,'Distribuidora de Telas Los Andes','','415678903','J-','contacto@telaslosandes.com',NULL,NULL,NULL,'2026-03-19 18:21:06','2026-03-19 18:21:06',NULL),(60,'Sofia','Mendoza','29347954','V-','sofi@gmail.com',NULL,NULL,NULL,'2026-04-14 21:26:32','2026-04-14 21:26:32',NULL),(61,'Maria','Mendoza','12345678','V-','mari10@gmail.com',NULL,NULL,NULL,'2026-04-29 00:22:53','2026-04-29 00:22:53',NULL),(62,'Jhoanir','Torres','11111111','V-','jhoanir10@gmail.com',NULL,NULL,NULL,'2026-04-29 00:25:48','2026-04-29 00:25:48',NULL),(65,'Julian','Ramirez','5233421','V-','Julian19@gmail.com','Portuguesa','2002-01-30','M','2026-04-30 20:25:42','2026-04-30 20:25:42',NULL),(66,'Kleiver Alexander','Colmenarez','14879947','V-','kleiverc@gmail.com','Portuguesa','1978-01-29','M','2026-05-30 19:47:16','2026-05-30 19:47:16',NULL),(67,'Yaneth Coromoto','Dsantis Salcedo','11544468','V-','Yanethds@gmail.com','Portuguesa','1974-01-05','F','2026-05-30 19:50:52','2026-05-30 19:50:52',NULL),(68,'Yanet Yubisai','Mendoza','14346453','V-','Yanethyubisai@gmail.com','Portuguesa','1975-11-30','F','2026-05-30 19:55:30','2026-05-30 19:55:30',NULL),(69,'Carmen Sofia','Rodriguez Perez','10644939','V-','Sofiar@gmail.com','Portuguesa','1969-04-30','F','2026-05-30 20:00:52','2026-05-30 20:00:52',NULL);
/*!40000 ALTER TABLE `persona` ENABLE KEYS */;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;

--
-- Table structure for table `produccion_diaria`
--

DROP TABLE IF EXISTS `produccion_diaria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produccion_diaria` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `orden_id` bigint unsigned NOT NULL,
  `fecha_produccion` date DEFAULT NULL,
  `empleado_id` bigint unsigned NOT NULL,
  `cantidad_producida` int NOT NULL,
  `cantidad_defectuosa` int NOT NULL DEFAULT '0',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `produccion_diaria_orden_id_foreign` (`orden_id`),
  KEY `produccion_diaria_empleado_id_index` (`empleado_id`),
  KEY `idx_prod_diaria_orden_fecha` (`orden_id`,`fecha_produccion`),
  CONSTRAINT `produccion_diaria_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleado` (`id`),
  CONSTRAINT `produccion_diaria_orden_id_foreign` FOREIGN KEY (`orden_id`) REFERENCES `orden_produccion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produccion_diaria`
--

/*!40000 ALTER TABLE `produccion_diaria` DISABLE KEYS */;
INSERT INTO `produccion_diaria` VALUES (4,8,'2026-03-06',4,2,0,'bien','2026-03-06 20:18:14','2026-03-06 20:18:14',NULL),(5,8,'2026-03-06',3,4,0,NULL,'2026-03-06 20:22:51','2026-03-06 20:22:51',NULL),(6,9,'2026-03-06',3,5,0,'hola','2026-03-06 20:28:27','2026-03-06 20:28:27',NULL),(7,10,'2026-03-06',4,10,0,'va bien','2026-03-06 21:00:54','2026-03-06 21:00:54',NULL),(8,11,'2026-03-18',3,1,0,'bien','2026-03-18 14:10:39','2026-03-18 14:10:39',NULL);
/*!40000 ALTER TABLE `produccion_diaria` ENABLE KEYS */;

--
-- Table structure for table `producto`
--

DROP TABLE IF EXISTS `producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `producto` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo_producto_id` bigint unsigned DEFAULT NULL,
  `insumo_tela_id` bigint unsigned DEFAULT NULL,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `precio_base` decimal(10,2) NOT NULL,
  `atributos_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `imagen` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `producto_codigo_unique` (`codigo`),
  KEY `producto_tipo_producto_id_foreign` (`tipo_producto_id`),
  KEY `producto_insumo_tela_id_foreign` (`insumo_tela_id`),
  CONSTRAINT `producto_insumo_tela_id_foreign` FOREIGN KEY (`insumo_tela_id`) REFERENCES `insumo` (`id`) ON DELETE SET NULL,
  CONSTRAINT `producto_tipo_producto_id_foreign` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipo_producto` (`id`) ON DELETE SET NULL,
  CONSTRAINT `producto_chk_1` CHECK (json_valid(`atributos_snapshot`))
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `producto`
--

/*!40000 ALTER TABLE `producto` DISABLE KEYS */;
INSERT INTO `producto` VALUES (1,1,NULL,'CHM-001','Polo de algodón pima con cuello redondo',29.90,NULL,'productoimg/imagenes/69406d911e834.jpg',1,'2025-12-04 18:58:28','2026-01-20 22:30:55','2026-01-20 22:30:55'),(2,NULL,NULL,NULL,'Camisa manga larga para oficina',59.90,NULL,NULL,1,'2025-12-04 18:58:28','2026-01-20 22:31:08','2026-01-20 22:31:08'),(3,NULL,NULL,NULL,'Corte Columbia I',19.00,NULL,'productoimg/imagenes/6940679f046be.png',1,'2025-12-15 19:55:11','2026-01-20 22:31:04','2026-01-20 22:31:04'),(4,NULL,NULL,NULL,'Pantalon de Seguridad',19.00,NULL,'productoimg/imagenes/69406ba3c92c3.jpg',1,'2025-12-15 20:12:19','2026-01-20 22:31:01','2026-01-20 22:31:01'),(5,3,NULL,'CAM-001',NULL,25.00,NULL,NULL,1,'2025-12-16 18:30:44','2026-02-26 19:44:29','2026-02-26 19:44:29'),(6,1,NULL,'CHM-002','chemise cuello chino',17.00,NULL,'productoimg/imagenes/6941a6dcf11a1.jpg',1,'2025-12-16 18:37:16','2025-12-16 18:37:16',NULL),(7,2,NULL,'FRN-CEV-001',NULL,12.00,NULL,NULL,1,'2025-12-16 18:57:27','2026-02-26 19:42:05','2026-02-26 19:42:05'),(8,2,5,'FRN-AJR-L-R-001','Franela basica',16.00,'{\"Manga\": \"Larga\", \"Cuello\": \"Redondo\"}','productoimg/imagenes/69a0a285243b5.jpg',1,'2026-02-26 19:44:05','2026-05-07 18:16:32',NULL),(9,3,NULL,'CAM-COL-001','Tela Oxford',15.00,NULL,'productoimg/imagenes/69f7bc4f7c299.jpg',1,'2026-05-03 21:21:19','2026-05-03 21:21:19',NULL),(10,3,NULL,'CAM-CLA-001','Tela Algodon Egipcio',16.00,NULL,'productoimg/imagenes/69f7bcf809142.JPG',1,'2026-05-03 21:24:08','2026-05-03 21:24:08',NULL),(11,9,NULL,'GO-CLA-001','Con Bordado',15.00,NULL,'productoimg/imagenes/69f7bdd0cbe87.jpg',1,'2026-05-03 21:27:44','2026-05-03 21:27:44',NULL),(12,1,3,'CHM-PIQ-C-CLA-CL-001','Tela Pique con 1 bordado',14.00,'{\"Corte\": \"Clasico\", \"Manga\": \"Corta\", \"Cuello\": \"Clásico\"}','productoimg/imagenes/69f7bdeb36a24.jpg',1,'2026-05-03 21:28:11','2026-05-28 23:37:04',NULL),(13,4,10,'PNT-GBD-RIG-001','Jean Jornes',18.00,'{\"Corte\": \"Rígido\"}','productoimg/imagenes/69f7be2758448.jpg',1,'2026-05-03 21:29:11','2026-05-07 17:43:42',NULL),(15,3,8,'CAM-OXF-L-CTB-001','descripcion de ropa',33.00,'{\"Manga\": \"Larga\", \"Cuello\": \"Con Tapa Botones\"}','productoimg/imagenes/69fcdbbf2f5e1.png',1,'2026-05-07 18:36:47','2026-05-07 18:36:47',NULL);
/*!40000 ALTER TABLE `producto` ENABLE KEYS */;

--
-- Table structure for table `producto_atributo_valor`
--

DROP TABLE IF EXISTS `producto_atributo_valor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `producto_atributo_valor` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` bigint unsigned NOT NULL,
  `atributo_valor_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `producto_atributo_valor_producto_id_atributo_valor_id_unique` (`producto_id`,`atributo_valor_id`),
  KEY `producto_atributo_valor_atributo_valor_id_foreign` (`atributo_valor_id`),
  CONSTRAINT `producto_atributo_valor_atributo_valor_id_foreign` FOREIGN KEY (`atributo_valor_id`) REFERENCES `atributo_valor` (`id`) ON DELETE CASCADE,
  CONSTRAINT `producto_atributo_valor_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `producto_atributo_valor`
--

/*!40000 ALTER TABLE `producto_atributo_valor` DISABLE KEYS */;
INSERT INTO `producto_atributo_valor` VALUES (1,13,8,'2026-05-07 17:43:42','2026-05-07 17:43:42'),(4,8,1,'2026-05-07 18:16:32','2026-05-07 18:16:32'),(5,8,6,'2026-05-07 18:16:32','2026-05-07 18:16:32'),(6,15,1,'2026-05-07 18:36:47','2026-05-07 18:36:47'),(7,15,5,'2026-05-07 18:36:47','2026-05-07 18:36:47'),(12,12,2,'2026-05-28 23:37:04','2026-05-28 23:37:04'),(13,12,3,'2026-05-28 23:37:04','2026-05-28 23:37:04'),(14,12,15,'2026-05-28 23:37:04','2026-05-28 23:37:04');
/*!40000 ALTER TABLE `producto_atributo_valor` ENABLE KEYS */;

--
-- Table structure for table `proveedor`
--

DROP TABLE IF EXISTS `proveedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedor` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo_proveedor` enum('natural','juridico') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'juridico',
  `persona_id` bigint unsigned DEFAULT NULL,
  `contacto` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono_contacto` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proveedor_persona_id_foreign` (`persona_id`),
  CONSTRAINT `proveedor_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedor`
--

/*!40000 ALTER TABLE `proveedor` DISABLE KEYS */;
INSERT INTO `proveedor` VALUES (1,'juridico',53,'Juan Pérez','0412-5231234',1,'2025-12-04 18:58:28','2026-05-29 21:00:11',NULL),(2,'juridico',54,'María García','0424890457',1,'2025-12-04 18:58:28','2025-12-04 18:58:28',NULL),(3,'natural',22,NULL,NULL,1,'2026-01-18 20:23:50','2026-01-18 20:23:50',NULL),(4,'juridico',55,'María González','0412-5678901',1,'2026-01-18 20:33:31','2026-01-18 20:33:31',NULL),(5,'juridico',56,'José Rodríguez','0414-3456789',1,'2026-01-18 20:34:28','2026-01-18 20:34:28',NULL),(6,'juridico',57,'Carmen Pérez','0424-9876543',1,'2026-01-18 20:35:48','2026-01-18 20:35:48',NULL),(7,'natural',23,NULL,NULL,1,'2026-01-18 20:37:01','2026-01-18 20:37:01',NULL),(8,'natural',24,NULL,NULL,1,'2026-01-18 20:38:40','2026-01-18 20:38:40',NULL),(9,'natural',25,NULL,NULL,1,'2026-01-18 20:41:05','2026-01-18 20:41:05',NULL),(10,'juridico',58,'Fernando Castillo','0416-7890123',1,'2026-01-18 20:42:44','2026-01-18 20:42:44',NULL),(11,'natural',26,NULL,NULL,1,'2026-01-18 20:43:53','2026-01-18 20:43:53',NULL),(12,'juridico',59,'Ana Beatriz Ramos','0426-5432109',1,'2026-01-18 20:45:00','2026-01-18 20:45:00',NULL),(13,'natural',27,NULL,NULL,1,'2026-01-18 20:57:49','2026-05-29 20:13:27','2026-05-29 20:13:27'),(14,'natural',28,NULL,NULL,1,'2026-01-18 21:52:28','2026-05-29 20:10:20','2026-05-29 20:10:20'),(15,'natural',62,NULL,NULL,1,'2026-04-29 00:25:48','2026-05-29 20:10:07','2026-05-29 20:10:07');
/*!40000 ALTER TABLE `proveedor` ENABLE KEYS */;

--
-- Table structure for table `recovery_attempt`
--

DROP TABLE IF EXISTS `recovery_attempt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recovery_attempt` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` enum('email','preguntas') COLLATE utf8mb4_unicode_ci NOT NULL,
  `resultado` enum('exito','fallo','bloqueado') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `recovery_attempt_user_id_index` (`user_id`),
  KEY `recovery_attempt_email_index` (`email`),
  KEY `recovery_attempt_created_at_index` (`created_at`),
  CONSTRAINT `recovery_attempt_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recovery_attempt`
--

/*!40000 ALTER TABLE `recovery_attempt` DISABLE KEYS */;
INSERT INTO `recovery_attempt` VALUES (1,NULL,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','preguntas','fallo','2026-04-26 21:37:32'),(2,1,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','preguntas','fallo','2026-04-27 02:14:16'),(3,1,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','preguntas','exito','2026-04-27 02:14:32'),(4,NULL,'emman6321@gmail.com','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','preguntas','fallo','2026-04-28 13:17:40'),(5,NULL,'emman6321@gmail.com','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','preguntas','fallo','2026-04-28 13:19:35'),(6,NULL,'emman6321@gmail.com','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','preguntas','fallo','2026-04-28 13:19:42'),(7,NULL,'emman6321@gmail.com','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','preguntas','fallo','2026-04-28 13:20:17');
/*!40000 ALTER TABLE `recovery_attempt` ENABLE KEYS */;

--
-- Table structure for table `talla`
--

DROP TABLE IF EXISTS `talla`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `talla` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Valor interno de talla (Ej: XS, M, Talla Unica)',
  `etiqueta` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Etiqueta visual para UI (Ej: Única)',
  `grupo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Agrupación visual: Única, Numéricas, Letras',
  `orden` int unsigned NOT NULL DEFAULT '0' COMMENT 'Orden de despliegue en UI',
  `activo` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Permite desactivar tallas sin borrarlas',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tallas_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `talla`
--

/*!40000 ALTER TABLE `talla` DISABLE KEYS */;
INSERT INTO `talla` VALUES (1,'Talla Unica','Única','Única',10,1,'2026-02-23 21:43:06','2026-02-23 21:43:06',NULL),(2,'2','2','Numéricas',20,1,'2026-02-23 21:43:06','2026-02-23 21:43:06',NULL),(3,'4','4','Numéricas',21,1,'2026-02-23 21:43:06','2026-02-23 21:43:06',NULL),(4,'6','6','Numéricas',22,1,'2026-02-23 21:43:06','2026-02-23 21:43:06',NULL),(5,'8','8','Numéricas',23,1,'2026-02-23 21:43:06','2026-02-23 21:43:06',NULL),(6,'10','10','Numéricas',24,1,'2026-02-23 21:43:06','2026-02-23 21:43:06',NULL),(7,'12','12','Numéricas',25,1,'2026-02-23 21:43:06','2026-02-23 21:43:06',NULL),(8,'14','14','Numéricas',26,1,'2026-02-23 21:43:06','2026-02-23 21:43:06',NULL),(9,'16','16','Numéricas',27,1,'2026-02-23 21:43:06','2026-02-23 21:43:06',NULL),(10,'XS','XS','Letras',40,1,'2026-02-23 21:43:06','2026-02-23 21:43:06',NULL),(11,'S','S','Letras',41,1,'2026-02-23 21:43:06','2026-02-23 21:43:06',NULL),(12,'M','M','Letras',42,1,'2026-02-23 21:43:06','2026-02-23 21:43:06',NULL),(13,'L','L','Letras',43,1,'2026-02-23 21:43:06','2026-02-23 21:43:06',NULL),(14,'XL','XL','Letras',44,1,'2026-02-23 21:43:06','2026-02-23 21:43:06',NULL),(15,'XXL','XXL','Letras',45,1,'2026-02-23 21:43:06','2026-02-23 21:43:06',NULL);
/*!40000 ALTER TABLE `talla` ENABLE KEYS */;

--
-- Table structure for table `tasa_cambio`
--

DROP TABLE IF EXISTS `tasa_cambio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasa_cambio` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `moneda` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(12,4) NOT NULL,
  `fecha_bcv` date NOT NULL,
  `fuente` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BCV',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tasa_cambio_moneda_fecha_bcv_unique` (`moneda`,`fecha_bcv`),
  KEY `idx_tasa_fecha` (`fecha_bcv`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasa_cambio`
--

/*!40000 ALTER TABLE `tasa_cambio` DISABLE KEYS */;
INSERT INTO `tasa_cambio` VALUES (1,'USD',270.7900,'2025-12-15','BCV (DolarAPI)','2025-12-15 20:47:22','2025-12-15 20:47:22'),(2,'USD',273.5900,'2025-12-16','BCV (DolarAPI)','2025-12-16 15:36:07','2025-12-16 15:36:07'),(3,'USD',279.5600,'2025-12-18','BCV (DolarAPI)','2025-12-18 13:34:20','2025-12-18 13:34:20'),(4,'USD',282.5100,'2025-12-19','BCV (DolarAPI)','2025-12-19 18:34:37','2025-12-19 18:34:37'),(5,'USD',336.4600,'2026-01-14','BCV (DolarAPI)','2026-01-14 23:32:37','2026-01-14 23:32:37'),(6,'USD',339.1500,'2026-01-15','BCV (DolarAPI)','2026-01-15 17:04:21','2026-01-15 17:04:21'),(7,'USD',341.7400,'2026-01-16','BCV (DolarAPI)','2026-01-16 17:18:16','2026-01-16 17:18:16'),(8,'USD',344.5100,'2026-01-17','BCV (DolarAPI)','2026-01-17 04:00:35','2026-01-17 04:00:35'),(9,'USD',344.5100,'2026-01-18','BCV (DolarAPI)','2026-01-18 14:29:14','2026-01-18 14:29:14'),(10,'USD',344.5100,'2026-01-19','BCV (DolarAPI)','2026-01-19 16:28:54','2026-01-19 16:28:54'),(11,'USD',344.5100,'2026-01-20','BCV (DolarAPI)','2026-01-20 19:43:50','2026-01-20 19:43:50'),(12,'USD',352.7063,'2026-01-23','BCV (DolarAPI)','2026-01-23 18:42:46','2026-01-23 18:42:46'),(13,'USD',355.5528,'2026-01-24','BCV (DolarAPI)','2026-01-25 02:37:20','2026-01-25 02:37:20'),(14,'USD',382.6318,'2026-02-09','BCV (DolarAPI)','2026-02-09 16:11:39','2026-02-09 16:11:39'),(15,'USD',396.3674,'2026-02-14','BCV (DolarAPI)','2026-02-14 20:01:56','2026-02-14 20:01:56'),(16,'USD',396.3674,'2026-02-16','BCV (DolarAPI)','2026-02-16 14:41:31','2026-02-16 14:41:31'),(17,'USD',396.3674,'2026-02-17','BCV (DolarAPI)','2026-02-17 18:27:16','2026-02-17 18:27:16'),(18,'USD',398.7456,'2026-02-19','BCV (DolarAPI)','2026-02-19 19:56:33','2026-02-19 19:56:33'),(19,'USD',402.3343,'2026-02-20','BCV (DolarAPI)','2026-02-20 13:09:37','2026-02-20 13:09:37'),(20,'USD',405.3518,'2026-02-22','BCV (DolarAPI)','2026-02-22 15:13:41','2026-02-22 15:13:41'),(21,'USD',405.3518,'2026-02-23','BCV (DolarAPI)','2026-02-23 17:36:41','2026-02-23 17:36:41'),(22,'USD',407.3786,'2026-02-24','BCV (DolarAPI)','2026-02-24 14:34:03','2026-02-24 14:34:03'),(23,'USD',414.0455,'2026-02-26','BCV (DolarAPI)','2026-02-26 14:10:28','2026-02-26 14:10:28'),(24,'USD',417.3579,'2026-02-27','BCV (DolarAPI)','2026-02-27 13:59:09','2026-02-27 13:59:09'),(25,'USD',425.6741,'2026-03-04','BCV (DolarAPI)','2026-03-04 18:24:59','2026-03-04 18:24:59'),(26,'USD',427.9302,'2026-03-05','BCV (DolarAPI)','2026-03-05 14:38:25','2026-03-05 14:38:25'),(27,'USD',431.0113,'2026-03-06','BCV (DolarAPI)','2026-03-06 14:11:54','2026-03-06 14:11:54'),(28,'USD',436.2419,'2026-03-10','BCV (DolarAPI)','2026-03-10 20:47:31','2026-03-10 20:47:31'),(29,'USD',440.9657,'2026-03-12','BCV (DolarAPI)','2026-03-12 23:28:11','2026-03-12 23:28:11'),(30,'USD',446.8048,'2026-03-15','BCV (DolarAPI)','2026-03-15 13:48:19','2026-03-15 13:48:19'),(31,'USD',446.8048,'2026-03-16','BCV (DolarAPI)','2026-03-17 17:26:17','2026-03-17 17:26:17'),(32,'USD',451.5072,'2026-03-18','BCV (DolarAPI)','2026-03-18 14:03:09','2026-03-18 14:03:09'),(33,'USD',455.2547,'2026-03-20','BCV (DolarAPI)','2026-03-20 13:17:33','2026-03-20 13:17:33'),(34,'USD',457.0757,'2026-03-23','BCV (DolarAPI)','2026-03-23 15:54:57','2026-03-23 15:54:57'),(35,'USD',462.6687,'2026-03-25','BCV (DolarAPI)','2026-03-25 18:43:19','2026-03-25 18:43:19'),(36,'USD',475.9583,'2026-04-09','BCV (DolarAPI)','2026-04-09 18:29:02','2026-04-09 18:29:02'),(37,'USD',477.1488,'2026-04-13','BCV (DolarAPI)','2026-04-13 21:42:31','2026-04-13 21:42:31'),(38,'USD',477.6259,'2026-04-14','BCV (DolarAPI)','2026-04-14 12:48:51','2026-04-14 12:48:51'),(39,'USD',481.6989,'2026-04-21','BCV (DolarAPI)','2026-04-21 19:48:19','2026-04-21 19:48:19'),(40,'USD',482.7586,'2026-04-22','BCV (DolarAPI)','2026-04-22 18:27:21','2026-04-22 18:27:21'),(41,'USD',483.8695,'2026-04-24','BCV (DolarAPI)','2026-04-26 20:05:11','2026-04-26 20:05:11'),(42,'USD',485.2251,'2026-04-28','BCV (DolarAPI)','2026-04-28 12:59:20','2026-04-28 12:59:20'),(43,'USD',487.1192,'2026-04-30','BCV (DolarAPI)','2026-04-30 14:19:08','2026-04-30 14:19:08'),(44,'USD',490.0442,'2026-05-05','BCV (DolarAPI)','2026-05-05 15:09:27','2026-05-05 15:09:27'),(45,'USD',496.8301,'2026-05-07','BCV (DolarAPI)','2026-05-07 14:18:30','2026-05-07 14:18:30'),(46,'USD',504.9146,'2026-05-12','BCV (DolarAPI)','2026-05-12 23:34:48','2026-05-12 23:34:48'),(47,'USD',510.7873,'2026-05-14','BCV (DolarAPI)','2026-05-14 14:27:58','2026-05-14 14:27:58'),(48,'USD',520.9142,'2026-05-20','BCV (DolarAPI)','2026-05-21 01:20:31','2026-05-21 01:20:31'),(49,'USD',535.3853,'2026-05-26','BCV (DolarAPI)','2026-05-26 23:18:33','2026-05-26 23:18:33'),(50,'USD',540.0431,'2026-05-27','BCV (DolarAPI)','2026-05-27 05:01:16','2026-05-27 05:01:16'),(51,'USD',544.5794,'2026-05-28','BCV (DolarAPI)','2026-05-28 04:13:27','2026-05-28 04:13:27'),(52,'USD',549.3716,'2026-05-29','BCV (DolarAPI)','2026-05-29 14:28:13','2026-05-29 14:28:13'),(53,'USD',554.4258,'2026-06-01','BCV (DolarAPI)','2026-06-01 17:11:23','2026-06-01 17:11:23'),(54,'USD',557.9741,'2026-06-02','BCV (DolarAPI)','2026-06-02 15:13:27','2026-06-02 15:13:27'),(55,'USD',558.6436,'2026-06-03','BCV (DolarAPI)','2026-06-03 04:08:40','2026-06-03 04:08:40');
/*!40000 ALTER TABLE `tasa_cambio` ENABLE KEYS */;

--
-- Table structure for table `telefono`
--

DROP TABLE IF EXISTS `telefono`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `telefono` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `persona_id` bigint unsigned NOT NULL,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('movil','casa','trabajo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'movil',
  `es_principal` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `telefono_persona_id_index` (`persona_id`),
  CONSTRAINT `telefono_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telefono`
--

/*!40000 ALTER TABLE `telefono` DISABLE KEYS */;
INSERT INTO `telefono` VALUES (1,2,'0426-3412567','movil',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(2,3,'0412-3453314','movil',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(3,4,'0426-1135645','movil',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(4,6,'0412555777','movil',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(5,7,'0424-869334','movil',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(6,8,'0422-344859','movil',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(7,9,'0424-898099','movil',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(8,10,'0422-778456','movil',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(9,11,'0414-5548982','movil',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(10,12,'0412-4436668','movil',1,'2025-12-10 20:29:40','2026-01-17 03:27:27',NULL),(11,14,'0412-3556789','movil',1,'2025-12-10 21:09:58','2025-12-10 21:09:58',NULL),(12,13,'0412-4435673','movil',1,'2025-12-10 21:17:57','2025-12-10 21:17:57',NULL),(13,15,'0412-9288102','movil',1,'2026-01-17 16:51:52','2026-01-17 16:51:52',NULL),(14,16,'0414-5684402','movil',1,'2026-01-17 17:11:09','2026-01-17 17:11:09',NULL),(15,17,'0424-3637623','movil',1,'2026-01-17 22:05:23','2026-01-17 22:05:23',NULL),(16,18,'0414-5684402','movil',1,'2026-01-17 22:31:33','2026-01-17 22:31:33',NULL),(17,19,'0424-1595466','movil',1,'2026-01-18 03:49:00','2026-01-18 03:49:00',NULL),(18,20,'0424-1595466','movil',1,'2026-01-18 03:56:57','2026-01-18 03:56:57',NULL),(20,22,'0412-5238473','movil',1,'2026-01-18 20:23:50','2026-01-18 20:23:50',NULL),(21,23,'0424-5671234','movil',1,'2026-01-18 20:37:01','2026-01-18 20:37:01',NULL),(22,24,'0412-8904567','movil',1,'2026-01-18 20:38:40','2026-01-18 20:38:40',NULL),(23,25,'0414-2345678','movil',1,'2026-01-18 20:41:05','2026-01-18 20:41:05',NULL),(24,26,'0426-3456789','movil',1,'2026-01-18 20:43:53','2026-01-18 20:43:53',NULL),(25,27,'0424-5678901','movil',1,'2026-01-18 20:57:49','2026-01-18 20:57:49',NULL),(26,28,'0424-8901234','movil',1,'2026-01-18 21:52:28','2026-01-18 21:52:28',NULL),(27,29,'0422-2222222','movil',1,'2026-01-19 00:25:34','2026-01-19 00:25:34',NULL),(28,30,'0424-5345463','movil',1,'2026-01-19 03:56:04','2026-01-19 03:56:04',NULL),(29,31,'0424-3442434','movil',1,'2026-01-19 04:01:50','2026-01-19 04:01:50',NULL),(30,32,'0424-5684402','movil',1,'2026-01-19 04:05:33','2026-01-19 04:05:33',NULL),(31,33,'0424-5684402','movil',1,'2026-01-19 04:17:44','2026-01-19 04:17:44',NULL),(32,34,'0424-2222222','movil',1,'2026-01-19 04:26:34','2026-01-19 04:26:34',NULL),(33,35,'0424-4523142','movil',1,'2026-01-19 16:49:16','2026-01-19 16:49:16',NULL),(34,36,'0412-9020671','movil',1,'2026-01-20 01:29:08','2026-01-20 01:29:08',NULL),(35,37,'0412-5235773','movil',1,'2026-01-20 21:23:34','2026-01-20 21:23:34',NULL),(36,38,'0412-3456775','movil',1,'2026-02-22 17:56:13','2026-02-22 17:56:13',NULL),(37,39,'0414-7821345','movil',1,'2026-02-26 19:10:25','2026-02-26 19:10:25',NULL),(38,40,'0424-5567890','movil',1,'2026-02-26 19:10:25','2026-02-26 19:10:25',NULL),(39,42,'0422-2514789','movil',1,'2026-02-26 19:11:43','2026-02-26 20:22:20',NULL),(40,43,'0412-8903456','movil',1,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL),(41,44,'0412-6618900','movil',1,'2026-02-26 19:11:43','2026-02-26 20:21:46',NULL),(42,45,'0416-4567123','movil',1,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL),(43,46,'0424-3345678','movil',1,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL),(44,47,'0422-5551234','movil',1,'2026-02-26 19:11:43','2026-02-26 20:23:03',NULL),(45,48,'0414-9012345','movil',1,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL),(46,49,'0412-6781234','movil',1,'2026-02-26 19:11:43','2026-02-26 19:11:43',NULL),(47,50,'0412-5773592','movil',1,'2026-02-26 19:46:04','2026-02-26 19:46:04',NULL),(48,51,'0424-5049283','movil',1,'2026-03-05 17:17:09','2026-03-05 17:17:09',NULL),(49,53,'0412-555666','trabajo',1,'2026-03-19 18:21:05','2026-03-19 18:21:05',NULL),(50,54,'01-3214567','trabajo',1,'2026-03-19 18:21:05','2026-03-19 18:21:05',NULL),(51,55,'0255-6234567','trabajo',1,'2026-03-19 18:21:06','2026-03-19 18:21:06',NULL),(52,56,'0255-6789012','trabajo',1,'2026-03-19 18:21:06','2026-03-19 18:21:06',NULL),(53,57,'0241-8345678','trabajo',1,'2026-03-19 18:21:06','2026-03-19 18:21:06',NULL),(54,58,'0251-7891234','trabajo',1,'2026-03-19 18:21:06','2026-03-19 18:21:06',NULL),(55,59,'0274-4567890','trabajo',1,'2026-03-19 18:21:06','2026-03-19 18:21:06',NULL),(56,60,'0422-7234564','movil',1,'2026-04-14 21:26:32','2026-04-14 21:26:32',NULL),(57,61,'0424-3452345','movil',1,'2026-04-29 00:22:53','2026-04-29 00:22:53',NULL),(58,62,'0424-3456272','movil',1,'2026-04-29 00:25:48','2026-04-29 00:25:48',NULL),(61,65,'0424-3456865','movil',1,'2026-04-30 20:25:42','2026-04-30 20:25:42',NULL),(62,66,'0412-0567198','movil',1,'2026-05-30 19:47:16','2026-05-30 19:47:16',NULL),(63,67,'0424-5396144','movil',1,'2026-05-30 19:50:52','2026-05-30 19:50:52',NULL),(64,68,'0422-6464295','movil',1,'2026-05-30 19:55:30','2026-05-30 19:55:30',NULL),(65,69,'0424-5348922','movil',1,'2026-05-30 20:00:52','2026-05-30 20:00:52',NULL);
/*!40000 ALTER TABLE `telefono` ENABLE KEYS */;

--
-- Table structure for table `tipo_producto`
--

DROP TABLE IF EXISTS `tipo_producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_producto` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prefijo` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `precio_confeccion` decimal(10,2) NOT NULL DEFAULT '0.00',
  `requiere_tela` tinyint(1) NOT NULL DEFAULT '1',
  `consumo_tela_por_unidad` decimal(8,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipo_producto_codigo_prefijo_unique` (`prefijo`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_producto`
--

/*!40000 ALTER TABLE `tipo_producto` DISABLE KEYS */;
INSERT INTO `tipo_producto` VALUES (1,'Chemise','CHM','Camisas tipo polo con cuello',12.00,1,0.00,'2025-12-16 17:48:48','2026-05-28 23:49:57',NULL),(2,'Franela','FRN','Franelas cuello redondo o V',8.00,1,0.00,'2025-12-16 17:48:48','2026-05-28 23:50:15',NULL),(3,'Camisa','CAM','Camisas formales manga larga/corta',15.00,1,2.00,'2025-12-16 17:48:48','2026-05-28 23:55:13',NULL),(4,'Pantalón','PNT','Pantalones de trabajo o formales',18.00,1,0.00,'2025-12-16 17:48:48','2026-05-07 17:15:29',NULL),(5,'Chaqueta','CHQ','Chaquetas industriales o formales',25.00,1,0.00,'2025-12-16 17:48:48','2026-05-07 17:15:29',NULL),(6,'Overol','OVR','Overoles y monos de trabajo',0.00,1,0.00,'2025-12-16 17:48:48','2025-12-16 17:48:48',NULL),(7,'Uniforme Escolar','ESC','Prendas para uniformes escolares',0.00,1,0.00,'2025-12-16 17:48:48','2025-12-16 17:48:48',NULL),(8,'Accesorio','ACC','Gorras, delantales, chalecos, etc.',0.00,1,0.00,'2025-12-16 17:48:48','2025-12-16 17:48:48',NULL),(9,'Gorra','GO','Clasica',0.00,1,0.00,'2026-05-03 21:27:03','2026-05-03 21:27:03',NULL);
/*!40000 ALTER TABLE `tipo_producto` ENABLE KEYS */;

--
-- Table structure for table `tipo_producto_atributo`
--

DROP TABLE IF EXISTS `tipo_producto_atributo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_producto_atributo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo_producto_id` bigint unsigned NOT NULL,
  `atributo_id` bigint unsigned NOT NULL,
  `es_obligatorio` tinyint(1) NOT NULL DEFAULT '1',
  `orden` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipo_producto_atributo_tipo_producto_id_atributo_id_unique` (`tipo_producto_id`,`atributo_id`),
  KEY `tipo_producto_atributo_atributo_id_foreign` (`atributo_id`),
  CONSTRAINT `tipo_producto_atributo_atributo_id_foreign` FOREIGN KEY (`atributo_id`) REFERENCES `atributo` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tipo_producto_atributo_tipo_producto_id_foreign` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipo_producto` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_producto_atributo`
--

/*!40000 ALTER TABLE `tipo_producto_atributo` DISABLE KEYS */;
INSERT INTO `tipo_producto_atributo` VALUES (1,3,1,1,1,'2026-05-07 17:15:29','2026-05-28 23:55:13'),(2,3,2,1,2,'2026-05-07 17:15:29','2026-05-28 23:55:13'),(3,2,1,1,1,'2026-05-07 17:15:29','2026-05-07 18:33:39'),(4,2,2,1,2,'2026-05-07 17:15:29','2026-05-07 18:33:39'),(5,1,1,1,1,'2026-05-07 17:15:29','2026-05-14 14:31:53'),(6,1,2,1,2,'2026-05-07 17:15:29','2026-05-14 14:31:53'),(7,4,3,1,1,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(8,5,4,1,1,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(9,5,5,1,2,'2026-05-07 17:15:29','2026-05-07 17:15:29'),(10,3,3,1,3,'2026-05-14 14:29:56','2026-05-28 23:55:13'),(11,1,3,1,3,'2026-05-14 14:31:53','2026-05-14 14:31:53');
/*!40000 ALTER TABLE `tipo_producto_atributo` ENABLE KEYS */;

--
-- Table structure for table `tipo_producto_insumo`
--

DROP TABLE IF EXISTS `tipo_producto_insumo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_producto_insumo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo_producto_id` bigint unsigned NOT NULL,
  `insumo_id` bigint unsigned NOT NULL,
  `cantidad_estimada` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipo_producto_insumo_unique` (`tipo_producto_id`,`insumo_id`),
  KEY `tipo_producto_insumo_insumo_id_foreign` (`insumo_id`),
  CONSTRAINT `tipo_producto_insumo_insumo_id_foreign` FOREIGN KEY (`insumo_id`) REFERENCES `insumo` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tipo_producto_insumo_tipo_producto_id_foreign` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipo_producto` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_producto_insumo`
--

/*!40000 ALTER TABLE `tipo_producto_insumo` DISABLE KEYS */;
INSERT INTO `tipo_producto_insumo` VALUES (7,3,2,8.00,'2026-05-28 23:43:33','2026-05-28 23:55:13');
/*!40000 ALTER TABLE `tipo_producto_insumo` ENABLE KEYS */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `persona_id` bigint unsigned DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('Administrador','Supervisor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `recovery_locked_until` timestamp NULL DEFAULT NULL,
  `recovery_failed_attempts` tinyint unsigned NOT NULL DEFAULT '0',
  `recovery_must_reset_questions` tinyint(1) NOT NULL DEFAULT '0',
  `password_reset_by_admin` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `user_persona_id_unique` (`persona_id`),
  CONSTRAINT `user_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,1,'Emmanuel Jesus','admin@gmail.com','Administrador',NULL,'$2y$12$jNY7MYv/rRrJw6BUV8cZHeA/6cH8..T03WCSR4vx6J8iQLRFreWYe','avatars/69a05e0f7203f.png',1,NULL,0,0,0,'l1Uqd8vkYNzLSiPhvyE1cNIMn5EgDltmz6Z42ZMusu8UMvVopRZwf4y2YlSa','2025-12-04 18:58:27','2026-06-02 17:43:36',NULL),(2,2,'Supervisor','supervisor@gmail.com','Supervisor',NULL,'$2y$12$WZ9jnte4F/DkVPbh64iBKOt91FLUDEDRzmYtJYvc6.iwwLhn3wef6',NULL,1,NULL,0,0,0,NULL,'2025-12-04 18:58:28','2026-05-29 21:46:51',NULL),(5,NULL,'Vanessa Diaz','vanessalopez090551@gmail.com','Administrador',NULL,'$2y$12$d0G/88tSU7qJKgmtlYP.ZO95ss9hgFYK8lZF6N9tsRQAsmlansFHC','avatars/69a060ce9e755.png',1,NULL,0,0,0,'Egbv2pBcVPvjVa8jdqGym2kvHdikOyAWaF03sHTyIAY1ZE28cT8ARZkfl6GR','2026-01-15 00:05:33','2026-05-29 21:49:26',NULL),(7,NULL,'Jesus Rodriguez','emman6321@gmail.com','Administrador',NULL,'$2y$12$b9CFjlKZfvn41Ap747aBLOrvO7JJncegQIKYLyfrS8jTeguVdvdhG','avatars/69a8a14e140ef.jpg',1,NULL,0,0,0,'7TL6JVx3otzY5Iku7Gjs6W65sXGZMmCFqW25GgwtGLrGcZupyl4MWDmXgUny','2026-03-04 21:17:02','2026-05-29 21:50:19',NULL),(8,NULL,'Francis','francis@gmail.com','Administrador',NULL,'$2y$12$iBPxm0QiVidpnp4gjx1vgeyZ3gEFE0hP8AUbvMt60gN69/CwrQ/ze',NULL,1,NULL,0,0,0,NULL,'2026-03-19 15:17:38','2026-03-19 15:17:38',NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;

--
-- Table structure for table `user_recovery_question`
--

DROP TABLE IF EXISTS `user_recovery_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_recovery_question` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `pregunta_id` tinyint unsigned NOT NULL,
  `respuesta` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` tinyint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_recovery_question_user_id_orden_unique` (`user_id`,`orden`),
  UNIQUE KEY `user_recovery_question_user_id_pregunta_id_unique` (`user_id`,`pregunta_id`),
  CONSTRAINT `user_recovery_question_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_recovery_question`
--

/*!40000 ALTER TABLE `user_recovery_question` DISABLE KEYS */;
INSERT INTO `user_recovery_question` VALUES (1,1,1,'$2y$12$O.578XXRW1QdIHrZS9Qi9uWz.DCrMwApRih1WNxBLlKOoCTu3p0Cm',1,'2026-04-27 00:01:14','2026-04-27 00:01:14'),(2,1,10,'$2y$12$wCFiXmfG0Lud6r3FM0tItecUYLeK0eHVUlhnJQk0zfh6GwXkRVvtq',2,'2026-04-27 00:01:15','2026-04-27 00:01:15'),(3,1,8,'$2y$12$wOWMCJxPaUsgkGiWnC4fLuPhn89hEbfmuni7VYUWxpMTG9PDPrZVm',3,'2026-04-27 00:01:15','2026-04-27 00:01:15'),(4,7,1,'$2y$12$rbWogqgf.62EtuA/96d63uyMg8VX1ozBK.OYkVQmvNYUaVUxomHSC',1,'2026-05-30 20:21:58','2026-05-30 20:21:58'),(5,7,10,'$2y$12$xQkTBfAABVAfUzWvZzIbe.6Qj.c2C0G.szxfPOOfOMB85EzJbntHy',2,'2026-05-30 20:21:58','2026-05-30 20:21:58'),(6,7,6,'$2y$12$mIB5WJ9dH0H/dvf6cZgIgOCkJi4/WvWZKGfZ4Zl286n3hn4.wEAMW',3,'2026-05-30 20:21:58','2026-05-30 20:21:58');
/*!40000 ALTER TABLE `user_recovery_question` ENABLE KEYS */;

--
-- Dumping routines for database 'sistema_atlantico5'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-03 21:02:57
