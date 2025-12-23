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

        // Contar propostas por status para as abas
        $this->data['contadores_status'] = $this->getContadoresStatus($where_array);

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
                'status' => $this->input->post('status') ?: 'Em aberto',
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

            $idProposta = $this->input->post('idProposta');
            $propostaAntiga = $this->propostas_model->getById($idProposta);
            $statusAntigo = $propostaAntiga->status ?? 'Em aberto';
            $novoStatus = $this->input->post('status');
            
            // Verificar mudanças de estoque necessárias
            $statusAntigoConsome = $this->statusConsumeEstoque($statusAntigo);
            $novoStatusConsome = $this->statusConsumeEstoque($novoStatus);
            
            if ($this->propostas_model->edit('propostas', $data, 'idProposta', $idProposta) == true) {
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
                
                // Gerenciar estoque baseado na mudança de status
                // Caso 1: Não consumia, agora consome (Rascunho → Aprovada)
                if (!$statusAntigoConsome && $novoStatusConsome) {
                    $this->consumirEstoqueProposta($idProposta);
                    log_info("Status mudou de '{$statusAntigo}' para '{$novoStatus}' - Estoque CONSUMIDO. Proposta: {$idProposta}");
                }
                // Caso 2: Consumia, agora não consome (Aprovada → Rascunho)
                elseif ($statusAntigoConsome && !$novoStatusConsome) {
                    $this->devolverEstoqueProposta($idProposta);
                    log_info("Status mudou de '{$statusAntigo}' para '{$novoStatus}' - Estoque DEVOLVIDO. Proposta: {$idProposta}");
                }

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
            // Buscar proposta para verificar status
            $proposta = $this->propostas_model->getById($idProposta);
            $statusConsomeEstoque = $this->statusConsumeEstoque($proposta->status ?? 'Em aberto');
            
            foreach ($produtos as $produto) {
                if (empty($produto['descricao'])) continue;

                // Converter preço corretamente - já vem como número do JavaScript
                $preco = isset($produto['preco']) ? floatval($produto['preco']) : 0;
                $quantidade = floatval($produto['quantidade'] ?? 1);
                $subtotal = $preco * $quantidade;
                $produtosId = !empty($produto['produtos_id']) ? $produto['produtos_id'] : null;

                // Verificar se campo estoque_consumido existe
                $campos = $this->db->list_fields('produtos_proposta');
                $temEstoqueConsumido = in_array('estoque_consumido', $campos);
                
                $dataInsert = [
                    'proposta_id' => $idProposta,
                    'produtos_id' => $produtosId,
                    'descricao' => $produto['descricao'],
                    'quantidade' => $quantidade,
                    'preco' => $preco,
                    'subtotal' => $subtotal,
                ];
                
                if ($temEstoqueConsumido) {
                    $dataInsert['estoque_consumido'] = 0;
                }
                
                $this->db->insert('produtos_proposta', $dataInsert);
                
                $lastId = $this->db->insert_id();
                
                // Consumir estoque se status permitir e produto tiver ID cadastrado
                if ($statusConsomeEstoque && $produtosId && $this->data['configuration']['control_estoque']) {
                    $this->load->model('produtos_model');
                    $this->produtos_model->updateEstoque($produtosId, $quantidade, '-');
                    
                    // Marcar como consumido (se campo existe)
                    if ($temEstoqueConsumido) {
                        $this->db->where('idProdutoProposta', $lastId);
                        $this->db->update('produtos_proposta', ['estoque_consumido' => 1]);
                    }
                    
                    log_info("Estoque consumido ao adicionar produto. Proposta: {$idProposta}, Produto: {$produtosId}, Qtd: {$quantidade}, Status: {$proposta->status}");
                }
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

        // Devolver estoque antes de excluir (se houver estoque consumido)
        // Verificar se status consome estoque - se sim, precisa devolver
        $statusConsomeEstoque = $this->statusConsumeEstoque($proposta->status ?? 'Em aberto');
        if ($statusConsomeEstoque) {
            $this->devolverEstoqueProposta($id);
            log_info("Estoque devolvido ao excluir proposta. ID: {$id}, Status: {$proposta->status}");
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
        
        // QR Code Pix (se disponível)
        if (isset($this->data['configuration']['pix_key']) && $this->data['configuration']['pix_key']) {
            $this->data['qrCode'] = $this->propostas_model->getQrCode(
                $result->idProposta,
                $this->data['configuration']['pix_key'],
                $this->data['emitente']
            );
            $this->data['chaveFormatada'] = $this->formatarChave($this->data['configuration']['pix_key']);
        }

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
        
        // Número da proposta (apenas número)
        $numeroProposta = $result->numero_proposta ?: $result->idProposta;
        $numeroProposta = preg_replace('/[^0-9]/', '', $numeroProposta);
        if (empty($numeroProposta)) {
            $numeroProposta = $result->idProposta;
        }
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
        
        // Número da proposta (apenas número)
        $numeroProposta = $result->numero_proposta ?: $result->idProposta;
        $numeroProposta = preg_replace('/[^0-9]/', '', $numeroProposta);
        if (empty($numeroProposta)) {
            $numeroProposta = $result->idProposta;
        }
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
    
    /**
     * Verifica se o status da proposta deve consumir estoque
     * Status que NÃO consomem: Rascunho, Modelo
     * Status que consomem: Todos os outros (Em aberto, Pendente, Aguardando, Aprovada, Não aprovada, Concluído)
     */
    private function statusConsumeEstoque($status)
    {
        $statusQueNaoConsomem = ['Rascunho', 'Modelo'];
        return !in_array($status, $statusQueNaoConsomem);
    }
    
    /**
     * Consome estoque de todos os produtos de uma proposta
     */
    private function consumirEstoqueProposta($idProposta)
    {
        // Verificar se campo estoque_consumido existe
        $campos = $this->db->list_fields('produtos_proposta');
        $temEstoqueConsumido = in_array('estoque_consumido', $campos);
        
        if ($temEstoqueConsumido) {
            $query = "SELECT pp.idProdutoProposta, pp.produtos_id, pp.quantidade 
                      FROM produtos_proposta pp 
                      WHERE pp.proposta_id = ? AND pp.estoque_consumido = 0 AND pp.produtos_id IS NOT NULL";
        } else {
            $query = "SELECT pp.idProdutoProposta, pp.produtos_id, pp.quantidade 
                      FROM produtos_proposta pp 
                      WHERE pp.proposta_id = ? AND pp.produtos_id IS NOT NULL";
        }

        $produtos = $this->db->query($query, [$idProposta])->result();

        if (!$produtos || count($produtos) == 0) {
            log_info("Nenhum produto para consumir estoque. Proposta: {$idProposta}");
            return true;
        }

        $this->load->model('produtos_model');
        $produtosProcessados = 0;

        foreach ($produtos as $produto) {
            if ($this->data['configuration']['control_estoque']) {
                $this->produtos_model->updateEstoque($produto->produtos_id, $produto->quantidade, '-');
            }

            if ($temEstoqueConsumido) {
                $this->db->where('idProdutoProposta', $produto->idProdutoProposta);
                $this->db->update('produtos_proposta', ['estoque_consumido' => 1]);
            }

            log_info("Estoque consumido: Produto {$produto->produtos_id}, Qtd: {$produto->quantidade}, Proposta: {$idProposta}");
            $produtosProcessados++;
        }

        log_info("Total de produtos com estoque consumido: {$produtosProcessados}. Proposta: {$idProposta}");
        return true;
    }
    
    /**
     * Devolve estoque de todos os produtos de uma proposta
     */
    private function devolverEstoqueProposta($idProposta)
    {
        // Verificar se campo estoque_consumido existe
        $campos = $this->db->list_fields('produtos_proposta');
        $temEstoqueConsumido = in_array('estoque_consumido', $campos);
        
        if ($temEstoqueConsumido) {
            $query = "SELECT pp.idProdutoProposta, pp.produtos_id, pp.quantidade 
                      FROM produtos_proposta pp 
                      WHERE pp.proposta_id = ? AND pp.estoque_consumido = 1 AND pp.produtos_id IS NOT NULL";
        } else {
            // Se campo não existe, não há como devolver (assumir que todos foram consumidos)
            log_info("Campo estoque_consumido não existe. Não é possível devolver estoque. Proposta: {$idProposta}");
            return true;
        }

        $produtos = $this->db->query($query, [$idProposta])->result();

        if (!$produtos || count($produtos) == 0) {
            log_info("Nenhum produto para devolver estoque. Proposta: {$idProposta}");
            return true;
        }

        $this->load->model('produtos_model');
        $produtosProcessados = 0;

        foreach ($produtos as $produto) {
            if ($this->data['configuration']['control_estoque']) {
                $this->produtos_model->updateEstoque($produto->produtos_id, $produto->quantidade, '+');
            }

            if ($temEstoqueConsumido) {
                $this->db->where('idProdutoProposta', $produto->idProdutoProposta);
                $this->db->update('produtos_proposta', ['estoque_consumido' => 0]);
            }

            log_info("Estoque devolvido: Produto {$produto->produtos_id}, Qtd: {$produto->quantidade}, Proposta: {$idProposta}");
            $produtosProcessados++;
        }

        log_info("Total de produtos com estoque devolvido: {$produtosProcessados}. Proposta: {$idProposta}");
        return true;
    }
    
    /**
     * Método temporário para executar SQL de atualização
     * Remover após execução
     */
    public function executarSqlAtualizacao()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aPropostas')) {
            die('Sem permissão');
        }
        
        $sql = file_get_contents(FCPATH . 'updates/add_estoque_consumido_propostas.sql');
        $commands = explode(';', $sql);
        
        foreach ($commands as $command) {
            $command = trim($command);
            if (empty($command) || strpos($command, '--') === 0) {
                continue;
            }
            
            if (strpos($command, 'PREPARE') !== false || strpos($command, 'EXECUTE') !== false || strpos($command, 'DEALLOCATE') !== false) {
                $this->db->query($command);
            } else {
                $this->db->query($command);
            }
        }
        
        echo "SQL executado com sucesso!";
    }
    
    /**
     * Atualiza o status de uma proposta via AJAX
     * Gerencia estoque baseado na mudança de status
     */
    public function atualizarStatus()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'ePropostas')) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['result' => false, 'message' => 'Você não tem permissão para editar propostas']));
            return;
        }

        $idProposta = $this->input->post('idProposta');
        $novoStatus = $this->input->post('status');

        if (!$idProposta || !$novoStatus) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['result' => false, 'message' => 'Dados inválidos']));
            return;
        }

        // Buscar proposta atual
        $proposta = $this->propostas_model->getById($idProposta);
        if (!$proposta) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['result' => false, 'message' => 'Proposta não encontrada']));
            return;
        }

        $statusAntigo = $proposta->status ?? 'Em aberto';

        // Verificar mudanças de estoque necessárias
        $statusAntigoConsome = $this->statusConsumeEstoque($statusAntigo);
        $novoStatusConsome = $this->statusConsumeEstoque($novoStatus);

        // Caso 1: Não consumia, agora consome (Rascunho → Aprovada)
        if (!$statusAntigoConsome && $novoStatusConsome) {
            $this->consumirEstoqueProposta($idProposta);
            log_info("Status mudou de '{$statusAntigo}' para '{$novoStatus}' - Estoque CONSUMIDO. Proposta: {$idProposta}");
        }
        // Caso 2: Consumia, agora não consome (Aprovada → Rascunho)
        elseif ($statusAntigoConsome && !$novoStatusConsome) {
            $this->devolverEstoqueProposta($idProposta);
            log_info("Status mudou de '{$statusAntigo}' para '{$novoStatus}' - Estoque DEVOLVIDO. Proposta: {$idProposta}");
        }
        // Caso 3: Sem mudança no comportamento de estoque
        else {
            log_info("Status mudou de '{$statusAntigo}' para '{$novoStatus}' - SEM alteração de estoque. Proposta: {$idProposta}");
        }

        $data = array('status' => $novoStatus);

        if ($this->propostas_model->edit('propostas', $data, 'idProposta', $idProposta) == true) {
            log_info('Atualizou status da proposta. ID: ' . $idProposta . ' | Novo status: ' . $novoStatus);
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['result' => true, 'message' => 'Status atualizado com sucesso!']));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['result' => false, 'message' => 'Erro ao atualizar status']));
        }
    }
    
    /**
     * Retorna contadores de propostas por status
     */
    private function getContadoresStatus($where_array_excluindo_status = [])
    {
        $status_list = ['Em aberto', 'Rascunho', 'Pendente', 'Aguardando', 'Aprovada', 'Não aprovada', 'Concluído', 'Modelo'];
        $contadores = [];
        
        // Contar total geral (sem filtro de status)
        $where_geral = $where_array_excluindo_status;
        unset($where_geral['status']);
        $contadores['total'] = $this->propostas_model->countPropostas($where_geral);
        
        // Contar por status
        foreach ($status_list as $status) {
            $where_status = $where_array_excluindo_status;
            unset($where_status['status']);
            $where_status['status'] = [$status];
            $contadores[$status] = $this->propostas_model->countPropostas($where_status);
        }
        
        return $contadores;
    }
    
    /**
     * Formata chave Pix para exibição
     */
    public function formatarChave($chave)
    {
        if (!$chave) {
            return '';
        }
        
        // Remover caracteres não numéricos
        $chaveLimpa = preg_replace('/[^0-9]/', '', $chave);
        
        // CPF (11 dígitos)
        if (strlen($chaveLimpa) === 11) {
            return substr($chaveLimpa, 0, 3) . '.' . substr($chaveLimpa, 3, 3) . '.' . substr($chaveLimpa, 6, 3) . '-' . substr($chaveLimpa, 9);
        }
        // CNPJ (14 dígitos)
        elseif (strlen($chaveLimpa) === 14) {
            return substr($chaveLimpa, 0, 2) . '.' . substr($chaveLimpa, 2, 3) . '.' . substr($chaveLimpa, 5, 3) . '/' . substr($chaveLimpa, 8, 4) . '-' . substr($chaveLimpa, 12);
        }
        // Telefone (11 dígitos com DDD)
        elseif (strlen($chaveLimpa) === 11 && substr($chaveLimpa, 0, 2) >= 10) {
            return '(' . substr($chaveLimpa, 0, 2) . ') ' . substr($chaveLimpa, 2, 5) . '-' . substr($chaveLimpa, 7);
        }
        
        // Retornar chave original se não for nenhum dos formatos acima
        return $chave;
    }
}

