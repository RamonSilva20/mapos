/*
MySQL Data Transfer
Source Host: localhost
Source Database: banco
Target Host: localhost
Target Database: banco
Date: 17/02/2021 14:54:58
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for anexos
-- ----------------------------
CREATE TABLE `anexos` (
  `idAnexos` int(11) NOT NULL AUTO_INCREMENT,
  `anexo` varchar(1000) DEFAULT NULL,
  `thumb` varchar(1000) DEFAULT NULL,
  `url` varchar(1000) DEFAULT NULL,
  `path` varchar(1000) DEFAULT NULL,
  `os_id` int(11) NOT NULL,
  PRIMARY KEY (`idAnexos`),
  KEY `fk_anexos_os1` (`os_id`),
  CONSTRAINT `fk_anexos_os1` FOREIGN KEY (`os_id`) REFERENCES `os` (`idOs`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for anotacoes_os
-- ----------------------------
CREATE TABLE `anotacoes_os` (
  `idAnotacoes` int(11) NOT NULL AUTO_INCREMENT,
  `anotacao` varchar(255) NOT NULL,
  `data_hora` datetime NOT NULL,
  `os_id` int(11) NOT NULL,
  PRIMARY KEY (`idAnotacoes`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for categorias
-- ----------------------------
CREATE TABLE `categorias` (
  `idCategorias` int(11) NOT NULL AUTO_INCREMENT,
  `categoria` varchar(80) DEFAULT NULL,
  `cadastro` date DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `tipo` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`idCategorias`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for ci_sessions
-- ----------------------------
CREATE TABLE `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL DEFAULT 0,
  `data` blob NOT NULL,
  KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for clientes
-- ----------------------------
CREATE TABLE `clientes` (
  `idClientes` int(11) NOT NULL AUTO_INCREMENT,
  `nomeCliente` varchar(255) NOT NULL,
  `sexo` varchar(20) DEFAULT NULL,
  `pessoa_fisica` tinyint(1) NOT NULL DEFAULT 1,
  `documento` varchar(20) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `dataCadastro` date DEFAULT NULL,
  `rua` varchar(70) DEFAULT NULL,
  `numero` varchar(15) DEFAULT NULL,
  `bairro` varchar(45) DEFAULT NULL,
  `cidade` varchar(45) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `cep` varchar(20) DEFAULT NULL,
  `foto_url` varchar(45) DEFAULT '',
  `senha` varchar(45) DEFAULT '',
  `fornecedor` tinyint(1) DEFAULT 0,
  `contato` text DEFAULT NULL,
  `complemento` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`idClientes`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for cobrancas
-- ----------------------------
CREATE TABLE `cobrancas` (
  `idCobranca` int(11) NOT NULL AUTO_INCREMENT,
  `charge_id` int(11) DEFAULT NULL,
  `conditional_discount_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `custom_id` int(11) DEFAULT NULL,
  `expire_at` date NOT NULL,
  `message` varchar(255) NOT NULL,
  `payment_method` varchar(11) DEFAULT NULL,
  `payment_url` varchar(255) DEFAULT NULL,
  `request_delivery_address` varchar(64) DEFAULT NULL,
  `status` varchar(36) NOT NULL,
  `total` varchar(15) DEFAULT NULL,
  `barcode` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `payment_gateway` varchar(255) DEFAULT NULL,
  `payment` varchar(64) NOT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `vendas_id` int(11) DEFAULT NULL,
  `os_id` int(11) DEFAULT NULL,
  `clientes_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`idCobranca`),
  KEY `fk_cobrancas_os1` (`os_id`),
  KEY `fk_cobrancas_vendas1` (`vendas_id`),
  KEY `fk_cobrancas_clientes1` (`clientes_id`),
  CONSTRAINT `fk_cobrancas_clientes1` FOREIGN KEY (`clientes_id`) REFERENCES `clientes` (`idClientes`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_cobrancas_os1` FOREIGN KEY (`os_id`) REFERENCES `os` (`idOs`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_cobrancas_vendas1` FOREIGN KEY (`vendas_id`) REFERENCES `vendas` (`idVendas`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for configuracoes
-- ----------------------------
CREATE TABLE `configuracoes` (
  `idConfig` int(11) NOT NULL AUTO_INCREMENT,
  `config` varchar(20) NOT NULL,
  `valor` text DEFAULT NULL,
  PRIMARY KEY (`idConfig`),
  UNIQUE KEY `config` (`config`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for contas
-- ----------------------------
CREATE TABLE `contas` (
  `idContas` int(11) NOT NULL AUTO_INCREMENT,
  `conta` varchar(45) DEFAULT NULL,
  `banco` varchar(45) DEFAULT NULL,
  `numero` varchar(45) DEFAULT NULL,
  `saldo` decimal(10,2) DEFAULT NULL,
  `cadastro` date DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `tipo` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`idContas`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for documentos
-- ----------------------------
CREATE TABLE `documentos` (
  `idDocumentos` int(11) NOT NULL AUTO_INCREMENT,
  `documento` varchar(70) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `file` varchar(100) DEFAULT NULL,
  `path` varchar(300) DEFAULT NULL,
  `url` varchar(300) DEFAULT NULL,
  `cadastro` date DEFAULT NULL,
  `categoria` varchar(80) DEFAULT NULL,
  `tipo` varchar(15) DEFAULT NULL,
  `tamanho` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`idDocumentos`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for email_queue
-- ----------------------------
CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to` varchar(255) NOT NULL,
  `cc` varchar(255) DEFAULT NULL,
  `bcc` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('pending','sending','sent','failed') DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `headers` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for emitente
-- ----------------------------
CREATE TABLE `emitente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) DEFAULT NULL,
  `cnpj` varchar(45) DEFAULT NULL,
  `ie` varchar(50) DEFAULT NULL,
  `rua` varchar(70) DEFAULT NULL,
  `numero` varchar(15) DEFAULT NULL,
  `bairro` varchar(45) DEFAULT NULL,
  `cidade` varchar(45) DEFAULT NULL,
  `uf` varchar(20) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `url_logo` varchar(225) DEFAULT NULL,
  `url_termica` varchar(255) DEFAULT '',
  `cep` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for equipamento_os
-- ----------------------------
CREATE TABLE `equipamento_os` (
  `idEquipamento` int(11) NOT NULL AUTO_INCREMENT,
  `equipamento` text NOT NULL,
  `num_serie` text DEFAULT NULL,
  `modelo` text DEFAULT NULL,
  `voltagem` text DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `os_id` int(11) NOT NULL,
  PRIMARY KEY (`idEquipamento`),
  KEY `fk_equipamento_os` (`os_id`),
  CONSTRAINT `fk_equipamento_os_os1` FOREIGN KEY (`os_id`) REFERENCES `os` (`idOs`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for equipamentos
-- ----------------------------
CREATE TABLE `equipamentos` (
  `idEquipamentos` int(11) NOT NULL AUTO_INCREMENT,
  `equipamento` varchar(150) NOT NULL,
  `num_serie` varchar(80) DEFAULT NULL,
  `modelo` varchar(80) DEFAULT NULL,
  `cor` varchar(45) DEFAULT NULL,
  `descricao` varchar(150) DEFAULT NULL,
  `tensao` varchar(45) DEFAULT NULL,
  `potencia` varchar(45) DEFAULT NULL,
  `voltagem` varchar(45) DEFAULT NULL,
  `data_fabricacao` date DEFAULT NULL,
  `marcas_id` int(11) DEFAULT NULL,
  `clientes_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`idEquipamentos`),
  KEY `fk_equipanentos_marcas1_idx` (`marcas_id`),
  KEY `fk_equipanentos_clientes1_idx` (`clientes_id`),
  CONSTRAINT `fk_equipanentos_clientes1` FOREIGN KEY (`clientes_id`) REFERENCES `clientes` (`idClientes`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_equipanentos_marcas1` FOREIGN KEY (`marcas_id`) REFERENCES `marcas` (`idMarcas`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for equipamentos_os
-- ----------------------------
CREATE TABLE `equipamentos_os` (
  `idEquipamentos_os` int(11) NOT NULL AUTO_INCREMENT,
  `defeito_declarado` varchar(200) DEFAULT NULL,
  `defeito_encontrado` varchar(200) DEFAULT NULL,
  `solucao` varchar(45) DEFAULT NULL,
  `equipamentos_id` int(11) DEFAULT NULL,
  `os_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`idEquipamentos_os`),
  KEY `fk_equipamentos_os_equipanentos1_idx` (`equipamentos_id`),
  KEY `fk_equipamentos_os_os1_idx` (`os_id`),
  CONSTRAINT `fk_equipamentos_os_equipanentos1` FOREIGN KEY (`equipamentos_id`) REFERENCES `equipamentos` (`idEquipamentos`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_equipamentos_os_os1` FOREIGN KEY (`os_id`) REFERENCES `os` (`idOs`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for garantias
-- ----------------------------
CREATE TABLE `garantias` (
  `idGarantias` int(11) NOT NULL AUTO_INCREMENT,
  `dataGarantia` date DEFAULT NULL,
  `refGarantia` varchar(15) DEFAULT NULL,
  `textoGarantia` text DEFAULT NULL,
  `usuarios_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`idGarantias`),
  KEY `fk_garantias_usuarios1` (`usuarios_id`),
  CONSTRAINT `fk_garantias_usuarios1` FOREIGN KEY (`usuarios_id`) REFERENCES `usuarios` (`idUsuarios`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for itens_de_vendas
-- ----------------------------
CREATE TABLE `itens_de_vendas` (
  `idItens` int(11) NOT NULL AUTO_INCREMENT,
  `subTotal` varchar(45) DEFAULT NULL,
  `quantidade` int(11) DEFAULT NULL,
  `preco` varchar(15) DEFAULT NULL,
  `vendas_id` int(11) NOT NULL,
  `produtos_id` int(11) NOT NULL,
  PRIMARY KEY (`idItens`),
  KEY `fk_itens_de_vendas_vendas1` (`vendas_id`),
  KEY `fk_itens_de_vendas_produtos1` (`produtos_id`),
  CONSTRAINT `fk_itens_de_vendas_produtos1` FOREIGN KEY (`produtos_id`) REFERENCES `produtos` (`idProdutos`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_itens_de_vendas_vendas1` FOREIGN KEY (`vendas_id`) REFERENCES `vendas` (`idVendas`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for lancamentos
-- ----------------------------
CREATE TABLE `lancamentos` (
  `idLancamentos` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(255) DEFAULT NULL,
  `valor` varchar(15) NOT NULL,
  `data_vencimento` date NOT NULL,
  `data_pagamento` date DEFAULT NULL,
  `baixado` tinyint(1) DEFAULT 0,
  `cliente_fornecedor` varchar(255) DEFAULT NULL,
  `forma_pgto` varchar(100) DEFAULT NULL,
  `tipo` varchar(45) DEFAULT NULL,
  `anexo` varchar(250) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `clientes_id` int(11) DEFAULT NULL,
  `categorias_id` int(11) DEFAULT NULL,
  `contas_id` int(11) DEFAULT NULL,
  `vendas_id` int(11) DEFAULT NULL,
  `usuarios_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`idLancamentos`),
  KEY `fk_lancamentos_clientes1` (`clientes_id`),
  KEY `fk_lancamentos_categorias1_idx` (`categorias_id`),
  KEY `fk_lancamentos_contas1_idx` (`contas_id`),
  KEY `fk_lancamentos_usuarios1` (`usuarios_id`),
  CONSTRAINT `fk_lancamentos_categorias1` FOREIGN KEY (`categorias_id`) REFERENCES `categorias` (`idCategorias`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_lancamentos_clientes1` FOREIGN KEY (`clientes_id`) REFERENCES `clientes` (`idClientes`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_lancamentos_contas1` FOREIGN KEY (`contas_id`) REFERENCES `contas` (`idContas`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_lancamentos_usuarios1` FOREIGN KEY (`usuarios_id`) REFERENCES `usuarios` (`idUsuarios`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for logs
-- ----------------------------
CREATE TABLE `logs` (
  `idLogs` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(80) DEFAULT NULL,
  `tarefa` varchar(100) DEFAULT NULL,
  `data` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`idLogs`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for marcas
-- ----------------------------
CREATE TABLE `marcas` (
  `idMarcas` int(11) NOT NULL AUTO_INCREMENT,
  `marca` varchar(100) DEFAULT NULL,
  `cadastro` date DEFAULT NULL,
  `situacao` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`idMarcas`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
CREATE TABLE `migrations` (
  `version` bigint(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for os
-- ----------------------------
CREATE TABLE `os` (
  `idOs` int(11) NOT NULL AUTO_INCREMENT,
  `dataInicial` date DEFAULT NULL,
  `dataFinal` varchar(15) DEFAULT NULL,
  `dataSaida` varchar(15) DEFAULT NULL,
  `garantia` varchar(15) DEFAULT NULL,
  `rastreio` varchar(20) DEFAULT NULL,
  `descricaoProduto` text DEFAULT NULL,
  `defeito` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `laudoTecnico` text DEFAULT NULL,
  `valorTotal` varchar(15) DEFAULT NULL,
  `clientes_id` int(11) DEFAULT NULL,
  `usuarios_id` int(11) DEFAULT NULL,
  `lancamento` int(11) DEFAULT NULL,
  `faturado` tinyint(1) DEFAULT NULL,
  `garantias_id` int(11) DEFAULT NULL,
  `marca` varchar(90) DEFAULT NULL,
  `serial` varchar(90) DEFAULT NULL,
  PRIMARY KEY (`idOs`),
  KEY `fk_os_clientes1` (`clientes_id`),
  KEY `fk_os_usuarios1` (`usuarios_id`),
  KEY `fk_os_lancamentos1` (`lancamento`),
  KEY `fk_os_garantias1` (`garantias_id`),
  CONSTRAINT `fk_os_clientes1` FOREIGN KEY (`clientes_id`) REFERENCES `clientes` (`idClientes`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_os_lancamentos1` FOREIGN KEY (`lancamento`) REFERENCES `lancamentos` (`idLancamentos`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_os_usuarios1` FOREIGN KEY (`usuarios_id`) REFERENCES `usuarios` (`idUsuarios`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for pagamento
-- ----------------------------
CREATE TABLE `pagamento` (
  `idPag` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `client_id` varchar(200) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `client_secret` varchar(200) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `public_key` varchar(200) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `access_token` varchar(200) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `default_pag` int(1) NOT NULL,
  PRIMARY KEY (`idPag`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for permissoes
-- ----------------------------
CREATE TABLE `permissoes` (
  `idPermissao` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(80) NOT NULL,
  `permissoes` text DEFAULT NULL,
  `situacao` tinyint(1) DEFAULT NULL,
  `data` date DEFAULT NULL,
  PRIMARY KEY (`idPermissao`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for produtos
-- ----------------------------
CREATE TABLE `produtos` (
  `idProdutos` int(11) NOT NULL AUTO_INCREMENT,
  `codDeBarra` varchar(70) NOT NULL,
  `descricao` varchar(80) NOT NULL,
  `unidade` varchar(10) DEFAULT NULL,
  `precoCompra` decimal(10,2) DEFAULT NULL,
  `precoVenda` decimal(10,2) NOT NULL,
  `estoque` int(11) NOT NULL,
  `estoqueMinimo` int(11) DEFAULT NULL,
  `saida` tinyint(1) DEFAULT NULL,
  `entrada` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`idProdutos`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for produtos_os
-- ----------------------------
CREATE TABLE `produtos_os` (
  `idProdutos_os` int(11) NOT NULL AUTO_INCREMENT,
  `quantidade` int(11) NOT NULL,
  `descricao` varchar(80) DEFAULT NULL,
  `preco` varchar(15) DEFAULT NULL,
  `os_id` int(11) NOT NULL,
  `produtos_id` int(11) NOT NULL,
  `subTotal` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`idProdutos_os`),
  KEY `fk_produtos_os_os1` (`os_id`),
  KEY `fk_produtos_os_produtos1` (`produtos_id`),
  CONSTRAINT `fk_produtos_os_os1` FOREIGN KEY (`os_id`) REFERENCES `os` (`idOs`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_produtos_os_produtos1` FOREIGN KEY (`produtos_id`) REFERENCES `produtos` (`idProdutos`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for servicos
-- ----------------------------
CREATE TABLE `servicos` (
  `idServicos` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `descricao` varchar(45) DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  PRIMARY KEY (`idServicos`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for servicos_os
-- ----------------------------
CREATE TABLE `servicos_os` (
  `idServicos_os` int(11) NOT NULL AUTO_INCREMENT,
  `servico` varchar(80) DEFAULT NULL,
  `quantidade` double DEFAULT NULL,
  `preco` varchar(15) DEFAULT NULL,
  `os_id` int(11) NOT NULL,
  `servicos_id` int(11) NOT NULL,
  `subTotal` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`idServicos_os`),
  KEY `fk_servicos_os_os1` (`os_id`),
  KEY `fk_servicos_os_servicos1` (`servicos_id`),
  CONSTRAINT `fk_servicos_os_os1` FOREIGN KEY (`os_id`) REFERENCES `os` (`idOs`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_servicos_os_servicos1` FOREIGN KEY (`servicos_id`) REFERENCES `servicos` (`idServicos`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for usuarios
-- ----------------------------
CREATE TABLE `usuarios` (
  `idUsuarios` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(80) NOT NULL,
  `rg` varchar(20) DEFAULT NULL,
  `cpf` varchar(20) NOT NULL,
  `cep` varchar(9) NOT NULL,
  `rua` varchar(70) DEFAULT NULL,
  `numero` varchar(15) DEFAULT NULL,
  `bairro` varchar(45) DEFAULT NULL,
  `cidade` varchar(45) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `email` varchar(80) NOT NULL,
  `senha` varchar(200) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `situacao` tinyint(1) NOT NULL,
  `dataCadastro` date NOT NULL,
  `permissoes_id` int(11) NOT NULL,
  `dataExpiracao` date NOT NULL,
  PRIMARY KEY (`idUsuarios`),
  KEY `fk_usuarios_permissoes1_idx` (`permissoes_id`),
  CONSTRAINT `fk_usuarios_permissoes1` FOREIGN KEY (`permissoes_id`) REFERENCES `permissoes` (`idPermissao`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for vendas
-- ----------------------------
CREATE TABLE `vendas` (
  `idVendas` int(11) NOT NULL AUTO_INCREMENT,
  `dataVenda` date DEFAULT NULL,
  `valorTotal` varchar(45) DEFAULT NULL,
  `desconto` varchar(45) DEFAULT NULL,
  `faturado` tinyint(1) DEFAULT NULL,
  `obs` text DEFAULT '',
  `observacoes` text DEFAULT NULL,
  `observacoes_cliente` text DEFAULT NULL,
  `clientes_id` int(11) NOT NULL,
  `usuarios_id` int(11) DEFAULT NULL,
  `lancamentos_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`idVendas`),
  KEY `fk_vendas_clientes1` (`clientes_id`),
  KEY `fk_vendas_usuarios1` (`usuarios_id`),
  KEY `fk_vendas_lancamentos1` (`lancamentos_id`),
  CONSTRAINT `fk_vendas_clientes1` FOREIGN KEY (`clientes_id`) REFERENCES `clientes` (`idClientes`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_vendas_lancamentos1` FOREIGN KEY (`lancamentos_id`) REFERENCES `lancamentos` (`idLancamentos`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_vendas_usuarios1` FOREIGN KEY (`usuarios_id`) REFERENCES `usuarios` (`idUsuarios`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `clientes` VALUES ('1', 'Geral', '', '1', '000.000.000-00', '(00) 0000-0000', '', 'teste@biglobe.ne.jp', '2019-04-23', 'Rua', '0', 'Centro', 'Cidade', 'Estado', '00000-000', null, 'fw6q3xSXbd', '0', null, '');
INSERT INTO `configuracoes` VALUES ('1', 'app_name', 'Nome da sua Loja');
INSERT INTO `configuracoes` VALUES ('2', 'app_theme', 'novo');
INSERT INTO `configuracoes` VALUES ('3', 'per_page', '20');
INSERT INTO `configuracoes` VALUES ('4', 'os_notification', 'nenhum');
INSERT INTO `configuracoes` VALUES ('5', 'control_estoque', '1');
INSERT INTO `configuracoes` VALUES ('6', 'termo_uso', '<p><strong>Eu, abaixo assinado proprietário (e/ou fiel representante) do aparelho(s) acima descrito autorizo assumo as seguintes condições:<br>1.\r\n Por se tratar de equipamento(s) com micro componentes interligados \r\nentre si, os defeitos apresentados poderão provocar danos aos demais \r\ncomponentes, isentando assim a ASSISTÊNCIA TÉCNICA da responsabilidade \r\ndos defeitos.<br>2. Em caso de desistência do reparo, caberá a \r\nASSISTÊNCIA TÉCNICA apenas a responsabilidade da devolução do \r\nequipamento no estado em que se encontra, ficando isenta de qualquer \r\nreclamação e/ou ônus de minha parte;<br>3. Eu Abaixo Assinado estou ciente de que caso seja efetuado o procedimento de </strong><strong><strong>Reballing (BGA)</strong>\r\n para a tentativa do reparo, devido envolver altas temperaturas que são \r\nacima de 200°, sendo assim o equipamento pode vim a parar completamente \r\nde funcionar.<br>4. A Garantia dos serviços executados e informada nesse documento, e contada à partir da retirada do equipamento;<br>5.\r\n Os equipamentos em garantia no caso de defeito, só serão aceitos com a \r\napresentação desta ordem de serviço e desde que os selos de garantia não\r\n estejam violados;<br>6. A garantia será dada somente sobre os \r\ncomponentes eletrônicos e mão de obra aplicada no reparo especificado \r\nnesta Ordem de Serviço;<br>7. O cliente declara que o equipamento e de \r\nsua propriedade/responsabilidade, e não sendo produto de furto, roubo ou\r\n derivado de qualquer ato ilícito sendo de sua total responsabilidade;<br><br>ATENÇÃO:\r\n Os equipamentos não retirados no prazo de 15 dias, sofrerão reajuste e \r\ntaxa de armazenamento no valor de 2% ao dia sobre o valor do orçamento. \r\nDe acordo com o Código&nbsp; do Consumidor nº 8078/90 Artigo 39 incluso \r\nVII... A permanência do equipamento após o conserto ou orçamento e de no\r\n máximo&nbsp; 90 DIAS (Noventa DIAS). Após este prazo será tido como \r\nABANDONADO e será desmantelado para retirada das peças utilizadas no \r\nreparo e o restante será descartado, para a liberação de espaço.<br></strong></p>');
INSERT INTO `configuracoes` VALUES ('7', 'whats_app1', 'Favor entrar em contato para saber mais detalhes. ');
INSERT INTO `configuracoes` VALUES ('8', 'whats_app2', 'Nome da sua Loja');
INSERT INTO `configuracoes` VALUES ('9', 'whats_app3', '(00) 00000-0000');
INSERT INTO `configuracoes` VALUES ('10', 'whats_app4', null);
INSERT INTO `configuracoes` VALUES ('11', 'whats_app5', null);
INSERT INTO `configuracoes` VALUES ('12', 'whats_app6', null);
INSERT INTO `configuracoes` VALUES ('13', 'masteros_0', '@sistema.com');
INSERT INTO `configuracoes` VALUES ('14', 'masteros_1', null);
INSERT INTO `configuracoes` VALUES ('15', 'masteros_2', null);
INSERT INTO `configuracoes` VALUES ('16', 'masteros_3', null);
INSERT INTO `configuracoes` VALUES ('17', 'masteros_4', null);
INSERT INTO `configuracoes` VALUES ('18', 'masteros_5', null);
INSERT INTO `configuracoes` VALUES ('19', 'masteros_6', null);
INSERT INTO `configuracoes` VALUES ('20', 'masteros_7', null);
INSERT INTO `configuracoes` VALUES ('21', 'masteros_8', null);
INSERT INTO `configuracoes` VALUES ('22', 'masteros_9', null);
INSERT INTO `configuracoes` VALUES ('23', 'gerenciador_arquivos', 'arquivos_old/arquivos');
INSERT INTO `configuracoes` VALUES ('24', 'control_baixa', '1');
INSERT INTO `configuracoes` VALUES ('25', 'control_editos', '0');
INSERT INTO `configuracoes` VALUES ('26', 'control_datatable', '0');
INSERT INTO `configuracoes` VALUES ('27', 'pix_key', '');
INSERT INTO `migrations` VALUES ('20210114151944');
INSERT INTO `permissoes` VALUES ('1', 'Administrador', 'a:53:{s:8:\"aCliente\";s:1:\"1\";s:8:\"eCliente\";s:1:\"1\";s:8:\"dCliente\";s:1:\"1\";s:8:\"vCliente\";s:1:\"1\";s:8:\"aProduto\";s:1:\"1\";s:8:\"eProduto\";s:1:\"1\";s:8:\"dProduto\";s:1:\"1\";s:8:\"vProduto\";s:1:\"1\";s:8:\"aServico\";s:1:\"1\";s:8:\"eServico\";s:1:\"1\";s:8:\"dServico\";s:1:\"1\";s:8:\"vServico\";s:1:\"1\";s:3:\"aOs\";s:1:\"1\";s:3:\"eOs\";s:1:\"1\";s:3:\"dOs\";s:1:\"1\";s:3:\"vOs\";s:1:\"1\";s:6:\"aVenda\";s:1:\"1\";s:6:\"eVenda\";s:1:\"1\";s:6:\"dVenda\";s:1:\"1\";s:6:\"vVenda\";s:1:\"1\";s:9:\"aGarantia\";s:1:\"1\";s:9:\"eGarantia\";s:1:\"1\";s:9:\"dGarantia\";s:1:\"1\";s:9:\"vGarantia\";s:1:\"1\";s:8:\"aArquivo\";s:1:\"1\";s:8:\"eArquivo\";s:1:\"1\";s:8:\"dArquivo\";s:1:\"1\";s:8:\"vArquivo\";s:1:\"1\";s:10:\"aPagamento\";s:1:\"1\";s:10:\"ePagamento\";s:1:\"1\";s:10:\"dPagamento\";s:1:\"1\";s:10:\"vPagamento\";s:1:\"1\";s:11:\"aLancamento\";s:1:\"1\";s:11:\"eLancamento\";s:1:\"1\";s:11:\"dLancamento\";s:1:\"1\";s:11:\"vLancamento\";s:1:\"1\";s:8:\"cUsuario\";s:1:\"1\";s:9:\"cEmitente\";s:1:\"1\";s:10:\"cPermissao\";s:1:\"1\";s:7:\"cBackup\";s:1:\"1\";s:10:\"cAuditoria\";s:1:\"1\";s:6:\"cEmail\";s:1:\"1\";s:8:\"cSistema\";s:1:\"1\";s:8:\"rCliente\";s:1:\"1\";s:8:\"rProduto\";s:1:\"1\";s:8:\"rServico\";s:1:\"1\";s:3:\"rOs\";s:1:\"1\";s:6:\"rVenda\";s:1:\"1\";s:11:\"rFinanceiro\";s:1:\"1\";s:9:\"aCobranca\";s:1:\"1\";s:9:\"eCobranca\";s:1:\"1\";s:9:\"dCobranca\";s:1:\"1\";s:9:\"vCobranca\";s:1:\"1\";}', '1', '2021-02-08');
INSERT INTO `usuarios` VALUES ('1', 'admin_name', 'XX-00.000.000', '000.000.000-00', '00000-000', 'Rua', '0', 'Bairro', 'Cidade', 'Estado', 'admin_email', 'admin_password', '(00) 00000-0000', '', '1', 'admin_created_at', '1', 'admin_expiracao');
