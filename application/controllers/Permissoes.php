<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

/**
 * author: Ramon Silva
 * email: silva018-mg@yahoo.com.br
 *
 */

class Permissoes extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        if ((!session_id()) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');
        }

        $this->load->model('Permissoes_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        if (!$this->permission->check($this->session->userdata('permissao'), 'cPermissao')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_view') . ' ' . $this->lang->line('permissoes'));
            redirect(base_url());
        }

        $data['view'] = 'permissoes/permissoes_list';
        $this->load->view('tema/topo', $data, false);
    }

    public function datatable()
    {

        if (!$this->permission->check($this->session->userdata('permissao'), 'cPermissao')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_view') . ' ' . $this->lang->line('permissoes'));
            redirect(base_url());
        }

        $this->load->model('Permissoes_model');
        $result_data = $this->Permissoes_model->get_datatables();
        $data = array();

        foreach ($result_data as $row) {
            $line = array();

            $line[] = $row->idPermissao;
            $line[] = $row->nome;
            $line[] = $row->situacao ? $this->lang->line('app_active') : $this->lang->line('app_inactive');
            $line[] = date('d/m/Y', strtotime($row->data));

            $color = $row->situacao ? 'btn-danger' : 'btn-success';
            $icon = $row->situacao ? 'fa fa-remove' : 'fa fa-check';
            $title = $row->situacao ? $this->lang->line('app_disable') : $this->lang->line('app_activate');

            $line[] = '<a href="' . site_url('permissoes/update/' . $row->idPermissao) . '" class="btn btn-info" title="' . $this->lang->line('app_edit') . '"><i class="fa fa-edit"></i></a>
                       <a href="' . site_url('permissoes/status/' . $row->idPermissao) . '" class="btn '.$color.' delete" title="' . $title . '"><i class="'.$icon.'"></i></a>';
            $data[] = $line;
        }

        $output = array(
            'draw' => intval($this->input->post('draw')),
            'recordsTotal' => $this->Permissoes_model->get_all_data(),
            'recordsFiltered' => $this->Permissoes_model->get_filtered_data(),
            'data' => $data,
        );
        echo json_encode($output);
    }


    public function create()
    {
        if (!$this->permission->check($this->session->userdata('permissao'), 'cPermissao')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_add') . ' ' . $this->lang->line('permissoes'));
            redirect(base_url());
        }

        $data = array(
            'button' => '<i class="fa fa-plus"></i> ' . $this->lang->line('app_create'),
            'action' => site_url('permissoes/create_action'),
            'idPermissao' => set_value('idPermissao'),
            'nome' => set_value('nome'),
            'permissoes' => set_value('permissoes'),
            'situacao' => set_value('situacao'),
            'data' => set_value('data'),
        );

        $data['view'] = 'permissoes/permissoes_form';
        $this->load->view('tema/topo', $data, false);

    }

    public function create_action()
    {
        if (!$this->permission->check($this->session->userdata('permissao'), 'cPermissao')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_add') . ' ' . $this->lang->line('permissoes'));
            redirect(base_url());
        }

        $this->_rules();

        if ($this->form_validation->run() == false) {
            $this->create();
        } else {

            $permissoes = array(
                'aCliente' => $this->input->post('aCliente'),
                'eCliente' => $this->input->post('eCliente'),
                'dCliente' => $this->input->post('dCliente'),
                'vCliente' => $this->input->post('vCliente'),
                'aProduto' => $this->input->post('aProduto'),
                'eProduto' => $this->input->post('eProduto'),
                'dProduto' => $this->input->post('dProduto'),
                'vProduto' => $this->input->post('vProduto'),
                'aServico' => $this->input->post('aServico'),
                'eServico' => $this->input->post('eServico'),
                'dServico' => $this->input->post('dServico'),
                'vServico' => $this->input->post('vServico'),
                'aOs' => $this->input->post('aOs'),
                'eOs' => $this->input->post('eOs'),
                'dOs' => $this->input->post('dOs'),
                'vOs' => $this->input->post('vOs'),
                'aVenda' => $this->input->post('aVenda'),
                'eVenda' => $this->input->post('eVenda'),
                'dVenda' => $this->input->post('dVenda'),
                'vVenda' => $this->input->post('vVenda'),
                'aArquivo' => $this->input->post('aArquivo'),
                'eArquivo' => $this->input->post('eArquivo'),
                'dArquivo' => $this->input->post('dArquivo'),
                'vArquivo' => $this->input->post('vArquivo'),
                'aLancamento' => $this->input->post('aLancamento'),
                'eLancamento' => $this->input->post('eLancamento'),
                'dLancamento' => $this->input->post('dLancamento'),
                'vLancamento' => $this->input->post('vLancamento'),
                'cUsuario' => $this->input->post('cUsuario'),
                'cEmitente' => $this->input->post('cEmitente'),
                'cPermissao' => $this->input->post('cPermissao'),
                'cBackup' => $this->input->post('cBackup'),
                'rCliente' => $this->input->post('rCliente'),
                'rProduto' => $this->input->post('rProduto'),
                'rServico' => $this->input->post('rServico'),
                'rOs' => $this->input->post('rOs'),
                'rVenda' => $this->input->post('rVenda'),
                'rFinanceiro' => $this->input->post('rFinanceiro'),
            );

            $permissoes = serialize($permissoes);

            $data = array(
                'nome' => $this->input->post('nome', true),
                'permissoes' => $permissoes,
                'situacao' => $this->input->post('situacao', true),
                'data' => $this->input->post('data', true),
            );

            $this->Permissoes_model->insert($data);
            $this->session->set_flashdata('success', $this->lang->line('app_add_message'));
            redirect(site_url('permissoes'));
        }
    }

    public function update($id)
    {
        if (!is_numeric($id)) {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('permissoes');
        }

        if (!$this->permission->check($this->session->userdata('permissao'), 'cPermissao')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_edit') . ' ' . $this->lang->line('permissoes'));
            redirect(base_url());
        }

        $row = $this->Permissoes_model->get($id);

        if ($row) {
            $data = array(
                'button' => '<i class="fa fa-edit"></i> ' . $this->lang->line('app_edit'),
                'action' => site_url('permissoes/update_action'),
                'idPermissao' => set_value('idPermissao', $row->idPermissao),
                'nome' => set_value('nome', $row->nome),
                'permissoes' => set_value('permissoes', unserialize($row->permissoes)),
                'situacao' => set_value('situacao', $row->situacao),
                'data' => date('Y-m-d'),
            );
            $data['view'] = 'permissoes/permissoes_form';
            $this->load->view('tema/topo', $data, false);

        } else {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('permissoes'));
        }
    }

    public function update_action()
    {
        if (!$this->permission->check($this->session->userdata('permissao'), 'cPermissao')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_edit') . ' ' . $this->lang->line('permissoes'));
            redirect(base_url());
        }

        $this->_rules();

        if ($this->form_validation->run() == false) {
            $this->update($this->input->post('idPermissao', true));
        } else {

            $permissoes = array(
                'aCliente' => $this->input->post('aCliente'),
                'eCliente' => $this->input->post('eCliente'),
                'dCliente' => $this->input->post('dCliente'),
                'vCliente' => $this->input->post('vCliente'),
                'aProduto' => $this->input->post('aProduto'),
                'eProduto' => $this->input->post('eProduto'),
                'dProduto' => $this->input->post('dProduto'),
                'vProduto' => $this->input->post('vProduto'),
                'aServico' => $this->input->post('aServico'),
                'eServico' => $this->input->post('eServico'),
                'dServico' => $this->input->post('dServico'),
                'vServico' => $this->input->post('vServico'),
                'aOs' => $this->input->post('aOs'),
                'eOs' => $this->input->post('eOs'),
                'dOs' => $this->input->post('dOs'),
                'vOs' => $this->input->post('vOs'),
                'aVenda' => $this->input->post('aVenda'),
                'eVenda' => $this->input->post('eVenda'),
                'dVenda' => $this->input->post('dVenda'),
                'vVenda' => $this->input->post('vVenda'),
                'aArquivo' => $this->input->post('aArquivo'),
                'eArquivo' => $this->input->post('eArquivo'),
                'dArquivo' => $this->input->post('dArquivo'),
                'vArquivo' => $this->input->post('vArquivo'),
                'aLancamento' => $this->input->post('aLancamento'),
                'eLancamento' => $this->input->post('eLancamento'),
                'dLancamento' => $this->input->post('dLancamento'),
                'vLancamento' => $this->input->post('vLancamento'),
                'cUsuario' => $this->input->post('cUsuario'),
                'cEmitente' => $this->input->post('cEmitente'),
                'cPermissao' => $this->input->post('cPermissao'),
                'cBackup' => $this->input->post('cBackup'),
                'rCliente' => $this->input->post('rCliente'),
                'rProduto' => $this->input->post('rProduto'),
                'rServico' => $this->input->post('rServico'),
                'rOs' => $this->input->post('rOs'),
                'rVenda' => $this->input->post('rVenda'),
                'rFinanceiro' => $this->input->post('rFinanceiro'),
            );

            $permissoes = serialize($permissoes);

            $data = array(
                'nome' => $this->input->post('nome', true),
                'permissoes' => $permissoes,
                'situacao' => $this->input->post('situacao', true),
            );

            $this->Permissoes_model->update($data, $this->input->post('idPermissao', true));
            $this->session->set_flashdata('success', $this->lang->line('app_edit_message'));
            redirect(site_url('permissoes'));
        }
    }

    public function status($idPermissao)
    {
        if (!is_numeric($idPermissao)) {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('permissoes');
        }

        if (!$this->permission->check($this->session->userdata('permissao'), 'cPermissao')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_edit') . ' ' . $this->lang->line('permissoes'));
            redirect(base_url());
        }

        $row = $this->Permissoes_model->get($idPermissao);
        $ajax = $this->input->get('ajax');

        if ($row) {
            if ($this->Permissoes_model->update(array('situacao' => !$row->situacao), $idPermissao)) {

                if ($ajax) {
                    echo json_encode(array('result' => true, 'message' => $this->lang->line('app_edit_message')));die();
                }
                $this->session->set_flashdata('success', $this->lang->line('app_edit_message'));
                redirect(site_url('permissoes'));
            } else {

                if ($ajax) {
                    echo json_encode(array('result' => false, 'message' => $this->lang->line('app_error')));die();
                }

                $this->session->set_flashdata('error', $this->lang->line('app_error'));
                redirect(site_url('permissoes'));
            }

        } else {

            if ($ajax) {
                echo json_encode(array('result' => false, 'message' => $this->lang->line('app_not_found')));die();
            }
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('permissoes'));
        }

    }


    public function _rules()
    {
        $this->form_validation->set_rules('nome', '<b>' . $this->lang->line('perm_name') . '</b>', 'trim|required');
        $this->form_validation->set_rules('situacao', '<b>' . $this->lang->line('perm_status') . '</b>', 'trim|required');
        $this->form_validation->set_rules('data', '<b>' . $this->lang->line('perm_created') . '</b>', 'trim');

        $this->form_validation->set_rules('idPermissao', 'idPermissao', 'trim');
        $this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
    }

}

/* End of file Permissoes.php */
/* Location: ./application/controllers/Permissoes.php */
