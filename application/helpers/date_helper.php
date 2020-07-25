<?php

use Carbon\Carbon;

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

function dateIntervalInHours($startDate, $finalDate, $discount = null)
{
    $startDate = Carbon::create($startDate);
    $finalDate = Carbon::create($finalDate);

    if ($discount) {
        list($hours, $minutes, $seconds) = explode(':', $discount);

        $finalDate->subHours(intval($hours));
        $finalDate->subMinutes(intval($minutes));
        $finalDate->subSeconds(intval($seconds));
    }

    $diff = $startDate->diffInSeconds($finalDate, false);

    $time = gmdate('H:i:s', abs($diff));

    return $diff < 0
        ? '-' . $time
        : $time;
}
