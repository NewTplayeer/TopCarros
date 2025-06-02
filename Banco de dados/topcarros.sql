
CREATE DATABASE IF NOT EXISTS topcarros;
USE topcarros;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'cliente') DEFAULT 'cliente'
);

INSERT INTO `usuarios` (`nome`, `email`, `senha`) VALUES
('Bruno', 'brunotavaresdefreitas@gmail.com', '$2y$10$T7tdcoczlE4w6CAWQ/MXweU80gjcmOtMiq0xL5CUfXkSzEAuf.q3u'),
('MaFee', 'Mafee@gmail.com', '$2y$10$2HzSZ7tp/lufewlG5WKzHuiEhqXf9hfPt/PjQXm3t9NryMSMWCxW.'),
('Kerllon Gedyel', 't4kamoto@gmail.com', '$2y$10$UPdhjro3vkf6DN..fLkbt.0fcpG0AwZa9Z4W3Uoln9Zs/l1kO4N3O'),
('Caio', 'caio@gmail.com', '$2y$10$f19I/AGSATcWZGf9ksYoyOqz0Yd5lMRPkqqS2mD7UysLGh1DCvlze'),
('Lucas', 'Lucas@gmail.com', '$2y$10$/t6yJcLqcsMpZEUmP/SQDOT//.Q48/7u46yH1FnvNiX1Qt81DRhqC');

CREATE TABLE marcas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE veiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marca_id INT,
    modelo VARCHAR(100),
    ano INT,
    preco DECIMAL(10,2),
    imagem VARCHAR(255),
    FOREIGN KEY (marca_id) REFERENCES marcas(id)
);

CREATE TABLE anuncios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    veiculo_id INT,
    data_postagem DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('ativo', 'vendido') DEFAULT 'ativo',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (veiculo_id) REFERENCES veiculos(id)
);
