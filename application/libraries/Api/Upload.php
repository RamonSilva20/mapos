<?php

namespace Libraries\Api;

use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Resposta;

final class Upload
{
    private const CAMPO = 'arquivo_api';

    public function __construct(private readonly Contexto $ctx)
    {
    }

    /**
     * @param  array<string, mixed>  $arquivo  entrada de $_FILES já validada pelo Schema::arquivo()
     * @param  list<string>  $extensoes
     * @return array{nome: string, caminho: string, pasta: string, original: string, imagem: bool, tamanho_kb: float, extensao: string}
     */
    public function receber(array $arquivo, string $pasta, array $extensoes, ?int $limiteKb = null, string $campo = 'arquivo', array $extras = []): array
    {
        $this->ctx->helper('armazenamento');
        $upload = $this->ctx->biblioteca('upload');

        $_FILES[self::CAMPO] = $arquivo;
        $config = armazenamentoConfigUpload($pasta, $extensoes);
        if ($limiteKb !== null) {
            $config['max_size'] = $limiteKb;
        }
        $upload->initialize($extras + $config, true);

        try {
            if (! armazenamentoReceber($upload, self::CAMPO)) {
                throw HttpErro::validacao([$campo => trim(strip_tags($upload->display_errors('', ''))) ?: 'Não foi possível receber o arquivo.']);
            }
        } finally {
            unset($_FILES[self::CAMPO]);
        }

        $dados = $upload->data();
        if (($erro = armazenamentoValidarEnvio($dados)) !== null) {
            throw HttpErro::validacao([$campo => $erro]);
        }

        return [
            'nome' => (string) $dados['file_name'],
            'caminho' => (string) $dados['full_path'],
            'pasta' => (string) $dados['file_path'],
            'original' => (string) $dados['client_name'],
            'imagem' => (bool) $dados['is_image'],
            'tamanho_kb' => (float) $dados['file_size'],
            'extensao' => strtolower(ltrim((string) $dados['file_ext'], '.')),
        ];
    }

    public function miniatura(string $origem, string $destino, int $largura = 200, int $altura = 125): bool
    {
        $imagem = $this->ctx->biblioteca('image_lib');
        $imagem->clear();
        $imagem->initialize(['source_image' => $origem, 'new_image' => $destino, 'width' => $largura, 'height' => $altura]);

        return (bool) $imagem->resize();
    }

    public static function resposta(string $arquivo, string $nome, bool $baixar = false): Resposta
    {
        $cabecalhos = armazenamentoCabecalhos($nome, pathinfo($arquivo, PATHINFO_EXTENSION), (int) filesize($arquivo), $baixar);

        return new Resposta(200, $cabecalhos, null, static function () use ($arquivo): void {
            readfile($arquivo);
        });
    }
}
