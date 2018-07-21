<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Arquivos_model extends MY_Model
{

    public $table = 'documentos';
    public $primary_key = 'idDocumentos';
    public $select_column = array('idDocumentos', 'documento', 'descricao', 'file', 'path', 'url', 'cadastro', 'categoria', 'tipo', 'tamanho');

    public $order_column = array('idDocumentos', 'documento', 'cadastro', 'tipo', 'tamanho',null);
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
            $this->db->like("idDocumentos", $_POST["search"]["value"]);
            $this->db->or_like("documento", $_POST["search"]["value"]);
        }
        if (isset($_POST["order"])) {
            $this->db->order_by($this->order_column[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else {
            $this->db->order_by('idDocumentos', 'DESC');
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

    // get total rows
    public function total_rows($q = null)
    {
        if ($q) {
            $this->db->like('idDocumentos', $q);
            $this->db->or_like('documento', $q);
        }
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }

    // get data with limit and search
    public function get_limit_data($limit, $start = 0, $q = null)
    {
        $this->db->order_by($this->primary_key, $this->order);

        if ($q) {

            $this->db->like('idDocumentos', $q);
            $this->db->or_like('documento', $q);
        }

        $this->db->limit($limit, $start);
        return $this->db->get($this->table)->result();
    }

}

/* End of file Arquivos_model.php */
/* Location: ./application/models/Arquivos_model.php */
