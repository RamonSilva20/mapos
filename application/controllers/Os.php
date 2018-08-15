<?php

class Os extends CI_Controller
{
    
    /**
     * author: Ramon Silva
     * email: silva018-mg@yahoo.com.br
     *
     */

    function __construct()
    {
        parent::__construct();
        
        if ((!session_id()) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');
        }
        
        $this->load->helper(array('form','codegen_helper'));
        $this->load->model('os_model', '', true);
        $this->data['menuOs'] = 'OS';
    }
    
    function index()
    {
        $this->gerenciar();
    }

    function gerenciar()
    {
        
        $this->load->library('pagination');
        
        $where_array = array();

        $pesquisa = $this->input->get('pesquisa');
        $status = $this->input->get('status');
        $de = $this->input->get('data');
        $ate = $this->input->get('data2');

        if ($pesquisa) {
            $where_array['pesquisa'] = $pesquisa;
        }
        if ($status) {
            $where_array['status'] = $status;
        }
        if ($de) {

            $de = explode('/', $de);
            $de = $de[2].'-'.$de[1].'-'.$de[0];

            $where_array['de'] = $de;
        }
        if ($ate) {
            $ate = explode('/', $ate);
            $ate = $ate[2].'-'.$ate[1].'-'.$ate[0];

            $where_array['ate'] = $ate;
        }
        
        $config['base_url'] = base_url().'index.php/os/gerenciar/';
        $config['total_rows'] = $this->os_model->count('os');
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
        $config['first_link'] = 'Primeira';
        $config['last_link'] = 'Última';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
            
        $this->pagination->initialize($config);

        $this->data['results'] = $this->os_model->getOs('os', 'idOs,dataInicial,dataFinal,garantia,descricaoProduto,defeito,status,observacoes,laudoTecnico', $where_array, $config['per_page'], $this->uri->segment(3));
       
        $this->data['view'] = 'os/os';
        $this->load->view('tema/topo', $this->data);
      
        
    }
    
    function adicionar()
    {


        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar O.S.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';
        
        if ($this->form_validation->run('os') == false) {
            $this->data['custom_error'] = (validation_errors() ? true : false);
        } else {

            $dataInicial = $this->input->post('dataInicial');
            $dataFinal = $this->input->post('dataFinal');

            try {
                
                $dataInicial = explode('/', $dataInicial);
                $dataInicial = $dataInicial[2].'-'.$dataInicial[1].'-'.$dataInicial[0];

                if ($dataFinal) {
                    $dataFinal = explode('/', $dataFinal);
                    $dataFinal = $dataFinal[2].'-'.$dataFinal[1].'-'.$dataFinal[0];
                } else {
                    $dataFinal = date('Y/m/d');
                }

            } catch (Exception $e) {
                $dataInicial = date('Y/m/d');
                $dataFinal = date('Y/m/d');
            }

            $data = array(
                'dataInicial' => $dataInicial,
                'clientes_id' => $this->input->post('clientes_id'),//set_value('idCliente'),
                'usuarios_id' => $this->input->post('usuarios_id'),//set_value('idUsuario'),
                'dataFinal' => $dataFinal,
                'garantia' => set_value('garantia'),
                'descricaoProduto' => set_value('descricaoProduto'),
                'defeito' => set_value('defeito'),
                'status' => set_value('status'),
                'observacoes' => set_value('observacoes'),
                'laudoTecnico' => set_value('laudoTecnico'),
                'faturado' => 0
            );

            if (is_numeric($id = $this->os_model->add('os', $data, true))) {
                $this->session->set_flashdata('success', 'OS adicionada com sucesso, você pode adicionar produtos ou serviços a essa OS nas abas de "Produtos" e "Serviços"!');
                redirect('os/editar/'.$id);

            } else {
                
                $this->data['custom_error'] = '<div class="form_error"><p>An Error Occured.</p></div>';
            }
        }
         
        $this->data['view'] = 'os/adicionarOs';
        $this->load->view('tema/topo', $this->data);
    }
    
    public function adicionarAjax()
    {

        $this->load->library('form_validation');

        if ($this->form_validation->run('os') == false) {
            $json = array("result"=> false);
            echo json_encode($json);
        } else {
            $data = array(
                'dataInicial' => set_value('dataInicial'),
                'clientes_id' => $this->input->post('clientes_id'),//set_value('idCliente'),
                'usuarios_id' => $this->input->post('usuarios_id'),//set_value('idUsuario'),
                'dataFinal' => set_value('dataFinal'),
                'garantia' => set_value('garantia'),
                'descricaoProduto' => set_value('descricaoProduto'),
                'defeito' => set_value('defeito'),
                'status' => set_value('status'),
                'observacoes' => set_value('observacoes'),
                'laudoTecnico' => set_value('laudoTecnico')
            );

            if (is_numeric($id = $this->os_model->add('os', $data, true))) {
                $json = array("result"=> true, "id"=> $id);
                echo json_encode($json);

            } else {
                $json = array("result"=> false);
                echo json_encode($json);

            }
        }
         
    }

    function editar()
    {

        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar O.S.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('os') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {

            $dataInicial = $this->input->post('dataInicial');
            $dataFinal = $this->input->post('dataFinal');

            try {
                
                $dataInicial = explode('/', $dataInicial);
                $dataInicial = $dataInicial[2].'-'.$dataInicial[1].'-'.$dataInicial[0];

                $dataFinal = explode('/', $dataFinal);
                $dataFinal = $dataFinal[2].'-'.$dataFinal[1].'-'.$dataFinal[0];

            } catch (Exception $e) {
                $dataInicial = date('Y/m/d');
            }

            $data = array(
                'dataInicial' => $dataInicial,
                'dataFinal' => $dataFinal,
                'garantia' => $this->input->post('garantia'),
                'descricaoProduto' => $this->input->post('descricaoProduto'),
                'defeito' => $this->input->post('defeito'),
                'status' => $this->input->post('status'),
                'observacoes' => $this->input->post('observacoes'),
                'laudoTecnico' => $this->input->post('laudoTecnico'),
                'usuarios_id' => $this->input->post('usuarios_id'),
                'clientes_id' => $this->input->post('clientes_id')
            );

            if ($this->os_model->edit('os', $data, 'idOs', $this->input->post('idOs')) == true) {
                $this->session->set_flashdata('success', 'Os editada com sucesso!');
                redirect(base_url() . 'index.php/os/editar/'.$this->input->post('idOs'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro</p></div>';
            }
        }

        $this->data['result'] = $this->os_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->os_model->getProdutos($this->uri->segment(3));
        $this->data['servicos'] = $this->os_model->getServicos($this->uri->segment(3));
        $this->data['anexos'] = $this->os_model->getAnexos($this->uri->segment(3));
        $this->data['view'] = 'os/editarOs';
        $this->load->view('tema/topo', $this->data);
   
    }

    public function visualizar()
    {

        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar O.S.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->os_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->os_model->getProdutos($this->uri->segment(3));
        $this->data['servicos'] = $this->os_model->getServicos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();

        $this->data['view'] = 'os/visualizarOs';
        $this->load->view('tema/topo', $this->data);
       
    }

    public function imprimir()
    {
        
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }
        
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar O.S.');
            redirect(base_url());
        }
        
                $this->data['custom_error'] = '';
                $this->load->model('mapos_model');
                $this->data['result'] = $this->os_model->getById($this->uri->segment(3));
                $this->data['produtos'] = $this->os_model->getProdutos($this->uri->segment(3));
                $this->data['servicos'] = $this->os_model->getServicos($this->uri->segment(3));
                $this->data['emitente'] = $this->mapos_model->getEmitente();
        
                $this->load->view('os/imprimirOs', $this->data);
               
    }
    
    function excluir()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'dOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir O.S.');
            redirect(base_url());
        }
        
        $id =  $this->input->post('id');
        if ($id == null) {

            $this->session->set_flashdata('error', 'Erro ao tentar excluir OS.');
            redirect(base_url().'index.php/os/gerenciar/');
        }

        $this->db->where('os_id', $id);
        $this->db->delete('servicos_os');

        $this->db->where('os_id', $id);
        $this->db->delete('produtos_os');

        $this->db->where('os_id', $id);
        $this->db->delete('anexos');

        $this->os_model->delete('os', 'idOs', $id);
        

        $this->session->set_flashdata('success', 'OS excluída com sucesso!');
        redirect(base_url().'index.php/os/gerenciar/');


        
    }

    public function autoCompleteProduto()
    {
        
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->os_model->autoCompleteProduto($q);
        }

    }

    public function autoCompleteProdutoSaida()
    {
        
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->os_model->autoCompleteProdutoSaida($q);
        }

    }

    public function autoCompleteCliente()
    {

        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->os_model->autoCompleteCliente($q);
        }

    }

    public function autoCompleteUsuario()
    {

        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->os_model->autoCompleteUsuario($q);
        }

    }

    public function autoCompleteServico()
    {

        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->os_model->autoCompleteServico($q);
        }

    }

    public function adicionarProduto()
    {

        
        $preco = $this->input->post('preco');
        $quantidade = $this->input->post('quantidade');
        $subtotal = $preco * $quantidade;
        $produto = $this->input->post('idProduto');
        $data = array(
            'quantidade'=> $quantidade,
            'subTotal'=> $subtotal,
            'produtos_id'=> $produto,
            'os_id'=> $this->input->post('idOsProduto'),
        );

        if ($this->os_model->add('produtos_os', $data) == true) {
            $sql = "UPDATE produtos set estoque = estoque - ? WHERE idProdutos = ?";
            $this->db->query($sql, array($quantidade, $produto));
            
            echo json_encode(array('result'=> true));
        } else {
            echo json_encode(array('result'=> false));
        }
      
    }

    function excluirProduto()
    {
        
            $ID = $this->input->post('idProduto');
        if ($this->os_model->delete('produtos_os', 'idProdutos_os', $ID) == true) {
                
            $quantidade = $this->input->post('quantidade');
            $produto = $this->input->post('produto');


            $sql = "UPDATE produtos set estoque = estoque + ? WHERE idProdutos = ?";

            $this->db->query($sql, array($quantidade, $produto));
                
            echo json_encode(array('result'=> true));
        } else {
            echo json_encode(array('result'=> false));
        }
    }

    public function adicionarServico()
    {

        $data = array(
            'servicos_id'=> $this->input->post('idServico'),
            'os_id'=> $this->input->post('idOsServico'),
            'subTotal'=> $this->input->post('precoServico')
        );

        if ($this->os_model->add('servicos_os', $data) == true) {

            echo json_encode(array('result'=> true));
        } else {
            echo json_encode(array('result'=> false));
        }

    }

    function excluirServico()
    {
            $ID = $this->input->post('idServico');
        if ($this->os_model->delete('servicos_os', 'idServicos_os', $ID) == true) {

            echo json_encode(array('result'=> true));
        } else {
            echo json_encode(array('result'=> false));
        }
    }


    public function anexar()
    {

        $this->load->library('upload');
        $this->load->library('image_lib');

        $upload_conf = array(
            'upload_path'   => realpath('./assets/anexos'),
            'allowed_types' => 'jpg|png|gif|jpeg|JPG|PNG|GIF|JPEG|pdf|PDF|cdr|CDR|docx|DOCX|txt', // formatos permitidos para anexos de os
            'max_size'      => 0,
            );
    
        $this->upload->initialize($upload_conf);
        
        foreach ($_FILES['userfile'] as $key => $val) {
            $i = 1;
            foreach ($val as $v) {
                $field_name = "file_".$i;
                $_FILES[$field_name][$key] = $v;
                $i++;
            }
        }
        unset($_FILES['userfile']);
    

        $error = array();
        $success = array();
        
        foreach ($_FILES as $field_name => $file) {
            if (! $this->upload->do_upload($field_name)) {
                $error['upload'][] = $this->upload->display_errors();
            } else {

                $upload_data = $this->upload->data();
                
                if ($upload_data['is_image'] == 1) {

                   // set the resize config
                    $resize_conf = array(
    
                        'source_image'  => $upload_data['full_path'],
                        'new_image'     => $upload_data['file_path'].'thumbs/thumb_'.$upload_data['file_name'],
                        'width'         => 200,
                        'height'        => 125
                        );

                    $this->image_lib->initialize($resize_conf);

                    if (! $this->image_lib->resize()) {
                        $error['resize'][] = $this->image_lib->display_errors();
                    } else {
                        $success[] = $upload_data;
                        $this->load->model('Os_model');
                        $this->Os_model->anexar($this->input->post('idOsServico'), $upload_data['file_name'], base_url().'assets/anexos/', 'thumb_'.$upload_data['file_name'], realpath('./assets/anexos/'));

                    }
                } else {

                    $success[] = $upload_data;

                    $this->load->model('Os_model');

                    $this->Os_model->anexar($this->input->post('idOsServico'), $upload_data['file_name'], base_url().'assets/anexos/', '', realpath('./assets/anexos/'));
 
                }
                
            }
        }


        if (count($error) > 0) {
            echo json_encode(array('result'=> false, 'mensagem' => 'Nenhum arquivo foi anexado.'));
        } else {
            echo json_encode(array('result'=> true, 'mensagem' => 'Arquivo(s) anexado(s) com sucesso .'));
        }
        

    }


    public function excluirAnexo($id = null)
    {
        if ($id == null || !is_numeric($id)) {
            echo json_encode(array('result'=> false, 'mensagem' => 'Erro ao tentar excluir anexo.'));
        } else {

            $this->db->where('idAnexos', $id);
            $file = $this->db->get('anexos', 1)->row();

            unlink($file->path.'/'.$file->anexo);

            if ($file->thumb != null) {
                unlink($file->path.'/thumbs/'.$file->thumb);
            }
            
            if ($this->os_model->delete('anexos', 'idAnexos', $id) == true) {

                echo json_encode(array('result'=> true, 'mensagem' => 'Anexo excluído com sucesso.'));
            } else {
                echo json_encode(array('result'=> false, 'mensagem' => 'Erro ao tentar excluir anexo.'));
            }

            
        }
    }


    public function downloadanexo($id = null)
    {
        
        if ($id != null && is_numeric($id)) {
            
            $this->db->where('idAnexos', $id);
            $file = $this->db->get('anexos', 1)->row();

            $this->load->library('zip');

            $path = $file->path;

            $this->zip->read_file($path.'/'.$file->anexo);

            $this->zip->download('file'.date('d-m-Y-H.i.s').'.zip');

        }
      
    }


    public function faturar()
    {

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';
 

        if ($this->form_validation->run('receita') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {


            $vencimento = $this->input->post('vencimento');
            $recebimento = $this->input->post('recebimento');

            try {
                
                $vencimento = explode('/', $vencimento);
                $vencimento = $vencimento[2].'-'.$vencimento[1].'-'.$vencimento[0];

                if ($recebimento != null) {
                    $recebimento = explode('/', $recebimento);
                    $recebimento = $recebimento[2].'-'.$recebimento[1].'-'.$recebimento[0];

                }
            } catch (Exception $e) {
                $vencimento = date('Y/m/d');
            }
            
            $data = array(
                'descricao' => set_value('descricao'),
                'valor' => $this->input->post('valor'),
                'clientes_id' => $this->input->post('clientes_id'),
                'data_vencimento' => $vencimento,
                'data_pagamento' => $recebimento,
                'baixado' => $this->input->post('recebido') ? : 0,
                'cliente_fornecedor' => set_value('cliente'),
                'forma_pgto' => $this->input->post('formaPgto'),
                'tipo' => $this->input->post('tipo')
            );

            if ($this->os_model->add('lancamentos', $data) == true) {
                
                $os = $this->input->post('os_id');

                $this->db->set('faturado', 1);
                $this->db->set('valorTotal', $this->input->post('valor'));
                $this->db->set('status', 'Faturado');
                $this->db->where('idOs', $os);
                $this->db->update('os');

                $this->session->set_flashdata('success', 'OS faturada com sucesso!');
                $json = array('result'=>  true);
                echo json_encode($json);
                die();
            } else {
                $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar faturar OS.');
                $json = array('result'=>  false);
                echo json_encode($json);
                die();
            }
        }

        $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar faturar OS.');
        $json = array('result'=>  false);
        echo json_encode($json);
        
    }
}
