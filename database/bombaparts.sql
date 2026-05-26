-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-05-2026 a las 05:58:08
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bombaparts`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizaciones`
--

CREATE TABLE `cotizaciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo_cotizacion` varchar(20) NOT NULL,
  `cliente_nombre` varchar(150) NOT NULL,
  `cliente_email` varchar(100) DEFAULT NULL,
  `cliente_telefono` varchar(20) DEFAULT NULL,
  `organizacion` varchar(150) DEFAULT NULL,
  `tipo_cliente` enum('Persona','Empresa') DEFAULT 'Empresa',
  `ubicacion_ciudad` varchar(100) DEFAULT NULL,
  `pais` varchar(50) DEFAULT 'México',
  `estado_republica` varchar(50) DEFAULT NULL,
  `fecha_solicitud` date NOT NULL,
  `vigencia_dias` int(11) DEFAULT 30,
  `notas_web` text DEFAULT NULL,
  `estado_cotizacion` enum('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
  `total` decimal(12,2) DEFAULT 0.00,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cotizaciones`
--

INSERT INTO `cotizaciones` (`id`, `codigo_cotizacion`, `cliente_nombre`, `cliente_email`, `cliente_telefono`, `organizacion`, `tipo_cliente`, `ubicacion_ciudad`, `pais`, `estado_republica`, `fecha_solicitud`, `vigencia_dias`, `notas_web`, `estado_cotizacion`, `total`, `creado_en`, `precio_unitario`, `subtotal`) VALUES
(1, 'Q-20251110-48AB', 'Abel Corona', 'benskywalker2001@gmail.com', '241 146 4369', 'Corona', 'Empresa', 'Tlaxcala', 'México', 'Tlaxcala', '2025-11-10', 30, 'Preferencia de contacto: Email', 'aprobada', 0.00, '2026-04-25 05:35:05', 0.00, 0.00),
(2, 'Q-20260424-001', 'Abel Corona', 'abel@ejemplo.com', '2461234567', 'BombaParts Tlaxcala', '', 'Apizaco', 'México', 'Tlaxcala', '2026-04-24', 30, 'Requiero cotización para 5 impulsores SS304.', 'aprobada', 0.00, '2026-04-25 05:43:42', 0.00, 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_actividades`
--

CREATE TABLE `historial_actividades` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario` varchar(100) NOT NULL DEFAULT 'Admin',
  `accion` varchar(50) NOT NULL COMMENT 'CREAR, EDITAR, ELIMINAR',
  `detalle` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `modulo` varchar(50) NOT NULL,
  `fecha_movimiento` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `historial_actividades`
--

INSERT INTO `historial_actividades` (`id`, `usuario`, `accion`, `detalle`, `fecha`, `modulo`, `fecha_movimiento`) VALUES
(1, 'Admin', 'Aprobada', 'Se ha marcado como aprobada la cotización Q-20260424-001', '2026-04-25 16:39:01', 'Cotizaciones', '2026-04-25 10:39:01'),
(2, 'Admin', 'Aprobada', 'Se ha marcado como aprobada la cotización Q-20251110-48AB', '2026-04-25 20:01:27', 'Cotizaciones', '2026-04-25 14:01:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas`
--

CREATE TABLE `marcas` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `marcas`
--

INSERT INTO `marcas` (`id`, `nombre`, `slug`, `activo`, `creado_en`) VALUES
(1, 'Grundfos', 'grundfos', 1, '2026-04-25 03:07:48'),
(2, 'Xylem', 'xylem', 1, '2026-04-25 03:07:48'),
(3, 'Pedrollo', 'pedrollo', 1, '2026-04-25 03:07:48'),
(4, 'Pentax', 'pentax', 1, '2026-04-25 03:07:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modelos_bomba`
--

CREATE TABLE `modelos_bomba` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `marca_id` smallint(5) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `modelos_bomba`
--

INSERT INTO `modelos_bomba` (`id`, `marca_id`, `nombre`, `slug`, `activo`) VALUES
(1, 1, 'CM 3-5', 'cm-3-5', 1),
(2, 1, 'SP 5A-18', 'sp-5a-18', 1),
(3, 2, 'LCC 100-250', 'lcc-100-250', 1),
(4, 3, 'F 32/160A', 'f-32-160a', 1),
(5, 4, 'CAM 80/00', 'cam-80-00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `piezas`
--

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

--
-- Volcado de datos para la tabla `piezas`
--

INSERT INTO `piezas` (`id`, `sku`, `nombre`, `marca_id`, `descripcion_tecnica`, `precio_unitario`, `stock`, `imagen_ruta`, `activo`, `creado_en`, `actualizado_en`) VALUES
(3, 'BP-GRU-SEL-001', 'Sello Mecánico Carbono/Cerámica 12mm', 1, 'Sello de alta resistencia para serie CM.', 450.50, 15, 'uploads/piezas/sello.webp', 1, '2026-04-25 17:31:49', '2026-04-25 17:31:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `piezas_imagenes`
--

CREATE TABLE `piezas_imagenes` (
  `id` int(10) UNSIGNED NOT NULL,
  `pieza_id` int(10) UNSIGNED NOT NULL,
  `ruta_imagen` varchar(500) NOT NULL,
  `orden` tinyint(4) DEFAULT 0,
  `es_principal` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `piezas_traducciones`
--

CREATE TABLE `piezas_traducciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `pieza_id` int(10) UNSIGNED NOT NULL,
  `idioma` enum('es','en') NOT NULL DEFAULT 'es',
  `nombre` varchar(200) NOT NULL,
  `descripcion_tecnica` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pieza_modelo`
--

CREATE TABLE `pieza_modelo` (
  `pieza_id` int(10) UNSIGNED NOT NULL,
  `modelo_id` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pieza_modelo`
--

INSERT INTO `pieza_modelo` (`pieza_id`, `modelo_id`) VALUES
(3, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_cotizacion` (`codigo_cotizacion`);

--
-- Indices de la tabla `historial_actividades`
--
ALTER TABLE `historial_actividades`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_marcas_slug` (`slug`);

--
-- Indices de la tabla `modelos_bomba`
--
ALTER TABLE `modelos_bomba`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_modelos_slug` (`slug`),
  ADD KEY `idx_modelos_marca` (`marca_id`);

--
-- Indices de la tabla `piezas`
--
ALTER TABLE `piezas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_piezas_sku` (`sku`),
  ADD KEY `idx_piezas_marca` (`marca_id`),
  ADD KEY `idx_piezas_activo` (`activo`),
  ADD KEY `idx_piezas_stock` (`stock`),
  ADD KEY `idx_piezas_precio` (`precio_unitario`);

--
-- Indices de la tabla `piezas_imagenes`
--
ALTER TABLE `piezas_imagenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_img_pieza` (`pieza_id`);

--
-- Indices de la tabla `piezas_traducciones`
--
ALTER TABLE `piezas_traducciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_trad_pieza` (`pieza_id`);

--
-- Indices de la tabla `pieza_modelo`
--
ALTER TABLE `pieza_modelo`
  ADD PRIMARY KEY (`pieza_id`,`modelo_id`),
  ADD KEY `idx_pm_modelo` (`modelo_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `historial_actividades`
--
ALTER TABLE `historial_actividades`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `modelos_bomba`
--
ALTER TABLE `modelos_bomba`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `piezas`
--
ALTER TABLE `piezas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `piezas_imagenes`
--
ALTER TABLE `piezas_imagenes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `piezas_traducciones`
--
ALTER TABLE `piezas_traducciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `modelos_bomba`
--
ALTER TABLE `modelos_bomba`
  ADD CONSTRAINT `fk_modelos_marca` FOREIGN KEY (`marca_id`) REFERENCES `marcas` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `piezas`
--
ALTER TABLE `piezas`
  ADD CONSTRAINT `fk_piezas_marca` FOREIGN KEY (`marca_id`) REFERENCES `marcas` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `piezas_imagenes`
--
ALTER TABLE `piezas_imagenes`
  ADD CONSTRAINT `fk_img_pieza` FOREIGN KEY (`pieza_id`) REFERENCES `piezas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `piezas_traducciones`
--
ALTER TABLE `piezas_traducciones`
  ADD CONSTRAINT `fk_trad_pieza` FOREIGN KEY (`pieza_id`) REFERENCES `piezas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pieza_modelo`
--
ALTER TABLE `pieza_modelo`
  ADD CONSTRAINT `fk_pm_modelo` FOREIGN KEY (`modelo_id`) REFERENCES `modelos_bomba` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pm_pieza` FOREIGN KEY (`pieza_id`) REFERENCES `piezas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
