<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
    
    /**
     * author: Ramon Silva 
     * email: silva018-mg@yahoo.com.br
     * 
     */
    
class Usuarios extends CI_Controller{
    
    public function __construct(){
        parent::__construct();

        if( (!session_id()) || (!$this->session->userdata('logado'))){
            redirect('mapos/login');
        }

        $this->load->model('Usuarios_model');
        $this->load->library('form_validation');
    }

    
    public function index(){
        if(!$this->permission->check($this->session->userdata('permissao'),'cUsuario')){
           $this->session->set_flashdata('error', $this->lang->line('app_permission_view').' '.$this->lang->line('users'));
           redirect(base_url());
        }

        $data['view'] = 'usuarios/usuarios_list';
        $this->load->view('tema/topo', $data, FALSE);
    }

    public function datatable(){

        if(!$this->permission->check($this->session->userdata('permissao'),'cUsuario')){
            $this->session->set_flashdata('error', $this->lang->line('app_permission_view').' '.$this->lang->line('users'));
            redirect(base_url());
         }

        $this->load->model('Usuarios_model');  
        $result_data = $this->Usuarios_model->get_datatables();  
        $data = array(); 

        foreach($result_data as $row){  
            $line = array(); 
            
	        $line[] = $row->idUsuarios;
	        $line[] = $row->nome;
	        $line[] = $row->celular;
	        $line[] = $row->situacao ? $this->lang->line('app_active') : $this->lang->line('app_inactive');
	        $line[] = date('d/m/Y', strtotime($row->dataCadastro));
	        $line[] = $row->permissao;
	 
            $color = $row->situacao ? 'btn-danger' : 'btn-success';
            $icon = $row->situacao ? 'fa fa-remove' : 'fa fa-check';
            $title = $row->situacao ? $this->lang->line('app_disable') : $this->lang->line('app_activate');

            $line[] = '<a href="'.site_url('usuarios/read/'.$row->idUsuarios).'" class="btn btn-dark" title="'.$this->lang->line('app_view').'"><i class="fa fa-eye"></i> </a> 
                       <a href="'.site_url('usuarios/update/'.$row->idUsuarios).'" class="btn btn-info" title="'.$this->lang->line('app_edit').'"><i class="fa fa-edit"></i></a> 
                       <a href="'.site_url('usuarios/status/' . $row->idUsuarios) . '" class="btn '.$color.' delete" title="' . $title . '"><i class="'.$icon.'"></i></a>';
            $data[] = $line;  
        }  

        $output = array(  
            'draw'                => intval($this->input->post('draw')),  
            'recordsTotal'        => $this->Usuarios_model->get_all_data(),  
            'recordsFiltered'     => $this->Usuarios_model->get_filtered_data(),  
            'data'                => $data  
        );  
        echo json_encode($output);
    }

    public function read($id){

        if(!is_numeric($id)){
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('usuarios');
        }

        if(!$this->permission->check($this->session->userdata('permissao'),'cUsuario')){
           $this->session->set_flashdata('error', $this->lang->line('app_permission_view').' '.$this->lang->line('users'));
           redirect(base_url());
        }

        $row = $this->Usuarios_model->with('permissao')->get($id);
        if ($row){
            $data = array(
		        'idUsuarios' => $row->idUsuarios,
		        'nome' => $row->nome,
		        'rg' => $row->rg,
		        'cpf' => $row->cpf,
		        'rua' => $row->rua,
		        'numero' => $row->numero,
		        'bairro' => $row->bairro,
		        'cidade' => $row->cidade,
		        'estado' => $row->estado,
		        'email' => $row->email,
		        'telefone' => $row->telefone,
		        'celular' => $row->celular,
		        'situacao' => $row->situacao,
		        'dataCadastro' => $row->dataCadastro,
		        'permissoes_id' => $row->permissoes_id,
		        'permissao' => $row->permissao->nome,
	        );

            $data['view'] = 'usuarios/usuarios_read';
            $this->load->view('tema/topo', $data, FALSE);
        } else {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('usuarios'));
        }
    }

    public function create(){
        if(!$this->permission->check($this->session->userdata('permissao'),'cUsuario')){
           $this->session->set_flashdata('error',$this->lang->line('app_permission_add').' '.$this->lang->line('users'));
           redirect(base_url());
        }

        $this->load->model('Permissoes_model');
        $permissoes = $this->Permissoes_model->where('situacao','1')->as_dropdown('nome')->get_all();

        $data = array(
            'button' => '<i class="fa fa-plus"></i> '.$this->lang->line('app_create'),
            'action' => site_url('usuarios/create_action'),
	        'idUsuarios' => set_value('idUsuarios'),
	        'nome' => set_value('nome'),
	        'rg' => set_value('rg'),
	        'cpf' => set_value('cpf'),
	        'rua' => set_value('rua'),
	        'numero' => set_value('numero'),
	        'bairro' => set_value('bairro'),
	        'cidade' => set_value('cidade'),
	        'estado' => set_value('estado'),
	        'email' => set_value('email'),
	        'senha' => set_value('senha'),
	        'telefone' => set_value('telefone'),
	        'celular' => set_value('celular'),
	        'situacao' => set_value('situacao'),
	        'dataCadastro' => set_value('dataCadastro'),
            'permissoes_id' => set_value('permissoes_id'),
            'permissoes' => $permissoes
	    );
    
        $data['view'] = 'usuarios/usuarios_form';
        $this->load->view('tema/topo', $data, FALSE);

    }
    
    public function create_action() {
        if(!$this->permission->check($this->session->userdata('permissao'),'cUsuario')){
           $this->session->set_flashdata('error',$this->lang->line('app_permission_add').' '.$this->lang->line('users'));
           redirect(base_url());
        }

        $this->_rules();
        $this->form_validation->set_rules('senha', '<b>'.$this->lang->line('user_password').'</b>', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            $this->create();
        } else {
            $data = array(
		        'nome' => $this->input->post('nome',TRUE),
		        'rg' => $this->input->post('rg',TRUE),
		        'cpf' => $this->input->post('cpf',TRUE),
		        'rua' => $this->input->post('rua',TRUE),
		        'numero' => $this->input->post('numero',TRUE),
		        'bairro' => $this->input->post('bairro',TRUE),
		        'cidade' => $this->input->post('cidade',TRUE),
		        'estado' => $this->input->post('estado',TRUE),
		        'email' => $this->input->post('email',TRUE),
		        'senha' => password_hash($this->input->post('senha'), PASSWORD_DEFAULT),
		        'telefone' => $this->input->post('telefone',TRUE),
		        'celular' => $this->input->post('celular',TRUE),
		        'situacao' => $this->input->post('situacao',TRUE),
		        'dataCadastro' => date('Y-m-d'),
		        'permissoes_id' => $this->input->post('permissoes_id',TRUE),
	        );

            $this->Usuarios_model->insert($data);
            $this->session->set_flashdata('success', $this->lang->line('app_add_message'));
            redirect(site_url('usuarios'));
        }
    }
    
    public function update($id){
        if(!is_numeric($id)){
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('usuarios');
        }

        if(!$this->permission->check($this->session->userdata('permissao'),'cUsuario')){
           $this->session->set_flashdata('error',$this->lang->line('app_permission_edit').' '.$this->lang->line('users'));
           redirect(base_url());
        }

        $row = $this->Usuarios_model->get($id);

        if ($row) {

            $this->load->model('Permissoes_model');
            $permissoes = $this->Permissoes_model->where('situacao','1')->as_dropdown('nome')->get_all();

            $data = array(
                'button' => '<i class="fa fa-edit"></i> '.$this->lang->line('app_edit'),
                'action' => site_url('usuarios/update_action'),
		        'idUsuarios' => set_value('idUsuarios', $row->idUsuarios),
		        'nome' => set_value('nome', $row->nome),
		        'rg' => set_value('rg', $row->rg),
		        'cpf' => set_value('cpf', $row->cpf),
		        'rua' => set_value('rua', $row->rua),
		        'numero' => set_value('numero', $row->numero),
		        'bairro' => set_value('bairro', $row->bairro),
		        'cidade' => set_value('cidade', $row->cidade),
		        'estado' => set_value('estado', $row->estado),
		        'email' => set_value('email', $row->email),
		        'senha' => '',
		        'telefone' => set_value('telefone', $row->telefone),
		        'celular' => set_value('celular', $row->celular),
		        'situacao' => set_value('situacao', $row->situacao),
                'permissoes_id' => set_value('permissoes_id', $row->permissoes_id),
                'permissoes' => $permissoes
	        );
            $data['view'] = 'usuarios/usuarios_form';
            $this->load->view('tema/topo', $data, FALSE);

        } else {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('usuarios'));
        }
    }
    
    public function update_action(){
        if(!$this->permission->check($this->session->userdata('permissao'),'cUsuario')){
           $this->session->set_flashdata('error',$this->lang->line('app_permission_edit').' '.$this->lang->line('users'));
           redirect(base_url());
        }

        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->update($this->input->post('idUsuarios', TRUE));
        } else {
            $data = array(
		        'nome' => $this->input->post('nome',TRUE),
		        'rg' => $this->input->post('rg',TRUE),
		        'cpf' => $this->input->post('cpf',TRUE),
		        'rua' => $this->input->post('rua',TRUE),
		        'numero' => $this->input->post('numero',TRUE),
		        'bairro' => $this->input->post('bairro',TRUE),
		        'cidade' => $this->input->post('cidade',TRUE),
		        'estado' => $this->input->post('estado',TRUE),
		        'email' => $this->input->post('email',TRUE),
		        'telefone' => $this->input->post('telefone',TRUE),
		        'celular' => $this->input->post('celular',TRUE),
		        'situacao' => $this->input->post('situacao',TRUE),
		        'permissoes_id' => $this->input->post('permissoes_id',TRUE),
            );
            
            // Change password if not blank
            if($this->input->post('senha')){
                $senha = password_hash($this->input->post('senha'), PASSWORD_DEFAULT);
                $data['senha'] = $senha;
            } 

            $this->Usuarios_model->update($data, $this->input->post('idUsuarios', TRUE));
            $this->session->set_flashdata('success', $this->lang->line('app_edit_message'));
            redirect(site_url('usuarios'));
        }
    }
    
    public function status($idUsuarios){

        if(!is_numeric($idUsuarios)){
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('usuarios');
        }

        if(!$this->permission->check($this->session->userdata('permissao'),'cUsuario')){
           $this->session->set_flashdata('error',$this->lang->line('app_permission_edit').' '.$this->lang->line('users'));
           redirect(base_url());
        } 

        $row = $this->Usuarios_model->get($idUsuarios);
        $ajax = $this->input->get('ajax');

        if ($row) {
            if($this->Usuarios_model->update(array('situacao' => !$row->situacao), $idUsuarios)){

                if($ajax){
                    echo json_encode(array('result' => true, 'message' => $this->lang->line('app_delete_message'))); die();
                }
                $this->session->set_flashdata('success', $this->lang->line('app_delete_message'));
                redirect(site_url('usuarios'));
            }else{

                if($ajax){
                    echo json_encode(array('result' => false, 'message' => $this->lang->line('app_error'))); die();
                }

                $this->session->set_flashdata('error', $this->lang->line('app_error'));
                redirect(site_url('usuarios'));
            }

        } else {

            if($ajax){
                echo json_encode(array('result' => false, 'message' => $this->lang->line('app_not_found'))); die();
            }
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('usuarios'));
        }

    }

    public function _rules() 
    {
	    $this->form_validation->set_rules('nome', '<b>'.$this->lang->line('user_name').'</b>', 'trim|required');
	    $this->form_validation->set_rules('rg', '<b>'.$this->lang->line('user_rg').'</b>', 'trim|required');
	    $this->form_validation->set_rules('cpf', '<b>'.$this->lang->line('user_cpf').'</b>', 'trim|required');
	    $this->form_validation->set_rules('rua', '<b>'.$this->lang->line('user_street').'</b>', 'trim|required');
	    $this->form_validation->set_rules('numero', '<b>'.$this->lang->line('user_number').'</b>', 'trim|required');
	    $this->form_validation->set_rules('bairro', '<b>'.$this->lang->line('user_district').'</b>', 'trim|required');
	    $this->form_validation->set_rules('cidade', '<b>'.$this->lang->line('user_city').'</b>', 'trim|required');
	    $this->form_validation->set_rules('estado', '<b>'.$this->lang->line('user_state').'</b>', 'trim|required');
	    $this->form_validation->set_rules('email', '<b>'.$this->lang->line('user_email').'</b>', 'trim|required');
	    $this->form_validation->set_rules('senha', '<b>'.$this->lang->line('user_password').'</b>', 'trim');
	    $this->form_validation->set_rules('telefone', '<b>'.$this->lang->line('user_phone').'</b>', 'trim|required');
	    $this->form_validation->set_rules('celular', '<b>'.$this->lang->line('user_cel').'</b>', 'trim');
	    $this->form_validation->set_rules('situacao', '<b>'.$this->lang->line('user_status').'</b>', 'trim|required');
	    $this->form_validation->set_rules('permissoes_id', '<b>'.$this->lang->line(' user_group').'</b>', 'trim|required');

	    $this->form_validation->set_rules('idUsuarios', 'idUsuarios', 'trim');
	    $this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
    }

}

/* End of file Usuarios.php */
/* Location: ./application/controllers/Usuarios.php */