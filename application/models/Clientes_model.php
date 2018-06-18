<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Clientes_model extends MY_Model
{

    public $table = 'clientes';
    public $primary_key = 'idClientes';
    public $select_column = array('idClientes', 'nomeCliente', 'sexo', 'pessoa_fisica', 'documento', 'telefone', 'celular', 'email', 'dataCadastro', 'rua', 'numero', 'bairro', 'cidade', 'estado', 'cep');

    public $order_column = array(null, 'idClientes', 'nomeCliente', 'documento', 'celular', 'dataCadastro');
    public $timestamps = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function get_query()
    {
        $this->db->select($this->select_column);
        $this->db->from($this->table);
        if (isset($_POST["search"]["value"])) {
            $this->db->like("idClientes", $_POST["search"]["value"]);
            $this->db->or_like("nomeCliente", $_POST["search"]["value"]);
        }
        if (isset($_POST["order"])) {
            $this->db->order_by($this->order_column[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else {
            $this->db->order_by('idClientes', 'DESC');
        }
    }

    public function get_datatables()
    {
        $this->get_query();
        if ($_POST["length"] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function get_filtered_data()
    {
        $this->get_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function get_all_data()
    {
        $this->db->select("*");
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }

    public function delete_many($items)
    {
        $this->db->where_in($this->primary_key, $items);
        return $this->db->delete($this->table);
    }

    public function delete_linked($id)
    {

        $this->db->where('clientes_id', $id);
        $os = $this->db->get('os')->result();

        if ($os) {
            foreach ($os as $o) {
                $this->db->where('os_id', $o->idOs);
                $this->db->delete('servicos_os');
                $this->db->where('os_id', $o->idOs);
                $this->db->delete('produtos_os');
                $this->db->where('idOs', $o->idOs);
                $this->db->delete('os');
            }
        }

        $this->db->where('clientes_id', $id);
        $vendas = $this->db->get('vendas')->result();
        if ($vendas) {
            foreach ($vendas as $v) {
                $this->db->where('vendas_id', $v->idVendas);
                $this->db->delete('itens_de_vendas');
                $this->db->where('idVendas', $v->idVendas);
                $this->db->delete('vendas');
            }
        }

        $this->db->where('clientes_id', $id);
        $this->db->delete('lancamentos');
    }

}

/* End of file Clientes_model.php */
/* Location: ./application/models/Clientes_model.php */
