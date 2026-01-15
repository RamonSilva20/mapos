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
                'status' => $this->input->post('status') ?: 'Aberto',
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
            $statusAntigo = $propostaAntiga->status ?? 'Aberto';
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
                // Caso 1: Não consumia, agora consome (Orçamento → Aprovado)
                if (!$statusAntigoConsome && $novoStatusConsome) {
                    $this->consumirEstoqueProposta($idProposta);
                    log_info("Status mudou de '{$statusAntigo}' para '{$novoStatus}' - Estoque CONSUMIDO. Proposta: {$idProposta}");
                }
                // Caso 2: Consumia, agora não consome (Aprovado → Cancelado)
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
            $statusConsomeEstoque = $this->statusConsumeEstoque($proposta->status ?? 'Aberto');
            
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

    public function clonar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aPropostas')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para clonar propostas.');
            redirect('propostas/gerenciar');
        }

        $idOriginal = $this->uri->segment(3);
        if (!$idOriginal || !is_numeric($idOriginal)) {
            $this->session->set_flashdata('error', 'Proposta não encontrada.');
            redirect('propostas/gerenciar');
        }

        // Buscar proposta original
        $propostaOriginal = $this->propostas_model->getById($idOriginal);
        if (!$propostaOriginal) {
            $this->session->set_flashdata('error', 'Proposta não encontrada.');
            redirect('propostas/gerenciar');
        }

        // Gerar novo número de proposta
        $numeroProposta = $this->propostas_model->gerarNumeroProposta();

        // Preparar dados da nova proposta (copiar tudo exceto ID e número)
        $data = [
            'numero_proposta' => $numeroProposta,
            'data_proposta' => date('Y-m-d'),
            'data_validade' => $propostaOriginal->data_validade,
            'status' => 'Aberto', // Sempre começar como "Aberto" ao clonar
            'clientes_id' => $propostaOriginal->clientes_id,
            'cliente_nome' => $propostaOriginal->cliente_nome,
            'usuarios_id' => $this->session->userdata('id_admin'),
            'observacoes' => $propostaOriginal->observacoes,
            'desconto' => $propostaOriginal->desconto,
            'valor_desconto' => $propostaOriginal->valor_desconto,
            'tipo_desconto' => $propostaOriginal->tipo_desconto,
            'valor_total' => $propostaOriginal->valor_total,
            'tipo_cond_comerc' => $propostaOriginal->tipo_cond_comerc,
            'cond_comerc_texto' => $propostaOriginal->cond_comerc_texto,
            'validade_dias' => $propostaOriginal->validade_dias,
            'prazo_entrega' => $propostaOriginal->prazo_entrega,
            'lancamento' => null, // Não copiar lançamento financeiro
        ];

        // Criar nova proposta
        $idNovaProposta = $this->propostas_model->add('propostas', $data, true);
        
        if (!$idNovaProposta) {
            $this->session->set_flashdata('error', 'Erro ao clonar proposta.');
            redirect('propostas/gerenciar');
        }

        // Copiar produtos
        $produtos = $this->propostas_model->getProdutos($idOriginal);
        foreach ($produtos as $produto) {
            $this->db->insert('produtos_proposta', [
                'proposta_id' => $idNovaProposta,
                'produtos_id' => $produto->produtos_id,
                'descricao' => $produto->descricao,
                'quantidade' => $produto->quantidade,
                'preco' => $produto->preco,
                'subtotal' => $produto->subtotal,
                'estoque_consumido' => 0, // Não copiar consumo de estoque
            ]);
        }

        // Copiar serviços
        $servicos = $this->propostas_model->getServicos($idOriginal);
        foreach ($servicos as $servico) {
            $this->db->insert('servicos_proposta', [
                'proposta_id' => $idNovaProposta,
                'servicos_id' => $servico->servicos_id,
                'descricao' => $servico->descricao,
                'quantidade' => $servico->quantidade,
                'preco' => $servico->preco,
                'subtotal' => $servico->subtotal,
            ]);
        }

        // Copiar parcelas
        $parcelas = $this->propostas_model->getParcelas($idOriginal);
        foreach ($parcelas as $parcela) {
            $this->db->insert('parcelas_proposta', [
                'proposta_id' => $idNovaProposta,
                'numero' => $parcela->numero,
                'valor' => $parcela->valor,
                'data_vencimento' => $parcela->data_vencimento,
                'dias' => $parcela->dias,
                'observacao' => $parcela->observacao,
            ]);
        }

        // Copiar outros produtos/serviços
        $outros = $this->propostas_model->getOutros($idOriginal);
        foreach ($outros as $outro) {
            $this->db->insert('outros_proposta', [
                'proposta_id' => $idNovaProposta,
                'descricao' => $outro->descricao,
                'preco' => $outro->preco,
            ]);
        }

        log_info('Clonou proposta #' . $idOriginal . ' para proposta #' . $idNovaProposta . ' (Número: ' . $numeroProposta . ')');
        $this->session->set_flashdata('success', 'Proposta clonada com sucesso! Número: ' . $numeroProposta);
        redirect('propostas/editar/' . $idNovaProposta);
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
        $statusConsomeEstoque = $this->statusConsumeEstoque($proposta->status ?? 'Aberto');
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
     * Status que NÃO consomem: Orçamento, Negociação (mesmos de OS)
     * Status que consomem: Todos os outros (Aberto, Aprovado, Em Andamento, etc.)
     */
    private function statusConsumeEstoque($status)
    {
        $statusQueNaoConsomem = ['Orçamento', 'Negociação'];
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

        $statusAntigo = $proposta->status ?? 'Aberto';

        // Verificar mudanças de estoque necessárias
        $statusAntigoConsome = $this->statusConsumeEstoque($statusAntigo);
        $novoStatusConsome = $this->statusConsumeEstoque($novoStatus);

        // Caso 1: Não consumia, agora consome (Orçamento → Aprovado)
        if (!$statusAntigoConsome && $novoStatusConsome) {
            $this->consumirEstoqueProposta($idProposta);
            log_info("Status mudou de '{$statusAntigo}' para '{$novoStatus}' - Estoque CONSUMIDO. Proposta: {$idProposta}");
        }
        // Caso 2: Consumia, agora não consome (Aprovado → Cancelado)
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
            
            // Gerar lançamento automático se mudou para Finalizado ou Faturado
            if (($novoStatus === 'Finalizado' || $novoStatus === 'Faturado') && 
                ($statusAntigo !== 'Finalizado' && $statusAntigo !== 'Faturado')) {
                
                // Verificar se já tem lançamento vinculado (verificar se campo existe)
                $campos = $this->db->list_fields('propostas');
                $temLancamento = false;
                if (in_array('lancamento', $campos)) {
                    $temLancamento = !empty($proposta->lancamento);
                }
                
                if (!$temLancamento) {
                    // Verificar se tem parcelas configuradas
                    $parcelas = $this->propostas_model->getParcelas($idProposta);
                    if (!empty($parcelas)) {
                        $this->gerarLancamentoAutomatico($idProposta);
                    }
                }
            }
            
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
     * Gera lançamento financeiro automaticamente usando dados já salvos na Proposta
     * Chamado quando a Proposta muda para Finalizado ou Faturado
     */
    private function gerarLancamentoAutomatico($idProposta)
    {
        // Buscar Proposta
        $proposta = $this->propostas_model->getById($idProposta);
        if (!$proposta) {
            log_info("Erro ao gerar lançamento automático: Proposta #{$idProposta} não encontrada");
            return false;
        }

        // Verificar se já tem lançamento
        if (!empty($proposta->lancamento)) {
            log_info("Proposta #{$idProposta} já possui lançamento vinculado. Pulando geração automática.");
            return false;
        }

        // Verificar se tem parcelas configuradas
        $parcelasConfiguradas = $this->propostas_model->getParcelas($idProposta);
        
        if (empty($parcelasConfiguradas)) {
            log_info("Proposta #{$idProposta} não possui parcelas configuradas. Pulando geração automática.");
            return false;
        }

        // Calcular valor total
        $produtos = $this->propostas_model->getProdutos($idProposta);
        $servicos = $this->propostas_model->getServicos($idProposta);
        $outros = $this->propostas_model->getOutros($idProposta);
        
        $totalProdutos = 0;
        $totalServicos = 0;
        $totalOutros = 0;
        
        foreach ($produtos as $p) {
            $totalProdutos += $p->preco * $p->quantidade;
        }
        foreach ($servicos as $s) {
            $totalServicos += $s->preco * $s->quantidade;
        }
        foreach ($outros as $o) {
            $totalOutros += $o->preco ?? 0;
        }
        
        $valorTotal = $totalProdutos + $totalServicos + $totalOutros;
        
        // Aplicar desconto se houver
        if (!empty($proposta->desconto) && $proposta->desconto > 0) {
            if ($proposta->tipo_desconto === 'percentual') {
                $valorTotal = $valorTotal * (1 - ($proposta->desconto / 100));
            } else {
                $valorTotal = $valorTotal - $proposta->desconto;
            }
        }
        
        if ($valorTotal <= 0) {
            log_info("Proposta #{$idProposta} tem valor total zero. Pulando geração automática.");
            return false;
        }

        $this->load->model('financeiro_model');
        $this->load->model('pagamentos_parciais_model');
        $lancamentosIds = [];
        
        // Verificar se o status é "Faturado" (NF emitida = pagamento automático)
        $statusFaturado = ($proposta->status === 'Faturado');
        
        // Obter nome do cliente
        $nomeCliente = $proposta->cliente_nome ?? '';
        if (empty($nomeCliente) && !empty($proposta->clientes_id)) {
            $this->load->model('clientes_model');
            $cliente = $this->clientes_model->getById($proposta->clientes_id);
            if ($cliente) {
                $nomeCliente = $cliente->nomeCliente;
            }
        }
        if (empty($nomeCliente)) {
            $nomeCliente = 'Cliente não identificado';
        }
        
        // Se tem parcelas configuradas, usar elas
        if (!empty($parcelasConfiguradas) && count($parcelasConfiguradas) > 0) {
            foreach ($parcelasConfiguradas as $parcela) {
                $valor = floatval($parcela->valor) ?: 0;
                if ($valor <= 0) {
                    continue; // Pular parcelas sem valor
                }
                
                // Usar data de vencimento da parcela ou calcular baseado em dias
                $dataVencimento = date('Y-m-d');
                if (!empty($parcela->data_vencimento)) {
                    $dataVencimento = $parcela->data_vencimento;
                } elseif (!empty($parcela->dias) && $parcela->dias > 0) {
                    $dataBase = $proposta->data_proposta ?? date('Y-m-d');
                    $dataVencimento = date('Y-m-d', strtotime($dataBase . ' +' . intval($parcela->dias) . ' days'));
                }
                
                // Se status é "Faturado", registrar pagamento automaticamente
                if ($statusFaturado) {
                    $statusPagamento = 'pago';
                    $valorPago = $valor;
                    $baixado = 1;
                    $dataPagamento = $dataVencimento; // Usar data de vencimento como data de pagamento
                } else {
                    // Status pendente para outros casos
                    $statusPagamento = 'pendente';
                    $valorPago = 0;
                    $baixado = 0;
                    $dataPagamento = null;
                }
                
                $dataLancamento = [
                    'descricao' => 'Proposta #' . ($proposta->numero_proposta ?: $idProposta) . ' - Parcela ' . $parcela->numero . ' - ' . htmlspecialchars($nomeCliente),
                    'valor' => $valor,
                    'valor_desconto' => $valor,
                    'valor_pago' => $valorPago,
                    'status_pagamento' => $statusPagamento,
                    'desconto' => 0,
                    'tipo_desconto' => 'real',
                    'data_vencimento' => $dataVencimento,
                    'data_pagamento' => $dataPagamento,
                    'baixado' => $baixado,
                    'cliente_fornecedor' => $nomeCliente,
                    'clientes_id' => $proposta->clientes_id,
                    'forma_pgto' => $proposta->forma_pgto ?? '', // Usar forma de pagamento da proposta se disponível
                    'tipo' => 'receita',
                    'observacoes' => (!empty($parcela->observacao) ? $parcela->observacao . ' - ' : '') . 
                                    'Referente à Proposta #' . ($proposta->numero_proposta ?: $idProposta),
                    'usuarios_id' => $this->session->userdata('id_admin')
                ];
                
                $this->financeiro_model->add('lancamentos', $dataLancamento);
                $lancamentoId = $this->db->insert_id();
                $lancamentosIds[] = $lancamentoId;
                
                // Se status é "Faturado", registrar pagamento parcial automaticamente
                if ($statusFaturado && $lancamentoId) {
                    $dataPagamentoParcial = [
                        'lancamentos_id' => $lancamentoId,
                        'valor' => $valor,
                        'data_pagamento' => $dataPagamento,
                        'forma_pgto' => $proposta->forma_pgto ?? '',
                        'observacao' => 'Pagamento automático - NF emitida (Proposta #' . ($proposta->numero_proposta ?: $idProposta) . ')',
                        'usuarios_id' => $this->session->userdata('id_admin')
                    ];
                    
                    $this->pagamentos_parciais_model->add($dataPagamentoParcial);
                    log_info('Registrou pagamento automático de R$ ' . number_format($valor, 2, ',', '.') . ' para lançamento #' . $lancamentoId . ' (Proposta #' . $idProposta . ' - Status: Faturado)');
                }
            }
        } else {
            // Se não tem parcelas configuradas, criar lançamento único
            // Se status é "Faturado", registrar pagamento automaticamente
            if ($statusFaturado) {
                $statusPagamento = 'pago';
                $valorPago = $valorTotal;
                $baixado = 1;
                $dataPagamento = $proposta->data_validade ?? date('Y-m-d');
            } else {
                $statusPagamento = 'pendente';
                $valorPago = 0;
                $baixado = 0;
                $dataPagamento = null;
            }
            
            $dataLancamento = [
                'descricao' => 'Proposta #' . ($proposta->numero_proposta ?: $idProposta) . ' - ' . htmlspecialchars($nomeCliente),
                'valor' => $valorTotal,
                'valor_desconto' => $valorTotal,
                'valor_pago' => $valorPago,
                'status_pagamento' => $statusPagamento,
                'desconto' => 0,
                'tipo_desconto' => 'real',
                'data_vencimento' => $proposta->data_validade ?? date('Y-m-d'),
                'data_pagamento' => $dataPagamento,
                'baixado' => $baixado,
                'cliente_fornecedor' => $nomeCliente,
                'clientes_id' => $proposta->clientes_id,
                'forma_pgto' => $proposta->forma_pgto ?? '',
                'tipo' => 'receita',
                'observacoes' => 'Referente à Proposta #' . ($proposta->numero_proposta ?: $idProposta),
                'usuarios_id' => $this->session->userdata('id_admin')
            ];
            
            $this->financeiro_model->add('lancamentos', $dataLancamento);
            $lancamentoId = $this->db->insert_id();
            $lancamentosIds[] = $lancamentoId;
            
            // Se status é "Faturado", registrar pagamento parcial automaticamente
            if ($statusFaturado && $lancamentoId) {
                $dataPagamentoParcial = [
                    'lancamentos_id' => $lancamentoId,
                    'valor' => $valorTotal,
                    'data_pagamento' => $dataPagamento,
                    'forma_pgto' => $proposta->forma_pgto ?? '',
                    'observacao' => 'Pagamento automático - NF emitida (Proposta #' . ($proposta->numero_proposta ?: $idProposta) . ')',
                    'usuarios_id' => $this->session->userdata('id_admin')
                ];
                
                $this->pagamentos_parciais_model->add($dataPagamentoParcial);
                log_info('Registrou pagamento automático de R$ ' . number_format($valorTotal, 2, ',', '.') . ' para lançamento #' . $lancamentoId . ' (Proposta #' . $idProposta . ' - Status: Faturado)');
            }
        }

        // Vincular primeiro lançamento à Proposta (se campo existir)
        if (!empty($lancamentosIds)) {
            // Verificar se campo lancamento existe
            $campos = $this->db->list_fields('propostas');
            if (in_array('lancamento', $campos)) {
                $this->propostas_model->edit('propostas', ['lancamento' => $lancamentosIds[0]], 'idProposta', $idProposta);
            }
        }

        log_info('Gerou lançamento financeiro automático para Proposta #' . $idProposta . '. Lançamentos: ' . implode(', ', $lancamentosIds));
        
        if ($statusFaturado) {
            log_info('Pagamentos automáticos registrados para Proposta #' . $idProposta . ' (Status: Faturado - NF emitida)');
        }
        
        return true;
    }
    
    /**
     * Retorna contadores de propostas por status (mesmos status de OS)
     */
    private function getContadoresStatus($where_array_excluindo_status = [])
    {
        $status_list = ['Aberto', 'Faturado', 'Negociação', 'Em Andamento', 'Orçamento', 'Finalizado', 'Cancelado', 'Aguardando Peças', 'Aprovado'];
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
     * Gera lançamento financeiro usando parcelas configuradas
     */
    public function gerarLancamentoParcelas()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'ePropostas')) {
            echo json_encode(['result' => false, 'message' => 'Você não tem permissão']);
            return;
        }

        $idProposta = $this->input->post('idProposta');
        $parcelasJson = $this->input->post('parcelas');
        $criarLancamento = $this->input->post('criar_lancamento');

        if (!$idProposta) {
            echo json_encode(['result' => false, 'message' => 'ID da proposta inválido']);
            return;
        }

        // Buscar Proposta
        $proposta = $this->propostas_model->getById($idProposta);
        if (!$proposta) {
            echo json_encode(['result' => false, 'message' => 'Proposta não encontrada']);
            return;
        }

        // Verificar se já tem lançamento vinculado
        if (!empty($proposta->lancamento)) {
            echo json_encode(['result' => false, 'message' => 'Esta proposta já possui um lançamento financeiro vinculado']);
            return;
        }

        // Decodificar parcelas
        $parcelas = json_decode($parcelasJson, true);
        if (!is_array($parcelas) || count($parcelas) === 0) {
            echo json_encode(['result' => false, 'message' => 'Nenhuma parcela configurada']);
            return;
        }

        // Se não deve criar lançamento, apenas retornar sucesso
        if (!$criarLancamento || $criarLancamento == '0') {
            echo json_encode(['result' => true, 'message' => 'Proposta atualizada. Lançamento não criado.']);
            return;
        }

        $this->load->model('financeiro_model');
        $this->load->model('pagamentos_parciais_model');
        $lancamentosIds = [];

        // Verificar se o status é "Faturado" (NF emitida = pagamento automático)
        $statusFaturado = ($proposta->status === 'Faturado');
        
        // Obter nome do cliente
        $nomeCliente = $proposta->cliente_nome ?? '';
        if (empty($nomeCliente) && !empty($proposta->clientes_id)) {
            $this->load->model('clientes_model');
            $cliente = $this->clientes_model->getById($proposta->clientes_id);
            if ($cliente) {
                $nomeCliente = $cliente->nomeCliente;
            }
        }
        if (empty($nomeCliente)) {
            $nomeCliente = 'Cliente não identificado';
        }

        foreach ($parcelas as $parcela) {
            // Converter data de vencimento
            $dataVencimento = date('Y-m-d');
            if (!empty($parcela['data_vencimento'])) {
                if (strpos($parcela['data_vencimento'], '/') !== false) {
                    $dataParts = explode('/', $parcela['data_vencimento']);
                    if (count($dataParts) == 3) {
                        $dataVencimento = $dataParts[2] . '-' . $dataParts[1] . '-' . $dataParts[0];
                    }
                } else {
                    $dataVencimento = $parcela['data_vencimento'];
                }
            } elseif (!empty($parcela['dias']) && $parcela['dias'] > 0) {
                $dataBase = $proposta->data_proposta ?? date('Y-m-d');
                $dataVencimento = date('Y-m-d', strtotime($dataBase . ' +' . intval($parcela['dias']) . ' days'));
            }

            $valor = floatval($parcela['valor']) ?: 0;
            if ($valor <= 0) {
                continue; // Pular parcelas sem valor
            }

            // Se status é "Faturado", registrar pagamento automaticamente
            if ($statusFaturado) {
                $statusPagamento = 'pago';
                $valorPago = $valor;
                $baixado = 1;
                $dataPagamento = $dataVencimento; // Usar data de vencimento como data de pagamento
            } else {
                // Status pendente para outros casos
                $statusPagamento = 'pendente';
                $valorPago = 0;
                $baixado = 0;
                $dataPagamento = null;
            }

            $dataLancamento = [
                'descricao' => 'Proposta #' . ($proposta->numero_proposta ?: $idProposta) . ' - Parcela ' . $parcela['numero'] . ' - ' . htmlspecialchars($nomeCliente),
                'valor' => $valor,
                'valor_desconto' => $valor,
                'valor_pago' => $valorPago,
                'status_pagamento' => $statusPagamento,
                'desconto' => 0,
                'tipo_desconto' => 'real',
                'data_vencimento' => $dataVencimento,
                'data_pagamento' => $dataPagamento,
                'baixado' => $baixado,
                'cliente_fornecedor' => $nomeCliente,
                'clientes_id' => $proposta->clientes_id,
                'forma_pgto' => $parcela['forma_pgto'] ?? '',
                'tipo' => 'receita',
                'observacoes' => (!empty($parcela['observacao']) ? $parcela['observacao'] . ' - ' : '') . 
                                'Referente à Proposta #' . ($proposta->numero_proposta ?: $idProposta),
                'usuarios_id' => $this->session->userdata('id_admin')
            ];
            
            // Adicionar conta bancária se fornecida
            if (isset($parcela['conta_id']) && !empty($parcela['conta_id'])) {
                $dataLancamento['contas_id'] = $parcela['conta_id'];
            }
            
            $this->financeiro_model->add('lancamentos', $dataLancamento);
            $lancamentoId = $this->db->insert_id();
            $lancamentosIds[] = $lancamentoId;
            
            // Se status é "Faturado", registrar pagamento parcial automaticamente
            if ($statusFaturado && $lancamentoId) {
                $dataPagamentoParcial = [
                    'lancamentos_id' => $lancamentoId,
                    'valor' => $valor,
                    'data_pagamento' => $dataPagamento,
                    'forma_pgto' => $parcela['forma_pgto'] ?? '',
                    'observacao' => 'Pagamento automático - NF emitida (Proposta #' . ($proposta->numero_proposta ?: $idProposta) . ')',
                    'usuarios_id' => $this->session->userdata('id_admin')
                ];
                
                $this->pagamentos_parciais_model->add($dataPagamentoParcial);
                log_info('Registrou pagamento automático de R$ ' . number_format($valor, 2, ',', '.') . ' para lançamento #' . $lancamentoId . ' (Proposta #' . $idProposta . ' - Status: Faturado)');
            }
        }

        // Vincular primeiro lançamento à Proposta
        if (!empty($lancamentosIds)) {
            $this->propostas_model->edit('propostas', ['lancamento' => $lancamentosIds[0]], 'idProposta', $idProposta);
        }

        log_info('Gerou lançamento financeiro para Proposta #' . $idProposta . ' usando parcelas. Lançamentos: ' . implode(', ', $lancamentosIds));
        
        if ($statusFaturado) {
            log_info('Pagamentos automáticos registrados para Proposta #' . $idProposta . ' (Status: Faturado - NF emitida)');
        }

        $mensagem = count($lancamentosIds) . ' lançamento(s) criado(s) com sucesso!';
        if ($statusFaturado) {
            $mensagem .= ' Pagamentos registrados automaticamente (NF emitida).';
        }
        echo json_encode(['result' => true, 'message' => $mensagem, 'lancamentos' => $lancamentosIds]);
    }
    
    /**
     * Retorna dados da Proposta para o modal de faturamento
     * Inclui parcelas se existirem
     */
    public function getDadosPropostaJson($idProposta)
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vPropostas')) {
            echo json_encode(['result' => false, 'message' => 'Sem permissão']);
            return;
        }

        $proposta = $this->propostas_model->getById($idProposta);
        if (!$proposta) {
            echo json_encode(['result' => false, 'message' => 'Proposta não encontrada']);
            return;
        }

        $produtos = $this->propostas_model->getProdutos($idProposta);
        $servicos = $this->propostas_model->getServicos($idProposta);
        $outros = $this->propostas_model->getOutros($idProposta);
        
        $totalProdutos = 0;
        $totalServicos = 0;
        $totalOutros = 0;
        
        foreach ($produtos as $p) {
            $totalProdutos += $p->preco * $p->quantidade;
        }
        foreach ($servicos as $s) {
            $totalServicos += $s->preco * $s->quantidade;
        }
        foreach ($outros as $o) {
            $totalOutros += $o->preco ?? 0;
        }
        
        $valorTotal = $totalProdutos + $totalServicos + $totalOutros;
        
        // Aplicar desconto se houver
        if (!empty($proposta->desconto) && $proposta->desconto > 0) {
            if ($proposta->tipo_desconto === 'percentual') {
                $valorTotal = $valorTotal * (1 - ($proposta->desconto / 100));
            } else {
                $valorTotal = $valorTotal - $proposta->desconto;
            }
        }

        // Buscar parcelas da Proposta
        $parcelas = $this->propostas_model->getParcelas($idProposta);
        
        // Preparar parcelas para JSON
        $parcelasArray = [];
        foreach ($parcelas as $p) {
            $parcelasArray[] = [
                'id' => $p->idParcelaProposta ?? null,
                'numero' => intval($p->numero),
                'dias' => intval($p->dias),
                'valor' => floatval($p->valor),
                'observacao' => $p->observacao ?? '',
                'data_vencimento' => $p->data_vencimento ?? null,
                'forma_pgto' => '', // Parcelas de proposta não têm forma de pagamento pré-configurada
                'status' => 'pendente'
            ];
        }

        echo json_encode([
            'result' => true,
            'proposta' => $proposta,
            'valorTotal' => $valorTotal,
            'temLancamento' => !empty($proposta->lancamento),
            'parcelas' => $parcelasArray
        ]);
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

