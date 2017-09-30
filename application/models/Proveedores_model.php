<?php
class Proveedores_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    
    function get($table,$fields,$where='',$perpage=0,$start=0,$one=false,$array='array'){
        
        $this->db->select($fields);
        $this->db->from($table);
        $this->db->order_by('idProveedores','desc');
        $this->db->limit($perpage,$start);
        if($where){
            $this->db->where($where);
        }
        
        $query = $this->db->get();
        
        $result =  !$one  ? $query->result() : $query->row();
        return $result;
    }

    function getById($id){
        $this->db->where('idProveedores',$id);
        $this->db->limit(1);
        return $this->db->get('proveedores')->row();
    }
    
    function add($table,$data){
        $this->db->insert($table, $data);         
        if ($this->db->affected_rows() == '1')
		{
			return TRUE;
		}
		
		return FALSE;       
    }
    
    function edit($table,$data,$fieldID,$ID){
        $this->db->where($fieldID,$ID);
        $this->db->update($table, $data);

        if ($this->db->affected_rows() >= 0)
		{
			return TRUE;
		}
		
		return FALSE;       
    }
    
    function delete($table,$fieldID,$ID){
        $this->db->where($fieldID,$ID);
        $this->db->delete($table);
        if ($this->db->affected_rows() == '1')
		{
			return TRUE;
		}
		
		return FALSE;        
    }
	function getByIdSendC($id){

        $this->db->select('nombreP,email1P,tel1P');

        $this->db->from('proveedores');

        $this->db->where('proveedores.idProveedores',$id);

        $this->db->limit(1);

        return $this->db->get()->row();

    }
    function getByIdSendE(){

        $this->db->select("emitente.*,AES_DECRYPT(passemail,'$this->keycrypt') as passemail");

        $this->db->from('emitente');

        $this->db->limit(1);

        return $this->db->get()->row();

    }

    function count($table) {
        return $this->db->count_all($table);
    }
    
    public function getOsByProveedor($id){
        $this->db->where('proveedores_id',$id);
        $this->db->order_by('idOs','desc');
        //$this->db->limit(10); // se ha comentado por que esta tabla no tiene paginador y si el cliente tiene mas de 10 os, no se podrian ver.
        return $this->db->get('os')->result();
    }

}