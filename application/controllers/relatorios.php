<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Relatorios extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        if ((!$this->session->userdata('session_id')) || (!$this->session->userdata('logado'))) {
            redirect('mapos/login');

        }

        $this->load->model('Relatorios_model', '', true);
        $this->data['menuRelatorios'] = 'Relatórios';

    }

    public function clientes()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rCliente')) {

            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de clientes.');
            redirect(base_url());

        }

        $this->data['view'] = 'relatorios/rel_clientes';
        $this->load->view('tema/topo', $this->data);

    }

    public function produtos()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rProduto')) {

            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de produtos.');
            redirect(base_url());

        }

        $this->data['view'] = 'relatorios/rel_produtos';
        $this->load->view('tema/topo', $this->data);

    }

    public function clientesCustom()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rCliente')) {

            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de clientes.');
            redirect(base_url());

        }

        $dataInicial = $this->input->get('dataInicial');
        $dataFinal = $this->input->get('dataFinal');
        $data['clientes'] = $this->Relatorios_model->clientesCustom($dataInicial, $dataFinal);

        $this->load->helper('mpdf');

        $html = $this->load->view('relatorios/imprimir/imprimirClientes', $data, true);
        pdf_create($html, 'relatorio_clientes' . date('d/m/y'), true);

    }

    public function clientesRapid()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rCliente')) {

            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de clientes.');
            redirect(base_url());

        }

        $data['clientes'] = $this->Relatorios_model->clientesRapid();

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

    public function servicos()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rServico')) {

            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de serviços.');
            redirect(base_url());

        }

        $this->data['view'] = 'relatorios/rel_servicos';
        $this->load->view('tema/topo', $this->data);

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
        $this->load->view('tema/topo', $this->data);

    }

    public function osRapid()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rOs')) {

            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de OS.');

            redirect(base_url());

        }

        $data['os'] = $this->Relatorios_model->osRapid();

        $this->load->helper('mpdf');

        $html = $this->load->view('relatorios/imprimir/imprimirOs', $data, true);

        pdf_create($html, 'relatorio_os' . date('d/m/y'), true);

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

        $data['os'] = $this->Relatorios_model->osCustom($dataInicial, $dataFinal, $cliente, $responsavel, $status);

        $this->load->view('relatorios/imprimir/imprimirOs', $data);

    }

    public function financeiro()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) {

            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios financeiros.');
            redirect(base_url());

        }

        $this->data['view'] = 'relatorios/rel_financeiro';
        $this->load->view('tema/topo', $this->data);

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

        $this->load->view('relatorios/imprimir/imprimirFinanceiro', $data);

    }

    public function vendas()
    {

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'rVenda')) {

            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de vendas.');
            redirect(base_url());

        }

        $this->data['view'] = 'relatorios/rel_vendas';
        $this->load->view('tema/topo', $this->data);

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
