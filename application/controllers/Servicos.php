<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
    
    /**
     * author: Ramon Silva 
     * email: silva018-mg@yahoo.com.br
     * 
     */
    
class Servicos extends CI_Controller {
    public function __construct() {
        parent::__construct();

        if( (!session_id()) || (!$this->session->userdata('logado'))){
            redirect('mapos/login');
        }

        $this->load->model('Servicos_model');
        $this->load->library('form_validation');
        
    }

    
    public function index() {
        if(!$this->permission->check($this->session->userdata('permissao'),'vServico')){
           $this->session->set_flashdata('error', $this->lang->line('app_permission_view').' '.$this->lang->line('services'));
           redirect(base_url());
        }

        $data['view'] = 'servicos/servicos_list';
        $this->load->view('tema/topo', $data, FALSE);
    
    }

    public function datatable() {
        $this->load->model('Servicos_model');  
        $result_data = $this->Servicos_model->get_datatables();  
        $data = array(); 

        foreach($result_data as $row)  
        {  
            $line = array(); 
            $line[] = '<input type="checkbox" class="remove" name="item_id[]" value="'.$row->idServicos.'">';  

            
	        $line[] = $row->idServicos;
	        $line[] = $row->nome;
	        $line[] = $row->descricao;
	        $line[] = $row->preco;
	 

            $line[] = '<a href="'.site_url('servicos/read/'.$row->idServicos).'" class="btn btn-dark" title="'.$this->lang->line('app_view').'"><i class="fa fa-eye"></i> </a> 
                       <a href="'.site_url('servicos/update/'.$row->idServicos).'" class="btn btn-info" title="'.$this->lang->line('app_edit').'"><i class="fa fa-edit"></i></a> 
                       <a href="'.site_url('servicos/delete/'.$row->idServicos).'" class="btn btn-danger delete" title="'.$this->lang->line('app_delete').'"><i class="fa fa-remove"></i></a>';  
            $data[] = $line;  
        }  

        $output = array(  
            'draw'                => intval($this->input->post('draw')),  
            'recordsTotal'        => $this->Servicos_model->get_all_data(),  
            'recordsFiltered'     => $this->Servicos_model->get_filtered_data(),  
            'data'                => $data  
        );  
        echo json_encode($output);
    }

    
    public function read($id) {
        if(!is_numeric($id)){
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('servicos');
        }

        if(!$this->permission->check($this->session->userdata('permissao'),'vServico')){
           $this->session->set_flashdata('error', $this->lang->line('app_permission_view').' '.$this->lang->line('services'));
           redirect(base_url());
        }

        $row = $this->Servicos_model->get($id);
        if ($row) {
            $data = array(
		        'idServicos' => $row->idServicos,
		        'nome' => $row->nome,
		        'descricao' => $row->descricao,
		        'preco' => $row->preco,
	        );

            $data['view'] = 'servicos/servicos_read';
            $this->load->view('tema/topo', $data, FALSE);

        } else {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('servicos'));
        }
    }

    public function create() {

        if(!$this->permission->check($this->session->userdata('permissao'),'aServico')){
           $this->session->set_flashdata('error',$this->lang->line('app_permission_add').' '.$this->lang->line('services'));
           redirect(base_url());
        }

        $data = array(
            'button' => '<i class="fa fa-plus"></i> '.$this->lang->line('app_create'),
            'action' => site_url('servicos/create_action'),
	        'idServicos' => set_value('idServicos'),
	        'nome' => set_value('nome'),
	        'descricao' => set_value('descricao'),
	        'preco' => set_value('preco'),
	    );
        
        $data['view'] = 'servicos/servicos_form';
        $this->load->view('tema/topo', $data, FALSE);

    }
    
    public function create_action() {
        if(!$this->permission->check($this->session->userdata('permissao'),'aServico')){
           $this->session->set_flashdata('error',$this->lang->line('app_permission_add').' '.$this->lang->line('services'));
           redirect(base_url());
        }

        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->create();
        } else {
            $data = array(
		        'nome' => $this->input->post('nome',TRUE),
		        'descricao' => $this->input->post('descricao',TRUE),
		        'preco' => $this->input->post('preco',TRUE),
	        );

            $this->Servicos_model->insert($data);
            $this->session->set_flashdata('success', $this->lang->line('app_add_message'));
            redirect(site_url('servicos'));
        }
    }
    
    public function update($id) {

        if(!is_numeric($id)){
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('servicos');
        }

        if(!$this->permission->check($this->session->userdata('permissao'),'eServico')){
           $this->session->set_flashdata('error',$this->lang->line('app_permission_edit').' '.$this->lang->line('services'));
           redirect(base_url());
        }

        $row = $this->Servicos_model->get($id);

        if ($row) {
            $data = array(
                'button' => '<i class="fa fa-edit"></i> '.$this->lang->line('app_edit'),
                'action' => site_url('servicos/update_action'),
		        'idServicos' => set_value('idServicos', $row->idServicos),
		        'nome' => set_value('nome', $row->nome),
		        'descricao' => set_value('descricao', $row->descricao),
		        'preco' => set_value('preco', $row->preco),
            );
            
            $data['view'] = 'servicos/servicos_form';
            $this->load->view('tema/topo', $data, FALSE);

        } else {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('servicos'));
        }
    }
    
    public function update_action(){

        if(!$this->permission->check($this->session->userdata('permissao'),'eServico')){
           $this->session->set_flashdata('error',$this->lang->line('app_permission_edit').' '.$this->lang->line('services'));
           redirect(base_url());
        }

        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->update($this->input->post('idServicos', TRUE));
        } else {
            $data = array(
		        'nome' => $this->input->post('nome',TRUE),
		        'descricao' => $this->input->post('descricao',TRUE),
		        'preco' => $this->input->post('preco',TRUE),
	        );

            $this->Servicos_model->update($data, $this->input->post('idServicos', TRUE));
            $this->session->set_flashdata('success', $this->lang->line('app_edit_message'));
            redirect(site_url('servicos'));
        }
    }
    
    public function delete($idServicos){

        if(!is_numeric($idServicos)){
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('servicos');
        }

        if(!$this->permission->check($this->session->userdata('permissao'),'dServico')){
           $this->session->set_flashdata('error',$this->lang->line('app_permission_delete').' '.$this->lang->line('services'));
           redirect(base_url());
        } 

        $row = $this->Servicos_model->get($idServicos);
        $ajax = $this->input->get('ajax');

        if ($row) {

            // remover itens vinculados ao serviço
            $this->Servicos_model->delete_linked($idServicos);

            if($this->Servicos_model->delete($idServicos)){

                if($ajax){
                    echo json_encode(array('result' => true, 'message' => $this->lang->line('app_delete_message'))); die();
                }
                $this->session->set_flashdata('success', $this->lang->line('app_delete_message'));
                redirect(site_url('servicos'));
            }else {

                if($ajax){
                    echo json_encode(array('result' => false, 'message' => $this->lang->line('app_error'))); die();
                }

                $this->session->set_flashdata('error', $this->lang->line('app_error'));
                redirect(site_url('servicos'));
            }

        } else {

            if($ajax){
                echo json_encode(array('result' => false, 'message' => $this->lang->line('app_not_found'))); die();
            }
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('servicos'));
        }

    }

    public function delete_many(){
        
        if(!$this->permission->check($this->session->userdata('permissao'),'dServico')){
           $this->session->set_flashdata('error',$this->lang->line('app_permission_delete').' '.$this->lang->line('services'));
           redirect(base_url());
        } 

        $items = $this->input->post('item_id[]');

        if($items){

            $verify = implode('', $items);
            if(is_numeric($verify)){

                $this->Servicos_model->delete_linked($items);

                $result = $this->Servicos_model->delete_many($items);
                if($result){
                    echo json_encode(array('result' => true, 'message' => $this->lang->line('app_delete_message_many'))); die();
                }else{
                    echo json_encode(array('result' => false, 'message' => $this->lang->line('app_error'))); die();
                }

            }else{
                echo json_encode(array('result' => false, 'message' => $this->lang->line('app_data_not_supported'))); die();
            }
        }
        echo json_encode(array('result' => false, 'message' => $this->lang->line('app_empty_data'))); die();
        
    }

    public function _rules(){
	    $this->form_validation->set_rules('nome', '<b>'.$this->lang->line('service_name').'</b>', 'trim|required');
	    $this->form_validation->set_rules('descricao', '<b>'.$this->lang->line('service_description').'</b>', 'trim|required');
	    $this->form_validation->set_rules('preco', '<b>'.$this->lang->line('service_price').'</b>', 'trim|required|numeric');

	    $this->form_validation->set_rules('idServicos', 'idServicos', 'trim');
	    $this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
    }

}

/* End of file Servicos.php */
/* Location: ./application/controllers/Servicos.php */