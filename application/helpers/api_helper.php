<?php

/**
 * Regras de saída e de edição da API (#2879), em funções puras para serem
 * testadas sem servidor.
 */
if (! function_exists('apiSemSenha')) {
    /**
     * Remove a coluna `senha` de um registro, de uma lista de registros ou de
     * um objeto com sub-registros. Telas e API não devolvem o hash.
     *
     * @param  mixed  $valor
     * @return mixed
     */
    function apiSemSenha($valor)
    {
        if (is_array($valor)) {
            unset($valor['senha']);
            foreach ($valor as $chave => $item) {
                if (is_array($item) || is_object($item)) {
                    $valor[$chave] = apiSemSenha($item);
                }
            }
        } elseif (is_object($valor)) {
            unset($valor->senha);
            foreach (get_object_vars($valor) as $chave => $item) {
                if (is_array($item) || is_object($item)) {
                    $valor->$chave = apiSemSenha($item);
                }
            }
        }

        return $valor;
    }
}

if (! function_exists('usuarioApiPlanoEdicao')) {
    /**
     * Decide o que `PUT /api/v1/usuarios/{id}` pode gravar.
     *
     * - Em outro usuário (exige cUsuario): perfil, situação, grupo e expiração.
     * - No próprio usuário: só os campos de perfil e a senha, e esta exige a
     *   senha atual. Grupo, situação e expiração nunca mudam por aqui, para
     *   ninguém se promover, reativar ou estender a própria conta.
     * - A senha é sempre gravada com hash; o e-mail não pode repetir.
     *
     * - Em outro usuário vale a mesma regra do painel (#2879): o administrador
     *   principal só é alterado por ele mesmo, e quem não é administrador só
     *   edita usuários de grupos, e só atribui grupos, com permissões que ele
     *   próprio tem ($podeDelegarGrupo).
     *
     * @param  object  $usuarios  Usuarios_model (emailEmUso, senhaConfere e getById)
     * @param  array<string, mixed>  $entrada  corpo da requisição
     * @param  callable(int): bool  $podeDelegarGrupo  o operador alcança o grupo (id)? Ver usuarioApiPodeDelegarGrupo()
     * @return array{ok: bool, status: int, message: string, dados: array<string, mixed>}
     */
    function usuarioApiPlanoEdicao($usuarios, int $idLogado, int $idAlvo, bool $podeGerir, array $entrada, callable $podeDelegarGrupo): array
    {
        $erro = static fn (int $status, string $message): array => ['ok' => false, 'status' => $status, 'message' => $message, 'dados' => []];
        $texto = static fn ($valor): string => is_scalar($valor) ? trim((string) $valor) : '';

        $proprio = $idLogado === $idAlvo;
        if ($idAlvo <= 0) {
            return $erro(400, 'Informe o ID do Usuário!');
        }
        if (! $proprio && ! $podeGerir) {
            return $erro(401, 'Você não está autorizado a Editar Usuários!');
        }
        if (! $proprio) {
            $motivo = usuarioPodeSerEditadoPor($idAlvo, $idLogado);
            if ($motivo !== null) {
                return $erro(403, $motivo);
            }
            $alvo = $usuarios->getById($idAlvo);
            if (! $alvo) {
                return $erro(404, 'Usuário não encontrado.');
            }
            if (! $podeDelegarGrupo((int) $alvo->permissoes_id)) {
                return $erro(403, 'Este usuário tem permissões que você não tem. Só quem as tem pode alterá-lo.');
            }
        }

        $obrigatorios = ['nome', 'rg', 'cpf', 'cep', 'rua', 'numero', 'bairro', 'cidade', 'estado', 'email', 'telefone'];
        if (! $proprio) {
            array_push($obrigatorios, 'situacao', 'permissoes_id');
        }
        foreach ($obrigatorios as $campo) {
            if ($texto($entrada[$campo] ?? null) === '') {
                return $erro(400, 'Preencha todos campos');
            }
        }

        $email = $texto($entrada['email']);
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $erro(400, 'Informe um e-mail válido.');
        }
        if ($usuarios->emailEmUso($email, $idAlvo)) {
            return $erro(400, 'Já existe um usuário com este e-mail.');
        }

        $dados = [];
        foreach (['nome', 'rg', 'cpf', 'cep', 'rua', 'numero', 'bairro', 'cidade', 'estado', 'telefone'] as $campo) {
            $dados[$campo] = $texto($entrada[$campo]);
        }
        $dados['email'] = $email;
        if (array_key_exists('celular', $entrada)) {
            $dados['celular'] = $texto($entrada['celular']);
        }

        if (! $proprio) {
            $situacao = $texto($entrada['situacao']);
            $grupo = $texto($entrada['permissoes_id']);
            if (! in_array($situacao, ['0', '1'], true) || ! ctype_digit($grupo) || (int) $grupo <= 0) {
                return $erro(400, 'Situação ou grupo de permissões inválido.');
            }
            if ($idAlvo === USUARIO_SUPER_ADMIN && ($situacao !== '1' || (int) $grupo !== PERMISSAO_ADMIN)) {
                return $erro(400, 'O usuário super admin não pode ser desativado nem sair do grupo Administrador!');
            }

            if ((int) $grupo !== (int) $alvo->permissoes_id && ! $podeDelegarGrupo((int) $grupo)) {
                return $erro(403, 'Você só pode escolher grupos com permissões que você também tem.');
            }

            $dados['situacao'] = (int) $situacao;
            $dados['permissoes_id'] = (int) $grupo;
            if (array_key_exists('dataExpiracao', $entrada)) {
                $expiracao = $texto($entrada['dataExpiracao']);
                if ($expiracao !== '' && strtotime($expiracao) === false) {
                    return $erro(400, 'Data de expiração inválida.');
                }
                $dados['dataExpiracao'] = $expiracao === '' ? null : $expiracao;
            }
        }

        $senha = $texto($entrada['senha'] ?? null);
        if ($senha !== '') {
            if (mb_strlen($senha) < USUARIO_SENHA_MINIMA) {
                return $erro(400, 'A senha deve ter pelo menos ' . USUARIO_SENHA_MINIMA . ' caracteres.');
            }
            if ($proprio) {
                $atual = is_scalar($entrada['senha_atual'] ?? null) ? (string) $entrada['senha_atual'] : '';
                if ($atual === '' || ! $usuarios->senhaConfere($idAlvo, $atual)) {
                    return $erro(403, 'Informe a senha atual para trocar a senha.');
                }
            }
            $dados['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        }

        return ['ok' => true, 'status' => 200, 'message' => '', 'dados' => $dados];
    }
}

if (! function_exists('usuarioApiPodeDelegarGrupo')) {
    /**
     * O operador da API alcança o grupo de permissões? Mesma regra do painel
     * (MY_Controller::podeDelegarGrupo, #2879): o administrador (usuário
     * principal ou grupo Administrador) delega tudo; os demais, só grupos cujas
     * permissões ele também tem. Grupo inexistente nunca é alcançado.
     *
     * @param  object  $permissoes  Permissoes_model (getById)
     */
    function usuarioApiPodeDelegarGrupo($permissoes, int $idLogado, int $grupoLogado, int $idGrupo): bool
    {
        $grupo = $idGrupo > 0 ? $permissoes->getById($idGrupo) : null;
        if (! $grupo) {
            return false;
        }
        if ($idLogado === USUARIO_SUPER_ADMIN || $grupoLogado === PERMISSAO_ADMIN) {
            return true;
        }

        $meu = $permissoes->getById($grupoLogado);

        return delegacaoPermitida(false, permissoesMarcadas($meu->permissoes ?? null), permissoesMarcadas($grupo->permissoes ?? null));
    }
}

if (! function_exists('usuarioApiGravarEdicao')) {
    /**
     * Grava a edição de um usuário e registra a auditoria.
     *
     * Trocar a senha (ou desativar a conta) invalida os access tokens do
     * usuário dentro de `edit()`. Quem edita o próprio usuário passa a ter um
     * token vencido, e `log_app()` sem o nome chamaria `logged_user()`, que o
     * recusaria com 403 mesmo com a gravação feita. Por isso o nome de quem
     * edita chega aqui lido ANTES do `edit()`.
     *
     * @param  object  $usuarios  Usuarios_model
     * @param  callable(string, string): mixed  $auditar  recebe a tarefa e o nome de quem editou (log_app)
     * @param  array<string, mixed>  $dados
     */
    function usuarioApiGravarEdicao($usuarios, callable $auditar, string $nomeLogado, int $id, array $dados): bool
    {
        if (! $usuarios->edit('usuarios', $dados, 'idUsuarios', $id)) {
            return false;
        }

        $auditar('Alterou um usuário. ID: ' . $id, $nomeLogado);

        return true;
    }
}
