-- ===========================================
-- Estrutura de Propostas Comerciais
-- Data: 22/12/2025
-- ===========================================

-- Criar tabela principal de propostas
CREATE TABLE IF NOT EXISTS `propostas` (
  `idProposta` INT(11) NOT NULL AUTO_INCREMENT,
  `numero_proposta` VARCHAR(20) NULL COMMENT 'Número da proposta (gerado automaticamente)',
  `data_proposta` DATE NOT NULL COMMENT 'Data de criação da proposta',
  `data_validade` DATE NULL COMMENT 'Data de validade da proposta',
  `status` VARCHAR(20) DEFAULT 'Rascunho' COMMENT 'Rascunho, Enviada, Aprovada, Recusada, Convertida',
  `clientes_id` INT(11) NOT NULL,
  `usuarios_id` INT(11) NOT NULL COMMENT 'Vendedor/Responsável',
  `observacoes` TEXT NULL,
  `desconto` DECIMAL(10, 2) DEFAULT 0.00,
  `valor_desconto` DECIMAL(10, 2) DEFAULT 0.00,
  `tipo_desconto` VARCHAR(8) NULL COMMENT 'percentual, fixo',
  `valor_total` DECIMAL(10, 2) DEFAULT 0.00,
  `os_id` INT(11) NULL COMMENT 'ID da OS se convertida',
  `venda_id` INT(11) NULL COMMENT 'ID da Venda se convertida',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idProposta`),
  INDEX `fk_propostas_clientes_idx` (`clientes_id` ASC),
  INDEX `fk_propostas_usuarios_idx` (`usuarios_id` ASC),
  INDEX `fk_propostas_os_idx` (`os_id` ASC),
  INDEX `fk_propostas_vendas_idx` (`venda_id` ASC),
  CONSTRAINT `fk_propostas_clientes`
    FOREIGN KEY (`clientes_id`)
    REFERENCES `clientes` (`idClientes`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_propostas_usuarios`
    FOREIGN KEY (`usuarios_id`)
    REFERENCES `usuarios` (`idUsuarios`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_propostas_os`
    FOREIGN KEY (`os_id`)
    REFERENCES `os` (`idOs`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_propostas_vendas`
    FOREIGN KEY (`venda_id`)
    REFERENCES `vendas` (`idVendas`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Criar tabela de produtos da proposta
CREATE TABLE IF NOT EXISTS `produtos_proposta` (
  `idProdutoProposta` INT(11) NOT NULL AUTO_INCREMENT,
  `proposta_id` INT(11) NOT NULL,
  `produtos_id` INT(11) NULL COMMENT 'ID do produto (pode ser NULL para produtos customizados)',
  `descricao` VARCHAR(255) NOT NULL COMMENT 'Descrição do produto',
  `quantidade` DECIMAL(10, 2) NOT NULL DEFAULT 1.00,
  `preco` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `subtotal` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idProdutoProposta`),
  INDEX `fk_produtos_proposta_idx` (`proposta_id` ASC),
  INDEX `fk_produtos_proposta_produtos_idx` (`produtos_id` ASC),
  CONSTRAINT `fk_produtos_proposta`
    FOREIGN KEY (`proposta_id`)
    REFERENCES `propostas` (`idProposta`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_produtos_proposta_produtos`
    FOREIGN KEY (`produtos_id`)
    REFERENCES `produtos` (`idProdutos`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Criar tabela de serviços da proposta
CREATE TABLE IF NOT EXISTS `servicos_proposta` (
  `idServicoProposta` INT(11) NOT NULL AUTO_INCREMENT,
  `proposta_id` INT(11) NOT NULL,
  `servicos_id` INT(11) NULL COMMENT 'ID do serviço (pode ser NULL para serviços customizados)',
  `descricao` TEXT NOT NULL COMMENT 'Descrição do serviço',
  `quantidade` DECIMAL(10, 2) NOT NULL DEFAULT 1.00,
  `preco` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `subtotal` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idServicoProposta`),
  INDEX `fk_servicos_proposta_idx` (`proposta_id` ASC),
  INDEX `fk_servicos_proposta_servicos_idx` (`servicos_id` ASC),
  CONSTRAINT `fk_servicos_proposta`
    FOREIGN KEY (`proposta_id`)
    REFERENCES `propostas` (`idProposta`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_servicos_proposta_servicos`
    FOREIGN KEY (`servicos_id`)
    REFERENCES `servicos` (`idServicos`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Criar tabela de parcelas da proposta
CREATE TABLE IF NOT EXISTS `parcelas_proposta` (
  `idParcelaProposta` INT(11) NOT NULL AUTO_INCREMENT,
  `proposta_id` INT(11) NOT NULL,
  `numero` INT(11) NOT NULL COMMENT 'Número da parcela (1, 2, 3...)',
  `dias` INT(11) NOT NULL COMMENT 'Dias até o vencimento (30, 60, 90...)',
  `valor` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `observacao` TEXT NULL COMMENT 'Observação da parcela',
  `data_vencimento` DATE NULL COMMENT 'Data de vencimento calculada',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idParcelaProposta`),
  INDEX `fk_parcelas_proposta_idx` (`proposta_id` ASC),
  CONSTRAINT `fk_parcelas_proposta`
    FOREIGN KEY (`proposta_id`)
    REFERENCES `propostas` (`idProposta`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Criar tabela para outros produtos/serviços da proposta
CREATE TABLE IF NOT EXISTS `outros_proposta` (
  `idOutros` INT(11) NOT NULL AUTO_INCREMENT,
  `proposta_id` INT(11) NOT NULL,
  `descricao` TEXT NOT NULL,
  `preco` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idOutros`),
  INDEX `fk_outros_proposta_idx` (`proposta_id` ASC),
  CONSTRAINT `fk_outros_proposta`
    FOREIGN KEY (`proposta_id`)
    REFERENCES `propostas` (`idProposta`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

