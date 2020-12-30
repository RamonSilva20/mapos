--
-- Estrutura da tabela `cobrancas`
--

CREATE TABLE `cobrancas` (
  `idCobranca` int(4) NOT NULL,
  `charge_id` int(4) DEFAULT NULL,
  `conditional_discount_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `custom_id` int(4) DEFAULT NULL,
  `expire_at` date NOT NULL,
  `message` varchar(255) NOT NULL,
  `payment_method` varchar(11) DEFAULT NULL,
  `payment_url` varchar(255) DEFAULT NULL,
  `request_delivery_address` varchar(64) DEFAULT NULL,
  `status` varchar(36) NOT NULL,
  `total` varchar(15) DEFAULT NULL,
  `barcode` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `payment` varchar(64) NOT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `vendas_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
--
-- Índices para tabela `cobrancas`
--
ALTER TABLE `cobrancas`
  ADD PRIMARY KEY (`idCobranca`),
  ADD KEY `fk_cobrancas_vendas1_idx` (`vendas_id`);
 
--
-- AUTO_INCREMENT de tabela `cobrancas`
--
ALTER TABLE `cobrancas`
  MODIFY `idCobranca` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
 
--
-- Limitadores para a tabela `cobrancas`
--
ALTER TABLE `cobrancas`
  ADD CONSTRAINT `fk_cobrancas_vendas1` FOREIGN KEY (`vendas_id`) REFERENCES `vendas` (`idVendas`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;
 