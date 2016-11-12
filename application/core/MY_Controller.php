<?php
if ( ! defined('BASEPATH')) exit('Sem acesso direto ao script');


class MY_Controller extends CI_Controller{

  public $data = array();

  public function __CONSTRUCT(){
    parent::__CONSTRUCT();
  }
}
class MY_Acesso extends MY_Controller{

  public $data = array();

  public function __CONSTRUCT(){

    parent::__CONSTRUCT();
	  if((!$this->session->session_id) OR (!$this->session->logado)){
      	redirect('mapos/login');
    }
  }
}
