<?php

class Proveedores extends CI_Controller {

    function __construct() {
        parent::__construct();
            if((!$this->session->session_id) || (!$this->session->userdata('logado'))){
            redirect('mapos/login');
            }
            $this->load->helper(array('codegen_helper'));
            $this->load->model('proveedores_model','',TRUE);
            $this->data['menuProveedores'] = 'proveedores';
            $this->load->model('mapos_model','',TRUE);
            $data['dados'] = $this->mapos_model->getEmitente();
            if(!isset($data['dados']) || $data['dados'] == null){
                redirect(site_url('mapos/emitente'));
            }
    }   

    function index(){
        $this->gerenciar();
    }

    function gerenciar(){
        if(!$this->permission->checkPermission($this->session->userdata('permissao'),'vCliente')){
           $this->session->set_flashdata('error','No tiene permiso para visualizar proveedores.');
           redirect(site_url());
        }
        $this->load->library('table');
        $this->load->library('pagination');
        
        $config['base_url'] = site_url('proveedores/gerenciar');
        $config['total_rows'] = $this->proveedores_model->count('proveedores');
        $config['per_page'] = 10;
        $config['next_link'] = 'Próxima';
        $config['prev_link'] = 'Anterior';
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
        $config['first_link'] = 'Primera';
        $config['last_link'] = 'Última';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';

        $this->pagination->initialize($config);

        $this->data['results'] = $this->proveedores_model->get('proveedores','idProveedores,nombreP,rucP,contactoP,tel1P,celular,email1P,direccionP,barrioP,ciudadP,cpP,paisP,notasP','',$config['per_page'],$this->uri->segment(3));

        $this->data['view'] = 'proveedores/proveedores';
        $this->load->view('tema/topo',$this->data);

    }

    function crear_proveedor() {
        $this->load->library('form_validation');
        $this->data['custom_error'] = '';
        
        if ($this->form_validation->run('proveedores') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        }
        else{

            $data = array(
                'nombreP'       => $this->input->post('txtRazonSocial'),
                'rucP'          => $this->input->post('txtRUC'),
                'contactoP'     => $this->input->post('txtContacto'),
                'tel1P'         => $this->input->post('txtTelefono'),
                'celular'       => $this->input->post('txtCelular'),
                'email1P'       => $this->input->post('txtEmail'),
                'direccionP'    => $this->input->post('txtDireccion'),
                'barrioP'       => $this->input->post('txtSector'),
                'ciudadP'       => $this->input->post('txtCiudad'),
                'cpP'           => $this->input->post('txtCP'),
                'paisP'         => $this->input->post('txtPais'),
                'notasP'        => $this->input->post('txtNotas'),
                'fecharegistro' => date('Y-m-d')
            );

            if ($this->proveedores_model->add('proveedores', $data) == TRUE) {
                $this->session->set_flashdata('success','Proveedor agregado con éxito!');
                redirect(site_url('proveedores/crear_proveedor'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ha ocurrido un error.</p></div>';
            }
        }

        $this->data['view'] = 'proveedores/adicionarProveedor';
        $this->load->view('tema/topo', $this->data);
        var_dump($this->data);
    }

    public function visualizar(){

        if(!$this->permission->checkPermission($this->session->userdata('permissao'),'vCliente')){
           $this->session->set_flashdata('error','No tiene permiso para visualizar proveedores.');
           redirect(site_url());
        }

        $this->data['custom_error'] = '';
        $this->data['result'] = $this->proveedores_model->getById($this->uri->segment(3));
        $this->data['results'] = $this->proveedores_model->getOsByCliente($this->uri->segment(3));
        $this->data['view'] = 'proveedores/visualizar';
        $this->load->view('tema/topo', $this->data);
    }

    public function excluir(){

            if(!$this->permission->checkPermission($this->session->userdata('permissao'),'dCliente')){
               $this->session->set_flashdata('error','No tiene permiso para eliminar proveedores.');
               redirect(site_url());
            }

            $id =  $this->input->post('id');
            if ($id == null){
                $this->session->set_flashdata('error','Error al intentar eliminar proveedores.');
                redirect(site_url('proveedores'));
            }
            //$id = 2;
            // excluyendo OSs vinculadas al cliente
            
            $this->db->where('idProveedores', $id);
            $os = $this->db->get('os')->result();
            if($os != null){
                foreach ($os as $o) {
                    $this->db->where('os_id', $o->idOs);
                    $this->db->delete('servicos_os');
                    $this->db->where('os_id', $o->idOs);
                    $this->db->delete('produtos_os');
                    $this->db->where('idOs', $o->idOs);
                    $this->db->delete('os');
                }
            }
            // excluyendo Ventas vinculadas al cliente

            $this->db->where('idProveedores', $id);
            $vendas = $this->db->get('vendas')->result();
            if($vendas != null){
                foreach ($vendas as $v) {
                    $this->db->where('vendas_id', $v->idVendas);
                    $this->db->delete('itens_de_vendas');
                    $this->db->where('idVendas', $v->idVendas);
                    $this->db->delete('vendas');
                }
            }
            //excluyendo ingresos vinculados al cliente

            $this->db->where('idProveedores', $id);
            $this->db->delete('lancamentos');
            $this->proveedores_model->delete('proveedores','idProveedores',$id);
            $this->session->set_flashdata('success','Proveedor eliminado con éxito!');
            redirect(site_url('proveedores'));
    }
}
