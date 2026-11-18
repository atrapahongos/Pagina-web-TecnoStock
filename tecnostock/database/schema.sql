CREATE DATABASE IF NOT EXISTS tecnostock_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tecnostock_db;

DROP TABLE IF EXISTS movimientos;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS usuarios;

-- 1. Usuarios
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    clave VARCHAR(255) NOT NULL,
    estado TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Categorías
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    estado TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Productos
CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    precio DECIMAL(10,2) NOT NULL CHECK (precio >= 0),
    stock_actual INT NOT NULL DEFAULT 0 CHECK (stock_actual >= 0),
    stock_minimo INT NOT NULL DEFAULT 0 CHECK (stock_minimo >= 0),
    estado TINYINT(1) DEFAULT 1,
    id_categoria INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_producto_categoria FOREIGN KEY (id_categoria) 
        REFERENCES categorias(id_categoria) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Movimientos
CREATE TABLE movimientos (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo ENUM('ENTRADA', 'SALIDA') NOT NULL,
    cantidad INT NOT NULL CHECK (cantidad > 0),
    observacion TEXT NULL,
    id_producto INT NOT NULL,
    id_usuario INT NOT NULL,
    CONSTRAINT fk_movimiento_producto FOREIGN KEY (id_producto) 
        REFERENCES productos(id_producto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_movimiento_usuario FOREIGN KEY (id_usuario) 
        REFERENCES usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos Semilla
INSERT INTO usuarios (nombre, correo, clave, estado) VALUES
('Encargado de Inventario', 'admin@tecnostock.cl', '$2y$10$4n9/MhV8Y3vM/B6XfP4J5.T7b8p0Y6sV7n7s9Z5n5s5s5s5s5s5s', 1);

INSERT INTO categorias (nombre, descripcion, estado) VALUES
('Audio y Sonido', 'Audífonos, parlantes bluetooth y micrófonos', 1),
('Cables y Adaptadores', 'Cables HDMI, USB-C, adaptadores multipuerto', 1),
('Almacenamiento', 'Pendrives, discos externos y tarjetas SD', 1),
('Periféricos y Accesorios', 'Mouses, teclados, fundas y soportes', 1);

INSERT INTO productos (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, estado, id_categoria) VALUES
('PROD-001', 'Audífonos Bluetooth Pro', 'Audífonos in-ear con cancelación de ruido', 29990.00, 15, 5, 1, 1),
('PROD-002', 'Cable USB-C a HDMI 4K', 'Cable reforzado de nylon 2 metros', 12990.00, 3, 10, 1, 2),
('PROD-003', 'Pendrive 64GB USB 3.2', 'Memoria metálica resistente al agua', 8990.00, 25, 8, 1, 3);

INSERT INTO movimientos (fecha_hora, tipo, cantidad, observacion, id_producto, id_usuario) VALUES
(NOW(), 'ENTRADA', 20, 'Stock inicial de importación', 1, 1),
(NOW(), 'SALIDA', 5, 'Venta corporativa', 1, 1);
