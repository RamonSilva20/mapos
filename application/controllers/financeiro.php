<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Financeiro extends CI_Controller
{

    public function __construct()
    {

        parent::__construct();

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('logado'))) {

            redirect('mapos/login');

        }

        $this->load->model('financeiro_model', '', true);

        $this->data['menuFinanceiro'] = 'financeiro';

        $this->load->helper(array('codegen_helper'));

    }

    public function index()
    {

        $this->lancamentos();

    }

    public function lancamentos()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar lançamentos.');
            redirect(base_url());
        }

        $where = '';
        $dataInicial = $this->input->get('dataInicial');
        $dataFinal = $this->input->get('dataFinal');
        $periodo = $this->input->get('periodo');
        $situacao = $this->input->get('situacao');
        $responsavel = $this->input->get('usuarios_id');
        //verifica se existe um recurso selecionado
        if ($responsavel != "") {
            $whereResponsavel = ' AND usuarios_id = "' . $responsavel . '"';
        } else {
            $whereResponsavel = '';
        }

        $newDataInicial = date("Y-m-d H:i:s", strtotime($dataInicial));
        $newDataFinal = date("Y-m-d H:i:s", strtotime($dataFinal));
        $this->data['estatisticas_financeiro'] = $this->financeiro_model->getEstatisticasFinanceiro($newDataInicial, $newDataFinal);

        //busca uma data específica

        if ($dataInicial != "") {

            if ($dataFinal != "") {
                $where = 'data_vencimento BETWEEN "' . $dataInicial . '" AND "' . $dataFinal . '"';
            } else {
                $where = 'data_vencimento = "' . $dataInicial . '"';
            }

        } else {

            // busca todos os lançamentos

            if ($periodo == 'todos') {

                if ($situacao == 'previsto') {

                    $where = 'data_vencimento > "' . date('Y-m-d') . '" AND baixado = "0"';

                } else {

                    if ($situacao == 'atrasado') {

                        $where = 'data_vencimento < "' . date('Y-m-d') . '" AND baixado = "0"';

                    } else {

                        if ($situacao == 'realizado') {

                            $where = 'baixado = "1"';

                        }

                        if ($situacao == 'pendente') {

                            $where = 'baixado = "0"';

                        }

                    }

                }

            } else {

                // busca lançamentos do dia

                if ($periodo == null || $periodo == 'dia') {

                    $where = 'data_vencimento = "' . date('Y-m-d' . '"');

                } // fim lançamentos dia

                else {

                    // busca lançamentos da semana

                    if ($periodo == 'semana') {

                        $semana = $this->getThisWeek();

                        if (!isset($situacao) || $situacao == 'todos') {

                            $where = 'data_vencimento BETWEEN "' . $semana[0] . '" AND "' . $semana[1] . '"';

                        } else {

                            if ($situacao == 'previsto') {

                                $where = 'data_vencimento BETWEEN "' . date('Y-m-d') . '" AND "' . $semana[1] . '" AND baixado = "0"';

                            } else {

                                if ($situacao == 'atrasado') {

                                    $where = 'data_vencimento BETWEEN "' . $semana[0] . '" AND "' . date('Y-m-d') . '" AND baixado = "0"';

                                } else {

                                    if ($situacao == 'realizado') {

                                        $where = 'data_vencimento BETWEEN "' . $semana[0] . '" AND "' . $semana[1] . '" AND baixado = "1"';

                                    } else {

                                        $where = 'data_vencimento BETWEEN "' . $semana[0] . '" AND "' . $semana[1] . '" AND baixado = "0"';

                                    }

                                }

                            }

                        }

                    } // fim lançamentos dia

                    else {

                        // busca lançamento do mês

                        if ($periodo == 'mes') {

                            $mes = $this->getThisMonth();

                            if (!isset($situacao) || $situacao == 'todos') {

                                $where = 'data_vencimento BETWEEN "' . $mes[0] . '" AND "' . $mes[1] . '"';

                            } else {

                                if ($situacao == 'previsto') {

                                    $where = 'data_vencimento BETWEEN "' . date('Y-m-d') . '" AND "' . $mes[1] . '" AND baixado = "0"';

                                } else {

                                    if ($situacao == 'atrasado') {

                                        $where = 'data_vencimento BETWEEN "' . $mes[0] . '" AND "' . date('Y-m-d') . '" AND baixado = "0"';

                                    } else {

                                        if ($situacao == 'realizado') {

                                            $where = 'data_vencimento BETWEEN "' . $mes[0] . '" AND "' . $mes[1] . '" AND baixado = "1"';

                                        } else {

                                            $where = 'data_vencimento BETWEEN "' . $mes[0] . '" AND "' . $mes[1] . '" AND baixado = "0"';

                                        }

                                    }

                                }

                            }

                        }

                        // busca lançamentos do ano

                        else {

                            $ano = $this->getThisYear();

                            if (!isset($situacao) || $situacao == 'todos') {

                                $where = 'data_vencimento BETWEEN "' . $ano[0] . '" AND "' . $ano[1] . '"';

                            } else {

                                if ($situacao == 'previsto') {

                                    $where = 'data_vencimento BETWEEN "' . date('Y-m-d') . '" AND "' . $ano[1] . '" AND baixado = "0"';

                                } else {

                                    if ($situacao == 'atrasado') {

                                        $where = 'data_vencimento BETWEEN "' . $ano[0] . '" AND "' . date('Y-m-d') . '" AND baixado = "0"';

                                    } else {

                                        if ($situacao == 'realizado') {

                                            $where = 'data_vencimento BETWEEN "' . $ano[0] . '" AND "' . $ano[1] . '" AND baixado = "1"';

                                        } else {

                                            $where = 'data_vencimento BETWEEN "' . $ano[0] . '" AND "' . $ano[1] . '" AND baixado = "0"';

                                        }

                                    }

                                }

                            }

                        }

                    }

                }

            }

        }

        $this->load->library('pagination');

        $config['base_url'] = base_url() . 'financeiro/lancamentos';

        $config['total_rows'] = $this->financeiro_model->count('lancamentos');

        $config['per_page'] = 1000;

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

        $this->pagination->initialize($config);

        $where = $where . $whereResponsavel;

        $this->data['results'] = $this->financeiro_model->get('lancamentos', 'idLancamentos,descricao,valor,data_vencimento,data_pagamento,baixado,cliente_fornecedor,tipo,forma_pgto,usuarios_id', $where, $config['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'financeiro/lancamentos';

        $this->load->view('tema/topo', $this->data);

    }

    public function folha()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vLancamento')) {

            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar lançamentos.');

            redirect(base_url());

        }

        $where = '';
        $recurso = $this->input->get('recurso');
        $dataInicial = $this->input->get('dataInicial');
        $dataFinal = $this->input->get('dataFinal');
        $periodo = $this->input->get('periodo');
        $situacao = "todos";

        //busca uma data específica

        if ($dataInicial != "") {

            if ($dataFinal != "") {
                $where = 'data_vencimento BETWEEN "' . $dataInicial . '" AND "' . $dataFinal . '" AND cliente_fornecedor = "' . $recurso . '"';
            } else {
                $where = 'data_vencimento = "' . $dataInicial . '" AND cliente_fornecedor = "' . $recurso . '"';
            }

        } else {

            // busca todos os lançamentos

            if ($periodo == 'todos') {

                $where = 'cliente_fornecedor = "' . $recurso . '"';

            } else {

                // busca lançamentos do dia

                if ($periodo == null || $periodo == 'dia') {

                    $where = 'cliente_fornecedor = "' . $recurso . '" AND data_vencimento = "' . date('Y-m-d' . '"');

                } // fim lançamentos dia

                else {

                    // busca lançamentos da semana

                    if ($periodo == 'semana') {

                        $semana = $this->getThisWeek();

                        if (!isset($situacao) || $situacao == 'todos') {

                            $where = 'cliente_fornecedor = "' . $recurso . '" AND data_vencimento BETWEEN "' . $semana[0] . '" AND "' . $semana[1] . '"';

                        } else {

                            if ($situacao == 'previsto') {

                                $where = 'cliente_fornecedor = "' . $recurso . '" AND data_vencimento BETWEEN "' . date('Y-m-d') . '" AND "' . $semana[1] . '" AND baixado = "0"';

                            } else {

                                if ($situacao == 'atrasado') {

                                    $where = 'cliente_fornecedor = "' . $recurso . '" AND data_vencimento BETWEEN "' . $semana[0] . '" AND "' . date('Y-m-d') . '" AND baixado = "0"';

                                } else {

                                    if ($situacao == 'realizado') {

                                        $where = 'cliente_fornecedor = "' . $recurso . '" AND data_vencimento BETWEEN "' . $semana[0] . '" AND "' . $semana[1] . '" AND baixado = "1"';

                                    } else {

                                        $where = 'cliente_fornecedor = "' . $recurso . '" AND data_vencimento BETWEEN "' . $semana[0] . '" AND "' . $semana[1] . '" AND baixado = "0"';

                                    }

                                }

                            }

                        }

                    } // fim lançamentos dia

                    else {

                        // busca lançamento do mês

                        if ($periodo == 'mes') {

                            $mes = $this->getThisMonth();

                            if (!isset($situacao) || $situacao == 'todos') {

                                $where = 'cliente_fornecedor = "' . $recurso . '" AND data_vencimento BETWEEN "' . $mes[0] . '" AND "' . $mes[1] . '"';

                            } else {

                                if ($situacao == 'previsto') {

                                    $where = 'cliente_fornecedor = "' . $recurso . '" AND data_vencimento BETWEEN "' . date('Y-m-d') . '" AND "' . $mes[1] . '" AND baixado = "0"';

                                } else {

                                    if ($situacao == 'atrasado') {

                                        $where = 'cliente_fornecedor = "' . $recurso . '" AND data_vencimento BETWEEN "' . $mes[0] . '" AND "' . date('Y-m-d') . '" AND baixado = "0"';

                                    } else {

                                        if ($situacao == 'realizado') {

                                            $where = 'cliente_fornecedor = "' . $recurso . '" AND data_vencimento BETWEEN "' . $mes[0] . '" AND "' . $mes[1] . '" AND baixado = "1"';

                                        } else {

                                            $where = 'cliente_fornecedor = "' . $recurso . '" AND data_vencimento BETWEEN "' . $mes[0] . '" AND "' . $mes[1] . '" AND baixado = "0"';

                                        }

                                    }

                                }

                            }

                        }

                        // busca lançamentos do ano

                        else {

                            $ano = $this->getThisYear();

                            if (!isset($situacao) || $situacao == 'todos') {

                                $where = 'cliente_fornecedor = "' . $recurso . '" AND data_vencimento BETWEEN "' . $ano[0] . '" AND "' . $ano[1] . '"';

                            } else {

                                if ($situacao == 'previsto') {

                                    $where = 'cliente_fornecedor = "' . $recurso . '" AND data_vencimento BETWEEN "' . date('Y-m-d') . '" AND "' . $ano[1] . '" AND baixado = "0"';

                                } else {

                                    if ($situacao == 'atrasado') {

                                        $where = 'cliente_fornecedor = "' . $recurso . '" AND data_vencimento BETWEEN "' . $ano[0] . '" AND "' . date('Y-m-d') . '" AND baixado = "0"';

                                    } else {

                                        if ($situacao == 'realizado') {

                                            $where = 'cliente_fornecedor = "' . $recurso . '" AND data_vencimento BETWEEN "' . $ano[0] . '" AND "' . $ano[1] . '" AND baixado = "1"';

                                        } else {

                                            $where = 'cliente_fornecedor = "' . $recurso . '" AND data_vencimento BETWEEN "' . $ano[0] . '" AND "' . $ano[1] . '" AND baixado = "0"';

                                        }

                                    }

                                }

                            }

                        }

                    }

                }

            }

        }

        $this->load->library('pagination');

        $config['base_url'] = base_url() . 'financeiro/folha';

        $config['total_rows'] = $this->financeiro_model->count('lancamentos');

        $config['per_page'] = 1000;

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

        $this->pagination->initialize($config);

        $this->data['results'] = $this->financeiro_model->get('lancamentos', 'idLancamentos,descricao,valor,data_vencimento,data_pagamento,baixado,cliente_fornecedor,tipo,forma_pgto,usuarios_id', $where, $config['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'financeiro/folha';

        $this->load->view('tema/topo', $this->data);

    }

    public function adicionarReceita()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aLancamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar lançamentos.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';
        $urlAtual = $this->input->post('urlAtual');

        if ($this->form_validation->run('receita') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $vencimento = $this->input->post('vencimento');
            $recebimento = $this->input->post('recebimento');

            //aplica desconto dos juros das operadoras de cartão
            $formaPgto = $this->input->post('formaPgto');

            //visa débito
            $offvisa = ($this->input->post('valor') * 2.46) / 100;
            $valorvisadebito = $this->input->post('valor') - $offvisa;
            // master débito
            $offmaster = ($this->input->post('valor') * 2.46) / 100;
            $valormasterdebito = $this->input->post('valor') - $offmaster;
            //elo débito
            $offelo = ($this->input->post('valor') * 2.45) / 100;
            $valorelodebito = $this->input->post('valor') - $offelo;

            if ($recebimento != null) {
                $recebimento = explode('/', $recebimento);
                $recebimento = $recebimento[2] . '-' . $recebimento[1] . '-' . $recebimento[0];
            }

            if ($vencimento == null) {
                $vencimento = date('d/m/Y');
            }

            try {
                $vencimento = explode('/', $vencimento);
                $vencimento = $vencimento[2] . '-' . $vencimento[1] . '-' . $vencimento[0];
            } catch (Exception $e) {
                $vencimento = date('Y/m/d');
            }

            if ($formaPgto == "Débito - Visa") {
                $valor = $valorvisadebito;
            } elseif ($formaPgto == "Débito - Master") {
                $valor = $valormasterdebito;
            } elseif ($formaPgto == "Débito - Elo") {
                $valor = $valorelodebito;
            } else {
                $valor = $this->input->post('valor');
            }

            $data = array(
                'descricao' => set_value('descricao'),
                'valor' => $valor,
                'data_vencimento' => $vencimento,
                'data_pagamento' => $recebimento != null ? $recebimento : date('Y-m-d'),
                'baixado' => $this->input->post('recebido'),
                'cliente_fornecedor' => set_value('cliente'),
                'forma_pgto' => $this->input->post('formaPgto'),
                'tipo' => set_value('tipo'),
                'usuarios_id' => $this->input->post('usuarios_id'),
            );

            if ($this->financeiro_model->add('lancamentos', $data) == true) {
                $this->session->set_flashdata('success', 'Receita adicionada com sucesso!');
                redirect($urlAtual);
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }

        $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar adicionar receita.');
        redirect($urlAtual);

    }

    public function adicionarDespesa()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aLancamento')) {

            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar lançamentos.');

            redirect(base_url());

        }

        $this->load->library('form_validation');

        $this->data['custom_error'] = '';

        $urlAtual = $this->input->post('urlAtual');

        if ($this->form_validation->run('despesa') == false) {

            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);

        } else {

            $vencimento = $this->input->post('vencimento');

            $pagamento = $this->input->post('pagamento');

            if ($pagamento != null) {

                $pagamento = explode('/', $pagamento);

                $pagamento = $pagamento[2] . '-' . $pagamento[1] . '-' . $pagamento[0];

            }

            if ($vencimento == null) {

                $vencimento = date('d/m/Y');

            }

            try {

                $vencimento = explode('/', $vencimento);

                $vencimento = $vencimento[2] . '-' . $vencimento[1] . '-' . $vencimento[0];

            } catch (Exception $e) {

                $vencimento = date('Y/m/d');

            }

            $data = array(

                'descricao' => set_value('descricao'),

                'valor' => set_value('valor'),

                'data_vencimento' => $vencimento,

                'data_pagamento' => $pagamento != null ? $pagamento : date('Y-m-d'),

                'baixado' => $this->input->post('pago'),

                'cliente_fornecedor' => set_value('fornecedor'),

                'forma_pgto' => $this->input->post('formaPgto'),

                'tipo' => set_value('tipo'),

            );

            if ($this->financeiro_model->add('lancamentos', $data) == true) {

                $this->session->set_flashdata('success', 'Despesa adicionada com sucesso!');

                redirect($urlAtual);

            } else {

                $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar adicionar despesa!');

                redirect($urlAtual);

            }

        }

        $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar adicionar despesa.');

        redirect($urlAtual);

    }

    public function editar()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eLancamento')) {

            $this->session->set_flashdata('error', 'Você não tem permissão para editar lançamentos.');

            redirect(base_url());

        }

        $this->load->library('form_validation');

        $this->data['custom_error'] = '';

        $urlAtual = $this->input->post('urlAtual');

        $this->form_validation->set_rules('descricao', '', 'trim|required|xss_clean');

        $this->form_validation->set_rules('fornecedor', '', 'trim|required|xss_clean');

        $this->form_validation->set_rules('valor', '', 'trim|required|xss_clean');

        $this->form_validation->set_rules('vencimento', '', 'trim|required|xss_clean');

        $this->form_validation->set_rules('pagamento', '', 'trim|xss_clean');

        if ($this->form_validation->run() == false) {

            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);

        } else {

            $vencimento = $this->input->post('vencimento');

            $pagamento = $this->input->post('pagamento');

            try {

                $vencimento = explode('/', $vencimento);

                $vencimento = $vencimento[2] . '-' . $vencimento[1] . '-' . $vencimento[0];

                $pagamento = explode('/', $pagamento);

                $pagamento = $pagamento[2] . '-' . $pagamento[1] . '-' . $pagamento[0];

            } catch (Exception $e) {

                $vencimento = date('Y/m/d');

            }

            $data = array(

                'descricao' => $this->input->post('descricao'),

                'valor' => $this->input->post('valor'),

                'data_vencimento' => $vencimento,

                'data_pagamento' => $pagamento,

                'baixado' => $this->input->post('pago'),

                'cliente_fornecedor' => $this->input->post('fornecedor'),

                'forma_pgto' => $this->input->post('formaPgto'),

                'tipo' => $this->input->post('tipo'),

                'usuarios_id' => $this->input->post('usuarios_id'),

            );

            if ($this->financeiro_model->edit('lancamentos', $data, 'idLancamentos', $this->input->post('id')) == true) {

                $this->session->set_flashdata('success', 'lançamento editado com sucesso!');

                redirect($urlAtual);

            } else {

                $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar editar lançamento!');

                redirect($urlAtual);

            }

        }

        $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar editar lançamento.');

        redirect($urlAtual);

        $data = array(

            'descricao' => $this->input->post('descricao'),

            'valor' => $this->input->post('valor'),

            'data_vencimento' => $this->input->post('vencimento'),

            'data_pagamento' => $this->input->post('pagamento'),

            'baixado' => $this->input->post('pago'),

            'cliente_fornecedor' => set_value('fornecedor'),

            'forma_pgto' => $this->input->post('formaPgto'),

            'tipo' => $this->input->post('tipo'),

            'usuarios_id' => $this->input->post('usuarios_id'),

        );

        print_r($data);

    }

    public function excluirLancamento()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'dLancamento')) {

            $this->session->set_flashdata('error', 'Você não tem permissão para excluir lançamentos.');

            redirect(base_url());

        }

        $id = $this->input->post('id');

        if ($id == null || !is_numeric($id)) {

            $json = array('result' => false);

            echo json_encode($json);

        } else {

            $result = $this->financeiro_model->delete('lancamentos', 'idLancamentos', $id);

            if ($result) {

                $json = array('result' => true);

                echo json_encode($json);

            } else {

                $json = array('result' => false);

                echo json_encode($json);

            }

        }

    }

    protected function getThisYear()
    {

        $dias = date("z");

        $primeiro = date("Y-m-d", strtotime("-" . ($dias) . " day"));

        $ultimo = date("Y-m-d", strtotime("+" . (364 - $dias) . " day"));

        return array($primeiro, $ultimo);

    }

    protected function getThisWeek()
    {

        return array(date("Y/m/d", strtotime("last sunday", strtotime("now"))), date("Y/m/d", strtotime("next saturday", strtotime("now"))));

    }

    protected function getLastSevenDays()
    {

        return array(date("Y-m-d", strtotime("-7 day", strtotime("now"))), date("Y-m-d", strtotime("now")));

    }

    protected function getThisMonth()
    {

        $mes = date('m');

        $ano = date('Y');

        $qtdDiasMes = date('t');

        $inicia = $ano . "-" . $mes . "-01";

        $ate = $ano . "-" . $mes . "-" . $qtdDiasMes;

        return array($inicia, $ate);

    }

}
