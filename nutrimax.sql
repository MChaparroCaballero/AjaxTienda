-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-01-2026 a las 13:35:19
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `nutrimax`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carro`
--

CREATE TABLE `carro` (
  `CodCarro` int(11) NOT NULL,
  `Usuario` varchar(100) NOT NULL,
  `Fecha` datetime NOT NULL,
  `Total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Enviado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carro`
--

INSERT INTO `carro` (`CodCarro`, `Usuario`, `Fecha`, `Total`, `Enviado`) VALUES
(39, 'mchaparro@gmail.com', '2025-12-03 19:24:40', 4.60, 1),
(40, 'mchaparro@gmail.com', '2025-12-03 19:26:35', 4.00, 1),
(41, 'mchaparro@gmail.com', '2025-12-04 10:04:04', 2.15, 1),
(42, 'mchaparro@gmail.com', '2025-12-10 09:56:44', 4.50, 1),
(43, 'mchaparro@gmail.com', '2025-12-10 09:57:20', 21.95, 1),
(44, 'mchaparro@gmail.com', '2025-12-10 18:38:36', 2.50, 1),
(45, 'mchaparro@gmail.com', '2025-12-10 18:40:26', 6.95, 1),
(46, 'mchaparro@gmail.com', '2026-01-16 10:26:50', 4.10, 1),
(47, 'mchaparro@gmail.com', '2026-01-16 12:49:58', 0.25, 1),
(48, 'mchaparro@gmail.com', '2026-01-16 13:08:45', 1.20, 1),
(49, 'mchaparro@gmail.com', '2026-01-16 13:09:11', 1.20, 1),
(50, 'mchaparro@gmail.com', '2026-01-16 13:11:38', 0.50, 1),
(51, 'mchaparro@gmail.com', '2026-01-16 13:16:13', 0.75, 1),
(52, 'mchaparro@gmail.com', '2026-01-16 13:17:08', 0.50, 1),
(53, 'mchaparro@gmail.com', '2026-01-16 13:30:31', 0.00, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carroproductos`
--

CREATE TABLE `carroproductos` (
  `CodCarroProd` int(11) NOT NULL,
  `CodCarro` int(11) NOT NULL,
  `CodProd` int(11) NOT NULL,
  `Unidades` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carroproductos`
--

INSERT INTO `carroproductos` (`CodCarroProd`, `CodCarro`, `CodProd`, `Unidades`) VALUES
(112, 39, 11, 2),
(114, 40, 10, 1),
(117, 41, 7, 1),
(118, 41, 12, 1),
(121, 42, 4, 1),
(122, 42, 12, 1),
(125, 43, 22, 1),
(128, 44, 4, 1),
(131, 45, 1, 10),
(132, 45, 3, 3),
(133, 45, 2, 1),
(134, 46, 3, 5),
(136, 46, 2, 2),
(137, 46, 9, 1),
(138, 47, 3, 1),
(139, 48, 2, 1),
(141, 49, 2, 1),
(142, 50, 1, 1),
(143, 51, 1, 1),
(144, 51, 3, 1),
(145, 52, 3, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `CodCat` int(11) NOT NULL,
  `Nombre` varchar(45) NOT NULL,
  `Descripcion` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`CodCat`, `Nombre`, `Descripcion`) VALUES
(1, 'Frutas', 'Frutas de españas'),
(2, 'Verduras', 'Verduleria'),
(3, 'Gluten', 'productos con gluten'),
(4, 'Cereales', 'Cereales'),
(5, 'Carnes y embutidos', 'Carnes varias'),
(6, 'Pescados', 'Pescados frescos'),
(7, 'Lacteos', 'Alimentos derivados de la leche'),
(8, 'Legumbres', 'Semillas comestibles de las plantas leguminosas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `CodProd` int(11) NOT NULL,
  `Nombre` varchar(45) DEFAULT NULL,
  `Descripcion` varchar(90) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `CodCat` int(11) NOT NULL,
  `Precio` decimal(10,2) NOT NULL,
  `estado` varchar(50) NOT NULL DEFAULT 'catalogado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`CodProd`, `Nombre`, `Descripcion`, `stock`, `CodCat`, `Precio`, `estado`) VALUES
(1, 'Manzana', 'Manzana Envy: Crujiente, dulce y jugosa.', 18, 1, 0.50, 'catalogado'),
(2, 'Avocado', 'Aguacates Hass cremosos, perfectos para guacamole.', 2, 1, 1.20, 'catalogado'),
(3, 'Bananas', 'Plátanos de Canarias: Dulces, cultivo tradicional.', 2, 1, 0.25, 'catalogado'),
(4, 'Arandanos', 'Arándanos frescos, alto contenido en fibra.', 21, 1, 2.50, 'catalogado'),
(5, 'Pan', 'Pan de Masa Madre artesanal. Horneado diariamente.', 28, 3, 2.80, 'catalogado'),
(6, 'Cebollas', 'Cebollas enteras', 13, 2, 0.20, 'catalogado'),
(7, 'Zanahorias', 'Zanahorias Gigantes, dulces. Ideales para zumos.', 38, 2, 0.15, 'catalogado'),
(8, 'Naranjas', 'Naranjas Navel de Valencia. Muy jugosas.', 34, 1, 0.30, 'catalogado'),
(9, 'Peras', 'Peras Conferencia. Pulpa fina, muy jugosa.', 44, 1, 0.45, 'catalogado'),
(10, 'Sandias', 'Sandías sin Pepitas. Refrescantes y con agua.', 23, 1, 4.00, 'catalogado'),
(11, 'Bacon', 'Bacón ahumado Monells lonchas', 6, 5, 2.30, 'catalogado'),
(12, 'Chocodays', 'Cereales copos de trigo Chocodays Hacendado con chocolate', 93, 4, 2.00, 'catalogado'),
(13, 'Fumet de pescado de roca y marisco', 'Gallina Blanca sin gluten brik 1 l', 30, 6, 3.89, 'catalogado'),
(14, 'Gula del Norte', 'La Auténtica La Gula del Norte 100g', 50, 6, 4.50, 'catalogado'),
(15, 'Pulpo', 'Patas de pulpo cocido 270g', 66, 6, 9.89, 'catalogado'),
(16, 'Mejillones', 'Mejillón fresco cocido en su jugo 900 g', 70, 6, 6.95, 'catalogado'),
(17, 'Leche de nueces', 'Puleva omega 3 leche de nueces 1l', 99, 7, 1.75, 'catalogado'),
(18, 'Café caramel macchiato', 'Bebida láctea con café caramel macchiato Go Chill 230ml', 55, 7, 1.93, 'catalogado'),
(19, 'Yogur de fresa', 'Nestle, Yogur de fresa Yogolino 100g', 89, 7, 1.50, 'catalogado'),
(20, 'Preparado lacteo', 'Nutribén innova preparado lacteo de crecimiento 800g', 99, 7, 2.00, 'catalogado'),
(21, 'Lentejas', 'Lenteja pardina Don Pedro 1kg', 99, 8, 4.09, 'catalogado'),
(22, 'Garbanzos', 'Garbanzo baby cocido Don Pedro 400g', 97, 8, 1.95, 'catalogado'),
(23, 'Alubias', 'Alubia pinta categoría extra Luengo 1kg', 100, 8, 4.55, 'catalogado'),
(24, 'Alubias frijol', 'Alubia frijol La Cochura 1 kg', 100, 8, 2.89, 'catalogado'),
(25, 'Brownies', 'Bizcocho brownie Milka 150g', 95, 3, 3.99, 'catalogado'),
(26, 'Berlinas', 'Berlinas Christmas Collection Dunkin 4 ud', 100, 3, 5.50, 'catalogado'),
(27, 'Magdalenas', 'Magdalenas al cacao con pepitas de chocolate La Bella Easo 8 ud', 100, 3, 2.75, 'catalogado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `ID` int(10) NOT NULL,
  `NOMBRE` varchar(100) NOT NULL,
  `GMAIL` varchar(200) NOT NULL,
  `CLAVE` varchar(200) NOT NULL,
  `rol` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`ID`, `NOMBRE`, `GMAIL`, `CLAVE`, `rol`) VALUES
(1, 'Maria', 'mchaparro@gmail.com', '12345', 1),
(2, 'Fran', 'Fran@gmail.com', '12345', 0),
(3, 'Victor', 'Victor@gmail.com', '12345', 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carro`
--
ALTER TABLE `carro`
  ADD PRIMARY KEY (`CodCarro`),
  ADD KEY `fk_carro_usuario` (`Usuario`);

--
-- Indices de la tabla `carroproductos`
--
ALTER TABLE `carroproductos`
  ADD PRIMARY KEY (`CodCarroProd`),
  ADD KEY `CodCarro` (`CodCarro`),
  ADD KEY `CodProd` (`CodProd`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`CodCat`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`CodProd`),
  ADD KEY `CodCat` (`CodCat`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `GMAIL` (`GMAIL`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carro`
--
ALTER TABLE `carro`
  MODIFY `CodCarro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `carroproductos`
--
ALTER TABLE `carroproductos`
  MODIFY `CodCarroProd` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `CodCat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `CodProd` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carro`
--
ALTER TABLE `carro`
  ADD CONSTRAINT `fk_carro_usuario` FOREIGN KEY (`Usuario`) REFERENCES `usuarios` (`GMAIL`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `carroproductos`
--
ALTER TABLE `carroproductos`
  ADD CONSTRAINT `carroproductos_ibfk_1` FOREIGN KEY (`CodCarro`) REFERENCES `carro` (`CodCarro`),
  ADD CONSTRAINT `carroproductos_ibfk_2` FOREIGN KEY (`CodProd`) REFERENCES `productos` (`CodProd`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`CodCat`) REFERENCES `categoria` (`CodCat`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
