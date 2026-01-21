-- MySQL dump 10.13  Distrib 8.0.44, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: sistema_atlantico4
-- ------------------------------------------------------
-- Server version	8.0.44-0ubuntu0.24.04.2

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
-- Table structure for table `banco`
--

DROP TABLE IF EXISTS `banco`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banco` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bancos_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banco`
--

/*!40000 ALTER TABLE `banco` DISABLE KEYS */;
INSERT INTO `banco` VALUES (1,'Banco de Venezuela','2026-01-19 19:27:19','2026-01-19 19:27:19'),(2,'Banco Mercantil','2026-01-19 19:27:19','2026-01-19 19:27:19'),(3,'Banco Provincial','2026-01-19 19:27:19','2026-01-19 19:27:19'),(4,'Banesco','2026-01-19 19:27:19','2026-01-19 19:27:19'),(5,'Bancaribe','2026-01-19 19:27:19','2026-01-19 19:27:19'),(6,'BOD','2026-01-19 19:27:19','2026-01-19 19:27:19'),(7,'Banco Caroní','2026-01-19 19:27:19','2026-01-19 19:27:19'),(8,'Banco Plaza','2026-01-19 19:27:19','2026-01-19 19:27:19'),(9,'BFC Banco Fondo Común','2026-01-19 19:27:19','2026-01-19 19:27:19'),(10,'100% Banco','2026-01-19 19:27:19','2026-01-19 19:27:19'),(11,'DelSur','2026-01-19 19:27:19','2026-01-19 19:27:19'),(12,'Banco del Tesoro','2026-01-19 19:27:19','2026-01-19 19:27:19'),(13,'Bancrecer','2026-01-19 19:27:19','2026-01-19 19:27:19'),(14,'Banco Activo','2026-01-19 19:27:19','2026-01-19 19:27:19'),(15,'Bancamiga','2026-01-19 19:27:19','2026-01-19 19:27:19'),(16,'Banplus','2026-01-19 19:27:19','2026-01-19 19:27:19'),(17,'Banco Bicentenario','2026-01-19 19:27:19','2026-01-19 19:27:19'),(18,'BNC Nacional de Crédito','2026-01-19 19:27:19','2026-01-19 19:27:19'),(19,'Zelle','2026-01-19 19:27:19','2026-01-19 19:27:19'),(20,'PayPal','2026-01-19 19:27:19','2026-01-19 19:27:19'),(21,'Binance','2026-01-19 19:27:19','2026-01-19 19:27:19'),(22,'Efectivo','2026-01-19 19:27:19','2026-01-19 19:27:19');
/*!40000 ALTER TABLE `banco` ENABLE KEYS */;

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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cliente`
--

/*!40000 ALTER TABLE `cliente` DISABLE KEYS */;
INSERT INTO `cliente` VALUES (1,3,'2025-12-04 19:37:44','2026-01-16 21:57:40','2026-01-16 21:57:40','natural',1),(2,6,'2025-12-05 19:22:15','2026-01-16 21:57:58','2026-01-16 21:57:58','natural',1),(3,7,'2025-12-08 19:23:05','2026-01-16 21:57:47','2026-01-16 21:57:47','natural',1),(4,8,'2025-12-08 20:04:57','2025-12-09 18:51:56','2025-12-09 18:51:56','natural',1),(5,9,'2025-12-08 20:19:32','2025-12-09 18:16:57','2025-12-09 18:16:57','natural',1),(6,10,'2025-12-09 18:54:35','2025-12-09 18:57:12','2025-12-09 18:57:12','natural',1),(7,11,'2025-12-10 18:09:48','2026-01-16 21:57:54','2026-01-16 21:57:54','natural',1),(8,12,'2025-12-10 20:29:40','2026-01-17 22:34:46',NULL,'natural',1),(9,15,'2026-01-17 16:51:52','2026-01-17 16:51:52',NULL,'natural',1),(10,16,'2026-01-17 17:11:09','2026-01-17 17:11:09',NULL,'natural',1),(11,17,'2026-01-17 22:05:23','2026-01-17 22:10:55','2026-01-17 22:10:55','natural',1),(12,18,'2026-01-17 22:31:33','2026-01-17 22:31:33',NULL,'natural',1),(13,19,'2026-01-18 03:49:00','2026-01-18 03:54:29','2026-01-18 03:54:29','natural',1),(14,20,'2026-01-18 03:56:57','2026-01-18 03:56:57',NULL,'natural',1),(15,29,'2026-01-19 00:25:34','2026-01-19 00:25:34',NULL,'natural',1),(16,30,'2026-01-19 03:56:04','2026-01-19 04:20:07','2026-01-19 04:20:07','natural',1),(17,31,'2026-01-19 04:01:50','2026-01-19 04:20:11','2026-01-19 04:20:11','natural',1),(18,32,'2026-01-19 04:05:33','2026-01-19 04:20:04','2026-01-19 04:20:04','natural',1),(19,33,'2026-01-19 04:17:44','2026-01-19 04:20:00','2026-01-19 04:20:00','natural',1),(20,34,'2026-01-19 04:26:34','2026-01-19 04:26:34',NULL,'natural',1),(21,35,'2026-01-19 16:49:16','2026-01-19 16:49:16',NULL,'natural',1),(22,36,'2026-01-20 01:29:08','2026-01-20 01:29:08',NULL,'natural',1),(23,37,'2026-01-20 21:23:34','2026-01-20 21:23:34',NULL,'natural',1);
/*!40000 ALTER TABLE `cliente` ENABLE KEYS */;

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
  `estado` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `user_id` bigint unsigned NOT NULL,
  `prioridad` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Normal',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cotizaciones_user_id_foreign` (`user_id`),
  KEY `cotizacion_cliente_id_foreign` (`cliente_id`),
  CONSTRAINT `cotizacion_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`),
  CONSTRAINT `cotizaciones_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cotizacion`
--

/*!40000 ALTER TABLE `cotizacion` DISABLE KEYS */;
INSERT INTO `cotizacion` VALUES (1,2,'2025-11-11','2025-12-31','Aprobada',59.80,1,'Normal','2025-12-08 18:51:54','2026-01-17 16:56:05','2026-01-17 16:56:05'),(2,3,'2025-11-10','2026-01-01','Aprobada',599.00,1,'Normal','2025-12-08 19:24:18','2026-01-17 16:56:10','2026-01-17 16:56:10'),(3,5,'2025-10-09','2025-12-24','Aprobada',599.00,1,'Normal','2025-12-08 20:21:31','2026-01-17 16:56:16','2026-01-17 16:56:16'),(4,6,'2025-11-13','2026-01-30','Aprobada',2396.00,1,'Normal','2025-12-09 18:56:23','2026-01-17 16:48:18','2026-01-17 16:48:18'),(5,7,'2025-11-06','2025-12-28','Pendiente',897.00,1,'Normal','2025-12-10 18:11:52','2026-01-17 16:48:14','2026-01-17 16:48:14'),(6,7,'2025-12-10','2025-12-31','Aprobada',419.00,1,'Normal','2025-12-11 00:29:48','2026-01-16 22:02:54','2026-01-16 22:02:54'),(7,1,'2025-12-19','2025-12-30','Convertida',34.00,1,'Normal','2025-12-19 20:15:18','2026-01-16 22:02:37','2026-01-16 22:02:37'),(8,9,'2026-01-17','2026-01-24','Cancelado',119.60,5,'Normal','2026-01-17 16:53:23','2026-01-17 22:48:34',NULL),(9,12,'2026-01-19','2026-01-22','Aprobada',119.60,1,'Normal','2026-01-17 17:11:33','2026-01-19 18:53:19',NULL),(10,8,'2026-01-17','2026-01-22','Aprobado',9956.70,1,'Normal','2026-01-17 22:35:19','2026-01-19 04:36:58','2026-01-19 04:36:58'),(11,14,'2026-01-18','2026-01-28','Convertida',388.70,1,'Normal','2026-01-18 22:11:33','2026-01-19 03:42:07',NULL),(12,9,'2026-01-19','2026-01-20','Pendiente',50.00,5,'Normal','2026-01-18 23:44:02','2026-01-19 04:34:46',NULL),(13,15,'2026-01-15','2026-01-23','Convertida',25.00,1,'Normal','2026-01-19 00:26:43','2026-01-19 19:24:34',NULL),(14,9,'2026-01-19','2026-01-22','Convertida',1317.80,5,'Normal','2026-01-19 04:25:53','2026-01-19 19:13:45',NULL),(15,20,'2026-01-19','2026-01-22','Aprobada',1317.80,1,'Normal','2026-01-19 04:26:49','2026-01-20 21:38:04',NULL),(16,9,'2026-01-19','2026-01-22','Convertida',374.00,1,'Normal','2026-01-19 05:10:13','2026-01-20 21:34:18',NULL),(17,15,'2026-01-19','2026-01-23','Convertida',2963.40,1,'Normal','2026-01-19 05:15:58','2026-01-19 20:16:20',NULL),(18,21,'2026-01-19','2026-01-21','Convertida',627.00,1,'Normal','2026-01-19 16:49:36','2026-01-19 19:51:33',NULL),(19,9,'2026-01-19','2026-01-23','Convertida',986.70,1,'Normal','2026-01-19 16:51:55','2026-01-19 19:32:56',NULL),(20,22,'2026-01-19','2026-01-30','Convertida',29.90,1,'Normal','2026-01-20 01:35:43','2026-01-20 21:21:33',NULL),(21,23,'2026-01-20','2026-01-31','Convertida',51.00,1,'Normal','2026-01-20 21:25:00','2026-01-20 21:25:39',NULL),(22,23,'2026-01-19','2026-01-28','Convertida',358.80,1,'Normal','2026-01-20 21:31:53','2026-01-20 21:32:04',NULL),(23,23,'2026-01-21','2026-01-31','Convertida',190.00,1,'Normal','2026-01-20 21:39:00','2026-01-20 21:39:09',NULL),(24,14,'2026-01-20','2026-01-30','Convertida',29.90,1,'Normal','2026-01-20 21:45:35','2026-01-20 21:45:49',NULL),(25,23,'2026-01-20','2026-01-31','Convertida',29.90,1,'Normal','2026-01-20 21:51:41','2026-01-20 21:52:03',NULL),(26,23,'2026-01-20','2026-01-29','Convertida',59.80,1,'Normal','2026-01-20 21:56:09','2026-01-20 21:56:29',NULL),(27,23,'2026-01-20','2026-02-03','Convertida',29.90,1,'Normal','2026-01-20 22:01:32','2026-01-20 22:01:40',NULL),(28,23,'2026-01-19','2026-01-28','Convertida',358.80,1,'Normal','2026-01-20 22:21:26','2026-01-20 22:21:37',NULL);
/*!40000 ALTER TABLE `cotizacion` ENABLE KEYS */;

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
  `cantidad` int NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `lleva_bordado` tinyint(1) NOT NULL DEFAULT '0',
  `nombre_logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ubicacion_logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cantidad_logo` int DEFAULT NULL,
  `color` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `talla` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detalle_cotizaciones_cotizacion_id_foreign` (`cotizacion_id`),
  KEY `detalle_cotizaciones_producto_id_foreign` (`producto_id`),
  CONSTRAINT `detalle_cotizaciones_cotizacion_id_foreign` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizacion` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_cotizaciones_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_cotizacion`
--

/*!40000 ALTER TABLE `detalle_cotizacion` DISABLE KEYS */;
INSERT INTO `detalle_cotizacion` VALUES (3,1,1,2,'Promo Utah Valley University',1,'Turning Point USA','Frontal Izquierdo',1,NULL,'S',29.90,'2025-12-08 19:11:06','2025-12-08 19:11:06'),(4,2,2,10,'Camisa para Oracle',1,'Oracle','Frontal Izquierdo',1,NULL,'M',59.90,'2025-12-08 19:24:18','2025-12-08 19:24:18'),(5,3,2,10,'Camisas Corte Clasico',1,'maxcell corporation logo','Frontal Izquierdo',1,NULL,'XL',59.90,'2025-12-08 20:21:31','2025-12-08 20:21:31'),(6,4,2,40,'Camisas Corte Columbia para Empresa de GPU\'S',1,'nvidia logo','Frontal Izquierdo',1,NULL,'M',59.90,'2025-12-09 18:56:23','2025-12-09 18:56:23'),(8,5,1,30,'Cotizacion para empresa de IA',1,'Palantir Logo','Frontal Izquierdo',1,NULL,'S',29.90,'2025-12-10 23:45:22','2025-12-10 23:45:22'),(9,6,1,6,'Chemise Clasica para empresa de IA',1,'Logo de IA','Frontal Izquierdo',1,NULL,'6',29.90,'2025-12-11 00:29:48','2025-12-11 00:29:48'),(10,6,2,4,'Camisa para empresa',1,'Logo de Camisa','Frontal Derecho',1,NULL,'L',59.90,'2025-12-11 00:29:48','2025-12-11 00:29:48'),(11,7,6,2,NULL,1,'Logo UPTP','Frontal Izquierdo',1,NULL,'M',17.00,'2025-12-19 20:15:18','2025-12-19 20:15:18'),(21,8,1,4,'null',1,'Logo UPTP','Frontal Izquierdo',1,NULL,'XS',29.90,'2026-01-17 22:48:34','2026-01-17 22:48:34'),(30,11,1,13,'Chemises Unicolor',1,'Lacoste','Frontal Izquierdo',1,NULL,'M',29.90,'2026-01-19 02:32:34','2026-01-19 02:32:34'),(31,10,1,333,'null',0,'null',NULL,1,NULL,'Talla Unica',29.90,'2026-01-19 02:34:08','2026-01-19 02:34:08'),(34,12,5,2,'null',1,'uptp','Frontal Izquierdo',1,NULL,'XS',25.00,'2026-01-19 04:34:46','2026-01-19 04:34:46'),(36,15,2,22,'null',0,'null',NULL,1,NULL,'4',59.90,'2026-01-19 04:37:45','2026-01-19 04:37:45'),(37,16,6,22,'weqew',1,'uptp','izquierda',1,NULL,'XS',17.00,'2026-01-19 05:10:13','2026-01-19 05:10:13'),(40,14,2,22,'null',0,'null',NULL,1,NULL,'4',59.90,'2026-01-19 05:24:59','2026-01-19 05:24:59'),(44,9,1,4,'null',0,'null',NULL,1,NULL,'2',29.90,'2026-01-19 18:53:19','2026-01-19 18:53:19'),(45,13,5,1,'null',0,'null',NULL,1,NULL,'XS',25.00,'2026-01-19 18:53:47','2026-01-19 18:53:47'),(46,19,1,33,'null',0,'null',NULL,1,NULL,'2',29.90,'2026-01-19 19:31:03','2026-01-19 19:31:03'),(47,18,4,33,'null',0,'null',NULL,1,NULL,'2',19.00,'2026-01-19 19:51:06','2026-01-19 19:51:06'),(50,17,1,33,'scs',0,'null',NULL,1,NULL,'Talla Unica',29.90,'2026-01-19 20:15:52','2026-01-19 20:15:52'),(51,17,2,33,'null',0,'null',NULL,1,NULL,'M',59.90,'2026-01-19 20:15:52','2026-01-19 20:15:52'),(54,20,1,1,'faltan medidas',1,'Paica','Frontal izquierdo',1,NULL,'L',29.90,'2026-01-20 01:41:41','2026-01-20 01:41:41'),(55,21,6,3,'Chemises para tienda de servicio tecnico',1,'Arzatek','Frontal Izquierdo',1,NULL,'M',17.00,'2026-01-20 21:25:00','2026-01-20 21:25:00'),(56,22,1,12,'Dotacion',1,'TROC','Frontal Izquierdo',1,NULL,'12',29.90,'2026-01-20 21:31:53','2026-01-20 21:31:53'),(57,23,4,10,'10',1,'Paica','Pierna Izquierda',1,NULL,'Talla Unica',19.00,'2026-01-20 21:39:00','2026-01-20 21:39:00'),(58,24,1,1,'Bera',1,'Bera','Frontal Izquierdo',1,NULL,'M',29.90,'2026-01-20 21:45:35','2026-01-20 21:45:35'),(59,25,1,1,'urgente',1,'Arzatek','Frontal Izquierdo',1,NULL,'S',29.90,'2026-01-20 21:51:41','2026-01-20 21:51:41'),(60,26,1,2,'urgente',1,'Arzatek','Frontal Izquierdo',1,NULL,'M',29.90,'2026-01-20 21:56:09','2026-01-20 21:56:09'),(61,27,1,1,'si',0,NULL,NULL,1,NULL,'M',29.90,'2026-01-20 22:01:32','2026-01-20 22:01:32'),(62,28,1,12,'pedido urgente',0,NULL,NULL,1,NULL,'S',29.90,'2026-01-20 22:21:26','2026-01-20 22:21:26');
/*!40000 ALTER TABLE `detalle_cotizacion` ENABLE KEYS */;

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
  CONSTRAINT `detalle_orden_insumos_insumo_id_foreign` FOREIGN KEY (`insumo_id`) REFERENCES `insumo` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_orden_insumos_orden_produccion_id_foreign` FOREIGN KEY (`orden_produccion_id`) REFERENCES `orden_produccion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_orden_insumo`
--

/*!40000 ALTER TABLE `detalle_orden_insumo` DISABLE KEYS */;
INSERT INTO `detalle_orden_insumo` VALUES (3,4,1,2.00,0.00,'2025-12-18 18:00:17','2025-12-18 18:00:17'),(4,5,1,2.00,0.00,'2025-12-19 18:44:18','2025-12-19 18:44:18'),(6,6,1,2.00,0.00,'2026-01-16 18:10:26','2026-01-16 18:10:26'),(9,7,2,4.00,0.00,'2026-01-17 17:35:23','2026-01-17 17:35:23');
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
  `cantidad` int NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `lleva_bordado` tinyint(1) NOT NULL DEFAULT '0',
  `nombre_logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `talla` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ubicacion_logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cantidad_logo` int DEFAULT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detalle_pedidos_pedido_id_foreign` (`pedido_id`),
  KEY `detalle_pedidos_producto_id_foreign` (`producto_id`),
  CONSTRAINT `detalle_pedidos_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_pedidos_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_pedido`
--

/*!40000 ALTER TABLE `detalle_pedido` DISABLE KEYS */;
INSERT INTO `detalle_pedido` VALUES (3,3,5,1,'con tapa botones',1,'Los Caminos','Blanco','L','Frontal Izquierdo',1,25.00,'2025-12-18 17:45:32','2025-12-18 17:45:32'),(7,4,6,2,NULL,0,NULL,'Blanco','6',NULL,NULL,17.00,'2026-01-16 18:00:46','2026-01-16 18:00:46'),(8,2,5,2,'manga corta',1,'Palantir Logo','Blanco','M','Frontal Izquierdo',1,25.00,'2026-01-16 18:07:36','2026-01-16 18:07:36'),(9,1,1,1,'Chemises clasicas para empresa de Inteligencia Artificial',1,'Palantir Logo','azul','L','Frontal Izquierdo',1,29.90,'2026-01-16 18:07:49','2026-01-16 18:07:49'),(12,6,1,4,'null',0,'null','Blanco','2',NULL,1,29.90,'2026-01-17 22:34:12','2026-01-17 22:34:12'),(13,5,1,4,'null',1,'Logo UPTP','Blanco','XS','Frontal Izquierdo',1,29.90,'2026-01-17 22:34:28','2026-01-17 22:34:28'),(15,7,1,4,'null',0,'null','Blanco','2',NULL,1,29.90,'2026-01-17 23:23:25','2026-01-17 23:23:25'),(16,8,1,13,'Chemises Unicolor',1,'Lacoste','Blanco','M','Frontal Izquierdo',1,29.90,'2026-01-19 03:37:08','2026-01-19 03:37:08'),(17,9,1,13,'Chemises Unicolor',1,'Lacoste','Blanco','M','Frontal Izquierdo',1,29.90,'2026-01-19 03:42:07','2026-01-19 03:42:07'),(18,10,2,22,'null',0,'null','Blanco','4',NULL,1,59.90,'2026-01-19 04:36:20','2026-01-19 04:36:20'),(19,11,2,22,'null',0,NULL,NULL,'4',NULL,NULL,59.90,'2026-01-19 19:13:45','2026-01-19 19:13:45'),(20,12,5,1,'null',0,NULL,NULL,'XS',NULL,NULL,25.00,'2026-01-19 19:24:34','2026-01-19 19:24:34'),(21,13,1,33,'null',0,NULL,NULL,'2',NULL,NULL,29.90,'2026-01-19 19:32:56','2026-01-19 19:32:56'),(22,14,4,33,'null',0,NULL,NULL,'2',NULL,NULL,19.00,'2026-01-19 19:51:33','2026-01-19 19:51:33'),(23,15,1,33,'scs',0,NULL,NULL,'Talla Unica',NULL,NULL,29.90,'2026-01-19 20:16:20','2026-01-19 20:16:20'),(24,15,2,33,'null',0,NULL,NULL,'M',NULL,NULL,59.90,'2026-01-19 20:16:20','2026-01-19 20:16:20'),(25,16,1,1,'faltan medidas',1,'Paica',NULL,'L','Frontal izquierdo',1,29.90,'2026-01-20 21:21:33','2026-01-20 21:21:33'),(26,17,6,3,'Chemises para tienda de servicio tecnico',1,'Arzatek',NULL,'M','Frontal Izquierdo',1,17.00,'2026-01-20 21:25:39','2026-01-20 21:25:39'),(27,18,1,12,'Dotacion',1,'TROC',NULL,'12','Frontal Izquierdo',1,29.90,'2026-01-20 21:32:04','2026-01-20 21:32:04'),(28,19,6,22,'weqew',1,'uptp',NULL,'XS','izquierda',1,17.00,'2026-01-20 21:34:18','2026-01-20 21:34:18'),(29,20,4,10,'10',1,'Paica',NULL,'Talla Unica','Pierna Izquierda',1,19.00,'2026-01-20 21:39:09','2026-01-20 21:39:09'),(30,21,1,1,'Bera',1,'Bera',NULL,'M','Frontal Izquierdo',1,29.90,'2026-01-20 21:45:49','2026-01-20 21:45:49'),(31,22,1,1,'urgente',1,'Arzatek',NULL,'S','Frontal Izquierdo',1,29.90,'2026-01-20 21:52:03','2026-01-20 21:52:03'),(32,23,1,2,'urgente',1,'Arzatek',NULL,'M','Frontal Izquierdo',1,29.90,'2026-01-20 21:56:29','2026-01-20 21:56:29'),(33,24,1,1,'si',0,NULL,NULL,'M',NULL,1,29.90,'2026-01-20 22:01:40','2026-01-20 22:01:40'),(35,25,1,12,'pedido urgente',0,NULL,'verde','S',NULL,1,29.90,'2026-01-20 22:22:03','2026-01-20 22:22:03');
/*!40000 ALTER TABLE `detalle_pedido` ENABLE KEYS */;

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
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `direccion`
--

/*!40000 ALTER TABLE `direccion` DISABLE KEYS */;
INSERT INTO `direccion` VALUES (1,2,'La Goajira','acarigua',NULL,'casa',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(2,3,'Washington DC','Washington',NULL,'casa',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(3,4,'Urbanización Fundación Mendoza\r\nAvenida 7, Calle Principal','Acarigua, Municipio Paez',NULL,'casa',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(4,6,'Wall Street','New York NY',NULL,'casa',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(5,7,'Sillycon Valley','California',NULL,'casa',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(6,8,'Dayton','Ohio',NULL,'casa',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(7,9,'Headington Hill Hall, Reino Unido','Oxford',NULL,'casa',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(8,10,'San Tomas Expressway','Santa Clara, CA',NULL,'casa',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(9,11,'Denver','Colorado',NULL,'casa',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(10,12,'Crescent Park, 11 Avenue Palo Alto','Araure','Portuguesa','casa',1,'2025-12-10 20:29:40','2026-01-18 23:37:09',NULL),(11,14,'Avenue 10','Dallas',NULL,'casa',1,'2025-12-10 21:09:58','2025-12-10 21:09:58',NULL),(12,13,'villas','acarigua',NULL,'casa',1,'2025-12-10 21:17:57','2025-12-10 21:17:57',NULL),(13,15,'Urb prados del sol','Araure',NULL,'casa',1,'2026-01-17 16:51:52','2026-01-17 16:51:52',NULL),(14,16,'prados del sol','araure',NULL,'casa',1,'2026-01-17 17:11:09','2026-01-17 17:11:09',NULL),(15,18,'Urb prados del sol','Araure',NULL,'casa',1,'2026-01-17 22:31:33','2026-01-17 22:31:33',NULL),(17,20,'Urb. Los Cortijos','Páez','Portuguesa','casa',1,'2026-01-18 03:56:57','2026-01-18 03:56:57',NULL),(19,22,'Avenida los Pescadores calle 5','Araure','Portuguesa','trabajo',1,'2026-01-18 20:23:50','2026-01-18 20:23:50',NULL),(20,23,'Urb. Los Pinos, Calle 3, Casa 15, Acarigua','Páez','Portuguesa','trabajo',1,'2026-01-18 20:37:01','2026-01-18 20:37:01',NULL),(21,24,'Av. Principal, Edif. Sol, Apto 4B, Araure','Araure','Portuguesa','trabajo',1,'2026-01-18 20:38:40','2026-01-18 20:38:40',NULL),(22,25,'carlossilva@gmail.com','Guanare','Portuguesa','trabajo',1,'2026-01-18 20:41:05','2026-01-18 20:41:05',NULL),(23,26,'Urb. El Recreo, Calle 10, Casa 8, Barinas','Barinas','Barinas','trabajo',1,'2026-01-18 20:43:53','2026-01-18 20:43:53',NULL),(24,27,'Urb. Los Samanes, Calle 5, Casa 12, Sector Centro','Ospino','Portuguesa','trabajo',1,'2026-01-18 20:57:49','2026-01-18 20:57:49',NULL),(25,28,'Calle 8, Casa 22, Sector La Barinesa, Acarigua','Páez','Portuguesa','trabajo',1,'2026-01-18 21:52:28','2026-01-18 21:52:28',NULL),(26,29,'Urb prados del sol','Araure','Portuguesa','casa',1,'2026-01-19 00:25:34','2026-01-19 00:25:34',NULL),(27,30,'agua clar','Araure','Portuguesa','casa',1,'2026-01-19 03:56:04','2026-01-19 03:56:04',NULL),(28,31,'agua','Araure','Portuguesa','casa',1,'2026-01-19 04:01:50','2026-01-19 04:01:50',NULL),(29,32,'Urb prados del sol','Araure','Portuguesa','casa',1,'2026-01-19 04:05:33','2026-01-19 04:05:33',NULL),(30,33,'Urb prados del sol','Araure','Portuguesa','casa',1,'2026-01-19 04:17:44','2026-01-19 04:17:44',NULL),(31,34,'Urb prados del sol','Esteller','Portuguesa','casa',1,'2026-01-19 04:26:34','2026-01-19 04:26:34',NULL),(32,35,'Urb prados del sol','Araure','Portuguesa','casa',1,'2026-01-19 16:49:16','2026-01-19 16:49:16',NULL),(33,36,'Urb. villas del pilar','Araure','Portuguesa','casa',1,'2026-01-20 01:29:08','2026-01-20 01:29:08',NULL),(34,37,'Fundacion Mendoza','Páez','Portuguesa','casa',1,'2026-01-20 21:23:34','2026-01-20 21:23:34',NULL);
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
  `cargo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `departamento` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `salario` decimal(10,2) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `empleado_persona_id_unique` (`persona_id`),
  UNIQUE KEY `empleado_codigo_empleado_unique` (`codigo_empleado`),
  CONSTRAINT `empleado_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empleado`
--

/*!40000 ALTER TABLE `empleado` DISABLE KEYS */;
INSERT INTO `empleado` VALUES (1,2,'EMP-001','2025-12-04','Supervisor de Producción','Produccion',NULL,1,'2025-12-04 19:45:19','2026-01-17 05:25:17','2026-01-17 05:25:17'),(2,3,'EMP-002','2025-04-07','Supervisor','Administracion',897000.00,1,'2025-12-04 20:19:19','2026-01-16 21:58:15','2026-01-16 21:58:15'),(3,4,'EMP-003','2025-09-29','Cortador','Produccion',NULL,1,'2025-12-05 20:14:04','2025-12-05 20:14:04',NULL),(4,13,'EMP-004','2025-05-08','Limpieza','Produccion',NULL,1,'2025-12-10 20:57:36','2025-12-10 20:57:36',NULL),(5,14,'EMP-005','2025-12-10','Supervisor 2','Produccion',NULL,1,'2025-12-10 21:09:58','2026-01-16 21:58:10','2026-01-16 21:58:10');
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
  `tipo` enum('Tela','Hilo','Botón','Cierre','Etiqueta','Otro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `unidad_medida` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `costo_unitario` decimal(10,2) NOT NULL,
  `stock_actual` decimal(10,2) NOT NULL,
  `stock_minimo` decimal(10,2) NOT NULL,
  `proveedor_id` bigint unsigned DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `insumos_proveedor_id_foreign` (`proveedor_id`),
  CONSTRAINT `insumos_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedor` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `insumo`
--

/*!40000 ALTER TABLE `insumo` DISABLE KEYS */;
INSERT INTO `insumo` VALUES (1,'Tela Algodón Pima','Tela','Metro',15.50,985.00,200.00,1,1,'2025-12-04 18:58:28','2026-01-17 17:35:36','2026-01-17 17:35:36'),(2,'Botón Nacar 18mm','Botón','Unidad',0.50,4981.00,1000.00,2,1,'2025-12-04 18:58:28','2026-01-19 04:36:20',NULL),(3,'Pique','Tela','Kilos',50.00,0.00,5.00,1,1,'2025-12-11 00:39:02','2026-01-19 00:01:47',NULL),(4,'microdurazno','Hilo','43',29.00,40.00,39.00,2,1,'2026-01-14 23:55:14','2026-01-17 05:19:57','2026-01-17 05:19:57'),(5,'Jersey','Tela','Kg',3.00,100.00,10.00,1,1,'2026-01-20 20:36:23','2026-01-20 20:38:10',NULL);
/*!40000 ALTER TABLE `insumo` ENABLE KEYS */;

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
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2025_03_01_000000_create_sistema_produccion_tables',1),(6,'2025_06_14_091624_create_pedidos_table',1),(7,'2025_06_14_091726_create_detalle_pedidos_table',1),(8,'2025_06_14_094205_add_fecha_entrega_estimada_to_pedidos_table',1),(9,'2025_06_14_100214_add_rif_to_pedidos_table',1),(10,'2025_06_14_102229_remove_unique_rif_from_pedidos_table',1),(11,'2025_06_14_103232_rename_rif_to_ci_rif_in_pedidos_table',1),(12,'2025_06_14_112859_add_description_and_logo_fields_to_detalle_pedidos_table',1),(13,'2025_06_14_114729_add_talla_and_color_to_detalle_pedidos_table',1),(14,'2025_06_14_115649_update_talla_enum_in_detalle_pedidos_table',1),(15,'2025_06_14_123551_force_update_talla_enum_in_detalle_pedidos_table',1),(16,'2025_06_14_210039_create_detalle_pedido_insumo_table',1),(17,'2025_06_15_191252_create_bancos_table',1),(18,'2025_06_15_191339_add_payment_fields_to_pedidos_table',1),(19,'2025_06_19_143226_create_clientes_table',1),(20,'2025_06_19_143359_add_cliente_id_to_pedidos_table',1),(21,'2025_06_20_000001_create_cotizaciones_table',1),(22,'2025_06_20_000002_create_detalle_cotizaciones_table',1),(23,'2025_06_21_112333_add_deleted_at_to_clientes_table',1),(24,'2025_06_26_221106_remove_prioridad_column_from_cotizaciones_table',1),(25,'2025_12_04_134221_update_user_role_enum',1),(26,'2025_12_04_150028_rename_all_tables_to_singular_final',2),(27,'2025_12_04_153326_add_missing_columns_to_cliente_table',3),(28,'2025_12_04_154406_create_persona_table',4),(29,'2025_12_04_154408_create_empleado_table',4),(30,'2025_12_04_154409_add_persona_id_to_user_table',4),(31,'2025_12_04_154448_migrate_users_to_persona',4),(32,'2025_12_04_154449_create_empleados_from_supervisores',4),(33,'2025_12_05_165423_rename_ruc_to_rif_in_proveedor_table',5),(34,'2025_12_08_151400_add_cliente_id_to_cotizacion',6),(36,'2025_12_08_153400_normalize_cotizacion_remove_redundant_cliente_fields',7),(37,'2025_12_08_154900_normalize_cliente_with_persona',8),(38,'2025_12_09_170406_remove_payment_columns_from_cotizacion_table',9),(39,'2025_12_10_143835_create_telefono_table',10),(40,'2025_12_10_144011_create_direccion_table',10),(41,'2025_12_10_144137_migrate_telefono_direccion_data_from_persona',10),(42,'2025_12_10_164505_remove_telefono_direccion_ciudad_from_persona_table',11),(43,'2025_12_10_173653_add_cliente_id_to_pedido_table',12),(44,'2025_12_10_194225_remove_legacy_cliente_columns_from_pedido_table',13),(45,'2025_12_15_150500_make_color_nullable_in_producto_table',14),(46,'2025_12_15_155200_make_material_talla_nullable_in_producto_table',15),(47,'2025_12_15_160400_drop_material_talla_from_producto_table',16),(48,'2025_12_15_164400_create_tasa_cambio_table',17),(49,'2025_12_16_134300_create_tipo_producto_table',18),(50,'2025_12_16_134400_add_tipo_and_codigo_to_producto_table',18),(51,'2025_12_18_134800_add_pedido_id_and_logo_to_orden_produccion',19),(52,'2025_12_19_152000_add_cotizacion_id_to_pedido',20),(53,'2026_01_17_225337_rename_estado_to_estatus_and_add_estado_territorial',21),(54,'2026_01_18_160000_add_tipo_proveedor_and_persona_id_to_proveedor',22),(55,'2026_01_18_162000_make_proveedor_fields_nullable',23),(56,'2026_01_19_145036_add_separate_bank_fields_to_pedidos_table',24);
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
  CONSTRAINT `movimientos_insumos_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`),
  CONSTRAINT `movimientos_insumos_insumo_id_foreign` FOREIGN KEY (`insumo_id`) REFERENCES `insumo` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimiento_insumo`
--

/*!40000 ALTER TABLE `movimiento_insumo` DISABLE KEYS */;
INSERT INTO `movimiento_insumo` VALUES (1,1,'Entrada',1000.00,0.00,1000.00,'Inventario inicial',2,'2025-12-04 18:58:28','2025-12-04 18:58:28'),(2,2,'Entrada',5000.00,0.00,5000.00,'Compra inicial',2,'2025-12-04 18:58:28','2025-12-04 18:58:28'),(3,1,'Salida',5.00,1000.00,995.00,'Consumo por Pedido #1 - Producto: Polo Clásico',1,'2025-12-10 23:24:45','2025-12-10 23:24:45'),(4,3,'Salida',20.00,50.00,30.00,'Vendidos a una costurera',1,'2025-12-11 00:40:50','2025-12-11 00:40:50'),(5,1,'Salida',4.00,995.00,991.00,'Consumo por Pedido #2 - Producto: Camisa Oxford Clasica',1,'2025-12-16 20:47:53','2025-12-16 20:47:53'),(6,1,'Salida',2.00,991.00,989.00,'Consumo por Pedido #3 - Producto: Camisa Oxford Clasica',1,'2025-12-18 17:45:32','2025-12-18 17:45:32'),(7,1,'Salida',4.00,989.00,985.00,'Consumo por Pedido #4 - Producto: Chemise Cuello Mao',1,'2025-12-19 20:28:18','2025-12-19 20:28:18'),(8,1,'Entrada',4.00,985.00,989.00,'Reversión por actualización de Pedido #4 - Producto: Chemise Cuello Mao',5,'2026-01-16 17:28:16','2026-01-16 17:28:16'),(9,1,'Salida',4.00,989.00,985.00,'Consumo por actualización de Pedido #4 - Producto: Chemise Cuello Mao',5,'2026-01-16 17:28:16','2026-01-16 17:28:16'),(10,1,'Entrada',4.00,985.00,989.00,'Reversión por actualización de Pedido #4 - Producto: Chemise Cuello Mao',5,'2026-01-16 17:59:27','2026-01-16 17:59:27'),(11,2,'Salida',4.00,5000.00,4996.00,'Consumo por actualización de Pedido #4 - Producto: CE-001',5,'2026-01-16 17:59:27','2026-01-16 17:59:27'),(12,2,'Entrada',4.00,4996.00,5000.00,'Reversión por actualización de Pedido #4 - Producto: CE-001',5,'2026-01-16 18:00:46','2026-01-16 18:00:46'),(13,2,'Salida',4.00,5000.00,4996.00,'Consumo por actualización de Pedido #4 - Producto: Chemise Cuello Mao',5,'2026-01-16 18:00:46','2026-01-16 18:00:46'),(14,1,'Salida',4.00,989.00,985.00,'Consumo por actualización de Pedido #4 - Producto: Chemise Cuello Mao',5,'2026-01-16 18:00:46','2026-01-16 18:00:46'),(15,1,'Entrada',4.00,985.00,989.00,'Reversión por actualización de Pedido #2 - Producto: Camisa Oxford Clasica',5,'2026-01-16 18:07:36','2026-01-16 18:07:36'),(16,1,'Salida',4.00,989.00,985.00,'Consumo por actualización de Pedido #2 - Producto: Camisa Oxford Clasica',5,'2026-01-16 18:07:36','2026-01-16 18:07:36'),(17,1,'Entrada',5.00,985.00,990.00,'Reversión por actualización de Pedido #1 - Producto: Chemise Clasica',5,'2026-01-16 18:07:49','2026-01-16 18:07:49'),(18,1,'Salida',5.00,990.00,985.00,'Consumo por actualización de Pedido #1 - Producto: Chemise Clasica',5,'2026-01-16 18:07:49','2026-01-16 18:07:49'),(19,2,'Salida',1.00,4996.00,4995.00,'Consumo por Pedido #5 - Producto: Chemise Clasica',5,'2026-01-17 16:59:42','2026-01-17 16:59:42'),(20,2,'Salida',4.00,4995.00,4991.00,'Consumo por Pedido #6 - Producto: Chemise Clasica',5,'2026-01-17 22:33:53','2026-01-17 22:33:53'),(21,2,'Entrada',4.00,4991.00,4995.00,'Reversión por actualización de Pedido #6 - Producto: Chemise Clasica',5,'2026-01-17 22:34:12','2026-01-17 22:34:12'),(22,2,'Salida',4.00,4995.00,4991.00,'Consumo por actualización de Pedido #6 - Producto: Chemise Clasica',5,'2026-01-17 22:34:12','2026-01-17 22:34:12'),(23,2,'Entrada',1.00,4991.00,4992.00,'Reversión por actualización de Pedido #5 - Producto: Chemise Clasica',5,'2026-01-17 22:34:28','2026-01-17 22:34:28'),(24,2,'Salida',1.00,4992.00,4991.00,'Consumo por actualización de Pedido #5 - Producto: Chemise Clasica',5,'2026-01-17 22:34:28','2026-01-17 22:34:28'),(25,2,'Salida',4.00,4991.00,4987.00,'Consumo por Pedido #7 - Producto: Chemise Clasica',5,'2026-01-17 22:36:10','2026-01-17 22:36:10'),(26,2,'Entrada',4.00,4987.00,4991.00,'Reversión por actualización de Pedido #7 - Producto: Chemise Clasica',5,'2026-01-17 23:23:25','2026-01-17 23:23:25'),(27,2,'Salida',4.00,4991.00,4987.00,'Consumo por actualización de Pedido #7 - Producto: Chemise Clasica',5,'2026-01-17 23:23:25','2026-01-17 23:23:25'),(28,3,'Salida',2.00,30.00,28.00,'chemise',5,'2026-01-18 23:59:30','2026-01-18 23:59:30'),(29,3,'Salida',28.00,28.00,0.00,'chemises',5,'2026-01-19 00:01:47','2026-01-19 00:01:47'),(30,2,'Salida',2.00,4987.00,4985.00,'Consumo por Pedido #8 - Producto: Chemise Clasica',5,'2026-01-19 03:37:08','2026-01-19 03:37:08'),(31,2,'Salida',2.00,4985.00,4983.00,'Consumo por Pedido #9 - Producto: Chemise Clasica',5,'2026-01-19 03:42:07','2026-01-19 03:42:07'),(32,2,'Salida',2.00,4983.00,4981.00,'Consumo por Pedido #10 - Producto: CE-001',5,'2026-01-19 04:36:20','2026-01-19 04:36:20'),(33,5,'Entrada',10.00,100.00,110.00,'pues llegaron 10 mas',1,'2026-01-20 20:36:39','2026-01-20 20:36:39'),(34,5,'Salida',10.00,110.00,100.00,'Se gastaron',1,'2026-01-20 20:38:10','2026-01-20 20:38:10');
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
  `producto_id` bigint unsigned NOT NULL,
  `cantidad_solicitada` int NOT NULL,
  `cantidad_producida` int NOT NULL DEFAULT '0',
  `fecha_inicio` date NOT NULL,
  `fecha_fin_estimada` date NOT NULL,
  `estado` enum('Pendiente','En Proceso','Finalizado','Cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `costo_estimado` decimal(12,2) NOT NULL DEFAULT '0.00',
  `logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notas` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ordenes_produccion_producto_id_foreign` (`producto_id`),
  KEY `ordenes_produccion_created_by_foreign` (`created_by`),
  KEY `orden_produccion_pedido_id_foreign` (`pedido_id`),
  CONSTRAINT `orden_produccion_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordenes_produccion_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`),
  CONSTRAINT `ordenes_produccion_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orden_produccion`
--

/*!40000 ALTER TABLE `orden_produccion` DISABLE KEYS */;
INSERT INTO `orden_produccion` VALUES (3,3,5,1,0,'2025-12-18','2026-01-28','Pendiente',25.00,'Los Caminos','urgenteeeee',1,'2025-12-18 17:51:44','2025-12-19 18:43:30','2025-12-19 18:43:30'),(4,3,5,1,0,'2025-12-18','2026-01-28','Pendiente',25.00,'Los Caminos','urgente',1,'2025-12-18 18:00:17','2025-12-19 18:43:21','2025-12-19 18:43:21'),(5,3,5,1,0,'2025-12-19','2026-01-28','Pendiente',25.00,'Los Caminos','urgente',1,'2025-12-19 18:44:18','2026-01-17 16:56:43','2026-01-17 16:56:43'),(6,3,5,1,0,'2026-01-16','2026-01-28','Finalizado',25.00,'Los Caminos',NULL,5,'2026-01-16 18:10:08','2026-01-16 18:10:26',NULL),(7,5,1,4,0,'2026-01-17','2026-01-20','Finalizado',119.60,'Logo UPTP',NULL,5,'2026-01-17 17:01:08','2026-01-17 17:35:23',NULL);
/*!40000 ALTER TABLE `orden_produccion` ENABLE KEYS */;

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
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES ('vanessalopez090551@gmail.com','$2y$12$y7iiZTcAZq9wHPrufhpSK./ZStflchwZvYdOigU4/YZJlzXQWr6C2','2026-01-15 00:32:57'),('admin@gmail.com','$2y$12$kyw1hn8dGMMp.qiatjb0p.V1cnK0HKs7nxIuFIglgqYH5Kdz0tibW','2026-01-18 19:39:45');
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
  `estado` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `prioridad` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Normal',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `abono` decimal(10,2) NOT NULL DEFAULT '0.00',
  `efectivo_pagado` tinyint(1) NOT NULL DEFAULT '0',
  `transferencia_pagado` tinyint(1) NOT NULL DEFAULT '0',
  `pago_movil_pagado` tinyint(1) NOT NULL DEFAULT '0',
  `referencia_transferencia` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banco_transferencia_id` bigint unsigned DEFAULT NULL,
  `referencia_pago_movil` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banco_pago_movil_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `banco_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedidos_user_id_foreign` (`user_id`),
  KEY `pedidos_banco_id_foreign` (`banco_id`),
  KEY `pedido_cliente_id_foreign` (`cliente_id`),
  KEY `pedido_cotizacion_id_foreign` (`cotizacion_id`),
  CONSTRAINT `pedido_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pedido_cotizacion_id_foreign` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizacion` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pedidos_banco_id_foreign` FOREIGN KEY (`banco_id`) REFERENCES `banco` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pedidos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedido`
--

/*!40000 ALTER TABLE `pedido` DISABLE KEYS */;
INSERT INTO `pedido` VALUES (1,NULL,7,'2025-12-10','2026-01-07','Procesando','Normal',29.90,0.00,0,0,0,NULL,NULL,NULL,NULL,1,'2025-12-10 23:24:45','2026-01-17 16:58:05','2026-01-17 16:58:05',NULL),(2,NULL,7,'2025-12-16','2025-12-31','Cancelado','Normal',50.00,0.00,0,0,0,NULL,NULL,NULL,NULL,1,'2025-12-16 20:47:53','2026-01-17 16:58:01','2026-01-17 16:58:01',NULL),(3,NULL,3,'2025-12-18','2026-01-30','Pendiente','Urgente',25.00,0.00,0,0,0,NULL,NULL,NULL,NULL,1,'2025-12-18 17:45:32','2026-01-17 16:57:54','2026-01-17 16:57:54',NULL),(4,7,1,'2025-12-20','2026-01-16','Completado','Urgente',34.00,4.00,0,0,0,NULL,NULL,NULL,NULL,1,'2025-12-19 20:28:18','2026-01-17 16:57:58','2026-01-17 16:57:58',NULL),(5,8,9,'2026-01-17','2026-01-22','Cancelado','Normal',119.60,11.00,1,0,0,NULL,NULL,NULL,NULL,5,'2026-01-17 16:59:42','2026-01-17 22:34:28',NULL,NULL),(6,9,12,'2026-01-24','2026-01-31','Completado','Normal',119.60,7.00,1,0,0,NULL,NULL,NULL,NULL,5,'2026-01-17 22:33:53','2026-01-17 22:34:12',NULL,NULL),(7,10,8,'2026-01-24','2026-01-31','Procesando','Normal',119.60,0.00,0,0,0,NULL,NULL,NULL,NULL,5,'2026-01-17 22:36:10','2026-01-17 23:23:25',NULL,NULL),(8,11,14,'2026-01-18','2026-01-20','Pendiente','Normal',388.70,22.00,1,0,0,NULL,NULL,NULL,NULL,5,'2026-01-19 03:37:08','2026-01-19 03:37:08',NULL,NULL),(9,11,14,'2026-01-18','2026-01-20','Pendiente','Normal',388.70,22.00,0,0,0,NULL,NULL,NULL,NULL,5,'2026-01-19 03:42:07','2026-01-19 03:42:07',NULL,NULL),(10,15,20,'2026-01-18','2026-01-20','Pendiente','Normal',1317.80,33.00,1,0,0,NULL,NULL,NULL,NULL,5,'2026-01-19 04:36:20','2026-01-19 04:36:20',NULL,NULL),(11,14,9,'2026-01-19','2026-01-21','Pendiente','Alta',1317.80,33.00,0,0,0,NULL,NULL,NULL,NULL,1,'2026-01-19 19:13:45','2026-01-19 19:13:45',NULL,NULL),(12,13,15,'2026-01-19','2026-01-23','Pendiente','Normal',25.00,20.00,1,0,0,NULL,NULL,NULL,NULL,1,'2026-01-19 19:24:34','2026-01-19 19:24:34',NULL,NULL),(13,19,9,'2026-01-19','2026-01-23','Pendiente','Normal',986.70,900.00,1,0,0,NULL,NULL,NULL,NULL,1,'2026-01-19 19:32:56','2026-01-19 19:32:56',NULL,NULL),(14,18,21,'2026-01-19','2026-01-21','Pendiente','Normal',627.00,44.00,1,0,0,NULL,NULL,NULL,NULL,1,'2026-01-19 19:51:33','2026-01-19 19:51:33',NULL,NULL),(15,17,15,'2026-01-19','2026-01-23','Pendiente','Normal',2963.40,300.00,1,0,0,NULL,NULL,NULL,NULL,1,'2026-01-19 20:16:20','2026-01-19 20:16:20',NULL,NULL),(16,20,22,'2026-01-20','2026-01-27','Pendiente','Normal',29.90,0.00,0,0,0,NULL,NULL,NULL,NULL,1,'2026-01-20 21:21:33','2026-01-20 21:21:33',NULL,NULL),(17,21,23,'2026-01-20','2026-01-27','Pendiente','Normal',51.00,0.00,0,0,0,NULL,NULL,NULL,NULL,1,'2026-01-20 21:25:39','2026-01-20 21:25:39',NULL,NULL),(18,22,23,'2026-01-20','2026-01-27','Pendiente','Normal',358.80,0.00,0,0,0,NULL,NULL,NULL,NULL,1,'2026-01-20 21:32:04','2026-01-20 21:32:04',NULL,NULL),(19,16,9,'2026-01-20','2026-01-27','Pendiente','Normal',374.00,0.00,0,0,0,NULL,NULL,NULL,NULL,1,'2026-01-20 21:34:18','2026-01-20 21:34:18',NULL,NULL),(20,23,23,'2026-01-20','2026-01-27','Pendiente','Normal',190.00,0.00,0,0,0,NULL,NULL,NULL,NULL,1,'2026-01-20 21:39:09','2026-01-20 21:39:09',NULL,NULL),(21,24,14,'2026-01-20','2026-01-27','Pendiente','Normal',29.90,0.00,0,0,0,NULL,NULL,NULL,NULL,1,'2026-01-20 21:45:49','2026-01-20 21:45:49',NULL,NULL),(22,25,23,'2026-01-20','2026-01-27','Pendiente','Normal',29.90,0.00,0,0,0,NULL,NULL,NULL,NULL,1,'2026-01-20 21:52:03','2026-01-20 21:52:03',NULL,NULL),(23,26,23,'2026-01-20','2026-01-27','Pendiente','Normal',59.80,0.00,0,0,0,NULL,NULL,NULL,NULL,1,'2026-01-20 21:56:29','2026-01-20 21:56:29',NULL,NULL),(24,27,23,'2026-01-20','2026-01-27','Pendiente','Normal',29.90,0.00,0,0,0,NULL,NULL,NULL,NULL,1,'2026-01-20 22:01:40','2026-01-20 22:01:40',NULL,NULL),(25,28,23,'2026-01-20','2026-01-27','Pendiente','Normal',358.80,0.00,0,0,0,NULL,NULL,NULL,NULL,1,'2026-01-20 22:21:37','2026-01-20 22:22:03',NULL,NULL);
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
  `estado_persona` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` enum('M','F','Otro') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `persona_documento_identidad_unique` (`documento_identidad`),
  UNIQUE KEY `persona_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `persona`
--

/*!40000 ALTER TABLE `persona` DISABLE KEYS */;
INSERT INTO `persona` VALUES (1,'Administrador','Sistema','00000001','V-','admin@gmail.com',NULL,NULL,NULL,'2025-12-04 18:58:27','2025-12-04 18:58:27',NULL),(2,'El','Supervisor','00000002','V-',NULL,'Portuguesa','2003-04-08','M','2025-12-04 18:58:28','2025-12-08 14:30:58',NULL),(3,'James David','Vance','8889292','G-','jdvance@gmail.com','Washington DC','1980-04-04','M','2025-12-04 20:19:19','2025-12-04 20:19:19',NULL),(4,'Jose Luis','Rodriguez','4567899','V-','isavale10@gmail.com','Portuguesa','2005-03-31','M','2025-12-05 20:14:04','2025-12-05 20:14:04',NULL),(6,'Peter','Thiel','8769044','E-','pltrinvest@gmail.com',NULL,NULL,NULL,'2025-12-08 19:55:18','2025-12-08 20:23:37',NULL),(7,'Larry','Ellison','545683666','E-','oraclecorporation@gmail.com',NULL,NULL,NULL,'2025-12-08 19:55:18','2025-12-08 20:23:29',NULL),(8,'Leslie Herbert','Wexner','6233455','E-','vsecret@gmail.com',NULL,NULL,NULL,'2025-12-08 20:04:57','2025-12-08 20:23:20',NULL),(9,'Robert','Maxwell','987489','E-','maxwellcorporation@gmail.com',NULL,NULL,NULL,'2025-12-08 20:19:32','2025-12-08 20:22:30',NULL),(10,'Jose','Juan','7499586','E-','nvidiaceo@gmail.com',NULL,NULL,NULL,'2025-12-09 18:54:35','2025-12-09 18:56:47',NULL),(11,'Alexander Caedmon','Karp','89320234','E-','alexkpr@gmail.com',NULL,NULL,NULL,'2025-12-10 18:09:48','2025-12-10 18:09:48',NULL),(12,'Mark','Zuckerberg','18728555','V-','facebook@gmail.com',NULL,NULL,NULL,'2025-12-10 20:29:40','2025-12-10 20:31:20',NULL),(13,'Santiago','Mendoza','30822318','V-','santitron@gmail.com','Portuguesa','2005-01-11','M','2025-12-10 20:57:36','2025-12-10 20:57:36',NULL),(14,'Mark','Cuban','6786543','E-','markcu@gmail.com','Texas','2004-03-10','M','2025-12-10 21:09:58','2025-12-10 21:09:58',NULL),(15,'Vanessa','diaz','30966655','V-','vanessalopez090551@gmail.com',NULL,NULL,NULL,'2026-01-17 16:51:52','2026-01-17 16:51:52',NULL),(16,'valeria','diaz','32152373','V-','valediaz@gmail.com',NULL,NULL,NULL,'2026-01-17 17:11:09','2026-01-17 17:11:09',NULL),(17,'cvane','','30966654','V-',NULL,NULL,NULL,NULL,'2026-01-17 22:05:23','2026-01-17 22:05:23',NULL),(18,'alalalallaa','kneoucnewuivcw','30966659','V-','alalallala@email.com',NULL,NULL,NULL,'2026-01-17 22:31:33','2026-01-17 22:31:33',NULL),(19,'Cleymar','Mendoza','30966271','V-','cley@gmail.com',NULL,NULL,NULL,'2026-01-18 03:49:00','2026-01-18 03:49:00',NULL),(20,'Cleymar','Mendoza','30966275','V-','cleymar@gmail.com',NULL,NULL,NULL,'2026-01-18 03:56:57','2026-01-18 03:56:57',NULL),(22,'Victor','Mendoza','12344093','V-','victorm@gmail.com',NULL,NULL,NULL,'2026-01-18 20:23:50','2026-01-18 20:23:50',NULL),(23,'Luis Alberto','Mendoza García','15789234','V-','luismendoza@gmail.com',NULL,NULL,NULL,'2026-01-18 20:37:01','2026-01-18 20:37:01',NULL),(24,'Rosa María','Hernández López','18234567','V-','rosahdez@hotmail.com',NULL,NULL,NULL,'2026-01-18 20:38:40','2026-01-18 20:38:40',NULL),(25,'Carlos Eduardo','Silva Martínez','84567890','E-','carlossilva@gmail.com',NULL,NULL,NULL,'2026-01-18 20:41:05','2026-01-18 20:41:05',NULL),(26,'Angela Patricia','Vargas Rojas','12456789','V-','angelavargas@gmail.com',NULL,NULL,NULL,'2026-01-18 20:43:53','2026-01-18 20:43:53',NULL),(27,'María Fernanda','Gutiérrez Méndez','16823456','V-','mariagutierrez@gmail.com',NULL,NULL,NULL,'2026-01-18 20:57:49','2026-01-18 20:57:49',NULL),(28,'Pedro Antonio','Briceño Rivas','19876543','V-','pedrobriceno@gmail.com',NULL,NULL,NULL,'2026-01-18 21:52:28','2026-01-18 21:52:28',NULL),(29,'Angely','Canelon','37782737','V-','loca123@gmail.com',NULL,NULL,NULL,'2026-01-19 00:25:34','2026-01-19 00:25:34',NULL),(30,'Alejandro','Abreu','31558506','V-','ale@gmail.com',NULL,NULL,NULL,'2026-01-19 03:56:04','2026-01-19 03:56:04',NULL),(31,'alejandro','abreu','31558507','V-','ale2@gmail.com',NULL,NULL,NULL,'2026-01-19 04:01:50','2026-01-19 04:01:50',NULL),(32,'alejandro','diaz','31558508','V-','vd6955291@gmail.com',NULL,NULL,NULL,'2026-01-19 04:05:33','2026-01-19 04:05:33',NULL),(33,'josefina','lopez','31558509','V-','vd695529221@gmail.com',NULL,NULL,NULL,'2026-01-19 04:17:44','2026-01-19 04:17:44',NULL),(34,'angel','Canelon','37782735','V-','loca1233@gmail.com',NULL,NULL,NULL,'2026-01-19 04:26:34','2026-01-19 04:26:34',NULL),(35,'abby','chuela','31558599','V-','abbychuela@gmail.com',NULL,NULL,NULL,'2026-01-19 16:49:16','2026-01-19 16:49:16',NULL),(36,'Yohan','Mendoza','15692128','V-',NULL,NULL,NULL,NULL,'2026-01-20 01:29:08','2026-01-20 01:29:08',NULL),(37,'Emmanuel','Arroyo','30922671','V-','emman6321@gmail.com',NULL,NULL,NULL,'2026-01-20 21:23:34','2026-01-20 21:23:34',NULL);
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
  `operario_id` bigint unsigned NOT NULL,
  `cantidad_producida` int NOT NULL,
  `cantidad_defectuosa` int NOT NULL DEFAULT '0',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `produccion_diaria_orden_id_foreign` (`orden_id`),
  KEY `produccion_diaria_operario_id_foreign` (`operario_id`),
  CONSTRAINT `produccion_diaria_operario_id_foreign` FOREIGN KEY (`operario_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `produccion_diaria_orden_id_foreign` FOREIGN KEY (`orden_id`) REFERENCES `orden_produccion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produccion_diaria`
--

/*!40000 ALTER TABLE `produccion_diaria` DISABLE KEYS */;
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
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `modelo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio_base` decimal(10,2) NOT NULL,
  `imagen` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `producto_codigo_unique` (`codigo`),
  KEY `producto_tipo_producto_id_foreign` (`tipo_producto_id`),
  CONSTRAINT `producto_tipo_producto_id_foreign` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipo_producto` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `producto`
--

/*!40000 ALTER TABLE `producto` DISABLE KEYS */;
INSERT INTO `producto` VALUES (1,1,'CHM-001','Polo de algodón pima con cuello redondo','Clasica',29.90,'productoimg/imagenes/69406d911e834.jpg',1,'2025-12-04 18:58:28','2026-01-20 22:30:55','2026-01-20 22:30:55'),(2,NULL,NULL,'Camisa manga larga para oficina','CE-001',59.90,NULL,1,'2025-12-04 18:58:28','2026-01-20 22:31:08','2026-01-20 22:31:08'),(3,NULL,NULL,'Corte Columbia I','CL-1',19.00,'productoimg/imagenes/6940679f046be.png',1,'2025-12-15 19:55:11','2026-01-20 22:31:04','2026-01-20 22:31:04'),(4,NULL,NULL,'Pantalon de Seguridad','JORNES-001',19.00,'productoimg/imagenes/69406ba3c92c3.jpg',1,'2025-12-15 20:12:19','2026-01-20 22:31:01','2026-01-20 22:31:01'),(5,3,'CAM-001',NULL,'Oxford Clasica',25.00,NULL,1,'2025-12-16 18:30:44','2025-12-16 18:30:44',NULL),(6,1,'CHM-002','chemise cuello chino','Cuello Mao',17.00,'productoimg/imagenes/6941a6dcf11a1.jpg',1,'2025-12-16 18:37:16','2025-12-16 18:37:16',NULL),(7,2,'FRN-CEV-001',NULL,'Cuello en V',12.00,NULL,1,'2025-12-16 18:57:27','2025-12-16 18:57:27',NULL);
/*!40000 ALTER TABLE `producto` ENABLE KEYS */;

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
  `razon_social` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rif` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacto` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono_contacto` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `proveedores_ruc_unique` (`rif`),
  KEY `proveedor_persona_id_foreign` (`persona_id`),
  CONSTRAINT `proveedor_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedor`
--

/*!40000 ALTER TABLE `proveedor` DISABLE KEYS */;
INSERT INTO `proveedor` VALUES (1,'juridico',NULL,'Textiles Caracas Vzla','J-1231321','Av. Industrial 123, Venezuela','0412-555666','ventas@textilesvenezuela.com','Juan Pérez','0412-5231234',1,'2025-12-04 18:58:28','2025-12-05 20:55:48',NULL),(2,'juridico',NULL,'Insumos Textiles C.C.S','J-11112222','Torre Jalisco, Las Mercedes','01-3214567','ventas@insumostextiles.com','María García','0424890457',1,'2025-12-04 18:58:28','2025-12-04 18:58:28',NULL),(3,'natural',22,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-01-18 20:23:50','2026-01-18 20:23:50',NULL),(4,'juridico',NULL,'Telas y Bordados del Centro CA','J-401234567','Zona Industrial II, Galpón 15, Acarigua','0255-6234567','ventas@telasbordados.com','María González','0412-5678901',1,'2026-01-18 20:33:31','2026-01-18 20:33:31',NULL),(5,'juridico',NULL,'Hilos Industriales Portuguesa SA','J-312456789','Av. Bolívar, Local 23, Araure','0255-6789012','info@hilosindustriales.com','José Rodríguez','0414-3456789',1,'2026-01-18 20:34:28','2026-01-18 20:34:28',NULL),(6,'juridico',NULL,'Insumos Textiles Venezuela CA','G-201987654','Calle 5, CC Los Llanos, Valencia','0241-8345678','compras@insumostextiles.com.ve','Carmen Pérez','0424-9876543',1,'2026-01-18 20:35:48','2026-01-18 20:35:48',NULL),(7,'natural',23,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-01-18 20:37:01','2026-01-18 20:37:01',NULL),(8,'natural',24,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-01-18 20:38:40','2026-01-18 20:38:40',NULL),(9,'natural',25,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-01-18 20:41:05','2026-01-18 20:41:05',NULL),(10,'juridico',NULL,'Botones y Accesorios Lara CA','J-502345671','Av. Libertador, Edif. Comercial, Piso 2, Barquisimeto','0251-7891234','ventas@botonesaccesorios.com','Fernando Castillo','0416-7890123',1,'2026-01-18 20:42:44','2026-01-18 20:42:44',NULL),(11,'natural',26,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-01-18 20:43:53','2026-01-18 20:43:53',NULL),(12,'juridico',NULL,'Distribuidora de Telas Los Andes','J-415678903','Calle Principal, Galpón 8, Mérida','0274-4567890','contacto@telaslosandes.com','Ana Beatriz Ramos','0426-5432109',1,'2026-01-18 20:45:00','2026-01-18 20:45:00',NULL),(13,'natural',27,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-01-18 20:57:49','2026-01-18 20:57:49',NULL),(14,'natural',28,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-01-18 21:52:28','2026-01-18 21:52:28',NULL);
/*!40000 ALTER TABLE `proveedor` ENABLE KEYS */;

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
  UNIQUE KEY `tasa_cambio_moneda_fecha_bcv_unique` (`moneda`,`fecha_bcv`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasa_cambio`
--

/*!40000 ALTER TABLE `tasa_cambio` DISABLE KEYS */;
INSERT INTO `tasa_cambio` VALUES (1,'USD',270.7900,'2025-12-15','BCV (DolarAPI)','2025-12-15 20:47:22','2025-12-15 20:47:22'),(2,'USD',273.5900,'2025-12-16','BCV (DolarAPI)','2025-12-16 15:36:07','2025-12-16 15:36:07'),(3,'USD',279.5600,'2025-12-18','BCV (DolarAPI)','2025-12-18 13:34:20','2025-12-18 13:34:20'),(4,'USD',282.5100,'2025-12-19','BCV (DolarAPI)','2025-12-19 18:34:37','2025-12-19 18:34:37'),(5,'USD',336.4600,'2026-01-14','BCV (DolarAPI)','2026-01-14 23:32:37','2026-01-14 23:32:37'),(6,'USD',339.1500,'2026-01-15','BCV (DolarAPI)','2026-01-15 17:04:21','2026-01-15 17:04:21'),(7,'USD',341.7400,'2026-01-16','BCV (DolarAPI)','2026-01-16 17:18:16','2026-01-16 17:18:16'),(8,'USD',344.5100,'2026-01-17','BCV (DolarAPI)','2026-01-17 04:00:35','2026-01-17 04:00:35'),(9,'USD',344.5100,'2026-01-18','BCV (DolarAPI)','2026-01-18 14:29:14','2026-01-18 14:29:14'),(10,'USD',344.5100,'2026-01-19','BCV (DolarAPI)','2026-01-19 16:28:54','2026-01-19 16:28:54'),(11,'USD',344.5100,'2026-01-20','BCV (DolarAPI)','2026-01-20 19:43:50','2026-01-20 19:43:50');
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
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telefono`
--

/*!40000 ALTER TABLE `telefono` DISABLE KEYS */;
INSERT INTO `telefono` VALUES (1,2,'0426-3412567','movil',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(2,3,'0412-3453314','movil',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(3,4,'0426-1135645','movil',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(4,6,'0412555777','movil',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(5,7,'0424-869334','movil',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(6,8,'0422-344859','movil',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(7,9,'0424-898099','movil',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(8,10,'0422-778456','movil',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(9,11,'0414-5548982','movil',1,'2025-12-10 18:42:20','2025-12-10 18:42:20',NULL),(10,12,'0412-4436668','movil',1,'2025-12-10 20:29:40','2026-01-17 03:27:27',NULL),(11,14,'0412-3556789','movil',1,'2025-12-10 21:09:58','2025-12-10 21:09:58',NULL),(12,13,'0412-4435673','movil',1,'2025-12-10 21:17:57','2025-12-10 21:17:57',NULL),(13,15,'0412-9288102','movil',1,'2026-01-17 16:51:52','2026-01-17 16:51:52',NULL),(14,16,'0414-5684402','movil',1,'2026-01-17 17:11:09','2026-01-17 17:11:09',NULL),(15,17,'0424-3637623','movil',1,'2026-01-17 22:05:23','2026-01-17 22:05:23',NULL),(16,18,'0414-5684402','movil',1,'2026-01-17 22:31:33','2026-01-17 22:31:33',NULL),(17,19,'0424-1595466','movil',1,'2026-01-18 03:49:00','2026-01-18 03:49:00',NULL),(18,20,'0424-1595466','movil',1,'2026-01-18 03:56:57','2026-01-18 03:56:57',NULL),(20,22,'0412-5238473','movil',1,'2026-01-18 20:23:50','2026-01-18 20:23:50',NULL),(21,23,'0424-5671234','movil',1,'2026-01-18 20:37:01','2026-01-18 20:37:01',NULL),(22,24,'0412-8904567','movil',1,'2026-01-18 20:38:40','2026-01-18 20:38:40',NULL),(23,25,'0414-2345678','movil',1,'2026-01-18 20:41:05','2026-01-18 20:41:05',NULL),(24,26,'0426-3456789','movil',1,'2026-01-18 20:43:53','2026-01-18 20:43:53',NULL),(25,27,'0424-5678901','movil',1,'2026-01-18 20:57:49','2026-01-18 20:57:49',NULL),(26,28,'0424-8901234','movil',1,'2026-01-18 21:52:28','2026-01-18 21:52:28',NULL),(27,29,'0422-2222222','movil',1,'2026-01-19 00:25:34','2026-01-19 00:25:34',NULL),(28,30,'0424-5345463','movil',1,'2026-01-19 03:56:04','2026-01-19 03:56:04',NULL),(29,31,'0424-3442434','movil',1,'2026-01-19 04:01:50','2026-01-19 04:01:50',NULL),(30,32,'0424-5684402','movil',1,'2026-01-19 04:05:33','2026-01-19 04:05:33',NULL),(31,33,'0424-5684402','movil',1,'2026-01-19 04:17:44','2026-01-19 04:17:44',NULL),(32,34,'0424-2222222','movil',1,'2026-01-19 04:26:34','2026-01-19 04:26:34',NULL),(33,35,'0424-4523142','movil',1,'2026-01-19 16:49:16','2026-01-19 16:49:16',NULL),(34,36,'0412-9020671','movil',1,'2026-01-20 01:29:08','2026-01-20 01:29:08',NULL),(35,37,'0412-5235773','movil',1,'2026-01-20 21:23:34','2026-01-20 21:23:34',NULL);
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
  `codigo_prefijo` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `contador` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipo_producto_codigo_prefijo_unique` (`codigo_prefijo`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_producto`
--

/*!40000 ALTER TABLE `tipo_producto` DISABLE KEYS */;
INSERT INTO `tipo_producto` VALUES (1,'Chemise','CHM','Camisas tipo polo con cuello',2,'2025-12-16 17:48:48','2025-12-16 18:37:16'),(2,'Franela','FRN','Franelas cuello redondo o V',0,'2025-12-16 17:48:48','2025-12-16 17:48:48'),(3,'Camisa','CAM','Camisas formales manga larga/corta',1,'2025-12-16 17:48:48','2025-12-16 18:30:44'),(4,'Pantalón','PNT','Pantalones de trabajo o formales',0,'2025-12-16 17:48:48','2025-12-16 17:48:48'),(5,'Chaqueta','CHQ','Chaquetas industriales o formales',0,'2025-12-16 17:48:48','2025-12-16 17:48:48'),(6,'Overol','OVR','Overoles y monos de trabajo',0,'2025-12-16 17:48:48','2025-12-16 17:48:48'),(7,'Uniforme Escolar','ESC','Prendas para uniformes escolares',0,'2025-12-16 17:48:48','2025-12-16 17:48:48'),(8,'Accesorio','ACC','Gorras, delantales, chalecos, etc.',0,'2025-12-16 17:48:48','2025-12-16 17:48:48');
/*!40000 ALTER TABLE `tipo_producto` ENABLE KEYS */;

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
  `avatar` text COLLATE utf8mb4_unicode_ci,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `user_persona_id_unique` (`persona_id`),
  CONSTRAINT `user_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,1,'Administrador','admin@gmail.com','Administrador',NULL,'$2y$12$eq5H9CQvR.2WeRggx.y92Ot93ftz0Ml0rrPRGhmxGMsfEwq0/zahm',NULL,1,'8DWNyUGy0EwuaBPxtFeQaGuWDtPeL2247gjhTrjPx4LBx4SSYFD1u9wCkJbD','2025-12-04 18:58:27','2025-12-04 18:58:27'),(2,2,'Supervisor','supervisor@gmail.com','Supervisor',NULL,'$2y$12$WZ9jnte4F/DkVPbh64iBKOt91FLUDEDRzmYtJYvc6.iwwLhn3wef6',NULL,1,NULL,'2025-12-04 18:58:28','2025-12-04 18:58:28'),(5,NULL,'Vanessa Diaz','vanessalopez090551@gmail.com','Administrador',NULL,'$2y$12$d0G/88tSU7qJKgmtlYP.ZO95ss9hgFYK8lZF6N9tsRQAsmlansFHC','avatars/69682f4d49bb1.jpg',1,'Egbv2pBcVPvjVa8jdqGym2kvHdikOyAWaF03sHTyIAY1ZE28cT8ARZkfl6GR','2026-01-15 00:05:33','2026-01-15 00:05:33');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;

--
-- Dumping routines for database 'sistema_atlantico4'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-21 11:31:52
