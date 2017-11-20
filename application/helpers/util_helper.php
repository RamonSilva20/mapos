<?php 


// print bootstrap label - options: success, primary, info, warning, danger, default
/**
 * @param  [string]
 * @param  [strin]
 * @return [string]
 */
function print_label($text, $color)
{

	$label = '';
	switch ($color) 
	{
		case 'success':
			$label = '<span class="label label-success">'.$text.'</span>';
			break;
		case 'primary':
			$label = '<span class="label label-primary">'.$text.'</span>';
			break;
		case 'info':
			$label = '<span class="label label-info">'.$text.'</span>';
			break;
		case 'warning':
			$label = '<span class="label label-warning">'.$text.'</span>';
			break;
		case 'danger':
			$label = '<span class="label label-danger">'.$text.'</span>';
			break;		
		default:
			$label = '<span class="label label-default">'.$text.'</span>';
			break;
	}

	return $label;
}


// breadcrumb value
$breadcrumb = '';

// set breadcrumb
/**
 * @param  string
 * @param  string
 * @param  string
 * @param  string
 */
function set_breadcrumb($text, $link, $text2 = null, $link2 = null)
{
	$ci =& get_instance();

	global $breadcrumb;
	$breadcrumb = '<li><a href="'.base_url().'"> '.$ci->lang->line('app_dashboard').'</a></li> <li><a href="'.$link.'">'.$text.'</a></li>';

	if($text2 && $link2){
		$breadcrumb .= '<li><a href="'.$link2.'">'.$text2.'</a></li>';
	}
}


// get breadcrumb
/**
 * @return string
 */
function get_breadcrumb()
{
	$ci =& get_instance();

	global $breadcrumb;
	if(!$breadcrumb){
		$breadcrumb = '<li><a href="'.base_url().'"> '.$ci->lang->line('app_dashboard').'</a></li>';
	}
	return $breadcrumb;
}