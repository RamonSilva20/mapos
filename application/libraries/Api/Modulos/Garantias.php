<?php

namespace Libraries\Api\Modulos;

use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Modulo;
use Libraries\Api\Recursos\Valor;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Schema\Comuns;
use Libraries\Api\Schema\Schema;

final class Garantias extends Modulo
{
    public static function rotas(): array
    {
        $tag = 'Termos de garantia';

        return [
            Rota::get('/garantias', [self::class, 'listar'])->permissao('vGarantia')->tag($tag)
                ->resumo('Lista os termos de garantia')
                ->consulta(Comuns::paginacaoConsulta(['busca' => Schema::texto()->max(100)->descricao('Referência ou texto do termo.')]))
                ->lista(self::schema()),

            Rota::post('/garantias', [self::class, 'criar'])->permissao('aGarantia')->tag($tag)
                ->resumo('Cadastra um termo de garantia')
                ->corpo(self::entrada())
                ->criado(self::schema()),

            Rota::get('/garantias/{id}', [self::class, 'ver'])->permissao('vGarantia')->tag($tag)
                ->resumo('Mostra um termo de garantia')
                ->dados(self::schema())
                ->erros(404),

            Rota::patch('/garantias/{id}', [self::class, 'atualizar'])->permissao('eGarantia')->tag($tag)
                ->resumo('Altera um termo de garantia')
                ->corpo(self::entrada()->semObrigatorios())
                ->dados(self::schema())
                ->erros(404),

            Rota::delete('/garantias/{id}', [self::class, 'excluir'])->permissao('dGarantia')->tag($tag)
                ->resumo('Exclui um termo de garantia', 'Recusa com 409 enquanto alguma OS usar o termo.')
                ->semConteudo()
                ->erros(404, 409),
        ];
    }

    public static function schema(): Schema
    {
        static $schema = null;

        return $schema ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio(),
            'referencia' => Schema::texto()->anulavel()->obrigatorio()->exemplo('Padrão 90 dias'),
            'texto' => Schema::texto()->obrigatorio()->descricao('Texto puro; quebras de linha preservadas.'),
            'data' => Schema::data()->anulavel()->obrigatorio(),
            'autor' => Comuns::pessoa()->anulavel()->obrigatorio(),
        ])->nome('TermoDeGarantia');
    }

    private static function entrada(): Schema
    {
        return Schema::objeto([
            'referencia' => Schema::texto()->max(15),
            'texto' => Schema::texto()->obrigatorio()->max(20000),
        ])->nome('TermoDeGarantiaEntrada');
    }

    public static function de(object $g): array
    {
        return [
            'id' => (int) $g->idGarantias,
            'referencia' => Valor::textoOuNulo($g->refGarantia),
            'texto' => Valor::textoDoEditor($g->textoGarantia),
            'data' => Valor::data($g->dataGarantia),
            'autor' => $g->usuarios_id ? ['id' => (int) $g->usuarios_id, 'nome' => (string) ($g->nome ?? '')] : null,
        ];
    }

    public function listar(): Resposta
    {
        return $this->listarPeloModelo($this->ctx->modelo('Garantias_model'), ['pesquisa' => $this->ctx->consulta()['busca'] ?? ''], [self::class, 'de']);
    }

    public function ver(): Resposta
    {
        return Resposta::dados(self::de($this->garantia()));
    }

    public function criar(): Resposta
    {
        $dados = $this->ctx->corpo();
        $id = $this->inserirLinha('garantias', [
            'refGarantia' => (string) ($dados['referencia'] ?? ''),
            'textoGarantia' => osTextoParaGravar($dados['texto']),
            'dataGarantia' => date('Y-m-d'),
            'usuarios_id' => $this->ctx->titular()->id,
        ]);
        $this->ctx->auditar('Adicionou uma garantia');

        return Resposta::criado(self::de($this->garantia($id)), $this->ctx->url('garantias/' . $id));
    }

    public function atualizar(): Resposta
    {
        $garantia = $this->garantia();
        [$dados] = $this->ctx->corpoParcial(['referencia' => (string) $garantia->refGarantia, 'texto' => osTextoParaEdicao($garantia->textoGarantia)]);
        $id = (int) $garantia->idGarantias;
        $this->atualizarLinha('garantias', 'idGarantias', $id, ['refGarantia' => (string) ($dados['referencia'] ?? ''), 'textoGarantia' => osTextoParaGravar($dados['texto'])]);
        $this->ctx->auditar('Alterou uma garantia. ID: ' . $id);

        return Resposta::dados(self::de($this->garantia($id)));
    }

    public function excluir(): Resposta
    {
        $id = (int) $this->garantia()->idGarantias;
        $emUso = (int) $this->db->where('garantias_id', $id)->count_all_results('os');
        if ($emUso > 0) {
            throw HttpErro::conflito(($emUso === 1 ? '1 OS usa' : $emUso . ' OS usam') . ' este termo. Troque o termo dela e tente de novo.', 'termo_em_uso');
        }
        $this->removerLinha('garantias', 'idGarantias', $id);
        $this->ctx->auditar('Removeu uma garantia. ID: ' . $id);

        return Resposta::semConteudo();
    }

    private function garantia(?int $id = null): object
    {
        $linha = $this->db->select('garantias.*, usuarios.nome')->from('garantias')->join('usuarios', 'usuarios.idUsuarios = garantias.usuarios_id', 'left')
            ->where('garantias.idGarantias', $id ?? $this->ctx->id())->limit(1)->get()->row();
        if (! $linha) {
            throw HttpErro::naoEncontrado('Termo de garantia não encontrado.');
        }

        return $linha;
    }
}
