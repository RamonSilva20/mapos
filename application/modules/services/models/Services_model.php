<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Services_model extends MY_Model
{

    var $table = 'services'; 
    var $primary_key = 'id'; 
    var $select_column = array('id','service_name','price','active','created_at','updated_at',
	 );

    var $order_column = array(null,  null, null, null, null, null,
	 );
    var $timestamps = False;

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
            $this->db->like("id", $_POST["search"]["value"]);  
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


    // get all
    /*function get_all()
    {
        $this->db->order_by($this->id, $this->order);
        return $this->db->get($this->table)->result();
    }*/

    // get data by id
    /*function get_by_id($id)
    {
        $this->db->where($this->id, $id);
        return $this->db->get($this->table)->row();
    }*/
    
    // get total rows
    function total_rows($q = NULL) {

        if($q){

        $this->db->like('id', $q);
	$this->db->or_like('service_name', $q);
	$this->db->or_like('price', $q);
	$this->db->or_like('active', $q);
	$this->db->or_like('created_at', $q);
	$this->db->or_like('updated_at', $q);
	 } 
	 
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }

    // get data with limit and search
    function get_limit_data($limit, $start = 0, $q = NULL) {
        $this->db->order_by($this->primary_key, $this->order);

        if($q){

        $this->db->like('id', $q);
	$this->db->or_like('service_name', $q);
	$this->db->or_like('price', $q);
	$this->db->or_like('active', $q);
	$this->db->or_like('created_at', $q);
	$this->db->or_like('updated_at', $q);
	 } 
	 
        $this->db->limit($limit, $start);
        return $this->db->get($this->table)->result();
    }

    // insert data
    /*function insert($data)
    {
        $this->db->insert($this->table, $data);
    }*/

    // update data
    /*function update($id, $data)
    {
        $this->db->where($this->id, $id);
        $this->db->update($this->table, $data);
    }*/

    // delete data
    /*function delete($id)
    {
        $this->db->where($this->id, $id);
        $this->db->delete($this->table);
    }*/

    function delete_many($items)
    {
        $this->db->where_in($this->primary_key, $items);
        return $this->db->delete($this->table);
    }

}

/* End of file Services_model.php */
/* Location: ./application/models/Services_model.php */