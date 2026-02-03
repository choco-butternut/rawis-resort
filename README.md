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

create Tables

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
    room_status VARCHAR(20) NOT NULL
);

```

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
