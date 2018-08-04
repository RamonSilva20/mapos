<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Mapos_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {

        $this->db->select($fields);
        $this->db->from($table);
        $this->db->limit($perpage, $start);

        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();
        $result = !$one ? $query->result() : $query->row();

        return $result;

    }

    public function getById($id)
    {

        $this->db->from('usuarios');
        $this->db->select('usuarios.*, permissoes.nome as permissao');
        $this->db->join('permissoes', 'permissoes.idPermissao = usuarios.permissoes_id', 'left');
        $this->db->where('idUsuarios', $id);
        $this->db->limit(1);

        return $this->db->get()->row();

    }

    public function alterarSenha($senha, $oldSenha, $id)
    {

        $this->db->where('idUsuarios', $id);
        $this->db->limit(1);

        $usuario = $this->db->get('usuarios')->row();

        if ($usuario->senha != $oldSenha) {
            return false;
        } else {

            $this->db->set('senha', $senha);
            $this->db->where('idUsuarios', $id);
            return $this->db->update('usuarios');

        }

    }

    public function pesquisarOs($termo)
    {

        $this->db->select('idOs');
        $this->db->from('os');
        $this->db->where('idOs', $termo);
        $this->db->limit(2);

        $os = '';
        $os = $this->db->get()->row();
        $count = $this->db->count_all_results();

        if ($count == 1) {
            return $os;
        }

    }

    public function pesquisar($termo)
    {

        $data = array();

        // buscando clientes
        $this->db->like('nomeCliente', $termo);
        $this->db->limit(5);
        $data['clientes'] = $this->db->get('clientes')->result();

        // buscando telefone
        $this->db->like('celular', $termo);
        $this->db->limit(5);
        $data['celular'] = $this->db->get('clientes')->result();

        // buscando os

        $this->db->like('idOs', $termo);
        $this->db->limit(5);
        $data['os'] = $this->db->get('os')->result();

        // buscando produtos
        $this->db->like('descricao', $termo);
        $this->db->limit(5);
        $data['produtos'] = $this->db->get('produtos')->result();

        //buscando serviços
        $this->db->like('nome', $termo);
        $this->db->limit(5);
        $data['servicos'] = $this->db->get('servicos')->result();

        return $data;

    }

    public function add($table, $data)
    {

        $this->db->insert($table, $data);

        if ($this->db->affected_rows() == '1') {

            return true;

        }

        return false;

    }

    public function edit($table, $data, $fieldID, $ID)
    {

        $this->db->where($fieldID, $ID);
        $this->db->update($table, $data);
        if ($this->db->affected_rows() >= 0) {
            return true;
        }

        return false;

    }

    public function delete($table, $fieldID, $ID)
    {

        $this->db->where($fieldID, $ID);
        $this->db->delete($table);
        if ($this->db->affected_rows() == '1') {
            return true;
        }
        return false;

    }

    public function count($table)
    {
        return $this->db->count_all($table);
    }

    public function getOsAtrasadas()
    {

        $this->db->select('os.*, clientes.nomeCliente');
        $this->db->from('os');
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id');
        $this->db->where('dataFinal <=', 'CURDATE()', false);
        $this->db->where('status !=', 'Cancelado');
        $this->db->where('faturado =', '0');
        $this->db->limit(100);

        return $this->db->get()->result();

    }

    public function getOsVencendo($tecnico)
    {

        $this->db->select('os.*, clientes.nomeCliente');
        $this->db->from('os');
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id');

        // $this->db->where('dataFinal >=','CURDATE()',FALSE);
        $this->db->where('status !=', 'Faturado');
        $this->db->where('status !=', 'Cancelado');
        $this->db->order_by('dataFinal', 'desc');
        if ($tecnico != "") {
            $this->db->where('usuarios_id =', $tecnico);
        }

        $this->db->limit(1000);

        return $this->db->get()->result();

    }

    public function getOsAbertas()
    {

        $this->db->select('os.*, clientes.nomeCliente');
        $this->db->from('os');
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id');
        $this->db->where('os.status', 'Aberto');
        $this->db->limit(10);

        return $this->db->get()->result();

    }

    public function getOsAndamento()
    {

        $this->db->select('os.*, clientes.nomeCliente');
        $this->db->from('os');
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id');
        $this->db->where('os.status', 'Em Andamento');
        $this->db->limit(10);

        return $this->db->get()->result();

    }

    public function getOsOrcamento()
    {

        $this->db->select('os.*, clientes.nomeCliente');
        $this->db->from('os');
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id');
        $this->db->where('os.status', 'Orçamento');
        $this->db->limit(10);

        return $this->db->get()->result();

    }

    public function getOsFinalizado()
    {

        $this->db->select('os.*, clientes.nomeCliente');
        $this->db->from('os');
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id');
        $this->db->where('os.status', 'Finalizado');
        $this->db->limit(10);

        return $this->db->get()->result();

    }

    public function getProdutosMinimo()
    {

        $sql = "SELECT * FROM produtos WHERE estoque <= estoqueMinimo LIMIT 10";
        return $this->db->query($sql)->result();

    }

    public function getOsEstatisticas()
    {

        $sql = "SELECT status, COUNT(status) as total FROM os GROUP BY status ORDER BY status";
        return $this->db->query($sql)->result();

    }

    public function getEstatisticasFinanceiro()
    {
        $sql = "SELECT SUM(CASE WHEN baixado = 1 AND tipo = 'receita' AND data_pagamento >= '20160101' AND data_pagamento < '20160930' THEN valor END) as total_receita,
                       SUM(CASE WHEN baixado = 1 AND tipo = 'despesa' AND data_pagamento >= '20160101' AND data_pagamento < '20160930' THEN valor END) as total_despesa,
                       SUM(CASE WHEN baixado = 0 AND tipo = 'receita' AND data_vencimento >= '20160101' AND data_vencimento < '20160930' THEN valor END) as total_receita_pendente,
                       SUM(CASE WHEN baixado = 0 AND tipo = 'despesa' AND data_vencimento >= '20160101' AND data_vencimento < '20160930' THEN valor END) as total_despesa_pendente FROM lancamentos";
        return $this->db->query($sql)->row();
    }

    public function getEmitente()
    {

        return $this->db->get('emitente')->result();

    }

    public function addEmitente($nome, $cnpj, $ie, $logradouro, $numero, $bairro, $cidade, $uf, $telefone, $celular, $whatsapp, $email, $logo)
    {

        $this->db->set('nome', $nome);
        $this->db->set('cnpj', $cnpj);
        $this->db->set('ie', $ie);
        $this->db->set('rua', $logradouro);
        $this->db->set('numero', $numero);
        $this->db->set('bairro', $bairro);
        $this->db->set('cidade', $cidade);
        $this->db->set('uf', $uf);
        $this->db->set('telefone', $telefone);
        $this->db->set('celular', $celular);
        $this->db->set('whatsapp', $whatsapp);
        $this->db->set('email', $email);
        $this->db->set('url_logo', $logo);

        return $this->db->insert('emitente');

    }

    public function editEmitente($id, $nome, $cnpj, $ie, $logradouro, $numero, $bairro, $cidade, $uf, $telefone, $celular, $whatsapp, $email)
    {

        $this->db->set('nome', $nome);
        $this->db->set('cnpj', $cnpj);
        $this->db->set('ie', $ie);
        $this->db->set('rua', $logradouro);
        $this->db->set('numero', $numero);
        $this->db->set('bairro', $bairro);
        $this->db->set('cidade', $cidade);
        $this->db->set('uf', $uf);
        $this->db->set('telefone', $telefone);
        $this->db->set('celular', $celular);
        $this->db->set('whatsapp', $whatsapp);
        $this->db->set('email', $email);
        $this->db->where('id', $id);

        return $this->db->update('emitente');

    }

    public function editLogo($id, $logo)
    {

        $this->db->set('url_logo', $logo);
        $this->db->where('id', $id);

        return $this->db->update('emitente');

    }

}
