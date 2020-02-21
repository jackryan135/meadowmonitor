DROP DATABASE IF EXISTS meadow_monitor;
CREATE DATABASE meadow_monitor;

  use meadow_monitor;

  CREATE TABLE users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(30) NOT NULL,
    lastname VARCHAR(30) NOT NULL,
    email VARCHAR(50) NOT NULL,
    date TIMESTAMP
  ) ENGINE=INNODB;

  CREATE TABLE devices (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ownerID INT(11) UNSIGNED NOT NULL,
    plantSpecies VARCHAR(30),
    date TIMESTAMP,
    CONSTRAINT fk_owner
    FOREIGN KEY (ownerID)
        REFERENCES users(id)
        ON DELETE CASCADE
  ) ENGINE=INNODB;

  CREATE TABLE data (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    deviceID INT(11) UNSIGNED NOT NULL,
    plantSpecies VARCHAR(30),
    ph VARCHAR(10),
    temp VARCHAR(10),
    light VARCHAR(10),
    moisture VARCHAR(10),
    date TIMESTAMP,
    CONSTRAINT fk_device
    FOREIGN KEY (deviceID)
        REFERENCES devices(id)
        ON DELETE CASCADE
  ) ENGINE=INNODB;