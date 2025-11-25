-- SQL: Estructura mínima de tablas para el sistema de ofertas
-- Ejecutar en la base de datos `sistema_egresados` (phpMyAdmin o MySQL CLI)

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `postulaciones`;
DROP TABLE IF EXISTS `ofertas_laborales`;
DROP TABLE IF EXISTS `usuarios`;

SET NAMES utf8mb4;

CREATE TABLE `usuarios` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre_completo` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','docente','egresado','ti') NOT NULL DEFAULT 'egresado',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ofertas_laborales` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  `empresa` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `requisitos` TEXT DEFAULT NULL,
  `contacto` VARCHAR(255) DEFAULT NULL,
  `fecha_publicacion` DATE DEFAULT NULL,
  `fecha_expiracion` DATE DEFAULT NULL,
  `semaforo` ENUM('verde','amarillo','rojo') DEFAULT 'verde',
  `usuario_publica_id` INT DEFAULT NULL,
  `estado` VARCHAR(50) DEFAULT 'pendiente',
  PRIMARY KEY (`id`),
  KEY `idx_fecha_expiracion` (`fecha_expiracion`),
  KEY `idx_usuario_publica` (`usuario_publica_id`),
  CONSTRAINT `fk_ofertas_usuario` FOREIGN KEY (`usuario_publica_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `postulaciones` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `oferta_id` INT NOT NULL,
  `usuario_id` INT NOT NULL,
  `fecha_postulacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_oferta_usuario` (`oferta_id`,`usuario_id`),
  KEY `idx_oferta` (`oferta_id`),
  KEY `idx_usuario` (`usuario_id`),
  CONSTRAINT `fk_postulaciones_oferta` FOREIGN KEY (`oferta_id`) REFERENCES `ofertas_laborales`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_postulaciones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Ejemplo: insertar un usuario administrador
-- NOTA: reemplaza el hash por uno generado con password_hash() en PHP.
-- Por ejemplo, en PHP: echo password_hash('tu_password_segura', PASSWORD_DEFAULT);
-- INSERT INTO usuarios (nombre_completo, email, password, role) VALUES ('Administrador', 'admin@example.com', '$2y$........', 'admin');

-- Opcional: ejemplo de oferta de prueba
-- INSERT INTO ofertas_laborales (titulo, empresa, descripcion, requisitos, contacto, fecha_publicacion, fecha_expiracion, semaforo, usuario_publica_id, estado)
-- VALUES ('Desarrollador PHP', 'Empresa X', 'Se busca desarrollador PHP con experiencia', 'PHP, MySQL', 'rrhh@empresa.com', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'verde', 1, 'aprobada');

-- Fin del script
