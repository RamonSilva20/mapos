<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class MY_Model extends CI_Model
{
    protected $table = '';
    protected $idName = '';

	public function __construct(){
        parent::__construct();
	}
	protected function _updateDescontoTotal($desconto, $id, $opercao_matematica = '+')
    {
        $this->db->set('descontoTotal', "descontoTotal {$opercao_matematica} {$desconto}", FALSE);
        return $this->_salvaUpdateValida($id, $this->idName);
    }      
    protected function _updateValorTotal($valorTotal, $id, $opercao_matematica = '+')
    {
        $this->db->set('valorTotal', "valorTotal {$opercao_matematica} {$valorTotal}", FALSE);
        return $this->_salvaUpdateValida($id, $this->idName);
    }
    protected function _salvaUpdateValida($id, $campo_where)
    {
        $id = intval($id);
        if ($id > 0) {
            $this->db->where($campo_where, $id);
            $resultado = $this->db->update($this->table);
        }else{
            $resultado = false;
        }
        return $resultado;
    }
    public function _converteValorParaAmericano($valor)
    {
        return str_replace(',', '.', str_replace('.','', $valor));
    }

}