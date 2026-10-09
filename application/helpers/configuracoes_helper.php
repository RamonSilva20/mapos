<?php

/**
 * Configurações do sistema, Emitente, Fila de e-mails e Auditoria migrados
 * (#2846), em funções puras para serem testadas sem banco nem sessão.
 *
 * A tela de configurações é montada a partir de CONFIG_ABAS e
 * configuracaoCampos(): cada aba é um formulário próprio, que grava só os
 * campos dela (na tabela configuracoes ou no .env).
 */
if (! defined('CONFIG_ABAS')) {
    define('CONFIG_ABAS', [
        'geral' => 'Geral',
        'os' => 'Ordens de serviço',
        'vendas' => 'Vendas e estoque',
        'financeiro' => 'Financeiro',
        'email' => 'E-mail',
        'pagamentos' => 'Pagamentos',
        'api' => 'API',
        'sistema' => 'Atualizações',
    ]);
}

if (! defined('CONFIG_MARCADORES_WHATSAPP')) {
    // Marcadores da mensagem de WhatsApp da OS (osTextoWhatsApp()).
    define('CONFIG_MARCADORES_WHATSAPP', [
        '{CLIENTE_NOME}' => 'Nome do cliente',
        '{NUMERO_OS}' => 'Número da OS',
        '{STATUS_OS}' => 'Status da OS',
        '{VALOR_OS}' => 'Valor da OS',
        '{DESCRI_PRODUTOS}' => 'Descrição do produto',
        '{EMITENTE}' => 'Nome do emitente',
        '{TELEFONE_EMITENTE}' => 'Telefone do emitente',
        '{OBS_OS}' => 'Observações',
        '{DEFEITO_OS}' => 'Defeito',
        '{LAUDO_OS}' => 'Laudo',
        '{DATA_INICIAL}' => 'Data inicial',
        '{DATA_FINAL}' => 'Data final',
        '{DATA_GARANTIA}' => 'Garantia',
    ]);
}

if (! function_exists('configuracaoDiasDeBoleto')) {
    /** Opções de vencimento do boleto: P1D => 1 dia ... P30D => 30 dias. */
    function configuracaoDiasDeBoleto(): array
    {
        $opcoes = [];
        for ($i = 1; $i <= 30; $i++) {
            $opcoes['P' . $i . 'D'] = $i . ($i === 1 ? ' dia' : ' dias');
        }

        return $opcoes;
    }
}

if (! function_exists('configuracaoCampos')) {
    /**
     * Campos de uma aba. Cada campo:
     *
     * - destino: config (tabela configuracoes) ou env (application/.env);
     * - tipo: sim_nao (1/0), bool_env (true/false), opcao (lista em opcoes),
     *   texto, area (textarea), numero, segredo (vazio mantém o atual; o
     *   valor nunca volta para a tela), status (lista de status de OS em
     *   JSON) ou gerar_jwt (marcado gera uma chave nova);
     * - rotulo, ajuda, obrigatorio, max, grupo (subtítulo na aba).
     *
     * @return array<string, array<string, mixed>>
     */
    function configuracaoCampos(string $aba): array
    {
        $statusOs = array_combine(array_keys(OS_STATUS_VARIANTES), array_keys(OS_STATUS_VARIANTES));

        return match ($aba) {
            'geral' => [
                'app_name' => ['destino' => 'config', 'tipo' => 'texto', 'rotulo' => 'Nome do sistema', 'obrigatorio' => true, 'max' => 100, 'ajuda' => 'Aparece no título das páginas e nos e-mails.'],
                'per_page' => ['destino' => 'config', 'tipo' => 'opcao', 'rotulo' => 'Registros por página', 'opcoes' => ['10' => '10', '20' => '20', '50' => '50', '100' => '100']],
                'app_tema_modo' => ['destino' => 'config', 'tipo' => 'opcao', 'rotulo' => 'Tema padrão', 'opcoes' => ['claro' => 'Claro', 'escuro' => 'Escuro', 'sistema' => 'Igual ao do sistema operacional'], 'ajuda' => 'Cada pessoa pode trocar o tema na barra do topo.'],
                'control_datatable' => ['destino' => 'config', 'tipo' => 'sim_nao', 'rotulo' => 'Busca e ordenação nas tabelas das telas antigas', 'ajuda' => 'Vale só para as telas que ainda não foram migradas para a v5.'],
            ],
            'os' => [
                'control_editos' => ['destino' => 'config', 'tipo' => 'sim_nao', 'rotulo' => 'Permitir editar OS faturada ou cancelada'],
                'os_status_list' => ['destino' => 'config', 'tipo' => 'status', 'rotulo' => 'Status mostrados na listagem de OS', 'opcoes' => $statusOs, 'ajuda' => 'Sem filtro, a listagem mostra só estes. Os demais aparecem ao filtrar pelo status.'],
                'control_2vias' => ['destino' => 'config', 'tipo' => 'sim_nao', 'rotulo' => 'Imprimir a OS em 2 vias'],
                'IMPRIMIR_ANEXOS' => ['destino' => 'env', 'tipo' => 'bool_env', 'rotulo' => 'Imprimir os anexos (imagens) com a OS'],
                'os_notification' => ['destino' => 'config', 'tipo' => 'opcao', 'rotulo' => 'Quem recebe o e-mail da OS', 'grupo' => 'Notificações', 'opcoes' => ['todos' => 'Todos (cliente, técnico e emitente)', 'cliente' => 'Só o cliente', 'tecnico' => 'Só o técnico', 'emitente' => 'Só o emitente', 'nenhum' => 'Ninguém']],
                'email_automatico' => ['destino' => 'config', 'tipo' => 'sim_nao', 'rotulo' => 'Enviar o e-mail ao criar e editar a OS'],
                'notifica_whats' => ['destino' => 'config', 'tipo' => 'area', 'rotulo' => 'Mensagem de WhatsApp da OS', 'max' => 2000, 'ajuda' => 'Use os marcadores abaixo; eles são trocados pelos dados da OS.'],
            ],
            'vendas' => [
                'control_estoque' => ['destino' => 'config', 'tipo' => 'sim_nao', 'rotulo' => 'Controlar o estoque', 'ajuda' => 'Adicionar produto à OS ou à venda baixa o estoque; tirar, cancelar ou excluir devolve.'],
                'control_edit_vendas' => ['destino' => 'config', 'tipo' => 'sim_nao', 'rotulo' => 'Permitir editar venda faturada ou cancelada'],
            ],
            'financeiro' => [
                'control_baixa' => ['destino' => 'config', 'tipo' => 'sim_nao', 'rotulo' => 'Impedir editar lançamento já pago'],
                'pix_key' => ['destino' => 'config', 'tipo' => 'texto', 'rotulo' => 'Chave PIX', 'max' => 100, 'ajuda' => 'CPF, CNPJ, e-mail, telefone ou chave aleatória. Gera o QR Code e o copia e cola na OS e na venda. Em branco, sem PIX.'],
            ],
            'email' => [
                'EMAIL_PROTOCOL' => ['destino' => 'env', 'tipo' => 'opcao', 'rotulo' => 'Protocolo', 'opcoes' => ['smtp' => 'SMTP', 'mail' => 'mail() do PHP', 'sendmail' => 'Sendmail']],
                'EMAIL_SMTP_HOST' => ['destino' => 'env', 'tipo' => 'texto', 'rotulo' => 'Servidor SMTP', 'max' => 255],
                'EMAIL_SMTP_PORT' => ['destino' => 'env', 'tipo' => 'numero', 'rotulo' => 'Porta', 'max' => 5],
                'EMAIL_SMTP_CRYPTO' => ['destino' => 'env', 'tipo' => 'opcao', 'rotulo' => 'Criptografia', 'opcoes' => ['tls' => 'TLS', 'ssl' => 'SSL']],
                'EMAIL_SMTP_USER' => ['destino' => 'env', 'tipo' => 'texto', 'rotulo' => 'Usuário', 'max' => 255],
                'EMAIL_SMTP_PASS' => ['destino' => 'env', 'tipo' => 'segredo', 'rotulo' => 'Senha', 'max' => 255],
            ],
            'pagamentos' => [
                'PAYMENT_GATEWAYS_EFI_PRODUCTION' => ['destino' => 'env', 'tipo' => 'bool_env', 'rotulo' => 'Ambiente de produção', 'grupo' => 'Efí (antiga Gerencianet)'],
                'PAYMENT_GATEWAYS_EFI_CREDENTIAIS_CLIENT_ID' => ['destino' => 'env', 'tipo' => 'texto', 'rotulo' => 'Client ID', 'max' => 255],
                'PAYMENT_GATEWAYS_EFI_CREDENTIAIS_CLIENT_SECRET' => ['destino' => 'env', 'tipo' => 'segredo', 'rotulo' => 'Client secret', 'max' => 255],
                'PAYMENT_GATEWAYS_EFI_BOLETO_EXPIRATION' => ['destino' => 'env', 'tipo' => 'opcao', 'rotulo' => 'Vencimento do boleto', 'opcoes' => configuracaoDiasDeBoleto(), 'ajuda' => 'Dias depois de gerar a cobrança.'],
                'PAYMENT_GATEWAYS_MERCADO_PAGO_CREDENTIALS_PUBLIC_KEY' => ['destino' => 'env', 'tipo' => 'texto', 'rotulo' => 'Public key', 'max' => 255, 'grupo' => 'Mercado Pago'],
                'PAYMENT_GATEWAYS_MERCADO_PAGO_CREDENTIALS_ACCESS_TOKEN' => ['destino' => 'env', 'tipo' => 'segredo', 'rotulo' => 'Access token', 'max' => 255],
                'PAYMENT_GATEWAYS_MERCADO_PAGO_CREDENTIALS_CLIENT_ID' => ['destino' => 'env', 'tipo' => 'texto', 'rotulo' => 'Client ID', 'max' => 255],
                'PAYMENT_GATEWAYS_MERCADO_PAGO_CREDENTIALS_CLIENT_SECRET' => ['destino' => 'env', 'tipo' => 'segredo', 'rotulo' => 'Client secret', 'max' => 255],
                'PAYMENT_GATEWAYS_MERCADO_PAGO_BOLETO_EXPIRATION' => ['destino' => 'env', 'tipo' => 'opcao', 'rotulo' => 'Vencimento do boleto', 'opcoes' => configuracaoDiasDeBoleto()],
                'PAYMENT_GATEWAYS_ASAAS_PRODUCTION' => ['destino' => 'env', 'tipo' => 'bool_env', 'rotulo' => 'Ambiente de produção', 'grupo' => 'Asaas'],
                'PAYMENT_GATEWAYS_ASAAS_NOTIFY' => ['destino' => 'env', 'tipo' => 'bool_env', 'rotulo' => 'Asaas avisa o cliente da cobrança'],
                'PAYMENT_GATEWAYS_ASAAS_CREDENTIAIS_API_KEY' => ['destino' => 'env', 'tipo' => 'segredo', 'rotulo' => 'Chave da API', 'max' => 255],
                'PAYMENT_GATEWAYS_ASAAS_BOLETO_EXPIRATION' => ['destino' => 'env', 'tipo' => 'opcao', 'rotulo' => 'Vencimento do boleto', 'opcoes' => configuracaoDiasDeBoleto()],
            ],
            'api' => [
                'API_ENABLED' => ['destino' => 'env', 'tipo' => 'bool_env', 'rotulo' => 'API ligada', 'ajuda' => 'Usada pelo aplicativo do Map-OS.'],
                'API_TOKEN_EXPIRE_TIME' => ['destino' => 'env', 'tipo' => 'opcao', 'rotulo' => 'Validade do token de acesso', 'opcoes' => ['60' => '1 minuto', '3600' => '1 hora', '86400' => '1 dia', '604800' => '1 semana', '2592000' => '1 mês']],
                'API_JWT_KEY' => ['destino' => 'env', 'tipo' => 'gerar_jwt', 'rotulo' => 'Gerar uma chave nova para os tokens', 'ajuda' => 'Todos os tokens emitidos deixam de valer: quem usa o aplicativo entra de novo.'],
            ],
            default => [],
        };
    }
}

if (! function_exists('configuracaoDadosDoFormulario')) {
    /**
     * Confere o POST de uma aba e separa o que vai para a tabela
     * configuracoes e o que vai para o .env. Só os campos da aba entram.
     *
     * @param  array<string, mixed>  $post
     * @param  callable(): string    $novaChave  Gera a chave JWT (injetável nos testes)
     * @return array{0: array<string, string>, 1: array<string, string>, 2: array<string, string>}  [config, env, erros]
     */
    function configuracaoDadosDoFormulario(string $aba, array $post, ?callable $novaChave = null): array
    {
        $config = [];
        $env = [];
        $erros = [];

        foreach (configuracaoCampos($aba) as $nome => $campo) {
            $bruto = $post[$nome] ?? null;
            $texto = is_scalar($bruto) ? trim(str_replace(["\r\n", "\r"], "\n", (string) $bruto)) : '';
            $valor = null;

            switch ($campo['tipo']) {
                case 'sim_nao':
                    $valor = in_array($texto, ['1', 'on'], true) ? '1' : '0';
                    break;
                case 'bool_env':
                    $valor = in_array($texto, ['1', 'on', 'true'], true) ? 'true' : 'false';
                    break;
                case 'opcao':
                    if (! array_key_exists($texto, $campo['opcoes'])) {
                        $erros[$nome] = 'Escolha uma opção da lista.';
                        break;
                    }
                    $valor = $texto;
                    break;
                case 'status':
                    $escolhidos = is_array($bruto) ? array_values(array_intersect(array_keys($campo['opcoes']), $bruto)) : [];
                    if ($escolhidos === []) {
                        $erros[$nome] = 'Marque ao menos um status.';
                        break;
                    }
                    $valor = (string) json_encode($escolhidos, JSON_UNESCAPED_UNICODE);
                    break;
                case 'numero':
                    if ($texto !== '' && (! ctype_digit($texto) || strlen($texto) > ($campo['max'] ?? 10))) {
                        $erros[$nome] = 'Use só números.';
                        break;
                    }
                    $valor = $texto;
                    break;
                case 'segredo':
                    // Em branco mantém o atual: o valor nunca volta para a tela.
                    if ($texto === '') {
                        continue 2;
                    }
                    $valor = $texto;
                    break;
                case 'gerar_jwt':
                    if (! in_array($texto, ['1', 'on'], true)) {
                        continue 2;
                    }
                    $valor = ($novaChave ?? static fn () => base64_encode(random_bytes(32)))();
                    break;
                default:
                    $valor = $texto;
            }

            if ($valor === null) {
                continue;
            }

            if (($campo['obrigatorio'] ?? false) && $valor === '') {
                $erros[$nome] = 'Preencha este campo.';
            } elseif (isset($campo['max']) && in_array($campo['tipo'], ['texto', 'area', 'segredo'], true) && mb_strlen($valor) > $campo['max']) {
                $erros[$nome] = 'Até ' . $campo['max'] . ' caracteres.';
            }

            if ($campo['destino'] === 'env') {
                $env[$nome] = $valor;
            } else {
                $config[$nome] = $valor;
            }
        }

        if ($aba === 'financeiro' && ($config['pix_key'] ?? '') !== '' && ! isset($erros['pix_key']) && function_exists('valid_pix_key') && ! valid_pix_key($config['pix_key'])) {
            $erros['pix_key'] = 'Chave PIX inválida. Use CPF, CNPJ, e-mail, telefone com DDD ou a chave aleatória.';
        }

        // As telas antigas ainda leem app_theme (tema-*.css): acompanha o modo.
        if (isset($config['app_tema_modo'])) {
            $config['app_theme'] = $config['app_tema_modo'] === 'escuro' ? 'puredark' : 'white';
        }

        return [$config, $env, $erros];
    }
}

if (! function_exists('configuracaoValorEnv')) {
    /**
     * Valor no formato do .env (phpdotenv): sem aspas quando só tem
     * caracteres seguros; entre aspas simples (literal) quando pode; entre
     * aspas duplas com \ e " escapados quando tem aspas simples. Quebras de
     * linha saem: cada valor é uma linha só.
     */
    function configuracaoValorEnv(string $valor): string
    {
        $valor = str_replace(["\r", "\n"], '', $valor);

        if ($valor === '' || preg_match('#^[A-Za-z0-9_.:/@+=-]+$#', $valor)) {
            return $valor;
        }

        if (! str_contains($valor, "'")) {
            return "'" . $valor . "'";
        }

        return '"' . addcslashes($valor, '\\"') . '"';
    }
}

if (! function_exists('configuracaoEnvAtualizado')) {
    /**
     * Conteúdo do .env com as chaves trocadas (a linha CHAVE=... inteira,
     * com ou sem aspas) ou acrescentadas no fim. Na v4 a troca procurava o
     * texto "CHAVE=valor atual", que não casava quando o valor estava entre
     * aspas, e o valor novo era gravado sem aspas.
     *
     * @param  array<string, string>  $valores
     */
    function configuracaoEnvAtualizado(string $conteudo, array $valores): string
    {
        foreach ($valores as $chave => $valor) {
            if (! preg_match('/^[A-Z][A-Z0-9_]*$/', $chave)) {
                continue;
            }

            $linha = $chave . '=' . configuracaoValorEnv($valor);
            $padrao = '/^' . preg_quote($chave, '/') . '=.*$/m';

            if (preg_match($padrao, $conteudo)) {
                $conteudo = (string) preg_replace_callback($padrao, static fn () => $linha, $conteudo, 1);
            } else {
                $conteudo = rtrim($conteudo, "\n") . "\n" . $linha . "\n";
            }
        }

        return $conteudo;
    }
}

if (! function_exists('configuracaoValores')) {
    /**
     * Valores para a tela de uma aba: o POST quando volta com erro, senão a
     * configuração e o .env atuais. Segredos nunca voltam (só se estão
     * definidos, para a ajuda do campo).
     *
     * @return array<string, mixed>
     */
    function configuracaoValores(string $aba, ?array $post, array $configuracao, array $env): array
    {
        $valores = [];
        foreach (configuracaoCampos($aba) as $nome => $campo) {
            $atual = $campo['destino'] === 'env' ? ($env[$nome] ?? '') : ($configuracao[$nome] ?? '');

            $valores[$nome] = match ($campo['tipo']) {
                'segredo' => ['definido' => (string) $atual !== ''],
                'gerar_jwt' => false,
                'status' => $post !== null
                    ? array_values(array_intersect(array_keys($campo['opcoes']), is_array($post[$nome] ?? null) ? $post[$nome] : []))
                    : (osStatusVisiveis($atual) ?? array_keys($campo['opcoes'])),
                'bool_env' => $post !== null ? in_array($post[$nome] ?? null, ['1', 'on', 'true'], true) : filter_var($atual, FILTER_VALIDATE_BOOLEAN),
                'sim_nao' => $post !== null ? in_array($post[$nome] ?? null, ['1', 'on'], true) : (string) $atual === '1',
                default => $post !== null ? (is_scalar($post[$nome] ?? null) ? (string) $post[$nome] : '') : (string) $atual,
            };
        }

        if ($aba === 'geral' && ! isset($configuracao['app_tema_modo']) && $post === null) {
            $valores['app_tema_modo'] = temaConfiguracao($configuracao)['modo'];
        }

        return $valores;
    }
}

if (! function_exists('emitenteDadosDoFormulario')) {
    /**
     * Dados do emitente a partir do POST, com os erros por campo. Obrigatórios
     * como na v4: nome, CNPJ, endereço, telefone e e-mail.
     *
     * @param  array<string, mixed>  $post
     * @return array{0: array<string, string>, 1: array<string, string>}
     */
    function emitenteDadosDoFormulario(array $post): array
    {
        $campos = [
            'nome' => ['Informe a razão social.', 255],
            'cnpj' => ['Informe o CNPJ ou CPF.', 45],
            'ie' => [null, 50],
            'cep' => ['Informe o CEP.', 20],
            'rua' => ['Informe a rua.', 70],
            'numero' => ['Informe o número.', 15],
            'bairro' => ['Informe o bairro.', 45],
            'cidade' => ['Informe a cidade.', 45],
            'estado' => ['Escolha a UF.', 20],
            'telefone' => ['Informe o telefone.', 20],
            'email' => ['Informe o e-mail.', 255],
        ];

        $dados = [];
        $erros = [];
        foreach ($campos as $nome => [$obrigatorio, $max]) {
            $valor = is_scalar($post[$nome] ?? null) ? trim((string) $post[$nome]) : '';
            if ($valor === '' && $obrigatorio !== null) {
                $erros[$nome] = $obrigatorio;
            } elseif (mb_strlen($valor) > $max) {
                $erros[$nome] = 'Até ' . $max . ' caracteres.';
            }
            $dados[$nome] = $valor;
        }

        // O campo da tela chama "estado", como nos clientes e usuários (o
        // módulo do CEP preenche esse nome); a coluna é emitente.uf.
        $dados['uf'] = strtoupper($dados['estado']);
        unset($dados['estado']);
        if ($dados['uf'] !== '' && ! array_key_exists($dados['uf'], ufsDoBrasil())) {
            $erros['estado'] = 'Escolha a UF da lista.';
        }
        if ($dados['email'] !== '' && ! filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $erros['email'] = 'Informe um e-mail válido.';
        }
        if ($dados['cnpj'] !== '' && ! isset($erros['cnpj']) && ! verific_cpf_cnpj($dados['cnpj'])) {
            $erros['cnpj'] = 'Informe um CNPJ ou CPF válido.';
        }

        return [$dados, $erros];
    }
}

if (! function_exists('emitenteArquivoDoLogo')) {
    /**
     * Caminho do arquivo do logo atual que pode ser apagado ao trocar: só um
     * arquivo que existe dentro de assets/uploads, pelo nome que está no fim
     * da URL gravada. Na v4 trocar o logo apagava a pasta inteira.
     */
    function emitenteArquivoDoLogo(?string $urlLogo, string $pastaUploads): ?string
    {
        $pasta = realpath($pastaUploads);
        $nome = basename((string) parse_url((string) $urlLogo, PHP_URL_PATH));
        if ($pasta === false || $nome === '' || $nome === '.' || $nome === '..') {
            return null;
        }

        $arquivo = realpath($pasta . DIRECTORY_SEPARATOR . $nome);

        return $arquivo !== false && is_file($arquivo) && str_starts_with($arquivo, $pasta . DIRECTORY_SEPARATOR) ? $arquivo : null;
    }
}

if (! function_exists('emailFilaPill')) {
    /**
     * Situação de um e-mail da fila como pill-status.
     *
     * @return array{label: string, variant: string}
     */
    function emailFilaPill(?string $status): array
    {
        return match ($status) {
            'pending' => ['label' => 'Na fila', 'variant' => 'info'],
            'sending' => ['label' => 'Enviando', 'variant' => 'progress'],
            'sent' => ['label' => 'Enviado', 'variant' => 'success'],
            'failed' => ['label' => 'Falhou', 'variant' => 'warning'],
            default => ['label' => 'Sem status', 'variant' => 'neutral'],
        };
    }
}

if (! function_exists('emailFilaAssunto')) {
    /** Assunto do e-mail da fila (o cabeçalho Subject, guardado em JSON). */
    function emailFilaAssunto(?string $headers): string
    {
        $dados = json_decode((string) $headers, true);

        if (! is_array($dados) || ! is_scalar($dados['Subject'] ?? null)) {
            return '';
        }

        // O CI_Email grava o Subject codificado (=?UTF-8?Q?...?=).
        // Texto que já está puro (com acento) passa direto: o iconv descartaria
        // os caracteres fora do ASCII.
        $assunto = (string) $dados['Subject'];
        if (! str_contains($assunto, '=?')) {
            return trim($assunto);
        }

        $decodificado = function_exists('iconv_mime_decode') ? @iconv_mime_decode($assunto, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8') : mb_decode_mimeheader($assunto);

        return trim($decodificado !== false ? $decodificado : $assunto);
    }
}
