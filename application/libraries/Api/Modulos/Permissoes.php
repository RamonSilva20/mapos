<?php

namespace Libraries\Api\Modulos;

use Libraries\Api\Contexto;
use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Modulo;
use Libraries\Api\Recursos\Valor;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Schema\Schema;

final class Permissoes extends Modulo
{
    public static function rotas(): array
    {
        $tag = 'Grupos de permissão';

        return [
            Rota::get('/permissoes', [self::class, 'catalogo'])->permissao('cPermissao', 'cUsuario')->tag($tag)
                ->resumo('Catálogo das permissões', 'Todos os códigos aceitos em `permissoes`, agrupados como na tela do painel.')
                ->dados(Schema::lista(Schema::objeto([
                    'codigo' => Schema::texto()->obrigatorio()->exemplo('vCliente'),
                    'grupo' => Schema::texto()->obrigatorio()->exemplo('Clientes e fornecedores'),
                    'nome' => Schema::texto()->obrigatorio()->exemplo('Ver'),
                    'descricao' => Schema::texto()->anulavel()->obrigatorio(),
                ]))),

            Rota::get('/grupos', [self::class, 'listar'])->permissao('cPermissao', 'cUsuario')->tag($tag)
                ->resumo('Lista os grupos de permissão')
                ->dados(Schema::lista(self::schema())),

            Rota::post('/grupos', [self::class, 'criar'])->permissao('cPermissao')->tag($tag)
                ->resumo('Cria um grupo', 'Quem não é administrador só marca permissões que o próprio grupo tem.')
                ->corpo(self::entrada())
                ->criado(self::schema())
                ->erros(403),

            Rota::get('/grupos/{id}', [self::class, 'ver'])->permissao('cPermissao', 'cUsuario')->tag($tag)
                ->resumo('Mostra um grupo')
                ->dados(self::schema())
                ->erros(404),

            Rota::patch('/grupos/{id}', [self::class, 'atualizar'])->permissao('cPermissao')->tag($tag)
                ->resumo('Altera um grupo', 'O grupo Administrador e o do próprio usuário não são desativados e mantêm `cUsuario` e `cPermissao`.')
                ->corpo(self::entrada()->semObrigatorios())
                ->dados(self::schema())
                ->erros(403, 404),
        ];
    }

    public static function schema(): Schema
    {
        static $schema = null;

        return $schema ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio(),
            'nome' => Schema::texto()->obrigatorio(),
            'ativo' => Schema::booleano()->obrigatorio(),
            'exigir_2fa' => Schema::booleano()->obrigatorio(),
            'permissoes' => Schema::lista(Schema::texto())->obrigatorio(),
            'usuarios' => Schema::inteiro()->obrigatorio()->descricao('Quantos usuários estão no grupo.'),
            'data' => Schema::data()->anulavel()->obrigatorio(),
        ])->nome('GrupoDePermissao');
    }

    private static function entrada(): Schema
    {
        return Schema::objeto([
            'nome' => Schema::texto()->obrigatorio()->max(80),
            'ativo' => Schema::booleano()->padrao(true),
            'exigir_2fa' => Schema::booleano()->padrao(false),
            'permissoes' => Schema::lista(Schema::enum(permissoesCodigos()))->obrigatorio()->max(200),
        ])->nome('GrupoDePermissaoEntrada');
    }

    public static function podeDelegarGrupo(Contexto $ctx, int $idGrupo): bool
    {
        $grupo = $ctx->db()->select('permissoes')->where('idPermissao', $idGrupo)->get('permissoes', 1)->row();

        return $grupo !== null && self::podeDelegar($ctx, permissoesMarcadas($grupo->permissoes ?? null));
    }

    /** @param  list<string>  $codigos */
    public static function podeDelegar(Contexto $ctx, array $codigos): bool
    {
        $titular = $ctx->titular();

        return delegacaoPermitida($titular->ehAdministrador(), $titular->permissoes, $codigos);
    }

    public static function de(object $g): array
    {
        return [
            'id' => (int) $g->idPermissao,
            'nome' => (string) $g->nome,
            'ativo' => (int) $g->situacao === 1,
            'exigir_2fa' => (int) ($g->exigir_2fa ?? 0) === 1,
            'permissoes' => permissoesMarcadas($g->permissoes ?? null),
            'usuarios' => (int) ($g->usuarios ?? 0),
            'data' => Valor::data($g->data ?? null),
        ];
    }

    public function catalogo(): Resposta
    {
        $lista = [];
        foreach (PERMISSOES_MODULOS as $modulo => $nomeModulo) {
            foreach (PERMISSOES_ACOES as $acao => $nomeAcao) {
                $lista[] = ['codigo' => $acao . $modulo, 'grupo' => $nomeModulo, 'nome' => $nomeAcao, 'descricao' => null];
            }
        }
        foreach (PERMISSOES_RELATORIOS as $codigo => $nome) {
            $lista[] = ['codigo' => $codigo, 'grupo' => 'Relatórios', 'nome' => $nome, 'descricao' => null];
        }
        foreach (PERMISSOES_SISTEMA as $codigo => [$nome, $descricao]) {
            $lista[] = ['codigo' => $codigo, 'grupo' => 'Sistema', 'nome' => $nome, 'descricao' => $descricao];
        }

        return Resposta::dados($lista);
    }

    public function listar(): Resposta
    {
        return Resposta::dados(array_map([self::class, 'de'], $this->consulta()->order_by('permissoes.nome', 'ASC')->get()->result()));
    }

    public function ver(): Resposta
    {
        return Resposta::dados(self::de($this->grupo()));
    }

    public function criar(): Resposta
    {
        $dados = $this->paraBanco($this->ctx->corpo(), null);
        $id = $this->inserirLinha('permissoes', $dados + ['data' => date('Y-m-d')]);
        $this->ctx->auditar('Adicionou uma permissão');

        return Resposta::criado(self::de($this->grupo($id)), $this->ctx->url('grupos/' . $id));
    }

    public function atualizar(): Resposta
    {
        $grupo = $this->grupo();
        $id = (int) $grupo->idPermissao;
        if (! self::podeDelegar($this->ctx, permissoesMarcadas($grupo->permissoes ?? null))) {
            throw HttpErro::proibido('Este grupo tem permissões que você não tem. Só quem as tem pode alterá-lo.', 'fora_do_alcance');
        }
        [$entrada] = $this->ctx->corpoParcial([
            'nome' => (string) $grupo->nome,
            'ativo' => (int) $grupo->situacao === 1,
            'exigir_2fa' => (int) ($grupo->exigir_2fa ?? 0) === 1,
            'permissoes' => permissoesMarcadas($grupo->permissoes ?? null),
        ]);
        $this->atualizarLinha('permissoes', 'idPermissao', $id, $this->paraBanco($entrada, $grupo));
        $this->ctx->auditar('Alterou uma permissão. ID: ' . $id);

        return Resposta::dados(self::de($this->grupo($id)));
    }

    private function consulta(): object
    {
        return $this->db->select('permissoes.*')
            ->select('(SELECT COUNT(*) FROM usuarios WHERE usuarios.permissoes_id = permissoes.idPermissao) AS usuarios', false)
            ->from('permissoes');
    }

    private function grupo(?int $id = null): object
    {
        $grupo = $this->consulta()->where('permissoes.idPermissao', $id ?? $this->ctx->id())->limit(1)->get()->row();
        if (! $grupo) {
            throw HttpErro::naoEncontrado('Grupo de permissão não encontrado.');
        }

        return $grupo;
    }

    private function paraBanco(array $entrada, ?object $grupo): array
    {
        [$dados, $erros] = permissaoDadosDoFormulario([
            'nome' => (string) $entrada['nome'],
            'permissoes' => array_values($entrada['permissoes'] ?? []),
            'situacao' => ! empty($entrada['ativo']) ? '1' : '0',
        ], false);
        if ($erros !== []) {
            throw HttpErro::validacao($erros);
        }
        $dados['situacao'] = ! empty($entrada['ativo']) ? 1 : 0;
        $dados['exigir_2fa'] = ! empty($entrada['exigir_2fa']) ? 1 : 0;

        $marcadas = permissoesMarcadas($dados['permissoes']);
        $doLogado = $grupo !== null && (int) $grupo->idPermissao === $this->ctx->titular()->grupo;
        $protegido = $doLogado || ($grupo !== null && (int) $grupo->idPermissao === PERMISSAO_ADMIN);

        if ($protegido && $dados['situacao'] !== 1) {
            throw HttpErro::validacao(['ativo' => 'Este grupo não pode ser desativado.']);
        }
        if ($protegido && array_diff(['cPermissao', 'cUsuario'], $marcadas) !== []) {
            throw HttpErro::validacao(['permissoes' => ($doLogado ? 'Este é o grupo do seu usuário' : 'Este é o grupo Administrador') . ': mantenha cUsuario e cPermissao para não perder o acesso.']);
        }
        if (! self::podeDelegar($this->ctx, $marcadas)) {
            throw HttpErro::proibido('Você só pode marcar permissões que você também tem.', 'fora_do_alcance');
        }

        return $dados;
    }
}
