<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Identificador da cobrança no gateway, que é uma string (ex.: "1234567890"),
 * e não o id numérico interno.
 *
 * Duas correções em relação ao arquivo original:
 *
 * 1. O SQL tinha dois comandos na mesma string. O driver mysqli do CI3 executa
 *    uma instrução por chamada, então a migration sempre falhou com erro de
 *    sintaxe (1064) e travava latest() na versão 20210125173741. Cada comando
 *    vai agora em sua própria chamada.
 *
 * 2. A criação de clientes.asaas_id saiu daqui. A migration 20221119210810 já
 *    faz esse trabalho, e manter os dois faria o segundo ADD COLUMN bater na
 *    coluna que este criaria (erro 1060). O efeito pretendido continua o mesmo:
 *    a coluna existe no fim da cadeia, criada pela migration de 2022.
 */
class Migration_asaas_payment_gateway extends CI_Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE `cobrancas` MODIFY COLUMN `charge_id` VARCHAR(255) NULL');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE `cobrancas` MODIFY COLUMN `charge_id` INT(11) NOT NULL');
    }
}
