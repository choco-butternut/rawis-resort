# Rawis-resort
Website created for Rawis Resort

# Initialization
## For linux

create database
```
sudo mysql -u root -p
CREATE DATABASE rawis_resort_db;
USE rawis_resort_db;
```

grant to user and use a password

```
CREATE USER 'root'@'localhost' IDENTIFIED BY 'gabmontes';
GRANT ALL PRIVILEGES ON my_database.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

create tables

```
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    phone_number VARCHAR(255),
    email VARCHAR(255) NOT NULL UNIQUE,
    address VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    role ENUM('admin','guest') NOT NULL DEFAULT 'guest'
);

CREATE TABLE rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(20) NOT NULL UNIQUE,
    room_type VARCHAR(50) NOT NULL,
    max_capacity INT NOT NULL,
    price_per_night DECIMAL(10,2) NOT NULL,
    room_status VARCHAR(20) NOT NULL,
    image_path TEXT
);

CREATE TABLE reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    guest_id INT UNSIGNED NOT NULL,
    room_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    num_guests INT NOT NULL,
    reservation_status ENUM('Pending','Confirmed','Cancelled','Completed') DEFAULT 'Pending',
    extra_requests TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_reservation_guest
        FOREIGN KEY (guest_id) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_reservation_room
        FOREIGN KEY (room_id) REFERENCES rooms(room_id)
        ON DELETE CASCADE
);

CREATE TABLE amenities (
    amenity_id INT AUTO_INCREMENT PRIMARY KEY,
    amenity_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) DEFAULT 0.00,
    amenity_status ENUM('Available','Unavailable') DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reservation_amenities (
    reservation_id INT NOT NULL,
    amenity_id INT NOT NULL,
    quantity INT DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (reservation_id, amenity_id),

    CONSTRAINT fk_ra_reservation
        FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_ra_amenity
        FOREIGN KEY (amenity_id) REFERENCES amenities(amenity_id)
        ON DELETE CASCADE
);

CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Cash','GCash','Card') NOT NULL,
    payment_status ENUM('Pending','Completed','Refunded') DEFAULT 'Pending',
    reference_number VARCHAR(100),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_payment_reservation
        FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id)
        ON DELETE CASCADE
);



```

## For Windows

download XAMPP

```
cd C:\xampp\mysql\bin
mysql -u root
```

create Database
```
CREATE DATABASE rawis_resort_db;
USE rawis_resort_db;
```

add new user with password

```
CREATE USER 'rawis_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON rawis_resort_db.* TO 'rawis_user'@'localhost';
FLUSH PRIVILEGES;
```

create Tables(see For linux)

change necessary info on php/config.php

```
define("DB_HOST","localhost");
define("DB_USER","user_name");
define("DB_PASS","password");
define("DB_NAME","rawis_resort_db");
```

# Running

## For linux and windows

```
cd path/to/project
php -S localhost:8000
```

if php not recognized

Add PHP to PATH
or use full path 
```
C:\xampp\php\php.exe -S localhost:8000
```
