<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Clientes extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('clientes_model');
        $this->data['menuClientes'] = 'clientes';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar clientes.');
            redirect(base_url());
        }

        $pesquisa = $this->input->get('pesquisa');

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('clientes/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->clientes_model->count('clientes');
        if($pesquisa) {
            $this->data['configuration']['suffix'] = "?pesquisa={$pesquisa}";
            $this->data['configuration']['first_url'] = base_url("index.php/clientes")."\?pesquisa={$pesquisa}";
        }

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->clientes_model->get('clientes', '*', $pesquisa, $this->data['configuration']['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'clientes/clientes';

        return $this->layout();
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar clientes.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $senhaCliente = $this->input->post('senha') ? $this->input->post('senha') : preg_replace('/[^\p{L}\p{N}\s]/', '', set_value('documento'));

        $cpf_cnpj = preg_replace('/[^\p{L}\p{N}\s]/', '', set_value('documento'));

        if (strlen($cpf_cnpj) == 11) {
            $pessoa_fisica = true;
        } else {
            $pessoa_fisica = false;
        }

        if ($this->form_validation->run('clientes') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $email = trim(set_value('email'));
            // Limpar emails de exemplo ou inválidos
            if ($email && (strpos($email, '@exemplo.com') !== false || $email === '...' || empty($email))) {
                $email = '';
            }
            
            if ($email && $this->clientes_model->emailExists($email)) {
                $this->data['custom_error'] = '<div class="form_error"><p>Este e-mail já está sendo utilizado por outro cliente.</p></div>';
            } else {
                $data = [
                'nomeCliente' => set_value('nomeCliente'),
                'contato' => set_value('contato'),
                'pessoa_fisica' => $pessoa_fisica,
                'documento' => set_value('documento'),
                'telefone' => set_value('telefone'),
                'celular' => set_value('celular'),
                'email' => $email ?: null,
                'senha' => password_hash($senhaCliente, PASSWORD_DEFAULT),
                'rua' => set_value('rua'),
                'numero' => set_value('numero'),
                'complemento' => set_value('complemento'),
                'bairro' => set_value('bairro'),
                'cidade' => set_value('cidade'),
                'estado' => set_value('estado'),
                'cep' => set_value('cep'),
                'dataCadastro' => date('Y-m-d'),
                'fornecedor' => $this->input->post('fornecedor') ? 1 : 0,
            ];

            if ($this->clientes_model->add('clientes', $data) == true) {
                $this->session->set_flashdata('success', 'Cliente adicionado com sucesso!');
                log_info('Adicionou um cliente.');
                redirect(site_url('clientes/'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
            }
        }

        $this->data['view'] = 'clientes/adicionarCliente';

        return $this->layout();
    }

    public function editar()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3)) || ! $this->clientes_model->getById($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Cliente não encontrado ou parâmetro inválido.');
            redirect('clientes/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar clientes.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('clientes') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            
            $email = trim($this->input->post('email'));
            // Limpar emails de exemplo ou inválidos
            if ($email && (strpos($email, '@exemplo.com') !== false || $email === '...' || empty($email))) {
                $email = '';
            }
            
            $idCliente = $this->input->post('idClientes');
            if ($email && $this->clientes_model->emailExists($email, $idCliente)) {
                $this->data['custom_error'] = '<div class="form_error"><p>Este e-mail já está sendo utilizado por outro cliente.</p></div>';
            } else {
                $senha = $this->input->post('senha');
            if ($senha != null) {
                $senha = password_hash($senha, PASSWORD_DEFAULT);

                $data = [
                    'nomeCliente' => $this->input->post('nomeCliente'),
                    'contato' => $this->input->post('contato'),
                    'documento' => $this->input->post('documento'),
                    'telefone' => $this->input->post('telefone'),
                    'celular' => $this->input->post('celular'),
                    'email' => $email ?: null,
                    'senha' => $senha,
                    'rua' => $this->input->post('rua'),
                    'numero' => $this->input->post('numero'),
                    'complemento' => $this->input->post('complemento'),
                    'bairro' => $this->input->post('bairro'),
                    'cidade' => $this->input->post('cidade'),
                    'estado' => $this->input->post('estado'),
                    'cep' => $this->input->post('cep'),
                    'fornecedor' => (set_value('fornecedor') == true ? 1 : 0),
                ];
            } else {
                $data = [
                    'nomeCliente' => $this->input->post('nomeCliente'),
                    'contato' => $this->input->post('contato'),
                    'documento' => $this->input->post('documento'),
                    'telefone' => $this->input->post('telefone'),
                    'celular' => $this->input->post('celular'),
                    'email' => $email ?: null,
                    'rua' => $this->input->post('rua'),
                    'numero' => $this->input->post('numero'),
                    'complemento' => $this->input->post('complemento'),
                    'bairro' => $this->input->post('bairro'),
                    'cidade' => $this->input->post('cidade'),
                    'estado' => $this->input->post('estado'),
                    'cep' => $this->input->post('cep'),
                    'fornecedor' => (set_value('fornecedor') == true ? 1 : 0),
                ];
            }

            if ($this->clientes_model->edit('clientes', $data, 'idClientes', $this->input->post('idClientes')) == true) {
                $this->session->set_flashdata('success', 'Cliente editado com sucesso!');
                log_info('Alterou um cliente. ID' . $this->input->post('idClientes'));
                redirect(site_url('clientes/editar/') . $this->input->post('idClientes'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro</p></div>';
            }
            }
        }

        $this->data['result'] = $this->clientes_model->getById($this->uri->segment(3));
        $this->data['view'] = 'clientes/editarCliente';

        return $this->layout();
    }

    public function visualizar()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar clientes.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->data['result'] = $this->clientes_model->getById($this->uri->segment(3));
        $this->data['results'] = $this->clientes_model->getOsByCliente($this->uri->segment(3));
        $this->data['result_vendas'] = $this->clientes_model->getAllVendasByClient($this->uri->segment(3));
        $this->data['view'] = 'clientes/visualizar';

        return $this->layout();
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir clientes.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir cliente.');
            redirect(site_url('clientes/gerenciar/'));
        }

        $os = $this->clientes_model->getAllOsByClient($id);
        if ($os != null) {
            $this->clientes_model->removeClientOs($os);
        }

        // excluindo Vendas vinculadas ao cliente
        $vendas = $this->clientes_model->getAllVendasByClient($id);
        if ($vendas != null) {
            $this->clientes_model->removeClientVendas($vendas);
        }

        $this->clientes_model->delete('clientes', 'idClientes', $id);
        log_info('Removeu um cliente. ID' . $id);

        $this->session->set_flashdata('success', 'Cliente excluido com sucesso!');
        redirect(site_url('clientes/gerenciar/'));
    }

    /**
     * Exibe a página de importação em massa
     */
    public function importar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar clientes.');
            redirect(base_url());
        }

        $this->data['view'] = 'clientes/importar';
        return $this->layout();
    }

    /**
     * Processa o upload e importação do arquivo CSV
     */
    public function processarImportacao()
    {
        if (! $this->permission->checkPermission($this->session->userdata("permissao"), "aCliente")) {
            $this->session->set_flashdata("error", "Você não tem permissão para adicionar clientes.");
            redirect(base_url());
        }

        $config["upload_path"] = "./assets/arquivos/";
        $config["allowed_types"] = "csv";
        $config["max_size"] = 5120; // 5MB
        $config["file_name"] = "importacao_" . date("YmdHis") . ".csv";

        $this->load->library("upload", $config);

        if (!$this->upload->do_upload("arquivo_csv")) {
            $error = $this->upload->display_errors();
            $this->session->set_flashdata("error", "Erro no upload: " . $error);
            redirect(site_url("clientes/importar"));
        }

        $upload_data = $this->upload->data();
        $file_path = $upload_data["full_path"];

        // Processar o CSV
        $resultado = $this->processarCSV($file_path);

        // Remover arquivo após processamento
        @unlink($file_path);

        // Exibir resultado
        $this->session->set_flashdata("success", 
            "Importação concluída!<br>" .
            "✅ Clientes cadastrados: {$resultado["sucesso"]}<br>" .
            "❌ Erros: {$resultado["erros"]}<br>" .
            "⚠️ Ignorados (duplicados): {$resultado["duplicados"]}"
        );

        if (!empty($resultado["detalhes_erros"])) {
            $this->session->set_flashdata("error", "Detalhes dos erros:<br>" . implode("<br>", array_slice($resultado["detalhes_erros"], 0, 10)));
        }

        redirect(site_url("clientes/gerenciar/"));
    }

    /**
     * Processa o arquivo CSV e cadastra os clientes
     */
    private function processarCSV($file_path)
    {
        $resultado = [
            "sucesso" => 0,
            "erros" => 0,
            "duplicados" => 0,
            "detalhes_erros" => []
        ];

        if (($handle = fopen($file_path, "r")) !== FALSE) {
            // Pular cabeçalho
            $cabecalho = fgetcsv($handle, 1000, ",");
            
            $linha = 1; // Começa em 2 (já que pulou cabeçalho)
            
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $linha++;
                
                // Validar se tem dados suficientes
                if (count($data) < 3) {
                    $resultado["erros"]++;
                    $resultado["detalhes_erros"][] = "Linha {$linha}: Dados insuficientes";
                    continue;
                }

                // Mapear dados do CSV
                $nomeCliente = trim($data[0] ?? "");
                $documento = preg_replace("/[^\p{L}\p{N}\s]/", "", trim($data[1] ?? ""));
                $email = trim($data[2] ?? "");
                $telefone = trim($data[3] ?? "");
                $celular = trim($data[4] ?? "");
                $contato = trim($data[5] ?? "");
                $rua = trim($data[6] ?? "");
                $numero = trim($data[7] ?? "");
                $complemento = trim($data[8] ?? "");
                $bairro = trim($data[9] ?? "");
                $cidade = trim($data[10] ?? "");
                $estado = trim($data[11] ?? "");
                $cep = trim($data[12] ?? "");
                $fornecedor = isset($data[13]) ? (strtolower(trim($data[13])) == "sim" || trim($data[13]) == "1") : false;
                $senha = trim($data[14] ?? "");

                // Validações básicas
                if (empty($nomeCliente)) {
                    $resultado["erros"]++;
                    $resultado["detalhes_erros"][] = "Linha {$linha}: Nome do cliente é obrigatório";
                    continue;
                }

                if (empty($documento)) {
                    $resultado["erros"]++;
                    $resultado["detalhes_erros"][] = "Linha {$linha}: CPF/CNPJ é obrigatório";
                    continue;
                }

                // Verificar se email já existe
                if (!empty($email) && $this->clientes_model->emailExists($email)) {
                    $resultado["duplicados"]++;
                    continue;
                }

                // Determinar se é pessoa física ou jurídica
                $pessoa_fisica = strlen($documento) == 11 ? true : false;

                // Gerar senha se não fornecida
                if (empty($senha)) {
                    $senha = preg_replace("/[^\p{L}\p{N}\s]/", "", $documento);
                }

                // Preparar dados para inserção
                $dados = [
                    "nomeCliente" => $nomeCliente,
                    "contato" => $contato,
                    "pessoa_fisica" => $pessoa_fisica,
                    "documento" => $documento,
                    "telefone" => $telefone,
                    "celular" => $celular,
                    "email" => $email ?: "cliente" . time() . rand(1000, 9999) . "@exemplo.com",
                    "senha" => password_hash($senha, PASSWORD_DEFAULT),
                    "rua" => $rua,
                    "numero" => $numero,
                    "complemento" => $complemento,
                    "bairro" => $bairro,
                    "cidade" => $cidade,
                    "estado" => $estado,
                    "cep" => $cep,
                    "dataCadastro" => date("Y-m-d"),
                    "fornecedor" => $fornecedor ? 1 : 0,
                ];

                // Tentar inserir
                if ($this->clientes_model->add("clientes", $dados)) {
                    $resultado["sucesso"]++;
                } else {
                    $resultado["erros"]++;
                    $resultado["detalhes_erros"][] = "Linha {$linha}: Erro ao cadastrar cliente";
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
        if (! $this->permission->checkPermission($this->session->userdata("permissao"), "aCliente")) {
            $this->session->set_flashdata("error", "Você não tem permissão para adicionar clientes.");
            redirect(base_url());
        }

        $filename = "modelo_importacao_clientes.csv";
        $file_path = "./assets/arquivos/" . $filename;

        // Criar arquivo modelo se não existir
        if (!file_exists($file_path)) {
            $fp = fopen($file_path, "w");
            
            // Cabeçalho
            fputcsv($fp, [
                "Nome/Razão Social",
                "CPF/CNPJ",
                "Email",
                "Telefone",
                "Celular",
                "Contato",
                "Rua",
                "Número",
                "Complemento",
                "Bairro",
                "Cidade",
                "Estado",
                "CEP",
                "Fornecedor (Sim/Não)",
                "Senha (opcional)"
            ]);

            // Exemplo de dados
            fputcsv($fp, [
                "João Silva",
                "12345678901",
                "joao@exemplo.com",
                "(11) 1234-5678",
                "(11) 98765-4321",
                "Maria Silva",
                "Rua das Flores",
                "123",
                "Apto 45",
                "Centro",
                "São Paulo",
                "SP",
                "01234-567",
                "Não",
                ""
            ]);

            fputcsv($fp, [
                "Empresa XYZ Ltda",
                "12345678000190",
                "contato@empresa.com",
                "(11) 2345-6789",
                "(11) 98765-4321",
                "Carlos Santos",
                "Av. Paulista",
                "1000",
                "Sala 10",
                "Bela Vista",
                "São Paulo",
                "SP",
                "01310-100",
                "Sim",
                ""
            ]);

            fclose($fp);
        }

        // Forçar download
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        readfile($file_path);
        exit;
    }
}
