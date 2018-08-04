<?php

$post = array('Objetos' => 'PS083211288BR');
// iniciar CURL
$ch = curl_init();
// informar URL e outras funções ao CURL
curl_setopt($ch, CURLOPT_URL, "http://www2.correios.com.br/sistemas/rastreamento/resultado_semcontent.cfm");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($post));
// Acessar a URL e retornar a saída
$output = curl_exec($ch);
// liberar
curl_close($ch);
// Imprimir a saída
echo $output;

?>