# Rawis-resort
Website created for Rawis Resort

# Initialization

install necessary packages

```
composer require vlucas/phpdotenv
composer require phpmailer/phpmailer
```

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
for creating tables refer to /database


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
