SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- 1. Tabla para guardar a los Usuarios/Clientes
CREATE TABLE `clientes` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nombre` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `telefono` varchar(20) DEFAULT NULL,
  `organizacion` varchar(150) DEFAULT NULL,
  `tipo_cliente` enum('Persona','Empresa') DEFAULT 'Empresa',
  `pais` varchar(50) DEFAULT 'México',
  `ubicacion_ciudad` varchar(100) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla de la Cotización (La Cabecera)
CREATE TABLE `cotizaciones` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `cliente_id` int(10) UNSIGNED NOT NULL,
  `codigo_cotizacion` varchar(20) NOT NULL,
  `fecha_solicitud` date NOT NULL,
  `vigencia_dias` int(11) DEFAULT 30,
  `notas_web` text DEFAULT NULL,
  `estado_cotizacion` enum('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
  `total` decimal(12,2) DEFAULT 0.00,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla de Detalles (Las piezas dentro de la cotización)
CREATE TABLE `cotizacion_detalles` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `cotizacion_id` int(10) UNSIGNED NOT NULL,
  `pieza_id` int(10) UNSIGNED NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




CREATE TABLE `historial_actividades` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario` varchar(100) NOT NULL DEFAULT 'Admin',
  `accion` varchar(50) NOT NULL COMMENT 'CREAR, EDITAR, ELIMINAR',
  `detalle` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `modulo` varchar(50) NOT NULL,
  `fecha_movimiento` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `marcas` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `modelos_bomba` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `marca_id` smallint(5) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `piezas_imagenes` (
  `id` int(10) UNSIGNED NOT NULL,
  `pieza_id` int(10) UNSIGNED NOT NULL,
  `ruta_imagen` varchar(500) NOT NULL,
  `orden` tinyint(4) DEFAULT 0,
  `es_principal` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `piezas_traducciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `pieza_id` int(10) UNSIGNED NOT NULL,
  `idioma` enum('es','en') NOT NULL DEFAULT 'es',
  `nombre` varchar(200) NOT NULL,
  `descripcion_tecnica` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `pieza_modelo` (
  `pieza_id` int(10) UNSIGNED NOT NULL,
  `modelo_id` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `piezas` (
  `id` int(10) UNSIGNED NOT NULL,
  `sku` varchar(40) NOT NULL COMMENT 'Código interno único',
  `nombre` varchar(200) NOT NULL,
  `marca_id` smallint(5) UNSIGNED NOT NULL,
  `descripcion_tecnica` text DEFAULT NULL,
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `imagen_ruta` varchar(500) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Aquí guardaremos la contraseña encriptada (Hash)
    rol ENUM('admin', 'vendedor') DEFAULT 'admin',
    estado ENUM('pendiente', 'activo', 'denegado') DEFAULT 'pendiente',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);