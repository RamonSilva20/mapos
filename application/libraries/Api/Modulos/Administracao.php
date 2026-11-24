<?php

namespace Libraries\Api\Modulos;

use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Modulo;
use Libraries\Api\Recursos\Endereco;
use Libraries\Api\Recursos\Valor;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Schema\Comuns;
use Libraries\Api\Schema\Schema;
use Libraries\Api\Upload;
use Throwable;

final class Administracao extends Modulo
{
    public static function rotas(): array
    {
        return [
            Rota::get('/emitente', [self::class, 'emitente'])->tag('Emitente')
                ->resumo('Dados da empresa', 'Qualquer usuário logado lê: o app usa nas telas e impressões. 404 enquanto não houver cadastro.')
                ->dados(self::schemaEmitente())
                ->erros(404),

            Rota::put('/emitente', [self::class, 'salvarEmitente'])->permissao('cEmitente')->tag('Emitente')
                ->resumo('Cadastra ou altera os dados da empresa')
                ->corpo(Schema::objeto([
                    'nome' => Schema::texto()->obrigatorio()->max(255),
                    'cnpj' => Schema::texto()->obrigatorio()->max(45)->descricao('CNPJ ou CPF.'),
                    'ie' => Schema::texto()->max(50),
                    'endereco' => Endereco::entrada(true),
                    'telefone' => Schema::texto()->obrigatorio()->max(20),
                    'email' => Schema::email()->obrigatorio()->max(255),
                ]))
                ->dados(self::schemaEmitente()),

            Rota::put('/emitente/logo', [self::class, 'logo'])->permissao('cEmitente')->tag('Emitente')
                ->resumo('Troca o logo', 'PNG, JPG ou BMP de até 2 MB, em multipart/form-data. O emitente precisa estar cadastrado.')
                ->corpo(Schema::objeto(['arquivo' => Schema::arquivo()->obrigatorio()]), true)
                ->dados(self::schemaEmitente())
                ->erros(409),

            Rota::get('/configuracoes', [self::class, 'configuracoes'])->permissao('cSistema')->tag('Configurações')
                ->resumo('Todas as configurações, por aba', 'Cada campo traz rótulo, tipo, opções e o valor atual. Campos `segredo` nunca trazem o valor: só se está definido.')
                ->dados(Schema::lista(self::schemaAba())),

            Rota::patch('/configuracoes/{aba:s}', [self::class, 'salvarConfiguracoes'])->permissao('cSistema')->tag('Configurações')
                ->resumo('Altera configurações de uma aba', "Envie só as chaves que mudam, com o tipo do campo: `sim_nao` e `bool_env` como booleano, `status` como lista, os demais como texto. `segredo` vazio mantém o atual. `gerar_jwt: true` troca a chave dos tokens e derruba todas as sessões da API, inclusive a desta chamada.\n\nAbas: " . implode(', ', array_map(static fn ($a) => '`' . $a . '`', array_diff(array_keys(CONFIG_ABAS), ['sistema']))) . '.')
                ->corpo(Schema::mapa(Schema::qualquer()))
                ->dados(self::schemaAba())
                ->erros(404),

            Rota::get('/auditoria', [self::class, 'auditoria'])->permissao('cAuditoria')->tag('Auditoria')
                ->resumo('Registro das ações dos usuários', 'Do mais recente para o mais antigo.')
                ->consulta(Comuns::paginacaoConsulta([
                    'busca' => Schema::texto()->max(100)->descricao('Usuário, ação ou IP.'),
                    'de' => Schema::data(),
                    'ate' => Schema::data(),
                ]))
                ->lista(Schema::objeto([
                    'id' => Schema::inteiro()->obrigatorio(),
                    'usuario' => Schema::texto()->anulavel()->obrigatorio(),
                    'acao' => Schema::texto()->obrigatorio(),
                    'ip' => Schema::texto()->anulavel()->obrigatorio(),
                    'data_hora' => Schema::dataHora()->anulavel()->obrigatorio(),
                ])->nome('RegistroDeAuditoria')),

            Rota::delete('/auditoria', [self::class, 'limparAuditoria'])->permissao('cAuditoria')->tag('Auditoria')
                ->resumo('Apaga os registros com mais de 30 dias')
                ->dados(Schema::objeto(['apagados' => Schema::inteiro()->obrigatorio()])),

            Rota::get('/emails', [self::class, 'emails'])->permissao('cEmail')->tag('Fila de e-mails')
                ->resumo('Fila de envio de e-mails')
                ->consulta(Comuns::paginacaoConsulta(['status' => Schema::enum(['pending', 'sending', 'sent', 'failed'])]))
                ->lista(Schema::objeto([
                    'id' => Schema::inteiro()->obrigatorio(),
                    'para' => Schema::texto()->obrigatorio(),
                    'assunto' => Schema::texto()->anulavel()->obrigatorio(),
                    'status' => Schema::enum(['pending', 'sending', 'sent', 'failed'])->obrigatorio(),
                    'data_hora' => Schema::dataHora()->anulavel()->obrigatorio(),
                ])->nome('EmailNaFila')),

            Rota::delete('/emails/{id}', [self::class, 'excluirEmail'])->permissao('cEmail')->tag('Fila de e-mails')
                ->resumo('Tira um e-mail da fila')
                ->semConteudo()
                ->erros(404),

            Rota::get('/sistema', [self::class, 'sistema'])->permissao('cSistema')->liberadaComBancoDesatualizado()->tag('Sistema')
                ->resumo('Versão e situação da instalação')
                ->dados(Schema::objeto([
                    'versao' => Schema::texto()->obrigatorio()->exemplo('5.4.0'),
                    'migracoes_pendentes' => Schema::inteiro()->obrigatorio(),
                    'problemas' => Schema::lista(Schema::texto())->obrigatorio()->descricao('O que impede a atualização automática (permissão de pastas, extensões...).'),
                ])),

            Rota::post('/sistema/banco', [self::class, 'atualizarBanco'])->permissao('cSistema')->liberadaComBancoDesatualizado()->tag('Sistema')
                ->resumo('Atualiza o banco de dados', 'Roda as migrations pendentes. É a única rota, além de `GET /sistema`, que responde com o banco desatualizado.')
                ->dados(Schema::objeto(['migracoes_pendentes' => Schema::inteiro()->obrigatorio()]))
                ->erros(500),

            Rota::get('/sistema/atualizacao', [self::class, 'atualizacao'])->permissao('cSistema')->tag('Sistema')
                ->resumo('Consulta a última versão no GitHub')
                ->dados(Schema::objeto([
                    'versao_atual' => Schema::texto()->obrigatorio(),
                    'ultima_versao' => Schema::texto()->anulavel()->obrigatorio(),
                    'publicada_em' => Schema::dataHora()->anulavel()->obrigatorio(),
                    'notas' => Schema::texto()->anulavel()->obrigatorio(),
                    'tem_atualizacao' => Schema::booleano()->obrigatorio(),
                    'problemas' => Schema::lista(Schema::texto())->obrigatorio(),
                ]))
                ->erros(502),

            Rota::post('/sistema/atualizacao', [self::class, 'atualizarMapos'])->permissao('cSistema')->tag('Sistema')
                ->resumo('Atualiza o Map-OS para uma versão', 'Baixa a release do GitHub, confere e troca os arquivos. Depois, chame `POST /sistema/banco`.')
                ->corpo(Schema::objeto(['versao' => Schema::texto()->obrigatorio()->max(40)->exemplo('v5.5.0')]))
                ->dados(Schema::objeto(['versao' => Schema::texto()->obrigatorio()]))
                ->erros(500),

            Rota::get('/sistema/backup', [self::class, 'backup'])->permissao('cBackup')->tag('Sistema')
                ->resumo('Baixa o backup do banco', 'Arquivo ZIP com o SQL. Sessões, tentativas de login e tokens ficam de fora.')
                ->arquivo('application/zip'),
        ];
    }

    private static function schemaEmitente(): Schema
    {
        static $schema = null;

        return $schema ??= Schema::objeto([
            'nome' => Schema::texto()->obrigatorio(),
            'cnpj' => Schema::texto()->anulavel()->obrigatorio(),
            'ie' => Schema::texto()->anulavel()->obrigatorio(),
            'endereco' => Endereco::schema()->obrigatorio(),
            'telefone' => Schema::texto()->anulavel()->obrigatorio(),
            'email' => Schema::texto()->anulavel()->obrigatorio(),
            'logo_url' => Schema::url()->anulavel()->obrigatorio(),
        ])->nome('Emitente');
    }

    private static function schemaAba(): Schema
    {
        static $schema = null;

        return $schema ??= Schema::objeto([
            'id' => Schema::texto()->obrigatorio()->exemplo('os'),
            'nome' => Schema::texto()->obrigatorio(),
            'campos' => Schema::lista(Schema::objeto([
                'chave' => Schema::texto()->obrigatorio()->exemplo('control_editos'),
                'rotulo' => Schema::texto()->obrigatorio(),
                'tipo' => Schema::enum(['texto', 'area', 'numero', 'opcao', 'status', 'sim_nao', 'bool_env', 'segredo', 'gerar_jwt'])->obrigatorio(),
                'grupo' => Schema::texto()->anulavel()->obrigatorio(),
                'ajuda' => Schema::texto()->anulavel()->obrigatorio(),
                'opcoes' => Schema::lista(Schema::objeto(['valor' => Schema::texto()->obrigatorio(), 'nome' => Schema::texto()->obrigatorio()]))->anulavel()->obrigatorio(),
                'valor' => Schema::qualquer()->anulavel()->obrigatorio()->descricao('Texto, booleano ou lista, conforme o tipo. `segredo`: `{"definido": true}`.'),
            ]))->obrigatorio(),
        ])->nome('AbaDeConfiguracao');
    }

    public function emitente(): Resposta
    {
        $emitente = $this->ctx->modelo('Mapos_model')->getEmitente();
        if (! $emitente) {
            throw HttpErro::naoEncontrado('O emitente ainda não foi cadastrado.');
        }

        return Resposta::dados(self::emitenteDe($emitente));
    }

    public static function emitenteDe(object $e): array
    {
        return [
            'nome' => (string) $e->nome,
            'cnpj' => Valor::textoOuNulo($e->cnpj),
            'ie' => Valor::textoOuNulo($e->ie),
            'endereco' => Endereco::de($e->cep, $e->rua, $e->numero, $e->bairro, $e->cidade, $e->uf),
            'telefone' => Valor::textoOuNulo($e->telefone),
            'email' => Valor::textoOuNulo($e->email),
            'logo_url' => cobrancaUrlSegura($e->url_logo),
        ];
    }

    public function salvarEmitente(): Resposta
    {
        $entrada = $this->ctx->corpo();
        [$dados, $erros] = emitenteDadosDoFormulario([
            'nome' => $entrada['nome'],
            'cnpj' => $entrada['cnpj'],
            'ie' => $entrada['ie'] ?? '',
            'telefone' => $entrada['telefone'],
            'email' => $entrada['email'],
        ] + Endereco::paraBanco($entrada['endereco']));
        if ($erros !== []) {
            throw HttpErro::validacao($this->traduzirErros($erros, ['cep' => 'endereco.cep', 'rua' => 'endereco.rua', 'numero' => 'endereco.numero', 'bairro' => 'endereco.bairro', 'cidade' => 'endereco.cidade', 'estado' => 'endereco.estado']));
        }

        $mapos = $this->ctx->modelo('Mapos_model');
        $emitente = $mapos->getEmitente();
        if ($emitente) {
            $this->atualizarLinha('emitente', 'id', (int) $emitente->id, $dados);
        } else {
            $this->inserirLinha('emitente', $dados + ['url_logo' => '']);
        }
        $this->ctx->auditar($emitente ? 'Alterou informações de emitente.' : 'Adicionou informações de emitente.');

        return Resposta::dados(self::emitenteDe($mapos->getEmitente()));
    }

    public function logo(): Resposta
    {
        $mapos = $this->ctx->modelo('Mapos_model');
        $emitente = $mapos->getEmitente();
        if (! $emitente) {
            throw HttpErro::conflito('Cadastre os dados do emitente antes do logo.', 'emitente_inexistente');
        }

        $pasta = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'uploads';
        armazenamentoProtegerPastaPublica($pasta);
        $enviado = (new Upload($this->ctx))->receber($this->ctx->corpo()['arquivo'], $pasta, ['png', 'jpg', 'jpeg', 'bmp'], 2048);
        $anterior = emitenteArquivoDoLogo($emitente->url_logo, $pasta);

        $this->atualizarLinha('emitente', 'id', (int) $emitente->id, ['url_logo' => base_url('assets/uploads/' . $enviado['nome'])]);
        if ($anterior !== null) {
            @unlink($anterior);
        }
        $this->ctx->auditar('Alterou informações de emitente.');

        return Resposta::dados(self::emitenteDe($mapos->getEmitente()));
    }

    public function configuracoes(): Resposta
    {
        $abas = [];
        foreach (CONFIG_ABAS as $aba => $nome) {
            if (configuracaoCampos($aba) !== []) {
                $abas[] = $this->aba($aba);
            }
        }

        return Resposta::dados($abas);
    }

    public function salvarConfiguracoes(): Resposta
    {
        $aba = (string) $this->ctx->parametro('aba');
        $campos = configuracaoCampos($aba);
        if ($campos === []) {
            throw HttpErro::naoEncontrado('Aba de configuração inexistente.');
        }

        $enviado = $this->ctx->corpo();
        $desconhecidos = array_diff(array_keys($enviado), array_keys($campos));
        if ($desconhecidos !== []) {
            throw HttpErro::validacao(array_fill_keys($desconhecidos, 'Campo desconhecido nesta aba.'));
        }

        $atuais = configuracaoValores($aba, null, $this->ctx->configuracoes(), $_ENV);
        $post = [];
        foreach ($campos as $chave => $campo) {
            $valor = array_key_exists($chave, $enviado) ? $enviado[$chave] : $atuais[$chave];
            $post[$chave] = match ($campo['tipo']) {
                'sim_nao' => $valor === true || $valor === '1' || $valor === 1 ? '1' : '0',
                'bool_env' => $valor === true || $valor === 'true' || $valor === '1' || $valor === 1 ? 'true' : 'false',
                'status' => is_array($valor) ? array_values(array_filter($valor, 'is_string')) : [],
                'segredo' => array_key_exists($chave, $enviado) && is_scalar($valor) ? (string) $valor : '',
                'gerar_jwt' => $valor === true ? '1' : '',
                default => is_scalar($valor) ? (string) $valor : '',
            };
        }

        [$config, $env, $erros] = configuracaoDadosDoFormulario($aba, $post);
        if ($erros !== []) {
            throw HttpErro::validacao($erros);
        }

        if ($env !== []) {
            $arquivo = APPPATH . '.env';
            $conteudo = is_readable($arquivo) ? (string) file_get_contents($arquivo) : '';
            if (! is_writable($arquivo) || file_put_contents($arquivo, configuracaoEnvAtualizado($conteudo, $env)) === false) {
                throw HttpErro::interno('Não foi possível gravar o arquivo application/.env. Confira a permissão de escrita.');
            }
            foreach ($env as $chave => $valor) {
                $_ENV[$chave] = $valor;
            }
        }
        if ($config !== [] && ! $this->ctx->modelo('Mapos_model')->saveConfiguracao($config)) {
            throw HttpErro::interno('Não foi possível salvar as configurações.');
        }
        $this->ctx->auditar('Alterou as configurações do sistema (' . CONFIG_ABAS[$aba] . ').');

        return Resposta::dados($this->aba($aba, $config));
    }

    private function aba(string $aba, array $alterados = []): array
    {
        $valores = configuracaoValores($aba, null, array_merge($this->ctx->configuracoes(), $alterados), $_ENV);
        $campos = [];
        foreach (configuracaoCampos($aba) as $chave => $campo) {
            $opcoes = null;
            if (isset($campo['opcoes'])) {
                $opcoes = [];
                foreach ($campo['opcoes'] as $valor => $nome) {
                    $opcoes[] = ['valor' => (string) $valor, 'nome' => (string) $nome];
                }
            }
            $campos[] = [
                'chave' => $chave,
                'rotulo' => (string) $campo['rotulo'],
                'tipo' => (string) $campo['tipo'],
                'grupo' => $campo['grupo'] ?? null,
                'ajuda' => $campo['ajuda'] ?? null,
                'opcoes' => $opcoes,
                'valor' => $valores[$chave] ?? null,
            ];
        }

        return ['id' => $aba, 'nome' => CONFIG_ABAS[$aba], 'campos' => $campos];
    }

    public function auditoria(): Resposta
    {
        $consulta = $this->ctx->consulta();

        return $this->listarPeloModelo($this->ctx->modelo('Audit_model'), [
            'pesquisa' => $consulta['busca'] ?? '',
            'de' => $consulta['de'] ?? '',
            'ate' => $consulta['ate'] ?? '',
        ], static fn (object $l): array => [
            'id' => (int) $l->idLogs,
            'usuario' => Valor::textoOuNulo($l->usuario),
            'acao' => (string) $l->tarefa,
            'ip' => Valor::textoOuNulo($l->ip),
            'data_hora' => Valor::dataHora(trim($l->data . ' ' . $l->hora)),
        ]);
    }

    public function limparAuditoria(): Resposta
    {
        $this->db->where('data <', date('Y-m-d', strtotime('-30 days')))->delete('logs');
        $apagados = (int) $this->db->affected_rows();
        $this->ctx->auditar('Efetuou limpeza de logs');

        return Resposta::dados(['apagados' => $apagados]);
    }

    public function emails(): Resposta
    {
        return $this->listarPeloModelo($this->ctx->modelo('Email_model'), ['status' => $this->ctx->consulta()['status'] ?? ''], static fn (object $e): array => [
            'id' => (int) $e->id,
            'para' => (string) $e->to,
            'assunto' => Valor::textoOuNulo(emailFilaAssunto($e->headers ?? null)),
            'status' => in_array($e->status, ['pending', 'sending', 'sent', 'failed'], true) ? $e->status : 'pending',
            'data_hora' => Valor::dataHora($e->date),
        ]);
    }

    public function excluirEmail(): Resposta
    {
        $id = (int) $this->encontrar('email_queue', 'id', $this->ctx->id(), 'E-mail não encontrado na fila.')->id;
        $this->removerLinha('email_queue', 'id', $id);
        $this->ctx->auditar('Removeu um e-mail da fila de envio. ID: ' . $id);

        return Resposta::semConteudo();
    }

    public function sistema(): Resposta
    {
        $atualizador = $this->ctx->biblioteca('github_updater');

        return Resposta::dados([
            'versao' => $atualizador->versaoAtual(),
            'migracoes_pendentes' => count(migracoesPendentes($this->db)),
            'problemas' => array_values(array_map('strval', $atualizador->problemasDoAmbiente())),
        ]);
    }

    public function atualizarBanco(): Resposta
    {
        $migracao = $this->ctx->biblioteca('migration');
        try {
            $resultado = $migracao->latest();
        } catch (Throwable $erro) {
            log_message('error', 'API, atualizar banco: ' . $erro->getMessage());

            throw HttpErro::interno('A atualização do banco parou: ' . $erro->getMessage());
        }
        if ($resultado === false) {
            throw HttpErro::interno(trim(strip_tags($migracao->error_string())) ?: 'A atualização do banco falhou.');
        }
        $this->ctx->auditar('Atualizou o banco de dados.');

        return Resposta::dados(['migracoes_pendentes' => count(migracoesPendentes($this->db))]);
    }

    public function atualizacao(): Resposta
    {
        $atualizador = $this->ctx->biblioteca('github_updater');
        try {
            $release = $atualizador->ultimaRelease();
        } catch (Throwable $e) {
            log_message('error', 'API, atualização do Map-OS: ' . $e->getMessage());

            throw new HttpErro(502, 'github_indisponivel', 'GitHub indisponível', 'Não foi possível consultar as releases no GitHub agora. Tente de novo em alguns minutos.');
        }

        return Resposta::dados([
            'versao_atual' => $atualizador->versaoAtual(),
            'ultima_versao' => isset($release['tag']) ? (string) $release['tag'] : null,
            'publicada_em' => Valor::dataHora($release['publicada_em'] ?? null),
            'notas' => Valor::textoOuNulo($release['notas'] ?? null),
            'tem_atualizacao' => $atualizador->temAtualizacao($release),
            'problemas' => array_values(array_map('strval', $atualizador->problemasDoAmbiente())),
        ]);
    }

    public function atualizarMapos(): Resposta
    {
        try {
            $resultado = $this->ctx->biblioteca('github_updater')->atualizar($this->ctx->corpo()['versao']);
        } catch (Throwable $e) {
            log_message('error', 'API, atualização do Map-OS: ' . $e->getMessage());

            throw HttpErro::interno($e instanceof \RuntimeException ? $e->getMessage() : 'Erro ao atualizar o Map-OS. Veja o log da aplicação.');
        }
        $this->ctx->auditar('Atualizou o Map-OS para a versão ' . $resultado['versao'] . '.');

        return Resposta::dados(['versao' => (string) $resultado['versao']]);
    }

    public function backup(): Resposta
    {
        $this->ctx->ci->load->dbutil();
        $conteudo = $this->ctx->ci->dbutil->backup([
            'format' => 'zip',
            'foreign_key_checks' => false,
            'filename' => 'backup' . date('d-m-Y') . '.sql',
            'ignore' => ['ci_sessions', 'login_attempts', 'api_sessoes', 'api_tokens_renovacao', 'api_rate_limit', 'resets_de_senha'],
        ]);
        $this->ctx->auditar('Efetuou backup do banco de dados.');

        return Resposta::conteudo((string) $conteudo, backupNomeArquivo(time()), 'application/zip');
    }
}
