<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

function p($a)
{
    echo '<pre>';
    print_r($a);
    echo '</pre>';

}
function v($a)
{
    echo '<pre>';
    var_dump($a);
    echo '</pre>';

}

function clean_header($array){
    $CI = get_instance();
    $CI->load->helper('inflector');
    foreach($array as $a){
        $arr[] = humanize($a);
    }
    return $arr;
}

/*
 * Validate money 
 */
function validate_money($valor){

    if(preg_match("/^([0-9]*)\.(\d{2})$/", $valor)){
        return true;
    }
    return false;
    
}

/*
 *-----------------------------------------
 * Return format corresponding to datepicker
 *-----------------------------------------
 * Ex: d/m/Y -> dd/mm/yyyy  
 */
function date_format_datepicker($format){

    switch($format){
        case 'd/m/Y':
            return 'dd/mm/yyyy';
            break;
        case 'd-m-Y':
            return 'dd-mm-yyyy'; 
            break;
        case 'Y/m/d':
            return 'yyyy/mm/dd'; 
            break;
        case 'Y-m-d':
            return 'yyyy-mm-dd'; 
            break;
        default:
            return 'dd/mm/yyyy';
    }
}

/*
 *-------------------------------------
 * Format date to SQL format (Y-m-d)
 *------------------------------------- 
 * @param $date - string (Ex: 10/10/1990)
 * @param $format - string (Ex: d/m/Y)
 * return string - (1990-10-10)
 */
function date_to_sql($date, $format){
    if($format == 'Y-m-d' || $format == 'Y/m/d'){
        return $date;
    }
    else{

        if(preg_match('/([0-9]+)-([0-9]+)-([0-9]+)/', $date ) ){
            return preg_replace( '/([0-9]+)-([0-9]+)-([0-9]+)/', '$3-$2-$1', $date );
        }
        else {
            return preg_replace( '/([0-9]+)\/([0-9]+)\/([0-9]+)/', '$3-$2-$1', $date );
        }
    }
}