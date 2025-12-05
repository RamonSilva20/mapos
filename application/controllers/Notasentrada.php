<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Notasentrada extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper('form');
        $this->load->model('Notasentrada_model', 'notasentrada_model');
        $this->load->model('clientes_model');
        $this->load->model('produtos_model');
        $this->data['menuNotasEntrada'] = 'Notas de Entrada';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vNotaEntrada')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar notas de entrada.');
            redirect(base_url());
        }

        $pesquisa = $this->input->get('pesquisa');

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('notasentrada/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->notasentrada_model->count('notas_entrada');
        if($pesquisa) {
            $this->data['configuration']['suffix'] = "?pesquisa={$pesquisa}";
            $this->data['configuration']['first_url'] = base_url("index.php/notasentrada")."?pesquisa={$pesquisa}";
        }

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->notasentrada_model->get('notas_entrada', '*', $pesquisa, $this->data['configuration']['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'notasentrada/notas_entrada';
        return $this->layout();
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aNotaEntrada')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar notas de entrada.');
            redirect(base_url());
        }

        $this->data['view'] = 'notasentrada/adicionar_nota';
        return $this->layout();
    }

    public function visualizar($id = null)
    {
        if (! $id || ! is_numeric($id)) {
            $this->session->set_flashdata('error', 'Nota não encontrada ou parâmetro inválido.');
            redirect('notasentrada/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vNotaEntrada')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar notas de entrada.');
            redirect(base_url());
        }

        $this->data['nota'] = $this->notasentrada_model->getById($id);
        if (!$this->data['nota']) {
            $this->session->set_flashdata('error', 'Nota não encontrada.');
            redirect('notasentrada/gerenciar');
        }

        $this->data['itens'] = $this->notasentrada_model->getItensByNota($id);
        $this->data['view'] = 'notasentrada/visualizar_nota';
        return $this->layout();
    }

    public function uploadXML()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aNotaEntrada')) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(403)
                ->set_output(json_encode(['result' => false, 'message' => 'Você não tem permissão para adicionar notas de entrada.']));
        }

        // Configurar upload
        $upload_path = FCPATH . 'assets/arquivos/xml/';
        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'xml';
        $config['max_size'] = 5120; // 5MB
        $config['file_name'] = 'nfe_' . date('YmdHis') . '_' . uniqid() . '.xml';

        // Criar diretório se não existir
        if (!is_dir($upload_path)) {
            if (!mkdir($upload_path, 0777, true)) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(500)
                    ->set_output(json_encode([
                        'result' => false,
                        'message' => 'Erro ao criar diretório de upload. Verifique as permissões.'
                    ]));
            }
        }
        
        // Verificar se o diretório é gravável
        if (!is_writable($upload_path)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'result' => false,
                    'message' => 'Diretório de upload não tem permissão de escrita. Contate o administrador.'
                ]));
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('arquivo_xml')) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'result' => false,
                    'message' => 'Erro no upload: ' . $this->upload->display_errors()
                ]));
        }

        $upload_data = $this->upload->data();
        $file_path = $upload_data['full_path'];

        // Processar XML
        $resultado = $this->processarXML($file_path);

        if ($resultado['result']) {
            log_info('Upload de XML de nota de entrada. Chave: ' . ($resultado['chave_acesso'] ?? 'N/A'));
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode([
                    'result' => true,
                    'message' => 'Nota de entrada processada com sucesso!',
                    'nota_id' => $resultado['nota_id']
                ]));
        } else {
            // Remover arquivo em caso de erro
            @unlink($file_path);
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'result' => false,
                    'message' => $resultado['message']
                ]));
        }
    }

    private function processarXML($file_path)
    {
        // Ler conteúdo do XML
        $xml_content = file_get_contents($file_path);
        
        // Desabilitar warnings de XML para evitar problemas com namespaces
        libxml_use_internal_errors(true);
        
        // Carregar XML
        $xml = simplexml_load_string($xml_content);
        
        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            return [
                'result' => false,
                'message' => 'XML inválido ou malformado.'
            ];
        }

        // Detectar tipo de XML (NFe ou NFSe)
        $namespaces = $xml->getNamespaces(true);
        $xml_content_lower = strtolower($xml_content);
        $is_nfse = false;
        
        // Verificar se é NFSe
        if (isset($xml->CompNfse) || 
            isset($xml->Nfse) ||
            strpos($xml_content_lower, 'compnfse') !== false || 
            strpos($xml_content_lower, 'nfse') !== false ||
            strpos($xml_content_lower, 'declaracaoprestacaoservico') !== false) {
            $is_nfse = true;
        }
        
        // Processar conforme o tipo
        if ($is_nfse) {
            return $this->processarNFSe($xml, $xml_content, $file_path);
        } else {
            return $this->processarNFe($xml, $xml_content, $file_path);
        }
    }
    
    private function processarNFSe($xml, $xml_content, $file_path)
    {
        // Registrar namespaces para NFSe
        $namespaces = $xml->getNamespaces(true);
        $nfseNamespace = 'http://shad.elotech.com.br/schemas/iss/nfse_v2_03.xsd';
        if (isset($namespaces[''])) {
            $nfseNamespace = $namespaces[''];
        }
        $xml->registerXPathNamespace('nfse', $nfseNamespace);
        
        // Inicializar variáveis
        $chave_acesso = '';
        $numero_nf = '';
        $serie_nf = '';
        $data_emissao = '';
        $cnpj_emitente = '';
        $nome_emitente = '';
        $cnpj_destinatario = '';
        $nome_destinatario = '';
        $valor_total = 0;
        $valor_produtos = 0;
        $valor_icms = 0;
        $valor_ipi = 0;
        $valor_frete = 0;
        $valor_desconto = 0;
        $natureza_operacao = '';
        $modelo_nf = 'SE'; // NFSe
        $tipo_operacao = '0';
        
        try {
            // Buscar InfNfse (estrutura: CompNfse > Nfse > InfNfse)
            $infNfse = null;
            
            if (isset($xml->CompNfse->Nfse->InfNfse)) {
                $infNfse = $xml->CompNfse->Nfse->InfNfse;
            } elseif (isset($xml->Nfse->InfNfse)) {
                $infNfse = $xml->Nfse->InfNfse;
            } else {
                // Tentar via xpath
                $infNfse_array = $xml->xpath('//InfNfse');
                if (isset($infNfse_array[0])) {
                    $infNfse = $infNfse_array[0];
                }
            }
            
            if (!$infNfse) {
                return [
                    'result' => false,
                    'message' => 'Estrutura XML NFSe inválida. Não foi possível encontrar InfNfse.'
                ];
            }
            
            // Número da nota
            $numero_nf = (string)$infNfse->Numero;
            
            // Chave de acesso
            $chave_acesso = (string)$infNfse->ChaveAcesso;
            
            // Data de emissão
            $data_emissao = (string)$infNfse->DataEmissao;
            if (!empty($data_emissao)) {
                $data_emissao = date('Y-m-d', strtotime($data_emissao));
            }
            
            // Valores
            if (isset($infNfse->ValoresNfse)) {
                $valores = $infNfse->ValoresNfse;
                $valor_total = (float)(string)$valores->ValorLiquidoNfse;
                $valor_produtos = (float)(string)$valores->BaseCalculo;
                $valor_icms = (float)(string)$valores->ValorIss;
            }
            
            // Prestador (Emitente)
            if (isset($infNfse->PrestadorServico)) {
                $prestador = $infNfse->PrestadorServico;
                if (isset($prestador->IdentificacaoPrestador->CpfCnpj->Cnpj)) {
                    $cnpj_emitente = preg_replace('/[^0-9]/', '', (string)$prestador->IdentificacaoPrestador->CpfCnpj->Cnpj);
                } elseif (isset($prestador->IdentificacaoPrestador->CpfCnpj->Cpf)) {
                    $cnpj_emitente = preg_replace('/[^0-9]/', '', (string)$prestador->IdentificacaoPrestador->CpfCnpj->Cpf);
                }
                $nome_emitente = (string)$prestador->RazaoSocial;
            }
            
            // Declaração de Prestação de Serviço
            $declaracao = null;
            if (isset($infNfse->DeclaracaoPrestacaoServico->InfDeclaracaoPrestacaoServico)) {
                $declaracao = $infNfse->DeclaracaoPrestacaoServico->InfDeclaracaoPrestacaoServico;
                
                // Tomador (Destinatário)
                if (isset($declaracao->Tomador)) {
                    $tomador = $declaracao->Tomador;
                    if (isset($tomador->IdentificacaoTomador->CpfCnpj->Cnpj)) {
                        $cnpj_destinatario = preg_replace('/[^0-9]/', '', (string)$tomador->IdentificacaoTomador->CpfCnpj->Cnpj);
                    } elseif (isset($tomador->IdentificacaoTomador->CpfCnpj->Cpf)) {
                        $cnpj_destinatario = preg_replace('/[^0-9]/', '', (string)$tomador->IdentificacaoTomador->CpfCnpj->Cpf);
                    }
                    $nome_destinatario = (string)$tomador->RazaoSocial;
                }
                
                // Serviço
                if (isset($declaracao->Servico)) {
                    $servico = $declaracao->Servico;
                    
                    // Valores do serviço (pode sobrescrever valores de ValoresNfse)
                    if (isset($servico->Valores)) {
                        $valoresServico = $servico->Valores;
                        $valor_total = (float)(string)$valoresServico->ValorServicos;
                        $valor_produtos = $valor_total;
                        $valor_icms = (float)(string)$valoresServico->ValorIss;
                        $valor_desconto = (float)(string)$valoresServico->DescontoIncondicionado + (float)(string)$valoresServico->DescontoCondicionado;
                    }
                    
                    // Discriminação (natureza da operação)
                    $natureza_operacao = (string)$servico->Discriminacao;
                }
                
                // RPS (série)
                if (isset($declaracao->Rps->IdentificacaoRps->Serie)) {
                    $serie_nf = (string)$declaracao->Rps->IdentificacaoRps->Serie;
                }
            }
            
            // Verificar se já existe
            if (!empty($chave_acesso)) {
                $nota_existente = $this->notasentrada_model->getByChaveAcesso($chave_acesso);
                if ($nota_existente) {
                    return [
                        'result' => false,
                        'message' => 'Esta nota fiscal já foi cadastrada anteriormente.'
                    ];
                }
            }
            
        } catch (Exception $e) {
            return [
                'result' => false,
                'message' => 'Erro ao processar XML NFSe: ' . $e->getMessage()
            ];
        }
        
        // Salvar nota
        $data_nota = [
            'numero_nf' => $numero_nf,
            'serie_nf' => $serie_nf ?: '1',
            'chave_acesso' => $chave_acesso,
            'cnpj_emitente' => $cnpj_emitente,
            'nome_emitente' => $nome_emitente,
            'cnpj_destinatario' => $cnpj_destinatario,
            'nome_destinatario' => $nome_destinatario,
            'data_emissao' => $data_emissao,
            'data_entrada' => date('Y-m-d'),
            'valor_total' => $valor_total,
            'valor_produtos' => $valor_produtos,
            'valor_icms' => $valor_icms,
            'valor_ipi' => 0,
            'valor_frete' => 0,
            'valor_desconto' => $valor_desconto,
            'natureza_operacao' => $natureza_operacao,
            'modelo_nf' => $modelo_nf,
            'tipo_operacao' => $tipo_operacao,
            'xml_path' => $file_path,
            'xml_content' => $xml_content,
            'status' => 'Processada',
            'usuario_cadastro' => $this->session->userdata('id')
        ];
        
        // Tentar encontrar fornecedor pelo CNPJ
        if (!empty($cnpj_emitente)) {
            $this->db->where('documento', $cnpj_emitente);
            $fornecedor = $this->db->get('clientes')->row();
            if ($fornecedor) {
                $data_nota['fornecedor_id'] = $fornecedor->idClientes;
            }
        }
        
        $nota_id = $this->notasentrada_model->add('notas_entrada', $data_nota);
        
        if (!$nota_id) {
            return [
                'result' => false,
                'message' => 'Erro ao salvar nota no banco de dados.'
            ];
        }
        
        // Processar itens da NFSe
        try {
            // Buscar ListaItensServico através do caminho completo
            $listaItens = null;
            
            // Tentar caminho completo
            if (isset($infNfse->DeclaracaoPrestacaoServico->InfDeclaracaoPrestacaoServico->Servico->ListaItensServico)) {
                $listaItens = $infNfse->DeclaracaoPrestacaoServico->InfDeclaracaoPrestacaoServico->Servico->ListaItensServico;
            }
            
            // Se não encontrou, tentar via xpath
            if (!$listaItens) {
                $listaItens_array = $xml->xpath('//ListaItensServico');
                if (isset($listaItens_array[0])) {
                    $listaItens = $listaItens_array[0];
                }
            }
            
            if ($listaItens) {
                // Tentar buscar ItemServico de diferentes formas
                $itens = null;
                
                if (isset($listaItens->ItemServico)) {
                    $itens = $listaItens->ItemServico;
                } else {
                    // Tentar via xpath
                    $itens_array = $xml->xpath('//ItemServico');
                    if (!empty($itens_array)) {
                        $itens = $itens_array;
                    }
                }
                
                if ($itens) {
                    // Verificar se é array ou objeto único
                    if (is_array($itens)) {
                        foreach ($itens as $item) {
                            $this->processarItemNFSe($item, $nota_id);
                        }
                    } elseif (is_object($itens)) {
                        // Se for único item
                        $this->processarItemNFSe($itens, $nota_id);
                    }
                } else {
                    // Se não encontrou ItemServico, criar item genérico
                    if (isset($infNfse->DeclaracaoPrestacaoServico->InfDeclaracaoPrestacaoServico->Servico)) {
                        $servico = $infNfse->DeclaracaoPrestacaoServico->InfDeclaracaoPrestacaoServico->Servico;
                        $discriminacao = isset($servico->Discriminacao) ? (string)$servico->Discriminacao : '';
                        $valor_servicos = 0;
                        
                        if (isset($servico->Valores->ValorServicos)) {
                            $valor_servicos = (float)(string)$servico->Valores->ValorServicos;
                        } elseif ($valor_total > 0) {
                            $valor_servicos = $valor_total;
                        }
                        
                        // Criar item genérico
                        $data_item = [
                            'nota_entrada_id' => $nota_id,
                            'codigo_produto' => 'SERVICO',
                            'descricao_produto' => !empty($discriminacao) ? $discriminacao : 'Serviço prestado',
                            'ncm' => '',
                            'cest' => '',
                            'cfop' => '',
                            'unidade' => 'UN',
                            'quantidade' => 1.0,
                            'valor_unitario' => $valor_servicos,
                            'valor_total' => $valor_servicos,
                            'valor_icms' => 0,
                            'valor_ipi' => 0,
                            'aliquota_icms' => 0,
                            'aliquota_ipi' => 0,
                            'origem' => '0',
                            'tributacao' => '',
                            'produto_id' => null
                        ];
                        
                        $this->notasentrada_model->add('notas_entrada_itens', $data_item);
                    }
                }
            } else {
                // Se não encontrou itens, criar um item genérico com os dados do serviço
                if (isset($infNfse->DeclaracaoPrestacaoServico->InfDeclaracaoPrestacaoServico->Servico)) {
                    $servico = $infNfse->DeclaracaoPrestacaoServico->InfDeclaracaoPrestacaoServico->Servico;
                    $discriminacao = (string)$servico->Discriminacao;
                    $valor_servicos = 0;
                    
                    if (isset($servico->Valores->ValorServicos)) {
                        $valor_servicos = (float)(string)$servico->Valores->ValorServicos;
                    } elseif ($valor_total > 0) {
                        $valor_servicos = $valor_total;
                    }
                    
                    // Criar item genérico
                    $data_item = [
                        'nota_entrada_id' => $nota_id,
                        'codigo_produto' => 'SERVICO',
                        'descricao_produto' => !empty($discriminacao) ? $discriminacao : 'Serviço prestado',
                        'ncm' => '',
                        'cest' => '',
                        'cfop' => '',
                        'unidade' => 'UN',
                        'quantidade' => 1.0,
                        'valor_unitario' => $valor_servicos,
                        'valor_total' => $valor_servicos,
                        'valor_icms' => 0,
                        'valor_ipi' => 0,
                        'aliquota_icms' => 0,
                        'aliquota_ipi' => 0,
                        'origem' => '0',
                        'tributacao' => '',
                        'produto_id' => null
                    ];
                    
                    $this->notasentrada_model->add('notas_entrada_itens', $data_item);
                }
            }
        } catch (Exception $e) {
            // Continuar mesmo se houver erro ao processar itens
            log_message('error', 'Erro ao processar itens NFSe: ' . $e->getMessage());
            
            // Tentar criar item genérico em caso de erro
            try {
                $data_item = [
                    'nota_entrada_id' => $nota_id,
                    'codigo_produto' => 'SERVICO',
                    'descricao_produto' => $natureza_operacao ?: 'Serviço prestado',
                    'ncm' => '',
                    'cest' => '',
                    'cfop' => '',
                    'unidade' => 'UN',
                    'quantidade' => 1.0,
                    'valor_unitario' => $valor_total,
                    'valor_total' => $valor_total,
                    'valor_icms' => 0,
                    'valor_ipi' => 0,
                    'aliquota_icms' => 0,
                    'aliquota_ipi' => 0,
                    'origem' => '0',
                    'tributacao' => '',
                    'produto_id' => null
                ];
                
                $this->notasentrada_model->add('notas_entrada_itens', $data_item);
            } catch (Exception $e2) {
                // Ignorar erro ao criar item genérico
            }
        }
        
        return [
            'result' => true,
            'nota_id' => $nota_id,
            'chave_acesso' => $chave_acesso
        ];
    }
    
    private function processarItemNFSe($item, $nota_id)
    {
        // Extrair dados do item com validação
        $codigo_produto = isset($item->ItemListaServico) ? (string)$item->ItemListaServico : 'SERVICO';
        $descricao_produto = isset($item->Descricao) ? (string)$item->Descricao : 'Serviço prestado';
        $quantidade = isset($item->Quantidade) ? (float)(string)$item->Quantidade : 1.0;
        $valor_unitario = isset($item->ValorUnitario) ? (float)(string)$item->ValorUnitario : 0.0;
        $valor_total_item = isset($item->ValorLiquido) ? (float)(string)$item->ValorLiquido : $valor_unitario;
        
        // Se valor total for zero, usar valor unitário * quantidade
        if ($valor_total_item == 0 && $valor_unitario > 0) {
            $valor_total_item = $valor_unitario * $quantidade;
        }
        
        // Se ainda for zero, usar valor total da nota
        if ($valor_total_item == 0) {
            $this->db->where('idNotaEntrada', $nota_id);
            $nota = $this->db->get('notas_entrada')->row();
            if ($nota && $nota->valor_total > 0) {
                $valor_total_item = $nota->valor_total;
                $valor_unitario = $valor_total_item / ($quantidade > 0 ? $quantidade : 1.0);
            }
        }
        
        $data_item = [
            'nota_entrada_id' => $nota_id,
            'codigo_produto' => $codigo_produto,
            'descricao_produto' => $descricao_produto,
            'ncm' => '',
            'cest' => '',
            'cfop' => '',
            'unidade' => 'UN',
            'quantidade' => $quantidade > 0 ? $quantidade : 1.0,
            'valor_unitario' => $valor_unitario,
            'valor_total' => $valor_total_item,
            'valor_icms' => 0,
            'valor_ipi' => 0,
            'aliquota_icms' => 0,
            'aliquota_ipi' => 0,
            'origem' => '0',
            'tributacao' => '',
            'produto_id' => null
        ];
        
        $result = $this->notasentrada_model->add('notas_entrada_itens', $data_item);
        
        if (!$result) {
            log_message('error', 'Erro ao salvar item NFSe. Nota ID: ' . $nota_id . ', Item: ' . $descricao_produto);
        }
    }
    
    private function processarNFe($xml, $xml_content, $file_path)
    {
        // Registrar namespaces (pode variar dependendo da versão da NFe)
        $namespaces = $xml->getNamespaces(true);
        $nfeNamespace = 'http://www.portalfiscal.inf.br/nfe';
        if (isset($namespaces[''])) {
            $nfeNamespace = $namespaces[''];
        }
        $xml->registerXPathNamespace('nfe', $nfeNamespace);
        
        // Extrair dados da NFe
        $chave_acesso = '';
        $numero_nf = '';
        $serie_nf = '';
        $data_emissao = '';
        $cnpj_emitente = '';
        $nome_emitente = '';
        $cnpj_destinatario = '';
        $nome_destinatario = '';
        $valor_total = 0;
        $valor_produtos = 0;
        $valor_icms = 0;
        $valor_ipi = 0;
        $valor_frete = 0;
        $valor_desconto = 0;
        $natureza_operacao = '';
        $modelo_nf = '55';
        $tipo_operacao = '0';

        try {
            // Chave de acesso
            $infNFe = $xml->xpath('//nfe:infNFe');
            if (isset($infNFe[0])) {
                $infNFe = $infNFe[0];
                $chave_acesso = (string)$infNFe['Id'];
                $chave_acesso = str_replace('NFe', '', $chave_acesso);
            }

            // Verificar se já existe
            if (!empty($chave_acesso)) {
                $nota_existente = $this->notasentrada_model->getByChaveAcesso($chave_acesso);
                if ($nota_existente) {
                    return [
                        'result' => false,
                        'message' => 'Esta nota fiscal já foi cadastrada anteriormente.'
                    ];
                }
            }

            // Ide (Identificação)
            $ide = $xml->xpath('//nfe:ide');
            if (isset($ide[0])) {
                $ide = $ide[0];
                $numero_nf = (string)$ide->nNF;
                $serie_nf = (string)$ide->serie;
                $data_emissao = (string)$ide->dhEmi;
                $natureza_operacao = (string)$ide->natOp;
                $modelo_nf = (string)$ide->mod;
                $tipo_operacao = (string)$ide->tpNF;
                
                // Converter data
                if (!empty($data_emissao)) {
                    $data_emissao = date('Y-m-d', strtotime($data_emissao));
                }
            }

            // Emitente
            $emit = $xml->xpath('//nfe:emit');
            if (isset($emit[0])) {
                $emit = $emit[0];
                $cnpj_emitente = preg_replace('/[^0-9]/', '', (string)$emit->CNPJ);
                $nome_emitente = (string)$emit->xNome;
            }

            // Destinatário
            $dest = $xml->xpath('//nfe:dest');
            if (isset($dest[0])) {
                $dest = $dest[0];
                $cnpj_destinatario = preg_replace('/[^0-9]/', '', (string)$dest->CNPJ);
                $nome_destinatario = (string)$dest->xNome;
            }

            // Totais
            $total = $xml->xpath('//nfe:total/nfe:ICMSTot');
            if (isset($total[0])) {
                $total = $total[0];
                $valor_total = (float)(string)$total->vNF;
                $valor_produtos = (float)(string)$total->vProd;
                $valor_icms = (float)(string)$total->vICMS;
                $valor_ipi = (float)(string)$total->vIPI;
                $valor_frete = (float)(string)$total->vFrete;
                $valor_desconto = (float)(string)$total->vDesc;
            }

        } catch (Exception $e) {
            return [
                'result' => false,
                'message' => 'Erro ao processar XML: ' . $e->getMessage()
            ];
        }

        // Salvar nota
        $data_nota = [
            'numero_nf' => $numero_nf,
            'serie_nf' => $serie_nf,
            'chave_acesso' => $chave_acesso,
            'cnpj_emitente' => $cnpj_emitente,
            'nome_emitente' => $nome_emitente,
            'cnpj_destinatario' => $cnpj_destinatario,
            'nome_destinatario' => $nome_destinatario,
            'data_emissao' => $data_emissao,
            'data_entrada' => date('Y-m-d'),
            'valor_total' => $valor_total,
            'valor_produtos' => $valor_produtos,
            'valor_icms' => $valor_icms,
            'valor_ipi' => $valor_ipi,
            'valor_frete' => $valor_frete,
            'valor_desconto' => $valor_desconto,
            'natureza_operacao' => $natureza_operacao,
            'modelo_nf' => $modelo_nf,
            'tipo_operacao' => $tipo_operacao,
            'xml_path' => $file_path,
            'xml_content' => $xml_content,
            'status' => 'Processada',
            'usuario_cadastro' => $this->session->userdata('id')
        ];

        // Tentar encontrar fornecedor pelo CNPJ
        if (!empty($cnpj_emitente)) {
            $this->db->where('documento', $cnpj_emitente);
            $fornecedor = $this->db->get('clientes')->row();
            if ($fornecedor) {
                $data_nota['fornecedor_id'] = $fornecedor->idClientes;
            }
        }

        $nota_id = $this->notasentrada_model->add('notas_entrada', $data_nota);

        if (!$nota_id) {
            return [
                'result' => false,
                'message' => 'Erro ao salvar nota no banco de dados.'
            ];
        }

        // Processar itens
        $itens_processados = false;
        try {
            // Tentar múltiplas formas de buscar os itens
            $itens = null;
            
            // Método 1: Via xpath com namespace
            $itens = $xml->xpath('//nfe:det');
            
            // Método 2: Via xpath sem namespace
            if (empty($itens)) {
                $itens = $xml->xpath('//det');
            }
            
            // Método 3: Via estrutura direta
            if (empty($itens) && isset($infNFe->det)) {
                $dets = $infNFe->det;
                if (is_array($dets)) {
                    $itens = $dets;
                } else {
                    $itens = [$dets];
                }
            }
            
            if ($itens && count($itens) > 0) {
                foreach ($itens as $item) {
                    // Tentar buscar produto de diferentes formas
                    $prod = null;
                    
                    // Método 1: Via xpath com namespace
                    $prod_array = $item->xpath('.//nfe:prod');
                    if (isset($prod_array[0])) {
                        $prod = $prod_array[0];
                    }
                    
                    // Método 2: Via xpath sem namespace
                    if (!$prod) {
                        $prod_array = $item->xpath('.//prod');
                        if (isset($prod_array[0])) {
                            $prod = $prod_array[0];
                        }
                    }
                    
                    // Método 3: Via estrutura direta
                    if (!$prod && isset($item->prod)) {
                        $prod = $item->prod;
                    }
                    
                    if ($prod) {
                        $codigo_produto = isset($prod->cProd) ? (string)$prod->cProd : '';
                        $descricao_produto = isset($prod->xProd) ? (string)$prod->xProd : '';
                        $ncm = isset($prod->NCM) ? (string)$prod->NCM : '';
                        $cest = isset($prod->CEST) ? (string)$prod->CEST : '';
                        $cfop = isset($prod->CFOP) ? (string)$prod->CFOP : '';
                        $unidade = isset($prod->uCom) ? (string)$prod->uCom : 'UN';
                        $quantidade = isset($prod->qCom) ? (float)(string)$prod->qCom : 0;
                        $valor_unitario = isset($prod->vUnCom) ? (float)(string)$prod->vUnCom : 0;
                        $valor_total_item = isset($prod->vProd) ? (float)(string)$prod->vProd : 0;
                        
                        // Se valor total for zero, calcular
                        if ($valor_total_item == 0 && $valor_unitario > 0 && $quantidade > 0) {
                            $valor_total_item = $valor_unitario * $quantidade;
                        }
                        
                        $valor_icms_item = 0;
                        $valor_ipi_item = 0;
                        $aliquota_icms = 0;
                        $aliquota_ipi = 0;
                        $origem = '0';
                        $tributacao = '';
                        
                        // Buscar impostos
                        $imposto = null;
                        $imposto_array = $item->xpath('.//nfe:imposto');
                        if (isset($imposto_array[0])) {
                            $imposto = $imposto_array[0];
                        } elseif (isset($item->imposto)) {
                            $imposto = $item->imposto;
                        }
                        
                        if ($imposto) {
                            // ICMS
                            $icms = null;
                            $icms_array = $imposto->xpath('.//nfe:ICMS/*');
                            if (isset($icms_array[0])) {
                                $icms = $icms_array[0];
                            } elseif (isset($imposto->ICMS)) {
                                $icms_children = $imposto->ICMS->children();
                                if (count($icms_children) > 0) {
                                    $icms = $icms_children[0];
                                }
                            }
                            
                            if ($icms) {
                                $valor_icms_item = isset($icms->vICMS) ? (float)(string)$icms->vICMS : 0;
                                $aliquota_icms = isset($icms->pICMS) ? (float)(string)$icms->pICMS : 0;
                                $origem = isset($icms->orig) ? (string)$icms->orig : '0';
                                $tributacao = isset($icms->CST) ? (string)$icms->CST : (isset($icms->CSOSN) ? (string)$icms->CSOSN : '');
                            }
                            
                            // IPI
                            $ipi = null;
                            $ipi_array = $imposto->xpath('.//nfe:IPI/nfe:IPITrib');
                            if (isset($ipi_array[0])) {
                                $ipi = $ipi_array[0];
                            } elseif (isset($imposto->IPI->IPITrib)) {
                                $ipi = $imposto->IPI->IPITrib;
                            }
                            
                            if ($ipi) {
                                $valor_ipi_item = isset($ipi->vIPI) ? (float)(string)$ipi->vIPI : 0;
                                $aliquota_ipi = isset($ipi->pIPI) ? (float)(string)$ipi->pIPI : 0;
                            }
                        }
                        
                        // Tentar encontrar produto pelo código
                        $produto_id = null;
                        if (!empty($codigo_produto)) {
                            $this->db->where('codDeBarra', $codigo_produto);
                            $produto = $this->db->get('produtos')->row();
                            if ($produto) {
                                $produto_id = $produto->idProdutos;
                            }
                        }
                        
                        $data_item = [
                            'nota_entrada_id' => $nota_id,
                            'codigo_produto' => $codigo_produto,
                            'descricao_produto' => $descricao_produto,
                            'ncm' => $ncm,
                            'cest' => $cest,
                            'cfop' => $cfop,
                            'unidade' => $unidade,
                            'quantidade' => $quantidade > 0 ? $quantidade : 1.0,
                            'valor_unitario' => $valor_unitario,
                            'valor_total' => $valor_total_item,
                            'valor_icms' => $valor_icms_item,
                            'valor_ipi' => $valor_ipi_item,
                            'aliquota_icms' => $aliquota_icms,
                            'aliquota_ipi' => $aliquota_ipi,
                            'origem' => $origem,
                            'tributacao' => $tributacao,
                            'produto_id' => $produto_id
                        ];
                        
                        $result = $this->notasentrada_model->add('notas_entrada_itens', $data_item);
                        if ($result) {
                            $itens_processados = true;
                        }
                    }
                }
            }
            
            // Se não processou nenhum item, criar item genérico
            if (!$itens_processados && $valor_total > 0) {
                $data_item = [
                    'nota_entrada_id' => $nota_id,
                    'codigo_produto' => 'PRODUTO',
                    'descricao_produto' => $natureza_operacao ?: 'Produto adquirido',
                    'ncm' => '',
                    'cest' => '',
                    'cfop' => '',
                    'unidade' => 'UN',
                    'quantidade' => 1.0,
                    'valor_unitario' => $valor_produtos,
                    'valor_total' => $valor_produtos,
                    'valor_icms' => $valor_icms,
                    'valor_ipi' => $valor_ipi,
                    'aliquota_icms' => 0,
                    'aliquota_ipi' => 0,
                    'origem' => '0',
                    'tributacao' => '',
                    'produto_id' => null
                ];
                
                $this->notasentrada_model->add('notas_entrada_itens', $data_item);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Erro ao processar itens NFe: ' . $e->getMessage());
            
            // Tentar criar item genérico em caso de erro
            if (!$itens_processados && $valor_total > 0) {
                try {
                    $data_item = [
                        'nota_entrada_id' => $nota_id,
                        'codigo_produto' => 'PRODUTO',
                        'descricao_produto' => $natureza_operacao ?: 'Produto adquirido',
                        'ncm' => '',
                        'cest' => '',
                        'cfop' => '',
                        'unidade' => 'UN',
                        'quantidade' => 1.0,
                        'valor_unitario' => $valor_produtos,
                        'valor_total' => $valor_produtos,
                        'valor_icms' => $valor_icms,
                        'valor_ipi' => $valor_ipi,
                        'aliquota_icms' => 0,
                        'aliquota_ipi' => 0,
                        'origem' => '0',
                        'tributacao' => '',
                        'produto_id' => null
                    ];
                    
                    $this->notasentrada_model->add('notas_entrada_itens', $data_item);
                } catch (Exception $e2) {
                    // Ignorar erro
                }
            }
        }

        return [
            'result' => true,
            'nota_id' => $nota_id,
            'chave_acesso' => $chave_acesso
        ];
    }

    public function buscarSEFAZ()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aNotaEntrada')) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(403)
                ->set_output(json_encode(['result' => false, 'message' => 'Você não tem permissão para adicionar notas de entrada.']));
        }

        $chave_acesso = $this->input->post('chave_acesso');
        $cnpj = preg_replace('/[^0-9]/', '', $chave_acesso);
        
        if (strlen($chave_acesso) != 44) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['result' => false, 'message' => 'Chave de acesso deve ter 44 caracteres.']));
        }

        // Verificar se já existe
        $nota_existente = $this->notasentrada_model->getByChaveAcesso($chave_acesso);
        if ($nota_existente) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['result' => false, 'message' => 'Esta nota fiscal já foi cadastrada anteriormente.']));
        }

        // Buscar na SEFAZ usando API pública (exemplo: ReceitaWS ou similar)
        // Nota: Para produção, você precisará de certificado digital e integração adequada
        $url = "https://www.receitaws.com.br/v1/nfe/{$chave_acesso}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code != 200) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'result' => false,
                    'message' => 'Não foi possível buscar a nota na SEFAZ. Verifique a chave de acesso ou tente fazer upload do XML.'
                ]));
        }

        $data = json_decode($response, true);
        
        if (!$data || isset($data['status']) && $data['status'] == 'ERROR') {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'result' => false,
                    'message' => 'Nota não encontrada na SEFAZ ou chave de acesso inválida.'
                ]));
        }

        // Processar resposta e salvar
        // Nota: A API pública pode não retornar todos os dados. 
        // Para produção, use integração oficial com certificado digital
        
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'result' => true,
                'message' => 'Nota encontrada na SEFAZ. Por favor, faça o upload do XML para processar completamente.',
                'data' => $data
            ]));
    }

    public function reprocessar($id = null)
    {
        if (! $id || ! is_numeric($id)) {
            $this->session->set_flashdata('error', 'Nota não encontrada ou parâmetro inválido.');
            redirect('notasentrada/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eNotaEntrada')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar notas de entrada.');
            redirect(base_url());
        }

        $nota = $this->notasentrada_model->getById($id);
        if (!$nota) {
            $this->session->set_flashdata('error', 'Nota não encontrada.');
            redirect('notasentrada/gerenciar');
        }

        // Verificar se tem XML
        if (empty($nota->xml_content) && empty($nota->xml_path)) {
            $this->session->set_flashdata('error', 'XML da nota não encontrado para reprocessamento.');
            redirect('notasentrada/visualizar/' . $id);
        }

        // Ler XML
        $xml_content = '';
        if (!empty($nota->xml_content)) {
            $xml_content = $nota->xml_content;
        } elseif (!empty($nota->xml_path) && file_exists($nota->xml_path)) {
            $xml_content = file_get_contents($nota->xml_path);
        }

        if (empty($xml_content)) {
            $this->session->set_flashdata('error', 'Não foi possível ler o XML da nota.');
            redirect('notasentrada/visualizar/' . $id);
        }

        // Carregar XML
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xml_content);
        
        if ($xml === false) {
            $this->session->set_flashdata('error', 'XML inválido ou malformado.');
            redirect('notasentrada/visualizar/' . $id);
        }

        // Detectar tipo
        $xml_content_lower = strtolower($xml_content);
        $is_nfse = false;
        if (strpos($xml_content_lower, 'compnfse') !== false || 
            strpos($xml_content_lower, 'nfse') !== false ||
            strpos($xml_content_lower, 'declaracaoprestacaoservico') !== false) {
            $is_nfse = true;
        }

        // Remover itens existentes
        $this->db->where('nota_entrada_id', $id);
        $this->db->delete('notas_entrada_itens');

        // Reprocessar itens
        if ($is_nfse) {
            // Processar NFSe
            $infNfse = null;
            if (isset($xml->CompNfse->Nfse->InfNfse)) {
                $infNfse = $xml->CompNfse->Nfse->InfNfse;
            } elseif (isset($xml->Nfse->InfNfse)) {
                $infNfse = $xml->Nfse->InfNfse;
            }
            
            if ($infNfse && isset($infNfse->DeclaracaoPrestacaoServico->InfDeclaracaoPrestacaoServico->Servico->ListaItensServico)) {
                $listaItens = $infNfse->DeclaracaoPrestacaoServico->InfDeclaracaoPrestacaoServico->Servico->ListaItensServico;
                if (isset($listaItens->ItemServico)) {
                    $itens = $listaItens->ItemServico;
                    if (is_array($itens)) {
                        foreach ($itens as $item) {
                            $this->processarItemNFSe($item, $id);
                        }
                    } else {
                        $this->processarItemNFSe($itens, $id);
                    }
                }
            }
        } else {
            // Processar NFe
            $namespaces = $xml->getNamespaces(true);
            $nfeNamespace = 'http://www.portalfiscal.inf.br/nfe';
            if (isset($namespaces[''])) {
                $nfeNamespace = $namespaces[''];
            }
            $xml->registerXPathNamespace('nfe', $nfeNamespace);
            
            $itens = $xml->xpath('//nfe:det');
            if (empty($itens)) {
                $itens = $xml->xpath('//det');
            }
            
            if ($itens && count($itens) > 0) {
                foreach ($itens as $item) {
                    $prod = null;
                    $prod_array = $item->xpath('.//nfe:prod');
                    if (isset($prod_array[0])) {
                        $prod = $prod_array[0];
                    } else {
                        $prod_array = $item->xpath('.//prod');
                        if (isset($prod_array[0])) {
                            $prod = $prod_array[0];
                        } elseif (isset($item->prod)) {
                            $prod = $item->prod;
                        }
                    }
                    
                    if ($prod) {
                        $codigo_produto = isset($prod->cProd) ? (string)$prod->cProd : '';
                        $descricao_produto = isset($prod->xProd) ? (string)$prod->xProd : '';
                        $ncm = isset($prod->NCM) ? (string)$prod->NCM : '';
                        $cest = isset($prod->CEST) ? (string)$prod->CEST : '';
                        $cfop = isset($prod->CFOP) ? (string)$prod->CFOP : '';
                        $unidade = isset($prod->uCom) ? (string)$prod->uCom : 'UN';
                        $quantidade = isset($prod->qCom) ? (float)(string)$prod->qCom : 0;
                        $valor_unitario = isset($prod->vUnCom) ? (float)(string)$prod->vUnCom : 0;
                        $valor_total_item = isset($prod->vProd) ? (float)(string)$prod->vProd : 0;
                        
                        if ($valor_total_item == 0 && $valor_unitario > 0 && $quantidade > 0) {
                            $valor_total_item = $valor_unitario * $quantidade;
                        }
                        
                        $imposto = null;
                        $imposto_array = $item->xpath('.//nfe:imposto');
                        if (isset($imposto_array[0])) {
                            $imposto = $imposto_array[0];
                        } elseif (isset($item->imposto)) {
                            $imposto = $item->imposto;
                        }
                        
                        $valor_icms_item = 0;
                        $valor_ipi_item = 0;
                        $aliquota_icms = 0;
                        $aliquota_ipi = 0;
                        $origem = '0';
                        $tributacao = '';
                        
                        if ($imposto) {
                            $icms = null;
                            $icms_array = $imposto->xpath('.//nfe:ICMS/*');
                            if (isset($icms_array[0])) {
                                $icms = $icms_array[0];
                            } elseif (isset($imposto->ICMS)) {
                                $icms_children = $imposto->ICMS->children();
                                if (count($icms_children) > 0) {
                                    $icms = $icms_children[0];
                                }
                            }
                            
                            if ($icms) {
                                $valor_icms_item = isset($icms->vICMS) ? (float)(string)$icms->vICMS : 0;
                                $aliquota_icms = isset($icms->pICMS) ? (float)(string)$icms->pICMS : 0;
                                $origem = isset($icms->orig) ? (string)$icms->orig : '0';
                                $tributacao = isset($icms->CST) ? (string)$icms->CST : (isset($icms->CSOSN) ? (string)$icms->CSOSN : '');
                            }
                            
                            $ipi = null;
                            $ipi_array = $imposto->xpath('.//nfe:IPI/nfe:IPITrib');
                            if (isset($ipi_array[0])) {
                                $ipi = $ipi_array[0];
                            } elseif (isset($imposto->IPI->IPITrib)) {
                                $ipi = $imposto->IPI->IPITrib;
                            }
                            
                            if ($ipi) {
                                $valor_ipi_item = isset($ipi->vIPI) ? (float)(string)$ipi->vIPI : 0;
                                $aliquota_ipi = isset($ipi->pIPI) ? (float)(string)$ipi->pIPI : 0;
                            }
                        }
                        
                        $produto_id = null;
                        if (!empty($codigo_produto)) {
                            $this->db->where('codDeBarra', $codigo_produto);
                            $produto = $this->db->get('produtos')->row();
                            if ($produto) {
                                $produto_id = $produto->idProdutos;
                            }
                        }
                        
                        $data_item = [
                            'nota_entrada_id' => $id,
                            'codigo_produto' => $codigo_produto,
                            'descricao_produto' => $descricao_produto,
                            'ncm' => $ncm,
                            'cest' => $cest,
                            'cfop' => $cfop,
                            'unidade' => $unidade,
                            'quantidade' => $quantidade > 0 ? $quantidade : 1.0,
                            'valor_unitario' => $valor_unitario,
                            'valor_total' => $valor_total_item,
                            'valor_icms' => $valor_icms_item,
                            'valor_ipi' => $valor_ipi_item,
                            'aliquota_icms' => $aliquota_icms,
                            'aliquota_ipi' => $aliquota_ipi,
                            'origem' => $origem,
                            'tributacao' => $tributacao,
                            'produto_id' => $produto_id
                        ];
                        
                        $this->notasentrada_model->add('notas_entrada_itens', $data_item);
                    }
                }
            }
        }

        log_info('Reprocessou itens da nota de entrada. ID: ' . $id);
        $this->session->set_flashdata('success', 'Itens da nota reprocessados com sucesso!');
        redirect('notasentrada/visualizar/' . $id);
    }

    public function adicionarEstoque()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aProduto')) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(403)
                ->set_output(json_encode(['result' => false, 'message' => 'Você não tem permissão para adicionar produtos.']));
        }

        $nota_id = $this->input->post('nota_id');
        $tipo_calculo = $this->input->post('tipo_calculo');
        $markup_global = $this->input->post('markup_global');
        $processar_itens = $this->input->post('processar_item');
        $markups = $this->input->post('markup');
        $precos_venda = $this->input->post('preco_venda');

        if (!$nota_id) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['result' => false, 'message' => 'ID da nota não informado.']));
        }

        // Buscar nota
        $nota = $this->notasentrada_model->getById($nota_id);
        if (!$nota) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(404)
                ->set_output(json_encode(['result' => false, 'message' => 'Nota não encontrada.']));
        }

        // Buscar itens da nota
        $itens = $this->notasentrada_model->getItensByNota($nota_id);
        if (empty($itens)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['result' => false, 'message' => 'Nenhum item encontrado na nota.']));
        }

        $this->load->model('produtos_model');
        $sucesso = 0;
        $erros = 0;
        $atualizados = 0;
        $criados = 0;
        $detalhes_erros = [];

        foreach ($itens as $item) {
            // Verificar se o item deve ser processado
            if (!isset($processar_itens[$item->idItemNotaEntrada]) || $processar_itens[$item->idItemNotaEntrada] != '1') {
                continue;
            }

            $preco_compra = $item->valor_unitario;
            $quantidade = $item->quantidade;
            
            // Calcular preço de venda
            $preco_venda = 0;
            if ($tipo_calculo == 'preco' && isset($precos_venda[$item->idItemNotaEntrada])) {
                $preco_venda_str = $precos_venda[$item->idItemNotaEntrada];
                // Remover prefixo R$ e espaços, depois converter vírgula para ponto
                $preco_venda_str = str_replace(['R$', ' ', '.'], '', $preco_venda_str);
                $preco_venda_str = str_replace(',', '.', $preco_venda_str);
                $preco_venda = (float)$preco_venda_str;
            } else {
                // Usar markup
                $markup = isset($markups[$item->idItemNotaEntrada]) ? (float)$markups[$item->idItemNotaEntrada] : (float)$markup_global;
                $preco_venda = $preco_compra * (1 + ($markup / 100));
            }

            // Verificar se produto já existe
            $produto_existente = null;
            if (!empty($item->codigo_produto)) {
                $this->db->where('codDeBarra', $item->codigo_produto);
                $produto_existente = $this->db->get('produtos')->row();
            }

            // Se não encontrou pelo código, tentar pela descrição
            if (!$produto_existente && !empty($item->descricao_produto)) {
                $this->db->where('descricao', $item->descricao_produto);
                $produto_existente = $this->db->get('produtos')->row();
            }

            if ($produto_existente) {
                // Atualizar produto existente
                $novo_estoque = $produto_existente->estoque + $quantidade;
                
                $data_produto = [
                    'precoCompra' => $preco_compra,
                    'precoVenda' => $preco_venda,
                    'estoque' => $novo_estoque
                ];
                
                // Atualizar campos fiscais se estiverem vazios no produto
                if (empty($produto_existente->ncm) && !empty($item->ncm)) {
                    $data_produto['ncm'] = $item->ncm;
                }
                if (empty($produto_existente->cest) && !empty($item->cest)) {
                    $data_produto['cest'] = $item->cest;
                }
                if (empty($produto_existente->cfop) && !empty($item->cfop)) {
                    $data_produto['cfop'] = $item->cfop;
                }
                if (empty($produto_existente->origem) && !empty($item->origem)) {
                    $data_produto['origem'] = $item->origem;
                }
                if (empty($produto_existente->tributacao) && !empty($item->tributacao)) {
                    $data_produto['tributacao'] = $item->tributacao;
                }

                if ($this->produtos_model->edit('produtos', $data_produto, 'idProdutos', $produto_existente->idProdutos)) {
                    $sucesso++;
                    $atualizados++;
                    
                    // Atualizar referência do item da nota
                    $this->db->where('idItemNotaEntrada', $item->idItemNotaEntrada);
                    $this->db->update('notas_entrada_itens', ['produto_id' => $produto_existente->idProdutos]);
                } else {
                    $erros++;
                    $detalhes_erros[] = "Erro ao atualizar produto: " . $item->descricao_produto;
                }
            } else {
                // Criar novo produto
                $codigo_barras = !empty($item->codigo_produto) ? $item->codigo_produto : 'PROD' . time() . rand(1000, 9999);
                
                $data_produto = [
                    'codDeBarra' => $codigo_barras,
                    'descricao' => $item->descricao_produto,
                    'unidade' => !empty($item->unidade) ? $item->unidade : 'UN',
                    'precoCompra' => $preco_compra,
                    'precoVenda' => $preco_venda,
                    'estoque' => $quantidade,
                    'estoqueMinimo' => 0,
                    'entrada' => 1,
                    'saida' => 1,
                    'ncm' => !empty($item->ncm) ? $item->ncm : null,
                    'cest' => !empty($item->cest) ? $item->cest : null,
                    'cfop' => !empty($item->cfop) ? $item->cfop : null,
                    'origem' => !empty($item->origem) ? $item->origem : '0',
                    'tributacao' => !empty($item->tributacao) ? $item->tributacao : null
                ];

                if ($this->produtos_model->add('produtos', $data_produto)) {
                    // Buscar ID do produto criado
                    $this->db->where('codDeBarra', $codigo_barras);
                    $this->db->order_by('idProdutos', 'DESC');
                    $this->db->limit(1);
                    $produto_criado = $this->db->get('produtos')->row();
                    
                    if ($produto_criado) {
                        // Atualizar referência do item da nota
                        $this->db->where('idItemNotaEntrada', $item->idItemNotaEntrada);
                        $this->db->update('notas_entrada_itens', ['produto_id' => $produto_criado->idProdutos]);
                    }
                    
                    $sucesso++;
                    $criados++;
                } else {
                    $erros++;
                    $detalhes_erros[] = "Erro ao criar produto: " . $item->descricao_produto;
                }
            }
        }

        // Atualizar status da nota
        if ($sucesso > 0) {
            $this->db->where('idNotaEntrada', $nota_id);
            $this->db->update('notas_entrada', ['status' => 'Processada e Estoque Atualizado']);
        }

        $mensagem = "Processamento concluído! ";
        $mensagem .= "Sucesso: {$sucesso} (Criados: {$criados}, Atualizados: {$atualizados})";
        if ($erros > 0) {
            $mensagem .= ", Erros: {$erros}";
        }

        log_info('Adicionou itens da nota de entrada ao estoque. Nota ID: ' . $nota_id . ', Sucesso: ' . $sucesso . ', Erros: ' . $erros);

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'result' => $sucesso > 0,
                'message' => $mensagem,
                'sucesso' => $sucesso,
                'criados' => $criados,
                'atualizados' => $atualizados,
                'erros' => $erros,
                'detalhes_erros' => $detalhes_erros
            ]));
    }

    public function excluir($id = null)
    {
        if (! $id || ! is_numeric($id)) {
            $this->session->set_flashdata('error', 'Nota não encontrada ou parâmetro inválido.');
            redirect('notasentrada/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eNotaEntrada')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir notas de entrada.');
            redirect(base_url());
        }

        $nota = $this->notasentrada_model->getById($id);
        if (!$nota) {
            $this->session->set_flashdata('error', 'Nota não encontrada.');
            redirect('notasentrada/gerenciar');
        }

        // Remover arquivo XML se existir
        if (!empty($nota->xml_path) && file_exists($nota->xml_path)) {
            @unlink($nota->xml_path);
        }

        if ($this->notasentrada_model->delete('notas_entrada', 'idNotaEntrada', $id)) {
            log_info('Excluiu nota de entrada. ID: ' . $id);
            $this->session->set_flashdata('success', 'Nota de entrada excluída com sucesso!');
        } else {
            $this->session->set_flashdata('error', 'Erro ao excluir nota de entrada.');
        }

        redirect('notasentrada/gerenciar');
    }
}

