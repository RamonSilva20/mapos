<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Persons_model extends MY_Model
{

    var $table = 'persons'; 
    var $primary_key = 'id'; 
    var $select_column = array('id','company','name','company_name','cpf_cnpj','rg_ie','phone','celphone','email','image','obs','active','client','supplier','employee','shipping_company','created_at','updated_at');

    var $order_column = array(null,  null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
    var $timestamps = True;

    function __construct()
    {
        parent::__construct();
    }


    public function get_query()  
    {  
        $this->db->select($this->select_column);  
        $this->db->from($this->table);  
        if(isset($_POST["search"]["value"]))  
        {  
            $this->db->like("name", $_POST["search"]["value"]);  
        }  
        if(isset($_POST["order"]))  
        {  
            $this->db->order_by($this->order_column[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);  
        }  
        else  
        {  
            $this->db->order_by('id', 'DESC');  
        }  
    }  
    
    public function get_datatables(){  
        $this->get_query();  
        if($_POST["length"] != -1)  
           {  
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


    function delete_many($items)
    {
        $this->db->where_in($this->primary_key, $items);
        return $this->db->delete($this->table);
    }

}

/* End of file Persons_model.php */
/* Location: ./application/models/Persons_model.php */