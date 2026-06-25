<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Modulos_model extends CI_Model
{
    public function getAll()
    {
        return $this->db->order_by('nome', 'ASC')->get('modulos')->result();
    }

    public function getBySlug(string $slug)
    {
        return $this->db->where('slug', $slug)->get('modulos')->row();
    }

    public function getMenuItems(string $slug)
    {
        return $this->db->where('modulo_slug', $slug)->get('modulo_menu_items')->result();
    }
}
