<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
    
    /**
     * author: Ramon Silva 
     * email: silva018-mg@yahoo.com.br
     * 
     */
    
class Services extends MX_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('Services_model');
    }

    
    public function index()
    {
        // if(!$this->permission->checkPermission($this->session->userdata('permissao'),'vServices')){
        //    $this->session->set_flashdata('error','Você não tem permissão para visualizar services.');
        //    redirect(base_url());
        // }

        $this->template->build('services/services_list');
    }

    public function datatable()
    {
        $this->load->model('Services_model');  
        $result_data = $this->Services_model->get_datatables();  
        $data = array(); 

        foreach($result_data as $row)  
        {  
            $line = array(); 
            $line[] = '<input type="checkbox" class="remove" name="item_id[]" value="'.$row->id.'">';  

            
	        $line[] = $row->id;
	        $line[] = $row->service_name;
	        $line[] = $row->price;
	        $line[] = $row->active;
	        $line[] = $row->created_at;
	        $line[] = $row->updated_at;
	 

            $line[] = '<a href="'.site_url('Services/read/'.$row->id).'" class="btn btn-default" title="'.$this->lang->line('app_view').'"><i class="fa fa-eye"></i> </a> 
                       <a href="'.site_url('Services/update/'.$row->id).'" class="btn btn-primary" title="'.$this->lang->line('app_edit').'"><i class="fa fa-edit"></i></a> 
                       <a href="'.site_url('Services/delete/'.$row->id).'" class="btn btn-danger delete" title="'.$this->lang->line('app_delete').'"><i class="fa fa-remove"></i></a>';  
            $data[] = $line;  
        }  

        $output = array(  
            'draw'                => intval($this->input->post('draw')),  
            'recordsTotal'        => $this->Services_model->get_all_data(),  
            'recordsFiltered'     => $this->Services_model->get_filtered_data(),  
            'data'                => $data  
        );  
        echo json_encode($output);
    }

    

    
    public function read($id) 
    {
        if(!is_numeric($id)){
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('tipos','refresh');
        }

        // if(!$this->permission->checkPermission($this->session->userdata('permissao'),'vServices')){
        //    $this->session->set_flashdata('error', $this->lang->line('app_permission_view') .'services');
        //    redirect(base_url());
        // }

        $row = $this->Services_model->get($id);
        if ($row) {
            $data = array(
		        'id' => $row->id,
		        'service_name' => $row->service_name,
		        'price' => $row->price,
		        'active' => $row->active,
		        'created_at' => $row->created_at,
		        'updated_at' => $row->updated_at,
	        );

            $this->template->build('services/services_read', $data);
        } else {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('services'));
        }
    }

    public function create() 
    {
        // if(!$this->permission->checkPermission($this->session->userdata('permissao'),'aServices')){
        //    $this->session->set_flashdata('error',$this->lang->line('app_permission_add') .'services');
        //    redirect(base_url());
        // }

        $data = array(
            'button' => $this->lang->line('app_create'),
            'action' => site_url('services/create_action'),
	        'id' => set_value('id'),
	        'service_name' => set_value('service_name'),
	        'price' => set_value('price'),
	        'active' => set_value('active'),
	        'created_at' => set_value('created_at'),
	        'updated_at' => set_value('updated_at'),
	    );
    
        $this->template->build('services/services_form', $data);

    }
    
    public function create_action() 
    {
        // if(!$this->permission->checkPermission($this->session->userdata('permissao'),'aServices')){
        //    $this->session->set_flashdata('error',$this->lang->line('app_permission_add') .'services');
        //    redirect(base_url());
        // }

        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->create();
        } else {
            $data = array(
		        'service_name' => $this->input->post('service_name',TRUE),
		        'price' => $this->input->post('price',TRUE),
		        'active' => $this->input->post('active',TRUE),
		        'created_at' => $this->input->post('created_at',TRUE),
		        'updated_at' => $this->input->post('updated_at',TRUE),
	        );

            $this->Services_model->insert($data);
            $this->session->set_flashdata('success', $this->lang->line('app_add_message'));
            redirect(site_url('services'));
        }
    }
    
    public function update($id) 
    {
        if(!is_numeric($id)){
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('tipos','refresh');
        }

        // if(!$this->permission->checkPermission($this->session->userdata('permissao'),'eServices')){
        //    $this->session->set_flashdata('error',$this->lang->line('app_permission_edit') .'services');
        //    redirect(base_url());
        // }

        $row = $this->Services_model->get($id);

        if ($row) {
            $data = array(
                'button' => $this->lang->line('app_edit'),
                'action' => site_url('services/update_action'),
		        'id' => set_value('id', $row->id),
		        'service_name' => set_value('service_name', $row->service_name),
		        'price' => set_value('price', $row->price),
		        'active' => set_value('active', $row->active),
		        'created_at' => set_value('created_at', $row->created_at),
		        'updated_at' => set_value('updated_at', $row->updated_at),
	        );
            $this->template->build('services/services_form', $data);

        } else {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('services'));
        }
    }
    
    public function update_action() 
    {
        // if(!$this->permission->checkPermission($this->session->userdata('permissao'),'eServices')){
        //    $this->session->set_flashdata('error',$this->lang->line('app_permission_edit') .'services');
        //    redirect(base_url());
        // }

        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->update($this->input->post('id', TRUE));
        } else {
            $data = array(
		        'service_name' => $this->input->post('service_name',TRUE),
		        'price' => $this->input->post('price',TRUE),
		        'active' => $this->input->post('active',TRUE),
		        'created_at' => $this->input->post('created_at',TRUE),
		        'updated_at' => $this->input->post('updated_at',TRUE),
	        );

            $this->Services_model->update($data, $this->input->post('id', TRUE));
            $this->session->set_flashdata('success', $this->lang->line('app_edit_message'));
            redirect(site_url('services'));
        }
    }
    
    public function delete($id) 
    {
        if(!is_numeric($id)){
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('services','refresh');
        }

        // if(!$this->permission->checkPermission($this->session->userdata('permissao'),'dServices')){
        //    $this->session->set_flashdata('error',$this->lang->line('app_permission_delete') .'services');
        //    redirect(base_url());
        // } 

        $row = $this->Services_model->get($id);
        $ajax = $this->input->get('ajax');

        if ($row) {
            if($this->Services_model->delete($id)){

                if($ajax){
                    echo json_encode(array('result' => true, 'message' => $this->lang->line('app_delete_message'))); die();
                }
                $this->session->set_flashdata('success', $this->lang->line('app_delete_message'));
                redirect(site_url('services'));
            }else{

                if($ajax){
                    echo json_encode(array('result' => false, 'message' => $this->lang->line('app_error'))); die();
                }

                $this->session->set_flashdata('error', $this->lang->line('app_error'));
                redirect(site_url('services'));
            }

        } else {

            if($ajax){
                echo json_encode(array('result' => false, 'message' => $this->lang->line('app_not_found'))); die();
            }
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('services'));
        }

    }


    public function delete_many()
    {
        
        // if(!$this->permission->checkPermission($this->session->userdata('permissao'),'dServices')){
        //    $this->session->set_flashdata('error',$this->lang->line('app_permission_delete') .'services');
        //    redirect(base_url());
        // } 

        $items = $this->input->post('item_id[]');

        if($items){

            $verify = implode('', $items);
            if(is_numeric($verify)){

                $result = $this->Services_model->delete_many($items);
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


    public function _rules() 
    {
	    $this->form_validation->set_rules('service_name', 'service name', 'trim|required');
	    $this->form_validation->set_rules('price', 'price', 'trim|required|numeric');
	    $this->form_validation->set_rules('active', 'active', 'trim|required');
	    $this->form_validation->set_rules('created_at', 'created at', 'trim|required');
	    $this->form_validation->set_rules('updated_at', 'updated at', 'trim|required');

	    $this->form_validation->set_rules('id', 'id', 'trim');
	    $this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
    }

}

/* End of file Services.php */
/* Location: ./application/controllers/Services.php */