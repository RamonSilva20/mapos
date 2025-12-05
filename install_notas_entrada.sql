-- Script SQL para criar as tabelas do módulo de Notas de Entrada
-- Execute este script no banco de dados mapos

USE mapos;

-- Tabela principal de notas de entrada
CREATE TABLE IF NOT EXISTS `notas_entrada` (
  `idNotaEntrada` INT(11) NOT NULL AUTO_INCREMENT,
  `numero_nf` VARCHAR(20) NOT NULL,
  `serie_nf` VARCHAR(5) DEFAULT '1',
  `chave_acesso` VARCHAR(44) UNIQUE,
  `cnpj_emitente` VARCHAR(18),
  `nome_emitente` VARCHAR(255),
  `cnpj_destinatario` VARCHAR(18),
  `nome_destinatario` VARCHAR(255),
  `data_emissao` DATE,
  `data_entrada` DATE,
  `valor_total` DECIMAL(10,2) DEFAULT 0.00,
  `valor_produtos` DECIMAL(10,2) DEFAULT 0.00,
  `valor_icms` DECIMAL(10,2) DEFAULT 0.00,
  `valor_ipi` DECIMAL(10,2) DEFAULT 0.00,
  `valor_frete` DECIMAL(10,2) DEFAULT 0.00,
  `valor_desconto` DECIMAL(10,2) DEFAULT 0.00,
  `natureza_operacao` VARCHAR(255),
  `modelo_nf` VARCHAR(2) DEFAULT '55',
  `tipo_operacao` VARCHAR(1) DEFAULT '0',
  `xml_path` VARCHAR(500),
  `xml_content` LONGTEXT,
  `status` VARCHAR(20) DEFAULT 'Pendente',
  `observacoes` TEXT,
  `fornecedor_id` INT(11),
  `data_cadastro` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `usuario_cadastro` INT(11),
  PRIMARY KEY (`idNotaEntrada`),
  INDEX `idx_chave_acesso` (`chave_acesso`),
  INDEX `idx_numero_nf` (`numero_nf`),
  INDEX `idx_fornecedor` (`fornecedor_id`),
  FOREIGN KEY (`fornecedor_id`) REFERENCES `clientes`(`idClientes`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de itens das notas de entrada
CREATE TABLE IF NOT EXISTS `notas_entrada_itens` (
  `idItemNotaEntrada` INT(11) NOT NULL AUTO_INCREMENT,
  `nota_entrada_id` INT(11) NOT NULL,
  `codigo_produto` VARCHAR(50),
  `descricao_produto` VARCHAR(255),
  `ncm` VARCHAR(8),
  `cest` VARCHAR(7),
  `cfop` VARCHAR(4),
  `unidade` VARCHAR(10),
  `quantidade` DECIMAL(10,3) DEFAULT 0.000,
  `valor_unitario` DECIMAL(10,2) DEFAULT 0.00,
  `valor_total` DECIMAL(10,2) DEFAULT 0.00,
  `valor_icms` DECIMAL(10,2) DEFAULT 0.00,
  `valor_ipi` DECIMAL(10,2) DEFAULT 0.00,
  `aliquota_icms` DECIMAL(5,2) DEFAULT 0.00,
  `aliquota_ipi` DECIMAL(5,2) DEFAULT 0.00,
  `origem` VARCHAR(1) DEFAULT '0',
  `tributacao` VARCHAR(2),
  `produto_id` INT(11),
  PRIMARY KEY (`idItemNotaEntrada`),
  INDEX `idx_nota_entrada` (`nota_entrada_id`),
  INDEX `idx_produto` (`produto_id`),
  FOREIGN KEY (`nota_entrada_id`) REFERENCES `notas_entrada`(`idNotaEntrada`) ON DELETE CASCADE,
  FOREIGN KEY (`produto_id`) REFERENCES `produtos`(`idProdutos`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

