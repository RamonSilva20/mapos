<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Propostas extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
        $this->load->model('propostas_model');
        $this->data['menuPropostas'] = 'Propostas';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vPropostas')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar propostas.');
            redirect(base_url());
        }

        $this->load->library('pagination');
        $this->load->model('mapos_model');

        $where_array = [];

        $pesquisa = $this->input->get('pesquisa');
        $status = $this->input->get('status');
        $inputDe = $this->input->get('data');
        $inputAte = $this->input->get('data2');

        if ($pesquisa) {
            $where_array['pesquisa'] = $pesquisa;
        }
        if ($status) {
            $where_array['status'] = is_array($status) ? $status : [$status];
        }
        if ($inputDe) {
            $de = explode('/', $inputDe);
            $de = $de[2] . '-' . $de[1] . '-' . $de[0];
            $where_array['de'] = $de;
        }
        if ($inputAte) {
            $ate = explode('/', $inputAte);
            $ate = $ate[2] . '-' . $ate[1] . '-' . $ate[0];
            $where_array['ate'] = $ate;
        }

        $this->data['configuration']['base_url'] = site_url('propostas/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->propostas_model->count('propostas');
        if (count($where_array) > 0) {
            $this->data['configuration']['suffix'] = "?pesquisa={$pesquisa}&status={$status}&data={$inputDe}&data2={$inputAte}";
            $this->data['configuration']['first_url'] = base_url("index.php/propostas/gerenciar") . "?pesquisa={$pesquisa}&status={$status}&data={$inputDe}&data2={$inputAte}";
        }

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->propostas_model->getPropostas(
            $where_array,
            $this->data['configuration']['per_page'],
            $this->uri->segment(3)
        );

        $this->data['emitente'] = $this->mapos_model->getEmitente();
        $this->data['view'] = 'propostas/propostas';

        return $this->layout();
    }

    public function adicionar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aPropostas')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar propostas.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('propostas') == false) {
            $this->data['custom_error'] = (validation_errors() ? true : false);
        } else {
            $dataProposta = $this->input->post('data_proposta');
            $dataValidade = $this->input->post('data_validade');

            try {
                $dataProposta = explode('/', $dataProposta);
                $dataProposta = $dataProposta[2] . '-' . $dataProposta[1] . '-' . $dataProposta[0];

                if ($dataValidade) {
                    $dataValidade = explode('/', $dataValidade);
                    $dataValidade = $dataValidade[2] . '-' . $dataValidade[1] . '-' . $dataValidade[0];
                }
            } catch (Exception $e) {
                $dataProposta = date('Y-m-d');
            }

            $numeroProposta = $this->propostas_model->gerarNumeroProposta();

            // Cliente pode ser ID cadastrado ou apenas nome
            $clienteId = $this->input->post('clientes_id');
            $clienteNome = $this->input->post('cliente');
            
            // Se não tem ID, usar apenas o nome
            if (empty($clienteId) || !is_numeric($clienteId)) {
                $clienteId = null;
            } else {
                // Se tem ID, não precisa do nome
                $clienteNome = null;
            }
            
            // Processar desconto
            $desconto = $this->input->post('desconto');
            if ($desconto) {
                $desconto = str_replace(['.', ','], ['', '.'], $desconto);
            }
            $desconto = floatval($desconto) ?: 0;

            $data = [
                'numero_proposta' => $numeroProposta,
                'data_proposta' => $dataProposta,
                'data_validade' => $dataValidade ?: null,
                'status' => $this->input->post('status') ?: 'Rascunho',
                'clientes_id' => $clienteId,
                'cliente_nome' => $clienteNome,
                'usuarios_id' => $this->input->post('usuarios_id') ?: $this->session->userdata('id_admin'),
                'observacoes' => $this->input->post('observacoes'),
                'desconto' => $desconto,
                'valor_desconto' => $this->input->post('valor_desconto') ?: 0,
                'tipo_desconto' => $this->input->post('tipo_desconto'),
                'valor_total' => $this->input->post('valor_total') ?: 0,
                'tipo_cond_comerc' => $this->input->post('tipo_cond_comerc') ?: 'N',
                'cond_comerc_texto' => $this->input->post('cond_comerc_texto'),
                'validade_dias' => $this->input->post('validade_dias') ?: null,
                'prazo_entrega' => $this->input->post('prazo_entrega'),
            ];

            if (is_numeric($id = $this->propostas_model->add('propostas', $data, true))) {
                $idProposta = $id;

                // Salvar produtos
                $this->salvarProdutos($idProposta);

                // Salvar serviços
                $this->salvarServicos($idProposta);

                // Salvar parcelas
                $this->salvarParcelas($idProposta);

                // Salvar outros produtos/serviços
                $this->salvarOutros($idProposta);

                log_info('Adicionou uma Proposta. ID: ' . $id);
                $this->session->set_flashdata('success', 'Proposta adicionada com sucesso!');
                redirect(site_url('propostas/gerenciar/'));
            } else {
                $this->data['custom_error'] = '<div class="alert">Ocorreu um erro.</div>';
            }
        }

        $this->data['view'] = 'propostas/adicionarProposta';
        return $this->layout();
    }

    public function editar()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3)) || !$this->propostas_model->getById($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Proposta não encontrada ou parâmetro inválido.');
            redirect('propostas/gerenciar');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'ePropostas')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar propostas.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $result = $this->propostas_model->getById($this->uri->segment(3));
        $this->data['result'] = $result;

        // Carregar produtos, serviços, parcelas e outros
        $this->data['produtos'] = $this->propostas_model->getProdutos($result->idProposta);
        $this->data['servicos'] = $this->propostas_model->getServicos($result->idProposta);
        $this->data['parcelas'] = $this->propostas_model->getParcelas($result->idProposta);
        $this->data['outros'] = $this->propostas_model->getOutros($result->idProposta);

        if ($this->form_validation->run('propostas') == false) {
            $this->data['custom_error'] = (validation_errors() ? true : false);
        } else {
            $dataProposta = $this->input->post('data_proposta');
            $dataValidade = $this->input->post('data_validade');

            try {
                $dataProposta = explode('/', $dataProposta);
                $dataProposta = $dataProposta[2] . '-' . $dataProposta[1] . '-' . $dataProposta[0];

                if ($dataValidade) {
                    $dataValidade = explode('/', $dataValidade);
                    $dataValidade = $dataValidade[2] . '-' . $dataValidade[1] . '-' . $dataValidade[0];
                }
            } catch (Exception $e) {
                $dataProposta = date('Y-m-d');
            }

            // Cliente pode ser ID cadastrado ou apenas nome
            $clienteId = $this->input->post('clientes_id');
            $clienteNome = $this->input->post('cliente');
            
            // Se não tem ID, usar apenas o nome
            if (empty($clienteId) || !is_numeric($clienteId)) {
                $clienteId = null;
            } else {
                // Se tem ID, não precisa do nome
                $clienteNome = null;
            }

            // Processar desconto
            $desconto = $this->input->post('desconto');
            if ($desconto) {
                $desconto = str_replace(['.', ','], ['', '.'], $desconto);
            }
            $desconto = floatval($desconto) ?: 0;

            $data = [
                'data_proposta' => $dataProposta,
                'data_validade' => $dataValidade ?: null,
                'status' => $this->input->post('status'),
                'clientes_id' => $clienteId,
                'cliente_nome' => $clienteNome,
                'usuarios_id' => $this->input->post('usuarios_id'),
                'observacoes' => $this->input->post('observacoes'),
                'desconto' => $desconto,
                'valor_desconto' => $this->input->post('valor_desconto') ?: 0,
                'tipo_desconto' => $this->input->post('tipo_desconto'),
                'valor_total' => $this->input->post('valor_total') ?: 0,
                'tipo_cond_comerc' => $this->input->post('tipo_cond_comerc') ?: 'N',
                'cond_comerc_texto' => $this->input->post('cond_comerc_texto'),
                'validade_dias' => $this->input->post('validade_dias') ?: null,
                'prazo_entrega' => $this->input->post('prazo_entrega'),
            ];

            if ($this->propostas_model->edit('propostas', $data, 'idProposta', $this->input->post('idProposta')) == true) {
                $idProposta = $this->input->post('idProposta');

                // Deletar e recriar produtos, serviços, parcelas e outros
                $this->db->where('proposta_id', $idProposta);
                $this->db->delete('produtos_proposta');

                $this->db->where('proposta_id', $idProposta);
                $this->db->delete('servicos_proposta');

                $this->db->where('proposta_id', $idProposta);
                $this->db->delete('parcelas_proposta');

                $this->db->where('proposta_id', $idProposta);
                $this->db->delete('outros_proposta');

                // Salvar produtos
                $this->salvarProdutos($idProposta);

                // Salvar serviços
                $this->salvarServicos($idProposta);

                // Salvar parcelas
                $this->salvarParcelas($idProposta);

                // Salvar outros produtos/serviços
                $this->salvarOutros($idProposta);

                log_info('Editou uma Proposta. ID: ' . $idProposta);
                $this->session->set_flashdata('success', 'Proposta editada com sucesso!');
                redirect(site_url('propostas/gerenciar/'));
            } else {
                $this->data['custom_error'] = '<div class="alert">Ocorreu um erro.</div>';
            }
        }

        $this->data['view'] = 'propostas/editarProposta';
        return $this->layout();
    }

    private function salvarProdutos($idProposta)
    {
        $produtos = json_decode($this->input->post('produtos_json'), true);
        if (is_array($produtos) && count($produtos) > 0) {
            foreach ($produtos as $produto) {
                if (empty($produto['descricao'])) continue;

                // Converter preço corretamente - já vem como número do JavaScript
                $preco = isset($produto['preco']) ? floatval($produto['preco']) : 0;
                $quantidade = floatval($produto['quantidade'] ?? 1);
                $subtotal = $preco * $quantidade;

                $this->db->insert('produtos_proposta', [
                    'proposta_id' => $idProposta,
                    'produtos_id' => !empty($produto['produtos_id']) ? $produto['produtos_id'] : null,
                    'descricao' => $produto['descricao'],
                    'quantidade' => $quantidade,
                    'preco' => $preco,
                    'subtotal' => $subtotal,
                ]);
            }
        }
    }

    private function salvarServicos($idProposta)
    {
        $servicos = json_decode($this->input->post('servicos_json'), true);
        if (is_array($servicos) && count($servicos) > 0) {
            foreach ($servicos as $servico) {
                if (empty($servico['descricao'])) continue;

                // Converter preço corretamente - já vem como número do JavaScript
                $preco = isset($servico['preco']) ? floatval($servico['preco']) : 0;
                $quantidade = floatval($servico['quantidade'] ?? 1);
                $subtotal = $preco * $quantidade;

                $this->db->insert('servicos_proposta', [
                    'proposta_id' => $idProposta,
                    'servicos_id' => !empty($servico['servicos_id']) ? $servico['servicos_id'] : null,
                    'descricao' => $servico['descricao'],
                    'quantidade' => $quantidade,
                    'preco' => $preco,
                    'subtotal' => $subtotal,
                ]);
            }
        }
    }

    private function salvarParcelas($idProposta)
    {
        $parcelas = json_decode($this->input->post('parcelas_json'), true);
        if (is_array($parcelas) && count($parcelas) > 0) {
            foreach ($parcelas as $parcela) {
                // O valor já vem como número do JavaScript (ex: 293.49)
                // Se for string, pode estar formatada (ex: "293,49" ou "293.49")
                $valor = $parcela['valor'] ?? 0;
                if (is_string($valor)) {
                    // Se for string, remover formatação brasileira
                    $valor = str_replace('.', '', $valor); // Remove separador de milhar
                    $valor = str_replace(',', '.', $valor); // Converte vírgula para ponto
                }
                $valor = floatval($valor);
                
                $dias = intval($parcela['dias'] ?? 0);

                // Calcular data de vencimento
                $dataVencimento = null;
                if ($dias > 0) {
                    $dataProposta = $this->db->select('data_proposta')->where('idProposta', $idProposta)->get('propostas')->row()->data_proposta;
                    $dataVencimento = date('Y-m-d', strtotime($dataProposta . " +{$dias} days"));
                }

                $this->db->insert('parcelas_proposta', [
                    'proposta_id' => $idProposta,
                    'numero' => intval($parcela['numero'] ?? 1),
                    'dias' => $dias,
                    'valor' => $valor,
                    'observacao' => $parcela['observacao'] ?? null,
                    'data_vencimento' => $dataVencimento,
                ]);
            }
        }
    }

    private function salvarOutros($idProposta)
    {
        $descricaoOutros = $this->input->post('descricao_outros');
        $precoOutros = $this->input->post('preco_outros');
        if (!empty($descricaoOutros) || !empty($precoOutros)) {
            $preco = 0;
            if (!empty($precoOutros)) {
                $preco = str_replace('.', '', $precoOutros);
                $preco = str_replace(',', '.', $preco);
                $preco = floatval($preco);
            }

            $this->db->insert('outros_proposta', [
                'proposta_id' => $idProposta,
                'descricao' => $descricaoOutros ?: '',
                'preco' => $preco,
            ]);
        }
    }

    public function excluir()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'dPropostas')) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => false,
                'message' => 'Você não tem permissão para excluir propostas.'
            ]));
            return;
        }

        $id = $this->input->post('id');
        
        // Debug
        log_message('debug', 'Tentando excluir proposta. ID recebido: ' . var_export($id, true));
        log_message('debug', 'POST completo: ' . var_export($this->input->post(), true));
        
        if (empty($id) || !is_numeric($id)) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => false,
                'message' => 'ID da proposta inválido. ID recebido: ' . var_export($id, true)
            ]));
            return;
        }

        // Verificar se proposta existe
        $proposta = $this->propostas_model->getById($id);
        if (!$proposta) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => false,
                'message' => 'Proposta não encontrada.'
            ]));
            return;
        }

        $this->db->trans_start();

        // Excluir itens relacionados
        $this->db->where('proposta_id', $id);
        $this->db->delete('produtos_proposta');

        $this->db->where('proposta_id', $id);
        $this->db->delete('servicos_proposta');

        $this->db->where('proposta_id', $id);
        $this->db->delete('parcelas_proposta');

        $this->db->where('proposta_id', $id);
        $this->db->delete('outros_proposta');

        // Excluir a proposta
        $this->db->where('idProposta', $id);
        $this->db->delete('propostas');

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $error = $this->db->error();
            log_message('error', 'Erro ao excluir proposta ID ' . $id . ': ' . json_encode($error));
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => false,
                'message' => 'Erro ao excluir proposta: ' . ($error['message'] ?? 'Erro desconhecido')
            ]));
        } else {
            log_info('Removeu uma proposta. ID: ' . $id);
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => true,
                'message' => 'Proposta excluída com sucesso!'
            ]));
        }
    }

    public function visualizar()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Proposta não encontrada.');
            redirect('propostas/gerenciar');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vPropostas')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar propostas.');
            redirect(base_url());
        }

        $result = $this->propostas_model->getById($this->uri->segment(3));
        if (!$result) {
            $this->session->set_flashdata('error', 'Proposta não encontrada.');
            redirect('propostas/gerenciar');
        }

        $this->data['result'] = $result;
        $this->data['produtos'] = $this->propostas_model->getProdutos($result->idProposta);
        $this->data['servicos'] = $this->propostas_model->getServicos($result->idProposta);
        $this->data['parcelas'] = $this->propostas_model->getParcelas($result->idProposta);
        $this->data['outros'] = $this->propostas_model->getOutros($result->idProposta);
        $this->load->model('mapos_model');
        $this->data['emitente'] = $this->mapos_model->getEmitente();

        $this->data['view'] = 'propostas/visualizarProposta';
        return $this->layout();
    }

    public function imprimir()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Proposta não encontrada.');
            redirect('propostas/gerenciar');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vPropostas')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar propostas.');
            redirect(base_url());
        }

        $result = $this->propostas_model->getById($this->uri->segment(3));
        if (!$result) {
            $this->session->set_flashdata('error', 'Proposta não encontrada.');
            redirect('propostas/gerenciar');
        }

        $this->data['result'] = $result;
        $this->data['produtos'] = $this->propostas_model->getProdutos($result->idProposta);
        $this->data['servicos'] = $this->propostas_model->getServicos($result->idProposta);
        $this->data['parcelas'] = $this->propostas_model->getParcelas($result->idProposta);
        $this->data['outros'] = $this->propostas_model->getOutros($result->idProposta);
        $this->load->model('mapos_model');
        $this->data['emitente'] = $this->mapos_model->getEmitente();

        $this->load->view('propostas/imprimirProposta', $this->data);
    }

    public function gerarPdf()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Proposta não encontrada.');
            redirect('propostas/gerenciar');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vPropostas')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar propostas.');
            redirect(base_url());
        }

        $result = $this->propostas_model->getById($this->uri->segment(3));
        if (!$result) {
            $this->session->set_flashdata('error', 'Proposta não encontrada.');
            redirect('propostas/gerenciar');
        }

        $this->data['result'] = $result;
        $this->data['produtos'] = $this->propostas_model->getProdutos($result->idProposta);
        $this->data['servicos'] = $this->propostas_model->getServicos($result->idProposta);
        $this->data['parcelas'] = $this->propostas_model->getParcelas($result->idProposta);
        $this->data['outros'] = $this->propostas_model->getOutros($result->idProposta);
        $this->load->model('mapos_model');
        $this->data['emitente'] = $this->mapos_model->getEmitente();

        $this->load->helper('mpdf');
        
        // Usar view específica para PDF (layout simples)
        $html = $this->load->view('propostas/pdfProposta', $this->data, true);
        
        $numeroProposta = $result->numero_proposta ?: 'PROP-' . str_pad($result->idProposta, 6, 0, STR_PAD_LEFT);
        $filename = 'Proposta_' . $numeroProposta;
        
        // true = stream, false = landscape, true = download (força download ao invés de abrir)
        pdf_create($html, $filename, true, false, true);
    }

    public function gerarPdfLink()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => false,
                'message' => 'Proposta não encontrada.'
            ]));
            return;
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vPropostas')) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => false,
                'message' => 'Você não tem permissão para visualizar propostas.'
            ]));
            return;
        }

        $result = $this->propostas_model->getById($this->uri->segment(3));
        if (!$result) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => false,
                'message' => 'Proposta não encontrada.'
            ]));
            return;
        }

        $this->data['result'] = $result;
        $this->data['produtos'] = $this->propostas_model->getProdutos($result->idProposta);
        $this->data['servicos'] = $this->propostas_model->getServicos($result->idProposta);
        $this->data['parcelas'] = $this->propostas_model->getParcelas($result->idProposta);
        $this->data['outros'] = $this->propostas_model->getOutros($result->idProposta);
        $this->load->model('mapos_model');
        $this->data['emitente'] = $this->mapos_model->getEmitente();

        $this->load->helper('mpdf');
        
        // Usar view específica para PDF (layout simples)
        $html = $this->load->view('propostas/pdfProposta', $this->data, true);
        
        $numeroProposta = $result->numero_proposta ?: 'PROP-' . str_pad($result->idProposta, 6, 0, STR_PAD_LEFT);
        $filename = 'Proposta_' . $numeroProposta . '_' . date('YmdHis');
        
        // Gerar PDF e salvar em local acessível
        $pdfPath = pdf_create($html, $filename, false, false, false);
        
        if ($pdfPath && file_exists($pdfPath)) {
            // Retornar link público do PDF
            $pdfUrl = base_url() . 'assets/uploads/temp/' . basename($pdfPath);
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => true,
                'url' => $pdfUrl
            ]));
        } else {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => false,
                'message' => 'Erro ao gerar PDF. Caminho: ' . ($pdfPath ?? 'null')
            ]));
        }
    }

    // AJAX methods
    public function autoCompleteCliente()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->db->select('idClientes, nomeCliente, documento, telefone, celular');
            $this->db->limit(25);
            $this->db->like('nomeCliente', $q);
            $this->db->or_like('documento', $q);
            $this->db->or_like('telefone', $q);
            $this->db->or_like('celular', $q);
            $query = $this->db->get('clientes');
            
            if ($query->num_rows() > 0) {
                $row_set = [];
                foreach ($query->result_array() as $row) {
                    $documento = $row['documento'] ? ' | ' . $row['documento'] : '';
                    $telefone = $row['telefone'] ? ' | ' . $row['telefone'] : ($row['celular'] ? ' | ' . $row['celular'] : '');
                    $row_set[] = [
                        'label' => $row['nomeCliente'] . $documento . $telefone,
                        'id' => $row['idClientes'],
                        'value' => $row['nomeCliente']
                    ];
                }
                echo json_encode($row_set);
            } else {
                echo json_encode([]);
            }
        }
    }

    public function autoCompleteUsuario()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->propostas_model->autoCompleteUsuario($q);
        }
    }

    public function autoCompleteProduto()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->db->select('*');
            $this->db->limit(25);
            $this->db->like('codDeBarra', $q);
            $this->db->or_like('descricao', $q);
            $query = $this->db->get('produtos');
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    $row_set[] = [
                        'label' => $row['descricao'] . ' | Preço: R$ ' . number_format($row['precoVenda'], 2, ',', '.') . ' | Estoque: ' . $row['estoque'], 
                        'estoque' => $row['estoque'], 
                        'id' => $row['idProdutos'], 
                        'preco' => $row['precoVenda']  // Retornar como número (ex: 45.22)
                    ];
                }
                echo json_encode($row_set);
            }
        }
    }

    public function autoCompleteServico()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->db->select('*');
            $this->db->limit(25);
            $this->db->like('nome', $q);
            $query = $this->db->get('servicos');
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    $row_set[] = [
                        'label' => $row['nome'] . ' | Preço: R$ ' . number_format($row['preco'], 2, ',', '.'), 
                        'id' => $row['idServicos'], 
                        'preco' => $row['preco']  // Retornar como número
                    ];
                }
                echo json_encode($row_set);
            }
        }
    }
}

