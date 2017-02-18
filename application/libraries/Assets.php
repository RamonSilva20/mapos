<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Assets
{
	private $js = '';


	public function js($arquivo)
	{
		$this->js .= sprintf('<script type="text/javascript" src="%s"></script>', $arquivo);
	}
	public function allJs(){
		return $this->js;
	}
}