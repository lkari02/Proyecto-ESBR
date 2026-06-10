-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-06-2026 a las 19:35:43
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
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `organizacion` varchar(150) DEFAULT NULL,
  `tipo_cliente` enum('Persona','Empresa') DEFAULT 'Empresa',
  `pais` varchar(50) DEFAULT 'México',
  `ubicacion_ciudad` varchar(100) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizaciones`
--

CREATE TABLE `cotizaciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `cliente_id` int(10) UNSIGNED NOT NULL,
  `codigo_cotizacion` varchar(20) NOT NULL,
  `fecha_solicitud` date NOT NULL,
  `vigencia_dias` int(11) DEFAULT 30,
  `notas_web` text DEFAULT NULL,
  `estado_cotizacion` enum('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
  `total` decimal(12,2) DEFAULT 0.00,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizacion_detalles`
--

CREATE TABLE `cotizacion_detalles` (
  `id` int(10) UNSIGNED NOT NULL,
  `cotizacion_id` int(10) UNSIGNED NOT NULL,
  `pieza_id` int(10) UNSIGNED NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(0, 'Admin', 'APROBADA', 'Inicio de sesión: Abel Corona', '2026-06-04 03:46:41', 'Sistema', '2026-06-03 21:46:41'),
(0, 'Admin', 'APROBADA', 'Inicio de sesión: Abel Corona', '2026-06-05 15:17:37', 'Sistema', '2026-06-05 09:17:37'),
(0, 'Admin', 'CREAR', 'Se registró nueva pieza SKU: werivhberwihbg', '2026-06-05 16:28:44', 'Catálogo', '2026-06-05 10:28:44'),
(0, 'Admin', 'ELIMINAR', 'Se eliminó la pieza con SKU: werivhberwihbg', '2026-06-05 16:28:50', 'Catálogo', '2026-06-05 10:28:50'),
(0, 'Admin', 'CREAR', 'Se registró nueva pieza SKU: REF-DISC-BRZ62', '2026-06-05 16:37:06', 'Catálogo', '2026-06-05 10:37:06'),
(0, 'Admin', 'CREAR', 'Se registró nueva pieza SKU: REF-IMP-HGRIS', '2026-06-05 16:39:26', 'Catálogo', '2026-06-05 10:39:26'),
(0, 'Admin', 'CREAR', 'Se registró nueva pieza SKU: REF-EJE-4140T', '2026-06-05 16:42:43', 'Catálogo', '2026-06-05 10:42:43'),
(0, 'Admin', 'CREAR', 'Se registró nueva pieza SKU: REF-RET-VITRON', '2026-06-05 16:45:32', 'Catálogo', '2026-06-05 10:45:32'),
(0, 'Admin', 'CREAR', 'Se registró nueva pieza SKU: REF-ORI-NITR', '2026-06-05 16:54:24', 'Catálogo', '2026-06-05 10:54:24'),
(0, 'Admin', 'CREAR', 'Se registró nueva pieza SKU: BOM-SWP150-MP', '2026-06-05 16:58:27', 'Catálogo', '2026-06-05 10:58:27'),
(0, 'Admin', 'CREAR', 'Se registró nueva pieza SKU: BOM-SWK130-MP', '2026-06-05 17:01:11', 'Catálogo', '2026-06-05 11:01:11'),
(0, 'Admin', 'CREAR', 'Se registró nueva pieza SKU: BOM-SWK110-MP', '2026-06-05 17:03:47', 'Catálogo', '2026-06-05 11:03:47'),
(0, 'Admin', 'CREAR', 'Se registró nueva pieza SKU: BOM-DNC-SS410', '2026-06-05 17:05:19', 'Catálogo', '2026-06-05 11:05:19'),
(0, 'Admin', 'CREAR', 'Se registró nueva pieza SKU: BOM-VERT-POZO', '2026-06-05 17:07:24', 'Catálogo', '2026-06-05 11:07:24');

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
(1, 'ESBR', 'esbr', 1, '2026-04-25 09:07:48'),
(2, 'Xylem', 'xylem', 1, '2026-04-25 09:07:48'),
(3, 'Pedrollo', 'pedrollo', 1, '2026-04-25 09:07:48'),
(4, 'Pentax', 'pentax', 1, '2026-04-25 09:07:48');

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
(0, 'REF-DISC-BRZ62', 'Disco de equilibrio ', 1, 'Material: Fabricado en Bronce SAE 62 \"C”, con anillo fabricado en tubo mecánico y tornillos de latón.', 12.00, 12, NULL, 1, '2026-06-05 16:37:06', '2026-06-05 16:37:06'),
(0, 'REF-IMP-HGRIS', 'Impulsor', 1, 'Material: Fabricado en hierro gris\r\nUso: Impulsor cerrado con 4 aspas que genera fuerza centrífuga que impulsa el fluido hacia el exterior de la carcaza, este movimiento transforma energía mecánica en presión hidráulica permitiendo el transporte continuo del líquido a través de sistemas de conducción con eficiencia y velocidad.', 13.00, 13, NULL, 1, '2026-06-05 16:39:26', '2026-06-05 16:39:26'),
(0, 'REF-EJE-4140T', 'EJE', 1, 'Material: Fabricado en AISI 4140T\r\nUso: Es el eslabón mecánico de potencia de una bomba. Su función principal es recibir la potencia del motor y transmitirla al impulsor.', 14.00, 14, NULL, 1, '2026-06-05 16:42:43', '2026-06-05 16:42:43'),
(0, 'REF-RET-VITRON', 'Retén', 1, 'Material: Vitón\r\nUso: Sellado de alta calidad para contener lubricantes, aceites, grasas, proteger componentes internos de los equipos.', 15.00, 15, NULL, 1, '2026-06-05 16:45:32', '2026-06-05 16:45:32'),
(0, 'REF-ORI-NITR', 'O-RING', 1, 'Material: Nitrilo\r\nUso: Previene fugas, sellado confiable, resiste calor y aceite.\r\n', 11.00, 11, NULL, 1, '2026-06-05 16:54:24', '2026-06-05 16:54:24'),
(0, 'BOM-SWP150-MP', 'Bomba centrifuga  SWP 150', 1, 'Bomba centrifuga horizontal multipasos para alta presión\r\nMaterial: Fabricado en hierro gris, sellado con estoperos, retenes para el sellado de aceite, eje construido en AISI 4140T', 11.00, 11, NULL, 1, '2026-06-05 16:58:27', '2026-06-05 16:58:27'),
(0, 'BOM-SWK130-MP', 'Bomba centrifuga SWK 130', 1, 'Bomba centrifuga horizontal multipasos para alta presión\r\nMaterial: Fabricado en hierro gris, sellado con empaquetadura, bujes internos en bronce estándar, rodamientos de baleros.', 12.00, 12, NULL, 1, '2026-06-05 17:01:11', '2026-06-05 17:01:11'),
(0, 'BOM-SWK110-MP', 'Bomba horizontal  SWK 110', 1, '', 13.00, 13, NULL, 1, '2026-06-05 17:03:47', '2026-06-05 17:03:47'),
(0, 'BOM-DNC-SS410', 'DNC', 1, 'Material: Construido en acero inoxidable 410 en sus partes internas, (impulsores y ruedas directriz) acero al carbón en sus partes externas (elementos de armazón, tapas, cajas de estopero y estopero) hierro gris en los extremos, eje en acero 410 con recubrimiento de cromo duro en contacto con el agua, ejecución de pistón de compensación', 14.00, 14, NULL, 1, '2026-06-05 17:05:19', '2026-06-05 17:05:19'),
(0, 'BOM-VERT-POZO', 'BOMBAS VERTICALES TIPO POZO ', 1, 'Material: Eje fabricado en A.I. 316 rectificado, bujes fabricados en bronce SAE 62, tazones fabricados en hierro gris.\r\nUso: Diseñada específicamente para bombear líquidos limpios o poco contaminados desde depósitos profundos y pozos subterráneos.\r\nPartes:\r\nEje de bomba\r\nTazones múltiples \r\nImpulsores \r\nCampana de succión', 15.00, 16, NULL, 1, '2026-06-05 17:07:24', '2026-06-05 17:07:24');

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

--
-- Volcado de datos para la tabla `piezas_imagenes`
--

INSERT INTO `piezas_imagenes` (`id`, `pieza_id`, `ruta_imagen`, `orden`, `es_principal`) VALUES
(0, 0, '/Proyecto/public/admin/uploads/piezas/0_1780679244_0.png', 0, 0),
(0, 0, '/Proyecto/public/admin/uploads/piezas/0_1780679244_1.png', 1, 0),
(0, 0, '/Proyecto/public/admin/uploads/piezas/0_1780679244_2.png', 2, 0);

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

--
-- Volcado de datos para la tabla `piezas_traducciones`
--

INSERT INTO `piezas_traducciones` (`id`, `pieza_id`, `idioma`, `nombre`, `descripcion_tecnica`) VALUES
(0, 0, 'es', 'kjsfvnkjfsv', 'dovnebvkjude'),
(0, 0, 'es', 'Disco de equilibrio ', 'Material: Fabricado en Bronce SAE 62 \"C”, con anillo fabricado en tubo mecánico y tornillos de latón.'),
(0, 0, 'en', 'Balancing Disk', 'Material: Manufactured in SAE 62 \"C\" Bronze, featuring a ring made of mechanical tubing and brass screws. '),
(0, 0, 'es', 'Impulsor', 'Material: Fabricado en hierro gris\r\nUso: Impulsor cerrado con 4 aspas que genera fuerza centrífuga que impulsa el fluido hacia el exterior de la carcaza, este movimiento transforma energía mecánica en presión hidráulica permitiendo el transporte continuo del líquido a través de sistemas de conducción con eficiencia y velocidad.'),
(0, 0, 'en', 'Impeller', 'Material: Manufactured in gray iron. Use: Closed impeller with 4 blades that generates centrifugal force to drive the fluid to the outside of the casing. This movement transforms mechanical energy into hydraulic pressure, allowing the continuous transport of the liquid through conduction systems with efficiency and speed. '),
(0, 0, 'es', 'EJE', 'Material: Fabricado en AISI 4140T\r\nUso: Es el eslabón mecánico de potencia de una bomba. Su función principal es recibir la potencia del motor y transmitirla al impulsor.'),
(0, 0, 'en', 'Shaft', 'Material: Manufactured in AISI 4140T. Use: It is the mechanical power link of a pump. Its main function is to receive motor power and transmit it to the impeller. '),
(0, 0, 'es', 'Retén', 'Material: Vitón\r\nUso: Sellado de alta calidad para contener lubricantes, aceites, grasas, proteger componentes internos de los equipos.'),
(0, 0, 'en', 'Retainer', 'Material: Viton. Use: High-quality sealing to contain lubricants, oils, greases, and to protect internal equipment components. '),
(0, 0, 'es', 'O-RING', 'Material: Nitrilo\r\nUso: Previene fugas, sellado confiable, resiste calor y aceite.\r\n'),
(0, 0, 'en', 'O-RING', 'Material: Nitrile. Use: Prevents leaks, provides reliable sealing, resists heat and oil. '),
(0, 0, 'es', 'Bomba centrifuga  SWP 150', 'Bomba centrifuga horizontal multipasos para alta presión\r\nMaterial: Fabricado en hierro gris, sellado con estoperos, retenes para el sellado de aceite, eje construido en AISI 4140T'),
(0, 0, 'en', 'Centrifugal pump SWP 150', 'High-pressure multistage horizontal centrifugal pump. Material: Manufactured in gray iron, sealed with stuffing boxes, retainers for oil sealing, shaft built in AISI 4140T. '),
(0, 0, 'es', 'Bomba centrifuga SWK 130', 'Bomba centrifuga horizontal multipasos para alta presión\r\nMaterial: Fabricado en hierro gris, sellado con empaquetadura, bujes internos en bronce estándar, rodamientos de baleros.'),
(0, 0, 'en', 'Centrifugal pump SWK 130', 'High-pressure multistage horizontal centrifugal water pump. Material: Manufactured in gray iron, sealed with stuffing boxes. '),
(0, 0, 'es', 'Bomba horizontal  SWK 110', ''),
(0, 0, 'en', 'Horizontal Pump SWK 110', 'High-pressure multistage horizontal centrifugal water pump. Material: Manufactured in gray iron, sealed with stuffing boxes. '),
(0, 0, 'es', 'DNC', 'Material: Construido en acero inoxidable 410 en sus partes internas, (impulsores y ruedas directriz) acero al carbón en sus partes externas (elementos de armazón, tapas, cajas de estopero y estopero) hierro gris en los extremos, eje en acero 410 con recubrimiento de cromo duro en contacto con el agua, ejecución de pistón de compensación'),
(0, 0, 'en', 'DNC', 'Material: Constructed with 410 stainless steel in its internal parts (impellers and guide wheels), carbon steel in its external parts (frame elements, covers, stuffing box housings, and stuffing box). Ends made of gray iron, 410 steel shaft with hard chrome plating in contact with water, compensation piston execution. '),
(0, 0, 'es', 'BOMBAS VERTICALES TIPO POZO ', 'Material: Eje fabricado en A.I. 316 rectificado, bujes fabricados en bronce SAE 62, tazones fabricados en hierro gris.\r\nUso: Diseñada específicamente para bombear líquidos limpios o poco contaminados desde depósitos profundos y pozos subterráneos.\r\nPartes:\r\nEje de bomba\r\nTazones múltiples \r\nImpulsores \r\nCampana de succión'),
(0, 0, 'en', 'VERTICAL WELL PUMPS', 'Material: Shaft manufactured in ground A.I. 316, bushings manufactured in SAE 62 bronze, bowls manufactured in gray iron. Use: Designed specifically to pump clean or slightly contaminated liquids from deep tanks and underground wells. Parts: Pump shaft, multiple bowls, impellers, suction bell. ');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pieza_modelo`
--

CREATE TABLE `pieza_modelo` (
  `pieza_id` int(10) UNSIGNED NOT NULL,
  `modelo_id` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','vendedor') DEFAULT 'vendedor',
  `estado` enum('pendiente','activo','denegado') DEFAULT 'pendiente',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `estado`, `creado_en`) VALUES
(1, 'Abel Corona', 'abelabdielcoronafranco2000@gmail.com', '$2y$10$pND9j.legTFzRm9iZN2lB.s2ZcxuCYs.uap6LWw8zRYUbijLX6W5a', 'admin', 'activo', '2026-06-04 03:40:38');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `cotizacion_detalles`
--
ALTER TABLE `cotizacion_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cotizacion_id` (`cotizacion_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cotizacion_detalles`
--
ALTER TABLE `cotizacion_detalles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD CONSTRAINT `cotizaciones_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cotizacion_detalles`
--
ALTER TABLE `cotizacion_detalles`
  ADD CONSTRAINT `cotizacion_detalles_ibfk_1` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
