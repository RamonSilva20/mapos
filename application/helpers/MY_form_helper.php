<?php

function existe_value($value, $alternativa = '')
{
	if(isset($value))
	{
		$retorno = $value;
	}else
	{
		$retorno = $alternativa;
	}

	return $retorno;
}