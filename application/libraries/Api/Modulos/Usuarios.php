<?php

namespace Libraries\Api\Modulos;

use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Modulo;
use Libraries\Api\Recursos\Endereco;
use Libraries\Api\Recursos\Usuario;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Schema\Comuns;
use Libraries\Api\Schema\Schema;

final class Usuarios extends Modulo
{
    public static function rotas(): array
    {
        $tag = 'Usuários';

        return [
            Rota::get('/usuarios', [self::class, 'listar'])->permissao('cUsuario')->tag($tag)
                ->resumo('Lista os usuários do painel', 'Em ordem alfabética.')
                ->consulta(Comuns::paginacaoConsulta([
                    'busca' => Schema::texto()->max(100)->descricao('Nome, e-mail, CPF ou telefone.'),
                    'situacao' => Schema::enum(['ativo', 'inativo']),
                ]))
                ->lista(Usuario::schema()),

            Rota::post('/usuarios', [self::class, 'criar'])->permissao('cUsuario')->tag($tag)
                ->resumo('Cadastra um usuário', 'Quem não é administrador só escolhe grupos com permissões que o próprio grupo tem.')
                ->corpo(self::entrada(true))
                ->criado(Usuario::schema()),

            Rota::get('/usuarios/{id}', [self::class, 'ver'])->permissao('cUsuario')->tag($tag)
                ->resumo('Mostra um usuário')
                ->dados(Usuario::schema())
                ->erros(404),

            Rota::patch('/usuarios/{id}', [self::class, 'atualizar'])->permissao('cUsuario')->tag($tag)
                ->resumo('Altera um usuário', "Regras do painel: o administrador principal só é alterado por ele mesmo e não é desativado; ninguém desativa a si mesmo nem troca o próprio grupo; quem não é administrador só altera usuários de grupos que ele alcança.\n\nTrocar a senha ou desativar encerra as sessões do usuário.")
                ->corpo(self::entrada(false)->semObrigatorios())
                ->dados(Usuario::schema())
                ->erros(403, 404),

            Rota::delete('/usuarios/{id}', [self::class, 'excluir'])->permissao('cUsuario')->tag($tag)
                ->resumo('Exclui um usuário', 'Recusa com 409 se o usuário tem OS, vendas, lançamentos ou termos: desative em vez de excluir.')
                ->semConteudo()
                ->erros(403, 404, 409),

            Rota::post('/usuarios/{id}/dois-fatores/redefinir', [self::class, 'redefinirDoisFatores'])->permissao('cUsuario')->tag($tag)
                ->resumo('Desliga o 2FA de um usuário', 'Para quem perdeu o aplicativo e os códigos de recuperação.')
                ->semConteudo()
                ->erros(403, 404),
        ];
    }

    private static function entrada(bool $novo): Schema
    {
        return Schema::objeto([
            'nome' => Schema::texto()->obrigatorio()->max(80),
            'email' => Schema::email()->obrigatorio()->max(80),
            'cpf' => Schema::texto()->obrigatorio()->max(20)->regra(static fn (string $v): ?string => verific_cpf_cnpj($v) ? null : 'CPF inválido.'),
            'rg' => Schema::texto()->max(20),
            'telefone' => Schema::texto()->obrigatorio()->max(20),
            'celular' => Schema::texto()->max(20),
            'senha' => Schema::senha()->min(USUARIO_SENHA_MINIMA)->max(72)->obrigatorio($novo),
            'endereco' => Endereco::entrada(),
            'ativo' => Schema::booleano()->padrao(true),
            'grupo_id' => Schema::inteiro()->obrigatorio()->min(1),
            'data_expiracao' => Schema::data()->anulavel()->descricao('Nulo: não expira.'),
        ])->nome($novo ? 'UsuarioEntrada' : 'UsuarioAlteracao');
    }

    public function listar(): Resposta
    {
        $consulta = $this->ctx->consulta();

        return $this->listarPeloModelo($this->modelo(), [
            'pesquisa' => $consulta['busca'] ?? '',
            'situacao' => $consulta['situacao'] ?? '',
        ], static fn (object $u): array => Usuario::de($u));
    }

    public function ver(): Resposta
    {
        return Resposta::dados(Usuario::de($this->usuario()));
    }

    public function criar(): Resposta
    {
        $entrada = $this->ctx->corpo();
        $dados = $this->paraBanco($entrada, null);
        $id = $this->inserirLinha('usuarios', $dados + ['dataCadastro' => date('Y-m-d')]);
        $this->ctx->auditar('Adicionou um usuário.');

        return Resposta::criado(Usuario::de($this->usuario($id)), $this->ctx->url('usuarios/' . $id));
    }

    public function atualizar(): Resposta
    {
        $usuario = $this->usuarioAlcancavel();
        $id = (int) $usuario->idUsuarios;
        [$entrada] = $this->ctx->corpoParcial([
            'nome' => (string) $usuario->nome,
            'email' => (string) $usuario->email,
            'cpf' => (string) $usuario->cpf,
            'rg' => (string) $usuario->rg,
            'telefone' => (string) $usuario->telefone,
            'celular' => (string) $usuario->celular,
            'endereco' => Endereco::de($usuario->cep, $usuario->rua, $usuario->numero, $usuario->bairro, $usuario->cidade, $usuario->estado),
            'ativo' => (int) $usuario->situacao === 1,
            'grupo_id' => (int) $usuario->permissoes_id,
            'data_expiracao' => substr((string) $usuario->dataExpiracao, 0, 10) ?: null,
        ]);
        $dados = $this->paraBanco($entrada, $usuario);

        $this->ctx->modelo('Usuarios_model')->edit('usuarios', $dados, 'idUsuarios', $id);
        $this->ctx->auditar('Alterou um usuário. ID: ' . $id);

        return Resposta::dados(Usuario::de($this->usuario($id)));
    }

    public function excluir(): Resposta
    {
        $usuario = $this->usuarioAlcancavel();
        $id = (int) $usuario->idUsuarios;
        if (($motivo = usuarioPodeSerRemovido($id, $this->ctx->titular()->id)) !== null) {
            throw HttpErro::proibido($motivo, 'usuario_protegido');
        }
        if ($this->modelo()->referencias($id) > 0) {
            throw HttpErro::conflito('Este usuário tem OS, vendas, lançamentos ou termos de garantia. Desative-o em vez de excluir.', 'usuario_com_registros');
        }

        $this->transacao(function () use ($id): void {
            $this->removerLinha('usuarios', 'idUsuarios', $id);
            $this->ctx->sessoes->encerrarDoTitular(Rota::USUARIO, $id, 'conta_excluida');
        });
        $this->ctx->auditar('Removeu um usuário. ID: ' . $id);

        return Resposta::semConteudo();
    }

    public function redefinirDoisFatores(): Resposta
    {
        $usuario = $this->usuarioAlcancavel();
        $id = (int) $usuario->idUsuarios;
        $this->ctx->modelo('Mapos_model')->desativarTotp($id);
        $this->ctx->auditar('Redefiniu o 2FA de um usuário. ID: ' . $id);

        return Resposta::semConteudo();
    }

    private function modelo(): object
    {
        return $this->ctx->modelo('Usuarios_model');
    }

    private function usuario(?int $id = null): object
    {
        $usuario = $this->ctx->modelo('Mapos_model')->getById($id ?? $this->ctx->id());
        if (! $usuario) {
            throw HttpErro::naoEncontrado('Usuário não encontrado.');
        }

        return $usuario;
    }

    private function usuarioAlcancavel(): object
    {
        $usuario = $this->usuario();
        $motivo = usuarioPodeSerEditadoPor((int) $usuario->idUsuarios, $this->ctx->titular()->id);
        if ($motivo === null && ! Permissoes::podeDelegarGrupo($this->ctx, (int) $usuario->permissoes_id)) {
            $motivo = 'Este usuário tem permissões que você não tem. Só quem as tem pode alterá-lo.';
        }
        if ($motivo !== null) {
            throw HttpErro::proibido($motivo, 'fora_do_alcance');
        }

        return $usuario;
    }

    private function paraBanco(array $entrada, ?object $usuario): array
    {
        $endereco = is_array($entrada['endereco'] ?? null) ? $entrada['endereco'] : [];
        [$dados, $erros, $senha] = usuarioDadosDoFormulario([
            'nome' => (string) $entrada['nome'],
            'rg' => (string) ($entrada['rg'] ?? ''),
            'cpf' => (string) $entrada['cpf'],
            'email' => (string) $entrada['email'],
            'telefone' => (string) $entrada['telefone'],
            'celular' => (string) ($entrada['celular'] ?? ''),
            'senha' => (string) ($entrada['senha'] ?? ''),
            'dataExpiracao' => (string) ($entrada['data_expiracao'] ?? ''),
            'situacao' => ! empty($entrada['ativo']) ? '1' : '0',
            'permissoes_id' => (string) $entrada['grupo_id'],
        ] + Endereco::paraBanco($endereco), $usuario === null);

        $mapa = ['permissoes_id' => 'grupo_id', 'dataExpiracao' => 'data_expiracao', 'situacao' => 'ativo', 'estado' => 'endereco.estado'];
        $erros = $this->traduzirErros($erros, $mapa);
        $idAtual = $usuario !== null ? (int) $usuario->idUsuarios : null;
        $logado = $this->ctx->titular()->id;

        if (! isset($erros['cpf']) && $this->modelo()->cpfEmUso($dados['cpf'], $idAtual)) {
            $erros['cpf'] = 'Este CPF já está cadastrado para outro usuário.';
        }
        if ($this->modelo()->emailEmUso($dados['email'], $idAtual)) {
            $erros['email'] = 'Já existe um usuário com este e-mail.';
        }
        if ($erros !== []) {
            throw HttpErro::validacao($erros);
        }

        if ($usuario !== null && $dados['situacao'] === 0 && ($motivo = usuarioPodeSerRemovido($idAtual, $logado)) !== null) {
            throw HttpErro::proibido($motivo, 'usuario_protegido');
        }
        if ($usuario !== null && $idAtual === $logado && $dados['permissoes_id'] !== (int) $usuario->permissoes_id) {
            throw HttpErro::proibido('Você não pode trocar o grupo do seu próprio usuário.', 'proprio_grupo');
        }
        if ($idAtual === USUARIO_SUPER_ADMIN && $dados['permissoes_id'] !== PERMISSAO_ADMIN) {
            throw HttpErro::proibido('O administrador principal não sai do grupo Administrador.', 'usuario_protegido');
        }

        $grupo = $this->db->where('idPermissao', $dados['permissoes_id'])->get('permissoes', 1)->row();
        $mesmoGrupo = $usuario !== null && (int) $usuario->permissoes_id === $dados['permissoes_id'];
        if (! $grupo || ((int) $grupo->situacao !== 1 && ! $mesmoGrupo)) {
            throw HttpErro::validacao(['grupo_id' => 'Escolha um grupo de permissão ativo.']);
        }
        if (! $mesmoGrupo && ! Permissoes::podeDelegarGrupo($this->ctx, (int) $grupo->idPermissao)) {
            throw HttpErro::proibido('Você só pode escolher grupos com permissões que você também tem.', 'fora_do_alcance');
        }

        if ($senha !== null) {
            $dados['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        }

        return $dados;
    }
}
