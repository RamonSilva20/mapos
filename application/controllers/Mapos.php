<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
class Mapos extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('mapos_model');
    }

    /**
     * Painel inicial (#2847), com os componentes da v5: cards de resumo,
     * agenda das entregas de OS (FullCalendar 6), balanço do ano e OS por
     * status (Chart.js 4) e as listas do que pede atenção. Cada bloco só
     * aparece com a permissão de ver o módulo dele (na v4 as listas de OS e
     * de lançamentos apareciam para todos).
     */
    public function index()
    {
        $this->load->helper(['os', 'painel']);

        $hoje = date('Y-m-d');
        $anoAtual = (int) date('Y');
        $pode = [
            'os' => $this->permite('vOs'),
            'vendas' => $this->permite('vVenda'),
            'lancamentos' => $this->permite('vLancamento'),
            'balanco' => $this->permite('rFinanceiro'),
            'produtos' => $this->permite('vProduto'),
        ];

        $painel = ['hoje' => $hoje, 'pode' => $pode];

        if ($pode['os']) {
            $osPorStatus = $this->mapos_model->contarPorStatus('os');
            $painel['os_por_status'] = $osPorStatus;
            $painel['os_grafico'] = painelOsPorStatus($osPorStatus);
            $painel['os_lista'] = $this->mapos_model->osParaPainel(PAINEL_OS_ANDAMENTO, 8);
        }

        if ($pode['vendas']) {
            $painel['vendas_por_status'] = $this->mapos_model->contarPorStatus('vendas');
            $painel['vendas_lista'] = $this->mapos_model->vendasParaPainel(PAINEL_VENDAS_ABERTAS, 8);
        }

        if ($pode['lancamentos'] || $pode['balanco']) {
            $this->load->model('financeiro_model');
        }

        if ($pode['lancamentos']) {
            $mesAtual = painelBalanco($this->financeiro_model->balancoAnual($anoAtual), $anoAtual);
            $indice = (int) date('n') - 1;
            $painel['mes'] = ['receitas' => $mesAtual['receitas'][$indice], 'despesas' => $mesAtual['despesas'][$indice], 'saldo' => $mesAtual['saldo'][$indice]];
            $painel['visao_geral'] = $this->financeiro_model->visaoGeral();
            $painel['lancamentos_lista'] = $this->financeiro_model->proximosPendentes(8);
        }

        if ($pode['balanco']) {
            $ano = painelAno($this->input->get('ano'), $anoAtual);
            $painel['ano'] = $ano;
            $painel['anos'] = range($anoAtual + 1, max(2000, $anoAtual - 5));
            $painel['balanco'] = painelBalanco($this->financeiro_model->balancoAnual($ano), $ano);
        }

        if ($pode['produtos']) {
            $painel['estoque_lista'] = $this->mapos_model->produtosEstoqueBaixo(8);
        }

        $this->data = array_merge($this->data, $painel);

        if ($this->permite('aOs')) {
            $this->data['topbar_acao'] = ['label' => 'Nova OS', 'icon' => 'plus', 'href' => site_url('os/adicionar')];
        } elseif ($this->permite('aVenda')) {
            $this->data['topbar_acao'] = ['label' => 'Nova venda', 'icon' => 'plus', 'href' => site_url('vendas/adicionar')];
        }

        $this->data['menuPainel'] = 'Painel';
        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'mapos/painel';

        return $this->layout();
    }

    public function minhaConta()
    {
        $this->data['usuario'] = $this->mapos_model->getById($this->session->userdata('id_admin'));
        $this->data['view'] = 'mapos/minhaConta';

        return $this->layout();
    }

    public function alterarSenha()
    {
        $current_user = $this->mapos_model->getById($this->session->userdata('id_admin'));

        if (!$current_user) {
            $this->session->set_flashdata('error', 'Ocorreu um erro ao pesquisar usuário!');
            redirect(site_url('mapos/minhaConta'));
        }

        $oldSenha = $this->input->post('oldSenha');
        $senha = $this->input->post('novaSenha');

        if (!password_verify($oldSenha, $current_user->senha)) {
            $this->session->set_flashdata('error', 'A senha atual não corresponde com a senha informada.');
            redirect(site_url('mapos/minhaConta'));
        }

        $result = $this->mapos_model->alterarSenha($senha);

        if ($result) {
            $this->session->set_flashdata('success', 'Senha alterada com sucesso!');
            redirect(site_url('mapos/minhaConta'));
        }

        $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar alterar a senha!');
        redirect(site_url('mapos/minhaConta'));
    }

    public function pesquisar()
    {
        $termo = $this->input->get('termo');

        $data['results'] = $this->mapos_model->pesquisar($termo);

        // Cada grupo de resultados só é exibido para quem pode visualizá-lo
        $this->data['produtos'] = $this->hasAnyPermission(['vProduto']) ? $data['results']['produtos'] : [];
        $this->data['servicos'] = $this->hasAnyPermission(['vServico']) ? $data['results']['servicos'] : [];
        $this->data['os'] = $this->hasAnyPermission(['vOs']) ? $data['results']['os'] : [];
        $this->data['clientes'] = $this->hasAnyPermission(['vCliente']) ? $data['results']['clientes'] : [];
        $this->data['view'] = 'mapos/pesquisa';

        return $this->layout();
    }

    public function backup()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cBackup')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para efetuar backup.');
            redirect(base_url());
        }

        $this->load->dbutil();
        $prefs = [
            'format' => 'zip',
            'foreign_key_checks' => false,
            'filename' => 'backup' . date('d-m-Y') . '.sql',
        ];

        $backup = $this->dbutil->backup($prefs);

        // O backup só é entregue como download. Antes ele também era "gravado"
        // em base_url() . 'backup/backup.zip': uma URL, então a gravação falhava
        // sempre; e, se funcionasse, deixaria o dump do banco numa pasta
        // pública (#2856).
        log_info('Efetuou backup do banco de dados.');

        $this->load->helper('download');
        force_download(backupNomeArquivo(time()), $backup);
    }

    /**
     * Emitente (#2846): dados da empresa nas impressões, nos e-mails e no PIX,
     * num formulário só (cadastra quando ainda não há, senão edita o que
     * existe; o id nunca vem do POST). O logo é opcional e troca só o arquivo
     * dele (na v4 trocar o logo apagava a pasta de uploads inteira).
     */
    public function emitente()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cEmitente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar emitente.');
            redirect(base_url());
        }

        $this->load->helper('configuracoes');
        $emitente = $this->mapos_model->getEmitente();
        $erros = [];

        if ($this->input->method() === 'post') {
            [$dados, $erros] = emitenteDadosDoFormulario($this->input->post());

            $enviouLogo = isset($_FILES['userfile']) && ($_FILES['userfile']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
            if ($erros === [] && $enviouLogo) {
                [$arquivo, $erroLogo] = $this->enviarImagem(FCPATH . 'assets/uploads');
                if ($arquivo === null) {
                    $erros['userfile'] = $erroLogo;
                } else {
                    $anterior = $emitente ? emitenteArquivoDoLogo($emitente->url_logo, FCPATH . 'assets/uploads') : null;
                    $dados['url_logo'] = base_url('assets/uploads/' . $arquivo);
                }
            } elseif ($erros === [] && ! $emitente) {
                $dados['url_logo'] = '';
            }

            if ($erros === []) {
                $salvou = $emitente
                    ? $this->mapos_model->edit('emitente', $dados, 'id', (int) $emitente->id)
                    : $this->mapos_model->add('emitente', $dados);

                if ($salvou) {
                    if (! empty($anterior)) {
                        @unlink($anterior);
                    }
                    log_info($emitente ? 'Alterou informações de emitente.' : 'Adicionou informações de emitente.');
                    $this->session->set_flashdata('success', 'Dados do emitente salvos.');

                    return redirect('mapos/emitente');
                }

                $erros['_geral'] = 'Não foi possível salvar. Tente de novo.';
            }
        }

        $post = $this->input->method() === 'post' ? $this->input->post() : null;
        $campos = ['nome', 'cnpj', 'ie', 'cep', 'rua', 'numero', 'bairro', 'cidade', 'estado', 'telefone', 'email'];
        $valores = [];
        foreach ($campos as $campo) {
            $valores[$campo] = $post !== null
                ? (is_scalar($post[$campo] ?? null) ? trim((string) $post[$campo]) : '')
                : (string) ($emitente->{$campo === 'estado' ? 'uf' : $campo} ?? '');
        }

        $this->data['menuConfiguracoes'] = 'Configuracoes';
        $this->data['emitente'] = $emitente;
        $this->data['valores'] = $valores;
        $this->data['erros'] = $erros;
        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'mapos/emitente';

        return $this->layout();
    }

    /**
     * Grava a imagem enviada em userfile (png, jpg, bmp até 2 MB, nome
     * aleatório) e devolve [nome do arquivo, null] ou [null, mensagem]. Na
     * v4 o erro de upload aparecia cru na tela e a página parava.
     *
     * @return array{0: string|null, 1: string|null}
     */
    private function enviarImagem(string $pasta): array
    {
        if (!is_dir($pasta) && !@mkdir($pasta, DIR_WRITE_MODE, true)) {
            return [null, 'Não foi possível criar a pasta de uploads no servidor.'];
        }

        // Acima do limite do PHP o arquivo nem chega: a mensagem do CI fala em
        // "configuração do PHP", que não ajuda quem está enviando.
        if (in_array($_FILES['userfile']['error'] ?? UPLOAD_ERR_OK, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            return [null, 'A imagem passa de 2 MB. Reduza o tamanho e envie de novo.'];
        }

        $this->load->library('upload');

        $this->upload->initialize([
            // SVG fora da lista: é um documento XML que pode carregar script e
            // seria servido a partir da própria origem da aplicação.
            'upload_path' => $pasta,
            'allowed_types' => 'png|jpg|jpeg|bmp',
            'max_size' => 2048,
            'remove_space' => true,
            'encrypt_name' => true,
        ]);

        // Mensagens do upload em português: o idioma global é english, e a
        // biblioteca carrega o arquivo de idioma ao registrar cada erro (como
        // em validarFormulario()).
        $idioma = $this->config->item('language');
        $this->config->set_item('language', 'pt-br');
        try {
            $enviou = $this->upload->do_upload('userfile');
            $erro = $enviou ? null : (trim(strip_tags($this->upload->display_errors('', ''))) ?: 'Não foi possível enviar a imagem.');
        } finally {
            $this->config->set_item('language', $idioma);
        }

        return $enviou ? [$this->upload->data('file_name'), null] : [null, $erro];
    }

    public function uploadUserImage()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cUsuario')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para mudar a foto.');
            redirect(base_url());
        }

        $id = $this->session->userdata('id_admin');
        if ($id == null || !is_numeric($id)) {
            $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar alterar sua foto.');
            redirect(site_url('mapos/minhaConta'));
        }

        $usuario = $this->mapos_model->getById($id);

        [$image, $erroUpload] = $this->enviarImagem(FCPATH . 'assets/userImage');
        if ($image === null) {
            $this->session->set_flashdata('error', $erroUpload);
            redirect(site_url('mapos/minhaConta'));
        }

        // A foto antiga sai só depois que a nova foi gravada.
        $antiga = FCPATH . 'assets/userImage/' . basename((string) $usuario->url_image_user);
        if ((string) $usuario->url_image_user !== '' && is_file($antiga)) {
            unlink($antiga);
        }
        $imageUserPath = $image;
        $retorno = $this->mapos_model->editImageUser($id, $imageUserPath);

        if ($retorno) {
            $this->session->set_userdata('url_image_user', $imageUserPath);
            $this->session->set_flashdata('success', 'Foto alterada com sucesso.');
            log_info('Alterou a Imagem do Usuario.');
        } else {
            $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar alterar sua foto.');
        }
        redirect(site_url('mapos/minhaConta'));
    }

    /**
     * Fila de e-mails (#2846), no padrão das listagens (#2852): filtro de
     * situação na URL e exclusão em modal-confirm.
     */
    public function emails()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cEmail')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar fila de e-mails');
            redirect(base_url());
        }

        $this->load->helper('configuracoes');
        $this->load->model('email_model');

        $filtros = listagemFiltros(['status' => ['pending', 'sending', 'sent', 'failed']], $this->input->get());
        $offset = (int) $this->uri->segment(3);
        $total = $this->email_model->contar($filtros);

        $this->data['menuConfiguracoes'] = 'Email';
        $this->data['filtros'] = $filtros;
        $this->data['total'] = $total;
        $this->data['results'] = $this->email_model->listar($filtros, (int) $this->data['configuration']['per_page'], $offset);
        $this->data['paginacao'] = $this->paginacao(site_url('mapos/emails'), $total, $offset, null, $filtros);
        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'emails/emails';

        return $this->layout();
    }

    public function excluirEmail()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cEmail')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir e-mail da fila.');
            redirect(base_url());
        }

        $id = (int) $this->input->post('id');
        $voltar = site_url('mapos/emails') . listagemQuery(listagemFiltros(['status' => ['pending', 'sending', 'sent', 'failed']], $this->input->get()));

        $this->load->model('email_model');
        if ($id <= 0 || !$this->email_model->getById($id)) {
            $this->session->set_flashdata('error', 'E-mail não encontrado na fila.');
            redirect($voltar);
        }

        $this->email_model->delete('email_queue', 'id', $id);
        log_info('Removeu um e-mail da fila de envio. ID: ' . $id);

        $this->session->set_flashdata('success', 'E-mail removido da fila.');
        redirect($voltar);
    }

    /**
     * Configurações do sistema (#2846), em abas por link (?aba=). Cada aba é
     * um formulário que grava só os campos dela, na tabela configuracoes ou
     * no .env (configuracaoCampos()). Segredos do .env não voltam para a tela
     * e, em branco, ficam como estão.
     */
    public function configurar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cSistema')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar o sistema');
            redirect(base_url());
        }

        $this->load->helper(['configuracoes', 'os']);
        $aba = listagemFiltros(['aba' => array_keys(CONFIG_ABAS)], $this->input->get())['aba'] ?? 'geral';
        $erros = [];

        if ($this->input->method() === 'post' && $aba !== 'sistema') {
            [$config, $env, $erros] = configuracaoDadosDoFormulario($aba, $this->input->post());

            if ($erros === [] && $env !== [] && !$this->gravarEnv($env)) {
                $erros['_geral'] = 'Não foi possível gravar o arquivo application/.env. Confira a permissão de escrita.';
            }

            if ($erros === [] && ($config === [] || $this->mapos_model->saveConfiguracao($config))) {
                log_info('Alterou as configurações do sistema (' . CONFIG_ABAS[$aba] . ').');
                $this->session->set_flashdata('success', 'Configurações salvas.');

                return redirect('mapos/configurar' . ($aba === 'geral' ? '' : listagemQuery(['aba' => $aba])));
            }

            $erros['_geral'] ??= 'Não foi possível salvar. Tente de novo.';
        }

        $this->data['menuConfiguracoes'] = 'Sistema';
        $this->data['aba'] = $aba;
        $this->data['campos'] = configuracaoCampos($aba);
        $this->data['valores'] = configuracaoValores($aba, $this->input->method() === 'post' ? $this->input->post() : null, $this->data['configuration'], $_ENV);
        $this->data['erros'] = $erros;
        $this->data['pode_backup'] = $this->permite('cBackup');
        $this->data['legacy_assets'] = false;
        $this->data['view'] = 'mapos/configurar';

        return $this->layout();
    }

    /** Grava as chaves no application/.env (configuracaoEnvAtualizado()). */
    private function gravarEnv(array $valores): bool
    {
        $arquivo = dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . '.env';
        $conteudo = is_readable($arquivo) ? (string) file_get_contents($arquivo) : '';

        return is_writable($arquivo) && file_put_contents($arquivo, configuracaoEnvAtualizado($conteudo, $valores)) !== false;
    }

    public function atualizarBanco()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cSistema')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar o sistema');
            redirect(base_url());
        }

        // Só por POST (com o token CSRF): na v4 bastava abrir o link.
        if ($this->input->method() !== 'post') {
            return redirect(site_url('mapos/configurar?aba=sistema'));
        }

        $this->load->library('migration');

        if ($this->migration->latest() === false) {
            $this->session->set_flashdata('error', $this->migration->error_string());
        } else {
            $this->session->set_flashdata('success', 'Banco de dados atualizado com sucesso!');
        }

        return redirect(site_url('mapos/configurar?aba=sistema'));
    }

    public function atualizarMapos()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cSistema')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar o sistema');
            redirect(base_url());
        }

        if ($this->input->method() !== 'post') {
            return redirect(site_url('mapos/configurar?aba=sistema'));
        }

        $this->load->library('github_updater');

        if (!$this->github_updater->has_update()) {
            $this->session->set_flashdata('success', 'Seu mapos já está atualizado!');

            return redirect(site_url('mapos/configurar?aba=sistema'));
        }

        $success = $this->github_updater->update();

        if ($success) {
            $this->session->set_flashdata('success', 'Mapos atualizado com sucesso!');
        } else {
            $this->session->set_flashdata('error', 'Erro ao atualizar mapos!');
        }

        return redirect(site_url('mapos/configurar?aba=sistema'));
    }

    public function calendario()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar O.S.');
            redirect(base_url());
        }
        $this->load->model('os_model');
        // Status fora da lista é ignorado (mostra todos).
        $status = listagemFiltros(['status' => array_keys(OS_STATUS_VARIANTES)], $this->input->get())['status'] ?? null;
        // O FullCalendar manda start/end em ISO 8601; sem eles (ou com lixo) a
        // consulta virava `dataFinal >= NULL` e quebrava com erro 500 (#2901).
        $start = dataIsoParaYmd($this->input->get('start'));
        $end = dataIsoParaYmd($this->input->get('end'));

        if ($start === null || $end === null || $start > $end) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['error' => 'Informe start e end válidos (AAAA-MM-DD).']));
        }

        $allOs = $this->mapos_model->calendario(
            $start,
            $end,
            $status
        );
        // Só texto e URLs (painelEventoDaOs()); o painel monta o modal.
        $this->load->helper(['os', 'painel']);
        $podeEditar = $this->permite('eOs');
        $events = array_map(fn ($os) => painelEventoDaOs(
            $os,
            site_url('os/visualizar/' . (int) $os->idOs),
            $podeEditar && $this->os_model->isEditable($os->idOs) ? site_url('os/editar/' . (int) $os->idOs) : null
        ), $allOs);

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($events));
    }
}
