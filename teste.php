<?php



class Teste 
{

	var $mensagem = '999';

	function amensagem($m)
	{
		$this->mensagem = $m;
		return '33';
	}

	function diga(){
		echo $this->mensagem;
	}
}

$a = new Teste();

var_dump($a->amensagem('Hello word!')->diga());