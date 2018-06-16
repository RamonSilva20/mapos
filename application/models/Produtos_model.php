<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Produtos_model extends MY_Model
{

    public $table = 'produtos';
    public $primary_key = 'idProdutos';
    public $select_column = array('idProdutos', 'descricao', 'unidade', 'precoCompra', 'precoVenda', 'estoque', 'estoqueMinimo', 'saida', 'entrada');

    public $order_column = array(null, 'idProdutos', 'descricao', 'unidade', 'precoCompra', 'precoVenda', 'estoque', 'estoqueMinimo');
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
            $this->db->like("idProdutos", $_POST["search"]["value"]);
            $this->db->or_like("descricao", $_POST["search"]["value"]);
        }
        if (isset($_POST["order"])) {
            $this->db->order_by($this->order_column[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else {
            $this->db->order_by('idProdutos', 'DESC');
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

    public function delete_linked($id){
        $this->db->where_in('produtos_id', $id);
        return $this->db->delete('produtos_os');
    }

}

/* End of file Produtos_model.php */
/* Location: ./application/models/Produtos_model.php */
