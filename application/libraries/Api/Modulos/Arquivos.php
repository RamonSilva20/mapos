<?php

namespace Libraries\Api\Modulos;

use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Modulo;
use Libraries\Api\Recursos\Valor;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Schema\Comuns;
use Libraries\Api\Schema\Schema;
use Libraries\Api\Upload;

final class Arquivos extends Modulo
{
    private const EXTENSOES = ['txt', 'jpg', 'jpeg', 'gif', 'png', 'pdf'];

    public static function rotas(): array
    {
        $tag = 'Arquivos';

        return [
            Rota::get('/arquivos', [self::class, 'listar'])->permissao('vArquivo')->tag($tag)
                ->resumo('Lista os arquivos')
                ->consulta(Comuns::paginacaoConsulta([
                    'busca' => Schema::texto()->max(100)->descricao('Nome ou descrição.'),
                    'de' => Schema::data(),
                    'ate' => Schema::data(),
                ]))
                ->lista(self::schema()),

            Rota::post('/arquivos', [self::class, 'criar'])->permissao('aArquivo')->tag($tag)
                ->resumo('Envia um arquivo', 'multipart/form-data. TXT, JPG, GIF, PNG ou PDF; imagens de até 3000 × 2000 px.')
                ->corpo(Schema::objeto([
                    'arquivo' => Schema::arquivo()->obrigatorio(),
                    'nome' => Schema::texto()->obrigatorio()->max(70),
                    'descricao' => Schema::texto()->max(2000),
                    'data' => Schema::data()->descricao('Padrão: hoje.'),
                ]), true)
                ->criado(self::schema())
                ->erros(413),

            Rota::get('/arquivos/{id}', [self::class, 'ver'])->permissao('vArquivo')->tag($tag)
                ->resumo('Mostra os dados de um arquivo')
                ->dados(self::schema())
                ->erros(404),

            Rota::get('/arquivos/{id}/download', [self::class, 'baixar'])->permissao('vArquivo')->tag($tag)
                ->resumo('Baixa o arquivo')
                ->consulta(Schema::objeto(['download' => Schema::booleano()->padrao(false)->descricao('true força o download em vez de abrir no navegador.')]))
                ->arquivo()
                ->erros(404),

            Rota::patch('/arquivos/{id}', [self::class, 'atualizar'])->permissao('eArquivo')->tag($tag)
                ->resumo('Altera nome, descrição ou data', 'O arquivo em si não muda: para trocar, envie outro.')
                ->corpo(Schema::objeto([
                    'nome' => Schema::texto()->max(70),
                    'descricao' => Schema::texto()->max(2000),
                    'data' => Schema::data(),
                ]))
                ->dados(self::schema())
                ->erros(404),

            Rota::delete('/arquivos/{id}', [self::class, 'excluir'])->permissao('dArquivo')->tag($tag)
                ->resumo('Exclui um arquivo')
                ->semConteudo()
                ->erros(404),
        ];
    }

    public static function schema(): Schema
    {
        static $schema = null;

        return $schema ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio(),
            'nome' => Schema::texto()->obrigatorio(),
            'descricao' => Schema::texto()->anulavel()->obrigatorio(),
            'data' => Schema::data()->anulavel()->obrigatorio(),
            'extensao' => Schema::texto()->anulavel()->obrigatorio()->exemplo('pdf'),
            'tamanho_kb' => Schema::numero()->anulavel()->obrigatorio(),
            'url' => Schema::url()->obrigatorio()->descricao('Download autenticado (Authorization: Bearer).'),
        ])->nome('Arquivo');
    }

    public function de(object $a): array
    {
        return [
            'id' => (int) $a->idDocumentos,
            'nome' => (string) $a->documento,
            'descricao' => Valor::textoOuNulo($a->descricao),
            'data' => Valor::data($a->cadastro),
            'extensao' => Valor::textoOuNulo(ltrim((string) $a->tipo, '.')),
            'tamanho_kb' => is_numeric($a->tamanho) ? (float) $a->tamanho : null,
            'url' => $this->ctx->url('arquivos/' . (int) $a->idDocumentos . '/download'),
        ];
    }

    public function listar(): Resposta
    {
        $consulta = $this->ctx->consulta();

        return $this->listarPeloModelo($this->ctx->modelo('Arquivos_model'), [
            'pesquisa' => $consulta['busca'] ?? '',
            'de' => $consulta['de'] ?? '',
            'ate' => $consulta['ate'] ?? '',
        ], [$this, 'de']);
    }

    public function ver(): Resposta
    {
        return Resposta::dados($this->de($this->arquivo()));
    }

    public function criar(): Resposta
    {
        $dados = $this->ctx->corpo();
        $relativo = 'arquivos/' . date('d-m-Y');
        $pasta = armazenamentoPasta($relativo);
        if ($pasta === null) {
            throw HttpErro::interno('Não foi possível criar a pasta de arquivos no servidor.');
        }

        $enviado = (new Upload($this->ctx))->receber($dados['arquivo'], $pasta, self::EXTENSOES, null, 'arquivo', ['max_width' => 3000, 'max_height' => 2000]);

        $id = $this->inserirLinha('documentos', [
            'documento' => $dados['nome'],
            'descricao' => (string) ($dados['descricao'] ?? ''),
            'cadastro' => $dados['data'] ?? date('Y-m-d'),
            'file' => $enviado['nome'],
            'path' => $relativo . '/' . $enviado['nome'],
            'url' => '',
            'tamanho' => (string) $enviado['tamanho_kb'],
            'tipo' => '.' . $enviado['extensao'],
        ]);
        $this->ctx->auditar('Adicionou um arquivo');

        return Resposta::criado($this->de($this->arquivo($id)), $this->ctx->url('arquivos/' . $id));
    }

    public function baixar(): Resposta
    {
        $registro = $this->arquivo();
        $caminho = armazenamentoLocalizar((string) $registro->path, [FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'arquivos']);
        if ($caminho === null) {
            throw HttpErro::naoEncontrado('O arquivo não foi encontrado no servidor.');
        }
        $nome = trim((string) $registro->documento) !== '' ? (string) $registro->documento : 'arquivo';
        $extensao = strtolower(pathinfo($caminho, PATHINFO_EXTENSION));

        return Upload::resposta($caminho, str_ends_with(strtolower($nome), '.' . $extensao) ? $nome : $nome . '.' . $extensao, (bool) $this->ctx->consulta()['download']);
    }

    public function atualizar(): Resposta
    {
        $registro = $this->arquivo();
        [$dados] = $this->ctx->corpoParcial(['nome' => (string) $registro->documento, 'descricao' => (string) $registro->descricao, 'data' => Valor::data($registro->cadastro)]);
        if (($dados['nome'] ?? '') === '') {
            throw HttpErro::validacao(['nome' => 'Campo obrigatório.']);
        }
        $id = (int) $registro->idDocumentos;
        $this->atualizarLinha('documentos', 'idDocumentos', $id, [
            'documento' => $dados['nome'],
            'descricao' => (string) ($dados['descricao'] ?? ''),
            'cadastro' => $dados['data'] ?? date('Y-m-d'),
        ]);
        $this->ctx->auditar('Alterou um arquivo, ID: ' . $id);

        return Resposta::dados($this->de($this->arquivo($id)));
    }

    public function excluir(): Resposta
    {
        $registro = $this->arquivo();
        $id = (int) $registro->idDocumentos;
        $this->removerLinha('documentos', 'idDocumentos', $id);
        $caminho = armazenamentoLocalizar((string) $registro->path, [FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'arquivos']);
        if ($caminho !== null) {
            @unlink($caminho);
        }
        $this->ctx->auditar('Removeu um arquivo. ID: ' . $id);

        return Resposta::semConteudo();
    }

    private function arquivo(?int $id = null): object
    {
        return $this->encontrar('documentos', 'idDocumentos', $id ?? $this->ctx->id(), 'Arquivo não encontrado.');
    }
}
