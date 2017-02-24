<?php

if (!defined('BASEPATH')) {    exit('No direct script access allowed'); }

class Configuracoes extends MY_Acesso{

	public function __construct() 
	{
    parent::__construct();
	}
	public function index()
	{		
			$arquivo = APPPATH.'config/form_validation.php';
			require($arquivo);
      $this->config->load('form_validation');
			$validate_rules = $this->config->item('clientes');
      $rules_js = array();
      foreach ($validate_rules as $key => $rule) {
        if (strpos($rule['rules'], 'required') !==   false) {
          $rules_js[$rule['field']] = 'checked';
        }else{
          $rules_js[$rule['field']] = '';
        }
      }
      $this->data['validate_rules'] = $rules_js;

      if(!empty($_POST)){

	      foreach ($validate_rules as $key => $value) {
	      	if ($this->input->post($value['field']) !== NULL) {
	      		$required = 'required';
	      	}else{
	      		$required = '';
	      	}
	      	if($value['field'] === 'email' ) {
	      		$required .= '|valid_email';
	      	}
	      	$new_config[] = [
	         	'field'=> $value['field'],
	        	'label'=> $value['label'],
	        	'rules'=> $required
	        	];
	        
	      }
	      $config['clientes'] = $new_config;
	    	// $resultado = $this->config->set_item('clientes', $new_config );
				$configuracao = '<?php $config = '.var_export($config, true).';';
				$resultado = file_put_contents($arquivo, $configuracao);
				redirect('configuracoes');
	      // echo($resultado);
	   //    $myfile = fopen($arquivo, "w") or die("Unable to open file!");
				
				// fwrite($myfile, $config);
				// fclose($myfile);
   	 }

    $this->data['view'] = 'configuracoes/configs_alterar';
    $this->load->view('tema/topo', $this->data);


	}
}
