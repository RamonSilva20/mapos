<?php

namespace Libraries\Api\Modulos;

use Libraries\Api\Http\Resposta;
use Libraries\Api\Modulo;
use Libraries\Api\Recursos\Valor;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Schema\Comuns;
use Libraries\Api\Schema\Schema;

final class Servicos extends Modulo
{
    public static function rotas(): array
    {
        $tag = 'Serviços';

        return [
            Rota::get('/servicos', [self::class, 'listar'])->permissao('vServico')->tag($tag)
                ->resumo('Lista os serviços')
                ->consulta(Comuns::paginacaoConsulta(['busca' => Schema::texto()->max(100)]))
                ->lista(self::schema()),

            Rota::post('/servicos', [self::class, 'criar'])->permissao('aServico')->tag($tag)
                ->resumo('Cadastra um serviço')
                ->corpo(self::entrada())
                ->criado(self::schema()),

            Rota::get('/servicos/{id}', [self::class, 'ver'])->permissao('vServico')->tag($tag)
                ->resumo('Mostra um serviço')
                ->dados(self::schema())
                ->erros(404),

            Rota::patch('/servicos/{id}', [self::class, 'atualizar'])->permissao('eServico')->tag($tag)
                ->resumo('Altera um serviço')
                ->corpo(self::entrada()->semObrigatorios())
                ->dados(self::schema())
                ->erros(404),

            Rota::delete('/servicos/{id}', [self::class, 'excluir'])->permissao('dServico')->tag($tag)
                ->resumo('Exclui um serviço', 'Como no painel, o serviço sai também das OS em que foi usado.')
                ->semConteudo()
                ->erros(404),
        ];
    }

    public static function schema(): Schema
    {
        static $schema = null;

        return $schema ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio(),
            'nome' => Schema::texto()->obrigatorio()->exemplo('Troca de tela'),
            'descricao' => Schema::texto()->anulavel()->obrigatorio(),
            'preco' => Schema::decimal()->obrigatorio(),
        ])->nome('Servico');
    }

    private static function entrada(): Schema
    {
        return Schema::objeto([
            'nome' => Schema::texto()->obrigatorio()->max(45),
            'descricao' => Schema::texto()->max(45),
            'preco' => Schema::decimal()->obrigatorio()->min(0)->max(99999999.99),
        ])->nome('ServicoEntrada');
    }

    public static function de(object $s): array
    {
        return [
            'id' => (int) $s->idServicos,
            'nome' => (string) $s->nome,
            'descricao' => Valor::textoOuNulo($s->descricao),
            'preco' => Valor::decimal($s->preco),
        ];
    }

    public function listar(): Resposta
    {
        return $this->listarPeloModelo($this->ctx->modelo('Servicos_model'), ['pesquisa' => $this->ctx->consulta()['busca'] ?? ''], [self::class, 'de']);
    }

    public function ver(): Resposta
    {
        return Resposta::dados(self::de($this->servico()));
    }

    public function criar(): Resposta
    {
        $id = $this->inserirLinha('servicos', $this->paraBanco($this->ctx->corpo()));
        $this->ctx->auditar('Adicionou um serviço');

        return Resposta::criado(self::de($this->servico($id)), $this->ctx->url('servicos/' . $id));
    }

    public function atualizar(): Resposta
    {
        $servico = $this->servico();
        [$dados] = $this->ctx->corpoParcial(['nome' => (string) $servico->nome, 'descricao' => (string) $servico->descricao, 'preco' => Valor::decimal($servico->preco)]);
        $id = (int) $servico->idServicos;
        $this->atualizarLinha('servicos', 'idServicos', $id, $this->paraBanco($dados));
        $this->ctx->auditar('Alterou um serviço. ID: ' . $id);

        return Resposta::dados(self::de($this->servico($id)));
    }

    public function excluir(): Resposta
    {
        $id = (int) $this->servico()->idServicos;
        $this->transacao(function () use ($id): void {
            $this->removerLinha('servicos_os', 'servicos_id', $id);
            $this->removerLinha('servicos', 'idServicos', $id);
        });
        $this->ctx->auditar('Removeu um serviço. ID: ' . $id);

        return Resposta::semConteudo();
    }

    private function servico(?int $id = null): object
    {
        return $this->encontrar('servicos', 'idServicos', $id ?? $this->ctx->id(), 'Serviço não encontrado.');
    }

    private function paraBanco(array $dados): array
    {
        return ['nome' => $dados['nome'], 'descricao' => (string) ($dados['descricao'] ?? ''), 'preco' => $dados['preco']];
    }
}
