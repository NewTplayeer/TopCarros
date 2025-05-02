CREATE DATABASE topcarros;
USE topcarros;

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `usuarios` (`nome`, `email`, `senha`) VALUES
('Bruno', 'brunotavaresdefreitas@gmail.com', '$2y$10$T7tdcoczlE4w6CAWQ/MXweU80gjcmOtMiq0xL5CUfXkSzEAuf.q3u'),
('MaFee', 'Mafee@gmail.com', '$2y$10$2HzSZ7tp/lufewlG5WKzHuiEhqXf9hfPt/PjQXm3t9NryMSMWCxW.'),
('Kerllon Gedyel', 't4kamoto@gmail.com', '$2y$10$UPdhjro3vkf6DN..fLkbt.0fcpG0AwZa9Z4W3Uoln9Zs/l1kO4N3O'),
('Caio', 'caio@gmail.com', '$2y$10$f19I/AGSATcWZGf9ksYoyOqz0Yd5lMRPkqqS2mD7UysLGh1DCvlze'),
('Lucas', 'Lucas@gmail.com', '$2y$10$/t6yJcLqcsMpZEUmP/SQDOT//.Q48/7u46yH1FnvNiX1Qt81DRhqC');
