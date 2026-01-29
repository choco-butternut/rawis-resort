# rawis-resort
A responsive website created for Rawis Resort to showcase its facilities, services, and contact information in a clean and user-friendly layout.

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