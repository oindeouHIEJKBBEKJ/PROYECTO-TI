-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-11-2025 a las 06:16:09
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
-- Base de datos: `sistema_egresados`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ofertas_laborales`
--

CREATE TABLE `ofertas_laborales` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `empresa` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `requisitos` text DEFAULT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `estado` enum('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
  `semaforo` enum('verde','amarillo','rojo') DEFAULT 'verde',
  `usuario_publica_id` int(11) DEFAULT NULL,
  `fecha_publicacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_expiracion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ofertas_laborales`
--

INSERT INTO `ofertas_laborales` (`id`, `titulo`, `empresa`, `descripcion`, `requisitos`, `contacto`, `estado`, `semaforo`, `usuario_publica_id`, `fecha_publicacion`, `fecha_expiracion`) VALUES
(1, 'Desarrolo de HIGEO', 'Universidad Tecnológica de Puebla', 'plataforma educativa', 'html,css,js', 'manolo@utpebla.edu.mx', 'aprobada', 'rojo', 4, '2025-11-23 06:00:00', '2025-11-24'),
(2, 'Biblioteca', 'Universidad Tecnológica de Puebla', 'Sofware de libros', 'JS, CSS, HTML', 'biblioteca@gmail.com', 'aprobada', 'rojo', 4, '2025-11-24 06:00:00', '2025-11-25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `postulaciones`
--

CREATE TABLE `postulaciones` (
  `id` int(11) NOT NULL,
  `oferta_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha_postulacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','rechazada','aceptada') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `postulaciones`
--

INSERT INTO `postulaciones` (`id`, `oferta_id`, `usuario_id`, `fecha_postulacion`, `estado`) VALUES
(1, 1, 2, '2025-11-24 05:13:38', 'aceptada'),
(2, 2, 2, '2025-11-25 04:45:38', 'aceptada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguimiento_egresados`
--

CREATE TABLE `seguimiento_egresados` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `genero` enum('masculino','femenino','otro') DEFAULT NULL,
  `ano_nacimiento` year(4) DEFAULT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `generacion` varchar(20) DEFAULT NULL,
  `experiencia_laboral` text DEFAULT NULL,
  `habilidades` text DEFAULT NULL,
  `areas_interes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `seguimiento_egresados`
--

INSERT INTO `seguimiento_egresados` (`id`, `usuario_id`, `genero`, `ano_nacimiento`, `especialidad`, `generacion`, `experiencia_laboral`, `habilidades`, `areas_interes`) VALUES
(1, 2, 'femenino', '2005', 'Desarrolladora', '2023-2026', 'Mucha', 'respeto, altruismo,etica,trabajo en equipo', 'software');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `matricula` varchar(20) DEFAULT NULL,
  `curp` varchar(18) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `tipo_usuario` enum('egresado','docente','ti','admin') NOT NULL,
  `id_docente` varchar(20) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_completo`, `matricula`, `curp`, `email`, `telefono`, `tipo_usuario`, `id_docente`, `username`, `password_hash`, `fecha_registro`, `activo`) VALUES
(2, 'Aracely Guadalupe Perez Ramon', '2321082570', 'PELM041221HPLRPNA6', 'manuelpl9965@gmail.com', '2212105960', 'egresado', '', 'aracely.guadalupe.perez.ramon', '$2y$10$jqG2BPvMGGQpmTUivWaREeIBk0ZAQRcXrgYWybYu438NlyTXFx2Iu', '2025-11-06 19:02:57', 1),
(4, 'Manolo21', '2311080828', 'PELM041221HPLRPNA7', 'manolo@gmail.com', '2212105961', 'docente', '1', 'manolo21', '$2y$10$SpfDRL7owujiMukbpaNk7OQ6ZTLjXICFAvEH4eH8qBu41p020xQMi', '2025-11-19 16:33:33', 1),
(6, 'Aruel', '2311080820', 'PELM041221HPLRPNA0', 'aruel@alumno.utpuebla.edu.mx', '2212105961', 'ti', '', 'aruel', '$2y$10$7q0vAAwlWwfC1IpyTZLInejkRRFjIPlIXG9JLhtv8muPU.1KwTaqe', '2025-11-24 06:00:11', 1),
(8, 'Administrador Principal', NULL, NULL, 'admin@utpuebla.edu.mx', NULL, 'admin', NULL, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-11-24 06:05:46', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ofertas_laborales`
--
ALTER TABLE `ofertas_laborales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_publica_id` (`usuario_publica_id`);

--
-- Indices de la tabla `postulaciones`
--
ALTER TABLE `postulaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oferta_id` (`oferta_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `seguimiento_egresados`
--
ALTER TABLE `seguimiento_egresados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `matricula` (`matricula`),
  ADD UNIQUE KEY `curp` (`curp`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ofertas_laborales`
--
ALTER TABLE `ofertas_laborales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `postulaciones`
--
ALTER TABLE `postulaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `seguimiento_egresados`
--
ALTER TABLE `seguimiento_egresados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `ofertas_laborales`
--
ALTER TABLE `ofertas_laborales`
  ADD CONSTRAINT `ofertas_laborales_ibfk_1` FOREIGN KEY (`usuario_publica_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `postulaciones`
--
ALTER TABLE `postulaciones`
  ADD CONSTRAINT `postulaciones_ibfk_1` FOREIGN KEY (`oferta_id`) REFERENCES `ofertas_laborales` (`id`),
  ADD CONSTRAINT `postulaciones_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `seguimiento_egresados`
--
ALTER TABLE `seguimiento_egresados`
  ADD CONSTRAINT `seguimiento_egresados_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
