<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Arquivos extends MY_Controller
{
    /** Filtros da listagem, na query string: busca no texto e período (AAAA-MM-DD). */
    public const FILTROS = ['pesquisa' => 'texto', 'de' => 'texto', 'ate' => 'texto'];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('arquivos_model');
        $this->data['menuArquivos'] = 'Arquivos';
    }

    public function index()
    {
        $this->gerenciar();
    }

    /**
     * Listagem de arquivos (#2845), no padrão das listagens (#2852). As datas
     * do período que não forem AAAA-MM-DD válidas são descartadas.
     */
    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vArquivo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar arquivos.');
            redirect(base_url());
        }

        $filtros = listagemFiltros(self::FILTROS, $this->input->get());
        foreach (['de', 'ate'] as $periodo) {
            $data = isset($filtros[$periodo]) ? dataIsoParaYmd($filtros[$periodo]) : null;
            if ($data === null) {
                unset($filtros[$periodo]);
            } else {
                $filtros[$periodo] = $data;
            }
        }

        $offset = (int) $this->uri->segment(3);
        $total = $this->arquivos_model->contar($filtros);

        $this->data['filtros'] = $filtros;
        $this->data['total'] = $total;
        $this->data['results'] = $this->arquivos_model->listar($filtros, (int) $this->data['configuration']['per_page'], $offset);
        $this->data['paginacao'] = $this->paginacao(site_url('arquivos/gerenciar'), $total, $offset, null, $filtros);
        $this->data['pode'] = [
            'adicionar' => $this->permite('aArquivo'),
            'editar' => $this->permite('eArquivo'),
            'excluir' => $this->permite('dArquivo'),
        ];

        if ($this->data['pode']['adicionar']) {
            $this->data['topbar_acao'] = ['label' => 'Novo arquivo', 'icon' => 'plus', 'href' => site_url('arquivos/adicionar')];
        }

        $this->data['view'] = 'arquivos/arquivos';

        return $this->layout();
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aArquivo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar arquivos.');
            redirect(base_url());
        }

        return $this->formulario(null);
    }

    public function editar()
    {
        $arquivo = is_numeric($this->uri->segment(3)) ? $this->arquivos_model->getById((int) $this->uri->segment(3)) : null;
        if (! $arquivo) {
            $this->session->set_flashdata('error', 'Arquivo não encontrado ou parâmetro inválido.');
            redirect('arquivos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eArquivo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar arquivos.');
            redirect(base_url());
        }

        return $this->formulario($arquivo);
    }

    /**
     * Formulário de arquivo, para enviar e editar (#2845), no padrão dos
     * formulários (#2851). Ao enviar, o arquivo só é gravado depois de a
     * validação passar; na edição só mudam nome, descrição e data. O id
     * editado é o da URL.
     */
    private function formulario(?object $arquivo)
    {
        $erros = [];

        if ($this->input->method() === 'post') {
            $erros = $this->validarFormulario('arquivos');

            $data = trim((string) $this->input->post('data'));
            $data = $data === '' ? date('Y-m-d') : dataIsoParaYmd($data);
            if ($data === null) {
                $erros['data'] = 'Informe uma data válida.';
            }

            if ($erros === []) {
                $salvou = false;
                $dados = [
                    'documento' => trim((string) $this->input->post('nome')),
                    'descricao' => trim((string) $this->input->post('descricao')),
                    'cadastro' => $data,
                ];

                if ($arquivo === null) {
                    $envio = $this->do_upload();

                    if (is_string($envio)) {
                        $erros['userfile'] = $envio;
                    } else {
                        $dados += [
                            'file' => $envio['file_name'],
                            'path' => 'arquivos/' . date('d-m-Y') . '/' . $envio['file_name'],
                            'url' => '',
                            'tamanho' => $envio['file_size'],
                            'tipo' => $envio['file_ext'],
                        ];
                        $salvou = $this->arquivos_model->add('documentos', $dados) == true;
                    }
                } else {
                    $salvou = $this->arquivos_model->edit('documentos', $dados, 'idDocumentos', (int) $arquivo->idDocumentos) == true;
                }

                if ($erros === []) {
                    if ($salvou) {
                        log_info($arquivo === null ? 'Adicionou um arquivo' : 'Alterou um arquivo, ID: ' . (int) $arquivo->idDocumentos);
                        $this->session->set_flashdata('success', $arquivo === null ? 'Arquivo enviado com sucesso!' : 'Alterações salvas.');

                        return redirect('arquivos');
                    }

                    $erros['_geral'] = 'Não foi possível salvar. Tente de novo.';
                }
            }
        }

        $post = $this->input->method() === 'post' ? $this->input->post() : null;
        $this->data['arquivo'] = $arquivo;
        $this->data['valores'] = [
            'nome' => is_array($post) ? (string) ($post['nome'] ?? '') : (string) ($arquivo->documento ?? ''),
            'descricao' => is_array($post) ? (string) ($post['descricao'] ?? '') : (string) ($arquivo->descricao ?? ''),
            'data' => is_array($post) ? (string) ($post['data'] ?? '') : (string) ($arquivo->cadastro ?? date('Y-m-d')),
        ];
        $this->data['erros'] = $erros;
        $this->data['view'] = 'arquivos/formulario';

        return $this->layout();
    }

    public function download($id = null)
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vArquivo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar arquivos.');
            redirect(base_url());
        }

        $file = $id !== null && is_numeric($id) ? $this->arquivos_model->getById($id) : null;
        $arquivo = $file ? armazenamentoLocalizar((string) $file->path, [FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'arquivos']) : null;
        if ($arquivo === null) {
            $this->session->set_flashdata('error', 'Erro! O arquivo não pode ser localizado.');
            redirect(base_url() . 'index.php/arquivos/');
        }

        // Imagens e PDF abrem na página; o resto baixa (armazenamentoEnviar()).
        $nome = trim((string) $file->documento) !== '' ? (string) $file->documento : 'arquivo';
        $extensao = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
        armazenamentoEnviar($arquivo, str_ends_with(strtolower($nome), '.' . $extensao) ? $nome : $nome . '.' . $extensao, $this->input->get('baixar') !== null);
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dArquivo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir arquivos.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        $file = $id !== null && is_numeric($id) ? $this->arquivos_model->getById($id) : null;
        if (! $file) {
            $this->session->set_flashdata('error', 'Erro! O arquivo não pode ser localizado.');
            redirect(site_url('arquivos'));
        }

        $this->db->where('idDocumentos', $id);
        if ($this->db->delete('documentos')) {
            $arquivo = armazenamentoLocalizar((string) $file->path, [FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'arquivos']);
            if ($arquivo !== null) {
                unlink($arquivo);
            }
            $this->session->set_flashdata('success', 'Arquivo excluído com sucesso!');
            log_info('Removeu um arquivo. ID: ' . $id);
        } else {
            $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar excluir o arquivo.');
        }

        redirect(site_url('arquivos') . listagemQuery(listagemFiltros(self::FILTROS, $this->input->get())));
    }

    /**
     * Auxiliar interno (como método público seria acessível via
     * /arquivos/do_upload). Devolve os dados do arquivo gravado, ou o texto do
     * erro, que a tela mostra no campo do arquivo.
     *
     * @return array|string
     */
    protected function do_upload()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aArquivo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar arquivos.');
            redirect(base_url());
        }

        $pasta = armazenamentoPasta('arquivos/' . date('d-m-Y'));
        if ($pasta === null) {
            return 'Não foi possível criar a pasta de arquivos no servidor.';
        }

        $config = armazenamentoConfigUpload($pasta, ['txt', 'jpg', 'jpeg', 'gif', 'png', 'pdf']);
        $config['max_width'] = '3000';
        $config['max_height'] = '2000';

        $this->load->library('upload', $config);

        if (empty($_FILES['userfile']['name'])) {
            return 'Escolha o arquivo.';
        }

        if (! armazenamentoReceber($this->upload)) {
            return 'Não foi possível enviar o arquivo. Use TXT, JPG, GIF, PNG ou PDF, de até ' . tamanhoDeArquivo(armazenamentoLimiteKb()) . ' (imagens de até 3000 × 2000 px).';
        }

        $dados = $this->upload->data();
        if (($erro = armazenamentoValidarEnvio($dados)) !== null) {
            return $erro;
        }

        return $dados;
    }
}

/* End of file arquivos.php */
/* Location: ./application/controllers/arquivos.php */
