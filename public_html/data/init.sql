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
    idealPlantSpecies VARCHAR(30),
    idealPH VARCHAR(10),
    idealTemp VARCHAR(10),
    idealLight VARCHAR(10),
    idealMoisture VARCHAR(10),
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

  INSERT INTO users(firstname, lastname, email)
  VALUES
    ("Jeff", "Bridges", "jbridges@test.com"),
    ("Jennifer", "Anniston", "janniston@test.com"),
    ("Brad", "Pitt", "bpitt@test.com"),
    ("Oprah", "Winfrey", "owinfrey@test.com"),
    ("Zach", "Efron", "zefron@test.com");

INSERT INTO devices(ownerID, idealPlantSpecies, idealPH, idealTemp, idealLight, idealMoisture)
  VALUES
    (1, "Fern", "5", "75", "375", "560"),
    (1, "Palm", "6", "72", "400", "510"),
    (2, "Rose", "7", "64", "460", "465"),
    (2, "Lilac", "8", "68", "430", "490"),
    (3, "Lily", "8", "69", "570", "485"),
    (3, "Tomato", "6", "70", "545", "530"),
    (4, "Orchid", "7", "73", "560", "525"),
    (4, "Sunflower", "7", "72", "600", "470"),
    (5, "Succulent", "6", "71", "585", "415"),
    (5, "Ficus", "7", "68", "500", "425"),
    (5, "Snakeplant", "8", "70", "500", "500");

INSERT INTO data(deviceID, plantSpecies, pH, temp, light, moisture)
  VALUES
    (1, "Fern", "5", "75", "375", "560"),
    (1, "Fern", "5", "75", "375", "560"),
    (1, "Fern", "5", "75", "375", "560"),
    (1, "Fern", "5", "75", "375", "560"),
    (1, "Fern", "5", "75", "375", "560"),
    (1, "Fern", "5", "75", "375", "560"),
    (1, "Fern", "5", "75", "375", "560"),
    (2, "Palm", "6", "72", "400", "510"),
    (2, "Palm", "6", "72", "400", "510"),
    (2, "Palm", "6", "72", "400", "510"),
    (2, "Palm", "6", "72", "400", "510"),
    (2, "Palm", "6", "72", "400", "510"),
    (2, "Palm", "6", "72", "400", "510"),
    (2, "Palm", "6", "72", "400", "510"),
    (3, "Rose", "7", "64", "460", "465"),
    (3, "Rose", "7", "64", "460", "465"),
    (3, "Rose", "7", "64", "460", "465"),
    (3, "Rose", "7", "64", "460", "465"),
    (3, "Rose", "7", "64", "460", "465"),
    (3, "Rose", "7", "64", "460", "465"),
    (4, "Lilac", "8", "68", "430", "490"),
    (4, "Lilac", "8", "68", "430", "490"),
    (4, "Lilac", "8", "68", "430", "490"),
    (4, "Lilac", "8", "68", "430", "490"),
    (4, "Lilac", "8", "68", "430", "490"),
    (5, "Lily", "8", "69", "570", "485"),
    (5, "Lily", "8", "69", "570", "485"),
    (5, "Lily", "8", "69", "570", "485"),
    (5, "Lily", "8", "69", "570", "485"),
    (5, "Lily", "8", "69", "570", "485"),
    (5, "Lily", "8", "69", "570", "485"),
    (6, "Tomato", "6", "70", "545", "530"),
    (6, "Tomato", "6", "70", "545", "530"),
    (6, "Tomato", "6", "70", "545", "530"),
    (6, "Tomato", "6", "70", "545", "530"),
    (6, "Tomato", "6", "70", "545", "530"),
    (6, "Tomato", "6", "70", "545", "530"),
    (6, "Tomato", "6", "70", "545", "530"),
    (7, "Orchid", "7", "73", "560", "525"),
    (7, "Orchid", "7", "73", "560", "525"),
    (7, "Orchid", "7", "73", "560", "525"),
    (7, "Orchid", "7", "73", "560", "525"),
    (7, "Orchid", "7", "73", "560", "525"),
    (7, "Orchid", "7", "73", "560", "525"),
    (7, "Orchid", "7", "73", "560", "525"),
    (8, "Sunflower", "7", "72", "600", "470"),
    (8, "Sunflower", "7", "72", "600", "470"),
    (8, "Sunflower", "7", "72", "600", "470"),
    (8, "Sunflower", "7", "72", "600", "470"),
    (8, "Sunflower", "7", "72", "600", "470"),
    (8, "Sunflower", "7", "72", "600", "470"),
    (8, "Sunflower", "7", "72", "600", "470"),
    (8, "Sunflower", "7", "72", "600", "470"),
    (9, "Succulent", "6", "71", "585", "415"),
    (9, "Succulent", "6", "71", "585", "415"),
    (9, "Succulent", "6", "71", "585", "415"),
    (9, "Succulent", "6", "71", "585", "415"),
    (9, "Succulent", "6", "71", "585", "415"),
    (9, "Succulent", "6", "71", "585", "415"),
    (9, "Succulent", "6", "71", "585", "415"),
    (9, "Succulent", "6", "71", "585", "415"),
    (9, "Succulent", "6", "71", "585", "415"),
    (10, "Ficus", "7", "68", "500", "425"),
    (10, "Ficus", "7", "68", "500", "425"),
    (10, "Ficus", "7", "68", "500", "425"),
    (10, "Ficus", "7", "68", "500", "425"),
    (10, "Ficus", "7", "68", "500", "425"),
    (10, "Ficus", "7", "68", "500", "425"),
    (10, "Ficus", "7", "68", "500", "425"),
    (10, "Ficus", "7", "68", "500", "425"),
    (10, "Ficus", "7", "68", "500", "425"),
    (11, "Snakeplant", "8", "70", "500", "500"),
    (11, "Snakeplant", "8", "70", "500", "500"),
    (11, "Snakeplant", "8", "70", "500", "500"),
    (11, "Snakeplant", "8", "70", "500", "500"),
    (11, "Snakeplant", "8", "70", "500", "500"),
    (11, "Snakeplant", "8", "70", "500", "500"),
    (11, "Snakeplant", "8", "70", "500", "500");
