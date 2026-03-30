-- ============================================================
--  Rawis Resort Hotel — Database Schema
--  Generated: 2026-03-30
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
--  USERS
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `username`     VARCHAR(150)    NOT NULL UNIQUE,
  `password`     VARCHAR(255)    NOT NULL,
  `first_name`   VARCHAR(255)    DEFAULT NULL,
  `last_name`    VARCHAR(255)    DEFAULT NULL,
  `phone_number` VARCHAR(255)    DEFAULT NULL,
  `created_at`   TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
  `role`         ENUM('admin','guest') NOT NULL DEFAULT 'guest',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` VALUES
(1, 'admin', 'admin123', NULL, NULL, NULL, NOW(), 'admin');


-- ------------------------------------------------------------
--  ROOMS
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `rooms`;
CREATE TABLE `rooms` (
  `room_id`         INT          NOT NULL AUTO_INCREMENT,
  `room_number`     VARCHAR(20)  NOT NULL UNIQUE,
  `room_type`       VARCHAR(50)  NOT NULL,
  `max_capacity`    INT          NOT NULL,
  `price_per_night` DECIMAL(10,2) NOT NULL,
  `room_status`     VARCHAR(20)  NOT NULL DEFAULT 'available',
  `image_path`      TEXT         DEFAULT NULL,
  `extra_guest_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `extra_bed_fee`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `num_bedrooms`    INT          NOT NULL DEFAULT 1,
  `num_beds`        INT          NOT NULL DEFAULT 1,
  `bed_type`        VARCHAR(50)  NOT NULL DEFAULT 'Double',
  PRIMARY KEY (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `rooms` VALUES
(1, '1', 'Premier Single Room',    2, 2500.00, 'available', 'assets/rooms/1774396541_premier-single.png',  300.00, 250.00, 1, 1, 'Double'),
(2, '2', 'Premier Twin Room',      2, 2600.00, 'available', 'assets/rooms/1774396551_premier-twin.png',    300.00, 250.00, 1, 2, 'Twin'),
(3, '3', 'Superior Twin Room',     2, 3000.00, 'available', 'assets/rooms/1774396558_superior-twin.png',   300.00, 250.00, 1, 2, 'Twin'),
(4, '4', 'Superior Quadruple Room',4, 3500.00, 'available', 'assets/rooms/1774396568_superior-quadruple.png', 300.00, 250.00, 1, 2, 'Double');


-- ------------------------------------------------------------
--  AMENITIES
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `amenities`;
CREATE TABLE `amenities` (
  `amenity_id`     INT          NOT NULL AUTO_INCREMENT,
  `amenity_name`   VARCHAR(100) NOT NULL,
  `description`    TEXT         DEFAULT NULL,
  `image_path`     TEXT         DEFAULT NULL,
  `amenity_status` ENUM('Available','Unavailable') DEFAULT 'Available',
  `created_at`     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`amenity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `amenities` (`amenity_name`, `description`, `amenity_status`) VALUES
('Swimming Pool',                    'Enjoy our refreshing outdoor swimming pool, perfect for leisure and relaxation.',                     'Available'),
('Nightly Acoustic Band',            'Live acoustic music every night to set the mood for a relaxing evening.',                             'Available'),
('Function Rooms',                   'Versatile function rooms available for all occasions including events, meetings, and celebrations.',   'Available'),
('Laundry Services',                 'Convenient laundry services available for guests during their stay.',                                  'Available'),
('24-Hour Security System',          'Round-the-clock security system to ensure the safety of all guests.',                                  'Available'),
('CCTV Cameras',                     'Property-wide CCTV surveillance for enhanced guest security.',                                        'Available'),
('Parking Area',                     'Dedicated and secured parking area available for guest vehicles.',                                     'Available'),
('Fully Automatic Fire Alarm System','State-of-the-art automatic fire alarm system for guest safety.',                                      'Available'),
('High Powered Standby Generator',   'High-powered standby generator ensures uninterrupted power supply during outages.',                   'Available');


-- ------------------------------------------------------------
--  RESERVATIONS
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
  `reservation_id`     INT          NOT NULL AUTO_INCREMENT,
  `guest_id`           INT UNSIGNED NOT NULL,
  `room_id`            INT          NOT NULL,
  `check_in_date`      DATE         NOT NULL,
  `check_out_date`     DATE         NOT NULL,
  `num_guests`         INT          NOT NULL DEFAULT 1,
  `extra_guests`       INT          NOT NULL DEFAULT 0,
  `extra_beds`         INT          NOT NULL DEFAULT 0,
  `total_amount`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `reservation_status` ENUM('Pending','Confirmed','Cancelled','Completed') DEFAULT 'Pending',
  `extra_requests`     TEXT         DEFAULT NULL,
  `created_at`         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`reservation_id`),
  CONSTRAINT `fk_reservation_guest` FOREIGN KEY (`guest_id`) REFERENCES `users`(`id`)  ON DELETE CASCADE,
  CONSTRAINT `fk_reservation_room`  FOREIGN KEY (`room_id`)  REFERENCES `rooms`(`room_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ------------------------------------------------------------
--  PAYMENTS
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `payment_id`       INT          NOT NULL AUTO_INCREMENT,
  `reservation_id`   INT          NOT NULL,
  `amount_paid`      DECIMAL(10,2) NOT NULL,
  `payment_method`   ENUM('Cash','GCash','Card') NOT NULL,
  `payment_status`   ENUM('Pending','Awaiting Verification','Completed','Rejected','Refunded') DEFAULT 'Pending',
  `reference_number` VARCHAR(100) DEFAULT NULL,
  `card_last4`       VARCHAR(4)   DEFAULT NULL,
  `notes`            TEXT         DEFAULT NULL,
  `payment_date`     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  CONSTRAINT `fk_payment_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations`(`reservation_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


SET FOREIGN_KEY_CHECKS = 1;