<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

/**
 * author: Ramon Silva
 * email: silva018-mg@yahoo.com.br
 *
 */

class Clientes extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        if ((!session_id()) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');
        }

        $this->load->model('Clientes_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        if (!$this->permission->check($this->session->userdata('permissao'), 'vCliente')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_view') . ' ' . $this->lang->line('clientes'));
            redirect(base_url());
        }

        $data['view'] = 'clientes/clientes_list';
        $this->load->view('tema/topo', $data, false);
    }

    public function datatable()
    {

        if (!$this->permission->check($this->session->userdata('permissao'), 'vCliente')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_view') . ' ' . $this->lang->line('clientes'));
            redirect(base_url());
        }

        $this->load->model('Clientes_model');
        $result_data = $this->Clientes_model->get_datatables();
        $data = array();

        foreach ($result_data as $row) {
            $line = array();
            $line[] = '<input type="checkbox" class="remove" name="item_id[]" value="'.$row->idClientes.'">';

            $line[] = $row->idClientes;
            $line[] = $row->nomeCliente;
            $line[] = $row->documento;
            $line[] = $row->celular;
            $line[] = date('d/m/Y', strtotime($row->dataCadastro));

            $line[] = '<a href="' . site_url('clientes/read/' . $row->idClientes) . '" class="btn btn-dark" title="' . $this->lang->line('app_view') . '"><i class="fa fa-eye"></i> </a>
                       <a href="' . site_url('clientes/update/' . $row->idClientes) . '" class="btn btn-info" title="' . $this->lang->line('app_edit') . '"><i class="fa fa-edit"></i></a>
                       <a href="' . site_url('clientes/delete/' . $row->idClientes) . '" class="btn btn-danger delete" title="' . $this->lang->line('app_delete') . '"><i class="fa fa-remove"></i></a>';
            $data[] = $line;
        }

        $output = array(
            'draw' => intval($this->input->post('draw')),
            'recordsTotal' => $this->Clientes_model->get_all_data(),
            'recordsFiltered' => $this->Clientes_model->get_filtered_data(),
            'data' => $data,
        );
        echo json_encode($output);
    }

    public function read($id)
    {
        if (!is_numeric($id)) {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('clientes');
        }

        if (!$this->permission->check($this->session->userdata('permissao'), 'vCliente')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_view') . ' ' . $this->lang->line('clientes'));
            redirect(base_url());
        }

        $row = $this->Clientes_model->get($id);
        if ($row) {
            $data = array(
                'idClientes' => $row->idClientes,
                'nomeCliente' => $row->nomeCliente,
                'sexo' => $row->sexo,
                'pessoa_fisica' => $row->pessoa_fisica,
                'documento' => $row->documento,
                'telefone' => $row->telefone,
                'celular' => $row->celular,
                'email' => $row->email,
                'dataCadastro' => $row->dataCadastro,
                'rua' => $row->rua,
                'numero' => $row->numero,
                'bairro' => $row->bairro,
                'cidade' => $row->cidade,
                'estado' => $row->estado,
                'cep' => $row->cep,
                'obs' => $row->obs,
            );

            $data['view'] = 'clientes/clientes_read';
            $this->load->view('tema/topo', $data, false);
        } else {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('clientes'));
        }
    }

    public function create()
    {
        if (!$this->permission->check($this->session->userdata('permissao'), 'aCliente')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_add') . ' ' . $this->lang->line('clientes'));
            redirect(base_url());
        }

        $data = array(
            'button' => '<i class="fa fa-plus"></i> ' . $this->lang->line('app_create'),
            'action' => site_url('clientes/create_action'),
            'idClientes' => set_value('idClientes'),
            'nomeCliente' => set_value('nomeCliente'),
            'sexo' => set_value('sexo'),
            'pessoa_fisica' => set_value('pessoa_fisica'),
            'documento' => set_value('documento'),
            'telefone' => set_value('telefone'),
            'celular' => set_value('celular'),
            'email' => set_value('email'),
            'rua' => set_value('rua'),
            'numero' => set_value('numero'),
            'bairro' => set_value('bairro'),
            'cidade' => set_value('cidade'),
            'estado' => set_value('estado'),
            'cep' => set_value('cep'),
            'obs' => set_value('obs'),
        );

        $data['view'] = 'clientes/clientes_form';
        $this->load->view('tema/topo', $data, false);

    }

    public function create_action()
    {
        if (!$this->permission->check($this->session->userdata('permissao'), 'aCliente')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_add') . ' ' . $this->lang->line('clientes'));
            redirect(base_url());
        }

        $this->_rules();

        if ($this->form_validation->run() == false) {
            $this->create();
        } else {
            $data = array(
                'nomeCliente' => $this->input->post('nomeCliente', true),
                // 'sexo' => $this->input->post('sexo', true),
                'sexo' => '',
                // 'pessoa_fisica' => $this->input->post('pessoa_fisica', true),
                'pessoa_fisica' => 1,
                'documento' => $this->input->post('documento', true),
                'telefone' => $this->input->post('telefone', true),
                'celular' => $this->input->post('celular', true),
                'email' => $this->input->post('email', true),
                'dataCadastro' => date('Y-m-d'),
                'rua' => $this->input->post('rua', true),
                'numero' => $this->input->post('numero', true),
                'bairro' => $this->input->post('bairro', true),
                'cidade' => $this->input->post('cidade', true),
                'estado' => $this->input->post('estado', true),
                'cep' => $this->input->post('cep', true),
                'obs' => $this->input->post('obs', true),
            );

            $this->Clientes_model->insert($data);
            $this->session->set_flashdata('success', $this->lang->line('app_add_message'));
            redirect(site_url('clientes'));
        }
    }

    public function update($id)
    {
        if (!is_numeric($id)) {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('clientes');
        }

        if (!$this->permission->check($this->session->userdata('permissao'), 'eCliente')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_edit') . ' ' . $this->lang->line('clientes'));
            redirect(base_url());
        }

        $row = $this->Clientes_model->get($id);

        if ($row) {
            $data = array(
                'button' => '<i class="fa fa-edit"></i> ' . $this->lang->line('app_edit'),
                'action' => site_url('clientes/update_action'),
                'idClientes' => set_value('idClientes', $row->idClientes),
                'nomeCliente' => set_value('nomeCliente', $row->nomeCliente),
                'sexo' => set_value('sexo', $row->sexo),
                'pessoa_fisica' => set_value('pessoa_fisica', $row->pessoa_fisica),
                'documento' => set_value('documento', $row->documento),
                'telefone' => set_value('telefone', $row->telefone),
                'celular' => set_value('celular', $row->celular),
                'email' => set_value('email', $row->email),
                'rua' => set_value('rua', $row->rua),
                'numero' => set_value('numero', $row->numero),
                'bairro' => set_value('bairro', $row->bairro),
                'cidade' => set_value('cidade', $row->cidade),
                'estado' => set_value('estado', $row->estado),
                'cep' => set_value('cep', $row->cep),
                'obs' => set_value('obs', $row->obs),
            );
            $data['view'] = 'clientes/clientes_form';
            $this->load->view('tema/topo', $data, false);

        } else {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('clientes'));
        }
    }

    public function update_action()
    {
        if (!$this->permission->check($this->session->userdata('permissao'), 'eCliente')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_edit') . ' ' . $this->lang->line('clientes'));
            redirect(base_url());
        }

        $this->_rules();

        if ($this->form_validation->run() == false) {
            $this->update($this->input->post('idClientes', true));
        } else {
            $data = array(
                'nomeCliente' => $this->input->post('nomeCliente', true),
                // 'sexo' => $this->input->post('sexo', true),
                // 'pessoa_fisica' => $this->input->post('pessoa_fisica', true),
                'documento' => $this->input->post('documento', true),
                'telefone' => $this->input->post('telefone', true),
                'celular' => $this->input->post('celular', true),
                'email' => $this->input->post('email', true),
                'rua' => $this->input->post('rua', true),
                'numero' => $this->input->post('numero', true),
                'bairro' => $this->input->post('bairro', true),
                'cidade' => $this->input->post('cidade', true),
                'estado' => $this->input->post('estado', true),
                'cep' => $this->input->post('cep', true),
                'obs' => $this->input->post('obs', true),
            );

            $this->Clientes_model->update($data, $this->input->post('idClientes', true));
            $this->session->set_flashdata('success', $this->lang->line('app_edit_message'));
            redirect(site_url('clientes'));
        }
    }

    public function delete($idClientes)
    {
        if (!is_numeric($idClientes)) {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('clientes');
        }

        if (!$this->permission->check($this->session->userdata('permissao'), 'dCliente')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_delete') . ' ' . $this->lang->line('clientes'));
            redirect(base_url());
        }

        $row = $this->Clientes_model->get($idClientes);
        $ajax = $this->input->get('ajax');
        

        if ($row) {

            $this->Clientes_model->delete_linked($idClientes);

            if ($this->Clientes_model->delete($idClientes)) {

                if ($ajax) {
                    echo json_encode(array('result' => true, 'message' => $this->lang->line('app_delete_message')));die();
                }
                $this->session->set_flashdata('success', $this->lang->line('app_delete_message'));
                redirect(site_url('clientes'));
            } else {

                if ($ajax) {
                    echo json_encode(array('result' => false, 'message' => $this->lang->line('app_error')));die();
                }

                $this->session->set_flashdata('error', $this->lang->line('app_error'));
                redirect(site_url('clientes'));
            }

        } else {

            if ($ajax) {
                echo json_encode(array('result' => false, 'message' => $this->lang->line('app_not_found')));die();
            }
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('clientes'));
        }

    }

    public function delete_many()
    {

        if (!$this->permission->check($this->session->userdata('permissao'), 'dCliente')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_delete') . ' ' . $this->lang->line('clientes'));
            redirect(base_url());
        }

        $items = $this->input->post('item_id[]');

        if ($items) {

            $verify = implode('', $items);
            if (is_numeric($verify)) {

                $this->Clientes_model->delete_linked($items);

                $result = $this->Clientes_model->delete_many($items);
                if ($result) {
                    echo json_encode(array('result' => true, 'message' => $this->lang->line('app_delete_message_many')));die();
                } else {
                    echo json_encode(array('result' => false, 'message' => $this->lang->line('app_error')));die();
                }

            } else {
                echo json_encode(array('result' => false, 'message' => $this->lang->line('app_data_not_supported')));die();
            }
        }

        echo json_encode(array('result' => false, 'message' => $this->lang->line('app_empty_data')));die();

    }

    public function _rules()
    {
        $this->form_validation->set_rules('nomeCliente', '<b>' . $this->lang->line('client_name') . '</b>', 'trim|required');
        $this->form_validation->set_rules('sexo', '<b>' . $this->lang->line('client_sex') . '</b>', 'trim');
        $this->form_validation->set_rules('pessoa_fisica', '<b>' . $this->lang->line('client_type') . '</b>', 'trim');
        $this->form_validation->set_rules('documento', '<b>' . $this->lang->line('client_doc') . '</b>', 'trim|required');
        $this->form_validation->set_rules('telefone', '<b>' . $this->lang->line('client_phone') . '</b>', 'trim|required');
        $this->form_validation->set_rules('celular', '<b>' . $this->lang->line('client_cel') . '</b>', 'trim|required');
        $this->form_validation->set_rules('email', '<b>' . $this->lang->line('client_mail') . '</b>', 'trim|required|valid_email');
        $this->form_validation->set_rules('dataCadastro', '<b>' . $this->lang->line('client_created') . '</b>', 'trim');
        $this->form_validation->set_rules('rua', '<b>' . $this->lang->line('client_street') . '</b>', 'trim|required');
        $this->form_validation->set_rules('numero', '<b>' . $this->lang->line('client_number') . '</b>', 'trim|required');
        $this->form_validation->set_rules('bairro', '<b>' . $this->lang->line('client_district') . '</b>', 'trim|required');
        $this->form_validation->set_rules('cidade', '<b>' . $this->lang->line('client_city') . '</b>', 'trim|required');
        $this->form_validation->set_rules('estado', '<b>' . $this->lang->line('client_state') . '</b>', 'trim|required');
        $this->form_validation->set_rules('cep', '<b>' . $this->lang->line('client_zip') . '</b>', 'trim|required');
        $this->form_validation->set_rules('obs', '<b>' . $this->lang->line('client_obs') . '</b>', 'trim');

        $this->form_validation->set_rules('idClientes', 'idClientes', 'trim');
        $this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
    }

}

/* End of file Clientes.php */
/* Location: ./application/controllers/Clientes.php */
