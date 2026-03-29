/*M!999999 enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.6-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: rawis_resort_db
-- ------------------------------------------------------
-- Server version	11.8.6-MariaDB-2 from Debian

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `amenities`
--

DROP TABLE IF EXISTS `amenities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `amenities` (
  `amenity_id` int(11) NOT NULL AUTO_INCREMENT,
  `amenity_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` text DEFAULT NULL,
  `amenity_status` enum('Available','Unavailable') DEFAULT 'Available',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`amenity_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amenities`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `amenities` WRITE;
/*!40000 ALTER TABLE `amenities` DISABLE KEYS */;
INSERT INTO `amenities` VALUES
(11,'Swimming Pool','Enjoy our refreshing outdoor swimming pool, perfect for leisure and relaxation.',NULL,'Available','2026-03-21 10:49:39'),
(12,'Nightly Acoustic Band','Live acoustic music every night to set the mood for a relaxing evening.',NULL,'Available','2026-03-21 10:49:39'),
(13,'Function Rooms','Versatile function rooms available for all occasions including events, meetings, and celebrations.',NULL,'Available','2026-03-21 10:49:39'),
(14,'Laundry Services','Convenient laundry services available for guests during their stay.',NULL,'Available','2026-03-21 10:49:39'),
(15,'24-Hour Security System','Round-the-clock security system to ensure the safety of all guests.',NULL,'Available','2026-03-21 10:49:39'),
(16,'CCTV Cameras','Property-wide CCTV surveillance for enhanced guest security.',NULL,'Available','2026-03-21 10:49:39'),
(17,'Parking Area','Dedicated and secured parking area available for guest vehicles.',NULL,'Available','2026-03-21 10:49:39'),
(18,'Fully Automatic Fire Alarm System','State-of-the-art automatic fire alarm system for guest safety.',NULL,'Available','2026-03-21 10:49:39'),
(19,'High Powered Standby Generator','High-powered standby generator ensures uninterrupted power supply during outages.',NULL,'Available','2026-03-21 10:49:39');
/*!40000 ALTER TABLE `amenities` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `reservation_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','GCash','Card') NOT NULL,
  `payment_status` enum('Pending','Awaiting Verification','Completed','Rejected','Refunded') DEFAULT 'Pending',
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT current_timestamp(),
  `card_last4` varchar(4) DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `fk_payment_reservation` (`reservation_id`),
  CONSTRAINT `fk_payment_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES
(12,16,79900.00,'Cash','Pending','',NULL,'2026-03-22 14:22:15',NULL);
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `reservation_amenities`
--

DROP TABLE IF EXISTS `reservation_amenities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation_amenities` (
  `reservation_id` int(11) NOT NULL,
  `amenity_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`reservation_id`,`amenity_id`),
  KEY `fk_ra_amenity` (`amenity_id`),
  CONSTRAINT `fk_ra_amenity` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`amenity_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ra_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation_amenities`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `reservation_amenities` WRITE;
/*!40000 ALTER TABLE `reservation_amenities` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservation_amenities` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL AUTO_INCREMENT,
  `guest_id` int(10) unsigned NOT NULL,
  `room_id` int(11) NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `num_guests` int(11) NOT NULL,
  `reservation_status` enum('Pending','Confirmed','Cancelled','Completed') DEFAULT 'Pending',
  `extra_requests` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `extra_guests` int(11) NOT NULL DEFAULT 0,
  `extra_beds` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`reservation_id`),
  KEY `guest_id` (`guest_id`),
  KEY `room_id` (`room_id`),
  CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`guest_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
INSERT INTO `reservations` VALUES
(16,15,6,'2026-03-22','2026-04-08',1,'Pending','0','2026-03-22 14:22:15',4,4);
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL AUTO_INCREMENT,
  `room_number` varchar(20) NOT NULL,
  `room_type` varchar(50) NOT NULL,
  `max_capacity` int(11) NOT NULL,
  `price_per_night` decimal(10,2) NOT NULL,
  `room_status` varchar(20) NOT NULL,
  `image_path` text DEFAULT NULL,
  `extra_guest_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `extra_bed_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `num_bedrooms` int(11) NOT NULL DEFAULT 1,
  `num_beds` int(11) NOT NULL DEFAULT 1,
  `bed_type` varchar(50) NOT NULL DEFAULT 'Double',
  PRIMARY KEY (`room_id`),
  UNIQUE KEY `room_number` (`room_number`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES
(6,'1','Premier Single Room',2,2500.00,'available','assets/rooms/1774396541_premier-single.png',300.00,250.00,1,1,'Double'),
(7,'2','Premier Twin Room',2,2600.00,'available','assets/rooms/1774396551_premier-twin.png',300.00,250.00,1,2,'Twin'),
(8,'3','Superior Twin Room',2,3000.00,'available','assets/rooms/1774396558_superior-twin.png',300.00,250.00,1,2,'Twin'),
(9,'4','Superior Quadruple Room',4,3500.00,'available','assets/rooms/1774396568_superior-quadruple.png',300.00,250.00,1,2,'Double');
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `role` enum('admin','guest') NOT NULL DEFAULT 'guest',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'admin','admin123',NULL,NULL,NULL,'2026-01-29 11:09:54','admin'),
(2,'','','ef','dsf','23131233','2026-02-12 10:40:35','guest'),
(6,'fdsfsdsdfsdfsdfsdzxfczxczc zxczxczxczx','','fdsfsdsdfsdfsdfsdzxfczxczc','zxczxczxczx','123213432','2026-02-12 11:05:58','guest'),
(7,'zxczxczxc czcz','','zxczxczxc','czcz','23131233','2026-02-12 11:17:22','guest'),
(8,'dfsdf asdasd','','dfsdf','asdasd','231312331','2026-02-22 11:31:14','guest'),
(9,'adsdsa asd_1773656997','','adsdsa','asd','23131233','2026-03-16 10:29:57','guest'),
(10,'rfsdf sdfsdfs_1774100011','','rfsdf','sdfsdfs','23131233','2026-03-21 13:33:31','guest'),
(11,'ewr fsd_1774100090','','ewr','fsd','23131233','2026-03-21 13:34:50','guest'),
(12,'asdasdasasd asdasdd_1774179412','','asdasdasasd','asdasdd','12313131','2026-03-22 11:36:52','guest'),
(13,'asdasd asdasd_1774179448','','asdasd','asdasd','asdasda','2026-03-22 11:37:28','guest'),
(14,'aSAs asas_1774187291','','aSAs','asas','asaS','2026-03-22 13:48:11','guest'),
(15,'asdad asdasd_1774187388','','sdasd','zdasd','09611440750','2026-03-22 13:49:48','guest');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-03-25  7:56:58
