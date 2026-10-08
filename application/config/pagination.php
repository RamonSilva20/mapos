<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Paginação das telas legadas (CI_Pagination)
| -------------------------------------------------------------------------
|
| O CodeIgniter carrega este arquivo sozinho quando um controller faz
| $this->load->library('pagination') sem parâmetros, e usa os valores como
| padrão do CI_Pagination. O initialize() de cada controller continua
| informando base_url, total_rows e per_page.
|
| É o markup do Bootstrap 2 que ficava em MY_Controller::$data['configuration'].
| Ele foi movido para cá sem mudar nada, para que as telas ainda não migradas
| continuem iguais. Sai junto com o resto do frontend legado (#2855).
|
| Telas novas não usam o create_links(): usam o componente pagination, com
| as props montadas por MY_Controller::paginacao() (ver
| application/helpers/componente_helper.php, paginacaoProps()).
*/

$config['next_link'] = 'Próxima';
$config['prev_link'] = 'Anterior';
$config['first_link'] = 'Primeira';
$config['last_link'] = 'Última';

$config['full_tag_open'] = '<div class="pagination alternate"><ul>';
$config['full_tag_close'] = '</ul></div>';
$config['num_tag_open'] = '<li>';
$config['num_tag_close'] = '</li>';
$config['cur_tag_open'] = '<li><a style="color: #2D335B"><b>';
$config['cur_tag_close'] = '</b></a></li>';
$config['prev_tag_open'] = '<li>';
$config['prev_tag_close'] = '</li>';
$config['next_tag_open'] = '<li>';
$config['next_tag_close'] = '</li>';
$config['first_tag_open'] = '<li>';
$config['first_tag_close'] = '</li>';
$config['last_tag_open'] = '<li>';
$config['last_tag_close'] = '</li>';
