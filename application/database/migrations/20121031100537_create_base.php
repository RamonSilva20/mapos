<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_create_base extends CI_Migration
{
    public function up()
    {
        ## Create Table ci_sessions
        $this->dbforge->add_field([
            'id' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => false,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => false,
            ],
            'timestamp' => [
                'type' => 'INT',
                'constraint' => 1,
                'unsigned' => true,
                'null' => false,
                'default' => '0',
            ],
            'data' => [
                'type' => 'BLOB',
                'null' => false,
            ],
        ]);
        $this->dbforge->create_table("ci_sessions", true);
        $this->db->query('ALTER TABLE  `ci_sessions` ENGINE = InnoDB');

        ## Create Table clientes
        $this->dbforge->add_field([
            'idClientes' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'nomeCliente' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'sexo' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'pessoa_fisica' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => '1',
            ],
            'documento' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
            ],
            'telefone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
            ],
            'celular' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'dataCadastro' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'rua' => [
                'type' => 'VARCHAR',
                'constraint' => 70,
                'null' => true,
            ],
            'numero' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => true,
            ],
            'bairro' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'cidade' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'estado' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'cep' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idClientes", true);
        $this->dbforge->create_table("clientes", true);
        $this->db->query('ALTER TABLE  `clientes` ENGINE = InnoDB');

        ## Create Table categorias
        $this->dbforge->add_field([
            'idCategorias' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'categoria' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'cadastro' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'tipo' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idCategorias", true);
        $this->dbforge->create_table("categorias", true);
        $this->db->query('ALTER TABLE  `categorias` ENGINE = InnoDB');

        ## Create Table contas
        $this->dbforge->add_field([
            'idContas' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'conta' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'banco' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'numero' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'saldo' => [
                'type' => 'DECIMAL',
                'constraint' => 10, 2,
                'null' => true,
            ],
            'cadastro' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'tipo' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idContas", true);
        $this->dbforge->create_table("contas", true);
        $this->db->query('ALTER TABLE  `contas` ENGINE = InnoDB');

        ## Create Table lancamentos
        $this->dbforge->add_field([
            'idLancamentos' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'descricao' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'valor' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => false,
            ],
            'data_vencimento' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'data_pagamento' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'baixado' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
                'default' => '0',
            ],
            'cliente_fornecedor' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'forma_pgto' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'tipo' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'anexo' => [
                'type' => 'VARCHAR',
                'constraint' => 250,
                'null' => true,
            ],
            'clientes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'categorias_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'contas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'vendas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idLancamentos", true);
        $this->dbforge->create_table("lancamentos", true);
        $this->db->query('ALTER TABLE  `lancamentos` ENGINE = InnoDB');
        $this->db->query('ALTER TABLE  `lancamentos` ADD INDEX `fk_lancamentos_clientes1` (`clientes_id` ASC)');
        $this->db->query('ALTER TABLE  `lancamentos` ADD INDEX `fk_lancamentos_categorias1_idx` (`categorias_id` ASC)');
        $this->db->query('ALTER TABLE  `lancamentos` ADD INDEX `fk_lancamentos_contas1_idx` (`contas_id` ASC)');
        $this->db->query('ALTER TABLE  `lancamentos` ADD CONSTRAINT `fk_lancamentos_clientes1`
			FOREIGN KEY (`clientes_id`)
			REFERENCES `clientes` (`idClientes`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `lancamentos` ADD CONSTRAINT `fk_lancamentos_categorias1`
			FOREIGN KEY (`categorias_id`)
			REFERENCES `categorias` (`idCategorias`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `lancamentos` ADD  CONSTRAINT `fk_lancamentos_contas1`
			FOREIGN KEY (`contas_id`)
			REFERENCES `contas` (`idContas`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');

        ## Create Table permissoes
        $this->dbforge->add_field([
            'idPermissao' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => false,
            ],
            'permissoes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'situacao' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'data' => [
                'type' => 'DATE',
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idPermissao", true);
        $this->dbforge->create_table("permissoes", true);
        $this->db->query('ALTER TABLE  `permissoes` ENGINE = InnoDB');

        ## Create Table usuarios
        $this->dbforge->add_field([
            'idUsuarios' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => false,
            ],
            'rg' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'cpf' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
            ],
            'rua' => [
                'type' => 'VARCHAR',
                'constraint' => 70,
                'null' => true,
            ],
            'numero' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => true,
            ],
            'bairro' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'cidade' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'estado' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => false,
            ],
            'senha' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'telefone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
            ],
            'celular' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'situacao' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
            ],
            'dataCadastro' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'permissoes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'dataExpiracao' => [
                'type' => 'DATE',
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idUsuarios", true);
        $this->dbforge->create_table("usuarios", true);
        $this->db->query('ALTER TABLE  `usuarios` ENGINE = InnoDB');
        $this->db->query('ALTER TABLE  `usuarios` ADD INDEX `fk_usuarios_permissoes1_idx` (`permissoes_id` ASC)');
        $this->db->query('ALTER TABLE  `usuarios` ADD CONSTRAINT `fk_usuarios_permissoes1`
			FOREIGN KEY (`permissoes_id`)
			REFERENCES `permissoes` (`idPermissao`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');

        ## Create Table garantias
        $this->dbforge->add_field([
            'idGarantias' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'dataGarantia' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'refGarantia' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => true,
            ],
            'textoGarantia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'usuarios_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idGarantias", true);
        $this->dbforge->create_table("garantias", true);
        $this->db->query('ALTER TABLE  `garantias` ADD INDEX `fk_garantias_usuarios1` (`usuarios_id` ASC)');
        $this->db->query('ALTER TABLE  `garantias` ADD CONSTRAINT `fk_garantias_usuarios1`
			FOREIGN KEY (`usuarios_id`)
			REFERENCES `usuarios` (`idUsuarios`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `garantias` ENGINE = InnoDB');

        ## Create Table os
        $this->dbforge->add_field([
            'idOs' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'dataInicial' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'dataFinal' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'garantia' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'descricaoProduto' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'defeito' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'observacoes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'laudoTecnico' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'valorTotal' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => true,
            ],
            'clientes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'usuarios_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'lancamento' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'faturado' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
            ],
            'garantias_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idOs", true);
        $this->dbforge->create_table("os", true);
        $this->db->query('ALTER TABLE  `os` ADD INDEX `fk_os_clientes1` (`clientes_id` ASC)');
        $this->db->query('ALTER TABLE  `os` ADD INDEX `fk_os_usuarios1` (`usuarios_id` ASC)');
        $this->db->query('ALTER TABLE  `os` ADD INDEX `fk_os_lancamentos1` (`lancamento` ASC)');
        $this->db->query('ALTER TABLE  `os` ADD INDEX `fk_os_garantias1` (`garantias_id` ASC)');
        $this->db->query('ALTER TABLE  `os` ADD CONSTRAINT `fk_os_clientes1`
			FOREIGN KEY (`clientes_id`)
			REFERENCES `clientes` (`idClientes`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `os` ADD CONSTRAINT `fk_os_lancamentos1`
			FOREIGN KEY (`lancamento`)
			REFERENCES `lancamentos` (`idLancamentos`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `os` ADD CONSTRAINT `fk_os_usuarios1`
			FOREIGN KEY (`usuarios_id`)
			REFERENCES `usuarios` (`idUsuarios`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `os` ENGINE = InnoDB');

        ## Create Table produtos
        $this->dbforge->add_field([
            'idProdutos' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'codDeBarra' => [
                'type' => 'VARCHAR',
                'constraint' => 70,
                'null' => false,
            ],
            'descricao' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => false,
            ],
            'unidade' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'precoCompra' => [
                'type' => 'DECIMAL',
                'constraint' => 10, 2,
                'null' => true,
            ],
            'precoVenda' => [
                'type' => 'DECIMAL',
                'constraint' => 10, 2,
                'null' => false,
            ],
            'estoque' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'estoqueMinimo' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'saida' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'entrada' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idProdutos", true);
        $this->dbforge->create_table("produtos", true);
        $this->db->query('ALTER TABLE  `produtos` ENGINE = InnoDB');

        ## Create Table produtos_os
        $this->dbforge->add_field([
            'idProdutos_os' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'quantidade' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'descricao' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'preco' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => true,
            ],
            'os_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'produtos_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'subTotal' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idProdutos_os", true);
        $this->dbforge->create_table("produtos_os", true);
        $this->db->query('ALTER TABLE  `produtos_os` ADD INDEX `fk_produtos_os_os1` (`os_id` ASC)');
        $this->db->query('ALTER TABLE  `produtos_os` ADD INDEX `fk_produtos_os_produtos1` (`produtos_id` ASC)');
        $this->db->query('ALTER TABLE  `produtos_os` ADD CONSTRAINT `fk_produtos_os_os1`
			FOREIGN KEY (`os_id`)
			REFERENCES `os` (`idOs`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `produtos_os` ADD CONSTRAINT `fk_produtos_os_produtos1`
			FOREIGN KEY (`produtos_id`)
			REFERENCES `produtos` (`idProdutos`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `produtos_os` ENGINE = InnoDB');

        ## Create Table servicos
        $this->dbforge->add_field([
            'idServicos' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => false,
            ],
            'descricao' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'preco' => [
                'type' => 'DECIMAL',
                'constraint' => 10, 2,
                'null' => false,
            ],
        ]);
        $this->dbforge->add_key("idServicos", true);
        $this->dbforge->create_table("servicos", true);
        $this->db->query('ALTER TABLE  `servicos` ENGINE = InnoDB');

        ## Create Table servicos_os
        $this->dbforge->add_field([
            'idServicos_os' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'servico' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'quantidade' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'preco' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => true,
            ],
            'os_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'servicos_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'subTotal' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idServicos_os", true);
        $this->dbforge->create_table("servicos_os", true);
        $this->db->query('ALTER TABLE  `servicos_os` ADD INDEX `fk_servicos_os_os1` (`os_id` ASC)');
        $this->db->query('ALTER TABLE  `servicos_os` ADD INDEX `fk_servicos_os_servicos1` (`servicos_id` ASC)');
        $this->db->query('ALTER TABLE  `servicos_os` ADD CONSTRAINT `fk_servicos_os_os1`
			FOREIGN KEY (`os_id`)
			REFERENCES `os` (`idOs`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `servicos_os` ADD CONSTRAINT `fk_servicos_os_servicos1`
			FOREIGN KEY (`servicos_id`)
			REFERENCES `servicos` (`idServicos`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `servicos_os` ENGINE = InnoDB');

        ## Create Table vendas
        $this->dbforge->add_field([
            'idVendas' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'dataVenda' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'valorTotal' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'desconto' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'faturado' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'clientes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'usuarios_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'lancamentos_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idVendas", true);
        $this->dbforge->create_table("vendas", true);
        $this->db->query('ALTER TABLE  `vendas` ADD INDEX `fk_vendas_clientes1` (`clientes_id` ASC)');
        $this->db->query('ALTER TABLE  `vendas` ADD INDEX `fk_vendas_usuarios1` (`usuarios_id` ASC)');
        $this->db->query('ALTER TABLE  `vendas` ADD INDEX `fk_vendas_lancamentos1` (`lancamentos_id` ASC)');
        $this->db->query('ALTER TABLE  `vendas` ADD CONSTRAINT `fk_vendas_clientes1`
			FOREIGN KEY (`clientes_id`)
			REFERENCES `clientes` (`idClientes`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `vendas` ADD CONSTRAINT `fk_vendas_usuarios1`
			FOREIGN KEY (`usuarios_id`)
			REFERENCES `usuarios` (`idUsuarios`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `vendas` ADD CONSTRAINT `fk_vendas_lancamentos1`
			FOREIGN KEY (`lancamentos_id`)
			REFERENCES `lancamentos` (`idLancamentos`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `vendas` ENGINE = InnoDB');

        ## Create Table itens_de_vendas
        $this->dbforge->add_field([
            'idItens' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'subTotal' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'quantidade' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'preco' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => true,
            ],
            'vendas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'produtos_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
        ]);
        $this->dbforge->add_key("idItens", true);
        $this->dbforge->create_table("itens_de_vendas", true);
        $this->db->query('ALTER TABLE  `itens_de_vendas` ADD INDEX `fk_itens_de_vendas_vendas1` (`vendas_id` ASC)');
        $this->db->query('ALTER TABLE  `itens_de_vendas` ADD INDEX `fk_itens_de_vendas_produtos1` (`produtos_id` ASC)');
        $this->db->query('ALTER TABLE  `itens_de_vendas` ADD CONSTRAINT `fk_itens_de_vendas_vendas1`
			FOREIGN KEY (`vendas_id`)
			REFERENCES `vendas` (`idVendas`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `itens_de_vendas` ADD CONSTRAINT `fk_itens_de_vendas_produtos1`
			FOREIGN KEY (`produtos_id`)
			REFERENCES `produtos` (`idProdutos`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `itens_de_vendas` ENGINE = InnoDB');

        ## Create Table anexos
        $this->dbforge->add_field([
            'idAnexos' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'anexo' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'thumb' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'url' => [
                'type' => 'VARCHAR',
                'constraint' => 300,
                'null' => true,
            ],
            'path' => [
                'type' => 'VARCHAR',
                'constraint' => 300,
                'null' => true,
            ],
            'os_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
        ]);
        $this->dbforge->add_key("idAnexos", true);
        $this->dbforge->create_table("anexos", true);
        $this->db->query('ALTER TABLE  `anexos` ADD INDEX `fk_anexos_os1` (`os_id` ASC)');
        $this->db->query('ALTER TABLE  `anexos` ADD CONSTRAINT `fk_anexos_os1`
			FOREIGN KEY (`os_id`)
			REFERENCES `os` (`idOs`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `anexos` ENGINE = InnoDB');

        ## Create Table documentos
        $this->dbforge->add_field([
            'idDocumentos' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'documento' => [
                'type' => 'VARCHAR',
                'constraint' => 70,
                'null' => true,
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'file' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'path' => [
                'type' => 'VARCHAR',
                'constraint' => 300,
                'null' => true,
            ],
            'url' => [
                'type' => 'VARCHAR',
                'constraint' => 300,
                'null' => true,
            ],
            'cadastro' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'categoria' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'tipo' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => true,
            ],
            'tamanho' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idDocumentos", true);
        $this->dbforge->create_table("documentos", true);
        $this->db->query('ALTER TABLE  `documentos` ENGINE = InnoDB');

        ## Create Table marcas
        $this->dbforge->add_field([
            'idMarcas' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'marca' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'cadastro' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'situacao' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idMarcas", true);
        $this->dbforge->create_table("marcas", true);
        $this->db->query('ALTER TABLE  `marcas` ENGINE = InnoDB');

        ## Create Table equipamentos
        $this->dbforge->add_field([
            'idEquipamentos' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'equipamento' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => false,
            ],
            'num_serie' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'modelo' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'cor' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'descricao' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],
            'tensao' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'potencia' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'voltagem' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'data_fabricacao' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'marcas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'clientes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idEquipamentos", true);
        $this->dbforge->create_table("equipamentos", true);
        $this->db->query('ALTER TABLE  `equipamentos` ADD INDEX `fk_equipanentos_marcas1_idx` (`marcas_id` ASC)');
        $this->db->query('ALTER TABLE  `equipamentos` ADD INDEX `fk_equipanentos_clientes1_idx` (`clientes_id` ASC)');
        $this->db->query('ALTER TABLE  `equipamentos` ADD CONSTRAINT `fk_equipanentos_marcas1`
			FOREIGN KEY (`marcas_id`)
			REFERENCES `marcas` (`idMarcas`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `equipamentos` ADD CONSTRAINT `fk_equipanentos_clientes1`
			FOREIGN KEY (`clientes_id`)
			REFERENCES `clientes` (`idClientes`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `equipamentos` ENGINE = InnoDB');

        ## Create Table equipamentos_os
        $this->dbforge->add_field([
            'idEquipamentos_os' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'defeito_declarado' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true,
            ],
            'defeito_encontrado' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true,
            ],
            'solucao' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'equipamentos_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'os_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idEquipamentos_os", true);
        $this->dbforge->create_table("equipamentos_os", true);
        $this->db->query('ALTER TABLE  `equipamentos_os` ADD INDEX `fk_equipamentos_os_equipanentos1_idx` (`equipamentos_id` ASC)');
        $this->db->query('ALTER TABLE  `equipamentos_os` ADD INDEX `fk_equipamentos_os_os1_idx` (`os_id` ASC)');
        $this->db->query('ALTER TABLE  `equipamentos_os` ADD CONSTRAINT `fk_equipamentos_os_equipanentos1`
			FOREIGN KEY (`equipamentos_id`)
			REFERENCES `equipamentos` (`idEquipamentos`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `equipamentos_os` ADD CONSTRAINT `fk_equipamentos_os_os1`
			FOREIGN KEY (`os_id`)
			REFERENCES `os` (`idOs`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `equipamentos_os` ENGINE = InnoDB');

        ## Create Table logs
        $this->dbforge->add_field([
            'idLogs' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'usuario' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'tarefa' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'data' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'hora' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'ip' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idLogs", true);
        $this->dbforge->create_table("logs", true);
        $this->db->query('ALTER TABLE  `logs` ENGINE = InnoDB');

        ## Create Table emitente
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'cnpj' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'ie' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'rua' => [
                'type' => 'VARCHAR',
                'constraint' => 70,
                'null' => true,
            ],
            'numero' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => true,
            ],
            'bairro' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'cidade' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'uf' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'telefone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'url_logo' => [
                'type' => 'VARCHAR',
                'constraint' => 225,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("id", true);
        $this->dbforge->create_table("emitente", true);
        $this->db->query('ALTER TABLE  `emitente` ENGINE = InnoDB');

        ## Create Table email_queue
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'to' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'cc' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'bcc' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'status' => [
                'type' => 'ENUM("pending","sending","sent","failed")',
                'null' => true,
            ],
            'date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'headers' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("id", true);
        $this->dbforge->create_table("email_queue", true);
        $this->db->query('ALTER TABLE  `email_queue` ENGINE = InnoDB');

        ## Create Table anotacoes_os
        $this->dbforge->add_field([
            'idAnotacoes' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'anotacao' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'data_hora' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'os_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
        ]);
        $this->dbforge->add_key("idAnotacoes", true);
        $this->dbforge->create_table("anotacoes_os", true);
        $this->db->query('ALTER TABLE `anotacoes_os` ENGINE = InnoDB');

        ## Create Table configuracoes
        $this->dbforge->add_field([
            'idConfig' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'config' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
            ],
            'valor' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
            ],
        ]);
        $this->dbforge->add_key("idConfig", true);
        $this->dbforge->create_table("configuracoes", true);
        $this->db->query('ALTER TABLE `configuracoes` ADD CONSTRAINT `unique_valor` UNIQUE (`config`)');
        $this->db->query('ALTER TABLE `configuracoes` ENGINE = InnoDB');



        ## Create Table anotacoes_os
        $this->dbforge->add_field([
            'idPag' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'client_id' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'client_secret' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'public_key' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'access_token' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            
            'default_pag' => [
                'type' => 'INT',
                'null' => false,
            ],
        ]);
        $this->dbforge->add_key("idPag", true);
        $this->dbforge->create_table("pagamento", true);
        $this->db->query('ALTER TABLE `pagamento` ENGINE = InnoDB');
    }

    public function down()
    {
        ### Drop table configuracoes ##
        $this->dbforge->drop_table("configuracoes", true);

        ### Drop table anotacoes_os ##
        $this->dbforge->drop_table("anotacoes_os", true);

        ### Drop table email_queue ##
        $this->dbforge->drop_table("email_queue", true);

        ### Drop table emitente ##
        $this->dbforge->drop_table("emitente", true);

        ### Drop table logs ##
        $this->dbforge->drop_table("logs", true);

        ### Drop table equipamentos_os ##
        $this->dbforge->drop_table("equipamentos_os", true);

        ### Drop table equipamentos ##
        $this->dbforge->drop_table("equipamentos", true);

        ### Drop table marcas ##
        $this->dbforge->drop_table("marcas", true);

        ### Drop table documentos ##
        $this->dbforge->drop_table("documentos", true);

        ### Drop table anexos ##
        $this->dbforge->drop_table("anexos", true);

        ### Drop table itens_de_vendas ##
        $this->dbforge->drop_table("itens_de_vendas", true);

        ### Drop table vendas ##
        $this->dbforge->drop_table("vendas", true);

        ### Drop table servicos_os ##
        $this->dbforge->drop_table("servicos_os", true);

        ### Drop table servicos ##
        $this->dbforge->drop_table("servicos", true);

        ### Drop table produtos_os ##
        $this->dbforge->drop_table("produtos_os", true);

        ### Drop table produtos ##
        $this->dbforge->drop_table("produtos", true);

        ### Drop table os ##
        $this->dbforge->drop_table("os", true);

        ### Drop table garantias ##
        $this->dbforge->drop_table("garantias", true);

        ### Drop table usuarios ##
        $this->dbforge->drop_table("usuarios", true);

        ### Drop table permissoes ##
        $this->dbforge->drop_table("permissoes", true);

        ### Drop table lancamentos ##
        $this->dbforge->drop_table("lancamentos", true);

        ### Drop table contas ##
        $this->dbforge->drop_table("contas", true);

        ### Drop table clientes ##
        $this->dbforge->drop_table("categorias", true);

        ### Drop table clientes ##
        $this->dbforge->drop_table("clientes", true);

        ### Drop table ci_sessions ##
        $this->dbforge->drop_table("ci_sessions", true);

        ### Drop table pagamento ##
        $this->dbforge->drop_table("pagamento", true);
    }
}
