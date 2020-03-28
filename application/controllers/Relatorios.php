<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Relatorios extends MY_Controller
{

    /**
     * author: Ramon Silva
     * email: silva018-mg@yahoo.com.br
     *
     */

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Relatorios_model');
        $this->load->model('Usuarios_model');
        $this->load->model('Mapos_model');

        $this->data['menuRelatorios'] = 'Relatórios';
    }

    public function index()
    {
        redirect(base_url());
    }

    public function clientes()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de clientes.');
            redirect(base_url());
        }
        $this->data['view'] = 'relatorios/rel_clientes';
        return $this->layout();
    }

    public function produtos()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de produtos.');
            redirect(base_url());
        }
        $this->data['view'] = 'relatorios/rel_produtos';
        return $this->layout();
    }

    public function clientesCustom()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de clientes.');
            redirect(base_url());
        }

        $dataInicial = $this->input->get('dataInicial');
        $dataFinal = $this->input->get('dataFinal');

        $data['title'] = 'Relatório de Clientes Costumizado';
        $data['dataInicial'] = date('d/m/Y', strtotime($dataInicial));
        $data['dataFinal'] = date('d/m/Y', strtotime($dataFinal));

        $data['clientes'] = $this->Relatorios_model->clientesCustom($dataInicial, $dataFinal);
        $data['emitente'] = $this->Mapos_model->getEmitente();
        $data['topo'] = $this->load->view('relatorios/imprimir/imprimirTopo', $data, true);

        $this->load->helper('mpdf');
        //$this->load->view('relatorios/imprimir/imprimirClientes', $data);
        $html = $this->load->view('relatorios/imprimir/imprimirClientes', $data, true);
        pdf_create($html, 'relatorio_clientes' . date('d/m/y'), true);
    }

    public function clientesRapid()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de clientes.');
            redirect(base_url());
        }

        $data['title'] = 'Relatório de Clientes';
        $data['clientes'] = $this->Relatorios_model->clientesRapid();
        $data['emitente'] = $this->Mapos_model->getEmitente();
        $data['topo'] = $this->load->view('relatorios/imprimir/imprimirTopo', $data, true);

        $this->load->helper('mpdf');

        $html = $this->load->view('relatorios/imprimir/imprimirClientes', $data, true);
        pdf_create($html, 'relatorio_clientes' . date('d/m/y'), true);
    }

    public function produtosRapid()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de produtos.');
            redirect(base_url());
        }

        $data['produtos'] = $this->Relatorios_model->produtosRapid();

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirProdutos', $data, true);
        pdf_create($html, 'relatorio_produtos' . date('d/m/y'), true);
    }

    public function produtosRapidMin()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de produtos.');
            redirect(base_url());
        }

        $data['produtos'] = $this->Relatorios_model->produtosRapidMin();

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirProdutos', $data, true);
        pdf_create($html, 'relatorio_produtos' . date('d/m/y'), true);
    }

    public function produtosCustom()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de produtos.');
            redirect(base_url());
        }

        $precoInicial = $this->input->get('precoInicial');
        $precoFinal = $this->input->get('precoFinal');
        $estoqueInicial = $this->input->get('estoqueInicial');
        $estoqueFinal = $this->input->get('estoqueFinal');

        $data['produtos'] = $this->Relatorios_model->produtosCustom($precoInicial, $precoFinal, $estoqueInicial, $estoqueFinal);

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirProdutos', $data, true);
        pdf_create($html, 'relatorio_produtos' . date('d/m/y'), true);
    }

    public function produtosEtiquetas()
    {
        $de = $this->input->get('de_id');
        $ate = $this->input->get('ate_id');

        if ($de <= $ate) {
            $data['produtos'] = $this->Relatorios_model->produtosEtiquetas($de, $ate);
            $this->load->helper('mpdf');
            $html = $this->load->view('relatorios/imprimir/imprimirEtiquetas', $data, true);
            pdf_create($html, 'etiquetas_' . $de . '_' . $ate, true);
        } else {
            $this->session->set_flashdata('error', 'O campo "<b>De</b>" não pode ser maior doque o campo "<b>Até</b>"!');
            redirect('produtos');
        }
    }

    public function servicos()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rServico')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de serviços.');
            redirect(base_url());
        }
        $this->data['view'] = 'relatorios/rel_servicos';
        return $this->layout();
    }

    public function servicosCustom()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rServico')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de serviços.');
            redirect(base_url());
        }

        $precoInicial = $this->input->get('precoInicial');
        $precoFinal = $this->input->get('precoFinal');
        $data['servicos'] = $this->Relatorios_model->servicosCustom($precoInicial, $precoFinal);
        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirServicos', $data, true);
        pdf_create($html, 'relatorio_servicos' . date('d/m/y'), true);
    }

    public function servicosRapid()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rServico')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de serviços.');
            redirect(base_url());
        }

        $data['servicos'] = $this->Relatorios_model->servicosRapid();

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirServicos', $data, true);
        pdf_create($html, 'relatorio_servicos' . date('d/m/y'), true);
    }

    public function os()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de OS.');
            redirect(base_url());
        }
        $this->data['view'] = 'relatorios/rel_os';
        return $this->layout();
    }

    public function osRapid()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de OS.');
            redirect(base_url());
        }

        $data['os'] = $this->Relatorios_model->osRapid();
        $data['title'] = 'Relatório de OS';

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirOs', $data, true);
        pdf_create($html, 'relatorio_os' . date('d/m/y'), true, true);
    }

    public function osCustom()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de OS.');
            redirect(base_url());
        }

        $dataInicial = $this->input->get('dataInicial');
        $dataFinal = $this->input->get('dataFinal');
        $cliente = $this->input->get('cliente');
        $responsavel = $this->input->get('responsavel');
        $status = $this->input->get('status');

        $this->load->helper('mpdf');

        $title = $status == null ? 'Todas' : $status;
        $user = $responsavel == null ? 'Não foi selecionado' : $this->Usuarios_model->get(1, intval($responsavel) - 1);

        $os = $this->Relatorios_model->osCustom($dataInicial, $dataFinal, $cliente, $responsavel, $status);
        $emitente = $this->Mapos_model->getEmitente();
        $usuario = is_array($user) ? $user[0]->nome : $user;

        $data['title'] = 'Relatório de OS - ' . $title;
        $data['os'] = $os;
        $data['res_nome'] = $usuario;

        $data['dataInicial'] = $dataInicial != null ? date('d-m-Y', strtotime($dataInicial)) : 'indefinida';
        $data['dataFinal'] = $dataFinal != null ? date('d-m-Y', strtotime($dataFinal)) : 'indefinida';

        if ($emitente) {
            $data['em_nome'] = $emitente[0]->nome;
            $data['em_cnpj'] = $emitente[0]->cnpj;
            $data['em_logo'] = $emitente[0]->url_logo;
            $data['topo'] = $this->load->view('relatorios/rel_os_topo', $data, true);
        }

        $html = $this->load->view('relatorios/imprimir/imprimirOs', $data, true);
        pdf_create($html, 'relatorio_os' . date('d/m/y'), true, true);
    }

    public function financeiro()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios financeiros.');
            redirect(base_url());
        }

        $this->data['view'] = 'relatorios/rel_financeiro';
        return $this->layout();
    }

    public function financeiroRapid()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios financeiros.');
            redirect(base_url());
        }

        $data['lancamentos'] = $this->Relatorios_model->financeiroRapid();

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirFinanceiro', $data, true);
        pdf_create($html, 'relatorio_os' . date('d/m/y'), true);
    }

    public function financeiroCustom()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios financeiros.');
            redirect(base_url());
        }

        $dataInicial = $this->input->get('dataInicial');
        $dataFinal = $this->input->get('dataFinal');
        $tipo = $this->input->get('tipo');
        $situacao = $this->input->get('situacao');

        $data['lancamentos'] = $this->Relatorios_model->financeiroCustom($dataInicial, $dataFinal, $tipo, $situacao);

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirFinanceiro', $data, true);
        pdf_create($html, 'relatorio_financeiro' . date('d/m/y'), true);
    }

    public function vendas()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de vendas.');
            redirect(base_url());
        }

        $this->data['view'] = 'relatorios/rel_vendas';
        return $this->layout();
    }

    public function vendasRapid()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de vendas.');
            redirect(base_url());
        }
        $data['vendas'] = $this->Relatorios_model->vendasRapid();

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirVendas', $data, true);
        pdf_create($html, 'relatorio_vendas' . date('d/m/y'), true);
    }

    public function vendasCustom()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rVenda')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de vendas.');
            redirect(base_url());
        }
        $dataInicial = $this->input->get('dataInicial');
        $dataFinal = $this->input->get('dataFinal');
        $cliente = $this->input->get('cliente');
        $responsavel = $this->input->get('responsavel');

        $data['vendas'] = $this->Relatorios_model->vendasCustom($dataInicial, $dataFinal, $cliente, $responsavel);
        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirVendas', $data, true);
        pdf_create($html, 'relatorio_vendas' . date('d/m/y'), true);
    }
}
