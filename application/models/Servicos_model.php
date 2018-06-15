<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Servicos_model extends MY_Model {

    var $table = 'servicos'; 
    var $primary_key = 'idServicos'; 
    var $select_column = array('idServicos','nome','descricao','preco');

    var $order_column = array(null,'idServicos','nome','descricao','preco');
    var $timestamps = False;

    public function __construct() {
        parent::__construct();
    }

    public function get_query() {  

        $this->db->select($this->select_column);  
        $this->db->from($this->table);  
        if(isset($_POST["search"]["value"])) {  
            $this->db->like("idServicos", $_POST["search"]["value"]);  
            $this->db->or_like("nome", $_POST["search"]["value"]);  
        }  
        if(isset($_POST["order"])) {  
            $this->db->order_by($this->order_column[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);  
        }  
        else {  
            $this->db->order_by('idServicos', 'DESC');  
        }  
    }  
    
    public function get_datatables(){  
        $this->get_query();  
        if($_POST["length"] != -1) {  
            $this->db->limit($_POST['length'], $_POST['start']);  
        }  
        $query = $this->db->get();  
        return $query->result();  
    } 

    public function get_filtered_data(){  
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
    public function total_rows($q = NULL) {

        if($q){
            $this->db->like('idServicos', $q);
            $this->db->or_like('nome', $q);
            $this->db->or_like('descricao', $q);
            $this->db->or_like('preco', $q);
        } 
	 
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }

    // get data with limit and search
    public function get_limit_data($limit, $start = 0, $q = NULL) {
        $this->db->order_by($this->primary_key, $this->order);

        if($q){

            $this->db->like('idServicos', $q);
            $this->db->or_like('nome', $q);
            $this->db->or_like('descricao', $q);
            $this->db->or_like('preco', $q);
        } 
	 
        $this->db->limit($limit, $start);
        return $this->db->get($this->table)->result();
    }

    public function delete_many($items) {
        $this->db->where_in($this->primary_key, $items);
        return $this->db->delete($this->table);
    }

    public function delete_linked($id){
        $this->db->where_in('servicos_id', $id);
        return $this->db->delete('servicos_os');
    }

}

/* End of file Servicos_model.php */
/* Location: ./application/models/Servicos_model.php */