<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Produtos extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper('form');
        $this->load->model('produtos_model');
        $this->data['menuProdutos'] = 'Produtos';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar produtos.');
            redirect(base_url());
        }

        $pesquisa = $this->input->get('pesquisa');

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('produtos/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->produtos_model->count('produtos');
        if($pesquisa) {
            $this->data['configuration']['suffix'] = "?pesquisa={$pesquisa}";
            $this->data['configuration']['first_url'] = base_url("index.php/produtos")."\?pesquisa={$pesquisa}";
        }

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->produtos_model->get('produtos', '*', $pesquisa, $this->data['configuration']['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'produtos/produtos';

        return $this->layout();
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar produtos.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('produtos') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $precoCompra = $this->input->post('precoCompra');
            $precoCompra = str_replace(',', '', $precoCompra);
            $precoVenda = $this->input->post('precoVenda');
            $precoVenda = str_replace(',', '', $precoVenda);
            $data = [
                'codDeBarra' => set_value('codDeBarra'),
                'descricao' => set_value('descricao'),
                'unidade' => set_value('unidade'),
                'precoCompra' => $precoCompra,
                'precoVenda' => $precoVenda,
                'estoque' => set_value('estoque'),
                'estoqueMinimo' => set_value('estoqueMinimo'),
                'saida' => set_value('saida'),
                'entrada' => set_value('entrada'),
            ];

            if ($this->produtos_model->add('produtos', $data) == true) {
                $this->session->set_flashdata('success', 'Produto adicionado com sucesso!');
                log_info('Adicionou um produto');
                redirect(site_url('produtos/adicionar/'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>An Error Occured.</p></div>';
            }
        }
        $this->data['view'] = 'produtos/adicionarProduto';

        return $this->layout();
    }

    public function editar()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3)) || ! $this->produtos_model->getById($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Produto não encontrado ou parâmetro inválido.');
            redirect('produtos/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar produtos.');
            redirect(base_url());
        }
        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('produtos') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $precoCompra = $this->input->post('precoCompra');
            $precoCompra = str_replace(',', '', $precoCompra);
            $precoVenda = $this->input->post('precoVenda');
            $precoVenda = str_replace(',', '', $precoVenda);
            $data = [
                'codDeBarra' => set_value('codDeBarra'),
                'descricao' => $this->input->post('descricao'),
                'unidade' => $this->input->post('unidade'),
                'precoCompra' => $precoCompra,
                'precoVenda' => $precoVenda,
                'estoque' => $this->input->post('estoque'),
                'estoqueMinimo' => $this->input->post('estoqueMinimo'),
                'saida' => set_value('saida'),
                'entrada' => set_value('entrada'),
            ];

            if ($this->produtos_model->edit('produtos', $data, 'idProdutos', $this->input->post('idProdutos')) == true) {
                $this->session->set_flashdata('success', 'Produto editado com sucesso!');
                log_info('Alterou um produto. ID: ' . $this->input->post('idProdutos'));
                redirect(site_url('produtos/editar/') . $this->input->post('idProdutos'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>An Error Occured</p></div>';
            }
        }

        $this->data['result'] = $this->produtos_model->getById($this->uri->segment(3));

        $this->data['view'] = 'produtos/editarProduto';

        return $this->layout();
    }

    public function visualizar()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar produtos.');
            redirect(base_url());
        }

        $this->data['result'] = $this->produtos_model->getById($this->uri->segment(3));

        if ($this->data['result'] == null) {
            $this->session->set_flashdata('error', 'Produto não encontrado.');
            redirect(site_url('produtos/editar/') . $this->input->post('idProdutos'));
        }

        $this->data['view'] = 'produtos/visualizarProduto';

        return $this->layout();
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir produtos.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir produto.');
            redirect(base_url() . 'index.php/produtos/gerenciar/');
        }

        $this->produtos_model->delete('produtos_os', 'produtos_id', $id);
        $this->produtos_model->delete('itens_de_vendas', 'produtos_id', $id);
        $this->produtos_model->delete('produtos', 'idProdutos', $id);

        log_info('Removeu um produto. ID: ' . $id);

        $this->session->set_flashdata('success', 'Produto excluido com sucesso!');
        redirect(site_url('produtos/gerenciar/'));
    }

    public function atualizar_estoque()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para atualizar estoque de produtos.');
            redirect(base_url());
        }

        $idProduto = $this->input->post('id');
        $novoEstoque = $this->input->post('estoque');
        $estoqueAtual = $this->input->post('estoqueAtual');

        $estoque = $estoqueAtual + $novoEstoque;

        $data = [
            'estoque' => $estoque,
        ];

        if ($this->produtos_model->edit('produtos', $data, 'idProdutos', $idProduto) == true) {
            $this->session->set_flashdata('success', 'Estoque de Produto atualizado com sucesso!');
            log_info('Atualizou estoque de um produto. ID: ' . $idProduto);
            redirect(site_url('produtos/visualizar/') . $idProduto);
        } else {
            $this->data['custom_error'] = '<div class="alert">Ocorreu um erro.</div>';
        }
    }

    /**
     * Exibe a página de importação em massa de produtos
     */
    public function importar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar produtos.');
            redirect(base_url());
        }

        $this->data['view'] = 'produtos/importar';
        return $this->layout();
    }

    /**
     * Processa o upload e importação do arquivo CSV
     */
    public function processarImportacao()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar produtos.');
            redirect(base_url());
        }

        // Configurar upload
        $config['upload_path'] = './assets/arquivos/';
        $config['allowed_types'] = 'csv';
        $config['max_size'] = 10240; // 10MB
        $config['file_name'] = 'importacao_produtos_' . date('YmdHis') . '.csv';

        // Criar diretório se não existir
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('arquivo_csv')) {
            $this->session->set_flashdata('error', 'Erro no upload: ' . $this->upload->display_errors());
            redirect(site_url('produtos/importar'));
        }

        $upload_data = $this->upload->data();
        $file_path = $upload_data['full_path'];

        // Processar CSV
        $resultado = $this->processarCSV($file_path);

        // Remover arquivo temporário
        @unlink($file_path);

        // Preparar mensagem de resultado
        $mensagem = "Importação concluída! ";
        $mensagem .= "Sucesso: {$resultado['sucesso']}, ";
        $mensagem .= "Erros: {$resultado['erros']}, ";
        $mensagem .= "Duplicados: {$resultado['duplicados']}";

        if ($resultado['sucesso'] > 0) {
            $this->session->set_flashdata('success', $mensagem);
            log_info('Importou produtos em massa. Sucesso: ' . $resultado['sucesso'] . ', Erros: ' . $resultado['erros']);
        } else {
            $this->session->set_flashdata('error', $mensagem);
        }

        // Se houver erros detalhados, armazenar na sessão
        if (!empty($resultado['detalhes_erros'])) {
            $this->session->set_flashdata('detalhes_erros', $resultado['detalhes_erros']);
        }

        redirect(site_url('produtos/gerenciar/'));
    }

    /**
     * Processa o arquivo CSV e cadastra os produtos
     */
    private function processarCSV($file_path)
    {
        $resultado = [
            'sucesso' => 0,
            'erros' => 0,
            'duplicados' => 0,
            'detalhes_erros' => []
        ];

        if (($handle = fopen($file_path, 'r')) !== FALSE) {
            // Pular cabeçalho
            $cabecalho = fgetcsv($handle, 1000, ',');
            
            $linha = 1; // Começa em 2 (já que pulou cabeçalho)
            
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $linha++;
                
                // Validar se tem dados suficientes
                if (count($data) < 5) {
                    $resultado['erros']++;
                    $resultado['detalhes_erros'][] = "Linha {$linha}: Dados insuficientes";
                    continue;
                }

                // Mapear dados do CSV
                $codDeBarra = trim($data[0] ?? '');
                $descricao = trim($data[1] ?? '');
                $unidade = trim($data[2] ?? 'UN');
                $precoCompra = str_replace(',', '.', str_replace('.', '', trim($data[3] ?? '0')));
                $precoVenda = str_replace(',', '.', str_replace('.', '', trim($data[4] ?? '0')));
                $estoque = intval(trim($data[5] ?? '0'));
                $estoqueMinimo = !empty($data[6]) ? intval(trim($data[6])) : 0;
                $entrada = isset($data[7]) ? (strtolower(trim($data[7])) == 'sim' || trim($data[7]) == '1') : true;
                $saida = isset($data[8]) ? (strtolower(trim($data[8])) == 'sim' || trim($data[8]) == '1') : true;
                // Campos de nota fiscal
                $ncm = trim($data[9] ?? '');
                $cest = trim($data[10] ?? '');
                $cfop = trim($data[11] ?? '');
                $origem = trim($data[12] ?? '0');
                $tributacao = trim($data[13] ?? '');

                // Validações básicas
                if (empty($descricao)) {
                    $resultado['erros']++;
                    $resultado['detalhes_erros'][] = "Linha {$linha}: Descrição do produto é obrigatória";
                    continue;
                }

                if (empty($unidade)) {
                    $unidade = 'UN';
                }

                if (empty($precoVenda) || $precoVenda <= 0) {
                    $resultado['erros']++;
                    $resultado['detalhes_erros'][] = "Linha {$linha}: Preço de venda é obrigatório e deve ser maior que zero";
                    continue;
                }

                // Verificar se código de barras já existe (se fornecido)
                if (!empty($codDeBarra)) {
                    $this->db->where('codDeBarra', $codDeBarra);
                    $existe = $this->db->get('produtos')->row();
                    if ($existe) {
                        $resultado['duplicados']++;
                        continue;
                    }
                }

                // Preparar dados para inserção
                $dados = [
                    'codDeBarra' => $codDeBarra ?: 'PROD' . time() . rand(1000, 9999),
                    'descricao' => $descricao,
                    'unidade' => $unidade,
                    'precoCompra' => $precoCompra > 0 ? $precoCompra : null,
                    'precoVenda' => $precoVenda,
                    'estoque' => $estoque,
                    'estoqueMinimo' => $estoqueMinimo,
                    'entrada' => $entrada ? 1 : 0,
                    'saida' => $saida ? 1 : 0,
                    'ncm' => !empty($ncm) ? $ncm : null,
                    'cest' => !empty($cest) ? $cest : null,
                    'cfop' => !empty($cfop) ? $cfop : null,
                    'origem' => !empty($origem) ? $origem : '0',
                    'tributacao' => !empty($tributacao) ? $tributacao : null,
                ];

                // Tentar inserir
                if ($this->produtos_model->add('produtos', $dados)) {
                    $resultado['sucesso']++;
                } else {
                    $resultado['erros']++;
                    $resultado['detalhes_erros'][] = "Linha {$linha}: Erro ao cadastrar produto";
                }
            }
            
            fclose($handle);
        }

        return $resultado;
    }

    /**
     * Gera e disponibiliza o arquivo modelo CSV para download
     */
    public function downloadModelo()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aProduto')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar produtos.');
            redirect(base_url());
        }

        $filename = 'modelo_importacao_produtos.csv';
        $file_path = './assets/arquivos/' . $filename;

        // Criar diretório se não existir
        if (!is_dir('./assets/arquivos/')) {
            mkdir('./assets/arquivos/', 0777, true);
        }

        // Criar arquivo modelo se não existir
        if (!file_exists($file_path)) {
            $fp = fopen($file_path, 'w');
            
            // Cabeçalho
            fputcsv($fp, [
                'Código de Barras',
                'Descrição',
                'Unidade',
                'Preço de Compra',
                'Preço de Venda',
                'Estoque',
                'Estoque Mínimo',
                'Entrada (Sim/Não)',
                'Saída (Sim/Não)',
                'NCM',
                'CEST',
                'CFOP',
                'Origem (0-Nacional, 1-Estrangeira)',
                'Tributação ICMS'
            ]);

            // Exemplo de dados
            fputcsv($fp, [
                '7891234567890',
                'Produto Exemplo 1',
                'UN',
                '10.50',
                '25.90',
                '100',
                '10',
                'Sim',
                'Sim',
                '12345678',
                '1234567',
                '5102',
                '0',
                '00'
            ]);

            fputcsv($fp, [
                '7891234567891',
                'Produto Exemplo 2',
                'KG',
                '5.00',
                '12.00',
                '50',
                '5',
                'Sim',
                'Sim',
                '87654321',
                '',
                '5101',
                '0',
                '00'
            ]);

            fclose($fp);
        }

        // Forçar download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($file_path);
        exit;
    }
}
