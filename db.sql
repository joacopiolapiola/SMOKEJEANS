-- ============================================
-- DATABASE
-- ============================================

DROP DATABASE IF EXISTS smoke;
CREATE DATABASE smoke;
USE smoke;


-- ============================================
-- TABLES
-- ============================================

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    dispo TINYINT(1) DEFAULT 1,
    subido TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,

    FOREIGN KEY (producto_id)
        REFERENCES productos(id)
        ON DELETE CASCADE
);

CREATE TABLE prod_categoria (
    prod_id INT NOT NULL,
    categoria_id INT NOT NULL,

    PRIMARY KEY (prod_id, categoria_id),

    FOREIGN KEY (prod_id)
        REFERENCES productos(id)
        ON DELETE CASCADE,

    FOREIGN KEY (categoria_id)
        REFERENCES categorias(id)
        ON DELETE CASCADE
);


-- ============================================
-- MYSQL USERS
-- ============================================

DROP USER IF EXISTS 'smoke_client'@'localhost';
DROP USER IF EXISTS 'smoke_admin'@'localhost';

CREATE USER 'smoke_client'@'localhost'
IDENTIFIED BY 'CHANGE_CLIENT_PASSWORD';

CREATE USER 'smoke_admin'@'localhost'
IDENTIFIED BY 'CHANGE_ADMIN_PASSWORD';


-- ============================================
-- CLIENT PERMISSIONS
-- Can SELECT everything except admins
-- ============================================

GRANT SELECT ON smoke.categorias
TO 'smoke_client'@'localhost';

GRANT SELECT ON smoke.product_images
TO 'smoke_client'@'localhost';

GRANT SELECT ON smoke.productos
TO 'smoke_client'@'localhost';

GRANT SELECT ON smoke.prod_categoria
TO 'smoke_client'@'localhost';


-- ============================================
-- ADMIN PERMISSIONS
-- Full access to the database
-- ============================================

GRANT ALL PRIVILEGES ON smoke.*
TO 'smoke_admin'@'localhost';

FLUSH PRIVILEGES;
