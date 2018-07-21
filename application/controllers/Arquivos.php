<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

/**
 * author: Ramon Silva
 * email: silva018-mg@yahoo.com.br
 *
 */

class Arquivos extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        if ((!session_id()) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');
        }
        
        $config['upload_path']          = FCPATH.'assets/arquivos/documentos/'.date('m-Y').'/';
        $config['allowed_types']        = $this->config->item('allowed_types');
        $config['max_size']             = $this->config->item('max_size');
        $config['encrypt_name']         = true;
        $config['file_ext_tolower']     = true;
        
        $this->load->library('upload', $config);
        $this->load->model('Arquivos_model');
        $this->load->library('form_validation');
        $this->load->language('upload');
        
    }

    public function index()
    {
        if (!$this->permission->check($this->session->userdata('permissao'), 'vArquivo')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_view').' '.$this->lang->line('files'));
            redirect(base_url());
        }

        $data['view'] = 'arquivos/arquivos_list';
        $this->load->view('tema/topo', $data, false);
    }

    public function datatable()
    {

        if (!$this->permission->check($this->session->userdata('permissao'), 'vArquivo')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_view').' '.$this->lang->line('files'));
            redirect(base_url());
        }

        $this->load->model('Arquivos_model');
        $result_data = $this->Arquivos_model->get_datatables();
        $data = array();

        foreach ($result_data as $row) {
            $line = array();

            $line[] = $row->idDocumentos;
            $line[] = $row->documento;
            $line[] = date($this->config->item('date_format') ,strtotime($row->cadastro));
            $line[] = $row->tipo;
            $line[] = $row->tamanho;

            $line[] = '<a href="' . site_url('arquivos/read/' . $row->idDocumentos) . '" class="btn btn-dark" title="' . $this->lang->line('app_view') . '"><i class="fa fa-eye"></i> </a>
                       <a href="' . site_url('arquivos/update/' . $row->idDocumentos) . '" class="btn btn-info" title="' . $this->lang->line('app_edit') . '"><i class="fa fa-edit"></i></a>
                       <a href="' . site_url('arquivos/download/' . $row->idDocumentos) . '" class="btn btn-primary" title="' . $this->lang->line('file_download') . '"><i class="fa fa-download"></i></a>
                       <a href="' . site_url('arquivos/delete/' . $row->idDocumentos) . '" class="btn btn-danger delete" title="' . $this->lang->line('app_delete') . '"><i class="fa fa-remove"></i></a>';
            $data[] = $line;
        }

        $output = array(
            'draw' => intval($this->input->post('draw')),
            'recordsTotal' => $this->Arquivos_model->get_all_data(),
            'recordsFiltered' => $this->Arquivos_model->get_filtered_data(),
            'data' => $data,
        );
        echo json_encode($output);
    }

    public function read($id)
    {
        if (!is_numeric($id)) {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('arquivos');
        }

        if (!$this->permission->check($this->session->userdata('permissao'), 'vArquivo')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_view').' '.$this->lang->line('files'));
            redirect(base_url());
        }

        $row = $this->Arquivos_model->get($id);
        if ($row) {
            $data = array(
                'idDocumentos' => $row->idDocumentos,
                'documento' => $row->documento,
                'descricao' => $row->descricao,
                'file' => $row->file,
                'path' => $row->path,
                'url' => $row->url,
                'cadastro' => $row->cadastro,
                'categoria' => $row->categoria,
                'tipo' => $row->tipo,
                'tamanho' => $row->tamanho,
            );

            $data['view'] = 'arquivos/arquivos_read';
            $this->load->view('tema/topo', $data, false);
        } else {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('arquivos'));
        }
    }

    public function create()
    {
        if (!$this->permission->check($this->session->userdata('permissao'), 'aArquivo')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_add').' '.$this->lang->line('files'));
            redirect(base_url());
        }

        $data = array(
            'button' => '<i class="fa fa-plus"></i> ' . $this->lang->line('app_create'),
            'action' => site_url('arquivos/create_action'),
            'idDocumentos' => set_value('idDocumentos'),
            'documento' => set_value('documento'),
            'descricao' => set_value('descricao'),
            'file' => set_value('file'),
            'path' => set_value('path'),
            'url' => set_value('url'),
            'cadastro' => date('Y-m-d'),
            'categoria' => set_value('categoria'),
            'tipo' => set_value('tipo'),
            'tamanho' => set_value('tamanho'),
        );

        $data['view'] = 'arquivos/arquivos_form';
        $this->load->view('tema/topo', $data, false);

    }

    public function create_action()
    {
        if (!$this->permission->check($this->session->userdata('permissao'), 'aArquivo')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_add').' '.$this->lang->line('files'));
            redirect(base_url());
        }

        $this->_rules();

        if ($this->form_validation->run() == false) {
            $this->create();
        } else {

            // create folder if no exists
            if (!is_dir(FCPATH.'assets/arquivos/documentos/'.date('m-Y').'/')) {

                if (!@mkdir(FCPATH.'assets/arquivos/documentos/'.date('m-Y').'/', 0775, true)) {
                    $this->session->set_flashdata('error', $this->lang->line('upload_not_writable'));
                    redirect(site_url('arquivos'));
                }
            }

            // try to upload the file
            if (!$this->upload->do_upload('file'))
            {
                $this->create();
            }
            else
            {   

                $file = $this->upload->data();
                
                $data = array(
                    'documento' => $this->input->post('documento', true),
                    'descricao' => $this->input->post('descricao', true),
                    'cadastro' => date_to_sql($this->input->post('cadastro', true), $this->config->item('date_format')),
                    'file' => $file['file_name'],
                    'path' => $file['full_path'],
                    'url' => base_url('assets/arquivos/documentos/'.date('m-Y').'/'.$file['file_name']),
                    'tipo' => $file['file_ext'],
                    'tamanho' => $file['file_size'],
                );

                if($this->Arquivos_model->insert($data)){
                    $this->session->set_flashdata('success', $this->lang->line('app_add_message'));
                    redirect(site_url('arquivos'));
                }else{
                    $this->session->set_flashdata('error', $this->lang->line('app_error'));
                    redirect(site_url('arquivos'));
                }
            }

        }
    }

    public function update($id)
    {
        if (!is_numeric($id)) {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('arquivos');
        }

        if (!$this->permission->check($this->session->userdata('permissao'), 'eArquivo')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_edit').' '.$this->lang->line('files'));
            redirect(base_url());
        }

        $row = $this->Arquivos_model->get($id);

        if ($row) {
            $data = array(
                'button' => '<i class="fa fa-edit"></i> ' . $this->lang->line('app_edit'),
                'action' => site_url('arquivos/update_action'),
                'idDocumentos' => set_value('idDocumentos', $row->idDocumentos),
                'documento' => set_value('documento', $row->documento),
                'descricao' => set_value('descricao', $row->descricao),
                'cadastro' => set_value('cadastro', $row->cadastro),
            );
            $data['view'] = 'arquivos/arquivos_form';
            $this->load->view('tema/topo', $data, false);

        } else {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('arquivos'));
        }
    }

    public function update_action()
    {
        if (!$this->permission->check($this->session->userdata('permissao'), 'eArquivo')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_edit').' '. $this->lang->line('files'));
            redirect(base_url());
        }

        $this->_rules();

        if ($this->form_validation->run() == false) {
            $this->update($this->input->post('idDocumentos', true));
        } else {
            $data = array(
                'documento' => $this->input->post('documento', true),
                'descricao' => $this->input->post('descricao', true),
                'cadastro' => date_to_sql($this->input->post('cadastro', true), $this->config->item('date_format')),
            );

            $this->Arquivos_model->update($data, $this->input->post('idDocumentos', true));
            $this->session->set_flashdata('success', $this->lang->line('app_edit_message'));
            redirect(site_url('arquivos'));
        }
    }

    public function download($id){
    	
        if(!is_numeric($id)){
            $this->session->set_flashdata('error',$this->lang->line('app_not_found'));
            redirect(base_url() . 'index.php/arquivos/');
        }

        if (!$this->permission->check($this->session->userdata('permissao'), 'vArquivo')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_view').' '.$this->lang->line('files'));
            redirect(base_url());
        }
        
        $file = $this->Arquivos_model->get($id);

        if($file){

            $this->load->library('zip');
            $path = $file->path;
            $this->zip->read_file($path); 
            $this->zip->download('file'.date('d-m-Y-H.i.s').'.zip');

        }else{
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('arquivos'));
        }
        
    }

    public function delete($idDocumentos)
    {
        if (!is_numeric($idDocumentos)) {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('arquivos');
        }

        if (!$this->permission->check($this->session->userdata('permissao'), 'dArquivo')) {
            $this->session->set_flashdata('error', $this->lang->line('app_permission_delete').' '.$this->lang->line('files'));
            redirect(base_url());
        }

        $row = $this->Arquivos_model->get($idDocumentos);
        $ajax = $this->input->get('ajax');

        if ($row) {
            if ($this->Arquivos_model->delete($idDocumentos)) {

                // remove file from the disk
                @unlink($row->path);

                if ($ajax) {
                    echo json_encode(array('result' => true, 'message' => $this->lang->line('app_delete_message')));die();
                }
                $this->session->set_flashdata('success', $this->lang->line('app_delete_message'));
                redirect(site_url('arquivos'));

            } else {

                if ($ajax) {
                    echo json_encode(array('result' => false, 'message' => $this->lang->line('app_error')));die();
                }

                $this->session->set_flashdata('error', $this->lang->line('app_error'));
                redirect(site_url('arquivos'));
            }

        } else {

            if ($ajax) {
                echo json_encode(array('result' => false, 'message' => $this->lang->line('app_not_found')));die();
            }
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('arquivos'));
        }

    }

    public function _rules()
    {
        $this->form_validation->set_rules('documento', '<b>' . $this->lang->line('file_name') . '</b>', 'trim|required');
        $this->form_validation->set_rules('descricao', '<b>' . $this->lang->line('file_description') . '</b>', 'trim|required');
        $this->form_validation->set_rules('file', '<b>' . $this->lang->line('file') . '</b>', 'trim');
        $this->form_validation->set_rules('cadastro', '<b>' . $this->lang->line('file_date') . '</b>', 'trim|required|exact_length[10]');

        $this->form_validation->set_rules('idDocumentos', 'idDocumentos', 'trim');
        $this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
    }

}

/* End of file Arquivos.php */
/* Location: ./application/controllers/Arquivos.php */
