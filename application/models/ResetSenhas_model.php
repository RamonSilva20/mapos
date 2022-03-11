<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class ResetSenhas_model extends CI_Model
{

    /**
     * author: Wilmerson
     * email: will.phelipe@gmail.com
     *
     */

    public function getById($email)
    {
        $this->db->where('email', $email);
        $this->db->limit(1);
        return $this->db->get('resets_de_senha')->row();
    }

    public function add($table, $data, $returnId = false)
    {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            if ($returnId == true) {
                return $this->db->insert_id($table);
            }
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
}
