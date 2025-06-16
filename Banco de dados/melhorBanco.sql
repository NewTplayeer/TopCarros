DROP DATABASE IF EXISTS `topcarros`;
CREATE DATABASE `topcarros` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `topcarros`;

-- Tabela de usuários (mantida como você tinha)
CREATE TABLE `usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `senha` CHAR(64) NOT NULL,
  `tipo` ENUM('admin','cliente') DEFAULT 'cliente',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de marcas (mantida como você tinha)
CREATE TABLE `marcas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de veículos (mantida como você tinha)
CREATE TABLE `veiculos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `marca_id` INT(11),
  `modelo` VARCHAR(100),
  `ano` INT(11),
  `preco` DECIMAL(10,2),
  `imagem` VARCHAR(255),
  PRIMARY KEY (`id`),
  KEY (`marca_id`),
  CONSTRAINT `veiculos_ibfk_1` FOREIGN KEY (`marca_id`) REFERENCES `marcas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de anúncios (mantida como você tinha)
CREATE TABLE `anuncios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` INT(11),
  `veiculo_id` INT(11),
  `data_postagem` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('ativo','vendido') DEFAULT 'ativo',
  PRIMARY KEY (`id`),
  KEY (`usuario_id`),
  KEY (`veiculo_id`),
  CONSTRAINT `anuncios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `anuncios_ibfk_2` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABELAS ADICIONADAS PARA COMPRAS (FALTANDO NO SEU ORIGINAL)
-- Tabela de compras
CREATE TABLE `compras` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` INT(11) NOT NULL,
  `data_compra` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `valor_total` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pendente','pago','cancelado') DEFAULT 'pendente',
  PRIMARY KEY (`id`),
  CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de itens de compra
CREATE TABLE `compra_itens` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `compra_id` INT(11) NOT NULL,
  `veiculo_id` INT(11) NOT NULL,
  `preco_unitario` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `compra_itens_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`),
  CONSTRAINT `compra_itens_ibfk_2` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserções de dados (mantidas como você tinha)
-- Marcas
INSERT INTO `marcas` (`id`, `nome`) VALUES
(2, 'Ford'),
(3, 'Porsche'),
(4, 'Subaru'),
(5, 'Honda'),
(6, 'Audi');

-- Veículos
INSERT INTO `veiculos` (`id`, `marca_id`, `modelo`, `ano`, `preco`, `imagem`) VALUES
(2, 2, 'Mustang', 2018, 500000.00, 'MustangGT.jpg'),
(3, 3, 'GT3RS', 2020, 2600000.00, 'PorscheGT3RS.jpg'),
(6, 5, 'Honda Civic G6', 1986, 12000000.00, 'CivicEg6.jpg'),  -- Corrigido typo "Honde" para "Honda"
(7, 6, 'Audi A4', 2014, 30000000.00, 'AudiA4.jpg');

-- Usuários
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`) VALUES
(2, 'Bruno Tavares de Freitas', 'brunotavaresdefreitas@gmail.com', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'admin'),
(3, 'Gustavo Henrique de Oliveira Teles', '1gustavo1teles@gmail.com', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'cliente'),
(4, 'Breno Viado', 'Breno@gmail.com', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'cliente'),
(5, 'Maria Fernanda Prado Duque', 'MaFee@gmail.com', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'cliente'),
(6, 'Lucas Silva', 'lucasmst.silva@gmail.com', '$2y$10$.FX3Ndn1FqigmssqGATwOuNE9nMmSQtne1F/Tq6hgWjjYYYJ1Gfgm', 'admin');

-- Anúncio exemplo
INSERT INTO `anuncios` (`id`, `usuario_id`, `veiculo_id`, `data_postagem`, `status`) VALUES
(6, 2, 3, '2025-06-03 20:18:48', 'ativo');

-- DADOS DE EXEMPLO PARA COMPRAS (NOVOS)
-- Compra exemplo
INSERT INTO `compras` (`id`, `usuario_id`, `valor_total`, `status`) VALUES
(1, 2, 500000.00, 'pago');

-- Item de compra exemplo
INSERT INTO `compra_itens` (`compra_id`, `veiculo_id`, `preco_unitario`) VALUES
(1, 2, 500000.00);

-- Consultas para verificação
SELECT * FROM usuarios;
SELECT * FROM marcas;
SELECT * FROM veiculos;
SELECT * FROM anuncios;
SELECT * FROM compras;
SELECT * FROM compra_itens;


-- Adicionando compras para o usuário ID 6 (Lucas Silva - admin)
INSERT INTO `compras` (`id`, `usuario_id`, `data_compra`, `valor_total`, `status`) VALUES
(2, 6, '2025-06-10 14:30:00', 2600000.00, 'pago'),  -- Porsche GT3RS
(3, 6, '2025-06-15 09:45:00', 12000000.00, 'pago'); -- Honda Civic G6

-- Itens das compras do usuário 6
INSERT INTO `compra_itens` (`compra_id`, `veiculo_id`, `preco_unitario`) VALUES
(2, 3, 2600000.00),   -- Porsche GT3RS (compra 2)
(3, 6, 12000000.00);  -- Honda Civic G6 (compra 3)

-- Verificação das compras do usuário 6
SELECT c.id, u.nome AS usuario, c.data_compra, c.valor_total, c.status, 
       GROUP_CONCAT(v.modelo SEPARATOR ', ') AS veiculos
FROM compras c
JOIN compra_itens ci ON c.id = ci.compra_id
JOIN veiculos v ON ci.veiculo_id = v.id
JOIN usuarios u ON c.usuario_id = u.id
WHERE c.usuario_id = 6
GROUP BY c.id;