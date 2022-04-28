<?php

function dateInterval($startDate, $finalDate)
{
    $data = date('d/m/Y', strtotime($startDate));

    // Criar o objeto representando a data
    $obj_data = DateTime::createFromFormat('d/m/Y', $data);
    $obj_data->setTime(0, 0, 0);

    // Realizar a soma de dias
    $intervalo = new DateInterval('P' . intval($finalDate) . 'D');
    $obj_data->add($intervalo);

    // Formatar a data obtida
    return $obj_data->format('d/m/Y');
}
