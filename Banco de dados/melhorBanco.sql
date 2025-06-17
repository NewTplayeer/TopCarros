
  DROP DATABASE IF EXISTS `topcarros`;
  CREATE DATABASE `topcarros` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
  USE `topcarros`;

-- Tabela de usuários
CREATE TABLE `usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL, -- Alterado para VARCHAR(255) para senhas hashed (password_hash)
  `tipo` ENUM('admin','cliente') DEFAULT 'cliente',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de marcas
CREATE TABLE `marcas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de veículos
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

-- Tabela de anúncios
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

-- Tabela de compras (incluindo as colunas de frete e o status 'carrinho')
CREATE TABLE `compras` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` INT(11) NOT NULL,
  `data_compra` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `valor_total` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pendente','pago','cancelado','carrinho') DEFAULT 'carrinho',
  `tipo_frete` VARCHAR(50) DEFAULT NULL,
  `valor_frete` DECIMAL(10,2) DEFAULT 0.00,
  `prazo_frete` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de itens de compra (incluindo a coluna 'quantidade')
CREATE TABLE `compra_itens` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `compra_id` INT(11) NOT NULL,
  `veiculo_id` INT(11) NOT NULL,
  `preco_unitario` DECIMAL(10,2) NOT NULL,
  `quantidade` INT(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  CONSTRAINT `compra_itens_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`),
  CONSTRAINT `compra_itens_ibfk_2` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserções de dados
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
(6, 5, 'Honda Civic G6', 1986, 12000000.00, 'CivicEg6.jpg'),
(7, 6, 'Audi A4', 2014, 30000000.00, 'AudiA4.jpg');

-- Usuários (senhas são exemplos hashed, você pode gerar novas com password_hash() no PHP)
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`) VALUES
(2, 'Bruno Tavares de Freitas', 'brunotavaresdefreitas@gmail.com', '$2y$10$0VQ7ZTOP/K0CQlAhAAup.OSiJzlICZsocGDgK3mIg2bx2QL4diNtm', 'admin'),
(3, 'Gustavo Henrique de Oliveira Teles', '1gustavo1teles@gmail.com', '$2y$10$3PuDbmtR44PItMGCkAL5YOQ9lXSI1bpHlqvHYTVjpmEV6C8uwy8qG', 'cliente'),
(4, 'Breno Viado', 'Breno@gmail.com', '$2y$10$hrb4KPtT4Kj0JiUugsjTi.1.2iiWmKuGLXDIjsgD.KExWhJ37dVDm', 'cliente'),
(5, 'Maria Fernanda Prado Duque', 'MaFee@gmail.com', '$2y$10$h4xZoRp3sMdzgAUnINAR4.7l01QT7AJMSltpBXCM6xFZ.AGw3op2m', 'cliente'),
(6, 'Lucas Silva', 'lucasmst.silva@gmail.com', '$2y$10$.FX3Ndn1FqigmssqGATwOuNE9nMmSQtne1F/Tq6hgWjjYYYJ1Gfgm', 'admin');

-- Anúncio exemplo
INSERT INTO `anuncios` (`id`, `usuario_id`, `veiculo_id`, `data_postagem`, `status`) VALUES
(6, 2, 3, '2025-06-03 20:18:48', 'ativo');

-- DADOS DE EXEMPLO PARA COMPRAS (com info de frete)
INSERT INTO `compras` (`id`, `usuario_id`, `data_compra`, `valor_total`, `status`, `tipo_frete`, `valor_frete`, `prazo_frete`) VALUES
(1, 2, '2025-06-15 22:54:00', 500000.00, 'pago', 'Guincho Plataforma', 2006.01, '5 dias úteis'), -- Exemplo com dados do seu problema
(2, 6, '2025-06-10 14:30:00', 2600000.00, 'pago', 'Transporte Especializado', 2500.00, '7 dias úteis'),
(3, 6, '2025-06-15 09:45:00', 12000000.00, 'pago', 'Retirada na Loja', 0.00, 'Imediato');

-- Itens das compras
INSERT INTO `compra_itens` (`compra_id`, `veiculo_id`, `preco_unitario`, `quantidade`) VALUES
(1, 2, 500000.00, 1),
(2, 3, 2600000.00, 1),
(3, 6, 12000000.00, 1);

-- Consultas para verificação (opcionais, apenas para teste)
SELECT * FROM usuarios;
SELECT * FROM marcas;
SELECT * FROM veiculos;
SELECT * FROM anuncios;
SELECT * FROM compras;
SELECT * FROM compra_itens;