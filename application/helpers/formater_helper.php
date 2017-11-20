<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');



function format_currency()
{
	# code...
}

// convert date dd/mm/yyyy to yyyy-mm-dd
/**
 * @param  string
 * @return string
 */
function date_to_sql($date)
{

	if (preg_match("/^(((0[1-9]|[12]\d|3[01])\/(0[13578]|1[02])\/((19|[2-9]\d)\d{2}))|((0[1-9]|[12]\d|30)\/(0[13456789]|1[012])\/((19|[2-9]\d)\d{2}))|((0[1-9]|1\d|2[0-8])\/02\/((19|[2-9]\d)\d{2}))|(29\/02\/((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))))$/", $date)) 
	{

		$date = str_replace('/', '-', $date);
		$date = date('Y-m-d', strtotime($date));
		return $date;

	}
	
	return null;
}

// Convert date yyyy-mm-dd to dd/mm/yyyy
/**
 * @param  string
 * @return string
 */
function date_from_sql($date)
{

	if (preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/",$date))
    {
    	$date = date('d/m/Y', strtotime($date));
        return $date;

    }

    return null;
    
}


// Convert datetime yyyy-mm-dd H:i:s to dd/mm/yyyy H:i:s
/**
 * @param  string
 * @return string
 */
function datetime_from_sql($date)
{

	if (preg_match("/^\d\d\d\d-(0?[1-9]|1[0-2])-(0?[1-9]|[12][0-9]|3[01]) (00|[0-9]|1[0-9]|2[0-3]):([0-9]|[0-5][0-9]):([0-9]|[0-5][0-9])$/",$date))
    {
    	$date = date('d/m/Y H:i:s', strtotime($date));
        return $date;

    }

    return null;
    
}