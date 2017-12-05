<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
    
    /**
     * author: Ramon Silva 
     * email: silva018-mg@yahoo.com.br
     * 
     */
    
class Persons extends MX_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('Persons_model');
        $this->load->library('form_validation');
        $this->load->language('person');
    }

    
    public function index()
    {
        // if(!$this->permission->checkPermission($this->session->userdata('permissao'),'vPersons')){
        //    $this->session->set_flashdata('error','Você não tem permissão para visualizar persons.');
        //    redirect(base_url());
        // }

        $this->template->build('persons/persons_list');
    }

    public function datatable()
    {
        $this->load->model('Persons_model');  
        $result_data = $this->Persons_model->get_datatables();  
        $data = array(); 

        foreach($result_data as $row)  
        {  
            $line = array(); 
            $line[] = '<input type="checkbox" class="remove" name="item_id[]" value="'.$row->id.'">';  

            
	        $line[] = $row->id;
	        $line[] = $row->image ? '<img class="img-responsive img-thumbnail" style="max-width: 5em; margin: 0 auto;" src="'.base_url('assets/img/'.$row->image).'" alt="image">' : '<img class="img-responsive img-thumbnail" style="max-width: 3em; margin: 0 auto;" src="'.base_url('assets/img/user.png').'" alt="image">';
            $line[] = $row->company;
            $line[] = $row->name;
            $line[] = $row->cpf_cnpj;
            $line[] = $row->phone;
            $line[] = $row->email;
	        $line[] = $row->active ? print_label($this->lang->line('app_yes'), 'success') : print_label($this->lang->line('app_no'), 'danger');
	        $line[] = datetime_from_sql($row->created_at);
	        $line[] = datetime_from_sql($row->updated_at);
	 

            $line[] = '<a href="'.site_url('persons/read/'.$row->id).'" class="btn btn-default" title="'.$this->lang->line('app_view').'"><i class="fa fa-eye"></i> </a> 
                       <a href="'.site_url('persons/update/'.$row->id).'" class="btn btn-primary" title="'.$this->lang->line('app_edit').'"><i class="fa fa-edit"></i></a> 
                       <a href="'.site_url('persons/delete/'.$row->id).'" class="btn btn-danger delete" title="'.$this->lang->line('app_delete').'"><i class="fa fa-remove"></i></a>';  
            $data[] = $line;  
        }  

        $output = array(  
            'draw'                => intval($this->input->post('draw')),  
            'recordsTotal'        => $this->Persons_model->get_all_data(),  
            'recordsFiltered'     => $this->Persons_model->get_filtered_data(),  
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

        // if(!$this->permission->checkPermission($this->session->userdata('permissao'),'vPersons')){
        //    $this->session->set_flashdata('error', $this->lang->line('app_permission_view') .'persons');
        //    redirect(base_url());
        // }

        $row = $this->Persons_model->get($id);
        if ($row) {
            $data = array(
		        'id' => $row->id,
		        'company' => $row->company,
		        'name' => $row->name,
		        'company_name' => $row->company_name,
		        'cpf_cnpj' => $row->cpf_cnpj,
		        'rg_ie' => $row->rg_ie,
		        'phone' => $row->phone,
		        'celphone' => $row->celphone,
		        'email' => $row->email,
		        'image' => $row->image,
		        'obs' => $row->obs,
		        'active' => $row->active,
		        'client' => $row->client,
		        'supplier' => $row->supplier,
		        'employee' => $row->employee,
		        'shipping_company' => $row->shipping_company,
		        'created_at' => $row->created_at,
		        'updated_at' => $row->updated_at,
	        );

            $this->template->build('persons/persons_read', $data);
        } else {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('persons'));
        }
    }

    public function create() 
    {
        // if(!$this->permission->checkPermission($this->session->userdata('permissao'),'aPersons')){
        //    $this->session->set_flashdata('error',$this->lang->line('app_permission_add') .'persons');
        //    redirect(base_url());
        // }

        $data = array(
            'button' => $this->lang->line('app_create'),
            'action' => site_url('persons/create_action'),
	        'id' => set_value('id'),
	        'company' => set_value('company'),
	        'name' => set_value('name'),
	        'company_name' => set_value('company_name'),
	        'cpf_cnpj' => set_value('cpf_cnpj'),
	        'rg_ie' => set_value('rg_ie'),
	        'phone' => set_value('phone'),
	        'celphone' => set_value('celphone'),
	        'email' => set_value('email'),
	        'image' => set_value('image'),
	        'obs' => set_value('obs'),
	        'active' => set_value('active'),
	        'client' => set_value('client'),
	        'supplier' => set_value('supplier'),
	        'employee' => set_value('employee'),
	        'shipping_company' => set_value('shipping_company')
	    );
    
        $this->template->build('persons/persons_form', $data);

    }
    
    public function create_action() 
    {
        // if(!$this->permission->checkPermission($this->session->userdata('permissao'),'aPersons')){
        //    $this->session->set_flashdata('error',$this->lang->line('app_permission_add') .'persons');
        //    redirect(base_url());
        // }

        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->create();
        } else {
            $data = array(
		        'company' => $this->input->post('company',TRUE),
		        'name' => $this->input->post('name',TRUE),
		        'company_name' => $this->input->post('company_name',TRUE),
		        'cpf_cnpj' => $this->input->post('cpf_cnpj',TRUE),
		        'rg_ie' => $this->input->post('rg_ie',TRUE),
		        'phone' => $this->input->post('phone',TRUE),
		        'celphone' => $this->input->post('celphone',TRUE),
		        'email' => $this->input->post('email',TRUE),
		        'image' => $this->input->post('image',TRUE),
		        'obs' => $this->input->post('obs',TRUE),
		        'active' => $this->input->post('active',TRUE),
		        'client' => $this->input->post('client',TRUE),
		        'supplier' => $this->input->post('supplier',TRUE),
		        'employee' => $this->input->post('employee',TRUE),
		        'shipping_company' => $this->input->post('shipping_company',TRUE)
	        );

            $this->Persons_model->insert($data);
            $this->session->set_flashdata('success', $this->lang->line('app_add_message'));
            redirect(site_url('persons'));
        }
    }
    
    public function update($id) 
    {
        if(!is_numeric($id)){
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('tipos','refresh');
        }

        // if(!$this->permission->checkPermission($this->session->userdata('permissao'),'ePersons')){
        //    $this->session->set_flashdata('error',$this->lang->line('app_permission_edit') .'persons');
        //    redirect(base_url());
        // }

        $row = $this->Persons_model->get($id);

        if ($row) {
            $data = array(
                'button' => $this->lang->line('app_edit'),
                'action' => site_url('persons/update_action'),
		        'id' => set_value('id', $row->id),
		        'company' => set_value('company', $row->company),
		        'name' => set_value('name', $row->name),
		        'company_name' => set_value('company_name', $row->company_name),
		        'cpf_cnpj' => set_value('cpf_cnpj', $row->cpf_cnpj),
		        'rg_ie' => set_value('rg_ie', $row->rg_ie),
		        'phone' => set_value('phone', $row->phone),
		        'celphone' => set_value('celphone', $row->celphone),
		        'email' => set_value('email', $row->email),
		        'image' => set_value('image', $row->image),
		        'obs' => set_value('obs', $row->obs),
		        'active' => set_value('active', $row->active),
		        'client' => set_value('client', $row->client),
		        'supplier' => set_value('supplier', $row->supplier),
		        'employee' => set_value('employee', $row->employee),
		        'shipping_company' => set_value('shipping_company', $row->shipping_company)
	        );
            $this->template->build('persons/persons_form', $data);

        } else {
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('persons'));
        }
    }
    
    public function update_action() 
    {
        // if(!$this->permission->checkPermission($this->session->userdata('permissao'),'ePersons')){
        //    $this->session->set_flashdata('error',$this->lang->line('app_permission_edit') .'persons');
        //    redirect(base_url());
        // }

        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->update($this->input->post('id', TRUE));
        } else {
            $data = array(
		        'company' => $this->input->post('company',TRUE),
		        'name' => $this->input->post('name',TRUE),
		        'company_name' => $this->input->post('company_name',TRUE),
		        'cpf_cnpj' => $this->input->post('cpf_cnpj',TRUE),
		        'rg_ie' => $this->input->post('rg_ie',TRUE),
		        'phone' => $this->input->post('phone',TRUE),
		        'celphone' => $this->input->post('celphone',TRUE),
		        'email' => $this->input->post('email',TRUE),
		        'image' => $this->input->post('image',TRUE),
		        'obs' => $this->input->post('obs',TRUE),
		        'active' => $this->input->post('active',TRUE),
		        'client' => $this->input->post('client',TRUE),
		        'supplier' => $this->input->post('supplier',TRUE),
		        'employee' => $this->input->post('employee',TRUE),
		        'shipping_company' => $this->input->post('shipping_company',TRUE)
	        );

            $this->Persons_model->update($data, $this->input->post('id', TRUE));
            $this->session->set_flashdata('success', $this->lang->line('app_edit_message'));
            redirect(site_url('persons'));
        }
    }
    
    public function delete($id) 
    {
        if(!is_numeric($id)){
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect('persons','refresh');
        }

        // if(!$this->permission->checkPermission($this->session->userdata('permissao'),'dPersons')){
        //    $this->session->set_flashdata('error',$this->lang->line('app_permission_delete') .'persons');
        //    redirect(base_url());
        // } 

        $row = $this->Persons_model->get($id);
        $ajax = $this->input->get('ajax');

        if ($row) {
            if($this->Persons_model->delete($id)){

                if($ajax){
                    echo json_encode(array('result' => true, 'message' => $this->lang->line('app_delete_message'))); die();
                }
                $this->session->set_flashdata('success', $this->lang->line('app_delete_message'));
                redirect(site_url('persons'));
            }else{

                if($ajax){
                    echo json_encode(array('result' => false, 'message' => $this->lang->line('app_error'))); die();
                }

                $this->session->set_flashdata('error', $this->lang->line('app_error'));
                redirect(site_url('persons'));
            }

        } else {

            if($ajax){
                echo json_encode(array('result' => false, 'message' => $this->lang->line('app_not_found'))); die();
            }
            $this->session->set_flashdata('error', $this->lang->line('app_not_found'));
            redirect(site_url('persons'));
        }

    }


    public function delete_many()
    {
        
        // if(!$this->permission->checkPermission($this->session->userdata('permissao'),'dPersons')){
        //    $this->session->set_flashdata('error',$this->lang->line('app_permission_delete') .'persons');
        //    redirect(base_url());
        // } 

        $items = $this->input->post('item_id[]');

        if($items){

            $verify = implode('', $items);
            if(is_numeric($verify)){

                $result = $this->Persons_model->delete_many($items);
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
	    $this->form_validation->set_rules('company', 'company', 'trim|required');
	    $this->form_validation->set_rules('name', 'name', 'trim|required');
	    $this->form_validation->set_rules('company_name', 'company name', 'trim|required');
	    $this->form_validation->set_rules('cpf_cnpj', 'cpf cnpj', 'trim|required');
	    $this->form_validation->set_rules('rg_ie', 'rg ie', 'trim|required');
	    $this->form_validation->set_rules('phone', 'phone', 'trim|required');
	    $this->form_validation->set_rules('celphone', 'celphone', 'trim|required');
	    $this->form_validation->set_rules('email', 'email', 'trim|required');
	    $this->form_validation->set_rules('image', 'image', 'trim|required');
	    $this->form_validation->set_rules('obs', 'obs', 'trim|required');
	    $this->form_validation->set_rules('active', 'active', 'trim|required');
	    $this->form_validation->set_rules('client', 'client', 'trim|required');
	    $this->form_validation->set_rules('supplier', 'supplier', 'trim|required');
	    $this->form_validation->set_rules('employee', 'employee', 'trim|required');
	    $this->form_validation->set_rules('shipping_company', 'shipping company', 'trim|required');
	    $this->form_validation->set_rules('created_at', 'created at', 'trim|required');
	    $this->form_validation->set_rules('updated_at', 'updated at', 'trim|required');

	    $this->form_validation->set_rules('id', 'id', 'trim');
	    $this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
    }

}

/* End of file Persons.php */
/* Location: ./application/controllers/Persons.php */