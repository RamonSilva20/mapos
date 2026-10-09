<?php

/**
 * Regras de Usuários e Permissões migrados (#2846), em funções puras para
 * serem testadas sem banco nem sessão.
 */
if (! defined('USUARIO_SUPER_ADMIN')) {
    // O primeiro usuário (instalação) não pode ser desativado nem excluído.
    define('USUARIO_SUPER_ADMIN', 1);
}

if (! defined('PERMISSAO_ADMIN')) {
    // O grupo Administrador (instalação): não pode ser desativado nem perder
    // o acesso a usuários e permissões.
    define('PERMISSAO_ADMIN', 1);
}

if (! defined('USUARIO_SENHA_MINIMA')) {
    define('USUARIO_SENHA_MINIMA', 8);
}

if (! defined('PERMISSOES_MODULOS')) {
    /*
     * Módulos com as quatro ações (ver, adicionar, editar, excluir), na ordem
     * da matriz do formulário. A chave é o sufixo do código: vCliente,
     * aCliente, eCliente, dCliente.
     */
    define('PERMISSOES_MODULOS', [
        'Cliente' => 'Clientes e fornecedores',
        'Produto' => 'Produtos',
        'Servico' => 'Serviços',
        'Os' => 'Ordens de serviço',
        'Venda' => 'Vendas',
        'Garantia' => 'Termos de garantia',
        'Arquivo' => 'Arquivos',
        'Lancamento' => 'Lançamentos financeiros',
        'Cobranca' => 'Cobranças',
    ]);
}

if (! defined('PERMISSOES_ACOES')) {
    define('PERMISSOES_ACOES', ['v' => 'Ver', 'a' => 'Adicionar', 'e' => 'Editar', 'd' => 'Excluir']);
}

if (! defined('PERMISSOES_RELATORIOS')) {
    define('PERMISSOES_RELATORIOS', [
        'rCliente' => 'Clientes',
        'rProduto' => 'Produtos',
        'rServico' => 'Serviços',
        'rOs' => 'Ordens de serviço',
        'rVenda' => 'Vendas',
        'rFinanceiro' => 'Financeiro',
    ]);
}

if (! defined('PERMISSOES_SISTEMA')) {
    define('PERMISSOES_SISTEMA', [
        'cUsuario' => ['Usuários', 'Cadastrar, editar e excluir usuários do painel.'],
        'cPermissao' => ['Permissões', 'Criar e alterar grupos de permissão (inclusive o próprio).'],
        'cEmitente' => ['Emitente', 'Dados da empresa nas impressões e no PIX.'],
        'cSistema' => ['Configurações do sistema', 'Preferências, atualização do banco e do Map-OS.'],
        'cEmail' => ['Fila de e-mails', 'Ver e limpar a fila de envio.'],
        'cAuditoria' => ['Auditoria', 'Ver o registro das ações dos usuários.'],
        'cBackup' => ['Backup', 'Baixar a cópia do banco de dados.'],
    ]);
}

if (! function_exists('permissoesCodigos')) {
    /**
     * Todos os códigos de permissão que o formulário grava, na ordem da tela.
     *
     * @return list<string>
     */
    function permissoesCodigos(): array
    {
        $codigos = [];
        foreach (array_keys(PERMISSOES_MODULOS) as $modulo) {
            foreach (array_keys(PERMISSOES_ACOES) as $acao) {
                $codigos[] = $acao . $modulo;
            }
        }

        return [...$codigos, ...array_keys(PERMISSOES_RELATORIOS), ...array_keys(PERMISSOES_SISTEMA)];
    }
}

if (! function_exists('permissoesDoFormulario')) {
    /**
     * Mapa código => 0|1 para gravar em permissoes.permissoes (JSON), a partir
     * das caixas marcadas no POST (permissoes[] com os códigos). Só os códigos
     * conhecidos entram; o que vier a mais é ignorado.
     *
     * @return array<string, int>
     */
    function permissoesDoFormulario($marcadas): array
    {
        $marcadas = is_array($marcadas) ? array_filter($marcadas, 'is_string') : [];
        $mapa = [];
        foreach (permissoesCodigos() as $codigo) {
            $mapa[$codigo] = in_array($codigo, $marcadas, true) ? 1 : 0;
        }

        // Adicionar, editar ou excluir sem ver não leva a lugar nenhum: ver
        // entra junto (a tela faz o mesmo; aqui vale também para POST direto).
        foreach (array_keys(PERMISSOES_MODULOS) as $modulo) {
            if ($mapa['a' . $modulo] || $mapa['e' . $modulo] || $mapa['d' . $modulo]) {
                $mapa['v' . $modulo] = 1;
            }
        }

        return $mapa;
    }
}

if (! function_exists('permissoesMarcadas')) {
    /**
     * Códigos ligados num grupo gravado (JSON da v5, ou o serialize() que a
     * v4 já gravou). Valor "1" ou 1 conta como ligado.
     *
     * @return list<string>
     */
    function permissoesMarcadas(?string $gravado): array
    {
        if (trim((string) $gravado) === '') {
            return [];
        }

        $mapa = json_decode_legacy((string) $gravado);
        if (! is_array($mapa)) {
            return [];
        }

        return array_values(array_filter(permissoesCodigos(), static fn ($codigo) => (string) ($mapa[$codigo] ?? '') === '1'));
    }
}

if (! function_exists('permissaoDadosDoFormulario')) {
    /**
     * Nome, situação e permissões de um grupo a partir do POST.
     *
     * @param  array<string, mixed>  $post
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    function permissaoDadosDoFormulario(array $post, bool $novo): array
    {
        $nome = is_scalar($post['nome'] ?? null) ? trim((string) $post['nome']) : '';
        $erros = [];

        if ($nome === '') {
            $erros['nome'] = 'Informe o nome do grupo.';
        } elseif (mb_strlen($nome) > 80) {
            $erros['nome'] = 'O nome tem até 80 caracteres.';
        }

        $dados = [
            'nome' => $nome,
            'permissoes' => json_encode(permissoesDoFormulario($post['permissoes'] ?? [])),
            // Grupo novo nasce ativo; ao editar, a caixa "Ativo" decide.
            'situacao' => $novo || in_array($post['situacao'] ?? null, ['1', 'on'], true) ? 1 : 0,
        ];

        return [$dados, $erros];
    }
}

if (! function_exists('usuarioDadosDoFormulario')) {
    /**
     * Converte e confere o POST do formulário de usuário, depois das regras
     * do form_validation (grupo "usuarios_formulario"). Só lê os campos do
     * formulário. A senha volta em texto (o controller grava o hash) e só
     * quando foi digitada: ao editar, em branco mantém a atual.
     *
     * @param  array<string, mixed>  $post
     * @return array{0: array<string, mixed>, 1: array<string, string>, 2: string|null}  [dados, erros, senha]
     */
    function usuarioDadosDoFormulario(array $post, bool $novo): array
    {
        $texto = static fn (string $campo): string => is_scalar($post[$campo] ?? null) ? trim((string) $post[$campo]) : '';
        $erros = [];

        $senha = is_scalar($post['senha'] ?? null) ? (string) $post['senha'] : '';
        if ($senha === '' && $novo) {
            $erros['senha'] = 'Informe a senha.';
        } elseif ($senha !== '' && mb_strlen($senha) < USUARIO_SENHA_MINIMA) {
            $erros['senha'] = 'A senha tem pelo menos ' . USUARIO_SENHA_MINIMA . ' caracteres.';
        }

        $estado = strtoupper($texto('estado'));
        if ($estado !== '' && ! array_key_exists($estado, ufsDoBrasil())) {
            $erros['estado'] = 'Escolha a UF da lista.';
        }

        $expiracao = $texto('dataExpiracao');
        $expiracaoYmd = $expiracao === '' ? null : dataIsoParaYmd($expiracao);
        if ($expiracao !== '' && $expiracaoYmd === null) {
            $erros['dataExpiracao'] = 'Informe uma data válida ou deixe em branco.';
        }

        $situacao = $texto('situacao');
        if (! in_array($situacao, ['0', '1'], true)) {
            $erros['situacao'] = 'Escolha a situação.';
        }

        $permissao = $texto('permissoes_id');
        if (! ctype_digit($permissao) || (int) $permissao <= 0) {
            $erros['permissoes_id'] = 'Escolha o grupo de permissão.';
        }

        $dados = [
            'nome' => $texto('nome'),
            'rg' => $texto('rg'),
            'cpf' => usuarioCpfFormatado($texto('cpf')),
            'email' => mb_strtolower($texto('email')),
            'telefone' => $texto('telefone'),
            'celular' => $texto('celular'),
            'cep' => $texto('cep'),
            'rua' => $texto('rua'),
            'numero' => $texto('numero'),
            'bairro' => $texto('bairro'),
            'cidade' => $texto('cidade'),
            'estado' => $estado,
            'dataExpiracao' => $expiracaoYmd,
            'situacao' => (int) $situacao,
            'permissoes_id' => (int) $permissao,
        ];

        return [$dados, $erros, $senha === '' ? null : $senha];
    }
}

if (! function_exists('usuarioCpfFormatado')) {
    /**
     * CPF sempre como 000.000.000-00 quando tem 11 dígitos, para a regra de
     * CPF único não ser contornada digitando só os números (o banco guarda
     * com a máscara). Outro formato fica como veio (a validação recusa).
     */
    function usuarioCpfFormatado(string $cpf): string
    {
        $digitos = (string) preg_replace('/\D/', '', $cpf);

        return strlen($digitos) === 11
            ? substr($digitos, 0, 3) . '.' . substr($digitos, 3, 3) . '.' . substr($digitos, 6, 3) . '-' . substr($digitos, 9)
            : trim($cpf);
    }
}

if (! function_exists('usuarioValoresDoFormulario')) {
    /**
     * Valores dos campos: o POST quando a tela volta com erro (sem a senha),
     * o usuário ao editar ou os padrões ao cadastrar.
     *
     * @return array<string, string>
     */
    function usuarioValoresDoFormulario(?array $post, ?object $usuario): array
    {
        $campos = ['nome', 'rg', 'cpf', 'email', 'telefone', 'celular', 'cep', 'rua', 'numero', 'bairro', 'cidade', 'estado', 'dataExpiracao', 'situacao', 'permissoes_id'];

        if ($post !== null) {
            $valores = [];
            foreach ($campos as $campo) {
                $valores[$campo] = is_scalar($post[$campo] ?? null) ? trim((string) $post[$campo]) : '';
            }

            return $valores;
        }

        if ($usuario !== null) {
            $valores = [];
            foreach ($campos as $campo) {
                $valores[$campo] = (string) ($usuario->{$campo} ?? '');
            }
            $valores['dataExpiracao'] = dataIsoParaYmd(substr($valores['dataExpiracao'], 0, 10)) ?? '';

            return $valores;
        }

        return array_merge(array_fill_keys($campos, ''), ['situacao' => '1']);
    }
}

if (! function_exists('usuarioSituacaoPill')) {
    /**
     * Situação do usuário para o pill-status: inativo, expirado (a data de
     * expiração já passou e o login é negado) ou ativo.
     *
     * @return array{label: string, variant: string}
     */
    function usuarioSituacaoPill(object $usuario, string $hoje): array
    {
        if ((int) ($usuario->situacao ?? 0) !== 1) {
            return ['label' => 'Inativo', 'variant' => 'neutral'];
        }

        $expiracao = dataIsoParaYmd(substr((string) ($usuario->dataExpiracao ?? ''), 0, 10));
        if ($expiracao !== null && $expiracao < $hoje) {
            return ['label' => 'Expirado', 'variant' => 'warning'];
        }

        return ['label' => 'Ativo', 'variant' => 'success'];
    }
}

if (! function_exists('usuarioPodeSerEditadoPor')) {
    /**
     * Motivo para o usuário logado não abrir a edição de um usuário, ou null:
     * só o próprio administrador principal altera os dados dele (senha,
     * grupo, e-mail), mesmo que outro usuário tenha cUsuario.
     */
    function usuarioPodeSerEditadoPor(int $id, int $logado): ?string
    {
        return $id === USUARIO_SUPER_ADMIN && $logado !== USUARIO_SUPER_ADMIN
            ? 'Só o próprio administrador principal altera os dados dele.'
            : null;
    }
}

if (! function_exists('usuarioPodeSerRemovido')) {
    /**
     * Motivo para não excluir (ou desativar) um usuário, ou null se pode: o
     * super admin e o próprio usuário logado ficam de fora.
     */
    function usuarioPodeSerRemovido(int $id, int $logado): ?string
    {
        if ($id === USUARIO_SUPER_ADMIN) {
            return 'O usuário administrador principal não pode ser desativado nem excluído.';
        }
        if ($id === $logado) {
            return 'Você não pode desativar nem excluir o seu próprio usuário.';
        }

        return null;
    }
}
